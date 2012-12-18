<?php

$pageTitle=translate('search result',true);
$pageDescription=translate('an online photo gallery',true);

$folder=str_replace('_','',str_replace(' ','',strtolower($folder)));

$reverse=($folder=='%')?'DESC':'';

$page=isset($_GET['page'])?$_GET['page']:1;

//this is used for returning to the site;

$_SESSION['last_page']=$page;
$_SESSION['last_folder']=$folder;
$_SESSION['last_filter']=$filter;

$codewordPossible=false;
$codeword=false;
$hasPublic=false;
$folderGiven=$folder!='%' && $folder!='%%';
$tagGiven=stripos($filter,'tag_')!==false;
$codewordGiven=stripos($filter,'codeword_')!==false;

$search=mysql_query("SELECT md5(`key`) as `key`,files.tags as tags, filetags.tags as filetags,subfolder,copyright,folder,sortstring  FROM files LEFT JOIN filetags ON files.`key`=filetags.`image` WHERE $userQuery AND replace(replace(lower(files.folder),' ',''),'_','') LIKE '$folder' $filterSQL ORDER BY sortstring $reverse");


$copyrights=array();
$count=0;
   
while ($line=mysql_fetch_object($search)){
		
	$count++;
	
	if ($count==1 && $folderGiven && !($folder=='%' || $folder=='%%')) {
		$pageTitle=pretty(trim(substr($line->folder,strpos($line->folder,' '))));
	}
	
	if ($count==1){
		if (!$folderGiven && $tagGiven) {
			$activePart='tags';
			addToBreadcrumb('?mode=tags',translate('tags',true));
		} else {
			addToBreadcrumb('?',translate('events',true));
		}
		
		if ($folderGiven){
			if ($tagGiven) addToBreadcrumb('',$pageTitle);
			else addToBreadcrumb('?folder='.urlencode($folder),$pageTitle);
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
		$category=pretty($line->folder);
		if ($line->subfolder) $category.=' - '.$line->subfolder;
	}
	
	$files[$category][]=$line;
	$codewordPossible=$codewordPossible || (stripos($line->tags,'codeword_') !==false);
	$hasPublic=$hasPublic || (stripos($line->tags,'public') !==false);
	
	if ($codewordPossible && !$codeword){		
		$codeword=between('codeword_',' ',$line->tags);
	}
	$copyrights[ucwords_new(str_replace('_',' ',$line->copyright))]=true;  //TODO transfer this to update

}

$catOnPage=array();
$perPage=$config->perPage;
$i=1;$counter=0;

if (!isset($files)){
	$files=array();
	echo '<h1>'.translate('An error has occured!').'</h1>';
	echo '<p>'.translate('This event does not exist or you do not have access rights.').'</p>';
}

foreach($files as $category=>$elements){
	$thisCount=count($elements);
	if ($thisCount>$perPage || $counter+$thisCount>$perPage){
		$i++;
		$counter=0;
	} else {
		$counter+=$thisCount;
	}
	$catOnPage[$category]=$i;
}

$pageDescription.=' ('.$count.' items) ';

echo '<script>

var hashDelay=false;
function onScroll(){
	var element=document.getElementById("images");
	var top=getScrollY();
	var height=window.getHeight();
	var images=element.getElementsByTagName("img");
	var hash=false;
	
	for (var i in images){
		var image=images[i];
		
		var y=getElementPosition(image);
		
		if (image.title && y>top-200 && y<top+height+400) {
			image.src=image.title;
			image.title="";
		}
		
		if (!hash && y>top+20) {
			hash="scroll"+image.id.substr(3); //the first visible image is the scroll target
		}
		
		if (top<30) hash=""; //no scroll hash when on the top of a page

	}
	hashDelay=window.setTimeout(function(){
		if (hashDelay) {
			window.clearTimeout(hashDelay);
			hashDelay=false;
		}
		if (hash) window.replaceHash(hash);
	},1000);
	
}
</script>';

if ($folderGiven){echo '<h1>'.$pageTitle.' <nobr>('.get_date($folder).')</nobr></h1>';}

$copyrights=array_keys($copyrights);

$byString='';

switch (count($copyrights)){
	case 0: break;
	case 1: $byString=translate('taken by').' '.$copyrights[0]; break;
	default:
	    $last=' '.translate('and').' '.array_pop($copyrights);
		$first=implode(', ',$copyrights);
		$byString=translate('taken by').' '.$first.$last;
	break;
}

echo $byString;
$pageDescription.=$byString;

if (!isset($files) || !$files) $files=array();

$navi=$count.' '.translate('images').'&nbsp;&nbsp;&nbsp;';

if (count($files)>1){

	$navi.='<select onchange="setCategory(this.value);">';
	$navi.='<option>'.translate('Jump directly to a topic').'  </option>';
	
	foreach(array_keys($files) as $category){
		$navi.='<option value="'.$catOnPage[$category].'###'.urlencode($category).'">'.$category.'  </option>';
	}
	
	$navi.='</select>&nbsp;&nbsp;&nbsp;';
	

}		

$functionBar=$navi;$functionBar2=$navi;

if (!$config->local && $folderGiven && ($user||$codewordGiven)) {
	$functionBar='<a href="?folder='.urlencode($folder).'&amp;filter='.$filter.'&amp;mode=download"><img src="design/download1.png" alt="" />'.translate('download',true).'</a><span class="seperator"></span>'.$navi;
}

if (!$config->local && !$user && $codewordPossible && !$codewordGiven && $folderGiven) {
	
	echo '<p id="keywordnotice">'.translate('Due to privacy reasons, you only see a selection of photos of this event. You get access to all photos, if you know the codeword.').' <a href="javascript:enterCodeword();">'.translate('Enter the codeword now!').'</a></p>';

}

if (!$config->local && $user && $codeword && !$codewordGiven && $folderGiven && $hasPublic) {
	
	echo '<p id="notice">'.translate('Only a small selection of this event is publically available. The full event (except exlicitally private images) can be accessed with the following codeword:').' <b>'.$codeword.'</b></p>';

}

if (!$config->local && $user && $codeword && !$codewordGiven && $folderGiven && !$hasPublic) {
	
	echo '<p id="notice">'.translate('This event is not publically visible, but can directly be accessed:').' '.$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword.'</p>';

}

$first=true;

foreach ($catOnPage as $cat=>$pag){
	if ($cat && $pag<$page) echo '<h2><a href="javascript:setCategory(\''.$pag.'###'.urlencode($cat).'\')" style="text-decoration:none">'.translate('jump to',true).': '.$cat.'</a></h2>';
}

echo '<div id="images">';

foreach ($files as $category=>$entries){
	
	if ($catOnPage[$category]!=$page) continue;
		
	if ($category) echo '<h2 id="'.urlencode($category).'">'.$category.'</h2>';
	
	foreach ($entries as $entry){
		
		if ($first){
			$first=false;
			$functionBar='<span class="seperator"></span>'.$functionBar;
			$url='index.php?mode=diashow&folder='.urlencode($folder).'&filter='.$filter.'&image='.$entry->key;
			$functionBar='<a href="'.$url.'"><img src="design/galleries1.png" alt="" />'.translate('diashow',true).'</a>'.$functionBar;
		}
		
		$url='?image='.urlencode($entry->key);
		$imgurl=$config->imageGetterURL.'?key='.$entry->key.'&amp;width=300&amp;height=225';
				
		$readable=getReadableTags($entry->tags,$entry->sortstring);
		
		$mode='neutral';
		if ($user && stripos($readable,'public')!==false) $mode='public';
		if ($user && stripos($readable,'privat')!==false) $mode='private';

		$frameclass=($user)?'imageframe':'imageframe nouser_imageframe';
		
		echo '
		<div class="'.$frameclass.'" id="'.$entry->key.'">
		<table class="previmage">
		 <tr>
		  <td class="thumb"><a href="'.$url.'"><img alt="" src="design/ajax-loader.gif" title="'.$imgurl.'" id="img'.$entry->key.'"></a></td>
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

foreach ($catOnPage as $cat=>$pag){
	if ($cat && $pag>$page) echo '<h2 style="clear:both"><a href="javascript:setCategory(\''.$pag.'###'.urlencode($cat).'\')" style="text-decoration:none">'.translate('jump to',true).': '.$cat.'</a></h2>';
}

echo '<script>

function enterCodeword(){
	var result=prompt("'.translate('Please enter the codeword:').'","");
	if (!result) result=""; else result="codeword_"+result.toLowerCase();
	
	var folder="'.urlencode($folder).'";
	
	location.href="?folder="+folder+"&filter="+result;
}


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

function setCategory(data){
	data=data.split("###");
	var cat=data[1];
	var page=data[0];
	var thisPage="'.$page.'";
	if (page==thisPage){
 	window.location.hash="scroll"+cat;
 	hash="#scroll"+cat;
 	processHash(true);
 	return;
	} else {
		var url="?folder='.$folder.'&page="+page+"&filter='.$filter.'#scroll"+cat;
		window.location.href=url;
	}
}


onScroll();

</script>';

?>