<?php

include("../../config.php");
$USER_LOGIN=loggedIn();
$module = $_POST['module'];
$object_id = $_POST['object_id'];
$Do=db_query("DELETE FROM `mod_likes` WHERE `module` = '".$module."' AND `object_id` = '".$object_id."' AND `member_id` = '".$USER_LOGIN['id']."'");



?>