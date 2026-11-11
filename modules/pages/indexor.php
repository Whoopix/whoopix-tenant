<?php
if ($get_id) {
	$GET_PAGEINFO_q = db_query("SELECT L.*,T.`access`,T.`template` FROM `mod_pages` L,`mod_pages_list` T WHERE T.`id`=L.`page_id` AND L.`page_id` = $get_id AND L.`lang_id`=".LANG) or die(db_error());
	$GET_PAGEINFO_c = db_num_rows($GET_PAGEINFO_q);
	if ($GET_PAGEINFO_c > 0) {
		
		$GET_PAGEINFO_r = db_fetch_array($GET_PAGEINFO_q);
		$LOGOFORTHISPAGE1=$GET_PAGEINFO_r[image];
		$StartQu = db_query("SELECT * FROM `mod_iimage_list` WHERE `id`='$LOGOFORTHISPAGE1'") or die(db_error());
		$StartQuResult = db_fetch_array($StartQu);
		$LOGOFORTHISPAGE= $StartQuResult[imagepath];
		
		$TEMP_PAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['title']);
		$TEMP_INPAGE_TITLE = htmlspecialchars($GET_PAGEINFO_r['title']);
		if (!do_no_access($GET_PAGEINFO_r["access"]))
		{
			function PAGE_TEXT() { 
				global $GET_PAGEINFO_r;
				echo "<meta HTTP-Equiv='Refresh' Content='0; URL=,members,login' />"; 
			}
			$TEMP_TEMPLATE = 2;
		} else {
			function PAGE_TEXT() { 
				global $GET_PAGEINFO_r,$lang;
								
				echo $GET_PAGEINFO_r['text']; 
				
				
			}
			$TEMP_TEMPLATE = $GET_PAGEINFO_r[template];
		}
	}

	else {
		$TEMP_PAGE_TITLE = "עמוד לא קיים";
		function PAGE_TEXT() { 
			echo "<h3>עמוד לא קיים</h3>";
			echo"העמוד שאתה מחפש אינו קיים"; 
		}
		$TEMP_TEMPLATE = 2;

	}
}
?>