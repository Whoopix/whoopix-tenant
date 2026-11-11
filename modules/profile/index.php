<?php
$PUT_LEFT=false;
$TEMP_TEMPLATE = 2;
$get_id=$get_id?$get_id:0;

$biz = db_query("SELECT * FROM mod_members WHERE id = '".$get_id."'");
$bz = db_fetch_array($biz);

$TEMP_PAGE_TITLE  = $bz['username'];
$TEMP_INPAGE_TITLE = $bz['username'];
$USER_LOGIN=loggedIn();

if($get_id!=0){	
function PAGE_TEXT() {
	global $get_id, $bz, $USER_LOGIN;
	
	
	
	
	?>
	<style>
	@media screen and (max-width:800px){
	.msgmargin{
		margin-top:100px;
		
	}
	}
	</style>
	
	
<div class="col-md-8" style="padding-top:20px;padding-bottom:20px;background-color:#fafafa;min-height:350px;">
	
	
	<h3><?php echo $bz['name']; ?> 
	
	<? if($USER_LOGIN['id'] != $get_id){ ?>
	
	<b><a href=",members,messages?msgid=0&userid=<?=$get_id;?>">
	
	
	<img src="img/sendmsg.png"> שלח הודעה</a>
	<? } ?>
	
	</b></h3> 
	

	<?php echo $bz['about']; ?>

	
	<?php
	$get_likes = db_query("SELECT * FROM mod_likes WHERE member_id = '".$get_id."'");
	$gl = db_num_rows($get_likes);
	
	//echo $gl;
	
	$get_feedbacks = db_query("SELECT * FROM mod_feedback_list AS t1 INNER JOIN mod_biz AS t2 ON t1.product_id = t2.id WHERE t1.member_id = '".$get_id."' GROUP BY t2.id");
	$gf = db_num_rows($get_feedbacks);
	
	//echo $gf;
	//echo "<hr>";
	//echo "חישוב דרגה";
	//$gb = $gl * 3;
	
	//echo $gb;

	echo "<hr>";
	
	
	
	
$final_rank = $gl * 10;
													?>
													
													<?
													if($final_rank < 20){
													?>
													<img src="img/rank1.png">
													<?
													} else if($final_rank >= 20){
														?>

														<img src="img/rank2.png">

														<?
													}

													?>

	
</div>
<div class="col-md-4" style="padding-top:20px;padding-bottom:20px;border:1px solid #fafafa;min-height:350px;background-image:url('files/user_pic/<?php echo $bz['pic']; ?>');background-size:cover;background-position:center center;">

</div>
	
	
	
	
	<div style="clear:both;margin-bottom:40px;"></div>

	
	
	
	
	<?php
	while ($feedbacks = db_fetch_array($get_feedbacks)){
		
		
		$GET_SEO_URL = db_query("SELECT * FROM `mod_seo` WHERE `id` = '".$feedbacks['id']."' AND `mod` = 'products'");
		$GSURL = db_fetch_array($GET_SEO_URL);
	
	?>
	

	
	<?  
	if($GSURL['plink'] != ''){
	
	?>
	<a href="<?=$GSURL['plink'];?>">
	
	<? } else { ?>
	<a href=",products,<?=$GSURL['id'];?>">
	
	<? } ?>
	<h3><?=$feedbacks['name'];?></h3>
	</a>
	
	<div class="feedback" style="float:right;width:100%;">
	<?php
						
						
						$goIN = $feedbacks['rating_value'];
						$arrIN = json_decode($goIN, true);
					$someArrayIN = $arrIN;
						
						$totalFrating = 0;
						$g = 1;
						 foreach ($someArrayIN as $key => $value) {
							  
							
							
							//  echo "<b>".$gf['title']."</b>";
							$g++;
							$totalFrating += $value;
							//echo "<br>";
							
							
							
							
						  }
						
						?>
	
			<div class="col-md-2">
						
						
								
						<div class="c100 p<?=ceil(($totalFrating / ($g-1)) * 10)*2;?> medium rating-hover" style="margin-right:0px;">
											<span>
											
											
										<?=ceil(($totalFrating / ($g-1)) * 10)*2;?>								
											
											</span>
											<div class="slice">
												<div class="bar"></div>
												<div class="fill"></div>
											</div>
											
											
											
											
											
											
												<div class="rating-values" style="width:140px;">
											<?php	
						
						$go = $feedbacks['rating_value'];
						$arr = json_decode($go, true);

						//echo $fb['rating_value'];
						//echo "<br><br>";
						
						
					$someArray = $arr;

					if ($someArray != ''){
						  foreach ($someArray as $key => $value) {
							  
							  
							  $get_fields = db_query("SELECT * FROM mod_biz_feedback_fields WHERE id = '".$key."'");
							  $gf = db_fetch_array($get_fields);
							  
							  echo "<b>".$gf['title']."</b><br>";
							echo $value;
							
							for ($i = 1; $i <= $value; $i++) {

								 ?>
								 <div class="star-five">☆</div>
								 <?
							 }
							
							
							?>
							
							
							<?
							echo "<br>";
							
							
							
							
						  }
					}

							
						?>
						
						
						
										
										</div>
										</div>
										
										
						</div>
	
	<div class="col-md-10">
	
	
	
	
	
												<div class="col-md-2">
												  <div class="profile_img">
													<img class="" src="files/user_pic/<?=$bz['pic'];?>">
													</div>
												</div>
												<div class="col-md-10 msgmargin">
													<div class="arrow_box">
													
													
														<br>
														<?php echo $feedbacks['text']; ?>
															<br><br>
														
												<?php
													//echo $feedbacks['product_id'];
													
													
													
													
													
													//echo 
													
													//echo "<hr>";
													?>														
													  </div>
													
													
												</div>
										</div>
	
	
	
	
	
	
	
	
	
	</div>
	
	
	
	

<div style="height:50px;padding-top:5px;clear:both;width:100%;"><hr></div>

	<?php } ?>
	
	
	
	<?php
}
}
?>