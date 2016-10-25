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
 
  include_once ('environment.php');
  
  ini_set('memory_limit', '1024M');

  mysql_query("truncate table $resultCacheTable");
     
  writeLog('update','Updated called');
  
  if (tableExists($runTable)){
     if (tableAge($runTable)>30*60) 
        mysql_query("drop table $runTable");
     else {
        writeLog('update','Another update is running');
        die ('Another update is running');
     }
  }
  
  tableCopy($sourceTable,$runTable);
  
  mysql_query("truncate table $runTable");
  
  $delta=getFileChanges($runTable,$config);
  
  foreach ($delta as $filename=>$operation){
  	 switch ($operation){
  	    case 'delete': deleteEntry($filename); break;
	    default: addEntry($filename);break;
  	 }
  }
  
  tablesSwap($runTable,$sourceTable);
  mysql_query("DROP table $backupTable");
  mysql_query("RENAME TABLE $runTable TO $backupTable");
  
  final_cleanup();
  mysql_query("truncate table $resultCacheTable");
  writeLog('update','Database has been updated');
 
  
  
  die ('Ready');

  function final_cleanup(){
  	
     //Nothing at the moment
  	
  }
  
  function getKey($filename){
        $temp=basename($filename);
  		$temp=substr($temp,0,strrpos($temp,'.'));
  		$temp=str_replace(' ','_',cleanForFile($temp,true));
		$temp=str_replace('(','_',$temp);
		$temp=str_replace(')','_',$temp);
  		$key=filesize($filename).$temp;
  		return $key;
  }
  
  $keys=array();
  
  
  $topicCache=array();
  
  function addEntry($filename) {global $runTable,$config,$catCache;
        
        $contentPath=$config->contentPath;
        
        $depth=count(explode('/',$contentPath));
        
        $key=getKey($filename);
  		
  		$sortString=substr($filename,strlen($contentPath)+1);
        $sortString=str_replace("'",'',substr($sortString,strpos($sortString,'/')));
         
        $pathparts=explode('/',$filename);
        @$folder=$pathparts[$depth+1];
        $bracketPos=strpos($folder,'(');
	    if ($bracketPos) $folder=trim(substr($folder,0,$bracketPos));
	    
	    $bracketPos=strpos($folder,'[');
	    if ($bracketPos) $folder=trim(substr($folder,0,$bracketPos));
        
        $tags=getAllTags($key,$filename,$folder);
        
        @$category=$pathparts[$depth];
	   	
	   	$copyright='...';
	   	$temp=explode(' ',$tags);
	   	foreach ($temp as $v){
	   		if (stripos($v,'copyright_')!==false){
	   			$copyright=str_replace('copyright_','',$v);
	   			break;
	   		}
	   	}
	   	
	   	if ($copyright=='winfel') $copyright='felix_winkelnkemper';
                
        $start=substr($sortString,0,3);
        if ($start!='/20' && $start!='/19' && $start!='/21') $sortString='/0'.substr($sortString,1);
        $subfolder=explode('/',dirname($sortString));
        $subfolder=(isset($subfolder[2]))?$subfolder[2]:'';
        
        if (!isset($topicCache[$subfolder])){
        	$topic=$subfolder;
        	
        	$pos=strpos($topic,'['); if ($pos!==false) $topic=substr($topic,0,$pos);
			$pos=strpos($topic,'('); if ($pos!==false) $topic=substr($topic,0,$pos);
			$topic=trim($topic);
			
			$topic=explode(' ',$topic);
			
			if (is_numeric($topic[0])) unset ($topic[0]);
			
			$topic=implode(' ',$topic);
			$topic=pretty(trim($topic));
        	
        	$topicCache[$subfolder]=$topic;
        } else {
        	$topic=$topicCache[$subfolder];
        }
        
  		mysql_query("insert into $runTable (`key`,filename,sortstring,`tags`,folder, category, copyright, subfolder) values ('$key','$filename','$sortString','$tags','$folder','$category','$copyright','$topic')");
  		//echo "$filename<br>";
  
  }
  
  function deleteEntry($filename){global $runTable;
 
  	mysql_query("delete from $runTable where filename='$filename'");
  
  }
  
  function tablesSwap($one,$other){
     mysql_query("RENAME TABLE $one TO tmp_table,$other TO $one,tmp_table TO $other;");
  }
  
  	function tableExists($Tabellenname) {
       if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$Tabellenname."'"))) return true;
       return false;
    }
    
    function tableAge($table){
    	$fileTableInfo=mysql_fetch_object(mysql_query("SHOW TABLE STATUS LIKE '$table'"));
        $changeTime=$fileTableInfo->Update_time;
  
    	$timeInfo=mysql_fetch_object(mysql_query("SELECT TIME_TO_SEC(TIMEDIFF(NOW(),'$changeTime')) AS difference"));
    	return $timeInfo->difference;
    }
    
    function tableCopy($source,$destination){
       mysql_query("CREATE TABLE $destination ENGINE=MyISAM SELECT * FROM $source ");
    }
    
    function getFileChanges($sourceTable,$config){
         $list=array();
  
		  $result=mysql_query("SELECT filename FROM $sourceTable");
		  while ($resultSet=mysql_fetch_object($result)){
		  	$list[$resultSet->filename]='delete';
		  };
		  
		  
		  $t=time();
		  $handle = popen('ls -R -1 -r '.$config->contentPath, 'r');
		  echo "ls took ".(time()-$t).' seconds <br>';
  
		  while (!feof($handle)) {
		  	$line = trim(fgets($handle));
		  	$lowline=strtolower($line);
		  	if (strpos($line,$config->contentPath)===0) { // any new directory starts with it's path
		       $path=substr($line,0,-1);continue;
		    }
		    $fullpath=$path.'/'.$line; 
		    if (is_dir($fullpath)) continue;
		    if (stripos($lowline,'.db')===false
		     && stripos($lowline,'.tmp')===false
		     && stripos($lowline,'.webm')===false
		     && stripos($lowline,'.tags')===false
		     && stripos($lowline,'.exif')===false
		     && stripos($lowline,'.preview.jpg')===false) {  
		       if (isset($list[$fullpath])) unset($list[$fullpath]); else $list[$fullpath]='add';
		       //HERE
		    } 
		  }
		  pclose($handle);

		  
		 return $list;
    }
    

	function checkLetter($letter){
		if ($letter==' ') return true;
		if ($letter=='/') return true;
		if ($letter=='0') return true;
		if ($letter=='-') return true;
		if ($letter=='_') return true;
		if ($letter=='.') return true;
		if ($letter==',') return true;
		if ($letter=='\'') return true;
		if ($letter=='(') return true;
		if ($letter==')') return true;
		if ($letter=='@') return true;
		if ($letter>='a' && $letter<='z') return true;
		if ($letter>='A' && $letter<='Z') return true;
		if ($letter>='1' && $letter<='9') return true;
		return false;
	}
    
    function replaceCrazyLetters($string,$removeDoubleSpaces=true){
		$string=str_split($string);
		foreach ($string as $id=>$letter){
			if (!checkLetter($letter)) {
				$string[$id]=' ';
		    }
		}
		$string=implode('',$string);
		if ($removeDoubleSpaces){
		 $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
		}
	    return $string;
	}

	function cleanForFile($string,$removeslashesAndCommas=false){
		
	    $string=replaceCrazyLetters($string);
	    if ($removeslashesAndCommas) {
	      $string=str_replace('/','',$string);
	      $string=str_replace(',',' ',$string);
	    } else $string=str_replace('/ ','/',$string);
	   
	    $string=trim($string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	     $string=str_replace('  ',' ',$string);
	    $string=explode(' ',$string);
	    $string=array_unique($string);
	    $string=implode(' ',$string);
	    
	    return $string;
	}
	
	mysql_close();
	
?> 
