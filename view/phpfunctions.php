<?php

/**
  * collection of PHP functions
  *
  * These functions are used in many places throughout the
  *	project.
  *
  * @author  Felix Winkelnkemper <winkelnkemper@googlemail.com>
  * @copyright 2007-1012, Felix Winkelnkemper
  */


/**
*	prevents script or database incections
*
*	preventInjection parses through the GET and POST arrays
*	and remove letters which are prone to be used in 
*	code injections
*/ 
function preventInjection(){
	
	foreach ($_GET as $key=>$value){
	
		$value=str_replace("'",'',$value);
		$value=str_replace('"','',$value);
		$value=str_replace("\\",'',$value);
		$value=str_replace("/",'',$value);
		
		$_GET[$key]=$value;
	
	}
	
	foreach ($_POST as $key=>$value){
	
		$value=str_replace("'",'',$value);
		$value=str_replace('"','',$value);
		$value=str_replace("\\",'',$value);
		$value=str_replace("/",'',$value);
		
		$_POST[$key]=$value;
	
	}
	
}

/**
*	determine, if the user agent is an iPhone
*
*	determines if the user agent is an iPhone.
*
*	@param $ipad When true, die function returns true, even when the user agent is an IPad
*	@return true, when the user agent is an iPhone, false otherwise
*/
function isIPhone($ipad=false){
	$agent=$_SERVER['HTTP_USER_AGENT'];
	if (stripos($agent,'iphone')!==false) return true;
	if ($ipad && stripos($agent,'ipad')!==false) return true;
	return false;
}

/**
*	determine, if the user agent is an iPad
*
*	determines if the user agent is an iPad.
*
*	@return true, when the user agent is an iPad, false otherwise
*/
function isIPad(){
	return isIPhone(true);
}

function pretty($entry){
	$entry=str_replace('_ae','ä',$entry);
	$entry=str_replace('_oe','ö',$entry);
	$entry=str_replace('_ue','ü',$entry);
	$entry=str_replace('_Ae','Ä',$entry);
	$entry=str_replace('_Oe','Ö',$entry);
	$entry=str_replace('_Ue','Ü',$entry);
	$entry=str_replace('_ss','ß',$entry);
	$entry=str_replace('_n','ñ',$entry);
	$entry=str_replace('_,',"'",$entry);
	
	$entry=str_replace('Palamos','Palamós',$entry);
	
	$entry=explode('(',$entry);$entry=$entry[0];
	$entry=explode('[',$entry);$entry=$entry[0];
	
	$entry=trim($entry);
	
	return $entry;
}



$people=array();

function getReadableTag($in,$path){global $tagInfo,$people;

	$tag=$in;

	if (!$people){
		$query=mysql_query('SELECT * FROM people');
	
	    while ($person=mysql_fetch_object($query)){
	   	   $people[$person->tag]=$person;
	    }
	}

	if ($tag=='thumb') return '**'.translate('thumbnail',true).'**';

	if ($tag=='gk') return false;
	if ($tag=='nocopyrightnotice') return false;
	if ($tag=='nocopyright') return false;
	if ($tag=='person') return false;
	if ($tag=='noperson') return false;
	if ($tag=='rotate') return false;
	if ($tag=='rotatel') return false;
	if ($tag=='rotater') return false;
	if ($tag=='untagged') return false;
	if ($tag=='archiv') return false;
	
	if (strpos($tag,'gk_')!==false) return false;
	if (strpos($tag,'copyright_')!==false) return false;
	if (strpos($tag,'year_')!==false) return false;
	if (strpos($tag,'codeword_')!==false) return false;
	if (strpos($tag,'autotag_')!==false) return false;
	if (strpos($tag,'geo_')!==false) return false;
	
	$tag=explode('_',$tag);
	
	foreach ($tag as $key=>$value){
		$tag[$key]=ucwords_new($value);
	}
	
	$tag=implode($tag,' ');
	
	//if ($path) $tag.='P';
	
	if (isset($people[$in]) && $people[$in]->birthday!=0) {
		$date=0;
		$bday=$people[$in]->birthday;
		$path=explode('/',$path);
		foreach ($path as $part){
			if (  substr($part,4,1)=='-'
			   && substr($part,7,1)=='-'){
			   	$date=strtotime(substr($part,0,10));
			}
		}
		if ($date) $tag.=' ('.getAge($bday,$date,false).')';
	}
	
	return $tag;
}

function getReadableTags($string,$path=false){global $people,$config;
	$string=explode(' ',$string);
	
	$result=array();
	$mode='neutral';
	
	foreach ($string as $value){
		if ($value=='privat') {
			$mode='privat';
			continue;
		}
		if ($value=='public') {
			$mode='public';
			continue;
		}
		$value=getReadableTag($value,$path);
		if ($value) $result[]=$value;
	}
	
	if ($config->local) $mode='neutral';
	
	$result=implode($result,', ');
	
	if (!$result) $result='';
	
	$result='<span class="'.$mode.'">'.$result.'</span>';
	return $result;
}

$peopleCache=false;
$impliedCache=false;
$autotagCache=false;

function getAllTags($key,$path=false,$folder=false){global $peopleCache, $impliedCache, $autotagCache;

if (!$path){
   $pathRequest=mysql_query("SELECT filename,folder FROM files WHERE `key`='$key'");
   if ($pathRequest=mysql_fetch_object($pathRequest)){
       $path=$pathRequest->filename;
       $folder=$pathRequest->folder;
   }
}

   $result=array();
      
      $filetagRequest=mysql_query("SELECT * FROM filetags WHERE image='$key'");
      if ($filetagRequest=mysql_fetch_object($filetagRequest)){
         //logout('DEBUG: '.$filetagRequest->tags);
         $fileTags=explode(' ',$filetagRequest->tags);
         foreach ($fileTags as $fileTag){
      		if ($fileTag=='') continue;
      		$result[strtolower($fileTag)]=true;
      		if (substr($fileTag,0,3)=='gk_') {
   				$result[substr($fileTag,3)]=true;
   				$result['gk_named']=true;
   				$result['gk']=true;
   			}
      	}
      }
  
   // Auto tag videos
   
  
   $filename=basename($path);
   if (stripos($filename,'.m4v')) $result['video']=true;
  
   // Search for copyright information in brackets
   
   $dirname=($path);
   $copyright=false;
   if (strpos($dirname,'(')!==false){
   	$temp=explode('(',$dirname);
   	for ($i=0;$i<count($temp);$i++){
   		if ($i==0) continue;
   		$possible=$temp[$i];
   		$bracketPos=strpos($possible,')');
   		if ($bracketPos===false) continue;
   		$possible=strtolower(substr($possible,0,$bracketPos));
   		if (!($possible+1-1==0)) continue;
   		{//if (strpos($possible,' ')===false) {
   		    $possible=str_replace('__ae','ä',$possible);
		   	$possible=str_replace('__oe','ö',$possible);
		   	$possible=str_replace('__ue','ü',$possible);
		   	$possible=str_replace('__ss','ß',$possible);
		   	$possible=str_replace('__n','ñ',$possible);
		   	$possible=str_replace('__o','ó',$possible);
		   	$possible=str_replace(' ','_',$possible);
		   	
		   	if ($possible=='winfel') $possible='felix_winkelnkemper';
		   	
		   	$copyright=$possible;
   			
   		}
   	}
   }
   if ($copyright) {
   	$result['copyright_'.$copyright]=true;
   }
   
   // Search for folder tags in brackets
   
   $parts=explode('[',$path);
   foreach ($parts as $part){
   		$bracketPos=strpos($part,']');
   		if ($bracketPos===false) continue;
   		$possible=strtolower(substr($part,0,$bracketPos));
   		$possible=explode(' ',$possible);
   		foreach ($possible as $onetag){
   		    if (!trim($onetag)) continue;
   		    $onetag=str_replace('__ae','ä',$onetag);
		   	$onetag=str_replace('__oe','ö',$onetag);
		   	$onetag=str_replace('__ue','ü',$onetag);
		   	$onetag=str_replace('__ss','ß',$onetag);
		   	$onetag=str_replace('__o','ó',$onetag);
		   	$onetag=str_replace('__n','ñ',$onetag);
   		    $result[strtolower($onetag)]=true;
   		}
   } 
   
$year=0;
$month=1;
$day=1;


$parts=explode('/',$path);
foreach ($parts as $part){
	$test=(int)(substr($part,0,4));
	if ($test>'1950' && $test<'2050') {
		$year=$test;
		$result['year_'.$test]=true;
		break;
	}
}

if ($year<date("Y")-1) $result['archiv']=true;
   
   if (!$autotagCache) {
   	$query=mysql_query('SELECT * FROM autotags');

    while ($autotag=mysql_fetch_object($query)){
   	   $autotagCache[]=$autotag;
    }
   }

foreach ($autotagCache as $autotag){
	if (stripos($path,$autotag->pathpart)) $result[strtolower($autotag->tag)]=true;
}

   foreach (array_keys($result) as $tag){
   	
   	if (!$impliedCache){
   		$dbresult=mysql_query("SELECT tag,implied FROM tags_implied");
   	    while ($line=mysql_fetch_object($dbresult)){
   	    	$impliedCache[$line->tag]=explode(' ',$line->implied);
   	    }
   	}

	if (isset($impliedCache[$tag])){
		foreach ($impliedCache[$tag] as $implied){
		   $result[strtolower($implied)]=true;
		}
	}
	 
   }
   
   if (isset($result['privat']) && isset($result['public'])) unset($result['public']);  //private always wins
   
   $autoCodeword=false;
   if (strpos($path,'codeword_')===false && !isset($result['public']) && !isset($result['privat'])){
   		$result[getFolderCodeword($folder)]=true; //automatically create codeword for nonpublic events
   		$autoCodeword=true;
   }
 
   
   if (!$autoCodeword && strpos($path,'codeword_')===false && !isset($result['public']) && !isset($result['privat'])) $result['privat']=true;
      
   if (isset($result['palamos'])) {
   		$result['palamós']=true;
   		unset($result['palamos']);
   }
   
    $result=trim(implode(' ',array_keys($result)));

    if (stripos($result,'copyright_')===false) $result.=' copyright_author';

  	return strtolower($result);
}

function getFilterSQL($filter){
	$filterSQL='';

	if ($filter){
		
		$filter=str_replace('%','',$filter);
		$filter=str_replace('[','',$filter);
		$filter=str_replace(']','',$filter);	
		
		foreach (explode(' ',$filter) as $part){
			
			$part=trim($part);
			
			if (!$part) continue;
		
			if (stripos($part,'tag_')===false)
				if (stripos($part,'codeword_')===false)
			    	$filterSQL.=" AND concat(replace(filename,'/',' '),' ',files.tags,' ') LIKE '% $part %'";
			    else 
			    	$filterSQL.=" AND (concat(' ',files.tags,' ') LIKE '% ".$part." %' OR concat(' ',files.tags,' ') LIKE '% public %')";
			else {
				if (substr($part,0,2)=='no')
				    $filterSQL.=" AND NOT concat(' ',files.tags,' ') LIKE '% ".str_replace('_','\\_',substr($part,6))." %'";
			    else
				$filterSQL.=" AND concat(' ',files.tags,' ') LIKE '% ".str_replace('_','\\_',substr($part,4))." %'";
			}
		
		}
		
	} 
	
	$selection=explode('.',$_SERVER["HTTP_HOST"]);
 	$selection=$selection[0];
 	
 	if ($selection=='www') return $filterSQL;
 	if ($selection=='localhost') return $filterSQL;
 	if ($selection=='148') return $filterSQL;
 	
 	$filterSQL.=" AND concat(replace(filename,'/',' '),' ',files.tags,' ') LIKE '% $selection %'";
 
	return $filterSQL;
}

function getCodeword($filter){
	if (stripos($filter,'codeword_')===false) return 'public';
	
	$filter=explode(' ',$filter);
	
	foreach ($filter as $element){
		if (stripos($element,'codeword_')!==false) return $element;
	}
	
}

function writeLog($log,$message,$relay=false){global $config;
	if (!$relay) $relay=$_SERVER['REMOTE_ADDR'];
	$handle = fopen($config->logsPath.'/'.date("Y-m-d").'.'.$log, "a");
	fwrite($handle, "\n".date("H:i:s").' | '.$relay." || ".$message);
	fclose($handle);

}

function error($message,$kind='fatal'){global $config;
	writelog('error',"ERROR\n$kind\n$message\n");
	die ("ERROR\n$kind\n$message\n");
}

function gzip_start(){
	$encode = getenv("HTTP_ACCEPT_ENCODING");
    if(ereg("gzip",$encode)) ob_start("ob_gzhandler");
    else ob_start();
}

function getETag(){
	$result=mysql_query('SHOW TABLE STATUS');
	$time='';
	while ($line=mysql_fetch_object($result)){
		if ($line->Name!='sessions' && $line->Name!='temp' && $line->Name!='stats' && $line->Name!='resultcache')
		     $time=max($time,$line->Update_time);
	}
	$result=mysql_fetch_object(mysql_query("SELECT UNIX_TIMESTAMP('$time') as stamp"));
	date_default_timezone_set('UTC');
	$time=date("D, d M Y H:i:s",$result->stamp).' GMT';
	return $time;
}

function cache_control(){
	$eTag=getETag();
	if (!(strpos($_SERVER['QUERY_STRING'],'LOGIN')) && isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) &&$_SERVER["HTTP_IF_MODIFIED_SINCE"]==$eTag){
   		header("HTTP/1.1 304 Not Modified");
   		die();
	}

	header('Cache-Control: max-age=0, private, must-revalidate');
	header("Last-Modified: $eTag");
}

$language=false;
function translate($input,$upper=false){global $translations,$words,$language,$config;
	
	if (!$language){
		
		$dir=scandir('translations');
		
		$allowed_langs = array();
		
		foreach($dir as $lang){
			if ($lang[0]=='.') continue;
			$lang=str_replace('.php','',$lang);
			$allowed_langs[]=$lang;
			@include('translations/'.$lang.'.php');
		}
	
		$lang = lang_getfrombrowser ($allowed_langs, 'en', null, false);
		
		$language=$lang;
	}
	
	$output=$input;
	
	if (isset($translations[$language]) && isset($translations[$language][$input])) $output=$translations[$language][$input];
	
	if ($upper) $output=ucfirst($output);
	
	return $output;
}


function translateWords($input){global $words,$translations,$language,$config;

	if (!$language){
		
		$dir=scandir('translations');
		
		$allowed_langs = array();
		
		foreach($dir as $lang){
			if ($lang[0]=='.') continue;
			$lang=str_replace('.php','',$lang);
			$allowed_langs[]=$lang;
			@include('translations/'.$lang.'.php');
		}
	
		$lang = lang_getfrombrowser ($allowed_langs, 'en', null, false);
		
		$language=$lang;
	}
	
	if (!isset($words[$language])) return $input;
	
	$output=' '.$input.' ';
	$output=str_replace('-',' - ',$output);
	$output=str_replace(',',' , ',$output);
	$output=str_replace('.',' . ',$output);
	
	foreach ($words[$language] as $k=>$v){
		$output=str_replace(' '.$k.' ',' '.$v.' ',$output);
	}
	
	$output=str_replace(' - ','-',$output);
	$output=str_replace(' , ',',',$output);
	$output=str_replace(' . ','.',$output);
	
	return trim($output);
}

function ucwords_new($text){   //ucfirst that capitalizes letters after hyphens.
	
	$text=explode('-',$text);
	foreach ($text as $k=>$v){
		$text[$k]=ucwords($v);
	}
	$text=implode('-',$text);
	
	$text=str_replace(' Von ',' von ',$text);
	$text=str_replace(' Van ',' van ',$text);
	$text=str_replace(' Und ',' und ',$text);
	$text=str_replace('Djk ','DJK ',$text);
	
	return ($text);
}

function translateDate($year,$month,$day){
	
	$month=$month-1+1;
	$day=$day-1+1;
	
	$format=translate('YYYY-MM-DD');
	$output=str_replace('YYYY',$year,$format);
	$output=str_replace('MM',$month,$output);
	$output=str_replace('DD',$day,$output);
	return $output;
}

$foldercodewords=array();
function getFolderCodeword($folder){global $foldercodewords;
	
	if (!isset($foldercodewords[$folder])) {
		$foldercodewords[$folder]=md5('muckefuck'.$folder);
	}
	$output=$foldercodewords[$folder];
	
	return 'codeword_direct'.$output;
}

function getAge($input,$relation=false,$showyears=true){
	if (!$relation) $relation=time();
	$calc=$relation-$input;
	$calc=$calc/31556925.261;
	$years=floor($calc);
	$result=$years;
	if ($showyears) $result.=' '.translate('years');
	
	return $result;
}

function addToBreadcrumb($link,$text){global $breadcrumb;
	$element=array();
	$element['link']=$link;
	$element['text']=$text;
	$breadcrumb[]=$element;
}

function between($start,$stop,$string){
	$string=explode('codeword_',$string);
	$string=$string[1];
	$string=explode(' ',$string);
	$string=$string[0];
	return $string;
}

function get_date($folder){
   $d=explode(' ',$folder);
   $d=$d[0];
   $d=explode('-',$d);
   switch (count($d)){
   	  case 6:
   	    $date=translateDate($d[0],$d[1],$d[2])." ".translate('till')." ".translateDate($d[3],$d[4],$d[5]);break;
   	  case 5:
   	    $date=translateDate($d[0],$d[1],$d[2])." ".translate('till')." ".translateDate($d[0],$d[3],$d[4]);break;
   	  case 4:
   	    $date=translateDate($d[0],$d[1],$d[2])." ".translate('till')." ".translateDate($d[0],$d[1],$d[3]);break;
   	  case 3:
   	    $date=translateDate($d[0],$d[1],$d[2]);break;
   	  case 2:
   	    $date="$d[0]-$d[1]";break;
   	  case 1:
   	    $date="$d[0]";break;
   	  default:
   	    $date="";break;
   }
   
   return $date;
}

function timing(){global $startTime;
	echo time()-$startTime;
}

function cleanupCache(){global $config,$user;

	$path=$config->cachePath;
	
	$dir=scandir($path);
	
	$i=0;
	foreach($dir as $filename){
		if ($filename[0]=='.') continue;
		$filename=$path.'/'.$filename;
		$age=floor((time()-filemtime($filename))/60/60/24);
		if ($age>=14){ // check files older than 14 days
			$size=filesize($filename);
			if ($size<100*1024) continue; // do not check small files
			@unlink($filename);
			$i++;
		}
		
	}
}

// Browsersprache ermitteln
function lang_getfrombrowser ($allowed_languages, $default_language, $lang_variable = null, $strict_mode = true) {
        
        if (isset($_GET['lang'])) {
        	$_SESSION['lang']=$_GET['lang'];
        	return $_GET['lang'];
        }
        
        if (isset($_SESSION['lang'])) return $_SESSION['lang'];
        
        if ($lang_variable === null) {
                @$lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        // wurde irgendwelche Information mitgeschickt?
        if (empty($lang_variable)) {
                // Nein? => Standardsprache zurückgeben
                return $default_language;
        }

        // Den Header auftrennen
        $accepted_languages = preg_split('/,\s*/', $lang_variable);

        // Die Standardwerte einstellen
        $current_lang = $default_language;
        $current_q = 0;

        // Nun alle mitgegebenen Sprachen abarbeiten
        foreach ($accepted_languages as $accepted_language) {
                // Alle Infos über diese Sprache rausholen
                $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
                                   '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);

                // war die Syntax gültig?
                if (!$res) {
                        // Nein? Dann ignorieren
                        continue;
                }
                
                // Sprachcode holen und dann sofort in die Einzelteile trennen
                $lang_code = explode ('-', $matches[1]);

                // Wurde eine Qualität mitgegeben?
                if (isset($matches[2])) {
                        // die Qualität benutzen
                        $lang_quality = (float)$matches[2];
                } else {
                        // Kompabilitätsmodus: Qualität 1 annehmen
                        $lang_quality = 1.0;
                }

                // Bis der Sprachcode leer ist...
                while (count ($lang_code)) {
                        // mal sehen, ob der Sprachcode angeboten wird
                        if (in_array (strtolower (join ('-', $lang_code)), $allowed_languages)) {
                                // Qualität anschauen
                                if ($lang_quality > $current_q) {
                                        // diese Sprache verwenden
                                        $current_lang = strtolower (join ('-', $lang_code));
                                        $current_q = $lang_quality;
                                        // Hier die innere while-Schleife verlassen
                                        break;
                                }
                        }
                        // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                        if ($strict_mode) {
                                // innere While-Schleife aufbrechen
                                break;
                        }
                        // den rechtesten Teil des Sprachcodes abschneiden
                        array_pop ($lang_code);
                }
        }

        // die gefundene Sprache zurückgeben
        $_SESSION['lang']=$current_lang;
        return $current_lang;
}

function getImages($folder,$reverse=false){global $userQuery,$filterSQL;

	$reverse=($reverse)?'DESC':'';

	$query="SELECT md5(`key`) as `key`,files.filename as filename, files.tags as tags, filetags.tags as filetags,subfolder,copyright,folder as folderReadable,sortstring  FROM files LEFT JOIN filetags ON files.`key`=filetags.`image` WHERE (($userQuery) AND replace(replace(replace(replace(lower(files.folder),' ',''),'_',''),'.',''),',','') LIKE '$folder' $filterSQL) ORDER BY sortstring $reverse LIMIT 20000";
	
	$search=mysql_query($query);
	
	$output=array();
	
	while($line=mysql_fetch_object($search)){
		$output[]=$line;
	}
	
	return $output;
}

function getAlbums(){global $userQuery,$filterSQL;

	$search=mysql_query("SELECT DISTINCT folder,category, `key` AS thumb, replace(replace(replace(replace(lower(files.folder),' ',''),'_',''),'.',''),',','') as folderID, filename FROM files WHERE $userQuery AND SUBSTR(folder,1,4)<'9' $filterSQL GROUP BY folder ORDER BY folder DESC");
	
	$output=array();
	
	while($line=mysql_fetch_object($search)){
		
		$category=$line->category;
		
		if (strpos($category,'(')!==false) $category='';
		$category=ucwords(str_replace('_',' ',$category));
		
		if ($category=='2018') $category='';
		if ($category=='2017') $category='';
		if ($category=='2016') $category='';
		if ($category=='2015') $category='';
		if ($category=='2014') $category='';
		if ($category=='2013') $category='';
		if ($category=='2012') $category='';
		if ($category=='2011') $category='';
		if ($category=='2010') $category='';
		if ($category=='2009') $category='';
		if ($category=='2008') $category='';
		if ($category=='2007') $category='';
		if ($category=='2006') $category='';
		if ($category=='2005') $category='';
		if ($category=='2004') $category='';
		if ($category=='2003') $category='';
		if ($category=='2002') $category='';
		if ($category=='2001') $category='';
		if ($category=='2000') $category='';
		
		if ($category=='Partnerschaft') $category=translate('Twinning');
		if ($category=='Djk') $category='DJK Rheda';
		if ($category=='Ebr2012') $category='EBR 2012';
		if ($category=='Ftcr') $category='FTCR';
		if ($category=='Diverses') $category='';
		
		$line->category=$category;
		
		$output[$line->folder]=$line;
	}
	
	$search=mysql_query("SELECT folder,md5(`key`) AS thumb FROM files WHERE $userQuery AND tags LIKE '%thumb%' $filterSQL GROUP BY sortstring");
		
	while ($moreInfo=mysql_fetch_object($search)){
		if (!isset($output[$moreInfo->folder])) continue;
		$line=$output[$moreInfo->folder];
		$line->thumb=$moreInfo->thumb;
		$output[$line->folder]=$line;
	}
	
	return $output;

}


?>