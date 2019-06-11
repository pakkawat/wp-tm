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
  $my_order = get_query_var('my_order');
  $id = get_query_var('id');
  $deliver_ticket = get_query_var('deliver_ticket');


  if($deliver_ticket != 'Y'){
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
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 4 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">4</button>
            <?php
              if($order_status == 3 && $my_order){
                  echo '<p><button class="btn btn-success received-product" href="#" data-id="'.$id.'" data-nonce="'.wp_create_nonce( 'user_received_product_'.$id).'" >ได้รับสินค้าแล้ว</button></p>';
              }else {
                echo '<p>ได้รับสินค้าแล้ว</p>';
              }
            ?>

        </div>
    </div>
  </div>
<?php
  }else{
    ?>

  <div class="stepwizard">
    <div class="stepwizard-row">
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 1 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">1</button>
            <p>ได้รับคำสั่งซื้อแล้ว</p>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 2 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">2</button>
            <p>กำลังดำเนินการ</p>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 3 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">3</button>
            <p>ยืนยันคำสั่งซื้อแล้ว</p>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 4 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">4</button>
            <p>ได้รับสินค้าแล้ว</p>
        </div>
        <div class="stepwizard-step">
            <button type="button" class="btn btn-<?php echo ($order_status >= 5 ? 'primary' : 'default'); ?> btn-circle" disabled="disabled">5</button>
            <p>เสร็จสิ้น</p>
        </div>
    </div>
  </div>

    <?php
  }
}
?>
