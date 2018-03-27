
jQuery(document).ready(function($){

  $('#myLatLong').click(function(e){
    e.preventDefault();
    $('#geoStatus').empty();
    // Try HTML5 geolocation.
    //if (false) {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        var pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
        };
        Latphp = position.coords.latitude;
        Lngphp = position.coords.longitude;
        //console.log("Lat is"+Latphp);
        //console.log("Long is"+Lngphp);

        //document.getElementById("mylatitude").value = Latphp;
        //document.getElementById("mylongitude").value = Lngphp;
        $("#mylatitude").val(Latphp);
        $("#mylongitude").val(Lngphp);
        $('#geoStatus').html('<img src="https://www.tamzang.com/Test02/wp-content/themes/GeoDirectory_whoop-child/js/pass.png" /> ')
        }, function() {
        //handleLocationError(true, infoWindow, map.getCenter());
      });
    } else {
      // Browser doesn't support Geolocation

      $("#geoStatus").css("color", "red") ;
      $('#geoStatus').html('Browser doesn\'t support Geolocation');
      $('#geoStatus').prepend('<img src="https://www.tamzang.com/Test02/wp-content/themes/GeoDirectory_whoop-child/js/error.png" /> ')
    }
  });

});
