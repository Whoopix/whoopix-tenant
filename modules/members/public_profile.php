<?php
	if(!is_array($USER_LOGIN))
		include "login.php";
	else{
		$TEMP_PAGE_TITLE = "פרופיל";
		$TEMP_INPAGE_TITLE = "פרופיל";

		$error=true;
		if($_POST["sub"]=="SUBTRUE"){
			$error=false;
			$Pname=strip($_POST["name"],false,true);
			$Pemail=strip($_POST["email"],false,true);
			$Pphone=strip($_POST["phone"],false,true);
			$Pcell=strip($_POST["cell"],false,true);
			$Pnewsletter=intval($_POST["newsletter"]);
			$check_e=db_query("SELECT * FROM `mod_members` WHERE `email`='$Pemail'");
			if(md5($_POST["oldpass"])!=$USER_LOGIN["pass"]){
				$error=true;
				$err="סיסמה ישנה אינה נכונה";
			}elseif(trim($Pname)==""){
				$error=true;
				$err="אנא מלא שם";
			}elseif(trim($Pemail) != "" && !preg_match("/^([a-zA-Z0-9]+)([a-zA-Z0-9\._\-]+)*@([a-zA-Z0-9])+([a-zA-Z0-9\-][a-zA-Z0-9]+)*(\.[a-zA-Z0-9]{2,3}){1,2}$/",$Pemail)){
				$error=true;
				$err="כתובת דוא\"ל לא חוקית";
			}elseif(trim($Pemail) != "" &&db_num_rows($check_e)>0 && $Pemail!=$USER_LOGIN["email"]){
					$error=true;
					$err="כתובת דוא\"ל קיימת במערכת";
			}elseif(trim($_POST["pass"]) !="" && $_POST["pass"] != $_POST["pass2"]){
				$error=true;
				$err="סיסמאות חדשות לא תואמות";
			}
			if(!$error){
				$err="הפרטים עודכנו";
				$Ppass=md5($_POST["pass"]);
				db_query("UPDATE `mod_members` SET
					".(trim($Pemail) != ""?"`email`='$Pemail',":"").(trim($_POST["pass"]) !=""?"`pass`='$Ppass',":"")."`name`='$Pname',`birthday`='".mktime(0,0,0,$_POST["month"],$_POST["day"],$_POST["year"])."',`phone`='$Pphone',`cell`='$Pcell',`newsletter`='$Pnewsletter' WHERE `id`=".$USER_LOGIN["id"])or die(db_error());
				if(trim($_POST["pass"]) !=""){
					if(isset($_SESSION["user"]) && $_SESSION["user"]!=null){
						$_SESSION["user"]=intval($USER_LOGIN["id"])."_".$Ppass;
					}
					if(isset($_COOKIE["USER"])){
						setcookie("USER[pass]",$Ppass,time()+60*60*24*31*12)or die();
					}
				}
					/*function PAGE_TEXT() {
				//	echo "<% BLOCK_3 %>";
				}*/
			}
		
		
		}
//	if($error){
	function PAGE_TEXT() {
		global $error,$err;
		$USER_LOGIN=loggedIn();
		$order=false;
		$Vname=strip($USER_LOGIN["name"]);
		$Vphone=strip($USER_LOGIN["phone"]);
		$Vcell=strip($USER_LOGIN["cell"]);		
		$Vnewsletter=strip($USER_LOGIN["newsletter"]);	
			echo $err;
?>
	<form id="realregform" action=",members" method="post" autocomplete="off">
	<table id="regform">
		<tr>
			<td>שם מלא:</td>
			<td><input class="bg" type="text" name="name" value="<?=$Vname;?>" /></td><td></td>
		</tr>
		<tr>
			<td>יום הולדת:</td>
			<td>
				<div class="bith">
					<select class="year" name="year" >
						<?php for($i=date("Y")-1;$i>date("Y")-100;$i--){?><option <?=($i==date("Y",$USER_LOGIN["birthday"])?"selected=\"selected\"":"");?> value="<?=$i;?>"><?=$i;?></option><?}?>
					</select>
					<select class="month" name="month" >
						<?php for($i=1;$i<=12;$i++){?><option <?=($i==date("m",$USER_LOGIN["birthday"])?"selected=\"selected\"":"");?> value="<?=$i;?>"><?=$i;?></option><?}?>
					</select>
					<select class="day" name="day" >
					<?php for($i=1;$i<=31;$i++){?><option <?=($i==date("d",$USER_LOGIN["birthday"])?"selected=\"selected\"":"");?> value="<?=$i;?>"><?=$i;?></option><?}?>
					</select>
				</div>
			</td>
			<td></td>
		</tr>
		<tr>
			<td>שינוי סיסמה</td>
			<td></td>
		</tr>		
		
		<tr>
			<td>סיסמה:</td>
			<td><input type="password" class="bg" name="pass" value="" /></td><td></td>
		</tr>
		<tr>
			<td>אימות סיסמה:</td>
			<td><input type="password" class="bg" name="pass2" value="" /></td>
			<td></td>
		</tr>
	
		<tr>
			<td colspan="2" valign="top" style="padding-top:15px;">
				<input type="checkbox" name="newsletter" value="1" <?=($Vnewsletter==1)?" checked=\"checked\"":"";?>/>אני מאשר קבלת ניוזלטר מהאתר<br />
			</td>
			<td style="padding-top:15px;width:240px;text-align:left">
				<input type="hidden" name="sub" value="SUBTRUE" />
				<input type="image" src="images/send.png" alt="שלח" value="שלח" />
			</td>
		</tr>
		<tr>
			<td>סיסמה נוכחית:</td>
			<td><input type="password" class="bg" name="oldpass" value="" /></td>
			<td></td>
		</tr>
	</table>
	</form>

<?php
//	}
	//}
	}
		$TEMP_TEMPLATE = 2;
	}
?>