<?php /* Template Name: driver-exit_head */ ?>

<?php


$sup_id = get_query_var('superDriver_id');
$sup_name = get_query_var('superDriver_name');
file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Driver-exit_head ".$sup_name, true),FILE_APPEND);
?>



<div id="main_order">
		<div class="modal-header">
			<h4 class="modal-title" id="myDriverId">หมายเลขไอดีของคุณคือ : <?php echo get_current_user_id() ;?></h4>
		</div>
		<div class="modal-body">
		<p>ขออภัยขณะนี้คุณมี Driver หมายเลข : <?php echo $sup_id ;?><b><i class="order-text"></i></b></p>
		<p> ชื่อ : <?php echo $sup_name;?> เป็นหัวหน้าอยู่แล้ว  </p>
		<p>หากต้องการทำการเพิกถอนบุคคลนี้จากการเป็นหัวหน้ากรุณาติดต่อ เจ้าหน้าที่ 'ตามสั่ง' <b></b></p>
        </div>
</div>
