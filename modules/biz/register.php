<?php
	if(isset($_SESSION["biz"]))
		header("Location: ,biz");
$TEMP_PAGE_TITLE  = 'הרשמת עסקים';
$TEMP_INPAGE_TITLE = 'הרשמת עסקים';
$TEMP_TEMPLATE = 2;

if(!function_exists("PAGE_TEXT")){
	function PAGE_TEXT() {
		
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
			}elseif(db_num_rows($c_mail)>0){
				$error=true;
				echo "אימייל קיים";
			}
			elseif(trim($_POST["pass"])==""){
				$error=true;
				echo "אנא הכנס סיסמה";			
			}
			elseif(trim($_POST["name"])==""){
				$error=true;
				echo "אנא הכנס שם לעסק";			
			}		

			elseif(trim($_POST["phone"])==""){
				$error=true;
				echo "אנא הכנס טלפון לעסק";			
			}		
			elseif(trim($_POST["show_mail"]) != "" && !preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",$_POST["show_mail"])){
				$error=true;
				echo "אימייל לא תקין";					
			}
			elseif($_POST["terms"]!=1){
				$error=true;
				echo "אנא הסכם לתנאי השימוש";			
			}
			if(!$error){
				$pass=pass($_POST["pass"]);
				$Pcode=gocode(8);
				db_query("INSERT INTO `mod_biz`
					(`email` ,`pass` ,`name` ,`desc` ,`c_name` ,`c_phone` ,`phone` ,`phone2` ,`fax` ,`show_mail` ,`area` ,`address` ,`active`,`code`,`newsletter`)
					VALUES
					('".db_escape($_POST["email"])."',
					'".$pass."',
					'".db_escape($_POST["name"])."',
					'".db_escape($_POST["desc"])."',
					'".db_escape($_POST["c_name"])."',
					'".db_escape($_POST["c_phone"])."',
					'".db_escape($_POST["phone"])."',
					'".db_escape($_POST["phone2"])."',
					'".db_escape($_POST["fax"])."',
					'".db_escape($_POST["show_mail"])."',
					".intval($_POST["area"]).",
					'".db_escape($_POST["address"])."',
					0,
					'".$Pcode."',
					'".intval($_POST["newsletter"])."')");
				$biz_id=db_insert_id();
				foreach((array)$_POST["props"] AS $value){
					db_query("INSERT INTO `mod_biz_props` (`biz_id`,`prop_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				foreach((array)$_POST["event"] AS $value){
					db_query("INSERT INTO `mod_biz_event` (`biz_id`,`event_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				foreach((array)$_POST["sop"] AS $value){
					db_query("INSERT INTO `mod_biz_sop` (`biz_id`,`sop_id`) VALUES (".$biz_id.",".intval($value).")");
				}
				$Pemail=$_POST["email"];
				
				
				$block=get_block(21);
				$link="http://".SITEDOMAIN."/".",biz,email"."&code=".$Pcode."&email=".$Pemail;
					$text=str_replace(
						array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
						array($Pemail,$Pcode,htmlspecialchars($_POST["name"]),$link,htmlspecialchars($_POST[name]),SITEDOMAIN)
						,$block["text"]);
					$subject=str_replace(
						array("{__EMAIL__}","{__CODE__}","{__NAME__}","{__LINK__}","{__SITENAME__}","{_SITEDOMAIN_}"),
						array($Pemail,$Pcode,htmlspecialchars($_POST["name"]),$link,htmlspecialchars($_POST[name]),SITEDOMAIN)
						,$block["title"]);
					send_mail($Pemail,htmlspecialchars($_POST[name])." <no-reply@".SITEDOMAIN.">",$subject,$text);//
				
				$_SESSION["biz_id"]=$biz_id;
				$_SESSION["biz_pass"]=$pass;
				echo "<% BLOCK_15 %>";
			}
		
		
		}
		if($error){
			$_POST=ArrayClearHTML($_POST);
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
					 });
					 </script>
		<form id="realregform" action=",biz,register" method="post" autocomplete="off">
			<input type="hidden" name="sub" value="true" />
		<table id="regform">
			<tr>
				<td class="b" style="width:150px;">*אימייל:</td>
				<td><input type="text" class="bg" name="email"  value="<?=$_POST["email"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b">*סיסמה:</td>
				<td><input type="password" class="bg" name="pass"  value="" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">*שם העסק:</td>
				<td><input type="text" class="bg" name="name"  value="<?=$_POST["name"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b" valign="top">תיאור העסק:</td>
				<td><textarea class="bg" style="resize: none;height:80px;" name="desc" ><?=$_POST["desc"];?></textarea></td><td></td>
			</tr>
			<tr>
				<td class="b">שם איש קשר:</td>
				<td><input type="text" class="bg" name="c_name"  value="<?=$_POST["c_name"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">טלפון איש קשר:</td>
				<td><input type="text" class="bg" name="c_phone"  value="<?=$_POST["c_phone"];?>" /></td><td></td>
			</tr>
			<tr>
				<td class="b">*טלפון:</td>
				<td><input type="text" class="bg" name="phone"  value="<?=$_POST["phone"];?>" /></td><td></td>
			</tr>			
			<tr>
				<td class="b">טלפון נוסף:</td>
				<td><input type="text" class="bg" name="phone2"  value="<?=$_POST["phone2"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">פקס:</td>
				<td><input type="text" class="bg" name="fax"  value="<?=$_POST["fax"];?>" /></td><td></td>
			</tr>	
			<tr>
				<td class="b">אימייל של העסק:</td>
				<td><input type="text" class="bg" name="show_mail"  value="<?=$_POST["show_mail"];?>" /></td><td></td>
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
						echo "<option value=\"".$area_r1["id"]."\">".$area_r1["title"]."</option>";
					}
					echo "</optgroup>";
				
				}
?>			
			</select></td><td></td>
			</tr>	
			<tr>
				<td class="b">כתובת:</td>
				<td><input type="text" class="bg" name="address" value="<?=$_POST["address"];?>" /></td><td></td>
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
				$area_q1=db_query("SELECT * FROM `mod_sop` WHERE `cat_id`=".$ACT_CATS_r["id"]." ORDER BY id DESC");
				while($area_r1=db_fetch_array($area_q1)){	
					echo "<div><input type=\"checkbox\" name=\"sop[]\" rel=\"".implode(",",(array)$props[$area_r1["id"]])."\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
				}
			}
?>			
			</td>
			</tr>
			<tr id="ps">
				<td class="b" valign="top">מאפיינים:</td>
				<td colspan="2"><?php
			$area_q1=db_query("SELECT * FROM `mod_props`");
			while($area_r1=db_fetch_array($area_q1)){	
				echo "<div><input type=\"checkbox\" name=\"props[]\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
			}
?>				</td>
			</tr>
			<tr>
				<td class="b" valign="top">אירועים:</td>
				<td colspan="2" id="re">
	<?php
				$area_q1=db_query("SELECT * FROM `mod_eventstypes`");
				while($area_r1=db_fetch_array($area_q1)){	
					echo "<div><input type=\"checkbox\" name=\"event[]\" value=\"".$area_r1["id"]."\">".$area_r1["title"]."</div>";
				}
	?>			
				</td>
			</tr>
		<tr>
			<td colspan="2" valign="top" style="padding-top:15px;">
				<input type="checkbox" name="terms" value="1" <?=($Vterms==1)?" checked=\"checked\"":"";?>/>אני מאשר שקראתי את תנאי השימוש באתר ואני מסכים להם<br /><br />
				<input type="checkbox" name="newsletter" value="1" <?=($Vnewsletter==1)?" checked=\"checked\"":"";?>/>אני מאשר קבלת ניוזלטר מהאתר<br />
			</td>
			<td style="padding-top:15px;width:240px;text-align:left">
				<input type="submit" class="submit" onclick="" value="" />
			</td>
		</tr>
		</table>
		</form>
<?php		
		}
	}
}
?>