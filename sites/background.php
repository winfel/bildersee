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



$result=mysql_query("SELECT filename FROM files WHERE (concat(' ',tags,' ') like '% top %') ORDER BY rand() LIMIT 1");
if (!$resultSet=mysql_fetch_object($result)) die('');
$getFile=($resultSet->filename);
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
 ImageJPEG($temp,false,60);
 imagedestroy($temp);
 imagedestroy($alt);
?>
