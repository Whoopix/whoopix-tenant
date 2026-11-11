<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;


$TEMP_PAGE_TITLE  = 'הוספת עסק / מוצר';
$TEMP_INPAGE_TITLE = 'הוספת עסק / מוצר';


// להוסיף לטבלת עסקים שיוך לפי יוזר שהוסיף + 
// לעשות גם הכנסה לטבלת mod_biz_event - שיוך לקטגוריה

$rating_value = $_POST['rating_value'];
$text = $_POST['text'];
$product_id = $_POST['product_id'];

//$newFileName = uniqid('uploaded-', true) 
//    . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
//move_uploaded_file($_FILES['logofile']['tmp_name'], 'files/user_pic/' . $newFileName);



//db_query("INSERT INTO `mod_biz` (`id`,`name`,`address`,`pic`,`desc`,`cat_id`) VALUES (DEFAULT,'$name','$address','$newFileName','$desc','$cat_id')");


db_query("INSERT INTO `mod_feedback_list` (`id`,`member_id`,`product_id`,`text`,`rating_value`) VALUES (DEFAULT,'$USER_LOGIN[id]','$product_id','$text','$rating_value')");



function PAGE_TEXT() {
	global $get_id;

}
?>