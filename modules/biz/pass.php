<?php
	$biz=check_biz_login();
	if($biz!=false)
		header("Location: ,biz");

	$TEMP_PAGE_TITLE = "שכחתי סיסמה";
	$TEMP_INPAGE_TITLE = "שכחתי סיסמה";
	if($_GET["code"] && $_GET["email"]){
		
		function PAGE_TEXT(){
			global $GET_SITE_CONFIG_r;
			$Pemail=db_escape($_GET["email"]);
			$Pcode=db_escape($_GET["code"]);
			$check_e=db_query("SELECT * FROM `mod_biz` WHERE `email`='$Pemail' AND `code`='$Pcode' AND `active`=3")or die(db_error());
			if(db_num_rows($check_e)>0){
						$check_r=db_fetch_array($check_e);
						$Pcode=gocode(8);
						$Ppass=gocode(8);
						db_query("UPDATE `mod_biz` SET `code`='".$Pcode."',`pass`='".pass($Ppass)."',`active`=1 WHERE `id`=".$check_r["id"])or die(db_error());
						$block=get_block(7);
						$link="http://".SITEDOMAIN."/".",biz";
						$text=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{__NEWPASS__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),$Ppass,SITEDOMAIN)
						,$block["text"]);
						$subject=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{__NEWPASS__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),$Ppass,SITEDOMAIN)
							,$block["title"]);
						send_mail($Pemail,htmlspecialchars($GET_SITE_CONFIG_r[name])." < no-reply@".SITEDOMAIN.">",$subject,$text);//
							echo "<% BLOCK_20 %>";				
			}else
				header("Location: ,");


		}	
	}else{
		if($_POST["sub"]=="SUBTRUE"){
				function PAGE_TEXT(){
					global $GET_SITE_CONFIG_r;
					$Pemail=db_escape($_POST["email"]);
					$check_e=db_query("SELECT * FROM `mod_biz` WHERE `email`='$Pemail'");
					if(db_num_rows($check_e)==0)
						echo "אימייל לא קיים במערכת";
					else{
						$check_r=db_fetch_array($check_e);
						
						$Pcode=gocode(8);
						db_query("UPDATE `mod_biz` SET `code`='".$Pcode."',`active`=3 WHERE `id`=".$check_r["id"]);
						$block=get_block(3);
						$link="http://".SITEDOMAIN."/".",biz,pass"."&code=".$Pcode."&email=".$Pemail;
						$text=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),SITEDOMAIN)
							,$block["text"]);
						$subject=str_replace(
								array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
								array($Pemail,$Pcode,htmlspecialchars($check_r["name"]),$link,htmlspecialchars($GET_SITE_CONFIG_r[name]),SITEDOMAIN)
							,$block["title"]);
						send_mail($Pemail,htmlspecialchars($GET_SITE_CONFIG_r[name])." <no-reply@".SITEDOMAIN.">",$subject,$text);//
							echo "<% BLOCK_5 %>";
			
						
							
					}
			}
		}else{
			function PAGE_TEXT(){
?>
				<form  id="regform" action=",biz,pass" method="post">
					
					<div>אימייל:<input type="text" class="bg" name="email" value="" /></div>
					<div style="padding-top:10px;"><input type="hidden" name="sub" value="SUBTRUE" /><input type="image" src="images/send.png" value="שלח" alt="שלח" /></div>
				</form>
<?php			
			}		
		
		
		
		}
	}
	$TEMP_TEMPLATE = 2;

?>