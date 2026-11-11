<?php
//$html = preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $html);
	if(!$biz=check_biz_login())
		header("Location: ,biz,login");
$TEMP_PAGE_TITLE  = 'ניהול עסקים';
$TEMP_INPAGE_TITLE = 'ניהול עסקים';
$TEMP_TEMPLATE = 2;

if(!function_exists("PAGE_TEXT")){
function PAGE_TEXT() {
	global $get_act,$biz;
	
	include "inc/menu.php";
	
		$error=true;
		if($_POST["sub"]=="true"){
			$error=false;
			$c_mail=db_query("SELECT `id` FROM `mod_biz` WHERE `email`='".db_escape($_POST["email"])."'");
			if(trim($_POST["email"])==""){
				$error=true;
				echo "אנא הכנס אימייל";
			}elseif(!preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",$_POST["email"])){
				$error=true;
				echo "אימייל לא תקין";					
			}elseif(db_num_rows($c_mail)>0 && $_POST["email"]!=$biz["email"]){
				$error=true;
				echo "אימייל קיים";
			}
			if(trim($_POST["name"])==""){
				$error=true;
				echo "אנא הכנס שם לעסק";			
			}

			if(trim($_POST["phone"])==""){
				$error=true;
				echo "אנא הכנס טלפון לעסק";			
			}		
			if(trim($_POST["show_mail"]) != "" && !preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",$_POST["show_mail"])){
				$error=true;
				echo "אימייל לא תקין";					
			}	
			if(pass($_POST["pass"])!=$biz["pass"]){
				$error=true;
				echo "סיסמה ישנה אינה נכונה";			
			}			
			if(!$error){
			
				$pass=pass($_POST["newpass"]);
				db_query("UPDATE `mod_biz` SET 
					".(trim($_POST["newpass"])!=""?"`pass`='".$pass."',":"")."
					`email`='".db_escape($_POST["email"])."',
					`name`='".db_escape($_POST["name"])."',
					`desc`='".db_escape($_POST["desc"])."',
					`c_name`='".db_escape($_POST["c_name"])."',
					`c_phone`='".db_escape($_POST["c_phone"])."',
					`phone`='".db_escape($_POST["phone"])."',
					`phone2`='".db_escape($_POST["phone2"])."',
					`fax`='".db_escape($_POST["fax"])."',
					`show_mail`='".db_escape($_POST["show_mail"])."',
					`area`=".intval($_POST["area"]).",
					`newsletter`=".intval($_POST["newsletter"]).",
					`address`='".db_escape($_POST["address"])."'
					WHERE `id`=".$biz["id"])or die(db_error());
				$biz_id=$biz["id"];
					db_query("DELETE FROM `mod_biz_props` WHERE `biz_id`=".$biz_id);
					db_query("DELETE FROM `mod_biz_event` WHERE `biz_id`=".$biz_id);
					db_query("DELETE FROM `mod_biz_sop` WHERE `biz_id`=".$biz_id);
				foreach((array)$_POST["props"] AS $value){
					db_query("INSERT INTO `mod_biz_props` (`biz_id`,`prop_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				foreach((array)$_POST["event"] AS $value){
					db_query("INSERT INTO `mod_biz_event` (`biz_id`,`event_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				foreach((array)$_POST["sop"] AS $value){
					db_query("INSERT INTO `mod_biz_sop` (`biz_id`,`sop_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				if(trim($_POST["newpass"]) !=""){
					$_SESSION["biz_id"]=$biz["id"];
					$_SESSION["biz_pass"]=pass($_POST["newpass"]);
				}	
				echo "הפרטים עודכנו";
			}
		
		
		}
		$biz=check_biz_login();
		$biz=ArrayClearHTML($biz);
?>
					<script type="text/javascript">
					 $(document).ready(function(){
						var unique = function(origArr) {
							var newArr = [],
								origLen = origArr.length,
								found,
								x, y;
						 
							for ( x = 0; x < origLen; x++ ) {
								found = undefined;
								for ( y = 0; y < newArr.length; y++ ) {
									if ( origArr[x] === newArr[y] ) { 
									  found = true;
									  break;
									}
								}
								if ( !found) newArr.push( origArr[x] );    
							}
						   return newArr;
						}
						$('#field input').click(function(){
							var all=new Array();
							$('#field input:checked').each(function(){
								if($(this).attr("rel")!="")
								all.push($(this).attr("rel"));
							});
							var allS=all.join(",");
							var u=unique(allS.split(","));
							$('#ps div').hide();
							$.each(u, function(index, value) { 
							  $('#ps div input[value='+value+']').parent().show();
							});
						});
						$('#field input:checked').each(function(){
							var all=new Array();
							$('#field input:checked').each(function(){
								if($(this).attr("rel")!="")
								all.push($(this).attr("rel"));
							});
							var allS=all.join(",");
							var u=unique(allS.split(","));
							$('#ps div').hide();
							$.each(u, function(index, value) { 
							  $('#ps div input[value='+value+']').parent().show();
							});						
						});
					 });
					 </script>
		<form id="realregform" action=",biz" method="post" autocomplete="off">
			<input type="hidden" name="sub" value="true" />
		<table id="regform">
			<tr>
				<td class="b" style="width:150px;">*אימייל:</td>
				<td><input type="text" class="bg" name="email"  value="<?=$biz["email"];?>" /></td><td></td>
			</tr>

			<tr>
				<td class="b">*שם העסק:</td>
				<td><input type="text" class="bg" name="name"  value="<?=$biz["name"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b" valign="top">תיאור העסק:</td>
				<td><textarea class="bg" style="resize: none;height:80px;" name="desc" ><?=$biz["desc"];?></textarea></td><td></td>
			</tr>
			<tr>
				<td class="b">שם איש קשר:</td>
				<td><input type="text" class="bg" name="c_name"  value="<?=$biz["c_name"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">טלפון איש קשר:</td>
				<td><input type="text" class="bg" name="c_phone"  value="<?=$biz["c_phone"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b">*טלפון:</td>
				<td><input type="text" class="bg" name="phone"  value="<?=$biz["phone"];?>" /></td><td></td>
			</tr>			
			<tr>
				<td class="b">טלפון נוסף:</td>
				<td><input type="text" class="bg" name="phone2"  value="<?=$biz["phone2"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">פקס:</td>
				<td><input type="text" class="bg" name="fax"  value="<?=$biz["fax"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">אימייל של העסק:</td>
				<td><input type="text" class="bg" name="show_mail"  value="<?=$biz["show_mail"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b">אזור:</td>
				<td>
				<select name="area" class="bg">
<?php
				$area_q=db_query("SELECT * FROM `mod_area` WHERE `parent`=0 ORDER BY `order`");
				while($area_r=db_fetch_array($area_q)){
					echo "<optgroup label=\"".$area_r["title"]."\">";
					$area_q1=db_query("SELECT * FROM `mod_area` WHERE `parent`=".$area_r["id"]." ORDER BY `order`");
					while($area_r1=db_fetch_array($area_q1)){	
						echo "<option ".($biz["area"]==$area_r1["id"]?"selected=\"selected\"":"")." value=\"".$area_r1["id"]."\">".$area_r1["title"]."</option>";
					}
					echo "</optgroup>";
				
				}
?>			
			</select></td><td></td>
			</tr>	
			<tr>
				<td class="b">כתובת:</td>
				<td><input type="text" class="bg" name="address" value="<?=$biz["address"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b" valign="top" style="padding-top:10px;">תחום:</td>
				<td id="field" colspan="2">

<?php
			$get_props_q=db_query("SELECT * FROM `mod_sop_props`");
			$props=array();
			while($get_props_r=db_fetch_array($get_props_q))
				$props[$get_props_r["sop_id"]][]=$get_props_r["prop_id"];
			$ACT_CATS_q = db_query("SELECT * FROM `mod_sop_cats` ORDER BY `order`");
			while($ACT_CATS_r=db_fetch_array($ACT_CATS_q)){
				echo "<div class=\"clear\"><b>".$ACT_CATS_r["title"].":</b></div><div class=\"clear\"></div>";
				
				$area_q1=db_query("SELECT `e`.*,`b`.`biz_id` FROM `mod_sop` AS `e` LEFT JOIN `mod_biz_sop` AS `b` ON `e`.`id`=`b`.`sop_id` AND `b`.`biz_id`=".$biz["id"]." WHERE `e`.`cat_id`=".$ACT_CATS_r["id"]." ORDER BY `e`.`id` DESC");
				while($area_r1=db_fetch_array($area_q1)){	
					echo "<div><input type=\"checkbox\" ".($area_r1["biz_id"]>0?"checked=\"checked\"":"")." name=\"sop[]\" rel=\"".implode(",",(array)$props[$area_r1["id"]])."\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
				}
			}
?>			
			</td>
			</tr>
			<tr id="ps">
				<td class="b" valign="top">מאפיינים:</td>
				<td colspan="2"><?php
				
			$area_q1=db_query("SELECT `e`.*,`b`.`biz_id` FROM `mod_props` AS `e` LEFT JOIN `mod_biz_props` AS `b` ON `e`.`id`=`b`.`prop_id` AND `b`.`biz_id`=".$biz["id"]);
			while($area_r1=db_fetch_array($area_q1)){	
				echo "<div><input type=\"checkbox\" ".($area_r1["biz_id"]>0?"checked=\"checked\"":"")." name=\"props[]\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
			}
?>				</td>
			</tr>
			<tr>
				<td class="b" valign="top">אירועים:</td>
				<td colspan="2" id="re">
	<?php
				$area_q1=db_query("SELECT `e`.*,`b`.`biz_id` FROM `mod_eventstypes` AS `e` LEFT JOIN `mod_biz_event` AS `b` ON `e`.`id`=`b`.`event_id` AND `b`.`biz_id`=".$biz["id"])or die(db_error());
				while($area_r1=db_fetch_array($area_q1)){	
					echo "<div><input type=\"checkbox\" ".($area_r1["biz_id"]>0?"checked=\"checked\"":"")." name=\"event[]\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
				}
	?>			
				</td>
			</tr>
			<tr>
			<td colspan="3" valign="top" style="padding-top:15px;">
				<input type="checkbox" name="newsletter" value="1" <?=($biz["newsletter"]==1)?" checked=\"checked\"":"";?>/>אני מאשר קבלת ניוזלטר מהאתר<br />
			</td>

		</tr>
			<tr>
				<td class="b">*שינוי סיסמה:</td>
				<td><input type="password" class="bg" name="newpass"  value="" /></td><td></td>
			</tr>

			<tr>
				<td class="b">*אימות סיסמה ישנה:</td>
				<td><input type="password" class="bg" name="pass"  value="" /></td><td></td>
			</tr>
		<tr>
			<td colspan="2"></td>
			<td style="padding-top:15px;width:240px;text-align:left">
				<input type="image" src="images/send.png" alt="שלח" value="שלח" />
			</td>		
		</tr>
		</table>
		</form>
<?php		
	}
}
?>