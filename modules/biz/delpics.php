<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_id,$biz;
	include "inc/menu.php";
	$del=array_unique(array_filter((array)$_POST["check"],create_function('$var','return (intval($var)>0);')));
	if($get_id)
		$del[]=intval($get_id);
	$did=array();
	$get_pics_q=db_query("SELECT `file`,`mod_biz_gallery_pics`.`id`,`mod_biz_gallery`.`id` AS `gid` FROM `mod_biz_gallery_pics`,`mod_biz_gallery` WHERE `mod_biz_gallery_pics`.`id` IN (".implode(",",$del).") AND `mod_biz_gallery_pics`.`gid`=`mod_biz_gallery`.`id` AND `mod_biz_gallery`.`bid`=".$biz["id"]);
	$g="";
	while($get_pics_r=db_fetch_array($get_pics_q)){
		@unlink("files/bizfiles/".$get_pics_r["file"]);
		$did[]=$get_pics_r["id"];
		$g=$get_pics_r["gid"];
	}
	if(count($did)>0)
		db_query("DELETE FROM `mod_biz_gallery_pics` WHERE `id` IN (".implode(",",$did).")");
	echo "בוצע";
	go(",biz,gallery");
	
}
?>