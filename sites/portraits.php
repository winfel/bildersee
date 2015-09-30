<?php

 /*
 
    SeeSite - A tool for managing a local photo collection globally
    
    PHP script displaying a small thumbnail image of a given file
 
 
 */

 ini_set ('error_reporting', E_ALL);
 ini_set ('display_errors', true);
 
 include('../server/environment.php');
 
header("Content-type: image/jpeg");
//$expires=date("D, d M Y H:i:s",time() + (24 * 60 * 60)).' GMT';
//header("Expires: $expires");  //tomorrow

 $filter=isset($_GET['filter'])?"AND (concat(' ',tags,' ') like '% ".$_GET['filter']." %')":'';



$result=mysql_query("SELECT filename,tags FROM files WHERE (concat(' ',tags,' ') like '% portrait %') $filter ORDER BY rand() LIMIT 1");
if (!$resultSet=mysql_fetch_object($result)) die('');
$getFile=($resultSet->filename);
$tags=$resultSet->tags;
$text='';
$age='';

$tags=explode(' ',$tags);

foreach ($tags as $tag){
	if (substr($tag,0,4)=='age_') {
		$age=' ('.substr($tag,4).')';
		continue;
	}
	if (strpos($tag,'_')) continue;
	if ($tag=='top') continue;
	if ($tag=='arsch') continue;
	if ($tag=='portrait') continue;
	if ($tag=='thumb') continue;
	if ($tag=='privat') continue;
	if ($tag=='archiv') continue;
	$inner=mysql_query("SELECT readable FROM tags_readable WHERE `tag`='$tag'");
	if ($inner=mysql_fetch_object($inner)) $tag=$inner->readable;
	$text.=' '.$tag;
}

$text.=$age;


$info=getimagesize($getFile);
 
 $getWidth=$_GET['width'];
 $getHeight=200000;
 
 $origwidth=$info[0];$origheight=$info[1];
 $imageRelation= ($origwidth/$origheight);
 $wantedRelation= ($getWidth/$getHeight);
 
 
  $newwidth=$getWidth;
  $newheight=$newwidth/$imageRelation;


$lokalurl=$getFile	;
	
 ini_set('memory_limit', '1024M');
 set_time_limit(60);
 ini_set('gd.jpeg_ignore_warning', 1);               
 
 if (!$alt=@ImageCreateFromJPEG($lokalurl))  $alt=@ImageCreateFromPNG($lokalurl);
 
 
 if (!$alt) die('');
 
 //Shrinking of the image
 $temp=ImageCreateTrueColor($newwidth,$newheight); 
 ImageCopyResampled($temp,$alt,0,0,0,0,$newwidth,$newheight,$origwidth,$origheight);
  	$color = imagecolorresolve($temp, 0, 0, 0);
 	imagestring($temp,7,5,6,$text,$color);
 	imagestring($temp,7,5,4,$text,$color);
 	imagestring($temp,7,4,5,$text,$color);
 	imagestring($temp,7,6,5,$text,$color);
 	$color = imagecolorresolve($temp, 255, 255, 255);
 	imagestring($temp,7,5,5,$text,$color);
 ImageJPEG($temp,false,60);
 imagedestroy($temp);
 imagedestroy($alt);
?>
