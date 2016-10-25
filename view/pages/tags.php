<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');


$activePart='tags';
$element=array();$element['link']='';$element['text']='Tags';$breadcrumb[]=$element;

$pageTitle=translate('tags',true);

$people=json_decode(file_get_contents($config->settingsPath.'/people'),true);

if ($user) echo '<a href="?mode=people">'.translate('search for people',true).'</a>';

echo '<div style="width:800px;margin:auto">';
		      
		$search=mysql_query("SELECT tags FROM files WHERE $userQuery AND SUBSTR(folder,1,4)<'9' $filterSQL");
		
		
		$tags=array();
 
		 while($line=mysql_fetch_object($search)){
		 
		    $folder='';//$tags->folder;
		    $string=$line->tags;
		    
		    $string=explode(' ',$string);
		    
		    foreach ($string as $tag){
		    	$tags[$tag]=1;
		    }
		    
		    //echo "$folder $tags<br />";
		 }
		 
		 ksort($tags);
		 $oldLetter='';
		 
		 foreach (array_keys($tags) as $tag){
		 	
		 		if (isset($people[$tag])) continue;
		 	    if ($tag=='gk') continue;
		    	if ($tag=='archiv') continue;
		    	if ($tag=='person') continue;
		    	if ($tag=='noperson') continue;
		    	if ($tag=='public') continue;
		    	if ($tag=='privat') continue;
		    	if ($tag=='thumb') continue;
		    	if ($tag=='top') continue;
		    	if ($tag=='rotate') continue;
		    	if ($tag=='rotatel') continue;
		    	if ($tag=='rotater') continue;
		    	if ($tag=='norotate') continue;
		    	if ($tag=='norandom') continue;
		    	if ($tag=='gruppe') continue;
		    	if (stripos($tag,'gk_')===0) continue;
		    	if (stripos($tag,'geo_')===0) continue;
		    	if (stripos($tag,'codeword_')===0) continue;
		    	if (stripos($tag,'copyright_')===0) continue;
		    	if (stripos($tag,'thumb_')===0) continue;
		    	if (stripos($tag,'year_')===0) continue;
		    	if (stripos($tag,'autotag_')===0) continue;
		    	if (stripos($tag,'photo_')===0) continue;
		    	if (stripos($tag,'auswahl')===0) continue;
		 			 	
		 	$readable=explode('_',$tag);
		 	foreach ($readable as $k=>$v){
		 		$readable[$k]=ucfirst($v);
		 	}
		 	$readable=implode($readable,' ');
		 	
		 	$letter=substr($readable,0,1);
		 	
		 	if ($letter!=$oldLetter) {
		 		echo "<h1>$letter</h1>";
		 	}
		 	
		 	echo "<a href=\"?filter=tag_$tag&amp;folder=%\" class=\"taglink\">&nbsp; $readable &nbsp;</a>";
		 	
		 	$oldLetter=$letter;
		 }
		 
		 
		 echo "<script>
		 
		 		document.getElementById('filter').style.display='block';
		 
				var filterTimeout=false;
				function filterDelayed(){
				   	   if (filterTimeout) {
				   	   	   clearTimeout(filterTimeout);
				   	   	   filterTimeout=false;
				   	   }
				   	   filterTimeout=setTimeout(\"filter(document.getElementById('filter').value)\",500);
				}
				
				function filter(value){
					var divs=document.getElementsByTagName('a');
					for (var i in divs){
						var div=divs[i];
						if (div.className!='taglink') continue;
						if (value=='' || div.innerHTML.toLowerCase().indexOf(value.toLowerCase())!==-1) div.style.display='inline'; else div.style.display='none';
					}
					
					var h1s=document.getElementsByTagName('h1');
					for (var i in h1s){
						var h1=h1s[i];
						if (value=='') h1.style.display='block';
						else h1.style.display='none';
					}
				}
				

		 </script>";

echo '</div>';

?>