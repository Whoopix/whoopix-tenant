<?php
		$TEMP_PAGE_TITLE = $lang["SITEMAP"];
		$TEMP_INPAGE_TITLE = $lang["SITEMAP"];
		function PAGE_TEXT() { 
			echo "\t\t<ul id=\"sitemap\">\n";
			echo "\t\t\t"."<li><a href=\"\"><% LANG_MAIN %></a></li>"."\n";
			
			$q=db_query("SELECT L.`title`,L.`page_id` FROM `mod_pages` L,`mod_pages_list` T WHERE T.`id`=L.`page_id` AND `lang_id`=".LANG." AND `page_id`>1");
			while($r=db_fetch_array($q))
				echo "<li><a href=\",pages,".$r["page_id"]."\">".$r["title"]."</a></li>";

			echo "\t\t\t"."<li><a href=\"/,catalog\"><% LANG_CATALOG %></a></li>"."\n";
			$q=db_query("SELECT * FROM `mod_materials` L,`mod_materials_list` T WHERE T.`id`=L.`material_id` AND `lang_id`=".LANG) or die(db_error());
			echo "\t\t\t<ul>\n";
			while($r=db_fetch_array($q))
				echo "<li><a href=\"/,catalog,".$r["material_id"]."\">".$r["title"]."</a></li>";
			echo "\t\t\t</ul>\n";
			echo "\t\t\t"."<li><a href=\"/,products\"><% LANG_PRODUCTS %></a></li>"."\n";
			echo "\t\t\t<ul>\n";
			$q=db_query("SELECT L.`title`,L.`procat_id` FROM `mod_procats` L
							LEFT JOIN `mod_procats_list` T ON L.`procat_id`=T.`id` WHERE T.`parent`=0 AND `lang_id`=".LANG);
			while($r=db_fetch_array($q))
				echo "<li><a href=\"/,products,".$r["id"]."\">".$r["title"]."</a></li>";
			echo "\t\t\t</ul>\n";
			echo "\t\t\t"."<li><a href=\"/,contact\"><% LANG_CONTACT %></a></li>"."\n";
			echo "\t\t</ul>\n";
		}
		$TEMP_TEMPLATE = 2;
?>