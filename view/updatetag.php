<?php

session_start();

 include ('../config.php');
 include_once ('phpfunctions.php');
 
 if ($config->local) die('Not locally');

  
 mysql_connect($config->dbServer, $config->dbUser, $config->dbPassword) or die('Could not connect: ' . mysql_error());
 mysql_select_db($config->dbBase) or die('Could not select database');

 ini_set ('error_reporting', E_ALL);
 ini_set ('html_errors', 'Off');
 
preventInjection();
 
$key=isset($_GET['key'])?$_GET['key']:false;
$tags=isset($_GET['tags'])?$_GET['tags']:'';

if (!$key) die ('ERROR! No image!');

$tags=str_replace("\n",' ',$tags);
$tags=str_replace("\r",' ',$tags);

while (strpos($tags,'  ')!==false){
	$tags=str_replace('  ',' ',$tags);
}

$tags=trim($tags);

if (isset($_SESSION['user'])){
	$user=$_SESSION['user'];
	$userQuery=$_SESSION['userQuery'];
} else die ('ERROR! No session');


mysql_query("DELETE FROM filetags WHERE `image`='$key'");
mysql_query("INSERT INTO filetags (tags,image) VALUES('$tags','$key')");

$alltags=getAllTags($key);

mysql_query("UPDATE files SET tags='$alltags' WHERE `key`='$key'");

echo getReadableTags($alltags);

mysql_close();
?>