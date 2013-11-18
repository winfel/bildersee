<?php

include_once ('environment.php');
 
preventInjection();
 
$key=isset($_GET['key'])?$_GET['key']:false;

if (!$key) die ('ERROR! No image!');

$folder=mysql_query("SELECT folder,subfolder,filename FROM files WHERE $userQuery AND md5(`key`)='$key'");

if (!$folder=mysql_fetch_object($folder)) die ('Image not found!');

$topic=$folder->subfolder;

if (!$topic && substr(basename($folder->filename),4,1)=='-'){
	$topic=substr(basename($folder->filename),0,10);
}

$folder=$folder->folder;


$images=mysql_query("SELECT md5(`key`) as `key`,subfolder,filename FROM files WHERE $userQuery AND `folder`='$folder' ORDER BY sortstring");

$topics=array();
while ($image=mysql_fetch_object($images)){
	$category=$image->subfolder;
	if (!$image->subfolder && substr(basename($image->filename),4,1)=='-'){
			$category=substr(basename($image->filename),0,10);
		}
	
	@$topics[$category]++;
	
}

$catOnPage=array();
$perPage=$config->perPage;
$i=1;$counter=0;
foreach($topics as $category=>$thisCount){
	if ($counter !==0 && $counter+$thisCount>$perPage){
		$i++;
		$counter=0;
	} else {
		$counter+=$thisCount;
	}
	$catOnPage[$category]=$i;
}


$page=$catOnPage[$topic];

$folder=str_replace('_','',str_replace(' ','',strtolower($folder)));

header ("Location: index.php?folder=".urlencode($folder).'&page='.$page.'&filter=#scroll'.$key);

mysql_close();

?>