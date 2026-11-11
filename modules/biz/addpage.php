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
	
		if($_POST["gallery"]>0){
			$q=db_query("SELECT * FROM `mod_biz_gallery` WHERE `id`=".intval($_POST["gallery"])." AND `bid`=".$biz["id"]);
			if(db_num_rows($q)==0)
				$_POST["gallery"]=0;
		}else
			$_POST["gallery"]=0;
	
		db_query("INSERT INTO `mod_biz_pages` (`id`,`bid`,`title`,`text`,`gallery`) 
										VALUES ('$get_id','".$biz["id"]."','".db_escape($_POST["title"])."','".db_escape($_POST["text"])."','".intval($_POST["gallery"])."')
					ON DUPLICATE KEY UPDATE 
						`id`='$get_id',`bid`='".$biz["id"]."',`title`='".db_escape($_POST["title"])."',`text`='".db_escape($_POST["text"])."',`gallery`='".intval($_POST["gallery"])."'");
		echo "בוצע בהצלחה";
		go(",biz,pages");
						}
	$GET_PAGE_q=db_query("SELECT * FROM `mod_biz_pages` WHERE `id`=".intval($get_id)." AND `bid`=".$biz["id"])or die(db_error());
	$GET_PAGE_r=db_fetch_array($GET_PAGE_q);
?>
	<script type="text/javascript" src="admin/resources/editor/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="admin/resources/editor/ckeditor/adapters/jquery.js"></script>
	<script type="text/javascript">
	CKEDITOR.config.language = 'he';
	CKEDITOR.config.toolbar = 'Full';
	//CKEDITOR.config.enterMode=CKEDITOR.ENTER_DIV;CKEDITOR.ENTER_BR
	//KEDITOR.config.shiftEnterMode=CKEDITOR.ENTER_DIV;CKEDITOR.ENTER_BR;CKEDITOR.ENTER_P
	//CKEDITOR.config.specialChars = [ '"', '’', [ '&custom;', 'Custom label' ] ];
	CKEDITOR.config.resize_enabled = false;

	CKEDITOR.config.specialChars = CKEDITOR.config.specialChars.concat( [[ '&#8362;', '&#8362;' ] ] );
	CKEDITOR.config.toolbar_Full =
	[
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','Iframe' ] },
		'/',
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
		'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links', items : [ 'Link','Unlink'] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
	];
	jQuery(document).ready(function($) {
			$('textarea.wysiwyg').ckeditor();
	});
	</script>
	<form action=",biz,addpage_<?=$get_id;?>" method="post">
	<table id="regform">
		<tr>
			<td>שם העמוד</td>
			<td><input class="bg" type="text" name="title" value="<?=htmlspecialchars($GET_PAGE_r["title"]);?>" /></td>
		</tr>
		<tr>
			<td>צרף גלריה</td>
			<td>
				<select name="gallery">
					<option value="0">ללא</option>
<?php
					$GET_G=db_query("SELECT * FROM `mod_biz_gallery` WHERE `bid`=".$biz["id"]);
					while($GET_G_r=db_fetch_array($GET_G)){
						echo "<option ".($GET_G_r["id"]==$GET_PAGE_r["gallery"]?"selected=\"selected\" ":"")."value=\"".$GET_G_r["id"]."\">".htmlspecialchars($GET_G_r["title"])."</option>";
					
					}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td>תוכן</td>
			<td>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea class="wysiwyg" name="text"><?=htmlspecialchars($GET_PAGE_r["text"]);?></textarea>
			</td>
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