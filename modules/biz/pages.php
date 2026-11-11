<?php
if(!$biz=check_biz_login())
	header("Location: ,biz,login");
$TEMP_TEMPLATE = 2;
	$TEMP_PAGE_TITLE  = 'ניהול עסקים';
	$TEMP_INPAGE_TITLE = 'ניהול עסקים';
function PAGE_TEXT() {
	global $get_act,$biz;
	include "inc/menu.php";
	if($_POST["sub"]=="SUBTRUE"){
		foreach((array)$_POST["order"] AS $key=>$value)
		{
			db_query("UPDATE `mod_biz_pages` SET `order`='".intval($key)."' WHERE `id`='".intval($value)."' AND `bid`=".$biz["id"])or die(db_error());
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
				$('#orderform').submit();
				return false;
			});
		});
	</script>
	<div class="font12">
	<a href=",biz,addpage" style="text-decoration:underline">הוסף עמוד</a>
	<form id="orderform" action=",biz,pages" method="post">
	<table class="do-order">
		<tr class="nodrag nodrop">
			<td class="right">שם העמוד</td><td>ערוך</td><td>מחק</td>
		</tr>
<?php
			$get_page_q=db_query("SELECT `id`,`title` FROM `mod_biz_pages` WHERE `bid`=".$biz["id"]." ORDER BY `order`");
			while($get_page_r=db_fetch_array($get_page_q)){
?>
				<tr>
					<td class="right"><input type="hidden" name="order[]" value="<?=htmlspecialchars($get_page_r["id"]);?>" /> <?=htmlspecialchars($get_page_r["title"]);?></td>
					<td><a href=",biz,addpage_<?=htmlspecialchars($get_page_r["id"]);?>">ערוך</a></td>
					<td><a href=",biz,delpage_<?=htmlspecialchars($get_page_r["id"]);?>">מחק</a></td>
				</tr>
<?php
			}
?>
	</table>
		<input type="hidden" name="sub" value="SUBTRUE" />
		<div style="text-align:center;">
		<br />
		<a href="#" id="sendIT" style="text-decoration:underline">שמור סדר עמודים</a>
		</div>
	</form>
	</div>

<?php
}
?>