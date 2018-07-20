<?php /* Template Name: ajax-order-status */ ?>

<?php


if ($order_status == 99){
?>

<div class="order-row" style="text-align:center;">
  <h1>ยกเลิก</h1>
</div>

<?php
}
else{
 ?>

 <div class="stepwizard">
   <div class="stepwizard-row">
       <div class="stepwizard-step">
           <button type="button" class="btn btn-<?php echo ($order_status >= 1 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">1</button>
           <p>รอการจ่ายเงิน</p>
       </div>
       <div class="stepwizard-step">
           <button type="button" class="btn btn-<?php echo ($order_status >= 2 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">2</button>
           <p>ยืนยันการจ่ายเงิน</p>
       </div>
       <div class="stepwizard-step">
           <button type="button" class="btn btn-<?php echo ($order_status >= 3 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">3</button>
           <p>ทำการจัดส่งแล้ว</p>
       </div>
   </div>
 </div>


<?php
}
?>
