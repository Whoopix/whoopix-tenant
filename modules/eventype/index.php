<?php

	$event=$get_id;
	$GET_EVENT_q=db_query("SELECT * FROM `mod_eventstypes` WHERE `id`=".intval($get_id));
	if($GET_EVENT_r=db_fetch_array($GET_EVENT_q)){
		$TEMP_PAGE_TITLE = $GET_EVENT_r["title"];
		$TEMP_INPAGE_TITLE = $GET_EVENT_r["title"];	
		function PAGE_TEXT() {
			global $event,$GET_EVENT_r;
			
?>
	<div id="cat_title">
		<div class="event" style="background-image:url(files/events/<?=$GET_EVENT_r["pic1"];?>);"><?=$GET_EVENT_r["title"];?></div>
	</div>
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
	<div style="margin-top:7px;width:753px;">
<?php
/*

		$p = db_query("SELECT * FROM mod_sop_cats WHERE cat_id = '".$get_id."'");
		while($pp = db_fetch_array($p)){
			
			echo $pp['title'];
			$k = db_query("SELECT * FROM mod_sop WHERE cat_id = '".$get_id."'");
			
			
		}
*/



			$q=db_query("SELECT * FROM `mod_sop_cats` WHERE `id` IN (
					SELECT `s`.`cat_id` FROM `mod_sop` AS `s`,`mod_event_sop` AS `e` WHERE `e`.`sop_id`=`s`.`id` AND `e`.`event_id`='".$event."'
				) ORDER BY `order`"); 
			while($r=db_fetch_array($q)) {
				echo "<a href=\",eventype,cat_".$event."&c=".$r["id"]."\" class=\"big_cat\"><div style=\"background-image:url(files/events_cats/".$r["pic1"].");\"><table cellpadding=\"0\" cellspacing=\"0\"><tr><td style='padding-left:15px;'>".$r["title"]."</td></tr></table></div></a>";
				
				
				
				
				
				
				$q=db_query("SELECT * FROM `mod_sop` WHERE 
						`cat_id` ='".$cat_id."' 
					AND `id` IN(SELECT `sop_id` FROM `mod_event_sop` WHERE `event_id`=".$event.") ORDER BY `order`")or die(db_error());	

while($r=db_fetch_array($q)) {
						echo "<a href=\",eventype,biz_".$event."&c=".$r["id"]."\" class=\"small_cat\"><div style=\"background-image:url(files/events_sops/".$r["pic1"].");\"><table cellpadding=\"0\" cellspacing=\"0\"><tr><td>".$r["title"]."</td></tr></table></div></a>";
}
			
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
			}
?>


		</div>
<?php
		}
		$TEMP_TEMPLATE = 3;
	}else
		include "404.php";
?>