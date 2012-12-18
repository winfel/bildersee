<style>
 header {display:none}
 #breadcrumb {display:none}
 #visu {padding:0}
</style>

<?php
    
    if (!$pageTitle || $pageTitle=='%' || $pageTitle=='%%') $pageTitle=translate('search result',true);
	
	$reverse=($folder=='%')?'DESC':'';
	
	$search=mysql_query("SELECT md5(`key`) as `key`,filename,copyright,tags FROM files WHERE $userQuery AND replace(replace(lower(folder),' ',''),'_','') LIKE '$folder' $filterSQL ORDER BY sortstring $reverse");
	
	$prev=false;
	$thisimage=false;
	$next=false;
	$number=0;
	
	$temp=false;
	$i=0;
	while ((!$thisimage || !$next) && $element=mysql_fetch_object($search)){
		if ($thisimage) $next=$element->key;
		if ($element->key==$image) {
			$thisimage=$element;
			$pageTitle=basename($element->filename);
			$filename=$element->filename;
			$pageDescription.=' taken by '.ucwords_new(str_replace('_',' ',$element->copyright));
			if ($temp) $prev=$temp->key;
			$number=$i;
			$tags=trim($element->tags);
			$geo=false;
			if (stripos($tags,'geo_')!==false) {
				$geo=explode('geo_',$tags);
				$geo=$geo[1];
				$geo=explode(' ',$geo);
				$geo=$geo[0];
			}
		}
		$temp=$element;
		$i++;
	}
	
	if (!$thisimage){
		die('No access');
	}

	$mainurl=$config->imageGetterURL.'?key='.$image.'&width=1000000&height=1000';
		echo '<img src="" id="theimage" />';
	
	echo '
	<script>
	
	      var myWidth = 0, myHeight = 0;
		  if( typeof( window.innerWidth ) == "number" ) {
		    //Non-IE
		    myWidth = window.innerWidth;
		    myHeight = window.innerHeight;
		  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		    //IE 6+ in "standards compliant mode"
		    myWidth = document.documentElement.clientWidth;
		    myHeight = document.documentElement.clientHeight;
		  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		    //IE 4 compatible
		    myWidth = document.body.clientWidth;
		    myHeight = document.body.clientHeight;
		  }
		
		var image=document.getElementById("theimage");
		
        var mHeight=myHeight;
		
		image.src="";
		
		image.onload=function(){
			var mHeight=myHeight;
			var isHeight=(image.offsetHeight);
			image.isHeight=isHeight;
			if (isHeight>mHeight){image.style.maxHeight=mHeight+"px";}
			image.style.maxWidth="100%";
			image.style.opacity=1;
			
		}
		
		image.src="'.$mainurl.'";
		
		image.onclick=function(){
			var url="?folder='.urlencode($folder).'&filter='.$filter.'#scroll'.urlencode($image).'";
			location.href=url;
		}
	
	</script>
	
	';	
		
	$filename=$thisimage->filename;
  
  
  // Prefetching of previous and next image
  
	if ($next) {
		$prevURL=$config->imageGetterURL.'?key='.$next.'&width=1000000&height=1000';	
  		$legalShort.= '<img src="'.$prevURL.'" width="1" height="1" onload="showNext();" />';
		$url='?mode=diashow&folder='.urlencode($folder).'&image='.urlencode($next).'&filter='.$filter;
		
		echo '
		<script>
			function showNext(){
				window.setTimeout(function(){
					var url="'.$url.'";
					location.href=url;
				},5000);
			}
		</script>
		';
		
	} else {
		echo '
		<script>
			window.setTimeout(function(){
				var url="?folder='.urlencode($folder).'&filter='.$filter.'#scroll'.urlencode($image).'";
				location.href=url;
			},5000);
		</script>
		';
	}
  

		
?>