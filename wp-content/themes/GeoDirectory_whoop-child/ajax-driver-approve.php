<?php /* Template Name: ajax-driver-approve */ ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
	function Cancel_order(order_id) {
		/* Check checkbox is check*/
		
		console.log("order id is : "+order_id);
		if($("#priority1_"+order_id).is(":checked"))
		{
			console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
			var driver_id = $("#priority1_"+order_id).val();
		}
		
		if($("#priority2_"+order_id).is(":checked"))
		{
			//console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
			var driver_id = $("#priority2_"+order_id).val();
		}
		if($("#priority3_"+order_id).is(":checked"))
		{
			//console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
			var driver_id = $("#priority3_"+order_id).val();
		}
		if($("#priority4_"+order_id).is(":checked"))
		{
			//console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
			var driver_id = $("#priority4_"+order_id).val();
		}
		if($("#priority5_"+order_id).is(":checked"))
		{
			//console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
			var driver_id = $("#priority5_"+order_id).val();
		}	
		if(driver_id == null)
		{
			
			alert('You must Choose Driver to cancel');
			throw new Error('You must Choose Driver to cancel');
			
		}
		//var driver_id = document.getElementById("priority").value;
		
		var status = document.getElementById("cancelBtn").value;
		var send_data = 'action=assign_order_driver&status='+status+'&orderID='+order_id+"&priority="+driver_id;
		console.log( "Cancel Data "+send_data);
		
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: send_data,
		success: function(resultPhp) 
	{
		alert(resultPhp.data);
		location.reload();
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
	  
	}
	$(function () {

        $('form').on('submit', function (e) {

          e.preventDefault();
          
		var request_method = $(this).attr("method"); //get form GET/POST method
		var form_data = $(this).serialize(); //Encode form elements for submission
		//var x = $("input[name='submit']",this).val(); 
		console.log("Check data "+form_data);
		/* 		
		
		var driver_id = $("#driverID").val();
		var tamzang_id = $("#TamzangId").val();
		console.log("AJAX @ Check Box Value is "+form_data);
		  */
		//console.log("AJAX @ Check Box Value X is "+x );
          $.ajax({
            type: "POST",
            url: ajaxurl,
            //data: {"action": "update_driver_piority", form_data},
			data:form_data,
            success: function (resultPhp) {
              alert(resultPhp.data);
			  location.reload();
            }
          });

        });

    });
    </script>
<?php
global $wpdb;
echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';
		 
//$num_driver = 2;
// Get Value from Ajax

$usr_id = get_query_var('usr_id');
$name = get_query_var('name');
$phone = get_query_var('phone');
$note = get_query_var('note');
//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $usr_id, true),FILE_APPEND);
/*
$res_id_array = get_query_var('res_id');
$res_id = explode(",", $res_id_array);
$order_id_array = get_query_var('order_id');
$order_id = explode(",", $order_id_array);
*/



//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $order_id[0], true));
if($num_order > 0)
{
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Inside Loop start", true));
	for($i=0; $i<$num_order; $i++)
	{
		if($order_id[$i] != "res_id")
		{
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "INSIDE FOR START", true),FILE_APPEND);
		
		// Get Order-date from Order
		
		$order_sql = "SELECT * FROM orders where id = ".$order_id[$i]."";
		$result_order = $wpdb->get_results($order_sql, ARRAY_A );
		$order_date = $result_order[0]['order_date'];
		
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $order_date, true));
		
		// Get Tamzang_id / res title from wp_geodir_gd_place_detail
		
		$tamzang_sql = "SELECT * FROM wp_geodir_gd_place_detail where post_id = ".$res_id[$i]."";
		$result_tamzang = $wpdb->get_results($tamzang_sql, ARRAY_A );
		$tamzang_id = $result_tamzang[0]['geodir_tamzang_id'];
		$res_name = $result_tamzang[0]['post_title'];
		
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $res_name, true),FILE_APPEND);
		
		// Get list of driver_id from driver_of_restaurant
		
		$win_id = array();
		$driver_sql = "SELECT * FROM driver_of_restaurant where Tamzang_id = ".$tamzang_id."";
		$result_driver = $wpdb->get_results($driver_sql, ARRAY_A );
		$win_id[] = $result_driver[0]['win_1'];
		$win_id[] = $result_driver[0]['win_2'];
		$win_id[] = $result_driver[0]['win_3'];
		$win_id[] = $result_driver[0]['win_4'];
		$win_id[] = $result_driver[0]['win_5'];
		
		// Get driver name from driver
		$driver_name = array();
		for($j=0; $j<5;$j++)
		{
			$driver_name_sql = "SELECT * FROM driver where Driver_id = ".$win_id[$j]."";
			$result_driver_name = $wpdb->get_results($driver_name_sql, ARRAY_A );
			$driver_name[] = $result_driver_name[0]['name'];
		}
		
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_name, true));
		
		// Get status from driver_order_log
		
		$status_sql = "SELECT * FROM driver_order_log where driver_order_id = ".$order_id[$i]." and status = '1'";
		$result_status = $wpdb->get_results($status_sql, ARRAY_A );
		$driver_id = $result_status[0]['driver_id'];

		
		
	//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $order_id[$i], true));
	?>
	


<?php
		}
	}
}

?>
	<table>
	<tr>
		<th>User_id</th>
		<th>Name</th>
		<th>Note</th>
		<th>Phone</th>
		<th>Approve</th>
	</tr>
	<tr><td><?php echo $usr_id; ?></td><td><?php echo $name; ?></td><td><?php echo $phone; ?></td><td><?php echo $note; ?></td><td> <input type="checkbox" id="priority1_<?php echo $usr_id; ?>" name="checkapprove[]" value="<?php echo $usr_id;?>"></td></tr>
</table>