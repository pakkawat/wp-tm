
<?php

if ( is_single() ) {
  if ( is_user_logged_in() ){
    $post_type = geodir_get_current_posttype();
    if(($post_type == "gd_place")||($post_type == "gd_product")){
    global $wpdb, $current_user;
    $user_has_address = false;
    $user_address = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array($current_user->ID)
        )
    );

    if($wpdb->num_rows > 0)
    {
      $user_has_address = true;
    }

    ?>
    <script>
    jQuery(document).ready(function($){

      function display_currency(money){
        money = (money).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        parts = money.split(".");
        if(parts[1] > 0)
          money = parts[0]+"."+parts[1];
        else
          money = parts[0];
        return money;
      }

      //$(".btn-tamzang-quantity").on("click", function () {
      jQuery(document).on("click", ".btn-tamzang-quantity", function(){

          var $button = $(this);
          var oldValue = $button.closest('.sp-quantity').find("input.quntity-input").val();

          if ($button.data( 'type' ) == "plus") {
              var newVal = parseFloat(oldValue) + 1;
          } else {
              // Don't allow decrementing below zero
              if (oldValue > 1) {
                  var newVal = parseFloat(oldValue) - 1;
              } else {
                  newVal = 1;
              }
          }
          $('.wrapper-loading').toggleClass('cart-loading');
          var id = $button.data( 'id' );

          var nonce = $button.data( 'nonce' );
          var send_data = 'action=update_product_cart&id='+id+'&nonce='+nonce+'&qty='+newVal;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  if(msg.success){
                    console.log( "Data deleted: " + JSON.stringify(msg) );

                    $button.closest('.sp-quantity').find("input.quntity-input").val(newVal);

                    var total = display_currency(msg.data);
                    $("#"+id+"-total").text(total);
                    var sum = 0;
                    $(".price").each(function() {
                      var value = $(this).text().replace(/,/g, '');
                      // add only if the value is number
                      if(!isNaN(value) && value.length != 0) {
                          sum += parseFloat(value);
                      }
                    });
                    $("#sum").text( display_currency(sum));

                  }

                  $('.wrapper-loading').toggleClass('cart-loading');
                  //console.log( "Data Saved: " + msg );
                  //console.log(tamzang_ajax_settings.ajaxurl);
                  // ถ้า msg = 0 แสดงว่าไม่ได้ login
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
               console.log(textStatus);
               $('.wrapper-loading').toggleClass('cart-loading');
            }
          });



          // $('.wrapper-loading').toggleClass('loading');
          // setTimeout(function() {
          //           $('.wrapper-loading').toggleClass('loading');
          // }, 2000)
      });




      $('#confirm-delete').on('click', '.btn-ok', function(e) {
        var $modalDiv = $(e.delegateTarget);
        var id = $(this).data('recordId');
        var nonce = $(this).data('recordNonce');
        //console.log(id);
        // $.ajax({url: '/api/record/' + id, type: 'DELETE'})
        // $.post('/api/record/' + id).then()
        $modalDiv.addClass('loading');
        var send_data = 'action=delete_product_cart&id='+id+'&nonce='+nonce;
        $.ajax({
          type: "POST",
          url: geodir_var.geodir_ajax_url,
          data: send_data,
          success: function(msg){
                if(msg.success){
                  console.log( "Data deleted: " + JSON.stringify(msg) );
                  $('#' + id).remove();

                  var sum = 0;
                  $(".price").each(function() {
                    var value = $(this).text().replace(/,/g, '');
                    // add only if the value is number
                    if(!isNaN(value) && value.length != 0) {
                        sum += parseFloat(value);
                    }
                  });

                  $("#sum").text(display_currency(sum));


                }

                $modalDiv.modal('hide').removeClass('loading');
                //console.log( "Data Saved: " + msg );
                //console.log(tamzang_ajax_settings.ajaxurl);
                // ถ้า msg = 0 แสดงว่าไม่ได้ login
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
             console.log(textStatus);
             $modalDiv.modal('hide').removeClass('loading');
          }
        });

        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
            $('.btn-ok', this).data('recordNonce', data.recordNonce);
        });


        // $('#select-payment-type').on('show.bs.modal', function(e) {
        //     var data = $(e.relatedTarget).data();
        //     $('.btn-ok', this).data('recordId', data.recordId);
        //
        //     console.log("Modal show!! "+id);
        // });

        $("#place_order").click(function(e){
          var rowCount = $('#tb-cart >tbody >tr').length;
          console.log(rowCount);
          if (rowCount <= 1){
            $("#no-item").modal();
            e.preventDefault();
          }
        });

        // check where the shoppingcart-div is
        var offset = $('#tamzang-shopping-cart').offset();
        $(window).scroll(function () {
            var scrollTop = $(window).scrollTop();
            // check the visible top of the browser
            if (offset.top<scrollTop) {
                $('#tamzang-shopping-cart').addClass('tamzang-cart-fixed');
                $('#tamzang-shopping-cart-button').addClass('tamzang-cart-button-fixed');
            } else {
                $('#tamzang-shopping-cart').removeClass('tamzang-cart-fixed');
                $('#tamzang-shopping-cart-button').removeClass('tamzang-cart-button-fixed');
            }
        });



    });

	function HideShop() {
          var x = document.getElementById("tamzang-shopping-cart");
          if (x.style.display === "none") {
              x.style.display = "block";
          }
        	else if (x.style.display === "block"){
          	x.style.display = "none";
          }
          else {
            x.style.display = "none";
          }
        }
    </script>

    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">ยืนยันการลบสินค้า</h4>
                </div>
                <div class="modal-body">
                    <p>คุณกำลังจะลบสินค้า <b><i class="title"></i></b> ออกจากตะกร้าสินค้า</p>
                    <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger btn-ok">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="no-item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">ข้อความ</h4>
                </div>
                <div class="modal-body">
                    <p>กรุณาเลือกสินค้า</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$user_has_address){ ?>
    <div class="modal fade" id="no-address" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">ข้อความ</h4>
                </div>
                <div class="modal-body">
                    <p>คุณยังไม่ได้เพิ่มที่อยู่ในการจัดส่งกรุณา <a href="<?php echo bp_get_loggedin_user_link().'address/';?>">คลิก</a> เพื่อเพิ่มที่อยู่ในการจัดส่ง</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <p class="tamzang-shopping-cart-button" id="tamzang-shopping-cart-button" onclick="HideShop()">
      <img src="https://www.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/images/shop2.png" alt="ตามสั่ง">
    </p>
    <div class="tamzang_cart" id="tamzang-shopping-cart" <?php //echo (wp_is_mobile()? 'style="background-color: #f5f5f1;"' : ''); ?>>
      <div class="wrapper-loading" id="table-my-cart">
        <?php get_template_part( 'ajax-cart' ); ?>
      </div>
      <div style="float:right;">
        <?php if($user_has_address){ ?>
          <a id="place_order" class="btn btn-success" href="<?php echo home_url('/confirmed-order/').'?pid='.(geodir_get_current_posttype() == 'gd_product'?geodir_get_post_meta(get_the_ID(),'geodir_shop_id',true):get_the_ID()) ?>">
            <span style="color: #ffffff !important;" class="glyphicon glyphicon-play">สั่งเลย</span>
          </a>
        <?php }else{ ?>



          <button class="btn btn-success" data-toggle="modal" data-target="#no-address"
            ><span class="glyphicon glyphicon-play"></span> สั่งเลย</button>


        <?php } ?>
      </div>
    </div>

    <?php
  }// if($post_type == "gd_place")
  }
}
customer_rating();

?>

<footer class="footer" role="contentinfo">
  <?php global $gdf; if(!empty($gdf) && $gdf['footer-widgets']){//print_r($gdf);
			$x = $gdf['footer-widgets'];
			?>

  <div id="widget-footer" class="wrap cf" style="text-align: center;">
    <?php if($gdf['footer-widgets']>0){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-1');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>1){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-2');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>2){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-3');?>
    </div>
    <?php }?>
    <?php if($gdf['footer-widgets']>3){?>
    <div class="f-col-<?php echo $x;?>">
      <?php dynamic_sidebar('footer-4');?>
    </div>
    <?php }?>
  </div>
  <hr />
  <?php }?>
  <div id="inner-footer" class="wrap cf <?php echo (has_nav_menu( 'footer-links' )) ? 'footer-links-active' : ''; ?>">
    <nav role="navigation">
      <?php wp_nav_menu(array(
    					'container' => '',                              // remove nav container
    					'container_class' => 'footer-links cf',         // class of container (should you choose to use it)
    					'menu' => __( 'Footer Links', GEODIRECTORY_FRAMEWORK ),   // nav name
    					'menu_class' => 'nav footer-nav cf',            // adding custom nav class
    					'theme_location' => 'footer-links',             // where it's located in the theme
    					'before' => '',                                 // before the menu
        			'after' => '',                                  // after the menu
        			'link_before' => '',                            // before each link
        			'link_after' => '',                             // after each link
        			'depth' => 0,                                   // limit the depth of the nav
    					'fallback_cb' => 'geodirf_footer_links_fallback'  // fallback function
						)); ?>
    </nav>
    <p class="source-org copyright">
        <?php global $gdf;
        if(!empty($gdf) && $gdf['footer-copyright-text']) {
            if (strstr($gdf['footer-copyright-text'], 'wpgeodirectory.com') && !is_front_page()) {
                echo wp_strip_all_tags($gdf['footer-copyright-text']);
            } else {
                echo $gdf['footer-copyright-text'];
            }
        }
        ?>
    </p>
  </div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
<!-- end of site. what a ride! -->
