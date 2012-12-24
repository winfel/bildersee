(function(namespace) { // Closure to protect local variable "var hash"
    if ('replaceState' in history) { // Yay, supported!
        namespace.replaceHash = function(newhash) {
            if ((''+newhash).charAt(0) !== '#') newhash = '#' + newhash;
            history.replaceState('', '', newhash);
        }
    } else {
        var hash = location.hash;
        namespace.replaceHash = function(newhash) {
        	if (location.hash !== hash) history.back();
            location.hash = newhash;
        };
    }
})(window);

function processHash(direct){
	var type=hash.substring(1,7);
	if (type=="scroll"){
				
		var elementID=hash.substr(7);
		var element=document.getElementById(elementID);
		if (!element) {
			return;
		}
		var position=getElementPosition(element)-75;
		
		if (direct){
			scrollToNew(position);
		} else {
			window.setTimeout(function(){scrollToNew(position);}, 300);
		}
		
	}
}

function scrollToNew(to){
	
	window.scrollTo(0,to); return;
	/*
	var from=getScrollY();
	var distance=(to-from)/10;
	var value=(distance>0)?distance:distance*-1;
	if (value<1){window.scrollTo(0,to); return;}
	window.scrollTo(0,Math.round(from+distance));
	if (from==getScrollY()) return;
	window.setTimeout(function(){scrollToNew(to);},10);
	*/
}

function getElementPosition(e){
	
		var y=0;
		
		do {
		y+=e.offsetTop;
		
		e=e.offsetParent;
		} while(e != null);
		
		return y;
				
}

var scrollDelay=false;
function scrollHandlerDelayed(){
	
	if (scrollDelay) {
		window.clearTimeout(scrollDelay);
		scrollDelay=false;
	}
	scrollDelay = window.setTimeout(function(){scrollHandler();}, 300);
}

window.onscroll=function(){ //Internet Explorer <9
	scrollHandlerDelayed();
}


function scrollHandler(){
	if(typeof onScroll == 'function') {onScroll();}
}


var resizeDelay=false;
function resizeHandlerDelayed(){
	if (resizeDelay) {
		window.clearTimeout(resizeDelay);
		resizeDelay=false;
	}
	resizeDelay = window.setTimeout("resizeHandler()", 300);
}

function resizeHandler(){
	if(typeof onResize == 'function') {onResize();}
	if(typeof onScroll == 'function') {onScroll();}
}


window.getHeight=function(){
	return this.innerHeight || document.body.clientHeight;
};

window.getWidth=function(){
	return this.innerWidth || document.body.clientWidth;
}; 

function keypressed (Ereignis) {
  if (!Ereignis)
    Ereignis = window.event;
  if (Ereignis.which) {
    Tastencode = Ereignis.which;
  } else if (Ereignis.keyCode) {
    Tastencode = Ereignis.keyCode;
  }
  
  
  if (Tastencode==39 || Tastencode==32) {
  	var nextlink=document.getElementById('nextlink');
  	           if (nextlink){
  	           	  location.href=nextlink.href;
  				}
  }
  
  if (Tastencode==37) {
  	var prevlink=document.getElementById('prevlink');
  	           if (prevlink){
  	           	  location.href=prevlink.href;
  				}
  }
  if (Tastencode==27) {
  	var backlink=document.getElementById('backlink');
  	           if (backlink){
  	           	  location.href=backlink.href;
  				}
  }
  //alert(Tastencode);
  
}

function server_query(query,callback){
  query=encodeURI(query);
  var oXmlHttp=zXmlHttp.createRequest();
  oXmlHttp.open("get",query,!!callback);
  if (callback) {
    oXmlHttp.onreadystatechange = function (){
    	if (oXmlHttp.readyState ==4){
    		if (oXmlHttp.status==200) callback(oXmlHttp.responseText); 
            //else alert('Ajax-Error! No connection to server!'); 
    	}
    };
  }
  oXmlHttp.send(null);
  if (!callback){
    if (oXmlHttp.status==200) return (oXmlHttp.responseText); 
    //else alert('Ajax-Error! No connection to server!'); 
  }
}

function isIPhone(ipad){
	if(navigator.userAgent.match(/iPhone/i)){
		return true;
	}

	if(navigator.userAgent.match(/iPod/i)){
		return true;
	}
	
	if (!ipad) return false;
	
	if(navigator.userAgent.match(/iPad/i)){
		return true;
	}
	
	return false;
}


function getScrollY() {
    var scrOfY = 0;
 
    if( typeof( window.pageYOffset ) == "number" ) {
        //Netscape compliant
        scrOfY = window.pageYOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
        //DOM compliant
        scrOfY = document.body.scrollTop;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
        //IE6 standards compliant mode
        scrOfY = document.documentElement.scrollTop;
    }
    return scrOfY;
}

function show(id){
	if (document.getElementById(id).style.display=='block')
	    document.getElementById(id).style.display='none';
	else 
		document.getElementById(id).style.display='block';
}

function hide(id){
	document.getElementById(id).style.display='none';
}

function login_error(){
	alert("You could not be logged in. Please check your username and password!");
}