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

$clientId  = $headers['client'];
$requestId = $body['request_id'] ?? '';
$operation = $body['operation']  ?? null;

if ($requestId === '') {
    agent_error('Missing request_id', 400);
}

if (!is_array($operation)) {
    agent_error('Missing operation', 400);
}

$action     = $operation['action']      ?? '';
$tableName  = $operation['table']       ?? '';
$primaryKey = $operation['primary_key'] ?? 'id';
$rows       = $operation['rows']        ?? null;

$allowedActions = ['upsert', 'delete', 'update', 'delete_where'];
if (!in_array($action, $allowedActions, true)) {
    agent_error("Unsupported action: {$action}", 400);
}

if ($tableName === '') {
    agent_error('Missing table name', 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    agent_error('Invalid table name', 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $primaryKey)) {
    agent_error('Invalid primary_key', 400);
}

if (!is_array($rows) || count($rows) === 0) {
    agent_error('Missing or empty rows', 400);
}

if (count($rows) > 1000) {
    agent_error('Too many rows (max 1000)', 400);
}

$db = agent_db();

// 3. Deduplicate
agent_check_request_id($db, $requestId);

// ─── Execute ──────────────────────────────────────────────────────────────────

$rowsAffected = 0;
$lastInsertId = 0;

try {
    $db->beginTransaction();

    foreach ($rows as $rowData) {
        if (!is_array($rowData)) {
            continue;
        }

        $safe = [];
        foreach ($rowData as $col => $val) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $col)) {
                agent_error("Invalid column name: {$col}", 400);
            }
            $safe[$col] = $val;
        }

        if ($action === 'upsert') {
            if (count($safe) === 0) {
                continue;
            }
            $rowsAffected += agent_exec_upsert($db, $tableName, $primaryKey, $safe);
            $insertedId = (int) $db->lastInsertId();
            if ($insertedId > 0) {
                $lastInsertId = $insertedId;
            }

        } elseif ($action === 'delete') {
            $pkValue = $safe[$primaryKey] ?? null;
            if ($pkValue === null) {
                continue;
            }
            $rowsAffected += agent_exec_delete($db, $tableName, $primaryKey, $pkValue);

        } elseif ($action === 'update') {
            $pkValue = $safe[$primaryKey] ?? null;
            if ($pkValue === null) {
                continue;
            }
            $updateData = $safe;
            unset($updateData[$primaryKey]);
            if (count($updateData) === 0) {
                continue;
            }
            $rowsAffected += agent_exec_update($db, $tableName, $primaryKey, $pkValue, $updateData);

        } elseif ($action === 'delete_where') {
            // Composite/multi-column delete: each row is a WHERE map whose
            // (already column-name validated) key=>value pairs are ANDed.
            // Enables managing junction tables with a composite PK and no `id`
            // column (e.g. mod_biz_sop(biz_id,sop_id)) — delete-by-where then
            // re-insert is how association sets are replaced.
            if (count($safe) === 0) {
                continue; // refuse an unconditional (full-table) delete
            }
            $rowsAffected += agent_exec_delete_where($db, $tableName, $safe);
        }
    }

    agent_record_request($db, $requestId, $clientId, 'operation');
    agent_log($db, $requestId, $clientId, 'operation', $action, $tableName, $rowsAffected, 'success');

    $db->commit();

} catch (Throwable $e) {
    $db->rollBack();
    $logError = $e->getMessage();
    agent_record_request($db, $requestId, $clientId, 'operation', 'failed', $logError);
    agent_log($db, $requestId, $clientId, 'operation', $action, $tableName, 0, 'failed', $logError);
    agent_error('Operation failed: ' . $logError, 500);
}

agent_json([
    'success'        => true,
    'request_id'     => $requestId,
    'rows_affected'  => $rowsAffected,
    'last_insert_id' => $lastInsertId,
]);

// ─── DB execution helpers ─────────────────────────────────────────────────────

function agent_exec_upsert(PDO $db, string $table, string $pk, array $data): int
{
    $cols         = array_keys($data);
    $colList      = implode(', ', array_map(function ($c) { return "`{$c}`"; }, $cols));
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $updates      = implode(', ', array_map(function ($c) { return "`{$c}` = VALUES(`{$c}`)"; }, $cols));

    $sql  = "INSERT INTO `{$table}` ({$colList}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updates}";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));

    return min(1, $stmt->rowCount());
}

function agent_exec_delete(PDO $db, string $table, string $pk, $pkValue): int
{
    $stmt = $db->prepare("DELETE FROM `{$table}` WHERE `{$pk}` = ?");
    $stmt->execute([$pkValue]);
    return $stmt->rowCount();
}

function agent_exec_update(PDO $db, string $table, string $pk, $pkValue, array $data): int
{
    $setClauses = implode(', ', array_map(function ($c) { return "`{$c}` = ?"; }, array_keys($data)));
    $values     = array_values($data);
    $values[]   = $pkValue;

    $stmt = $db->prepare("UPDATE `{$table}` SET {$setClauses} WHERE `{$pk}` = ?");
    $stmt->execute($values);
    return $stmt->rowCount();
}

function agent_exec_delete_where(PDO $db, string $table, array $where): int
{
    // $where keys are already validated as [a-zA-Z0-9_]; values bound as params.
    $clauses = array_map(function ($c) { return "`{$c}` = ?"; }, array_keys($where));
    $sql     = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $clauses);
    $stmt    = $db->prepare($sql);
    $stmt->execute(array_values($where));
    return $stmt->rowCount();
}
