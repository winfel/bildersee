<?php

include_once ('environment.php');
 
preventInjection();
 
$key=isset($_GET['key'])?$_GET['key']:false;

if (!$key) die ('ERROR! No image!');

$folder=mysql_query("SELECT folder,subfolder FROM files WHERE $userQuery AND md5(`key`)='$key'");

if (!$folder=mysql_fetch_object($folder)) die ('Image not found!');

$topic=$folder->subfolder;
$folder=$folder->folder;

$images=getImages($folder);

$topics=array();
while ($image=array_shift($images)){
	
	@$topics[$image->subfolder]++;
	
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