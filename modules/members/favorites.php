<?php
/* Site member account — מועדפים (Favorites)
 * Routed at  ,members,favorites
 *
 * No favorites/saved-businesses data model exists yet in the platform, so this
 * renders a clean, styled empty-state section (instead of a dead/404 link).
 * When a favorites mechanism is added (e.g. a mod_biz_favorites table), the
 * empty-state below can be replaced with the saved-business list. */

if (!is_array($USER_LOGIN)) {
	include "login.php";
} else {
	header('Location: ,members');
	exit;

	$TEMP_PAGE_TITLE   = "מועדפים";
	$TEMP_INPAGE_TITLE = "מועדפים";

	function PAGE_TEXT() {
		global $USER_LOGIN;
		?>
  <div class="member-dashboard profile-settings">
      <div class="dashboard-layout">

        <?php $ACCOUNT_NAV_ACTIVE = 'favorites'; include __DIR__ . "/inc/account_sidebar.php"; ?>

        <div class="dashboard-main">
          <div class="settings-header">
            <div class="settings-header-content">
              <span>החשבון שלי</span>
              <h2>מועדפים</h2>
              <p>כאן יוצגו העסקים שסימנת כמועדפים לגישה מהירה.</p>
            </div>
          </div>

          <div class="member-empty-state" style="padding:60px 30px;text-align:center;">
            <i class="fa-solid fa-heart" style="font-size:46px;color:#e5e7eb;"></i>
            <h3 style="margin-top:18px;">אין מועדפים עדיין</h3>
            <p style="color:#6b7280;max-width:420px;margin:10px auto 0;">
              עדיין לא הוספת עסקים למועדפים. גלה עסקים באינדקס וסמן אותם כמועדפים כדי שיופיעו כאן.
            </p>
            <a href=",pages,1" class="btn btn-primary" style="margin-top:22px;display:inline-block;">
              <i class="fa-solid fa-magnifying-glass"></i> עיון בעסקים
            </a>
          </div>
        </div>
      </div>
  </div>
<?php
	}
	$TEMP_TEMPLATE = 5;
}
?>
