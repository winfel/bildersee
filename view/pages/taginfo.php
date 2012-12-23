<?php

if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');


if (!$userIsAdmin) die();

$pageTitle='Tag-Information';

$from=isset($_GET['from'])?trim($_GET['from']):false;
$to=isset($_GET['to'])?trim($_GET['to']):false;

if ($from && $to && $from!=$to){
	
	$request="UPDATE filetags SET tags=trim(REPLACE(concat(' ',tags,' '), ' $from ', ' $to ')) WHERE concat(' ',tags,' ') LIKE '% $from %'";
	mysql_query ($request);
	
	$request="UPDATE files SET tags=trim(REPLACE(concat(' ',tags,' '), ' $from ', ' $to ')) WHERE concat(' ',tags,' ') LIKE '% $from %'";
	mysql_query ($request);
	
	$request="UPDATE autotags SET tag='$to' WHERE tag='$from'";
	mysql_query ($request);
	
	$request="UPDATE tags_implied SET tag='$to' WHERE tag='$from'";
	mysql_query ($request);
	
	$request="UPDATE tags_implied SET implied=trim(REPLACE(concat(' ',implied,' '), ' $from ', ' $to ')) WHERE concat(' ',implied,' ') LIKE '% $from %'";
	mysql_query ($request);
	
}

$search=mysql_query("SELECT tags FROM files WHERE $userQuery AND SUBSTR(folder,1,4)<'9' $filterSQL");
		
		$tags=array();
		
		 $max=0;
 
		 while($line=mysql_fetch_object($search)){
		 
		    $folder='';//$tags->folder;
		    $string=$line->tags;
		    
		    $string=explode(' ',$string);
		    
		    foreach ($string as $tag){
		    	if ($tag=='') continue;
		    	if ($tag=='gk') continue;
		    	if ($tag=='archiv') continue;
		    	if ($tag=='person') continue;
		    	if ($tag=='noperson') continue;
		    	if ($tag=='public') continue;
		    	if ($tag=='privat') continue;
		    	if ($tag=='thumb') continue;
		    	if ($tag=='top') continue;
		    	if ($tag=='rotate') continue;
		    	if ($tag=='rotatel') continue;
		    	if ($tag=='rotater') continue;
		    	if ($tag=='norotate') continue;
		    	if ($tag=='norandom') continue;
		    	if ($tag=='gruppe') continue;
		    	if (stripos($tag,'codeword_')===0) continue;
		    	if (stripos($tag,'copyright_')===0) continue;
		    	if (stripos($tag,'year_')===0) continue;
		    	@$tags[$tag]++;
		    	$max=max($max,$tags[$tag]);
		    }
		    
		    //echo "$folder $tags<br />";
		 }
		 
		 ksort($tags);

		$search=mysql_query("SELECT DISTINCT sortstring FROM files");
		
		$foldertags=array();
		$done=array();
 
		 while($line=mysql_fetch_object($search)){
		 	$folder=dirname($line->sortstring);
		 	if (isset($done[$folder])) continue;
		 	$done[$folder]=true;
		 	$temp=explode('[',$folder);
		 	foreach ($temp as $temp2){
		 		if (stripos($temp2,']')===false) continue;
		 		$temp2=explode(']',$temp2);
		 		$temp2=$temp2[0];
		 		$temp2=explode(' ',$temp2);
		 		foreach ($temp2 as $tag){
		 			$foldertags[$tag][]=$folder;
		 		}
		 	}
		 }

		 
		 $minimum=8;
		 $delta=4;
		 $even=false;
		 
		 echo '<table style="margin:auto;text-align:left">
		 <tr style="background:#aaa"><th>Tag</th><th>Ordner</th><th>Anzahl</th><th>ähnlich</th><th>Operationen</th></tr>';
		 foreach ($tags as $tag=>$amount){
		 	
		 	$similars=array();
		 	
		 	{
			 	foreach (array_keys($tags) as $compare){
			 		if ($tag==$compare) continue;
			 		
			 		if (strlen($tag)<$minimum) continue;
			 		if (strlen($compare)<$minimum) continue;
			 		if ($tag==$compare) continue;
			 		$value=levenshtein ($tag,$compare);
			 		if ($value<$delta) {
			 			$similars[]='<a href="?mode=taginfo&from='.$tag.'&to='.$compare.'">'.$compare.'</a>';
			 		}
			 	}
		 	}
		 	
		 	$foldertag='';
		 	if (isset($foldertags[$tag])){
		 		$data=implode(', ',$foldertags[$tag]);
		 		$foldertag='<span title="'.$data.'">Ja</span>';
		 	}
		 	
		 	$operations='';
		 	
		 	if (!$foldertag){
		 		$operations='<a href="?mode=tagchange&from='.$tag.'">bearbeiten</a>';
		 	}
		 	
		 	$even=!$even;
		 	
		 	$color='';
		 	
		 	if ($even){
		 		$color='style="background:white"';
		 	}
		 	
		 	echo '<tr '.$color.'><th> '.$tag.'</th><td>'.$foldertag.'</td><td>'.$amount.'</td><td>'.implode(', ',$similars).'</td><td>'.$operations.'</td></tr>';
		 	
		 }
		 echo '</table>';


?>