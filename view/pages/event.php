<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

$pageTitle=translate('search result',true);
$pageDescription=translate('an online photo gallery',true);

$folder=str_replace('.','',str_replace(',','',str_replace('_','',str_replace(' ','',strtolower($folder)))));

$reverse=($folder=='%');

$page=isset($_GET['page'])?$_GET['page']:1;

//this is used for returning to the site;

$_SESSION['last_page']=$page;
$_SESSION['last_folder']=$folder;
$_SESSION['last_filter']=$filter;

$codewordPossible=false;
$codeword=false;
$autocodeword=false;
$hasPublic=false;
$folderGiven=$folder!='%' && $folder!='%%';
$tagGiven=stripos($filter,'tag_')!==false;
$codewordGiven=stripos($filter,'codeword_')!==false;
$restrictedToAuthor=stripos($filter,'copyright_')!==false;

$mayDownload=!$config->local && $folderGiven && ($user||$codewordGiven);

$search=getImages($folder,$reverse);

$copyrights=array();
$count=0;
$hasThumb=false;
   
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
		$category=pretty($line->folderReadable);
		if ($line->subfolder) $category.=' - '.$line->subfolder;
	}
	
	$files[$category][]=$line;
	$codewordPossible=$codewordPossible || (stripos($line->tags,'codeword_') !==false);
	$hasPublic=$hasPublic || (stripos($line->tags,'public') !==false);
	if (!$config->local && (stripos($line->tags,'download') !==false)) $mayDownload=true;
	if ((stripos($line->tags,'thumb') !==false)){
		$thumb=$line->key;
	}
	
	if ($codewordPossible && !$codeword){		
		$codeword=between('codeword_',' ',$line->tags);
		$autocodeword=(stripos($codeword,'direct') !==false);
	}
	
	if (!isset($copyrights[$line->copyright])) {
		$copyReadable=ucwords_new(str_replace('_',' ',$line->copyright));
		if (!$restrictedToAuthor) {
			$newFilter=trim($filter.' copyright_'.$line->copyright);
			$copyrights[$line->copyright]='<a href="?folder='.urlencode($folder).'&amp;filter='.urlencode($newFilter).'">'.$copyReadable.'</a>';
		} else {
			$copyrights[$line->copyright]=$copyReadable;
		}
	}

}

if (isset($thumb)) $thumbnail=$config->imageGetterURL.'?key='.$thumb.'&width=250&height=250&minimum=1';

if (!isset($files)){
	$files=array();
	echo '<h1>'.translate('An error has occured!').'</h1>';
	echo '<p>'.translate('This event does not exist or you do not have access rights.').'</p>';
}

$catOnPage=array();
$perPage=$config->perPage;
$i=1;$counter=0;

foreach($files as $category=>$elements){
	$thisCount=count($elements);
	if ($counter !==0 && $counter+$thisCount>$perPage){
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

if ($folderGiven && isset($folderReadable)){echo '<h1>'.$pageTitle.' <nobr>('.get_date($folderReadable).')</nobr></h1>';}

$byString='';

switch (count($copyrights)){
	case 0: break;
	case 1: $byString=translate('taken by').' '.array_pop($copyrights); break;
	default:
	    $last=' '.translate('and').' '.array_pop($copyrights);
		$first=implode(', ',$copyrights);
		$byString=translate('taken by').' '.$first.$last;
	break;
}

echo $byString;
$pageDescription.=$byString;

if ($restrictedToAuthor) {
	$temp=explode(' ',$filter);
	foreach ($temp as $k=>$v){
		if (stripos($v,'copyright_')!==false) unset($temp[$k]);
	}
	$newFilter=trim(implode(' ',$temp));
    $link='?folder='.urlencode($folder).'&amp;filter='.urlencode($newFilter);
	echo ' - <a href="'.$link.'">'.translate('also show images taken by other photographers',true).'</a>';
}

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

if ($mayDownload) {
	$functionBar='<a href="?folder='.urlencode($folder).'&amp;filter='.$filter.'&amp;mode=download"><img src="design/download1.png" alt="" />'.translate('download',true).'</a><span class="seperator"></span>'.$navi;
}

if ($codewordGiven && !$autocodeword){
		echo '<p id="warning">'.translate('You are browsing this event using a codeword. Please do not share neither the codeword nor this address with people who have no connection to this event!').'</p>';
}

if ($codewordGiven && $autocodeword){
		echo '<p id="warning">'.translate('You are browsing this event using a direct access address. Please do not share this address with people who have no connection to this event!').'</p>';
}

if (!$user && $codewordPossible && !$autocodeword && !$codewordGiven && $folderGiven) {
	
	echo '<p id="keywordnotice">'.translate('Due to privacy reasons, you only see a selection of photos of this event. You get access to all photos, if you know the codeword.').' <a href="javascript:enterCodeword();">'.translate('Enter the codeword now!').'</a></p>';

}

if ($user && $codeword && !$autocodeword && !$codewordGiven && $folderGiven && $hasPublic) {
	
	echo '<p id="notice">'.translate('Only a small selection of this event is publically available. The full event (except exlicitally private images) can be accessed with the following codeword:').' <b>'.$codeword.'</b></p>';

}

if ($user && $codeword && $autocodeword && !$codewordGiven && $folderGiven && $hasPublic) {
	$url=$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword;
	if ($config->local){$url=str_replace('http://localhost',$config->localReplacement,$url);}
	
	echo '<p id="notice">'.translate('Only a small selection of this event is publically available. The full event (except exlicitally private images) can be accessed under this address:').' <a href="'.$url.'" onclick="return showAddress(this);">'.translate('go to address',true).'</a></p>';

}

if ($user && $codeword && !$codewordGiven && $folderGiven && !$hasPublic) {
	$url=$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword;
	if ($config->local){$url=str_replace('http://localhost',$config->localReplacement,$url);}
	
	$out='<p id="notice">'.translate('This event is not publically visible, but can directly be accessed:').' <a href="'.$url.'" onclick="return showAddress(this);">'.translate('go to address',true).'</a></p>';
	echo $out;

}

$first=true;

$jumpNavi=array();

foreach ($catOnPage as $cat=>$pag){
	
	if ($cat && $pag<$page) {
		$jumpNavi[]='<a href="javascript:setCategory(\''.$pag.'###'.urlencode($cat).'\')"> '.$cat.'</a>';
	}
}

if (count($jumpNavi)){
	echo '<p class="jump">'.translate('more images',true).': ';
	echo implode($jumpNavi,', ');
	echo '</p>';
}

echo '<div id="images">';

foreach ($files as $category=>$entries){
	
	if ($catOnPage[$category]!=$page) continue;
		
	if ($category) echo '<h2 id="'.urlencode($category).'">'.$category.'</h2>';
	
	foreach ($entries as $entry){
		
		if ($first){
			$first=false;
			$functionBar='<span class="seperator"></span>'.$functionBar;
			$url='index.php?mode=slideshow&folder='.urlencode($folder).'&filter='.$filter;
			$functionBar='<a href="'.$url.'"><img src="design/galleries1.png" alt="" />'.translate('slideshow',true).'</a>'.$functionBar;
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

$jumpNavi=array();

foreach ($catOnPage as $cat=>$pag){
	
	if ($cat && $pag>$page) {
		$jumpNavi[]='<a href="javascript:setCategory(\''.$pag.'###'.urlencode($cat).'\')"> '.$cat.'</a>';
	}
}

if (count($jumpNavi)){
	echo '<p class="jump">'.translate('more images',true).': ';
	echo implode($jumpNavi,', ');
	echo '</p>';
}

if ($hasPublic){
		$functionBar='<span class="seperator"></span>'.$functionBar;
		$url='javascript:shareOnFacebook();';
		$functionBar='<a href="'.$url.'"><img src="design/share1.png" alt="" />'.translate('share',true).'</a>'.$functionBar;
}

echo '<script>

		function shareOnFacebook(){
			
			var text="'.translate("attention",true).': ";
			text+="'.translate("Please respect author\'s rights and the rights to the personal image when sharing photos on Facebook! Do you still want to share the image on facebook?").'";
			
			if (confirm(text)){
				var reference=location.href;
				
				'.($config->local?('reference=reference.replace("http://localhost","'.$config->localReplacement.'");'):'').'
				
				var FBURL="http://www.facebook.com/sharer/sharer.php?u="+escape(reference);
				var myWindow = window.open(FBURL, "Facebook", "width=780,height=200,toolbar=no,menubar=no,resizable=no,scrollbars=no,status=no");
		 		myWindow.focus();
			}
		}

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

function showAddress(){
	
	return confirm("'.translate('This event is not publically visible. Please do not share neither the codeword nor this address with people who have no connection to this event!').'");
	
}


onScroll();

</script>';

?>