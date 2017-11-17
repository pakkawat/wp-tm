<?php
/**
 * Contains functions related to Custom Post Types plugin.
 *
 * @since 1.0.0
 * @package GeoDirectory_Custom_Post_Types
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) )
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );

/**
 * Plugin activation hook.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.0.0
 * @since 1.3.1 Changes for remove data on plugin uninstall feature.
 */
function geodir_custom_post_type_activation() {
    if (get_option('geodir_installed')) {
        add_option('geodir_custom_post_type_activation_redirect', 1);
    }
}

/**
 * Plugin deactivation hook.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.3.1
 */
function geodir_custom_post_type_deactivation() {
    // Plugin deactivation stuff here
}

/**
 * Check GeoDirectory plugin installed.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.0.0
 */
function geodir_custom_post_type_plugin_activated( $plugin ) {
    if ( !get_option( 'geodir_installed' ) )  {
        $file = plugin_basename( GEODIR_CP_PLUGIN_FILE );
        
        if ( $file == $plugin ) {
            $all_active_plugins = get_option( 'active_plugins', array() );
            
            if ( !empty( $all_active_plugins ) && is_array( $all_active_plugins ) ) {
                foreach ( $all_active_plugins as $key => $plugin ) {
                    if ( $plugin == $file ) {
                        unset( $all_active_plugins[$key] );
                    }
                }
            }
            update_option( 'active_plugins', $all_active_plugins );
        }
        
        wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodir_custom_posts' ) );
    }
}

/**
 * Plugin activation redirect.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.0.0
 */
function geodir_cp_activation_redirect() {
    if ( get_option( 'geodir_custom_post_type_activation_redirect', false ) ) {
        delete_option( 'geodir_custom_post_type_activation_redirect' );
        
        wp_redirect( admin_url( 'admin.php?page=geodirectory&tab=geodir_manage_custom_posts' ) );
    }
}

/**
 * Load geodirectory custom post types plugin textdomain.
 *
 * @package GeoDirectory_Custom_Post_Types
 * @since 1.0.0
 */
function geodir_load_translation_custom_posts() {
    $locale = apply_filters( 'plugin_locale', get_locale(), 'geodir_custom_posts' );
    load_textdomain( 'geodir_custom_posts', WP_LANG_DIR . '/geodir_custom_posts/geodir_custom_posts-' . $locale . '.mo' );
    load_plugin_textdomain( 'geodir_custom_posts', false, dirname( plugin_basename( GEODIR_CP_PLUGIN_FILE ) ) . '/geodir-cp-languages' );

    /**
     * Define language constants.
     */
    require_once( GEODIR_CP_PLUGIN_PATH . '/language.php' );
}

function geodir_cp_from_submit_handler(){

	global $plugin_prefix, $wpdb;
	if(isset($_REQUEST['geodir_save_post_type']))
	{

			$custom_post_type	= (trim($_REQUEST['geodir_custom_post_type']));
			$listing_slug 		= urldecode(sanitize_title($_REQUEST['geodir_listing_slug']));
			$listing_order 		= trim($_REQUEST['geodir_listing_order']);
			$categories 			= $_REQUEST['geodir_categories'];
			$tags 						= isset($_REQUEST['geodir_tags']) ? $_REQUEST['geodir_tags'] : '';
			$name 						= isset($_REQUEST['geodir_name']) ? $_REQUEST['geodir_name'] : ucfirst($custom_post_type);//htmlentities(trim($_REQUEST['geodir_name']));
			$singular_name 		= isset($_REQUEST['geodir_singular_name']) ?  (trim($_REQUEST['geodir_singular_name'])) : ucfirst($custom_post_type);
			$add_new 					= (trim($_REQUEST['geodir_add_new']));
			$add_new_item 		= (trim($_REQUEST['geodir_add_new_item']));
			$edit_item 				= (trim($_REQUEST['geodir_edit_item']));
			$new_item 				= (trim($_REQUEST['geodir_new_item']));
			$view_item 				= (trim($_REQUEST['geodir_view_item']));
			$search_item 			= (trim($_REQUEST['geodir_search_item']));
			$not_found 				= (trim($_REQUEST['geodir_not_found']));
			$not_found_trash 	= (trim($_REQUEST['geodir_not_found_trash']));
			$support 					= $_REQUEST['geodir_support'];
			$description 			= (trim($_REQUEST['geodir_description']));
			$menu_icon 				= (trim($_REQUEST['geodir_menu_icon']));
			$can_export 			= $_REQUEST['geodir_can_export'];
			$geodir_cp_meta_keyword = $_REQUEST['geodir_cp_meta_keyword'];
			$geodir_cp_meta_description = $_REQUEST['geodir_cp_meta_description'];
			$label_post_profile 	= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_profile']));
			$label_post_info 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_info']));
			$label_post_images 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_images']));
			$label_post_map 		= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_post_map']));
			$label_reviews 			= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_reviews']));
			$label_related_listing 	= stripslashes_deep(normalize_whitespace($_REQUEST['geodir_label_related_listing']));
			
			$cpt_image = isset($_FILES['geodir_cpt_img']) && !empty($_FILES['geodir_cpt_img']) ? $_FILES['geodir_cpt_img'] : NULL;
			$cpt_image_remove = isset($_POST['geodir_cpt_img_remove']) ? $_POST['geodir_cpt_img_remove'] : false;

			$link_business = isset( $_REQUEST['link_business'] ) && (int)$_REQUEST['link_business'] == 1 ? 1 : 0;
			$linkable_to = isset($_REQUEST['linkable_to']) ? stripslashes_deep($_REQUEST['linkable_to']) : '';
			$old_linkable_to = isset($_REQUEST['old_linkable_to']) ? stripslashes_deep($_REQUEST['old_linkable_to']) : '';
			$linkable_from = isset($_REQUEST['linkable_from']) ? stripslashes_deep($_REQUEST['linkable_from']) : '';
			
			if($can_export == 'true')
			{
				$can_export = true;
			}
			else
			{
				$can_export = false;
			}
			
			$custom_post_type	= geodir_clean( $custom_post_type ); // erase special characters from string
			
			
			if(isset($_REQUEST['posttype']) && $_REQUEST['posttype'] != '')
			{
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				$post_type_array = $geodir_post_types[$_REQUEST['posttype']];
			}
			
			
			if($custom_post_type != '' && $listing_slug != '')
			{
						
				if(empty($post_type_array))
				{
						$is_custom = 1; //check post type create by custom or any other add-once
						
						$posttypes_array = get_option( 'geodir_post_types' );
						
						$post_type = $custom_post_type;
						$custom_post_type = 'gd_'.$custom_post_type;
						
						if (array_key_exists($custom_post_type, $posttypes_array))
						{
							$error[] = __( 'Post Type already exists.', 'geodir_custom_posts' );
						}
						
						foreach($posttypes_array as $key=>$value)
						{
							if($value['has_archive'] == $listing_slug)
							{
								$error[] = __( 'Listing Slug already exists.', 'geodir_custom_posts' );
								break;
							}
						}
						
				}
				else
				{
						
						$post_type = preg_replace('/gd_/', '', $_REQUEST['posttype'], 1);	
						$custom_post_type = $_REQUEST['posttype'];
						
						$is_custom = isset($post_type_array['is_custom']) ? $post_type_array['is_custom'] : ''; /*check post type create by custom or any other add-once */
						
						//Edit case check duplicate listing slug
						if($post_type_array['has_archive'] != $listing_slug)
						{
							$posttypes_array = get_option( 'geodir_post_types' );
						
							foreach($posttypes_array as $key=>$value)
							{
								if($value['has_archive'] == $listing_slug)
								{
									$error[] = __( 'Listing Slug already exists.', 'geodir_custom_posts' );
									break;
								}
							}
						}
				
				}
				
				
				if(empty($error))
				{		
						/**
						 * Include any functions needed for upgrades.
						 *
						 * @since 1.1.7
						 */
						require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
						
						if(!empty($post_type_array))
						{
						
							if(!$categories)
							{
								
								$geodir_taxonomies = get_option('geodir_taxonomies');
								
								if(array_key_exists($custom_post_type.'category', $geodir_taxonomies))
								{
									unset($geodir_taxonomies[$custom_post_type.'category']);
									
									update_option( 'geodir_taxonomies', $geodir_taxonomies );
											
								}
							
							}

							if(!$tags)
							{
								
								$geodir_taxonomies = get_option('geodir_taxonomies');
								
								if (array_key_exists($custom_post_type.'_tags', $geodir_taxonomies))
								{
									unset($geodir_taxonomies[$custom_post_type.'_tags']);
									
									update_option( 'geodir_taxonomies', $geodir_taxonomies );
											
								}
								
							}
						
						}
						
						
						$taxonomies = array();
						if ( $categories ) {
							$taxonomies[] = $custom_post_type.'category';
							$categories =  $custom_post_type.'category';
						}
						
						if ( $tags ) {
							$taxonomies[] = $custom_post_type.'_tags';
							$tags =  $custom_post_type.'_tags';
						}
						
						if ( $categories ) {
							$gd_placecategory = array();
							$gd_placecategory['object_type']= $custom_post_type;
							$gd_placecategory['listing_slug']= $listing_slug;
							
							$gd_placecategory['args'] = array (
								'public' 		=> true,
								'hierarchical'  => true,
								'rewrite' 		=> array ( 'slug' => $listing_slug, 'with_front' => false, 'hierarchical' => true ),
								'query_var'		=> true,
								'labels' 		=> array (
									'name'          => __( $singular_name.' Categories', 'geodir_custom_posts' ),
									'singular_name' => __( $singular_name.' Category', 'geodir_custom_posts' ),
									'search_items'  => __( 'Search '.$singular_name.' Categories', 'geodir_custom_posts' ),
									'popular_items' => __( 'Popular '.$singular_name.' Categories', 'geodir_custom_posts' ),
									'all_items'     => __( 'All '.$singular_name.' Categories', 'geodir_custom_posts' ),
									'edit_item'     => __( 'Edit '.$singular_name.' Category', 'geodir_custom_posts' ),
									'update_item'   => __( 'Update '.$singular_name.' Category', 'geodir_custom_posts' ),
									'add_new_item'  => __( 'Add New '.$singular_name.' Category', 'geodir_custom_posts' ),
									'new_item_name' => __( 'New '.$singular_name.' Category', 'geodir_custom_posts' ),
									'add_or_remove_items' => __( 'Add or remove '.$singular_name.' categories', 'geodir_custom_posts' ),
								),
								'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['cats'] ) ? 0 : 1 ),
							);
							$geodir_taxonomies = get_option( 'geodir_taxonomies' );
							$geodir_taxonomies[$categories] = $gd_placecategory;
							update_option( 'geodir_taxonomies', $geodir_taxonomies );
						}
						
						if ( $tags ) {
							$gd_placetags = array();
							$gd_placetags['object_type']	= $custom_post_type;
							$gd_placetags['listing_slug']	= $listing_slug.'/tags';
							
							$gd_placetags['args'] = array (
								'public' 			=> true,
								'hierarchical' 		=> false,
								'rewrite' 			=> array ( 
															'slug' => $listing_slug.'/tags', 
															'with_front' => false, 'hierarchical' => false 
														),
								'query_var' 		=> true,
								'labels' 			=> array (
									'name'          => __( $singular_name.' Tags', 'geodir_custom_posts' ),
									'singular_name' => __( $singular_name.' Tag', 'geodir_custom_posts' ),
									'search_items'  => __( 'Search '.$singular_name.' Tags', 'geodir_custom_posts' ),
									'popular_items' => __( 'Popular '.$singular_name.' Tags', 'geodir_custom_posts' ),
									'all_items'     => __( 'All '.$singular_name.' Tags', 'geodir_custom_posts' ),
									'edit_item'     => __( 'Edit '.$singular_name.' Tag', 'geodir_custom_posts' ),
									'update_item'   => __( 'Update '.$singular_name.' Tag', 'geodir_custom_posts' ),
									'add_new_item'  => __( 'Add New '.$singular_name.' Tag', 'geodir_custom_posts' ),
									'new_item_name' => __( 'New '.$singular_name.' Tag Name', 'geodir_custom_posts' ),
									'add_or_remove_items' => __( 'Add or remove '.$singular_name.' tags', 'geodir_custom_posts' ),
									'choose_from_most_used' => __( 'Choose from the most used '.$singular_name.' tags', 'geodir_custom_posts' ),
									'separate_items_with_commas' => __( 'Separate '.$singular_name.' tags with commas', 'geodir_custom_posts' ),
								),
								'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['tags'] ) ? 0 : 1 ),
							);
							
							$geodir_taxonomies = get_option( 'geodir_taxonomies' );
							$geodir_taxonomies[$tags] = $gd_placetags;
							update_option( 'geodir_taxonomies', $geodir_taxonomies );
						}
						
						
						if(empty($name)) $name = __( ucfirst($post_type), 'geodir_custom_posts' );
						if(empty($singular_name)) $singular_name = __( ucfirst($post_type), 'geodir_custom_posts' );
						if(empty($add_new)) $add_new = __( 'Add New '.ucfirst($singular_name), 'geodir_custom_posts' );
						if(empty($add_new_item)) $add_new_item = __( 'Add New '.ucfirst($singular_name), 'geodir_custom_posts' );
						if(empty($edit_item)) $edit_item = __( 'Edit '.ucfirst($singular_name), 'geodir_custom_posts' );
						if(empty($new_item)) $new_item = __( 'New '.ucfirst($singular_name), 'geodir_custom_posts' );
						if(empty($view_item)) $view_item = __( 'View '.ucfirst($singular_name), 'geodir_custom_posts' );
						if(empty($search_item)) $search_item = __( 'Search '.ucfirst($name), 'geodir_custom_posts' );
						if(empty($not_found)) $not_found = __( 'No '.ucfirst($name).' Found', 'geodir_custom_posts' );
						if(empty($not_found_trash)) $not_found_trash = __( 'No '.ucfirst($name).' Found In Trash', 'geodir_custom_posts' );
						if(empty($menu_icon)) $menu_icon = geodir_plugin_url() . '/geodirectory-assets/images/favicon.ico';
						
						$labels = array (
							'name'         		=> 	ucfirst($name),
							'singular_name' 	=> 	ucfirst($singular_name),
							'add_new'       	=>	ucfirst($add_new),
							'add_new_item'  	=> 	ucfirst($add_new_item),
							'edit_item'     	=> 	ucfirst($edit_item),
							'new_item'      	=> 	ucfirst($new_item),
							'view_item'     	=> 	ucfirst($view_item),
							'search_items'  	=> 	ucfirst($search_item),
							'not_found'     	=> 	ucfirst($not_found),
							'not_found_in_trash' => ucfirst($not_found_trash),
							'label_post_profile' 	=> $label_post_profile,
							'label_post_info' 		=> $label_post_info,
							'label_post_images' 	=> $label_post_images,
							'label_post_map' 		=> $label_post_map,
							'label_reviews'			=> $label_reviews,
							'label_related_listing' => $label_related_listing
						);

						$place_default = array (
											'labels' 			=> $labels,
											'can_export' 		=> $can_export,
											'capability_type'	=> 'post',
											'description'		=> $description,
											'has_archive' 		=> $listing_slug,
											'hierarchical' 		=> false,
											'map_meta_cap' 		=> true,
											'menu_icon' 		=> apply_filters('geodir_custom_post_type_default_menu_icon', $menu_icon),
											'public'			=> true,
											'query_var' 		=> true,
											'rewrite' 			=> array (
																		'slug' => $listing_slug,
																		'with_front' => false, 
																		'hierarchical' => true,
																		'feeds' => true
																	),
											'supports' 			=> $support,
											'taxonomies' 		=> $taxonomies,
											'is_custom' 		=> $is_custom,
											'listing_order'     => $listing_order,
											'seo'         		=> array (
																		'meta_keyword'=> $geodir_cp_meta_keyword,
																		'meta_description'=> $geodir_cp_meta_description
																	),
											'show_in_nav_menus' => ( !empty( $_REQUEST['geodir_disable_nav_menus']['posts'] ) ? 0 : 1 ),
											'link_business' 	=> $link_business,
											'linkable_to'       => $linkable_to,
											'linkable_from'     => $linkable_from
										);
						
						update_option( 'temp_post_type' , $place_default ) ;
						$geodir_post_types = get_option( 'geodir_post_types' );
						$geodir_post_types[$custom_post_type] = $place_default;
						update_option( 'geodir_post_types', $geodir_post_types );

					    $geodir_linked_post_types = get_option('geodir_linked_post_types');

						if ($linkable_to == '') {
							if(is_array($geodir_linked_post_types)) {
								if (isset($geodir_linked_post_types[$old_linkable_to])) {
									unset($geodir_linked_post_types[$old_linkable_to]);
									update_option('geodir_linked_post_types', $geodir_linked_post_types);
								}
							}


						} elseif (!empty($linkable_to)) {

							if(is_array($geodir_linked_post_types))
								$geodir_linked_post_types[$linkable_to] = $custom_post_type;
							else
								$geodir_linked_post_types = array($linkable_to => $custom_post_type);

							update_option('geodir_linked_post_types', $geodir_linked_post_types);
						}

						
						//ADD NEW CUSTOM POST TYPE IN SHOW POST TYPE NAVIGATIONS 
						
						if(!isset($_REQUEST['posttype'])){
					
							$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard','geodir_favorite_link_user_dashboard');
							
							foreach($get_posttype_settings_options as $get_posttype_settings_options_obj){
								$geodir_post_types_listing = get_option( $get_posttype_settings_options_obj);
								
								if(empty($geodir_post_types_listing) || (is_array($geodir_post_types_listing) && !in_array($custom_post_type, $geodir_post_types_listing))){
								
								$geodir_post_types_listing[] = $custom_post_type;
								update_option( $get_posttype_settings_options_obj, $geodir_post_types_listing );
								
								}
							}
					}
						
						
						// Save post types in default table
						if(empty($post_type_array))
						{
							
							$geodir_custom_post_types = get_option('geodir_custom_post_types');
							
							if(!$geodir_custom_post_types)
								$geodir_custom_post_types = array();
							
							if (!array_key_exists($custom_post_type, $geodir_custom_post_types))
							{
								$geodir_custom_post_types[$custom_post_type] = $custom_post_type;
								
								update_option( 'geodir_custom_post_types', $geodir_custom_post_types );
							}
								
						}
						
						// Table for storing custom post type attribute - these are user defined
						
						$collate = '';
						if($wpdb->has_cap( 'collation' )) {
							if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
							if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
						}
						/*
						 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
						 * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
						 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
						 */
						$max_index_length = 191;
						
						$newtable_name = $plugin_prefix.$custom_post_type.'_detail';
						
						$newposttype_detail = "CREATE TABLE ".$newtable_name." (
										post_id int(11) NOT NULL,
										post_title text NULL DEFAULT NULL,
										post_status varchar(20) NULL DEFAULT NULL,
										default_category INT NULL DEFAULT NULL,
										post_tags text NULL DEFAULT NULL,
										post_location_id int(11) NOT NULL,
										geodir_link_business varchar(10) NULL DEFAULT NULL,
										marker_json text NULL DEFAULT NULL,
										claimed ENUM( '1', '0' ) NULL DEFAULT '0',
										businesses ENUM( '1', '0' ) NULL DEFAULT '0',
										is_featured ENUM( '1', '0' ) NULL DEFAULT '0',
										featured_image VARCHAR( 254 ) NULL DEFAULT NULL,
										paid_amount DOUBLE NOT NULL DEFAULT '0',
										package_id INT(11) NOT NULL DEFAULT '0',
										alive_days INT(11) NOT NULL DEFAULT '0',
										paymentmethod varchar(30) NULL DEFAULT NULL,
										expire_date VARCHAR( 100 ) NULL DEFAULT NULL,
										submit_time varchar(25) NULL DEFAULT NULL,
										submit_ip varchar(15) NULL DEFAULT NULL,
										overall_rating float(11) DEFAULT NULL,
										rating_count INT(11) DEFAULT '0',
										post_locations VARCHAR( 254 ) NULL DEFAULT NULL,
										PRIMARY KEY (post_id),
										KEY post_locations (post_locations($max_index_length)),
										KEY is_featured (is_featured)
										) $collate ";
										
						dbDelta($newposttype_detail);
						
						do_action('geodir_after_custom_detail_table_create', $custom_post_type, $newtable_name);
						
						$package_info = array() ;
						/*$package_info = apply_filters('geodir_post_package_info' , $package_info , '', $custom_post_type);
						$package_id = $package_info->pid;*/
						
						$package_info = geodir_post_package_info($package_info , '', $custom_post_type);
						$package_id = $package_info->pid;
						
						if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE FIND_IN_SET(%s, packages)",array($package_id))))
						{
							
							$table = $plugin_prefix.$custom_post_type.'_detail';
							
							$wpdb->query($wpdb->prepare("UPDATE ".$table." SET package_id=%d",array($package_id)));
							
							$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_CUSTOM_FIELDS_TABLE." SET packages=%s WHERE post_type=%s",array($package_id,$custom_post_type)));
							
						}
						
						
						geodir_cp_create_default_fields($custom_post_type, $package_id);
						
						$msg = 	__( 'Post type created successfully.', 'geodir_custom_posts' );
						
						if(isset($_REQUEST['posttype']) && $_REQUEST['posttype'] != ''){
							$msg = 	__( 'Post type updated successfully.', 'geodir_custom_posts' );
						}
						
						/// call the geodirectory core function to register all posttypes again.
						geodir_register_post_types();
						// call the geodirectory core function to register all taxonomies again.
						geodir_register_taxonomies();
						
						
						geodir_flush_rewrite_rules();
						
						geodir_set_user_defined_order() ;
						
						// Save CPT image
						$uploads = wp_upload_dir();
						 
						// if remove is set then remove the file
						if ($cpt_image_remove) {
							if (get_option('geodir_cpt_img_' . $custom_post_type)) {
								$image_name_arr = explode('/', get_option('geodir_cpt_img_' . $custom_post_type));
								$img_path = $uploads['path'] . '/' . end($image_name_arr);
								if (file_exists($img_path))
									unlink($img_path);
							}
			
							update_option('geodir_cpt_img_' . $custom_post_type, '');
						}
						
						if ($cpt_image) {
							$tmp_name = isset($cpt_image['tmp_name']) ? $cpt_image['tmp_name'] : '';
							$filename = isset($cpt_image['name']) ? $cpt_image['name'] : '';
							$ext = pathinfo($filename, PATHINFO_EXTENSION);
							$uplaods = array();
							$uplaods[] = $tmp_name;
							
							$allowed_file_types = array('jpg' => 'image/jpg','jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
    						$upload_overrides = array('test_form' => false, 'mimes' => $allowed_file_types);
                			$cpt_img = wp_handle_upload($cpt_image, $upload_overrides);
							
							if (!empty($cpt_img) && !empty($cpt_img['url'])) {
								if (get_option('geodir_cpt_img_' . $custom_post_type)) {
									$image_name_arr = explode('/', get_option('geodir_cpt_img_' . $custom_post_type));
									$img_path = $uploads['path'] . '/' . end($image_name_arr);
									
									if (file_exists($img_path))
										unlink($img_path);
								}
								
								// set width and height
								$w = apply_filters('geodir_cpt_img_width', 300); // get large size width
								$h = apply_filters('geodir_cpt_img_height', 300); // get large size width
								
								// get the uploaded image
								$cpt_img_file = wp_get_image_editor( $cpt_img['file'] );
								
								// if no error
								if ( ! is_wp_error( $cpt_img_file ) ) {
									// get image width and height
									$size = getimagesize( $cpt_img['file'] ); // $size[0] = width, $size[1] = height
									
									if ( $size[0] > $w || $size[1] > $h ){ // if the width or height is larger than the large-size
										$cpt_img_file->resize( $w, $h, false ); // resize the image
										$final_image = $cpt_img_file->save( $cpt_img['file'] ); // save the resized image
									}
								}
								
								update_option('geodir_cpt_img_' . $custom_post_type, $cpt_img['url']);
							}
						}

						$msg = urlencode($msg);
						
						$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&cp_success='.$msg;

						wp_redirect( $redirect_to );
						gd_die();
						
				}
				else
				{
					
					global $cp_error;
					foreach($error as $err)
					{
						$cp_error .= '<div id="message" style="color:#FF0000;" class="updated fade"><p><strong>' . $err . '</strong></p></div>';
					}
					
				}
			
		}
	
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'cp_delete')
	{
		if($_REQUEST['posttype'])
		{
				
				$geodir_taxonomies = get_option('geodir_taxonomies');
				
				if (array_key_exists($_REQUEST['posttype'].'category', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$_REQUEST['posttype'].'category']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				
				if (array_key_exists($_REQUEST['posttype'].'_tags', $geodir_taxonomies))
				{
					unset($geodir_taxonomies[$_REQUEST['posttype'].'_tags']);
					update_option( 'geodir_taxonomies', $geodir_taxonomies );
				}
				
				
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				if (array_key_exists($_REQUEST['posttype'], $geodir_post_types))
				{
					unset($geodir_post_types[$_REQUEST['posttype']]);
					update_option( 'geodir_post_types', $geodir_post_types );
				}
				
				//UPDATE SHOW POST TYPES NAVIGATION OPTIONS 
					
				$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard','geodir_favorite_link_user_dashboard');
									
				foreach($get_posttype_settings_options as $get_posttype_settings_options_obj)
				{
					$geodir_post_types_listing = get_option( $get_posttype_settings_options_obj);
					
					if (in_array($_REQUEST['posttype'], $geodir_post_types_listing))
					{
						$geodir_update_post_type_nav = array_diff($geodir_post_types_listing, array($_REQUEST['posttype']));
						update_option( $get_posttype_settings_options_obj, $geodir_update_post_type_nav );	
					}
				}
				
				//END CODE OPTIONS				
				
				geodir_flush_rewrite_rules() ;
				
				$msg = 	__( 'Post type deleted successfully.', 'geodir_custom_posts' );
		
				$msg = urlencode($msg);
				
				$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&confirm=true&geodir_customposttype='.$_REQUEST['posttype'].'&cp_success='.$msg;
				
				wp_redirect( $redirect_to );
				
				gd_die();
					
		}
	}
	
}

function geodir_set_user_defined_order()
{
	$geodir_post_types = get_option( 'geodir_post_types' );
	$geodir_post_types_in_new_order = array() ;
	$geodir_temp_post_types = array() ;
	$geodir_temp_post_type_keys = array() ;
	foreach($geodir_post_types as $key =>$value)
	{
		if(!empty($geodir_temp_post_types ) )
		{
			if(!isset($value['listing_order']) || $value['listing_order']==0 || array_key_exists($value['listing_order'], $geodir_temp_post_types ))
				$value['listing_order'] = max(array_keys($geodir_temp_post_types))+1 ;
		}
		else
		{
			if(!isset($value['listing_order']) || $value['listing_order']==0 )
				$value['listing_order'] =1 ;
		}
		$geodir_temp_post_types[$value['listing_order']] = $value;
		$geodir_temp_post_type_keys[$value['listing_order']] = $key ;
	}
	
	ksort($geodir_temp_post_types) ;
	foreach($geodir_temp_post_types as $key => $value)
	{
		$geodir_post_types_in_new_order[$geodir_temp_post_type_keys[$key]] =  $value ;
	}
	
	update_option( 'geodir_post_types', $geodir_post_types_in_new_order );
	
}

function geodir_cp_create_default_fields($custom_post_type, $package_id='')
{

	$fields = geodir_default_custom_fields($custom_post_type,$package_id);
	
	$fields = apply_filters('geodir_add_custom_field',$fields,$custom_post_type,$package_id);
	
	foreach($fields as $field_index => $field )
	{ 
		geodir_custom_field_save( $field ); 
	}
}


function geodir_cp_ajax_url(){
	return admin_url('admin-ajax.php?action=geodir_cp_ajax_action');
}

function geodir_custom_post_type_ajax($post_type = ''){
	
	global $wpdb, $plugin_prefix;
	
	if($post_type == '')
		$post_type = $_REQUEST['geodir_deleteposttype'];
	
	$args = array( 'post_type' => $post_type, 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
	
	
	/* ------- START DELETE ALL TERMS ------- */
	
	$terms = $wpdb->get_results("SELECT term_id, taxonomy FROM ".$wpdb->prefix."term_taxonomy WHERE taxonomy IN ('".$post_type."category', '".$post_type."_tags')");
	
	if(!empty($terms)){
		foreach( $terms as $term ){
			wp_delete_term($term->term_id,$term->taxonomy);
		}
	}
	
	$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '%tax_meta_".$post_type."_%'");
	
	
	/* ------- END DELETE ALL TERMS ------- */
	
	$geodir_all_posts = get_posts( $args );
	
	if(!empty($geodir_all_posts)){
	
		foreach($geodir_all_posts as $posts)
		{
			wp_delete_post($posts->ID);
		}
	}
	
	do_action('geodir_after_post_type_deleted'  , $post_type);

	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s",array($post_type)));
	
	$wpdb->query($wpdb->prepare("DELETE FROM ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." WHERE post_type=%s",array($post_type)));
	
	$detail_table =  $plugin_prefix . $post_type . '_detail';
	
	$wpdb->query("DROP TABLE IF EXISTS ".$detail_table);
	
	$msg = 	__( 'Post type related data deleted successfully.', 'geodir_custom_posts' );
	
	$msg = urlencode($msg);
	
	if(isset($_REQUEST['geodir_deleteposttype']) && $_REQUEST['geodir_deleteposttype']){
	
		$redirect_to = admin_url().'admin.php?page=geodirectory&tab=geodir_manage_custom_posts&cp_success='.$msg;
		wp_redirect( $redirect_to );
	
		gd_die();
	}
	
}


function geodir_payment_remove_unnecessary_fields(){
	global $wpdb, $plugin_prefix;
	
	if(!get_option('geodir_payment_remove_unnecessary_fields')){
		
		$all_postypes = geodir_get_posttypes();
		
		foreach($all_postypes as $post_type){
			
			$table_name = $plugin_prefix.$post_type.'_detail';
			
			if($wpdb->get_var("SHOW COLUMNS FROM ".$table_name." WHERE field = 'categories'"))
				$wpdb->query("ALTER TABLE `".$table_name."` DROP `categories`");
			
		}
		
		update_option('geodir_payment_remove_unnecessary_fields', '1');
		
	}
}

function geodir_display_cp_messages() {
    if (isset($_REQUEST['cp_success']) && $_REQUEST['cp_success'] != '') {
        echo '<div id="message" class="updated fade"><p><strong>' . sanitize_text_field($_REQUEST['cp_success']) . '</strong></p></div>';
    }
}

/**
 * Check physical location disabled.
 *
 * @since 1.1.6
 *
 * @param string $post_type WP post type or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if physical location disabled, otherwise false.
 */ 
function geodir_cpt_no_location( $post_type = '', $taxonomy = false ) {
	$post_types = get_option( 'geodir_cpt_disable_location' );
	
	if ( $taxonomy && !empty( $post_types ) ) {
		$posttypes = array();
		
		foreach ( $post_types as $posttype ) {
			$posttypes[] = $posttype . 'category';
			$posttypes[] = $posttype . '_tags';
		}
		
		$post_types = $posttypes;
	}

	$return = false;
	if ( $post_type != '' && !empty( $post_types ) && in_array( $post_type, $post_types ) ) {
		$return = true;
	}

	return $return;
}

/**
 * Add option to manage enable/disable location for CPT
 *
 * @since 1.1.6
 *
 * @param array $general_settings Array of GeoDirectory general settings.
 * @return array Array of settings.
 */
function geodir_cpt_tab_general_settings( $general_settings ) {
	if ( !empty( $general_settings ) ) {				
		$post_types = geodir_get_posttypes( 'object' );
		
		$geodir_posttypes = array();
		$post_type_options = array();
	
		foreach ( $post_types as $key => $post_types_obj ) {
			$geodir_posttypes[] = $key;
			
			$post_type_options[$key] = $post_types_obj->labels->singular_name;
		}
		
		$new_settings = array();
		
		foreach ( $general_settings as $setting ) {
			if ( isset( $setting['id'] ) && $setting['id']=='general_options' && isset( $setting['type'] ) && $setting['type']=='sectionend' ) {
				$extra_setting = array(
									'name' => __( 'Select CPT to disable physical location', 'geodir_custom_posts' ),
									'desc' => __( 'Select the post types that does not require geographic position/physical location. All fields will be disabled that related to geographic position/physical location. <span style="color:red;">( WARNING: this will remove all location data from the CPT, it can not be recovered if you set the wrong CPT )</span>', 'geodir_custom_posts' ),
									'tip' => '',
									'id' => 'geodir_cpt_disable_location',
									'css' => 'min-width:300px;',
									'std' => $geodir_posttypes,
									'type' => 'multiselect',
									'placeholder_text' => __( 'Select post types', 'geodir_custom_posts' ),
									'class' => 'chosen_select',
									'options' => $post_type_options
								);
				
				$new_settings[] = $extra_setting;
			}
			$new_settings[] = $setting;
		}
		
		$general_settings = $new_settings;
	}
	
	return $general_settings;
}

/**
 * Filter the general settings saved.
 *
 * After general settings saved it process the option of enable/disable location
 * for CPT.
 *
 * @since 1.1.6
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_cpt_submit_general_settings() {
	global $wpdb, $plugin_prefix;
	
	$cpt_disable_location = !empty( $_REQUEST['geodir_cpt_disable_location'] ) ? $_REQUEST['geodir_cpt_disable_location'] : NULL;
    $gd_posttypes = geodir_get_posttypes();
	
	foreach ( $gd_posttypes as $gd_posttype ) {
		if ( !empty( $cpt_disable_location ) && in_array( $gd_posttype, $cpt_disable_location ) ) {
			$sql = $wpdb->prepare( "UPDATE " . GEODIR_CUSTOM_FIELDS_TABLE . " SET is_active = '0' WHERE post_type=%s AND field_type=%s AND htmlvar_name=%s AND is_active != '0'", array( $gd_posttype, 'address', 'post' ) );
		} else {
			$sql = $wpdb->prepare( "UPDATE " . GEODIR_CUSTOM_FIELDS_TABLE . " SET is_active = '1' WHERE post_type=%s AND field_type=%s AND htmlvar_name=%s AND is_active != '1'", array( $gd_posttype, 'address', 'post' ) );
		}

		$wpdb->query( $sql );
	}

	if ( !empty( $cpt_disable_location ) ) {

		$exclude_post_types = get_option( 'geodir_exclude_post_type_on_map' );
		$exclude_post_types = !empty( $cpt_disable_location ) ? array_unique( array_merge( $exclude_post_types, $cpt_disable_location ) ) : $exclude_post_types;

		update_option( 'geodir_exclude_post_type_on_map', $exclude_post_types );

	}
}

/**
 * Retrieve the term link.
 *
 * @since 1.1.6
 * @since 1.3.3 Fix category slug of location less CPT for WPML.
 *
 * @global object $post WordPress Post object.
 *
 * @param string $termlink Term link URL.
 * @param object $term Term object.
 * @param string $taxonomy Taxonomy slug.
 * $return string The term link
 */
function geodir_cpt_term_link( $termlink, $term, $taxonomy ) {
	if ( geodir_cpt_no_location( $taxonomy, true ) ) {
		global $post;
		
		if ( geodir_is_page( 'detail' ) && !empty( $post ) && isset( $post->country_slug ) ) {
			$location_vars = array(
				'gd_country' => $post->country_slug,
				'gd_region' => $post->region_slug,
				'gd_city' => $post->city_slug
			);
		} else {
			$location_vars = geodir_get_current_location_terms( 'query_vars' );
		}
		
		$location_vars = geodir_remove_location_terms( $location_vars );
		
		if ( !empty( $location_vars ) ) {
			$listing_slug = geodir_get_listing_slug( $taxonomy );
			
			if ( get_option('permalink_structure') ) {
				// WPML
				if ( geodir_is_wpml() ) {
					$taxonomy_obj = get_taxonomy( $taxonomy );
					
					if ( empty( $taxonomy_obj->object_type[0] ) ) {
						return $termlink;
					}
					
					$post_type = $taxonomy_obj->object_type[0];
					
					if ( geodir_wpml_is_post_type_translated( $post_type ) && gd_wpml_slug_translation_turned_on( $post_type ) && $language_code = gd_wpml_get_lang_from_url( $termlink ) ) {
						$slug = apply_filters('wpml_translate_single_string', $listing_slug, 'WordPress', 'URL slug: ' . $listing_slug, $language_code );
						$listing_slug = $slug ? $slug : $listing_slug;
					}
				}
				// WPML
				
				$location_vars = implode( '\/', $location_vars );
				$old_listing_slug = '\/' . $listing_slug . '\/' . $location_vars . '\/';
				$new_listing_slug = '/' . $listing_slug . '/';
				$termlink = trailingslashit( preg_replace( '/' . $old_listing_slug . '/', $new_listing_slug, $termlink, 1 ) );
			} else {
				$termlink = esc_url( remove_query_arg( array( 'gd_country', 'gd_region', 'gd_city', 'gd_neighbourhood' ), $termlink ) );
			}
		}
	}
	
	return $termlink;
}

/**
 * Retrieve the post type archive permalink.
 *
 * @since 1.1.6
 * @since 1.3.3 Fix location less CPT archive slug for WPML.
 *
 * @global object $post WordPress Post object.
 *
 * @param string $link The post type archive permalink.
 * @param string $post_type Post type name.
 * @param string The post type archive permalink.
 */
function geodir_cpt_post_type_archive_link( $link, $post_type ) {
	if ( geodir_cpt_no_location( $post_type ) ) {
		global $post;
		
		if ( geodir_is_page( 'detail' ) && !empty( $post ) && isset( $post->country_slug ) ) {
			$location_vars = array(
				'gd_country' => $post->country_slug,
				'gd_region' => $post->region_slug,
				'gd_city' => $post->city_slug
			);
		} else {
			$location_vars = geodir_get_current_location_terms( 'query_vars' );
		}
		
		$location_vars = geodir_remove_location_terms( $location_vars );
		
		if ( !empty( $location_vars ) ) {
			if ( get_option( 'permalink_structure' ) ) {
				$post_type_obj = get_post_type_object( $post_type );
				
				if ( empty( $post_type_obj->rewrite['slug'] ) ) {
					return $link;
				}
				
				$listing_slug = $post_type_obj->rewrite['slug'];
				
				// WPML
				if ( geodir_wpml_is_post_type_translated( $post_type ) ) {
					if ( gd_wpml_slug_translation_turned_on( $post_type ) && $language_code = gd_wpml_get_lang_from_url( $link ) ) {
						$slug = apply_filters('wpml_translate_single_string', $listing_slug, 'WordPress', 'URL slug: ' . $listing_slug, $language_code );
						$listing_slug = $slug ? $slug : $listing_slug;
					}
				}
				// WPML
				
				$location_vars = implode( '\/', $location_vars );
				$old_listing_slug = '\/' . $listing_slug . '\/' . $location_vars . '\/';
				$new_listing_slug = '/' . $listing_slug . '/';
				$link = trailingslashit( preg_replace( '/' . $old_listing_slug . '/', $new_listing_slug, $link, 1 ) );
			} else {
				$link = esc_url( remove_query_arg( array( 'gd_country', 'gd_region', 'gd_city' ), $link ) );
			}
		}
	}
	return $link;
}

/**
 * Filter if the location should be added to the url, and dont add if location is locationless.
 *
 * @since 1.1.9
 * @param bool $active If the location is set to be added to the url or not.
 * @param WP_Post $post_obj Post object. Default current post.
 * @param string $post_type The post type of the post being tested.
 * @return bool False if CPT is locationless.
 */
function geodir_cpt_post_type_link( $active, $post_type, $post_obj ) {

	// if the post type is locationless then set to false so the location is not added to the url.
	if ( geodir_cpt_no_location( $post_type ) ) {
        $active = false;
    }

    return $active;
}

/**
 * Filter whether search by location allowed for CPT.
 *
 * @since 1.1.6
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param bool $allowed True if search by location allowed. Otherwise false.
 * @param object $gd_wp_query_vars WP_Query query vars object.
 * @param string $gd_table Listing database table name.
 * @param object $gd_wp_query WP_Query query object.
 * @param string $gd_p_table Listing database table name.
 * @return bool True if search by location allowed. Otherwise false.
 */
function geodir_cpt_allowed_location_where( $allowed, $gd_wp_query_vars, $gd_table, $gd_wp_query, $gd_p_table = '' ) {
	global $plugin_prefix;
	
	$gd_post_type = !empty( $gd_wp_query_vars ) && isset( $gd_wp_query_vars['post_type'] ) && $gd_wp_query_vars['post_type'] != '' ? $gd_wp_query_vars['post_type'] : '';
	
	if ( $gd_table != '' || $gd_p_table != '' ) {
		$gd_posttypes = geodir_get_posttypes();
		
		$gd_table = $gd_p_table != '' ? $gd_p_table : $gd_table;
		
		foreach ( $gd_posttypes as $gd_posttype ) {
			if ( $gd_table == $plugin_prefix . $gd_posttype . '_detail' ) {
				$gd_post_type = $gd_posttype;
			}
		}
	}
	
	if ( geodir_cpt_no_location( $gd_post_type ) ) {
		$allowed = false;
	}
	
	return $allowed;
}

/**
 * Add a class to the `li` element of the listings list template.
 *
 * @since 1.1.6
 *
 * @global WP_Post $post Post object. Default current post.
 *
 * @param string $class Css style class.
 * @param array $all_postypes Array of post types. Default empty.
 * @return string Css style class.
 */
function geodir_cpt_post_view_class( $class, $all_postypes = '' ) {
	global $post;

    $gdp_post_id = !empty($post) && isset($post->ID) ? $post->ID : NULL;
    $gdp_post_type = $gdp_post_id > 0 && isset($post->post_type) ? $post->post_type : NULL;

    if ( $gdp_post_id && $gdp_post_type ) {
        if ( geodir_cpt_no_location( $gdp_post_type ) ) {
			$class .= ' gd-post-no-geo';
		}
    }

    return $class;
}

/**
 * Filter post type columns in backend listing.
 *
 * @since 1.1.6
 *
 * @param string $columns Array of post type columns.
 * @param string Array of post type columns.
 */
function geodir_cpt_edit_post_columns( $columns ) {
	if ( !empty( $columns ) && isset( $columns['location'] ) && $post_type = geodir_admin_current_post_type() ) {
		if ( geodir_cpt_no_location( $post_type ) )
			unset( $columns['location'] );
	}
	return $columns;
}

/**
 * Filter the columns displayed for CPT in backend listing.
 *
 * @since 1.1.6
 *
 */
function geodir_cpt_admin_list_columns() {
    if ( $post_types = get_option( 'geodir_cpt_disable_location' ) ) {
        foreach ( $post_types as $post_type ) {
            add_filter("manage_edit-{$post_type}_columns", 'geodir_cpt_edit_post_columns', 9999 );
        }
    }
}


/**
 * Hide the near search field if the CPT is locationless.
 *
 * @since 1.3.1
 * @param string $html The html to filter.
 * @param string $cpt The current CPT.
 *
 * @return string The filtered html.
 */
function geodir_cpt_hide_near_locationless($html,$cpt){
	$cpt_disable_location = get_option( 'geodir_cpt_disable_location' );

	if(is_array($cpt_disable_location) && in_array($cpt,$cpt_disable_location)){
		$html .= ' style="display:none;" ';
	}

	return $html;
}
add_filter('geodir_near_input_extra','geodir_cpt_hide_near_locationless',10,2);


/**
 * Filter the location terms.
 *
 * @since 1.1.6
 *
 * @param array $location_array Array of location terms. Default empty.
 * @param string $location_array_from Source type of location terms. Default session.
 * @param string $gd_post_type WP post type.
 * @return array Array of location terms.
 */
function geodir_cpt_current_location_terms( $location_array = array(), $location_array_from = 'session', $gd_post_type = '' ) {
	if ( geodir_cpt_no_location( $gd_post_type ) ) {
		$location_array = array();
	}
	
	return $location_array;
}

/**
 * Outputs the listings template title.
 *
 * @since 1.1.6
 * @deprecated 1.2.6 non needed
 *
 * @global object $wp The WordPress object.
 * @global string $term Current term slug.
 *
 * @param string $list_title The post page title.
 * @return string The post page title.
 */
function geodir_cpt_listing_page_title( $list_title = '' ) {
    global $wp, $term;

    $gd_post_type = geodir_get_current_posttype();
	if ( !geodir_cpt_no_location( $gd_post_type ) ) {
		return $list_title;
	}
    $post_type_info = get_post_type_object( $gd_post_type );

    $add_string_in_title = __( 'All', 'geodir_custom_posts' ) . ' ';
    if ( isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'favourite' ) {
        $add_string_in_title = __( 'My Favorite', 'geodir_custom_posts' ) . ' ';
    }

    $list_title = $add_string_in_title . __( ucfirst( $post_type_info->labels->name ), 'geodir_custom_posts' );
    $single_name = $post_type_info->labels->singular_name;

    $taxonomy = geodir_get_taxonomies($gd_post_type, true);

    if (!empty($term)) {
        $current_term_name = '';
		
		$current_term = get_term_by( 'slug', $term, $taxonomy[0] );
		if ( !empty( $current_term ) ) {
            $current_term_name = __( ucfirst( $current_term->name ), 'geodir_custom_posts' );
        } else {
            if (count($taxonomy) > 1) {
                $current_term = get_term_by( 'slug', $term, $taxonomy[1] );

                if (!empty($current_term)) {
                    $current_term_name = __( ucfirst( $current_term->name ), 'geodir_custom_posts' );
                }
            }
        }
		
		if ( $current_term_name != '' ) {
			$list_title .= __(' in', 'geodir_custom_posts' ) . " '" . $current_term_name . "'";
		}

    }

    if ( is_search() ) {
        $list_title = __( 'Search', 'geodir_custom_posts' ) . ' ' . __( ucfirst( $post_type_info->labels->name ), 'geodir_custom_posts' ) . __(' For :', GEODIRECTORY_TEXTDOMAIN ) . " '" . get_search_query() . "'";
    }
	return $list_title;
}

/**
 * Filter the map should be displayed on detail page or not.
 *
 * @since 1.1.6
 *
 * @global WP_Post $post WP Post object. Default current post.
 *
 * @param bool $is_display True if map should be displayed, otherwise false.
 * @param string $tab The listing detail page tab.
 * @return True if map should be displayed, otherwise false.
 */
function geodir_cpt_detail_page_map_is_display( $is_display, $tab ) {
	global $post;

    // this bit added for preview page
    if(isset($post->post_type) && $post->post_type){$post_type = $post->post_type;}
    elseif(isset($post->listing_type) && $post->listing_type){$post_type = $post->listing_type;}

    if ( $tab == 'post_map' && ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) && !empty( $post ) && isset( $post_type) && geodir_cpt_no_location( $post_type ) ) {
        $is_display = false;
	}

    return $is_display;
}

/**
 * Remove filter on location change on search page.
 *
 * @since 1.1.6
 *
 */
function geodir_cpt_remove_loc_on_search() {
	$search_posttype = isset( $_REQUEST['stype'] ) ? $_REQUEST['stype'] : geodir_get_current_posttype();
	
	if ( geodir_cpt_no_location( $search_posttype ) ) {	
		remove_filter( 'init', 'geodir_change_loc_on_search' );
	}
}

/**
 * Remove terms from location search request.
 *
 * @since 1.1.6
 *
 * @global int $dist Distance in range to search.
 * @global string $mylat Geo latitude
 * @global string $mylon Geo longitude
 * @global string $snear Nearest place to search.
 */
function geodir_cpt_remove_location_search() {
	$search_posttype = isset( $_REQUEST['stype'] ) ? $_REQUEST['stype'] : geodir_get_current_posttype();
	
	if ( geodir_cpt_no_location( $search_posttype ) ) {	
		global $dist, $mylat, $mylon, $snear;
		$dist = $mylat = $mylon = $snear = '';
		
		if ( isset( $_REQUEST['snear'] ) ) {
			unset( $_REQUEST['snear'] );
		}
		
		if ( isset( $_REQUEST['sgeo_lat'] ) ) {
			unset( $_REQUEST['sgeo_lat'] );
		}
			
		if ( isset( $_REQUEST['sgeo_lon'] ) ) {
			unset( $_REQUEST['sgeo_lon'] );
		}
	}
}

/**
 * Filter the listing map should to be displayed or not.
 *
 * @since 1.1.6
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @global object $post The current post object.
 *
 * @param bool $display true if map should be displayed, false if not.
 * @return bool true if map should be displayed, false if not.
 */
function geodir_cpt_remove_map_listing( $display = true ) {
	if ( geodir_is_page( 'listing' ) || geodir_is_page( 'detail' ) || geodir_is_page( 'search' ) ) {
		global $wp_query, $post;
		
		$gd_post_type = '';
		if ( geodir_is_page( 'detail' ) ) {
			$gd_post_type = !empty( $post ) && isset( $post->post_type ) ? $post->post_type : $gd_post_type;
		} else if ( geodir_is_page( 'search' ) ) {
			$gd_post_type = isset( $_REQUEST['stype'] ) ? $_REQUEST['stype'] : $gd_post_type;
		} else {
			$gd_post_type = !empty( $wp_query ) && isset( $wp_query->query_vars ) && isset( $wp_query->query_vars['post_type'] ) ? $wp_query->query_vars['post_type'] : '';
		}
		
		if ( $gd_post_type && geodir_cpt_no_location( $gd_post_type ) ) {	
			$display = false;
		}
	}
	
	return $display;
}

/**
 * Filter the terms count by location.
 *
 * @since 1.1.6
 *
 * @param array $terms_count Array of term count row.
 * @param array $terms Array of terms.
 * @return array Array of term count row.
 */
function geodir_cpt_loc_term_count( $terms_count, $terms ) {
	if ( !empty( $terms_count ) ) {
		foreach ( $terms as $term ) {
			if ( isset( $term->taxonomy ) && geodir_cpt_no_location( $term->taxonomy, true ) ) {
				$terms_count[$term->term_id] = $term->count;
			}
		}
	}
	return $terms_count;
}

/**
 * Add an action hook for disable location post type.
 *
 * @since 1.1.7
 *
 * @param string $sub_tab Current sub tab.
 */
function geodir_cpt_manage_available_fields( $sub_tab = '' ) {
	if ( !empty( $_REQUEST['listing_type'] ) && geodir_cpt_no_location( $_REQUEST['listing_type'] ) ) {
		add_action( 'admin_footer', 'geodir_cpt_admin_no_location_js' );
	}
}

/**
 * Add the javascript to hide address field from custom field.
 *
 * @since 1.1.7
 *
 * @return string Print the inline script.
 */
function geodir_cpt_admin_no_location_js() {
	if ( !empty( $_REQUEST['listing_type'] ) && geodir_cpt_no_location( $_REQUEST['listing_type'] ) ) {
		echo '<script type="text/javascript">jQuery(\'#field_type[value="address"]\', \'#geodir-selected-fields\').each(function(){jQuery(this).closest(\'[id^="licontainer_"]\').remove();});jQuery(\'a.gt-address\', \'#geodir-available-fields\').parent(\'li\').remove();</script>';
	}
}

/**
 * Add the javascript to make cat icon upload optional.
 *
 * @since 1.1.7
 *
 * @global string $pagenow The current screen.
 *
 * @return string Print the inline script.
 */
function geodir_cpt_admin_footer() {
	global $pagenow;
	if ( ( $pagenow == 'edit-tags.php' || $pagenow == 'term.php' ) && !empty( $_REQUEST['taxonomy'] ) && geodir_cpt_no_location( $_REQUEST['taxonomy'], true ) ) {
		echo '<script type="text/javascript">jQuery(\'[name="ct_cat_icon[src]"]\', \'#addtag, #edittag\').removeClass(\'ct_cat_icon[src]\');jQuery(\'[name="ct_cat_icon[id]"]\', \'#addtag, #edittag\').closest(\'.form-field\').removeClass(\'form-required\').removeClass(\'form-invalid\');</script>';
	}
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.3.1
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_cpt_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}