<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_act,$biz;
	include "inc/menu.php";
?>
	<a href=",biz,addgallery" style="text-decoration:underline">הוסף גלרייה</a>

	<table class="do-order">
		<tr class="nodrag nodrop">
			<td class="right">שם הגלריה</td><td>צפה</td><td>ערוך</td><td>מחק</td>
		</tr>
<?php
			$get_page_q=db_query("SELECT `id`,`title` FROM `mod_biz_gallery` WHERE `bid`=".$biz["id"]);
			while($get_page_r=db_fetch_array($get_page_q)){
?>
				<tr>
					<td class="right"><?=htmlspecialchars($get_page_r["title"]);?></td>
					<td><a href=",biz,viewgallery_<?=htmlspecialchars($get_page_r["id"]);?>">צפה</a></td>
					<td><a href=",biz,addgallery_<?=htmlspecialchars($get_page_r["id"]);?>">ערוך</a></td>
					<td><a href=",biz,delpage_<?=htmlspecialchars($get_page_r["id"]);?>">מחק</a></td>
				</tr>
<?php
			}
?>
	</table>

<?php
}
?>