<?php
		$TEMP_PAGE_TITLE = "<% LANG_CONTACT %>";
		$TEMP_INPAGE_TITLE = "<% LANG_CONTACT %>";
		function PAGE_TEXT() {
			$error=true;
			$_POST=ArrayClearHTML($_POST);
			if($_POST["sub"]=="SUBTRUE"){
				$Pto=intval($_POST["to"]);
				$q=db_query("SELECT `email` FROM `contact_config` WHERE `id`='1'");
				$r=db_fetch_array($q);
				
		$to = $r["email"];
		$subject = "Website | $_POST[fname]";

		$mess = "
		<strong>נושא: </strong> $_POST[subject]<br>
			<strong>שם: </strong> $_POST[name]<br>
			<strong>טלפון: </strong> $_POST[phone]<br>
			<strong>אימייל: </strong> $_POST[email]<br>


			<br>$_POST[text]</div></body></html>
		";

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "<html dir=\"rtl\">";
		$headers .= "<body>";
		$headers .= "<div style='color: #000000; font-size: 10pt; font-family: Arial; text-align: right; diretion: rtl;'>";


		mail($to,$subject,$mess,$headers);

		echo "<font color='#990000'><i>$cont4</i></font>";
		
		
		echo "
			$iok <strong><% BLOCK_13 %></strong><br />
			

			<meta HTTP-Equiv='Refresh' Content='3; URL=index.php' />
			";
				
				
				
				if(db_num_rows($q)==0){
					$GLOBALS["members"]="alert('<% LANG_ErrorTryAgain %>');";
				}elseif(trim($_POST["subject"])==""){
					$GLOBALS["members"]="alert('<% LANG_ErrorFeedSubject %>');";
				}elseif(trim($_POST["text"])==""){
					$GLOBALS["members"]="alert('<% LANG_ErrorFeedContent %>');";
				}elseif(trim($_POST["fname"])==""){
					$GLOBALS["members"]="alert('<% LANG_ErrorFeedFirstName %>');";
				}elseif(trim($_POST["lname"])==""){
					$GLOBALS["members"]="alert('<% LANG_ErrorFeedFamilyName %>');";
				}elseif(trim($_POST["email"])==""){
					$GLOBALS["members"]="alert('<% LANG_ErrorFeedEmail %>');";
				}elseif(!preg_match("/^([a-zA-Z0-9]+)([a-zA-Z0-9\._\-]+)*@([a-zA-Z0-9])+([a-zA-Z0-9\-][a-zA-Z0-9]+)*(\.[a-zA-Z0-9]{2,3}){1,2}$/",$_POST["email"])){
					$GLOBALS["members"]="alert('<% LANG_ErrorEmailNotOk %>');";
				}else{
					$error=false;
					$r=db_fetch_array($q);
					$html=<<<HTML
					
HTML;
				
				}



			}
			
			
			
			if($error){
			echo "<% BLOCK_1 %>";
?>

<h1 class="site-content-block__title">צור קשר</h1>
<p class="site-content-block__lead">נשמח לעזור — מלאו את הטופס ונחזור אליכם בהקדם.</p>

<form id="contform" class="auth-form" action=",contact" method="post" onsubmit="return oncont();" style="max-width:480px;">
	<div class="mb-3">
		<label class="form-label" for="contName">שם מלא</label>
		<input id="contName" type="text" class="form-control" name="name" placeholder="שם מלא" required>
	</div>
	<div class="mb-3">
		<label class="form-label" for="contEmail">אימייל</label>
		<input id="contEmail" type="email" class="form-control" name="email" placeholder="name@example.com" required>
	</div>
	<div class="mb-3">
		<label class="form-label" for="contPhone">טלפון</label>
		<input id="contPhone" type="text" class="form-control" name="phone" placeholder="050-0000000" required>
	</div>
	<div class="mb-3">
		<label class="form-label" for="contSubject">נושא הפנייה</label>
		<select id="contSubject" class="form-select" name="subject">
			<option value="">בחרו נושא</option>
			<option value="פרסום באתר">פרסום באתר</option>
			<option value="הצעה לקטגוריה חדשה">הצעה לקטגוריה חדשה</option>
			<option value="הצעה לשיפור באתר">הצעה לשיפור באתר</option>
		</select>
	</div>
	<div class="mb-3">
		<label class="form-label" for="contText">הודעה</label>
		<textarea id="contText" class="form-control" name="text" rows="6" placeholder="כתבו את הודעתכם..." required></textarea>
	</div>
	<input type="hidden" name="sub" value="SUBTRUE" />
	<button class="btn btn-primary w-100" type="submit">שליחת הודעה</button>
</form>

<?php
			}
		}
		$TEMP_TEMPLATE = 5;

	
?>