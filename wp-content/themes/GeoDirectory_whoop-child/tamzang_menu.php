<?php /* Template Name: tamzang_menu */ 
global $wpdb;

if ( !isset( $_GET['pid'] ) || empty(get_post( $_GET['pid'] ))) {
    return;
}



// $order_log = $wpdb->get_results(
//     $wpdb->prepare(
//         "SELECT * FROM driver_order_log ORDER by driver_order_id desc  ", array()
//     )
// );
$pid = $_GET['pid'];

$query_args = array(
  'is_geodir_loop' => true,
  'post_type' => 'gd_product',
  'posts_per_page' => -1,
  'order_by' => 'default_category_ASC'
);

add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);

$uploads = wp_upload_dir();

$arrProducts = geodir_get_widget_listings($query_args);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>ตามสั่ง</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo '<script type="text/javascript">var ajaxurl = "' .admin_url('admin-ajax.php'). '";</script>';?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>

<style>
.order-status-loading:before {
display: flex;
flex-direction: column;
justify-content: center;
content: 'รอสักครู่...';
text-align: center;
font-size: 20px;
background: rgba(0, 0, 0, .8);
position: absolute;
top: 0px;
bottom: 0;
left: 0;
right: 0;
color: #EEE;
z-index: 1000;
width: 100%;
height: 100%;
}

@media only screen and (min-width: 990px) {
  .image-type-lg{
    height:100%;
  }
}

@media only screen and (max-width: 990px) {
  .image-type{
    height:387px;
  }
}
</style>



<script>

$(function () {
  var inputs = $('[data-toggle="popover"]');

  // Popover init
  inputs.popover({
      'content'   : $('#popover-content').html(),
      'html'      : true,
      'placement' : 'right',
      'trigger': 'click',

  });

  inputs.on('show.bs.popover', function() {
    console.log($(this).data('pid'));
    inputs.not(this).popover('hide');
    //$('#popover-content').html($(this).data('pid'));
    inputs.attr('data-content', $(this).data('pid'));
  });

})

jQuery(document).ready(function() {

});


</script>
<body>
<div id="popover-content" style="display:none;">
</div>
<div class="container">
  <div class="row justify-content-center">
    <img class="img-fluid" src="https://test02.tamzang.com/wp-content/uploads/2019/05/2637_4.png" class="food-img">
  </div>
<?php

$category = "";
$i = 0;
$count_row = 1;
foreach ( $arrProducts as $product ){
  // echo print_r($product);
  // echo "<br><br><hr>";
  if($product->default_category != $category)
  {
    echo '<div style="padding: 20px;"><div class="row p-2" style="background-color: rgba(240,240,240,.9);" data-toggle="collapse" data-target="#collapse'.$product->default_category.'">';
    echo '<h3>'.get_term_by('id', $product->default_category, 'gd_productcategory')->name.'</h3>';
    echo '</div>';

    echo '<div class="row collapse show" id="collapse'.$product->default_category.'">';
    echo '<div class="row flex-grow-1">';

    $category = $product->default_category;
    $arr_images = geodir_get_images($product->ID, 'medium', get_option('geodir_listing_no_img'));
    $rand_keys = array_rand($arr_images, 1);
    $rand_image = $arr_images[$rand_keys];
    echo '<div class="col-12 order-1 col-lg-6 order-lg-2">';
      echo '<div class="row ml-lg-3 image-type image-type-lg" style="background-image: url('.$rand_image->src.');"></div>';
    echo '</div>';
    $count_row = 1;
  }

  echo '<div class="col-12 order-2 col-lg-6 order-lg-'.($count_row > 1 ? '3' : '1').' border-top border-bottom" 
        data-toggle="popover" data-pid="'.$product->ID.'">';
    echo '<div class="row py-3">';
      echo '<div class="col-10">';
        echo '<div class="col">';
          echo '<div class="float-left mr-3">';
            echo '<img src="'.$uploads['baseurl'].$product->featured_image.'" style="width: 64px; height: 64px;">';
          echo '</div>';
          echo '<div>';
            echo '<h6>'.$product->post_title.'</h6>';
          echo '</div>';
          echo '<div>';
            echo $product->post_content;
          echo '</div>';
        echo '</div>';
      echo '</div>';
      echo '<div class="col-2">';
        echo str_replace(".00", "",number_format($product->geodir_price,2));
      echo '</div>';
    echo '</div>';
  echo '</div>';

  if($product->default_category != $arrProducts[$i+1]->default_category)
  {
    echo '</div>';// class="row"
    echo '</div>';//class="row collapse show"
    echo '</div>';
  }

  echo '';
  echo '';
  echo '';
  echo '';
  echo '';
  echo '';
  echo '';
  $i++;
  $count_row++;
}// foreach $arrProducts
?>

</div><!-- class="container" -->

</body>
</html>


