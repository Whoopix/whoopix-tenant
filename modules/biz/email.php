<?php
	if($USER_LOGIN["active"]==1)
		header("Location: ,biz");

	$TEMP_PAGE_TITLE = "אישור מייל";
	$TEMP_INPAGE_TITLE = "אישור מייל";
	$TEMP_TEMPLATE = 2;
	$error=true;
	$q=db_query("SELECT `id`,`pass` FROM `mod_biz` WHERE `email`='".db_escape($_GET["email"])."' AND `code`='".db_escape($_GET["code"])."'");
	if(db_num_rows($q)>0){
		$r=db_fetch_array($q);
		
		db_query("UPDATE `mod_biz` SET `code`='".gocode(8)."',`active`=1,`pack`=0 WHERE `id`=".$r["id"]);
		header("Location: /,biz");
	}else
		header("Location: /,");
	$TEMP_TEMPLATE = 2;
	function PAGE_TEXT(){
	
	}
?>