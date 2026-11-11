<?php
	if($USER_LOGIN!=false)
		header("Location: ,members");

	$TEMP_PAGE_TITLE = "שכחתי סיסמה";
	$TEMP_INPAGE_TITLE = "שכחתי סיסמה";
	if($_GET["code"] && $_GET["email"]){
		function PAGE_TEXT(){
			global $GET_SITE_CONFIG_r;
			$Pemail=db_escape($_GET["email"]);
			$Pcode=db_escape($_GET["code"]);
			$check_e=db_query("SELECT * FROM `mod_members` WHERE `email`='$Pemail' AND `code`='$Pcode' AND `stat`=3");
			if(db_num_rows($check_e)>0){
						$check_r=db_fetch_array($check_e);
						$Pcode=gocode(8);
						$Ppass=gocode(8);
						db_query("UPDATE `mod_members` SET `code`='".$Pcode."',`pass`='".md5($Ppass)."',`stat`=1 WHERE `id`=".$check_r["id"]);
						$block=get_block(7);
						$link="http://".SITEDOMAIN."/".",members";
						$text=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{__NEWPASS__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),$Ppass,SITEDOMAIN)
						,$block["text"]);
						$subject=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{__NEWPASS__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),$Ppass,SITEDOMAIN)
							,$block["title"]);
						send_mail($Pemail,htmlspecialchars($GET_SITE_CONFIG_r[name])." < no-reply@".SITEDOMAIN.">",$subject,$text);//
							echo "<h1 class=\"site-content-block__title\">סיסמה חדשה נשלחה</h1><div class=\"site-content-block__body\"><% BLOCK_20 %></div>";				
			}else
				header("Location: ,");


		}	
	}else{
		if($_POST["sub"]=="SUBTRUE"){
				function PAGE_TEXT(){
					global $GET_SITE_CONFIG_r;
					$Pemail=db_escape($_POST["email"]);
					$check_e=db_query("SELECT * FROM `mod_members` WHERE `email`='$Pemail'");
					if(db_num_rows($check_e)==0)
						echo "<h1 class=\"site-content-block__title\">שחזור סיסמה</h1><div class=\"site-content-block__error\">אימייל לא קיים במערכת</div>";
					else{
						$check_r=db_fetch_array($check_e);
						
						$Pcode=gocode(8);
						db_query("UPDATE `mod_members` SET `code`='".$Pcode."',`stat`=3 WHERE `id`=".$check_r["id"]);
						$block=get_block(3);
						$link="http://".SITEDOMAIN."/".",members,pass"."&code=".$Pcode."&email=".$Pemail;
						$text=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),SITEDOMAIN)
							,$block["text"]);
						$subject=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),SITEDOMAIN)
							,$block["title"]);
						send_mail($Pemail,htmlspecialchars($GET_SITE_CONFIG_r[name])." <no-reply@".SITEDOMAIN.">",$subject,$text);//
							echo "<h1 class=\"site-content-block__title\">בדקו את תיבת האימייל</h1><div class=\"site-content-block__body site-content-block__success\"><% BLOCK_5 %></div>";
			
						
							
					}
			}
		}else{
			function PAGE_TEXT(){
?>

<h1 class="site-content-block__title">שכחתי סיסמה</h1>
<p class="site-content-block__lead">הזינו את כתובת האימייל שלכם ונשלח אליכם קישור לאיפוס הסיסמה.</p>

<form class="auth-form" action=",members,pass" method="post" style="max-width:420px;">
	<div class="mb-3">
		<label class="form-label" for="passEmail">דואר אלקטרוני</label>
		<input id="passEmail" type="email" class="form-control" name="email" placeholder="name@example.com" required>
	</div>
	<input type="hidden" name="sub" value="SUBTRUE">
	<button class="btn btn-primary w-100" type="submit">שליחת קישור לאיפוס</button>
</form>

<p class="site-content-block__switch">
	<a href=",members">חזרה להתחברות</a>
</p>

<?php			
			}		
		
		
		
		}
	}
	$TEMP_TEMPLATE = 5;

?>
