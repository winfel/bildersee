<style>
 header {display:none}
 #breadcrumb {display:none}
 #visu {padding:5px 0 0 0}
</style>
<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');

//get the context from session. This is the last event which was visited

@$contextPage=$_SESSION['last_page'];
@$contextFolder=$_SESSION['last_folder'];
@$contextFilter=$_SESSION['last_filter'];

$contextOK=false;

if (isset($_GET['target'])){
	$target=$_GET['target'];
	$_SESSION['last_target']=$target;
}

//determine the state of the image (public? user has rights?)
$state='non-existant';
$contextQuery="($userQuery";
if ($contextFolder) $contextQuery.=" OR replace(replace(replace(replace(lower(folder),' ',''),'_',''),'.',''),',','') LIKE '$contextFolder'";
$contextQuery.=')';
$contextQuery.=' '.getFilterSQL($contextFilter);
$filterTemp=substr(getFilterSQL($contextFilter),5);
if (!$filterTemp) $filterTemp='0';

$search=mysql_query("SELECT filename,copyright,folder,tags,($userQuery) as hasRights,($filterTemp) as inContext FROM files WHERE md5(`key`)='$image'");

if ($search=mysql_fetch_object($search)){
	//determine rights
	$state='no-rights';
	if ($search->hasRights) $state='has-rights';
	if ($search->inContext) {
		$state='has-rights';
		$contextOK=true;
	}
	if (stripos($search->tags,'public')!==false) $state='public';
	
	//general image information
	$pageTitle=basename($search->filename);
	$filename=$search->filename;
	$folderReadable=pretty(trim(substr($search->folder,strpos($search->folder,' '))));
	$pageDescription=translate('an image in',true).' &quot;'.$folderReadable.'&quot;';;
	$pageDescription.=' '.translate('taken by').' '.ucwords_new(str_replace('_',' ',$search->copyright));
	$tags=$search->tags;
	$geo=false;
	if (stripos($tags,'geo_')!==false) {
		$geo=explode('geo_',$tags);$geo=$geo[1];$geo=explode(' ',$geo);$geo=$geo[0];
	}
	
}

//create context information
if ($contextOK){
	$page=$contextPage;
	$folder=$contextFolder;
	$filter=$contextFilter;
	
} else {
	$page=1;
	$folder=str_replace("'","\\'",$search->folder);
	$folder=str_replace('.','',str_replace(',','',str_replace('_','',str_replace(' ','',strtolower($folder)))));
	$filter='';
	$contextQuery="($userQuery OR ".$filterTemp.')';
	if ($folder) $contextQuery.=" AND replace(replace(replace(replace(lower(folder),' ',''),'_',''),'.',''),',','') LIKE '$folder'";	
}
	
$folderGiven=$folder!='%' && $folder!='%%';
$tagGiven=stripos($filter,'tag_')!==false;
$codewordGiven=stripos($filter,'codeword_')!==false;	

//determine previous and next image
$prev=false;$next=false;


if ($state!='no-rights'){
	
	$reverse=($folder=='%')?'DESC':'';
	
	$search=mysql_query("SELECT md5(`key`) as `key` FROM files WHERE $contextQuery ORDER BY sortstring $reverse");
	
	$thisimage=false;$temp=false;

	while ((!$thisimage || !$next) && $element=mysql_fetch_object($search)){
		
		if ($thisimage) $next=$element->key;
		if ($element->key==$image) {
			$thisimage=$element;
			if ($temp) $prev=$temp->key;
		}
		$temp=$element;
	}
	
}

	
if (!$folderGiven && $tagGiven) {
	$activePart='tags';
	addToBreadcrumb('?mode=tags',translate('tags',true));
} else {
	addToBreadcrumb('?',translate('events',true));
}

if ($folderGiven){
	addToBreadcrumb('?folder='.urlencode($folder),$folderReadable);
} 

if ($tagGiven) {
	$temp=$filter;
	$temp=str_replace(' ',', ',$temp);
	$temp=str_replace('tag_','',$temp);
	$temp=str_replace('notag_',translate('not').' ',$temp);
	$temp=str_replace('_',' ',$temp);
	$temp=ucwords_new($temp);
	addToBreadcrumb('?folder='.urlencode($folder).'&filter='.urlencode($filter),$temp);
}

$functionBar='';

if ($state=='has-rights' || $state=='public'){
	

	$url='index.php?folder='.urlencode($folder).'&filter='.$filter.'&page='.$page.'#scroll'.$image;
	$functionBar.='<a href="'.$url.'"><img src="design/overview1.png" alt="" />'.translate('overview',true).'</a>';
	
	if ($user){
		$url='findimage.php?key='.$image;
		$functionBar.= '<a href="'.$url.'"><img src="design/context1.png" alt="" />'.translate('context',true).'</a>';
	}
	
	$functionBar.='<span class="seperator"></span>';
}

	$functionBar.='<a href="getimage.php?key='.$image.'&download=1" target="_blank"><img src="design/download1.png" alt="" />'.translate('download',true).'</a>';



if ($state=='non-existant') {
	echo '<h1>'.translate('An error has occured!').'</h1>';
	echo '<p>'.translate('An image with this address could not be found. Please check if you have typed in or copied the address correctly.').'</p>';
} else {

    if (stripos($filename,'.youtube')){
		$id=file_get_contents($filename);
		$mainurl='http://www.youtube.com/embed/'.$id;
		echo '<div id="imagediv"><iframe id="theimage" width="1000" height="1000" src="http://www.youtube.com/embed/'.$id.'" frameborder="0" allowfullscreen></iframe></div>';
	} else {
		
		if (stripos($filename,'.jpg')===false && stripos($filename,'.jpeg')===false && stripos($filename,'.png')===false){
			$url=$filename;
			$url=str_replace($config->contentPath,$config->contentURL,$url);
			$mainurl='';
			echo '
			
<video controls="controls"  autoplay="autoplay" poster="'.str_replace('.m4v','.preview.jpg',$url).'" width="640" height="480" title="2013-01-01 Neujahr 2013">
<source src="'.$url.'" type="video/mp4" />
<source src="'.str_replace('.m4v','.webm',$url).'" type="video/webm" />
</video>			
			
			
			';
				   
		} else {
		
			//display of image
			
			$mainurl=$config->imageGetterURL.'?key='.$image;
			$thumbnail=$config->imageGetterURL.'?key='.$image.'&size=preview';
			
			echo '<div id="imagediv"><img src="" id="theimage" /><noscript><img src="'.$mainurl.'" id="theimage" style="opacity:1;width:100%" /></noscript></div>';
			
			if (isset($_SESSION['last_target']) && $_SESSION['last_target']){ //target
				@$target=$_SESSION['last_target'];
				$message=translate('images are shown on presenter',true).' '.$target;
				$message.= ' - <a href="?image='.$image.'&amp;target=">'.translate('switch back to local image display',true).'</a>';
				
				echo '<br />'.$message;
				
				$targetPath=$config->tempPath.'/'.$target;
				file_put_contents ($targetPath,$image);
			} 
			
			//Display image tag data
			
			if ($user){
				$imagedata=mysql_query("SELECT * FROM files WHERE md5(`key`) ='$image'");
				
				if ($imagedata=mysql_fetch_object($imagedata)){
					$theseTags='';
					$readable=getReadableTags($imagedata->tags,$imagedata->sortstring);
					
					$imagedata=mysql_query("SELECT * FROM filetags WHERE md5(`image`) ='$image'");
					if ($imagedata=mysql_fetch_object($imagedata)){
						$theseTags=$imagedata->tags;
					}
					echo '
					   <div id="imagetags" onclick="changeState(\''.$image.'\',true)">
					   <span>'.$readable.'</span>
					   <textarea onblur="changeState(\''.$image.'\',false)" onkeyup="handleEnter(event,\''.$image.'\');" >'.$theseTags.' </textarea><textarea>'.$theseTags.' </textarea>
					   </div>
					 ';

					
				}
			}
			
			//$targetPath=$config->tempPath.'/spy';
			//file_put_contents ($targetPath,$image);
			/**/
			
			
		}
		
	}
	
		
	$element=array();$element['link']='';$element['text']=basename($filename);$breadcrumb[]=$element;

	if ($prev || $next) $functionBar.='<span class="seperator"></span>';
	
	if ($prev) {
		$url='?image='.urlencode($prev);
		$functionBar.='<a href="'.$url.'" id="prevlink"><img src="design/back1.png" alt="back" /></a>';
	}
	
	echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
	
	if ($next) {
		$url='?image='.urlencode($next);
		$functionBar.='<a href="'.$url.'" id="nextlink"><img src="design/forward1.png" alt="forward" /></a>';
	}
	
	echo '
	<script>
	
		  function changeState(key,state){
				if ('.($config->local?'true':'false').') return;  //changeStated switched off, if in local mode
				
				var tagArea=document.getElementById("imagetags");
				
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
				
			function handleEnter(e,key){
				var characterCode;
				if(e && e.which){e = e;characterCode = e.which;} 
				else {e = event;characterCode = e.keyCode;}
				
				if(characterCode == 13){changeState(key,false);return false;} 
				else {return true;}
			}
			
			function updateValue(key,value){
				var tagArea=document.getElementById("imagetags");
				tagArea.getElementsByTagName("span")[0].innerHTML=value;
			}
	
	
		  function changeDescription(key,oldValue){
		  	  var newValue=prompt("'.translate('Enter a description for this image:').'",oldValue);
		  	  if (newValue==null) return;
		  	  server_query("updatedescription.php?key="+key+"&description="+escape(newValue),function(value){
					  		if (value) {
					  			alert(value);
					  		} else {
					  			document.getElementById("description").innerHTML=newValue;
					  			document.getElementById("description").className="set";
					  		}
				});
		  }
	
	      var myWidth = 0, myHeight = 0;
		  if( typeof( window.innerWidth ) == "number" ) {
		    //Non-IE
		    myWidth = window.innerWidth;
		    myHeight = window.innerHeight;
		  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		    //IE 6+ in "standards compliant mode"
		    myWidth = document.documentElement.clientWidth;
		    myHeight = document.documentElement.clientHeight;
		  } 
		
		var image=document.getElementById("theimage");
		var imagediv=document.getElementById("imagediv");
		
        var mHeight=myHeight-40;
        imagediv.style.height=mHeight+"px";
	        
		if (image){
			
			image.src="";
			
			image.onload=function(){
				var mHeight=myHeight-40;
				var isHeight=(image.offsetHeight);
				image.isHeight=isHeight;
				if (isHeight>mHeight){
					image.style.maxHeight=mHeight+"px";
					imagediv.style.height=mHeight+"px";
					imagediv.style.background="transparent";
					
				}
				image.style.maxWidth="100%";
				image.style.opacity=1;
				
			}
			
			image.src="'.$mainurl.'";
			
			var body=document.getElementsByTagName("body")[0];
			
			image.onclick=function(){
				if (!image.isHeight) return image.onload();
				image.style.maxWidth="1000000px";
				image.style.maxHeight=image.isHeight+"px";
				imagediv.style.height=image.isHeight+"px";
				image.isHeight=undefined;
			}
		}
		
		function showExif(e){
			hideExifs();
			var left=(e.clientX-200);
			if (left<0) left=0;
			document.getElementById("exifdata").style.display="block";
			document.getElementById("exifdata").style.left=left+"px";
			document.getElementById("downarrow").style.display="block";
			document.getElementById("downarrow").style.left=(e.clientX-27)+"px";
			document.getElementById("visu").onclick=hideExifs;
			e.stopPropagation();
		}
		
		function showLocation(e){
			hideExifs();
			var left=(e.clientX-250);
			if (left<0) left=0;
			document.getElementById("exiflocation").style.display="block";
			document.getElementById("exiflocation").style.left=left+"px";
			document.getElementById("downarrow").style.display="block";
			document.getElementById("downarrow").style.left=(e.clientX-27)+"px";
			document.getElementById("visu").onclick=hideExifs;
			e.stopPropagation();
		}
		
		function hideExifs(){
			document.getElementById("downarrow").style.display="none";
			if (document.getElementById("exifdata")) document.getElementById("exifdata").style.display="none";
			if (document.getElementById("exiflocation")) document.getElementById("exiflocation").style.display="none";
		}
		
		function fullScreen(){
						
			var elem = document.getElementById("imagediv");  
			if (elem.requestFullScreen) {  
			  elem.requestFullScreen();  
			} else if (elem.mozRequestFullScreen) {  
			  elem.mozRequestFullScreen();  
			} else if (elem.webkitRequestFullScreen) {  
			  elem.webkitRequestFullScreen();  
			}
			
			image.onload();
			image.onclick();
			  
		}
		
	
	</script>
	
	';	

//HIER SLIDESHOW
	
	if ($state=='has-rights' || $state=='public'){
	
		$functionBar='<span class="seperator notonsmall"></span>'.$functionBar;
		$url='index.php?mode=slideshow&folder='.urlencode($folder).'&filter='.$filter.'&image='.$image;
		$functionBar='<a href="'.$url.'" class="notonsmall"><img src="design/galleries1.png" alt="" />'.translate('slideshow',true).'</a>'.$functionBar;
		
	}
	
	@$exif=parseExif($filename,$geo);
	
	if ($exif['exif']) {
		$functionBar='<span class="seperator notonsmall"></span>'.$functionBar;
		$functionBar='<a href="#" onclick="showExif(event);return false;" class="notonsmall"><img src="design/metadata1.png" alt="" />'.translate('metadata',true).'</a>'.$functionBar;
		echo '<div id="exifdata">'.$exif['exif'].'</div>';
	}
	if ($exif['location']) {
		$functionBar='<a href="#" onclick="showLocation(event);return false;"><img src="design/location1.png" alt="" />'.translate('location',true).'</a>'.$functionBar;
		echo '<div id="exiflocation">'.$exif['location'].'</div>';
	}
	
	
	  if (isset($_GET['image']) && $_GET['image']){
  	echo '

  	   <script>
  	   	  document.onkeydown = keypressed;
  	   </script>
  	';
  }
  
  
  // Prefetching of previous and next image
  
  $nextURL=$config->imageGetterURL.'?key='.$next;	
  $prevURL=$config->imageGetterURL.'?key='.$prev;	
  
  $legalShort.= '<img src="'.$nextURL.'" width="1" height="1" />';
  $legalShort.= '<img src="'.$prevURL.'" width="1" height="1" />';
 
 }
 
 
function parseExif($filename,$geo){global $translations,$lang,$config;

$output=array();

exec('exiftool -json -c "%.14f" -groupHeadings "'.$filename.'"',$output);

$output=implode($output);

$output=json_decode($output,true);

$data=$output[0];

if (!$data) return;

//var_dump($data);

$allowed=array();
$allowed['FileSize']=true;
$allowed['FocalLength35efl']=true;
$allowed['ShutterSpeed']=true;
$allowed['DateTimeCreated']=true;
$allowed['Aperture']=true;
$allowed['ISO']=true;
$allowed['Flash']=true;
$allowed['ImageSize']=true;
$allowed['Make']=true;
$allowed['Model']=true;
$allowed['Software']=true;
$allowed['MeteringMode']=true;
$allowed['LightSource']=true;
$allowed['ExposureCompensation']=true;
$allowed['ExposureProgram']=true;
$allowed['ScaleFactor35efl']=true;
$allowed['CircleOfConfusion']=true;
$allowed['LightValue']=true; 
$allowed['FOV']=true;
$allowed['HyperfocalDistance']=true;
$allowed['Artist']=true;
$allowed['Copyright']=true;
$allowed['Lens']=true;
$allowed['SerialNumber']=true;
$allowed['SubjectDistance']=true;
$allowed['ExposureMode']=true;
$allowed['InternalSerialNumber']=true;
$allowed['Orientation']=true;
$allowed['Contrast']=true;

$allowed['WhiteBalance']=true;
$allowed['SceneCaptureType']=true;
$allowed['Sharpness']=true;
$allowed['SubjectDistanceRange']=true;
$allowed['Saturation']=true;
$allowed['FujiFlashMode']=true;
$allowed['FlashExposureComp']=true;
$allowed['Macro']=true;
$allowed['FocusMode']=true;
$allowed['SlowSync']=true;
$allowed['PictureMode']=true;
$allowed['AutoBracketing']=true;
$allowed['SequenceNumber']=true;
$allowed['BlurWarning']=true;
$allowed['FocusWarning']=true;
$allowed['ExposureWarning']=true;
$allowed['DynamicRange']=true;
$allowed['FilmMode']=true;
$allowed['DynamicRangeSetting']=true;
$allowed['FacesDetected']=true;


$allowed['MacroMode']=true;
$allowed['SelfTimer']=true;
$allowed['Quality']=true;
$allowed['CanonFlashMode']=true;
$allowed['ContinuousDrive']=true;
$allowed['RecordMode']=true;
$allowed['CanonImageSize']=true;
$allowed['FlashBits']=true;
$allowed['FocusContinuous']=true;
$allowed['AESetting']=true;
$allowed['ImageStabilization']=true;
$allowed['SpotMeteringMode']=true;
$allowed['ManualFlashOutput']=true;
$allowed['FocalType']=true;
$allowed['AutoExposureBracketing']=true;
$allowed['AEBBracketValue']=true;
$allowed['ControlMode']=true;
$allowed['BulbDuration']=true;
$allowed['AutoRotate']=true;
$allowed['NDFilter']=true;
$allowed['DateStampMode']=true;
$allowed['MyColorMode']=true;
$allowed['Categories']=true;
$allowed['DriveMode']=true;
$allowed['ShootingMode']=true;
$allowed['EasyMode']=true;
$allowed['DigitalZoom']=true;
$allowed['CameraISO']=true;
$allowed['FocusRange']=true;
$allowed['AFPoint']=true;
$allowed['CanonExposureMode']=true;
$allowed['GainControl']=true;
$allowed['ObjectDistance']=true;
$allowed['FlashDistance']=true;
$allowed['AFMode']=true;
$allowed['Audio']=true;
$allowed['WhiteBalanceBias']=true;
$allowed['FlashBias']=true;
$allowed['ColorEffect']=true;
$allowed['BurstMode']=true;
$allowed['NoiseReduction']=true;
$allowed['CameraID']=true;
$allowed['ColorTemperature']=true;
$allowed['SlowShutter']=true;
$allowed['OpticalZoomCode']=true;	
$allowed['FlashGuideNumber']=true;
$allowed['MeasuredEV']=true;
$allowed['SelfTimer2']=true;
$allowed['FlashType']=true;
$allowed['Lens35efl']=true;
$allowed['FlashGuideNumber']=true;
$allowed['SuperMacro']=true;
$allowed['FlashType']=true;
$allowed['RedEyeReduction']=true;
$allowed['ShutterCurtainHack']=true;
$allowed['DigitalZoomRatio']=true;
$allowed['ImageDescription']=true;
$allowed['BrightnessValue']=true;

$allowed['BestShotMode']=true;
$allowed['AutoISO']=true;
$allowed['ColorMode']=true;
$allowed['Enhancement']=true;
$allowed['Filter']=true;
$allowed['BracketSequence']=true;

$allowed['DateTimeOriginal']=true;
$allowed['CanonModelID']=true;

$allowed['ColorReproduction']=true;
$allowed['Anti-Blur']=true;
$allowed['LongExposureNoiseReduction']=true;

$allowed['LensInfo']=true;
$allowed['LensModel']=true;
$allowed['DOF']=true;
$allowed['AutoDynamicRange']=true;
$allowed['LensSerialNumber']=true;
$allowed['BWMode']=true;
$allowed['AFAreaMode']=true;
$allowed['ContrastMode']=true;

$allowed['SpecialEffectMode']=true;
$allowed['ReleaseMode']=true;
$allowed['ColorFilter']=true;
$allowed['LightingMode']=true;
$allowed['PortraitRefiner']=true;
$allowed['GPSDateTime']=true;
$allowed['Rotation']=true;
$allowed['AFAssistLamp']=true;
$allowed['OpticalZoomMode']=true;
$allowed['ConversionLens']=true;
$allowed['SceneMode']=true;
$allowed['FlashFired']=true;
$allowed['ProgramISO']=true;
$allowed['WhiteBalanceFineTune']=true;

$allowed['SensorSize']=true;
$allowed['SRResult']=true;
$allowed['ShakeReduction']=true;
$allowed['PictureMode2']=true;
$allowed['ProgramLine']=true;
$allowed['FlashOptions']=true;
$allowed['MeteringMode2']=true;
$allowed['AFPointMode']=true;
$allowed['FocusMode2']=true;
$allowed['DriveMode2']=true;
$allowed['AutoAperture']=true;
$allowed['AFIlluminator']=true;
$allowed['FlashLevel']=true;
$allowed['DynamicRangeOptimizer']=true;

$allowed['AdvancedSceneMode']=true;
$allowed['NumFacePositions']=true;
$allowed['Transform']=true;
$allowed['FlashWarning']=true;  

$readables=array();
$readables['FileName']='Filename';
$readables['FileSize']='File Size';
$readables['ImageSize']='Dimensions';
$readables['DateTimeOriginal']='Creation Date';
$readables['DateTimeCreated']='Creation Date';
$readables['Model']='Camera';
$readables['Lens']='Lens';
$readables['LensModel']='Lens';
$readables['LensID']='Lens';
$readables['FocalLength35efl']='Focal Length';
$readables['Aperture']='Aperture';
$readables['ShutterSpeed']='Shutter Speed';
$readables['ISO']='ISO Film Speed';
$readables['Flash']='Flash';
$readables['SubjectDistance']='Subject Distance';
$readables['ObjectDistance']='Subject Distance';
$readables['ApproximateFocusDistance']='Focus Distance';
$readables['Artist']='Camera Owner';
$readables['Copyright']='Copyright Notice';
$readables['Creator']='Creator';
$readables['Title']='Title';
$readables['Rights']='Rights';


foreach ($readables as $key=>$value){
	$allowed[$key]=true;
}

$skipped=array();
$metadataRaw=array();

foreach ($data as $category=>$entries){
	if ($category=="IPTC") continue;
	if (!is_array($entries)) continue;	
	foreach ($entries as $key=>$value){
		
		if (is_array($value)) continue;
		
		if (isset($allowed[$key])&&$key) {
		
			if ($value!='0' && $value!='Auto' && $value!='None' && $value!='Good' && $value!='Standard' && $value!='' && $value!='Off' && $value!='Normal' && $value!='Unknown' && $value!='F0/Standard' && $value!='n/a' && $value!='(none)' && $value!='No'){
				$metadataRaw[$key]=$value;	
			}
		} else {
			$skipped[$key]=$value;
		}
		
	}
}
/*
echo '<pre>';
var_dump($skipped);
echo '</pre>';
*/

$output='';

$output.='<table>';

if (isset($metadataRaw['Make']) && isset($metadataRaw['Model'])){
	$make=$metadataRaw['Make'];
	$model=$metadataRaw['Model'];
	unset($metadataRaw['Make']);
	if (stripos($model,$make)===false) $metadataRaw['Model']="$make $model";
}

$metadata=array();

foreach ($readables as $key=>$readable){
	if (isset($metadataRaw[$key])){
		$value=$metadataRaw[$key];
		unset($metadataRaw[$key]);
		$metadata[$readable]=$value;
	}
}

foreach ($metadata as $key=>$value){
	$output.="<tr><th><nobr>$key</nobr></th><td>: $value</td>";
}

$output.="<tr><th style=\"vertical-align:top\">More Info</th><td style=\"font-size:70%\">";

foreach ($metadataRaw as $key=>$value){
	$output.="<nobr>$key: $value;</nobr> ";
}

$output.='</td></table>';

/*
foreach ($skipped as $key=>$value){
	echo'<b title="'.$value.'" style="display:inline;font-size:70%;font-weight:normal">'.$key.':'.$value.' , </b><br/>';
}
//*/

$result['exif']=$output;

$coordinates=false;
$output='';


if (isset($data['Composite']['GPSPosition'])) 
	$coordinates=$data['Composite']['GPSPosition'];
else
	$coordinates=$geo;

if ($coordinates){
  $link='http://maps.google.com/maps?q='.$coordinates.'+(Standort)&output=embed&hl=de&z=20&t=h';
  if (!isIPhone(true)) $output.='<iframe src="'.$link.'" width="500" height="500" style="border:none"></iframe><br /><a href="'.$link.'" target="_blank">Bigger view</a>';
  $link2='http://maps.bing.de/maps/?v=2&lvl=2&style=o&where1='.$coordinates;



	if ($coordinates && isIphone()){
		$output.='<br /><a href="'.$link.'" target="_blank" class="iphonebig">Show in Google Maps</a><br />';
	}

}

$result['location']=$output;

return $result;
}
		
?>