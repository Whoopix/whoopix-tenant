<?php
	include "../../config.php";
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	$value=intval($_GET["value"]);
	$id=intval($_GET["id"]);
	if($_GET["action"]=="get_formation_cats")
	{
		$options = array();
		


			$q=db_query("SELECT MC.* FROM `mod_category` MC 
										INNER JOIN mod_materials_kind_list MKL
										ON MKL.`mat_id` = MC.`category_id`
										INNER JOIN mod_mat_tetz MMT
										ON MMT.`kind_id` = MKL.`id`
									WHERE MMT.`tetz_id`='".$value."' AND MC.`lang_id`='".intval($_GET["lang"])."' GROUP BY MC.`category_id`") or die(db_error());
			while($r=db_fetch_array($q))
				$options[]=array($r['category_id'],$r['title']);

			echo "<select id=\"mats1\" name=\"mats1[]\" style=\"width:100px;\"  size=\"6\" class=\"mate\" >";
			
			foreach($options AS $value)
				echo "<option  value=\"".htmlspecialchars($value[0])."\">".htmlspecialchars($value[1])."</option>";
	
				echo "</select>";


	}
	

?>