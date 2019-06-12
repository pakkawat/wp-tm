<?php /* Template Name: Driver-assign */ ?>
<?php

echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';

?>
<html>
<head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
var intervalID;

$(document).ready(function(){

	$('#getorderlist').click(function(){
	//console.log("Text box is "+ $('#Textid').val());
	getorder();
	intervalID = window.setInterval(getorder, 5000);    
	});
	
	$('#stopgetorderlist').click(function(){
	//console.log("Text box is "+ $('#Textid').val());
	clearInterval(intervalID);
	});
	
	$( window ).on( "load", function() {
        console.log( "window loaded" );
    });
	
	$('#getorderbyone').click(function(){
	console.log("Text box is "+ $('#orderid').val());
    getorder($('#orderid').val());
	});
	

});


// Get List Order For delivery
    function getorder(order_id_txt) {
		console.log( "order assign  loaded" );
	//var order_id = 1;
	if(order_id_txt == null)
	{
		var send_data = 'action=get_order_list_delivery';
	}
	else{
		var send_data = 'action=get_order_list_delivery&OrderId='+order_id_txt;
	}
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
		var len = arrayPHP.data.length;
		var res_id = [];
		var order_id = [];
		var buyer_name = [];
		var order_date = [];
		console.log("Length is "+ len);
		
		for(var i=0; i<len; i++)
		{
			
			res_id[i] = arrayPHP.data[i].id;
			order_id[i] = arrayPHP.data[i].order_id;
			buyer_name[i] = arrayPHP.data[i].buyer_name;
			order_date[i] = arrayPHP.data[i].order_date;
			console.log("name is  " + buyer_name[i]);
		}

		console.log("Call List Driver START!! ");
		// Call List Driver
		
		
		$( ".orderlist-loading").load( ajaxurl+"?action=listdriverassign&order_num="+len+"&res_id="+res_id+"&order_id="+order_id, function( response, status, xhr ) 
		{
			
		console.log("Second CALL list AJAX START" );
		if ( status == "error" ) {
			var msg = "Sorry but there was an error: ";
			$( ".orderlist-loading" ).html( msg + xhr.status + " " + xhr.statusText );
		}
		
		});
		
		
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
	
    }


</script>
</head>
<body>
 <p>Select specific order</p>
 
 <input type="text" id="orderid"><br>
 <button id ="getorderbyone">Get Order</button>
 <p> OR </p>
 <p> List All order </p>
 <button id ="getorderlist">Refresh</button>
 <button id ="stopgetorderlist">Stop</button>
<div class="orderlist-row">
	<div class="orderlist-loading">
		
	</div>
</div>

</body>
</html>