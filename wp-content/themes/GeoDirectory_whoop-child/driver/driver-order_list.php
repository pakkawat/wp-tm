<?php /* Template Name: driver-order_list */ ?>
<?php

function get_my_orders($PERPAGE_LIMIT, $pageNumber) {
    global $wpdb, $current_user;
  
    $sql = "SELECT * FROM orders where wp_user_id = ".$current_user->ID." ";
    $sql2 = "SELECT count(id) FROM orders where wp_user_id = ".$current_user->ID." ";
    //$sql = "SELECT * FROM wp_terms ";
    //$sql2 = "SELECT count(term_id) FROM wp_terms ";
  
    // getting parameters required for pagination
    // $currentPage = 1;
    // if(isset($pageNumber)){
    //   $currentPage = $pageNumber;
    // }
    $currentPage = $pageNumber;
    $startPage = ($currentPage-1)*$PERPAGE_LIMIT;
    if($startPage < 0) $startPage = 0;
  
  
    //adding limits to select query
    $sql .= " ORDER BY id DESC limit " . $startPage . "," . $PERPAGE_LIMIT . " ";
    $result  = $wpdb->get_results( $sql );
    $count   = $wpdb->get_var( $sql2 );
    //file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $count, true));
    //echo '<h1>sql: '.$sql.'</h1>';
    //echo '<h1>sql2: '.$sql2.'</h1>';
    return array($result, $count);
}

function pagination($count, $PERPAGE_LIMIT, $pageNumber) {
    $output = '';

    //if(!isset($pageNumber)) $pageNumber = 1;
    if($PERPAGE_LIMIT != 0)
        $pages  = ceil($count/$PERPAGE_LIMIT);

    $output .= '<ul class="pagination pagination-lg">';
    //if pages exists after loop's lower limit
    if($pages>1) {
        if(($pageNumber-3)>0) {
        $output = $output . '<li class="page-item"><a data-page="1">1</a></li>';
        }
        if(($pageNumber-3)>1) {
        $output = $output . '<li class="page-item"><a data-page="'.($pageNumber-1).'"><strong>&lt;</strong></a></li>';
        }

        //Loop for provides links for 2 pages before and after current page
        for($i=($pageNumber-2); $i<=($pageNumber+2); $i++)	{
        if($i<1) continue;
        if($i>$pages) break;
        if($pageNumber == $i)
            $output = $output . '<li class="page-item active"><a>'.$i.'</a></li>';
        else
            $output = $output . '<li class="page-item"><a data-page="'.$i.'">'.$i.'</a></li>';
        }

        //if pages exists after loop's upper limit
        if(($pages-($pageNumber+2))>1) {
        $output = $output . '<li class="page-item"><a data-page="'.($pageNumber+1).'"><strong>&gt;</strong></a></li>';
        }
        if(($pages-($pageNumber+2))>0) {
        if($pageNumber == $pages)
            $output = $output . '<li class="page-item active"><a>' . ($pages) .'</a></li>';
        else
            $output = $output . '<li class="page-item"><a data-page="'.($pages).'">' . ($pages) .'</a></li>';
        }

    }
    $output .= '</ul>';
    return $output;
}

$PERPAGE_LIMIT = 5;
$uploads = wp_upload_dir();

$pageNumber = get_query_var('pageNumber');
if(!isset($pageNumber) || $pageNumber=='') $pageNumber = 1;

?>
<script>

</script>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php

list($arrOrders, $count) = get_my_orders($PERPAGE_LIMIT, $pageNumber);

foreach ($arrOrders as $order){
?>
<div class="panel panel-default" id="panel_<?php echo $order->id; ?>">
    <div class="panel-heading" role="tab" id="heading_<?php echo $order->id; ?>">
        <h4 class="panel-title">

            <div class="order-row">
                <div class="order-col-6">
                    #<?php echo $order->id; ?> ร้าน: <a href="<?php echo get_page_link($order->post_id); ?>"><?php $title = get_the_title($order->post_id);echo $title; ?></a>
                    <?php 
                        $lat = geodir_get_post_meta( $order->post_id, 'post_latitude', true );
                        $long = geodir_get_post_meta( $order->post_id, 'post_longitude', true );
                        driver_map($title, $lat, $long);
                    ?>
                </div>
                <div class="order-col-6" style="text-align:right;">
                    <span id="confirm_button_<?php echo $order->id; ?>">
                    <button class="btn btn-success" href="#" data-id="<?php echo $order->id; ?>" data-text="<?php echo '#'.$order->id.'ร้าน'.$title; ?>"
                        data-nonce="<?php echo wp_create_nonce( 'driver_confirm_order_'.$order->id); ?>"
                        data-toggle="modal" data-target="#confirm-order" >ยืนยันรับคำสั่งซื้อ</button>
                    </span>
                    
                    <a role="button" data-toggle="collapse" data-parent="#accordion" class="btn btn-warning"
                    href="#collapse_<?php echo $order->id; ?>" aria-expanded="false" aria-controls="collapse_<?php echo $order->id; ?>">
                        <span style="color: #ffffff !important;" >ดูรายการ</span>
                    </a>
                </div>
                <div class="order-clear"></div>
            </div>
        </h4>
    </div>
    <div id="collapse_<?php echo $order->id; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_<?php echo $order->id; ?>">
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
                    <div class="order-col-6">
                        <h4 class="product-name"><strong><?php echo $product->product_name; ?></strong></h4>
                    </div>
                    <div class="order-col-6">
                        <div class="order-col-6" style="text-align:right;">
                            <strong><?php echo $product->price; ?> <span class="text-muted">x</span> <?php echo $product->qty; ?></strong>
                        </div>
                        <div class="order-col-2">
                            <strong>รวม</strong>
                        </div>
                        <div class="order-col-4">
                            <strong><?php echo $product->price*$product->qty; ?> บาท</strong>
                        </div>
                    </div>
                </div>
                <div class="order-clear"></div>
                <hr>
            <?php
            }
            ?>

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
                                      <strong><?php echo $order->driver_adjust; ?> บาท</strong>
                                  </div>

                                </div>
                            </div>
                            <div class="order-clear"></div>
                            <hr>

                <?php } else{ 
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
                                        data-id="<?php echo $order->id; ?>" data-nonce="<?php echo wp_create_nonce( 'driver_adjust_price_'.$order->id); ?>" 
                                        data-toggle="modal" data-target="#confirm-adjust" >เพิ่มราคา</button>
                                    </div>
                                </div>
                                <div class="order-clear"></div>
                                <hr>
                            <?php } ?>

                <?php } ?>

                <div class="order-row">
                    <div class="order-col-9" style="text-align:right;">
                        <h4>ทั้งหมด</h4>
                    </div>
                    <div class="order-col-3">
                        <h4><strong><?php echo ($order->adjust_accept ? $order->total_amt+$order->driver_adjust : $order->total_amt); ?></strong> บาท</h4>
                    </div>
              </div>

        </div>
    </div>
</div>
<?php
}
?>
</div>
<footer class="article-footer cf"><?php echo pagination($count, $PERPAGE_LIMIT, $pageNumber); ?></footer>
