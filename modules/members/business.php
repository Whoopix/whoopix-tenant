<?php

if(!is_array($USER_LOGIN)) {
	include "login.php";
} else {

// Agent points CSV export (L3 / any member with points)
if (isset($_GET['export']) && $_GET['export'] === 'csv' && function_exists('site_points_export_rows')) {
    $agent_id = (int) $USER_LOGIN['id'];
    $rows = array_filter(site_points_export_rows(0), fn($r) => (int)($r['id'] ?? 0) === $agent_id);
    if (!$rows && function_exists('site_member_points') && site_member_points($agent_id) > 0) {
        $mq = db_query("SELECT id, email, username, name FROM mod_members WHERE id = :id", ['id' => $agent_id]);
        $m = $mq ? db_fetch_array($mq, PDO::FETCH_ASSOC) : [];
        $rows = [[
            'id' => $agent_id,
            'email' => $m['email'] ?? '',
            'username' => $m['username'] ?? '',
            'name' => $m['name'] ?? '',
            'total_pts' => site_member_points($agent_id),
            'lead_pts' => site_member_points($agent_id),
            'rollup_pts' => 0,
        ]];
    }
    site_send_points_csv($rows, 'site-agent-' . $agent_id . '-points-' . date('Y-m-d') . '.csv');
}

$TEMP_PAGE_TITLE = "ניהול עסקים";
$TEMP_INPAGE_TITLE = "ניהול עסקים";
$TEMP_TEMPLATE = 5;

require __DIR__ . '/inc/bizpanel_common.php';

if (function_exists('site_roles_ensure_schema')) {
	site_roles_ensure_schema();
}

// ── Flash + modal stash (PRG after add / edit / delete) ─────────────────────
$MSG = bizpanel_take_flash();
$biz_form = is_array($_SESSION['bizpanel_biz_form'] ?? null) ? $_SESSION['bizpanel_biz_form'] : [];
unset($_SESSION['bizpanel_biz_form']);

$biz_add_msg   = "";
$biz_add_error = false;
$biz_edit_open = false;

foreach ($MSG as $row) {
	$kind = (string) ($row[0] ?? '');
	$text = (string) ($row[1] ?? '');
	if ($text === '') {
		continue;
	}
	$biz_add_msg   = $text;
	$biz_add_error = ($kind === 'danger' || $kind === 'err');
	break;
}

if (!empty($biz_form['reopen'])) {
	$biz_edit_open = (($biz_form['action'] ?? '') === 'edit_business');
}

// ── Helper: handle an optional logo upload, return [filename|'', errorMsg|''] ─
if (!function_exists('site_handle_logo_upload')) {
	function site_handle_logo_upload(int $agent_id) : array
	{
		if (empty($_FILES['biz_logo']['name']) || (int)($_FILES['biz_logo']['error'] ?? 4) !== UPLOAD_ERR_OK) {
			return ['', ''];
		}
		$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
		$ext     = strtolower(pathinfo((string)$_FILES['biz_logo']['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowed, true)) {
			return ['', "סוג קובץ לוגו לא נתמך (jpg, png, gif, webp)"];
		}
		if ((int)$_FILES['biz_logo']['size'] > 4 * 1024 * 1024) {
			return ['', "קובץ הלוגו גדול מדי (עד 4MB)"];
		}
		$logo_dir = __DIR__ . '/../../files/biz_logo';
		if (!is_dir($logo_dir)) {
			@mkdir($logo_dir, 0755, true);
		}
		$logo_filename = 'biz_' . $agent_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
		if (!@move_uploaded_file($_FILES['biz_logo']['tmp_name'], $logo_dir . '/' . $logo_filename)) {
			return ['', "אירעה שגיאה בהעלאת הלוגו"];
		}
		return [$logo_filename, ''];
	}
}

// ── Helper: can the current member manage this business row? ──────────────────
if (!function_exists('site_member_can_manage')) {
	function site_member_can_manage(array $member, array $biz) : bool
	{
		// Owner (agent) can always manage their own business.
		if ((int)($biz['agent_id'] ?? 0) === (int)($member['id'] ?? 0) && (int)($member['id'] ?? 0) > 0) {
			return true;
		}
		// Otherwise defer to the roles subsystem (L1 / scoped L2) if present.
		if (function_exists('site_can_manage_business')) {
			return site_can_manage_business($member, $biz);
		}
		return false;
	}
}

// ── Helper: resolve a business logo to a web path, or '' if none/missing ─────
if (!function_exists('site_logo_src')) {
	function site_logo_src(?string $logofile) : string
	{
		$logofile = trim((string)$logofile);
		if ($logofile === '') {
			return '';
		}
		// Reject any path trickery — only a bare filename is valid.
		if (strpbrk($logofile, "/\\") !== false) {
			return '';
		}
		$abs = __DIR__ . '/../../files/biz_logo/' . $logofile;
		if (!is_file($abs)) {
			return '';
		}
		return 'files/biz_logo/' . rawurlencode($logofile);
	}
}

$agent_id_post = (int)$USER_LOGIN['id'];
$biz_post_action = (string)($_POST['biz_action'] ?? '');

if ($biz_post_action !== '' && (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {

	$flash = [];
	$stash = [
		'reopen'     => false,
		'action'     => $biz_post_action,
		'biz_id'     => (string) ($_POST['biz_id'] ?? ''),
		'biz_name'   => (string) ($_POST['biz_name'] ?? ''),
		'biz_phone'  => (string) ($_POST['biz_phone'] ?? ''),
		'biz_address'=> (string) ($_POST['biz_address'] ?? ''),
		'biz_email'  => (string) ($_POST['biz_email'] ?? ''),
		'biz_desc'   => (string) ($_POST['biz_desc'] ?? ''),
	];

	// ── Add-business handler (member-scoped create into mod_biz) ────────────
	if ($biz_post_action === 'add_business') {

		$agent_id  = $agent_id_post;
		$b_name    = trim($stash['biz_name']);
		$b_phone   = trim($stash['biz_phone']);
		$b_address = trim($stash['biz_address']);
		$b_email   = trim($stash['biz_email']);
		$b_desc    = trim($stash['biz_desc']);

		if ($b_name === '' || $b_phone === '') {
			$flash[] = ['danger', 'יש למלא שם עסק וטלפון'];
			$stash['reopen'] = true;
		} else {
			[$logo_filename, $logo_err] = site_handle_logo_upload($agent_id);
			if ($logo_err !== '') {
				$flash[] = ['danger', $logo_err];
				$stash['reopen'] = true;
			} else {
				$new_id = bizpanel_insert_biz([
					'name'     => $b_name,
					'phone'    => $b_phone,
					'address'  => $b_address,
					'email'    => $b_email,
					'desc'     => $b_desc,
					'logofile' => $logo_filename,
					'agent_id' => $agent_id,
					'cat_id'   => 0,
					'area'     => 0,
				]);

				if ($new_id > 0) {
					bizpanel_insert_biz_seo($new_id, $b_name, $b_desc);
					if (function_exists('site_member_aff_code')) {
						site_member_aff_code($agent_id);
					}
					if (function_exists('site_stamp_biz_referrer')) {
						site_stamp_biz_referrer($new_id, $agent_id);
					}
					$flash[] = ['success', 'העסק נוסף בהצלחה'];
				} else {
					$flash[] = ['danger', bizpanel_db_error_message()];
					$stash['reopen'] = true;
				}
			}
		}
	}

	// ── Edit-business handler (owner / L1 / scoped L2) ──────────────────────
	elseif ($biz_post_action === 'edit_business') {

		$agent_id = $agent_id_post;
		$biz_id   = (int)($_POST['biz_id'] ?? 0);

		$bq  = $biz_id > 0 ? db_query("SELECT * FROM mod_biz WHERE id = :id", ['id' => $biz_id]) : false;
		$biz = $bq ? db_fetch_array($bq, PDO::FETCH_ASSOC) : false;

		if (!$biz || !site_member_can_manage($USER_LOGIN, $biz)) {
			$flash[] = ['danger', 'אין הרשאה לערוך עסק זה'];
		} else {
			$b_name    = trim($stash['biz_name']);
			$b_phone   = trim($stash['biz_phone']);
			$b_address = trim($stash['biz_address']);
			$b_email   = trim($stash['biz_email']);
			$b_desc    = trim($stash['biz_desc']);

			if ($b_name === '' || $b_phone === '') {
				$flash[] = ['danger', 'יש למלא שם עסק וטלפון'];
				$stash['reopen'] = true;
			} else {
				[$logo_filename, $logo_err] = site_handle_logo_upload($agent_id);
				if ($logo_err !== '') {
					$flash[] = ['danger', $logo_err];
					$stash['reopen'] = true;
				} else {
					$keep_logo = (string)($biz['logofile'] ?? '');
					$new_logo  = $logo_filename !== '' ? $logo_filename : $keep_logo;

					$ok = db_query(
						"UPDATE mod_biz
						 SET name = :name, phone = :phone, address = :address, email = :email, `desc` = :descr, logofile = :logo
						 WHERE id = :id",
						[
							'name'    => $b_name,
							'phone'   => $b_phone,
							'address' => $b_address,
							'email'   => $b_email,
							'descr'   => $b_desc,
							'logo'    => $new_logo,
							'id'      => $biz_id,
						]
					);

					if ($ok !== false) {
						$flash[] = ['success', 'פרטי העסק עודכנו בהצלחה'];
					} else {
						$flash[] = ['danger', bizpanel_db_error_message()];
						$stash['reopen'] = true;
					}
				}
			}
		}
	}

	// ── Delete-business handler (owner / L1 / scoped L2) ────────────────────
	elseif ($biz_post_action === 'delete_business') {

		$biz_id = (int)($_POST['biz_id'] ?? 0);

		$bq  = $biz_id > 0 ? db_query("SELECT * FROM mod_biz WHERE id = :id", ['id' => $biz_id]) : false;
		$biz = $bq ? db_fetch_array($bq, PDO::FETCH_ASSOC) : false;

		if (!$biz || !site_member_can_manage($USER_LOGIN, $biz)) {
			$flash[] = ['danger', 'אין הרשאה למחוק עסק זה'];
		} else {
			$ok = db_query("DELETE FROM mod_biz WHERE id = :id", ['id' => $biz_id]);
			if ($ok !== false) {
				@db_query("DELETE FROM mod_biz_leads WHERE biz_id = :id", ['id' => $biz_id]);
				@db_query("DELETE FROM mod_seo WHERE mod = 'products' AND id = :id", ['id' => $biz_id]);
				$flash[] = ['success', 'העסק נמחק בהצלחה'];
			} else {
				$flash[] = ['danger', bizpanel_db_error_message()];
			}
		}
	}

	if (!empty($stash['reopen'])) {
		$_SESSION['bizpanel_biz_form'] = $stash;
	}
	bizpanel_redirect_self($flash);
}

function PAGE_TEXT(){

global $USER_LOGIN, $biz_add_msg, $biz_add_error, $biz_edit_open, $biz_form;

$agent_id = (int)$USER_LOGIN['id'];

$get_businesses = db_query("
	SELECT *
	FROM mod_biz
	WHERE agent_id = '".$agent_id."'
	ORDER BY id DESC
");

$total_businesses = db_num_rows($get_businesses);

$get_leads = db_query("
	SELECT mod_biz_leads.*
	FROM mod_biz_leads
	INNER JOIN mod_biz ON mod_biz_leads.biz_id = mod_biz.id
	WHERE mod_biz.agent_id = '".$agent_id."'
");

$total_leads = db_num_rows($get_leads);

// Points come from the auditable affiliate/points ledger (credited per inquiry
// that arrives through an affiliate link). Falls back to a per-lead estimate if
// the ledger helper is not available yet.
if (function_exists('site_member_points')) {
    $total_points = site_member_points($agent_id);
} else {
    $total_points = $total_leads * (defined('WPX_SITE_POINTS_PER_LEAD') ? (int)WPX_SITE_POINTS_PER_LEAD : 15);
}

// Estimated profit reflects real activity only (no fake per-business baseline):
// each inquiry is valued at an estimated ₪35. Zero inquiries → ₪0.
$value_per_lead = 35;
$total_profit   = $total_leads * $value_per_lead;

// Affiliate identity for this member (for the share panel).
$aff_code = function_exists('site_member_aff_code') ? site_member_aff_code($agent_id) : '';
$pts_per_lead = defined('WPX_SITE_POINTS_PER_LEAD') ? (int)WPX_SITE_POINTS_PER_LEAD : 15;

$__user_pic = 'resources/design/images/coment-avatar-3.jpg';
$__pic = (string)($USER_LOGIN['pic'] ?? '');
if ($__pic !== '') {
	if (file_exists(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . '/files/user_pic/' . $__pic)) {
		$__user_pic = '/files/user_pic/' . $__pic;
	} elseif (file_exists(rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') . '/' . $__pic)) {
		$__user_pic = '/' . ltrim($__pic, '/');
	}
}

?>

<style>
.biz-member-header .avatar-upload img{width:72px;height:72px;border-radius:50%;object-fit:cover}
.biz-member-header .settings-header-meta span{background:rgba(96,165,250,0.12);border-radius:999px;padding:6px 14px;font-size:13px;font-weight:700}
.biz-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:30px}
.biz-stat-card{border-radius:24px;padding:28px;color:#fff}
.bg-purple{background:linear-gradient(135deg,#7c3aed,#4f46e5)}
.bg-blue{background:linear-gradient(135deg,#2563eb,#06b6d4)}
.bg-green{background:linear-gradient(135deg,#059669,#10b981)}
.bg-orange{background:linear-gradient(135deg,#ea580c,#f59e0b)}
.biz-panel{background:#fff;border-radius:24px;padding:30px;margin-bottom:30px;box-shadow:0 10px 30px rgba(0,0,0,0.06)}
.biz-table{width:100%;border-collapse:collapse}
.biz-table th,.biz-table td{padding:16px;border-top:1px solid #e5e7eb;text-align:right;vertical-align:middle}
.biz-logo{width:55px;height:55px;object-fit:cover;border-radius:14px;display:block}
.biz-logo-ph{width:55px;height:55px;border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;background:linear-gradient(135deg,#7c3aed,#4f46e5)}
.lead-card{background:#f8fafc;border-radius:20px;padding:20px;margin-bottom:15px}
.lead-card h4{margin:0 0 8px}
.lead-meta{color:#6b7280;font-size:13px;margin-bottom:8px}
.biz-empty{background:#f8fafc;border:1px dashed #d1d5db;border-radius:18px;padding:30px;text-align:center;color:#6b7280}
.biz-panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:12px}
.biz-panel-head h3{margin:0}
.biz-add-btn{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border:none;border-radius:14px;padding:12px 22px;font-size:15px;font-weight:bold;cursor:pointer}
.biz-add-btn:hover{opacity:.92}
.biz-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.biz-act-btn{border:none;border-radius:10px;padding:8px 14px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block}
.biz-act-edit{background:#e0e7ff;color:#3730a3}
.biz-act-del{background:#fee2e2;color:#991b1b}
.biz-act-edit:hover,.biz-act-del:hover{opacity:.85}
.biz-msg{border-radius:14px;padding:14px 18px;margin-bottom:20px;font-weight:bold}
.biz-msg.ok{background:#dcfce7;color:#166534}
.biz-msg.err{background:#fee2e2;color:#991b1b}
.biz-share-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:14px 0;border-top:1px solid #e5e7eb}
.biz-share-row .name{font-weight:700;min-width:140px}
.biz-share-input{flex:1;min-width:180px;direction:ltr;text-align:left;border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font-size:13px;font-family:monospace}
.biz-share-copy{background:#4f46e5;color:#fff;border:none;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer}
.biz-share-wa{background:#22c55e;color:#fff;border:none;border-radius:10px;padding:9px 16px;font-weight:700;cursor:pointer;text-decoration:none}
.biz-aff-meta{background:#f8fafc;border-radius:16px;padding:16px 20px;margin-bottom:8px;display:flex;gap:24px;flex-wrap:wrap}
.biz-aff-meta b{color:#111827}
.biz-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:flex-start;justify-content:center;padding:40px 16px;overflow:auto}
.biz-modal-overlay.open{display:flex}
.biz-modal{background:#fff;border-radius:24px;padding:30px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.biz-modal h3{margin-top:0}
.biz-field{margin-bottom:16px}
.biz-field label{display:block;margin-bottom:6px;font-weight:bold;color:#374151}
.biz-field input[type=text],.biz-field input[type=email],.biz-field input[type=file],.biz-field textarea{width:100%;padding:12px 14px;border:1px solid #d1d5db;border-radius:12px;font-size:15px;box-sizing:border-box;font-family:inherit}
.biz-modal-actions{display:flex;gap:12px;justify-content:flex-start;margin-top:10px}
.biz-btn-cancel{background:#e5e7eb;color:#374151;border:none;border-radius:12px;padding:12px 22px;cursor:pointer;font-weight:bold}
.biz-current-logo{font-size:13px;color:#6b7280;margin-top:6px}
</style>

<div class="member-dashboard profile-settings">
<div class="dashboard-layout">

<?php $ACCOUNT_NAV_ACTIVE = 'business'; include __DIR__ . "/inc/account_sidebar.php"; ?>

<div class="dashboard-main">

<div class="settings-header biz-member-header">
  <div class="avatar-upload">
    <img src="<?php echo htmlspecialchars($__user_pic); ?>" alt="תמונת משתמש" />
  </div>
  <div class="settings-header-content">
    <span>פאנל עסקים</span>
    <h2>ניהול עסקים וסוכנים</h2>
    <p><?php echo htmlspecialchars($USER_LOGIN['username'] ?? ''); ?></p>
  </div>
  <div class="settings-header-meta">
    <span>נקודות: <?php echo number_format($total_points); ?></span>
    <span>עסקים: <?php echo (int)$total_businesses; ?></span>
    <span>פניות: <?php echo (int)$total_leads; ?></span>
  </div>
</div>

<?php if (!empty($biz_add_msg)) { ?>
<div class="biz-msg <?php echo $biz_add_error ? 'err' : 'ok'; ?>"><?php echo htmlspecialchars($biz_add_msg); ?></div>
<?php } ?>

<div class="biz-stats">

<div class="biz-stat-card bg-purple">
<h4>סה״כ עסקים</h4>
<strong><?php echo $total_businesses; ?></strong>
</div>

<div class="biz-stat-card bg-blue">
<h4>סה״כ פניות</h4>
<strong><?php echo $total_leads; ?></strong>
</div>

<div class="biz-stat-card bg-green">
<h4>נקודות שנצברו</h4>
<strong><?php echo number_format($total_points); ?></strong>
</div>

<div class="biz-stat-card bg-orange">
<h4>רווח משוער</h4>
<strong>₪<?php echo number_format($total_profit); ?></strong>
</div>

</div>

<div class="biz-panel">

<div class="biz-panel-head">
<h3>רשימת העסקים שלי</h3>
<button type="button" class="biz-add-btn" onclick="bizOpenAdd()">+ הוסף עסק</button>
</div>

<table class="biz-table">

<tr>
<th>לוגו</th>
<th>שם העסק</th>
<th>טלפון</th>
<th>כתובת</th>
<th>פניות</th>
<th>פעולות</th>
</tr>

<?php

$get_businesses_loop = db_query("
	SELECT *
	FROM mod_biz
	WHERE agent_id = '".$agent_id."'
	ORDER BY id DESC
");

$has_business = false;

while($biz = db_fetch_array($get_businesses_loop, PDO::FETCH_ASSOC)){

$has_business = true;

$biz_leads = db_query("
	SELECT COUNT(id) as total
	FROM mod_biz_leads
	WHERE biz_id = '".$biz['id']."'
");

$gl = db_fetch_array($biz_leads);

$logo_src = site_logo_src($biz['logofile'] ?? '');
$initial  = mb_strtoupper(mb_substr(trim((string)$biz['name']), 0, 1, 'UTF-8'), 'UTF-8');

?>

<tr>

<td>
<?php if ($logo_src !== '') { ?>
<img class="biz-logo" src="<?php echo htmlspecialchars($logo_src); ?>" alt="<?php echo htmlspecialchars($biz['name']); ?>">
<?php } else { ?>
<div class="biz-logo-ph"><?php echo htmlspecialchars($initial !== '' ? $initial : '★'); ?></div>
<?php } ?>
</td>

<td><strong><?php echo htmlspecialchars($biz['name']); ?></strong></td>
<td><?php echo htmlspecialchars($biz['phone']); ?></td>
<td><?php echo htmlspecialchars($biz['address']); ?></td>
<td><?php echo (int)$gl['total']; ?></td>

<td>
<div class="biz-row-actions">
<button type="button" class="biz-act-btn biz-act-edit"
	onclick="bizOpenEdit(this)"
	data-id="<?php echo (int)$biz['id']; ?>"
	data-name="<?php echo htmlspecialchars($biz['name'], ENT_QUOTES); ?>"
	data-phone="<?php echo htmlspecialchars((string)($biz['phone'] ?? ''), ENT_QUOTES); ?>"
	data-address="<?php echo htmlspecialchars((string)($biz['address'] ?? ''), ENT_QUOTES); ?>"
	data-email="<?php echo htmlspecialchars((string)($biz['email'] ?? ''), ENT_QUOTES); ?>"
	data-desc="<?php echo htmlspecialchars((string)($biz['desc'] ?? ''), ENT_QUOTES); ?>"
	data-logo="<?php echo htmlspecialchars((string)($biz['logofile'] ?? ''), ENT_QUOTES); ?>">ערוך</button>
<form method="post" style="margin:0;display:inline;" onsubmit="return confirm('למחוק את העסק לצמיתות? פעולה זו אינה ניתנת לשחזור.');">
<input type="hidden" name="biz_action" value="delete_business">
<input type="hidden" name="biz_id" value="<?php echo (int)$biz['id']; ?>">
<button type="submit" class="biz-act-btn biz-act-del">מחק</button>
</form>
</div>
</td>

</tr>

<?php } ?>

<?php if (!$has_business) { ?>
<tr><td colspan="6"><div class="biz-empty">עדיין לא הוספת עסקים. לחצו על "הוסף עסק" כדי להתחיל.</div></td></tr>
<?php } ?>

</table>

</div>

<?php
// ── Affiliate share panel — share each business and earn points per inquiry ──
$get_biz_share = db_query("
	SELECT *
	FROM mod_biz
	WHERE agent_id = '".$agent_id."'
	ORDER BY id DESC
");
$share_rows = [];
while ($b = db_fetch_array($get_biz_share, PDO::FETCH_ASSOC)) { $share_rows[] = $b; }

if ($aff_code !== '' && function_exists('site_affiliate_link')) {
    $add_biz_link = function_exists('site_affiliate_add_business_link')
        ? site_affiliate_add_business_link($aff_code) : '';
?>
<div class="biz-panel">
<h3>שיתוף וצבירת נקודות</h3>
<div class="biz-aff-meta">
<span>קוד השותף שלך: <b><?php echo htmlspecialchars($aff_code); ?></b></span>
<span>נקודות שנצברו: <b><?php echo number_format($total_points); ?></b></span>
<span>כל פנייה דרך הקישור מזכה ב־<b><?php echo $pts_per_lead; ?></b> נקודות</span>
<?php if ($total_points > 0): ?>
<a href="?export=csv" class="biz-act-btn biz-act-edit" style="margin-right:auto;">ייצוא נקודות (CSV)</a>
<?php endif; ?>
</div>
<?php if ($add_biz_link !== '') {
	$add_biz_wa = 'היי! הוסף את העסק שלך לביזפלייס דרך הקישור שלי: ' . $add_biz_link;
?>
<h4 style="margin:20px 0 8px;font-size:17px">שתף קישור להוספת עסק</h4>
<p style="font-size:13px;color:#6b7280;margin:0 0 12px;">כשמישהו מוסיף עסק דרך הקישור שלך, תקבל נקודות על כל פנייה שהעסק מקבל.</p>
<div class="biz-share-row" style="border-top:none;padding-top:0;">
<span class="name">הוספת עסק</span>
<input type="text" class="biz-share-input" value="<?php echo htmlspecialchars($add_biz_link); ?>" readonly onclick="this.select()">
<button type="button" class="biz-share-copy" onclick="bizCopyShare(this)">העתק</button>
<a class="biz-share-wa" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo rawurlencode($add_biz_wa); ?>">וואטסאפ</a>
</div>
<?php } ?>
<?php if (!empty($share_rows)) { ?>
<h4 style="margin:24px 0 8px;font-size:17px">שתף עסק קיים</h4>
<?php } ?>
<?php if (empty($share_rows)) { ?>
<p style="color:#6b7280;">עדיין אין עסקים — הוסיפו עסק או שתפו את קישור ההוספה.</p>
<?php } ?>
<?php foreach ($share_rows as $b) {
	$link = site_affiliate_link((int)$b['id'], $aff_code);
?>
<div class="biz-share-row">
<span class="name"><?php echo htmlspecialchars($b['name']); ?></span>
<input type="text" class="biz-share-input" value="<?php echo htmlspecialchars($link); ?>" readonly onclick="this.select()">
<button type="button" class="biz-share-copy" onclick="bizCopyShare(this)">העתק</button>
<a class="biz-share-wa" target="_blank" rel="noopener" href="https://wa.me/?text=<?php echo rawurlencode($link); ?>">וואטסאפ</a>
</div>
<?php } ?>
</div>
<?php } ?>

<div class="biz-panel">

<h3>פניות אחרונות</h3>

<?php

$get_latest_leads = db_query("
	SELECT mod_biz_leads.*, mod_biz.name as biz_name
	FROM mod_biz_leads
	INNER JOIN mod_biz ON mod_biz_leads.biz_id = mod_biz.id
	WHERE mod_biz.agent_id = '".$agent_id."'
	ORDER BY mod_biz_leads.id DESC
	LIMIT 20
");

$has_lead = false;

while($lead = db_fetch_array($get_latest_leads, PDO::FETCH_ASSOC)){

$has_lead = true;

$lead_date = '';
if (!empty($lead['created_at']) && $lead['created_at'] !== '0000-00-00 00:00:00') {
	$ts = strtotime((string)$lead['created_at']);
	if ($ts) { $lead_date = date('d/m/Y H:i', $ts); }
}

?>

<div class="lead-card">

<h4><?php echo htmlspecialchars($lead['biz_name']); ?></h4>

<?php if ($lead_date !== '') { ?>
<div class="lead-meta"><?php echo htmlspecialchars($lead_date); ?></div>
<?php } ?>

<p>
<strong><?php echo htmlspecialchars($lead['full_name']); ?></strong><br>
<?php echo htmlspecialchars($lead['phone']); ?><br>
<?php echo htmlspecialchars($lead['email']); ?>
</p>

<?php if (trim((string)($lead['message'] ?? '')) !== '') { ?>
<div>
<?php echo nl2br(htmlspecialchars($lead['message'])); ?>
</div>
<?php } ?>

</div>

<?php } ?>

<?php if (!$has_lead) { ?>
<div class="biz-empty">אין עדיין פניות. שתפו את קישור השותף של העסק כדי לקבל פניות ולצבור נקודות.</div>
<?php } ?>

</div>

</div>
</div>

<!-- Add / edit business modal -->
<?php
$__modal_action = (string) ($biz_form['action'] ?? '');
if ($__modal_action !== 'edit_business') {
	$__modal_action = 'add_business';
}
$__is_edit = ($__modal_action === 'edit_business');
$__fv = static function (string $key) use ($biz_form): string {
	if (isset($biz_form[$key])) {
		return (string) $biz_form[$key];
	}
	return (string) ($_POST[$key] ?? '');
};
?>
<div class="biz-modal-overlay" id="bizAddModal">
<div class="biz-modal">
<h3 id="bizModalTitle"><?php echo $__is_edit ? 'עריכת עסק' : 'הוספת עסק חדש'; ?></h3>
<form method="post" action="" enctype="multipart/form-data">
<input type="hidden" name="biz_action" id="bizActionInput" value="<?php echo htmlspecialchars($__modal_action, ENT_QUOTES, 'UTF-8'); ?>">
<input type="hidden" name="biz_id" id="bizIdInput" value="<?php echo htmlspecialchars($__fv('biz_id'), ENT_QUOTES, 'UTF-8'); ?>">
<?php
$__modal_aff = function_exists('site_current_affiliate_code') ? site_current_affiliate_code() : '';
if ($__modal_aff !== '') {
	echo '<input type="hidden" name="aff" value="' . htmlspecialchars($__modal_aff, ENT_QUOTES, 'UTF-8') . '">';
}
?>

<div class="biz-field">
<label>שם העסק *</label>
<input type="text" name="biz_name" id="bizNameInput" value="<?php echo htmlspecialchars($__fv('biz_name'), ENT_QUOTES, 'UTF-8'); ?>" required>
</div>

<div class="biz-field">
<label>טלפון *</label>
<input type="text" name="biz_phone" id="bizPhoneInput" value="<?php echo htmlspecialchars($__fv('biz_phone'), ENT_QUOTES, 'UTF-8'); ?>" required>
</div>

<div class="biz-field">
<label>כתובת</label>
<input type="text" name="biz_address" id="bizAddressInput" value="<?php echo htmlspecialchars($__fv('biz_address'), ENT_QUOTES, 'UTF-8'); ?>">
</div>

<div class="biz-field">
<label>אימייל</label>
<input type="email" name="biz_email" id="bizEmailInput" value="<?php echo htmlspecialchars($__fv('biz_email'), ENT_QUOTES, 'UTF-8'); ?>">
</div>

<div class="biz-field">
<label>תיאור</label>
<textarea name="biz_desc" id="bizDescInput" rows="3"><?php echo htmlspecialchars($__fv('biz_desc'), ENT_QUOTES, 'UTF-8'); ?></textarea>
</div>

<div class="biz-field">
<label>לוגו</label>
<input type="file" name="biz_logo" accept="image/*">
<div class="biz-current-logo" id="bizCurrentLogo" style="display:none;"></div>
</div>

<div class="biz-modal-actions">
<button type="submit" class="biz-add-btn" id="bizSubmitBtn"><?php echo $__is_edit ? 'שמור שינויים' : 'שמור עסק'; ?></button>
<button type="button" class="biz-btn-cancel" onclick="bizCloseAdd()">ביטול</button>
</div>

</form>
</div>
</div>

<script>
function bizOpenAdd(){
	document.getElementById('bizModalTitle').innerText='הוספת עסק חדש';
	document.getElementById('bizActionInput').value='add_business';
	document.getElementById('bizIdInput').value='';
	document.getElementById('bizNameInput').value='';
	document.getElementById('bizPhoneInput').value='';
	document.getElementById('bizAddressInput').value='';
	document.getElementById('bizEmailInput').value='';
	document.getElementById('bizDescInput').value='';
	document.getElementById('bizSubmitBtn').innerText='שמור עסק';
	document.getElementById('bizCurrentLogo').style.display='none';
	var m=document.getElementById('bizAddModal');if(m){m.classList.add('open');}
}
function bizOpenEdit(btn){
	document.getElementById('bizModalTitle').innerText='עריכת עסק';
	document.getElementById('bizActionInput').value='edit_business';
	document.getElementById('bizIdInput').value=btn.getAttribute('data-id')||'';
	document.getElementById('bizNameInput').value=btn.getAttribute('data-name')||'';
	document.getElementById('bizPhoneInput').value=btn.getAttribute('data-phone')||'';
	document.getElementById('bizAddressInput').value=btn.getAttribute('data-address')||'';
	document.getElementById('bizEmailInput').value=btn.getAttribute('data-email')||'';
	document.getElementById('bizDescInput').value=btn.getAttribute('data-desc')||'';
	document.getElementById('bizSubmitBtn').innerText='שמור שינויים';
	var lg=btn.getAttribute('data-logo')||'';var cl=document.getElementById('bizCurrentLogo');
	if(lg){cl.innerText='לוגו נוכחי: '+lg+' (העלאת קובץ חדש תחליף אותו)';cl.style.display='block';}else{cl.style.display='none';}
	var m=document.getElementById('bizAddModal');if(m){m.classList.add('open');}
}
function bizCloseAdd(){var m=document.getElementById('bizAddModal');if(m){m.classList.remove('open');}}
function bizCopyShare(btn){
	var inp=btn.parentNode.querySelector('.biz-share-input');
	if(!inp)return;inp.select();inp.setSelectionRange(0,99999);
	try{document.execCommand('copy');var t=btn.innerText;btn.innerText='הועתק!';setTimeout(function(){btn.innerText=t;},1500);}catch(e){}
}
<?php if (!empty($biz_add_error) || !empty($biz_edit_open) || !empty($biz_form['reopen'])) { ?>
// Re-open the modal preserving the values the member just entered (PHP-rendered
// above). The hidden action/biz_id keep add vs edit context across the error.
document.getElementById('bizAddModal').classList.add('open');
<?php } ?>
</script>

</div>

<?php

}

}

?>
