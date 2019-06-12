<?php /* Template Name: Driver-head */ ?>
<?php

echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';

?>
<html>
<head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function(){

	$( window ).on( "load", function() {
        console.log( "window loaded" );
    });
	
	$('#getorderbyone').click(function(){
	console.log("Text box is "+ $('#driverid').val());
    getdriver($('#driverid').val());
	});
	

});


// Get List Order For delivery
function getdriver(driver_id_txt) {
		console.log( "order assign  loaded" );
	//var order_id = 1;
	var send_data = 'action=get_driver_super&DriverId='+driver_id_txt;
	console.log( " Url "+send_data);
    $.ajax({
        type: 'POST',
		dataType: 'json',
        url: ajaxurl,
        data: send_data,
		//data: {"action": "get_order_list_delivery"},
		success: function(arrayPHP) 
		{
			//alert(JSON.stringify(arrayPHP.data));
			var super_name = arrayPHP.data[0].name;
			var super_id = arrayPHP.data[0].id;
			console.log( " Supervisor is: "+super_name);
			
			if(super_name != null){
				// Show Driver Supervisor
				$( ".driverlist-loading").load( ajaxurl+"?action=listdriversuper&super_driver_id="+super_id+"&driver_id="+driver_id_txt, function( response, status, xhr ) 
				{
					
				console.log("Second CALL list AJAX START" );
				if ( status == "error" ) {
					var msg = "Sorry but there was an error: ";
					$( ".orderlist-loading" ).html( msg + xhr.status + " " + xhr.statusText );
				}		
				});
			}
			else{
				alert("Driver ID คนนี้ไม่มี Head");
				location.reload();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
		console.log(textStatus);
		}
		
	});
}


</script>
</head>
<body>
 <p>Select specific Driver</p>
 
<input type="text" id="driverid"><br>
<button id ="getorderbyone">Get Driver Supervisor</button>
<div class="driverlist-row">
	<div class="driverlist-loading">
		
	</div>
</div>

</body>
</html>