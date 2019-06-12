<?php /* Template Name: myaddress-form */ ?>

<?php
global $wpdb, $current_user;

$address_id = get_query_var('address_id');
$province = '';
if (isset($address_id) && $address_id != ''){
  $address = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT * FROM user_address WHERE id = %d ",
          array($address_id)
      )
  );

}

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<script>

jQuery(document).ready(function($){

  let dropdown = $('#dd_province');

  dropdown.empty();

  dropdown.append('<option selected="true" value="" >กรุณาเลือกจังหวัด</option>');
  dropdown.prop('selectedIndex', 0);

  // Populate dropdown with list of provinces
  $.getJSON(ajaxurl+'?action=get_province', function (data) {
    $.each(data.data, function (key, entry) {
      dropdown.append($('<option></option>').attr('value', entry.province).text(entry.province));
    })
    var province = "<?php echo $address->province; ?>";
    if (province != "")
      dropdown.val(province).change();
  });



  $("#dd_province").change(function () {
      var region = this.value;

      let dropdown = $('#dd_district');

      dropdown.empty();

      dropdown.append('<option selected="true" value="" >กรุณาเลือกเขต/อำเภอ</option>');
      dropdown.prop('selectedIndex', 0);

      $.getJSON(ajaxurl+'?action=get_district&region='+region, function (data) {
        $.each(data.data, function (key, entry) {
          dropdown.append($('<option></option>').attr('value', entry.district).text(entry.district));
        })
        var district = "<?php echo $address->district; ?>";
        if (district != "")
          dropdown.val(district).change();
      });

      dropdown.prop("disabled", false);

  });

  $("#dd_province").change(function () {
      var district = this.value;
      $("#tb_postcode").prop("disabled", false);
  });


  $("form[name='user_address_form']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      name: "required",
      phone:{
        required: true,
        maxlength: 10,
        digits: true
      },
      address: "required",
      dd_province:{
        required: true
      },
      dd_district:{
        required: true
      },
      tb_postcode:{
        required: true,
        maxlength: 5,
        digits: true
      }
    },
    // Specify validation error messages
    messages: {
      name: "กรุณากรอกชื่อ-สกุล",
      phone: "กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง",
      address: "กรุณากรอกที่อยู่",
      dd_province: "กรุณาเลือกจังหวัด",
      dd_district: "กรุณาเลือกอำเภอ",
      tb_postcode: "กรุณากรอกรหัสไปรษณีย์ให้ถูกต้อง"
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      $( "#address-content" ).toggleClass('order-status-loading');

      var clikedForm = $(form);
      console.log(clikedForm.serialize());
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: clikedForm.serialize(),
        success: function(msg){
              if(msg.success){
                $( "#address-content").load( ajaxurl+"?action=load_address_list", function( response, status, xhr ) {
                  if ( status == "error" ) {
                    var msg = "Sorry but there was an error: ";
                    $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
                  }

                  console.log("Response: "+status);
                });
              }

              $( "#address-content" ).toggleClass('order-status-loading');
              //$('.wrapper-loading').toggleClass('cart-loading');

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
           $( "#address-content" ).toggleClass('order-status-loading');
        }
      });

    }
  });

  $('#back-address-list').click(function(){
    $( "#address-content" ).toggleClass('order-status-loading');
    $( "#address-content" ).load( ajaxurl+"?action=load_address_list", function( response, status, xhr ) {
      if ( status == "error" ) {
        var msg = "Sorry but there was an error: ";
        $( "#address-content" ).html( msg + xhr.status + " " + xhr.statusText );
      }
      //console.log( "load_order_status: " + status );
      $( "#address-content" ).toggleClass('order-status-loading');
    });

  });

});
</script>
    <style>
	#mapdiv {
		height: 500px;
	}
	#autocomplete{
	height: 100%;
	width: 90%;
	float:left;
    margin-right:5px;
	}
	#mylocationicon{
	position: relative;
	height: 100%;
	width: 10%;
	float:right;
	}
    </style>
<h1>เพิ่มที่อยู่ใหม่</h1>
<section class="entry-content cf" itemprop="articleBody" style="width:67%;">

  <form name="user_address_form">

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ชื่อ-สกุล<span>*</span> </label>
       <input type="text" id = "name" name="name" value="<?php echo esc_attr(stripslashes($address->name)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>หมายเลขโทรศัพท์<span>*</span> </label>
       <input type="text" id = "phone" name="phone" value="<?php echo esc_attr(stripslashes($address->phone)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ที่อยู่<span>*</span> </label>
       <input type="text" id = "address" name="address" value="<?php echo esc_attr(stripslashes($address->address)); ?>">
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>จังหวัด<span>*</span> </label>
       <select id="dd_province" name="dd_province" ></select>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>อำเภอ<span>*</span> </label>
       <select id="dd_district" name="dd_district" disabled>
         <option selected="true">กรุณาเลือกเขต/อำเภอ</option>
       </select>
    </div>

    <div class="geodir_form_row clearfix gd-fieldset-details">
       <label>รหัสไปรษณีย์<span>*</span> </label>
       <input type="text" id = "tb_postcode" name="tb_postcode" value="<?php echo esc_attr(stripslashes($address->postcode)); ?>" disabled>
    </div>
	
	<div class="geodir_form_row clearfix gd-fieldset-details">
       <label>ตำแหน่งบนแผนที่<span>*</span> </label>
	   <input type="text" id = "autocomplete" name="autocomplete" onFocus="geolocate()">
	   <div id= "mylocationicon">
			<i style="color: red;" class="fas fa-map-marker-alt" aria-hidden="true" onclick="getLocation()" ></i>
		</div>
    </div>
	<div class="geodir_form_row clearfix gd-fieldset-details" id="mapdiv">
	</div>

    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'add_user_address_' . $current_user->ID ); ?>"  />
    <input type="hidden" name="address_id" value="<?php echo esc_attr(stripslashes($address_id)); ?>"  />
	<input type="hidden" id ="lat" name="lat"  />
	<input type="hidden" id ="lng" name="lng"  />
    <input type="hidden" name="action" value="add_user_address"  />
    <div class="order-row">
      <div class="order-col-6" style="text-align:left;">
        <button type="submit" class="btn btn-warning">บันทึก</button>
      </div>
      <div class="order-col-6" style="text-align:right;">
        <button type="button" id="back-address-list" class="btn btn-info">ย้อนกลับ</button>
      </div>
    </div>
  </form>
</section>
<script>
	//Bank 20190206
	// Create Map for Point on Map and make it dragable
	 map = new OpenLayers.Map("mapdiv");
	
	function initMap(lat,lon)
	{
		var LonDB			= lon;
		var LatDB			= lat;
		var	Lon             = lon;
		var	Lat             = lat;
		var	Zoom            = 14;
		var EPSG4326        = new OpenLayers.Projection( "EPSG:4326" ); // location by Lat Lon
		var EPSG900913      = new OpenLayers.Projection("EPSG:900913"); // location by Point
	
		var	LL              = new OpenLayers.LonLat( Lon, Lat );
		console.log("LL :"+LL);
		var	XY              = LL.clone().transform( EPSG4326, EPSG900913 );
		//console.log("XY :"+XY);
		var	deftColor     = "#00FF00";
		var	deftIcon      = "https://www.tamzang.com/wp-content/uploads/2018/09/iconfortamzang-44-e1537943600396.png";
		var	featureHeight = 50;
		var	featureWidth  = 45;
		var	featureStyle  =	{
			fillColor:      deftColor,
			strokeColor:    deftColor,
			pointRadius:    1,
			externalGraphic:deftIcon,
			graphicWidth:   featureWidth,
			graphicHeight:  featureHeight,
			graphicXOffset: -featureWidth/2,
			graphicYOffset: -featureHeight,
			fontColor:      "#000000",
			fontSize:       "10px",
			fontWeight:     "bold",
			labelAlign:     "rm"
		};
		//console.log("Map Init");
		map.addLayer(new OpenLayers.Layer.OSM());
	
		var center = new OpenLayers.LonLat( Lon,Lat )
			.transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				map.getProjectionObject() // to Spherical Mercator Projection
			);
		var zoom=18;	
		map.setCenter (center, zoom);
	
		var	vectorL = new OpenLayers.Layer.Vector(  "Vector Layer", {
													styleMap:   new OpenLayers.StyleMap(  featureStyle  )
		});
		map.addLayer( vectorL );	
	
		var	dragVectorC = new OpenLayers.Control.DragFeature(   vectorL, { 
																onComplete: function(feature){
	
			//Don´t user the position of the pixel or the feature, use the point position instead!
			var point = feature.geometry.components[0];
			// Convert into Lat Lon
			var llpoint = point.clone().transform(  new OpenLayers.Projection(EPSG900913), 
								new OpenLayers.Projection(EPSG4326));
			LonDB = llpoint.x;
			LatDB = llpoint.y;
			console.log("Move point Detect!Lat:"+LatDB+"Lon :"+LonDB);
			var lattmp= document.getElementById('lat');
			lattmp.value = LatDB;
		
			var lngtmp= document.getElementById('lng');
			lngtmp.value = LonDB;
		}});
	
		map.addControl( dragVectorC );
		dragVectorC.activate();
	
		var	point       = new OpenLayers.Geometry.Point( XY.lon, XY.lat );
		var	featureOb   = new OpenLayers.Feature.Vector( new OpenLayers.Geometry.Collection([point]) );
		vectorL.addFeatures( [featureOb] );
		
		var lattmp= document.getElementById('lat');
		lattmp.value = LatDB;
		
		var lngtmp= document.getElementById('lng');
		lngtmp.value = LonDB;
	}

	// Google Api autocomplete search
	var placeSearch, autocomplete;
    function initAutocomplete() 
	{	
		console.log("function initAutocomplete()");
        // Create the autocomplete object, restricting the search to geographical
        // location types.
		var options = {
			type:['address'],
			componentRestrictions: {country: 'th'}
		}
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
            options);
			
		// Avoid paying for data that you don't need by restricting the set of
		// place fields that are returned to just the address components.
		autocomplete.setFields(['geometry']);
		
        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
    }

    function fillInAddress() 
	{
		// Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();
		console.log("Place Lat : "+place.geometry.location.lat());
		console.log("Place Lon : "+place.geometry.location.lng());
		
		initMap(place.geometry.location.lat(),place.geometry.location.lng());

    }

    // Bias the autocomplete object to the user's geographical location,
    // as supplied by the browser's 'navigator.geolocation' object.
    function geolocate() 
	{
		console.log("function geolocate()");
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
			console.log("lat :"+geolocation.lat);
			console.log("Lng :"+geolocation.lng);
            autocomplete.setBounds(circle.getBounds());
          });
        }
    }
	function getLocation() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(showPosition);
		} else { 
			x.innerHTML = "Geolocation is not supported by this browser.";
		}
	}

	function showPosition(position) {
	// Set value back to hidden input
	var lattmp= document.getElementById('lat');
		lattmp.value = position.coords.latitude;
		
	var lngtmp= document.getElementById('lng');
		lngtmp.value = position.coords.longitude;
	initMap(position.coords.latitude,position.coords.longitude);
	}
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3mypqGAf0qnl5xGwsxwQinUIfeiTIYtM&libraries=places&callback=initAutocomplete"async defer>
	</script>
