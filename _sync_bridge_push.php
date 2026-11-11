<?php
/**
 * Push tenant DB snapshots to CMS sync bridge (tenant → cms outbound).
 * Credentials come ONLY from system/api/config.php — never guessed from username.
 */
declare(strict_types=1);

const TOKEN = 'YOUR_BRIDGE_PUSH_TOKEN';
const BRIDGE_URL = 'https://www.cms.whoopix.ai/sync-bridge/ingest.php';
const BRIDGE_TOKEN = 'YOUR_BRIDGE_TOKEN';

if (php_sapi_name() !== 'cli' && !hash_equals(TOKEN, (string) ($_GET['token'] ?? ''))) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$helper = __DIR__ . '/sync_bridge_db.php';
if (!is_file($helper)) {
    $helper = dirname(__DIR__, 2) . '/sync_bridge_db.php';
}
require_once $helper;

$creds = wpx_sync_bridge_load_tenant_db(__DIR__);
$clientId = $creds['client_id'];
$dbName = $creds['db_name'];
$dbUser = $creds['db_user'];
$dbPass = $creds['db_pass'];
$licenseId = (string) ($creds['license_id'] ?? '');

if ($clientId === '') {
    echo json_encode(array('ok' => false, 'error' => 'missing client_id (set SYNC_CLIENT_ID in system/api/config.php)'));
    exit;
}
if ($creds['error'] !== '') {
    echo json_encode(array('ok' => false, 'error' => $creds['error'], 'client_id' => $clientId));
    exit;
}

$tables = array(
    'templates', 'mod_pages_list', 'mod_pages', 'mod_menu', 'mod_modules', 'mod_conf',
    'mod_languages', 'config_site', 'mod_seo_list', 'mod_seo', 'mod_blocks_list', 'mod_blocks',
    'mod_faqs_list', 'mod_faqs', 'mod_faq_items', 'mod_services', 'contact_leads', 'contact_config',
    'mod_settings', 'mod_blog_posts', 'mod_blog_categories', 'mod_team_members', 'mod_movies',
    'mod_serp_keywords', 'mod_serp_rankings', 'mod_serp_positions', 'mod_serp_config',
    'mod_products_list', 'mod_products',
    'mod_products_categories_list', 'mod_products_categories',
        'mod_biz', 'mod_sop_cats', 'mod_sop', 'mod_biz_sop', 'mod_area', 'mod_feedback_list',
    'mod_eventstypes', 'mod_biz_event', 'mod_members', 'mod_biz_roles', 'mod_biz_leads', 'mod_points_ledger', 'mod_articles_list', 'mod_articles',
    'mod_news_list', 'mod_news', 'mod_brands_list', 'mod_brands',
    // Lab catalog (client.whoopix.ai)
    'mod_lab_clients', 'mod_lab_products', 'mod_lab_client_prices', 'mod_lab_tests',
    'mod_lab_test_statuses', 'mod_lab_quote_requests', 'mod_lab_orders', 'mod_lab_certifications',
    'mod_lab_intake_forms', 'mod_lab_intake_items',
    // Site Content accreditations (migrated from lab scaffold)
    'mod_certifications',
);

// Optional: ?only=contact_leads (or comma-separated) for lightweight post-form nudges.
// Stats tables are not in $tables above — allow them via only= as well.
$statsOnlyAllow = array('config_statistics', 'config_unstatistics');
$onlyRaw = trim((string) ($_GET['only'] ?? ''));
$onlyTables = array();
$onlyStats = array();
if ($onlyRaw !== '') {
    foreach (preg_split('/\s*,\s*/', $onlyRaw) as $t) {
        $t = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $t);
        if ($t === '') {
            continue;
        }
        if (in_array($t, $tables, true)) {
            $onlyTables[] = $t;
        } elseif (in_array($t, $statsOnlyAllow, true)) {
            $onlyStats[] = $t;
        }
    }
    if (!empty($onlyTables)) {
        $tables = array_values(array_unique($onlyTables));
    } elseif (!empty($onlyStats)) {
        // Stats-only nudge: skip the heavy content table loop.
        $tables = array();
    }
}

$out = array('client_id' => $clientId, 'license_id' => $licenseId, 'db' => $dbName, 'tables' => array(), 'ok' => false);
if (!empty($onlyTables) || !empty($onlyStats)) {
    $out['only'] = array_values(array_unique(array_merge($onlyTables, $onlyStats)));
}

// Apply queued CMS writes to tenant DB before snapshot push (SERP scans, etc.).
// Skip on lightweight stats-only nudges (dashboard realtime refresh).
$applyScript = __DIR__ . '/_sync_bridge_apply.php';
if (is_file($applyScript) && empty($onlyStats)) {
    $applyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\') . '/_sync_bridge_apply.php?token=' . TOKEN;
    $chApply = curl_init($applyUrl);
    curl_setopt_array($chApply, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_RESOLVE        => array(
            preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'hoops.site'))
            . ':443:195.189.141.116',
        ),
    ));
    $applyRaw = curl_exec($chApply);
    $applyHttp = (int) curl_getinfo($chApply, CURLINFO_HTTP_CODE);
    $applyError = (string) curl_error($chApply);
    curl_close($chApply);
    $out['apply_before_push'] = json_decode((string) $applyRaw, true);
    if ($applyError !== '' || $applyHttp < 200 || $applyHttp >= 300
        || !is_array($out['apply_before_push']) || empty($out['apply_before_push']['ok'])) {
        echo json_encode(array_merge($out, array(
            'ok' => false,
            'error' => 'apply_before_push_failed',
            'apply_http' => $applyHttp,
            'apply_error' => $applyError,
        )), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $dbPass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
} catch (Throwable $e) {
    echo json_encode(array('ok' => false, 'error' => 'db: ' . $e->getMessage(), 'client_id' => $clientId, 'db' => $dbName));
    exit;
}

$bizdirOnlyTables = array(
    'mod_biz', 'mod_sop_cats', 'mod_sop', 'mod_biz_sop', 'mod_area', 'mod_feedback_list',
    'mod_eventstypes', 'mod_biz_event', 'mod_biz_roles', 'mod_biz_leads', 'mod_points_ledger', 'mod_articles_list', 'mod_articles',
    'mod_news_list', 'mod_news', 'mod_brands_list', 'mod_brands',
);
$skipBizdirTables = false;
try {
    $pdo->query('SELECT 1 FROM `mod_lab_clients` LIMIT 1');
    $skipBizdirTables = true;
    try {
        $stBiz = $pdo->query("SELECT setting_value FROM mod_settings WHERE module = 'site' AND setting_key = 'bizdir_enabled' LIMIT 1");
        $bizVal = $stBiz ? $stBiz->fetchColumn() : false;
        if ($bizVal === '1') {
            $skipBizdirTables = false;
        }
    } catch (Throwable $e) { /* keep skip */ }
} catch (Throwable $e) {
    $skipBizdirTables = false;
}
// Non-site tenants with inherited directory schema (e.g. PGL): skip unless opted in.
if (!$skipBizdirTables) {
    $clientIdLower = strtolower((string) $clientId);
    $isSite = ($clientIdLower === 'site')
        || ($licenseId !== '' && (string) (int) $licenseId === '3453215');
    if (!$isSite) {
        try {
            $stBiz = $pdo->query("SELECT setting_value FROM mod_settings WHERE module = 'site' AND setting_key = 'bizdir_enabled' LIMIT 1");
            $bizVal = $stBiz ? $stBiz->fetchColumn() : false;
            if ($bizVal !== '1') {
                $skipBizdirTables = true;
            }
        } catch (Throwable $e) {
            $skipBizdirTables = true;
        }
    }
}
if ($skipBizdirTables) {
    $tables = array_values(array_filter($tables, static function ($t) use ($bizdirOnlyTables) {
        return !in_array($t, $bizdirOnlyTables, true);
    }));
    $out['skip_bizdir_tables'] = true;
}

$payload = array(
    'client_id'  => $clientId,
    'license_id' => $licenseId,
    'tables'     => array(),
    'schemas'    => array(),
    'files'      => array(),
);

foreach ($tables as $table) {
    try {
        $st = $pdo->query('SELECT * FROM `' . $table . '`');
        $rows = $st ? $st->fetchAll() : array();
        $payload['tables'][$table] = $rows;
        $out['tables'][$table] = count($rows);
        $cols = array();
        foreach ($pdo->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll() as $c) {
            $cols[] = array('name' => $c['Field'], 'type' => $c['Type']);
        }
        $payload['schemas'][$table] = $cols;
    } catch (Throwable $e) {
        $out['tables'][$table] = 'skip: ' . $e->getMessage();
    }
}

// Growth chart traffic — last 90 days only (per-IP rows can grow large).
// Skip on lightweight ?only= nudges unless only= includes stats tables.
$statsFrom = date('Y-m-d', strtotime('-90 days'));
$statsToPush = array();
if (!empty($onlyStats)) {
    $statsToPush = array_values(array_unique($onlyStats));
} elseif (empty($onlyTables) && empty($onlyRaw)) {
    $statsToPush = array('config_statistics', 'config_unstatistics');
}
foreach ($statsToPush as $statsTable) {
    try {
        $sql = 'SELECT * FROM `' . $statsTable . '` WHERE `time` >= ' . $pdo->quote($statsFrom) . ' ORDER BY `time` ASC';
        $st = $pdo->query($sql);
        $rows = $st ? $st->fetchAll() : array();
        $payload['tables'][$statsTable] = $rows;
        $out['tables'][$statsTable] = count($rows);
        $cols = array();
        foreach ($pdo->query('SHOW COLUMNS FROM `' . $statsTable . '`')->fetchAll() as $c) {
            $cols[] = array('name' => $c['Field'], 'type' => $c['Type']);
        }
        $payload['schemas'][$statsTable] = $cols;
    } catch (Throwable $e) {
        $out['tables'][$statsTable] = 'skip: ' . $e->getMessage();
    }
}

// Push template source files so CMS template editor can read them via bridge.
// Skip on lightweight ?only= nudges (content tables or stats-only).
$tplPaths = array();
if ($onlyRaw === '' && !empty($payload['tables']['templates']) && is_array($payload['tables']['templates'])) {
    foreach ($payload['tables']['templates'] as $row) {
        if (!empty($row['file'])) {
            $tplPaths[(string) $row['file']] = true;
            $stem = preg_replace('/\.php$/i', '', (string) $row['file']);
            if ($stem !== '') {
                foreach (glob(__DIR__ . '/templates/' . $stem . '_*.php') ?: array() as $langFile) {
                    $tplPaths[basename($langFile)] = true;
                }
            }
        }
    }
}
$tplDir = __DIR__ . '/templates';
if ($onlyRaw === '' && is_dir($tplDir)) {
    foreach (glob($tplDir . '/*.php') ?: array() as $abs) {
        $tplPaths[basename($abs)] = true;
    }
}
foreach (array_keys($tplPaths) as $rel) {
    $abs = $tplDir . '/' . $rel;
    if (!is_file($abs)) {
        continue;
    }
    $size = filesize($abs);
    if ($size === false || $size <= 0 || $size > 3145728) {
        continue;
    }
    $bytes = file_get_contents($abs);
    if ($bytes === false || $bytes === '') {
        continue;
    }
    $payload['files'][] = array(
        'type'    => 'templates',
        'path'    => $rel,
        'content' => base64_encode($bytes),
    );
}
$out['template_files'] = count($payload['files']);

// Push include fragments (head/header/footer) for CMS template editor preview.
$includeCount = 0;
$incDir = __DIR__ . '/includes';
if ($onlyRaw === '' && is_dir($incDir)) {
    foreach (glob($incDir . '/*.php') ?: array() as $abs) {
        $rel = basename($abs);
        $size = filesize($abs);
        if ($size === false || $size <= 0 || $size > 1048576) {
            continue;
        }
        $bytes = file_get_contents($abs);
        if ($bytes === false || $bytes === '') {
            continue;
        }
        $b64 = base64_encode($bytes);
        $payload['files'][] = array('type' => 'includes', 'path' => $rel, 'content' => $b64);
        $payload['files'][] = array('type' => 'includes', 'path' => 'includes/' . $rel, 'content' => $b64);
        $includeCount++;
    }
}
$out['include_files'] = $includeCount;

$body = json_encode($payload, JSON_UNESCAPED_UNICODE);
$ch = curl_init(BRIDGE_URL);
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_HTTPHEADER     => array(
        'Content-Type: application/json',
        'X-Bridge-Token: ' . BRIDGE_TOKEN,
    ),
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_RESOLVE        => array('www.cms.whoopix.ai:443:195.189.140.104'),
));
$response = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

$out['bridge_http'] = $http;
$out['bridge_error'] = $err;
$out['bridge_response'] = json_decode((string) $response, true);
$out['ok'] = ($http >= 200 && $http < 300 && empty($err));

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
