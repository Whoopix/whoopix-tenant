<?php
/**
 * Tenant DB credentials — copy to db.php (do not commit db.php).
 */

$dbnfo = array(
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'user' => getenv('DB_USER') ?: 'whoopix_tenant',
    'pass' => getenv('DB_PASS') ?: 'change-me',
    'name' => getenv('DB_NAME') ?: 'whoopix_tenant',
);

// Legacy keyed form used by older includes
$dbnfo['host'] = $dbnfo['host'];
$dbnfo['user'] = $dbnfo['user'];
$dbnfo['pass'] = $dbnfo['pass'];
$dbnfo['name'] = $dbnfo['name'];
