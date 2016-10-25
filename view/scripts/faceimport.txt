<?php
 
  /*
 
  SeeSite database update script
  
  */
  

  if ($_SERVER["HTTP_HOST"]=='localhost'){
 	die('This connot be done locally');
  }
  
  set_time_limit(30*60);
  ini_set('memory_limit', '1024M');
 
  $resultCacheTable='resultcache';
  $runTable='running';
  $sourceTable='files';
  $backupTable='files_backup';
  $count=0;
 
  include_once ('environment.php');
 
   if (!tableExists($runTable)){
     parse_faces();
   }
    else {
       writeLog('faces','An update is running');
       die ("An update is running\n");
    }
 


  die ();
  
  function parse_faces(){global $config,$count;
  	
  	if(file_exists($config->tempPath.'/facelist')){
  		writeLog('update','Parsing face file');
  		$file=file_get_contents($config->tempPath.'/facelist');
  		$data=json_decode(gzuncompress($file),true);
  		$dbdata=array();
  		$query=mysql_query("SELECT files.tags as alltags, filetags.tags as filetags, filename, `key` FROM files LEFT JOIN filetags ON files.`key`=filetags.image");
		while ($dbentry=mysql_fetch_object($query)){
			$dbdata[$dbentry->filename]=$dbentry;
		}
		
		$allPicasaFaces=array();
  		foreach ($data as $entry){
  			$filename=str_replace('#',$config->contentPath,$entry['filename']);
  			
  			
  			
  			if (!isset($dbdata[$filename])){
  				//echo $entry['filename']." not in db selection\n";
  				continue;
  			}
  		
  			$dbEntry=$dbdata[$filename];
  			
  			$filetags=$dbEntry->filetags;
  			$alltags=$dbEntry->alltags;
  			
  			$changed=false;
  			
  			foreach ($entry['faces'] as $candidate){
  				$allPicasaFaces[$candidate]=true;
  				if (strpos($filetags,$candidate)!==false) continue;
  				echo "Adding $candidate to ".$entry['filename']."\n";
  				$count++;
  				$changed=true;
  				$filetags.=" $candidate";
  				$alltags.=" $candidate";
  			}
  			
  			if ($changed){
  				$key=$dbEntry->key;
  				mysql_query("UPDATE files SET tags='$alltags' WHERE `key`='$key'");
  				mysql_query("DELETE FROM filetags WHERE image='$key'");
  				mysql_query("INSERT INTO filetags (tags, image) VALUES('$filetags','$key')");
  			}
  			
  		}
  		
  		echo 'Added '.$count." faces.\n";
  		
  		foreach (array_keys($allPicasaFaces) as $name){
  			$query=mysql_query("SELECT * FROM `people` WHERE `tag` LIKE '$name'");
  			
  			if ($dbentry=mysql_fetch_object($query)){
				
			} else {
				echo "Adding $name  to people database.\n";
				mysql_query("INSERT INTO `people` (`tag`) VALUES ('$name')");
			}
  			
  			
  			
  			
  		}
  		
  	}
  	
  	
  }
   
  
  	function tableExists($Tabellenname) {
       if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$Tabellenname."'"))) return true;
       return false;
    }
    

	
	mysql_close();
	
?> 
