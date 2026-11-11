<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ─── DB ──────────────────────────────────────────────────────────────────────

function agent_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=' . AGENT_DB_HOST . ';dbname=' . AGENT_DB_NAME . ';charset=utf8mb4',
        AGENT_DB_USER,
        AGENT_DB_PASS,
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        ]
    );

    agent_ensure_tables($pdo);

    return $pdo;
}

// ─── HTTP helpers ─────────────────────────────────────────────────────────────

function agent_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function agent_error(string $message, int $status = 400, array $extra = []): void
{
    agent_json(array_merge(['success' => false, 'error' => $message], $extra), $status);
}

// ─── Request reading ─────────────────────────────────────────────────────────

function agent_require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        agent_error('Method Not Allowed', 405);
    }
}

function agent_read_headers(): array
{
    return [
        'client'    => $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '',
        'timestamp' => $_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? '',
        'signature' => $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '',
    ];
}

// ─── HMAC verification ───────────────────────────────────────────────────────

function agent_verify_signature(string $rawBody, array $headers): void
{
    $clientId  = $headers['client'];
    $timestamp = (int) $headers['timestamp'];
    $signature = $headers['signature'];

    if ($clientId === '' || $timestamp === 0 || $signature === '') {
        agent_error('Missing sync headers', 401);
    }

    if ($clientId !== SYNC_CLIENT_ID) {
        agent_error('Unknown client', 401);
    }

    $secret = SYNC_CLIENT_SECRET;
    if ($secret === '') {
        agent_error('Agent not configured (missing secret)', 500);
    }

    $age = abs(time() - $timestamp);
    if ($age > SYNC_MAX_AGE) {
        agent_error('Request timestamp too old (replay protection)', 401);
    }

    $expected = hash_hmac('sha256', $rawBody . '.' . $timestamp, $secret);
    if (!hash_equals($expected, $signature)) {
        agent_error('Invalid signature', 401);
    }
}

// ─── Body parsing ─────────────────────────────────────────────────────────────

function agent_parse_body(string $rawBody): array
{
    if ($rawBody === '') {
        agent_error('Empty request body', 400);
    }

    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        agent_error('Invalid JSON body', 400);
    }

    return $data;
}

function agent_validate_envelope(array $body, string $clientId): void
{
    if (($body['protocol'] ?? '') !== SYNC_PROTOCOL) {
        agent_error('Unsupported protocol', 400);
    }

    if (($body['client_id'] ?? '') !== $clientId) {
        agent_error('client_id mismatch', 400);
    }
}

// ─── Table auto-provisioning ─────────────────────────────────────────────────

function agent_ensure_tables(PDO $db): void
{
    try {
        $db->exec('
            CREATE TABLE IF NOT EXISTS `cms_requests` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `request_id`  VARCHAR(128) NOT NULL,
                `client_id`   VARCHAR(128) NOT NULL DEFAULT \'\',
                `type`        VARCHAR(64)  NOT NULL DEFAULT \'\',
                `received_at` DATETIME     NOT NULL,
                `status`      VARCHAR(32)  NOT NULL DEFAULT \'success\',
                `error`       TEXT         NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_request_id` (`request_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
        $db->exec('
            CREATE TABLE IF NOT EXISTS `cms_logs` (
                `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `request_id`    VARCHAR(128) NOT NULL,
                `client_id`     VARCHAR(128) NOT NULL DEFAULT \'\',
                `type`          VARCHAR(64)  NOT NULL DEFAULT \'\',
                `action`        VARCHAR(64)  NULL,
                `resource_name` VARCHAR(255) NULL,
                `rows_affected` INT          NOT NULL DEFAULT 0,
                `status`        VARCHAR(32)  NOT NULL DEFAULT \'success\',
                `error`         TEXT         NULL,
                `created_at`    DATETIME     NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_request_id` (`request_id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    } catch (Throwable $e) {
        // Cannot create tables — log to error_log but don't crash
        error_log('cms_api: agent_ensure_tables failed: ' . $e->getMessage());
    }
}

// ─── Request ID deduplication ────────────────────────────────────────────────

function agent_check_request_id(PDO $db, string $requestId): void
{
    try {
        $stmt = $db->prepare('SELECT status FROM cms_requests WHERE request_id = ? LIMIT 1');
        $stmt->execute([$requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false && $row['status'] === 'success') {
            // Only block retries of successful requests — failed ones may retry
            agent_error('Duplicate request_id', 409, ['request_id' => $requestId]);
        }
    } catch (Throwable $e) {
        // Table missing — skip dedup check, don't crash
        error_log('cms_api: agent_check_request_id failed: ' . $e->getMessage());
    }
}

function agent_record_request(
    PDO    $db,
    string $requestId,
    string $clientId,
    string $type,
    string $status = 'success',
    string $error  = ''
): void {
    try {
        $stmt = $db->prepare(
            'INSERT IGNORE INTO cms_requests
             (request_id, client_id, type, received_at, status, error)
             VALUES (?, ?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$requestId, $clientId, $type, $status, $error ?: null]);
    } catch (Throwable $e) {
        // Logging table missing — don't let it kill the response
    }
}

// ─── Audit logging ───────────────────────────────────────────────────────────

function agent_log(
    PDO    $db,
    string $requestId,
    string $clientId,
    string $type,
    string $action,
    string $resourceName,
    int    $rowsAffected,
    string $status,
    string $error = ''
): void {
    try {
        $stmt = $db->prepare(
            'INSERT INTO cms_logs
             (request_id, client_id, type, action, resource_name, rows_affected, status, error, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $requestId,
            $clientId,
            $type,
            $action ?: null,
            $resourceName ?: null,
            $rowsAffected,
            $status,
            $error ?: null,
        ]);
    } catch (Throwable $e) {
        // Logging table missing — don't let it kill the response
    }
}

