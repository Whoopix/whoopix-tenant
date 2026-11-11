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
		echo "בוצע בהצלחה";
		go(",biz,viewgallery_".$get_id);
						}
	$GET_PAGE_q=db_query("SELECT * FROM `mod_biz_gallery` WHERE `id`=".intval($get_id)." AND `bid`=".$biz["id"])or die(db_error());
	$GET_PAGE_r=db_fetch_array($GET_PAGE_q);
?>

<!-- Third party script for BrowserPlus runtime (Google Gears included in Gears runtime now) -->
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="/plupload/js/plupload.full.js"></script>

<script type="text/javascript">
// Custom example logic
jQuery(function($){ 
	var uploader = new plupload.Uploader({
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles',
		container : 'container',
		drop_element:'filelist',
		url : '/multyupload.php',
		flash_swf_url : '/plupload/js/plupload.flash.swf',
		silverlight_xap_url : '/plupload/js/plupload.silverlight.xap',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png,bmp"},
		],
	});

	//uploader.bind('Init', function(up, params) {
	//	$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	//});

	$('#uploadfiles').click(function(e) {
		$("#pic_title").prop('disabled', true);
		$("#slideshow_id").prop('disabled', true);
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#filelist').append(
				'<tr id="' + file.id + '"><td>' + file.name +'</td><td><input type="text" value="" /></td><td class="prog"></td></tr>');//(' + plupload.formatSize(file.size) + ')
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " td.prog").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
		$('#' + file.id + " td.prog").append("Error: " + err.code +
			", Message: " + err.message);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file) {
		$('#' + file.id + " td.prog").html("הושלם");
	});
	
	uploader.bind('BeforeUpload', function(up, file) {
		up.settings.multipart_params = {'gallery' : $("#slideshow_id").val(),'title' : $('#'+file.id+' input').val()};
		$('#' + file.id + " input").prop('disabled',true);
	});
	uploader.bind('UploadComplete', function(up, file) {
		window.location.href=',biz,viewgallery_'+$("#slideshow_id").val();
	});
	
});
</script>
<table>
				<table width="100%">
					<tr>
						<td><strong>גלריה:</strong></td>
						<td>
							<select id="slideshow_id" name="slideshow">

<?php
						$q=db_query("SELECT * FROM `mod_biz_gallery` WHERE `bid`=".$biz["id"]);
						while($r=db_fetch_array($q)){
							echo "<option value=\"".$r["id"]."\"".($r["id"]==$Vget_id?" selected=\"selected\"":"").">".$r['title']."</option>";			
						}
?>
				</select>
						</td>
						<td></td>
					</tr>
					<tr>
						<td width="80"><strong>* תמונות:</strong></td>
						<td>
							<div id="container" style="padding:5px;">
								<table id="filelist" style="text-align:center;width:100%;min-height:70px;border:1px black solid;">
									<tr>
										<td valign="top" style="text-align:center">שם קובץ</td>
										<td valign="top" style="text-align:center">שם לתמונה</td>
										<td valign="top" style="text-align:center">מצב</td>
									</tr>
								</table>
								<a id="pickfiles" href="#">[בחר קבצים]</a>
							</div>
						</td>
						<td></td>
					</tr>
					<tr>
					<td><input type="image" src="images/send.png" id="uploadfiles"  value="העלה תמונות"  /></td>
					</tr>
</table>

<?php
}
?>