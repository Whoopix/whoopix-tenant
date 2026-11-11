<?php

include("../../config.php");
$USER_LOGIN=loggedIn();
$id = $_POST['id'];
$member_to = $_POST['member_to'];
$text = $_POST['text'];


//$rating = implode(", ", $rating_array);

if($USER_LOGIN['id'] != ''){




$Do=db_query("INSERT INTO `mod_members_messages` (`id`,`member_id`,`member_to`,`text`) VALUES (DEFAULT,'$USER_LOGIN[id]','$member_to','$text')");

$get = db_query("SELECT * FROM `mod_members_messages` ORDER BY `id` DESC LIMIT 1")or die (db_error());
$geta = db_fetch_array($get);

echo $geta['time'];

}

?>