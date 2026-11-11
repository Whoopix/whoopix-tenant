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
	$error=true;
	if($_POST){
		$error=false;
/*		if($_FILES["logo"]["tmp_name"] && !in_array(get_ext($_FILES["logo"]),ALLOW_IMAGES())){
			$error=true;
			echo "קבצי תמונה בלבד";
		}elseif($_FILES["logo"]["tmp_name"] && $_FILES["logo"]['size']>500*1024){
			$error=true;
			echo "קובץ הלוגו גדול מדי, עד 250Kb";
		}
		if($_FILES["misc"]["tmp_name"] && !in_array(get_ext($_FILES["logo"]),ALLOW_IMAGES())){
			$error=true;
			echo "קבצי תמונה בלבד";
		}elseif($_FILES["misc"]["tmp_name"] && $_FILES["logo"]['size']>500*1024){
			$error=true;
			echo "קובץ התמונה גדול מדי, עד 500Kb";
		}
*/
		if(!$error){
		
			$error=false;
			$Plogo="";
			$arr=array();
			$arr[]="`facebook`='".db_escape($_POST["facebook"])."'";
			$arr[]="`mysite`='".db_escape($_POST["mysite"])."'";
			$arr[]="`gallery`='".intval($_POST["gallery"])."'";
			/*if($_FILES["logo"]["tmp_name"]){
				$arr[]="`logofile`='".Up_file($_FILES["logo"],"files/biz_logo/")."'";
			}		
			$Pmisc="";
			if($_FILES["misc"]["tmp_name"]){
				$arr[]="`miscfile`='".Up_file($_FILES["misc"],"files/biz_misc/")."'";
			}*/
			if(count($arr)>0){
				db_query("UPDATE `mod_biz` SET ".implode(",",$arr)." WHERE `id`=".$biz["id"]);
			}
		
		echo "עודכן בהצלחה";
		go(",biz,logos");
		}
	}
	if($error){
		echo "<% BLOCK_22 %>";
?>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="/plupload/js/plupload.full.js"></script>
<script type="text/javascript">
// Custom example logic
jQuery(function($){ 
	var uploader_logo = new plupload.Uploader({
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles_logo',
		//container : 'container',
		url : '/multyupload.php?do=logo',
		multi_selection:false, 
		flash_swf_url : '/plupload/js/plupload.flash.swf',
		silverlight_xap_url : '/plupload/js/plupload.silverlight.xap',
		max_file_size:'250kb',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png,bmp"},
		],
	});

	//uploader.bind('Init', function(up, params) {
	//	$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	//});

	$('#uploadfiles_logo').click(function(e) {
		uploader_logo.start();
		e.preventDefault();
	});

	uploader_logo.init();

	uploader_logo.bind('FilesAdded', function(up, files) {
		$('#prog_logo').html('');
		$('#filelist_logo').html('');
        if (uploader_logo.files.length == 2) {
            uploader_logo.removeFile(uploader.files[0]);
        }
		$.each(files, function(i, file) {
			
			$('#filelist_logo').html(file.name );//(' + plupload.formatSize(file.size) + ')
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader_logo.bind('UploadProgress', function(up, file) {
		$('#prog_logo').html(file.percent + "%");
	});

	uploader_logo.bind('Error', function(up, err) {
		$('#filelist_logo').html('');
		$('#prog_logo').html(err.message);
		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader_logo.bind('FileUploaded', function(up, file) {
		$('#filelist_logo').html('הושלם');
		$('#prog_logo').html('');		
	});

	var uploader_misc = new plupload.Uploader({
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles_misc',
		//container : 'container',
		url : '/multyupload.php?do=misc',
		multi_selection:false, 
		flash_swf_url : '/plupload/js/plupload.flash.swf',
		silverlight_xap_url : '/plupload/js/plupload.silverlight.xap',
		max_file_size:'250kb',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png,bmp"},
		],
	});

	//uploader.bind('Init', function(up, params) {
	//	$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	//});

	$('#uploadfiles_misc').click(function(e) {
		uploader_misc.start();
		e.preventDefault();
	});

	uploader_misc.init();

	uploader_misc.bind('FilesAdded', function(up, files) {
		$('#prog_misc').html('');
		$('#filelist_misc').html('');
        if (uploader_misc.files.length == 2) {
            uploader_misc.removeFile(uploader.files[0]);
        }
		$.each(files, function(i, file) {
			
			$('#filelist_misc').html(file.name );//(' + plupload.formatSize(file.size) + ')
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader_misc.bind('UploadProgress', function(up, file) {
		$('#prog_misc').html(file.percent + "%");
	});

	uploader_misc.bind('Error', function(up, err) {
		$('#filelist_misc').html('');
		$('#prog_misc').html('');
		$('#prog_misc').html(err.message);
		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader_misc.bind('FileUploaded', function(up, file) {
		$('#filelist_misc').html('הושלם');
		$('#prog_misc').html('');
	});	
});
</script>

	<table>
		<tr>
			<td>קובץ לוגו</td>
			<td><input type="button" id="pickfiles_logo" value="בחר קובץ" /><input type="button" id="uploadfiles_logo" value="שלח" /><span id="filelist_logo"></span><span id="prog_logo"></span></td>
		</tr>
<?php
		if($biz["pack"]==2){
?>
		<tr>
			<td>קובץ תמונה גדול (אם לא הוגדרה מצגת)</td>
			<td><input type="button" id="pickfiles_misc" value="בחר קובץ" /><input type="button" id="uploadfiles_misc" value="שלח" /><span id="filelist_misc"></span><span id="prog_misc"></span></td>
		</tr>

		<form action=",biz,logos" method="post"  enctype="multipart/form-data">
		<tr>
			<td>מצגת עליונה (בחר גלריה)</td>
			<td>
				<select name="gallery">
					<option value="0">ללא</option>
<?php
					$GET_G=db_query("SELECT * FROM `mod_biz_gallery` WHERE `bid`=".$biz["id"]);
					while($GET_G_r=db_fetch_array($GET_G)){
						echo "<option ".($GET_G_r["id"]==$biz["gallery"]?"selected=\"selected\" ":"")."value=\"".$GET_G_r["id"]."\">".htmlspecialchars($GET_G_r["title"])."</option>";
					
					}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td>דף פייסבוק</td>
			<td style="direction:ltr">http://www.facebook.com/<input type="text" name="facebook" value="<?=htmlspecialchars($biz["facebook"]);?>" /></td>
		</tr>	
		<tr>
			<td>אתר שלי</td>
			<td ><input type="text" name="mysite" value="<?=htmlspecialchars($biz["mysite"]);?>" /></td>
		</tr>
		
<?php
		}
?>
		<tr>
			<td><input type="hidden" name="sub" value="SUBTRUE" /><input type="image" src="images/send.png" alt="שלח" value="שלח" /></td>
			<td>
			</td>
		</tr>
		</form>
	</table>


<?php
	}
}
?>