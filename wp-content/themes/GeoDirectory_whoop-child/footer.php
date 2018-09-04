
<?php

if ( is_single() ) {
  if ( is_user_logged_in() ){
    //global $current_user;
    //$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);
    ?>
    <script>
    jQuery(document).ready(function($){

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
          console.log($("#"+id+"-price").text());
          var nonce = $button.data( 'nonce' );
          var send_data = 'action=update_product_cart&id='+id+'&nonce='+nonce+'&qty='+newVal;
          $.ajax({
            type: "POST",
            url: geodir_var.geodir_ajax_url,
            data: send_data,
            success: function(msg){
                  var total = msg;
                  console.log( "Data deleted: " + JSON.stringify(msg) );
                  console.log( "Data deleted: " + total.data );
                  $("#tamzang_cart_count").html(total.data);
                  $button.closest('.sp-quantity').find("input.quntity-input").val(newVal);
                  $("#"+id+"-total").text(parseInt($("#"+id+"-price").text())*parseInt(newVal));

                  columnTh = $("table th:contains('ทั้งหมด')");
                  columnIndex = columnTh.index() + 1;
                  var sum = 0;
                  $('table tr td:nth-child(' + columnIndex + ')').each(function() {
                    //var column = $(this).html();
                    var total = $("[id$='-total']", $(this).html());
                    if (typeof total.html() !== "undefined")
                      sum += parseInt(total.html());
                    //console.log( found.html() );
                  });
                  $("#sum").text(sum);

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
                console.log( "Data deleted: " + JSON.stringify(msg) );
                $('#' + id).remove();
                var total = msg;
                $("#tamzang_cart_count").html(total.data);

                columnTh = $("table th:contains('ทั้งหมด')");
                columnIndex = columnTh.index() + 1;
                var sum = 0;
                $('table tr td:nth-child(' + columnIndex + ')').each(function() {
                  //var column = $(this).html();
                  var total = $("[id$='-total']", $(this).html());
                  if (typeof total.html() !== "undefined")
                    sum += parseInt(total.html());
                  //console.log( found.html() );
                });
                $("#sum").text(sum);


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


        // setTimeout(function() {
        //     $modalDiv.modal('hide').removeClass('loading');
        // }, 1000)

        //console.log(id);
        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
            $('.btn-ok', this).data('recordNonce', data.recordNonce);
            //console.log(data);
        });


        // check where the shoppingcart-div is
        var offset = $('#tamzang-shopping-cart').offset();
        $(window).scroll(function () {
            var scrollTop = $(window).scrollTop();
            // check the visible top of the browser
            if (offset.top<scrollTop) {
                $('#tamzang-shopping-cart').addClass('tamzang-cart-fixed');
            } else {
                $('#tamzang-shopping-cart').removeClass('tamzang-cart-fixed');
            }
        });

    });
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

    <div class="tamzang_cart" id="tamzang-shopping-cart">
      <div class="wrapper-loading" id="table-my-cart">
        <?php get_template_part( 'ajax-cart' ); ?>
      </div>
      <div style="float:right;">
        <form action="<?php echo home_url('/my-order/'); ?>" method="post" name="product_form">
          <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>"/>
          <button type="submit" class="btn btn-success">
              สั่งเลย <span class="glyphicon glyphicon-play"></span>
          </button>
        </form>
      </div>
    </div>

    <?php
  }
}


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
