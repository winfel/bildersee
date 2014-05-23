<?php

 //ini_set ('error_reporting', E_ALL);
 //ini_set ('display_errors', true);

	include_once ('environment.php');
	
	$config=new Config(); 
	
	if (isset($_GET['key'])){$input=($_GET['key']);} else {die ('error');}
	
	$userIsAdmin=false;
	if(isset($_SESSION['userIsAdmin'])) $userIsAdmin=$_SESSION['userIsAdmin'];
	
	$result=mysql_query("SELECT filename,copyright,tags FROM files WHERE md5(`key`)='$input' OR `key`='$input'");
	$resultSet=mysql_fetch_object($result);
	@$getFile=($resultSet->filename);
	@$getTags=($resultSet->tags);
	@$copyright=($resultSet->copyright);
	
	$width=0; $height=0; $size='1080';
	
	if (isset($_GET['size'])) $size=$_GET['size'];
	if (isset($_GET['width'])) $width=$_GET['width']; 
	if (isset($_GET['height'])) $height=$_GET['height'];
	if (isset($_GET['minimum'])) $minimum='minimum'; else $minimum='';
	if (isset($_GET['download'])) $download=true; else $download=false;

	if (!$width&&!$height&&!$download){
		switch ($size){
			case 'smallthumb':$width=170;$height=170;$minimum='minimum';$download=false;break;
			case 'thumb':$width=250;$height=250;$minimum='minimum';$download=false;break;
			case 'preview':$width=450;$height=338;$minimum='';$download=false;break;
			default:
				$width=1000000;$height=1080;$minimum='';$download=false;
			break;
		}
	}
	
	if (!$userIsAdmin && $width==0 &&!$download){
		$width=10000000; $height=10000000;
	}
	
	if (!$user && $download){
		$width=2048;$height=2048;
	}
	
	$isLimited=($width>0);
	$playIcon=false;
	
	if (stripos($getFile,'.m4v')) {
		$getFile=str_replace('.m4v','.preview.jpg',$getFile);
		$playIcon=true;
	}
	
	if (!file_exists($getFile)) {
		$getFile=$config->viewPath.'/design/missingimage.jpg';
	}
	
	if ($download) header('Content-Disposition: attachment; filename="'.basename($getFile).'"');
	
	$nocopyrightnotice=(strpos($getTags,'nocopyright')!==false);
	
	if ($isLimited) {
	 if ($minimum || $nocopyrightnotice || (isset($_GET['nocopyright']) && $_GET['nocopyright'])) $text='';
	 if (!isset($text)) {
	 	$copyright=ucwords_new(str_replace('_',' ',$copyright));
	 	if ($copyright=='...'){
	 		$copyright='';
	 	}
	 	$text=utf8_decode($copyright);
	 	if ($copyright=='none') $text='';
	 	
	 }
	 
	 $rotate=false;
	 if (strpos($getTags,'rotater')!==false) $rotate='right';
	 else if (strpos($getTags,'rotatel')!==false) $rotate='left';
	 else if (strpos($getTags,'norotate')!==false) $rotate='none';
	 else if (strpos($getTags,'rotate')!==false) $rotate='rotate';

	 if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== null && $_SERVER['HTTP_REFERER'] !== '') {
	 	$server=$_SERVER['HTTP_REFERER'];
	 	$server=str_replace('http://','',$server);
	 	$server=str_replace('https://','',$server);
	 	$server=str_replace('www.','',$server);
	 	$server=explode('/',$server);
	 	$server=$server[0];
	 	$entry='Image access from '.$server;
	 } else $entry='Direct URL access';
	 
	 shrinkImage($getFile,$width,$height,$rotate,$config->cachePath,$input,$text,$minimum,$playIcon);
	 
	}
	else {
	 readfile($getFile);
	}
	
function shrinkImage($lokalurl,$limitWidth,$limitHeight,$rotate,$cachePath,$key,$text='',$minimum='',$playIcon=false){global $config;	
	
	 if (stripos($lokalurl,'.youtube')){
	 	
	 	 //Get a youtube preview
	 	
	 	 $id=file_get_contents($lokalurl);
		 $content=file_get_contents('http://i3.ytimg.com/vi/'.$id.'/0.jpg');
		 $lokalurl=$cachePath.'/'.$id.'.jpg';
		 file_put_contents($lokalurl,$content);
	 }
	 
	 if (!$rotate){
	 	
	 	//Try to determine image rotation from exif data
	 	
	 	@$exif = exif_read_data($lokalurl);
	 	@$ort = $exif['Orientation'];
	    switch($ort){
	        case 3: $rotate='rotate'; break; // 180 rotate left
	        case 6: $rotate='right'; break; // 90 rotate right          
	        case 8: $rotate='left';  break;  // 90 rotate left
	    }
	 }
	 
	 if ($rotate=='right' || $rotate=='left'){
	 	
	 	// Swap width and hight limitations n case of a rotated image
	 	
	 	$temp=$limitHeight;
	 	$limitHeight=$limitWidth;
	 	$limitWidth=$temp;
	 }
	 
	 $withText=str_replace('%','_',rawurlencode($text));
		
	 // CACHING	
		
	 $cachePath.='/'.$key.'.'.$limitWidth.'.'.$limitHeight.'.'.filesize($lokalurl).$minimum.$withText.'.jpg';
	
	 if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL']=='no-cache') {
	 	if (file_exists($cachePath)) unlink($cachePath);
	 }     
	 
	 if (file_exists($cachePath)) {
	 	header ('location: '.$config->cacheURL.'/'.basename($cachePath));die('FROM CACHE');
	 } 
	 
	 // END CACHING
		
	 $expires=date("D, d M Y H:i:s",time() + (3 * 60 * 60)).' GMT';
	 header("Expires: $expires");  //in one week
	 header("Content-type: image/jpeg");	
		
	 ini_set('memory_limit', '1024M');set_time_limit(60);ini_set('gd.jpeg_ignore_warning', 1);               
	 
	 if (!$original=@imagecreatefromfile($lokalurl)) return;	
	 
	 // determine new image dimensions
	 $size=GetImageSize($lokalurl);
	 $origwidth=$size[0];$origheight=$size[1];
	 
	 /*
	 //no upsizing
	 if ($origwidth<$limitWidth) $limitWidth=$origwidth;
	 if ($origheight<$limitHeight) $limitHeight=$origheight;
	 */
	 
	 $relationWidth=$origwidth/$limitWidth;
	 $relationHeight=$origheight/$limitHeight;
	 
	 //if minimum is set, a square image is created
	 if (!$minimum){
	   if ($relationHeight>$relationWidth){
	   	  $newheight=$limitHeight;
	 	  $newwidth=floor($origwidth/$relationHeight);
	   } else {
	 	  $newwidth=$limitWidth;
	 	  $newheight=floor($origheight/$relationWidth);
	   }
	 } else {
	 	$newheight=$limitHeight;
	 	$newwidth=$limitWidth;
	 	if ($relationHeight>$relationWidth){
	     $newheight=floor($origheight/$relationWidth);
	   } else {
	 	  $newwidth=floor($origwidth/$relationHeight);
	   }
	 }
	 
	 //Shrinking of the image
	 if ($minimum){
	 	$temp=ImageCreateTrueColor($limitWidth,$limitHeight);
	 	fastimagecopyresampled($temp,$original,-($newwidth-$limitWidth)/2,-($newheight-$limitHeight)/4,0,0,$newwidth,$newheight,$origwidth,$origheight);
	 }
	 else {
	   $temp=ImageCreateTrueColor($newwidth,$newheight);
	   fastimagecopyresampled($temp,$original,0,0,0,0,$newwidth,$newheight,$origwidth,$origheight);
	 } 
	 
	 if ($rotate=='rotate'){$temp=ImageRotateRightAngle ($temp, 180);}
	 if ($rotate=='right'){$temp=ImageRotateRightAngle ($temp, 90);$newheight=$newwidth;}
	 if ($rotate=='left'){$temp=ImageRotateRightAngle ($temp, 270);$newheight=$newwidth;}
	 
	 if (!$minimum) {
	 	
	 	$size=($newwidth/1200*17);
	 	$offset=($newwidth/1200*10);
	 	
	 	if($size<10 || $offset<7){
	 		$size=10;$offset=7;
	 	}
	 	
	 	// Set the enviroment variable for GD
		putenv('GDFONTPATH=' . realpath('.'));
		
		// Name the font to be used (note the lack of the .ttf extension)
		$font = 'design/MeriendaOne-Regular';
	 	
	 	$color = imagecolorresolvealpha($temp, 0, 0, 0,63);
	 	/*
	 	imagefttext($temp, $size, 0, $offset, $newheight-$offset+1, $color, $font, $text);
	 	imagefttext($temp, $size, 0, $offset, $newheight-$offset-1, $color, $font, $text);
	 	imagefttext($temp, $size, 0, $offset-1, $newheight-$offset, $color, $font, $text);
	 	imagefttext($temp, $size, 0, $offset+1, $newheight-$offset, $color, $font, $text);
	 	*/
	 	$color = imagecolorresolvealpha($temp, 255, 255, 255,34);
	 	imagefttext($temp, $size, 0, $offset, $newheight-$offset, $color, $font, $text);
	
		 //show a play icon
		 
		 if ($playIcon){
		 	$colorCircle=imagecolorallocatealpha ( $temp,255,255,255,30 );
		 	$colorTriangle=imagecolorallocatealpha ( $temp,0,0,0,30 );
		 	imagefilledellipse ( $temp , $newwidth/2 , $newheight/2 ,  100 ,  100 , $colorCircle );
		 	imagefilledpolygon ( $temp , array($newwidth/2-20,  $newheight/2-35, $newwidth/2+35,  $newheight/2, $newwidth/2-20,  $newheight/2+35) , 3 , $colorTriangle );
		 }
	 	
	 }
	 
	 //create output image
	 
	 $quality=($newheight*$newwidth<=300*300)?75:85;
	 if ($newheight*$newwidth<=100*100) $quality=20;
	 imageinterlace($temp,1);
	 ImageJPEG($temp,$cachePath,$quality);
	 
	 //copy original exif data to new file
	 
	 if ($newheight*$newwidth>=300*300){
	  	$command= ('exiftool -overwrite_original -TagsFromFile "'.$lokalurl.'" --Orientation "'.$cachePath.'"');
	  	exec($command);
	 }
	 
	 //deliver newly created image
	 
	 echo (file_get_contents($cachePath));
	 imagedestroy($temp);
	 imagedestroy($original);
}

function imagecreatefromfile($path, $user_functions = false)
{
    $info = @getimagesize($path);
    
    if(!$info)
    {
        return false;
    }
    
    $functions = array(
        IMAGETYPE_GIF => 'imagecreatefromgif',
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_WBMP => 'imagecreatefromwbmp',
        IMAGETYPE_XBM => 'imagecreatefromwxbm',
        );
    
    if($user_functions)
    {
        $functions[IMAGETYPE_BMP] = 'imagecreatefrombmp';
    }
    
    if(!$functions[$info[2]])
    {
        return false;
    }
    
    if(!function_exists($functions[$info[2]]))
    {
        return false;
    }
    
    return $functions[$info[2]]($path);
}

function ImageRotateRightAngle( $imgSrc, $angle ) 
{ 
    // ensuring we got really RightAngle (if not we choose the closest one) 
    $angle = min( ( (int)(($angle+45) / 90) * 90), 270 ); 

    // no need to fight 
    if( $angle == 0 ) 
        return( $imgSrc ); 

    // dimenstion of source image 
    $srcX = imagesx( $imgSrc ); 
    $srcY = imagesy( $imgSrc ); 

    switch( $angle ) 
        { 
        case 90: 
            $imgDest = imagecreatetruecolor( $srcY, $srcX ); 
            for( $x=0; $x<$srcX; $x++ ) 
                for( $y=0; $y<$srcY; $y++ ) 
                    imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1); 
            break; 

        //case 180: 
        //    $imgDest = ImageFlip( $imgSrc, IMAGE_FLIP_BOTH ); 
        //    break; 

        case 270: 
            $imgDest = imagecreatetruecolor( $srcY, $srcX ); 
            for( $x=0; $x<$srcX; $x++ ) 
                for( $y=0; $y<$srcY; $y++ ) 
                    imagecopy($imgDest, $imgSrc, $y, $srcX-$x-1, $x, $y, 1, 1); 
            break; 
        } 

    return( $imgDest ); 
} 

function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
  // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
  // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
  // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
  // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
  //
  // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
  // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
  // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
  // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
  // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
  // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
  // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

  if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }
  if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
    $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
    imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
    imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
    imagedestroy ($temp);
  } else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  return true;
}

?>