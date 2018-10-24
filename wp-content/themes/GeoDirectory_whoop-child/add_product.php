<?php /* Template Name: Add Product */ ?>


<?php

function check_integer($value){
  if ((is_int($value) || ctype_digit($value)) && (int)$value >= 0 ){
    return true;
  }
  return false;
}

global $wpdb, $current_user, $gd_session;

$pid = '';
$product_id ='';
$product_name = '';
$is_current_user_owner = false;
$button_text = 'เพิ่มสินค้า';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pid = $_POST['pid'];
  if (isset($pid) && $pid != '') {
    $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
  }
  if (is_user_logged_in() && $is_current_user_owner) {
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    if ((isset($_POST['product_id']) && $_POST['product_id'] != '') && check_integer($price) && check_integer($stock)) { //update Product
      $product_id = $_POST['product_id'];
      $product_name = $_POST['product_name'];
      $short_desc = $_POST['short_desc'];
      $long_desc = $_POST['long_desc'];
      $unlimited = $_POST['unlimited'];
      $current_date = date("Y-m-d H:i:s");

      $wpdb->query($wpdb->prepare("UPDATE products SET  name = %s, short_desc = %s, long_desc = %s, price = %d, stock = %d, unlimited = %d, update_date = %s  where id =%d",
        array($product_name, $short_desc, $long_desc, $price, $stock, $unlimited, $current_date, $product_id)));

      if (isset($_POST['post_images']) && $_POST['post_images'] != '') {
        $newArr = explode(',', $_POST['post_images']);
        file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $newArr, true));
        tamzang_save_images($product_id, $newArr);
      }

      //wp_redirect(get_permalink($pid).'/#product_list');
      wp_redirect(home_url('/add-product/') . '?pid='.$pid.'&product_id='.$product_id);
    }
    else{ // เพิ่ม Product
      $price = $_POST['price'];
      $stock = $_POST['stock'];
      if ((isset($_POST['product_name']) && $_POST['product_name'] != '') && check_integer($price) && check_integer($stock)) {
        //file_put_contents( 'debug' . time() . '.log', var_export( $_POST, true));
        //file_put_contents( dirname(__FILE__).'/debug/debug' . time() . '.log', var_export( $_POST, true));
        $product_name = $_POST['product_name'];
        $short_desc = $_POST['short_desc'];
        $long_desc = $_POST['long_desc'];
        $unlimited = $_POST['unlimited'];
        $current_date = date("Y-m-d H:i:s");

        $wpdb->query($wpdb->prepare("INSERT INTO products SET wp_user_id = %d, post_id = %d, name = %s, short_desc = %s, long_desc = %s, price = %d, stock = %d, unlimited = %d, update_date = %s ",
          array($current_user->ID, $pid, $product_name, $short_desc, $long_desc, $price, $stock, $unlimited, $current_date)));
        $product_id = $wpdb->insert_id;

        //wp_redirect(get_permalink($_REQUEST['pid']));
        //exit;

        if (isset($_POST['post_images']) && $_POST['post_images'] != '') {
          $newArr = explode(',', $_POST['post_images']);
          tamzang_save_images($product_id, $newArr);
        }

        //file_put_contents( dirname(__FILE__).'/debug/debug' . time() . '.log', var_export( $_POST['post_images'], true));
        //file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $newArr, true));
        wp_redirect(get_permalink($pid).'/#product_list');
      }
      //file_put_contents( 'debug' . time() . '.log', var_export( $_POST, true));
    }
  }else{
    wp_redirect(home_url());
  }
}else{
  $pid = $_REQUEST['pid'];
  if (isset($pid) && $pid != '') {
    $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
  }
  if (!is_user_logged_in() && !$is_current_user_owner) {
    wp_redirect(home_url());
  }

  if (isset($_REQUEST['product_id']) && $_REQUEST['product_id'] != '') { // แสดงหน้าแก้ไข Product
    $product_id = $_REQUEST['product_id'];
    $button_text = 'แก้ไขสินค้า';
    $tamzang_product = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM products WHERE id = %d ", array($product_id)
        )
    );
    $product_name = $tamzang_product->name;
    $price = $tamzang_product->price;
    $short_desc = $tamzang_product->short_desc;
    $long_desc = $tamzang_product->long_desc;
    $stock = $tamzang_product->stock;
    $unlimited = $tamzang_product->unlimited;
  }
}


?>


<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-single">
  <?php //geodir_breadcrumb();?>
  <div class="clearfix geodir-common">
    <div id="geodir_content" class="" role="main" style="width: 100%">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">
        <header class="article-header">
          <h1 class="page-title entry-title" itemprop="headline" style="padding-bottom:40px;">
            <div style="width:50%;float:left;">
              <?php the_title(); ?>
            </div>
            <a class="geodir_button" style="float:right;" href="<?php echo home_url('/product-list/').'?pid='.$pid ?>"><span style="color: #ffffff !important;" >หน้ารายการสินค้า</span></a>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header
        //$wp_upload_dir = wp_upload_dir();
        //echo $wp_upload_dir['path'] . '-' . $wp_upload_dir['baseurl'] . '----' . $wp_upload_dir['url'];
        //echo get_permalink($pid);
        ?>
        <section class="entry-content cf" itemprop="articleBody" style="width:67%;">
          <?php the_content(); ?>
          <form action="<?php the_permalink(); ?>" method="post" name="product_form">

            <div class="geodir_form_row clearfix gd-fieldset-details">
               <label>ชื่อสินค้า<span>*</span> </label>
               <input type="text" id = "product_name" name="product_name" value="<?php echo esc_attr(stripslashes($product_name)); ?>">
            </div>
            <div class="geodir_form_row clearfix gd-fieldset-details" style="margin-top:10px;">
              <label>ราคาสินค้า<span>*</span> </label>
              <input type="text" id = "price" name="price" value="<?php echo esc_attr(stripslashes($price)); ?>">
            </div>
            <div class="geodir_form_row clearfix gd-fieldset-details" style="margin-top:10px;">
              <label>รายละเอียดสินค้าแบบย่อ</label>
              <textarea field_type="textarea" name="short_desc" id="short_desc" class="geodir_textarea" maxlength="100"><?php echo $short_desc; ?></textarea>
            </div>
            <div class="geodir_form_row clearfix gd-fieldset-details" style="margin-top:10px;">
              <label>รายละเอียดสินค้า</label>
              <textarea field_type="textarea" name="long_desc" id="long_desc" class="geodir_textarea"><?php echo $long_desc; ?></textarea>
            </div>
            <div class="geodir_form_row clearfix gd-fieldset-details" style="margin-top:10px;">
              <label>จำนวนสินค้า</label>
              <input type="text" id = "stock" name="stock" value="<?php echo esc_attr(stripslashes($stock)); ?>">
            </div>
            <div class="geodir_form_row clearfix gd-fieldset-details" style="margin-top:10px;">
              <label>ไม่จำกัดจำนวนสินค้า?</label>
              <select id="unlimited" name="unlimited">
                <option <?php if ($unlimited == '0') echo ' selected="selected" '; ?> value="0">ไม่</option>
                <option <?php if ($unlimited == '1') echo ' selected="selected" '; ?> value="1">ใช่</option>
              </select>
            </div>
            <?php
            // --------- upload image -------------------
            // adjust values here

            $id = "post_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == �img1� then $_POST[�img1�] will have all the image urls

            $multiple = true; // allow multiple files upload

            $width = geodir_media_image_large_width(); // If you want to automatically resize all uploaded images then provide width here (in pixels)

            $height = geodir_media_image_large_height(); // If you want to automatically resize all uploaded images then provide height here (in pixels)

            $thumb_img_arr = array();
            $curImages = '';
            $totImg = 0;

            if (isset($_REQUEST['product_id']) && $_REQUEST['product_id'] != '') {
                //$thumb_img_arr = tamzang_get_product_images($_REQUEST['product_id']);
                $thumb_img_arr = tamzang_get_product_images($_REQUEST['product_id']);
                if ($thumb_img_arr) {
                    foreach ($thumb_img_arr as $post_img) {
                        $curImages .= $post_img->src . ',';
                    }
                }
            }

            if (!empty($thumb_img_arr)) {
                $totImg = count((array)$thumb_img_arr);
            }

            if ($curImages != '')
                $svalue = $curImages; // this will be initial value of the above form field. Image urls.
            else
                $svalue = '';

            $image_limit = 5;

            ?>
              <h5 id="geodir_form_title_row" class="geodir-form_title">
                  <?php echo '<br /><small>(' . __('คุณสามารถอัปโหลดภาพได้ 5 รูป', 'geodirectory') . ')</small>'; ?>
              </h5>

              <div class="geodir_form_row clearfix" id="<?php echo $id; ?>dropbox"
                   style="border:1px solid #ccc;min-height:100px;height:auto;padding:10px;text-align:center;">
                  <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>"/>
                  <input type="hidden" name="<?php echo $id; ?>totImg" id="<?php echo $id; ?>totImg"
                         value="<?php echo $totImg; ?>"/>
                  <div
                      class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>"
                      id="<?php echo $id; ?>plupload-upload-ui">
                      <h4><?php _e('Drop files to upload', 'geodirectory');?></h4><br/>
                      <input id="<?php echo $id; ?>plupload-browse-button" type="button"
                             value="<?php esc_attr_e('Select Files', 'geodirectory'); ?>" class="geodir_button"/>
                      <span class="ajaxnonceplu"
                            id="ajaxnonceplu<?php echo wp_create_nonce($id . 'pluploadan'); ?>"></span>
                      <?php if ($width && $height): ?>
                          <span class="plupload-resize"></span>
                          <span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
                          <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
                      <?php endif; ?>
                      <div class="filelist"></div>
                  </div>

                  <div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?> clearfix"
                       id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
                  </div>
                  <span
                      id="upload-msg"><?php _e('Please drag &amp; drop the images to rearrange the order', 'geodirectory');?></span>
                  <span id="<?php echo $id; ?>upload-error" style="display:none"></span>
              </div>

            <?php //---------- end upload image --------------- ?>


            <input type="hidden" name="pid" value="<?php echo $pid; ?>"/>
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>"/>
            <br>
            <p><input type="submit" value="<?php echo $button_text; ?>"></p>
          </form>
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
