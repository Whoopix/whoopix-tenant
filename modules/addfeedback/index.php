<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;

$get_par1 = db_query("SELECT * FROM mod_biz WHERE id = '".$get_id."'");

$gp1 = db_fetch_array($get_par1);
$get_par = db_query("SELECT * FROM mod_biz_feedback_fields WHERE biz_id = '".$gp1['cat_id']."'");

$TEMP_PAGE_TITLE  = 'הוספת חוות דעת '.$gp1['name'].'';
$TEMP_INPAGE_TITLE = 'הוספת חוות דעת '.$gp1['name'].'';



function PAGE_TEXT() {
	global $get_id, $get_par;



$USER_LOGIN=loggedIn();



	if ($USER_LOGIN['id'] != ''){ ?>







			<script>

							  function AddFeedback(id,feedback_id)
							  {

								var text= $("#text").val();
								var url = $("#url").val();
								var rating = {};
								$(".rating").each(function() {
									var checked = $(this).find('[parameter-id]:checked');
									rating[$(this).attr('parameter-id')] = checked.length? +checked.val() : 0;
								});

								var num = 0;
								var total_rating = 0;
								for (var i in rating) {
									if (rating[i] === 0) {
										alert('חובה למלא את כל שדות הדירוג');
										return;
									}
									total_rating += rating[i];
									num++;
								}
								total_rating /= num;

								rating = JSON.stringify(rating);


								  jQuery.ajax({
								   type: "POST",
								   url: "modules/addfeedback/addsubmit.php",
								   data: 'id='+id+'&text='+text+'&rating='+rating+'&total_rating='+Math.round(total_rating),
								   cache: false,
								   success: function(response)
								   {
									  alert("התווספה הביקורת לאתר");
									  location.href = url;
								   }
								 });
							 }








								/// לבדוק ולידציה אם סימן שדות דירוג






						</script>





			<form id="AddFeedback">





							 <?php
							 $i = 1;

							while($gp = db_fetch_array($get_par)){


						?>

								<style>
								.rating_wrapper {
									height:100px;
									padding-top:30px;
									border-bottom:1px solid lightgray;
								}
								.rating {
									font-size:28px;

								}

								.hide<?php echo $i; ?> {
						  display: none;
						}

						.clear<?php echo $i; ?> {
						  float: none;
						  clear: both;
						}

						.rating<?php echo $i; ?> {


							unicode-bidi: bidi-override;
							direction: rtl;
							text-align: center;
							position: relative;
						}

						.rating<?php echo $i; ?> > label {
							float: right;
							display: inline;
							padding: 0;
							margin: 0;
							position: relative;
							width: 1.1em;
							cursor: pointer;
							color: rgb(133,146,158);
							text-shadow:0 0 1px;

						}

						.rating<?php echo $i; ?> > label:hover,
						.rating<?php echo $i; ?> > label:hover ~ label,
						.rating<?php echo $i; ?> > input.radio-btn:checked ~ label {
							color: transparent;
						}

						.rating<?php echo $i; ?> > label:hover:before,
						.rating<?php echo $i; ?> > label:hover ~ label:before,
						.rating<?php echo $i; ?> > input.radio-btn:checked ~ label:before,
						.rating<?php echo $i; ?> > input.radio-btn:checked ~ label:before {
							content: "\2605";
							position: absolute;
							left: 0;
							color: #8ACEC0;
						}

								</style>
						<!--
						 <input type="text" class="rating" name="rating" >
						 -->
						<div class="rating_wrapper">
						<div class="col-md-2">

						<h3><?php echo $gp['title']; ?></h3>
						</div>
						<div class="col-md-10">
							<div class="rating rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>">
								<input id="star5_<?php echo $i; ?>" name="rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>" type="radio" value="5" class="radio-btn hide" />
								<label for="star5_<?php echo $i; ?>" >☆</label>
								<input id="star4_<?php echo $i; ?>" name="rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>" type="radio" value="4" class="radio-btn hide" />
								<label for="star4_<?php echo $i; ?>" >☆</label>
								<input id="star3_<?php echo $i; ?>" name="rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>" type="radio" value="3" class="radio-btn hide" />
								<label for="star3_<?php echo $i; ?>" >☆</label>
								<input id="star2_<?php echo $i; ?>" name="rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>" type="radio" value="2" class="radio-btn hide" />
								<label for="star2_<?php echo $i; ?>" >☆</label>
								<input id="star1_<?php echo $i; ?>" name="rating<?php echo $i; ?>" parameter-id="<?=$gp['id']; ?>" type="radio" value="1" class="radio-btn hide" />
								<label for="star1_<?php echo $i; ?>" >☆</label>
								<div class="clear"></div>
							</div>
						</div>
						</div>
						<div style="clear:both;"></div>
						<?php
						$i++;
							}
						?>



											<textarea id="text" name="text" style="width:100%;height:250px;"  placeholder="תוכן הביקורת" required="true"></textarea>
											<input type="text" class="col-md-10"
												style="width:100%;height:50px;"
												placeholder="כמה שילמתי" id="" name="title">
												<input type="text" class="col-md-10"
												style="width:100%;height:50px;"
												placeholder="הערות למחיר" id="" name="title">
									<input type="hidden" name="product_id" value="<?=$get_id?>">
<?php


$g = db_query("SELECT * FROM `mod_seo` WHERE `id` = '$get_id' AND `mod` = 'products'");
$p = db_fetch_array($g);
if($p['plink'] != ''){
	$url = $p['plink'];
} else {
	$url = ",products,".$get_id;
}
?>
						<input type="hidden" id="url" value="<?=$url;?>">
				<button onclick="event.preventDefault(); AddFeedback(<?php echo $get_id; ?>); " class="btn btn-primary btn-block login-button btnmenu" style="padding:0px!important;margin:0px!important;" type="submit"><i class="fa fa-sign-in"></i> הוספת ביקורת</button>




			</form>












	<?php

	} else {

		?>
		התחבר כדי להוסיף חוות דעת ועסק חדש
		<?php
	}

}
?>
