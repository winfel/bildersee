<?php

 //ini_set ('error_reporting', E_ALL);
 //ini_set ('display_errors', true);

	include_once ('environment.php');
	
	$config=new Config(); 
	
	
	if (isset($_GET['key'])){
	   $input=($_GET['key']);
	} else {
		die ('error');
	}
	
	$userIsAdmin=false;
	if(isset($_SESSION['userIsAdmin'])) $userIsAdmin=$_SESSION['userIsAdmin'];
	
	
	$result=mysql_query("SELECT filename,copyright,tags FROM files WHERE md5(`key`)='$input' OR `key`='$input'");
	$resultSet=mysql_fetch_object($result);
	@$getFile=($resultSet->filename);
	@$getTags=($resultSet->tags);
	@$copyright=($resultSet->copyright);
	
	$width=0; $height=0;
	
	
	if (isset($_GET['width'])) $width=$_GET['width']; 
	if (isset($_GET['height'])) $height=$_GET['height'];
	if (isset($_GET['minimum'])) $minimum='minimum'; else $minimum='';
	
	if (!$userIsAdmin && $width==0){
		$width=10000000; $height=10000000;
	}
	
	$isLimited=($width>0);
	
	if (!file_exists($getFile)) {
	$getFile=$config->viewPath.'/design/missingimage.jpg';
	}
	
	//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	$expires=date("D, d M Y H:i:s",time() + (3 * 60 * 60)).' GMT';
	header("Expires: $expires");  //in one week
	header("Content-type: image/jpeg");
	//echo $getFile;
	
	
	if (isset($_GET['download'])) header('Content-Disposition: attachment; filename="'.basename($getFile).'"');
	
	$nocopyrightnotice=(strpos($getTags,'nocopyright')!==false);
	
	if ($isLimited) {
	 if ($minimum || $nocopyrightnotice || (isset($_GET['nocopyright']) && $_GET['nocopyright'])) $text='';
	 if (!isset($text)) {
	 	$copyright=ucwords_new(str_replace('_',' ',$copyright));
	 	if ($copyright=='...'){
	 		$copyright=$config->pageTitle;
	 	}
	 	$text=chr(169).' '.utf8_decode($copyright);
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
	 
	 shrinkImage($getFile,$width,$height,$rotate,$config->cachePath,$input,$text,$minimum);
	 
	}
	else {
	 readfile($getFile);
	}
	
function shrinkImage($lokalurl,$limitWidth,$limitHeight,$rotate,$cachePath,$key,$text='',$minimum=''){global $config;	
	
 //TODO Handling of different formats
 if (stripos($lokalurl,'.youtube')){
 	 	$id=file_get_contents($lokalurl);
	 	$content=file_get_contents('http://i3.ytimg.com/vi/'.$id.'/0.jpg');
	 	$lokalurl=$cachePath.'/'.$id.'.jpg';
	 	file_put_contents($lokalurl,$content);
 }
 
 if (!$rotate){
 	@$exif = exif_read_data($lokalurl);
 	@$ort = $exif['Orientation'];
    switch($ort)
    {
                      
        case 3: // 180 rotate left
            $rotate='rotate';
        break;
                                  
        case 6: // 90 rotate right
            $rotate='right';
        break;
               
        case 8:    // 90 rotate left
            $rotate='left';
        break;
    }
 }
 
 if ($rotate=='right' || $rotate=='left'){
 	$temp=$limitHeight;
 	$limitHeight=$limitWidth;
 	$limitWidth=$temp;
 }
 
 $withText=str_replace('%','_',rawurlencode($text));
	
 $cachePath.='/'.$key.'.'.$limitWidth.'.'.$limitHeight.'.'.filesize($lokalurl).$minimum.$withText.'.jpg';

 if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL']=='no-cache') {
 	if (file_exists($cachePath)) unlink($cachePath);
 }

        
 
 if (file_exists($cachePath)) {
 	//writeLog('update','Got'.$cachePath.' from cache');
 	header ('location: '.$config->cacheURL.'/'.basename($cachePath));
 	die('');
 	//return readfile($cachePath);
 } //else writeLog('update','Did not find '.$cachePath.' in cache');
	
 ini_set('memory_limit', '1024M');
 set_time_limit(60);
 ini_set('gd.jpeg_ignore_warning', 1);               
 
 if (!$alt=@imagecreatefromfile($lokalurl)) {
 	return;
 }	
 	
 $size=GetImageSize($lokalurl);
 $origwidth=$size[0];$origheight=$size[1];
 
 if ($origwidth<$limitWidth) $limitWidth=$origwidth;
 if ($origheight<$limitHeight) $limitHeight=$origheight;
 
 $relationWidth=$origwidth/$limitWidth;
 $relationHeight=$origheight/$limitHeight;
 
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
 	ImageCopyResampled($temp,$alt,-($newwidth-$limitWidth)/2,-($newheight-$limitHeight)/4,0,0,$newwidth,$newheight,$origwidth,$origheight);
 }
 else {
   $temp=ImageCreateTrueColor($newwidth,$newheight);
   ImageCopyResampled($temp,$alt,0,0,0,0,$newwidth,$newheight,$origwidth,$origheight);
 } 
 
 if ($rotate=='rotate'){
 	$temp=ImageRotateRightAngle ($temp, 180);
 }
 
 if ($rotate=='right'){
 	$temp=ImageRotateRightAngle ($temp, 90);
 	$newheight=$newwidth;
 }
 
 if ($rotate=='left'){
 	$temp=ImageRotateRightAngle ($temp, 270);
 	$newheight=$newwidth;
 }
 
 if (!$minimum) {
 	
 	$size=($newwidth/1200*13);
 	$offset=($newwidth/1200*10);
 	
 	if($size<8 || $offset<5){
 		$size=8;$offset=5;
 	}
 	
 	// Set the enviroment variable for GD
	putenv('GDFONTPATH=' . realpath('.'));
	
	// Name the font to be used (note the lack of the .ttf extension)
	$font = 'design/MeriendaOne-Regular';
 	
 	$color = imagecolorresolve($temp, 0, 0, 0);
 	imagefttext($temp, $size, 0, $offset, $newheight-$offset+1, $color, $font, $text);
 	imagefttext($temp, $size, 0, $offset, $newheight-$offset-1, $color, $font, $text);
 	imagefttext($temp, $size, 0, $offset-1, $newheight-$offset, $color, $font, $text);
 	imagefttext($temp, $size, 0, $offset+1, $newheight-$offset, $color, $font, $text);
 	$color = imagecolorresolve($temp, 255, 255, 255);
 	imagefttext($temp, $size, 0, $offset, $newheight-$offset, $color, $font, $text);
 	
 }
 
 $quality=($newheight*$newwidth<=300*300)?75:95;
 if ($newheight*$newwidth<=100*100) $quality=20;
 imageinterlace($temp,1);
 ImageJPEG($temp,$cachePath,$quality);
 if ($newheight*$newwidth>=300*300){
  	$command= ('exiftool -overwrite_original -TagsFromFile "'.$lokalurl.'" --Orientation "'.$cachePath.'"');
  	exec($command);
 }
 echo (file_get_contents($cachePath));
 imagedestroy($temp);
 imagedestroy($alt);
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
mysql_close();

?>