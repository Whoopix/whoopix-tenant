<?php
/* Site member account — התראות (Notifications)
 * Routed at  ,members,notifications
 *
 * Wired to REAL data: surfaces the most recent inquiries (mod_biz_leads) that
 * arrived for the member's own businesses (mod_biz.agent_id = current member).
 * Falls back to a clean empty-state when there are no inquiries yet. */

if (!is_array($USER_LOGIN)) {
	include "login.php";
} else {
	header('Location: ,members');
	exit;

	$TEMP_PAGE_TITLE   = "התראות";
	$TEMP_INPAGE_TITLE = "התראות";

	function PAGE_TEXT() {
		global $USER_LOGIN;

		$agent_id = (int) $USER_LOGIN['id'];

		$leads = db_query(
			"SELECT l.*, b.name AS biz_name
			 FROM mod_biz_leads l
			 INNER JOIN mod_biz b ON l.biz_id = b.id
			 WHERE b.agent_id = :a
			 ORDER BY l.id DESC
			 LIMIT 30",
			['a' => $agent_id]
		);

		$count = db_num_rows($leads);
		?>
  <div class="member-dashboard profile-settings">
      <div class="dashboard-layout">

        <?php $ACCOUNT_NAV_ACTIVE = 'notifications'; include __DIR__ . "/inc/account_sidebar.php"; ?>

        <div class="dashboard-main">
          <div class="settings-header">
            <div class="settings-header-content">
              <span>החשבון שלי</span>
              <h2>התראות</h2>
              <p>פניות ועדכונים אחרונים שהתקבלו עבור העסקים שלך.</p>
            </div>
          </div>

          <?php if ($count < 1) { ?>
            <div style="background:#fff;border-radius:24px;padding:60px 30px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
              <i class="fa-solid fa-bell" style="font-size:46px;color:#e5e7eb;"></i>
              <h3 style="margin-top:18px;">אין התראות עדיין</h3>
              <p style="color:#6b7280;max-width:420px;margin:10px auto 0;">
                כשלקוחות ישלחו פניות לעסקים שלך, ההתראות יופיעו כאן.
              </p>
              <a href=",members,business" class="btn btn-primary" style="margin-top:22px;display:inline-block;">
                <i class="fa-solid fa-briefcase"></i> לניהול העסקים
              </a>
            </div>
          <?php } else { ?>
            <div style="display:flex;flex-direction:column;gap:14px;">
              <?php while ($lead = db_fetch_array($leads)) { ?>
                <div style="background:#fff;border-radius:18px;padding:20px 22px;box-shadow:0 8px 24px rgba(0,0,0,0.05);display:flex;gap:16px;align-items:flex-start;">
                  <div style="flex:0 0 46px;height:46px;width:46px;border-radius:14px;background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-envelope-open-text"></i>
                  </div>
                  <div style="flex:1;">
                    <div style="font-weight:700;margin-bottom:4px;">
                      פנייה חדשה לעסק: <?= htmlspecialchars((string)$lead['biz_name']) ?>
                    </div>
                    <div style="color:#374151;">
                      <strong><?= htmlspecialchars((string)($lead['full_name'] ?? '')) ?></strong>
                      <?php if (!empty($lead['phone'])) { ?> · <?= htmlspecialchars((string)$lead['phone']) ?><?php } ?>
                      <?php if (!empty($lead['email'])) { ?> · <?= htmlspecialchars((string)$lead['email']) ?><?php } ?>
                    </div>
                    <?php if (!empty($lead['message'])) { ?>
                      <div style="color:#6b7280;margin-top:6px;">
                        <?= nl2br(htmlspecialchars((string)$lead['message'])) ?>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </div>
  </div>
<?php
	}
	$TEMP_TEMPLATE = 5;
}
?>
