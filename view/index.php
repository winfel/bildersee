<?php

/**
  * main controller file
  *
  * This is the main controller file for the image gallery.
  *	This is an online image gallery which presents private and
  *	insitutional photos to a greater audience with respect to the individual
  *	copyrights.
  *
  * @author  Felix Winkelnkemper <winkelnkemper@googlemail.com>
  * @copyright 2007-1012, Felix Winkelnkemper
  */

ob_start();

include_once('environment.php');

if ($config->local && $_SERVER['HTTP_HOST']!='localhost') die('<h1>Local access only</h1>');

$startTime=time();	//Debug

// Basic variables for a certain page and naviation information. Information is set in
// the actual page implementation

$activePart='events';	// the actively selected mode - 'events' if nothing else is given	
$breadcrumb=array();	// navigational breadcrumb
$pageTitle='';			// the title of the current page
$thumbnail=$config->designURL.'/webclip.png';
$sideBar='';
$pageDescription='';	// the description of the current page

//Include the head part of the HTML template. It is written in PHP as it contains some
//translations
include('design/head.html');

//the product consist of many page types. When a user is not logged in, he does not have access
//to all types of pages. The following lines determine, which page is the displayed in which
//circumstances

//When an image id (key) is there, the image page is used to display the image
//This even works, when the user has no rights but is passed a URL directly
if ($image){
	switch($mode){
		case 'slideshow':include('pages/slideshow.php');break;
		default:include('pages/image.php');
	}
		
} else {
	if (!$folder){
		
		//when not logged in, modes are restricted to a very few. Paricularly administration and tagging
		//are not accessible
		if (!$user && $mode!='gallery' && $mode!='tags' && $mode!='legal' && $mode!='privacy' && $mode!='upload') $mode='';
		
		//these modes are specified directly by url
		switch ($mode){
		 case 'tags':include('pages/tags.php');break;
		 case 'people':include('pages/people.php');break;
		 case 'taginfo':include('pages/taginfo.php');break;
		 case 'tagchange':include('pages/tagchange.php');break;
		 case 'stats':include('pages/stats.php');break;
		 case 'legal':include('pages/legal.php');break;
		 case 'privacy':include('pages/privacy.php');break;
		 case 'gallery':include('pages/gallery.php');break;
		 
		 //the default mode "overwiew" shows the most recent event
		 default:include('pages/overview.php');$mode='overview';
		 
		}
		
		//when a folder (an event) is specified by url, we either show the download
		//page or show the folders content.
		
	} else {
		
		switch ($mode){
			case 'download':include('pages/download.php');break;
			case 'slideshow':include('pages/slideshow.php');break;;
			case 'tagging':include('pages/tagging.php');break;;
			default: include ('pages/event.php'); break;

		}
		
	}
}

//Include the bottom part of the HTML template
include('design/foot.html');
mysql_close();

$output = ob_get_contents();
ob_end_clean();

//inserting page heading and description into the otherwise fully created page
$output=str_replace('#HEADING#',$pageTitle,$output);
$output=str_replace('#THUMBNAIL#',$thumbnail,$output);
$output=str_replace('#DESCRIPTION#',$pageDescription,$output);

echo $output;

?>