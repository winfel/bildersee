<?php

$pageTitle='Festplattenstatistik';

 $statistics=array();
 $count=array();
 $query=mysql_query('SELECT * FROM files');
 
 while ($result=mysql_fetch_object($query)){
 	$filename=$result->filename;
 	$category=$result->category;
 	$folder=$result->folder;
 	$year=substr($result->sortstring,1,4);
 	if ($year==0) $year='kein Jahr';
 	
 	$size=filesize($filename);
 	
 	if ($year!=$category) {
 		@$statistics['Cat '.$category]+=$size;
 		@$count['Cat '.$category]+=1;
 	}
 	@$statistics['Year '.$year]+=$size;
 	@$statistics['Folder '.$folder]+=$size;
 	@$statistics[' -- TOTAL --']+=$size;
 	
 	@$count['Year '.$year]+=1;
 	@$count['Folder '.$folder]+=1;
 	@$count[' -- TOTAL --']+=1;

 }
 
 ksort($statistics);
 
 foreach (array_keys($statistics) as $category){
 		$statistics[$category]=(round($statistics[$category]/1024/1024/1.024)/1000).'GiB '.$count[$category].'files  average '.(round($statistics[$category]/$count[$category]/1024/102.4)/10).' MB';
 }

 echo '<table style="margin:auto">';
 foreach ($statistics as $category=>$amount){
 	echo "<tr><td style=\"text-align:left\">$category</td><td style=\"text-align:right\">$amount </td></tr>";
 }
 echo '</table>';

?>