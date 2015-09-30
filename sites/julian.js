/*
          *********************
 		  *	 C O N F I G      *
 		  *********************
*/

if (!queryURL) var queryURL="http://bildersee.eu/server/query.php";
if (!imageURL) var imageURL="http://bildersee.eu/server/getimage.php";

if (!backwards) var backwards=false;

var imageHeight=500;screen.height-200;
var imageWidth=20000;
var images=new Array();
var first=false;

window.onload=function(){
	setPage();
};

/*
          *********************
 		  *	 T O O L S        *
 		  *********************
*/

 window.getHeight=function(){
 	var height= this.innerHeight || document.body.clientHeight;
 	return height;
 };
 
 window.getWidth=function(){
 	return this.innerWidth || document.body.clientWidth;
 };


function set(element,value){
	if (document.getElementById(element).value!==undefined){
		document.getElementById(element).value=value; return;
	} 
	if (document.getElementById(element).innerHTML!==undefined){
	    document.getElementById(element).innerHTML=value; return;
	}
}


function setPage(){
 

var query=buildSearchQuery(search);
var forward='';

if (!backwards && query.search('contains')!=-1) forward=' ORDER BY date';

var result=server_getresults('SELECT id FROM pictures WHERE '+query+' '+forward+' LIMIT 1000');

var output='';
var isFirst=true;

for (var i in result){
	  if (isNaN(i)) continue;  //Interference with prototype
	  eval(makeEvaluable(result[i]));
	  images.push(eval_id);
	  if (isFirst) first=eval_id;
	  isFirst=false;
	  output+=''
	  +'<img src="'+imageURL+'?key='+eval_id+'&width='+imageWidth+'&height='+imageHeight+'&nocopyright=1" alt="" height="100" id="'+eval_id+'" onclick="showImage(this.id)" />';
}

document.getElementById('allimages').style.width='100%';
set('allimages',output+' &nbsp;');

showImage(first);
	
}

   
  
   
   
/*
          *********************
 		  *	 S E R V E R      *
 		  *********************
*/

function server_query(query,callback){
  query=encodeURI(query);
  var oXmlHttp=zXmlHttp.createRequest();
  oXmlHttp.open("get",queryURL+"?query="+query,!!callback);
  if (callback) {
    oXmlHttp.onreadystatechange = function (){
    	if (oXmlHttp.readyState ==4){
    		if (oXmlHttp.status==200) callback(oXmlHttp.responseText); 
            //DEBUG else alert('Ajax-Error');  // EROOR-Handling
    	}
    };
  }
  oXmlHttp.send(null);
  if (!callback){
    if (oXmlHttp.status==200) return (oXmlHttp.responseText); 
    //DEBUG else alert('Ajax-Error');  // EROOR-Handling
  }
}

function server_getresults(query,callback){
	if (!callback) return buildresults(server_query(query));
    server_query(query,function(value){callback(buildresults(value));});
}


/*
          *********************************
 		  *	 E R R O R - H A N D L I N G  *
 		  *********************************
*/

function handleError(message){
	message=(message+'').split('\n');
	if (message[0]=='OK') return;
	if (message[0]!='ERROR') alert(message);
	else {
		switch(message[1]){
			default: alert(message);break;
		}
	}
}

/*
          ***************************
 		  *	 C O N V E R S I O N S  *
 		  ***************************
*/

function buildresults(textResult){
    textResult=textResult.split('\n----\n');
	if (textResult[0]=='OK') {	
	   return (textResult.slice(1));
	} else handleError(textResult); 
}

function makeEvaluable(input){
	return 'eval_'+(input+'').replace(/^\s+|\s+$/g, '').replace(/\n/g, "';\neval_").replace(/:/g, "='")+"';";
}

function buildSearchQuery(search){
	search=search.split('"');
    var tagsearch='';
    var query='';
    for (var i in search){
      if (isNaN(i)) continue;  //Interference with prototype
	  if(i%2==0) tagsearch+=search[i];
	  else query+=" AND contains('"+search[i]+"')";
    }

    tagsearch=tagsearch.split(' ');
    for (var i in tagsearch){
       if (isNaN(i)) continue;  //Interference with prototype
	   if (tagsearch[i]=='') continue;
	   
	   if (tagsearch[i]=='empty') {
	   	query+=" AND notags";
	   	continue;
	   }
	  
	   if (tagsearch[i].substring(0,1)=='-') query+=" AND NOT tagged('"+tagsearch[i].substr(1)+"')";
	   else query+=" AND tagged('"+tagsearch[i]+"')";
    }
    return query.substr(5);

}