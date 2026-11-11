<?php
/**
 * Pull queued CMS writes and apply to the local tenant DB (outbound path).
 * Run via cron or GET ?token=... (also nudged from onboarding UI).
 */
declare(strict_types=1);

const TOKEN = 'YOUR_BRIDGE_PUSH_TOKEN';
const WRITES_URL = 'https://www.cms.whoopix.ai/sync-bridge/writes.php';
const BRIDGE_TOKEN = 'YOUR_BRIDGE_TOKEN';
const PUSH_TOKEN = 'YOUR_BRIDGE_PUSH_TOKEN';
/** Deploy marker — bump when sanitize / apply order changes. */
const APPLY_PATCH_VERSION = '20260809-dyn-mod-v1';

/**
 * Allow CMS admin browser fetch (wpxMenuApplyToTenant) from cms.whoopix.ai origins.
 */
function wpx_sync_bridge_apply_cors(): void {
    if (php_sapi_name() === 'cli') {
        return;
    }
    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    $allowed = array(
        'https://www.cms.whoopix.ai',
        'https://cms.whoopix.ai',
    );
    if ($origin !== '' && in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
    }
}

if (php_sapi_name() !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    wpx_sync_bridge_apply_cors();
    http_response_code(204);
    exit;
}

if (php_sapi_name() !== 'cli' && !hash_equals(TOKEN, (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    exit;
}

wpx_sync_bridge_apply_cors();
header('Content-Type: application/json; charset=utf-8');

$helper = __DIR__ . '/sync_bridge_db.php';
if (!is_file($helper)) {
    $helper = dirname(__DIR__, 2) . '/sync_bridge_db.php';
}
require_once $helper;

/**
 * Call the central CMS bridge. Hosting DNS occasionally fails on both tenant and
 * CMS servers, so retry the same authenticated HTTPS hostname with its bound
 * platform IP. CURLOPT_RESOLVE preserves TLS SNI and never changes tenant scope.
 *
 * @return array{raw:string,http:int,error:string,resolved_fallback:bool}
 */
function wpx_sync_bridge_cms_request(string $body, int $timeout = 60): array {
    $attempts = array(
        array('resolve' => array()),
        array('resolve' => array('www.cms.whoopix.ai:443:195.189.140.104')),
    );
    $last = array('raw' => '', 'http' => 0, 'error' => '', 'resolved_fallback' => false);
    foreach ($attempts as $index => $attempt) {
        $ch = curl_init(WRITES_URL);
        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json', 'X-Bridge-Token: ' . BRIDGE_TOKEN),
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => $timeout,
        );
        if (!empty($attempt['resolve'])) {
            $opts[CURLOPT_RESOLVE] = $attempt['resolve'];
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $last = array(
            'raw' => is_string($raw) ? $raw : '',
            'http' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'error' => curl_error($ch),
            'resolved_fallback' => ($index > 0),
        );
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($last['http'] >= 200 && $last['http'] < 300 && $last['raw'] !== '') {
            return $last;
        }
        // Only retry transport/DNS failures. An HTTP response is authoritative.
        if ($last['http'] > 0 || ($errno !== CURLE_COULDNT_RESOLVE_HOST && $errno !== CURLE_COULDNT_CONNECT)) {
            break;
        }
    }
    return $last;
}

/**
 * domain_relink full-tree copy can wipe Lab includes with Site chrome
 * from /user/YOUR_CLIENT_ID/. Skip for this tenant only (never touches Site).
 */
if (!function_exists('wpx_sync_bridge_relink_or_protect_shai')) {
    function wpx_sync_bridge_relink_or_protect_shai(string $clientId, string $licenseId, ?PDO $pdo = null): array {
        if ($clientId === 'client' || $licenseId === '3453219') {
            // Re-assert Lab chrome from client's own bundles after any prior overwrite.
            $tpl = __DIR__ . '/templates';
            $inc = __DIR__ . '/includes';
            $restored = array();
            foreach (array('header' => 'header.php', 'footer' => 'footer.php') as $key => $file) {
                $bundle = $tpl . '/_bundle_' . $key . '.php';
                $dest = $inc . '/' . $file;
                if (!is_readable($bundle)) {
                    continue;
                }
                $src = (string) file_get_contents($bundle);
                if ($src === '') {
                    continue;
                }
                if (stripos($src, 'Site') !== false || stripos($src, 'site.co.il') !== false) {
                    $src = str_ireplace(
                        array('Site', 'site.co.il', 'contact@example.com'),
                        array('Lab', 'lab.co.il', 'contact@example.com'),
                        $src
                    );
                }
                $restored[$key] = @file_put_contents($dest, $src, LOCK_EX) !== false;
            }
            return array(
                'ok' => true,
                'skipped' => true,
                'reason' => 'shai_lab_protect_no_relink_overwrite',
                'chrome_restored' => $restored,
            );
        }
        return wpx_sync_bridge_relink_custom_domain($clientId, $licenseId, $pdo);
    }
}

$creds = wpx_sync_bridge_load_tenant_db(__DIR__);
$clientId = $creds['client_id'];
$dbName = $creds['db_name'];
$dbUser = $creds['db_user'];
$dbPass = $creds['db_pass'];

$licenseId = trim((string) ($creds['license_id'] ?? ''));
$operationId = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string) ($_GET['operation_id'] ?? ''));
$out = array(
    'client_id' => $clientId,
    'license_id' => $licenseId,
    'operation_id' => $operationId,
    'ok' => false,
    'applied' => 0,
    'acked' => 0,
);

if ($clientId === '') {
    echo json_encode(array('ok' => false, 'error' => 'missing client_id'));
    exit;
}
if ($creds['error'] !== '') {
    echo json_encode(array('ok' => false, 'error' => $creds['error'], 'client_id' => $clientId));
    exit;
}

$pullBody = json_encode(array(
    'client_id'  => $clientId,
    'license_id' => $licenseId,
    'action'     => 'pull',
    'operation_id' => $operationId,
), JSON_UNESCAPED_UNICODE);
$pullTransport = wpx_sync_bridge_cms_request($pullBody, 60);
$pullRaw = $pullTransport['raw'];
$pull = json_decode((string) $pullRaw, true);
$out['pull_http'] = $pullTransport['http'];
$out['pull_resolved_fallback'] = $pullTransport['resolved_fallback'];
if ($pullTransport['http'] < 200 || $pullTransport['http'] >= 300 || !is_array($pull) || empty($pull['success'])) {
    $out['error'] = 'bridge_pull_failed';
    $out['pull_error'] = $pullTransport['error'];
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}
$writes = (is_array($pull) && !empty($pull['writes']) && is_array($pull['writes'])) ? $pull['writes'] : array();
$fileItems = (is_array($pull) && !empty($pull['files']) && is_array($pull['files'])) ? $pull['files'] : array();

// Apply SERP history rows before other queued writes (mod_settings can block the queue).
usort($writes, function ($a, $b) {
    $prio = function ($w) {
        $t = is_array($w) ? (string) ($w['table'] ?? '') : '';
        if ($t === 'mod_serp_rankings' || $t === 'mod_serp_positions') {
            return 0;
        }
        if ($t === 'mod_serp_keywords') {
            return 1;
        }
        if ($t === 'mod_settings') {
            return 9;
        }
        return 5;
    };
    return $prio($a) <=> $prio($b);
});

$filesWritten = 0;
$fileAck = array();
$filesApplied = array();
foreach ($fileItems as $fItem) {
    if (!is_array($fItem) || empty($fItem['path']) || empty($fItem['content_b64'])) {
        continue;
    }
    $fType = preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($fItem['type'] ?? 'templates'));
    $fPath = ltrim(str_replace('\\', '/', (string) $fItem['path']), '/');
    if ($fType === '' || $fPath === '' || strpos($fPath, '..') !== false) {
        continue;
    }
    $bytes = base64_decode((string) $fItem['content_b64'], true);
    if ($bytes === false || $bytes === '') {
        continue;
    }
    if (($fType === 'images' || $fType === 'imported')) {
        if (strlen($bytes) > 20 * 1024 * 1024) {
            continue;
        }
        $imageInfo = @getimagesizefromstring($bytes);
        $imageType = is_array($imageInfo) && isset($imageInfo[2]) ? (int) $imageInfo[2] : 0;
        $imageTypes = array(
            IMAGETYPE_JPEG => array('mime' => 'image/jpeg', 'ext' => array('jpg', 'jpeg')),
            IMAGETYPE_PNG  => array('mime' => 'image/png', 'ext' => array('png')),
            IMAGETYPE_GIF  => array('mime' => 'image/gif', 'ext' => array('gif')),
            IMAGETYPE_WEBP => array('mime' => 'image/webp', 'ext' => array('webp')),
        );
        $incomingExt = strtolower(pathinfo($fPath, PATHINFO_EXTENSION));
        if (!isset($imageTypes[$imageType]) || !in_array($incomingExt, $imageTypes[$imageType]['ext'], true)) {
            continue;
        }
    }
    if ($fType === 'root' || $fType === 'sitemap') {
        $baseName = basename($fPath);
        if ($baseName === '' || $baseName !== $fPath || strpos($baseName, '..') !== false) {
            continue;
        }
        // sitemap type: only allow .xml at document root
        if ($fType === 'sitemap' && strtolower(pathinfo($baseName, PATHINFO_EXTENSION)) !== 'xml') {
            continue;
        }
        $destFile = __DIR__ . '/' . $baseName;
    } elseif ($fType === 'design') {
        $destFile = __DIR__ . '/resources/design/' . $fPath;
    } elseif ($fType === 'images' || $fType === 'videos') {
        // CMS template/media uploads → public files/upload (or files/videos).
        $baseName = basename($fPath);
        if ($baseName === '' || strpos($baseName, '..') !== false) {
            continue;
        }
        $mediaDir = ($fType === 'videos') ? 'files/videos' : 'files/upload';
        $destFile = __DIR__ . '/' . $mediaDir . '/' . $baseName;
    } elseif ($fType === 'imported') {
        // Copy Site assets land under files/upload/imported/{hash}.ext
        $baseName = basename($fPath);
        if ($baseName === '' || strpos($baseName, '..') !== false) {
            continue;
        }
        $destFile = __DIR__ . '/files/upload/imported/' . $baseName;
    } else {
        $destFile = __DIR__ . '/' . $fType . '/' . $fPath;
    }
    $destDir = dirname($destFile);
    if (!is_dir($destDir) && !@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        continue;
    }
    if (@file_put_contents($destFile, $bytes, LOCK_EX) !== false) {
        $filesWritten++;
        $fileAck[] = $fPath;
        $writtenMime = '';
        if ($fType === 'images' || $fType === 'imported') {
            $writtenMime = $imageTypes[$imageType]['mime'];
        }
        $filesApplied[] = array(
            'type' => $fType,
            'path' => $fPath,
            'operation_id' => (string) ($fItem['operation_id'] ?? pathinfo(basename($fPath), PATHINFO_FILENAME)),
            'bytes' => strlen($bytes),
            'mime' => $writtenMime,
            'sha256' => hash('sha256', $bytes),
        );
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($destFile, true);
        }
        @clearstatcache(true, $destFile);
    }
}
$out['files_written'] = $filesWritten;
$out['files'] = $filesApplied;

if (empty($writes) && $filesWritten <= 0) {
    $out['ok'] = true;
    $out['message'] = 'no_pending_writes';
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($writes) && $filesWritten > 0 && !empty($fileAck)) {
    $ackBody = json_encode(array(
        'client_id'  => $clientId,
        'license_id' => $licenseId,
        'action'     => 'ack',
        'ids'        => array(),
        'files'      => $fileAck,
    ), JSON_UNESCAPED_UNICODE);
    $ackTransport = wpx_sync_bridge_cms_request($ackBody, 30);
    $out['ack_http'] = $ackTransport['http'];
    $out['ack_resolved_fallback'] = $ackTransport['resolved_fallback'];
    $out['ok'] = true;
    try {
        $pdoRelink = new PDO('mysql:host=localhost;dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $dbPass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ));
        $out['domain_relink'] = wpx_sync_bridge_relink_or_protect_shai($clientId, $licenseId, $pdoRelink);
    } catch (Throwable $e) {
        $out['domain_relink'] = wpx_sync_bridge_relink_or_protect_shai($clientId, $licenseId, null);
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $dbPass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
} catch (Throwable $e) {
    echo json_encode(array('ok' => false, 'error' => 'db: ' . $e->getMessage()));
    exit;
}

// Dynamic blog CRUD is available in every self-serve CMS. Older tenant
// snapshots may not contain its table; create it before applying queued rows.
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `mod_blog_posts` (
        `id` INT NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL DEFAULT '',
        `slug` VARCHAR(255) NOT NULL DEFAULT '', `content` LONGTEXT, `excerpt` TEXT,
        `category_id` INT NOT NULL DEFAULT 0, `lang` VARCHAR(12) NOT NULL DEFAULT 'he',
        `status` VARCHAR(24) NOT NULL DEFAULT 'draft', `featured_image` TEXT,
        `meta_title` VARCHAR(255) NOT NULL DEFAULT '', `meta_description` TEXT,
        `meta_keywords` TEXT, `link_keywords` TEXT, `ai_generated` TINYINT(1) NOT NULL DEFAULT 0,
        `scheduled_date` DATETIME NULL, `published_at` DATETIME NULL,
        `created_at` DATETIME NULL, `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`), KEY `status_created` (`status`,`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {
    $out['blog_schema_error'] = $e->getMessage();
}

// Additive self-heal for managed tenants provisioned from an older schema.
try {
    $pageCols = $pdo->query("SHOW COLUMNS FROM `mod_pages_list` LIKE 'template'");
    if (!$pageCols || !$pageCols->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("ALTER TABLE `mod_pages_list` ADD COLUMN `template` INT(11) NOT NULL DEFAULT 0");
    }
    $pageIndexes = $pdo->query("SHOW INDEX FROM `mod_pages_list` WHERE `Key_name` = 'idx_wpx_page_template'");
    if (!$pageIndexes || !$pageIndexes->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("ALTER TABLE `mod_pages_list` ADD INDEX `idx_wpx_page_template` (`template`)");
    }
    $out['page_template_schema'] = APPLY_PATCH_VERSION;
} catch (Throwable $e) {
    $out['page_template_schema_error'] = $e->getMessage();
}

// Builder-created content types stage DDL packs under modules/_dynamic/schema/.
// Ensure those tables exist before applying queued row upserts.
try {
    $dynEnsured = array();
    $schemaDir = __DIR__ . '/modules/_dynamic/schema';
    if (is_dir($schemaDir)) {
        foreach (glob($schemaDir . '/*.json') ?: array() as $schemaFile) {
            $pack = json_decode((string) @file_get_contents($schemaFile), true);
            if (!is_array($pack) || empty($pack['tables']) || !is_array($pack['tables'])) {
                continue;
            }
            foreach ($pack['tables'] as $tableName => $colMap) {
                $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $tableName);
                if ($tableName === '' || !preg_match('/^mod_[a-z][a-z0-9_]{0,47}(_list)?$/', $tableName)) {
                    continue;
                }
                if (!is_array($colMap)) { continue; }
                $parts = array();
                $hasId = false;
                foreach ($colMap as $col => $def) {
                    $col = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
                    $def = trim((string) $def);
                    if ($col === '' || $def === '' || !preg_match('/^[A-Za-z0-9_(),\s]+$/', $def)) {
                        continue;
                    }
                    if (strcasecmp($col, 'id') === 0) { $hasId = true; }
                    $parts[] = '`' . $col . '` ' . $def;
                }
                if (!$hasId) {
                    array_unshift($parts, '`id` INT NOT NULL AUTO_INCREMENT');
                }
                if (empty($parts)) { continue; }
                $sql = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` ('
                    . implode(', ', $parts) . ', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
                try {
                    $pdo->exec($sql);
                    // Add any newly declared columns that an older pack missed.
                    $existing = array();
                    foreach ($pdo->query('SHOW COLUMNS FROM `' . $tableName . '`')->fetchAll(PDO::FETCH_ASSOC) as $c) {
                        $existing[strtolower((string) $c['Field'])] = true;
                    }
                    foreach ($colMap as $col => $def) {
                        $col = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
                        $def = trim((string) $def);
                        if ($col === '' || isset($existing[strtolower($col)])) { continue; }
                        if (!preg_match('/^[A-Za-z0-9_(),\s]+$/', $def)) { continue; }
                        $pdo->exec('ALTER TABLE `' . $tableName . '` ADD COLUMN `' . $col . '` ' . $def);
                    }
                    $dynEnsured[] = $tableName;
                } catch (Throwable $e) {
                    $out['dynamic_schema_errors'][$tableName] = $e->getMessage();
                }
            }
        }
    }
    $out['dynamic_tables_ensured'] = $dynEnsured;
} catch (Throwable $e) {
    $out['dynamic_schema_error'] = $e->getMessage();
}

$acked = array();
$verifiedPages = array();

/**
 * Coerce CMS bridge row values to match tenant column types (schema drift safe).
 *
 * CRITICAL: action=update must NOT pad missing NOT NULL varchar/text columns with ''.
 * Menu reorder / partial updates only send parent/order; padding wiped title/link on save.
 *
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function wpx_sync_bridge_sanitize_row(PDO $pdo, string $table, array $row, string $action = 'upsert'): array {
    static $colMeta = array();
    if (!isset($colMeta[$table])) {
        $colMeta[$table] = array();
        try {
            $st = $pdo->query('SHOW COLUMNS FROM `' . $table . '`');
            if ($st) {
                foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $col) {
                    $name = (string) ($col['Field'] ?? '');
                    if ($name !== '') {
                        $colMeta[$table][$name] = array(
                            'type'    => strtolower((string) ($col['Type'] ?? '')),
                            'null'    => strtoupper((string) ($col['Null'] ?? '')) === 'YES',
                            'default' => $col['Default'] ?? null,
                            'extra'   => strtolower((string) ($col['Extra'] ?? '')),
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            $colMeta[$table] = array();
        }
    }
    $meta = $colMeta[$table];
    if ($meta === array()) {
        return $row;
    }

    $isUpdate = (strtolower($action) === 'update');

    foreach ($meta as $col => $info) {
        $type = (string) $info['type'];
        $has  = array_key_exists($col, $row);
        $val  = $has ? $row[$col] : null;

        // Column-name heuristic covers reserved names like `order` even if Type metadata is odd.
        $looksInt = (bool) (
            preg_match('/^(tiny|small|medium|big)?int(\(\d+\))?( unsigned)?$/', $type)
            || preg_match('/\bint\b/', $type)
            // `status` is deliberately absent: modules commonly use VARCHAR/
            // ENUM states such as published, draft and active. Coercing those
            // labels to integer zero silently unpublishes live content.
            || in_array($col, array('order', 'sort', 'sort_order', 'position', 'parent', 'show_m', 'id'), true)
        );
        if ($looksInt) {
            if ($has) {
                if ($val === '' || $val === null || !is_numeric($val)) {
                    // Empty string must become 0 (or NULL) — MySQL strict rejects '' for INT.
                    $row[$col] = ($info['null'] && ($val === null || $val === '')) ? null : 0;
                } else {
                    $row[$col] = (int) $val;
                }
            } elseif (!$isUpdate && !$info['null'] && $info['default'] === null && strpos($info['extra'], 'auto_increment') === false) {
                $row[$col] = 0;
            }
            continue;
        }
        if (preg_match('/^(decimal|float|double)/', $type)) {
            if ($has && ($val === '' || $val === null || !is_numeric($val))) {
                $row[$col] = ($info['null'] && ($val === null || $val === '')) ? null : 0;
            }
            continue;
        }
        // Insert/upsert only: pad required string cols. Partial updates must not invent ''.
        if (!$isUpdate && !$has && !$info['null'] && $info['default'] === null && strpos($info['extra'], 'auto_increment') === false) {
            if (strpos($type, 'int') !== false) {
                $row[$col] = 0;
            } elseif (strpos($type, 'text') !== false || strpos($type, 'char') !== false) {
                $row[$col] = '';
            }
        }

        // Truncate oversized VARCHAR/CHAR values (e.g. mod_settings.setting_value) so one
        // fat onboarding JSON blob cannot abort the whole apply batch / leave sites at 500.
        if ($has && is_string($row[$col] ?? null) && preg_match('/^(var)?char\((\d+)\)$/i', $type, $m)) {
            $max = (int) $m[2];
            if ($max > 0 && strlen((string) $row[$col]) > $max) {
                $row[$col] = function_exists('mb_substr')
                    ? mb_substr((string) $row[$col], 0, $max, 'UTF-8')
                    : substr((string) $row[$col], 0, $max);
            }
        }
    }

    return $row;
}

/**
 * Prove that a page assignment resolves to an active local PHP template.
 */
function wpx_sync_bridge_validate_page_template(PDO $pdo, int $templateId): array {
    if ($templateId <= 0) {
        throw new RuntimeException('page_template_invalid_id:' . $templateId);
    }
    $st = $pdo->prepare('SELECT * FROM `templates` WHERE `id` = ? LIMIT 1');
    $st->execute(array($templateId));
    $tpl = $st->fetch(PDO::FETCH_ASSOC);
    if (!$tpl || (isset($tpl['archived']) && (int) $tpl['archived'] > 0)) {
        throw new RuntimeException('page_template_missing_or_archived:id=' . $templateId);
    }
    $file = trim(str_replace('\\', '/', (string) ($tpl['file'] ?? '')));
    if ($file === '' || basename($file) !== $file || strpos($file, '..') !== false
        || !preg_match('/\.php$/i', $file)) {
        throw new RuntimeException('page_template_invalid_file:id=' . $templateId);
    }
    $absolute = __DIR__ . '/templates/' . $file;
    if (!is_file($absolute)) {
        throw new RuntimeException('page_template_file_missing:id=' . $templateId . ':file=' . $file);
    }
    return array('id' => $templateId, 'file' => $file);
}


foreach ($writes as $write) {
    if (!is_array($write) || empty($write['table']) || empty($write['action'])) {
        continue;
    }
    $table  = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $write['table']);
    $action = strtolower((string) $write['action']);
    $pk     = (string) ($write['pk'] ?? 'id');
    if ($table === 'mod_seo') {
        $pk = 'uniqe_id';
        if ($action === 'update') {
            $action = 'upsert';
        }
    }
    $rows   = isset($write['rows']) && is_array($write['rows']) ? $write['rows'] : array();
    if ($table === '' || empty($rows)) {
        continue;
    }
    $languageTx = false;
    try {
        // A default-language switch is queued as one multi-row write.  Keep
        // unset-previous + set-selected atomic on the tenant database.
        if ($table === 'mod_languages' && !$pdo->inTransaction()) {
            $languageTx = $pdo->beginTransaction();
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $originalKeys = array_fill_keys(array_keys($row), true);
            $row = wpx_sync_bridge_sanitize_row($pdo, $table, $row, $action);
            if ($table === 'mod_pages_list' && $action !== 'delete' && array_key_exists('template', $row)) {
                wpx_sync_bridge_validate_page_template($pdo, (int) $row['template']);
            }
            if ($action === 'update') {
                // Belt-and-suspenders: never SET columns inventively padded by sanitize.
                foreach (array_keys($row) as $_sk) {
                    if ($_sk === $pk) { continue; }
                    if (!isset($originalKeys[$_sk])) { unset($row[$_sk]); }
                }
            }
            if ($action === 'delete') {
                if (!isset($row[$pk])) {
                    continue;
                }
                $st = $pdo->prepare('DELETE FROM `' . $table . '` WHERE `' . $pk . '` = ?');
                $st->execute(array($row[$pk]));
            } elseif ($action === 'update') {
                if (!isset($row[$pk])) {
                    continue;
                }
                $pkVal = $row[$pk];
                $sets = array();
                $vals = array();
                foreach ($row as $col => $val) {
                    if ($col === $pk) {
                        continue;
                    }
                    $colSafe = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
                    if ($colSafe === '') {
                        continue;
                    }
                    static $tableCols = null;
                    if ($tableCols === null) {
                        $tableCols = array();
                    }
                    if (!isset($tableCols[$table])) {
                        try {
                            $colSt = $pdo->query('SHOW COLUMNS FROM `' . $table . '`');
                            $tableCols[$table] = $colSt ? array_column($colSt->fetchAll(PDO::FETCH_ASSOC), 'Field') : array();
                        } catch (Throwable $e) {
                            $tableCols[$table] = array();
                        }
                    }
                    if (empty($tableCols[$table]) || !in_array($colSafe, $tableCols[$table], true)) {
                        continue;
                    }
                    $sets[] = '`' . $colSafe . '` = ?';
                    $vals[] = $val;
                }
                if (empty($sets)) {
                    continue;
                }
                $vals[] = $pkVal;
                $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE `' . $pk . '` = ?';
                $st = $pdo->prepare($sql);
                $st->execute($vals);
            } else {
                $cols = array();
                $vals = array();
                static $insertTableCols = null;
                if ($insertTableCols === null) {
                    $insertTableCols = array();
                }
                if (!isset($insertTableCols[$table])) {
                    try {
                        $colSt = $pdo->query('SHOW COLUMNS FROM `' . $table . '`');
                        $insertTableCols[$table] = $colSt ? array_column($colSt->fetchAll(PDO::FETCH_ASSOC), 'Field') : array();
                    } catch (Throwable $e) {
                        $insertTableCols[$table] = array();
                    }
                }
                foreach ($row as $col => $val) {
                    $colSafe = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
                    if ($colSafe === '') {
                        continue;
                    }
                    if (empty($insertTableCols[$table]) || !in_array($colSafe, $insertTableCols[$table], true)) {
                        continue;
                    }
                    $cols[] = '`' . $colSafe . '`';
                    $vals[] = $val;
                }
                if (empty($cols)) {
                    continue;
                }
                $placeholders = implode(',', array_fill(0, count($cols), '?'));
                $updates = array();
                foreach ($cols as $c) {
                    $updates[] = $c . ' = VALUES(' . $c . ')';
                }
                $sql = 'INSERT INTO `' . $table . '` (' . implode(',', $cols) . ') VALUES (' . $placeholders . ')'
                     . ' ON DUPLICATE KEY UPDATE ' . implode(',', $updates);
                $st = $pdo->prepare($sql);
                $st->execute($vals);
            }
        }
        if ($table === 'mod_pages_list') {
            foreach ($rows as $expectedRow) {
                if (!is_array($expectedRow) || !isset($expectedRow['id']) || !array_key_exists('template', $expectedRow)) {
                    continue;
                }
                $verify = $pdo->prepare('SELECT `template` FROM `mod_pages_list` WHERE `id` = ? LIMIT 1');
                $verify->execute(array((int) $expectedRow['id']));
                $actualTemplate = $verify->fetchColumn();
                $expectedTemplate = (int) $expectedRow['template'];
                if ($actualTemplate === false || (int) $actualTemplate !== $expectedTemplate) {
                    throw new RuntimeException(
                        'page_template_readback_mismatch:id=' . (int) $expectedRow['id']
                        . ':expected=' . $expectedTemplate
                        . ':actual=' . ($actualTemplate === false ? 'missing' : (int) $actualTemplate)
                    );
                }
                $verifiedTemplate = wpx_sync_bridge_validate_page_template($pdo, $expectedTemplate);
                $verifiedPages[] = array(
                    'id' => (int) $expectedRow['id'],
                    'template' => $expectedTemplate,
                    'file' => (string) $verifiedTemplate['file'],
                );
                if (function_exists('apcu_delete')) {
                    foreach (array(
                        'wpx_page_' . $licenseId . '_' . (int) $expectedRow['id'],
                        'wpx_page_' . $clientId . '_' . (int) $expectedRow['id'],
                        'wpx_template_' . $licenseId . '_' . $expectedTemplate,
                        'wpx_template_' . $clientId . '_' . $expectedTemplate,
                    ) as $cacheKey) {
                        @apcu_delete($cacheKey);
                    }
                }
            }
        }
        if ($table === 'mod_languages') {
            $changesDefault = false;
            $expectedDefaultId = 0;
            foreach ($rows as $expectedRow) {
                if (!is_array($expectedRow) || !array_key_exists('default', $expectedRow)) { continue; }
                $changesDefault = true;
                if (!empty($expectedRow['default'])) { $expectedDefaultId = (int) ($expectedRow['id'] ?? 0); }
            }
            // A normal language edit may carry `default=0` for only that row.
            // It is not a global default switch and must not require that the
            // tenant suddenly have zero defaults (nor block later queue rows).
            if ($changesDefault && $expectedDefaultId > 0) {
                $verify = $pdo->query('SELECT `id` FROM `mod_languages` WHERE `default` = 1 ORDER BY `id` ASC');
                $defaultIds = $verify ? array_map('intval', $verify->fetchAll(PDO::FETCH_COLUMN)) : array();
                if (count($defaultIds) !== 1 || (int) $defaultIds[0] !== $expectedDefaultId) {
                    throw new RuntimeException(
                        'language_default_readback_mismatch:expected=' . $expectedDefaultId
                        . ':actual=' . implode(',', $defaultIds)
                    );
                }
            }
            if (function_exists('apcu_clear_cache')) {
                @apcu_clear_cache();
            }
        }
        if ($languageTx && $pdo->inTransaction()) {
            $pdo->commit();
        }
        $out['applied']++;
        if (!empty($write['id'])) {
            $acked[] = (string) $write['id'];
        }
    } catch (Throwable $e) {
        if ($languageTx && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errMsg = $e->getMessage();
        $out['errors'][] = array('table' => $table, 'action' => $action, 'error' => $errMsg);
        // Drop stuck mod_settings oversize writes so SERP rows behind them can flow.
        if ($table === 'mod_settings' && stripos($errMsg, 'Data too long') !== false && !empty($write['id'])) {
            $acked[] = (string) $write['id'];
            $out['skipped'][] = array('id' => (string) $write['id'], 'table' => $table, 'reason' => 'oversize_setting_value');
        }
    }
}

if (!empty($acked) || !empty($fileAck)) {
    $ackBody = json_encode(array(
        'client_id'  => $clientId,
        'license_id' => $licenseId,
        'action'     => 'ack',
        'ids'        => $acked,
        'files'      => $fileAck,
    ), JSON_UNESCAPED_UNICODE);
    $ackTransport = wpx_sync_bridge_cms_request($ackBody, 30);
    $ackRaw = $ackTransport['raw'];
    $ack = json_decode((string) $ackRaw, true);
    $out['acked'] = (int) ($ack['removed'] ?? 0);
    $out['files_acked'] = (int) ($ack['files_removed'] ?? 0);
    $out['ack_http'] = $ackTransport['http'];
    $out['ack_resolved_fallback'] = $ackTransport['resolved_fallback'];
}

$out['verified_pages'] = $verifiedPages;
$out['ok'] = ($out['applied'] > 0 || $filesWritten > 0) && empty($out['errors']);

if (($out['applied'] > 0 || $filesWritten > 0) && is_file(__DIR__ . '/_sync_bridge_push.php')) {
    $pushUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . dirname($_SERVER['SCRIPT_NAME'] ?? '') . '/_sync_bridge_push.php?token=' . PUSH_TOKEN;
    $ch = curl_init($pushUrl);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 120,
    ));
    curl_exec($ch);
    curl_close($ch);
    $out['pushed'] = true;
}

if ($out['applied'] > 0 || $filesWritten > 0) {
    try {
        $pdoRelink = isset($pdo) && $pdo instanceof PDO
            ? $pdo
            : new PDO('mysql:host=localhost;dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $dbPass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
        $out['domain_relink'] = wpx_sync_bridge_relink_or_protect_shai($clientId, $licenseId, $pdoRelink);
    } catch (Throwable $e) {
        $out['domain_relink'] = wpx_sync_bridge_relink_or_protect_shai($clientId, $licenseId, null);
    }
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
