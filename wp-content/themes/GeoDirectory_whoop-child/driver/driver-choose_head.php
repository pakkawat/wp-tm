<?php /* Template Name: driver-choose_head */ ?>
<?php

?>

<script>
jQuery(document).ready(function($){
	
	// submit function
	$(function () {

        $('form').on('submit', function (e) {
			var del=confirm("ยืนยันการเลือกหัวหน้ากลุ่ม(หมายเหตุหากต้องการยกเลิกให้ติดต่อเจ้าหน้าที่ 'ตามสั่ง' โดยตรงเท่านั้น)");
			if(del==true)
			{
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
					alert("ยืนยันการเลือกหัวหน้าเรียบร้อย");
					location.reload();
					}
				});
			}
		});
    });
	
});

</script>

<div id="main_order">
		<div class="modal-header">
			<h4 class="modal-title" id="myDriverId">หมายเลขไอดีของคุณคือ : <?php echo get_current_user_id() ;?></h4>
		</div>
		<div class="modal-body">
		<form id="chooseSuperDriverForm">
			Driver ID : <input type="textbox" id="driverSuperID" name="driverSuperID"></input>
			<p>กรุณาระบุหมายเลขไอดีของ Driver ที่คุณต้องการให้เป็นหัวหน้า <b><i class="order-text"></i></b></p>
			<input type="hidden" name="driverID" value="<?php echo get_current_user_id() ;?>"/>
			<input type="hidden" name="action" value="update_head_driver"/>
			<input name="submit" type="submit" value="ยืนยัน" />
		</form>
        </div>        
</div>
