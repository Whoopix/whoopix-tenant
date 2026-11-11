<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');

if ($uri === '') {
    http_response_code(404);
    exit;
}

$q = db_query(
    "SELECT `after` FROM `mod_301` WHERE `before` = :before LIMIT 1",
    ['before' => $uri]
);

if (db_num_rows($q) > 0) {
    $r = db_fetch_array($q);

    if (!empty($r['after'])) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $r['after']);
        exit;
    }
}

http_response_code(404);
exit;