<?php

include('environment.php');

$id=$_GET['id'];

if (!$id) die ('No id provided');

if (file_exists('../temp/'.$id)){
	$content=file_get_contents('../temp/'.$id);
} else $content="showid";

echo $content;

?>