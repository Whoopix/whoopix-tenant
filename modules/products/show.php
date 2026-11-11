<?php
$PUT_LEFT=false;


$get_prod_q=db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_products` AS `p` 
													INNER JOIN `mod_products_list` AS `l` 
													ON `p`.`prod_id`=`l`.`id` 
													WHERE `p`.`lang_id`=".LANG." AND `l`.`id`='".intval($get_id)."'")or die(db_error());
$get_prod_r=db_fetch_array($get_prod_q);												
$TEMP_PAGE_TITLE  = $get_prod_r["lang_title"];
$TEMP_INPAGE_TITLE = $get_prod_r["lang_title"];
$TEMP_TEMPLATE = 2;
if(!function_exists("PAGE_TEXT")){
function PAGE_TEXT() {
	global $bis_s,$CART_STAT, $get_id, $get_page,$GET_T,$lang, $msg,$get_prod_r;
	
		$G_cart=db_query("SELECT `quantity` FROM `mod_cart` WHERE `hash`='".db_escape($_SESSION["cart"])."' AND `prod_id`=".intval($get_id));
		$G_cart_r=db_fetch_array($G_cart);
		if($get_prod_r["pic1"]=="")
			$get_prod_r["pic1"]="../../images/nopic.png";
		if($get_prod_r["pic3"]=="")
			$get_prod_r["pic3"]="../../images/nopic.png";
?>

		<div id="right_prod" style="width:220px;float:left">
			<div class="pic"><a href="https://example.com/files/products/<?=$get_prod_r["pic3"];?>" onclick="window.open(this.href);return false;"><img src="https://example.com/files/products/<?=$get_prod_r["pic1"];?>" alt="" /></a></div>
			<div style="text-align:center;">
				<!--<a href="https://example.com/files/products/<?=$get_prod_r["pic3"];?>" onclick="window.open(this.href);return false;">הגדל תמונה</a>-->
				<div class="p_price" <?=$get_prod_r["onsale"]>0?" style=\"text-decoration:line-through\"":"";?>><b>מחיר:</b> <?=$get_prod_r["price"];?> ש"ח</div>
<?php
				if($get_prod_r["onsale"]>0){
?>
					<div class="p_price"><b>מחיר מבצע:</b> <?=$get_prod_r["newprice"];?> ש"ח</div>
<?php
				}
?>
			</div>
			<div style="text-align:center;padding-right:30px;">
				<div class="quantityDIV" style="display:block;padding:5px 0px 0px 5px;float:right;">
					<a title="minus" href="#">-</a>
					<input type="text" class="quantity" value="<?=intval($G_cart_r["quantity"]>0?$G_cart_r["quantity"]:1);?>" />
					<a title="plus" href="#">+</a>
				</div>
				<a href="" style="display:block;float:right;" class="addtocart" rel="<?=$get_prod_r["prod_id"];?>"><img src="images/addcart.png" alt="" /></a>
	<?php
		/*
				<select name="quantity" style="display:block;float:right;margin-top:7px;">
<?php
					for($i=1;$i<=($G_cart_r["quantity"]>20?$G_cart_r["quantity"]:20);$i++){
?>
						<option value="<?=$i;?>"<?=$G_cart_r["quantity"]==$i?" selected=\"selected\"":"";?>><?=$i;?></option>
<?php
					}
?>
				</select>
	*/
	?>
			</div>
		</div>
		<div id="left_prod" style="float:left;width: 465px;">
					<div><?=preg_replace("/(WIDTH: [0-9]+(pt|%|px);?|width=\"[0-9]+%?\")/i","",$get_prod_r["long_desc"]);?></div>
					<div><?=preg_replace("/(WIDTH: [0-9]+(pt|%|px);?|width=\"[0-9]+%?\")/i","",$get_prod_r["text"]);?></div>
		</div>
<?php

}
}
?>