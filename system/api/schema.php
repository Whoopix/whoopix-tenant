<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

agent_require_post();

$rawBody = file_get_contents('php://input');
$headers = agent_read_headers();

// 1. Verify HMAC
agent_verify_signature($rawBody, $headers);

// 2. Parse and validate envelope
$body = agent_parse_body($rawBody);
agent_validate_envelope($body, $headers['client']);

$tableName = $body['table'] ?? '';
$action    = $body['action'] ?? 'describe';

if ($tableName === '') {
    agent_error('Missing table name', 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    agent_error('Invalid table name', 400);
}

$db = agent_db();

if ($action === 'create_table') {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `id`      INT(11)      NOT NULL AUTO_INCREMENT,
            `lang_id` INT(11)      NOT NULL DEFAULT 1,
            `page_id` INT(11)      NOT NULL DEFAULT 0,
            `title`   VARCHAR(255) NOT NULL DEFAULT '',
            `body`    TEXT,
            `status`  TINYINT(1)  NOT NULL DEFAULT 1,
            `order`   INT(11)      NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        agent_json(['success' => true, 'table' => $tableName, 'created' => true]);
    } catch (Throwable $e) {
        agent_error('Failed to create table: ' . $e->getMessage(), 500);
    }
}


if ($action === 'ensure_id_pk') {
    try {
        $stmt     = $db->query("DESCRIBE `{$tableName}`");
        $existing = array_column($stmt->fetchAll(), 'Field');
    } catch (Throwable $e) {
        agent_error('Table not found: ' . $tableName, 404);
    }
    if (!in_array('id', $existing)) {
        try {
            $db->exec("ALTER TABLE `{$tableName}` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        } catch (Throwable $e) {
            agent_error('Failed to add id column: ' . $e->getMessage(), 500);
        }
    }
    agent_json(['success' => true, 'table' => $tableName]);
}

if ($action === 'ensure_columns') {
    $newCols = $body['columns'] ?? [];
    $added   = [];
    try {
        $stmt     = $db->query("DESCRIBE `{$tableName}`");
        $existing = array_column($stmt->fetchAll(), 'Field');
    } catch (Throwable $e) {
        agent_error('Table not found: ' . $tableName, 404);
    }
    foreach ($newCols as $col) {
        $colName = $col['name'] ?? '';
        $colType = $col['type'] ?? 'TEXT';
        if (!$colName || !preg_match('/^[a-zA-Z0-9_]+$/', $colName)) continue;
        if (in_array($colName, $existing)) continue;
        $hasDefault = array_key_exists('default', $col);
        $colDefault = $col['default'] ?? null;
        if ($hasDefault && $colDefault !== null) {
            $q   = is_numeric($colDefault) ? $colDefault : $db->quote($colDefault);
            $ddl = "`{$colName}` {$colType} NOT NULL DEFAULT {$q}";
        } else {
            $ddl = "`{$colName}` {$colType} DEFAULT NULL";
        }
        try {
            $db->exec("ALTER TABLE `{$tableName}` ADD COLUMN {$ddl}");
            $added[] = $colName;
        } catch (Throwable $e) {
            // column add failed (ignore, best-effort)
        }
    }
    agent_json(['success' => true, 'table' => $tableName, 'added' => $added]);
}

try {
    $stmt = $db->query("DESCRIBE `{$tableName}`");
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    agent_error('Table not found or not accessible: ' . $tableName, 404);
}

$columns = [];
foreach ($rows as $row) {
    $columns[] = [
        'name'     => $row['Field'],
        'type'     => $row['Type'],
        'nullable' => ($row['Null'] === 'YES'),
        'key'      => $row['Key'],
        'default'  => $row['Default'],
        'extra'    => $row['Extra'],
    ];
}

agent_json([
    'success' => true,
    'table'   => $tableName,
    'columns' => $columns,
]);
