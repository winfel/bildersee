<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

if (isset($_GET['target'])){
	$target=$_GET['target'];
	$_SESSION['last_target']=$target;
}

@$target=$_SESSION['last_target'];

$pageTitle=translate('search result',true);
$pageDescription=translate('an online photo gallery',true);

$folder=str_replace('.','',str_replace(',','',str_replace('_','',str_replace(' ','',strtolower($folder)))));

$reverse=($folder=='%');

$page=isset($_GET['page'])?$_GET['page']:1;

//this is used for returning to the site;

$_SESSION['last_page']=$page;
$_SESSION['last_folder']=$folder;
$_SESSION['last_filter']=$filter;

$folderGiven=$folder!='%' && $folder!='%%';
$tagGiven=stripos($filter,'tag_')!==false;

$search=getImages($folder,$reverse);

$count=0;
   
while ($line=array_shift($search)){
		
	$count++;
	
	if ($count==1 && $folderGiven && !($folder=='%' || $folder=='%%')) {
		$pageTitle=pretty(trim(substr($line->folderReadable,strpos($line->folderReadable,' '))));
		$folderReadable=$line->folderReadable;
	}
	
	if ($count==1){
		if (!$folderGiven && $tagGiven) {
			$activePart='tags';
			addToBreadcrumb('?mode=tags',translate('tags',true));
		} else {
			addToBreadcrumb('?',translate('events',true));
		}
		
		if ($folderGiven){
			if (!$tagGiven) addToBreadcrumb('',ucfirst(translateWords($pageTitle)));
			else addToBreadcrumb('?folder='.urlencode($folder),ucfirst(translateWords($pageTitle)));
		} 
		
		if ($tagGiven) {
			$temp=$filter;
			$temp=str_replace(' ',', ',$temp);
			$temp=str_replace('notag_',translate('not').' ',$temp);
			$temp=str_replace('tag_','',$temp);
			$temp=str_replace('_',' ',$temp);
			$temp=ucwords_new($temp);
			addToBreadcrumb('',$temp);
		}
	}
	
	
	
	if ($folderGiven){
		$category=$line->subfolder;
	} else {
		$category=pretty($line->folderReadable);
	    if ($line->subfolder) $category.=' - '.$line->subfolder;
	}
	  
	$files[$category][]=$line;
	
}

if (!isset($files)){
	$files=array();
	echo '<h1>'.translate('An error has occured!').'</h1>';
	echo '<p>'.translate('This event does not exist or you do not have access rights.').'</p>';
}

$catOnPage=array();


foreach($files as $category=>$elements){
	$catOnPage[$category]=1;
}

echo '<script>

function onScroll(){
	var element=document.getElementById("images");
	var top=getScrollY();
	var height=window.getHeight();
	var images=element.getElementsByTagName("img");
	
	for (var i in images){
		var image=images[i];
		
		var y=getElementPosition(image);
		
		if (image.title && y>top-200 && y<top+height+400) {
			image.src=image.title;
			image.srcset=image.title+" 1x, "+image.title+"2x 2x";
			image.title="";
		}
		
		if (!hash && y>top+20) {
			hash="scroll"+image.id.substr(3); //the first visible image is the scroll target
		}
		
		if (top<30) hash=""; //no scroll hash when on the top of a page

	}
	
}

</script>';


if ($folderGiven && isset($folderReadable)){echo '<h1>'.ucfirst(translateWords($pageTitle)).' <nobr>('.get_date($folderReadable).')</nobr></h1>';}

if (!isset($files) || !$files) $files=array();

$url='index.php?folder='.urlencode($folder).'&filter='.$filter;
$functionBar='<a href="'.$url.'"><img src="design/events1.png" alt="" />'.translate('standard view',true).'</a>';


echo '<div id="images">';

foreach ($files as $category=>$entries){
			
	if ($category && count($catOnPage)>1) echo '<h2 id="'.urlencode($category).'">'.ucfirst(translateWords($category)).'</h2>';
	
	foreach ($entries as $entry){
		
		$imgurl=$config->imageGetterURL.'?key='.$entry->key.'&amp;size=preview';
				
		$readable=getReadableTags($entry->tags,$entry->sortstring);
		
		$mode='neutral';
		if ($user && stripos($readable,'public')!==false) $mode='public';
		if ($user && stripos($readable,'privat')!==false) $mode='private';

		$frameclass=($user)?'imageframe':'imageframe nouser_imageframe';
		
		echo '
		<div class="'.$frameclass.'" id="'.$entry->key.'">
		<table class="previmage">
		 <tr>
		  <td class="thumb"><img alt="" src="design/ajax-loader.gif" title="'.$imgurl.'" id="img'.$entry->key.'"></td>
		 </tr>';
		
		if ($user) echo '
		 <tr>
		  <td class="tag '.$mode.'" onclick="changeState(\''.$entry->key.'\',true)" id="tags'.$entry->key.'">
		   <span>'.$readable.'</span>
		   <textarea onblur="changeState(\''.$entry->key.'\',false)" onkeyup="handleEnter(event,\''.$entry->key.'\');" >'.$entry->filetags.' </textarea><textarea>'.$entry->filetags.' </textarea>
		  </td>
		 </tr>
		 ';
		
		echo '</table>
		</div>';
	}
}

echo '</div>';


echo '<script>


function handleEnter(e,key){
	var characterCode;
	if(e && e.which){e = e;characterCode = e.which;} 
	else {e = event;characterCode = e.keyCode;}
	
	if(characterCode == 13){activateNext(key);return false;} 
	else {return true;}
}

function activateNext(key){
	changeState(key,false);

	var nextKey=false;var found=false;

	var tds=document.getElementsByTagName("td");

	for(var i in tds){
		var td=tds[i];
		if (!td.className || td.className.search("tag")==-1) continue;

		if (found) {
			nextKey=td.id.substring(4);
		}

		if (td.id=="tags"+key) found=true; else found=false;
	}

	if (nextKey) changeState(nextKey,true);
}

function updateValue(key,value){
	var tagArea=document.getElementById("tags"+key);
	tagArea.getElementsByTagName("span")[0].innerHTML=value;
	var mode="neutral";
	if(value.search("privat")!==-1) mode="private";
	if(value.search("public")!==-1) mode="public";
	document.getElementById("tags"+key).className="tag "+mode;
}

function changeState(key,state){
	if ('.($config->local?'true':'false').') return;  //changeStated switched off, if in local mode
	
	var tagArea=document.getElementById("tags"+key);
	
	tagArea.getElementsByTagName("span")[0].style.display=(state)?"none":"block";
	tagArea.getElementsByTagName("textarea")[0].style.display=(state)?"block":"none";
	
	if (state) tagArea.getElementsByTagName("textarea")[0].focus();
	else {
		if (tagArea.getElementsByTagName("textarea")[0].value.trim() 
		!=tagArea.getElementsByTagName("textarea")[1].value.trim() ){
			tagArea.getElementsByTagName("textarea")[1].value=
			tagArea.getElementsByTagName("textarea")[0].value;
			var invalue=tagArea.getElementsByTagName("textarea")[0].value.trim();
			server_query("updatetag.php?key="+key+"&tags="+invalue,function(value){
				updateValue(key,value);
			});
		}
	}
}



onScroll();

</script>';

?>