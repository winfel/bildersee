<?php

/**
 * Configuration file
 */

  class Config{
   
   function __construct(){
   	 
   	  $docroot=$_SERVER['DOCUMENT_ROOT'];
   	  $serverURL='http://www.website.com';
   	  $staticServerURL='http://static.website.com';
   	  
   	  $this->hash=md5(time()); // security
  
	  $this->dbServer='127.0.0.1';
      $this->dbUser='user';
      $this->dbPassword='password';
      $this->dbBase='database';   	  
   	 
   	  $this->enableBrowserCaching=true;
   	  $this->backDoor=''; //md5 hash of backdoor password
   	  
   	  $this->local=true;
   	  $this->alwaysLoggedIn=true;
   	  $this->perPage=300;
   	  
   	  $this->serverURL=$serverURL;
   	  $this->viewPath=$docroot.'/view';
   	  $this->viewURL=$serverURL.'/view';
   	  $this->logsPath=$docroot.'/logs';
   	  $this->contentPath=$docroot.'/pics';
   	  $this->tempPath=$docroot.'/temp';
      $this->uploadPath=$docroot.'/upload_pics';
      $this->cachePath=$docroot.'/cache';
      $this->updateURL=$serverURL.'/upload';
      
      $this->imageGetterURL=$staticServerURL.'/view/getimage.php';
      $this->cacheURL=$staticServerURL.'/cache';
      $this->designURL=$staticServerURL.'/view/design';
      
      $this->pageTitle='Page Title';
      $this->pageDescription='Page Description';  
      $this->copyright='&copy;  2012, Felix Winkelnkemper – Horner Straße 22 – 33102 Paderborn – Germany
  <br />Phone: +49 151 40432121 – E-mail: winkelnkemper@googlemail.com – ICQ: 54428358';   
      
   }
 
} 

 $config=new Config(); 
 $securityHash=$config->hash;

?>