<?php
/* Site member account — הודעות פרטיות (Messages)
 * Routed at  ,members,messages
 *
 * Replaces legacy member-to-member messaging UI (short_open_tag + brace bugs
 * caused a PHP parse error on PHP 8 with short_open_tag=Off). This version
 * uses the shared account layout and lists recent messages when available. */

if (!is_array($USER_LOGIN)) {
	include "login.php";
} else {
	header('Location: ,members');
	exit;

	$TEMP_PAGE_TITLE   = "הודעות פרטיות";
	$TEMP_INPAGE_TITLE = "הודעות פרטיות";

	function PAGE_TEXT() {
		global $USER_LOGIN;

		$uid = (int) $USER_LOGIN['id'];
		$messages = db_query(
			"SELECT m.*,
			        sender.username AS sender_name,
			        recipient.username AS recipient_name
			   FROM mod_members_messages m
			   LEFT JOIN mod_members sender ON m.member_id = sender.id
			   LEFT JOIN mod_members recipient ON m.member_to = recipient.id
			  WHERE m.member_id = :uid OR m.member_to = :uid
			  ORDER BY m.id DESC
			  LIMIT 50",
			['uid' => $uid]
		);
		$count = db_num_rows($messages);
		?>
  <div class="member-dashboard profile-settings">
      <div class="dashboard-layout">

        <?php $ACCOUNT_NAV_ACTIVE = 'messages'; include __DIR__ . "/inc/account_sidebar.php"; ?>

        <div class="dashboard-main">
          <div class="settings-header">
            <div class="settings-header-content">
              <span>החשבון שלי</span>
              <h2>הודעות פרטיות</h2>
              <p>הודעות בין חברי האתר.</p>
            </div>
          </div>

          <?php if ($count < 1) { ?>
            <div class="member-empty-state" style="padding:60px 30px;text-align:center;">
              <i class="fa-solid fa-envelope" style="font-size:46px;color:#e5e7eb;"></i>
              <h3 style="margin-top:18px;">אין הודעות</h3>
              <p style="color:#6b7280;max-width:420px;margin:10px auto 0;">
                עדיין לא התקבלו הודעות. כשחבר אחר ישלח לך הודעה, היא תופיע כאן.
              </p>
            </div>
          <?php } else { ?>
            <div style="background:#fff;border-radius:24px;padding:24px 28px;box-shadow:0 10px 30px rgba(0,0,0,0.06);">
              <ul style="list-style:none;margin:0;padding:0;">
                <?php while ($row = db_fetch_array($messages)) {
                  $is_out = ((int) $row['member_id'] === $uid);
                  $peer   = $is_out ? ($row['recipient_name'] ?? '') : ($row['sender_name'] ?? '');
                  $when   = htmlspecialchars((string) ($row['time'] ?? ''));
                  $text   = htmlspecialchars((string) ($row['text'] ?? ''));
                  $dir    = $is_out ? 'אל' : 'מ';
                ?>
                <li style="border-bottom:1px solid #f3f4f6;padding:16px 0;">
                  <div style="font-size:13px;color:#6b7280;margin-bottom:6px;">
                    <?php echo $dir . ' ' . htmlspecialchars($peer); ?>
                    <?php if ($when !== '') { ?> · <?php echo $when; ?><?php } ?>
                  </div>
                  <div style="color:#111827;line-height:1.5;"><?php echo $text; ?></div>
                </li>
                <?php } ?>
              </ul>
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
