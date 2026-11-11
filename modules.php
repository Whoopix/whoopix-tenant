<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!defined('IN_SYSTEM')) {
    define('IN_SYSTEM', true);
}

$modParam = (string) ($_GET['mod'] ?? '');
$indexGetExp = explode(',', $modParam);

if (($indexGetExp[0] ?? '') === 'pages' && isset($indexGetExp[1]) && !is_numeric($indexGetExp[1])) {
    $moduleExp = explode('-', $indexGetExp[1]);
    $_GET['mod'] = 'pages,' . $moduleExp[0];
}

$langCode = strtolower((string) ($_GET['lang'] ?? ''));

$q = db_query("SELECT * FROM mod_languages WHERE code = :c", ['c' => $langCode]);
$r = db_fetch_array($q);

if (!$r) {
    $q = db_query("SELECT * FROM mod_languages WHERE `default` = 1");
    $r = db_fetch_array($q);
}

define('LANG',        (int) ($r['id'] ?? 0));
define('LANGCODE',    (string) ($r['code'] ?? 'he'));
define('LANGDIR',     (string) ($r['direction'] ?? 'rtl'));
define('LANDDEFAULT', (int) ($r['default'] ?? 0) > 0);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = md5(session_id());
}

$lang = [];

$get_langs_q = db_query(
    "SELECT L.text, V.text AS vtext
     FROM mod_languages_label L
     LEFT JOIN mod_languages_values V 
     ON L.id = V.lable_id AND V.lang_id = :lid",
    ['lid' => LANG]
);

while ($row = db_fetch_array($get_langs_q)) {
    $lang[$row['text']] = $row['vtext'];
}

// Unique visitors: one row per IP per day; logged = last hit (realtime window).
static $__stats_schema_ok = false;
if (!$__stats_schema_ok) {
    $__stats_schema_ok = true;
    @db_query("ALTER TABLE config_statistics ADD COLUMN logged DATETIME NULL DEFAULT NULL");
    @db_query("ALTER TABLE config_statistics ADD UNIQUE KEY uniq_ip_time (ip, time)");
}

$__stats_upsert = @db_query(
    "INSERT INTO config_statistics (visits, ip, time, logged) VALUES (1, :ip, CURRENT_DATE(), NOW())
     ON DUPLICATE KEY UPDATE visits = visits + 1, logged = NOW()",
    ['ip' => $usrip]
);
if (!$__stats_upsert) {
    @db_query(
        "UPDATE config_statistics
         SET visits = visits + 1
         WHERE ip = :ip AND time = CURRENT_DATE()",
        ['ip' => $usrip]
    );
    if (!db_affected_rows()) {
        @db_query(
            "INSERT INTO config_statistics (visits, ip, time) VALUES (1, :ip, CURRENT_DATE())",
            ['ip' => $usrip]
        );
        if (!defined('UNIQUE')) {
            define('UNIQUE', true);
        }
    } elseif (!defined('UNIQUE')) {
        define('UNIQUE', false);
    }
} elseif (!defined('UNIQUE')) {
    define('UNIQUE', false);
}

@db_query(
    "UPDATE config_unstatistics
     SET visits = visits + 1
     WHERE time = CURRENT_DATE()"
);

if (!db_affected_rows()) {
    @db_query(
        "INSERT INTO config_unstatistics (time, visits) VALUES (CURRENT_DATE(), 1)"
    );
}

$GET_SITE_LOCK_r = db_fetch_array(db_query("SELECT * FROM config_sitelock")) ?: [];

$UserSess = $_SESSION[$SESSUSER] ?? '';
$PassSess = $_SESSION[$SESSPASS] ?? '';

$GET_SITE_CONFIG_r = db_fetch_array(
    db_query("SELECT * FROM config_site WHERE lang_id = :lid", ['lid' => LANG])
) ?: [];

if ($UserSess && $PassSess) {
    $LOGIN_C_q = db_query(
        "SELECT id FROM admins WHERE username = :u AND password = :p",
        ['u' => $UserSess, 'p' => $PassSess]
    );

    if (db_num_rows($LOGIN_C_q) > 0) {
        $GET_SITE_LOCK_r['status'] = 0;
    }
}

$get_mod  = $get_mod  ?? '';
$get_act  = $get_act  ?? '';
$get_acts = $get_acts ?? '';
$get_id   = $get_id   ?? '';
$get_page = $get_page ?? '';

if (((int) ($GET_SITE_LOCK_r['status'] ?? 0)) === 0) {

    $queryParam = (string) ($_GET['query'] ?? '');

    if ($queryParam !== '') {
        $q  = db_query("SELECT * FROM mod_301 WHERE `before` = :b", ['b' => $queryParam]);
        // Exact plink match first (legacy dental import used "Services" → products; must not shadow "services" → pages).
        $q1 = db_query(
            "SELECT * FROM mod_seo WHERE BINARY plink = :p ORDER BY id DESC LIMIT 1",
            ['p' => $queryParam]
        );
        if (!$q1 || db_num_rows($q1) === 0) {
            $q1 = db_query(
                "SELECT * FROM mod_seo WHERE LOWER(plink) = LOWER(:p) AND `mod` = 'pages' ORDER BY id DESC LIMIT 1",
                ['p' => $queryParam]
            );
        }

        if (db_num_rows($q) > 0) {
            $r = db_fetch_array($q);
            header('HTTP/1.1 301 Moved Permanently');
            $loc = (string) ($r['after'] ?? '');
            if (function_exists('tenant_resolve_location')) {
                $loc = tenant_resolve_location($loc);
            }
            header('Location: ' . $loc);
            exit;
        }

        if (db_num_rows($q1) > 0) {
            $r = db_fetch_array($q1);
            $get_mod  = $r['mod'];
            $get_acts = $r['act'];
            $get_id   = $r['id'];
        } else {
            http_response_code(404);
            exit;
        }
    }

    if ($get_mod === '' && isset($_GET['mod'])) {
        $modRaw = (string) $_GET['mod'];
        $q301 = db_query("SELECT `after` FROM `mod_301` WHERE `before` = :b LIMIT 1", ['b' => $modRaw]);
        if ($q301 && db_num_rows($q301) > 0) {
            $r301 = db_fetch_array($q301);
            if (!empty($r301['after'])) {
                header('HTTP/1.1 301 Moved Permanently');
                $loc = (string) $r301['after'];
                if (function_exists('tenant_resolve_location')) {
                    $loc = tenant_resolve_location($loc);
                }
                header('Location: ' . $loc);
                exit;
            }
        }

        $getmod = explode(',', $modRaw);

        $get_mod  = $getmod[0] ?? '';
        $get_acts = $getmod[1] ?? '';
        $get_page = $getmod[2] ?? '';

        if (is_numeric($get_acts)) {
            $get_id = $get_acts;
            $get_acts = '';
        } else {
            // Prefer full act filename (quote_request, order_quote) over legacy id split (quote + id).
            $fullActFile = __DIR__ . "/modules/{$get_mod}/{$get_acts}.php";
            if ($get_acts !== '' && is_file($fullActFile)) {
                $get_act = $get_acts;
                $get_id  = '';
            } else {
                $parts = explode('_', $get_acts);
                $get_act = $parts[0] ?? '';
                $get_id  = $parts[1] ?? '';
            }
        }
    }

    if ($get_act === '' && $get_acts !== '' && $get_acts !== '0') {
        $get_act = $get_acts;
    }

    // Legacy + Phase 1 act aliases (same module file).
    $actAliases = [
        'quote-request' => 'quote_request',
        'order-quote'   => 'order_quote',
        'export-xls'    => 'exportxls',
    ];
    if (isset($actAliases[$get_act])) {
        $get_act = $actAliases[$get_act];
    }

    $INC_File = $get_act ?: 'index';
    $moduleFile = __DIR__ . "/modules/{$get_mod}/{$INC_File}.php";
    // Builder-created content types ship without a per-slug PHP file. Fall back
    // to the generic dynamic public runtime so ,{slug} and ,{slug},{id} work.
    if (!file_exists($moduleFile) && preg_match('/^[a-z][a-z0-9_]{0,47}$/', (string) $get_mod)) {
        $dynFile = __DIR__ . "/modules/_dynamic/{$INC_File}.php";
        if (file_exists($dynFile)) {
            $moduleFile = $dynFile;
        }
    }

    if (file_exists($moduleFile)) {

        $MOD_CHECK_r = db_fetch_array(
            db_query("SELECT * FROM mod_modules WHERE module = :m", ['m' => $get_mod])
        ) ?: [];

        if ((int) ($MOD_CHECK_r['status'] ?? 0) === 1) {

            $MOD_TEMPCHECK_q = @db_query("SELECT * FROM {$get_mod}_config WHERE id = 1");

            if ($MOD_TEMPCHECK_q && db_num_rows($MOD_TEMPCHECK_q) > 0) {
                $MOD_TEMPCHECK_r = db_fetch_array($MOD_TEMPCHECK_q);
                $TEMP_TEMPLATE = $MOD_TEMPCHECK_r['template'] ?? null;
            }

            include $moduleFile;
        }
    }
}

require_once __DIR__ . '/templates.php';