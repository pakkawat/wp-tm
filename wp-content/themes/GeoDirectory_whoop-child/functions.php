<?php

function is_tamzang_admin(){
    global $wp_query, $current_user;

    if ( is_user_logged_in() ){
        $user = get_userdata( $current_user->ID );

        if(in_array( 'administrator', (array) $user->roles ))
            return true;
    }

    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}

function tamzang_get_current_date(){
    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp

    return $dt->format("Y-m-d H:i:s");
}

function tamzang_thai_datetime($date_time){
    $date_time = explode(" ", $date_time);
    $date = explode("-", $date_time[0]);
    $time = $date_time[1];
    $month = array ( "ม.ค", "ก.พ", "มี.ค", "เม.ย","พ.ค", "มิ.ย", "ก.ค", "ส.ค","ก.ย", "ต.ค", "พ.ย", "ธ.ค" );

    return $date[2]." ".$month[$date[1]-1]." ".$date[0]." ".$time;
}

function my_theme_enqueue_styles() {

    $parent_style = 'whoop'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( 'bootstrap',
    get_stylesheet_directory_uri() . '/bootstrap.min.css',
    array(  ),
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
      wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );
      wp_enqueue_script( 'tamzang_product_validation', get_stylesheet_directory_uri() . '/js/product_validation.js' , array(), '1.0',  false );
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

    }elseif (is_page_template( 'product_list.php' )) {
      wp_enqueue_script( 'tamzang_delete_product', get_stylesheet_directory_uri() . '/js/tamzang_delete_product.js' , array(), '1.0',  false );
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

function remove_details_next_prev(){
    remove_action('geodir_details_main_content', 'geodir_action_details_next_prev', 80);
}
add_action('geodir_details_main_content', 'remove_details_next_prev', 1);

add_action('wp_enqueue_scripts','scripts_transfer_slip_picture');
function scripts_transfer_slip_picture(){
    if ( is_page_template('my_order.php') || is_page_template('shop_order.php') ) {
        wp_enqueue_script( 'uploader-script', get_stylesheet_directory_uri() . '/js/uploader/jquery.dm-uploader.min.js' , array(), '1.0',  false );
    }
}


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

function tamzang_ecommerce_view ($post_id){
  set_query_var( 'post_id', $post_id );
  set_query_var( 'cat_id', 0 );
  get_template_part( 'ecommerce-view' );
}

//to get all sub cids
function sub_cids($cid,$cids=0){
    global $cids, $wpdb;
    
    $categories = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM category WHERE parent = %d", array($cid)
        )
    );
    foreach ( $categories as $category ){
        $cids[]=$category->cid;
        sub_cids($category->cid,$cids);
    }
    
    return $cids;
    
}

function tamzang_get_all_products($post_id, $cat_id){
  global $wpdb;

  $cids=sub_cids($cat_id);
  $cids[]=$cat_id;
  $cids_str=implode(",",$cids);

  $arrProducts = $wpdb->get_results(
      $wpdb->prepare(
          "SELECT * FROM products where post_id = %d AND category_id in ($cids_str)", array($post_id)
      )
  );
  return $arrProducts;
}

function create_product_modal($product, $post_id){
    $nonce = wp_create_nonce( 'add_to_cart_' . $product->ID );
    $html = '';

    if ( is_user_logged_in() ){
        $html .= '<div class="modal fade" id="product_'.$product->ID.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">';
        $html .= '<div class="modal-dialog" role="document">';
        $html .= '<div class="modal-content">';
        $html .= '<form method="POST" id="add_cart_' . $product->ID . '" name="modal_add_cart">';
        $html .= '<div class="modal-header">';
        $html .= '<div class="order-col-9"><h3 class="modal-title" id="exampleModalLabel">'.$product->post_title.'</h3></div>';
        $html .= '<div class="order-col-3"><button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        $html .= '<span aria-hidden="true">&times;</span>';
        $html .= '</button></div>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= create_product_carousel($product, geodir_get_images($product->ID, 'medium', get_option('geodir_listing_no_img')));
        $html .= '<input type="hidden" class="quntity-input form-control" name="qty" value="1">';
        $html .= '<input type="hidden" name="post_id" value="'.$post_id.'"  />';
        $html .= '<input type="hidden" name="product_id" value="'.$product->ID.'"  />';
        $html .= '<input type="hidden" name="nonce" value="'.$nonce.'"  />';
        $html .= '<input type="hidden" name="action" value="add_to_cart"  />';
        $html .= '</div>';
        $html .= '<div class="modal-footer">';
        $html .= '<div class="order-col-6" style="text-align: left;">';
        $html .= '<h3>ราคา: '.str_replace(".00", "",number_format($product->geodir_price,2)).' บาท</h3>';
        $html .= '</div>';
        $html .= '<div class="order-col-6">';
        $html .= '<input type="submit" value="เพิ่มสินค้า" class="btn btn-primary"></input>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }else{
        $html .= '<div class="modal fade" id="product_'.$product->ID.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">';
        $html .= '<div class="modal-dialog" role="document">';
        $html .= '<div class="modal-content">';

        $html .= '<div class="modal-header">';
        $html .= '<div class="order-col-9"><h3 class="modal-title" id="exampleModalLabel">'.$product->post_title.'</h3></div>';
        $html .= '<div class="order-col-3"><button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        $html .= '<span aria-hidden="true">&times;</span>';
        $html .= '</button></div>';
        $html .= '</div>';

        $html .= '<div class="modal-body">';
        $html .= do_shortcode('[gd_login_box]');
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    echo $html;
//     }
//   }

}

function create_product_carousel($product, $arr_images = array()){
  $html = '';
  //$total_image = count((array)$arr_images);

  $indicators = '';
  $slides = '';
  $is_first = true;
  $x = 0;
  foreach ($arr_images as $image){
    $indicators .= '<li data-target="#ProductCarousel_'.$product->ID.'" data-slide-to="'.$x.'" '.($is_first ? 'class="active"' : '').' ></li>';

    $slides .= '<div class="item '.($is_first ? 'active' : '').'">';
    $slides .= '<img src="'.$image->src.'" >';
    $slides .= '</div>';
    $x++;
    $is_first = false;
  }

  //$html .= '<p align="left" style = "font-size:18px">'.$product->long_desc.'</p>';
  $html .= '<div id="ProductCarousel_'.$product->ID.'" class="carousel slide" data-ride="carousel">';
  $html .= '<ol class="carousel-indicators">';
  $html .= $indicators;
  $html .= '</ol>';
  $html .= '<div class="carousel-inner">';
  $html .= $slides;
  $html .= '</div>';
  $html .= '<a class="left carousel-control" href="#ProductCarousel_'.$product->ID.'" data-slide="prev">';
  $html .= '<span class="glyphicon glyphicon-chevron-left"></span>';
  $html .= '<span class="sr-only">Previous</span>';
  $html .= '</a>';
  $html .= '<a class="right carousel-control" href="#ProductCarousel_'.$product->ID.'" data-slide="next">';
  $html .= '<span class="glyphicon glyphicon-chevron-right"></span>';
  $html .= '<span class="sr-only">Next</span>';
  $html .= '</a>';
  $html .= '';
  $html .= '</div>';



  return $html;
}

//add_action('geodir_after_single_post','create_product_modal');

//Ajax functions
add_action('wp_ajax_add_to_cart', 'add_to_cart_callback');

function add_to_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'add_to_cart_' . $data['product_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {
    $geodir_tamzang_id = geodir_get_post_meta( $data['post_id'], 'geodir_tamzang_id', true );
    $show_button = geodir_get_post_meta( $data['product_id'], 'geodir_show_addcart', true );
    // $show_button = $wpdb->get_var(
    //     $wpdb->prepare(
    //         "SELECT show_button FROM products where id = %d ", array($data['product_id'])
    //     )
    // );
    if(!empty($geodir_tamzang_id)&&$show_button){

        $product = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT product_id,qty FROM shopping_cart where product_id = %d AND wp_user_id = %d ", array($data['product_id'], $current_user->ID)
            )
        );
    
        if($wpdb->num_rows > 0)
        {
          $wpdb->query(
              $wpdb->prepare(
                  "UPDATE shopping_cart SET qty = %d where product_id = %d AND wp_user_id =%d",
                  array((int)$product->qty + (int)$data['qty'], $product->product_id, $current_user->ID)
              )
          );
        }else{
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
        }

        wp_send_json_success($data);

    }else{
        wp_send_json_error();
    }


  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }
  //$valid_file_ids[] = $wpdb->insert_id;

}


//Ajax functions
add_action('wp_ajax_delete_product', 'delete_product_callback');

function delete_product_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_product_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];

  try {

    // $images = tamzang_get_product_images($product_id);

    // // check wp_user_id ด้วยว่าตรงไหม

    // if (!empty($images))
    //     geodir_remove_attachments($images);

    // $wpdb->query($wpdb->prepare("DELETE FROM product_images WHERE product_id = %d", $product_id));

    // $wpdb->query($wpdb->prepare("DELETE FROM products WHERE id = %d", $product_id));

    // $wpdb->query($wpdb->prepare("DELETE FROM shopping_cart WHERE product_id = %d", $product_id));


    wp_delete_post($product_id);


    wp_send_json_success($data);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}

function tamzang_cart_count()
{
  global $wpdb, $current_user;

  $cart_item = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT sum(qty) FROM shopping_cart where wp_user_id = %d", array($current_user->ID)
      )
  );

  if($cart_item == NULL)
    $cart_item = "0";

  return $cart_item;
}

function tamzang_get_all_products_in_cart($user_id){
  global $wpdb;
//   $arrProducts = $wpdb->get_results(
//       $wpdb->prepare(
//           "SELECT p.id as product_id,p.post_id,p.name,p.short_desc,p.featured_image,p.price,s.qty,p.stock
//           FROM products p INNER JOIN shopping_cart s
//           on p.id = s.product_id AND s.wp_user_id = %d AND p.post_id = %d ORDER BY s.id ", array($user_id, $post_id)
//       )
//   );
  add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
  add_filter('geodir_filter_widget_listings_join', 'inner_join_user_id', 10, 2);
  add_filter('geodir_filter_widget_listings_fields', 'select_shopping_cart_field', 10, 3);
  $query_args = array(
    'is_geodir_loop' => true,
    'post_type' => 'gd_product',
    'posts_per_page' => -1,
    'order_by' => 'post_title'
  );
  
  $arrProducts = geodir_get_widget_listings($query_args);


  return $arrProducts;
}


//Ajax functions
add_action('wp_ajax_update_product_cart', 'update_product_cart_callback');

function update_product_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_product_cart_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];
  $qty = $data['qty'];

  try {

    $total = 0;
    if($qty == 0){
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM shopping_cart WHERE product_id = %d AND wp_user_id =%d",
                array($product_id, $current_user->ID)
            )
        );
    }else{
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE shopping_cart SET qty = %d where product_id = %d AND wp_user_id =%d",
                array($qty, $product_id, $current_user->ID)
            )
        );
        $total = geodir_get_post_meta($data['id'], 'geodir_price', true)*$qty;
    }

    wp_send_json_success($total);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}


//Ajax functions
add_action('wp_ajax_delete_product_cart', 'delete_product_cart_callback');

function delete_product_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_product_cart_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];

  try {

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM shopping_cart WHERE product_id = %d AND wp_user_id =%d",
            array($product_id, $current_user->ID)
        )
    );

    //$total = tamzang_cart_count();
    wp_send_json_success($total);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}


//Ajax functions
add_action('wp_ajax_load_tamzang_cart', 'load_tamzang_cart_callback');

function load_tamzang_cart_callback(){
  $data = $_GET;
  set_query_var( 'post_id', $data['post_id'] );
  get_template_part( 'ajax-cart' );
  wp_die();
}


//Ajax functions
add_action('wp_ajax_update_order_status', 'update_order_status_callback');

function update_order_status_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;
  $current_date = tamzang_get_current_date();
  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  //check the nonce
  if ( check_ajax_referer( 'update_order_status_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $order_id = $data['id'];
  $status = $data['status'];
  //get_post_field( 'post_author', $order->post_id )
  if($status == '99'){
    $order_status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT status FROM orders where id = %d", array($order_id)
        )
    );

    if($order_status >= 3)//ยกเลิกไม่ได้
        wp_send_json_error("พนักงานตามส่งยืนยันคำสั่งซื้อแล้ว");

    // 2019/07/03 Bank Add delete status 1 in driver_order_log_assign
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM  driver_order_log_assign WHERE driver_order_id = %d ",
            array($order_id)
        )
    );


  }
  try {

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET status = %d where id = %d ",
            array($status, $order_id)
        )
    );

    $order = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT thread_id,wp_user_id,post_id FROM orders where id = %d ", array($order_id)
        )
    );

    //$shop_owner_id = get_post_field ('post_author', $order->post_id);
    $url = "";
    $user_nicename = "";
    if($status == "1" || $status == "2" || $status == "3") // ร้านค้าส่งข้อความไปหาลูกค้า
    {
        // $customer_info = get_userdata($order->wp_user_id);
        // $user_nicename = $customer_info->user_nicename;
        $url = home_url('/my-order/');
        
    }else // ลูกค้าส่งข้อความหาร้านค้า
    {
        // $author_id = get_post_field ('post_author', $order->post_id);
        // $user_nicename = get_the_author_meta( 'user_nicename' , $author_id ); 
        $url = home_url('/shop-order/').'?pid='.$order->post_id;
    }
    //$url = home_url('/members/'.$user_nicename.'/messages/view/'.$order->thread_id);

    $reply_message = "";
    switch ($status) {
        case "1":
            $reply_message = '<strong><p style="font-size:14px;">รอการจ่ายเงิน</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "2":
            $reply_message = '<strong><p style="font-size:14px;">ยืนยันการจ่ายเงิน</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "3":
            $reply_message = '<strong><p style="font-size:14px;">ทำการจัดส่งแล้ว</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "4":
            $reply_message = '<strong><p style="font-size:14px;">ใบสั่งซื้อเลขที่: #'.$order_id.' ลูกค้าได้รับสินค้าแล้ว</p>';
            break;
        case "99":
            $reply_message = '<strong><p style="font-size:14px;">ลูกค้าได้ทำการยกเลิกการสั่งซื้อ</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        default:
            $reply_message = "";
    }

    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($order->thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$order_id , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d, sender_only = %d where thread_id = %d AND user_id != %d ",
            array(1, 0, $order->thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($data);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

  //wp_send_json_success($data);
  //return $data;
}

//Ajax functions
add_action('wp_ajax_user_received_product', 'user_received_product_callback');

function user_received_product_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  //check the nonce
  if ( check_ajax_referer( 'user_received_product_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $order_id = $data['id'];
  $order_owner = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT wp_user_id FROM orders where id = %d", array($order_id)
      )
  );

  if($current_user->ID == $order_owner)
  {
    try {

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET status = %d where id = %d ",
              array(4, $order_id)
          )
      );

      wp_send_json_success($data);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
  }else{
    wp_send_json_error();
  }


  //wp_send_json_success($data);
  //return $data;
}

//Ajax functions
add_action('wp_ajax_load_order_status', 'load_order_status_callback');

function load_order_status_callback(){
  $data = $_GET;
  set_query_var( 'order_status', $data['order_status'] );
  get_template_part( 'ajax-order-status' );
  wp_die();
}


//Ajax functions
add_action('wp_ajax_add_transfer_slip_picture', 'add_transfer_slip_picture_callback');

function add_transfer_slip_picture_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  //check the nonce
  if ( check_ajax_referer( 'add_transfer_slip_picture_' . $data['order_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }


  $owner = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT wp_user_id,thread_id,post_id,status,deliver_ticket FROM orders where id = %d AND status != 99 ", array($data['order_id'])
      )
  );

  if($owner->deliver_ticket == 'Y')
  {
    if($owner->status > 4)
        wp_send_json_error();
  }
  else if($owner->status > 3)
    wp_send_json_error();

  if($current_user->ID == $owner->wp_user_id)
  {
    $thread_id = $owner->thread_id;
    $current_date = tamzang_get_current_date();
    //$target_dir = '/home/tamzang/domains/tamzang.com/public_html/Test02/wp-content/themes/GeoDirectory_whoop-child/images/upload/';
    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'].'/slip/'; //C:/path/to/wordpress/wp-content/uploads/2010/05/slip
    if (!file_exists($uploads_dir))
    {
      mkdir($uploads_dir);
    }


    $old_file_name = basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($old_file_name,PATHINFO_EXTENSION));
    $target_file = $uploads_dir . $data['order_id'] .'.'. $imageFileType;
    $image = $uploads['subdir'].'/slip/'.$data['order_id'] .'.'. $imageFileType;
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET image_slip = %s where id = %d ",
            array($image, $data['order_id'])
        )
    );

    $return = array(
        'image' => $uploads['url'].'/slip/'.$data['order_id'] .'.'. $imageFileType,
        'order_id'      => $data['order_id']
    );

    $reply_message = '<strong><p style="font-size:14px;">ลูกค้าได้ส่งหลักฐานการโอนเงินแล้ว</p> <a href="'.home_url('/shop-order/').'?pid='.$owner->post_id.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$data['order_id'] , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d where thread_id = %d AND user_id != %d ",
            array(1, $thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($return);
  }else{
    wp_send_json_error();
  }
  //wp_send_json_success();
}

add_action('wp_ajax_add_tracking_image', 'add_tracking_image_callback');

function add_tracking_image_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  //check the nonce
  if ( check_ajax_referer( 'add_tracking_image_' . $data['order_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }


  $order = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT post_id,thread_id,wp_user_id,status,deliver_ticket FROM orders where id = %d AND status != 99 ", array($data['order_id'])
      )
  );

  if($order->deliver_ticket == 'Y')
  {
    if($order->status > 4)
        wp_send_json_error();
  }
  else if($order->status > 3)
    wp_send_json_error();

  if(geodir_listing_belong_to_current_user((int)$order->post_id))
  {
    $current_date = tamzang_get_current_date();
    $thread_id = $order->thread_id;
    //$target_dir = '/home/tamzang/domains/tamzang.com/public_html/Test02/wp-content/themes/GeoDirectory_whoop-child/images/upload/';
    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'].'/tracking/'; //C:/path/to/wordpress/wp-content/uploads/2010/05/tracking
    if (!file_exists($uploads_dir))
    {
      mkdir($uploads_dir);
    }


    $old_file_name = basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($old_file_name,PATHINFO_EXTENSION));
    $target_file = $uploads_dir . $data['order_id'] .'.'. $imageFileType;
    $image = $uploads['subdir'].'/tracking/'.$data['order_id'] .'.'. $imageFileType;
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET tracking_image = %s where id = %d ",
            array($image, $data['order_id'])
        )
    );

    $return = array(
        'image' => $uploads['url'].'/tracking/'.$data['order_id'] .'.'. $imageFileType,
        'order_id'      => $data['order_id']
    );

    $reply_message = '<strong><p style="font-size:14px;">ร้านได้ส่งหลักฐานการจัดส่งแล้ว</p> <a href="'.home_url('/my-order/').'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$data['order_id'] , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d where thread_id = %d AND user_id != %d ",
            array(1, $thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($return);
  }else{
    wp_send_json_error();
  }
  //wp_send_json_success();
}

add_action('wp_ajax_driver_add_image', 'driver_add_image_callback');

function driver_add_image_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  //check the nonce
  if ( check_ajax_referer( 'driver_add_image_' . $data['order_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }


  $order = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT post_id,thread_id,wp_user_id,status,deliver_ticket FROM orders where id = %d ", array($data['order_id'])
      )
  );

  if($order->status > 4)
    wp_send_json_error();

  $owner = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT driver_id FROM driver_order_log_assign where driver_order_id = %d AND status = 2 ", array($data['order_id'])
    )
  );

  if($owner != $current_user->ID)
    wp_send_json_error();

  $current_date = tamzang_get_current_date();
  $thread_id = $order->thread_id;
  //$target_dir = '/home/tamzang/domains/tamzang.com/public_html/Test02/wp-content/themes/GeoDirectory_whoop-child/images/upload/';
  $uploads = wp_upload_dir();
  $uploads_dir = $uploads['path'].'/driver_image/'; //C:/path/to/wordpress/wp-content/uploads/2010/05/tracking
  if (!file_exists($uploads_dir))
  {
      mkdir($uploads_dir);
  }


  $old_file_name = basename($_FILES["file"]["name"]);
  $imageFileType = strtolower(pathinfo($old_file_name,PATHINFO_EXTENSION));
  $target_file = $uploads_dir . $data['order_id'] .'.'. $imageFileType;
  $image = $uploads['subdir'].'/driver_image/'.$data['order_id'] .'.'. $imageFileType;
  move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

  $wpdb->query(
      $wpdb->prepare(
          "UPDATE orders SET driver_image = %s where id = %d ",
          array($image, $data['order_id'])
      )
  );

  $return = array(
      'image' => $uploads['url'].'/driver_image/'.$data['order_id'] .'.'. $imageFileType,
      'order_id'      => $data['order_id']
  );

//   $reply_message = '<strong><p style="font-size:14px;">ร้านได้ส่งหลักฐานการจัดส่งแล้ว</p> <a href="'.home_url('/my-order/').'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
//   $wpdb->query(
//       $wpdb->prepare(
//           "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
//           array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$data['order_id'] , $reply_message, $current_date)
//       )
//   );

//   $wpdb->query(
//       $wpdb->prepare(
//           "UPDATE wp_bp_messages_recipients SET unread_count = %d where thread_id = %d AND user_id != %d ",
//           array(1, $thread_id, $current_user->ID)
//       )
//   );

  wp_send_json_success($return);

}


//Ajax functions
add_action('wp_ajax_load_address_form', 'load_address_form_callback');

function load_address_form_callback(){
  global $wpdb, $current_user;
  $data = $_GET;

  $address_id = $data['address_id'];
  if (isset($address_id) && $address_id != ''){

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner)
      set_query_var( 'address_id', $data['address_id'] );

  }

  get_template_part( 'address/myaddress', 'form' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_load_address_list', 'load_address_list_callback');

function load_address_list_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'list' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_get_province', 'get_province_callback');

function get_province_callback(){
  global $wpdb;

  $arrProvince = $wpdb->get_results("SELECT DISTINCT region FROM ".POST_LOCATION_TABLE." ORDER BY region ");

  $return_arr = array();
  if (!empty($arrProvince)) {
    foreach ( $arrProvince as $province ){
      $arr_province = array();
      $arr_province['province'] = $province->region;

      $return_arr[] = (object)$arr_province;
    }
  }

  wp_send_json_success($return_arr);

  // $response= array(
  //       'message'   => 'Saved',
  //       'ID'        => POST_LOCATION_TABLE
  //   );
  //   wp_send_json_success($response);

}

add_action('wp_ajax_get_district', 'get_district_callback');

function get_district_callback(){
  global $wpdb;
  $data = $_GET;

  $arrDistrict = $wpdb->get_results(
      $wpdb->prepare(
          "SELECT DISTINCT city FROM ".POST_LOCATION_TABLE." WHERE region=%s ORDER BY city ", array($data['region'])
      )
  );

  $return_arr = array();
  if (!empty($arrDistrict)) {
    foreach ( $arrDistrict as $district ){
      $arr_district = array();
      $arr_district['district'] = $district->city;

      $return_arr[] = (object)$arr_district;
    }
  }

  wp_send_json_success($return_arr);

}

add_action('wp_ajax_add_user_address', 'add_user_address_callback');

function add_user_address_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  if ( check_ajax_referer( 'add_user_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $user_address = array();

    $user_address['name'] = $data['name'];
    $user_address['address'] = $data['address'];
    $user_address['district'] = $data['dd_district'];
    $user_address['province'] = $data['dd_province'];
    $user_address['postcode'] = $data['tb_postcode'];
    $user_address['phone'] = $data['phone'];
	//Bank 20190206 add new 2 field
	$user_address['latitude'] = $data['lat'];
	$user_address['longitude'] = $data['lng'];
	


    $address_id = $data['address_id'];
    $sql = '';
    $where = '';
    if (isset($address_id) && $address_id != ''){ // update user_address
      $sql = "UPDATE user_address SET ";
      $where = " WHERE id=".$address_id;
    }else{ // insert user_address
      $count = $wpdb->get_var(
          $wpdb->prepare(
              "SELECT count(id) FROM user_address where wp_user_id = %d", array($current_user->ID)
          )
      );

      if($count == 0){
        $user_address['shipping_address'] = true;
        $user_address['billing_address'] = true;
      }
      $user_address['wp_user_id'] = $current_user->ID;
      $sql = "INSERT INTO user_address SET ";
    }

    $sql_set = '';

    foreach ($user_address as $key => $val) {
        if ($val != '')
            $sql_set .= $key . " = '" . $val . "', ";
    }
    $sql_set = trim($sql_set, ", ");
    $wpdb->query($sql . $sql_set . $where);

    wp_send_json_success();
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


//Ajax functions
add_action('wp_ajax_delete_user_address', 'delete_user_address_callback');

function delete_user_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_user_address_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['id'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){
      $wpdb->query(
          $wpdb->prepare(
              "DELETE FROM user_address WHERE id = %d ", array($address_id)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}

//Ajax functions
add_action('wp_ajax_load_billing_address', 'load_billing_address_callback');

function load_billing_address_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'billing' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_update_billing_address', 'update_billing_address_callback');

function update_billing_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_billing_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['billing_address'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET billing_address = true where id = %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET billing_address = false where id != %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


//Ajax functions
add_action('wp_ajax_load_shipping_address', 'load_shipping_address_callback');

function load_shipping_address_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'shipping' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_update_shipping_address', 'update_shipping_address_callback');

function update_shipping_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_shipping_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['shipping_address'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET shipping_address = true where id = %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET shipping_address = false where id != %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_confirm_order_select_shipping', 'confirm_order_select_shipping_callback');

function confirm_order_select_shipping_callback(){
    global $wpdb, $current_user;    

    $data = $_POST;
    //$data['id'] id ของ table user_address
    //$data['shop_id'] id ร้านค้า
    //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));
    
    // check the nonce
    if ( check_ajax_referer( 'select_shipping_address' . $data['id'], 'nonce', false ) == false ) {
        wp_send_json_error();
    }

    try {

        $owner = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT wp_user_id FROM user_address where id = %d ", array($data['id'])
            )
        );

        if($current_user->ID != $owner || empty($data['shop_id']))
            wp_send_json_error();

        $pre = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM user_address where wp_user_id = %d AND shipping_address = true ", array($current_user->ID)
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE user_address SET shipping_address = true where id = %d AND wp_user_id = %d ",
                array($data['id'], $current_user->ID)
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE user_address SET shipping_address = false where id = %d AND wp_user_id = %d ",
                array($pre, $current_user->ID)
            )
        );

        $current_address = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM user_address where id = %d ", array($data['id'])
            )
        );
        list($delivery_fee,$distance) = get_delivery_fee($data['shop_id'],$data['deli_type']);
        $sum = $delivery_fee+$data['total'];
        

        // 20190627 Bank put deliver fee
        if(($delivery_fee == 0) and ($distance == 0))
            $button = 0;         
        else
            $button = 1; 


        $return = array(
            'select' => '<img src="'.get_stylesheet_directory_uri().'/js/pass.png" />',
            'select_address' => $current_address->address.' '.$current_address->district.' '.$current_address->province.' '.$current_address->postcode,
            'pre_id' => $pre,
            'pre'      => '<a class="btn btn-success select-shipping" href="#"
                            data-id="'.$pre.'"
                            data-shop-id="'.$data['shop_id'].'"
                            data-nonce="'.wp_create_nonce( 'select_shipping_address' . $pre ).'"
                            style="color:white;" >เลือก</a>',
            'new_sum' => $sum,
            'new_delivery_fee' => $delivery_fee,
            'new_distance' => $distance,
            'new_order_button' => $button
        );

        wp_send_json_success($return);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

add_action('wp_ajax_select_delivery_type', 'select_delivery_type_callback');

function select_delivery_type_callback(){
    global $wpdb, $current_user;    

    $data = $_POST;

    // check the nonce
    if ( check_ajax_referer( 'select_delivery_type' . $current_user->ID, 'nonce', false ) == false ) {
        wp_send_json_error();
    }   
    

    try {

        $pre = 1;
        if($data['dtype'] == 1){// พนักงานตามสั่ง
            $pre = 2; 
        }else{// พนักงานประจำร้าน
            $xx = 0;            
        }
        list($delivery_fee,$distance) = get_delivery_fee($data['pid'],$data['dtype']);
        $sum = $delivery_fee+$data['total'];



        // 20191108 Bank put deliver fee Check
        if(($delivery_fee == 0) && ($distance == 0))
            $button = 0;     
        else
            $button = 1; 

        $return = array(
            'select' => '<img src="'.get_stylesheet_directory_uri().'/js/pass.png" />',
            'select_type' => $data['dtype'],
            'pre_type' => $pre,
            'pre'      => '<a class="btn btn-success select-delivery_type" href="#"
                            data-pid="'.$data['pid'].'"
                            data-dtype="'.$pre.'"
                            data-nonce="'.wp_create_nonce( 'select_delivery_type'.$current_user->ID ).'"
                            style="color:white;" >เลือก</a>',
                            'new_sum' => $sum,
                            'new_delivery_fee' => $delivery_fee,
                            'new_order_button' => $button
        );

        wp_send_json_success($return);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}


function tamzang_bp_user_address_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'สมุดที่อยู่',
            'slug' => 'address',
            'position' => 100,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_address_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'address'
        ));
}

add_action('bp_setup_nav', 'tamzang_bp_user_address_nav_adder',100);

function tamzang_user_address_screen()
{
  //add_action( 'bp_template_title', 'tamzang_user_address_screen_title' );
  add_action( 'bp_template_content', 'tamzang_user_address_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_address_screen_title()
{
  ?>
  <h1>สมุดที่อยู่</h1>

  <?php
}

function tamzang_user_address_screen_content()
{
  wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );
  ?>
  <div id="address-content" class="wrapper-loading">
    <?php get_template_part( 'address/myaddress', 'list' ); ?>
  </div>
  <?php
}


function tamzang_bp_user_shop_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'ร้านค้าของฉัน',
            'slug' => 'myshop',
            'position' => 101,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_shop_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'myshop'
        ));
}

add_action('bp_setup_nav', 'tamzang_bp_user_shop_nav_adder',101);

function tamzang_user_shop_screen()
{
  //add_action( 'bp_template_title', 'tamzang_user_shop_screen_title' );
  add_action( 'bp_template_content', 'tamzang_user_shop_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_shop_screen_title()
{
  ?>
  <h1>ร้านค้าของฉัน</h1>

  <?php
}

function tamzang_user_shop_screen_content()
{
  global $wpdb, $current_user;

  $my_query = new WP_Query( array(
      'post_type' => 'gd_place',
      'order'             => 'ASC',
      'orderby'           => 'title',
      'author' => $current_user->ID,
      'post_per_page' => -1,
      'nopaging' => true
  ) );

  if ( $my_query->have_posts() ) {

    if ( wp_is_mobile() ){

        ?>

        <table class="table">
            <thead>
            <th>ร้านค้า</th>

            </thead>
            <tbody>

            <?php

            while ( $my_query->have_posts() ) {

                $my_query->the_post();
                echo '<tr>';
                echo '<td>';
                echo '<a href="' .get_permalink(). ' ">';
                the_title();
                echo '</a>';
                echo '<br><br><a class="btn btn-info btn-block" href="'. home_url('/shop-order/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >รายการสั่งซื้อของร้าน</span></a>';
                echo '<br>';
                echo '<div class="order-row">';
                echo '<div class="order-col-6"><a class="btn btn-success btn-block" href="'. home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.get_the_ID() .'"><span style="color: #ffffff !important;" >เพิ่มสินค้า</span></a></div>';
                echo '<div class="order-col-6"><a class="btn btn-primary btn-block" href="'. home_url('/product-list/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >แก้ไขสินค้า</span></a></div>';
                echo '</div>';
                echo '</td>';
                echo '</tr>';

            }


            ?>

            </tbody>
        </table>


        <?php

    }else{

    ?>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <th>ชื่อร้านค้า</th>
          <th></th>
          <th></th>
          <th></th>
        </thead>
        <tbody>

        <?php

        while ( $my_query->have_posts() ) {

            $my_query->the_post();
            echo '<tr>';
            echo '<td>';
            echo '<a href="' .get_permalink(). ' ">';
            the_title();
            echo '</a>';
            echo '</td>';
            echo '<td style="text-align:center;"><a class="btn btn-info btn-block" href="'. home_url('/shop-order/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >รายการสั่งซื้อของร้าน</span></a></td>';
            echo '<td style="text-align:center;"><a class="btn btn-success btn-block" href="'. home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.get_the_ID() .'"><span style="color: #ffffff !important;" >เพิ่มสินค้า</span></a></td>';
            echo '<td style="text-align:center;"><a class="btn btn-primary btn-block" href="'. home_url('/product-list/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >แก้ไขสินค้า</span></a></td>';
            echo '</tr>';

        }


        ?>

        </tbody>
      </table>
    </div>

    <?php
    }


  }

}

	// Make Google map direction
function geodirectory_detail_page_google_map_link( $options ) {
    global $post;
    $post_type = geodir_get_current_posttype();
    if (($post_type !=gd_product)&&(!empty($post->post_latitude) && !empty($post->post_longitude) )) {
       
        $maps_url = add_query_arg( array(
                        'q' => $post->post_latitude . ',' . $post->post_longitude,
                    ), 'https://maps.google.com/maps' );
        ?>
        <div class="direction_button">
        <p><a href="<?php echo $maps_url; ?>" target="_blank"><input type=button id=direction_button value='Get Directions on Google Maps'></a></p>
        </div>
        <?php
    }
}
//add_action( 'geodir-whoop-listing-slider-div', 'geodirectory_detail_page_google_map_link',10,2);
add_action( 'whoop_detail_page_hide_map', 'geodirectory_detail_page_google_map_link',10,2);

function hide_map_on_product_detail($hide_map){
    $post_type = geodir_get_current_posttype();
    if($post_type == "gd_product")
        return true;
    else
        return $hide_map;
}

add_filter( 'whoop_detail_page_hide_map', 'hide_map_on_product_detail',20,1);

function shop_product_list_tab($arr_tabs){

    $post_type = geodir_get_current_posttype();

    if($post_type == "gd_product"){
        unset($arr_tabs['post_map']);
        $arr_tabs['post_profile']['heading_text'] = "รายละเอียดสินค้า";
    }else{
        $arr_tabs['product_list'] = array(
            'heading_text'  => __( 'รายการสินค้า', 'geodirectory' ),
            'is_active_tab' => false,
            'is_display'    => true,
            'tab_content'   => ''
        );
    }



    return $arr_tabs;
}

add_filter('geodir_detail_page_tab_list_extend', 'shop_product_list_tab', 10, 1);

function remove_sidebar_right_from_product_detail(){
    $post_type = geodir_get_current_posttype();
    if($post_type == "gd_product")
        remove_action('geodir_detail_sidebar', 'geodir_action_details_sidebar', 10);
}

add_action('geodir_detail_sidebar', 'remove_sidebar_right_from_product_detail', 1);

function add_hidden_shop_id(){
    $listing_type = sanitize_text_field($_REQUEST['listing_type']);
    if($listing_type == "gd_product"){
        if(!empty($_REQUEST['shop_id'])){
            $post = geodir_get_post_info($_REQUEST['shop_id']);
            if($post){
                $is_current_user_owner = geodir_listing_belong_to_current_user((int)$post->ID);
                if ($is_current_user_owner)
                    echo '<input type="hidden" name="geodir_shop_id" id="geodir_shop_id" value="'.$post->ID.'"/>';
                else
                    wp_redirect(home_url());
            }
            else
                wp_redirect(home_url());
        }else{
            wp_redirect(home_url());
        }
    }
}

add_action('geodir_before_detail_fields', 'add_hidden_shop_id', 10);

function tamzang_select_geodir_delivery_type($html,$cf){   

    $groupID = geodir_get_post_meta( $_REQUEST['pid'], 'groupID', true );
    ob_start(); // Start  buffering;
    $value = geodir_get_cf_value($cf);

    ?>
    <div id="<?php echo $cf['name'];?>_row"
         class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row geodir_custom_fields clearfix gd-fieldset-details">
        <label>
            <?php $site_title = __($cf['site_title'], 'geodirectory');
            echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
            <?php if ($cf['is_required']) echo '<span>*</span>';?>
        </label>
        <?php
        $option_values_arr = geodir_string_values_to_options($cf['option_values'], true);
        $select_options = '';
        if (!empty($option_values_arr)) {
            if($groupID == 0)
                unset($option_values_arr[2]);// hard code
            foreach ($option_values_arr as $option_row) {
                if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
                    $option_label = isset($option_row['label']) ? $option_row['label'] : '';

                    $select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
                } else {
                    $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                    $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                    $selected = $option_value == stripslashes($value) ? 'selected="selected"' : '';

                    $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                }
            }
        }
        ?>
        <select field_type="<?php echo $cf['type'];?>" name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                class="geodir_textfield textfield_x chosen_select"
                data-placeholder="<?php echo __('Choose', 'geodirectory') . ' ' . $site_title . '&hellip;';?>"
                option-ajaxchosen="false"><?php echo $select_options;?></select>
        <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
        <?php if ($cf['is_required']) { ?>
            <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
        <?php } ?>
    </div>

    <?php
    $html = ob_get_clean();


    return $html;

}
add_filter('geodir_custom_field_input_select_geodir_delivery_type', 'tamzang_select_geodir_delivery_type', 9, 2);

function add_shop_link(){
    global $post, $preview;

    $is_current_user_owner = geodir_listing_belong_to_current_user();
    if (!$preview){
        
        if ($is_current_user_owner){
            $post_id = $post->ID;
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.$post_id . '">เพิ่มสินค้า</a></p>';
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/product-list/') . '?pid='.$post_id . '">แก้ไขสินค้า</a></p>';
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/shop-order/') . '?pid='.$post_id . '">รายการสั่งซื้อของร้าน</a></p>';

            $group_id = geodir_get_post_meta($post->ID,'groupID',true);
            if($group_id != 0)
                echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/shop-driver/') . '?pid='.$post_id . '">เพิ่มคนส่งประจำ</a></p>';
        }
        // else{
        //     echo ' <p class="edit_link"><i class="fa fa-pencil"></i> ' . do_shortcode( '[popup_anything id="34987"]' ) . '</p>';
        // }

    }
}

add_action('geodir_after_edit_post_link', 'add_shop_link', 10);

function tamzang_apply_shop_id_temp($where, $post_type){
    if ( is_page_template( 'product_list.php' ) ){
        $where .= ' AND geodir_shop_id = '.$_GET['pid'].' ';
    }else if(geodir_is_page( 'detail' )){
        global $post;
        $where .= ' AND geodir_shop_id = '.$post->ID.' ';
    }
    echo '<h1>'.$where.'</h1>';
    return $where;
}

//add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);

function tamzang_apply_shop_id($where, $post_type){
    $post_id = '';
    if(isset($_GET['pid']))
        $post_id = $_GET['pid'];
    else if(isset($_POST['pid'])){
        $post_id = $_POST['pid'];
    }
    else{
        global $post;
        if($post->post_type == 'gd_product')
            $post_id = $post->geodir_shop_id;
        else
            $post_id = $post->ID;
    }
    $where .= ' AND geodir_shop_id = '.$post_id.' ';
    return $where;
}

function tamzang_apply_category_id($where, $post_type){
    $array_cat = array();
    $array_cat = get_ancestors( $_POST['cat_id'], 'gd_productcategory' );
    $array_cat[] = $_POST['cat_id'];
    $where .= ' AND default_category in ('.implode(",",$array_cat).') ';
    return $where;
}

function inner_join_user_id($join, $post_type){
    global $current_user;
    $join .= " INNER JOIN shopping_cart s ON (s.wp_user_id = ".$current_user->ID." AND 
                wp_geodir_gd_product_detail.post_id = s.product_id)";
    return $join;
}

function select_shopping_cart_field($fields, $table, $post_type){
    return $fields.', s.qty as shopping_cart_qty ';
}



function remove_add_photo_btn(){
    global $post;
    if($post->post_type == 'gd_product'){
        $is_current_user_owner = geodir_listing_belong_to_current_user();
        if (!$is_current_user_owner)
            return false;
    }

    return true;
}

add_filter('whoop_big_header_show_add_photo_btn', 'remove_add_photo_btn', 10);

function excerpt_read_more_link($output) {
    global $post;
    if ($post->post_type != 'gd_product')
    {
      $output .= '<p><a href="'. get_permalink($post->ID) . '">read more</a></p>';  
    }
    return $output;
}
add_filter('the_excerpt', 'excerpt_read_more_link');

function check_product_owner_before_add_photo(){
    global $post;
    if($post->post_type == 'gd_product'){
        $is_current_user_owner = geodir_listing_belong_to_current_user();
        if (!$is_current_user_owner)
            wp_redirect(get_permalink($post->ID));
    }
}

add_action('geodir_biz_photos_main_content', 'check_product_owner_before_add_photo', 1);

function remove_location_url($breadcrumb, $separator){
    global $post;
    if(geodir_is_page( 'detail' ) && ($post->post_type == 'gd_product')){
        $arr = explode($separator,$breadcrumb);
        $lenght = count($arr);
        $shop_link = '<a href="'.get_permalink($post->geodir_shop_id).'">'.get_the_title($post->geodir_shop_id).'</a>';
        $breadcrumb = $arr[0].$separator.$shop_link.$separator.$arr[1].$separator.$arr[$lenght-2].$separator.$arr[$lenght-1];
    }
    return $breadcrumb;
}

add_filter('geodir_breadcrumb', 'remove_location_url',10,2);

function product_pagination($max_num_pages,$paged, $prev = '«', $next = '»') {
    //global $wp_query, $wp_rewrite;
    //$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
    $pagination = array(
        'base' => get_pagenum_link(1) . '%_%',
        'format' => '&paged=%#%',
        'total' => $max_num_pages,
        'current' => $paged,
        'prev_text' => __($prev),
        'next_text' => __($next),
        'type' => 'array'
    );

    $arr_page = paginate_links( $pagination );
    $html = "";
    if(!empty($arr_page)){
        $html .= '<ul class="pagination pagination-lg">';
        foreach ( $arr_page as $key => $page_link ){
            $html .= '<li class="page-item '.( strpos( $page_link, 'current' ) !== false ? 'active' : '').'">'.$page_link.'</li>';
        }
        $html .= '</ul>';
        $html .= '';
    }
 
    echo $html;
}



function shop_product_list_content($tab_index){
    global $post;
    if($tab_index == 'product_list')
    {
        if($post->geodir_shop_product_list == '1')
        {
            echo create_dropdown_categort(1,$post->ID);
            echo '<div id="tamzang-menu">';
            tamzang_menu_view($post->ID);
            echo '</div>';
        }
        else
        {
            echo create_dropdown_categort(2,$post->ID);
            echo '<div id="tamzang-menu">';
            tamzang_ecommerce_view($post->ID);
            echo '</div>';
        }
    }
}

add_action( 'geodir_after_tab_content', 'shop_product_list_content', 10, 1);

function tamzang_menu_view($post_id)
{
    set_query_var( 'post_id', $post_id );
    //set_query_var( 'geodir_tamzang_id', $geodir_tamzang_id );
    set_query_var( 'cat_id', 0 );
    get_template_part( 'menu-view' );
}

//Ajax functions
add_action('wp_ajax_nopriv_load_tamzang_ecommerce_view', 'load_tamzang_ecommerce_view_callback');

function load_tamzang_ecommerce_view_callback(){
  $data = $_POST;
  set_query_var( 'post_id', $data['post_id'] );
  set_query_var( 'cat_id', $data['cat_id'] );
  get_template_part( 'ecommerce-view' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_nopriv_load_tamzang_menu_view', 'load_tamzang_menu_view_callback');
add_action('wp_ajax_load_tamzang_menu_view', 'load_tamzang_menu_view_callback');

function load_tamzang_menu_view_callback(){
  $data = $_POST;
  set_query_var( 'pid', $data['pid'] );
  set_query_var( 'cat_id', $data['cat_id'] );
  if($data['cat_type'] == 1)
    get_template_part( 'menu-view' );
  else
    get_template_part( 'ecommerce-view' );
  wp_die();
}

function create_dropdown_categort($type, $post_id){
    global $wpdb;
    $catList = get_cat_id_from_shop($post_id);
    $result = create_array_categort($catList);
    $html = '';
    if(!empty($result)){
        $html .= '<div class="order-row"><div class="order-col-4" style="float:right">
        <select id="dd_cat" data-cat_type="'.$type.'" data-id="'.$post_id.'">';
        $html .= '<option value="">ทั้งหมด</option>';
        foreach($result as $cl) {
            $html .= '<option value="'.$cl["id"].'">'.$cl["name"].'</option>';
        }
        $html .= '</select></div></div><div class="order-clear"></div>';
    }
    return $html;
}

function create_array_categort($cat_list){
    $cate_array = array();
    $top_level = array();

    foreach($cat_list as $cate){    
        $temp = end(get_ancestors( $cate->default_category, 'gd_productcategory' ));
        if(!empty($temp))    
            $top_level[] = $temp;
        else
            $top_level[] = $cate->default_category;
        
    }

    $top_level = array_unique($top_level);

    foreach($top_level as $cate_id){
        $spacing = '';
        if(!empty($cate_id)){
            $cate_array[] = array("id" => $cate_id, "name" => $spacing . get_term_by('id', $cate_id, 'gd_productcategory')->name);
            $cate_array = fetchCategoryTree($cate_id, $spacing . '&nbsp;&nbsp;', $cate_array);
        }
    }
    return $cate_array;
}

function get_cat_id_from_shop($post_id){
    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT(default_category) FROM wp_geodir_gd_product_detail where geodir_shop_id = %d ", array($post_id)
        )
    );

    return $results;
}
function fetchCategoryTree($parent = 0, $spacing = '', $user_tree_array = '') {

    if (!is_array($user_tree_array))
      $user_tree_array = array();
    

    $args = array( 'taxonomy' => 'gd_productcategory', 'parent' => $parent, 'hide_empty' => true );
    $terms = get_terms( $args );

    foreach ( $terms as $term ){
      $user_tree_array[] = array("id" => $term->term_id, "name" => $spacing . $term->name);
      $user_tree_array = fetchCategoryTree($term->term_id, $spacing . '&nbsp;&nbsp;', $user_tree_array);
    }
    return $user_tree_array;
  }

// AJAX function
add_action('wp_ajax_get_restaurant_positon', 'get_restaurant_positon');
//get  restaurant position by Tamzang_id
function get_restaurant_positon($tamzang_id) {
  
	 global $wpdb;
	//$data = $_POST;

	
  
	//$tamzang_id = $data['tamzang_id'];
	
	$sql = "SELECT * FROM wp_geodir_gd_place_detail where geodir_tamzang_id = ".$tamzang_id."";
	$result_res  = $wpdb->get_results($sql, ARRAY_A );
	$res_lat = $result_res[0]['post_latitude'];
	$res_lon = $result_res[0]['post_longitude'];
	
	return array($res_lat,$res_lon);
	/*
	$response= array(
		'lat' => $res_lat,
		'lon' => $res_lon
	);
	*/
	//wp_send_json_success($response);
	
	//wp_send_json_success("Work!!");
}

// AJAX function
add_action('wp_ajax_get_driver_restaurant', 'get_driver_restaurant');
//get list Driver for restaurant
function get_driver_restaurant() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "get_driver_restaurant START!", true));
	//print "Get Driver start";
	//$tamzang_id = $data['tamzang_id'];
	$tamzang_id = $_POST['tamzang_id'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Res Id:".$tamzang_id, true));
	$return_arr = array();
	$return_web = array();
	$restaurant_array = array();
    // Get Restaurant Position
	list($res_lat,$res_lon) = get_restaurant_positon($tamzang_id);
	
	// Put in ARRAY
	$restaurant_array['id'] = "res_id";
	$restaurant_array['Lat'] = $res_lat;
	$restaurant_array['Lon'] = $res_lon;
	$return_arr[] = $restaurant_array;
	
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $res_lat, true),FILE_APPEND);
	
	
	$sql = "SELECT * FROM driver ";
	
	$result_driver = $wpdb->get_results($wpdb->prepare("SELECT * FROM driver ",array()));
	
	//$total_driver = $wpdb->num_rows;
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true),FILE_APPEND);
	foreach ($result_driver as $driver)
	{
		$maker_drivers = array();
		$driver_lat = $driver->latitude;
		$driver_lon = $driver->longitude;
		$driver_id = $driver->Driver_id;
	    $maker_drivers['id'] = $driver_id;
		$maker_drivers['Lat'] = $driver_lat;
		$maker_drivers['Lon'] = $driver_lon;
		file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver_lat, true));
		
		$distance = distance($res_lat,$res_lon,$driver_lat,$driver_lon,"K");
		file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $distance, true),FILE_APPEND);
		if($distance <= 3)
		{
			//array_push($maker_drivers,$driver_id);
			$return_arr[] = $maker_drivers;
		}
		

	}
/*
	 $webhtmls = array();
	 $webhtmls['web'] = listdriver($result_driver);
	 $return_arr[] = $webhtmls;
	 */
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	wp_send_json_success($return_arr);
	//wp_send_json_success(json_encode($maker_drivers));
	//echo json_encode($maker_drivers);
	//return array($maker_drivers);
}

// Calculate Distance
function distance($lat1, $lon1, $lat2, $lon2,$unit){
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}

//AJAX FUNCTION
add_action('wp_ajax_listdriver', 'listdriver');
function listdriver(){


  $driver_num = $_GET;
  set_query_var( 'total_driver', $driver_num['driver_num']);
  set_query_var( 'tamzang_id', $driver_num['tamzang_id']);
  $driver_id_array = explode(",", $driver_num['driver_id']);
  set_query_var( 'driver_id', $driver_num['driver_id']);
  
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_num['tamzang_id'], true));

  //set_query_var( 'total_driver', $driver_num );
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Driver START", true));
  get_template_part( 'ajax-driver-list' );

  wp_die();
}




function tamzang_bp_user_driver_nav_adder()
{
    global $bp, $wpdb;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'ข้อมูล Driver',
            'slug' => 'driver',
            'position' => 102,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_driver_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'driver'
        )
    );

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM driver where driver_id = %d", array($user_id)
        )
    );

    if(!empty($id)){
        bp_core_new_nav_item(
            array(
                'name' => 'กลุ่มคนส่ง',
                'slug' => 'driver_group',
                'position' => 103,
                'show_for_displayed_user' => false,
                'screen_function' => 'tamzang_driver_group_screen',
                'item_css_id' => 'lists',
                'default_subnav_slug' => 'driver_group'
            )
        );

        bp_core_new_nav_item(
            array(
                'name' => 'driver_money',
                'slug' => 'driver_money',
                'position' => 104,
                'show_for_displayed_user' => false,
                'screen_function' => 'tamzang_driver_money_screen',
                'item_css_id' => 'lists',
                'default_subnav_slug' => 'driver_money'
            )
        );
    }
}

add_action('bp_setup_nav', 'tamzang_bp_user_driver_nav_adder',102);

function tamzang_user_driver_screen()
{
  add_action( 'bp_template_content', 'tamzang_user_driver_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_driver_screen_content()
{
  wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );

//   wp_register_style( 'gd-captcha-style', GEODIR_RECAPTCHA_PLUGIN_URL . '/css/gd-captcha-style.css', array(), GEODIR_RECAPTCHA_VERSION);
//   wp_enqueue_style( 'gd-captcha-style' );
  ?>
  <div id="driver-content">
    <?php
        global $wpdb, $current_user;

        $driver_approve = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT approve FROM register_driver where wp_user_id = %d ", array($current_user->ID)
            )
        );

        if(empty($driver_approve)){
            get_template_part( 'driver/driver', 'register' ); 
        }else{
            if($driver_approve->approve)
                get_template_part( 'driver/driver', 'transaction_details' );
            else
                get_template_part( 'driver/driver', 'pending' ); 
        }
        
    ?>
  </div>
  <?php

}

function tamzang_driver_group_screen()
{
  add_action( 'bp_template_content', 'tamzang_driver_group_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_driver_group_screen_content()
{
    get_template_part( 'driver/driver', 'group' ); 
}

function tamzang_driver_money_screen()
{
  add_action( 'bp_template_content', 'tamzang_driver_money_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

add_action('wp_ajax_load_driver_money', 'load_driver_money_callback');
function load_driver_money_callback(){
    global $wpdb, $current_user;

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT driver_id FROM driver_bank_account where driver_id = %d", array($current_user->ID)
        )
    );
    if(empty($id))
        wp_send_json_error();

    get_template_part( 'driver/driver', 'money'  );
    wp_die();
}

function tamzang_driver_money_screen_content()
{
    global $wpdb, $current_user;

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT driver_id FROM driver_bank_account where driver_id = %d", array($current_user->ID)
        )
    );
    echo '<div class="wrapper-loading">';
    if(empty($id)){
        get_template_part( 'driver/driver', 'bankaccount' );
    }else{
        get_template_part( 'driver/driver', 'money' );
    }
    echo '</div>';

}

add_action('wp_ajax_driver_add_bankaccount', 'driver_add_bankaccount_callback');
function driver_add_bankaccount_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_bankaccount_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT driver_id FROM driver_bank_account where driver_id = %d", array($current_user->ID)
        )
    );

    if(!empty($id))
        wp_send_json_error();

    $driver_name = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT driver_name FROM driver where Driver_id = %d", array($current_user->ID)
        )
    );
    
    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO driver_bank_account SET driver_id = %d, driver_name = %s, bank_account = %s, bank_account_name = %s ",
            array($current_user->ID, $driver_name, $data['account'], $data['name'])
        )
    );

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

add_action('wp_ajax_driver_withdraw_money', 'driver_withdraw_money_callback');
function driver_withdraw_money_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_withdraw_money_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $valid_value = array('0.5', '0.6', '0.7', '0.8', '0.9', '1');
    
    if(!in_array($data['dd_value'], $valid_value))
        wp_send_json_error();

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM driver_debit_batch where driver_id = %d AND DATE(date) = CURDATE() ", array($current_user->ID)
        )
    );
    if(!empty($id))
        wp_send_json_error();
    
    $driver = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM driver where driver_id = %d ", array($current_user->ID)
        )
    );

    $tz_date = tamzang_get_current_date();
    $debit = $driver->driver_cash*$data['dd_value'];
    $driver_new_balance= $driver->driver_cash - $debit;

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO driver_debit_batch SET driver_id = %d, driver_name = %s, driver_cash = %f, date = %s, 	bank_account = %s ",
            array($driver->Driver_id, $driver->driver_name, $debit, $tz_date, 'bank_account')
        )
    );
    insert_driver_transaction_details("DRIVER_WITHDRAW",
    array($driver->Driver_id, $debit, $driver_new_balance, "DRIVER_WITHDRAW", $tz_date));

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE driver SET previous_driver_debit_cash = %f, driver_cash = %f  WHERE Driver_id = %d ",
            array($debit, $driver_new_balance, $driver->Driver_id)
        )
    );

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


add_action('wp_ajax_register_driver', 'register_driver_callback');

function register_driver_callback(){
  global $wpdb, $current_user;
  $data = $_POST;
//   file_put_contents( dirname(__FILE__).'/debug/POST.log', var_export( $_POST, true));
//    file_put_contents( dirname(__FILE__).'/debug/register_driver.log', var_export( $_FILES, true));
//    wp_send_json_error();
  if ( check_ajax_referer( 'register_driver_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try
  {

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM register_driver where wp_user_id = %d", array($current_user->ID)
        )
    );

    if($id == NULL || empty($id) ){
        $tz = 'Asia/Bangkok';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    
        $current_date = $dt->format("Y-m-d H:i:s");

        
        $result_car_licence = tamzang_upload_picture('/driver_car_licence/', $_FILES['image_car_licence']['name'], $_FILES['image_car_licence']['tmp_name']);
        if(!$result_car_licence['result'])
            wp_send_json_error($result_car_licence['msg']);

        if(!empty($_FILES['image_car_licence2']['name']))
        {
            $result_car_licence2 = tamzang_upload_picture('/driver_car_licence2/', $_FILES['image_car_licence2']['name'], $_FILES['image_car_licence2']['tmp_name']);
            if(!$result_car_licence2['result'])
                wp_send_json_error($result_car_licence2['msg']);
        }

        $result_licence = tamzang_upload_picture('/driver_licence/', $_FILES['image_licence']['name'], $_FILES['image_licence']['tmp_name']);
        if(!$result_licence['result'])
            wp_send_json_error($result_licence['msg']);

        $result_id_card = tamzang_upload_picture('/driver_id_card/', $_FILES['image_id_card']['name'], $_FILES['image_id_card']['tmp_name']);
        if(!$result_id_card['result'])
            wp_send_json_error($result_id_card['msg']);

        $result_avatars = tamzang_upload_picture('/driver_avatars/', $_FILES['image']['name'], $_FILES['image']['tmp_name']);
        if(!$result_avatars['result'])
            wp_send_json_error($result_avatars['msg']);

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO register_driver SET wp_user_id = %d, name = %s, phone = %s, note = %s, regis_date = %s, approve = %d,
                 profile_pic = %s, id_card = %s, licence = %s, car_licence = %s, car_licence2 = %s ",
                array($current_user->ID, $data['name'], $data['phone'], $data['note'], $current_date, 0, 
                $result_avatars['file_name'], $result_id_card['file_name'], $result_licence['file_name'], $result_car_licence['file_name'], $result_car_licence2['file_name'])
            )
        );

        wp_send_json_success();
    }
    else{
        wp_send_json_error("ท่านได้สมัครสมาชิคแล้ว");
    }
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

add_action('wp_ajax_update_driver_profile', 'update_driver_profile_callback');

function update_driver_profile_callback(){
  global $wpdb, $current_user;
  $data = $_POST;
//   file_put_contents( dirname(__FILE__).'/debug/POST.log', var_export( $_POST, true));
//    file_put_contents( dirname(__FILE__).'/debug/register_driver.log', var_export( $_FILES, true));
//    wp_send_json_error();
  if ( check_ajax_referer( 'update_driver_profile_' . $data['driver_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try
  {

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM register_driver where wp_user_id = %d", array($data['driver_id'])
        )
    );

    if($id != NULL && !empty($id) ){

        $parameters[] = $data['name'];
        $parameters[] = $data['phone'];
        
    
        $a = false;
        if(!empty($_FILES['image']['name']))
        {
            $result_avatars = tamzang_upload_picture('/driver_avatars/', $_FILES['image']['name'], $_FILES['image']['tmp_name']);
            if(!$result_avatars['result'])
                wp_send_json_error($result_avatars['msg']);

            $parameters[] = $result_avatars['file_name'];
            $a = true;
        }

        $b = false;
        if(!empty($_FILES['image_id_card']['name']))
        {
            $result_id_card = tamzang_upload_picture('/driver_id_card/', $_FILES['image_id_card']['name'], $_FILES['image_id_card']['tmp_name']);
            if(!$result_id_card['result'])
                wp_send_json_error($result_id_card['msg']);

            $parameters[] = $result_id_card['file_name'];
            $b = true;
        }

        $c = false;
        if(!empty($_FILES['image_licence']['name']))
        {
            $result_licence = tamzang_upload_picture('/driver_licence/', $_FILES['image_licence']['name'], $_FILES['image_licence']['tmp_name']);
            if(!$result_licence['result'])
                wp_send_json_error($result_licence['msg']);

            $parameters[] = $result_licence['file_name'];
            $c = true;
        }

        $d = false;
        if(!empty($_FILES['image_car_licence']['name']))
        {
            $result_car_licence = tamzang_upload_picture('/driver_car_licence/', $_FILES['image_car_licence']['name'], $_FILES['image_car_licence']['tmp_name']);
            if(!$result_car_licence['result'])
                wp_send_json_error($result_car_licence['msg']);

            $parameters[] = $result_car_licence['file_name'];
            $d = true;
        }

        $e = false;
        if(!empty($_FILES['image_car_licence2']['name']))
        {
            $result_car_licence2 = tamzang_upload_picture('/driver_car_licence2/', $_FILES['image_car_licence2']['name'], $_FILES['image_car_licence2']['tmp_name']);
            if(!$result_car_licence2['result'])
                wp_send_json_error($result_car_licence2['msg']);

            $parameters[] = $result_car_licence2['file_name'];
            $e = true;
        }

        $parameters[] = $data['note'];
        $parameters[] = $data['driver_id'];

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE register_driver SET name = %s, phone = %s,  
                ".($a?'profile_pic = %s, ':'')." 
                ".($b?'id_card = %s, ':'')." 
                ".($c?'licence = %s, ':'')." 
                ".($d?'car_licence = %s, ':'')." 
                ".($e?'car_licence2 = %s, ':'')." note = %s WHERE wp_user_id = %d ",
                $parameters
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE register_driver SET name = %s  WHERE wp_user_id = %d ",
                array($data['name'], $data['driver_id'])
            )
        );

        wp_send_json_success();
    }
    else{
        wp_send_json_error("ไม่พบ id");
    }
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_update_picture', 'driver_update_picture_callback');

function driver_update_picture_callback(){
    global $wpdb, $current_user;
    $data = $_POST;
    //file_put_contents( dirname(__FILE__).'/debug/driver_update_picture.log', var_export( $_FILES, true));
    //wp_send_json_error($_FILES['file']['name'].'--test--'.$_FILES['file']['tmp_name']);
    //check the nonce
    if ( check_ajax_referer( 'driver_update_picture_' . $data['driver_id'], 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    if($current_user->ID != $data['driver_id'])
        wp_send_json_error();
  
    $result = tamzang_upload_picture('/driver_avatars/', $_FILES['file']['name'], $_FILES['file']['tmp_name']);

    if($result['result']){
        try
        {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE driver SET profile_pic = %s WHERE Driver_id = %d ",
                    array($result['file_name'], $current_user->ID)
                )
            );
            wp_send_json_success($result['msg']);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    else
        wp_send_json_error($result['msg']);

}

function tamzang_upload_picture($folder,$img,$tmp){
    global $current_user;
    $uploads = wp_upload_dir();
    $path = $uploads['basedir'] . $folder;
    $valid_extensions = array('jpeg', 'jpg', 'png');
    
    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));

    if(!in_array($ext, $valid_extensions))
        return array('msg' => "allow jpeg jpg png", 'result' => false);
    
    if ( !is_dir( $path ) ) {
        wp_mkdir_p( $path );
    }

    $path = $path.strtolower($current_user->ID.'.'.$ext);
    if(move_uploaded_file($tmp,$path)) 
    {
        $file_name = $folder.$current_user->ID.'.'.$ext;
        return array('msg' => $uploads['baseurl'].$file_name, 'result' => true, 'file_name' => $file_name);
    }else{
        return array('msg' => "move_uploaded_file error", 'result' => false);
    }
}

//Ajax functions
add_action('wp_ajax_load_driver_pending', 'load_driver_pending_callback');

function load_driver_pending_callback(){
  get_template_part( 'driver/driver', 'pending'  );
  wp_die();
}

// AJAX function
add_action('wp_ajax_update_driver_piority', 'update_driver_piority');
//get list Driver for restaurant
function update_driver_piority() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
    $data = $_POST;
	
	$driver_id = $_POST['driverID'];
	$Tamzang_id = $_POST['TamzangId'];
	$priority_num = $_POST['priority'];

	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $priority_num, true));

	
	$sql = $wpdb->prepare(
            "SELECT * FROM driver_of_restaurant WHERE Tamzang_id = %d ",
            array($Tamzang_id)
        );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
	$is_viewed = $result[0]['id'];
	$is_exist = $wpdb->num_rows;
	
	
	if($is_exist > 0 )
	{
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver_of_restaurant SET win_%d = %d where Tamzang_id = %d",
          array($priority_num,$driver_id,$Tamzang_id)
        )
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
	}
	else{
		$wpdb->insert( 
			'driver_of_restaurant', 
			array( 'Tamzang_id' => $Tamzang_id, 'win_'.$priority_num => $driver_id), 
			array( '%d','%d','%d')
		);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Insert", true));
	}



	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	wp_send_json_success($return_arr);

}

//Ajax functions
add_action('wp_ajax_load_order_list', 'load_order_list_callback');

function load_order_list_callback(){
    $data = $_GET;
    set_query_var( 'pageNumber', $data['page'] );
    get_template_part( 'driver/driver', 'order_list'  );
    wp_die();
}


// AJAX function
add_action('wp_ajax_get_order_list_delivery', 'get_order_list_delivery');
//get list Driver for restaurant
function get_order_list_delivery() {
    global $wpdb;
	$return_arr = array();
	$return_web = array();
	$restaurant_array = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;
	
    $Order_id = $data['OrderId'];
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));

	if($Order_id == null)
	{
		$sql = $wpdb->prepare(
            "SELECT * FROM orders WHERE deliver_ticket = 'Y'and status Not in (99,5) order by id ",array()
        );
	}
	else{
		$sql = $wpdb->prepare(
            "SELECT * FROM orders WHERE deliver_ticket = 'Y' and id = %d and status Not in (99,5) ",array($Order_id)  
        );
	}
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_list_order = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
	foreach ($result_list_order as $list_order)
	{
		$assign_drivers = array();
		$res_id = $list_order->post_id;
		$order_id = $list_order->id;
		$buyer_id = $list_order->wp_user_id;
		// get name of buyer
		$name = get_userdata($buyer_id);
		$order_date = $list_order->order_date;
	    $assign_drivers['id'] = $res_id;
		$assign_drivers['order_id'] = $order_id;
		$assign_drivers['buyer_name'] = $name->user_login;
		$name = get_userdata($buyer_id);
		$assign_drivers['order_date'] = $order_date;
		//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $name->user_login, true));	
		
		$return_arr[] = $assign_drivers;
	}
	wp_send_json_success($return_arr);
    wp_die();
}

// Get restaurant name and Tamzang ID
function get_res_name_id($post_id){
	global $wpdb;
	
	$sql = "SELECT * FROM wp_geodir_gd_place_detail where post_id = ".$post_id."";
	$result_res  = $wpdb->get_results($sql, ARRAY_A );
	$res_name = $result_res[0]['post_title'];
	$tamzang_id = $result_res[0]['geodir_tamzang_id'];
	
	return array($res_name,$tamzang_id);
}

//AJAX FUNCTION
add_action('wp_ajax_listdriverassign', 'listdriverassign');
function listdriverassign(){


	$driver_assign_array = $_GET;
	set_query_var( 'total_order', $driver_assign_array['order_num']); 
	set_query_var( 'res_id', $driver_assign_array['res_id']);
	set_query_var( 'order_id', $driver_assign_array['order_id']);    
  
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_assign_array['res_id'], true));

  get_template_part( 'ajax-driver-assign' );
  wp_die();
}

function driver_map( $title, $post_latitude, $post_longitude ) {
/*
    if ( !empty( $title ) && !empty( $post_latitude ) && !empty( $post_longitude ) ) {
        $maps_url = add_query_arg( array(
                        'q' => $title,
                        'sll' => $post_latitude . ',' . $post_longitude,
                    ), 'http://maps.google.com/' );
        ?>
        <a href="<?php echo $maps_url; ?>" class="btn btn-info" target="_blank"><span style="color: #ffffff !important;" >แผนที่</span></a>
        <?php
    }
    */
	if (!empty( $post_latitude ) && !empty( $post_longitude ))
	{
		$maps_url = add_query_arg( array(
                        'q' =>$post_latitude . ',' . $post_longitude,
                    ), 'http://maps.google.com/' );
        ?>
        <a href="<?php echo $maps_url; ?>" class="btn btn-info" target="_blank"><span style="color: #ffffff !important;" >แผนที่</span></a>
        <?php
	}
}

//Ajax functions
add_action('wp_ajax_driver_confirm_order', 'driver_confirm_order_callback');

function driver_confirm_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_confirm_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT Driver_id,driver_order_id FROM driver_order_log_assign where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner->Driver_id){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver_order_log_assign SET status = 2 where Id = %d ",
                array($data['log_id'])
            )
        );

        // Bank Add delete status 1 in driver_order_log_assign
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM  driver_order_log_assign WHERE status = 1 and driver_order_id = %d ",
                array($owner->driver_order_id)
            )
        );

        // Calculate Commission Driver
        // Get distance + deliver_price from shipping_address
        $delivery_price = $wpdb->get_row(
            $wpdb->prepare(
                "select A.post_id,B.price,B.distance from orders A join shipping_address B  WHERE A.id = B.order_id and B.order_id = %d", array($owner->driver_order_id)
            )
        );

        // Get delivery variable for Calculate tier
        $delivery_value = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT base,base_adjust,km_tier1,km_tier1_value,km_tier2,km_tier2_value,km_tier3_value FROM delivery_variable where post_id = %d ", array($delivery_price->post_id)
            )
        );
        $range_t1 = (($delivery_value->km_tier1)>=$delivery_price->distance)? $delivery_price->distance:$delivery_value->km_tier1;			
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T1:".$range_t1, true),FILE_APPEND);
        $range_t2 = (($delivery_value->km_tier1)<=$delivery_price->distance)?((($delivery_value->km_tier2)>=$delivery_price->distance-$range_t1)? $delivery_price->distance-($delivery_value->km_tier1):$delivery_value->km_tier2-$delivery_value->km_tier1):0;
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T2:".$range_t2, true),FILE_APPEND);
        $range_t3 = ($delivery_value->km_tier2<=$delivery_price->distance)?(($delivery_price->distance)-($delivery_value->km_tier2)):0;
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T3:".$range_t3, true),FILE_APPEND);

        
        $commission_value = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM driver_variable where driver_id = %d ", array($owner->Driver_id)
            )
        );  
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T2:".$commission_value->tier2_percent, true),FILE_APPEND);

        
        $commission = sprintf("%.2f",$commission_value->base_constance + (($commission_value->base_percent*$delivery_value->base)/100)
                    + $commission_value->tier1_constance+ (($commission_value->tier1_percent*($range_t1*$delivery_value->km_tier1_value))/100)
                    + $commission_value->tier2_constance+ (($commission_value->tier2_percent*($range_t2*$delivery_value->km_tier2_value))/100)
                    + $commission_value->tier3_constance+ (($commission_value->tier3_percent*($range_t3*$delivery_value->km_tier3_value))/100));

                    /*
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Commissoin Base:".$com_base, true),FILE_APPEND);
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Commissoin 1 tier:".$com_t1, true),FILE_APPEND);
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Commissoin 2 tier:".$com_t2, true),FILE_APPEND);
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Commissoin 3 tier:".$com_t3, true),FILE_APPEND);
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Commissoin Sum:".$commission, true),FILE_APPEND);
*/

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE orders SET status = 2 ,commission = %f  where id = %d ",
                array($commission,$data['id'])
            )
        );
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE shipping_address SET commission = %f  where order_id = %d ",
                array($commission,$data['id'])
            )
        );


    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_cancel_order', 'driver_cancel_order_callback');

function driver_cancel_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_cancel_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT Driver_id,driver_order_id FROM driver_order_log_assign where Id = %d ", array($data['log_id'])
        )
    );
    $buyer = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT wp_user_id FROM orders where Id = %d ", array($owner->driver_order_id)
        )
    );

    if($current_user->ID == $owner->Driver_id){

        $order = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT status, cancel_code FROM orders where id = %d ", array($owner->driver_order_id)
            )
        );

        if($order->status == 2){
            if($order->cancel_code != "")
                wp_send_json_success("<h4>รหัสยืนยันคำสั่งซื้อ: ".$order->cancel_code."</h4>");
            
            $tz = 'Asia/Bangkok';
            $timestamp = time();
            $dt = new DateTime("now", new DateTimeZone($tz));
            $dt->setTimestamp($timestamp);

            $current_date = $dt->format("Y-m-d H:i:s");
            $code = rand(1000, 9999);

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE orders SET cancel_code = %s, cancel_date = %s where id = %d ",
                    array($code,$current_date,$owner->driver_order_id)
                )
            );

             // Send Notification to user who Subscribe with OneSignal
             $message = "กรุณาติดต่อ พนักงานส่ง ภายใน 5 นาที หากไม่ดำเนินการระบบจะยกเลิกคำสั่งซื้อนี้";
            
             $sql = $wpdb->prepare(
                 "SELECT device_id FROM onesignal where user_id=%d ", array($buyer->wp_user_id)
             );
             $player_id_array = $wpdb->get_results($sql);
             foreach ($player_id_array as $list_player_device)
             {
                 $player_id = $list_player_device->device_id;
                 $response = sendMessage($player_id,$message);
                 $return["allresponses"] = $response;
                 $return = json_encode( $return);
                 file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "Return :".$return."\n", true),FILE_APPEND);
             }

            wp_send_json_success("<h4>รหัสยืนยันคำสั่งซื้อ: ".$code."</h4>");


        }else if($order->status > 2){
            wp_send_json_success("<h4>คุณได้ยืนยันคำสั่งซื้อแล้ว</h4>");
        }

    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_customer_confirm_code', 'customer_confirm_code_callback');

function customer_confirm_code_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'customer_confirm_code_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $order = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT wp_user_id,cancel_code FROM orders where Id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $order->wp_user_id){

        if($order->cancel_code == $data['code']){
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE orders SET cancel_code = 'ok' where id = %d ",
                    array($data['id'])
                )
            );

            wp_send_json_success("ยืนยันคำสั่งซื้อเรียบร้อย");
        }else{
            wp_send_json_error("รหัสไม่ถูกต้อง");
        }
    

    }else{
        wp_send_json_error();
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


//Ajax functions
add_action('wp_ajax_driver_reject_order', 'driver_reject_order_callback');

function driver_reject_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_confirm_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log_assign where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner){

        $log_assign = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM driver_order_log_assign where id = %d ", array($data['log_id'])
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO driver_order_log SET
                tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 4,assign_date =%s ".(!empty($log_assign->transfer_date) ? ",transfer_date =%s" : ""),
                array($log_assign->tamzang_id, $log_assign->driver_id, $log_assign->driver_order_id, $log_assign->assign_date, $log_assign->transfer_date)
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver_order_log_assign SET status = 4 where Id = %d ",
                array($data['log_id'])
            )
        );

    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

function driver_text_step($step){
    if($step == 2)
        return "ยืนยันคำสั่งซื้อ";
    elseif($step == 3)
        return "รับสินค้า";
    elseif($step == 4)
        return "ปิดงาน";
}

add_action('wp_ajax_driver_next_step', 'driver_next_step_callback');

function driver_next_step_callback(){
    global $wpdb, $current_user;
  
    $data = $_POST;
  
    // check the nonce
    if ( check_ajax_referer( 'driver_next_step_' . $data['id'], 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    try {
      // get Driver_id who accept job from assign 
      $owner = $wpdb->get_var(
          $wpdb->prepare(
              "SELECT Driver_id FROM driver_order_log_assign where Driver_order_id = %d and driver_id = %d and status = 2", array($data['id'],$current_user->ID)
          )
      );
      // Get Restaurant ID
      $Tamzang_id = $wpdb->get_var(
          $wpdb->prepare(
              "SELECT tamzang_id FROM driver_order_log_assign where Driver_order_id = %d and driver_id = %d and status = 2", array($data['id'],$current_user->ID)
          )
      );
  
      if(!empty($owner)){
          // Check status of the order
          $order = $wpdb->get_row(
              $wpdb->prepare(
                  "SELECT status, commission, wp_user_id, id, redeem_point, cancel_code, promotion_id FROM orders where id = %d ", array($data['id'])
              )
          );

          if(!empty($order->cancel_code) && $order->cancel_code != "ok")
            wp_send_json_error();
  
          if($order->status < 5){
              $order->status++;
  
              $wpdb->query(
                  $wpdb->prepare(
                      "UPDATE orders SET status = %d where id = %d ",
                      array($order->status, $data['id'])
                  )
              );
              if($order->status == 5){
                $tz = 'Asia/Bangkok';
                $timestamp = time();
                $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
                $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
              
                $current_date = $dt->format("Y-m-d H:i:s");
                // Job done 
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO driver_order_log SET
                        tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 3,assign_date =%s",
                        array($Tamzang_id,$owner,$data['id'],$current_date)
                    )
                );

                // check If buyer use Promotion
                if($order->promotion_id != 0)
                {
                    file_put_contents( dirname(__FILE__).'/debug/promition_check.log', var_export( "user used Promiton!!", true));
                    $shipping_address = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM shipping_address where order_id = %d ", array($order->id)
                        )
                    );
                    $shipping_price = $shipping_address->price;
                    $result = cal_shipping_price_with_promotion($order->promotion_id, $shipping_price);
                    $driver_promotion_cash = number_format($shipping_price-$result,2,'.','') ;

                }

                $drivercommission = (!empty($order->commission))?$order->commission:0;

                    $driver = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM driver where driver_id = %d ", array($current_user->ID)
                        )
                    );

                    $user_cash_back = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM cash_back where user_id = %d ", array($order->wp_user_id)
                        )
                    );

                    if(!empty($user_cash_back)){
                        $shipping_price = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT price FROM shipping_address where order_id = %d ", array($order->id)
                            )
                        );
    
                        $cash_back = ( $shipping_price * ($user_cash_back->cash_back_percentage/100)) * $driver->cash_back_point_rate;
    
                        if($driver->cash_back_level == "exclude"){
                            driver_commission($drivercommission, $driver, $current_date, $order->id,$driver_promotion_cash);
                        }else{
                            $new_commission = $drivercommission - $cash_back;
                            driver_commission($new_commission, $driver, $current_date, $order->id,$driver_promotion_cash);
                        }
    
                        $driver_new_balance = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT balance FROM driver where Driver_id = %d ", array($driver->Driver_id)
                            )
                        );
    
                        $driver_new_balance = $driver_new_balance - $cash_back;
    
                        insert_driver_transaction_details("COMMISSION",
                        array($driver->Driver_id, $cash_back, $driver_new_balance, "CASH_BACK", $current_date, $order->id));

                        $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE driver SET balance = %f where driver_id = %d ",
                                array($driver_new_balance, $driver->Driver_id)
                            )
                        );

                        if($order->redeem_point)
                            calculate_redeem_and_driver_credit($shipping_price, $user_cash_back, $current_date, $order->id, $driver->Driver_id);
                        else{// user cash back
                            $user_add_on_credit = ($shipping_price * ($user_cash_back->cash_back_percentage/100));
                            $wpdb->query(
                                $wpdb->prepare(
                                    "UPDATE cash_back SET add_on_credit = (add_on_credit + %f) where user_id = %d ",
                                    array($user_add_on_credit, $user_cash_back->user_id)
                                )
                            );
                        }

                    }else{
                        if($order->promotion_id != 0) // buyer used promotino
                        {
                            file_put_contents( dirname(__FILE__).'/debug/promition_check.log', var_export( "Input promotion on transaction detail!!", true),FILE_APPEND);
                            driver_commission($drivercommission, $driver, $current_date, $order->id,$driver_promotion_cash);
                        }
                        else{ // No promotion used
                            file_put_contents( dirname(__FILE__).'/debug/promition_check.log', var_export( "No promotion used!!", true),FILE_APPEND);
                            driver_commission($drivercommission, $driver, $current_date, $order->id,$driver_promotion_cash);
                        }
                        
                    }


                


                // Bank Add delete all  in driver_order_log_assign After Job done
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM  driver_order_log_assign WHERE driver_order_id = %d ",
                        array($data['id'])
                    )
                );
                wp_send_json_success("close");
              }
              else
              {
                  if($order->status == 3){
                      $code = $wpdb->get_var(
                          $wpdb->prepare(
                              "SELECT cancel_code FROM orders where Id = %d ", array($data['id'])
                          )
                      );
                  }
                  if($code != "" && $code != "ok"){
                      $wpdb->query(
                          $wpdb->prepare(
                              "UPDATE orders SET cancel_code = 'ok'  where id = %d ",
                              array($data['id'])
                          )
                      );
                  }
                  wp_send_json_success(driver_text_step($order->status));
              }
                  
          }else{
              wp_send_json_error("error2");
          }
  
      }else{
          wp_send_json_error("error1");
      }
  
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
  
}
  
function insert_driver_transaction_details($type, $parameters){// ตัด commission
    global $wpdb;

    if($type == "COMMISSION"){// balance
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO driver_transaction_details SET
                driver_id = %d,debit = %f,balance = %f,	transaction_type = %s, transaction_date = %s, order_id = %d",
                $parameters
            )
        );
    }elseif($type == "PROMOTION_CREDIT"){// balance_driver_cash
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO driver_transaction_details SET
                driver_id = %d,credit = %f,balance_driver_cash = %f,	transaction_type = %s, transaction_date = %s, order_id = %d",
                $parameters
            )
        );
    }elseif($type == "DRIVER_WITHDRAW"){
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO driver_transaction_details SET
                driver_id = %d,debit = %f, balance_driver_cash = %f, transaction_type = %s, transaction_date = %s ",
                $parameters
            )
        );
    }
    else{// credit
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO driver_transaction_details SET
                driver_id = %d, debit = %f, balance_add_on_credit = %f,	transaction_type = %s, transaction_date = %s, order_id = %d",
                $parameters
            )
        );
    }

}

function driver_commission($commission, $driver, $current_date, $order_id, $driver_promotion_cash){
    global $wpdb;

    file_put_contents( dirname(__FILE__).'/debug/promition_check.log', var_export( "Driver Cash Promo :".$driver_promotion_cash, true),FILE_APPEND);

    if($driver->add_on_credit == 0 || $driver->add_on_credit == ""){// ไม่มี point
        $driver->balance = $driver->balance - $commission;
        insert_driver_transaction_details("COMMISSION", 
        array($driver->Driver_id, $commission, $driver->balance, "COMMISSION", $current_date, $order_id));
    }else{
        $debit = $driver->add_on_credit - $commission;
        if($debit < 0){// point ติดลบ
            $driver->balance = $driver->balance + $debit;// point ติดลบ !!!
            
            insert_driver_transaction_details("ADD_ON_COMMISSION",
            array($driver->Driver_id, $driver->add_on_credit, 0, "ADD_ON_COMMISSION", $current_date, $order_id));

            $driver->add_on_credit = 0;

            insert_driver_transaction_details("COMMISSION",
            array($driver->Driver_id, abs($debit), $driver->balance, "COMMISSION", $current_date, $order_id));
        }else{// point เหลือ หรือ เท่ากับศูนย์
            $driver->add_on_credit = $debit;
            insert_driver_transaction_details("ADD_ON_COMMISSION",
            array($driver->Driver_id, $commission, $debit, "ADD_ON_COMMISSION", $current_date, $order_id));
        }
    }

    if(!empty($driver_promotion_cash))
    {
        $New_driver_balacne_cash = $driver->driver_cash + $driver_promotion_cash;
        file_put_contents( dirname(__FILE__).'/debug/promition_check.log', var_export( "PUT  INTO Transaction detail  :", true),FILE_APPEND);
        insert_driver_transaction_details("PROMOTION_CREDIT",
        array($driver->Driver_id, $driver_promotion_cash, $New_driver_balacne_cash, "PROMOTION_CREDIT", $current_date, $order_id));

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET driver_cash = %f where driver_id = %d ",
                array($New_driver_balacne_cash, $driver->Driver_id)
            )
        );
    }
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE driver SET balance = %f, add_on_credit = %f where driver_id = %d ",
            array($driver->balance, $driver->add_on_credit, $driver->Driver_id)
        )
    );

}

function calculate_redeem_and_driver_credit($shipping_price, $user_cash_back, $current_date, $order_id, $driver_id){
    global $wpdb;

    $minus_credit = $user_cash_back->add_on_credit * $user_cash_back->redeem_point_rate;
    $minus_credit = abs($shipping_price - $minus_credit);
    if($minus_credit > 0)
        $minus_credit = $minus_credit / $user_cash_back->redeem_point_rate;

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE cash_back SET add_on_credit = %f where user_id = %d ",
            array($minus_credit, $user_cash_back->user_id)
        )
    );

    $driver = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM driver where driver_id = %d ", array($driver_id)
        )
    );

    $driver_add_on_credit = $shipping_price * $driver->redeem_rate;

    $wpdb->query(
        $wpdb->prepare(
        "INSERT INTO driver_transaction_details SET driver_id = %d, credit = %f, balance_add_on_credit = %f, transaction_type = %s, transaction_date = %s, order_id = %d ",
        array($driver->Driver_id,$driver_add_on_credit,$driver_add_on_credit+$driver->add_on_credit,"REDEEM_CREDIT",$current_date,$order_id)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE driver SET add_on_credit = add_on_credit + %f where driver_id = %d ",
            array($driver_add_on_credit, $driver->Driver_id)
        )
    );
}

//Ajax functions
add_action('wp_ajax_driver_adjust_price', 'driver_adjust_price_callback');

function driver_adjust_price_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_adjust_price_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log_assign where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET driver_adjust = %f where id = %d ",
              array($data['adjust'], $data['id'])
          )
      );

      wp_send_json_success();

    }else{
        wp_send_json_error();
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_customer_response_adjust', 'customer_response_adjust_callback');

function customer_response_adjust_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'customer_response_adjust_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $customer = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT o.wp_user_id,o.total_amt,o.driver_adjust,s.price,o.status as order_status,o.redeem_point FROM orders as o
            inner join shipping_address as s on o.shipping_id = s.id
            where o.id = %d ", array($data['id'])
        )
    );

    if($customer->order_status >= 3)
        wp_send_json_success("พนักงานตามส่งยืนยันคำสั่งซื้อแล้ว");

    if($current_user->ID == $customer->wp_user_id){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET adjust_accept = %d ".($data['status'] == "0" ? ", status = 99" : "")." where wp_user_id = %d AND id = %d ",
              array($data['status'], $customer->wp_user_id, $data['id'])
          )
      );

      if($data['status'] == "0"){
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM driver_order_log_assign WHERE driver_order_id = %d ",
                array($data['id'])
            )
        );
      }

      if($customer->redeem_point)
        wp_send_json_success($customer->total_amt+$customer->driver_adjust);
      else
        wp_send_json_success($customer->total_amt+$customer->driver_adjust+$customer->price);

    }else{
        wp_send_json_error();
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_load_driver_order_template', 'load_driver_order_template_callback');

function load_driver_order_template_callback(){
  get_template_part( 'driver/driver', 'order_template' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_load_driver_transaction_details', 'load_driver_transaction_details_callback');

function load_driver_transaction_details_callback(){
  get_template_part( 'driver/driver', 'transaction_details' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_load_driver_transaction_list', 'load_driver_transaction_list_callback');

function load_driver_transaction_list_callback(){
  set_query_var( 'start_date', $_POST['start_date'] );
  set_query_var( 'end_date', $_POST['end_date'] );
  get_template_part( 'driver/driver', 'transaction_list' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_supervisor_assign_order', 'supervisor_assign_order_callback');

function supervisor_assign_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;
  $current_date = tamzang_get_current_date();

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'supervisor_assign_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $order = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM driver_order_log_assign where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $order->driver_id){
        /*
        $employee = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver_id FROM driver where driver_id = %d AND supervisor = %d", array($data['driver_id'],$current_user->ID)
            )            
        );    
        */
        //Bank Adjust sql for driver who on task can't not recive any more order
        $employee = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver.driver_id,driver.driver_name FROM driver 
        WHERE Supervisor=%d
        and driver.driver_id NOT IN (SELECT DISTINCT driver_id FROM driver_order_log_assign WHERE driver_order_id=%d or status IN (1,2))",
        array($current_user->ID,$data['driver_id'])
            )            
        );  
        // bank Change sql to auto assign
        if(!empty($employee )){
            
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO driver_order_log SET tamzang_id = %d, driver_id = %d, driver_order_id = %d, status = %d, transfer_date = %s ",
                    array($order->tamzang_id, $order->driver_id, $order->driver_order_id, 4, $current_date)
                
                )
            );

            $check_order = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM driver_order_log_assign where driver_id = %d AND driver_order_id = %d AND status = 4 ", array($data['driver_id'],$order->driver_order_id)
                )
            );// ตรวจว่า A -> B แล้ว B -> A หรือเปล่า

            if(empty($check_order)){
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO driver_order_log_assign SET tamzang_id = %d, driver_id = %d, driver_order_id = %d, status = %d, assign_date = %s ",
                    array($order->tamzang_id, $data['driver_id'], $order->driver_order_id, 2, $current_date)
                    )
                );

                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM driver_order_log_assign WHERE  status = 2 and  driver_id = %d ",
                        array($order->driver_id)
                    )
                );

                wp_send_json_success();
            }else{
                wp_send_json_error();
            }
        }
    }else{
        wp_send_json_error();
    }

    
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_ready', 'driver_ready_callback');

function driver_ready_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_ready_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver where Driver_id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $owner){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET is_ready = !is_ready where Driver_id = %d ",
                array($data['id'])
            )
        );

        wp_send_json_success();

    }else{
        wp_send_json_error("wrong user");
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_pin_enable', 'driver_pin_enable_callback');

function driver_pin_enable_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_pin_enable_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver where Driver_id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $owner){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET pin_enable = !pin_enable where Driver_id = %d ",
                array($data['id'])
            )
        );

        wp_send_json_success();

    }else{
        wp_send_json_error("wrong user");
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_tamzangEnable', 'driver_tamzangEnable_callback');

function driver_tamzangEnable_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_tamzangEnable_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver where Driver_id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $owner){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET tamzangEnable = !tamzangEnable where Driver_id = %d ",
                array($data['id'])
            )
        );

        wp_send_json_success();

    }else{
        wp_send_json_error("wrong user");
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

// AJAX function
add_action('wp_ajax_assign_order_driver', 'assign_order_driver');
//get list Driver for restaurant
function assign_order_driver() {
    global $wpdb;
	$current_date = tamzang_get_current_date();
	$return_arr = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
    $data = $_POST;

    $status = $_POST['status'];
	$order_id = $_POST['orderID'];
    $Tamzang_id = $_POST['TamzangId'];
    $emer_driver_id = $data['driverID'];
    $driver_id = $_POST['priority'];
    $driver_id_sql = (empty($emer_driver_id))?$driver_id:$emer_driver_id;

    //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_id, true));
    // Get tamzang ID and Driver ID
    $driver_cancel = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT tamzang_id,driver_id FROM driver_order_log_assign where status IN (1,2) and driver_order_id =%d ", array($order_id)
        )
    );
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $driver_cancel->tamzang_id, true));

	/*
	$sql = $wpdb->prepare(
            "SELECT * FROM driver_order_log WHERE driver_order_id = %d and driver_id = %d ",
            array($order_id,$driver_id)
        );
		*/
		

    // Check in driver_order_log
    /*
	$sql = "SELECT * FROM driver_order_log where status = 2 and (driver_order_id =".$order_id." or driver_id =".$driver_id_sql.")";
    $result_driver = $wpdb->get_results($sql, ARRAY_A );
	*/
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $sql, true));
	//$is_status = $result_driver[0]['status'];
	//$is_exist = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $is_status, true),FILE_APPEND);
    //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $is_exist, true),FILE_APPEND);
    
    // Check in driver_order_log_assign
    $sql_assign = "SELECT * FROM driver_order_log_assign where (status = 2 and driver_id =".$driver_id_sql.") and (status IN (1,4) and driver_id =".$driver_id_sql." and driver_order_id =".$order_id.")";
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
    $result_driver_assign = $wpdb->get_results($sql_assign, ARRAY_A );
    $is_exist_assign = $wpdb->num_rows;
	
	//Cancel Assign
	if( ($is_exist_assign >=0) && ($status == "Cancel") )
	{
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
        if($is_exist_assign == 0)
        {
            $return_result = "Nothing to Cancel";
            wp_send_json_success($return_result);
        }
        else{
            $wpdb->query(
                $wpdb->prepare(
                 "INSERT INTO driver_order_log SET
                 tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 4,assign_date =%s",
                 array($driver_cancel->tamzang_id,$driver_cancel->driver_id,$order_id,$current_date)
             )
            );
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE driver_order_log_assign SET status = 4 where driver_id = %s and driver_order_id = %d  ",
                 array($driver_cancel->driver_id,$order_id)
                )
            );    
            //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
            $return_result = "Cancel Order is compleate";
            wp_send_json_success($return_result);
        }

	}
	//Abort
	else if( ($is_exist_assign >= 0) && ($status == "Abort") )
	{      
           	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
	        // Update order in driver_order_log
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO driver_order_log SET
                    tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 5,assign_date =%s",
                    array($driver_cancel->tamzang_id,$driver_cancel->driver_id,$order_id,$current_date)
                )
            );
            // Delete all in driver_order_log_assign
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM  driver_order_log_assign WHERE driver_order_id = %d ",
                    array($order_id)
                    )
                );
                // Update order in orders
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE  orders SET deliver_ticket = 'Y',status = 99 where id = %d",array($order_id)                    
                    )
                );
                //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
                $return_result = "Abort Order is compleate";
                wp_send_json_success($return_result);
        
	}
	else if(($is_exist_assign >0) && ($status == null))
	{
		$return_result = "Cannot Assign this order to Driver This Order is already Assign";
		wp_send_json_success($return_result);
	}
    else
    {
        if($is_exist_assign == 0)
        {
            // Check this driver never reject this job
            $driver_log_status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT status FROM driver_order_log where driver_id=%d and driver_order_id =%d ", array($driver_id,$order_id)
                )
            );
            $driver_log_assign_status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT status FROM driver_order_log_assign where driver_id=%d and driver_order_id =%d ", array($driver_id,$order_id)
                )
            );

            if(($driver_log_status == 4) || ($driver_log_assign_status == 4))
            {
                $return_result = "##!!This Driver is Already Reject this Order!!##";
                wp_send_json_success($return_result);
            }
            else{

                if(empty($emer_driver_id))
                {
                
                    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "No Emer and Date :".$current_date, true),FILE_APPEND);
                    $query = $wpdb->prepare("INSERT INTO driver_order_log_assign SET
                                 tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 1,assign_date =%s",
                                 array($Tamzang_id,$driver_id,$order_id,$current_date)
                              );
                    $wpdb->query($query);
                    $username = get_user_by('id',$driver_id);
                }
                else
                {			
                    $query = $wpdb->prepare("INSERT INTO driver_order_log_assign SET
                                 tamzang_id = %d,driver_id = %s,driver_order_id =%d,status =1,assign_date =%s",
                                 array($Tamzang_id,$emer_driver_id,$order_id,$current_date)
                              );
                    $wpdb->query($query);
                    $username = get_user_by('id',$emer_driver_id);
                }		
                $driver_message = (empty($emer_driver_id))?$driver_id:$emer_driver_id;
                file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "User name is :".$username->user_nicename, true),FILE_APPEND);
                // file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "User name is :".$username, true),FILE_APPEND);
            
                // สร้าง message
                $current_date = tamzang_get_current_date();
                $thread_id = $wpdb->get_var(
                    $wpdb->prepare(
                    "SELECT thread_id FROM wp_bp_messages_messages ORDER BY thread_id DESC LIMIT 1 ", array()
                    )
                );
                $thread_id++;
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = 1, subject = %s, message = %s, date_sent = %s ",
                    array($thread_id, "ใบสั่งซื้อเลขที่: #".$order_id , '<strong><p style="font-size:14px;">ได้รับ order จากพนักงานตามสั่ง</p> <a href="'.home_url('/members/').$username->user_nicename."/driver/".'">คลิกที่นี่เพื่อดู Order</a></strong>', $current_date)
                    )
                );
        
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO wp_bp_messages_recipients SET user_id = %d, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
                    array($driver_message, $thread_id, 1, 0, 0)
                    )
                );
        
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO wp_bp_messages_recipients SET user_id = 1, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
                    array( $thread_id, 0, 1, 0)
                    )
                );
            
            
                //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Insert", true),FILE_APPEND);
            
                // Send Notification to user who Subscribe with OneSignal
                $message = "มี Order อาหารมาใหม่จาก Tamzang";
            
                $sql = $wpdb->prepare(
                    "SELECT device_id FROM onesignal where user_id=%d ", array($driver_message)
                );
                $player_id_array = $wpdb->get_results($sql);
                foreach ($player_id_array as $list_player_device)
                {
                    $player_id = $list_player_device->device_id;
                    $response = sendMessage($player_id,$message);
                    $return["allresponses"] = $response;
                    $return = json_encode( $return);
                    file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "Return :".$return."\n", true),FILE_APPEND);
                }
            
                $return_result = "New order is assign";
                wp_send_json_success($return_result);
                //echo $return_result;

            }		    
        }
        $return_result = "This Driver is waiting for answer another Order";
		wp_send_json_success($return_result);

	
	}

	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	

	//wp_send_json_success($return_arr);

}

// Onesignal Function Create Notification to player_id
function sendMessage($player_id,$message){
		$playerID = $player_id;
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log',"Send Notice Start to player_ID :".$playerID,FILE_APPEND);
		$content = array(
			"en" => $message
			);
		
		$fields = array(
            //30bcc12c-404d-494a-ac93-ac8ee755744f For Test02 || 73b7d329-0a82-4e80-aa74-c430b7b0705b for Prod
			'app_id' => "30bcc12c-404d-494a-ac93-ac8ee755744f",
			'include_player_ids' => array($playerID),
			//'include_player_ids' => array("1c072fb6-f1b3-44ba-9f19-7a6fb5534366","646d645e-382d-45d9-aea9-916401fe3954"),
			'data' => array("foo" => "bar"),
			'contents' => $content
		);
		
		$fields = json_encode($fields);
    	//print("\nJSON sent:\n");
    	//print($fields);
        
        
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
        return $response;
        
	}

// AJAX function
add_action('wp_ajax_get_driver_regis', 'get_driver_regis');
//get list Driver for restaurant
function get_driver_regis() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));

	$sql = $wpdb->prepare(
            "SELECT * FROM register_driver where approve = 0 ",array()  
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql);
	$is_exist = $wpdb->num_rows;

	if($is_exist >0)
	{
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
		//$result_list_order = $wpdb->get_results($sql);
		foreach ($result_driver as $list_order)
		{
			$assign_drivers = array();
			$usr_id = $list_order->wp_user_id;
			$name = $list_order->name;
			$phone = $list_order->phone;
            $note = $list_order->note;
            $picture = $list_order->profile_pic;
			// put value into process call Template

			//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $usr_id, true));
			set_query_var( 'usr_id', $usr_id); 
			set_query_var( 'name', $name); 
			set_query_var( 'phone', $phone); 
            set_query_var( 'note', $note);
            set_query_var( 'picture', $picture);
			get_template_part( 'ajax-driver-approve' );
		}
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));	
	
	wp_die();
	}
	else{
		$return_result = "0 Result";
		//wp_send_json_success($return_result);
		return $return_result;
		//get_template_part( 'ajax-driver-approve' );
	}

}

// AJAX function
add_action('wp_ajax_approve_driver', 'approve_driver');
//get list Driver for restaurant
function approve_driver() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
	$data = $_POST;
	$usr_id_array = $_POST['UserID'];
	$usr_id = explode(",", $usr_id_array);
	$count = count($usr_id);
	//$Tamzang_id = $_POST['TamzangId'];
	//$priority_num = $_POST['priority'];
	
	// generate sql
	$sql ="(".$usr_id_array.")";
	//$sql = str_replace("'","",$sql);	
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $sql, true));	

	// update status  register_driver		
	$status_sql = "UPDATE register_driver SET approve = 1 where wp_user_id IN ".$sql." and approve = '0'";
	$result_status = $wpdb->get_results($status_sql, ARRAY_A );
	
	// Get Value From register_driver
	$sql = "select * from register_driver where wp_user_id IN ".$sql;
	$result_list = $wpdb->get_results($sql, ARRAY_A );

	
	//$result_list = $wpdb->get_results($sql);
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $result_list, true),FILE_APPEND);
	foreach ($result_list as $row => $value )
	{
		$query = $wpdb->prepare("INSERT INTO driver SET
                             Driver_id = %d,driver_name = %s,phone =%d, profile_pic = %s ",
                             array($value['wp_user_id'],$value['name'],$value['phone'],$value['profile_pic'])
                          );
		$wpdb->query($query);
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Insert status ".$insert_status, true),FILE_APPEND);
	}

}

//Ajax functions
add_action('wp_ajax_approve_driver2', 'approve_driver2_callback');

function approve_driver2_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  //check the nonce
  if ( check_ajax_referer( 'approve_driver_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM driver where Driver_id = %d", array($data['id'])
        )
    );
    if(!empty($id))
        wp_send_json_success();

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE register_driver SET approve = !approve, approve_by = %d where wp_user_id = %d ",
            array($current_user->ID, $data['id'])
        )
    );

	$driver = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM register_driver where wp_user_id = %d ", array($data['id'])
		)
    );

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO driver SET
             Driver_id = %d,driver_name = %s,phone =%s, profile_pic = %s ",
             array($driver->wp_user_id, $driver->name, $driver->phone, $driver->profile_pic)
        )
    );

    wp_send_json_success($data);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

// AJAX function
add_action('wp_ajax_update_driver_location', 'update_driver_location');
//get list Driver for restaurant
function update_driver_location() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_driver_location START!", true));
	$data = $_POST;
	$lat_db = $_POST['Lat'];
	$lng_db = $_POST['Lng'];
	$user_id = $_POST['user_id'];	

	// update status  register_driver	
	$status_sql = "UPDATE driver SET latitude = ".$lat_db." ,longitude =".$lng_db." where Driver_id = ".$user_id."";
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $status_sql, true));
	$result_status = $wpdb->get_results($status_sql, ARRAY_A );
}

//Ajax functions
add_action('wp_ajax_chooseHeadDriver', 'chooseHeadDriver');

function chooseHeadDriver(){
	global $wpdb;
	$data = $_GET;
	
	$driver_id = $data['driver_id'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $data['driver_id'], true));
	//Check If Driver have supervisor bfr go to Choose_head page
	
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT supervisor  FROM driver WHERE Driver_id=%d", array($driver_id)
      )
	);
	if(empty($super_driver_id)){
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "No Super Driver", true),FILE_APPEND);
		get_template_part( 'driver/driver', 'choose_head');
		wp_die();
	}
	else{
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Super Driver Exit".$super_driver_id, true),FILE_APPEND);
			$super_driver_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT driver_name FROM driver WHERE Driver_id=%d", array($super_driver_id)
				)
			);
		set_query_var( 'superDriver_name', $super_driver_name );
		set_query_var( 'superDriver_id', $super_driver_id );
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Bfr Get Template".$super_driver_name, true),FILE_APPEND);
		get_template_part( 'driver/driver', 'exit_head');
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "After Get Template", true),FILE_APPEND);
		wp_die();
	}
}


// AJAX function
add_action('wp_ajax_update_head_driver', 'update_head_driver');
//get list Driver for restaurant
function update_head_driver() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_head_driver START!", true));

	$super_id = $_POST['driverSuperID'];
	$driver_id = $_POST['driverID'];

	// update supervisor driver	
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver SET supervisor = %d where Driver_id = %d",
          array($super_id,$driver_id)
        )
    );
}


// AJAX function
add_action('wp_ajax_get_delivery_fee', 'get_delivery_fee');
//get list Driver for restaurant
function get_delivery_fee($pid,$deliver_type) {
    global $wpdb;
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_head_driver START!", true));

	//$post_id = $_POST['postID'];
    $post_id = $pid;
    $buyer_delivery_type = $deliver_type;
	//$buyer_id = $_POST['buyerID'];
	
	//Get Buyer Latitude and Longitude from address of user
	$buyer_point = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT latitude,longitude FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array(get_current_user_id())
		)
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "point :".$buyer_point->latitude, true));
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "ID :".get_current_user_id(), true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "POST ID :".$post_id, true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Point Long :".$buyer_point->longitude, true),FILE_APPEND);
	
	//Check point of this address exit
    $deliver_fee_sql = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT distance FROM delivery_fee where wp_user_id = %d AND post_id = %d and latitude = %s and longitude = %s ", array(get_current_user_id(),$post_id,$buyer_point->latitude,$buyer_point->longitude)
        )
    );
	
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Price :".$deliver_fee_sql->price, true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "! Do Not Have Delivery_fee", true),FILE_APPEND);
		//Get Shop Latitude and Longitude from GD_place_detail
		$post_point = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_latitude,post_longitude FROM wp_geodir_gd_place_detail where post_id = %d ", array($post_id)
			)
		);
		//Calculate distance from google
		$check = $post_point->post_latitude.":".$post_point->post_longitude.":".$buyer_point->latitude.":".$buyer_point->longitude;
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "google distance :".$check, true),FILE_APPEND);
        $distance = longdo_distance($post_point->post_latitude,$post_point->post_longitude,$buyer_point->latitude,$buyer_point->longitude);
        //$distance = distance($post_point->post_latitude,$post_point->post_longitude,$buyer_point->latitude,$buyer_point->longitude,'K');
        $distance = round($distance,3);
		if($distance != "ขณะนี้ไม่สามารถคำนวนระยะทางของผู้ซื้อได้ชั่วคราว")
		{
			//Calculate delivery Fee
			//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Pass from Google".$distance, true),FILE_APPEND);
			$delivery_value = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT base,base_adjust,km_tier1,km_tier1_value,km_tier2,km_tier2_value,km_tier3_value FROM delivery_variable where post_id = %d and geodir_delivery_type =%d ", array($post_id,$buyer_delivery_type)
				)
            );
            if(empty($delivery_value)){
                return array(0,0);
            }
			$range_t1 = (($delivery_value->km_tier1)>=$distance)? $distance:$delivery_value->km_tier1;			
			//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T1:".$range_t1, true),FILE_APPEND);
            $range_t2 = (($delivery_value->km_tier1)<=$distance)?((($delivery_value->km_tier2)>=$distance-$range_t1)? $distance-($delivery_value->km_tier1):$delivery_value->km_tier2-$delivery_value->km_tier1):0;
            //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T2 :".$range_t2, true),FILE_APPEND);
            $range_t3 = ($delivery_value->km_tier2<=$distance)?($distance-$delivery_value->km_tier2):0;
			//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T3 :".$range_t3, true),FILE_APPEND);
			$deliver_fee = ($delivery_value->base + $delivery_value->base_adjust)+ ($range_t1*$delivery_value->km_tier1_value)+($range_t2*$delivery_value->km_tier2_value)+($range_t3*$delivery_value->km_tier3_value);
			$deliver_fee = round($deliver_fee,2);    
            
            if(empty($deliver_fee_sql->distance)){
                //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Price empty Ins new ", true),FILE_APPEND);
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO delivery_fee SET wp_user_id = %d, post_id = %d, latitude = %s, longitude = %s, price = %f, distance = %s",
                    array(get_current_user_id(),$post_id,$buyer_point->latitude,$buyer_point->longitude,$deliver_fee,$distance)
                    )
                );
            }            
            else{
               // file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Price 0 update new ", true),FILE_APPEND);

                $wpdb->query(
                    $wpdb->prepare(
                    "UPDATE delivery_fee SET price = %f,distance = %s WHERE wp_user_id = %d and post_id = %d and latitude = %s and longitude = %s",
                    array($deliver_fee,$distance,get_current_user_id(),$post_id,$buyer_point->latitude,$buyer_point->longitude)
                    )
                );
            }
			
			//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Delivery_fee :".$deliver_fee, true),FILE_APPEND);
			return array(number_format($deliver_fee,2,'.',''),$distance);
		}
		else{
			return array(0,0);
		}	
}

function longdo_distance($post_lat,$post_lng,$buyer_lat,$buyer_lng) {
	
	// google map geocode api url

    //$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$post_lat,$post_lng&destinations=$buyer_lat,$buyer_lng&key=AIzaSyC3mypqGAf0qnl5xGwsxwQinUIfeiTIYtM";
    
    $url ="https://mmmap15.longdo.com/mmroute/json/route/guide?flon=$post_lng&flat=$post_lat&tlon=$buyer_lng&tlat=$buyer_lat&key=1cc1885ba40b08c2ca002276b8d4bd92";

    
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "google URL :".$url, true),FILE_APPEND);
	

	//echo $url."<br>";
	// get the json response
    $resp_json = file_get_contents($url);

	// decode the json
	$resp = json_decode($resp_json, true);

    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Distance : ".$resp_json, true),FILE_APPEND);
	// response status will be 'OK', if able to geocode given address

    
	if($resp['status'] != "410")
	{
		// get the important data
		$distance = $resp['data'][0]['distance'];
		$int_distance = (float)$distance;
		$final_distance = $int_distance/1000;
		file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Distance data : ".$final_distance, true),FILE_APPEND);
        return $final_distance;
	}
	else{
		return "ขณะนี้ไม่สามารถคำนวนระยะทางของผู้ซื้อได้ชั่วคราว";
    }
    
	
}
//Ajax functions
add_action('wp_ajax_get_driver_super', 'get_driver_super');
function get_driver_super(){
	global $wpdb;
	$data = $_POST;
	
	$driver_id = $data['DriverId'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $driver_id, true));
	//Check If Driver have supervisor bfr go to Choose_head page
	
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT supervisor  FROM driver WHERE Driver_id=%d", array($driver_id)
      )
	);
	if(empty($super_driver_id)){
		wp_send_json_success("Driver คนนี้ไม่มีหัวหน้า");
	}
	else{
		file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Super Driver Exit".$super_driver_id, true),FILE_APPEND);
			$super_driver_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT driver_name FROM driver WHERE Driver_id=%d", array($super_driver_id)
				)
			);
		$super_drivers['id'] = $super_driver_id;
		$super_drivers['name'] = $super_driver_name;
		$return_arr[] = $super_drivers;
		wp_send_json_success($return_arr); 
	}
}

//AJAX FUNCTION
add_action('wp_ajax_listdriversuper', 'listdriversuper');
function listdriversuper(){
	$driver_assign_array = $_GET;
	set_query_var( 'super_id', $driver_assign_array['super_driver_id']); 
	set_query_var( 'driver_id', $driver_assign_array['driver_id']); 
   
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_assign_array['super_driver_id'], true));

  get_template_part( 'ajax-driver-head' );
  wp_die();
}

add_action( 'geodir_listing_after_pinpoint', 'open_time_listview', 10, 3 );

function open_time_listview($post_id, $post){
    $open = date(get_option('time_format'), strtotime($post->geodir_open_time));
    $close = date(get_option('time_format'), strtotime($post->geodir_close_time));
    echo '<div class="geodir_more_info" style="clear:both;">
        <span class="geodir-i-time">
        <i class="fa fa-clock-o"></i>: '.$open.' - '.$close.'</span></div>';
}

add_action( 'geodir_after_listing_post_excerpt', 'short_des_listview', 10, 3 );

function short_des_listview($post){
    echo '<div class="geodir-whoop-address">'.$post->post_content.'</div>';
}

//AJAX FUNCTION
add_action('wp_ajax_updateOnesignal', 'updateOnesignal');
add_action('wp_ajax_nopriv_updateOnesignal', 'updateOnesignal');

function updateOnesignal(){
	global $wpdb;
	$data = $_POST;
	$queery_sql = $data['doing']; 
	$device_id_bfr = $data['device_id'];
	$device_id = trim($device_id_bfr,'Optional(\\")');
	$user_id = get_current_user_id();
	$device_type = $data['deviceType'];
	
	file_put_contents( dirname(__FILE__).'/debug/testttt.log', var_export( $_POST, true));
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Start", true));
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Is enable is :".$device_id, true),FILE_APPEND);
	

	//Check data about this device in DB
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT user_id  FROM onesignal WHERE device_id=%s and device_type=%s", array($device_id,$device_type)
      )
	);
	if(empty($super_driver_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO onesignal SET
                             device_id = %s,device_type = %s,user_id =%d",
                             array($device_id,$device_type,$user_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
	}
	else {
        if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("UPDATE onesignal SET
                             user_id =%d WHERE device_id = %s",
                             array($user_id,$device_id)
                          );
			$wpdb->query($query);
			//file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal UPDATE".$device_id, true),FILE_APPEND);
        }		
    }
    if($queery_sql == "DELETE"){
		$wpdb->query($wpdb->prepare("DELETE FROM onesignal WHERE device_id = %s", $device_id));
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Delete".$device_id, true),FILE_APPEND);
	}
}

//Hock OneSignal with home page
add_action('geodir_sidebar_home_bottom_section', 'my_onesignal_check', 10);
add_action('wp_login', 'my_onesignal_check');
function my_onesignal_check(){
	$usrlogin = (is_user_logged_in())?1:0;
	$device = (wp_is_mobile())?"Mobile":"PC";
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Start Device is ".$device." and login is".$usrlogin, true));
?>
<script>
console.log("Before OneSignal Start:"); 







var usrlogincheck = <?php echo $usrlogin ?>;
var usrID = <?php echo get_current_user_id()?>;
var usrDevice = "<?php echo $device?>";

OneSignal.push(function() {
    console.log("OneSignal Start!!:");  

OneSignal.getUserId(function(deviceId) {
		console.log(" Not choose subscribe Check User ID:", deviceId);
		
    });

    
  OneSignal.isPushNotificationsEnabled(function(isEnabled) {
    <?php
    file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal push-notice  ".$usrDevice." and login is".$usrlogincheck, true),FILE_APPEND);
    ?>
    console.log("OneSignal Check isEnabled:"+isEnabled);

    if(!isEnabled){
        alert("หากต้องการใช้ promotion กรูณา subscirbe");
    }

    OneSignal.getUserId(function(deviceId) {
		console.log(" Promotion Check User ID:", deviceId);
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: 'action=updatePromotionCheck&doing=INSERT&device_id='+deviceId,
			success: function(arrayPHP) 
			{
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(textStatus);
			}
		});
    });
        
    if ((isEnabled)&&(usrlogincheck))
	{
      	OneSignal.getUserId(function(deviceId) {
			console.log("OneSignal User ID:", deviceId);
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=updateOnesignal&doing=INSERT&device_id='+deviceId+'&userid='+usrID+'&deviceType='+usrDevice,
				success: function(arrayPHP) 
				{

				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(textStatus);
				}
			});
		});
		console.log("Push notifications are enabled!");
	}	
    else
	{
		OneSignal.getUserId(function(deviceId) {
			console.log(" Else OneSignal User ID:", deviceId);
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=updateOnesignal&doing=DELETE&device_id='+deviceId+'&deviceType='+usrDevice,
				success: function(arrayPHP) 
				{

				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(textStatus);
				}
			});
		});
		console.log("Push notifications are Not enabled Yet!");
	}
	
  });
  // Occurs when the user's subscription changes to a new value.
  OneSignal.on('subscriptionChange', function (isSubscribed) {
    console.log("The user's subscription state is now:", isSubscribed);
	if(usrlogincheck){
		if(isSubscribed){
			OneSignal.getUserId(function(deviceId) {
				console.log("OneSignal User ID:", deviceId);
				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajaxurl,
					data: 'action=updateOnesignal&doing=INSERT&device_id='+deviceId+'&userid='+usrID+'&deviceType='+usrDevice,
					success: function(arrayPHP) 
					{
	
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(textStatus);
					}
				});
			});
			console.log("User login and click sub!");
		}
		else{
			OneSignal.getUserId(function(deviceId) {
			console.log("OneSignal User ID:", deviceId);
				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajaxurl,
					data: 'action=updateOnesignal&doing=DELETE&device_id='+deviceId+'&deviceType='+usrDevice,
					success: function(arrayPHP) 
					{
		
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(textStatus);
					}
				});
			});
			console.log("User Login and unsub!");
		}
	}
  });
});
</script>
<?php

}

//AJAX FUNCTION
add_action('wp_ajax_updateusrnoti', 'updateusrnoti');
add_action('wp_ajax_nopriv_updateusrnoti', 'updateusrnoti');
function updateusrnoti(){
    global $wpdb;
	$data = $_POST;
    $queery_sql = $data['doing']; 
    
	$device_id_bfr = $data['device_id'];
    $device_id = trim($device_id_bfr,'Optional(\\")');    
	$user_id = get_current_user_id();
    $device_type = $data['deviceType'];	

    //Promotion Log
    $super_device_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT user_id  FROM promotion_log WHERE device_id=%s", array($device_id)
        )
      );
      if(empty($super_device_id)){
          if($queery_sql == "INSERT"){
              $query = $wpdb->prepare("INSERT INTO promotion_log SET
                               device_id = %s,user_id =%d",
                               array($device_id,$user_id)
                            );
              $wpdb->query($query);
              file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
          }
      }	

    // Onesignal
	if($user_id != 0)
	{
		updateOneSignaliOS($queery_sql,$device_id,$user_id,$device_type);
		file_put_contents( dirname(__FILE__).'/debug/iostest.log', var_export( "Check user login".$device_id."user login is ".$user_id."Doing is".$queery_sql, true));
    }
    elseif($user_id == 0){
        updateOneSignaliOS("DELETE",$device_id,$user_id,$device_type);
    }
}

function updateOneSignaliOS($queery_sql,$device_id,$user_id,$device_type){
	global $wpdb;
	
	file_put_contents( dirname(__FILE__).'/debug/iostest.log', var_export( "Check user login".$device_id."user login is ".$user_id, true));
	//Check data about this device in DB
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT user_id  FROM onesignal WHERE device_id=%s and device_type=%s", array($device_id,$device_type)
      )
    );
    
    // Keep OneSignal
	if(empty($super_driver_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO onesignal SET
                             device_id = %s,device_type = %s,user_id =%d",
                             array($device_id,$device_type,$user_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
    }
    else {
        if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("UPDATE onesignal SET
                             user_id =%d WHERE device_id = %s",
                             array($user_id,$device_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
        }		
    }
    if($queery_sql == "DELETE"){
		$wpdb->query($wpdb->prepare("DELETE FROM onesignal WHERE device_id = %s", $device_id));
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Delete".$device_id, true),FILE_APPEND);
	}

    //Promotion Log
    $super_device_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT user_id  FROM promotion_log WHERE device_id=%s", array($device_id)
        )
    );
    if(empty($super_device_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO promotion_log SET
                             device_id = %s,user_id =%d",
                             array($device_id,$user_id)
                          );
			$wpdb->query($query);
			//file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
    } 
}


add_action('geodir_detail_before_main_content','restaurantName');
function restaurantName()
{
	global $post;
	$post_type = geodir_get_current_posttype();	
	$shop_id = geodir_get_post_meta(get_the_ID(),'geodir_shop_id',true);	
	$shop_title = get_the_title($shop_id);
	
	if ($post_type == "gd_product")
	{
        //echo "WPShout was here.".$post->ID;
        create_product_modal($post,$shop_id);
        echo '<h2><li><a href="'.get_permalink( $shop_id ).'">'.get_the_title( $shop_id ).'</a></li></h2>';
        
	}
}
add_action('whoop_detail_page_hide_map','addCartProductBtn',9);
function addCartProductBtn()
{
	global $post;
	$post_type = geodir_get_current_posttype();
	if ($post_type == "gd_product")
	{
		if($post->geodir_show_addcart){
            echo '<b>ราคา '.$post->geodir_price.'<sup>บาท</sup><b>';
			echo '<button type="button" style="color:white;" 
			data-toggle="modal" data-target="#product_'.$post->ID.'">+</button>';
		}
	}
}

add_action('geodir_detail_before_main_content','addOrderButton');
function addOrderButton()
{
global $post;
?>
	<!--  Bank   -->
	<div class="order-online-big-button">
	<?php 
	$check_button= $post->geodir_Button_enable;
	if($check_button){	
		echo "<span class='glf-button' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'>สั่งเลย</span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";
	}?>
	</div>
	
	<!-- Bank Add ORDER Big BUTTON   -->	
	<!-- Bank  -->
	<div class="order-online-small-button">
	<?php
	if($check_button){
	
	echo "<span class='glf-button glyphicon' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'></span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";
	
	}?>
	</div>
	<!-- Bank Add Shop Cart BUTTON on top  -->
	<?php
	if(wp_is_mobile())
	{
		if((strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false)||(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false))
		{
			?>	
			<script>
			function goBack() {
				window.history.back();
			}
			</script>
<?php
		}
	}
}
add_action('wp_ajax_getDriverCredit', 'getDriverCredit');
// For operater get Driver for adjust his balance
function getDriverCredit(){
    global $wpdb;
	$return_arr = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;

    $Driver_id = $data['DriverId'];
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));
	
	$sql = $wpdb->prepare(
         "SELECT * FROM driver WHERE driver_id = %d ",array($Driver_id)  
    );
	
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
    if(empty($result_driver)){
        wp_send_json_success("Cannot found");
        wp_die(); 
    }
    else{
        foreach ($result_driver as $Driver)
        {
            $drivers_array = array();
            $driver_name = $Driver->driver_name;
            $driver_id = $Driver->Driver_id;
            $driver_balance = $Driver->balance;
            $driver_credit = $Driver->add_on_credit;
                
            $drivers_array['name'] = $driver_name;
            $drivers_array['id'] = $driver_id;
            $drivers_array['balance'] = $driver_balance;
            $drivers_array['credit'] = $driver_credit;		
            
            $return_arr[] = $drivers_array;
        }
        wp_send_json_success($return_arr);
        wp_die();
    }	
    
}
//AJAX FUNCTION
add_action('wp_ajax_listdrivercredit', 'listdrivercredit');
function listdrivercredit(){
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Ajax listdrivercredit START", true));
    $driver_assign_array = $_GET;
    set_query_var('name', $driver_assign_array['driver_name']); 
    set_query_var('id', $driver_assign_array['driver_id']); 
    set_query_var('balance', $driver_assign_array['driver_balance']);
    set_query_var('credit', $driver_assign_array['driver_credit']); 
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Driver balance : ".$driver_assign_array['driver_balance'], true),FILE_APPEND);
    get_template_part( 'ajax-driver-credit' );
    wp_die();
}


//AJAX FUNCTION
add_action('wp_ajax_updatedrivercredit', 'updatedrivercredit');
function updatedrivercredit(){
    global $wpdb;
    // make sysdate on Bangkok
    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    $current_date = $dt->format("Y-m-d H:i:s");
	
	file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "updatedrivercredit START", true));
    $data = $_POST;

    $balance = $_POST['driverBalanceUpdate'];
	$credit = $_POST['driverCreditUpdate'];
    $driver_id = $_POST['driverID'];

    $driver_bfr =  $wpdb->get_row(
        $wpdb->prepare(
                "SELECT * FROM driver WHERE driver_id = %d ",array($driver_id)  
        )
    );

   $new_balance = $driver_bfr->balance + $balance;
   $new_credit = $driver_bfr->add_on_credit + $credit;

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE driver SET balance = %f , add_on_credit=%f where driver_id = %s ",
         array($new_balance,$new_credit,$driver_id)
        )
    );    

    if(!empty($balance)||$balance != 0)
    {
        $wpdb->query(
            $wpdb->prepare(
            "INSERT INTO driver_transaction_details SET driver_id = %d, credit = %f, balance = %f, transaction_type = %s, transaction_detail = %s, transaction_date = %s",
            array($driver_id,$balance,$new_balance,"CREDIT","Driver top up Balance",$current_date)
            )
        ); 
    }
    if(!empty($credit)||$credit != 0)
    {
        $wpdb->query(
            $wpdb->prepare(
            "INSERT INTO driver_transaction_details SET driver_id = %d, credit = %f, balance_add_on_credit = %f, transaction_type = %s, transaction_detail = %s, transaction_date = %s",
            array($driver_id,$credit,$new_credit,"ADD_ON_CREDIT","Driver receive free credit",$current_date)
            )
        ); 
    }


    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
    $return_result = "Update Driver Credit / Balance complete";
    wp_send_json_success($return_result);
}


add_action('wp_ajax_getUserCredit', 'getUserCredit');
// For operater get User for adjust his Credit
function getUserCredit(){
    global $wpdb;
	$return_arr = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;

    $User_id = $data['UserId'];
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));
    
    $user_name =  $wpdb->get_var(
        $wpdb->prepare(
                "SELECT user_nicename FROM wp_users WHERE ID = %d ",array($User_id)  
        )
    );

	
	$sql = $wpdb->prepare(
         "SELECT * FROM cash_back WHERE user_id = %d ",array($User_id)  
    );
	
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_user = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_user, true));
    if(empty($result_user)){
        wp_send_json_success("Cannot found");
        wp_die(); 
    }
    else{
        foreach ($result_user as $User)
        {
            $User_array = array();            
            $user_id = $User->user_id;
            $user_cb = $User->cash_back_percentage;
            $user_credit = $User->add_on_credit;                
            
            $User_array['id'] = $driver_id;
            $User_array['name'] = $user_name;
            $User_array['cb'] = $user_cb;
            $User_array['credit'] = $user_credit;		
            
            $return_arr[] = $User_array;
        }
        wp_send_json_success($return_arr);
        wp_die();
    }	
    
}
//AJAX FUNCTION
add_action('wp_ajax_listusercredit', 'listusercredit');
function listusercredit(){
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Ajax listusercredit START", true));
    $user_assign_array = $_GET;
    set_query_var('name', $user_assign_array['user_name']); 
    set_query_var('id', $user_assign_array['user_id']); 
    set_query_var('cash_back', $user_assign_array['user_cb']);
    set_query_var('credit', $user_assign_array['user_credit']); 
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Driver balance : ".$user_assign_array['user_cb'], true),FILE_APPEND);
    get_template_part( 'ajax-buyer-credit' );
    wp_die();
}


//AJAX FUNCTION
add_action('wp_ajax_updateusercredit', 'updateusercredit');
function updateusercredit(){
    global $wpdb;
    // make sysdate on Bangkok
    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    $current_date = $dt->format("Y-m-d H:i:s");
	
	file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "updateusercredit START", true));
    $data = $_POST;

    $newUserCB = $_POST['userCBUpdate'];
    $user_id = $_POST['userID'];

    if(empty($_POST['userCBUpdate'])){
        $return_result = "New Cash Back must be filled";
        wp_send_json_success($return_result);
    }
    else{
        $user_bfr =  $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT * FROM cash_back WHERE user_id = %d ",array($user_id) 
            )
        );

        $old_cb = $user_bfr->cash_back_percentage;
        // $new_credit = $driver_bfr->add_on_credit + $credit;
        if(empty($old_cb))
        {
            file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Insert New Cash back", true));
            $wpdb->query(
                $wpdb->prepare(
                "INSERT INTO cash_back SET user_id = %d, start_date = %s, balance = %f, cash_back_percentage = %s, add_on_credit = %s",
                array($user_id,$current_date,0,0,$newUserCB,0)
                )
            ); 
        }
        if(!empty($old_cb))
        {
            file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "update old cash back to new ".$newUserCB, true));
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE cash_back SET cash_back_percentage = %f where user_id = %d ",
                 array($newUserCB,$user_id)
                )
            );
        }
            
    
        /*
        if(!empty($credit)||$credit != 0)
        {
            $wpdb->query(
                $wpdb->prepare(
                "INSERT INTO driver_transaction_details SET driver_id = %d, credit = %f, balance_add_on_credit = %f, transaction_type = %s, transaction_detail = %s, transaction_date = %s",
                array($driver_id,$credit,$new_credit,"ADD_ON_CREDIT","Driver receive free credit",$current_date)
                )
            ); 
        }*/
    
    
        //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
        $return_result = "Update User Cash back / Credit complete";
        wp_send_json_success($return_result);
    }
}


function customer_rating(){
    global $wpdb, $current_user;

    if(is_page_template('my_order.php') || geodir_is_page( 'detail' )){


    if(isset($_POST['dl_id'])){
        // echo '<h1>driver_rating: '.$_POST['driver_rating'].'</h1>';
        // echo '<h1>dl_id: '.$_POST['dl_id'].'</h1>';

        $driver_order = $wpdb->get_row(
            $wpdb->prepare(
            "SELECT o.wp_user_id, dl.rating
            FROM orders as o
            INNER JOIN driver_order_log as dl on o.id = dl.driver_order_id
            WHERE dl.id = %d ", array($_POST['dl_id'])
        ));

        if($current_user->ID == $driver_order->wp_user_id && empty($driver_order->rating)){
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE driver_order_log SET rating = %d where id = %d ",
                 array($_POST['driver_rating'], $_POST['dl_id'])
                )
            );
        }
    }

    $driver = $wpdb->get_row(
        $wpdb->prepare(
        "SELECT d.driver_name, o.id as order_id, o.wp_user_id, dl.id as driver_log_id
        FROM driver as d
        INNER JOIN driver_order_log as dl on d.driver_id = dl.driver_id
        INNER JOIN orders as o on o.id = dl.driver_order_id
        WHERE o.wp_user_id = %d AND dl.rating IS NULL AND dl.status = 3 ", array($current_user->ID)
    ));


    if(!empty($driver)){
        $overall_star_offimg = get_option('geodir_reviewrating_overall_off_img');
        $star_width = get_option('geodir_reviewrating_overall_off_img_width');
    ?>
    <div class="modal show">
        <div class="modal-dialog">
            <form method="post">
            <!-- Modal content-->
            <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">กรุณาให้คะแนน</h4>
            </div>
            <div class="modal-body">
                <div id="rating_frm" style="margin-top:15px;">
                    <div class="gd-rating-box-in clearfix">
                        <div class="gd-rating-box-in-left">
                            <div class="gd-rate-area clearfix">
                                <style scoped="">ul.rate-area-list li.active, ul.rate-area-list li.active{background-color:#ed695d}</style>
                                พนักงานตามส่ง: <?php echo $driver->driver_name;?><br>
                                <span class="gd-ratehead">อัตราคะแนนโดยรวม</span>
                                <ul class="rate-area-list">
                                    <li data-star-rating="1" data-star-label="แย่มาก" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"/></a></li>
                                    <li data-star-rating="2" data-star-label="แย่" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"/></a></li>
                                    <li data-star-rating="3" data-star-label="ปานกลาง" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"/></a></li>
                                    <li data-star-rating="4" data-star-label="ดีมาก" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"/></a></li>
                                    <li data-star-rating="5" data-star-label="ยอดเยี่ยม" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;"/></a></li>
                                </ul>
                                <span class="gd-rank"></span>
                                <input type="hidden" name="driver_rating" value="0">
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="dl_id" value="<?php echo $driver->driver_log_id;?>">
                <button type="submit" class="btn btn-success btn-ok">ตกลง</button>
            </div>
            </div>
            </form>

        </div>
    </div>
    <div class="modal-backdrop fade in"></div>
    <?php
    }
    }else return;
}

add_action('wp_ajax_generateQRpayment', 'generateQRpayment');
// For operater get Driver for adjust his balance
function generateQRpayment(){
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;

    $Amount_topup = $data['amount'];
    $user_id = get_current_user_id();

    /*
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));
		$sql = $wpdb->prepare(
         "SELECT * FROM driver WHERE driver_id = %d ",array($Driver_id)  
    );
	$result_driver = $wpdb->get_results($sql);
    */
    $scb_token_obj = json_decode(SCBTokenGenerater());

    //file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( $scb_token_obj, true));
    file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( "Token is :".$scb_token_obj->data->accessToken, true),FILE_APPEND);

    $scb_qr_obj = json_decode(SCBQRGenerate($scb_token_obj->data->accessToken,$Amount_topup));
    file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( "QR Data is :".$scb_qr_obj->data->qrRawData, true),FILE_APPEND);
    wp_send_json_success($scb_qr_obj->data->qrRawData);
    
}

function SCBTokenGenerater()
{
    $fields = array(        
        'applicationKey' => "l7e0defea0cc1f4183ab3356ac23932a64",
        'applicationSecret' => "805247453ba54aa69e0409637e5dd653"   
    );
    $fields = json_encode($fields);
    //file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( $fields, true));

    // API key:l7e0defea0cc1f4183ab3356ac23932a64, API Secret:805247453ba54aa69e0409637e5dd653
	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.partners.scb/partners/sandbox/v1/oauth/token");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','resourceOwnerId:l7e0defea0cc1f4183ab3356ac23932a64','requestUId:tz-topup-12346'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		//file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( $response, true),FILE_APPEND);
        return $response;
}
function SCBQRGenerate($token,$amount)
{
    //ppId = Biller ID :632243887766375
    $authorization = "Bearer ".$token;
    $fields = array(        
        'qrType' => "PP",
        'ppType' => "BILLERID",
        "ppId"  => "632243887766375", 
	    "amount"  => $amount, 
        "ref1"  => "TZ01", 
        "ref2"  => "REFERENCE2",
        "ref3"  => "TZ01" 
    );
    $fields = json_encode($fields);
    //file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( $fields, true));

    // API key:l7e0defea0cc1f4183ab3356ac23932a64, API Secret:805247453ba54aa69e0409637e5dd653
	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.partners.scb/partners/sandbox/v1/payment/qrcode/create");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','resourceOwnerId:l7e0defea0cc1f4183ab3356ac23932a64','requestUId:tz-topup-12346','authorization:'.$authorization));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		file_put_contents( dirname(__FILE__).'/debug/SCBQR_start.log', var_export( $response, true));
        return $response;
}
/*
//AJAX FUNCTION
add_action('wp_ajax_listdrivercredit', 'listdrivercredit');
function listdrivercredit(){
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Ajax listdrivercredit START", true));
    $driver_assign_array = $_GET;
    set_query_var('name', $driver_assign_array['driver_name']); 
    set_query_var('id', $driver_assign_array['driver_id']); 
    set_query_var('balance', $driver_assign_array['driver_balance']);
    set_query_var('credit', $driver_assign_array['driver_credit']); 
    file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Driver balance : ".$driver_assign_array['driver_balance'], true),FILE_APPEND);
    get_template_part( 'ajax-driver-credit' );
    wp_die();
}
*/

function tamzang_modify_home_nav_menu_objects( $items, $args ) {
    global $wpdb, $current_user;
    if($args->theme_location != "main-nav")
        return $items;
    if ( !is_user_logged_in() )
        return $items;

    $user_link = bp_get_loggedin_user_link();
    $about_me = '<li class="menu-item "><a href="'.$user_link.'" class="">เกี่ยวกับฉัน</a></li>';

    $is_driver = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID FROM driver where Driver_id = %d ", array($current_user->ID)
        )
    );

    $driver = '';
    if(!empty($is_driver))
        $driver = '<li class="menu-item "><a href="'.home_url('/driver_orders/').'" class="">เปิดระบบตามส่ง</a></li>';
    
    return $items.$about_me.$driver;
    
}
add_filter( 'wp_nav_menu_items', 'tamzang_modify_home_nav_menu_objects', 120, 2 );//101


//AJAX FUNCTION
add_action('wp_ajax_refresh_Driver', 'refresh_Driver');
function refresh_Driver(){
    
    get_template_part( 'driver/driver', 'order_template' );
    wp_die();
}

function add_shop_link_at_product_form($type = '', $id = '', $class = ''){
    if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){//หน้าแก้ไขสินค้า
        $post = geodir_get_post_info($_REQUEST['pid']);
        $listing_type = $post->post_type;
        $geodir_shop_id = $post->geodir_shop_id;
    }else{//หน้าเพิ่มสินค้า
        $listing_type = sanitize_text_field($_REQUEST['listing_type']);
        $geodir_shop_id = $_REQUEST['shop_id'];
    }
    if($listing_type != "gd_product")
        return;
        
    //echo '<div id="shopname"><a href="'.get_permalink($geodir_shop_id).'">'.get_the_title($geodir_shop_id).'</a></div><br>';
    echo '<h2><li><a href="'.get_permalink( $geodir_shop_id ).'">'.get_the_title( $geodir_shop_id ).'</a></li></h2>';
}

add_action('geodir_wrapper_content_open', 'add_shop_link_at_product_form', 20, 3);

//AJAX FUNCTION
add_action('wp_ajax_refresh_seller_page', 'refresh_seller_page');
function refresh_seller_page(){    
    get_template_part( 'ajax-order-status' );
    wp_die();
}

//AJAX FUNCTION
add_action('wp_ajax_list_driver_marker', 'list_driver_marker');
function list_driver_marker(){
    global $wpdb;
    
    $driver_id = $_POST['driver_id'];
    $type_location = $_POST['typeLocation'];
    $is_ready = $_POST['ready'];
    file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "Is ready !!".$is_ready, true));

    if($is_ready == '1'){
        $isready_sql = " Where is_ready = 1";
    }



    file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "list_driver_marker !!".$isready_sql, true),FILE_APPEND);
    if(empty($driver_id))
    {
        //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "list_driver_marker !! Empty", true));
        $driverList  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM driver".$isready_sql,array()         
            )
        );
    }
    
    else
    {
        //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "list_driver_marker !!".$driver_id, true));
        $driverList  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM driver where Driver_id = %d  ".$isready_sql,array($driver_id)          
            )
        );
    }
    
    
    if(empty($type_location)||($type_location == "current")){
        //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "current !!".$driver_id, true));
        foreach ($driverList as $driver) {
            $assign_drivers = array();
            $assign_drivers['id'] = $driver->Driver_id;;
            $assign_drivers['name'] = $driver->driver_name;
            $assign_drivers['lat'] =$driver->current_latitude;
            $assign_drivers['lon'] = $driver->current_longitude;
            //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver->Driver_id, true),FILE_APPEND);	
            
            $return_arr[] = $assign_drivers;
        }
    }
    else{
        //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "PIN !!".$driver_id, true));
        foreach ($driverList as $driver) {
            $assign_drivers = array();
            $assign_drivers['id'] = $driver->Driver_id;;
            $assign_drivers['name'] = $driver->driver_name;
            $assign_drivers['lat'] =$driver->latitude;
            $assign_drivers['lon'] = $driver->longitude;
            //file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver->Driver_id, true),FILE_APPEND);	
            
            $return_arr[] = $assign_drivers;
        }
    }
    if(empty($driverList))
    {
        file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "list_driver_marker !! Empty", true));
        wp_send_json_success("empty");
    }
    else
    wp_send_json_success($return_arr);
}

function bangkok_current_time($output) {
    $tz = 'Asia/Bangkok';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
    return $dt->format("Y-m-d H:i:s");
}
add_filter('bp_core_current_time', 'bangkok_current_time');

// Add + price 0
add_action('wp_ajax_add_call_order_button', 'add_call_order_button');
function add_call_order_button(){ 
    global $wpdb; 
    file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "add_call_order_button !!", true));
    
    $shop_list  = $wpdb->get_results(
        $wpdb->prepare(
            "select * from wp_geodir_gd_place_detail
            where default_category not in (191,183,199,162,150,157,159,154,152,153,190,200,156,164)
            and ((geodir_tamzang_id is NULL) or (geodir_tamzang_id = ''))
            and post_id > 290000 and post_id <= 300000"
            ,array()          
        )
    );
    foreach ($shop_list as $shop) {

        $query = $wpdb->prepare("INSERT INTO wp_posts SET
                                post_author = %d,post_date = SYSDATE(),post_content ='(โปรดยืนยันราคารวมกับทางร้าน)',post_title =%s,post_status = 'publish',post_type='gd_product',Place_id=%s",
                                array(1,'#สั่งสินค้าด้วยตัวเอง กรุณาโทร '.$shop->geodir_contact,$shop->post_id)
                            );
        $wpdb->query($query);
        file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "Finish insert wp_Post !!".$shop->post_id, true),FILE_APPEND);
    }
    

    foreach($shop_list as $shop_array){
        $post_id  = $wpdb->get_row($wpdb->prepare("select * from wp_posts where Place_id = %s",array($shop_array->post_id)));
        $query = $wpdb->prepare("INSERT INTO wp_geodir_gd_product_detail SET
                                post_id = %d,post_title = %s,post_status = 'publish',default_category = '248',post_location_id = %d,marker_json =%s,post_locations = %s,
                                gd_productcategory = ',248,',post_latitude = %s,post_longitude = %s,geodir_price = 0,geodir_show_addcart = '1',geodir_shop_id =%s",
                                array($post_id->ID,$post_id->post_title,$shop_array->post_location_id,$shop_array->marker_json,$shop_array->post_locations,
                                $shop_array->post_latitude,$shop_array->post_longitude,$shop_array->post_id)
                            );
        $wpdb->query($query);
        file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "Finish insert wp_geodir_gd_product_detail !!".$post_id->ID, true),FILE_APPEND);
    }    
    
}


add_action('wp_ajax_shop_add_driver_to_group', 'shop_add_driver_to_group_callback');
function shop_add_driver_to_group_callback(){
    global $wpdb, $current_user;
    $data = $_POST;
    //file_put_contents( dirname(__FILE__).'/debug/driver_update_picture.log', var_export( $_FILES, true));
    //wp_send_json_error($_FILES['file']['name'].'--test--'.$_FILES['file']['tmp_name']);
    //check the nonce
    if ( check_ajax_referer( 'shop_add_driver_to_group_' . $data['pid'], 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    if(!geodir_listing_belong_to_current_user((int)$data['pid']))
        wp_send_json_error();
  
    try
    {
        $group_id = geodir_get_post_meta($data['pid'],'groupID',true);
        $d_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver_id FROM driver WHERE driver_id = %d AND groupID like '%".$group_id."%'", array($data['driver_id'])
            )
        );

        if(!empty($d_id))
            wp_send_json_error($d_id." ได้อยู่ในกลุ่มนี้แล้ว");

        $d_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver_id FROM request_group WHERE driver_id = %d AND group_id = %d ", array($data['driver_id'],$group_id)
            )
        );

        if(!empty($d_id))
            wp_send_json_error($d_id." อยู่ในรายชื่อที่รอการตอบรับ");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO request_group SET group_id = %d, Driver_id = %d ",
                array($group_id,$data['driver_id'])
            )
        );
        $driver = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT driver_id,driver_name FROM driver where driver_id = %d ", array($data['driver_id'])
            )
        );

        $return = array(
            'result' => $driver->driver_id." ".$driver->driver_name,
            'msg' => "เพิ่ม ".$driver->driver_name." ลงรายชื่อที่รอการตอบรับแล้ว"
        );
        wp_send_json_success($return);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

}

add_action('wp_ajax_driver_join_group', 'driver_join_group_callback');
function driver_join_group_callback(){
    global $wpdb, $current_user;
    $data = $_POST;

    if ( check_ajax_referer( 'driver_join_group_' . $current_user->ID, 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    try
    {
        $request_group = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM request_group where driver_id = %d AND group_id = %d ", array($current_user->ID, $data['gid'])
            )
        );

        if(empty($request_group))
            wp_send_json_error();

        $driver_group = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT groupID FROM driver WHERE driver_id = %d ", array($current_user->ID)
            )
        );

        $array_group = explode(",", $driver_group);

        if (in_array($data['gid'], $array_group))
            wp_send_json_error();


        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET groupID = CONCAT(groupID, '".$data['gid'].",') WHERE Driver_id = %d ",
                array($current_user->ID)
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM request_group where driver_id = %d AND group_id = %d ", array($current_user->ID, $data['gid'])
            )
        );

        $group_name = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT group_name FROM serviceGroup WHERE group_id = %d ", array($data['gid'])
            )
        );

        // $return = array(
        //     'gname' => $group_name,
        //     'gbutton' => '<button class="btn btn-danger" href="#"  data-toggle="modal" data-target="#confirm-group"
        //                 data-gid="'.$data['gid'].'" data-gname="'.$group_name.'" data-type="2"
        //                 data-nonce="'.wp_create_nonce( 'driver_quit_group_'.$current_user->ID).'">ออกจากกลุ่ม</button>'
        // );

        $return .= '<tr id="gid-'.$data['gid'].'">';
        $return .= '<td>';
        $return .= $group_name;
        $return .= '</td>';
        $return .= '<td style="text-align: center;">';
        $return .= '<button class="btn btn-info" href="#"  data-toggle="modal" data-target="#list-group"
                    data-gid="'.$data['gid'].'" data-gname="'.$group_name.'"
                    data-nonce="'.wp_create_nonce( 'driver_list_group_'.$current_user->ID).'">รายชื่อร้าน</button>';
        $return .= '</td>';
        $return .= '<td style="text-align: center;">';
        $return .= '<button class="btn btn-danger" href="#"  data-toggle="modal" data-target="#confirm-group"
                    data-gid="'.$data['gid'].'" data-gname="'.$group_name.'" data-type="2"
                    data-nonce="'.wp_create_nonce( 'driver_quit_group_'.$current_user->ID).'">ออกจากกลุ่ม</button>';
        $return .= '</td>';
        $return .= '</tr>';


        wp_send_json_success($return);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

}

add_action('wp_ajax_driver_decline_group', 'driver_decline_group_callback');
function driver_decline_group_callback(){
    global $wpdb, $current_user;
    $data = $_POST;

    if ( check_ajax_referer( 'driver_decline_group_' . $current_user->ID, 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    try
    {
        $request_group = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM request_group where driver_id = %d AND group_id = %d ", array($current_user->ID, $data['gid'])
            )
        );

        if(empty($request_group))
            wp_send_json_error();

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM request_group where driver_id = %d AND group_id = %d ", array($current_user->ID, $data['gid'])
            )
        );

        wp_send_json_success();
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

}

add_action('wp_ajax_driver_quit_group', 'driver_quit_group_callback');
function driver_quit_group_callback(){
    global $wpdb, $current_user;
    $data = $_POST;

    if ( check_ajax_referer( 'driver_quit_group_' . $current_user->ID, 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    try
    {
        $driver_group = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT groupID FROM driver WHERE driver_id = %d ", array($current_user->ID)
            )
        );

        $array_group = explode(",", $driver_group);

        if (!in_array($data['gid'], $array_group))
            wp_send_json_error();

        if (($key = array_search($data['gid'], $array_group)) !== false) {
            unset($array_group[$key]);
        }

        $new_group = implode(",",$array_group);

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET groupID = %s WHERE Driver_id = %d ",
                array($new_group, $current_user->ID)
            )
        );

        wp_send_json_success();
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

}

add_action('wp_ajax_driver_list_group', 'driver_list_group_callback');
function driver_list_group_callback(){
    global $wpdb, $current_user;
    $data = $_POST;

    if ( check_ajax_referer( 'driver_list_group_' . $current_user->ID, 'nonce', false ) == false ) {
        wp_send_json_error();
    }
  
    try
    {
        $driver_group = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT groupID FROM driver WHERE driver_id = %d ", array($current_user->ID)
            )
        );

        $array_group = explode(",", $driver_group);

        if (!in_array($data['gid'], $array_group))
            wp_send_json_error();


        $shop_list  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_title FROM wp_geodir_gd_place_detail where groupID = %d  ",array($data['gid'])          
            )
        );

        $return = "";
        foreach ($shop_list as $shop) {
            $return .= "<li>".$shop->post_title."</li>";
        }

        wp_send_json_success($return);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

}

//AJAX FUNCTION
add_action('wp_ajax_update_current_location', 'update_current_location');
function update_current_location(){
    global $wpdb, $current_user;
    $data = $_POST;
    
    
    
    if($data['type'] == "DRIVER"){
        //file_put_contents( dirname(__FILE__).'/debug/update_current_location.log', var_export( " Type Driver ID : ".$current_user->ID, true));
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET current_latitude = %s, current_longitude =%s WHERE Driver_id = %d ",
                array($data['lat'], $data['lng'],$current_user->ID)
            )
        );
    }
    else if($data['type'] == "USER"){
        file_put_contents( dirname(__FILE__).'/debug/update_current_location.log', var_export( "Type user ID : ".$current_user->ID, true));
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE wp_users SET current_latitude = %s, current_longitude =%s WHERE ID = %d ",
                array($data['lat'], $data['lng'],$current_user->ID)
            )
        );
    }
    
    
    
}

// AJAX function
add_action('wp_ajax_get_place_delivery_setup', 'get_place_delivery_setup');
//get list Driver for restaurant
function get_place_delivery_setup() {
    global $wpdb;
	$return_arr = array();
	$return_web = array();
	$restaurant_array = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;
	
    $Post_id = $data['PlaceId'];
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));
	
	$sql = $wpdb->prepare(
        "SELECT * FROM delivery_variable WHERE post_id = %d ",array($Post_id)  
    );	
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_list_delivery_variable = $wpdb->get_results($sql);	
	//$total_driver = $wpdb->num_rows;
    //file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
    if(!empty($result_list_delivery_variable)){
        foreach ($result_list_delivery_variable as $list)
        {
            $assign_drivers = array();
            $post_id = $list->post_id;
            $title = $list->title;
            $base = $list->base;
            $base_adjust = $list->base_adjust;
            $km_tier1 = $list->km_tier1;
            $km_tier1_value = $list->km_tier1_value;
            $km_tier2 = $list->km_tier2;
            $km_tier2_value = $list->km_tier2_value;
            $km_tier3 = $list->km_tier3;
            $km_tier3_value = $list->km_tier3_value;
            $km_tier4 = $list->km_tier4;
            $km_tier4_value = $list->km_tier4_value;
            $km_tier5 = $list->km_tier5;
            $km_tier5_value = $list->km_tier5_value;
            $geodir_delivery_type = $list->geodir_delivery_type;
            $assign_drivers['post_id'] = $post_id;
            $assign_drivers['title'] = $title;
            $assign_drivers['base'] = $base;
            $assign_drivers['base_adjust'] = $base_adjust;
            $assign_drivers['km_tier1'] = $km_tier1;
            $assign_drivers['km_tier1_value'] = $km_tier1_value;
            $assign_drivers['km_tier2'] = $km_tier2;
            $assign_drivers['km_tier2_value'] = $km_tier2_value;
            $assign_drivers['km_tier3'] = $km_tier3;
            $assign_drivers['km_tier3_value'] = $km_tier3_value;
            $assign_drivers['km_tier4'] = $km_tier4;
            $assign_drivers['km_tier4_value'] = $km_tier4_value;
            $assign_drivers['km_tier5'] = $km_tier5;
            $assign_drivers['km_tier5_value'] = $km_tier5_value;
            $assign_drivers['geodir_delivery_type'] = $geodir_delivery_type;
            file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( "Not Empty", true));	
            
            $return_arr[] = $assign_drivers;
        }
    }

    wp_send_json_success($return_arr);
    wp_die();
}

//AJAX FUNCTION
add_action('wp_ajax_list_delivery_setup', 'list_delivery_setup');
function list_delivery_setup(){


    $data = $_GET;
    /*
	set_query_var( 'total_order', $data['data']); 
	set_query_var( 'res_id', $data['res_id']);
    set_query_var( 'order_id', $data['order_id']); */  
    set_query_var('post_id',$data['post_id']);
    set_query_var('title',$data['title']);
    set_query_var('base',$data['base']);
    set_query_var('base_adjust',$data['base_adjust']);
    set_query_var('km_tier1',$data['km_tier1']);
    set_query_var('km_tier1_value',$data['km_tier1_value']);
    set_query_var('km_tier2',$data['km_tier2']);
    set_query_var('km_tier2_value',$data['km_tier2_value']);
    set_query_var('km_tier3',$data['km_tier3']);
    set_query_var('km_tier3_value',$data['km_tier3_value']);
    set_query_var('km_tier4',$data['km_tier4']);
    set_query_var('km_tier4_value',$data['km_tier4_value']);
    set_query_var('km_tier5',$data['km_tier5']);
    set_query_var('km_tier5_value',$data['km_tier5_value']);
    set_query_var('geodir_delivery_type',$data['geodir_delivery_type']); 
    
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $data['title'], true));

  get_template_part( 'ajax-restaurant-delivery' );
  wp_die();
}

//AJAX FUNCTION
add_action('wp_ajax_restaurant_delivery_setup', 'restaurant_delivery_setup');
function restaurant_delivery_setup(){
    global $wpdb;
	$current_date = tamzang_get_current_date();
	$return_arr = array();
	file_put_contents( dirname(__FILE__).'/debug/delivery_setup.log', var_export( "restaurant_delivery_setup", true));
    $data = $_POST;
    $check = $_POST['doing_1'];
    file_put_contents( dirname(__FILE__).'/debug/delivery_setup.log', var_export( "Check array".$check, true),FILE_APPEND);   
    /*
    $status = $_POST['status'];
	$order_id = $_POST['orderID'];
    $Tamzang_id = $_POST['TamzangId'];
    $emer_driver_id = $data['driverID'];
    $driver_id = $_POST['priority'];
    */

    for($i = 0; $i < 2; $i++){
        $doing = $_POST['doing_'.$i];             
        $post_id = $_POST['postid_'.$i];
        $title = $_POST['title_'.$i];
        $base = $_POST['base_'.$i];
        $base_adjust = $_POST['base_adjust_'.$i];
        $km_tier1 = $_POST['km_tier1_'.$i];
        $km_tier1_value = $_POST['km_tier1_value_'.$i];
        $km_tier2 = $_POST['km_tier2_'.$i];
        $km_tier2_value = $_POST['km_tier2_value_'.$i];
        $km_tier3 = $_POST['km_tier3_'.$i];
        $km_tier3_value = $_POST['km_tier3_value_'.$i];
        $km_tier4 = $_POST['km_tier4_'.$i];
        $km_tier4_value = $_POST['km_tier4_value_'.$i];
        $km_tier5 = $_POST['km_tier5_'.$i];
        $km_tier5_value = $_POST['km_tier5_value_'.$i];
        $geodir_delivery_type = $_POST['geodir_delivery_type_'.$i];

        if($doing == "UPDATE"){
           // file_put_contents( dirname(__FILE__).'/debug/delivery_setup.log', var_export( $doing , true),FILE_APPEND);
           $wpdb->query(
                $wpdb->prepare(
                    "UPDATE delivery_variable SET base = %f, base_adjust =%f ,km_tier1 =%f,km_tier1_value = %f, km_tier2 =%f ,km_tier2_value =%f,km_tier3 = %f, km_tier3_value =%f
                    ,km_tier4 =%f,km_tier4_value = %f, km_tier5 =%f ,km_tier5_value =%f
                    WHERE geodir_delivery_type = %d and post_id = %d ",
                    array($base, $base_adjust,$km_tier1,$km_tier1_value, $km_tier2,$km_tier2_value,$km_tier3, $km_tier3_value,$km_tier4,$km_tier4_value, $km_tier5,$km_tier5_value,$geodir_delivery_type,$post_id)
                )
            );
        }
        elseif($doing == "INSERT"){
          //  file_put_contents( dirname(__FILE__).'/debug/delivery_setup.log', var_export( $doing , true),FILE_APPEND);
          if(!empty($post_id)){
            $wpdb->query(
                $wpdb->prepare(
                 "INSERT INTO delivery_variable SET
                 post_id = %d,title = %s,base = %f, base_adjust =%f ,km_tier1 =%f,km_tier1_value = %f, km_tier2 =%f ,km_tier2_value =%f,km_tier3 = %f, km_tier3_value =%f
                    ,km_tier4 =%f,km_tier4_value = %f, km_tier5 =%f ,km_tier5_value =%f,geodir_delivery_type = %d",
                 array($post_id,$title,$base, $base_adjust,$km_tier1,$km_tier1_value, $km_tier2,$km_tier2_value,$km_tier3, $km_tier3_value,$km_tier4,$km_tier4_value, $km_tier5,$km_tier5_value,$geodir_delivery_type)
             )
            );
          }
            
        }
        
    }
    wp_send_json_success("SUCCESS");

    
}



//Ajax functions
add_action('wp_ajax_user_use_promotion', 'user_use_promotion_callback');
function user_use_promotion_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'user_use_promotion_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $check = check_promotion($data['promotion_input']);

    if($check['is_valid'])
    {
        $return = array(
            'name' => $check['name'],
            'constant' => $check['constant'],
            'percent' => $check['percent'],
        );

        wp_send_json_success($return);
    }
    else
        wp_send_json_error($check['msg']);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

function check_promotion($promotion_code){
    global $wpdb, $current_user;

    $tz_date = tamzang_get_current_date();
    $promotion = $wpdb->get_row(
        $wpdb->prepare(
            'SELECT * FROM promotion WHERE uses < max_uses 
            AND start_date < %s
            AND end_date > %s
            AND code = %s ', array($tz_date, $tz_date, $promotion_code)
        )
    );

    if(empty($promotion))
        return array('is_valid' => false, 'msg' => "code invalid");

    $device_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT device_id  FROM onesignal WHERE user_id=%d", array($current_user->ID)
        )
    );

    $check = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT promotion_id FROM promotion_log
            where instr(promotion_id,%s) >= 1 and (device_id = %s OR user_id = %d) ", 
            array($promotion->ID, $device_id, $current_user->ID)
        )
    );

    if(!empty($check))
        return array('is_valid' => false, 'msg' => "ใช้ code แล้ว");

    return array('is_valid' => true, 'name' => $promotion->name, 'constant' => $promotion->constant, 
    'percent' => $promotion->percent, 'promotion_id' => $promotion->ID, 'device_id' => $device_id);

}

function cal_shipping_price_with_promotion($promotion_id, $delivery_price){
    global $wpdb;

    $promotion = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM promotion where ID = %d ", array($promotion_id)
        )
    );

    $result = ( $delivery_price*(1-($promotion->percent/100))) - $promotion->constant;
    if($result <= 0)
        $result = 0;

    return $result;
}

//AJAX FUNCTION
add_action('wp_ajax_updatePromotionCheck', 'updatePromotionCheck');
add_action('wp_ajax_nopriv_updatePromotionCheck', 'updatePromotionCheck');

function updatePromotionCheck(){
	global $wpdb;
	$data = $_POST;
	$queery_sql = $data['doing']; 
	$device_id_bfr = $data['device_id'];
	$device_id = trim($device_id_bfr,'Optional(\\")');
	$user_id = get_current_user_id();	
	
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Start", true));
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Is enable is :".$device_id, true),FILE_APPEND);	

	//Check data about this device in DB
	$super_device_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT user_id  FROM promotion_log WHERE device_id=%s", array($device_id)
      )
	);
	if(empty($super_device_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO promotion_log SET
                             device_id = %s,user_id =%d",
                             array($device_id,$user_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
	}	
}

?>
