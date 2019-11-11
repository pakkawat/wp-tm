<?php /* Template Name: select-delivery_type */ ?>

<?php
global $wpdb, $current_user;


?>

<table id="delivery_type_table" class="table table-bordred table-striped">

  <tbody>
  <tr>
    <td>พนักงานส่งจากตามสั่ง</td>
    <td id="delivery_type_1">
      <img src="<?php echo get_stylesheet_directory_uri().'/js/pass.png'; ?>" />
    </td>
  </tr>
  <tr>
    <td>พนักงานส่งประจำร้าน</td>
    <td id="delivery_type_2">
      <a class="btn btn-success select-delivery_type" href="#"
            data-pid="<?php echo $_REQUEST['pid'];?>"
            data-dtype="2"
            data-nonce="<?php echo wp_create_nonce( 'select_delivery_type' . $current_user->ID );?>"
            style="color:white;" >เลือก</a>
    </td>
  </tr>
  </tbody>
</table>