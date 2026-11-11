<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;
$TEMP_PAGE_TITLE  = 'הוספת עסק / מוצר';
$TEMP_INPAGE_TITLE = 'הוספת עסק / מוצר';

function PAGE_TEXT() {
	global $get_id, $bz;
	$USER_LOGIN=loggedIn();

if ($USER_LOGIN['id'] != ''){ 
?>
	

<form action=",products,addsubmit" method="post" enctype="multipart/form-data">
<?php
$__add_aff = function_exists('site_current_affiliate_code') ? site_current_affiliate_code() : '';
if ($__add_aff !== '') {
	echo '<input type="hidden" name="aff" value="' . htmlspecialchars($__add_aff, ENT_QUOTES, 'UTF-8') . '">';
}
?>
	<select name="select1" id="select1" class="col-md-6 col-sm-6 col-xs-12" style="height:50px;" required="required">
												<option value="">בחר קטגוריה ראשית</option>
		<?php 
				$get_cat = db_query("SELECT * FROM mod_sop_cats");
				while ($gc = db_fetch_array($get_cat)) {
					
				$get_cat2 = db_query("SELECT * FROM `mod_sop` WHERE `id` = '".$_GET['cat']."'");
				$gc2 = db_fetch_array($get_cat2)
				
				
				//if cat_id > 0 {}
				
				
		 ?>
		 
			<option value="<?php echo $gc['id']; ?>" 
			<? if($_GET['cat'] == $gc['id']){ ?>selected="selected" <? } ?>
			<? if($gc['id'] == $gc2['cat_id']){ ?>selected="selected" <? } ?>
			
			>
			<?php echo $gc['title']; ?></option><?php } ?>



	</select>


	<select name="cat_id" id="select2" class="col-md-6 col-sm-6 col-xs-12" required="required" style="height:50px;" >
					<option value="">בחר תת קטגוריה</option>
			<?php 
			$get_cat1 = db_query("SELECT * FROM `mod_sop` WHERE parent = 0");
			while ($gc1 = db_fetch_array($get_cat1)) {
			?>
			
			
			
				  <option style="" class="NO<?php echo $gc1['cat_id']; ?>" id="<?php echo $gc1['cat_id']; ?>" <? if($_GET['cat'] == $gc1['id']){ ?> selected="selected" <? } ?> value="<?php echo $gc1['id']; ?>">
					<?php echo $gc1['title']; ?>
				  </option>
	<?php } ?>

	</select>
	
	


			<div style="clear:both;margin-bottom:20px;"></div>
				
			<input type="text" name="name" class="col-md-12 col-sm-12 col-xs-12" style="width:100%;height:50px;margin-bottom:20px;" required="required"  placeholder="שם העסק / מוצר">
			<input type="text" name="address" class="col-md-12 col-sm-12 col-xs-12" style="width:100%;height:50px;margin-bottom:20px;"  placeholder="כתובת העסק">
			<select name="area" class="col-md-12 col-sm-12 col-xs-12" required="required" style="height:50px;margin-bottom:20px;" >
							<option value="">בחר אזור בארץ</option>
							<option id="" value="all">כל הארץ</option>
						  <option id="" value="gush_dan">גוש דן</option>
						  <option id="" value="hasharon">השרון</option>
						  <option id="" value="tzafon">צפון</option>
						  <option id="" value="darom">דרום</option>
							
						  </option>
			

			</select>

			<textarea type="text" name="desc" class="col-md-12 col-sm-12 col-xs-12" style="width:100%;height:150px;margin-bottom:20px;" required="required"  placeholder="תיאור העסק"></textarea>
			  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type="text/javascript"></script>




    <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js" type="text/javascript"></script>

			
<div class="input-group image-preview col-md-12 col-sm-12 col-xs-12" style="margin-bottom:30px;">
                <input type="text" class="form-control image-preview-filename" disabled="disabled"> <!-- don't give a name === doesn't send on POST/GET -->
                <span class="input-group-btn">
                    <!-- image-preview-clear button -->
                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;height:50px;line-height:40px;margin:0px;">
                        <span class="glyphicon glyphicon-remove"></span> ניקוי
                    </button>
                    <!-- image-preview-input -->
                    <div class="btn btn-default image-preview-input" style="height:50px;line-height:40px;margin:0px;">
                        <span class="glyphicon glyphicon-folder-open"></span>
                        <span class="image-preview-input-title">בחירת תמונה</span>
                        <input type="file" accept="image/png, image/jpeg, image/gif" name="logofile"/> <!-- rename it -->
                    </div>
                </span>
            </div><!-- /input-group image-preview [TO HERE]--> 
		   
				
				
				<style>
				.fileUpload {
					position: relative;
					overflow: hidden;
					margin: 10px;
				}
				.fileUpload input.upload {
					position: absolute;
					top: 0;
					right: 0;
					margin: 0;
					padding: 0;
					font-size: 20px;
					cursor: pointer;
					opacity: 0;
					
					
				}
				

.image-preview-input {
    position: relative;
	overflow: hidden;
	margin: 0px;    
    color: #333;
    background-color: #fff;
    border-color: #ccc;    
}
.image-preview-input input[type=file] {
	position: absolute;
	top: 0;
	right: 0;
	margin: 0;
	padding: 0;
	font-size: 20px;
	cursor: pointer;
	opacity: 0;
	filter: alpha(opacity=0);
}
.image-preview-input-title {
    margin-left:2px;
}
				</style>
				
				
				
				<script>
				$(document).on('click', '#close-preview', function(){ 
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
        }, 
         function () {
           $('.image-preview').popover('hide');
        }
    );    
});

$(function() {
    // Create the close button
    var closebtn = $('<button/>', {
        type:"button",
        text: 'x',
        id: 'close-preview',
        style: 'font-size: initial;',
    });
    closebtn.attr("class","close pull-right");
    // Set the popover default content
    $('.image-preview').popover({
        trigger:'manual',
        html:true,
        title: "<strong>תצוגה מקדימה</strong>"+$(closebtn)[0].outerHTML,
        content: "לא נבחרה תמונה",
        placement:'bottom'
    });
    // Clear event
    $('.image-preview-clear').click(function(){
        $('.image-preview').attr("data-content","").popover('hide');
        $('.image-preview-filename').val("");
        $('.image-preview-clear').hide();
        $('.image-preview-input input:file').val("");
        $(".image-preview-input-title").text("Browse"); 
    }); 
    // Create the preview image
    $(".image-preview-input input:file").change(function (){     
        var img = $('<img/>', {
            id: 'dynamic',
            width:250,
            height:200
        });      
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function (e) {
            $(".image-preview-input-title").text("בחירה");
            $(".image-preview-clear").show();
            $(".image-preview-filename").val(file.name);            
            img.attr('src', e.target.result);
            $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
        }        
        reader.readAsDataURL(file);
    });  
});
				</script>
				
				
				
				
				
				
				
			
			
			
			<input class="btn btn-primary btn-block login-button btnmenu" style="padding:0px!important;margin:0px!important;"  type="submit" value="הוספת עסק והמשך לכתיבת חוות דעת">

			
			

</form>
	

	
	
	<?php
	} else { 
	header("Location: /,members");
	?>
		<h3>התחבר כדי להוסיף חוות דעת ועסק חדש</h3>
		<?php
	}

}
?>