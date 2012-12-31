<style>
 html {background:black;height:100%;overflow:hidden}
 header {display:none}
 footer {display:none}
 #breadcrumb {display:none}
 #visu,#maincontent {padding:0;height:100%}
 img {
	  opacity:0;
 }

</style>

<?php
    
    if (!isset($config) || !isset($config->hash) || !isset($securityHash) || $securityHash!=$config->hash) die ('<h1>Forbidden!</h1>');
    
    $legalShort='';
    
    if (!$pageTitle || $pageTitle=='%' || $pageTitle=='%%') $pageTitle=translate('slideshow',true);
	
	$reverse=($folder=='%')?'DESC':'';
	
	$search=mysql_query("SELECT md5(`key`) as `key` FROM files WHERE $userQuery AND replace(replace(replace(replace(lower(files.folder),' ',''),'_',''),'.',''),',','') LIKE '$folder' $filterSQL ORDER BY sortstring $reverse");
	
	$allImages=array();
	while ($line=mysql_fetch_object($search)){
		$allImages[]=$line->key;
	}  

	$allImages=json_encode($allImages);
	
	$image=(isset($_GET['image']))?$_GET['image']:false;
	
	echo "
	
	<img src=\"\" alt=\"preloader\" id=\"preloader\" onload=\"nextImage()\" width=\"100\" height=\"100\" style=\"opacity:0\">
	
	<script>
	
		var allImages=$allImages;
		var imageCount=allImages.length;
		var startTime=0;
		var delay=5000;
		
		var image=".(($image)?"'".$image."'":'false').";
		var position=false;
		
		if (image){
			for (var i in allImages){
				if (allImages[i]==image){
					position=i;
					break;
				}
			}
		}
		
		function random(min,max){
			return Math.floor(Math.random() * (max - min)) + min;
		}
		
		function randomImage(){
			var random= Math.floor(Math.random() * (allImages.length));
			return allImages[random];
		}
		
		function nextImageURL(){
			if (!image) return randomImage();
			
			var key=allImages[position];
			
			position++;
			if (position==imageCount) position=0;
			
			return key;
		}
		
		var nextTimeout=false;
		function nextImageInt(){
			var myWidth = screen.availWidth, myHeight = screen.availHeight;
			if (window.orientation==90 || window.orientation==-90){
				var t=myWidth;myWidth=myHeight;myHeight=t;
			}
			
			
			var url='".($config->imageGetterURL)."?key='+nextImageURL()+'&width='+myWidth+'&height='+myHeight;
			document.getElementById('preloader').src=url;
			startTime=getTime();
			if (nextTimeout){
				window.clearTimeout(nextTimeout);
				nextTimeout=false;
			}
			nextTimeout=window.setTimeout(function(){
				message('".translate('Network disruption! Trying the next image.')."');
				nextImageInt();
			},30000);
		}
		
		function nextImage(){
			
			var pause=delay-(getTime()-startTime);
			if (pause<0) {
				message('".translate('Slideshow is running slower due to slow network response time!')."');
				nextImageInt();
				
			} else {
				window.setTimeout(function(){nextImageInt();},pause);
			}
			
			var degrees=random(-10,10);
			
			var visu=document.getElementById('visu');
			var preloader=document.getElementById('preloader');
			var newImage = document.createElement('img');
			newImage.src=preloader.src;
			var style=newImage.style;
			window.setTimeout(function(){
				style.opacity='1.0';
			},1000)
		    
		    var top=random(0,2);
		    var left=random(0,2);
		    
		    style.position='absolute';
		    if (top){;
		    	style.top='15%';
		    } else {
		    	style.bottom='15%';
		    }
		    if (left){
		    	style.left=random(0,20)+'%';
		    } else {
		    	style.right=random(0,20)+'%'
		    }
		    style.maxWidth=(75)+'%';
		    style.maxHeight=(80)+'%';
		    style.height='auto'; //IE
		    style.width='auto'; //IE
		    style.transform='rotate('+degrees+'deg)';
            style.webkitTransform='rotate('+degrees+'deg)'; /* Safari and Chrome */
            style.mozTransform='rotate('+degrees+'deg)'; /* Firefox */
            style.msTransform='rotate('+degrees+'deg)'; /* Firefox */
		    style.boxShadow='0px 0px 20px #000';
		    style.border='10px solid white';
		    style.background='white';
			newImage.className='slide';
			visu.appendChild(newImage);
			
			//fade out background
			
			var temp=document.getElementsByTagName('img');
			var images=[];
			for (var i=temp.length-1;i>=0;i--){
				var image=temp[i];
				if (image.className!='slide' && image.className!='fadeout' && image.className!='disappear') continue;
				images.push(image);
			}


			for (var i in images){
				var image=images[i];
				var opacity=2-(i*0.2);
				if (opacity<=0.3){
					image.className='fadeout';
					image.style.opacity=opacity;
				}
				if (opacity<=0){
					image.className='disappear';
					removeImage(image);
				}
			}
			
		}
		
		function removeImage(image){
			window.setTimeout(function(){
						try{
							document.getElementById('visu').removeChild(image);
						} catch (e){
							//console.log('Image already gone');
						}
						delete(image);
			},15000);
		}
		
		function getTime(){
			return new Date().getTime();
		}
		
		nextImageInt();
		message('".translate('Press ENTER to view in fullscreen. Press ESC or double click to leave the slideshow.')."');

	document.addEventListener('keydown', function(e) {
	  switch (e.keyCode){
	  	case 13:toggleFullScreen();break;
	  	case 27:history.back();break;
	  	case 187:
	  	case 171:plus();break;
	  	case 189:
	  	case 173:minus();break;
	  	default:break;
	  }
	}, false);
	
	document.addEventListener('dblclick',function(e){
		history.back();
	},false);
	
	function plus(){
		delay+=500;
		message('Delay '+(delay/1000)+'s');
	}
	function minus(){
		delay-=500;
		if (delay<=3000) delay=3000;
		message('Delay '+(delay/1000)+'s');
	}
	

		
	</script>
	
	";
?>