<?php

include("../../config.php");
$USER_LOGIN=loggedIn();
$id = $_POST['id'];
$title = $_POST['title'];
$text = $_POST['text'];

if($USER_LOGIN['id'] != ''){
$get_likes = db_query("SELECT * FROM `mod_feedback_comments` WHERE `module` = 'feedback' AND `feedback_id` = '".$id."' AND `member_id` = '".$USER_LOGIN['id']."'");
	$gl = db_num_rows($get_likes);

echo $gl;

if($gl > 1){}else{

$Do=db_query("INSERT INTO `mod_feedback_comments` (`id`,`feedback_id`,`member_id`,`comment`) VALUES (DEFAULT,'$id','".$USER_LOGIN['id']."','$text')");

$get_user = db_query("SELECT * FROM mod_members WHERE id = '".$USER_LOGIN['id']."'");
$gu = db_fetch_array($get_user);

$get_c = db_query("SELECT * FROM `mod_feedback_list` WHERE id = '$id'");
$gc = db_fetch_array($get_c);

$action = "המשתמש ";
$action .= $gu['username'];
$action .= ' כתוב תגובה לביקורת  "';
$action .= substr($gc['text'],0,25);
$action .= '"';
$Do_log=db_query("INSERT INTO `mod_members_logs` (`id`,`member_id`,`member_action`,`action`,`link`) VALUES (DEFAULT,'".$USER_LOGIN['id']."','".$gc['member_id']."','$action','".$gc['product_id']."')")or die (db_error());


}
}

$get = db_query("SELECT * FROM `mod_feedback_comments` ORDER BY `id` DESC LIMIT 1")or die (db_error());
$geta = db_fetch_array($get);
echo $geta['time'];
//echo "INSERT INTO `mod_biz_feedback_fields` (`id`,`biz_id`,`title`) VALUES ('','$id','$title')";

?>