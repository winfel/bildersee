<style>
 html {background:black;height:100%;overflow:hidden}
 header {display:none}
 footer {display:none}
 #breadcrumb {display:none}
 #visu {padding:0;height:100%}
 img {
 	  -webkit-transition-property: opacity;
	  -webkit-transition-duration: 1s;
	  -webkit-transition-delay: 2s;
	  -moz-transition-property: opacity;
	  -moz-transition-duration: 1s;
	  -moz-transition-delay: 2s;
	  -o-transition-property: opacity;
	  -o-transition-duration: 1s;
	  -o-transition-delay: 2s;
	  transition-property: opacity;
	  transition-duration: 1s;
	  transition-delay: 2s;
	  opacity:0;
 }
 
 img.fadeout{
 	  -webkit-transition-property: opacity;
	  -webkit-transition-duration: 10s;
	  -webkit-transition-delay: 4s;
	  -moz-transition-property: opacity;
	  -moz-transition-duration: 10s;
	  -moz-transition-delay: 4s;
	  -o-transition-property: opacity;
	  -o-transition-duration: 10s;
	  -o-transition-delay: 4s;
	  transition-property: opacity;
	  transition-duration: 10s;
	  transition-delay: 4s;
	  opacity:1;
 }
 
 img.disappear{
 	  -webkit-transition-property: opacity;
	  -webkit-transition-duration: 12s;
	  -webkit-transition-delay: 0s;
	  -moz-transition-property: opacity;
	  -moz-transition-duration: 12s;
	  -moz-transition-delay: 0s;
	  -o-transition-property: opacity;
	  -o-transition-duration: 12s;
	  -o-transition-delay: 0s;
	  transition-property: opacity;
	  transition-duration: 12s;
	  transition-delay: 0s;
	  opacity:1;
 }
 
 #message {
	  -webkit-transition-property: opacity;
	  -webkit-transition-duration: 2s;
	  -webkit-transition-delay: 0s;
	  -moz-transition-property: opacity;
	  -moz-transition-duration: 2s;
	  -moz-transition-delay: 0s;
	  -o-transition-property: opacity;
	  -o-transition-duration: 2s;
	  -o-transition-delay: 0s;
	  transition-property: opacity;
	  transition-duration: 2s;
	  transition-delay: 0s;
	  background:rgba(0,0,0,0.8);
	  width:400px;
	  padding:20px;
	  position:absolute;
	  bottom:50px;
	  left:50%;
	  margin-left:-210px;
	  font-size:20px;
	  text-align:left;
	  z-index:1000;
	  opacity:1;
  }
</style>

<div id="message"></div>

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
			alert(i);
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
		  
			var myWidth = screen.availWidth, myHeight = screen.availHeight;
			var pause=delay-(getTime()-startTime);
			if (pause<0) {
				var newdelay=Math.ceil((delay-pause)/1000);
				message('".translate('Slideshow is running slower due to slow network response time!')."');
				pause=0;
				
			}
			window.setTimeout(function(){nextImageInt();},pause);
			
			var degrees=random(-15,15);
			
			var visu=document.getElementById('visu');
			var preloader=document.getElementById('preloader');
			var newImage = document.createElement('img');
			newImage.src=preloader.src;
			var style=newImage.style;
			window.setTimeout(function(){
				style.opacity='1.0';
			},100)
		    
		    var top=random(0,2.2);
		    var left=random(0,2.2);
		    
		    style.position='absolute';
		    if (top){;
		    	style.top='50px';
		    } else {
		    	style.bottom='50px';
		    }
		    if (left){
		    	style.left=random(0,myWidth/3)+'px';
		    } else {
		    	style.right=random(0,myWidth/3)+'px'
		    }
		    style.maxWidth=(myWidth/1.5)+'px';
		    style.maxHeight=(myHeight)+'px';
		    style.transform='rotateZ('+degrees+'deg)';
            style.webkitTransform='rotateZ('+degrees+'deg)'; /* Safari and Chrome */
            style.mozTransform='rotateZ('+degrees+'deg)'; /* Firefox */
		    style.boxShadow='0px 0px 10px #000';
		    style.border='10px solid white';
		    style.background='white';
		    //style.maxHeight=(myHeight/2)+'px';
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
				var opacity=1-(i*0.2);
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
		
function toggleFullScreen() {
  if ((document.fullscreenElement && document.fullscreenElement !== null) ||    // alternative standard method
      (!document.mozFullScreenElement && !document.webkitFullscreenElement)) {  // current working methods
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.cancelFullScreen) {
      document.cancelFullScreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitCancelFullScreen) {
      document.webkitCancelFullScreen();
    }
  }
}

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
	
	var hider=false;
	function message(text){
		var el=document.getElementById('message');
		el.style.opacity='1.0';
		el.innerHTML=text;
		if (hider){
			window.clearTimeout(hider);
			hider=false;
		}
		hider=window.setTimeout(function(){
			el.style.opacity='0.0';
		},3000);
	}
		
	</script>
	
	";
?>