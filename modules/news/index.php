<?php

if ($get_id) {
	$GET_PAGEINFO_q = db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_news` AS `p` 
													INNER JOIN `mod_news_list` AS `l` 
													ON `p`.`news_id`=`l`.`id` 
													WHERE `l`.`id`='".$get_id."' AND `p`.`lang_id`=".LANG."")or die(db_error());
	$GET_PAGEINFO_c = db_num_rows($GET_PAGEINFO_q);

	if ($GET_PAGEINFO_c > 0) {
		$GET_PAGEINFO_r = db_fetch_array($GET_PAGEINFO_q);
		
		$TEMP_INPAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['lang_title']);
		function PAGE_TEXT() { 
			global $GET_PAGEINFO_r, $get_id, $lang;
			echo $GET_PAGEINFO_r['text'];
		}
		$TEMP_TEMPLATE = 2;

	}
	
} else {
	$TEMP_PAGE_TITLE = "חדשות ועדכונים";
	$TEMP_INPAGE_TITLE =  "חדשות ועדכונים";

	function PAGE_TEXT() { 
		global $lang;
					$GET_UPDATES=db_query("SELECT *,`p`.`title` AS `lang_title` FROM `mod_news` AS `p` 
													INNER JOIN `mod_news_list` AS `l` 
													ON `p`.`news_id`=`l`.`id` 
													WHERE `p`.`lang_id`=".LANG."")or die(db_error());
					while($GET_UPDATESR = db_fetch_array($GET_UPDATES)) {
						?>
						<div><a href=",news,<?php echo $GET_UPDATESR[id];?>"><?php echo $GET_UPDATESR['lang_title'];?></a>
						<?php echo $GET_UPDATESR['description'];?></div>
						<?php
					}
	}
	$TEMP_TEMPLATE = 2;

}
?>