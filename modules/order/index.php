<?php
	$TEMP_PAGE_TITLE = "הזמנה";
	$TEMP_INPAGE_TITLE = "הזמנה";
	$TEMP_TEMPLATE = 2;

		$DATA=array();
		if($_POST["sub"]!="SUBTRUE")
			header("Location: /,cart");
		function PAGE_TEXT(){
			global $DATA;
			$pro=array_keys((array)$_POST["quantity"]);
			$pro=array_filter($pro,create_function('$var','return (intval($var)>0);'));
			$pro=array_unique($pro);
			$pro[]=0;
	$prods_q=db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_products` AS `p` 
														INNER JOIN `mod_products_list` AS `l` 
														ON `p`.`prod_id`=`l`.`id` 
														WHERE `p`.`lang_id`=".LANG." AND `l`.`id` IN(".implode(",",$pro).")")or die(db_error());
			
?>
	<form id="goorder" action=",order,submit" method="post">
	<table id="cart_table" cellpadding="0" cellspacing="0">
		<tr>
			<th class="prod_name">שם המוצר</th>
			<th class="prod_q">כמות</th>
			<th class="prod_p">מחיר ליחידה</th>
			<th class="prod_t">סה"כ</th>
		</tr>
<?php
	if(db_num_rows($prods_q)>0){
	$total=0;
	$tq=0;
	while($prods_r=db_fetch_array($prods_q)){
		$q=intval($_POST["quantity"][$prods_r["prod_id"]]);
		$tq+=$q;
		$price=$prods_r["onsale"]>0?$prods_r["newprice"]:$prods_r["price"];
		$total+=$price*$q;
?>
		<tr id="<?=$prods_r["prod_id"];?>">
			<td class="prod_name"><?=$prods_r["lang_title"];?></td>
			<td class="prod_q">
				<?=$q;?><input type="hidden" name="order_prod[<?=$prods_r["prod_id"];?>]" value="<?=$q;?>" />
			</td>
			<td class="prod_p"><?=$price;?> ש"ח</td>
			<td class="prod_t"><?=$price*$q?> ש"ח</td>
		</tr>
<?php
	}


?>
		<tr>
			<td colspan="1" style="padding-right:10px;border:0px;">סיכום כללי :</td>
			<td class="prod_q"><span><span><?=$tq;?></span></span></td>
			<td style="border:0px;"></td>
			<td class="prod_t"><span><span><?=$total;?></span> ש"ח</span></td>
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
<?php
					$city_q=db_query("SELECT `p`.`city_id`,`l`.`zipcode`,`p`.`title` FROM `data_city` AS `p` 
													INNER JOIN `data_city_list` AS `l` 
													ON `p`.`city_id`=`l`.`id` 
													WHERE `l`.`id`=".intval($_POST["city"])." AND `p`.`lang_id`=".LANG)or die(db_error());
					$city_r=db_fetch_array($city_q)
?>
			פרטים אישיים:
			<table>
				<tr>
					<td><b>שם פרטי:</b></td><td><?=htmlspecialchars($_POST["fname"]);?><input type="hidden" name="fname" value="<?=htmlspecialchars($_POST["fname"]);?>" /></td>
				</tr>
				<tr>
					<td><b>שם משפחה:</b></td><td><?=htmlspecialchars($_POST["lname"]);?><input type="hidden" name="lname" value="<?=htmlspecialchars($_POST["lname"]);?>" /></td>
				</tr>
				<tr>
					<td><b>דוא"ל:</b></td><td><?=htmlspecialchars($_POST["email"]);?><input type="hidden" name="email" value="<?=htmlspecialchars($_POST["email"]);?>" /></td>
				</tr>
				<tr>
					<td><b>טלפון:</b></td><td><?=htmlspecialchars($_POST["phone"]);?><input type="hidden" name="phone" value="<?=htmlspecialchars($_POST["phone"]);?>" /></td>
				</tr>
				<tr>
					<td><b>נייד:</b></td><td><?=htmlspecialchars($_POST["cell"]);?><input type="hidden" name="cell" value="<?=htmlspecialchars($_POST["cell"]);?>" /></td>
				</tr>
				<tr>
					<td><b>עיר:</b></td><td><?=htmlspecialchars($city_r["title"]);?><input type="hidden" name="city" value="<?=intval($_POST["city"]);?>" /></td>
				</tr>
				<tr>
					<td><b>מיקוד:</b></td><td><?=htmlspecialchars($_POST["zip"]);?><input type="hidden" name="zip" value="<?=htmlspecialchars($_POST["zip"]);?>" /></td>
				</tr>
				<tr>
					<td><b>כתובת:</b></td><td><?=htmlspecialchars($_POST["address"]);?><input type="hidden" name="address" value="<?=htmlspecialchars($_POST["address"]);?>" /></td>
				</tr>
			</table>
			<input type="hidden" name="sub" value="SUBTRUE" /><input type="submit" class="button" value="שלח הזמנה" /><input type="button" class="button" onclick="$('#goorder').attr('action',',cart');$('#goorder').submit();" value="תקן פרטים" />
			</form>
<?php
		}	
?>

