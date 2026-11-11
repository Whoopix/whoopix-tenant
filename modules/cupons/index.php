<?php
$event=$get_id;
$GET_EVENT_q=db_query("SELECT * FROM `mod_eventstypes` WHERE `id`=".intval($get_id)."");
if ($get_id && $GET_EVENT_r=db_fetch_array($GET_EVENT_q)) {

	$TEMP_PAGE_TITLE = $GET_EVENT_r["title"]."-קופונים";
	$TEMP_INPAGE_TITLE =  $GET_EVENT_r["title"]."-קופונים";

	function PAGE_TEXT() { 
		global $lang,$get_id,$event;
		?>
		<script type="text/javascript">
	 $(document).ready(function(){
		$('#main_menu a[rel="<?=$event?>"]').parent().addClass('hover');//.click();
		$('#main_menu a[rel="<?=$event?>"]').parent().next().css('border','0px');
		$('#main_menu a[rel="<?=$event?>"]').parent().next().css('padding-right','3px');
		$('#menu ul.in_menu[id="ev_<?=$event?>"]').show();
		$('a.big_cat div').click(function(){
				window.location.href=$(this).parent().attr('href');
		});
		
	 });
	</script>
		<?php
					$GET_UPDATES=db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_articles` AS `p` 
													INNER JOIN `mod_articles_list` AS `l` 
													ON `p`.`art_id`=`l`.`id` 
													WHERE `l`.`e_id`=".intval($get_id)." AND `p`.`lang_id`=".LANG." ORDER BY `l`.`id`")or die(db_error());
					while($GET_UPDATESR = db_fetch_array($GET_UPDATES)) {
						
						
						
						
						
						

						?>
						
						
						
						<div class="col-sm-3 sm-margin-b-50" style="height:500px;margin-bottom:50px;">
					<div class="lproductsb">
                        <div class="margin-b-20">
                            <img style="height:200px;width:100%;" class="img-responsive wow fadeIn" src="files/articles/<?=$GET_UPDATESR["pic"];?>" alt="<?php echo $GET_UPDATESR['lang_title'];?>" data-wow-duration=".3" data-wow-delay=".1s">
                        </div>
						<div class="paddinger">
							<h4><a href=",articles,art_<?php echo $GET_UPDATESR[id];?>"><?php echo $GET_UPDATESR['lang_title'];?></a></h4>
							<p>
							<?php echo substr($GET_UPDATESR['desc'],0,300); ?>
							</p>
							<a class="link" href=",articles,art_<?php echo $GET_UPDATESR[id];?>">>לפרטים נוספים</a>
						</div>
						</div>
                    </div>
					
					
					
						<?php
					}
	}
	$TEMP_TEMPLATE = 2;

} else {
		$TEMP_PAGE_TITLE = "קופונים";
		$TEMP_INPAGE_TITLE = "קופונים";	
		function PAGE_TEXT() {
			global $event,$GET_EVENT_r;
			
?>





<?php
					$i=0;
					$GET_events_q=db_query("SELECT * FROM `mod_eventstypes` ORDER BY `id`")or die (db_error());
					while($GET_events_r=db_fetch_array($GET_events_q)){
?>
				
						<div class="col-sm-3 sm-margin-b-50 ">
					<div class="lproductsb">
                        <div class="margin-b-20">
                            <img style="height:200px;width:100%;" class="img-responsive wow fadeIn" src="files/events/<?=$GET_events_r["pic"];?>" alt="<?php echo $GET_UPDATESR['lang_title'];?>" data-wow-duration=".3" data-wow-delay=".1s">
                        </div>
						<div class="paddinger">
							<h4><a href=",articles,<?=$GET_events_r["id"];?>"><?=$GET_events_r["title"];?></a></h4>
							
							<a class="link" href=",articles,<?=$GET_events_r["id"];?>">>לפרטים נוספים</a>
						</div>
						</div>
                    </div>
					
					
					



					
<?php								$i++;	
					}


?>










<?php
		}
		$TEMP_TEMPLATE = 2;
} 
?>