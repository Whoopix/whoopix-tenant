<?php
	$TEMP_PAGE_TITLE = '<% LANG_ADVSEARCH %>';
	$TEMP_INPAGE_TITLE = "<% LANG_ADVSEARCH %>";
	function PAGE_TEXT() {
?>
		<script type="text/javascript">
			function mat_is(){
				if($('mattt').selected){
					if($defined($('mats1')))
						$('mats1').destroy();
					
					$$('td.form_search').each(function(elem){
						
						elem.setStyle('display','none');
						elem.setStyle('width',0);
						
					
					});
					
					$$('td.cat_search').each(function(elem){
						elem.setStyle('display','block');
						elem.setStyle('width',100);
					
					});
					
					document.getElements('.mate').each(function(ele){
						ele.setStyle("display","");
						
						
					});
				}else{
					document.getElements('.mate').each(function(ele){
						ele.setStyle("display","none");
					});	
				}
				
				if($('tezzz').selected){
					
					$$('td.cat_search').each(function(elem){
						
						elem.setStyle('display','none');
						elem.setStyle('width',0);
						
					
					});
					
					$$('td.form_search').each(function(elem){
						elem.setStyle('display','block');
						elem.setStyle('width',100);
					
					});
					document.getElements('.tez_search').each(function(ele){
						ele.setStyle("display","");
					});
				}else{
					document.getElements('.tez_search').each(function(ele){
						ele.setStyle("display","none");
					});	
				}
			}
			var url="/modules/search/AJAX.php";
			var lang=<?=LANG;?>;
			function CreateCats(ele){
				
				if($defined($('mats1')))
					$('mats1').destroy();
				
				var req = new Request({   
	            method: 'get',   
				url: url, 
				noCache: true , 
	           data: { 
					'action' : 'get_formation_cats',
					'value':ele.options[ele.selectedIndex].value,
					'lang':lang
				}, 
	            onComplete: function(response) {
								
					var selects = new Array();
					/*$('selects_container').getElements('select').each(function(elem){
						var clone = elem.clone(true,true).cloneEvents(elem);;
						//alert(elem.get('name'));
						clone.setProperty('size',6);
						selects.push(clone);
						clone.setProperty('name',elem.get('name'));
					});*/
					//$('selects_container').empty();
					//$('selects_container').innerHTML=response;
					$('ajax_resp').innerHTML=response;
					
					//selects.reverse();
					
					//selects.each(function(ele){
					//	ele.inject($('selects_container'),'top');
					//});
					
				} 
			}).send();
			
			}
		
		</script>
		<form action=",search" method="post">
			<table border="0" width="100%" style="margin:auto;">
				<tr>
					<td valign="top" colspan="2">
						<input type="hidden" name="normal" value="0" />
						<b style="font-size:10pt;"><% LANG_SEARCH %>:</b> <input style="width:300px;height:15px;margin-bottom:1px;" type="text" name="q" value="" />	
						<input type="submit" value="<% LANG_SEND %>" class="button normal" />
					</td>
				</tr>
				</table>
				<table width="400">
				<tr>
					<td valign="top" style="width:45px;padding-top:15px;">
						<b><% LANG_SearchIn %>:</b>
					</td>
					<td id="selects_container" valign="top" style="width:100px" >
						<select name="search_in[]" style="width:100px;" onchange="mat_is();"  size="6">
							<option value="1"><% LANG_PagesTitle %></option>
							<option value="2"><% LANG_FAQTitle %></option>
							<option value="3"><% LANG_SalesTitle %></option>
							<option value="4"><% LANG_NewsTitle %></option>
							<option value="5" id="mattt"><% LANG_RawMaterialsTitle %></option>
							<option value="6" id="tezzz"><% LANG_FORMATIONSLIST %></option>
						</select>
					</td>
					<td class="cat_search" style="width:100px">
						<select name="mats[]" style="width:100px;display:none;" multiple="multiple" size="6" class="mate" >
<?php
			$q=db_query("SELECT `title`,`category_id` FROM `mod_category` WHERE `lang_id`=".LANG);
			while($r=db_fetch_array($q)){
?>
							<option value="<?=$r["category_id"];?>"><?=htmlspecialchars($r["title"]);?></option>
<?php
			}
?>
						</select>	
						</td>
						<td class="cat_search" style="width:100px">
						<select name="tetz[]" style="width:100px;display:none;" multiple="multiple" size="6" class="mate" >
<?php
			$q=db_query("SELECT `title`,`tetz_id` FROM `mod_tetzs` WHERE `lang_id`=".LANG);
			while($r=db_fetch_array($q)){
?>
							<option value="<?=$r["tetz_id"];?>"><?=htmlspecialchars($r["title"]);?></option>
<?php
			}
?>
						</select>	
						</td>
						<td class="form_search" style="width:100px">
						
			<select name="tetz1[]" style="width:100px;display:none;" onchange="CreateCats(this);"  size="6" class="tez_search" >
<?php
			$q=db_query("SELECT MT.`title`,MT.`tetz_id` FROM `mod_tetzs` MT 
							 INNER JOIN `mod_tetzs_list` MTL
							ON  MTL.`id` = MT.`tetz_id` 				
							WHERE 
							MTL.`catalog_id` = 4
							AND `lang_id`=".LANG);
			while($r=db_fetch_array($q)){
?>
							<option value="<?=$r["tetz_id"];?>"><?=htmlspecialchars($r["title"]);?></option>
<?php
			}
?>
						</select>
					</td>
					<td class="form_search" id="ajax_resp" style="width:100px">
					</td>
			</tr>
			</table>
		</form>
<?php
	}
	$TEMP_TEMPLATE = 2;
	


?>