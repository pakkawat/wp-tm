<?php
function my_theme_enqueue_styles() {

    $parent_style = 'whoop'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( 'bootstrap',
    get_stylesheet_directory_uri() . '/bootstrap.min.css',
    array( 'whoop' ),
    wp_get_theme()->get('Version')
    );
    wp_enqueue_style( 'bootstrap-theme',
        get_stylesheet_directory_uri() . '/bootstrap-theme.min.css',
        array( 'bootstrap' ),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_script( 'tamzang_bootstrapJS', get_stylesheet_directory_uri() . '/js/bootstrap.min.js' , array(), '1.0',  false );

    // wp_enqueue_style( 'child-style',
    //     get_stylesheet_directory_uri() . '/style.css',
    //     array( 'bootstrap-theme' ),
    //     wp_get_theme()->get('Version')
    // );
    if ( is_page_template( 'add_product.php' ) ) {

      // SCRIPT FOR UPLOAD
      wp_enqueue_script('plupload-all');
      wp_enqueue_script('jquery-ui-sortable');
      wp_register_script('geodirectory-plupload-script', get_stylesheet_directory_uri() . '/js/geodirectory-plupload.min.js#asyncload', array(), GEODIRECTORY_VERSION,true);
      wp_enqueue_script('geodirectory-plupload-script');
      // SCRIPT FOR UPLOAD END

      // check_ajax_referer function is used to make sure no files are uplaoded remotly but it will fail if used between https and non https so we do the check below of the urls
      if (str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
          $ajax_url = admin_url('admin-ajax.php');
      } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
          $ajax_url = admin_url('admin-ajax.php');
      } elseif (str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
          $ajax_url = str_replace("https", "http", admin_url('admin-ajax.php'));
      } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
          $ajax_url = str_replace("http", "https", admin_url('admin-ajax.php'));
      } else {
          $ajax_url = admin_url('admin-ajax.php');
      }

      // place js config array for plupload

      $plupload_init = array(
          'runtimes' => 'html5,silverlight,flash,browserplus,gears,html4',
          'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
          'container' => 'plupload-upload-ui', // will be adjusted per uploader
          'drop_element' => 'dropbox', // will be adjusted per uploader
          'file_data_name' => 'async-upload', // will be adjusted per uploader
          'multiple_queues' => true,
          'max_file_size' => geodir_max_upload_size(),
          'url' => $ajax_url,
          'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
          'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
          'filters' => array(array('title' => __('Allowed Files', 'geodirectory'), 'extensions' => '*')),
          'multipart' => true,
          'urlstream_upload' => true,
          'multi_selection' => false, // will be added per uploader
          // additional post data to send to our ajax hook
          'multipart_params' => array(
              '_ajax_nonce' => "", // will be added per uploader
              'action' => 'plupload_action', // the ajax action name
              'imgid' => 0 // will be added per uploader
          )
      );

      $base_plupload_config = json_encode($plupload_init);

      $gd_plupload_init = array('base_plupload_config' => $base_plupload_config,
          'upload_img_size' => geodir_max_upload_size());

      wp_localize_script('geodirectory-plupload-script', 'gd_plupload', $gd_plupload_init);

    }

    if (is_single()) {
      wp_enqueue_script( 'tamzang_add_cart', get_stylesheet_directory_uri() . '/js/tamzang_add_cart.js' , array(), '1.0',  false );

      // set variables for script
      wp_localize_script( 'tamzang_add_cart', 'tamzang_ajax_settings', array(
          'ajaxurl' => admin_url( 'admin-ajax.php' )
      ) );
    }
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_action( 'wpcf7_init', 'custom_add_form_tag_buttonLatLong' );

function custom_add_form_tag_buttonLatLong() {
    wpcf7_add_form_tag( 'buttonlatlong', 'custom_buttonLatLong_tag_handler' );
}

function custom_buttonLatLong_tag_handler( $tag ) {
  $scriptSrc = get_stylesheet_directory_uri() . '/js/getLaLong.js';
  wp_enqueue_script( 'myhandle', $scriptSrc , array(), '1.0',  false );
  return '<div style="width: 130px;color:white;"><button id="myLatLong">แนบที่อยู่</button><div id="geoStatus" style="float: right;"></div></div>';
}

function get_all_regions($atts){
  //set default attributes and values
  $values = shortcode_atts( array(
      'records'   	=> '10',
  ), $atts );
  $records = intval($values['records']);
  $region_args = array(
    'what' => 'region',
    'city_val' => '',
    'region_val' => '',
    'country_val' => '',
    'compare_operator' =>'in',
    'country_column_name' => 'country',
    'region_column_name' => 'region',
    'city_column_name' => 'city',
    'location_link_part' => true,
    'order_by' => ' asc ',
    'no_of_records' => $no_of_records,
    'format' => array('type' => 'array')
  );
  $region_loc_array = geodir_get_location_array($region_args);
  $i = 0;
  ?>
  <ul class="locations_list">
  <?php
  foreach($region_loc_array as $region_item) {
    if($i % $records == 0) echo '</ul><ul class="locations_list">';
    ?>
    <li class="region">
      <a href="<?php echo home_url('/places/').$region_item->location_link;?>"><?php echo __( $region_item->region, 'geodirectory' ) ;?></a>
    </li>
    <?php
    $i += 1;
  }
  ?>
  </ul>
  <?php
}

add_shortcode('all_regions', 'get_all_regions');


function tamzang_add_remove_images( $newArr, $product_id ) {
	global $wpdb, $current_user;

	$temp_folder_name = 'temp_' . $current_user->data->ID;

	if ( $current_user->data->ID == '' ) {
		$temp_folder_name = 'temp_' . session_id();
	}

	$wp_upload_dir = wp_upload_dir();
	$temp_folder = $wp_upload_dir['path'] . '/' . $temp_folder_name;


	$images = array();
	foreach( $newArr as $img ) {
		$file_ext = pathinfo( $img, PATHINFO_EXTENSION );
		$file_name = basename( $img, "." . $file_ext );
		$filename =  $temp_folder . '/' . basename( $img );
		$new_file_name =  $wp_upload_dir['path'] . '/' . $file_name . '_' . time() . '.' . $file_ext;
		copy( $filename, $new_file_name );
		$images[] = $wp_upload_dir['url'] . '/' . $file_name . '_' . time() . '.' . $file_ext;
    $query = $wpdb->prepare("INSERT INTO product_images SET
                             product_id = %d,title = %s,file =%s,file =%s,image_order = '0'",
                             array($post->ID,$user_ID,$commment_image_adj)
                          );
    $wpdb->query($query);
	}

	geodir_delete_directory( $temp_folder );


	return $images;
}


/*
* Ref: function geodir_save_post_images
* File: plugins/geodirectory/geodirectory-fuctions/post_functions.php
* @since 1.5.7
*/
function tamzang_save_images($product_id = 0, $post_image = array(), $dummy = false)
{
    global $wpdb, $current_user;
    //$post_type = get_post_type($post_id);
    //$table = $plugin_prefix . $post_type . '_detail';

    //$post_images = geodir_get_images($post_id);
    $post_images = tamzang_get_product_images($product_id);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE products SET featured_image = '' where id =%d",
            array($product_id)
        )
    );

    $invalid_files = $post_images;
    $valid_file_ids = array();
    $valid_files_condition = '';
    $geodir_uploaddir = '';

    $remove_files = array();

    if (!empty($post_image)) {

        $uploads = wp_upload_dir();
        $uploads_dir = $uploads['path'];
        $geodir_uploadpath = $uploads['path'];
        $geodir_uploadurl = $uploads['url'];
        $sub_dir = isset($uploads['subdir']) ? $uploads['subdir'] : '';
        $invalid_files = array();
        $postcurr_images = array();

        for ($m = 0; $m < count($post_image); $m++) {

            $menu_order = $m + 1;
            $file_path = '';

            /* --------- start ------- */

            $split_img_path = explode(str_replace(array('http://','https://'),'',$uploads['baseurl']), str_replace(array('http://','https://'),'',$post_image[$m]));

            $split_img_file_path = isset($split_img_path[1]) ? $split_img_path[1] : '';

            if (!$find_image = $wpdb->get_var($wpdb->prepare("SELECT ID FROM product_images WHERE file=%s AND product_id = %d", array($split_img_file_path, $product_id)))) {

                /* --------- end ------- */

                $curr_img_url = $post_image[$m];
                $image_name_arr = explode('/', $curr_img_url);
                $count_image_name_arr = count($image_name_arr) - 2;
                $count_image_name_arr = ($count_image_name_arr >= 0) ? $count_image_name_arr : 0;
                $curr_img_dir = $image_name_arr[$count_image_name_arr];
                $filename = end($image_name_arr);

                if (strpos($filename, '?') !== false) {
                    list($filename) = explode('?', $filename);
                }

                $curr_img_dir = str_replace($uploads['baseurl'], "", $curr_img_url);
                $curr_img_dir = str_replace($filename, "", $curr_img_dir);
                $img_name_arr = explode('.', $filename);
                $file_title = isset($img_name_arr[0]) ? $img_name_arr[0] : $filename;

                if (!empty($img_name_arr) && count($img_name_arr) > 2) {
                    $new_img_name_arr = $img_name_arr;
                    if (isset($new_img_name_arr[count($img_name_arr) - 1])) {
                        unset($new_img_name_arr[count($img_name_arr) - 1]);
                        $file_title = implode('.', $new_img_name_arr);
                    }
                }

                $file_title = sanitize_file_name($file_title);
                $file_name = sanitize_file_name($filename);
                $arr_file_type = wp_check_filetype($filename);
                $uploaded_file_type = $arr_file_type['type'];

                // Set an array containing a list of acceptable formats

                $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');

                // If the uploaded file is the right format

                if (in_array($uploaded_file_type, $allowed_file_types)) {
                    if (!function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }

                    if (!is_dir($geodir_uploadpath)) {
                        mkdir($geodir_uploadpath);
                    }

                    $external_img = false;

                    if (strpos( str_replace( array('http://','https://'),'',$curr_img_url ), str_replace(array('http://','https://'),'',$uploads['baseurl'] ) ) !== false) {
                    } else {
                        $external_img = true;
                    }

                    if ($dummy || $external_img) {
                        $uploaded_file = array();
                        $uploaded = (array)fetch_remote_file($curr_img_url);
                        if (isset($uploaded['error']) && empty($uploaded['error'])) {
                            $new_name = basename($uploaded['file']);
                            $uploaded_file = $uploaded;
                        }else{
                            print_r($uploaded);exit;
                        }
                        $external_img = false;
                    } else {
                        $new_name = $product_id . '_' . $file_name;

                        if ($curr_img_dir == $sub_dir) {
                            $img_path = $geodir_uploadpath . '/' . $filename;
                            $img_url = $geodir_uploadurl . '/' . $filename;
                        } else {
                            $img_path = $uploads_dir . '/temp_' . $current_user->data->ID . '/' . $filename;
                            $img_url = $uploads['url'] . '/temp_' . $current_user->data->ID . '/' . $filename;
                        }

                        $uploaded_file = '';

                        if (file_exists($img_path)) {
                            $uploaded_file = copy($img_path, $geodir_uploadpath . '/' . $new_name);
                            $file_path = '';
                        } else if (file_exists($uploads['basedir'] . $curr_img_dir . $filename)) {
                            $uploaded_file = true;
                            $file_path = $curr_img_dir . '/' . $filename;
                        }

                        if ($curr_img_dir != $geodir_uploaddir && file_exists($img_path))
                            unlink($img_path);
                    }


                    if (!empty($uploaded_file)) {
                        if (!isset($file_path) || !$file_path) {
                            $file_path = $sub_dir . '/' . $new_name;
                        }

                        $postcurr_images[] = str_replace(array('http://','https://'),'',$uploads['baseurl'] . $file_path);

                        if ($menu_order == 1) {
                            $wpdb->query($wpdb->prepare("UPDATE products SET featured_image = %s where id =%d", array($file_path, $product_id)));
                        }

                        // Set up options array to add this file as an attachment
                        $attachment = array();
                        $attachment['product_id'] = $product_id;
                        $attachment['title'] = $file_title;
                        //$attachment['content'] = '';
                        $attachment['file'] = $file_path;
                        //$attachment['mime_type'] = $uploaded_file_type;
                        $attachment['menu_order'] = $menu_order;
                        //$attachment['is_featured'] = 0;

                        $attachment_set = '';

                        foreach ($attachment as $key => $val) {
                            if ($val != '')
                                $attachment_set .= $key . " = '" . $val . "', ";
                        }
                        $attachment_set = trim($attachment_set, ", ");
                        $wpdb->query("INSERT INTO product_images SET " . $attachment_set);
                        $valid_file_ids[] = $wpdb->insert_id;
                    }
                }

            } else {

                $valid_file_ids[] = $find_image;
                $postcurr_images[] = str_replace(array('http://','https://'),'',$post_image[$m]);
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE product_images SET menu_order = %d where file =%s AND product_id =%d",
                        array($menu_order, $split_img_path[1], $product_id)
                    )
                );

                if ($menu_order == 1)
                    $wpdb->query($wpdb->prepare("UPDATE products SET featured_image = %s where id =%d", array($split_img_path[1], $product_id)));
            }

        }

        if (!empty($valid_file_ids)) {
            $remove_files = $valid_file_ids;
            $remove_files_length = count($remove_files);
            $remove_files_format = array_fill(0, $remove_files_length, '%d');
            $format = implode(',', $remove_files_format);
            $valid_files_condition = " ID NOT IN ($format) AND ";
        }

        //Get and remove all old images of post from database to set by new order

        if (!empty($post_images)) {
            foreach ($post_images as $img) {
                if (!in_array(str_replace(array('http://','https://'),'',$img->src), $postcurr_images)) {
                    $invalid_files[] = (object)array('src' => $img->src);
                }
            }
        }
        $invalid_files = (object)$invalid_files;
    }

    $remove_files[] = $product_id;
    $wpdb->query($wpdb->prepare("DELETE FROM product_images WHERE " . $valid_files_condition . " product_id = %d", $remove_files));
    if (!empty($invalid_files))
        geodir_remove_attachments($invalid_files);

    geodir_remove_temp_images();
    //geodir_set_wp_featured_image();
}





/*
* Ref: function geodir_get_images
* File: plugins/geodirectory/geodirectory-fuctions/post_functions.php
* @since 1.5.7
*/
function tamzang_get_product_images($product_id = 0, $limit = '')
{
    global $wpdb;
    if ($limit) {
        $limit_q = " LIMIT $limit ";
    } else {
        $limit_q = '';
    }

    $not_featured = '';
    $sub_dir = '';

    $arrImages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM product_images WHERE product_id = %d ORDER BY menu_order ASC, id DESC $limit_q ",
            array($product_id)
        )

    );

    $counter = 0;
    $return_arr = array();

    if (!empty($arrImages)) {
        foreach ($arrImages as $attechment) {
            $img_arr = array();
            $img_arr['id'] = $attechment->id;
            $img_arr['product_id'] = isset($attechment->product_id) ? $attechment->product_id : 0;
            //$img_arr['user_id'] = isset($attechment->user_id) ? $attechment->user_id : 0;

            $file_info = pathinfo($attechment->file);

            if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..')
                $sub_dir = stripslashes_deep($file_info['dirname']);

            $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
            $uploads_baseurl = $uploads['baseurl'];
            $uploads_path = $uploads['path'];

            $file_name = $file_info['basename'];
            $uploads_url = $uploads_baseurl . $sub_dir;

            $img_arr['src'] = apply_filters('geodir_get_images_src',$uploads_url . '/' . $file_name,$file_name,$uploads_url,$uploads_baseurl);
            $img_arr['path'] = $uploads_path . '/' . $file_name;
            $width = 0;
            $height = 0;

            if (is_file($img_arr['path']) && file_exists($img_arr['path'])) {
                $imagesize = getimagesize($img_arr['path']);
                $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
            }

            $img_arr['width'] = $width;
            $img_arr['height'] = $height;

            $img_arr['file'] = $file_name; // add the title to the array
            $img_arr['title'] = $attechment->title; // add the title to the array
            //$img_arr['caption'] = isset($attechment->caption) ? $attechment->caption : ''; // add the caption to the array
            //$img_arr['content'] = $attechment->content; // add the description to the array
            //$img_arr['is_approved'] = isset($attechment->is_approved) ? $attechment->is_approved : ''; // used for user image moderation. For backward compatibility Default value is 1.

            $return_arr[] = (object)$img_arr;

            $counter++;

        }
        return apply_filters('geodir_get_images_arr',$return_arr);
    }
    return $return_arr;
}

function tamzang_get_product ($product = array()){
  $html = '';

  $uploads = wp_upload_dir();

  $geodir_uploadpath = $uploads['path'];
  $geodir_uploadurl = $uploads['url'];

  if (!empty($product)){
    $html .= '<section>';
    if($product->featured_image != '')
      $html .= '<img src="'.$uploads['baseurl'].$product->featured_image.'" />';
    $html .= '<h2>'.$product->name.'</h2>';
    $html .= '<p>'.$product->short_desc.'</p>';
    $html .= '<aside>';
    $html .= '<ul>';
    $html .= '<li>ราคา: '.$product->price.' บาท</li>';
    $html .= '<li>มีสินค้า</li>';
    $html .= '</ul>';
    $html .= create_add_to_cart($product);
    $html .= '</aside>';
    $html .= '</section> ';
  }
  return $html;
}

function create_add_to_cart($product){
  $html = '';
  $html .= '<button type="button" style="color:white;" data-toggle="modal" data-target="#product_'.$product->id.'">';
  $html .= 'เพิ่มลงตะกร้า';
  $html .= '</button>';

  //$html .= '';
  //$html .= '';
  //$html .= '';
  return $html;
}

function tamzang_get_all_products($post_id){
  global $wpdb;
  $arrProducts = $wpdb->get_results(
      $wpdb->prepare(
          "SELECT * FROM products", array($post_id)
      )
  );
  return $arrProducts;
}

function create_product_modal($post_id){
  //echo $post_id.'------';
  $arrProducts = tamzang_get_all_products($post_id);
  $nonce = wp_create_nonce( 'add_to_cart_' . $post_id );
  if (!empty($arrProducts)) {
    foreach ( $arrProducts as $product ){
      $html = '';
      $html .= '<div class="modal fade" id="product_'.$product->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">';
      $html .= '<div class="modal-dialog" role="document">';
      $html .= '<div class="modal-content">';
      $html .= '<form method="POST" id="add_cart_' . $product->id . '">';
      $html .= '<div class="modal-header">';
      $html .= '<h5 class="modal-title" id="exampleModalLabel">'.$product->name.'</h5>';
      $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
      $html .= '<span aria-hidden="true">&times;</span>';
      $html .= '</button>';
      $html .= '</div>';
      $html .= '<div class="modal-body">';
      //$html .= json_encode(tamzang_get_product_images($product->id));
      $html .= create_product_carousel($product->id, tamzang_get_product_images($product->id));
      $html .= '<div class="sp-quantity">';
      $html .= '<div class="input-group">';
      $html .= '<span class="input-group-btn">';
      $html .= '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus">';
      $html .= '<span class="glyphicon glyphicon-minus"></span>';
      $html .= '</button>';
      $html .= '</span>';
      $html .= '<div class="sp-input">';
      $html .= '<input type="text" class="quntity-input form-control" name="qty" value="1">';
      $html .= '</div>';
      $html .= '<span class="input-group-btn">';
      $html .= '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-type="plus">';
      $html .= '<span class="glyphicon glyphicon-plus"></span>';
      $html .= '</button>';
      $html .= '</span>';
      $html .= '</div>';
      $html .= '</div>';

      $html .= '<input type="hidden" name="post_id" value="'.$post_id.'"  />';
      $html .= '<input type="hidden" name="product_id" value="'.$product->id.'"  />';
      $html .= '<input type="hidden" name="nonce" value="'.$nonce.'"  />';
      $html .= '<input type="hidden" name="action" value="add_to_cart"  />';
      $html .= '</div>';
      $html .= '<div class="modal-footer">';
      //$html .= '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
      $html .= '<input type="submit" value="เพิ่มสินค้า" class="btn btn-primary"></input>';

      $html .= '</div>';
      $html .= '</form>';
      $html .= '</div>';
      $html .= '</div>';
      $html .= '</div>';
      echo $html;
    }
    $testtext = '#aaa, #bbb';
    ?>
    <script>
    jQuery(document).ready(function($){
      $( "#sortable1, #sortable2" ).sortable({
        connectWith: ".list-group"
      }).disableSelection();
    } );
    </script>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">
          Panel Default</h3>
      </div>
      <ul id="sortable1" class="list-group">
        <li class="list-group-item">Item 1</li>
        <li class="list-group-item">Item 2</li>
        <li class="list-group-item">Item 3</li>
        <li class="list-group-item">Item 4</li>
        <li class="list-group-item">Item 5</li>
      </ul>
    </div>

    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">
          Panel primary</h3>
      </div>
<ul id="sortable2" class="list-group">
  <li class="list-group-item">Item 1</li>
  <li class="list-group-item">Item 2</li>
  <li class="list-group-item">Item 3</li>
  <li class="list-group-item">Item 4</li>
  <li class="list-group-item">Item 5</li>
</ul>
</div>
    <?php
  }

}

function create_product_carousel($product_id, $arr_images = array()){
  $html = '';
  $total_image = count($arr_images);

  $indicators = '';
  $slides = '';
  $is_first = true;
  $x = 0;
  foreach ($arr_images as $image){
    $indicators .= '<li data-target="#ProductCarousel_'.$product_id.'" data-slide-to="'.$x.'" '.($is_first ? 'class="active"' : '').' ></li>';

    $slides .= '<div class="item '.($is_first ? 'active' : '').'">';
    $slides .= '<img src="'.$image->src.'" >';
    $slides .= '</div>';
    $x++;
    $is_first = false;
  }


  $html .= '<div id="ProductCarousel_'.$product_id.'" class="carousel slide" data-ride="carousel">';
  $html .= '<ol class="carousel-indicators">';
  $html .= $indicators;
  $html .= '</ol>';
  $html .= '<div class="carousel-inner">';
  $html .= $slides;
  $html .= '</div>';
  $html .= '<a class="left carousel-control" href="#ProductCarousel_'.$product_id.'" data-slide="prev">';
  $html .= '<span class="glyphicon glyphicon-chevron-left"></span>';
  $html .= '<span class="sr-only">Previous</span>';
  $html .= '</a>';
  $html .= '<a class="right carousel-control" href="#ProductCarousel_'.$product_id.'" data-slide="next">';
  $html .= '<span class="glyphicon glyphicon-chevron-right"></span>';
  $html .= '<span class="sr-only">Next</span>';
  $html .= '</a>';
  $html .= '';
  $html .= '</div>';



  return $html;
}

add_action('tamzang_add_product_modal','create_product_modal');

//Ajax functions
add_action('wp_ajax_add_to_cart', 'add_to_cart_callback');

function add_to_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  // if ( check_ajax_referer( 'add_to_cart_' . $data['post_id'], 'nonce', false ) == false ) {
  //     wp_send_json_error();
  // }


  $cart = array();
  $cart['wp_user_id'] = $current_user->ID;
  $cart['product_id'] = $data['product_id'];
  $cart['qty'] = $data['qty'];

  $cart_set = '';

  foreach ($cart as $key => $val) {
      if ($val != '')
          $cart_set .= $key . " = '" . $val . "', ";
  }
  $cart_set = trim($cart_set, ", ");
  $wpdb->query("INSERT INTO shopping_cart SET " . $cart_set);

  //$valid_file_ids[] = $wpdb->insert_id;


  wp_send_json_success($data);
  //return $data;
}
?>
