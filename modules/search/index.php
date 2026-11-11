<?php

	foreach (['location','From','To','order','page','rating','voters','price'] as $__k) {
		if (!isset($_GET[$__k])) { $_GET[$__k] = ''; }
	}
	if (!isset($_GET['areas']) || !is_array($_GET['areas'])) { $_GET['areas'] = []; }

	$event=$get_id;
	$cat_id=$get_id;

	
	if (!isset($TEMP_PAGE_TITLE))   { $TEMP_PAGE_TITLE   = ''; }
	if (!isset($TEMP_INPAGE_TITLE)) { $TEMP_INPAGE_TITLE = ''; }

		$GET_CAT_q=db_query("SELECT * FROM `mod_sop_cats` WHERE `id`=".intval($cat_id));
		

		
			$TEMP_PAGE_TITLE   = (string) ($GET_CAT_r["title"] ?? '');
			$TEMP_INPAGE_TITLE = (string) ($GET_CAT_r["title"] ?? '');
			$TEMP_TEMPLATE     = 3;

			$q=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".intval($cat_id)."
					 ORDER BY `order`")or die(db_error());


		

				?>


				<?php function subcats() {
					global $get_id;
				?>
					<?php

				$i = 1;
				$cat_id=$get_id;
				$qR=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".$cat_id." AND `parent` = '0'
					 ORDER BY `order`")or die(db_error());
					 while($rR=db_fetch_array($qR)) {
						?>
					
				

                  <?php 
						if($i % 20 == 0){


							?>
							<div style="clear:both;"></div>
							<?php 
						}

						?>



						<?php


					$i++;
				}

				?>
			<?php } ?>
			
			

				<?php function catsearch() { ?>
				
				
				<div class="col-md-12">
				<h3>סינון לפי מחיר</h3>
				<form action="http://<?php echo $_SERVER["HTTP_HOST"]; echo $_SERVER["REQUEST_URI"]; ?>">



				<input class="col-md-4 inputdesign" style="padding:0px;" placeholder="ממחיר" name="From">


				<input class="col-md-4 inputdesign" style="padding:0px;" placeholder="עד מחיר" name="To">


				<?php if($_GET['order'] != ''){ ?>
					<input type="hidden" name="order" value="<?php echo $_GET['order']; ?>">
				<?php } ?>
				<?php if($_GET['price'] != ''){ ?>
					
					
					
					
					
				<?php } ?>
				<?php if($_GET['rating'] != ''){ ?>
					<input type="hidden" name="rating" value="<?php echo $_GET['rating']; ?>">
				<?php } ?>
				<?php if($_GET['voters'] != ''){ ?>
					<input type="hidden" name="voters" value="<?php echo $_GET['voters']; ?>">
				<?php } ?>

				<input class="col-md-2 inputdesignsend" style="padding:0px;" value="סנן" type="submit">

				<!-- HIDDEN INPUTS TO EXIST URL IF()--->
				</form>

				</div>

				<div class="col-md-12" style="padding-top:15px;">
					<h3>סינון לפי מיקום</h3>
					<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>

					<form action="http://<?php echo $_SERVER["HTTP_HOST"]; echo $_SERVER["REQUEST_URI"]; ?>">
						<input class="col-md-8 inputdesign col-sm-4" style="padding:0px;" id="locationTextField" type="text" name="location" placeholder="חפש מיקום">
							<?php if($_GET['order'] != ''){ ?>
					<input type="hidden" name="order" value="<?php echo $_GET['order']; ?>">
				<?php } ?>
							<?php if($_GET['From'] != ''){ ?>
					
					
					
					<input type="hidden" name="From" value="<?php echo $_GET['From']; ?>">
					
					
				<?php } ?>
				
				<?php if($_GET['To'] != ''){ ?>
				<input type="hidden" name="To" value="<?php echo $_GET['To']; ?>">
				<?php } ?>
				<?php if($_GET['rating'] != ''){ ?>
					<input type="hidden" name="rating" value="<?php echo $_GET['rating']; ?>">
				<?php } ?>
				<?php if($_GET['voters'] != ''){ ?>
					<input type="hidden" name="voters" value="<?php echo $_GET['voters']; ?>">
				<?php } ?>

			
						<input class="col-md-2 inputdesignsend" style="padding:0px;" value="סנן" type="submit">
						
						
					</form>

					<script>
						function init() {
							var input = document.getElementById('locationTextField');
							var autocomplete = new google.maps.places.Autocomplete(input);
						}
						google.maps.event.addDomListener(window, 'load', init);
					</script>
				</div>


				<div class="col-md-12 col-sm-12 area-filtering" style="padding-top: 15px; margin-bottom: 15px; display: flex; flex-direction: column;">
					<h3>סינון לפי אזור</h3>

					<form action="http://<? echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>">
					
					
								
				<?php if($_GET['order'] != ''){ ?>
					<input type="hidden" name="order" value="<?php echo $_GET['order']; ?>">
				<?php } ?>
				<?php if($_GET['From'] != ''){ ?>
					
					
					
					<input type="hidden" name="From" value="<?php echo $_GET['From']; ?>">
					
					
				<?php } ?>
				
				<?php if($_GET['To'] != ''){ ?>
				<input type="hidden" name="To" value="<?php echo $_GET['To']; ?>">
				<?php } ?>
				
				<?php if($_GET['rating'] != ''){ ?>
					<input type="hidden" name="rating" value="<?php echo $_GET['rating']; ?>">
				<?php } ?>
				<?php if($_GET['voters'] != ''){ ?>
					<input type="hidden" name="voters" value="<?php echo $_GET['voters']; ?>">
				<?php } ?>

						<?php
							$areas = [
								'gush_dan' => 'גוש דן',
								'hasharon' => 'השרון',
								'tzafon' => 'צפון',
								'darom' => 'דרום'
							];
						?>
						<?php foreach ($areas as $name => $displayName) { ?>
							<div class="area">
								<label class="area-label">
									<input name="areas[]"
										   value="<?echo $name ?>"
										   <?echo isset($_GET['areas']) && in_array($name, $_GET['areas'])? 'checked' : '' ?>
										   type="checkbox" />
									<?echo $displayName ?>
								</label>
							</div>
						<?php } ?>
						<div class="area all-areas">
							<label class="area-label all-areas-label">
								<input name="all_areas"
									   value="true"
									   type="checkbox" />
								כל הארץ
							</label>
						</div>
						<input class="col-md-2 inputdesignsend" style="padding:0; margin:4px 0 0;" value="סנן" type="submit" />
					</form>
				</div>
				<script>
	

					document.addEventListener('DOMContentLoaded', function(event) {
						var $areaFiltering = $('.area-filtering');

						var handleAreaCheckboxes = function() {
							var $checkboxes =  $areaFiltering.find('.area:not(.all-areas) :checkbox');
							var $checkedCheckboxes = $checkboxes.filter(':checked');
							$areaFiltering.find('.area.all-areas :checkbox').prop('checked', $checkboxes.length === $checkedCheckboxes.length);
						};

						$('.area-filtering .area :checkbox').on('change', function() {
							var $this = $(this);
							if ($this.attr('name') === 'all_areas') {
								var $checkboxes =  $areaFiltering.find('.area:not(.all-areas) :checkbox');
								$checkboxes.prop('checked', $this.is(':checked'));
							} else {
								handleAreaCheckboxes();
							}
						});

						handleAreaCheckboxes();
					});
				</script>


				<?php } ?>






				<?php
				function PAGE_TEXT() {
					global $cat_id,$event,$q,$GET_CAT_r,$GET_EVENT_r,$get_id;

				
$get_id = (int)$get_id;

$word = $_GET['word'];


$get_sub = db_query("SELECT id FROM mod_sop WHERE cat_id=".$get_id);

$ids = [];
while($rr = db_fetch_array($get_sub)) {
    $ids[] = (int)$rr['id'];
}
$ids[] = $get_id;

// אם אין IDs בכלל → תן fallback
if (empty($ids)) {
    $ids[] = 0;
}

$sql = "
SELECT *
FROM mod_biz
WHERE mod_biz.cat_id IN (".implode(',', $ids).")
";

// חיפוש
if ($word != '') {
    $sql .= " AND mod_biz.name LIKE '%".$word."%'";
} else {
    $sql .= " AND 1=0";
}

// pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = 20 * ($page - 1);

$sql .= " LIMIT $offset, 20";

$main_query = db_query($sql) or die(db_error());
				?>
				
				
    <section class="jobs-board">
      <div class="container">
        <div class="row g-4">
			<aside class="col-lg-3">
	<form method="GET">

		<div class="filter-card">
			<h5>אזור</h5>
			<ul class="filter-list">
				<li><label><input type="checkbox" name="area[]" value="center"> מרכז</label></li>
				<li><label><input type="checkbox" name="area[]" value="north"> צפון</label></li>
				<li><label><input type="checkbox" name="area[]" value="south"> דרום</label></li>
				<li><label><input type="checkbox" name="area[]" value="jerusalem"> ירושלים</label></li>
			</ul>
		</div>

		<div class="filter-card">
			<h5>תחום</h5>
			<ul class="filter-list">
				<li><label><input type="checkbox" name="cat[]" value="1"> שיווק</label></li>
				<li><label><input type="checkbox" name="cat[]" value="2"> עיצוב</label></li>
				<li><label><input type="checkbox" name="cat[]" value="3"> פיתוח אתרים</label></li>
				<li><label><input type="checkbox" name="cat[]" value="4"> שיפוצים</label></li>
			</ul>
		</div>

		<div class="filter-card">
			<h5>טווח מחירים</h5>
			<ul class="filter-list">
				<li><label><input type="checkbox" name="price[]" value="0-1000"> עד 1,000₪</label></li>
				<li><label><input type="checkbox" name="price[]" value="1000-3000"> 1,000₪ - 3,000₪</label></li>
				<li><label><input type="checkbox" name="price[]" value="3000-7000"> 3,000₪ - 7,000₪</label></li>
				<li><label><input type="checkbox" name="price[]" value="7000+"> 7,000₪+</label></li>
			</ul>
		</div>

		<button class="btn btn-primary w-100 mt-3">סנן</button>

	</form>
</aside>

 <div class="col-lg-9">
				<?php

				while ($t = db_fetch_array($main_query)){

					?>



<div class="job-list-item" data-sort-op="3" data-sort-in="1">

	<div class="job-card-status">
		<span class="job-type fulltime">עסק</span>
	</div>

	<div class="job-list-main">

		<div class="job-list-logo">
			<?php
			if (file_exists('files/biz_logo/'.$t['logofile'])) {
			?>
			<img src="files/biz_logo/<?php echo $t['logofile']; ?>">
			<?php } else { ?>
			<img src="img/logo.png">
			<?php } ?>
		</div>

		<div class="job-list-content">

			<h4>
				<a href="">
				<?php echo $t['name']; ?>
				</a>
			</h4>

			<div class="job-list-meta">
				<span><i class="fa fa-phone"></i> <?php echo $t['phone']; ?></span>
				<span><i class="fa fa-map-marker"></i> <?php echo $t['address']; ?></span>
			</div>

			<p class="job-list-summary">
				<?php echo $t['desc']; ?>
			</p>

			<b>טווח מחירים:</b>
			

		</div>

		<div class="job-list-actions">

			

			<div>
				<b>מדרגים:</b> 
			</div>

			<a href=",products,<?=$t['id'];?>" class="btn btn-outline-primary btn-sm">
				לעמוד העסק
			</a>

		</div>

	</div>

	

</div>
					<?php














				}


				?>







				</div>
			



				<?php
				$pages_query = db_query($sql)or die(db_error());
				$get_pages = db_num_rows($pages_query);

				$max = $max ?? 0;

				if ((int) $_GET['page'] > 1){

					echo '<div class="pages_div">';
					echo "<a href='";
					echo "/,categories,";
					echo $get_id;

					if($_GET['order'] != ''){
						echo "?";
						echo "order=";
						echo $_GET['order'];
					}
					if($_GET['From'] != ''){
						echo "&From=";
						echo $_GET['From'];
					}
					if($_GET['To'] != ''){
						echo "&To=";
						echo $_GET['To'];
					}
					
					if($_GET['price'] != ''){
						echo "&price=";
						echo $_GET['price'];
					}
					if($_GET['rating'] != ''){
						echo "&rating=";
						echo $_GET['rating'];
					}
					if($_GET['voters'] != ''){
						echo "&voters=";
						echo $_GET['voters'];
					}

					echo "&page=";
					echo ($_GET['page']-1);
					echo "'><b>עמוד קודם</b></a></div>";

				}




				for ($i = 1; $i <= (ceil($get_pages/20)); $i++) {



					echo '<div class="pages_div ';
					if($_GET['page'] == $i){
						echo "page_div_selected";
					}
					echo '">';
					echo "<a href='";
					echo "http://";

					echo $_SERVER["HTTP_HOST"];
					echo "/,categories,";
					echo $get_id;


					if($_GET['order'] != ''){
						echo "?";
						echo "order=";
						echo $_GET['order'];
					}	

					if($_GET['price'] != ''){
						echo "&price=";
						echo $_GET['price'];
					}					
					if($_GET['rating'] != ''){
						echo "&rating=";
						echo $_GET['rating'];
					}
					if($_GET['voters'] != ''){
						echo "&voters=";
						echo $_GET['voters'];
					}

					if($_GET['From'] != ''){
						echo "&From=";
						echo $_GET['From'];
					}
					if($_GET['To'] != ''){
						echo "&To=";
						echo $_GET['To'];
					}




					echo "&page=$i'>$i</a>";
					$max += $i;
					echo "</div>";
				}


				if($_GET['page'] < ($max + 1) / 20){


					echo '<div class="pages_div">';
					echo "<a href='";
					echo "/,categories,";
					echo $get_id;

					if($_GET['order'] != ''){
						echo "?";
						echo "order=";
						echo $_GET['order'];
					}
					if($_GET['From'] != ''){
						echo "&From=";
						echo $_GET['From'];
					}
					if($_GET['To'] != ''){
						echo "&To=";
						echo $_GET['To'];
					}
					if($_GET['rating'] != ''){
						echo "&rating=";
						echo $_GET['rating'];
					}
					
					if($_GET['price'] != ''){
						echo "&price=";
						echo $_GET['price'];
					}
					if($_GET['voters'] != ''){
						echo "&voters=";
						echo $_GET['voters'];
					}

					echo "&page=";
					if ($_GET['page'] < 1){
						echo '2';
					} else {
						echo ($_GET['page']+1);
					}
					echo "'><b>עמוד הבא</b></a></div>";


				}






?>


				</div>
				

	</div>


<?php
				}
				$TEMP_TEMPLATE = 3;

	
	
	
	
?>
