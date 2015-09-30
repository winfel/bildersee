var queryURL='';

function seesiteResult(URL,query){

    var queryURL=URL;
	
	var result=server_getresults(query);
	
	return result;
   
}
   
/*
          *********************
 		  *	 S E R V E R      *
 		  *********************
*/

function server_query(query){
  query=encodeURI(query);
  var oXmlHttp=zXmlHttp.createRequest();
  oXmlHttp.open("get",queryURL+"?query="+query,false);
  oXmlHttp.send(null);
    if (oXmlHttp.status==200) return (oXmlHttp.responseText); 
    else alert('Ajax-Error');  // EROOR-Handling
}

function server_getresults(query){
	return buildresults(server_query(query));
}

function handleError(message){
	message=(message+'').split('\n');
	if (message[0]=='OK') return;
	if (message[0]!='ERROR') alert(message);
	else {
		switch(message[1]){
			default: alert('ERROR:'+message);break;
		}
	}
}

function buildresults(textResult){
    textResult=textResult.split('\n----\n');
	if (textResult[0]=='OK') {	
	   result=textResult.slice(1);
	   for (var i in result){
	   	var element=result[i];
	   	if (!element.split) continue;
	   	var parts=element.split(/\n/g);
	   	var resObject=new Object();
	   	for (var j in parts){
	   		if (!parts) continue;
	   		if (!parts[j]) continue;
	   		if (!parts[j].split) continue;
	   		var part=parts[j].split(':');
			resObject[part[0]] = part[1];
	   	}

	   	resObject.toString=function(){
	   		var output='\n seeSiteResult \n';
	   		for (var Eigenschaft in this){
	   			if (Eigenschaft=='toString') continue;
    			output+=(Eigenschaft + ":" + this[Eigenschaft] + "\n");
	   		}
	   		return output;
	   	};
	   	resObject['imagePath']="http://bildersee.eu/server/getimage.php";
	   	result[i]=resObject;
        
	   }
	   return result;
	} else handleError(textResult); 
}



/*
        **** GET-PARAMETER ***
*/

var HTTP_GET_VARS=new Array();
var strGET=document.location.search.substr(1,document.location.search.length);
if(strGET!=''){
    gArr=strGET.split('&');
    for(i=0;i<gArr.length;++i)
        {
        v='';vArr=gArr[i].split('=');
        if(vArr.length>1){v=vArr[1];}
        HTTP_GET_VARS[unescape(vArr[0])]=unescape(v);
        }
}

function GET(v){
  if(!HTTP_GET_VARS[v]){return;}
  return HTTP_GET_VARS[v];
}