<?php /* Template Name: ajax-driver-assign */ ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script>
	function Abort_order(order_id) {
		
		//var driver_id = document.getElementById("priority").value;
		var del=confirm("ยืนยันการยกเลิกคำสั่งซื้อของลูกค้า (หมายเหตุ การยกเลิกคำสั่งซื้อถือเป็นสิ้นสุด)");
		if (del==true)
		{		
			var status = document.getElementById("abortBtn").value;
			var send_data = 'action=assign_order_driver&status='+status+'&orderID='+order_id;
			console.log( "Abort Data "+send_data);
			
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
		
			//alert ("record deleted")
		}
		return del;


	}
	
	function Cancel_order(order_id) {
		
		//var driver_id = document.getElementById("priority").value;
		
		var status = document.getElementById("cancelBtn").value;
		var send_data = 'action=assign_order_driver&status='+status+'&orderID='+order_id+"&priority=0";
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
	// submit function
	$(function () {

    $('form').on('submit', function (e) {
			e.preventDefault();

			/* Check checkbox is check*/
			//console.log("order id is : "+order_id);
			/*
			if($("#priority1_"+order_id).is(":checked"))
			{
				console.log( "P1 is check data is "+ $("#priority1_"+order_id).val());
				var driver_id = $("#priority1_"+order_id).val();
			}
      */   
			var request_method = $(this).attr("method"); //get form GET/POST method
			var form_data = $(this).serialize(); //Encode form elements for submission
			if(form_data.includes("priority")||form_data.includes("driverID"))
				console.log("Checked ");
			else
			{
				alert('You must Choose Driver to Assign');
				throw new Error('You must Choose Driver to Assign');
			}
			//console.log("Check data "+form_data);
      $.ajax({
        type: "POST",
				url: ajaxurl,
        //data: {"action": "update_driver_piority", form_data},
				data:form_data,
        success: function (resultPhp) {
					console.log("success !! ");
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

$num_order = get_query_var('total_order');
$res_id_array = get_query_var('res_id');
$res_id = explode(",", $res_id_array);
$order_id_array = get_query_var('order_id');
$order_id = explode(",", $order_id_array);


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
			$driver_name[] = $result_driver_name[0]['driver_name'];
		}
		
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_name, true));
		
		// Get status from driver_order_log
		
		$status_sql = "SELECT * FROM driver_order_log where driver_order_id = ".$order_id[$i]." and status IN ('1','2')";
		$result_status = $wpdb->get_results($status_sql, ARRAY_A );
		$driver_id = $result_status[0]['driver_id'];
		$driver_id = ($driver_id == null)?"New order":$driver_id;
		
		//Get Driver Name
		$driver_on_order = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT driver_name FROM driver WHERE Driver_id=%d", array($driver_id)
			)
		);
		
		//Get status of order
		$order_status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM orders WHERE id=%d", array($order_id[$i])
			)
		);
		if($order_status == 1)
			$order_text = "ขณะนี้กำลังรอ Driver ยืนยัน Order";
		else if($order_status == 2)
			$order_text = "Driver รับ Order เรียบร้อย";
		else
			$order_text = "Driver กำลังทำการส่งสินค้าให้ผู้ซื้อ";
		
		
	//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver_id, true));
	?>
	
	<div class="driver-row">
		<p><?php echo "Order ID : ".$order_id[$i]; ?></p>
		<p><?php echo "Tamzang ID: ".$tamzang_id."	ชื่อร้าน : ".$res_name; ?></p>
		<form id="driverForm">
		<?php echo $showdriver = ($driver_id == "New order")?"<p style='color: red;'> มอบหมาย Order ให้กับ <br>":"<p style='color: green;'>ขณะนี้ได้มอบหมาย Order นี้ให้กับ ".$driver_on_order."<br>"; ?></p>
		<p><?php echo $driver_name[0]; ?>
		<input type="radio" id="priority1_<?php echo $order_id[$i]; ?>" name="priority" value="<?php echo $win_id[0]; ?>"> 1st
		<?php
			if($driver_id == $win_id[0])
				echo $order_text;
		?>
		</p>
		<p>
		<?php echo $driver_name[1]; ?>
		<input type="radio" id="priority2_<?php echo $order_id[$i]; ?>" name="priority" value="<?php echo $win_id[1]; ?>"> 2nd
		<?php
			if($driver_id == $win_id[1])
				echo $order_text;
		?>
		</p>
		<p>
		<?php echo $driver_name[2]; ?>
		<input type="radio" id="priority3_<?php echo $order_id[$i]; ?>" name="priority" value="<?php echo $win_id[2]; ?>"> 3rd
		<?php
			if($driver_id == $win_id[2])
				echo $order_text;
		?>
		</p>
		<p>
		<?php echo $driver_name[3]; ?>
		<input type="radio" id="priority4_<?php echo $order_id[$i]; ?>" name="priority" value="<?php echo $win_id[3]; ?>"> 4th
		<?php
			if($driver_id == $win_id[3])
				echo $order_text;
		?>
		</p>
		<p>
		<?php echo $driver_name[4]; ?>
		<input type="radio" id="priority5_<?php echo $order_id[$i]; ?>" name="priority" value="<?php echo $win_id[4]; ?>"> 5th
		<?php
			if($driver_id == $win_id[4])
				echo $order_text;
		?>
		</p>
		<input type="text" name="driverID"> :สำหรับกรณีฉุกเฉิน Driver ทำการร้องขอให้มอบหมายให้กับ Driver คนอื่นโดยระบุ ID ประจำตัวมา<br>
		<input type="hidden" id="orderID_<?php echo $order_id[$i]; ?>" name="orderID" value="<?php echo $order_id[$i]; ?>"/>
		<input type="hidden" id="TamzangId_<?php echo $order_id[$i]; ?>" name="TamzangId" value="<?php echo $res_id[$i]; ?>"/>
		<input type="hidden" name="action" value="assign_order_driver" />
		<input name="submit" type="submit" value="Submit" />
		</form>
		<button id="cancelBtn" name="cancelBtn" value="Cancel" onclick="Cancel_order(<?php echo $order_id[$i]; ?>)">Cancel Current Assign</button>
		<button id="abortBtn" name="abortBtn" value="Abort" onclick="Abort_order(<?php echo $order_id[$i]; ?>)">Abort Order</button>
	</div>
<?php
		}
	}
}

?>
