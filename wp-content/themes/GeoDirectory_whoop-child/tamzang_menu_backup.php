<?php /* Template Name: tamzang_menu */ 
global $wpdb;

if ( !isset( $_GET['pid'] ) ) {
    return;
}

if(empty(get_post( $_GET['pid'] ))){
    return;
}


// $order_log = $wpdb->get_results(
//     $wpdb->prepare(
//         "SELECT * FROM driver_order_log ORDER by driver_order_id desc  ", array()
//     )
// );

$uploads = wp_upload_dir();

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
</style>



<script>

jQuery(document).ready(function() {

});

</script>
<body>

<?php get_template_part( 'menu-view' );?>

</body>
</html>

<div class="container">
  <div class="row justify-content-center">
    <img class="img-fluid" src="https://test02.tamzang.com/wp-content/uploads/2019/05/2637_4.png" class="food-img">
  </div>

  <div class="row" style=" padding: 10px;" data-toggle="collapse" data-target="#collapseOne">
    <h3>APPETIZER</h3>
  </div>

  <div class="row collapse show" id="collapseOne">

    <div class="col-12 col-lg-6 order-lg-12">
      <img class="img-fluid" src="https://test02.tamzang.com/wp-content/uploads/2019/04/2630_3.png">
    </div>

    <div class="row col-12 col-lg-6 order-lg-1">

      <div class="col-12 border-top border-bottom">
        <div class="row py-3">
          <div class="col-10">
            <div class="col">
              <div class="float-left mr-3">
                <img src="https://test02.tamzang.com/wp-content/uploads/comment_images/4_1543904019.png" style="width: 64px; height: 64px;">
              </div>
              <div>
                <h6>Chicken Wings</h6>
              </div>
              <div>
                Stir-fried fresh thin rice noodles with eggs, bean sprouts, green onion and exclusive Pad Thai sauce with ground peanuts on the side.
              </div>
            </div>
          </div>
          <div class="col-2">
            5.99
          </div>
        </div>
      </div>

      <div class="col-12 border-top border-bottom">
        <div class="row py-3">
          <div class="col-10">
            <div class="col">
              <div class="float-left mr-3">
                <img src="https://test02.tamzang.com/wp-content/uploads/comment_images/3_1543901403.png" style="width: 64px; height: 64px;">
              </div>
              <div>
                <h6>Pad Se-Ew (Not come with rice)</h6>
              </div>
              <div>
                Stir fried wide rice noodle with eggs, Chinese broccoli with your choice of protein
              </div>
            </div>
          </div>
          <div class="col-2">
            5.99
          </div>
        </div>
      </div>

    </div>

  </div>

</div>




<style>
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

<div class="container">
  <div class="row justify-content-center">
    <img class="img-fluid" src="https://test02.tamzang.com/wp-content/uploads/2019/05/2637_4.png" class="food-img">
  </div>

  <div class="row py-2" style="background-color: rgba(240,240,240,.9);" data-toggle="collapse" data-target="#collapseOne">
    <h3>APPETIZER</h3>
  </div>

  <div class="row collapse show" id="collapseOne">

    <div class="row">

      <div class="col-12 order-1 col-lg-6 order-lg-2">
        <div class="row ml-lg-3 image-type image-type-lg" style="background-image: url(https://test02.tamzang.com/wp-content/uploads/2019/04/2630_3.png);">
          <!-- <img class="img-fluid" src="https://test02.tamzang.com/wp-content/uploads/2019/04/2630_3.png"> -->
        </div>
      </div>

      <div class="col-12 order-2 col-lg-6 order-lg-1 border-top border-bottom">
        <div class="row py-3">
          <div class="col-10">
            <div class="col">
              <div class="float-left mr-3">
                <img src="https://test02.tamzang.com/wp-content/uploads/comment_images/4_1543904019.png" style="width: 64px; height: 64px;">
              </div>
              <div>
                <h6>Chicken Wings</h6>
              </div>
              <div>
                Stir-fried fresh thin rice noodles with eggs, bean sprouts, green onion and exclusive Pad Thai sauce with ground peanuts on the side.
              </div>
            </div>
          </div>
          <div class="col-2">
            5.99
          </div>
        </div>
      </div>

      <div class="col-12 order-2 col-lg-6 order-lg-3 border-top border-bottom">
        <div class="row py-3">
          <div class="col-10">
            <div class="col">
              <div class="float-left mr-3">
                <img src="https://test02.tamzang.com/wp-content/uploads/comment_images/3_1543901403.png" style="width: 64px; height: 64px;">
              </div>
              <div>
                <h6>Pad Se-Ew (Not come with rice)</h6>
              </div>
              <div>
                Stir fried wide rice noodle with eggs, Chinese broccoli with your choice of protein
              </div>
            </div>
          </div>
          <div class="col-2">
            5.99
          </div>
        </div>
      </div>

    </div> <!-- id="collapseOne" -->

  </div>

</div>