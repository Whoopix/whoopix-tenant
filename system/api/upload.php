<?php

declare(strict_types=1);

/**
 * Allow CMS admin (cms.whoopix.ai) to POST uploads directly to the tenant sync agent.
 */
function upload_apply_cors(): void
{
    $allowed = ['https://cms.whoopix.ai', 'https://www.cms.whoopix.ai'];
    $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (!in_array($origin, $allowed, true)) {
        return;
    }
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Sync-Client, X-Sync-Timestamp, X-Sync-Signature, X-Sync-File-Hash');
    header('Access-Control-Max-Age: 86400');
}

upload_apply_cors();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// ─── Route by method ─────────────────────────────────────────────────────────

if ($method === 'POST') {
    $action = $_GET['action'] ?? '';
    if ($action === 'write') {
        handle_write();
    } else {
        handle_upload();
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    if ($action === 'meta') {
        handle_meta();
    } elseif ($action === 'content') {
        handle_content();
    } else {
        handle_list();
    }
} elseif ($method === 'DELETE') {
    handle_delete();
} else {
    agent_error('Method Not Allowed', 405);
}

// ─── Upload (POST multipart/form-data) ───────────────────────────────────────

function handle_upload(): void
{
    $type      = $_POST['type']     ?? $_GET['type'] ?? '';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';
    $fileHash  = $_SERVER['HTTP_X_SYNC_FILE_HASH'] ?? '';

    if ($clientId === '' || $timestamp === 0 || $signature === '' || $fileHash === '') {
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

    // Verify signature over: fileHash + "." + type + "." + timestamp
    $expected = hash_hmac('sha256', $fileHash . '.' . $type . '.' . $timestamp, $secret);
    if (!hash_equals($expected, $signature)) {
        agent_error('Invalid signature', 401);
    }

    [$uploadPath, $allowedExts] = upload_resolve_type($type);

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['file']['error'] ?? -1;
        agent_error('No valid file received (error code: ' . $errCode . ')', 400);
    }

    $file      = $_FILES['file'];
    $tmpPath   = $file['tmp_name'];
    $origName  = $file['name'];
    $sizeBytes = $file['size'];

    // Verify file hash matches what CMS signed
    $actualHash = hash_file('sha256', $tmpPath);
    if (!hash_equals($fileHash, $actualHash)) {
        agent_error('File integrity check failed (hash mismatch)', 400);
    }

    // Validate size (videos allow a higher limit)
    $sizeKb = round($sizeBytes / 1024, 2);
    $maxKb  = UPLOAD_MAX_SIZE_KB;
    if ($type === 'videos') {
        $maxKb = defined('UPLOAD_VIDEOS_MAX_SIZE_KB') ? (int) UPLOAD_VIDEOS_MAX_SIZE_KB : 524288;
    }
    if ($sizeBytes > $maxKb * 1024) {
        agent_error("File too large ({$sizeKb}KB). Max: {$maxKb}KB", 400);
    }

    // Validate extension
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        agent_error("Extension '.{$ext}' not allowed for type '{$type}'", 400);
    }

    $uploadPath = upload_ensure_dir($uploadPath);
    if (!is_dir($uploadPath)) {
        agent_error("Upload directory does not exist: {$uploadPath}", 500);
    }

    if (!is_writable($uploadPath)) {
        agent_error('Upload directory is not writable', 500);
    }

    // Save with UUID filename
    $uuid     = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    $finalName = $uuid . '.' . $ext;
    $destPath  = $uploadPath . DIRECTORY_SEPARATOR . $finalName;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        agent_error('Failed to save file', 500);
    }

    if ($type === 'images') {
        $resizeLib = __DIR__ . '/wpix_image_resize.php';
        if (is_readable($resizeLib)) {
            require_once $resizeLib;
            wpix_image_resize_file($destPath);
            $sizeKb = round(filesize($destPath) / 1024, 2);
        }
    }

    $db = agent_db();
    try {
        $stmt = $db->prepare(
            'INSERT INTO file_uploads (filename, original_name, type, extension, size_kb, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$finalName, $origName, $type, $ext, $sizeKb]);
    } catch (Throwable $e) {
        // Metadata failure is non-fatal — file is saved
        error_log("file_uploads insert failed for {$finalName}: " . $e->getMessage());
    }

    agent_json([
        'success'       => true,
        'filename'      => $finalName,
        'original_name' => $origName,
        'type'          => $type,
        'extension'     => $ext,
        'size_kb'       => $sizeKb,
    ], 201);
}

// ─── List files (GET ?type=images) ──────────────────────────────────────────

function handle_list(): void
{
    $type      = $_GET['type']      ?? '';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';

    upload_verify_simple($clientId, $timestamp, $signature, 'list.' . $type);

    [$uploadPath] = upload_resolve_type($type);

    if (!is_dir($uploadPath)) {
        agent_error('Upload directory does not exist', 500);
    }

    $items   = [];
    $entries = scandir($uploadPath);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $entry;
        if (!is_file($fullPath)) {
            continue;
        }
        $items[] = [
            'filename'    => $entry,
            'size_kb'     => round(filesize($fullPath) / 1024, 2),
            'extension'   => pathinfo($entry, PATHINFO_EXTENSION),
            'modified'    => date('Y-m-d H:i:s', filemtime($fullPath)),
        ];
    }

    // Enrich with original names from DB
    if (!empty($items)) {
        $db        = agent_db();
        $filenames = array_column($items, 'filename');
        $in        = implode(', ', array_fill(0, count($filenames), '?'));
        $stmt      = $db->prepare(
            "SELECT filename, original_name FROM file_uploads WHERE type = ? AND filename IN ({$in})"
        );
        $stmt->execute(array_merge([$type], $filenames));
        $meta = [];
        foreach ($stmt->fetchAll() as $row) {
            $meta[$row['filename']] = $row['original_name'];
        }
        foreach ($items as &$item) {
            $item['original_name'] = $meta[$item['filename']] ?? null;
        }
        unset($item);
    }

    agent_json([
        'success' => true,
        'type'    => $type,
        'total'   => count($items),
        'data'    => $items,
    ]);
}

// ─── Delete (DELETE ?type=images&path=uuid.jpg) ───────────────────────────────

function handle_delete(): void
{
    parse_str(file_get_contents('php://input'), $params);
    $type      = $params['type']      ?? $_GET['type']      ?? '';
    $path      = $params['path']      ?? $_GET['path']      ?? '';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';

    upload_verify_simple($clientId, $timestamp, $signature, 'delete.' . $type . '.' . $path);

    [$uploadPath] = upload_resolve_type($type);

    $path     = upload_sanitize_path($path);
    $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $path;
    $realFile = realpath($fullPath);
    $realBase = realpath($uploadPath);

    if ($realFile === false || $realBase === false || strpos($realFile, $realBase) !== 0) {
        agent_error('File not found', 404);
    }

    if (!is_file($realFile)) {
        agent_error('Not a file', 400);
    }

    if (!unlink($realFile)) {
        agent_error('Failed to delete file', 500);
    }

    // Remove metadata row (non-fatal if missing)
    try {
        $db   = agent_db();
        $stmt = $db->prepare('DELETE FROM file_uploads WHERE filename = ? AND type = ?');
        $stmt->execute([basename($realFile), $type]);
    } catch (Throwable) {
    }

    agent_json([
        'success' => true,
        'deleted' => $path,
        'type'    => $type,
    ]);
}

// ─── Metadata (GET ?action=meta&type=images&filename=uuid.jpg) ────────────────

function handle_meta(): void
{
    $type      = $_GET['type']     ?? '';
    $filename  = $_GET['filename'] ?? '';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';

    upload_verify_simple($clientId, $timestamp, $signature, 'meta.' . $type . '.' . $filename);

    $db   = agent_db();
    $stmt = $db->prepare(
        'SELECT filename, original_name, type, extension, size_kb FROM file_uploads
         WHERE filename = ? AND type = ? LIMIT 1'
    );
    $stmt->execute([$filename, $type]);
    $row = $stmt->fetch();

    if (!$row) {
        agent_error('No metadata found for this file', 404);
    }

    agent_json(['success' => true, 'data' => $row]);
}

// ─── Read file content (GET ?action=content&type=templates&path=page.php) ─────

function handle_content(): void
{
    $type      = $_GET['type']      ?? '';
    $path      = $_GET['path']      ?? '';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';

    upload_verify_simple($clientId, $timestamp, $signature, 'read.' . $type . '.' . $path);

    [$basePath] = upload_resolve_type($type);

    $path     = upload_sanitize_path($path);
    $fullPath = $basePath . DIRECTORY_SEPARATOR . $path;
    $realFile = realpath($fullPath);
    $realBase = realpath($basePath);

    if ($realFile === false || $realBase === false || strpos($realFile, $realBase) !== 0) {
        agent_error('File not found', 404);
    }

    if (!is_file($realFile)) {
        agent_error('Not a file', 400);
    }

    $content = file_get_contents($realFile);
    if ($content === false) {
        agent_error('Failed to read file', 500);
    }

    agent_json([
        'success'  => true,
        'type'     => $type,
        'path'     => $path,
        'content'  => base64_encode($content),
        'encoding' => 'base64',
    ]);
}

// ─── Write file content (POST ?action=write&type=templates&path=page.php) ────

function handle_write(): void
{
    $type      = $_POST['type']    ?? $_GET['type']    ?? '';
    $path      = $_POST['path']    ?? $_GET['path']    ?? '';
    $content   = $_POST['content'] ?? '';
    $encoding  = $_POST['encoding'] ?? 'base64';
    $timestamp = (int) ($_SERVER['HTTP_X_SYNC_TIMESTAMP'] ?? 0);
    $clientId  = $_SERVER['HTTP_X_SYNC_CLIENT']    ?? '';
    $signature = $_SERVER['HTTP_X_SYNC_SIGNATURE'] ?? '';

    upload_verify_simple($clientId, $timestamp, $signature, 'write.' . $type . '.' . $path);

    $resolved     = upload_resolve_type($type);
    [$basePath, $allowedExts] = $resolved;
    $nameWhitelist = $resolved[2] ?? null;

    $path     = upload_sanitize_path($path);
    $fullPath = $basePath . DIRECTORY_SEPARATOR . $path;

    // Some write types (e.g. web-root PHP bootstrap files) are restricted to an
    // explicit filename whitelist so the agent can never overwrite arbitrary
    // root-level PHP. Match on basename only — the path-traversal check below
    // still pins the file to $basePath.
    if ($nameWhitelist !== null && !in_array(basename($path), $nameWhitelist, true)) {
        agent_error("Filename '" . basename($path) . "' not allowed for type '{$type}'", 403);
    }

    // Path traversal check
    $realBase = realpath($basePath);
    $dirPath  = realpath(dirname($fullPath));
    if ($realBase === false || $dirPath === false || strpos($dirPath, $realBase) !== 0) {
        agent_error('Invalid path', 400);
    }

    // Extension check
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        agent_error("Extension '.{$ext}' not allowed for type '{$type}'", 400);
    }

    $decoded = $encoding === 'base64' ? base64_decode($content, true) : $content;
    if ($decoded === false) {
        agent_error('Invalid base64 content', 400);
    }

    if (file_put_contents($fullPath, $decoded, LOCK_EX) === false) {
        agent_error('Failed to write file', 500);
    }

    agent_json([
        'success' => true,
        'type'    => $type,
        'path'    => $path,
        'size'    => strlen($decoded),
    ]);
}

// ─── Shared helpers ──────────────────────────────────────────────────────────

/**
 * Verify HMAC for non-upload requests (no file hash involved).
 * Signs: action_string + "." + timestamp
 */
function upload_verify_simple(string $clientId, int $timestamp, string $signature, string $actionString): void
{
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

    if (abs(time() - $timestamp) > SYNC_MAX_AGE) {
        agent_error('Request timestamp too old (replay protection)', 401);
    }

    $expected = hash_hmac('sha256', $actionString . '.' . $timestamp, $secret);
    if (!hash_equals($expected, $signature)) {
        agent_error('Invalid signature', 401);
    }
}

/**
 * Ensure a public media directory exists (mkdir -p) and return its absolute path.
 */
function upload_ensure_dir(string $path): string
{
    $path = rtrim(str_replace('\\', '/', $path), '/');
    if ($path === '') {
        return $path;
    }
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    $real = realpath($path);
    return ($real !== false) ? $real : $path;
}

/**
 * Resolve upload type → [absolutePath, allowedExtensions[]]
 */
function upload_resolve_type(string $type): array
{
    $webRoot = realpath(__DIR__ . '/../..') ?: (__DIR__ . '/../..');

    // Standard media folders — create on first use so new tenants / dynamic
    // modules with video/image fields do not fail with "directory does not exist".
    $imagesFallback = $webRoot . '/files/upload';
    $videosFallback = $webRoot . '/files/videos';
    $imagesPath = (defined('UPLOAD_IMAGES_PATH') && UPLOAD_IMAGES_PATH)
        ? (string) UPLOAD_IMAGES_PATH
        : $imagesFallback;
    // Guard against stale/wrong config (e.g. system/files/upload from ../files).
    if (!is_dir($imagesPath) && is_dir($imagesFallback)) {
        $imagesPath = $imagesFallback;
    }
    $imagesPath = upload_ensure_dir($imagesPath);

    $videosPath = (defined('UPLOAD_VIDEOS_PATH') && UPLOAD_VIDEOS_PATH)
        ? (string) UPLOAD_VIDEOS_PATH
        : $videosFallback;
    if (!is_dir($videosPath)) {
        $videosPath = $videosFallback;
    }
    $videosPath = upload_ensure_dir($videosPath);

    $importedPath = upload_ensure_dir($webRoot . '/files/upload/imported');
    $includesPath = upload_ensure_dir($webRoot . '/includes');
    $designPath   = upload_ensure_dir($webRoot . '/resources/design');
    $modulesPath  = upload_ensure_dir($webRoot . '/modules');
    $templatesPath = (defined('UPLOAD_TEMPLATES_PATH') && UPLOAD_TEMPLATES_PATH)
        ? (string) UPLOAD_TEMPLATES_PATH
        : upload_ensure_dir($webRoot . '/templates');

    $rootPath = $webRoot;

    $types = [
        'images'    => [$imagesPath,    array_map('trim', explode(',', UPLOAD_IMAGES_EXTENSIONS))],
        'templates' => [$templatesPath, array_map('trim', explode(',', UPLOAD_TEMPLATES_EXTENSIONS))],
        'videos'    => [
            $videosPath,
            defined('UPLOAD_VIDEOS_EXTENSIONS')
                ? array_map('trim', explode(',', UPLOAD_VIDEOS_EXTENSIONS))
                : ['mp4', 'webm', 'mov'],
        ],
        'includes'  => [$includesPath,          ['php']],
        // Copy-site import stores images, icon fonts, webfonts, and theme JS here.
        'imported'  => [$importedPath,         ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf', 'js', 'mjs', 'css']],
        'sitemap'   => [$rootPath,             ['xml']],
        // Theme / DexignZone design assets (CSS overrides for assistant theme colors).
        'design'    => [
            $designPath,
            ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf'],
        ],
        // Tenant PHP modules (modules/lab/*, etc.)
        'modules'   => [
            $modulesPath,
            ['php'],
        ],
        // Web-root PHP bootstrap files. Restricted to a known filename whitelist
        // (3rd tuple element) so the agent can update routing/bootstrap shims
        // like templates.php without being able to write arbitrary root PHP.
        'bootstrap' => [$rootPath,             ['php'], ['templates.php', 'modules.php', 'index.php']],
    ];

    if (!isset($types[$type])) {
        agent_error("Unknown upload type: '{$type}'. Allowed: " . implode(', ', array_keys($types)), 404);
    }

    return $types[$type];
}

/**
 * Strip path traversal attempts.
 */
function upload_sanitize_path(string $path): string
{
    $path = str_replace(['..', "\0"], '', $path);
    return trim($path, '/\\');
}
