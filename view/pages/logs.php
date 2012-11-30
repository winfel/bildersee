<?php

$pageTitle='Logfiles';

$activePart='admin';

	 @$updatelog=reverse(file_get_contents($config->logsPath.'/'.date("Y-m-d").'.update'));
	 @$errorlog=reverse(file_get_contents($config->logsPath.'/'.date("Y-m-d").'.error'));
	 @$loginlog=reverse(file_get_contents($config->logsPath.'/'.date("Y-m-d").'.login'));
	 @$querylog=reverse(file_get_contents($config->logsPath.'/'.date("Y-m-d").'.query'));
	 @$viewlog=reverse(file_get_contents($config->logsPath.'/'.date("Y-m-d").'.view'));

	  $output='<h3>Betrachter</h3>'
	 .'<div style="height:200px;overflow:scroll;background:#eee;text-align:left"><pre>'
	 .$viewlog
	 .'</pre></div>';
	 
	  $output.='<h3>Externe Anfragen</h3>'
	 .'<div style="height:200px;overflow:scroll;background:#eee;text-align:left"><pre>'
	 .$querylog
	 .'</pre></div>';	 
	  
	  $output.='<h3>Anmeldevorgänge</h3>'
	 .'<div style="height:200px;overflow:scroll;background:#eee;text-align:left"><pre>'
	 .$loginlog
	 .'</pre></div>';
	  
	  $output.='<h3>Updates</h3>'
	 .'<div style="height:200px;overflow:scroll;background:#eee;text-align:left"><pre>'
	 .$updatelog
	 .'</pre></div>'; 
	  
	  $output.='<h3>Fehlermeldungen</h3>'
	 .'<div style="height:200px;overflow:scroll;background:#eee;text-align:left"><pre>'
	 .$errorlog
	 .'</pre></div>';
	 
	 echo $output;
	 
	 function reverse($input){
	 	$input=trim($input);
	 	$input=explode("\n",$input);
	 	$input=array_reverse($input);
	 	$input=implode("\n",$input);
	 	return $input;
	 }
		
?>