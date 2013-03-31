<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

cleanupCache();

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

$search=getAlbums();

echo '<p id="count">'.count($search).' '.translate('events',true).'</p>';

echo '<div style="width:100%;margin:auto;clear:both;">';

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
while ($entry=array_shift($search)){

	if (!isset($entry->folder)) continue;
	
	$readable=pretty($entry->folder);
	
	$pos=strpos($readable,' ');
	if ($pos) {
		$readable=substr($readable,$pos+1);
	} else {
		$readable=substr($entry->category,3);
	}
	
	$month=substr($entry->folder,0,7);
	
	if ($month!==$lastMonth) echo '<h2 style="clear:both;text-align:left;margin-left:20px;">'.@$months[substr($month,5,2)].' '.substr($month,0,4).'</h2>';
	
	$lastMonth=$month;
	
	echo'<div class="overviewframe">
	<div class="albuminfo">
	<a href="?folder='.urlencode($entry->folderID).'&amp;filter='.$filter.'" style="display:block;padding-right:10px">
	<img width="170" height="170" src="design/loading.jpg" title="'.$config->imageGetterURL.'?key='.$entry->thumb.'&amp;width=170&amp;height=170&amp;minimum=1">
	
	<span class="readable">'.$readable.'</span> <br />
	<span class="date">'.get_date($entry->folder).' '.$entry->category.'</span>
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
		if (!h2.style) continue;
		if (value=='') h2.style.display='block';
		else h2.style.display='none';
	}
	
	document.getElementById('count').innerHTML=count+' events';
	
	onScroll();
}


</script>";

?>