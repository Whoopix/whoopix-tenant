<?php
	if($USER_LOGIN!=false){
		header("Location: ,members");
		die();
	}
	$TEMP_PAGE_TITLE = "אישור מייל";
	$TEMP_INPAGE_TITLE = "אישור מייל";
	$TEMP_TEMPLATE = 2;
	$error=true;
	$q=db_query("SELECT `id`,`pass` FROM `mod_members` WHERE `email`='".db_escape($_GET["email"])."' AND `code`='".db_escape($_GET["code"])."'");
	if(db_num_rows($q)>0){
		$r=db_fetch_array($q);
		$_SESSION["user"]=intval($r["id"])."_".$r["pass"];
		db_query("UPDATE `mod_members` SET `code`='".gocode(8)."',`stat`=1 WHERE `id`=".$r["id"]);
		header("Location: index.php");
	}else
		header("Location: index.php");
	
	function PAGE_TEXT(){
		
	}
	$TEMP_TEMPLATE = 2;

?>