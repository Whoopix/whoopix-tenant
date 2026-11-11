<?php
/**
 * Whoopix CMS — generic imported-site contact form handler.
 *
 * Deployed to tenant includes/contact_handler.php during website import.
 * Submit target for scraped contact forms (name/email/phone/subject/message).
 *
 *   1) Stores every submission in `contact_leads` (admin Leads module:
 *      admin.php?act=go&mod=leads&op=index_contact). PII encrypted at rest.
 *   2) Sends notification to contact_config.email / email2 / email3.
 *   3) Nudges SyncBridge to push `contact_leads` so CMS Leads sees the row
 *      immediately (bridge tenants cannot read the live tenant DB).
 *
 * Responds with JSON for AJAX; otherwise redirects back to the referring page.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) { @ob_end_clean(); }
}

function wpx_ch_is_ajax(): bool
{
    if (!empty($_POST['ajax'])) {
        return true;
    }
    $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if (strtolower((string) $xhr) === 'xmlhttprequest') {
        return true;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return stripos((string) $accept, 'application/json') !== false;
}

function wpx_ch_site_base(): string
{
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $base = str_replace('\\', '/', dirname(dirname($script)));
    return ($base === '.' || $base === '/') ? '' : rtrim($base, '/');
}

function wpx_ch_reply(int $status, string $msg, string $code = ''): void
{
    if (wpx_ch_is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => $status, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $flag = $status === 1 ? 'sent=1' : ('cerr=' . ($code !== '' ? $code : 'fail'));
    $ret = isset($_POST['ret']) ? trim((string) $_POST['ret']) : '';
    if ($ret !== '' && preg_match('/^,[A-Za-z0-9,_\-]+$/', $ret)) {
        $url = wpx_ch_site_base() . '/' . $ret . (strpos($ret, '?') === false ? '?' : '&') . $flag;
        header('Location: ' . $url);
        exit;
    }
    $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
    if ($ref !== '') {
        $url = $ref . (strpos($ref, '?') === false ? '?' : '&') . $flag;
        header('Location: ' . $url);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo $status === 1
        ? '<p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>'
        : '<p style="color:#c00;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

function wpx_ch_leads_key(): string
{
    static $key = null;
    if ($key !== null) {
        return $key;
    }
    $secret = '';
    if (defined('SYNC_CLIENT_SECRET')) {
        $secret = (string) SYNC_CLIENT_SECRET;
    } else {
        $cfgFile = __DIR__ . '/../system/api/config.php';
        if (is_file($cfgFile)) {
            $src = @file_get_contents($cfgFile);
            if ($src !== false && preg_match("/define\\('SYNC_CLIENT_SECRET',\\s*'([^']*)'/", $src, $m)) {
                $secret = $m[1];
            }
        }
    }
    if ($secret === '' || strpos($secret, '{{') !== false) {
        $key = '';
        return $key;
    }
    $key = hash_hmac('sha256', 'whoopix-leads-enc-v1', $secret, true);
    return $key;
}

function wpx_ch_leads_enc(?string $plain): ?string
{
    if ($plain === null || $plain === '') {
        return $plain;
    }
    if (strncmp($plain, 'enc:v1:', 7) === 0) {
        return $plain;
    }
    $key = wpx_ch_leads_key();
    if ($key === '' || !function_exists('openssl_encrypt')) {
        return $plain;
    }
    $iv  = random_bytes(12);
    $tag = '';
    $ct  = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($ct === false) {
        return $plain;
    }
    return 'enc:v1:' . base64_encode($iv . $tag . $ct);
}

function wpx_ch_post_str(string $key): string
{
    return isset($_POST[$key]) ? trim(strip_tags((string) $_POST[$key])) : '';
}

/**
 * Push contact_leads to CMS SyncBridge so admin Leads/Contact lists update.
 * Prefer local _sync_bridge_push.php?only=contact_leads (runs apply-before-push);
 * fall back to a direct ingest of the tenant table snapshot.
 */
function wpx_ch_bridge_push_contact_leads(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (!function_exists('curl_init')) {
        return;
    }

    $token = 'YOUR_BRIDGE_PUSH_TOKEN';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host !== '') {
        $base = wpx_ch_site_base();
        $pushUrl = $scheme . '://' . $host . $base . '/_sync_bridge_push.php?token=' . rawurlencode($token)
            . '&only=contact_leads';
        $ch = curl_init($pushUrl);
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_NOSIGNAL       => true,
            ]);
            $raw = @curl_exec($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            @curl_close($ch);
            $decoded = is_string($raw) ? json_decode($raw, true) : null;
            if ($http >= 200 && $http < 300 && is_array($decoded) && !empty($decoded['ok'])) {
                return;
            }
        }
    }

    // Direct ingest fallback (same bridge secret as _sync_bridge_push.php).
    $clientId = '';
    $licenseId = '';
    $cfgFile = __DIR__ . '/../system/api/config.php';
    if (is_file($cfgFile)) {
        $src = @file_get_contents($cfgFile);
        if (is_string($src) && $src !== '') {
            if (preg_match("/define\\('SYNC_CLIENT_ID',\\s*'([^']*)'/", $src, $m)
                && strpos($m[1], '{{') === false) {
                $clientId = $m[1];
            }
            if (preg_match("/define\\('SYNC_LICENSE_ID',\\s*'([^']*)'/", $src, $m)
                && strpos($m[1], '{{') === false) {
                $licenseId = $m[1];
            } elseif (preg_match("/define\\('SYNC_LICENSE_ID',\\s*(\\d+)/", $src, $m)) {
                $licenseId = $m[1];
            }
        }
    }
    if ($clientId === '') {
        return;
    }

    $rows = [];
    $cols = [];
    try {
        $st = db_query('SELECT * FROM `contact_leads` ORDER BY `id` ASC');
        while ($row = db_fetch_array($st, PDO::FETCH_ASSOC)) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }
        $stc = db_query('SHOW COLUMNS FROM `contact_leads`');
        while ($c = db_fetch_array($stc, PDO::FETCH_ASSOC)) {
            if (is_array($c) && !empty($c['Field'])) {
                $cols[] = ['name' => (string) $c['Field'], 'type' => (string) ($c['Type'] ?? '')];
            }
        }
    } catch (\Throwable $e) {
        error_log('wpx contact_handler: bridge leads read failed: ' . $e->getMessage());
        return;
    }

    $payload = json_encode([
        'client_id'  => $clientId,
        'license_id' => $licenseId,
        'tables'     => ['contact_leads' => $rows],
        'schemas'    => ['contact_leads' => $cols],
    ], JSON_UNESCAPED_UNICODE);
    if ($payload === false) {
        return;
    }

    $ch = curl_init('https://www.cms.whoopix.ai/sync-bridge/ingest.php');
    if ($ch === false) {
        return;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Bridge-Token: YOUR_BRIDGE_TOKEN',
        ],
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_NOSIGNAL       => true,
    ]);
    @curl_exec($ch);
    @curl_close($ch);
}

/** Finish HTTP response then push leads so the visitor is not blocked. */
function wpx_ch_finish_and_bridge_push(int $status, string $msg, array $extra = []): void
{
    if (wpx_ch_is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        $body = array_merge(['status' => $status, 'msg' => $msg], $extra);
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        } else {
            if (function_exists('ob_get_level')) {
                while (ob_get_level() > 0) { @ob_end_flush(); }
            }
            @flush();
        }
        if ($status === 1) {
            try {
                wpx_ch_bridge_push_contact_leads();
            } catch (\Throwable $e) {
                error_log('wpx contact_handler: bridge push failed: ' . $e->getMessage());
            }
        }
        exit;
    }

    if ($status === 1) {
        // Non-AJAX: push before redirect (usually still fast — contact_leads only).
        try {
            wpx_ch_bridge_push_contact_leads();
        } catch (\Throwable $e) {
            error_log('wpx contact_handler: bridge push failed: ' . $e->getMessage());
        }
    }
    wpx_ch_reply($status, $msg);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($_POST)) {
    wpx_ch_reply(0, 'Invalid request.', 'method');
}

if (!empty($_POST['company']) || !empty($_POST['website']) || !empty($_POST['url_field']) || !empty($_POST['dzHoney'])) {
    wpx_ch_reply(1, 'Thank you. Your message has been sent.');
}

$name = wpx_ch_post_str('name');
if ($name === '') {
    $fname = wpx_ch_post_str('fname');
    $lname = wpx_ch_post_str('lname');
    if ($fname !== '' || $lname !== '') {
        $name = trim($fname . ' ' . $lname);
    }
}
if ($name === '') {
    $name = wpx_ch_post_str('dzName');
}
if ($name === '') {
    $dzFirst = wpx_ch_post_str('dzFirstName');
    $dzLast  = wpx_ch_post_str('dzLastName');
    if ($dzFirst !== '' || $dzLast !== '') {
        $name = trim($dzFirst . ' ' . $dzLast);
    }
}

$email   = wpx_ch_post_str('email');
if ($email === '') {
    $email = wpx_ch_post_str('dzEmail');
}
$phone   = wpx_ch_post_str('phone');
if ($phone === '') {
    $phone = wpx_ch_post_str('dzPhoneNumber');
}
$subject = wpx_ch_post_str('subject');
$message = wpx_ch_post_str('text');
if ($message === '') {
    $message = wpx_ch_post_str('message');
}
if ($message === '') {
    $message = wpx_ch_post_str('content');
}
if ($message === '') {
    $message = wpx_ch_post_str('note');
}
if ($message === '') {
    $message = wpx_ch_post_str('dzMessage');
}
if ($subject === '') {
    $subject = 'Contact form submission';
}

if ($name === '' || mb_strlen($name) < 2) {
    wpx_ch_reply(0, 'Please enter your name.', 'name');
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    wpx_ch_reply(0, 'Please enter a valid email address.', 'email');
}
if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{6,24}$/', $phone)) {
    wpx_ch_reply(0, 'Phone number is not valid.', 'phone');
}
if ($message === '' || mb_strlen($message) < 2) {
    wpx_ch_reply(0, 'Please enter a message.', 'message');
}

$referer = isset($_SERVER['HTTP_REFERER']) ? substr((string) $_SERVER['HTTP_REFERER'], 0, 255) : '';
$now     = date('Y-m-d H:i');

try {
    db_query(
        "CREATE TABLE IF NOT EXISTS `contact_leads` (
            `id`      INT(11) NOT NULL AUTO_INCREMENT,
            `name`    VARCHAR(255) DEFAULT NULL,
            `phone`   VARCHAR(255) DEFAULT NULL,
            `email`   VARCHAR(255) DEFAULT NULL,
            `subject` VARCHAR(255) DEFAULT NULL,
            `content` TEXT,
            `url`     VARCHAR(255) DEFAULT NULL,
            `date`    VARCHAR(32)  DEFAULT NULL,
            `status`  INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (\Throwable $e) {
    error_log('wpx contact_handler: ensure contact_leads failed: ' . $e->getMessage());
}

$stored = false;
try {
    $st = db_query(
        "INSERT INTO `contact_leads` (`name`,`phone`,`email`,`subject`,`content`,`url`,`date`,`status`)
         VALUES (:name,:phone,:email,:subject,:content,:url,:date,0)",
        [
            'name'    => wpx_ch_leads_enc(mb_substr($name, 0, 255)),
            'phone'   => wpx_ch_leads_enc(mb_substr($phone, 0, 255)),
            'email'   => wpx_ch_leads_enc(mb_substr($email, 0, 255)),
            'subject' => wpx_ch_leads_enc(mb_substr($subject, 0, 255)),
            'content' => wpx_ch_leads_enc($message),
            'url'     => wpx_ch_leads_enc($referer),
            'date'    => $now,
        ]
    );
    $stored = ($st !== false);
} catch (\Throwable $e) {
    error_log('wpx contact_handler: lead insert failed: ' . $e->getMessage());
}

if (!$stored) {
    wpx_ch_reply(0, 'Could not save your message. Please try again.', 'store');
}

$recipients = [];
try {
    $cfg = db_fetch_array(db_query("SELECT * FROM `contact_config` WHERE `id` = 1"), PDO::FETCH_ASSOC);
    if (is_array($cfg)) {
        foreach (['email', 'email2', 'email3'] as $f) {
            $val = isset($cfg[$f]) ? trim((string) $cfg[$f]) : '';
            if ($val === '') { continue; }
            foreach (preg_split('/[;,]/', $val) as $one) {
                $one = trim((string) $one);
                if ($one !== '' && filter_var($one, FILTER_VALIDATE_EMAIL)) {
                    $recipients[strtolower($one)] = $one;
                }
            }
        }
    }
} catch (\Throwable $e) {
    error_log('wpx contact_handler: recipient lookup failed: ' . $e->getMessage());
}

if (empty($recipients) && !empty($GLOBALS['GET_SITE_CONFIG_r']['email'])) {
    $fallback = trim((string) $GLOBALS['GET_SITE_CONFIG_r']['email']);
    if ($fallback !== '' && filter_var($fallback, FILTER_VALIDATE_EMAIL)) {
        $recipients[strtolower($fallback)] = $fallback;
    }
}

$mailStatus = array(
    'attempted' => false,
    'ok' => null,
    'recipients' => array_values($recipients),
);

if (!empty($recipients)) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/^www\./', '', (string) $host);
    $mailSubject = 'New contact form — ' . $name;
    $body = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">'
        . '<p>New contact form submission:</p>'
        . '<table cellpadding="6" style="border-collapse:collapse;">'
        . '<tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($name) . '</td></tr>'
        . '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($email) . '</td></tr>'
        . '<tr><td><strong>Phone:</strong></td><td>' . htmlspecialchars($phone) . '</td></tr>'
        . '<tr><td><strong>Subject:</strong></td><td>' . htmlspecialchars($subject) . '</td></tr>'
        . '<tr><td style="vertical-align:top;"><strong>Message:</strong></td><td>' . nl2br(htmlspecialchars($message)) . '</td></tr>'
        . '</table>'
        . '<p style="color:#888;font-size:12px;">Sent from ' . htmlspecialchars($host) . ' at ' . htmlspecialchars($now) . '</p>'
        . '</div>';
    $to = implode(', ', array_values($recipients));
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: noreply@{$host}\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $encodedSubject = '=?UTF-8?B?' . base64_encode($mailSubject) . '?=';
    $mailStatus['attempted'] = true;
    try {
        $mailOk = @mail($to, $encodedSubject, $body, $headers);
        $mailStatus['ok'] = (bool) $mailOk;
        if (!$mailOk) {
            error_log('wpx contact_handler: mail() returned false for ' . $to);
        }
    } catch (\Throwable $e) {
        $mailStatus['ok'] = false;
        error_log('wpx contact_handler: mail send failed: ' . $e->getMessage());
    }
} else {
    error_log('wpx contact_handler: no recipients in contact_config / site config');
}

wpx_ch_finish_and_bridge_push(1, 'Thank you. Your message has been sent successfully.', [
    'mail' => $mailStatus,
]);
