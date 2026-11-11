<?php
/**
 * Public contact-form receiver for generated self-serve sites.
 * Same-origin POST only, bounded input and honeypot/rate controls.
 *
 * After insert, pushes contact_leads directly to CMS ingest (outbound).
 * Never HTTP-calls this tenant's own PHP-FPM (that deadlocks single workers).
 */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit; }
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$originHost = strtolower((string) parse_url((string) ($_SERVER['HTTP_ORIGIN'] ?? ''), PHP_URL_HOST));
if ($originHost !== '' && $host !== '' && !hash_equals($host, $originHost)) { http_response_code(403); exit; }

// Honeypot: duplicate name=website fields may arrive as an array.
$honeypot = $_POST['website'] ?? '';
if (is_array($honeypot)) {
    foreach ($honeypot as $hpVal) {
        if (trim((string) $hpVal) !== '') { http_response_code(204); exit; }
    }
} elseif (trim((string) $honeypot) !== '') {
    http_response_code(204);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? $_POST['content'] ?? ''));
$subject = trim((string) ($_POST['topic'] ?? $_POST['subject'] ?? ''));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === ''
    || strlen($name) > 180 || strlen($email) > 254 || strlen($subject) > 300 || strlen($message) > 8000) {
    http_response_code(422); exit('Invalid contact form.');
}
$rateKey = 'wpx_lead_' . hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $host);
$rateFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $rateKey;
$last = is_file($rateFile) ? (int) @file_get_contents($rateFile) : 0;
if ($last > 0 && time() - $last < 5) { http_response_code(429); exit('Please wait.'); }
@file_put_contents($rateFile, (string) time(), LOCK_EX);

require_once __DIR__ . '/sync_bridge_db.php';
$creds = wpx_sync_bridge_load_tenant_db(__DIR__);
$clientId = (string) ($creds['client_id'] ?? '');
$licenseId = (string) ($creds['license_id'] ?? '');
try {
    $pdo = new PDO('mysql:host=localhost;dbname=' . $creds['db_name'] . ';charset=utf8mb4',
        $creds['db_user'], $creds['db_pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $available = array();
    foreach ($pdo->query('SHOW COLUMNS FROM `contact_leads`')->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $available[(string) $column['Field']] = true;
    }
    $candidate = array(
        'name' => $name, 'email' => $email, 'phone' => trim((string) ($_POST['phone'] ?? '')),
        'url' => (string) ($_SERVER['HTTP_REFERER'] ?? ''), 'date' => date('d/m/Y H:i'),
        'subject' => $subject, 'content' => $message, 'status' => 1,
    );
    $row = array_intersect_key($candidate, $available);
    if (empty($row)) { throw new RuntimeException('contact_leads schema unavailable'); }
    $cols = array_keys($row);
    $sql = 'INSERT INTO `contact_leads` (`' . implode('`,`', $cols) . '`) VALUES ('
        . implode(',', array_fill(0, count($cols), '?')) . ')';
    $pdo->prepare($sql)->execute(array_values($row));

    // Outbound snapshot of contact_leads → CMS (no self-HTTP / FPM deadlock).
    if ($clientId !== '' && function_exists('curl_init')) {
        $leadRows = $pdo->query('SELECT * FROM `contact_leads` ORDER BY `id` DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        $schema = array();
        foreach ($pdo->query('SHOW COLUMNS FROM `contact_leads`')->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $schema[] = array('name' => $c['Field'], 'type' => $c['Type']);
        }
        $payload = array(
            'client_id' => $clientId,
            'license_id' => $licenseId,
            'tables' => array('contact_leads' => $leadRows),
            'schemas' => array('contact_leads' => $schema),
            'files' => array(),
        );
        $ch = curl_init('https://www.cms.whoopix.ai/sync-bridge/ingest.php');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Bridge-Token: YOUR_BRIDGE_TOKEN',
            ),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_RESOLVE => array('www.cms.whoopix.ai:443:195.189.140.104'),
        ));
        @curl_exec($ch);
        @curl_close($ch);
    }
} catch (Throwable $e) {
    http_response_code(503); exit('Contact form unavailable.');
}

$return = (string) ($_SERVER['HTTP_REFERER'] ?? './');
$return = preg_replace('/[?#].*$/', '', $return) . '?lead=sent#contact';
header('Location: ' . $return, true, 303);
exit;
