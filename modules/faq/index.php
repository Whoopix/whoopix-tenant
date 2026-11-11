<?php
	$TEMP_PAGE_TITLE = "שאלות ותשובות";
	$TEMP_INPAGE_TITLE = "שאלות ותשובות";
	function PAGE_TEXT() {
		$q=db_query("SELECT L.* FROM `mod_faqs` L,`mod_faqs_list` T WHERE L.`faq_id`=T.`id` AND `lang_id`=".LANG);
		echo "<div id=\"faq\" style=\"width:545px;margin:auto;\">";
		while($r=db_fetch_array($q)){
			echo "\t\t\t"."<a class=\"faq_title\" onclick=\"return showHideFAQ(this,'faq_".$r["id"]."');\">".$r["title"]."</a>"."\n";
			echo "\t\t\t"."<div id=\"faq_".$r["id"]."\" style=\"display:none;\">".$r["text"]."</div>"."\n";
			//echo "<hr />";
		}
		echo "</div>";
	}
	$TEMP_TEMPLATE = 2;
?>