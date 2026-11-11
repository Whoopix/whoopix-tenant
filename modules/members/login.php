<?php
	if($USER_LOGIN!=false)
		header("Location: ,members");
	$TEMP_PAGE_TITLE = "התחברות";
	$TEMP_INPAGE_TITLE = "התחברות";
	$TEMP_TEMPLATE = 5;
	$error  = true;
	$err    = '';
	$Vemail = (string) ($_POST['email'] ?? '');
	if (($_POST['sub'] ?? '') === 'SUBTRUE') {
		$I_pass  = md5((string) ($_POST['pass']  ?? ''));
		$I_email = (string) ($_POST['email'] ?? '');
		$LOG_q = db_query(
			"SELECT * FROM `mod_members` WHERE `email` = :e AND `pass` = :p",
			['e' => $I_email, 'p' => $I_pass],
		);
		if (db_num_rows($LOG_q) > 0) {
			$error = false;
			$LOG_r = db_fetch_array($LOG_q);
			$_SESSION['user'] = intval($LOG_r['id']) . '_' . $LOG_r['pass'];
			if (intval($_POST['rem'] ?? 0) > 0) {
				setcookie('USER[id]',   (string) $LOG_r['id'],   time() + 60 * 60 * 24 * 31 * 12);
				setcookie('USER[pass]', (string) $LOG_r['pass'], time() + 60 * 60 * 24 * 31 * 12);
			}
			$USER_LOGIN = loggedIn();
			if (!empty($_POST['ret'])) {
				header('Location: ' . $_POST['ret']);
			} else {
				header('Location: index.php');
			}
		} else {
			$err = 'הפרטים אשר הוזנו אינם נכונים';
		}
	}
	if($error){
		function PAGE_TEXT(){
			global $err, $Vemail;
?>
<h1 class="site-content-block__title">התחברות</h1>
<p class="site-content-block__lead">
	התחברו לניהול העסק שלכם, קבלו פניות מלקוחות ונהלו את הפרופיל באינדקס העסקים המוביל בישראל.
</p>

<?php if (trim($err) !== '') { ?>
<div class="site-content-block__error"><?php echo htmlspecialchars($err); ?></div>
<?php } ?>

<form class="auth-form" action=",members" method="post" style="max-width:420px;">
	<div class="mb-3">
		<label for="loginEmail" class="form-label">כתובת אימייל</label>
		<input type="email" class="form-control" id="loginEmail" name="email" placeholder="contact@example.com" value="<?php echo htmlspecialchars($Vemail); ?>" required />
	</div>
	<div class="mb-3">
		<div class="d-flex justify-content-between align-items-center">
			<label for="loginPass" class="form-label">סיסמה</label>
			<a href=",members,pass" class="auth-link">שכחת סיסמה?</a>
		</div>
		<input type="password" class="form-control" id="loginPass" name="pass" placeholder="הכנס סיסמה" required />
	</div>
	<div class="form-check mb-4">
		<input class="form-check-input" type="checkbox" value="1" id="rememberMe" name="rem" />
		<label class="form-check-label" for="rememberMe">השאר אותי מחובר</label>
	</div>
	<input type="hidden" name="sub" value="SUBTRUE" />
	<button type="submit" class="btn btn-primary w-100">התחברות</button>
</form>

<p class="site-content-block__switch">
	עדיין אין לך חשבון? <a href=",members,register">יצירת חשבון</a>
</p>
<a href=",members,register" class="site-content-block__cta">הוספת העסק שלכם &rsaquo;</a>

<?php
		}
	}

?>
