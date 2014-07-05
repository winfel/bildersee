<!DOCTYPE HTML>
<html>
 <head>
  <title>Bildersee - Bildersee</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=0;" />
  <meta name="format-detection" content="telephone=no">
  <script type="text/javascript" src="zxml.js.php"></script>
  <script type="text/javascript" src="qrcode.min.js"></script>
  <script type="text/javascript" src="jsfunctions.js?version=092912"></script>
  <style>
  	html,body {height:100%;width:100%;overflow:hidden;background:black;color:white;padding:0;margin:0;}
  	#id {display:none;position:absolute;top:0;padding-top:10%;left:0;width:100%;height:100%;background-color:white;font-size:60px;text-align:center;color:black;}
  	#image1,#image2 {opacity:0;position:absolute;top:0:left:0;width:100%;height:100%;text-align:center;
  	transition: opacity 2s;
    -webkit-transition: opacity 2s;
    -moz-transition: opacity 2s;
    -o-transition: opacity 4s;}
  	
    #image1 img,#image2 img {max-width:100%;max-height:100%;}
    
    #qrcode {margin:auto;width:256px;margin-top:10px;}
  </style>
  </head>
  <body>
<?php

 //var_dump($_SERVER);
 //die;

 include('environment.php');

 $id=(isset($_GET['id']))?$_GET['id']:substr(uniqid(),-7);

?>
<script>

var getterLocked=false;
var oldData=false;


var i=0;
function getPresenterData(){
	i++;
	if (getterLocked) {
		return;
	}
	getterLocked=true;
	var id='<?php echo $id ?>';
	var time=new Date().getTime();;
	server_query("getPresenterData.php?id="+id+"&time="+time,function(value){
		getterLocked=false;
		if (value!=oldData) processData(value);
		oldData=value;
	 });
}

var num=2;
function processData(data){
	if (data=='showid') {
		var div=document.getElementById('id');
		div.innerHTML='Target code: <?php echo $id ?><div id="qrcode"></div>';
		div.style.display='block';
		var url=("<?php echo $config->viewURL;?>/?target=<?php echo $id ?>");
		new QRCode(document.getElementById("qrcode"), url);
	} else {
		var div=document.getElementById('id');
		div.innerHTML='<?php echo $id ?>';
		div.style.display='none';
		
		if (num==2) num=1; else num=2;
		div=document.getElementById('image'+num);
		div.innerHTML='<img src="<?php echo $config->imageGetterURL; ?>?key='+data+'&width=1000000&height=1080" onload="show('+num+');">';
	}
}

function show(what){
	
	if (what==1) var other=2; else other=1;
	
	document.getElementById('image'+what).style.opacity=1;
	document.getElementById('image'+other).style.opacity=0;
	 
}

window.setInterval(function(){getPresenterData();},1000);

</script>
<div id="image1">
Waiting.
</div>
<div id="image2">
Waiting.
</div>
<div id="id">
Waiting.
</div>
</body>
</html>