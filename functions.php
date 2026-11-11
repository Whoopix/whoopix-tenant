<?php

declare(strict_types=1);

mb_internal_encoding('UTF-8');

/* ================= CORE ================= */

function pass(string $pass): string
{
    return md5(sha1(md5($pass)));
}

function clearHTML(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function strip(?string $text, bool $html = true): string
{
    $text = (string) $text;
    return $html ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : $text;
}

function gocode(int $length): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $out = '';

    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $out;
}

/* ================= DB FIX ================= */

function __fetch_row(string $sql, array $params = []): array
{
    foreach ($params as $key => $val) {

        if (is_int($val) || is_float($val)) {
            $sql = str_replace(':' . $key, (string)$val, $sql);
        } else {
            $val = db_escape((string)$val);
            $sql = str_replace(':' . $key, "'" . $val . "'", $sql);
        }
    }

    $res = db_query($sql);

    if (!$res) {
        return [];
    }

    $row = db_fetch_array($res);
    return $row ?: [];
}

function __fetch_all(string $sql, array $params = []): array
{
    foreach ($params as $key => $val) {

        if (is_int($val) || is_float($val)) {
            $sql = str_replace(':' . $key, (string)$val, $sql);
        } else {
            $val = db_escape((string)$val);
            $sql = str_replace(':' . $key, "'" . $val . "'", $sql);
        }
    }

    $res = db_query($sql);

    if (!$res) {
        return [];
    }

    $rows = [];
    while ($row = db_fetch_array($res)) {
        $rows[] = $row;
    }

    return $rows;
}

/* ================= AUTH ================= */

function loggedIn(): array|false
{
    if (!isset($_SESSION['user'])) {
        return false;
    }

    [$id, $pass] = explode('_', $_SESSION['user']);

    $user = __fetch_row(
        "SELECT * FROM mod_members WHERE id = :id AND pass = :p AND stat > 0",
        ['id' => (int)$id, 'p' => $pass]
    );

    return $user === [] ? false : $user;
}

function do_no_access(string $access = ''): bool
{
    global $USER_LOGIN;

    if ($access === '') {
        return true;
    }

    $allowed = array_filter(array_map('intval', explode(',', $access)));

    if (!$allowed) {
        return true;
    }

    if (!$USER_LOGIN || !is_array($USER_LOGIN)) {
        return false;
    }

    return in_array((int)$USER_LOGIN['group'], $allowed, true);
}

/* ================= URL ================= */

function doLink(string $mod = '', string $id = '', string $act = ''): string
{
    $r = __fetch_row(
        "SELECT plink FROM mod_seo WHERE mod = :m AND act = :a AND id = :id",
        ['m' => $mod, 'a' => $act, 'id' => $id],
    );

    if ($r !== []) {
        return (string)$r['plink'];
    }

    return ',' . $mod . ($act ? ',' . $act : '') . ($id ? '_' . $id : '');
}

/* ================= CART ================= */

function cart_stat(): array
{
    $rows = __fetch_all(
        "SELECT quantity, price, newprice, onsale 
         FROM mod_cart c
         JOIN mod_products_list p ON p.id = c.prod_id
         WHERE c.hash = :h",
        ['h' => (string)($_SESSION['cart'] ?? '')],
    );

    $amount = 0;
    $quantity = 0;

    foreach ($rows as $row) {
        $price = $row['onsale'] ? $row['newprice'] : $row['price'];
        $amount += $price * $row['quantity'];
        $quantity += $row['quantity'];
    }

    return [
        'amount' => (float)$amount,
        'quantity' => (int)$quantity
    ];
}

/* ================= FILES ================= */

function get_ext_name(string $file): string
{
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));
}

function fileIcon(string $name): string
{
    return match (get_ext_name($name)) {
        'pdf' => 'pdf',
        'zip' => 'zip',
        'doc', 'docx' => 'doc',
        'xls', 'xlsx' => 'xls',
        'ppt', 'pptx' => 'ppt',
        'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'image',
        default => 'file'
    };
}

/* ================= BLOCKS ================= */

function get_block(int|string $id): array
{
    if (!defined('LANG')) {
        return [];
    }

    return __fetch_row(
        "SELECT * FROM mod_blocks WHERE block_id = :id AND lang_id = :lang",
        ['id' => $id, 'lang' => LANG]
    );
}

/* ================= PATH ================= */

function PATH(int|string $id): array
{
    if (!defined('LANG')) {
        return [];
    }

    $row = __fetch_row(
        "SELECT * FROM mod_menu WHERE id = :id AND lang_id = :lang",
        ['id' => $id, 'lang' => LANG]
    );

    if (!$row) {
        return [];
    }

    if ((int)$row['parent'] !== 0) {
        return [...PATH($row['parent']), $row];
    }

    return [$row];
}

/* ================= SITE URL (multi-tenant /user/<name>/ paths) ================= */

if (!function_exists('tenant_site_base_url')) {
    /**
     * Absolute base URL for the current tenant site, including subdirectory installs
     * such as https://hoops.site/user/site/
     */
    function tenant_site_base_url(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $host = preg_replace('/^www\./i', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            $host = 'localhost';
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $https ? 'https' : 'http';

        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir    = dirname($script);
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            $path = '/';
        } else {
            $path = rtrim($dir, '/') . '/';
        }

        $cached = $scheme . '://' . $host . $path;
        return $cached;
    }
}

if (!function_exists('tenant_resolve_location')) {
    /**
     * Make a redirect Location safe for subdirectory tenants (hoops.site/user/X/).
     * Root-relative paths like /אודות become /user/X/אודות when not on domain root.
     */
    function tenant_resolve_location(string $after): string
    {
        $after = trim($after);
        if ($after === '') {
            return $after;
        }
        if (preg_match('~^(https?:)?//~i', $after) || preg_match('~^[a-z][a-z0-9+.-]*:~i', $after)) {
            return $after;
        }
        if (!function_exists('tenant_site_base_url')) {
            return $after;
        }
        $basePath = (string) (parse_url(tenant_site_base_url(), PHP_URL_PATH) ?: '/');
        if ($basePath === '/' || $basePath === '') {
            return $after;
        }
        $basePath = rtrim($basePath, '/');
        if (isset($after[0]) && $after[0] === '/') {
            return $basePath . $after;
        }
        return $basePath . '/' . ltrim($after, '/');
    }
}

if (!function_exists('tenant_public_href')) {
    /**
     * Normalize an HTML href for subdirectory tenants.
     * Leading-slash Whoopix routes (/,pages,2, /אודות) resolve at the domain root on
     * hoops.site — strip the slash so they stay under /user/<slug>/. Custom domains
     * at "/" still resolve correctly. Also repairs mangled "pages.2" → ",pages,2".
     */
    function tenant_public_href(string $link): string
    {
        $link = trim($link);
        if ($link === '' || $link === '#') {
            return $link;
        }
        if (preg_match('~^(mailto:|tel:|javascript:|data:)~i', $link)) {
            return $link;
        }
        if (preg_match('~^(https?:)?//~i', $link)) {
            return $link;
        }
        if (preg_match('~^\.?/?pages\.(\d+)$~i', $link, $m)) {
            return ',pages,' . (int) $m[1];
        }
        // Never rewrite static asset / system paths.
        if (isset($link[0]) && $link[0] === '/' && preg_match(
            '~^/(?:files|resources|system|admin|templates|includes|modules)(?:/|$)~i',
            $link
        )) {
            return $link;
        }
        if ($link === '/') {
            return './';
        }
        if (isset($link[0]) && $link[0] === '/') {
            return ltrim($link, '/');
        }
        return $link;
    }
}

/* ================= MAIL (shared hosting — same pattern as contact_smtp) ================= */

if (!function_exists('tenant_mail_log')) {
    /** Best-effort audit trail when outbound mail fails. */
    function tenant_mail_log(string $log, string $user = 'mail'): void
    {
        $log = mb_substr($log, 0, 500);
        error_log('tenant_mail: ' . $log);
        try {
            if (!function_exists('db_query')) {
                return;
            }
            db_query(
                'INSERT INTO `config_logs` (`ip`, `time`, `user`, `log`) VALUES (:ip, :t, :u, :log)',
                [
                    'ip'  => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                    't'   => (string) time(),
                    'u'   => $user,
                    'log' => $log,
                ]
            );
        } catch (\Throwable $e) {
            error_log('tenant_mail_log insert failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('tenant_mail_from_email')) {
    /** Envelope/from address on the site's own domain (required by most shared hosts). */
    function tenant_mail_from_email(): string
    {
        $host = preg_replace('/^www\./i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        return 'noreply@' . $host;
    }
}

if (!function_exists('send_mail')) {

    /**
     * HTML mail via PHP mail() with UTF-8 subject + domain-aligned From.
     * Mirrors the proven contact_smtp.php headers used on live tenants.
     */
    function send_mail($to, $from, $subject, $message)
    {
        $to = trim((string) $to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            tenant_mail_log('send_mail: invalid recipient address');
            return false;
        }

        $subject = (string) $subject;
        $message = (string) $message;

        $fromName  = '';
        $fromEmail = tenant_mail_from_email();
        $from      = trim((string) $from);
        if (preg_match('/^(.+?)\s*<([^>]+)>$/s', $from, $m)) {
            $fromName  = trim($m[1], " \t\"'");
            $candidate = trim($m[2]);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                $host = preg_replace('/^www\./i', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
                $cDom = substr($candidate, (int) strrpos($candidate, '@') + 1);
                if ($host !== '' && (strcasecmp($cDom, $host) === 0 || str_ends_with($host, $cDom))) {
                    $fromEmail = $candidate;
                }
            }
        } elseif ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = $from;
        }

        if ($fromName === '' && defined('IN_SYSTEM')) {
            try {
                if (function_exists('db_fetch_array') && function_exists('db_query') && defined('LANG')) {
                    $cfg = db_fetch_array(
                        db_query('SELECT `name` FROM `config_site` WHERE `lang_id` = :lid', ['lid' => LANG])
                    );
                    if (!empty($cfg['name'])) {
                        $fromName = (string) $cfg['name'];
                    }
                }
            } catch (\Throwable $e) {
                // non-fatal
            }
        }

        $encodedSubject = '=?' . 'UTF-8' . '?B?' . base64_encode($subject) . '?=';
        $fromHeader     = $fromName !== ''
            ? '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>'
            : $fromEmail;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . $fromHeader . "\r\n";
        $headers .= 'Reply-To: ' . $fromEmail . "\r\n";

        $prevSendmailFrom = ini_get('sendmail_from');
        @ini_set('sendmail_from', $fromEmail);

        $extra = '-f' . $fromEmail;
        $ok    = @mail($to, $encodedSubject, $message, $headers, $extra);

        if ($prevSendmailFrom !== false && $prevSendmailFrom !== '') {
            @ini_set('sendmail_from', (string) $prevSendmailFrom);
        }

        if (!$ok) {
            tenant_mail_log('send_mail failed (mail() returned false) to ' . $to);
        }

        return (bool) $ok;
    }

}

/* ================= OPTIONAL AFFILIATE / POINTS HELPERS ================= */

if (is_file(__DIR__ . '/site_roles.php')) {
    require_once __DIR__ . '/site_roles.php';

    // Capture an affiliate code (?aff=CODE) into the session/cookie on any page
    // view so it can be attributed when the visitor later submits an inquiry.
    if (function_exists('site_capture_affiliate')) {
        site_capture_affiliate();
    }
}

?>