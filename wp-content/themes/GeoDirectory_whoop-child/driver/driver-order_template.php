<?php /* Template Name: driver-order_template */ ?>
<?php

global $wpdb, $current_user;
$order = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT orders.id,orders.post_id,orders.adjust_accept,orders.driver_adjust,orders.total_amt,driver_order_log_assign.status,
        orders.status as order_status,driver_order_log_assign.Id as log_id, orders.cancel_code, orders.redeem_point
        FROM orders
        INNER JOIN driver_order_log_assign ON orders.id = driver_order_log_assign.driver_order_id and driver_id = %d 
        and (driver_order_log_assign.status = 1 OR driver_order_log_assign.status = 2)", $current_user->ID)
    );

$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT is_ready, redeem_rate FROM driver where Driver_id = %d ", array($current_user->ID)
    )
);
?>
<div class="order-row">
	<div class="order-col-6" id= "mylocationicon">
		<button onclick="getLocation()">ยืนยันตำแหน่ง</button>
		<p style="color: red;"><em>สำหรับผู้ส่งอิสระกรุณากดปุ่มเพื่อยืนยันความพร้อมก่อนรับอาหาร</p></em>
	</div>
	<div class="order-col-6" style="text-align:right;">
        <button class="btn <?php echo $driver->is_ready ? 'btn-success' : 'btn-danger';?> driver-ready" data-d-id="<?php echo $current_user->ID; ?>"
            data-nonce="<?php echo wp_create_nonce( 'driver_ready_'.$current_user->ID); ?>"
            ><?php echo $driver->is_ready ? 'พร้อมรับงาน' : 'ไม่พร้อมรับงาน';?></button>
            <p style="color: red;"><em><?php echo $driver->is_ready ?'สถานะ: ขณะนี้คุณจะไม่ได้รับคำสั่งซื้อเพราะไม่พร้อมทำงาน':'สถานะ: ขณะนี้คุณกำลังรอรับคำสั่งซื้อ';?></p></em>

            <button class="btn btn-default transaction_details" >transaction details</button>
	</div>
	<div class="order-clear"></div>

</div>

<?php if(!empty($order)){
    $title = get_the_title($order->post_id);
    ?>

<div class="modal fade" id="confirm-adjust" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel2">ยืนยันการปรับราคา</h4>
            </div>
            <div class="modal-body">
                <p class="adjust-text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-success btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<div class="order-row" style="text-align:center;">
    <?php if($order->status == 2){?>
        <img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/pass.png" />ยืนยันรับคำสั่งซื้อ
    <?php } ?>
</div>
<div class="order-clear"></div>
<br>
<div class="panel panel-default" id="panel_<?php echo $order->id; ?>">
<div class="panel-heading" role="tab" id="heading_<?php echo $order->id; ?>">
    <h4 class="panel-title">

        <div class="order-row">
            <div class="order-col-6">
                #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php echo $title; ?></a>
                <?php 
                    $lat = geodir_get_post_meta( $order->post_id, 'post_latitude', true );
                    $long = geodir_get_post_meta( $order->post_id, 'post_longitude', true );
                    driver_map($title, $lat, $long);
                ?>
            </div>
			<div class="order-col-6">
                <?php

                $shipping_address = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM shipping_address where order_id = %d ", array($order->id)
                    )
                );
                $shipping_price = $shipping_address->price;
                if($wpdb->num_rows > 0)
                {
                  echo "ที่อยู่ผู้รับ: ".$shipping_address->address." ".$shipping_address->district." ".$shipping_address->province." ".$shipping_address->postcode."<br>";
                  echo "เบอร์โทรศัพท์: ".$shipping_address->phone;
				  driver_map("", $shipping_address->ship_latitude, $shipping_address->ship_longitude);
                }

                ?>
            </div>

            <div class="order-clear"></div>
        </div>
    </h4>
</div>

<div class="panel-body">

    <?php
        $OrderItems  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM order_items where order_id =%d ",
                array($order->id)
            )
        );
        foreach ($OrderItems as $product) {
        ?>
            <div class="order-row">
                <h4 class="product-name"><strong><?php echo $product->product_name; ?></strong></h4>
            </div>
            <div class="order-clear"></div>
            <div class="order-row">
                <div class="order-col-6">
                    <strong><?php echo str_replace(".00", "",number_format($product->price,2)); ?> <span class="text-muted">x</span> <?php echo $product->qty; ?></strong>
                </div>
                <div class="order-col-2">
                    <strong>รวม</strong>
                </div>
                <div class="order-col-4">
                    <strong><?php echo str_replace(".00", "",number_format($product->price*$product->qty,2)); ?> บาท</strong>
                </div>
            </div>
            <div class="order-clear"></div>
            <hr>
        <?php
        }
        ?>
            <?php if($order->status == 2){?>

                <?php if($order->adjust_accept){?>

                            <div class="order-row">
                                <font color="green"><b>ลูกค้ายอมรับแล้ว</b></font>
                                <div class="order-col-6" style="float:right;">
                                
                                <div class="order-col-6" style="text-align:right;">
                                    <strong>ราคาเพิ่มเติม</strong>
                                </div>
                                <div class="order-col-2">
                                    <strong>รวม</strong>
                                </div>
                                <div class="order-col-4">
                                    <strong><?php echo str_replace(".00", "",number_format($order->driver_adjust,2)); ?> บาท</strong>
                                </div>

                                </div>
                            </div>
                            <div class="order-clear"></div>
                            <hr>

                <?php } else{ 
                            if($order->order_status < 3){
                                if(!empty($order->driver_adjust)) {?>

                                    <div class="order-row">
                                        <font color="#eb9316"><b>รอลูกค้ายอมรับ</b></font>
                                    </div>
                                    <div class="order-clear"></div>
                                    <hr>

                                <?php }else{ ?>
                                    <div class="order-row" id="order_adjust_<?php echo $order->id; ?>">
                                        <div class="order-col-4">
                                            <input type="text" id="adjust_<?php echo $order->id; ?>" value="">
                                        </div>
                                        <div class="order-col-4">
                                            <button class="btn btn-success adjust-price" href="#" 
                                            data-id="<?php echo $order->id; ?>" data-log_id="<?php echo $order->log_id; ?>"
                                            data-nonce="<?php echo wp_create_nonce( 'driver_adjust_price_'.$order->id); ?>" 
                                            data-toggle="modal" data-target="#confirm-adjust" >เพิ่มราคา</button>
                                        </div>
                                    </div>
                                    <div class="order-clear"></div>
                                    <hr>
                                <?php }
                            } ?>

                <?php } ?>

            <?php } ?>

            <div class="order-row">
                <h4>ระยะทาง <?php echo $shipping_address->distance; ?> กิโลเมตร</h4>
            </div>
            <div class="order-clear"></div>
            <div class="order-row">
                <div class="order-col-6">
                    <?php 
                        if($order->redeem_point){
                            echo "<br>ลูกค้าใช้ point แทนค่าส่งเป็นเงิน <strong>".str_replace(".00", "",number_format($shipping_price,2))."</strong> บาท";
                            echo "<br>คุณจะได้รับ เครดิต <strong>".str_replace(".00", "",number_format($shipping_price * $driver->redeem_rate,2))."</strong> บาทจาก point นี้";
                        }else{
                            echo "<strong>ราคาค่าจัดส่ง</strong>";
                        }
                    ?>
                </div>
                <div class="order-col-6" style="text-align:right;">
                    <?php 
                        if(!$order->redeem_point)
                            echo "<strong>".str_replace(".00", "",number_format($shipping_price,2))." บาท</strong>";
                    ?>
                </div>
            </div>
            <div class="order-clear"></div>
            <hr>

            <div class="order-row" style="text-align:right;">
                <div class="order-col-6">
                    <h4>ทั้งหมด</h4>
                </div>
                <div class="order-col-6">
                    <h4><strong><?php //echo ($order->adjust_accept ? $order->total_amt+$order->driver_adjust+$shipping_price : $order->total_amt+$shipping_price); 
                    $sum = $order->total_amt;

                    if($order->adjust_accept)
                        $sum += $order->driver_adjust;

                    if(!$order->redeem_point)
                        $sum += $shipping_price;

                    echo str_replace(".00", "",number_format($sum,2));
                    
                    ?></strong> บาท</h4>
                </div>
            </div>

    </div>

    <div class="panel-footer">

        
        <?php if($order->status == 2){ ?>
            <div class="order-row" >
                <div class="order-col-6" style="text-align:left;min-height:1px;">
                    <?php 
                    if($order->order_status == 2){
                        if($order->cancel_code == ""){ ?>
                        <button class="btn btn-danger" href="#" data-id="<?php echo $order->id; ?>" data-text="<?php echo '#'.$order->id.'ร้าน'.$title; ?>"
                                data-log_id="<?php echo $order->log_id; ?>"
                                data-nonce="<?php echo wp_create_nonce( 'driver_cancel_order_'.$order->id); ?>"
                                    data-toggle="modal" data-target="#cancel-order" >ยกเลิกคำสั่งซื้อ</button>
                        <?php }else if ($order->cancel_code == "ok"){
                                echo "<font color='green'><b>ยืนยันคำสั่งซื้อเรียบร้อย</b></font>";
                                }else{
                                    echo "<h4>รหัสยืนยันคำสั่งซื้อ: ".$order->cancel_code."</h4>";
                                }
                        }
                    ?>
                </div>
                <div class="order-col-6" style="text-align:right;">
                    <button class="btn btn-success driver-step"
                    data-id="<?php echo $order->id; ?>" data-nonce="<?php echo wp_create_nonce( 'driver_next_step_'.$order->id); ?>" 
                    ><?php echo driver_text_step($order->order_status); ?></button>
                </div>
            </div>
            <div class="order-clear"></div>
        <?php }else{ ?>
            <div class="order-row" style="text-align:center;">
                <span id="confirm_button_<?php echo $order->id; ?>">
                    <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>" data-text="<?php echo '#'.$order->id.'ร้าน'.$title; ?>"
                        data-log_id="<?php echo $order->log_id; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'driver_confirm_order_'.$order->id); ?>"
                        data-toggle="modal" data-target="#confirm-order" >รับคำสั่งซื้อ</button>
                </span>
            </div>
        <?php } ?>
        

    </div>

</div>


<?php
if($order->status == 2){
$arrEmployees = $wpdb->get_results(
    /*
    $wpdb->prepare(
        "SELECT driver.driver_id,driver.driver_name FROM driver 
        WHERE Supervisor=%d 
        and driver.driver_id NOT IN (SELECT driver_id FROM driver_order_log WHERE driver_order_id=%d and status=4)",
        array($current_user->ID,$order->id)
    )
    */
    // Bank Adjust sql for driver who on task can't not recive any more order
    
    $wpdb->prepare(
        "SELECT driver.driver_id,driver.driver_name FROM driver 
        WHERE Supervisor=%d
        and driver.driver_id NOT IN (SELECT DISTINCT driver_id FROM driver_order_log WHERE driver_order_id=%d or status IN (1,2))",
        array($current_user->ID,$order->id)
    )
);

if(!empty($arrEmployees)) {
?>

<div class="panel panel-default">
  <div class="panel-body">

    <div class="order-row">
        <strong>ส่งงานต่อไปให้:</strong>
    </div>
    <div class="order-clear"></div>

    <div class="order-row" style="text-align:left;">
        <select id="assign-employee">
            <?php foreach ( $arrEmployees as $employee ){?>
                <option value="<?php echo $employee->driver_id; ?>"><?php echo $employee->driver_name; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="order-clear"></div>

    <div class="order-row" style="text-align:center;">
        <button class="btn btn-success assign-order" href="#" 
        data-id="<?php echo $order->id; ?>" data-log_id="<?php echo $order->log_id; ?>" 
        data-nonce="<?php echo wp_create_nonce( 'supervisor_assign_order_'.$order->id); ?>" 
        >ส่งงาน</button>
    </div>
    <div class="order-clear"></div>

  </div>
</div>
<?php  }//if(!empty($arrEmployees)) ?>

<?php }// ($order->status == 2)

}// if(!empty($order))
?>
<div class="order-row">
    <div class="order-col-6" id= "chooseHeadDriver">
        <button style="<?php echo wp_is_mobile() ? 'white-space: nowrap;' : 'text-align:right;';?>">เลือกหัวหน้ากลุ่ม</button>
		<p style="color: green;">หมายเลขไอดีของคุณคือ :  <?php echo $current_user->ID ; ?></p>
		<p style="color: green;"><em>ใช้หมายเลขไอดีของคุณทำการตั้งกลุ่มกับบุคคลอื่นๆเพื่อที่คุณสามารถรับ order จากหัวหน้าของคุณได้</p></em>
	</div>
	<div class="order-clear"></div>
</div>
