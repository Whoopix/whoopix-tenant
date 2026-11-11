<?php
$TEMP_PAGE_TITLE = "אינדקס עסקים";

function PAGE_TEXT(){
	
	
	
	
	
	
	?>
	
	<div class="row g-4 job-grid">

  <?php
					$i = 1;
					$GET_CAT_q=db_query("SELECT * FROM `mod_sop_cats` ORDER BY `order` ASC");
					while ($GET_CAT_r=db_fetch_array($GET_CAT_q)){

					$q=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".$GET_CAT_r['id']."
					 AND `parent` = '0' ORDER BY `order` LIMIT 3")or die(db_error());?>
     	<div class="col-md-6 col-xl-4">
                <div class="job-board-card featured">
               
              <div class="category-icon">
                <img
                  src="resources/design/images/categories/business-management.png"
                  alt="Business Management"
                />
              </div>
              <h5><?=$GET_CAT_r['title'];?></h5>
              <a href=",categories,<?=$GET_CAT_r['id'];?>" class="category-jobs"><i class="fa fa-briefcase" aria-hidden="true"></i> צפה בקטגוריה</a>
          
			
			
                </div>
              </div>
			<?php  } ?>
	</div>
	<?php
	
	
}




			
				$TEMP_TEMPLATE = 3;
	
?>
