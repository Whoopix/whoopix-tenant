<?php
/** Shared styles + CSRF for Site role panels (domain / category). */
if (empty($_SESSION['bizpanel_token'])) {
    if (!empty($_SESSION['bizadmin_token'])) {
        $_SESSION['bizpanel_token'] = (string) $_SESSION['bizadmin_token'];
    } else {
        $_SESSION['bizpanel_token'] = bin2hex(random_bytes(16));
    }
}
$BIZPANEL_TOKEN = $_SESSION['bizpanel_token'];

/** panel_action (new) or biz_action (legacy bizadmin route). */
function bizpanel_post_action(): string
{
    return trim((string) ($_POST['panel_action'] ?? $_POST['biz_action'] ?? ''));
}

function bizpanel_post_section(string $fallback = 'overview'): string
{
    $sec = trim((string) ($_POST['section'] ?? $_GET['section'] ?? $fallback));
    return $sec !== '' ? $sec : $fallback;
}

/** @return array<int,array{0:string,1:string}> */
function bizpanel_take_flash(): array
{
    $flash = $_SESSION['bizpanel_flash'] ?? [];
    unset($_SESSION['bizpanel_flash']);
    return is_array($flash) ? $flash : [];
}

/** @param array<int,array{0:string,1:string}> $messages */
function bizpanel_redirect_section(string $section, array $messages = [], string $queryPrefix = ''): never
{
    if ($messages !== []) {
        $_SESSION['bizpanel_flash'] = $messages;
    }
    $qs = $queryPrefix . 'section=' . urlencode($section);
    header('Location: ?' . $qs);
    exit;
}

function bizpanel_db_error_message(): string
{
    $err = function_exists('db_error') ? trim((string) db_error()) : '';
    return $err !== '' ? ('שגיאת מסד נתונים: ' . $err) : 'שגיאה בשמירה.';
}

/** PRG redirect back to the current member panel route (no query string). */
function bizpanel_redirect_self(array $messages = []): never
{
    if ($messages !== []) {
        $_SESSION['bizpanel_flash'] = $messages;
    }
    $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $path = $uri !== '' ? (strtok($uri, '?') ?: $uri) : ',members,business';
    header('Location: ' . $path);
    exit;
}

function bizpanel_biz_code(): string
{
    if (function_exists('gocode')) {
        return gocode(8);
    }
    return bin2hex(random_bytes(4));
}

/**
 * Insert a member-owned business into mod_biz (tries required legacy columns).
 *
 * @param array{name:string,phone:string,address?:string,email?:string,desc?:string,logofile?:string,agent_id:int,cat_id?:int,area?:int} $fields
 */
function bizpanel_insert_biz(array $fields): int
{
    $name    = trim((string) ($fields['name'] ?? ''));
    $phone   = trim((string) ($fields['phone'] ?? ''));
    $agentId = (int) ($fields['agent_id'] ?? 0);
    if ($name === '' || $phone === '' || $agentId <= 0 || !function_exists('db_query')) {
        return 0;
    }

    $email = trim((string) ($fields['email'] ?? ''));
    if ($email === '') {
        $email = 'member-' . $agentId . '@site.local';
    }
    $desc = trim((string) ($fields['desc'] ?? ''));
    if ($desc === '') {
        $desc = '-';
    }

    $params = [
        'name'      => $name,
        'phone'     => $phone,
        'address'   => trim((string) ($fields['address'] ?? '')),
        'email'     => $email,
        'pass'      => '',
        'descr'     => $desc,
        'c_name'    => '',
        'c_phone'   => '',
        'phone2'    => '',
        'fax'       => '',
        'show_mail' => $email,
        'area'      => (int) ($fields['area'] ?? 0),
        'facebook'  => '',
        'mysite'    => '',
        'self_site' => '',
        'logo'      => trim((string) ($fields['logofile'] ?? '')),
        'miscfile'  => '',
        'agent'     => $agentId,
        'cat'       => (int) ($fields['cat_id'] ?? 0),
        'code'      => bizpanel_biz_code(),
        'aff'       => '',
    ];

    $attempts = [
        "INSERT INTO `mod_biz` (`name`,`phone`,`address`,`email`,`pass`,`desc`,`c_name`,`c_phone`,`phone2`,`fax`,`show_mail`,`area`,`facebook`,`mysite`,`self_site`,`logofile`,`miscfile`,`agent_id`,`cat_id`,`active`,`code`,`aff_code`)
         VALUES (:name,:phone,:address,:email,:pass,:descr,:c_name,:c_phone,:phone2,:fax,:show_mail,:area,:facebook,:mysite,:self_site,:logo,:miscfile,:agent,:cat,1,:code,:aff)",
        "INSERT INTO `mod_biz` (`name`,`phone`,`address`,`email`,`pass`,`desc`,`c_name`,`c_phone`,`phone2`,`fax`,`show_mail`,`area`,`logofile`,`agent_id`,`cat_id`,`active`,`code`)
         VALUES (:name,:phone,:address,:email,:pass,:descr,:c_name,:c_phone,:phone2,:fax,:show_mail,:area,:logo,:agent,:cat,1,:code)",
    ];

    foreach ($attempts as $sql) {
        if (db_query($sql, $params) !== false) {
            return function_exists('db_insert_id') ? (int) db_insert_id() : 0;
        }
    }
    return 0;
}

function bizpanel_insert_biz_seo(int $bizId, string $name, string $desc = ''): void
{
    if ($bizId <= 0 || !function_exists('db_query')) {
        return;
    }
    @db_query(
        "INSERT INTO `mod_seo` (`id`, `mod`, `plink`, `lang_id`, `ptitle`, `description`)
         VALUES (:id, 'products', :pl, 1, :pt, :de)",
        ['id' => $bizId, 'pl' => $name, 'pt' => $name, 'de' => $desc]
    );
}

function bizpanel_insert_sop_cat(string $title): bool
{
    $title = trim($title);
    if ($title === '') {
        return false;
    }
    $attempts = [
        "INSERT INTO `mod_sop_cats` (`title`, `order`, `pic`, `pic1`) VALUES (:t, 0, '', '')",
        "INSERT INTO `mod_sop_cats` (`title`, `order`) VALUES (:t, 0)",
    ];
    foreach ($attempts as $sql) {
        if (db_query($sql, ['t' => $title]) !== false) {
            return true;
        }
    }
    return false;
}

function bizpanel_insert_sop_subcat(int $catId, string $title): bool
{
    $title = trim($title);
    if ($catId <= 0 || $title === '') {
        return false;
    }
    $attempts = [
        "INSERT INTO `mod_sop` (`cat_id`, `parent`, `title`, `order`, `pic`, `pic1`) VALUES (:c, 0, :t, 0, '', '')",
        "INSERT INTO `mod_sop` (`cat_id`, `parent`, `title`, `order`) VALUES (:c, 0, :t, 0)",
    ];
    foreach ($attempts as $sql) {
        if (db_query($sql, ['c' => $catId, 't' => $title]) !== false) {
            return true;
        }
    }
    return false;
}

function bizpanel_styles(): void
{
?>
<style>
.bz-shell{width:100%;direction:rtl;}
.bz-panel-head{margin-bottom:20px;}
.bz-panel-title{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
.bz-panel-title h2{font-size:1.35rem;font-weight:800;margin:0;}
.bz-subnav{display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:0;list-style:none;}
.bz-subnav-link{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;background:rgba(255,255,255,0.06);border:1px solid rgba(148,163,184,0.15);color:var(--bp-navy-light,#cbd5e1);text-decoration:none;font-weight:600;font-size:14px;}
.bz-subnav-link i{width:16px;text-align:center;}
.bz-subnav-link:hover{background:rgba(96,165,250,0.12);color:#fff;}
.bz-subnav-link.active{background:rgba(96,165,250,0.18);border-color:rgba(96,165,250,0.35);color:#fff;}
.bz-main{width:100%;min-width:0;}
.bz-card{background:#fff;border-radius:20px;padding:26px;margin-bottom:24px;box-shadow:0 8px 26px rgba(0,0,0,.06);}
.bz-card h3{margin-bottom:18px;font-weight:700;color:#0f172a;}
.bz-card h4{margin:18px 0 10px;font-weight:700;color:#374151;}
.bz-badge{display:inline-block;padding:4px 12px;border-radius:30px;font-size:13px;font-weight:700;margin-inline-start:6px;}
.bz-l1{background:#ede9fe;color:#6d28d9;}
.bz-l2{background:#dbeafe;color:#1d4ed8;}
.bz-l3{background:#dcfce7;color:#15803d;}
.bz-stat{display:flex;gap:16px;flex-wrap:wrap;margin-top:6px;}
.bz-stat .bz-box{flex:1;min-width:140px;background:#f8fafc;border:1px solid #eef0f3;border-radius:14px;padding:16px;}
.bz-stat .bz-box span{display:block;color:#6b7280;font-size:13px;margin-bottom:6px;}
.bz-stat .bz-box strong{font-size:20px;font-weight:800;color:#0f172a;}
.bz-table{width:100%;border-collapse:collapse;}
.bz-table th,.bz-table td{padding:10px 12px;border-top:1px solid #eef0f3;text-align:right;font-size:14px;color:#374151;}
.bz-row-form{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px;}
.bz-row-form input,.bz-row-form select,.bz-row-form textarea{flex:1;min-width:160px;border:1px solid #d1d5db;border-radius:10px;padding:10px;}
.bz-btn{background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:10px 18px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;}
.bz-btn-sm{padding:6px 12px;font-size:13px;border-radius:8px;}
.bz-btn-red{background:#dc2626;}
.bz-btn-outline{background:#fff;color:#4f46e5;border:1px solid #4f46e5;}
.bz-pills{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
.bz-pill{padding:8px 14px;border-radius:30px;background:#f3f4f6;color:#374151;text-decoration:none;font-size:13px;font-weight:700;}
.bz-pill.active{background:#4f46e5;color:#fff;}
@media(max-width:640px){.bz-subnav-link{padding:8px 12px;font-size:13px;}}
</style>
<?php
}

function bizpanel_subnav(string $active, array $sections, string $title, string $badgeClass = '', string $queryPrefix = ''): void
{
?>
<div class="bz-panel-head">
    <div class="bz-panel-title">
        <h2><?php echo htmlspecialchars($title); ?></h2>
        <?php if ($badgeClass !== ''): ?><span class="bz-badge <?php echo htmlspecialchars($badgeClass); ?>"><?php
            echo $badgeClass === 'bz-l1' ? 'מנהל תחום' : ($badgeClass === 'bz-l2' ? 'מנהל קטגוריה' : '');
        ?></span><?php endif; ?>
    </div>
    <nav class="bz-subnav" aria-label="<?php echo htmlspecialchars($title); ?>">
<?php foreach ($sections as $key => $sec): ?>
        <a class="bz-subnav-link <?php echo $active === $key ? 'active' : ''; ?>" href="?<?php echo $queryPrefix; ?>section=<?php echo urlencode($key); ?>"><i class="fa-solid <?php echo $sec[1]; ?>"></i> <?php echo htmlspecialchars($sec[0]); ?></a>
<?php endforeach; ?>
    </nav>
</div>
<?php
}

/** @deprecated Use account_sidebar.php for primary nav; bizpanel_subnav for section tabs only. */
function bizpanel_nav(string $active, array $sections, string $title, string $badgeClass = '', string $queryPrefix = ''): void
{
    bizpanel_subnav($active, $sections, $title, $badgeClass, $queryPrefix);
}
