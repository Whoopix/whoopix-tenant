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
$readReq   = $body['read']       ?? null;

if ($requestId === '') {
    agent_error('Missing request_id', 400);
}

if (!is_array($readReq)) {
    agent_error('Missing read block', 400);
}

$tableName     = $readReq['table']        ?? '';
$action        = $readReq['action']       ?? 'list';
$requestedCols = $readReq['columns']      ?? ['*'];
$filters       = $readReq['filters']      ?? [];
$filterLogic   = strtoupper($readReq['filter_logic'] ?? 'AND');
$sort          = $readReq['sort']         ?? null;
$limit         = (int) ($readReq['limit'] ?? 100);
$page          = max(1, (int) ($readReq['page'] ?? 1));

// Aggregate params
$aggFn    = strtoupper($readReq['aggregate_fn']    ?? 'COUNT');
$aggField = $readReq['aggregate_field'] ?? '*';
$groupBy  = $readReq['group_by']        ?? null;

// Join params
$joins      = $readReq['joins']      ?? [];
$selectExpr = $readReq['select_expr'] ?? '';

$allowedActions = ['list', 'get', 'count', 'aggregate', 'join'];

if ($tableName === '') {
    agent_error('Missing table name', 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
    agent_error('Invalid table name', 400);
}

if (!in_array($action, $allowedActions, true)) {
    agent_error("Unsupported read action: {$action}", 400);
}

if (!in_array($filterLogic, ['AND', 'OR'], true)) {
    $filterLogic = 'AND';
}

// 3. Build SELECT column list
$isSelectAll = $requestedCols === ['*']
    || $requestedCols === '*'
    || (is_array($requestedCols) && in_array('*', $requestedCols, true));

if ($isSelectAll || $action === 'join') {
    // join uses select_expr for its column list; skip per-column validation
    $colList = '*';
} elseif (!empty($requestedCols)) {
    foreach ($requestedCols as $col) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $col)) {
            agent_error("Invalid column name: {$col}", 400);
        }
    }
    $colList = implode(', ', array_map(function ($c) { return "`{$c}`"; }, $requestedCols));
} else {
    $colList = '*';
}

// 4. Build WHERE clause
$allowedOperators = ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL', 'BETWEEN'];
$whereClauses     = [];
$bindings         = [];

foreach ($filters as $f) {
    if (!is_array($f)) {
        continue;
    }
    $field    = $f['field']    ?? '';
    $operator = strtoupper(trim($f['operator'] ?? '='));
    $value    = $f['value']    ?? null;

    if (!preg_match('/^[a-zA-Z0-9_.]+$/', $field)) {
        agent_error("Invalid filter field name: {$field}", 400);
    }

    // Allow table.column notation but wrap correctly
    $fieldSql = strpos($field, '.') !== false ? $field : "`{$field}`";

    switch ($operator) {
        case '=':
        case '!=':
        case '>':
        case '>=':
        case '<':
        case '<=':
            $whereClauses[] = "{$fieldSql} {$operator} ?";
            $bindings[] = $value;
            break;
        case 'LIKE':
            $whereClauses[] = "{$fieldSql} LIKE ?";
            $bindings[] = '%' . $value . '%';
            break;
        case 'IN':
            if (!is_array($value) || empty($value)) {
                agent_error("IN operator requires non-empty array for field: {$field}", 400);
            }
            $ph = implode(', ', array_fill(0, count($value), '?'));
            $whereClauses[] = "{$fieldSql} IN ({$ph})";
            foreach ($value as $v) { $bindings[] = $v; }
            break;
        case 'NOT IN':
            if (!is_array($value) || empty($value)) {
                agent_error("NOT IN operator requires non-empty array for field: {$field}", 400);
            }
            $ph = implode(', ', array_fill(0, count($value), '?'));
            $whereClauses[] = "{$fieldSql} NOT IN ({$ph})";
            foreach ($value as $v) { $bindings[] = $v; }
            break;
        case 'BETWEEN':
            if (!is_array($value) || count($value) < 2) {
                agent_error("BETWEEN operator requires [min, max] array for field: {$field}", 400);
            }
            $whereClauses[] = "{$fieldSql} BETWEEN ? AND ?";
            $bindings[] = $value[0];
            $bindings[] = $value[1];
            break;
        case 'IS NULL':
            $whereClauses[] = "{$fieldSql} IS NULL";
            break;
        case 'IS NOT NULL':
            $whereClauses[] = "{$fieldSql} IS NOT NULL";
            break;
        default:
            agent_error("Filter operator not allowed: {$operator}", 403);
    }
}

$glue        = " {$filterLogic} ";
$whereClause = !empty($whereClauses) ? ' WHERE ' . implode($glue, $whereClauses) : '';

// 5. Build ORDER BY
$orderClause = '';
if ($sort !== null && is_array($sort)) {
    $sortField = $sort['field']     ?? '';
    $sortDir   = strtoupper($sort['direction'] ?? 'ASC');

    if ($sortField !== '') {
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $sortField)) {
            agent_error("Invalid sort field name: {$sortField}", 400);
        }
        $sortDir     = in_array($sortDir, ['ASC', 'DESC'], true) ? $sortDir : 'ASC';
        $orderClause = " ORDER BY `{$sortField}` {$sortDir}";
    }
}

// 6. Cap limit
$limit = min(max(1, $limit), 2000);

// ─── Execute ──────────────────────────────────────────────────────────────────

$db     = agent_db();
$result = [];
$total  = 0;

try {

    // ── COUNT ─────────────────────────────────────────────────────────────────
    if ($action === 'count') {
        $sql  = "SELECT COUNT(*) AS total FROM `{$tableName}`{$whereClause}";
        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        $total = (int) $stmt->fetchColumn();

        agent_record_request($db, $requestId, $clientId, 'read');
        agent_log($db, $requestId, $clientId, 'read', 'count', $tableName, $total, 'success');

        agent_json([
            'success' => true,
            'action'  => 'count',
            'table'   => $tableName,
            'total'   => $total,
        ]);
    }

    // ── AGGREGATE ─────────────────────────────────────────────────────────────
    if ($action === 'aggregate') {
        $allowedFns = ['COUNT', 'COUNT_DISTINCT', 'MAX', 'MIN', 'SUM', 'AVG'];
        if (!in_array($aggFn, $allowedFns, true)) {
            agent_error("Invalid aggregate function: {$aggFn}", 400);
        }
        if ($aggField !== '*' && !preg_match('/^[a-zA-Z0-9_]+$/', $aggField)) {
            agent_error("Invalid aggregate field: {$aggField}", 400);
        }

        // group_by supports:
        //   "colname"        → GROUP BY `colname`, result key = colname
        //   "unix:colname"   → GROUP BY DATE(FROM_UNIXTIME(`colname`)), result key = "date"
        $groupByExpr  = null;
        $groupByAlias = null;
        if ($groupBy !== null) {
            $gbStr = (string) $groupBy;
            if (strpos($gbStr, 'unix:') === 0) {
                $gbCol = substr($gbStr, 5);
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $gbCol)) {
                    agent_error("Invalid group_by column: {$gbCol}", 400);
                }
                $groupByExpr  = "DATE(FROM_UNIXTIME(`{$gbCol}`))";
                $groupByAlias = 'date';
            } else {
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $gbStr)) {
                    agent_error("Invalid group_by field: {$gbStr}", 400);
                }
                $groupByExpr  = "`{$gbStr}`";
                $groupByAlias = $gbStr;
            }
        }

        $aggExpr = $aggField === '*' ? "{$aggFn}(*)" : (
            $aggFn === 'COUNT_DISTINCT' ? "COUNT(DISTINCT `{$aggField}`)" : "{$aggFn}(`{$aggField}`)"
        );

        if ($groupByExpr !== null) {
            $sql  = "SELECT {$groupByExpr} AS `{$groupByAlias}`, {$aggExpr} AS result FROM `{$tableName}`{$whereClause} GROUP BY {$groupByExpr}";
            $stmt = $db->prepare($sql);
            $stmt->execute($bindings);
            $rows = $stmt->fetchAll();

            agent_record_request($db, $requestId, $clientId, 'read');
            agent_log($db, $requestId, $clientId, 'read', 'aggregate', $tableName, count($rows), 'success');

            agent_json([
                'success'  => true,
                'action'   => 'aggregate',
                'table'    => $tableName,
                'function' => $aggFn,
                'field'    => $aggField,
                'group_by' => $groupByAlias,
                'results'  => $rows,
            ]);
        } else {
            $sql  = "SELECT {$aggExpr} AS result FROM `{$tableName}`{$whereClause}";
            $stmt = $db->prepare($sql);
            $stmt->execute($bindings);
            $row = $stmt->fetch();

            agent_record_request($db, $requestId, $clientId, 'read');
            agent_log($db, $requestId, $clientId, 'read', 'aggregate', $tableName, 1, 'success');

            agent_json([
                'success'  => true,
                'action'   => 'aggregate',
                'table'    => $tableName,
                'function' => $aggFn,
                'field'    => $aggField,
                'result'   => $row['result'] ?? 0,
            ]);
        }
    }

    // ── JOIN ──────────────────────────────────────────────────────────────────
    if ($action === 'join') {
        $joinSql = '';
        foreach ($joins as $j) {
            $joinTable = $j['table'] ?? '';
            $joinOn    = $j['on']    ?? '';
            $joinType  = strtoupper($j['type'] ?? 'LEFT');

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $joinTable)) {
                agent_error("Invalid join table name: {$joinTable}", 400);
            }
            // ON clause: allow table.col = table.col
            if (!preg_match('/^[a-zA-Z0-9_.]+\s*=\s*[a-zA-Z0-9_.]+$/', $joinOn)) {
                agent_error("Invalid join ON clause", 400);
            }
            if (!in_array($joinType, ['INNER', 'LEFT', 'RIGHT'], true)) {
                $joinType = 'LEFT';
            }
            $joinSql .= " {$joinType} JOIN `{$joinTable}` ON {$joinOn}";
        }

        // select_expr: free-form but from trusted CMS code (HMAC-verified)
        $selectSql = $selectExpr ?: ($colList ?: '*');

        if ($action === 'join') {
            $countSql  = "SELECT COUNT(*) FROM `{$tableName}`{$joinSql}{$whereClause}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($bindings);
            $total = (int) $countStmt->fetchColumn();
        }

        $offset = ($page - 1) * $limit;
        $sql    = "SELECT {$selectSql} FROM `{$tableName}`{$joinSql}{$whereClause}{$orderClause} LIMIT ? OFFSET ?";
        $stmt   = $db->prepare($sql);
        foreach ($bindings as $i => $val) {
            $stmt->bindValue($i + 1, $val);
        }
        $stmt->bindValue(count($bindings) + 1, $limit,  PDO::PARAM_INT);
        $stmt->bindValue(count($bindings) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        agent_record_request($db, $requestId, $clientId, 'read');
        agent_log($db, $requestId, $clientId, 'read', 'join', $tableName, count($result), 'success');

        agent_json([
            'success'  => true,
            'action'   => 'join',
            'table'    => $tableName,
            'data'     => $result,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
        ]);
    }

    // ── LIST / GET ────────────────────────────────────────────────────────────
    if ($action === 'get') {
        $limit = 1;
    }

    $offset = ($page - 1) * $limit;

    if ($action === 'list') {
        $countSql  = "SELECT COUNT(*) FROM `{$tableName}`{$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();
    }

    $sql  = "SELECT {$colList} FROM `{$tableName}`{$whereClause}{$orderClause} LIMIT ? OFFSET ?";
    $stmt = $db->prepare($sql);
    foreach ($bindings as $i => $val) {
        $stmt->bindValue($i + 1, $val);
    }
    $stmt->bindValue(count($bindings) + 1, $limit,  PDO::PARAM_INT);
    $stmt->bindValue(count($bindings) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll();

    agent_record_request($db, $requestId, $clientId, 'read');
    agent_log($db, $requestId, $clientId, 'read', $action, $tableName, count($result), 'success');

} catch (Throwable $e) {
    $logError = $e->getMessage();
    $db = agent_db();
    agent_record_request($db, $requestId, $clientId, 'read', 'failed', $logError);
    agent_log($db, $requestId, $clientId, 'read', $action, $tableName, 0, 'failed', $logError);
    agent_error('Read failed: ' . $logError, 500);
}

agent_json([
    'success'    => true,
    'request_id' => $requestId,
    'table'      => $tableName,
    'action'     => $action,
    'data'       => $result,
    'total'      => $action === 'list' ? $total : count($result),
    'page'       => $page,
    'limit'      => $limit,
]);
