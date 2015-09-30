<?php
  session_start();
?><html>
<head>
<title>Bildersee Public Uploader</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
* {font-family:verdana,arial,sans-serif;}

 body {font-family:'Gill Sans',tahoma,geneva,sans-serif;font-size:13px;
       color:black;background:#d3d3d3;padding:20px;
      }
      
 a {color:black;font-weight:bold;}
 
 a:hover {color:darkblue}
 
 p {color:#666666;max-width:500px}
 
 #cats {margin:20px;margin-left:30px;line-height:20px}
 
 .box {border:1px solid black;width:300px;float:right;padding:10px;margin:10px;clear:right;background:#EEEEEE}
 
 .box h2 {font-size:14px;margin-top:0}
 
 p.fuss {max-width:100%;text-align:center;clear:both;color:black;border-top:1px dotted #999999;padding-top:5px}
 
</style>
</head>
<body>

<div style="width:300px;float:right;clear:right;margin-right:10px;margin-top:-10px;text-align:right;font-size:30px">
bildersee.eu
</div>

<?php

   include ('../server/environment.php');
   
   $category='web';
   $language='de';
   $album=(isset($_REQUEST['album']))?$_REQUEST['album']:'';
   
   $rights=("Die von dir hochgeladenen Bilder bleiben Eigentum des Fotografen. Mit dem Hochladen auf dieser Seite best&auml;tigst du, die Rechte an den hochgeladenen Bildern zu besitzen. Ferner erteilst du mir die wiederrufbare, nicht exklusive Erlaubnis, die Bilder auf der Website bildersee.eu ver&ouml;ffentlichen zu d&uuml;rfen. Ich behalte mir vor, einzelne Bilder zu l&ouml;schen oder f&uuml;r den &ouml;ffentlichen Zugriff zu sperren.
            
Du kannst die Ver&ouml;ffentlichungserlaubnis jederzeit widerrufen. Ich l&ouml;sche die Bilder dann umgehend.
            
Das Recht von auf den Bildern abgebildeten Personen muss gewahrt bleiben. Dies bedeutet, dass Personen, die pers&ouml;nlich erkennbar sind, mit der Ver&ouml;ffentlichtung der Bilder einverstanden sein m&uuml;ssen. Mit dem Hochladen der Bilder best&auml;tigst du, diese Einverst&auml;ndnis nach bestem Wissen und Gewissen eingeholt zu haben. Sollte einer Ver&ouml;ffentlichung der Bilder im Nachhinein widersprochen werden, teile mir dies bitte unverz&uuml;glich mit. Ich werde die Bilder dann umgehend sperren bzw. l&ouml;schen.");
   
   if (strpos($category,'..')!==false || strpos($category,'/')!==false)
   die ('Hackversuch');
   
   $publicBoxes='<div class="box"><h2>Public upload</h2>'
   				.'On this website you can upload images for the Bildersee database. Images are published after inspection to avoid abuse!'
   	            .'</div>';
   
{
      
      if (!$album){
      	if (!file_exists($config->uploadPath.'/'.$category)) {
      		mkdir($config->uploadPath.'/'.$category);
      		@chmod($config->uploadPath.'/'.$category, 0777);
      	}
      	
      	echo $publicBoxes;
      	
      	echo '<div class="box"><h2>Rechtliches</h2><small>'.str_replace("\n",'<br>',$rights).'</small></div>';
      	
      	echo '<form method="post" action="">';
      	
      	echo '<input name="category" value="'.$category.'" type="hidden" />';
      	
      	echo '<input name="lang" value="'.$language.'" type="hidden" />';
      	      	
      	echo '<h1>Album Title:</h1>';
      	
      	echo '<p>Give your album a title. This title should include the date when the pictures were shot as well as a few important keywords</p>';
      	
      	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Title: <input name="album[name]" type="text" value="" size="40" /><br />';
      	      	
      	echo '<h1>Copyright:</h1>';
      	
      	echo '<p>Images published on Bildersee get a copyright notice. Please enter your name below for this copyright information.</p>';
      	
      	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name: <input name="album[uploader]" type="text" value="" size="40" /><br />';
      	
      	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E-Mail: <input name="album[uploadermail]" type="text" value="" size="40" />';
      	
      	
      	echo '<p style="text-align:center;margin-top:30px"><input type="submit" value="&gt;&gt;&gt;&gt;&gt; Upload images now! &gt;&gt;&gt;&gt;&gt;&gt;" /></p>';
      	
      	echo '</form>';
      }
      
      else {
      	   
      	   $foldername=$album['name'];
      	   
      	   if ($foldername=='') $foldername=time();
      	   
      	   $foldername=trim($category.'/'.replaceCrazyLetters(removeUmlauts($foldername)));
      	   
      	   if (!file_exists($config->uploadPath.'/'.$foldername)) {
      	   	   mkdir($config->uploadPath.'/'.$foldername);
      	   	   //die($config->uploadPath.'/'.$foldername);
      	   	   @chmod($config->uploadPath.'/'.$foldername, 0777);
      	   }
      	
      	   $_SESSION['folder']=$foldername;
      	   $_SESSION['album']=$album;
      	   
      	   echo '<h1>Upload files</h1>';
      	   
      	   echo '<p><b>You can change the language of the uploader by clicking on the flag!</b> Puedes cambiar la lengua del cargador al hacer un clic en la bandera!</p>';
      	   
      	   echo '<p>Now you can upload images. Just click on "Add files" and slect the files you want to upload. A click on "Upload" starts the upload process.<br /><br /><b>Note:</b>The upload can take some time depending on the size and quantity of your images as well as your internet connection.</p>';
		   
		   $content='<script type="text/javascript" src="swfobject.js"></script>
<script type="text/javascript">
   document.write(\'<div id="flashcontainer"><div id="flashcontent"><div class="noflash">TWG Flash Uploader requires at least Flash 8.<br>Please update your browser.</div></div></div>\');
var flashvars = {};
// - The following 3 lines would make a blue header bar with white text if you have a professional license or above
 flashvars.c_header_bg = "7777FF";
 flashvars.c_header_bg_2 = "0000FF";
 flashvars.c_text_header = "FFFFFF";

// Please read the description above about this settings.
// flashvars.tfu_description_mode="true";
// flashvars.hide_remote_view="true";
// flashvars.big_server_view="true";
// flashvars.show_server_date_instead_size="true";

var params = {};
// needed for fullscreen 
params.allowfullscreen = "true";
params.scale = "noScale";
var attributes = { id: "flash_tfu", name: "flash_tfu", lang:"en" };

swfobject.embedSWF("tfu_214.swf?lang=en", "flashcontent", "650", "340", "8.0.0", "", flashvars, params, attributes);
// Use this if you want to use the preloader
// swfobject.embedSWF("tfu_preloader.swf", "flashcontent", "650", "340", "8.0.0", "", flashvars, params, attributes);
    
</script>
<!-- end include with Javascript -->
<!-- static html include -->
<noscript>
<object name="mymovie" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="650" height="340"  align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="tfu_214.swf?lang=en" /><param name="quality" value="high" /><param name="lang" value="en" /><param name="bgcolor" value="#ffffff" /><param name="scale" value="noScale" /><param name="allowFullScreen" value="true" /><embed src="tfu_214.swf" name="mymovie" quality="high" bgcolor="#ffffff" width="650" height="340" align="middle" scale="noScale" allowfullscreen="true" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</noscript>';
		
		   echo $content;
      	
      	
      }
      
   }

?>
<p class="fuss">&copy; 2011, Felix Winkelnkemper - Wei&szlig;es Venn 1 - 33442 Herzebrock-Clarholz<br />
Telephone: 0049 151 40 43 21 21 - winkelnkemper@googlemail.com - ICQ:54428358</p>
</body>
</html>

