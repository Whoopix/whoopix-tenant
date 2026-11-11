<?php



	foreach (['search','location','From','To','order','page','rating','voters','price'] as $__k) {
		if (!isset($_GET[$__k])) { $_GET[$__k] = ''; }
	}
	if (!isset($_GET['areas']) || !is_array($_GET['areas'])) { $_GET['areas'] = []; }

	$event=$get_id;
	$cat_id=$get_id;

	
	if (!isset($TEMP_PAGE_TITLE))   { $TEMP_PAGE_TITLE   = ''; }
	if (!isset($TEMP_INPAGE_TITLE)) { $TEMP_INPAGE_TITLE = ''; }

		$GET_CAT_q=db_query("SELECT * FROM `mod_sop_cats` WHERE `id`=".intval($cat_id));
		if($GET_CAT_r=db_fetch_array($GET_CAT_q)){

		
			$TEMP_PAGE_TITLE   = (string) ($GET_CAT_r["title"] ?? '');
			$TEMP_INPAGE_TITLE = (string) ($GET_CAT_r["title"] ?? '');
			$TEMP_TEMPLATE     = 3;

			$q=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".intval($cat_id)."
					 ORDER BY `order`")or die(db_error());


			if(db_num_rows($q) ){

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

					<form action="http://<?= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>">
					
					
								
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
										   value="<?= $name ?>"
										   <?= isset($_GET['areas']) && in_array($name, $_GET['areas'])? 'checked' : '' ?>
										   type="checkbox" />
									<?= $displayName ?>
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






				
<style>

.job-list-item{
	margin-bottom:40px;
	padding:24px;
	border-radius:18px;
	background:#fff;
	box-shadow:0 4px 18px rgba(0,0,0,0.06);
}

.modern-feedback{
	margin-top:24px;
	padding-top:20px;
	border-top:1px solid #ececec;
}

.feedback-user{
	display:flex;
	align-items:center;
	gap:14px;
	margin-bottom:14px;
}

.feedback-avatar{
	width:56px;
	height:56px;
	border-radius:50%;
	object-fit:cover;
	border:3px solid #f3f3f3;
}

.feedback-user-info{
	display:flex;
	flex-direction:column;
}

.feedback-username{
	font-weight:700;
	font-size:16px;
	color:#222;
	text-decoration:none;
}

.feedback-rating{
	font-size:14px;
	color:#f39c12;
	margin-top:3px;
}

.feedback-content{
	background:#f8f9fb;
	padding:16px 18px;
	border-radius:14px;
	line-height:1.8;
	font-size:15px;
	color:#444;
}

</style>

<?php
				function PAGE_TEXT() {
					global $cat_id,$event,$q,$GET_CAT_r,$GET_EVENT_r,$get_id;



	

				// ---^_^--- This PHP/SQLW0rk0FArt Created by example.com ---^_^--- //

				$get_sub = db_query("SELECT * FROM `mod_sop` WHERE `cat_id`='".$get_id."'");
				$location_field = str_replace(', ישראל', '', (string) $_GET['location']);
				$sql = '';

				$sql .= "SELECT *, max(mod_feedback_list.`id`) AS Timer,
				AVG(mod_feedback_list.`rating`) AS RATING, 
				MAX(mod_feedback_list.`price`) AS PriceMax, 
				MIN(mod_feedback_list.`price`) AS PriceMin, 
				COUNT(mod_feedback_list.product_id) AS Counter FROM mod_biz";

				if ($get_id){
					$sql .= " INNER JOIN mod_feedback_list ON mod_biz.id = mod_feedback_list.product_id";
				}

				$sql .= " WHERE mod_biz.cat_id IN (";
				while($rr=db_fetch_array($get_sub)) {
					$sql .= $rr['id'];
					$sql .= ",";
				}
				$sql .= $get_id;
				$sql .= ")";

				if (isset($_GET['cat']) && is_array($_GET['cat']) && count($_GET['cat'])) {
					$selected_cats = array_map('intval', $_GET['cat']);
					$sql .= " AND mod_biz.cat_id IN (".implode(',', $selected_cats).") ";
				}

				
				if (!empty($_GET['search'])){
					$search = db_escape($_GET['search']);
					$sql .= " AND (
						mod_biz.name LIKE '%".$search."%' 
						OR mod_biz.desc LIKE '%".$search."%'
						OR mod_biz.address LIKE '%".$search."%'
					) ";
				}

if ($location_field){
					$sql .= " AND mod_biz.address LIKE '%".$location_field."%' ";
				}

				if ($_GET['From']){
					$sql .= " AND mod_biz.price BETWEEN ".$_GET['From']." AND ".$_GET['To'];
					// $sql .= " AND mod_biz.price BETWEEN ";
					// $sql .= $_GET['From'];
					// $sql .= " AND ";
					// $sql .= $_GET['To'];
					// $sql .= "";
				}

				if (isset($_GET['areas']) && count($_GET['areas'])) {
					$areas = array_map(function($item) { return db_escape($item); }, $_GET['areas']);
					$sql .= " AND `area` IN ('".implode("', '", $areas)."','all')";
				}
				// echo $sql;

				if($get_id){
					$sql .= " GROUP BY mod_biz.id,mod_feedback_list.product_id ";
				}

				$sql .= " ORDER BY ";
				if ($_GET['From'] != ''){
					$sql .= "mod_biz.price ".$_GET[$_GET['order']]."DESC";
				} else if ($_GET['order'] == 'rating'){
					$sql .= "RATING ".$_GET[$_GET['order']];
				} else if ($_GET['order'] == 'time'){
					$sql .= "mod_feedback_list.time DESC";
				} else if ($_GET['order'] == 'voters'){
					$sql .= "Counter ".$_GET[$_GET['order']];
				} 
					else if ($_GET['order'] == 'price'){
						$sql .= " ";
						if ($_GET[$_GET['order']] == 'DESC'){
							$sql .= "PriceMax";
						} else {
							$sql .= "PriceMin";
						}
						
						$sql .= " ";
						$sql .= $_GET[$_GET['order']];
				
				} else {
					$sql .= "RATING DESC"; // כנראה לוקח רייטינג של ביקורת אחרונה במקום ציון כללי - לתקן
				}

				if ($_GET['page'] != ''){
					$limit = " LIMIT ";
					if ($_GET['page'] == 1){
						$limit .= "0";
					} else {
						$limit .= 20*($_GET['page']-1);
					}
					$limit .= ", 20";
				} else {
					$limit = " LIMIT 0, 20";
				}

				$fullsql = $sql.$limit;

				//echo $fullsql;


				//echo $fullsql;
				//echo "<hr>";
				//echo $_GET['page'];
				//echo $_GET['From'];
				//echo $limit;
				//echo "<hr><br>";
				
				
				
				
				

				$main_query = db_query($fullsql)or die(db_error());
				?>
				
				
    <section class="jobs-board">
      <div class="container">
        <div class="row g-4">
			<aside class="col-lg-3">
	<form method="GET" class="category-sidebar-filter">

		<div class="filter-card">
			<h5>חיפוש</h5>
			<input 
				type="text" 
				name="location" 
				class="form-control" 
				placeholder="חיפוש בתוך הקטגוריה"
				value="<?= htmlspecialchars($_GET['location'] ?? '') ?>"
			>
		</div>

		<div class="filter-card">
			<h5>אזור</h5>
			<ul class="filter-list">
				<?php
				$sidebar_areas = [
					'gush_dan' => 'גוש דן',
					'hasharon' => 'השרון',
					'tzafon' => 'צפון',
					'darom' => 'דרום'
				];
				foreach ($sidebar_areas as $area_key => $area_name) {
				?>
				<li>
					<label>
						<input 
							type="checkbox" 
							name="areas[]" 
							value="<?= $area_key; ?>"
							<?= (isset($_GET['areas']) && in_array($area_key, $_GET['areas'])) ? 'checked' : ''; ?>
						>
						<?= $area_name; ?>
					</label>
				</li>
				<?php } ?>
			</ul>
		</div>

		<div class="filter-card">
			<h5>תתי קטגוריות</h5>
			<ul class="filter-list">
				<?php
				$sidebar_subcats = db_query("SELECT * FROM mod_sop WHERE cat_id = '".intval($get_id)."' ORDER BY `order` ASC");
				while($sidebar_cat = db_fetch_array($sidebar_subcats)) {
				?>
				<li>
					<label>
						<input 
							type="checkbox" 
							name="cat[]" 
							value="<?= $sidebar_cat['id']; ?>"
							<?= (isset($_GET['cat']) && in_array($sidebar_cat['id'], $_GET['cat'])) ? 'checked' : ''; ?>
						>
						<?= $sidebar_cat['title']; ?>
					</label>
				</li>
				<?php } ?>
			</ul>
		</div>

		<div class="filter-card">
			<h5>טווח מחירים</h5>

			<input 
				type="number" 
				name="From" 
				class="form-control mb-2" 
				placeholder="ממחיר"
				value="<?= htmlspecialchars($_GET['From'] ?? '') ?>"
			>

			<input 
				type="number" 
				name="To" 
				class="form-control" 
				placeholder="עד מחיר"
				value="<?= htmlspecialchars($_GET['To'] ?? '') ?>"
			>
		</div>

		<input type="hidden" name="page" value="1">

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
			$biz_image = 'resources/design/images/coment-avatar-3.jpg';

			if (!empty($t['logofile']) && file_exists('files/upload/'.$t['logofile'])) {
				$biz_image = 'files/upload/'.$t['logofile'];
			}
			?>
			<img src="<?php echo $biz_image; ?>">
		</div>

		<div class="job-list-content">

			<h4>
				<a href="
				<?php
				$get_biz_link = db_query("SELECT * FROM `mod_seo` WHERE `id` = '".$t['product_id']."' AND `mod` = 'products'")or die (db_error());
				$gbl = db_fetch_array($get_biz_link);
				if ($gbl != ''){
				?>
				<?= $gbl['plink']; ?>
				<?php } else { ?>
				,products,<?php echo $t['product_id']; ?>
				<?php } ?>
				">
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
			<?php 
			$mm = db_query("SELECT *, max(price) as max, min(price) as min FROM `mod_feedback_list` WHERE `product_id` = '".$t['product_id']."'");
			$mmm = db_fetch_array($mm);
			echo $mmm['max']; 
			echo " - ";
			echo $mmm['min']; 
			?>

			<br><br>
			<?=$t['RATING'];?>

		</div>

		<div class="job-list-actions">

			

			<div>
				<b>מדרגים:</b> 
			</div>

			<a href=",products,<?=$t['product_id'];?>" class="btn btn-outline-primary btn-sm">
				לעמוד העסק
			</a>

		</div>

	</div>

	<!-- FEEDBACK נשאר כמו שהוא -->
	<?php
	$feedbacksum = $feedbacksum ?? 0;
	$total_avg   = $total_avg   ?? 0;
	$Get_Rating = db_query("SELECT * FROM mod_feedback_list WHERE product_id = '".$t['product_id']."' ORDER BY id DESC LIMIT 1");
	while ($GR = db_fetch_array($Get_Rating)) {
	?>

	<div class="feedback modern-feedback">

		<div class="feedback-user">

			<?php
			$user_profile = db_query("SELECT * FROM mod_members WHERE id = '".$GR['member_id']."'");
			$UP = db_fetch_array($user_profile);

			$user_image = 'resources/design/images/coment-avatar-3.jpg';

			if (!empty($UP['pic']) && file_exists('files/user_pic/'.$UP['pic'])) {
				$user_image = 'files/user_pic/'.$UP['pic'];
			}
			?>

			<img class="feedback-avatar" src="<?= $user_image; ?>">

			<div class="feedback-user-info">
				<a href=",profile,<?= $UP['id']; ?>" class="feedback-username">
					<?= $UP['username']; ?>
				</a>

				<div class="feedback-rating">
					⭐ <?php echo number_format((float)$GR['rating'],1); ?>/5
				</div>
			</div>

		</div>

		<div class="feedback-content">
			<?php echo nl2br($GR['text']); ?>
		</div>

	</div>

	<?php } ?>

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
					
					if($_GET['search'] != ''){
						echo "&search=";
						echo urlencode($_GET['search']);
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
					
					if($_GET['search'] != ''){
						echo "&search=";
						echo urlencode($_GET['search']);
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
					
					if($_GET['search'] != ''){
						echo "&search=";
						echo urlencode($_GET['search']);
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
	}}
?>
