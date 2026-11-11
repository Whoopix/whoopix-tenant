<?php
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
	$TEMP_TEMPLATE = 2;
	if($biz=check_biz_login()){
		function PAGE_TEXT() {
			global $get_id,$biz;
			include "inc/menu.php";
			$biz=ArrayClearHTML($biz);
			$p=$get_id==1?"חבילה קטנה":"חבילה גדולה";
			$EMAIL=<<<html
				<table>
					<tr>
						<td>שם העסק:</td>
						<td>{$biz["name"]}</td>
					</tr>
					<tr>
						<td><b>חבילה מבוקשת:</b></td>
						<td>{$p}</td>
					</tr>
					<tr>
						<td>ID במערכת:</td>
						<td>{$biz["id"]}</td>
					</tr>
					<tr>
						<td>טלפון:</td>
						<td>{$biz["phone"]}</td>
					</tr>
					<tr>
						<td>שם איש קשר:</td>
						<td>{$biz["c_name"]}</td>
					</tr>
					<tr>
						<td>טלפון איש קשר:</td>
						<td>{$biz["c_phone"]}</td>
					</tr>
				<tr>
						<td>אימייל הרשמה:</td>
						<td>{$biz["email"]}</td>
					</tr>
				<tr>
						<td>אימייל הצגה:</td>
						<td>{$biz["show_email"]}</td>
					</tr>

				</table>
			
html;
			switch($get_id){
				case '1':
					send_mail(SALEMAIL,"שטרודל <no-reply@".SITEDOMAIN.">","בקשת חבילה",$EMAIL);
					echo "<% BLOCK_16 %>";
				break;
				case '2':
					send_mail(SALEMAIL,"שטרודל <no-reply@".SITEDOMAIN.">","בקשת חבילה",$EMAIL);
					echo "<% BLOCK_17 %>";
				break;
				default:
?>
				<table style="margin:auto;" cellspacing="20">
					<tr>
<?php
	if($biz["pack"]<1){
?>
						<td>
							<a class="pack blue" href=",biz,activate_1">
								<span class="title">חבילה קטנה</span>
								<span class="desc">הכי משתלם לעסקים קטנים</span>
								<span class="price">45 ש"ח לחודש</span>
								<span class="get">כחלק מהחבילה כל בעל עסק מקבל:</span>
								<span class="op">חומוס</span>
								<span class="op">צ'יפס</span>
								<span class="op">סםט</span>
								<span class="b">לחץ כאן</span>
							</a>
						</td>
<?php
}
?>
						<td>
							<a class="pack gray" href=",biz,activate_2">
								<span class="title">חבילה קטנה</span>
								<span class="desc">הכי משתלם לעסקים קטנים</span>
								<span class="price">45 ש"ח לחודש</span>
								<span class="get">כחלק מהחבילה כל בעל עסק מקבל:</span>
								<span class="op">חומוס</span>
								<span class="op">צ'יפס</span>
								<span class="op">סםט</span>
								<span class="b">לחץ כאן</span>
							</a>
						</td>
					</tr>
				</table>
<?php
				break;
			}
		
		}
	}else{
		function PAGE_TEXT() {
			echo "אינך מחובר";
		
		}	
	}
?>