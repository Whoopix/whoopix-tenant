<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;
include("../../config.php");




$USER_LOGIN=loggedIn();
$id = $_POST['id'];
$title = $_POST['title'];
$text = $_POST['text'];
$rating = $_POST['rating'];
$price = $_POST['price'];
$price_comment = $_POST['price_comment'];
$total_rating = $_POST['total_rating'];

if($USER_LOGIN['id'] != ''){




$Do=db_query("INSERT INTO `mod_feedback_list` (`id`,`member_id`,`product_id`,`title`,`text`,`rating_value`,`rating`,`price`,`price_comment`) 
VALUES 
(DEFAULT,'$USER_LOGIN[id]','$id','$title','$text','$rating','$total_rating','$price','$price_comment')");




}





$g = db_query("SELECT * FROM `mod_seo` WHERE `id` = '$product_id' AND `mod` = 'products'");
$p = db_fetch_array($g);




function PAGE_TEXT() {
	global $get_id;

}
?>