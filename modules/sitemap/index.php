<?php
		$TEMP_PAGE_TITLE = $lang["SITEMAP"];
		$TEMP_INPAGE_TITLE = $lang["SITEMAP"];
		function PAGE_TEXT() { 
		
	echo "\t\t<ul id=\"sitemap\">\n";
	echo "\t\t\t"."<li><a href=\"\"><% LANG_MAIN %></a></li>"."\n";
$menu_id_q=db_query("SELECT * FROM `mod_menu` WHERE `lang_id`='".LANG."' AND `link`='".db_escape($link)."' AND `show_m`='0' ");
$menu_id_r=db_fetch_array($menu_id_q);
function get_first_menus($link){
	$menu_id_q=db_query("SELECT * FROM `mod_menu` WHERE `lang_id`='".LANG."' AND `id`='".db_escape($link)."' AND `show_m`='0' ");
	$menu_id_r=db_fetch_array($menu_id_q);
	if($menu_id_r["parent"]!=0)
		return get_first_menus($menu_id_r["parent"]);
	else
		return $menu_id_r["id"];
}
function do_menus($menu_id=0,$parent=0,$i=1){
	$q=db_query("SELECT * FROM `mod_menu` WHERE `lang_id`='".LANG."' AND `parent`=".$parent."  ORDER BY `order`");
	$t=true;
	while($r=db_fetch_array($q)){
		$put=LANGDIR=="rtl"?"right":"left";
		$q1=db_query("SELECT * FROM `mod_menu` WHERE `parent`=".$r["id"]." AND `show_m`='0' ");
		echo "\t\t<li><a ".($i==1?" class=\"in\"":"")."href=\"".(($r["link"]==""|| $r["link"]=="/")?"#":substr($r["link"],1))."\">". $r["title"]."</a>\n";
		if(db_num_rows($q1)>0){
			echo "\t\t\t<ul>\n";
			do_menus($menu_id,$r["id"],$i+1);
			echo "\t\t\t</ul>\n";
		}
		echo "\t\t</li>\n";
		$t=false;
	}
}
	do_menus(get_first_menus($menu_id_r["id"]));
echo "\t\t</ul>\n";	
	echo "\t\t<ul id=\"sitemap\">\n";

	
function get_first_menuss($id,$title){
	$put=LANGDIR=="rtl"?"right":"left";
		echo "\t\t<li><a ".($i==1?" class=\"in\"":"")."href=\"/,cats_catalog,cat_".$id."\">". $title."</a>\n";
}	
function get_first_menusss($id,$title){
	$put=LANGDIR=="rtl"?"right":"left";
		echo "\t\t<li><a ".($i==1?" class=\"in\"":"")."href=\"/,cats_catalog,kind_".$id."\">". $title."</a>\n";
}
function get_first_menusssss($id,$title){
	$put=LANGDIR=="rtl"?"right":"left";
		echo "\t\t<li><a ".($i==1?" class=\"in\"":"")."href=\"/,cats_catalog,".$id."\">". $title."</a>\n";
}
	$menu_id_q7=db_query("SELECT mod_catalogs.* , mod_catalogs_list.* FROM `mod_catalogs` INNER JOIN  mod_catalogs_list
		ON  mod_catalogs.catalog_id=mod_catalogs_list.id
		WHERE mod_catalogs.lang_id='".LANG."' order by mod_catalogs_list.order asc ")or die(db_error());
	while($menu_id_r7=db_fetch_array($menu_id_q7)){
		$menu_id_q=db_query("SELECT mod_category.* , mod_category_list.* FROM `mod_category` INNER JOIN mod_category_list
			ON mod_category.category_id=mod_category_list.id
			WHERE mod_category.lang_id='".LANG."' and mod_category_list.parent = 0 && catalog_id=".$menu_id_r7["id"]." order by mod_category_list.order asc ")or die(db_error());
			get_first_menusssss($menu_id_r7[id],$menu_id_r7[title]);
			echo "\t\t\t<ul>\n";
		while($menu_id_r=db_fetch_array($menu_id_q)){
			get_first_menuss($menu_id_r["id"],$menu_id_r["title"]);
			$menu_id_q2=db_query("SELECT * FROM `mod_category_list` WHERE `parent`='".$menu_id_r["id"]."' order by `order` asc ");
			echo "\t\t\t<ul>\n";
			while($menu_id_r2=db_fetch_array($menu_id_q2)){
				get_first_menuss($menu_id_r2["id"],$menu_id_r2["title"]);
				$menu_id_q3=db_query("SELECT * FROM `mod_category_list` WHERE `parent`='".$menu_id_r2["id"]."' order by `order` asc ");
				echo "\t\t\t<ul>\n";
				while($menu_id_r3=db_fetch_array($menu_id_q3)){
					get_first_menuss($menu_id_r3["id"],$menu_id_r3["title"]);
				}
				$menu_id_qq3=db_query("SELECT * FROM `mod_materials_kind_list` WHERE `mat_id`='".$menu_id_r2["id"]."' order by `order` asc ");
				while($menu_id_rr3=db_fetch_array($menu_id_qq3)){
					get_first_menusss($menu_id_rr3["id"],$menu_id_rr3["title"]);
				}
				echo "\t\t\t</ul>\n";
			}
			$menu_id_qq2=db_query("SELECT * FROM `mod_materials_kind_list` WHERE `mat_id`='".$menu_id_r["id"]."' order by `order` asc ");
			while($menu_id_rr2=db_fetch_array($menu_id_qq2)){
				get_first_menusss($menu_id_rr2["id"],$menu_id_rr2["title"]);
			}
			echo "\t\t\t</ul>\n";
		}
		echo "\t\t\t</ul>\n";
}
	
echo "\t\t</ul>\n";
}
	/*		
			$q=db_query("SELECT L.`title`,L.`page_id` FROM `mod_pages` L,`mod_pages_list` T WHERE T.`id`=L.`page_id` AND `lang_id`=".LANG." AND `page_id`>1");
			while($r=db_fetch_array($q))
				echo "<li><a href=\",pages,".$r["page_id"]."\">".$r["title"]."</a></li>";

			echo "\t\t\t"."<li><a href=\"/,cats_catalog\"><% LANG_PRODUCTS %></a></li>"."\n";
			$q=db_query("SELECT L.`title`,L.`material_id` FROM `mod_materials` L,`mod_materials_list` T WHERE T.`id`=L.`material_id` AND `lang_id`=".LANG);
			echo "\t\t\t<ul>\n";
			while($r=db_fetch_array($q))
				echo "<li><a href=\"/,catalog,".$r["material_id"]."\">".$r["title"]."</a></li>";
			echo "\t\t\t</ul>\n";
			echo "\t\t\t"."<li><a href=\"/,products\"><% LANG_PRODUCTS %></a></li>"."\n";
			echo "\t\t\t<ul>\n";
			$q=db_query("SELECT L.`title`,L.`catalog_id` FROM `mod_catalogs` L
							LEFT JOIN `mod_catalogs_list` T ON L.`catalog_id`=T.`id` WHERE  `lang_id`=".LANG);
			while($r=db_fetch_array($q))
				echo "<li><a href=\"/,cats_catalog,".$r["catalog_id"]."\">".$r["title"]."</a></li>";
			echo "\t\t\t</ul>\n";
			echo "\t\t\t"."<li><a href=\"/,contact\"><% LANG_CONTACT %></a></li>"."\n";
			echo "\t\t</ul>\n";
		}
	*/

		$TEMP_TEMPLATE = 2;
?>