<?php


if (!defined('SITEDOMAIN')) {
    define('SITEDOMAIN', $_SERVER['HTTP_HOST']);
}
	if($USER_LOGIN!=false)
	header("Location: ,members");

$TEMP_PAGE_TITLE = "הרשמה";
$TEMP_INPAGE_TITLE = "הרשמה";
//$GLOBALS["members"]="alert('asd');";

$error=true;
$err="";

if (($_POST["sub"] ?? '') === "SUBTRUE") {

	$error=false;

	$Pname=db_escape(htmlspecialchars((string)($_POST["name"] ?? '')));
	$Pemail=db_escape(htmlspecialchars((string)($_POST["email"] ?? '')));
	$Pusername=db_escape(htmlspecialchars((string)($_POST["username"] ?? '')));
	// Username is no longer collected in the form; default it to the email so login/identity still works.
	if (trim($Pusername) === '') { $Pusername = $Pemail; }
	$Pphone=db_escape((string)($_POST["phone"] ?? ''));
	$Pcell=db_escape((string)($_POST["cell"] ?? ''));

	$Pfname=db_escape(htmlspecialchars((string)($_POST["fname"] ?? '')));
	$Psex = (string)($_POST['sex'] ?? '');

	// ================= FIXED PIC =================

	$pic_url = 'default.png';
	$file_size = 0;

	if (isset($_FILES['pic']) && !empty($_FILES['pic']['name'])) {

		$file_name = $_FILES['pic']['name'];
		$file_size = $_FILES['pic']['size'];
		$file_tmp  = $_FILES['pic']['tmp_name'];
		$file_type = $_FILES['pic']['type'];

		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		$expensions = array("jpeg","jpg","png");
		$random = md5(uniqid("") . time());

		if(!in_array($file_ext,$expensions)){
			$error=true;
			$err="סיומת תמונה לא חוקית - ניתן להעלות רק JPG או PNG";
		}
		elseif($file_size > 7680000){
			$error=true;
			$err="גודל תמונה מירבי הוא 7MB";
		}
		else{
			move_uploaded_file($file_tmp,"files/user_pic/".$random.$file_name);
			$pic_url = $random.$file_name;
		}

	} else {

		$dir = (($_POST['sex'] ?? '') == '1') ? 'files/random_user/male' : 'files/random_user/female';

		$files = glob($dir . '/*.*');

		if (!empty($files)) {
			$pic_url = $files[array_rand($files)];
		} else {
			$pic_url = 'default.png';
		}
	}

	// ================= END FIX =================



	$check_e=db_query("SELECT * FROM `mod_members` WHERE `email`='$Pemail'");

	if(trim($Pname)==""){
		$error=true;
		$err= "אנא מלא שם";
	}
	elseif(!preg_match("/^([a-zA-Z0-9]+)([a-zA-Z0-9\._\-]+)*@([a-zA-Z0-9])+([a-zA-Z0-9\-][a-zA-Z0-9]+)*(\.[a-zA-Z0-9]{2,3}){1,2}$/",$Pemail)){
		$error=true;
		$err= "אימייל לא תקין";
	}
	elseif(db_num_rows($check_e)>0){
		$error=true;
		$err= "דואר אלקטרוני קיים";
	}
	elseif(trim($_POST["pass"]) ==""){
		$error=true;
		$err= "אנא הכנס סיסמה";
	}
	elseif($_POST["pass"] != $_POST["pass2"]){
		$error=true;
		$err= "סיסמה לא תואמת";
	}
elseif(($_POST["terms"] ?? 0) != 1){
	$error=true;
	$err= "אנא הסכם לתנאי השימוש";
}

	if(!$error){

		$Pcode=gocode(8);
		$Ppass=md5($_POST["pass"]);
$Pnewsletter = intval($_POST['newsletter'] ?? 0);

db_query("INSERT INTO `mod_members`
(`pic`,`sex`,`fname`,`username`,`email`,`pass`,`name`,`birthday`,`phone`,`cell`,`newsletter`,`code`,`stat`,`about`,`mobile`,`type`,`bday_year`,`bday_month`,`bday_day`)
VALUES
('$pic_url','$Psex','$Pfname','$Pusername','$Pemail','$Ppass','$Pname','1','$Pphone','$Pcell','$Pnewsletter','$Pcode',0,'','1',0,'".intval($_POST['bday_year'] ?? 0)."','".intval($_POST['bday_month'] ?? 0)."','".intval($_POST['bday_day'] ?? 0)."')") or die(db_error());
		$block=get_block(2);

		$rawEmail = trim((string)($_POST['email'] ?? ''));
		$siteBase = function_exists('tenant_site_base_url')
			? tenant_site_base_url()
			: ('https://' . SITEDOMAIN . '/');
		$link = rtrim($siteBase, '/') . '/,members,email&code=' . urlencode($Pcode) . '&email=' . urlencode($rawEmail);

		$text=str_replace(
			array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
			array($Pemail,$Pcode,htmlspecialchars($_POST["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r['name']),SITEDOMAIN)
			,$block["text"]);

		$subject=str_replace(
			array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
			array($Pemail,$Pcode,htmlspecialchars($_POST["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r['name']),SITEDOMAIN)
			,$block["title"]);

		$fromLabel = htmlspecialchars((string)($GET_SITE_CONFIG_r['name'] ?? ''), ENT_QUOTES, 'UTF-8');
		$mailOk = send_mail(
			$rawEmail,
			$fromLabel . ' <noreply@' . preg_replace('/^www\./i', '', SITEDOMAIN) . '>',
			$subject,
			$text
		);
		if (!$mailOk) {
			error_log('members/register: verification email not sent for ' . $rawEmail);
		}

		function PAGE_TEXT() {
			
					
					?>
					<h1 class="site-content-block__title">הרשמה הושלמה</h1>
					<div class="site-content-block__body">
					<% BLOCK_4 %>
					</div>
					<a href=",members" class="site-content-block__cta">התחברות לחשבון &rsaquo;</a>
					<?php
				}
			}


		}
	if($error){
	function PAGE_TEXT() {
		global $error,$err;
		$Vname = $Vusername = $Vfname = $Vcell = $Vsex = $Vpassword = $Vpassword2 = $Vemail = '';
		$Vday = $Vmonth = $Vyear = $Vterms = $Vnewsletter = 0;
		if (($_POST["sub"] ?? '') === "SUBTRUE"){
			$Vname=($_POST["name"] ?? '');
			$Vusername=($_POST["username"] ?? '');
			$Vfname=($_POST["fname"] ?? '');
			$Vcell=($_POST["cell"] ?? '');
			$Vsex=($_POST["sex"] ?? '');
			$Vpassword=($_POST["password"] ?? '');
			$Vpassword2=($_POST["password2"] ?? '');
			$Vemail=($_POST["email"] ?? '');
			$Vday=intval($_POST["bday_day"] ?? 0);
			$Vmonth=intval($_POST["bday_month"] ?? 0);
			$Vyear=intval($_POST["bday_year"] ?? 0);
			$Vterms=intval($_POST["terms"] ?? 0);
			$Vnewsletter=intval($_POST["newsletter"] ?? 0);
		}
		if($error){//
		
		?>
						
<h1 class="site-content-block__title">יצירת חשבון</h1>
<p class="site-content-block__lead">
	הצטרפו לביזפלייס, פתחו פרופיל עסקי וקבלו פניות מלקוחות חדשים — ההרשמה חינם.
</p>

<?php if (trim($err) !== '') { ?>
<div class="site-content-block__error"><?php echo htmlspecialchars($err); ?></div>
<?php } ?>

<form class="auth-form site-register-form" action=",members,register" method="post" enctype="multipart/form-data" novalidate>
	<div class="auth-form__section">
		<div class="auth-form__legend">פרטי התחברות</div>
		<div class="mb-3">
			<label class="form-label" for="regEmail">כתובת אימייל</label>
			<input type="email" name="email" id="regEmail" class="form-control" placeholder="contact@example.com" value="<?=htmlspecialchars($Vemail)?>" autocomplete="email" required>
			<div class="auth-form__hint">נשתמש באימייל זה להתחברות ולעדכונים חשובים על החשבון.</div>
		</div>
		<div class="mb-3">
			<label class="form-label" for="regPass">סיסמה</label>
			<input type="password" name="pass" id="regPass" class="form-control" placeholder="בחרו סיסמה חזקה" autocomplete="new-password" minlength="6" required>
		</div>
		<div class="mb-0">
			<label class="form-label" for="regPass2">אימות סיסמה</label>
			<input type="password" name="pass2" id="regPass2" class="form-control" placeholder="הקלידו שוב את הסיסמה" autocomplete="new-password" minlength="6" required>
		</div>
	</div>

	<div class="auth-form__section">
		<div class="auth-form__legend">פרטים אישיים</div>
		<div class="mb-0">
			<label class="form-label" for="regName">שם מלא</label>
			<input type="text" name="name" id="regName" class="form-control" placeholder="שם פרטי ושם משפחה" value="<?=htmlspecialchars($Vname)?>" autocomplete="name" required>
		</div>
	</div>

	<div class="form-check form-check--terms">
		<input type="checkbox" name="terms" value="1" class="form-check-input" id="regTerms" <?=($Vterms==1?'checked':'')?> required>
		<label class="form-check-label" for="regTerms">
			אני מאשר/ת שקראתי את <a href=",pages,24" target="_blank" rel="noopener noreferrer">תנאי השימוש</a> של ביזפלייס
		</label>
	</div>

	<input type="hidden" name="sub" value="SUBTRUE">

	<button type="submit" class="btn btn-primary w-100 auth-form__submit">הרשמה</button>
</form>

<p class="site-content-block__switch">
	כבר יש לך חשבון? <a href=",members">התחברות</a>
</p>

<?php
	}
	}
	}
	$TEMP_TEMPLATE = 5;

?>
