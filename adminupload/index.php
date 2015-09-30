<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="http://www.tinywebgallery.com/favicon.ico" type="image/x-icon">
<title>Flash upload | file uploader | TWG Flash Uploader 3.2</title>
<meta name="description" lang="en" content="This is the demo for the TWG Flash Uploader which is a user friendly flash to upload and manage many files very fast and easy.">
<script type="text/javascript">


function getTFUFormData(fields) {
  var validateOk = true;
  var spacer = String.fromCharCode(4);
  // You have to return doNotUpload if you e.g. have mandatory fields and they are not filed.
  // add the check to this function and return 'doNotUpload'. Then the upload is not started.
  // noone should enter this
  var doNotUpload = String.fromCharCode(5) + String.fromCharCode(4) + String.fromCharCode(5);
  
  var result="";
  var sarray = fields.split(",");
  for (var i = 0; i < sarray.length; ++i) {
     if (document.getElementById(sarray[i])) {
       result += document.getElementById(sarray[i]).value;
     }
     result += spacer;
  }
  // if you validate and the validation fails return doNotUpload to prevent the upload
  if (validateOk) {
    return result;
  } else {
    return doNotUpload;
  }
}

/* All connection errors are wrapped in TFU.
   For enhanced debugging it is helpful to see the real error messages.  
   Only use the part below for debuging!
*/
function debugError(errorString) {
  alert(errorString);
}

// Used for the JFUploader plugin!
function setImage(index, name, x , y) {
}

/*
 You can refresh the file list by Javascript. This is e.g. used in WFU where the thumbnails are generated
 in an extra step and to keep the listing of the flash and the file in synch.  
*/
function refreshFileList() {
   var obj = document.getElementById("flash_tfu");     
   if (obj && typeof obj.refreshFileList != "undefined") {
      obj.refreshFileList();
   }
} 

/**
 * This is the function you have modify the return value if you use IDN-Domains
 * Please read the howto about IDN in the TFU FAQ 20.
 * The standalone version need the alias url + the full path to the tfu folder.
 * The Joomla, Wordpress and TWG version has config parameters which are 
 * explained in the howto as well.    
 */ 
function getIDN() {
  return "";
}

/**
This function is only here to demonstrate the different languages of TWG
Normaly you add this parameter directly like shown below in the code.
*/
function changeLanguage(lang) { 
var flashvars = {};
var params = {};
params.allowfullscreen = "true";
params.scale = "noScale";
var attributes = { id: "flash_tfu", name: "flash_tfu" };

flashvars.lang=lang;

document.getElementById("flashcontainer").innerHTML = "<div id=\"flashcontent\">Loading</div>";
swfobject.embedSWF("tfu_3.2.swf", "flashcontent", "650", "340", "8.0.0", "", flashvars, params, attributes);
}
</script>

<style type="text/css">
.noflash { padding:10px; margin:10px; border: 1px solid #555555; background-color: #fefefe; text-align:left; width:430px; -moz-border-radius: 15px; border-radius: 15px; }
.noflash p { text-align: center;}
.small {  font-size: 11px; margin:2px; }
div.flashcontent { padding:0;margin:0;border:none;}
a { color: #000099; text-decoration: none;  font-weight: normal; }
a visited { color: #000099;}
a link { color: #000099;}
a hover { color: #999999;}
body,table { font-family : Verdana,Lucida,sans-serif; font-size: 12px; margin:20px;}
h1 { background-image:url(http://www.tinywebgallery.com/buttons/logo.png); background-repeat:no-repeat; height:60px; font-size: 30px; font-family: Georgia,Times,"Times New Roman",serif; padding-top:15px; padding-left: 70px;}
h2 { background-color : #eeeeee; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000000; padding: 2px 2px 2px 10px; }

.style1 {
	color: #FF0000;
	font-weight: bold;
}
</style>
</head>
<body bgcolor="#ffffff" onLoad="if (document.getElementById('mymovie')) document.getElementById('mymovie').focus()">
<h1>Bildersee Upload</h1>
<center>
<div style="height:370px">
  <!--


Here Is the code how to include the flash!


-->
  <!-- include with javascript - best solution because otherwise you get the "klick to activate border in IE" -->
  <script type="text/javascript" src="swfobject.js"></script>
  <script type="text/javascript">
   document.write('<div id="flashcontainer"><div id="flashcontent"><div class="noflash"><p>The flash could not be loaded.</p> Most likely one of the following issues causes the problem: <ul><li>There is a Javascript error on the page</li><li>The Javascript that loads the flash was overwritten</li><li>The flash plugin is not installed.</li></ul> Solutions:<ul><li>Fix any Javascript errors</li><li>Include the flash with the object tag.</li><li>Install/update the flash plugin</li><li>See: <a href="http://www.tinywebgallery.com/en/tfu/tfu_faq_1.php" target="_blank">TFU FAQ 1</a></li></ul></div></div></div>');
var flashvars = {};
flashvars.lang = "de";
// - The following 3 lines would make a blue header bar with white text if you have a professional license or above
// flashvars.c_header_bg = "7777FF";
// flashvars.c_header_bg_2 = "0000FF";
// flashvars.c_bg = "FFFFFF";
// flashvars.c_bg_transparent = "true";  //  flashvars.c_bg = "FFFFFF"; is also needed!
// The following lines are for the settings for the Add and the Upload button. 
// This buttons can be styled different if you have a professional license or above 
// flashvars.c_add_bt_color = "000000";
// flashvars.c_add_bt_bg_color = "FFFF00";
// flashvars.c_add_bt_weight = "bold";
// flashvars.c_add_bt_theme = "haloOrange";
// flashvars.c_upload_bt_color = "ffffff";
// flashvars.c_upload_bt_bg_color = "FF0000";
// flashvars.c_upload_bt_weight = "bold";
// flashvars.c_upload_bt_theme = "haloOrange";

// Please read the description in the html page for details.
// flashvars.tfu_description_mode="true";
// flashvars.hide_remote_view="true";
// flashvars.big_server_view="true";
// flashvars.show_server_date_instead_size="true";
// flashvars.enable_absolut_path="true";
// flashvars.switch_sides="true";
// flashvars.hide_upload="true";  // This is when you set $allowed_file_extensions = ''
// flashvars.show_size="false"; // This is the parameter show_size from the config. You have to use "true" and "false". Not like in tfu_config '' for false. 

var params = {};
// needed for fullscreen 
params.allowfullscreen = "true";
params.scale = "noScale";
params.lang = "de";
// params.wmode = "transparent"; // needed when using transparent background! Set flashvars.c_bg_transparent = "true"; also!
var attributes = { id: "flash_tfu", name: "flash_tfu" };

// The flash is only 650 but ie does sometimes not display the left border. Therefore 1 extra pixel is provided. 
swfobject.embedSWF("tfu_3.2.swf", "flashcontent", "651", "340", "8.0.0", "", flashvars, params, attributes);
// Use this if you want to use the preloader
// swfobject.embedSWF("tfu_preloader.swf", "flashcontent", "652", "340", "8.0.0", "", flashvars, params, attributes);
    
</script>
  <!-- end include with Javascript -->
  <!-- static html include -->
  <noscript>
  <object name="mymovie" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="651" height="340"  align="middle">
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="tfu_3.2.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <param name="scale" value="noScale" />
    <param name="allowFullScreen" value="true" />
    <embed src="tfu_3.2.swf" name="mymovie" quality="high" bgcolor="#ffffff" width="651" height="340" align="middle" scale="noScale" allowfullscreen="true" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
  </object>
  </noscript>
  <!-- end static html include -->
  <!--


End of include code


-->
</div>
</center>
<h2>Namenskonventionen</h2>
<p>Bei Ordnernamen bitte die Namenskonvention "JJJJ-MM-TT Titel" beachten und im Titel Umlaute wie folgt umschreiben:</p>
<ul>
<li>ä wird zu _ae, Ä zu _Ae</li>
<li>ö wird zu _oe, Ö zu _Oe</li>
<li>ü wird zu _ue, Ü zu _Ue</li>
<li>ß wird zu _ss</li>
</ul>
<p>Das Ereignis "Begrüßung der Menschen" am 5.10.2015 wird also zu "2015-10-05 Begr_ue_ssung der Menschen".</p>
</body>
</html>
