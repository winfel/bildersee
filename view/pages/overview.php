<?php

$pageTitle=$config->pageTitle;
$pageDescription='';
$element=array();$element['link']='';$element['text']=translate('events',true);$breadcrumb[]=$element;
		
		if (!$config->local){
			if (!$user){
				
				echo '<h1>Willkommen! Welcome! Bienvenidos! Benvinguts!</h1>';
				
				
			} else {
				
				$heading=translate('Hello #NAME#. Welcome to #PAGETITLE#!');
				$heading=str_replace('#NAME#',$user,$heading);
				$heading=str_replace('#PAGETITLE#',$config->pageTitle,$heading);
				
				echo '<h1>'.$heading.'</h1>';
			}
		}
		
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
		
		$search=mysql_query("SELECT folder, `key` AS stdthumb, category, tags FROM files WHERE $userQuery AND SUBSTR(folder,1,4)<'9' $filterSQL GROUP BY folder ORDER BY folder DESC");
		
		//echo (time()-$t);
		
		while ($line=mysql_fetch_object($search)){
			$entry=array();
			$entry['folder']=$line->folder;
			$eventCount++;
			
			$category=$line->category;
			if (strpos($category,'(')!==false) $category='';
			$category=ucwords(str_replace('_',' ',$category));
			
			if ($category=='2014') $category='';
			if ($category=='2013') $category='';
			if ($category=='2012') $category='';
			if ($category=='2011') $category='';
			if ($category=='2010') $category='';
			if ($category=='2009') $category='';
			if ($category=='2008') $category='';
			if ($category=='2007') $category='';
			if ($category=='2006') $category='';
			if ($category=='2005') $category='';
			if ($category=='2004') $category='';
			if ($category=='2003') $category='';
			if ($category=='2002') $category='';
			if ($category=='2001') $category='';
			if ($category=='2000') $category='';
			
			if ($category=='Partnerschaft') $category=translate('Twinning');
			if ($category=='Djk') $category='DJK Rheda';
			if ($category=='Ebr2012') $category='EBR 2012';
			if ($category=='Ftcr') $category='FTCR';
			if ($category=='Diverses') $category='';
			
			if ($category) $category=translate('in').' '.$category;
			
			$entry['codeword']=strpos($line->tags,'codeword_')!==false;
			$entry['category']=$category;
			$entry['thumb']=$line->stdthumb;
			$eventData[$line->folder]=$entry;

		}
		
		echo '<p id="count">'.$eventCount.' '.translate('events',true).'</p>';
		
		echo '<div style="width:100%;margin:auto;clear:both;">';
		
		$search=mysql_query("SELECT folder,`key` AS thumb FROM files WHERE tags LIKE '%thumb%' $filterSQL GROUP BY folder");
		
		while ($line=mysql_fetch_object($search)){
			$eventData[$line->folder]['thumb']=$line->thumb;
		}
		
		$months=array();
		$months['01']=translate('January');
		$months['02']=translate('February');
		$months['03']=translate('March');
		$months['04']=translate('April');
		$months['05']=translate('May');
		$months['06']=translate('June');
		$months['07']=translate('July');
		$months['08']=translate('August');
		$months['09']=translate('September');
		$months['10']=translate('October');
		$months['11']=translate('November');
		$months['12']=translate('December');
		
		
		$lastMonth=false;
		foreach ($eventData as $entry){
			
			if (!isset($entry['folder'])) continue;
			
			$readable=pretty($entry['folder']);
			
			$pos=strpos($readable,' ');
			if ($pos) $readable=substr($readable,$pos+1); else $readable=substr($entry['category'],3);

			$month=substr($entry['folder'],0,7);
			
			if ($month!==$lastMonth) echo '<h2 style="clear:both;text-align:left;margin-left:20px;">'.@$months[substr($month,5,2)].' '.substr($month,0,4).'</h2>';
			
			$lastMonth=$month;
			
			echo'<div class="overviewframe">
			       <div class="albuminfo">
					<a href="?folder='.urlencode($entry['folder']).'&amp;filter='.$filter.'" style="display:block;padding-right:10px">
						<img width="170" height="170" src="design/loading.jpg" title="'.$config->imageGetterURL.'?key='.$entry['thumb'].'&amp;width=170&amp;height=170&amp;minimum=1">
						
						<span class="readable">'.$readable.'</span> <br />
						<span class="date">'.get_date($entry['folder']).' '.$entry['category'].'</span>
					</a></div>
				</div>';
		}
		
		echo '</div>';
		echo'<br style="clear:both">';
		echo '<script>onScroll();</script>';
		
		echo "<script type=\"text/javascript\">
		
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