<?php

	if ($get_id) {
	$GET_EVENT_q=db_query("SELECT * FROM `mod_sop` WHERE `id`='".$get_id."'")or die (db_error());

	$GEQ = db_fetch_array($GET_EVENT_q);
	$GET_EVENT_q2=db_query("SELECT * FROM `mod_sop_cats` WHERE `id`='".$GEQ['cat_id']."'")or die (db_error());
	$GEQ2 = db_fetch_array($GET_EVENT_q2);
	$TEMP_TEMPLATE = 3;
	$TEMP_PAGE_TITLE = $GEQ["title"];
	$TEMP_INPAGE_TITLE = $GEQ["title"];


	?>
		<?php function subcats() {
					global $get_id,$GEQ2,$GEQ;
				?>
					<?php

				$i = 1;
				$cat_id=$GEQ2['id'];


				?>
				<a class="" href=",categories,<?=$GEQ2['id'];?>"><h3><?=$GEQ2['title']?><h3></a>
				<?php
				$qR=db_query("SELECT * FROM `mod_sop` WHERE
						`cat_id` =".$cat_id." AND `parent` = '0'
					 ORDER BY `order`")or die(db_error());
					 while($rR=db_fetch_array($qR)) {
						?>
						 <div class="col-lg-4 col-md-4 col-sm-12 sm-margin-b-50" style="padding-bottom:20px;">
						<?php 

						echo "<a class=''  style='font-weight:bold;font-size:20px;' href=',categories,subcat_".$rR['id']."'>";
						echo $rR["title"];
						echo "</a><br>";



						






						?>
						</div>

						  <?php 
						if($i % 3 == 0){


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
				<form action="http://<?php echo $_SERVER[HTTP_HOST]; echo $_SERVER[REQUEST_URI]; ?>">



				<input class="col-md-4 inputdesign" style="padding:0px;" placeholder="ממחיר" name="From">


				<input class="col-md-4 inputdesign" style="padding:0px;" placeholder="עד מחיר" name="To">


				<?php if($_GET['order'] != ''){ ?>
				<input type="hidden" name="order" value="<?php echo $_GET['order']; ?>">
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

					<form action="http://<?php echo $_SERVER[HTTP_HOST]; echo $_SERVER[REQUEST_URI]; ?>">
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


				<div class="col-md-12 col-sm-12 col-xs-12 area-filtering" style="padding-top: 15px; margin-bottom: 15px; display: flex; flex-direction: column;">
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
					/* *** */ /* *** */ /* *** */
					// document.addEventListener('DOMContentLoaded', function(event) {
					// 	setTimeout(function() {
					// 		$('.morecats').trigger('click');
					// 		console.log('Remove ".morecats" automatic click...');
					// 	}, 0);
					// });
					/* *** */ /* *** */ /* *** */


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



				echo "<hr>";

				// ---^_^--- This PHP/SQL W0rk 0F Art Created by example.com ---^_^--- //

				$get_sub = db_query("SELECT * FROM `mod_sop` WHERE `cat_id`='".$get_id."'");
				$location_field = str_replace(', ישראל','',$_GET['location']);


				$sql .= "SELECT *, max(mod_feedback_list.`id`) AS Timer, AVG(mod_feedback_list.`rating`) AS RATING, MAX(mod_feedback_list.`price`) AS PriceMax, MIN(mod_feedback_list.`price`) AS PriceMin, COUNT(mod_feedback_list.product_id) AS Counter FROM mod_biz";
				

				if($get_id){
				$sql .=" INNER JOIN mod_feedback_list ON mod_biz.id = mod_feedback_list.product_id";
				}
				$sql .= " WHERE mod_biz.cat_id IN (";
				while($rr=db_fetch_array($get_sub)) {
						$sql .= $rr['id'];
						$sql .= ",";
					}

				$sql .= $get_id;
				$sql .= ")";

				if ($location_field){
					$sql .= " AND mod_biz.address LIKE '%".$location_field."%' ";
				}

				if ($_GET['From']){
						$sql .= " AND mod_biz.price BETWEEN ";
						$sql .= $_GET['From'];
						$sql .= " AND ";
						$sql .= $_GET['To'];
						$sql .= "";
				}

				if (isset($_GET['areas']) && count($_GET['areas'])) {
					$areas = array_map(function($item) { return db_escape($item); }, $_GET['areas']);
					$sql .= " AND `area` IN ('".implode("', '", $areas)."','all')";
				}

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
						$limit .= ",";
						$limit .= 20;
				}

				if ($_GET['page'] == ''){

						$limit = " LIMIT 0,20";




				}
				$fullsql = $sql.$limit;


				//echo $fullsql;
				//echo "<hr>";
				//echo $_GET['page'];
				//echo $_GET['From'];
				//echo $limit;
				//echo "<hr><br>";

				$main_query = db_query($fullsql)or die(db_error());
				?>
				  <div class="col-md-8 nosidemarginonmobile" style="direction:rtl;">




				<div class="sort_title">
					<b>סדר לפי:</b>
				</div>




				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=rating&rating=DESC'>דירוג</a>
				</div>


				<?php  if ($_GET['voters'] == 'DESC') { ?>


				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=voters&voters=ASC'>מספר מדרגים <i class="arrowdownlink"></i></a>
				</div>

				<?php  } else {  ?>

				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=voters&voters=DESC' class="">מספר מדרגים <i class="arrowuplink"></i></a>
				</div>


				<?php  } ?>



				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=time&time=DESC'>תאריך הוספה</a>
				</div>

				<?php  if ($_GET['price'] == 'DESC') { ?>

				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=price&price=ASC'  class="">מחיר <i class="arrowdownlink"></i></a>
				</div>

				<?php  } else {  ?>
				<div class="sort_btn">
					<a href=',categories,subcat_<?=$get_id ?>?order=price&price=DESC'  class="">מחיר <i class="arrowuplink"></i></a>
				</div>
					<?php  } ?>

				<!--
				<div class="sort_btn">
					<a href=',categories,<?=$get_id ?>?order=rating&rating=ASC'>דירוג יורד</a>
				</div>
				-->


				<div style="clear:both;"></div>
				<br><hr><br>

				<?php

				while ($t = db_fetch_array($main_query)){

					?>



			<div class="product" data-sort-op="3" data-sort-in="1" style="height:350px;">

									<div class="col-md-4 col-sm-4">

									<?php
									if (file_exists('files/biz_logo/'.$t['logofile'])) {

									?>
									<img src="files/biz_logo/<?php echo $t['logofile']; ?>" style="width:100%;border:3px solid white;outline:1px solid lightgray;">
									<?php

									 } else {?>
									<img src="img/logo.png" style="width:100%;border:3px solid white;outline:1px solid lightgray;">
									 <?php } ?>
									</div>
									<div class="col-md-8 col-sm-8">

											<div class="col-md-8 col-sm-8">
											<a href="
											<?php
											$get_biz_link = db_query("SELECT * FROM `mod_seo` WHERE `id` = '".$t['product_id']."' AND `mod` = 'products'")or die (db_error());
											$gbl = db_fetch_array($get_biz_link);
											if ($gbl != ''){
											?>
											<?= $gbl['plink']; ?>

											<?php
											} else {
											?>
											,products,<?php echo $t['product_id']; ?>
											<?php } ?>
											">

											<h3>
											<?php echo $t['name']; ?>
											</h3>
											</a>
											<img src="img/iconphone.png"> <b>טלפון:</b> <?php echo $t['phone']; ?>
											<br>
											<img src="img/iconmap.png"> <b>כתובת:</b> <?php echo $t['address']; ?>
											<br>
											<b>טווח מחירים:</b>
											
											<?php 
											
											
											// GET MIN - MAX PRICE 
				
											$mm = db_query("SELECT *, max(price) as max, min(price) as min FROM `mod_feedback_list` WHERE `product_id` = '".$t['product_id']."'");
											$mmm = db_fetch_array($mm);
											
											
											echo $mmm['max']; 
											echo " - ";
											echo $mmm['min']; 
											
											?>
											<br>

									

											<?php echo $t['desc']; ?>
											<br>
											<br>


											</div>
											<div class="col-md-4 col-sm-4">




				<?php
						//echo $t['product_id'];

						// הוא בעצם עושה ממוצע רק מהביקורת האחרונה לא מכל הביקורות של העסק
						$sumArray = array();
						$i = 0;
						$get_review_score = db_query("SELECT * FROM mod_feedback_list WHERE product_id = '".$t['product_id']."'"); // מושך את כל הביקורות
						while ($grs = db_fetch_array($get_review_score)){

							$go = $grs['rating_value'];
							$arr = json_decode($go, true);
							$someArray = $arr;

							  foreach ($someArray as $key => $value) { // מכניס את כל הציונים לתוך ARRAY

								$sumArray[$key] += $value;

							  }

							$i++;
						}






						$totaloss = 0;

						$p= 0;
						 foreach ($sumArray as $key => $value) {
							  $p++;
							 $finalll =  ($value / $i)*2;


							$totaloss += $finalll;

							// echo $finalll;
							// echo "<br>";
							// echo "<hr>";

							// echo $finalll;

						  }
						//echo $totaloss;
						?>

					 <div class="c100 p<?php if ($totaloss > 0) { echo ceil(($totaloss / ($p)) * 10);}?> small">
											<span>
							<?php if ($totaloss > 0) { echo ceil(($totaloss / ($p)) * 10);}?>


											</span>
											<div class="slice">
												<div class="bar"></div>
												<div class="fill"></div>
											</div>
										</div>
										<div style="clear:both;"></div>
										<div class="col-md-12" style="text-align:left;">
										<b>מדרגים: </b>
										<?=$i?>
										</div>
											</div>


									<?php
										$Get_Rating = db_query("SELECT * FROM mod_feedback_list WHERE product_id = '".$t['product_id']."' ORDER BY id DESC LIMIT 1");
										while ($GR = db_fetch_array($Get_Rating)) {

									?>

										<div class="feedback"  style="float:right;width:100%;">
												<div class="col-md-2 col-sm-2">


												<?php
												$user_profile = db_query("SELECT * FROM mod_members WHERE id = '".$GR['member_id']."'");
												$UP = db_fetch_array($user_profile);

												?>
													<div class="profile_img">


													<img class="" src="files/user_pic/<?= $UP['pic']; ?>">


													<?php
													$get_rank = db_query("SELECT * FROM mod_feedback_list WHERE member_id = '".$UP['id']."'");
													$grank = db_num_rows($get_rank);

													$final_rank = $grank * 10;
													?>
													<div class="rank">
													<?php 
													if($final_rank < 20){
													?>
													<img src="img/rank1.png">
													<?php 
													} else if($final_rank >= 20){
														?>

														<img src="img/rank2.png">

														<?php 
													}

													?>
													</div>


													<a href=",profile,<?= $UP['id']; ?>"><?= $UP['username']; ?></a>


													</div>
												</div>
												<div class="col-md-10 col-sm-10">
													<div class="arrow_box">
													<br>
														<b>ציון כולל:</b><?php echo $GR['rating']; ?>
														<br>

														<?php echo $GR['text']; ?>
														<br><br>
														<?php


					$feedbacksum++;

					$go = $GR['rating_value'];
					$arr = json_decode($go, true);
					$someArray = $arr;
					$sum = 0;
					$avg = 0;
						if ($someArray != ''){
							  foreach ($someArray as $key => $value) {


								  $get_fields = db_query("SELECT * FROM mod_biz_feedback_fields WHERE id = '".$key."'")or die (db_error());
								  $gf = db_fetch_array($get_fields);

									//echo "<b>".$gf['title'].":</b> ";
									//echo $value;
									//echo "<br>";
									$sum += $value;
									$avg++;



							  }


								//echo "כמות מאפיינים ";
								//echo $avg;
								//echo "<br>";
								//echo "סכום דירוגים ";
							    // echo $sum;
								// echo "<br>";
								// echo "ממוצע ";

								 $get_avg =  ($sum / $avg) * 10;
								 echo substr($get_avg, 0, 2);
								 $total_avg += $get_avg;


						}


														?>

													  </div>


												</div>
										</div>
										<div style="clear:both;"></div>


									<?php 	} ?>

									</div>
			</div>

					<?php














				}


				?>







				 <div style="clear:both;"></div>
				<br><hr>


					<?php
				$pages_query = db_query($sql)or die(db_error());
				$get_pages = db_num_rows($pages_query);


					if($_GET['page'] > 1){

									 echo '<div class="pages_div">';
					echo "<a href='";
									echo "/,categories,subcat_";
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

									echo $_SERVER[HTTP_HOST];
									echo "/,categories,subcat_";
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






									echo "&page=$i'>$i</a>";
									$max += $i;
						echo "</div>";
					}


			if($_GET['page'] < $max / 3){


	 echo '<div class="pages_div">';
					echo "<a href='";
									echo "/,categories,subcat_";
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
					if ($_GET['page'] < 1){
					echo '2';
					} else {
					echo ($_GET['page']+1);
					}
					echo "'><b>עמוד הבא</b></a></div>";


			}






?>



				</div>
				 <div class="col-md-4 col-sm-12 col-xs-12">



				<div class="addbanner">
				<a href=",products,add?cat=<?=$get_id?>"><% LANG_addbanner %></a>
				</div>


				 </div>

	</div>







	<?php
		}
	}




?>
