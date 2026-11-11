<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_id,$biz;
	include "inc/menu.php";
	$get_gallery_q=db_query("SELECT `id`,`title` FROM `mod_biz_gallery` WHERE `id`=".intval($get_id)." AND `bid`=".$biz["id"]);
	if($get_gallery_r=db_fetch_array($get_gallery_q)){
	if($_POST["sub"]=="SUBTRUE"){
		foreach((array)$_POST["order"] AS $key=>$value)
		{
			db_query("UPDATE `mod_biz_gallery_pics` SET `order`='".intval($key)."' WHERE `id`='".intval($value)."' AND `gid`=".$get_id)or die(db_error());
		}
		echo "הסדר נשמר בהצלחה";
	}
?>
	<script type="text/javascript" src="admin/style/jquery.tablednd.0.7.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$(".do-order").tableDnD({
				onDragClass: "rowdrag"
			});
			$('.do-order tr:gt(0)').hover(function(){
				$(this).addClass("thover");
			},function(){
				$(this).removeClass("thover");
			});
			$('#sendIT').click(function(){
				$('#orderform').attr("action",",biz,viewgallery_<?=$get_id;?>");
				$('#orderform').submit();
				return false;
			});
		});
	</script>
	<a href=",biz,addpics">הוסף תמונות</a>
	<form id="orderform" action=",biz,delpics" method="post">
	<table class="do-order">
		<tr class="nodrag nodrop">
			<td></td><td class="right">כותרת התמונה</td><td>מחק</td>
		</tr>
<?php
			$get_page_q=db_query("SELECT `id`,`title`,`file` FROM `mod_biz_gallery_pics` WHERE `gid`=".intval($get_id)." ORDER BY `order`");
			while($get_page_r=db_fetch_array($get_page_q)){
?>
				<tr>
					<td><img src="files/bizfiles/<?=$get_page_r["file"];?>" style="max-width:60px;max-height:70px;" alt="" /></td>
					<td class="right"><input type="hidden" name="order[]" value="<?=htmlspecialchars($get_page_r["id"]);?>"><?=htmlspecialchars($get_page_r["title"]);?></td>
					<td><a href=",biz,delpics_<?=htmlspecialchars($get_page_r["id"]);?>">מחק</a></td>
					<td><input type="checkbox" name="check[]" value="<?=htmlspecialchars($get_page_r["id"]);?>"></td>
				</tr>
<?php
			}
?>
	</table>
	<div style="text-align:left;width:70%;margin:auto;padding-left:25px;padding-top:10px;">
	<input style="border:1px #000000 solid;text-align:left;" type="submit" value="מחק" /></div>
		<input type="hidden" name="sub" value="SUBTRUE" />
		<div style="text-align:center;">
		<br />
		<a href="#" id="sendIT" style="text-decoration:underline">שמור סדר תמונות</a>
		</div>
	</form>
<?php
	}else
		echo "גלריה לא קיימת";
}
?>