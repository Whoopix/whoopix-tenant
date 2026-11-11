<?php
	if(!is_array($USER_LOGIN))
		header("Location: ,members");
	else{
		
		
		$TEMP_PAGE_TITLE = "כתוב פידבק";
		$TEMP_INPAGE_TITLE = "כתוב פידבק";

		function PAGE_TEXT() {
			global $USER_LOGIN, $get_id;
			$order=true;
			
			$get_product_data = db_query("SELECT * FROM mod_biz WHERE id = '".$get_id."'");
			$gpd = db_fetch_array($get_product_data);
			
			
			?>
			
			כתוב פידבק למוצר <?php echo $get_id; echo $gpd['name']; ?>
			<hr>
			
			<form>
			
			
			<input type="" name="">
			
			</form>
			<?php
			

			}
		}
		$TEMP_TEMPLATE = 2;
	
?>