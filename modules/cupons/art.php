
<?php

if ($get_id) {
	$GET_PAGEINFO_q = db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_articles` AS `p` 
													INNER JOIN `mod_articles_list` AS `l` 
													ON `p`.`art_id`=`l`.`id` 
													WHERE `l`.`id`='".$get_id."' AND `p`.`lang_id`=".LANG."")or die(db_error());
	$GET_PAGEINFO_c = db_num_rows($GET_PAGEINFO_q);

	if ($GET_PAGEINFO_c > 0) {
		$GET_PAGEINFO_r = db_fetch_array($GET_PAGEINFO_q);
		$event=$GET_PAGEINFO_r["e_id"];
		$TEMP_INPAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['lang_title']);
				$TEMP_PAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['lang_title']);

		function PAGE_TEXT() { 
		
			global $event;
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
			global $GET_PAGEINFO_r, $get_id, $lang;
			echo "<div style=\"min-height:619px\">";
			echo $GET_PAGEINFO_r['text'];
			echo "</div>";
?>
		<div id="m_art">
<?php
					$i=0;
	$GET_articles_q = db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_articles` AS `p` 
													INNER JOIN `mod_articles_list` AS `l` 
													ON `p`.`art_id`=`l`.`id` 
													WHERE  `p`.`lang_id`=".LANG." AND `l`.`e_id`=".$GET_PAGEINFO_r["e_id"]." AND `l`.`id`<>".$GET_PAGEINFO_r["art_id"]." ORDER BY RAND() DESC LIMIT 3")or die(db_error());
					while($GET_articles_r=db_fetch_array($GET_articles_q)){
?>
						<a class="article" href=",articles,art_<?=$GET_articles_r["art_id"];?>"><img src="files/articles/<?=$GET_articles_r["pic"];?>" alt="" /><span class="bg"></span><span><?=$GET_articles_r["title"];?></span></a>
<?php								
						$i++;
					}
					echo "</div>";
		}
		$TEMP_TEMPLATE = 2;

	}
	
}
?>