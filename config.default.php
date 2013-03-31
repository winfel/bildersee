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
   	  $this->localReplacement='http://www.example.com';
   	  
   	  $this->serverURL=$serverURL;
   	  $this->viewPath=$docroot.'/view';
   	  $this->viewURL=$serverURL.'/view';
   	  $this->logsPath=$docroot.'/logs';
   	  $this->contentPath=$docroot.'/pics';
   	  $this->contentURL=$serverURL.'/pics';
   	  $this->tempPath=$docroot.'/temp';
      $this->uploadPath=$docroot.'/upload_pics';
      $this->cachePath=$docroot.'/cache';
      $this->updateURL=$serverURL.'/upload';
      
      $this->imageGetterURL=$staticServerURL.'/view/getimage.php';
      $this->cacheURL=$staticServerURL.'/cache';
      $this->designURL=$staticServerURL.'/view/design';
      
      $this->pageTitle='Page Title';
      $this->pageDescription='Page Description';  
      $this->copyright='&copy;  2013, Copyright holder';
      
      $this->privacyBig='Data privacy statement';   
      
   }
 
} 

 $config=new Config(); 
 $securityHash=$config->hash;

?>