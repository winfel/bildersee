<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

//Determining target attribute for remote screen
if (isset($_GET['target'])){
	$target=$_GET['target'];
	$_SESSION['last_target']=$target;
}
@$target=$_SESSION['last_target'];

//this is used for returning to the site;

$_SESSION['last_page']=$page;
$_SESSION['last_folder']=$folder;
$_SESSION['last_filter']=$filter;

//Do the actual search
$folder=str_replace('.','',str_replace(',','',str_replace('_','',str_replace(' ','',strtolower($folder)))));
$reverse=($folder=='%');
$search=getImages($folder,$reverse);

//## SET INITAL VALUES ##

$pageTitle=translate('search result',true);  // page title, likely to be overridden by event title
$pageDescription=translate('an online photo gallery',true); // page description, likely to be overridden by event description

$page=isset($_GET['page'])?$_GET['page']:1; //page number. 1 if not specified

// for codeword handling
$codewordPossible=false; //is set to true later, if the result contains images which have a codeword
$codeword=false; //containts the codeword provided by url if present
$autocodeword=false; //true if the provided codeword is an automatically created one
$codewordGiven=stripos($filter,'codeword_')!==false; //true if a codeword has been provided

$hasPublic=false; // true if the result contains public images

$folderGiven=$folder!='%' && $folder!='%%'; //true if the search result is an album
$tagGiven=stripos($filter,'tag_')!==false; //true if there has been any tag restriction

$restrictedToAuthor=stripos($filter,'copyright_')!==false; //true if the result has been resticted to a single photographer

$mayDownload=!$config->local && $folderGiven && ($user||$codewordGiven); //set to true if the album may be downloaded

$count=0; // image counter
$hasThumb=false; //true if the album contains a thumbnail

$copyrights=array(); //list of photographers
$peopleList=array(); //list of all people
$imageTags=array(); //list of image tags
$peopleTags=array(); //list of image people
$selectionList=array(); //list of images in a selection

//#### RESULT PROCESSING ####

//passing through the search result image by image and thereby gathering all data necessary for
//result output including navigation etc.
   
while ($line=array_shift($search)){
		
	$count++;
	
	//if the result is an album/folder, determin the folder title
	if ($count==1 && $folderGiven) {
		$pageTitle=pretty(trim(substr($line->folderReadable,strpos($line->folderReadable,' '))));
		$folderReadable=$line->folderReadable;
	}
	
	//on first image create breadcrumb navigation
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
	
	//create list of subcategories
	if ($folderGiven){
		$category=$line->subfolder;
	} else {
		$category=pretty($line->folderReadable);
	    if ($line->subfolder) $category.=' - '.$line->subfolder;
	}
	
	//add the image data to the category
	$files[$category][]=$line;
	
	//go through the tags of every image and add them to tag and people data for navigation
	foreach (explode(' ',$line->tags) as $ele){
		if ($ele==' ') continue;
		if ($ele=='') continue;
		if ($ele=='public') continue;
		if ($ele=='privat') continue;
		if ($ele=='auswahl') continue;
		if ($ele=='thumb') continue;
		if ($ele=='archiv') continue;
		if (stripos($ele,'copyright_')!==false) continue;
		if (stripos($ele,'codeword_')!==false) continue;
		
		if (!$peopleList){ //load full people list used to determine if a tag is a person
			$query="SELECT * FROM people";
			$psearch=mysql_query($query);
			$peopleList=array();
			
			while($pline=mysql_fetch_object($psearch)){
				$peopleList[$pline->tag]=true;
			}
		}
		
		if ($ele=='selection'){
			$selectionList[]=$line;
		}
		
		if (isset($peopleList[$ele])){
			 if ($user) @$peopleTags[$ele]++;
			 continue;
		} else {
			@$imageTags[$ele]++;
		}
		
	}	  

	//update information if the result contains public images, images with password or images with 	
	$codewordPossible=$codewordPossible || (stripos($line->tags,'codeword_') !==false);
	$hasPublic=$hasPublic || (stripos($line->tags,'public') !==false);
	
	//determine, if the image is the the thumbnail
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


// ### RESULT DISPLAY ####


if (isset($thumb)) $thumbnail=$config->imageGetterURL.'?key='.$thumb.'&size=thumb';

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
			
			var data=imageData[image.title];
			
			var imgurl="previmage.php?key="+image.title+"&amp;size=";
			
			image.src=imgurl;
			image.srcset=data.prevImage1x+" 1x, "+data.prevImage15x+" 1.5x, "+data.prevImage2x+" 2x";
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

function onResize(){
	
	var windowWidth=document.documentElement.clientWidth||window.getWidth();
	
	//get lines
	
	var lines=[];
	var line=-1;
	var oldCategory="UNDEFINED";
	var filled=0;
	for (var i in imageDataArray){
		var image=imageDataArray[i];
		
		var consumedWidth=image.margin+image.minWidth+image.margin;
		
		if (image.category!=oldCategory || filled+consumedWidth>windowWidth) {
			line++;
			lines[line]=[];
			filled=0;
		}
		
		filled+=consumedWidth;
		
		lines[line].push(image);
		
		var oldCategory=image.category;
	}

	for (var i in lines){
		var line=lines[i];
		var number=line.length;

		var border=3.5;
		
		var availWidth=windowWidth-2*border*number;
		
		var totalWidth=0;
		
		for (var j in line){
			totalWidth+=line[j].minWidth;
		}
		
		var factor=availWidth/totalWidth;
		
		for (var j in line){
			var image=line[j];
			var width=Math.round(image.minWidth*factor);
			var height=Math.round(image.minHeight*factor);
			
			var element=document.getElementById("img"+image.id);
			
			element.width=Math.min(image.maxWidth,Math.round(width));
			element.height=Math.min(image.maxHeight,Math.round(height));
		}
		
	}
	
	return
		
}


</script>';


if ($folderGiven && isset($folderReadable)){echo '<h1>'.ucfirst(translateWords($pageTitle)).' <nobr>('.get_date($folderReadable).')</nobr></h1>';}

$byString='';

switch (count($copyrights)){
	case 0: break;
	
	case 1: 
	
	$element=array_keys($copyrights);
	$element=$element[0];
	$byString=translate('taken by').' '.ucwords_new(str_replace('_',' ',$element)); break;
	default:
	    $last=' '.translate('and').' '.array_pop($copyrights);
		$first=implode(', ',$copyrights);
		$byString=translate('taken by').' '.$first.$last;
	break;
}

echo $byString;
$pageDescription.=strip_tags($byString);


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
	$navi.='<option value="">'.translate('Jump directly to a topic').'  </option>';
	
	foreach(array_keys($files) as $category){
		$navi.='<option value="'.$catOnPage[$category].'###'.urlencode($category).'">'.ucfirst(translateWords($category)).'  </option>';
	}
	
	$navi.='</select>&nbsp;&nbsp;&nbsp;';
	

}

ksort($imageTags);
ksort($peopleTags);

foreach ($imageTags as $k=>$v){
	if ($v==$count) unset($imageTags[$k]);
}

if($imageTags || $tagGiven){
	$navi.='<select onchange="setTag(this.value);">';
	$navi.='<option value="">'.translate('Filter by tag').'  </option>';
	if ($folderGiven) $navi.='<option value="">'.translate('remove filter',true).'  </option>';
	foreach ($imageTags as $k=>$v){
		$readable=ucwords_new(str_replace('_',' ',$k)).' ('.$v.')';
		$navi.='<option value="'.$k.'">'.$readable.'  </option>';
	}
	$navi.='</select>&nbsp;&nbsp;&nbsp;';
} 

if($peopleTags || $tagGiven){
	$navi.='<select onchange="setTag(this.value);">';
	$navi.='<option value="">'.translate('Filter by person').'  </option>';
	foreach ($peopleTags as $k=>$v){
		$readable=ucwords_new(str_replace('_',' ',$k)).' ('.$v.')';
		$navi.='<option value="'.$k.'">'.$readable.'  </option>';
	}
	$navi.='</select>&nbsp;&nbsp;&nbsp;';
} 


$functionBar=$navi;$functionBar2=$navi;

if ($navi) echo '<br><br><p>'.$navi.'</p>';

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
	
	echo '<div id="keywordnotice">'.translate('Due to privacy reasons, you only see a selection of photos of this event. You get access to all photos, if you know the codeword.').'<br><br>'.translate('codeword',true).': <input type="text" id="codeword" onkeyup="handleEnter(event)"><input type="button" value="'.translate('send',true).'" onclick="codewordEntered();"></div>';

}

if ($user && $codeword && !$autocodeword && !$codewordGiven && $folderGiven && $hasPublic) {
	
	$url=$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword;
	if ($config->local){$url=str_replace('http://localhost',$config->localReplacement,$url);}
	
	echo '<p id="notice">'.translate('Only a small selection of this event is publically available. The full event (except exlicitally private images) can be accessed with the following codeword:').' <b>'.$codeword.'</b> '.translate('or directly using this address:').'<br><br><a href="'.$url.'" onclick="return showAddress(this);">'.translate('go to address',true).'</a></p>';

}

if ($user && $codeword && $autocodeword && !$codewordGiven && $folderGiven && $hasPublic) {
	$url=$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword;
	if ($config->local){$url=str_replace('http://localhost',$config->localReplacement,$url);}
	
	echo '<p id="notice">'.translate('Only a small selection of this event is publically available. The full event (except exlicitally private images) can be accessed under this address:').'<br><br><a href="'.$url.'" onclick="return showAddress(this);">'.translate('go to address',true).'</a></p>';

}

if ($user && $codeword && !$codewordGiven && $folderGiven && !$hasPublic) {
	$url=$config->viewURL.'/?folder='.urlencode($folder).'&filter=codeword_'.$codeword;
	if ($config->local){$url=str_replace('http://localhost',$config->localReplacement,$url);}
	
	$out='<p id="notice">'.translate('This event is not publically visible, but can directly be accessed:').'<br><br><a href="'.$url.'" onclick="return showAddress(this);">'.translate('go to address',true).'</a></p>';
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

echo '

<script>
  var imageDataArray=[];
  var imageData={};
</script>

<div id="images">';

function cacheURL($key,$size){global $config;
   
	 if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL']=='no-cache') {
	 	return false;
	 }     
	
	$filename='/'.$key.'.'.$size.'.jpg';
	
	if (file_exists($config->cachePath.$filename)){return $config->cacheURL.$filename;}
	
	return false;
}

foreach ($files as $category=>$entries){
	
	if ($catOnPage[$category]!=$page) continue;
		
	if ($category && count($catOnPage)>1) echo '<h2 id="'.urlencode($category).'">'.ucfirst(translateWords($category)).'</h2>';
	
	foreach ($entries as $entry){
		
		if ($first){
			$first=false;
			$functionBar='<span class="seperator"></span>'.$functionBar;
			$url='index.php?mode=slideshow&folder='.urlencode($folder).'&filter='.$filter;
			$functionBar='<a href="'.$url.'"><img src="design/galleries1.png" alt="" />'.translate('slideshow',true).'</a>'.$functionBar;
			if ($user){
				$functionBar='<span class="seperator"></span>'.$functionBar;
				$url='index.php?mode=tagging&folder='.urlencode($folder).'&filter='.$filter;
				$functionBar='<a href="'.$url.'"><img src="design/tags1.png" alt="" />'.translate('tagging view',true).'</a>'.$functionBar;
			}
		}
		
		$url='?image='.urlencode($entry->key);
				
		$readable=getReadableTags($entry->tags,$entry->sortstring);
		
		$mode='neutral';
		if ($user && stripos($readable,'public')!==false) $mode='public';
		if ($user && stripos($readable,'privat')!==false) $mode='private';

		$frameclass=($user)?'imageframe':'imageframe nouser_imageframe';
		

		$size=@GetImageSize($entry->filename);
	 	$origwidth=$size[0];$origheight=$size[1];
	 	if (!$origheight) continue; //Do not care about images that cannot be loaded
	 	$setHeight=250;
	 	$setWidth=round($setHeight/$origheight*$origwidth);
	 	
	 	$prevImage1x="previmage.php?key=".$entry->key."&amp;size=1x";
	 	$prevImage15x="previmage.php?key=".$entry->key."&amp;size=1.5x";
	 	$prevImage2x="previmage.php?key=".$entry->key."&amp;size=2x";
	 	
	 	if (cacheURL($entry->key,'1x')){$prevImage1x=cacheURL($entry->key,'1x');}
	 	if (cacheURL($entry->key,'1.5x')){$prevImage15x=cacheURL($entry->key,'1.5x');}
	 	if (cacheURL($entry->key,'2x')){$prevImage2x=cacheURL($entry->key,'2x');}
	 	
	 	echo '<script>
	 		var thisImageData={};
	 		thisImageData.category="'.$category.'";
	 		thisImageData.id="'.$entry->key.'";
	 		thisImageData.maxWidth='.($setWidth*2).';
	 		thisImageData.maxHeight='.($setHeight*2).';
	 		thisImageData.minWidth='.$setWidth.';
	 		thisImageData.minHeight='.$setHeight.';
	 		thisImageData.margin=3;
	 		thisImageData.prevImage1x="'.$prevImage1x.'";
	 		thisImageData.prevImage15x="'.$prevImage15x.'";
	 		thisImageData.prevImage2x="'.$prevImage2x.'";
	 		imageDataArray.push(thisImageData);
	 		imageData["'.$entry->key.'"]=thisImageData;
	 	</script>';
		
		echo '<a href="'.$url.'"><img alt="" src="design/blind.gif" title="'.$entry->key.'" id="img'.$entry->key.'" width="'.$setWidth.'" height="'.$setHeight.'" style="margin:3px;margin-bottom:0;background:#444" class="eventimage"></a>';
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


echo '<script>

function handleEnter(e){
	var characterCode;
	if(e && e.which){e = e;characterCode = e.which;} 
	else {e = event;characterCode = e.keyCode;}
	
	if(characterCode == 13){codewordEntered();return false;} 
	else {return true;}
}

function codewordEntered(){
	
	var result=document.getElementById("codeword");
	result=result.value;
	
	if (!result) result=""; else result="codeword_"+result.toLowerCase();
	
	var folder="'.urlencode($folder).'";
	
	location.href="?folder="+folder+"&filter="+result;
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


function setTag(data){
	if (!data) {
		var filter="";
	} else {
		var filter="'.(($filter)?$filter."%20":"").'tag_"+data;
	}
	
	var url="?folder='.$folder.'&filter="+filter;
	window.location.href=url;
}

function showAddress(){
	
	return confirm("'.translate('This event is not publically visible. Please do not share neither the codeword nor this address with people who have no connection to this event!').'");
	
}

onResize();
onScroll();

//loadImages();

</script>';

?>