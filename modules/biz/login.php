<?php
	$TEMP_PAGE_TITLE  = 'התחברות עסקים';
	$TEMP_INPAGE_TITLE = 'התחברות עסקים';
	$TEMP_TEMPLATE = 2;
	function PAGE_TEXT() {
		echo "<% BLOCK_18 %>";
		$error=true;
		if($_POST["sub"]=="true"){
			$error=false;
			$check=db_query("SELECT `id` FROM `mod_biz` WHERE `email`='".db_escape($_POST["email"])."' AND `pass`='".pass($_POST["pass"])."'");
			$get=db_fetch_array($check);
			if($get){
				header("Location: ,biz");
				$_SESSION["biz_id"]=$get["id"];
				$_SESSION["biz_pass"]=pass($_POST["pass"]);

			}else{
				$error=true;
				echo "<div style=\"text-align:center;\" class=\"c\">שם משתמש או סיסמה לא נכונים</div>";
			}
		}
		
		if($error){
?>
	<form id="realregform" action=",biz,login" method="post" onsubmit="return onreg();" autocomplete="off">
	<table style="margin:auto;width:430px;" id="regform" cellpadding="3" cellspacing="1">
		<tr>
			<td class="bg">דואר אלקטרוני</td>
			<td class="bg"><input type="text" name="email" class="bg" value="" /></td>
		</tr>
		<tr>
			<td class="bg">סיסמה</td>
			<td class="bg"><input type="password" name="pass"  class="bg" value="" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;">
				<input type="hidden" name="sub" value="true" /><input type="image" src="images/send.png" alt="שלח" value="שלח" style="margin-bottom:5px;" />
			</td>
			<td> <a href=",biz,pass"> שכחתי סיסמה</a> </td>
		</tr>
	</table>
	</form>
<?php
		}
	}
?>