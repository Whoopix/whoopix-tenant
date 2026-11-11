<?php

include("../../config.php");
$USER_LOGIN=loggedIn();
$id = $_POST['id'];
$title = $_POST['title'];
$text = $_POST['text'];
$rating = $_POST['rating'];
//$rating = implode(", ", $rating_array);

if($USER_LOGIN['id'] != ''){


//Insert to Logs
//$Logs=db_query("INSERT INTO `mod_members_logs` (`id`,`product_id`,`title`,`text`,`rating_value`) VALUES (DEFAULT,'$id','$title','$text','$rating')");


$Do=db_query("INSERT INTO `mod_feedback_list` (`id`,`member_id`,`product_id`,`title`,`text`,`rating_value`) VALUES (DEFAULT,'$USER_LOGIN[id]','$id','$title','$text','$rating')");




}
//echo "INSERT INTO `mod_biz_feedback_fields` (`id`,`biz_id`,`title`) VALUES ('','$id','$title')";

?>