<?php /* Template Name: Driver */ ?>
<?php

echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';

?>
<html>
<head>
	<style>
	#mapdiv {
			height: 500px;
			width: 100%;
	}
	</style>
  <script src="https://www.openlayers.org/api/OpenLayers.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function(){

	$('#getmarker').click(function(){
	//console.log("Text box is "+ $('#Textid').val());
	tamzang_id = $('#Textid').val();
    getmarker(tamzang_id);
});
$( window ).on( "load", function() {
        console.log( "window loaded" );
    });

});
</script>
</head>
<body>
  <div id="mapdiv"></div>
  <script>
  
  	map = new OpenLayers.Map("mapdiv");
    map.addLayer(new OpenLayers.Layer.OSM());

	var center = new OpenLayers.LonLat( 100.499564,13.721699 )
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
	var zoom=18;

    map.setCenter (center, zoom);
	
	var markers = new OpenLayers.Layer.Markers( "Markers" );
	

	
  	function addMarker($Lon,$Lat,$check)
	{		
		//var markers = new OpenLayers.Layer.Markers( "Markers" );
		//markers.clearMarkers();
		//markers.destroy(markers);
		
		
		if($check != "res_id")
		{
			var lonLat = new OpenLayers.LonLat($Lon,$Lat)
				.transform(
					new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
					map.getProjectionObject() // to Spherical Mercator Projection
			);
		
			//var markers = new OpenLayers.Layer.Markers( "Markers" );

			//markers.clearMarkers();
			markers.addMarker(new OpenLayers.Marker(lonLat));
		}
		else
		{
			var center = new OpenLayers.LonLat($Lon,$Lat )
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
			var zoom=18;

			map.setCenter (center, zoom);
			//var markers = new OpenLayers.Layer.Markers( "Markers" );
			//markers.clearMarkers();
			map.addLayer(markers);
			var size = new OpenLayers.Size(41,45);
			var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
			var icon = new OpenLayers.Icon('https://www.tamzang.com/wp-content/uploads/2018/09/iconfortamzang-44-e1537943600396.png', size, offset);
   			
			markers.addMarker(new OpenLayers.Marker(center,icon));
		}
	}
	
	/*
	map.events.register("click", map, function(evt) {
		 
        var lonlat = map.getLonLatFromViewPortPx(evt.xy).transform(new OpenLayers.Projection("EPSG:900913"), new OpenLayers.Projection("EPSG:4326"));
        $("#edit-field-jena-seta-map-openlayers-wkt").val('GEOMETRYCOLLECTION(POINT('+lonlat.lat+'  '+lonlat.lon+'))');
        //var pos = map.getLonLatFromPixel(evt.xy);
		console.log("Click Map Detect!"+lonlat.lon);
		addMarker(lonlat.lon,lonlat.lat,"res_id");

        });
*/

    function getmarker(tamzang_id) {
		//console.log( "addmarker loaded" );
		//var send_data = 'action=get_driver_restaurant&tamzang_id='+tamzang_id;
		console.log( "AJAX Url "+ajaxurl+ "Tamzang_id : "+tamzang_id);
    $.ajax({
        type: "POST",
        url: ajaxurl,
		dataType:"json",
        //data: send_data,
		data: {"action": "get_driver_restaurant", "tamzang_id":tamzang_id},
		success: function(arrayPHP) 
	{
        //alert(JSON.stringify(data.data));
		var len = arrayPHP.data.length;
		var driver_id = [];
		console.log("Length is "+ len);
		for(var i=0; i<len; i++)
		{
			
			var id = arrayPHP.data[i].id;
			driver_id[i] = arrayPHP.data[i].id;

			var Lon = arrayPHP.data[i].Lon;
			var Lat = arrayPHP.data[i].Lat;
			console.log("ID is "+driver_id[i]);

			addMarker(Lon,Lat,id);
		}

		// Call List Driver
		
		$( ".driver-loading").load( ajaxurl+"?action=listdriver&driver_num="+len+"&driver_id="+driver_id+"&tamzang_id="+tamzang_id, function( response, status, xhr ) 
		{
		//console.log("Second AJAX START and Tamzang ID is "+tamzang_id );
		if ( status == "error" ) {
			var msg = "Sorry but there was an error: ";
			$( ".driver-loading" ).html( msg + xhr.status + " " + xhr.statusText );
		}
		
		});
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
	
    }
	


  </script>
  
<div style="padding:16px;">
	Tamzang ID : <input type="textbox" id="Textid" value="Tamzang ID" ></input>
</div>
  <button id ="getmarker">get Driver</button>

<div class="driver-row">
	<div class="driver-loading">
		
	</div>
</div>

</body>
</html>