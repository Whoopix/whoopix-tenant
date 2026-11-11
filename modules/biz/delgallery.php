<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_act,$biz,$get_id;
	include "inc/menu.php";
	db_query("DELETE FROM `mod_biz_gallery` WHERE `id`=".intval($get_id)." AND `bid`=".$biz["id"]);
	echo "בוצע";
	go(",biz,pages");
}
?>