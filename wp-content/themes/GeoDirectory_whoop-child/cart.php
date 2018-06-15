<?php /* Template Name: Cart */ ?>

<?php

global $wpdb, $current_user;

if ( !is_user_logged_in() )
  wp_redirect(home_url());

$arrProducts = tamzang_get_all_products_in_cart($current_user->ID);

?>

<?php get_header(); ?>
<script>
jQuery(document).ready(function($){

  $(".btn-tamzang-quantity").on("click", function () {

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

});
</script>
<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header ?>
        <section class="entry-content cf" itemprop="articleBody">



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


          <div class="wrapper-loading">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>ทั้งหมด</th>
                <th> </th>
              </tr>
            </thead>
            <tbody>
              <?php
          			the_content();
                $sum = 0;
                $uploads = wp_upload_dir();

                foreach ($arrProducts as $product) {
                  $total = (int)$product->price*(int)$product->qty;
                  $sum += $total;
                  echo '<tr id="'.$product->product_id.'">';
                    echo "<td>";
                      echo "<div>";
                        echo '<a class="thumbnail pull-left" href="#"> <img class="media-object" src="'.$uploads['baseurl'].$product->featured_image.'" style="width: 72px; height: 72px;"> </a>';
                        echo '<div class="media-body">';
                          echo '<h4 class="media-heading">'.$product->name.'</h4>';
                          echo '<h5 class="media-heading"> ร้าน <a href="'.get_page_link($product->post_id).'">'.get_the_title($product->post_id).'</a></h5>';
                          echo '<span><strong>'.$product->short_desc.'</strong></span>';
                        echo "</div>";
                      echo "</div>";
                    echo "</td>";
                    echo '<td style="text-align: center">';


                      echo '<div class="sp-quantity">';
                      echo '<div class="input-group">';
                      echo '<span class="input-group-btn">';
                      echo '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-id="'.$product->product_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->product_id ).'">';
                      echo '<span class="glyphicon glyphicon-minus"></span>';
                      echo '</button>';
                      echo '</span>';
                      echo '<div class="sp-input">';
                      echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
                      echo '</div>';
                      echo '<span class="input-group-btn">';
                      echo '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-type="plus" data-id="'.$product->product_id.'" data-nonce="'.wp_create_nonce( 'update_product_cart_' . $product->product_id ).'">';
                      echo '<span class="glyphicon glyphicon-plus"></span>';
                      echo '</button>';
                      echo '</span>';
                      echo '</div>';
                      echo '</div>';


                      //echo '<input type="text" class="quntity-input form-control" name="qty" value="'.$product->qty.'">';
                    echo "</td>";
                    echo '<td style="text-align: center">';
                      echo '<strong><div id="'.$product->product_id.'-price" >'.$product->price.'</div></strong>';
                    echo "</td>";
                    echo '<td style="text-align: center">';
                      echo '<strong><div id="'.$product->product_id.'-total" >'.$total.'</div></strong>';
                    echo "</td>";
                    echo "<td>";
                      echo '<a class="btn btn-danger btn-xs" href="#"
                      data-record-id="'.$product->product_id.'"
                      data-record-title="'.$product->name.'"
                      data-record-nonce="'.wp_create_nonce( 'delete_product_cart_' . $product->product_id ).'"
                      data-toggle="modal" data-target="#confirm-delete" style="color:white;" ><span class="glyphicon glyphicon-trash"></span> ลบ</a>';
                    echo "</td>";
                  echo "</tr>";
                }
    		      ?>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><h3>รวมทั้งหมด</h3></td>
                <td class="text-right"><h3><strong><div id="sum"><?php echo $sum; ?></div></strong></h3></td>
              </tr>
            </tbody>
          </table>
            <div style="float:right;">
              <button type="button" class="btn btn-success">
                  ดำเนินการสั่งสินค้า <span class="glyphicon glyphicon-play"></span>
              </button>
            </div>
          </div>
        </section>
        <?php // end article section ?>
        <footer class="article-footer cf"> </footer>
      </article>
      <?php endwhile; else : ?>
      <article id="post-not-found" class="hentry cf">
        <header class="article-header">
          <h1>
            <?php _e( 'Oops, Post Not Found!', GEODIRECTORY_FRAMEWORK ); ?>
          </h1>
        </header>
        <section class="entry-content">
          <p>
            <?php _e( 'Uh Oh. Something is missing. Try double checking things.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </section>
        <footer class="article-footer">
          <p>
            <?php _e( 'This is the error message in the page.php template.', GEODIRECTORY_FRAMEWORK ); ?>
          </p>
        </footer>
      </article>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php get_footer(); ?>
