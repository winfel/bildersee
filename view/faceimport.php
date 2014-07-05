<?php
 
  /*
 
  SeeSite database update script
  
  */
  

  if ($_SERVER["HTTP_HOST"]=='localhost'){
 	die('This connot be done locally');
  }
  
  set_time_limit(30*60);
 
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
       die ('An update is running');
    }
 


  die ();
  
  function parse_faces(){global $config,$count;
  	
  	if(file_exists($config->tempPath.'/facelist')){
  		writeLog('update','Parsing face file');
  		$file=file_get_contents($config->tempPath.'/facelist');
  		$data=json_decode(gzuncompress($file),true);
  		$dbdata=array();
  		$query=mysql_query("SELECT files.tags as alltags, filetags.tags as filetags, filename, `key` FROM files LEFT JOIN filetags ON files.`key`=filetags.image WHERE files.tags LIKE '%year_2014%'");
		while ($dbentry=mysql_fetch_object($query)){
			$dbdata[$dbentry->filename]=$dbentry;
		}
  		foreach ($data as $entry){
  			$filename=str_replace('#',$config->contentPath,$entry['filename']);
  			
  			if (!isset($dbdata[$filename])){
  				//echo "$filename not in db selection\n";
  				continue;
  			}
  		
  			$dbEntry=$dbdata[$filename];
  			
  			$filetags=$dbEntry->filetags;
  			$alltags=$dbEntry->alltags;
  			
  			$changed=false;
  			
  			foreach ($entry['faces'] as $candidate){
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
  	}
  	
  	echo 'Added '.$count.' faces.';
  }
   
  
  	function tableExists($Tabellenname) {
       if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$Tabellenname."'"))) return true;
       return false;
    }
    

	
	mysql_close();
	
?> 
