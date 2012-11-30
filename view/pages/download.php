<?php

		$pageTitle=pretty($folder);
		
		$pos=strpos($pageTitle,' ');
	    $pageTitle=trim(substr($pageTitle,$pos));
	    
	    if (!$pageTitle || $pageTitle=='%' || $pageTitle=='%%') $pageTitle=translate('download event as ZIP',true);
	    
	    $reverse=($folder=='%')?'DESC':'';
		$order='ORDER BY sortstring';
		
		$random=isset($_GET['random']);
	
		$codewordPossible=false;
		$folderGiven=$folder!='%' && $folder!='%%';
		$tagGiven=stripos($filter,'tag_')!==false;
		$codewordGiven=stripos($filter,'codeword_')!==false;
		
		if (!$folderGiven && $tagGiven) {
			$activePart='tags';
			$element=array();$element['link']='?mode=tags';$element['text']='Stichworte';$breadcrumb[]=$element;
		} else {
			$element=array();$element['link']='?';$element['text']='Events';$breadcrumb[]=$element;
		}
		
		if ($folderGiven && $tagGiven){
			$element=array();$element['link']='?folder='.urlencode($folder);$element['text']=$pageTitle;$breadcrumb[]=$element;
		} else {
			if ($folderGiven){
				$element=array();$element['link']='?folder='.urlencode($folder);$element['text']=$pageTitle;$breadcrumb[]=$element;
				$element=array();$element['link']='';$element['text']=translate('download event as ZIP',true);$breadcrumb[]=$element;
			}
		}
		
		if ($tagGiven) {
			$temp=$filter;
			$temp=str_replace(' ',', ',$temp);
			$temp=str_replace('notag_',translate('not').' ',$temp);
			$temp=str_replace('tag_','',$temp);
			$temp=str_replace('_',' ',$temp);
			$temp=ucwords_new($temp);
			$element=array();$element['link']='';$element['text']=$temp;$breadcrumb[]=$element;
		}
		
		
		
			$files=array();
		
			$search=mysql_query("SELECT `key`,filename  FROM files LEFT JOIN filetags ON files.`key`=filetags.`image` WHERE $userQuery AND files.folder LIKE '$folder' $filterSQL $order $reverse");
			
		    $subfolders=array();
		    $copyrights=array();
			$pages=0;
			$count=0;
		    
			while ($line=mysql_fetch_object($search)){
				{
					$files[]=$line;
					$count++;
				} 
	
			}
		
		
		if ($folderGiven){

			echo '<h1>'.$pageTitle.' <nobr>('.get_date($folder).')</nobr>';
			
			echo '</h1>';
		
		}

		echo '<br>';
		
		if (true){
			
			if (!$files) $files=array();
			
			$functionBar='';
			
			echo '
			
			<div style="width:800px;text-align:left;margin:auto">';
			
			echo translate('Here you can download the entire event in one big ZIP file.');
			echo ' ';
			echo translate('The download may take very long depending on your internet connection.');
			echo ' ';
			echo translate('Please only donwload the ZIP file, if you really need all photos. If you are just interested in a few shots, please use the individual image download instead.');
			
			echo '<p style="font-size:200%;display:block;margin:10px;text-align:center"><a href="download.php?folder='.urlencode($folder).'&amp;filter='.$filter.'">';
			
			$size=0;
			
			foreach ($files as $entry){
		
				$size+=filesize($entry->filename);
								
			}
			
			$mbSize=$size/1024/1024;
			$gbSize=$mbSize/1024;
			
			if ($mbSize>1000){
				echo translate('download',true).' ('.translate('file size',true).': '.(round($gbSize*10)/10).' GiB)';
			} else {
				echo translate('download',true).' ('.translate('file size',true).': '.(round($mbSize*10)/10).' MiB)';
			}
			
			echo '</a></p>';
			
			echo '</div>';
						
		} 
		
?>