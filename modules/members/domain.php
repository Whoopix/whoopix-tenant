<?php
/**
 * L1 Domain Manager panel — ,members,domain
 * מנהל תחום: category managers, domain stats, leads, points, main categories.
 */
if (function_exists('site_roles_ensure_schema')) {
    site_roles_ensure_schema();
}

if (!is_array($USER_LOGIN)) {
    include 'login.php';
} else {

if (!function_exists('site_is_l1') || !site_is_l1($USER_LOGIN)) {
    if (function_exists('site_l2_categories') && !empty(site_l2_categories($USER_LOGIN))) {
        header('Location: ,members,category');
    } else {
        header('Location: ,members,business');
    }
    exit;
}

$TEMP_PAGE_TITLE   = 'ניהול תחום';
$TEMP_INPAGE_TITLE = 'ניהול תחום';
$TEMP_TEMPLATE     = 5;

require __DIR__ . '/inc/bizpanel_common.php';

$MSG = bizpanel_take_flash();
$me  = (int) $USER_LOGIN['id'];
$tok = $BIZPANEL_TOKEN;

$SECTIONS = [
    'overview'  => ['סקירת תחום', 'fa-gauge-high'],
    'managers'  => ['מנהלי קטגוריה', 'fa-user-shield'],
    'cats'      => ['קטגוריות ראשיות', 'fa-layer-group'],
    'leads'     => ['פניות בתחום', 'fa-inbox'],
    'points'    => ['נקודות ותשלומים', 'fa-coins'],
];

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = site_points_export_rows(0, BIZ_ROLE_L2);
    site_send_points_csv($rows, 'site-domain-points-' . date('Y-m-d') . '.csv');
}

$postAction = bizpanel_post_action();
if ($postAction !== '') {
    $action = $postAction;
    $ptoken = (string) ($_POST['token'] ?? '');
    $postSection = bizpanel_post_section('overview');
    if (!isset($SECTIONS[$postSection])) {
        $postSection = 'overview';
    }
    if (!hash_equals($tok, $ptoken)) {
        $MSG[] = ['danger', 'בקשה לא תקינה.'];
    } else {
        switch ($action) {
            case 'appoint_l2':
                $cid   = (int) ($_POST['cat_id'] ?? 0);
                $email = trim((string) ($_POST['email'] ?? ''));
                $mem   = site_find_member_by_email($email);
                if ($cid <= 0 || !$mem) {
                    $MSG[] = ['danger', 'קטגוריה או משתמש לא נמצא.'];
                } else {
                    site_assign_role((int) $mem['id'], BIZ_ROLE_L2, $cid, $me);
                    $MSG[] = ['success', 'מנהל קטגוריה מונה בהצלחה.'];
                }
                $postSection = 'managers';
                break;
            case 'revoke_l2':
                site_revoke_role((int) ($_POST['member_id'] ?? 0), BIZ_ROLE_L2, (int) ($_POST['cat_id'] ?? 0));
                $MSG[] = ['success', 'ההרשאה הוסרה.'];
                $postSection = 'managers';
                break;
            case 'add_cat':
                $title = trim((string) ($_POST['title'] ?? ''));
                if ($title === '') {
                    $MSG[] = ['danger', 'נא להזין שם קטגוריה.'];
                } elseif (bizpanel_insert_sop_cat($title)) {
                    $MSG[] = ['success', 'קטגוריה נוספה.'];
                } else {
                    $MSG[] = ['danger', bizpanel_db_error_message()];
                }
                $postSection = 'cats';
                break;
            case 'edit_cat':
                $cid   = (int) ($_POST['cat_id'] ?? 0);
                $title = trim((string) ($_POST['title'] ?? ''));
                if ($cid <= 0 || $title === '') {
                    $MSG[] = ['danger', 'נתונים חסרים.'];
                } elseif (db_query("UPDATE `mod_sop_cats` SET `title` = :t WHERE `id` = :id", ['t' => $title, 'id' => $cid]) === false) {
                    $MSG[] = ['danger', bizpanel_db_error_message()];
                } else {
                    $MSG[] = ['success', 'קטגוריה עודכנה.'];
                }
                $postSection = 'cats';
                break;
            case 'del_cat':
                $cid = (int) ($_POST['cat_id'] ?? 0);
                if ($cid > 0) {
                    $sub = db_query("SELECT COUNT(*) AS c FROM `mod_sop` WHERE `cat_id` = :c", ['c' => $cid]);
                    $sc  = $sub ? db_fetch_array($sub) : ['c' => 0];
                    if ((int) $sc['c'] > 0) {
                        $MSG[] = ['danger', 'לא ניתן למחוק קטגוריה עם תתי־קטגוריות.'];
                    } elseif (db_query("DELETE FROM `mod_sop_cats` WHERE `id` = :id", ['id' => $cid]) === false) {
                        $MSG[] = ['danger', bizpanel_db_error_message()];
                    } else {
                        db_query("DELETE FROM `mod_biz_roles` WHERE `level` = 2 AND `cat_id` = :id", ['id' => $cid]);
                        $MSG[] = ['success', 'קטגוריה נמחקה.'];
                    }
                }
                $postSection = 'cats';
                break;
        }
    }
    bizpanel_redirect_section($postSection, $MSG);
}

$cur = bizpanel_post_section('overview');
if (!isset($SECTIONS[$cur])) { $cur = 'overview'; }

function PAGE_TEXT()
{
    global $USER_LOGIN, $BIZPANEL_TOKEN, $MSG, $SECTIONS, $cur;
    $tok   = htmlspecialchars($BIZPANEL_TOKEN);
    $stats = site_domain_stats();
    $myPts = site_member_points((int) $USER_LOGIN['id']);
    bizpanel_styles();
?>
<div class="member-dashboard profile-settings">
<div class="dashboard-layout">
<?php $ACCOUNT_NAV_ACTIVE = 'domain'; include __DIR__ . "/inc/account_sidebar.php"; ?>
<div class="dashboard-main">
<div class="bz-shell">
<?php bizpanel_subnav($cur, $SECTIONS, 'ניהול תחום', 'bz-l1'); ?>
<div class="bz-main">
<?php foreach ($MSG as $m): ?>
<div class="alert alert-<?php echo $m[0]; ?>" style="border-radius:12px;"><?php echo htmlspecialchars($m[1]); ?></div>
<?php endforeach; ?>

<?php if ($cur === 'overview'): ?>
<div class="bz-card">
<h3>סקירת התחום</h3>
<div class="bz-stat">
<div class="bz-box"><span>קטגוריות ראשיות</span><strong><?php echo $stats['categories']; ?></strong></div>
<div class="bz-box"><span>מנהלי קטגוריה</span><strong><?php echo $stats['managers']; ?></strong></div>
<div class="bz-box"><span>עסקים</span><strong><?php echo $stats['businesses']; ?></strong></div>
<div class="bz-box"><span>פניות</span><strong><?php echo $stats['leads']; ?></strong></div>
<div class="bz-box"><span>נקודות בתחום</span><strong><?php echo number_format($stats['points']); ?></strong></div>
<div class="bz-box"><span>הנקודות שלי</span><strong><?php echo number_format($myPts); ?></strong></div>
</div>
</div>

<?php elseif ($cur === 'managers'): ?>
<div class="bz-card">
<h3>מנהלי קטגוריה (L2)</h3>
<form method="post" class="bz-row-form">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="managers">
<input type="hidden" name="panel_action" value="appoint_l2">
<select name="cat_id" required><option value="">בחר קטגוריה</option>
<?php
$cq = db_query("SELECT * FROM `mod_sop_cats` ORDER BY `title`");
while ($c = db_fetch_array($cq, PDO::FETCH_ASSOC)):
?><option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option><?php endwhile; ?>
</select>
<input type="email" name="email" placeholder="אימייל מנהל קטגוריה" required>
<button class="bz-btn">הוסף מנהל קטגוריה</button>
</form>
<table class="bz-table" style="margin-top:20px;">
<tr><th>משתמש</th><th>קטגוריה</th><th>נקודות</th><th>פעולה</th></tr>
<?php foreach (site_category_managers() as $r): ?>
<tr>
<td><?php echo htmlspecialchars(($r['username'] ?? '') . ' (' . ($r['email'] ?? '') . ')'); ?></td>
<td><?php echo htmlspecialchars($r['cat_title'] ?? ('#' . $r['cat_id'])); ?></td>
<td><?php echo number_format((int) ($r['member_points'] ?? 0)); ?></td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="managers">
<input type="hidden" name="panel_action" value="revoke_l2">
<input type="hidden" name="member_id" value="<?php echo (int) $r['member_id']; ?>">
<input type="hidden" name="cat_id" value="<?php echo (int) $r['cat_id']; ?>">
<button class="bz-btn bz-btn-sm bz-btn-red">הסר</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php elseif ($cur === 'cats'): ?>
<div class="bz-card">
<h3>קטגוריות ראשיות</h3>
<table class="bz-table">
<tr><th>#</th><th>שם</th><th>עסקים</th><th>פעולות</th></tr>
<?php
$cats = db_query("SELECT * FROM `mod_sop_cats` ORDER BY `order`, `id`");
while ($c = db_fetch_array($cats, PDO::FETCH_ASSOC)):
    $st = site_scope_stats((int) $c['id']);
?>
<tr>
<td><?php echo (int) $c['id']; ?></td>
<td>
<form method="post" class="bz-row-form" style="margin:0;">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="cats">
<input type="hidden" name="panel_action" value="edit_cat">
<input type="hidden" name="cat_id" value="<?php echo (int) $c['id']; ?>">
<input type="text" name="title" value="<?php echo htmlspecialchars($c['title']); ?>">
<button class="bz-btn bz-btn-sm">שמור</button>
</form>
</td>
<td><?php echo $st['businesses']; ?></td>
<td>
<form method="post" onsubmit="return confirm('למחוק?');" style="display:inline;">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="cats">
<input type="hidden" name="panel_action" value="del_cat">
<input type="hidden" name="cat_id" value="<?php echo (int) $c['id']; ?>">
<button class="bz-btn bz-btn-sm bz-btn-red">מחק</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
<form method="post" class="bz-row-form">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="cats">
<input type="hidden" name="panel_action" value="add_cat">
<input type="text" name="title" placeholder="שם קטגוריה חדשה" required>
<button type="submit" class="bz-btn">הוסף קטגוריה</button>
</form>
</div>

<?php elseif ($cur === 'leads'): ?>
<div class="bz-card">
<h3>פניות אחרונות בתחום</h3>
<table class="bz-table">
<tr><th>עסק</th><th>שם</th><th>טלפון</th><th>תאריך</th></tr>
<?php
$allLeads = [];
$cq2 = db_query("SELECT id FROM mod_sop_cats ORDER BY id");
while ($c = db_fetch_array($cq2, PDO::FETCH_ASSOC)) {
    foreach (site_scope_leads((int) $c['id'], 10) as $l) {
        $allLeads[] = $l;
    }
}
usort($allLeads, fn($a, $b) => (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0));
$allLeads = array_slice($allLeads, 0, 40);
foreach ($allLeads as $l):
?>
<tr>
<td><?php echo htmlspecialchars($l['biz_name'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($l['full_name'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($l['phone'] ?? ''); ?></td>
<td><?php echo htmlspecialchars((string) ($l['created_at'] ?? '')); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php else: /* points */ ?>
<div class="bz-card">
<h3>נקודות ותשלומים (PayPal ידני)</h3>
<p>ייצוא דוח נקודות למנהלי קטגוריה — העברה ידנית ב-PayPal לפי האימייל.</p>
<div class="bz-stat">
<div class="bz-box"><span>סה״כ נקודות בתחום</span><strong><?php echo number_format($stats['points']); ?></strong></div>
<div class="bz-box"><span>נקודות lead (סוכנים)</span><strong><?php echo (int) WPX_SITE_POINTS_PER_LEAD; ?> לפנייה</strong></div>
<div class="bz-box"><span>rollup L2</span><strong><?php echo (int) WPX_SITE_POINTS_ROLLUP_L2; ?> לפנייה</strong></div>
<div class="bz-box"><span>rollup L1</span><strong><?php echo (int) WPX_SITE_POINTS_ROLLUP_L1; ?> לפנייה</strong></div>
</div>
<p style="margin-top:16px;"><a class="bz-btn" href="?section=points&amp;export=csv">ייצוא CSV — מנהלי קטגוריה</a></p>
<table class="bz-table" style="margin-top:20px;">
<tr><th>משתמש</th><th>אימייל</th><th>נקודות lead</th><th>rollup</th><th>סה״כ</th></tr>
<?php foreach (site_points_export_rows(0) as $r): ?>
<tr>
<td><?php echo htmlspecialchars(($r['name'] ?? '') !== '' ? $r['name'] : ($r['username'] ?? '')); ?></td>
<td><?php echo htmlspecialchars($r['email'] ?? ''); ?></td>
<td><?php echo number_format((int) ($r['lead_pts'] ?? 0)); ?></td>
<td><?php echo number_format((int) ($r['rollup_pts'] ?? 0)); ?></td>
<td><strong><?php echo number_format((int) ($r['total_pts'] ?? 0)); ?></strong></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
</div></div>
</div></div></div>
<?php
}
}
?>
