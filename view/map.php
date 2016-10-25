<!DOCTYPE html>
<html>
  <head>
    <title>Bildersee Map Viewer</title>
    <style>
      #map {
        width: 375px;
        height: 100%;
        position:absolute;
        left:0;
        top:0;
      }
      #capture {
        width: calc(100% - 375px);
        height: 100%;
        position:absolute;
        left:375px;
        top:0;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js"></script>
    <script src="oms.min.js"></script>
    
    <script>
	
	if(window.location.href!==parent.location.href){
		parent.location.href=window.location.href;
	}
    
    </script>
    
  </head>
  <body>
    <div id="map"></div>
    <iframe id="capture" src="map.html"></iframe>
    
<?php

include_once ('environment.php');
 
preventInjection();
 
$search=getImages($folder,false);

echo '<script>
var points=[];
';


foreach ($search as $image){
	
	$coordinates=getCoordinates($image->filename,$image->key);
	
	if (!$coordinates) continue;
	
	echo'
	var point={};
	point.text="'.$image->subfolder.'";
	point.key="'.$image->key.'";
	point.lat='.$coordinates[0].';
	point.lng='.$coordinates[1].';
	points.push(point);
';
}

echo '
</script>
';

mysql_close();

function getCoordinates($image,$key){global $config;
	
	$data=getExif($image,$key);
	
	if (!$data) return false;
	
	if (isset($data['Composite']['GPSPosition'])) {
		$coordinates=$data['Composite']['GPSPosition'];
	} else return false;	
	
   $coordinates=explode(',',$coordinates);
   $temp=$coordinates[1];
   
   foreach ($coordinates as $i=>$element){
   	 $coordinates[$i]=floatval($element);
   	 //S W
   	 
   	 if (stripos($element,'S') || stripos($element,'W')) $coordinates[$i]=-1*$coordinates[$i];
   	 
   }

   return $coordinates;

}

?>
<script>
		/**
		 * @fileoverview Sample showing capturing a KML file click
		 *   and displaying the contents in a side panel instead of
		 *   an InfoWindow
		 */
		
		var map;
		
		/**
		 * Initializes the map and calls the function that creates polylines.
		 */
		function initMap() {
		  map = new google.maps.Map(document.getElementById('map'), {
		    center: new google.maps.LatLng(-19.257753, 146.823688),
		    zoom: 12,
		    mapTypeId: google.maps.MapTypeId.SATELLITE
		  });
		  
		  var oms = new OverlappingMarkerSpiderfier(map);
		  
		  var bounds = new google.maps.LatLngBounds();
		  
		  for (var i in points){
		  	var point=points[i];
		  	
			  var marker = new google.maps.Marker({
			    position: point,
			    map: map,
			    title: point.key
			  });
			  
			  oms.addListener('click', function(marker) {
			    var key=marker.title;
			    var testimonial = document.getElementById('capture');
		    	testimonial.src = '/view/?image='+key;
			  });
			  
			  oms.addMarker(marker);
			  
			  window.marker=marker;
			  
			  bounds.extend(marker.getPosition());
			  
			  if (i==0){
			  	map.panTo(point);
			  }
		  	
		  }
		  map.fitBounds(bounds);
		  map.panToBounds(bounds);
		  
		}
		
		window.onload=function(){
			initMap();
		}
				  
		

    </script>
  </body>
</html>