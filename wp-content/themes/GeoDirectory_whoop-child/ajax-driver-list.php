<?php /* Template Name: ajax-driver-list */ ?>
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
    <script>
      $(function () {

        $('form').on('submit', function (e) {

          e.preventDefault();
          //console.log("AJAX @ Driver-list work!!");
		var request_method = $(this).attr("method"); //get form GET/POST method
		var form_data = $(this).serialize(); //Encode form elements for submission
		/* Check checkbox is check*/
		var cbone = $("input#priorityOne");
		if(cbone.is(":checked"))
		{
			var name = $('input[name=one]').val();
		}
		var cbtwo = $("input#priorityTwo");
		if(cbtwo.is(":checked"))
		{
			var name = $('input[name=two]').val();
		}
		var cbthree = $("input#priorityThree");
		if(cbthree.is(":checked"))
		{
			var name = $('input[name=three]').val();
		}		
		
		
		var driver_id = $("#driverID").val();
		var tamzang_id = $("#TamzangId").val();
		//console.log("AJAX @ Check Box Value is "+form_data);
		  
          $.ajax({
            type: "POST",
            url: ajaxurl,
            //data: {"action": "update_driver_piority", form_data},
			data:form_data,
            success: function () {
              alert('form was submitted');
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
$num_driver = get_query_var('total_driver');
$driver_id_array = get_query_var('driver_id');
$driver_id = explode(",", $driver_id_array);
$tamzang_id = get_query_var('tamzang_id');
//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver_id[1], true));
if($num_driver > 0)
{
	//Get Shop Latitude and Longitude from GD_place_detail
		$post_point = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_latitude,post_longitude FROM wp_geodir_gd_place_detail where post_id = %d ", array($tamzang_id)
			)
		);
		
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Inside Loop start", true));
	for($i=0; $i<$num_driver; $i++)
	{
		if($driver_id[$i] != "res_id")
		{
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "INSIDE FOR START", true),FILE_APPEND);
		
		// Get Driver detail from Table
		
		$sql = "SELECT * FROM driver where Driver_id = ".$driver_id[$i];
		$result_driver = $wpdb->get_results($sql, ARRAY_A );
		$driver_name = $result_driver[0]['driver_name'];
		$driver_lat = $result_driver[0]['latitude'];
		$driver_lng = $result_driver[0]['longitude'];
		$distance = round(distance($driver_lat, $driver_lng, $post_point->post_latitude, $post_point->post_longitude,"K"),2);
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_name, true),FILE_APPEND);
	?>
	<div class="driver-row">
		<div class="driver-step-<?php echo $driver_id[$i]; ?>">
			<p><?php echo $driver_name; ?> ระยะห่างจากร้าน :<?php echo $distance;?> กิโลเมตร</p>
			<form id="driverForm">
			<input type="radio" id="priority" name="priority" value="1"> 1st
			<input type="radio" id="priority" name="priority" value="2"> 2nd
			<input type="radio" id="priority" name="priority" value="3"> 3rd
			<input type="radio" id="priority" name="priority" value="4"> 4th
			<input type="radio" id="priority" name="priority" value="5"> 5th
			<input type="hidden" id="driverID" name="driverID" value="<?php echo $driver_id[$i]; ?>"/>
			<input type="hidden" id="TamzangId" name="TamzangId" value="<?php echo $tamzang_id; ?>"/>
			<input type="hidden" name="action" value="update_driver_piority" />
			<input name="submit" type="submit" value="Submit" />
			</form>
		</div>
	</div>
<?php
		}
	}
}
?>
