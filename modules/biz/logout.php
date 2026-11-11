<?php
	if(!isset($_SESSION["biz"]))
		header("Location: index.php");
	unset($_SESSION["biz_id"]);
	unset($_SESSION["biz_pass"]);
	$TEMP_PAGE_TITLE  = 'התנתקות עסקים';
	$TEMP_INPAGE_TITLE = 'התנתקות עסקים';
	$TEMP_TEMPLATE = 2;
	function PAGE_TEXT() {
		echo "התנתקת בהצלחה";
		go("index.php");
	}
?>