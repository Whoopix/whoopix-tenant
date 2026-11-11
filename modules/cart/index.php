<?php
$PUT_LEFT=false;
$TEMP_PAGE_TITLE  = 'עגלת קניות';
$TEMP_INPAGE_TITLE = 'עגלת קניות';
$TEMP_TEMPLATE = 2;

if(!function_exists("PAGE_TEXT")){
function PAGE_TEXT() {
	global $bis_s,$CART_STAT, $get_id, $get_page,$GET_T,$lang, $msg,$S_WORD,$USER_LOGIN;
	echo $msg;
	


	$prods_q=db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_products` AS `p` 
														INNER JOIN `mod_products_list` AS `l` 
														ON `p`.`prod_id`=`l`.`id` 
														INNER JOIN `mod_cart` AS `c` 
														ON `l`.`id`=`c`.`prod_id` 
														WHERE `p`.`lang_id`=".LANG." AND `c`.`hash`='".db_escape($_SESSION["cart"])."' AND `c`.`quantity`>0")or die(db_error());
?>
<form id="orderform" action=",order" method="post">
	<table id="cart_table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="prod_name">שם המוצר</th>
			<th class="prod_q">כמות</th>
			<th class="prod_p">מחיר ליחידה</th>
			<th class="prod_t">סה"כ</th>
			<th class="prod_r">הסר</th>
		</tr>
<?php
	if(db_num_rows($prods_q)>0){
	while($prods_r=db_fetch_array($prods_q)){
		$price=$prods_r["onsale"]>0?$prods_r["newprice"]:$prods_r["price"];
?>
		<tr id="<?=$prods_r["prod_id"];?>">
			<td class="prod_name"><?=$prods_r["lang_title"];?></td>
			<td class="prod_q">
				<div class="quantityDIV" style="padding:5px 0px 0px 5px;text-align:center;">
					<a title="minus" href="#">-</a>
					<input type="text" class="quantity" name="quantity[<?=$prods_r["prod_id"];?>]" value="<?=intval($prods_r["quantity"]);?>" />
					<a title="plus" href="#">+</a>
				</div>			
<?/*
				<select title="quantity" name="quantity[<?=$prods_r["prod_id"];?>]">
<?php
				for($i=1;$i<=($prods_r["quantity"]>20?$prods_r["quantity"]:20);$i++){
?>
					<option value="<?=$i;?>"<?=($i==$prods_r["quantity"]?" selected=\"selected\"":"");?>><?=$i;?></option><?=$prods_r["lang_title"];?>
<?php
				}
?>
				</select>
				*/
				?>
			</td>
			<td class="prod_p"><span class="price"><?=$price;?></span> ש"ח</td>
			<td class="prod_t"><span class="totalprice"><?=$price*$prods_r["quantity"];?></span> ש"ח</td>
			<td class="prod_r"><a href="" class="removecart">הסר</a></td>
		</tr>
<?php
	}


?>
		<tr>
			<td colspan="1" style="padding-right:10px;border:0px;">סיכום כללי :</td>
			<td class="prod_q"><span><span class="cart_quantity"><?=$CART_STAT["quantity"];?></span></span></td>
			<td style="border:0px;"></td>
			<td class="prod_t"><span><span class="cart_amount"><?=$CART_STAT["amount"];?></span> ש"ח</span></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:center;border:0px;"><a href="" id="clearcart">רוקן עגלה</a> </td>
		</tr>
<?php
	}else{
?>
		<tr>
			<td colspan="5" style="text-align:center;border:0px;">לא נבחרו מוצרים </td>
		</tr>
<?php
	}
?>
	</table>
			<br />
			<div style="float:right;">
<?php
		if($_POST["sub"]=="SUBTRUE"){
			$Vfname=$_POST["fname"];
			$Vlname=$_POST["lname"];
			$Vemail=$_POST["email"];
			$Vphone=$_POST["phone"];
			$Vcell=$_POST["cell"];
			$Vcity=$_POST["city"];
			$Vzip=$_POST["zip"];
			$Vaddress=$_POST["address"];
		}else{
			$Vfname=$USER_LOGIN["fname"];
			$Vlname=$USER_LOGIN["lname"];
			$Vemail=$USER_LOGIN["email"];
			$Vphone=$USER_LOGIN["phone"];
			$Vcell=$USER_LOGIN["cell"];
			$Vcity=$USER_LOGIN["city"];
			$Vzip=$USER_LOGIN["zip"];
			$Vaddress=$USER_LOGIN["address"];
		}

?>	
	<table id="regform" cellpadding="3" cellspacing="1">

		<tr>
			<td class="bg red">*<% LANG_FIRSTNAME %>:</td>
			<td class="bg"><input type="text" name="fname" class="smallInput" value="<?=htmlspecialchars($Vfname);?>" /></td>
		</tr>
		<tr>
			<td class="bg red">*<% LANG_FAMILYNAME %>:</td>
			<td class="bg"><input type="text" name="lname" class="smallInput" value="<?=htmlspecialchars($Vlname);?>" /></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="5" class="sep"></td>
		</tr>	
		<tr>
			<td class="bg red">*<% LANG_EMAIL %>:</td>
			<td class="bg" colspan="3"><input type="text" name="email" class="smallInput" value="<?=htmlspecialchars($Vemail);?>" /></td>
			<td><span style="color:red;display:none;" id="emailajax">קיים במערכת</span></td>
		</tr>
		<tr>
			<td class="bg"><% LANG_TELEPHONE %>:</td>
			<td class="bg"><input type="text" name="phone" class="smallInput" value="<?=htmlspecialchars($Vphone);?>" /></td>
		</tr>
		<tr>
			<td class="bg"><% LANG_PHONEMOBILE %>:</td>
			<td class="bg"><input type="text" name="cell" class="smallInput" value="<?=htmlspecialchars($Vcell);?>" /></td>
			<td></td>
		</tr>
		<tr>
			<td class="bg"><% LANG_CITY %>:</td>
			<td class="bg">
				<select name="city">
<?php
					$city_q=db_query("SELECT `p`.`city_id`,`l`.`zipcode`,`p`.`title` FROM `data_city` AS `p` 
													INNER JOIN `data_city_list` AS `l` 
													ON `p`.`city_id`=`l`.`id` 
													WHERE `p`.`lang_id`=".LANG)or die(db_error());
					while($city_r=db_fetch_array($city_q)){
?>
						<option title="<?=$city_r["zipcode"];?>" value="<?=$city_r["city_id"];?>"<?=($Vcity==$city_r["city_id"]?" selected=\"\"":"");?>><?=$city_r["title"];?></option>
<?php
					}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="bg" ><% LANG_POSTCODE %>:</td>
			<td class="bg"><input type="text" name="zip" class="smallInput" value="<?=htmlspecialchars($Vzip);?>" /></td>
			<td></td>
		</tr>
		<tr>
			<td class="bg"><% LANG_ADDRESS %>:</td>
			<td class="bg" colspan="3"><input type="text" name="address" class="smallInput" value="<?=htmlspecialchars($Vaddress);?>" /></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:center;">
				<input type="hidden" name="sub" value="SUBTRUE" /><input type="submit" class="button register" onclick="" value="המשך להזמנה" />
			</td>
		</tr>
	</table>
	</form>			
			
			</div>
<?php
	if($USER_LOGIN==false){
?>
			<div style="float:left;border-right:1px black solid;padding-right:10px;">
			<form id="realregform" action=",members" method="post" onsubmit="return onreg();">
			<table style="width:300px;" cellpadding="3" cellspacing="1">
				<tr>
					<td class="bg" style="width:100px;">דואר אלקטרוני</td>
					<td class="bg"><input type="text" name="email" class="bigInput" style="width:122px;height:16px;border:1px #676767 solid;" value="<?=$Vemail;?>" /></td>
				</tr>
				<tr>
					<td class="bg" style="width:100px;">סיסמה</td>
					<td class="bg"><input type="password" name="pass" class="bigInput" style="width:122px;height:16px;border:1px #676767 solid;" value="" /></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="rem" value="1" />זכור אותי
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<a href=",members,pass">שכחת סיסמה?</a>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;">
						<input type="hidden" name="ret" value=",order" /><input type="hidden" name="sub" value="SUBTRUE" /><input type="submit" class="button enter" value="כניסה" style="margin-bottom:5px;" />
					</td>
				</tr>
			</table>
			</form>
			</div>
<?php
	}
?>
<?php

}
}
?>