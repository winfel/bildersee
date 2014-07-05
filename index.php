<?php

/**
 * Bildersee - Main controller
 */
 
 include_once ('config.php');

 $selection=explode('.',$_SERVER["HTTP_HOST"]);
 $selection=$selection[0];
 $url=$config->viewURL;
 
 if (stripos($config->serverURL,$selection)!==false){
 	$selection='www';
 } 
 
 $url=str_replace('www',$selection,$url);
 
 header("Location: $url");
?>