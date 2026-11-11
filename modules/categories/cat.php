<?php
	$event=$get_id;
	$cat_id=intval($_GET["c"]);
	$GET_EVENT_q=db_query("SELECT * FROM `mod_eventstypes` WHERE `id`=".intval($event));
	if($GET_EVENT_r=db_fetch_array($GET_EVENT_q)){	
		$GET_CAT_q=db_query("SELECT * FROM `mod_sop_cats` WHERE `id`=".intval($cat_id));
		if($GET_CAT_r=db_fetch_array($GET_CAT_q)){
			$q=db_query("SELECT * FROM `mod_sop` WHERE 
						`cat_id` =".$cat_id." 
					AND `id` IN(SELECT `sop_id` FROM `mod_event_sop` WHERE `event_id`=".$event.") ORDER BY `order`")or die(db_error());	
			if(db_num_rows($q) ){
				$TEMP_PAGE_TITLE = $GET_EVENT_r["title"]."-".$GET_CAT_r["title"];
				$TEMP_INPAGE_TITLE = $GET_EVENT_r["title"]."-".$GET_CAT_r["title"];
				function PAGE_TEXT() {
					global $cat_id,$event,$q,$GET_CAT_r,$GET_EVENT_r;
?>
					<div id="cat_title">
						<div class="event" style="background-image:url(files/events_cats/<?=$GET_CAT_r["pic"];?>);"><?=$GET_EVENT_r["title"]."-".$GET_CAT_r["title"];?></div>
					</div>
				<div class="link link_0" style="margin-top:7px;margin-bottom:22px;">
					<?php putAD(1,723,142);?>
					<img class="f top right" src="/images/blue_top_right.png" alt="" />
					<img class="f top left" src="/images/blue_top_left.png" alt="" />
					<img class="f bottom right" src="/images/blue_bottom_right.png" alt="" />
					<img class="f bottom left " src="/images/blue_bottom_left.png" alt="" />
				</div>
					<script type="text/javascript">
					 $(document).ready(function(){
						//$('#main_menu a[rel="<?=$event?>"]').click();
						$('#main_menu a[rel="<?=$event?>"]').parent().addClass('hover');
						$('#main_menu a[rel="<?=$event?>"]').parent().next().css('border','0px');
						$('#main_menu a[rel="<?=$event?>"]').parent().next().css('padding-right','3px');
						$('#menu ul.in_menu[id="ev_<?=$event?>"]').show();
						$('#menu ul.in_menu[id="ev_<?=$event?>"] a[rel="<?=$GET_CAT_r["id"];?>"]').parent().addClass('hover');
						$('a.small_cat div').click(function(){
								window.location.href=$(this).parent().attr('href');
						});
					 });
					</script>
	<div style="width:753px;">
<?php
					while($r=db_fetch_array($q))
						echo "<a href=\",eventype,biz_".$event."&c=".$r["id"]."\" class=\"small_cat\"><div style=\"background-image:url(files/events_sops/".$r["pic1"].");\"><table cellpadding=\"0\" cellspacing=\"0\"><tr><td>".$r["title"]."</td></tr></table></div></a>";
						//echo "<a class=\"small_cat\" href=\",eventype,biz_".$event."&c=".$r["id"]."\">".$r["title"]."</a>";
?>
	</div>
<?php
				}
				$TEMP_TEMPLATE = 3;
			}else
				include "404.php";
		}else
			include "404.php";
	}else
		include "404.php";
?>