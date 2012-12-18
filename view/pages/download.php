<?php

		$pageTitle=pretty($folder);
		
		$pos=strpos($pageTitle,' ');
	    $pageTitle=trim(substr($pageTitle,$pos));
	    
	    if (!$pageTitle || $pageTitle=='%' || $pageTitle=='%%') $pageTitle=translate('download event as ZIP',true);
	    
	    $reverse=($folder=='%')?'DESC':'';
		$order='ORDER BY sortstring';
	
		$folderGiven=$folder!='%' && $folder!='%%';
		$tagGiven=stripos($filter,'tag_')!==false;
		$codewordGiven=stripos($filter,'codeword_')!==false;
		
		$element=array();$element['link']='?';$element['text']='Events';$breadcrumb[]=$element;
		
		
			$files=array();
		
			$search=mysql_query("SELECT md5(`key`) as `key`,filename,folder  FROM files LEFT JOIN filetags ON files.`key`=filetags.`image` WHERE $userQuery AND replace(replace(lower(files.folder),' ',''),'_','') LIKE '$folder' $filterSQL $order $reverse");
			
		    $subfolders=array();
		    $copyrights=array();
			$pages=0;
			$count=0;
		    
			while ($line=mysql_fetch_object($search)){
				{
					$files[]=$line;
					$count++;
					
					if ($count==1 && $folderGiven && !($folder=='%' || $folder=='%%')) {
						$pageTitle=pretty(trim(substr($line->folder,strpos($line->folder,' '))));
						addToBreadcrumb('?folder='.urlencode($folder),$pageTitle);
						$element=array();$element['link']='';$element['text']=translate('download event as ZIP',true);$breadcrumb[]=$element;

					}
					
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