<?php
/**
 * Tenant site bootstrap — copy to config.php and adjust.
 * Connects local front PHP to MySQL; CMS sync goes through system/api.
 */

ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (!defined('IN_SYSTEM')) {
    define('IN_SYSTEM', true);
}

// Whoopix license id for this tenant (from CMS / provisioning).
if (!defined('PROVISION_LICENSE_ID')) {
    define('PROVISION_LICENSE_ID', getenv('WHOOPIX_LICENSE_ID') ?: '0');
}

// Prefer PDO helpers when present; fall back to mysql_* shims in older stacks.
if (is_file(__DIR__ . '/includes/db_helpers.php')) {
    require_once __DIR__ . '/includes/db_helpers.php';
}

if (!empty($dbnfo['host']) && !empty($dbnfo['user']) && !empty($dbnfo['name'])) {
    if (function_exists('mysqli_connect')) {
        $GLOBALS['wpx_mysqli'] = @mysqli_connect(
            $dbnfo['host'],
            $dbnfo['user'],
            $dbnfo['pass'],
            $dbnfo['name']
        );
        if ($GLOBALS['wpx_mysqli']) {
            @mysqli_set_charset($GLOBALS['wpx_mysqli'], 'utf8mb4');
        }
    }
}

if (is_file(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}
