<?php

 //ini_set ('error_reporting', E_ALL);
 //ini_set ('display_errors', true);

	include_once ('environment.php');
	
	$config=new Config(); 
	
	if (isset($_GET['key'])){$input=($_GET['key']);} else {die ('error');}
	
	$result=mysql_query("SELECT filename,copyright,tags FROM files WHERE md5(`key`)='$input' OR `key`='$input'");
	$resultSet=mysql_fetch_object($result);
	@$getFile=($resultSet->filename);
	@$getTags=($resultSet->tags);
	@$copyright=($resultSet->copyright);
	
	if (isset($_GET['size']) && $_GET['size']) $size=$_GET['size']; else $size='1x';
	
	$playIcon=false;
	
	if (stripos($getFile,'.m4v')) {
		$getFile=str_replace('.m4v','.preview.jpg',$getFile);
		$playIcon=true;
	}
	
	if (!file_exists($getFile)) {
		$getFile=$config->viewPath.'/design/missingimage.jpg';
	}
	
	$nocopyrightnotice=(strpos($getTags,'nocopyright')!==false);
	
	 if ($nocopyrightnotice || (isset($_GET['nocopyright']) && $_GET['nocopyright'])) $text='';
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
	 
	 shrinkImage($getFile,$size,$rotate,$config->cachePath,$input,$text,$playIcon);
	 
	
function shrinkImage($lokalurl,$size,$rotate,$cachePath,$key,$text='',$playIcon=false){global $config;	
	
	switch ($size){
	    case '1.5x':$limitWidth=1000001;$limitHeight=559;$quality=75;break;
	    case '2x':$limitWidth=1000001;$limitHeight=731;$quality=65;break;
		default:
			$limitWidth=1000001;$limitHeight=430;$quality=85;break;
		break;
	}
	
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
		
	 $cachePath.='/'.$key.'.'.$size.'.jpg';
	 
	 
	 $expires=date("D, d M Y H:i:s",time() + (3 * 60 * 60)).' GMT';
	 header("Expires: $expires");  //in one week
	 
	 
	 if (file_exists($cachePath)) {
	 	header ('location: '.$config->cacheURL.'/'.basename($cachePath));die('FROM CACHE');
	 } 
	
	 
	 // END CACHING
		
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
	 
	 	$newheight=$limitHeight;
	 	$newwidth=$limitWidth;
	 	
	 	if ($relationHeight>$relationWidth){
	   	  $newheight=$limitHeight;
	 	  $newwidth=floor($origwidth/$relationHeight);
	   } else {
	 	  $newwidth=$limitWidth;
	 	  $newheight=floor($origheight/$relationWidth);
	   }

	 
	 //Shrinking of the image
	
	   $temp=ImageCreateTrueColor($newwidth,$newheight);
	   imagecopyresampled($temp,$original,0,0,0,0,$newwidth,$newheight,$origwidth,$origheight); 
	 
	 if ($rotate=='rotate'){$temp=ImageRotateRightAngle ($temp, 180);}
	 if ($rotate=='right'){$temp=ImageRotateRightAngle ($temp, 90);$newheight=$newwidth;}
	 if ($rotate=='left'){$temp=ImageRotateRightAngle ($temp, 270);$newheight=$newwidth;}
	 	
	 	$size=($newwidth/1200*17);
	 	$offset=($newwidth/1200*10);
	 	
	 	if($size<10 || $offset<7){
	 		$size=10;$offset=7;
	 	}
	 	
	 	// Set the enviroment variable for GD
		putenv('GDFONTPATH=' . realpath('.'));
		
		// Name the font to be used (note the lack of the .ttf extension)
		$font = 'design/Chalkduster';
		
	 	$color = imagecolorresolvealpha($temp, 255, 255, 255,34);
	 	imagefttext($temp, $size, 0, $offset, $newheight-$offset, $color, $font, $text);
	
		 //show a play icon
		 
		 if ($playIcon){
		 	$colorCircle=imagecolorallocatealpha ( $temp,255,255,255,30 );
		 	$colorTriangle=imagecolorallocatealpha ( $temp,0,0,0,30 );
		 	imagefilledellipse ( $temp , $newwidth/2 , $newheight/2 ,  100 ,  100 , $colorCircle );
		 	imagefilledpolygon ( $temp , array($newwidth/2-20,  $newheight/2-35, $newwidth/2+35,  $newheight/2, $newwidth/2-20,  $newheight/2+35) , 3 , $colorTriangle );
		 }
	 	
	
	 
	 //create output image
	 
	 if (!$quality){
		 $quality=($newheight*$newwidth<=400*400)?75:85;
		 if ($newheight*$newwidth<=100*100) $quality=20;
	 }
	 imageinterlace($temp,1);
	 ImageJPEG($temp,$cachePath,$quality);
	 
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

?>