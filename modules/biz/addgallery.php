<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
$TEMP_PAGE_TITLE  = 'ניהול עסקים';
$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_act,$biz,$get_id;
	if(!$get_id)
		$get_id='';
	include "inc/menu.php";
	if($_POST){
	
	
	
		db_query("INSERT INTO `mod_biz_gallery` (`id`,`bid`,`title`) 
										VALUES ('$get_id','".$biz["id"]."','".db_escape($_POST["title"])."')
					ON DUPLICATE KEY UPDATE 
						`id`='$get_id',`bid`='".$biz["id"]."',`title`='".db_escape($_POST["title"])."'");
		$i=db_insert_id();
		echo "בוצע בהצלחה";
		go(",biz,viewgallery_".$i);
						}
	$GET_PAGE_q=db_query("SELECT * FROM `mod_biz_gallery` WHERE `id`=".intval($get_id)." AND `bid`=".$biz["id"])or die(db_error());
	$GET_PAGE_r=db_fetch_array($GET_PAGE_q);
?>
	<form action=",biz,addgallery_<?=$get_id;?>" method="post">
	<table id="regform">
		<tr>
			<td>שם הגלריה</td>
			<td><input type="text" class="bg" name="title" value="<?=htmlspecialchars($GET_PAGE_r["title"]);?>" /></td>
		</tr>
		<tr>
			<td><input type="hidden" name="sub" value="SUBTRUE" /><input type="image" src="images/send.png" alt="שלח" value="שלח" /></td>
			<td>
			</td>
		</tr>
	</table>
	</form>

<?php
}
?>