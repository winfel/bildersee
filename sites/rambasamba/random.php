<?php

 /*
 
    SeeSite - A tool for managing a local photo collection globally
    
    PHP script displaying a small thumbnail image of a given file
 
 
 */
 
 if (!isset($_GET)) die ('');
 
 //ini_set ('error_reporting', E_ALL);
 //ini_set ('display_errors', true);
 
 include('../../server/environment.php');
 
header("Content-type: image/jpeg");
//$expires=date("D, d M Y H:i:s",time() + (60 * 60)).' GMT';
//header("Expires: $expires"); 



 $result=mysql_query("SELECT * FROM files WHERE (filename LIKE '%rambasamba%' AND NOT filename LIKE '%privat%' AND NOT tags LIKE '%norandom%' AND NOT tags LIKE '%archiv%') ORDER BY rand() LIMIT 1");

$resultSet=mysql_fetch_object($result);
$lokalurl=($resultSet->filename);
	
 ini_set('memory_limit', '1024M');
 set_time_limit(60);
 ini_set('gd.jpeg_ignore_warning', 1);               
 
 if (!$alt=@ImageCreateFromJPEG($lokalurl)) {
 	$config=new Config();
 	$lokalurl=$config->serverPath.'/images/wrongformat.jpg';
 }	
 $size=GetImageSize($lokalurl);
 $origwidth=$size[0];$origheight=$size[1];
 
 $relationWidth=$origwidth/160;
 $relationHeight=$origheight/160;

 {
 	$newheight=160;
 	$newwidth=160;
 	if ($relationHeight>$relationWidth){
     $newheight=floor($origheight/$relationWidth);
   } else {
 	  $newwidth=floor($origwidth/$relationHeight);
   }
 }
 
 //Shrinking of the image
 {
 	$temp=ImageCreateTrueColor(160,160);
 	ImageCopyResampled($temp,$alt,-($newwidth-160)/2,-($newheight-160)/4,0,0,$newwidth,$newheight,$origwidth,$origheight);
 }
 
 ImageJPEG($temp, NULL, 75);
 //ImageJPEG($temp,$cachePath);
 imagedestroy($temp);
 imagedestroy($alt);


?>
