<?php /* Template Name: Driver-approve */ ?>
<?php

echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';

?>
<!DOCTYPE html>
<html>
<head> 
 <style>
 
  table {
   border-collapse: collapse;
   width: 50%;
   color: #588c7e;
   font-family: monospace;
   font-size: 25px;
   text-align: left;
     } 
  th {
   background-color: #588c7e;
   color: white;
    }
  tr:nth-child(even) {background-color: #f2f2f2}
 </style>
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>

$(document).ready(function(){
	
	$( window ).on( "load", function() {
		console.log( "window loaded" );
		getdriverregis();        
    });
	
	/*
	$('#getorderlist').click(function(){
		getdriverregis();
	});
	*/
	$('#approvedriver').click(function(){
	// Collect Value from check box
	var values = (function() {
                var a = [];
                $("input:checkbox:checked").each(function() {
                    a.push(this.value);
                });
                return a;
            })()
	//console.log("Text box is "+ values.length);
    approve_driver(values);
	});
	

});

// Get List Order For delivery
function getdriverregis()
	{
		console.log("Call List Driver START!! ");
		// Call List Driver
		
		$( ".driverlist-loading").load( ajaxurl+"?action=get_driver_regis", function( response, status, xhr ) 
		{
			
		//console.log("Second CALL list AJAX START" );
		if ( status == "error" ) {
			var msg = "Sorry but there was an error: ";
			$( ".driverlist-loading" ).html( /*msg + xhr.status + " " +*/ xhr.statusText );
		}
		
		});
		
	}
function approve_driver(chkvalue) {
		console.log( "Approve process" );
		var send_data = 'action=approve_driver&UserID='+chkvalue;

		//console.log( "AJAX Url "+ajaxurl);
    $.ajax({
        type: 'POST',
		dataType: 'json',
        url: ajaxurl,
        data: send_data,
		success: function() 
	{
        alert("Done");
		location.reload();
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
	
    }

</script>
<body>

	<div class="driverlist-row">

		<div class="driverlist-loading">
			
		</div>

	</div>

<button id ="approvedriver">Approve</button>

</body>
</html>