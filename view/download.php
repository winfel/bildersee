<?php

include_once ('environment.php');
 
preventInjection();
set_time_limit (0);
 
$folder=isset($_GET['folder'])?$_GET['folder']:false;

$title=$folder;

if ($title='%%') $title='download';


if (!$folder || $folder=='%') die ('Missing folder!');


	$files=array();

	$search=getImages($folder);
    
	while ($line=array_shift($search)){
		{
			$line->temp=str_replace($config->contentPath,'',$line->filename);
			$files[]=$line;
		} 

	}
	
	if (!$files) die('Error: No files');
	
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$title.'.zip"');
	
	//shorten temps
	
	$first=$files[0];
	$first=$first->temp;
	$length=strlen($first);
	
	$oldlength=0;
	
	while ($length!==$oldlength){
		set_time_limit (0);
		$oldlength=$length;
		foreach ($files as $id=>$entry){
			$entry=$entry->temp;
			if (substr($first,0,$length)!==substr($entry,0,$length)){
				$length--;
				break;
			}
		}
		
	}
	
	$tempfolder=$config->tempPath.'/'.'download'.time();
	
	foreach ($files as $id=>$entry){
		
		$file=$entry->filename;
		
		$temp=substr($entry->temp,$length-1);
		$temp=str_replace('[','####',$temp);
		$temp=str_replace(']','####',$temp);
		$temp=explode('####',$temp);
		foreach ($temp as $k=>$v){
			if ($k%2==1) unset ($temp[$k]);
		}
		$temp=implode($temp);
		$temp=str_replace('  ',' ',$temp);
		$temp=str_replace('  ',' ',$temp);
		$temp=str_replace(' /','/',$temp);
		$temp=str_replace('_',' ',$temp);
		
		$temp=$tempfolder.'/'.$temp;
		$temp=str_replace('//','/',$temp);
		$temp=str_replace('//','/',$temp);

		buildLink($file,$temp);
						
	}
	
	if (!file_exists($tempfolder)) die ('Could not cd to temp folder');
	
	$fp = popen('cd "'.$tempfolder.'/"; zip -0 -r - .', 'r');
	
	$bufsize = 8192;
	$buff = '';

	
	while ( !feof($fp) ) {
	   $buff = fread($fp, $bufsize);
	   echo $buff;
	   flush();
	}
	
	pclose($fp);
	

function buildLink($source,$target){
$filename=basename($target);
$path=dirname($target);
if (!file_exists($path)) mkdir($path,0777,true);
chmod ($path,0777);
symlink ($source ,$target);
}


?>