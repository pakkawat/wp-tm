<?php /* Template Name: Add Product */ ?>


<?php
global $wpdb, $current_user, $gd_session;

$pid = '';
$product_id ='';
$product_name = '';
$is_current_user_owner = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pid = $_POST['pid'];
  if (isset($pid) && $pid != '') {
    $is_current_user_owner = geodir_listing_belong_to_current_user((int)$pid);
  }
  if (is_user_logged_in() && $is_current_user_owner) {
    $product_id = $_POST['product_id'];
    if (isset($product_id) && $product_id != '') { //แก้ไข Product
      $product_name = $_POST['product_name'];


      if (isset($_POST['post_images']) && $_POST['post_images'] != '') {
        $newArr = explode(',', $_POST['post_images']);
        file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $newArr, true));
        tamzang_save_images($product_id, $newArr);
      }

      wp_redirect(get_permalink($pid).'/#product_list');
    }
    else{ // เพิ่ม Product
      if (isset($_POST['product_name']) && $_POST['product_name'] != '') {
        //file_put_contents( 'debug' . time() . '.log', var_export( $_POST, true));
        //file_put_contents( dirname(__FILE__).'/debug/debug' . time() . '.log', var_export( $_POST, true));
        $product_name = $_POST['product_name'];
        $current_date = date("Y-m-d H:i:s");

        $wpdb->query($wpdb->prepare("INSERT INTO products SET wp_user_id = %d, post_id = %d, name = %s, update_date = %s ",
                                      array($current_user->ID,$pid,$product_name,$current_date)));
        $product_id = $wpdb->insert_id;

        //wp_redirect(get_permalink($_REQUEST['pid']));
        //exit;

        if (isset($_POST['post_images']) && $_POST['post_images'] != '') {
          $newArr = explode(',', $_POST['post_images']);
          $images = tamzang_save_images($product_id, $newArr);
        }

        //file_put_contents( dirname(__FILE__).'/debug/debug' . time() . '.log', var_export( $newArr, true));
        //file_put_contents( dirname(__FILE__).'/debug/debug_insert_images_.log', var_export( $images, true));
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
    $tamzang_product = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM products WHERE id = %d ", array($product_id)
        )
    );
    $product_name = $tamzang_product->name;
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
          <h1 class="page-title entry-title" itemprop="headline">
            <?php the_title(); ?>
          </h1>
          <?php /*<p class="byline vcard"> <?php printf( __( 'Posted <time class="updated" datetime="%1$s" >%2$s</time> by <span class="author">%3$s</span>', GEODIRECTORY_FRAMEWORK ), get_the_time('c'), get_the_time(get_option('date_format')), get_the_author_link( get_the_author_meta( 'ID' ) )); ?> </p> */?>
        </header>
        <?php // end article header
        //$wp_upload_dir = wp_upload_dir();
        //echo $wp_upload_dir['path'] . '-' . $wp_upload_dir['baseurl'] . '----' . $wp_upload_dir['url'];
        //echo get_permalink($pid);
        ?>
        <section class="entry-content cf" itemprop="articleBody">
          <?php the_content(); ?>
          <form action="<?php the_permalink(); ?>" method="post">


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

            <p><label for="name">ชื่อสินค้า: <span>*</span> <br><input type="text" name="product_name" value="<?php echo esc_attr(stripslashes($product_name)); ?>"></label></p>
            <input type="hidden" name="pid" value="<?php echo $pid; ?>"/>
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>"/>
            <p><input type="submit" value="เพิ่มสินค้า"></p>
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
