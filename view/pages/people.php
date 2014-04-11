<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');


if (!$user) die();

$activePart='people';
$element=array();$element['link']='';$element['text']='Tags';$breadcrumb[]=$element;

$pageTitle=translate('people',true);

$temp=array();
$people=array();
$search=mysql_query("SELECT * FROM people ORDER BY tag");
while($line=mysql_fetch_assoc($search)){
  $temp[$line['tag']]=$line;
}
		      
		$search=mysql_query("SELECT `key`,tags FROM files WHERE $userQuery $filterSQL ORDER BY sortstring DESC");
		
		
		$tags=array();
 
		 while($line=mysql_fetch_object($search)){
		 
		    $string=$line->tags;
		    
		    $string=explode(' ',$string);
		    
		    foreach ($string as $tag){
		    	if (isset($temp[$tag]) && !isset($people[$tag])) {
		    		$people[$tag]=$temp[$tag];
		    		$people[$tag]['image']=md5($line->key);
		    		unset($temp[$tag]);
		    	}
		    }
		    
		 }

		$search=mysql_query("SELECT md5(`key`) as `key`,tags FROM files WHERE files.tags LIKE '%portrait%' ORDER BY sortstring");
		
		
		$tags=array();
 
		 while($line=mysql_fetch_object($search)){
		 
		    $string=$line->tags;
		    
		    $string=explode(' ',$string);
		    
		    foreach ($string as $tag){
		    	if (isset($people[$tag])) {
		    		$people[$tag]['image']=$line->key;
		    	}
		    }
		    
		 }

		 
		 foreach ($people as $data){
		 	
		 	$readable=ucwords_new(str_replace('_',' ',$data['tag']));
		 	$key=$data['image'];
		 	$tag=$data['tag'];
		 	$birthday=$data['birthday'];
		 	$dead=$data['dead']==1;
		 	
		 	if ($birthday && !$dead){
		 		$readable.='<br>('.getAge($birthday).')';
		 	}
		 	
 			echo'<div class="overviewframe">
			   <div class="albuminfo" style="text-align:center">
				<a href="?filter=tag_'.$tag.'&folder=%" style="display:block;padding-right:10px">
					<img width="170" height="170" src="design/loading.jpg" title="'.$config->imageGetterURL.'?key='.$key.'&amp;width=170&amp;height=170&amp;minimum=1">
					
					<span class="readable">'.$readable.'</span> 
				</a></div>
			</div>';
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
					var divs=document.getElementsByTagName('div');
					var count=0;
					for (var i in divs){
						var div=divs[i];
						if (div.className!='overviewframe') continue;
						if (value=='' || div.innerHTML.toLowerCase().indexOf(value.toLowerCase())!==-1) {
							div.style.display='block';
							count++;
						} else div.style.display='none';
						var image=div.getElementsByTagName('img')[0];
						if (value=='' || div.innerHTML.toLowerCase().indexOf(value.toLowerCase())!==-1) image.style.display='block'; else image.style.display='none';
					}
					
					var h2s=document.getElementsByTagName('h2');
					for (var i in h2s){
						var h2=h2s[i];
						if (value=='') h2.style.display='block';
						else h2.style.display='none';
					}
					
					document.getElementById('count').innerHTML=count+' events';
					
					onScroll();
				}
				
				function onScroll(){
					var element=document.getElementById('visu');
					var top=getScrollY();
					var height=window.getHeight();
					var images=element.getElementsByTagName('img');
					
					for (var i in images){
						var image=images[i];
						
						if (!image.style || image.style.display=='none') continue;
						
						var e=image;
						var y=0;
						
						do {
						y+=e.offsetTop;
						
						e=e.offsetParent;
						} while(e != null);
						
						if (y>top-200 && y<top+height+400) {
							image.src=image.title;
						}
				
					}
				}
				

		 </script>";

		 echo '<script>onScroll();</script>';
?>