<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;


$TEMP_PAGE_TITLE  = 'כתבו חוות דעת';
$TEMP_INPAGE_TITLE = 'כתבו חוות דעת';



function PAGE_TEXT() {
	global $get_id, $bz;
	

$USER_LOGIN=loggedIn();

	if ($USER_LOGIN['id'] != ''){ ?>
						
										
			<form action=",feedback,add_submit" method="post" onsubmit="event.preventDefault();">

								<div style="width:100%;height:80px;">
												<input type="text" class="col-md-10" 
												style="width:100%;height:50px;" 
												placeholder="חיפוש מוצר / עסק במערכת" id="searchauto" name="product_id">

												<div id="empty-message">
												</div>
												
											</div>
											
										


			</form>
				
	

	
	<?php

	} else {
		
		?>
		התחבר כדי להוסיף חוות דעת ועסק חדש
		<?php
	}

}
?>