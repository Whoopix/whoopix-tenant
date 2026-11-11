<?php
/**
 * L2 Category Manager panel — ,members,category
 * מנהל קטגוריה: agents, businesses, leads, points in scoped category.
 */
if (function_exists('site_roles_ensure_schema')) {
    site_roles_ensure_schema();
}

if (!is_array($USER_LOGIN)) {
    include 'login.php';
} else {

$L2_CATS = function_exists('site_l2_categories') ? site_l2_categories($USER_LOGIN) : [];
if (empty($L2_CATS)) {
    if (function_exists('site_is_l1') && site_is_l1($USER_LOGIN)) {
        header('Location: ,members,domain');
    } else {
        header('Location: ,members,business');
    }
    exit;
}

$TEMP_PAGE_TITLE   = 'ניהול קטגוריה';
$TEMP_INPAGE_TITLE = 'ניהול קטגוריה';
$TEMP_TEMPLATE     = 5;

require __DIR__ . '/inc/bizpanel_common.php';

$MSG = bizpanel_take_flash();
$me  = (int) $USER_LOGIN['id'];
$tok = $BIZPANEL_TOKEN;

$selCat = (int) ($_GET['cat'] ?? 0);
if (!in_array($selCat, $L2_CATS, true)) {
    $selCat = (int) $L2_CATS[0];
}

$SECTIONS = [
    'overview'   => ['סקירת קטגוריה', 'fa-gauge-high'],
    'agents'     => ['סוכנים', 'fa-users'],
    'businesses' => ['עסקים', 'fa-store'],
    'leads'      => ['פניות', 'fa-inbox'],
    'points'     => ['נקודות', 'fa-coins'],
    'subcats'    => ['תתי־קטגוריות', 'fa-folder-tree'],
];

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = site_points_export_rows($selCat, BIZ_ROLE_L3);
    site_send_points_csv($rows, 'site-category-' . $selCat . '-points-' . date('Y-m-d') . '.csv');
}

$postAction = bizpanel_post_action();
if ($postAction !== '') {
    $action = $postAction;
    $ptoken = (string) ($_POST['token'] ?? '');
    $postCat = (int) ($_POST['cat_id'] ?? $selCat);
    $postSection = bizpanel_post_section('overview');
    if (!isset($SECTIONS[$postSection])) {
        $postSection = 'overview';
    }
    if (!hash_equals($tok, $ptoken)) {
        $MSG[] = ['danger', 'בקשה לא תקינה.'];
    } else {
        if (!site_can_manage_category($USER_LOGIN, $postCat)) {
            $MSG[] = ['danger', 'אין הרשאה לקטגוריה זו.'];
        } else {
            switch ($action) {
                case 'appoint_l3':
                    $email = trim((string) ($_POST['email'] ?? ''));
                    $mem   = site_find_member_by_email($email);
                    if (!$mem) {
                        $MSG[] = ['danger', 'משתמש לא נמצא.'];
                    } else {
                        site_assign_role((int) $mem['id'], BIZ_ROLE_L3, $postCat, $me);
                        $MSG[] = ['success', 'סוכן מונה בהצלחה.'];
                    }
                    $postSection = 'agents';
                    break;
                case 'revoke_l3':
                    site_revoke_role((int) ($_POST['member_id'] ?? 0), BIZ_ROLE_L3, $postCat);
                    $MSG[] = ['success', 'הסוכן הוסר.'];
                    $postSection = 'agents';
                    break;
                case 'add_subcat':
                    $title = trim((string) ($_POST['title'] ?? ''));
                    if ($title === '') {
                        $MSG[] = ['danger', 'נא להזין שם.'];
                    } elseif (bizpanel_insert_sop_subcat($postCat, $title)) {
                        $MSG[] = ['success', 'תת־קטגוריה נוספה.'];
                    } else {
                        $MSG[] = ['danger', bizpanel_db_error_message()];
                    }
                    $postSection = 'subcats';
                    break;
            }
        }
    }
    bizpanel_redirect_section($postSection, $MSG, 'cat=' . $postCat . '&');
}

$cur = bizpanel_post_section('overview');
if (!isset($SECTIONS[$cur])) { $cur = 'overview'; }

$catTitle = '';
$cq = db_query("SELECT title FROM mod_sop_cats WHERE id = :id", ['id' => $selCat]);
if ($cq) { $cr = db_fetch_array($cq); $catTitle = $cr['title'] ?? ''; }

function PAGE_TEXT()
{
    global $USER_LOGIN, $BIZPANEL_TOKEN, $MSG, $SECTIONS, $cur, $L2_CATS, $selCat, $catTitle;
    $tok   = htmlspecialchars($BIZPANEL_TOKEN);
    $stats = site_scope_stats($selCat);
    $myPts = site_member_points((int) $USER_LOGIN['id']);
    bizpanel_styles();
?>
<div class="member-dashboard profile-settings">
<div class="dashboard-layout">
<?php $ACCOUNT_NAV_ACTIVE = 'category'; include __DIR__ . "/inc/account_sidebar.php"; ?>
<div class="dashboard-main">
<div class="bz-shell">
<?php bizpanel_subnav($cur, $SECTIONS, 'ניהול קטגוריה', 'bz-l2', 'cat=' . $selCat . '&'); ?>
<div class="bz-main">
<?php if (count($L2_CATS) > 1): ?>
<div class="bz-pills">
<?php foreach ($L2_CATS as $cid):
    $t = '';
    $tq = db_query("SELECT title FROM mod_sop_cats WHERE id = :id", ['id' => (int) $cid]);
    if ($tq) { $tr = db_fetch_array($tq); $t = $tr['title'] ?? ('#' . $cid); }
?>
<a class="bz-pill <?php echo (int) $cid === $selCat ? 'active' : ''; ?>" href="?cat=<?php echo (int) $cid; ?>&amp;section=<?php echo urlencode($cur); ?>"><?php echo htmlspecialchars($t); ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php foreach ($MSG as $m): ?>
<div class="alert alert-<?php echo $m[0]; ?>" style="border-radius:12px;"><?php echo htmlspecialchars($m[1]); ?></div>
<?php endforeach; ?>

<?php if ($cur === 'overview'): ?>
<div class="bz-card">
<h3><?php echo htmlspecialchars($catTitle); ?> — סקירה</h3>
<div class="bz-stat">
<div class="bz-box"><span>סוכנים</span><strong><?php echo $stats['agents']; ?></strong></div>
<div class="bz-box"><span>עסקים</span><strong><?php echo $stats['businesses']; ?></strong></div>
<div class="bz-box"><span>פניות</span><strong><?php echo $stats['leads']; ?></strong></div>
<div class="bz-box"><span>נקודות בקטגוריה</span><strong><?php echo number_format($stats['points']); ?></strong></div>
<div class="bz-box"><span>הנקודות שלי</span><strong><?php echo number_format($myPts); ?></strong></div>
</div>
</div>

<?php elseif ($cur === 'agents'): ?>
<div class="bz-card">
<h3>סוכנים (L3) בקטגוריה</h3>
<form method="post" class="bz-row-form">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="agents">
<input type="hidden" name="panel_action" value="appoint_l3">
<input type="hidden" name="cat_id" value="<?php echo $selCat; ?>">
<input type="email" name="email" placeholder="אימייל הסוכן" required>
<button class="bz-btn">הוסף סוכן</button>
</form>
<table class="bz-table" style="margin-top:20px;">
<tr><th>משתמש</th><th>עסקים</th><th>נקודות</th><th>קוד שותף</th><th>פעולה</th></tr>
<?php foreach (site_agents_in_category($selCat) as $a): ?>
<tr>
<td><?php echo htmlspecialchars(($a['username'] ?? '') . ' (' . ($a['email'] ?? '') . ')'); ?></td>
<td><?php echo (int) ($a['biz_count'] ?? 0); ?></td>
<td><?php echo number_format((int) ($a['member_points'] ?? 0)); ?></td>
<td><code><?php echo htmlspecialchars($a['aff_code'] ?? ''); ?></code></td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="agents">
<input type="hidden" name="panel_action" value="revoke_l3">
<input type="hidden" name="member_id" value="<?php echo (int) $a['member_id']; ?>">
<input type="hidden" name="cat_id" value="<?php echo $selCat; ?>">
<button class="bz-btn bz-btn-sm bz-btn-red">הסר</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php elseif ($cur === 'businesses'): ?>
<div class="bz-card">
<h3>עסקים בקטגוריה</h3>
<table class="bz-table">
<tr><th>שם</th><th>סוכן</th><th>טלפון</th><th>פניות</th></tr>
<?php foreach (site_scope_businesses($selCat) as $b): ?>
<tr>
<td><?php echo htmlspecialchars($b['name'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($b['agent_name'] ?? $b['agent_email'] ?? '—'); ?></td>
<td><?php echo htmlspecialchars($b['phone'] ?? ''); ?></td>
<td><?php echo (int) ($b['lead_count'] ?? 0); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php elseif ($cur === 'leads'): ?>
<div class="bz-card">
<h3>פניות בקטגוריה</h3>
<table class="bz-table">
<tr><th>עסק</th><th>שם</th><th>טלפון</th><th>אימייל</th><th>תאריך</th></tr>
<?php foreach (site_scope_leads($selCat, 50) as $l): ?>
<tr>
<td><?php echo htmlspecialchars($l['biz_name'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($l['full_name'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($l['phone'] ?? ''); ?></td>
<td><?php echo htmlspecialchars($l['email'] ?? ''); ?></td>
<td><?php echo htmlspecialchars((string) ($l['created_at'] ?? '')); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php elseif ($cur === 'subcats'): ?>
<div class="bz-card">
<h3>תתי־קטגוריות</h3>
<table class="bz-table">
<tr><th>#</th><th>שם</th></tr>
<?php
$subs = db_query("SELECT * FROM mod_sop WHERE cat_id = :c ORDER BY `order`, id", ['c' => $selCat]);
while ($s = db_fetch_array($subs, PDO::FETCH_ASSOC)):
?><tr><td><?php echo (int) $s['id']; ?></td><td><?php echo htmlspecialchars($s['title']); ?></td></tr><?php endwhile; ?>
</table>
<form method="post" class="bz-row-form">
<input type="hidden" name="token" value="<?php echo $tok; ?>">
<input type="hidden" name="section" value="subcats">
<input type="hidden" name="panel_action" value="add_subcat">
<input type="hidden" name="cat_id" value="<?php echo $selCat; ?>">
<input type="text" name="title" placeholder="שם תת־קטגוריה" required>
<button type="submit" class="bz-btn">הוסף</button>
</form>
</div>

<?php else: /* points */ ?>
<div class="bz-card">
<h3>נקודות סוכנים — ייצוא ל-PayPal</h3>
<p><a class="bz-btn" href="?cat=<?php echo $selCat; ?>&amp;section=points&amp;export=csv">ייצוא CSV סוכנים</a></p>
<table class="bz-table" style="margin-top:16px;">
<tr><th>סוכן</th><th>אימייל</th><th>lead</th><th>rollup</th><th>סה״כ</th></tr>
<?php foreach (site_points_export_rows($selCat, BIZ_ROLE_L3) as $r): ?>
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
