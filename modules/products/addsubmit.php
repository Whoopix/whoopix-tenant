<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;


$TEMP_PAGE_TITLE  = 'הוספת עסק / מוצר';
$TEMP_INPAGE_TITLE = 'הוספת עסק / מוצר';

// preg replace

$name1 = preg_replace('~[\\\\/:*?"<>|]~', ' ', $_POST['name']);
$name = trim($name1, " ");


$address = $_POST['address'];





$area = $_POST['area'];
$desc = $_POST['desc'];
$cat_id = $_POST['cat_id'];

// Level-3 "add own business": stamp ownership so the business shows on the
// member dashboard and so affiliate/points attribution can resolve an owner.
$__owner   = function_exists('loggedIn') ? loggedIn() : false;
$agent_id  = is_array($__owner) ? (int) ($__owner['id'] ?? 0) : 0;


 //file upload

      $errors= array();
      $file_name = $_FILES['logofile']['name'];
      $file_size =$_FILES['logofile']['size'];
      $file_tmp =$_FILES['logofile']['tmp_name'];
      $file_type=$_FILES['logofile']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['logofile']['name'])));
      $expensions= array("jpeg","jpg","png");
$random = md5(uniqid("") . time());
	  
	  
      
      if(in_array($file_ext,$expensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file.";
      }
      
      if($file_size > 750000){
         $errors[]='File size must be excately 2 MB';
      }
      
      if(empty($errors)==true){
         move_uploaded_file($file_tmp,"files/biz_logo/".$random.$file_name);
         echo "Success";
      }else{
         print_r($errors);
      }
   

//end file upload




db_query("INSERT INTO `mod_biz` (`id`,`name`,`address`,`area`,`logofile`,`desc`,`cat_id`,`agent_id`) VALUES (DEFAULT,'$name','$address','$area','".$random.$file_name."','$desc','$cat_id','".$agent_id."')")or die(db_error());

$get_new_id = db_query("SELECT * FROM mod_biz ORDER BY id DESC LIMIT 1");
$gnid = db_fetch_array($get_new_id);

// Ensure the owner has an affiliate code so their share link works immediately.
if ($agent_id > 0 && function_exists('site_member_aff_code')) {
    site_member_aff_code($agent_id);
}

// Attribute this business to the affiliate who shared the add-business link.
if (!empty($gnid['id']) && function_exists('site_stamp_biz_referrer')) {
    site_stamp_biz_referrer((int) $gnid['id'], $agent_id);
}



db_query("INSERT INTO `mod_seo` (`id`,`mod`,`plink`,`lang_id`,`ptitle`,`description`) VALUES ('$gnid[id]','products','$name','1','$name','$desc')")or die(db_error());
$get_new_link = db_query("SELECT * FROM mod_seo WHERE id = '".$gnid['id']."'");
$gnlink = db_fetch_array($get_new_link);
header('Location: /'.$gnlink['plink'].'?feedback=true');



//db_query("INSERT INTO `mod_feedback_list` (`id`,`name`,`desc`) VALUES (DEFAULT,'$USER_LOGIN[id]','$id')");



function PAGE_TEXT() {
	global $get_id;

}
?>