background:rgb(163,228,215)!important;<?php

include("../../config.php");
$USER_LOGIN=loggedIn();
$object_id = $_POST['object_id'];
$title = $_POST['title'];
$module = $_POST['module'];
$id = $_POST['id'];

if($USER_LOGIN['id'] != ''){
	
	// התניה אם כבר קיים לייק ב SQL
$get_likes = db_query("SELECT * FROM `mod_likes` WHERE `module` = '".$module."' AND `object_id` = '".$object_id."' AND `member_id` = '".$USER_LOGIN['id']."'");
	$gl = db_num_rows($get_likes);

echo $gl;

if($gl > 1){}else{





$Do=db_query("INSERT INTO `mod_likes` (`id`,`object_id`,`module`,`member_id`) VALUES (DEFAULT,'$object_id','$module','".$USER_LOGIN['id']."')");

$get_user = db_query("SELECT * FROM mod_members WHERE id = '".$USER_LOGIN['id']."'");
$gu = db_fetch_array($get_user);

$get_c = db_query("SELECT * FROM `mod_feedback_list` WHERE id = '$object_id'");
$gc = db_fetch_array($get_c);


$action = "המשתמש ";
$action .= $gu['username'];
$action .= ' עשה לייק לתגובה  "';
$action .= substr($gc['text'],0,25);
$action .= '"';
$Do_log=db_query("INSERT INTO `mod_members_logs` (`id`,`member_id`,`member_action`,`action`,`link`) VALUES (DEFAULT,'".$USER_LOGIN['id']."','".$gc['member_id']."','$action','".$gc['product_id']."')")or die (db_error());




}
}
//echo "INSERT INTO `mod_biz_feedback_fields` (`id`,`biz_id`,`title`) VALUES ('','$id','$title')";

?>