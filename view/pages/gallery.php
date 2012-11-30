<?php

//echo htmlspecialchars(SID);

$pageTitle=translate('gallery',true);
$activePart='gallery';
$pageDescription='';
		
		echo '<script>
				function setFilter(filter){
					location.href="?filter="+filter;
				}
				
				
				function onScroll(){
					var element=document.getElementById("visu");
					var top=getScrollY();
					var height=window.getHeight();
					var images=element.getElementsByTagName("img");
					
					for (var i in images){
						var image=images[i];
						
						if (!image.style || image.style.display=="none") continue;
						
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
				

				
		      </script>';
				
		$eventData=array();
		$eventCount=0;
		
		$t=time();
		
		$search=mysql_query("SELECT * FROM files WHERE  files.tags LIKE '%auswahl_%' ORDER BY sortstring");
		
		//echo (time()-$t);
		
		$copyrights=array();
		$selections=array();
		
		
		while ($line=mysql_fetch_object($search)){
			$tags=explode(' ',$line->tags);
			foreach ($tags as $tag){
				if (stripos($tag,'copyright_')!==false) @$copyrights[$tag]++;
				if (stripos($tag,'auswahl_')!==false) {
					$selections[$tag][]=$line;
				}
			}
		}
		
		//var_dump($copyrights);
		
		$selected=(isset($_GET['selection']))?'auswahl_'.$_GET['selection']:false;
		if ($selected && !isset($selections[$selected])) $selected=false;
		
		if (!$selected){
		
			$element=array();$element['link']='';$element['text']=translate('gallery',true);$breadcrumb[]=$element;
		
			echo '<h1>'.translate('gallery',true).'</h1>';
		
			$selections=array_reverse($selections);
			
			foreach ($selections as $selection=>$images){
				$selection=substr($selection,8);
				$readable=ucwords_new(str_replace('_',' ',$selection));
				$image=$images[rand (0 ,count($images)-1)];
				$key=$image->key;
				
				echo'<div class="overviewframe">
					   <div class="albuminfo" style="text-align:center">
						<a href="?mode=gallery&selection='.$selection.'" style="display:block;padding-right:10px">
							<img width="170" height="170" src="design/loading.jpg" title="'.$config->imageGetterURL.'?key='.$key.'&amp;width=170&amp;height=170&amp;minimum=1">
							
							<span class="readable">'.$readable.'</span> 
						</a></div>
					</div>';
				
			}
		} else {
			$images=$selections[$selected];
			$readable=ucwords_new(str_replace('_',' ',substr($selected,8)));
			$pageTitle=$readable;
			$pageDescription=translate('an online photo gallery',true);
			$element=array();$element['link']='?mode=gallery';$element['text']=translate('gallery',true);$breadcrumb[]=$element;
			$element=array();$element['link']='';$element['text']=$readable;$breadcrumb[]=$element;
			
			echo "<h1>$readable</h1>";
			
			for($i=0;$i<count($images);$i++) {
			
					$prev=(isset($images[$i-1]))?$images[$i-1]->key:'';
					$entry=$images[$i];
					$next=(isset($images[$i+1]))?$images[$i+1]->key:'';
				
					$imgurl=$config->imageGetterURL.'?key='.$entry->key.'&amp;width=300&amp;height=225';
					
					$year=substr($entry->sortstring,1,4);
					
					$readable='&copy; '.ucwords_new(str_replace('_',' ',$entry->copyright)).', '.$year;
					
					$mode='neutral';					
					$frameclass="imageframe nouser_imageframe";
					
					echo '
					<div class="'.$frameclass.'" id="'.$entry->key.'">
					<table class="previmage" onclick="showImage(\''.$entry->key.'\',\''.$prev.'\',\''.$next.'\')">
					 <tr>
					  <td class="thumb"><img alt="" src="design/ajax-loader.gif" title="'.$imgurl.'" id="img'.$entry->key.'"></td>
					 </tr>
					</table>
					</div>';
			}
			
		}

	
		echo'<br style="clear:both">';
		echo '<script>onScroll();</script>';
		
		echo "<script type=\"text/javascript\">
		
				function showImage(key,prev,next){
					var overlay=document.getElementById('overlay');
					var mainurl='".$config->imageGetterURL."?key='+key+'&width=1000000&height=1000';
					overlay.onclick=close;
					overlay.style.display='block';
					
					var myWidth = 0, myHeight = 0;
				    if( typeof( window.innerWidth ) == 'number' ) {
					//Non-IE
					myWidth = window.innerWidth;
					myHeight = window.innerHeight;
				  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
					//IE 6+ in standards compliant mode
					myWidth = document.documentElement.clientWidth;
					myHeight = document.documentElement.clientHeight;
				  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
					//IE 4 compatible
					myWidth = document.body.clientWidth;
					myHeight = document.body.clientHeight;
				    }
					
					var mHeight=myHeight-30;
					
					overlay.innerHTML='<img src=\"'+mainurl+'\" style=\"margin-top:15px;max-width:100%;max-height:'+mHeight+'px\">';
					
				}
				
				function close(){
					var overlay=document.getElementById('overlay');
					overlay.style.display='none';
				}
		
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
				

		 </script>";

?>