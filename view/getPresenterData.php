<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Cache-Control: post-check=0, pre-check=0", FALSE);

include('environment.php');

$id=$_GET['id'];

if (!$id) die ('No id provided');

if (file_exists('../temp/'.$id)){
	$content=file_get_contents('../temp/'.$id);
} else $content="showid";

echo $content;

?>