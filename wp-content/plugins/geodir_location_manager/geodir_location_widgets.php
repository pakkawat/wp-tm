<?php
/**
 * Contains functions related to Location Manager plugin update.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */

//paste this function in location_functions.php
/**
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $country
 * @param string $gd_region
 * @return bool|mixed
 */
function geodir_get_country_region_location($country = '', $gd_region = '') {
    global $wpdb;

    $location = $wpdb->get_results(
        $wpdb->prepare(
            "select * from ".POST_LOCATION_TABLE." where country_slug = %s AND region_slug = %s ",
            array($country, $gd_region)
        )
    );

    if (!empty($location)) {
        return $location;
    } else {
        return false;
    }
}

if (!function_exists('register_geodir_location_widgets')) {
    /**
     *
     */
    function register_geodir_location_widgets() {
    /**
    * Geodirectory popular locations widget *
    **/
    class geodir_popular_location extends WP_Widget {
        function __construct() {
            $widget_ops = array('classname' => 'geodir_popular_location', 'description' => __('GD > Popular Locations', 'geodirlocation') );
            parent::__construct(
                'popular_location', // Base ID
                __('GD > Popular Location', 'geodirlocation'), // Name
                $widget_ops// Args
            );
        }

        /**
         *
         * @param array $args
         * @param array $instance
         */
        public function widget($args, $instance) {
            extract($args, EXTR_SKIP);
            
            /**
             * Filter the widget title.
             *
             * @since 1.0.0
             *
             * @param string $title The widget title. Default empty.
             * @param array  $instance An array of the widget's settings.
             * @param mixed  $id_base The widget ID.
             */
            $title = apply_filters('geodir_popular_location_widget_title', !empty($instance['title']) ? $instance['title'] : '', $instance, $this->id_base);
            
            /**
             * Filter the no. of locations to shows on each page.
             *
             * @since 1.5.0
             *
             * @param int   $per_page No. of locations to be displayed.
             * @param array $instance An array of the widget's settings.
             * @param mixed $id_base The widget ID.
             */
            $params['per_page'] = apply_filters('geodir_popular_location_widget_per_page', !empty($instance['per_page']) ? absint($instance['per_page']) : '', $instance, $this->id_base);
            
            /**
             * Whether to show pagination on top of widget content.
             *
             * @since 1.5.0
             *
             * @param bool  $pagi_t If true then pagination displayed on top. Default false.
             * @param array $instance An array of the widget's settings.
             * @param mixed $id_base The widget ID.
             */
            $params['pagi_t'] = apply_filters('geodir_popular_location_widget_pagi_top', !empty($instance['pagi_t']) ? true : false, $instance, $this->id_base);
            
            /**
             * Whether to show pagination on bottom of widget content.
             *
             * @since 1.5.0
             *
             * @param bool  $pagi_b If true then pagination displayed on bottom. Default false.
             * @param array $instance An array of the widget's settings.
             * @param mixed $id_base The widget ID.
             */
            $params['pagi_b'] = apply_filters('geodir_popular_location_widget_pagi_bottom', !empty($instance['pagi_b']) ? true : false, $instance, $this->id_base);
            
            /**
             * Filter the position to display advanced pagination info.
             *
             * @since 1.5.0
             *
             * @param string  $pagi_info Position to display advanced pagination info.
             * @param array $instance An array of the widget's settings.
             * @param mixed $id_base The widget ID.
             */
            $params['pagi_info'] = apply_filters('geodir_popular_location_widget_pagi_info', !empty($instance['pagi_info']) ? $instance['pagi_info'] : '', $instance, $this->id_base);
            
            /**
             * Whether to disable filter results for current location.
             *
             * @since 1.5.0
             *
             * @param bool  $no_loc If true then results not filtered for current location. Default false.
             * @param array $instance An array of the widget's settings.
             * @param mixed $id_base The widget ID.
             */
            $params['no_loc'] = apply_filters('geodir_popular_location_widget_no_location_filter', !empty($instance['no_loc']) ? true : false, $instance, $this->id_base);
            
            $params['widget_atts'] = $params;
            
            echo $before_widget;
            ?>
            <div class="geodir-category-list-in clearfix">
                <div class="geodir-cat-list clearfix">
                    <?php 
                    if (!empty($title)) {
                        echo $before_title . __($title, 'geodirlocation') . $after_title;
                    }
                    
                    geodir_popular_location_widget_output($params);
                    ?>
                </div>
            </div>
            <?php
            echo $after_widget;
        }
        
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            $instance['per_page'] = !empty($new_instance['per_page']) ? absint($new_instance['per_page']) : false;
            $instance['pagi_t'] = !empty($new_instance['pagi_t']) ? true : false;
            $instance['pagi_b'] = !empty($new_instance['pagi_b']) ? true : false;
            $instance['pagi_info'] = !empty($new_instance['pagi_info']) ? $new_instance['pagi_info'] : '';
            $instance['no_loc'] = !empty($new_instance['no_loc']) ? true : false;
            
            return $instance;
        }
        
        public function form($instance) {
            $instance = wp_parse_args( (array)$instance, 
                array(
                    'title' => '',
                    'per_page' => '',
                    'pagi_t' => false,
                    'pagi_b' => false,
                    'pagi_info' => '',
                    'no_loc' => false
                )
            );
            
            $title = strip_tags($instance['title']);
            $per_page = !empty($instance['per_page']) ? absint($instance['per_page']) : false;
            $top_pagination = !empty($instance['pagi_t']) ? true : false;
            $bottom_pagination = !empty($instance['pagi_b']) ? true : false;
            $pagination_info = !empty($instance['pagi_info']) ? $instance['pagi_info'] : '';
            $no_location_filter = !empty($instance['no_loc']) ? true : false;
            
            $per_page = $per_page > 0 ? $per_page : '';
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirlocation');?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('per_page'); ?>"><?php _e('Number of locations:', 'geodirlocation');?>
                    <input class="widefat" id="<?php echo $this->get_field_id('per_page'); ?>" name="<?php echo $this->get_field_name('per_page'); ?>" type="number" step="1" value="<?php echo esc_attr($per_page); ?>"/>
                </label>
                <span class="description"><?php _e('Number of locations to be shown on each page. Use 0(zero) or ""(blank) to show all locations.', 'geodirlocation');?></span>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('pagi_t'); ?>">
                    <input type="checkbox" id="<?php echo $this->get_field_id('pagi_t'); ?>" name="<?php echo $this->get_field_name('pagi_t'); ?>" <?php checked($top_pagination, true); ?> value="1"/><?php _e('Show pagination on top', 'geodirlocation');?>
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('pagi_b'); ?>">
                    <input type="checkbox" id="<?php echo $this->get_field_id('pagi_b'); ?>" name="<?php echo $this->get_field_name('pagi_b'); ?>" <?php checked($bottom_pagination, true); ?> value="1"/><?php _e('Show pagination at bottom', 'geodirlocation');?>
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('pagi_info'); ?>"><?php _e('Show advanced pagination details:', 'geodirlocation');?>
                    <select class="widefat" id="<?php echo $this->get_field_id('pagi_info'); ?>" name="<?php echo $this->get_field_name('pagi_info'); ?>">
                        <option value="" <?php selected(true, empty($pagination_info));?>><?php _e('Never Display', 'geodirectory');?></option>
                        <option value="after" <?php selected(true, $pagination_info == 'after');?>><?php _e('After Pagination', 'geodirectory');?></option>
                        <option value="before" <?php selected(true, $pagination_info == 'before');?>><?php _e('Before Pagination', 'geodirectory');?></option>
                    </select>
                </label>
                <span class="description"><?php _e('This will add extra pagination info like "Showing locations x-y of z" after/before pagination.', 'geodirlocation');?></span>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('no_loc'); ?>">
                    <input type="checkbox" id="<?php echo $this->get_field_id('no_loc'); ?>" name="<?php echo $this->get_field_name('no_loc'); ?>" <?php checked($no_location_filter, true); ?> value="1"/><?php _e('Don\'t filter results for current location', 'geodirlocation');?>
                </label>
            </p>
            <?php  
        } 
    }
    register_widget('geodir_popular_location'); 
}
}

/* LOCATION NEIGHBOURHOOD WIDGET */
if (!function_exists('register_geodir_neighbourhood_widgets')) {
    /**
     *
     */
    function register_geodir_neighbourhood_widgets() {
		/**
		* Geodirectory popular locations widget *
		**/
		class geodir_location_neighbourhood extends WP_Widget {

            function __construct() {
                $widget_ops = array('classname' => 'geodir_location_neighbourhood', 'description' => __('GD > Location Neighbourhood', 'geodirlocation') );
                parent::__construct(
                    'location_neighbourhood', // Base ID
                    __('GD > Location Neighbourhood', 'geodirlocation'), // Name
                    $widget_ops// Args
                );
            }


            /**
             * Display the location neighbourhoods.
			 *
			 * @since 1.0.0
			 * @since 1.4.4 Permalink added for location neighbourhood urls.
			 * @since 1.5.6 Option added in location neighbourhood widget to use viewing CPT in links.
			 *
			 * @global object $gd_session GeoDirectory Session object.
			 *
			 * @param array $args
			 * @param array $instance
			 */
			public function widget( $args, $instance ) {
				global $gd_session;
				
				if (!get_option('location_neighbourhoods')) {
					return;
				}
				extract($args, EXTR_SKIP);
				
				$title = empty($instance['title']) ? __('Location Neighbourhood', 'geodirlocation') : apply_filters('geodir_location_neighbourhood_widget_title', __($instance['title'],'geodirlocation'));
				$use_current_cpt = !empty( $instance['use_current_cpt'] ) ? true : false;
				$post_type = !empty( $instance['post_type'] ) ? $instance['post_type'] : '';
				$use_current_cpt = apply_filters('geodir_location_neighbourhood_widget_use_current_cpt', $use_current_cpt, $instance, $this->id_base );
				if ( $use_current_cpt && $current_post_type = geodir_get_current_posttype() ) {
					$post_type = $current_post_type;
				}
				$post_type = apply_filters('geodir_location_neighbourhood_widget_post_type', $post_type, $instance, $this->id_base );
				
				if ( $post_type ) {
					$location_allowed = $post_type && function_exists( 'geodir_cpt_no_location' ) && geodir_cpt_no_location( $post_type ) ? false : true;
					if ( !$location_allowed ) {
						return;
					}
				}
				
				$location_id = '';
				if (get_option('geodir_enable_city') == 'default') {
					$default_location = geodir_get_default_location();
					$location_id = !empty($default_location->location_id) ? $default_location->location_id : $location_id;
				} else {
					$location_terms = geodir_get_current_location_terms();
					if (isset($location_terms['gd_city']) && !empty($location_terms['gd_city'])) {
							$gd_city = $location_terms['gd_city'];
						$gd_region = isset($location_terms['gd_region']) ? $location_terms['gd_region'] : '';
						$gd_country = isset($location_terms['gd_country']) ? $location_terms['gd_country'] : '';
						
						$location_info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);
						$location_id = !empty($location_info) ? $location_info->location_id : 0;
					}
				}
				
				$gd_neighbourhoods = $location_id ? geodir_get_neighbourhoods($location_id) : NULL;
				
				if (!empty($gd_neighbourhoods)) {
					$post_type_url = '';
					$location_page_url = '';
					if ( $post_type && get_option( 'geodir_add_location_url' ) ) {
						$location_page_url = geodir_get_location_link( 'base' );
						$set_multi_location = false;
						
						if ( $gd_session->get( 'gd_multi_location' ) ) {
							$gd_session->un_set( 'gd_multi_location' );
							$set_multi_location = true;
						}
						
						$post_type_url = get_post_type_archive_link( $post_type );
						
						if ( $set_multi_location ) {
							$gd_session->set( 'gd_multi_location', 1 );
						}
					}
					echo $before_widget;
					?>
					<div id="geodir-category-list">              
						<div class="geodir-category-list-in clearfix">
							<div class="geodir-cat-list clearfix">
							  <?php echo $before_title . __($title, 'geodirlocation') . $after_title; ?>
							  <?php 
								$hood_count = 0;
								echo '<ul>';     
								foreach ($gd_neighbourhoods as $gd_neighbourhood) {
									if ($hood_count%15 == 0) {
										echo '</ul><ul>';
									}

									$neighbourhood_name = __($gd_neighbourhood->hood_name, 'geodirlocation');
									$neighbourhood_url = geodir_location_get_neighbourhood_url($gd_neighbourhood->hood_slug, true);
									if ( $post_type_url && $location_page_url ) {
										$neighbourhood_url = str_replace( untrailingslashit( $location_page_url ), untrailingslashit( $post_type_url ), $neighbourhood_url );
									}
									echo '<li><a href="' . esc_url($neighbourhood_url) . '">' . stripslashes($neighbourhood_name) . '</a></li>';
									$hood_count++;
								}
								echo '</ul>';
							?>
							</div>  
						</div>
					</div>
					<?php
					echo $after_widget;
				}
			}
			
			public function update( $new_instance, $old_instance ) {
				$instance = $old_instance;
				$instance['title'] = strip_tags( $new_instance['title'] );
				$instance['use_current_cpt'] = !empty( $new_instance['use_current_cpt'] ) ? 1 : 0;
				return $instance;
			}
			
			public function form( $instance ) {
				$instance = wp_parse_args( (array)$instance,
					array( 
						'title' => '',
						'use_current_cpt' => false
					)
				);
				
				$title = strip_tags($instance['title']);
				$use_current_cpt = !empty( $instance['use_current_cpt'] ) ? true : false;
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirlocation');?>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
					</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'use_current_cpt' ); ?>">
						<input type="checkbox" id="<?php echo $this->get_field_id( 'use_current_cpt' ); ?>" name="<?php echo $this->get_field_name( 'use_current_cpt' ); ?>" <?php checked( $use_current_cpt, true ); ?> value="1" /> <?php _e( 'Use current viewing post type', 'geodirlocation' );?>
					</label>
					<span class="description"><?php _e( 'If widget used on listings & search pages then tick this option to use current viewing post type in neighbourhood links.', 'geodirlocation' ); ?></span>
				</p>
				<?php  
			} 
		}
		register_widget('geodir_location_neighbourhood'); 
	
	}
}

/* LOCATION NEIGHBOURHOOD WIDGET */

/**
 *
 */
function register_geodir_neighbourhood_posts_widgets(){
	
	class geodir_neighbourhood_posts extends WP_Widget {

        function __construct() {
            $widget_ops = array('classname' => 'geodir_neighbourhood_posts', 'description' => __('GD > Popular Neighbourhood Post', 'geodirlocation') );
            parent::__construct(
                'neighbourhood_posts', // Base ID
                __('GD > Popular Neighbourhood Post', 'geodirlocation'), // Name
                $widget_ops// Args
            );
        }


        /**
         *
         * @global object $wpdb WordPress Database object.
         *
         * @param array $args
         * @param array $instance
         */
        public function widget($args, $instance) {
			global $wpdb, $post;
			
			extract($args, EXTR_SKIP);

			$title = empty($instance['title']) ? $instance['category_title'] : apply_filters('widget_title', $instance['title']);
			$hood_post_type = empty($instance['hood_post_type']) ? 'gd_place' : apply_filters('widget_hood_post_type', $instance['hood_post_type']);
			$hood_category = empty($instance['hood_category']) ? '0' : apply_filters('widget_hood_category', $instance['hood_category']);
			$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
			$layout = empty($instance['layout']) ? 'gridview_onehalf' : apply_filters('widget_layout', $instance['layout']);
			$add_location_filter = empty($instance['add_location_filter']) ? '0' : apply_filters('widget_layout', $instance['add_location_filter']);
			$list_sort = empty($instance['list_sort']) ? 'latest' : apply_filters('widget_list_sort', $instance['list_sort']);
			$character_count = empty($instance['character_count']) ? 20 : apply_filters('widget_list_sort', $instance['character_count']);
			$use_viewing_post_type = apply_filters('geodir_neighbourhood_posts_use_viewing_post_type', !empty($instance['use_viewing_post_type'])) ? true : false;
			
			// set post type to current viewing post type
			if ($use_viewing_post_type) {
				$current_post_type = geodir_get_current_posttype();
				if ($current_post_type != '' && $current_post_type != $hood_post_type) {
					$hood_post_type = $current_post_type;
				}
			}
		
			if (empty($title) || $title == 'All' ) {
				$title = wp_sprintf(__('Neighbourhood %s', 'geodirlocation'), get_post_type_plural_label($hood_post_type));
			}
			
			/**
			 * Filter the neighbour hood widget title.
			 *
			 * @since 1.4.4
			 * @package GeoDirectory_Location_Manager
			 *
			 * @param string $title The widget title.
			 * @param string $hood_post_type The post type.
			 */
			$title = apply_filters('geodir_location_neighbourhood_post_widget_title', $title, $hood_post_type);
			
			$all_postypes = geodir_get_posttypes();
			
			$location_id = '';
			
			$not_in_array = array();
			if (geodir_is_page('detail') || geodir_is_page('preview') || geodir_is_page('add-listing')) {
				if (isset($post->post_type) && $post->post_type == $hood_post_type && isset($post->post_location_id)) {
					$not_in_array[] = $post->ID;
					$location_id = $post->post_location_id;
				}
			} elseif (in_array($hood_post_type, $all_postypes)) {
				$location_id = '';
				$location_terms = geodir_get_current_location_terms();
				
				if (isset($location_terms['gd_city']) && !empty($location_terms['gd_city'])) {
					$gd_city = $location_terms['gd_city'];
					$gd_region = isset($location_terms['gd_region']) ? $location_terms['gd_region'] : '';
					$gd_country = isset($location_terms['gd_country']) ? $location_terms['gd_country'] : '';
					
					$location_info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);
					$location_id = !empty($location_info) ? $location_info->location_id : 0;
				} else {
					$default_location = geodir_get_default_location();
					$location_id = $default_location->location_id;
				}
			}

			$gd_neighbourhoods = $location_id ? geodir_get_neighbourhoods($location_id) : NULL;

			if (!empty($gd_neighbourhoods)) {
				echo $before_widget;
				?>
				<div class="geodir_locations geodir_location_listing">
					<div class="locatin_list_heading clearfix"><h3><?php _e(ucfirst($title), 'geodirlocation');?></h3></div>
					<?php 
					$hood_slug_arr = array();
					if (!empty($gd_neighbourhoods)) {
						foreach ($gd_neighbourhoods as $hoodslug) {
							$hood_slug_arr[] = $hoodslug->hood_slug;
						}
					}
							
					$query_args = array( 
						'posts_per_page' => $post_number,
						'is_geodir_loop' => true,
						'post__not_in' => $not_in_array,
						'gd_neighbourhood' => $hood_slug_arr,
						'gd_location' 	 => ($add_location_filter) ? true : false,
						'post_type' => $hood_post_type,
						'order_by' =>$list_sort,
						'excerpt_length' => $character_count
					);
							
					if ($hood_category != 0 || $hood_category != '') {
						$category_taxonomy = geodir_get_taxonomies($hood_post_type); 
						
						$tax_query = array( 
										'taxonomy' => $category_taxonomy[0],
										'field' => 'id',
										'terms' => $hood_category
									);
						
						$query_args['tax_query'] = array( $tax_query );					
					}
						
					global $gridview_columns;
					
					query_posts( $query_args );
					
					if (strstr($layout, 'gridview')) {
						$listing_view_exp = explode('_', $layout);
						$gridview_columns = $layout;
						$layout = $listing_view_exp[0];
					}
						
					$template = apply_filters( "geodir_template_part-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
					include( $template );
					wp_reset_query();
					?> 
				</div>
				<?php
				echo $after_widget;
			}
		}
		
		public function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);	
			$instance['hood_post_type'] = strip_tags($new_instance['hood_post_type']);
			$instance['hood_category'] = strip_tags($new_instance['hood_category']);
			$instance['category_title'] = strip_tags($new_instance['category_title']);
			$instance['post_number'] = strip_tags($new_instance['post_number']);
			$instance['layout'] = strip_tags($new_instance['layout']);
			$instance['list_sort'] = strip_tags($new_instance['list_sort']);
			$instance['character_count'] = $new_instance['character_count'];
			if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
			$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
			else
			$instance['add_location_filter'] = '0';
			$instance['use_viewing_post_type'] = !empty($new_instance['use_viewing_post_type']) ? 1 : 0;
			
			$character_count = $instance['character_count'];
			
			return $instance;
		}
		
		public function form($instance)
		{
			
			$instance = wp_parse_args( 
										(array) $instance, 
										array(	'title' => '', 
														'hood_post_type' => '',
														'hood_category'=>'',
														'category_title'=>'',
														'list_sort'=>'', 
														'list_order'=>'',
														'post_number' => '5',
														'layout'=> 'gridview_onehalf',
														'add_location_filter'=>'1',
														'character_count'=>'20',
														'use_viewing_post_type' => ''
													) 
									 );
			
			$title = strip_tags($instance['title']);
			
			$hood_post_type = strip_tags($instance['hood_post_type']);
			
			$hood_category = strip_tags($instance['hood_category']);
			
			$category_title = strip_tags($instance['category_title']);
			
			$list_sort = strip_tags($instance['list_sort']);
			
			$list_order = strip_tags($instance['list_order']);
			
			$post_number = strip_tags($instance['post_number']);
			
			$layout = strip_tags($instance['layout']);
			
			$add_location_filter = strip_tags($instance['add_location_filter']);
			
			$character_count = $instance['character_count'];
			$use_viewing_post_type = !empty($instance['use_viewing_post_type']) ? true : false;
			
			?>
			
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','geodirlocation');?>
				
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p>
			
			<p>
					<label for="<?php echo $this->get_field_id('hood_post_type'); ?>"><?php _e('Post Type:','geodirlocation');?>
			
					<?php $postypes = geodir_get_posttypes(); ?>
			
					<select class="widefat" id="<?php echo $this->get_field_id('hood_post_type'); ?>" name="<?php echo $this->get_field_name('hood_post_type'); ?>" onchange="geodir_change_hood_category_list(this)">
						
			<?php foreach($postypes as $postypes_obj){ ?>
							
									<option <?php if($hood_post_type == $postypes_obj){ echo 'selected="selected"'; } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_',$postypes_obj); echo ucfirst($extvalue[1]); ?></option>
							
			<?php } ?>
							
					</select>
					</label>
			</p>
			<p>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('use_viewing_post_type'); ?>" name="<?php echo $this->get_field_name('use_viewing_post_type'); ?>"<?php checked( $use_viewing_post_type ); ?> value="1" />
				<label for="<?php echo $this->get_field_id('use_viewing_post_type'); ?>"><?php _e( 'Use current viewing post type', 'geodirlocation' ); ?></label>
			<p>
			
			<p id="hood_post_categories">
				<label for="<?php echo $this->get_field_id('hood_category'); ?>"><?php _e('Post Category:','geodirlocation');?>
			
			<?php 
			
			$hood_post_type = ($hood_post_type!= '') ? $hood_post_type : 'gd_place';
			
			$category_taxonomy = geodir_get_taxonomies($hood_post_type); 
			$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
			?>
			
				<select class="widefat" id="<?php echo $this->get_field_id('hood_category'); ?>" name="<?php echo $this->get_field_name('hood_category'); ?>" onchange="jQuery('#<?php echo $this->get_field_id('category_title'); ?>').val(jQuery('#<?php echo $this->get_field_id('hood_category'); ?> option:selected').text());" >
					
					<option <?php if($hood_category == '0'){ echo 'selected="selected"'; } ?> value="0"><?php _e('All','geodirlocation'); ?></option>
					
					<?php foreach($categories as $category_obj){ ?>
					
					<option <?php if($hood_category == $category_obj->term_id){ echo 'selected="selected"'; } ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
						
					<?php } ?>
						
				</select>
			 <input type="hidden" name="<?php echo $this->get_field_name('category_title'); ?>" id="<?php echo $this->get_field_id('category_title'); ?>" value="<?php if($category_title != '') echo $category_title; else echo __('All','geodirlocation');?>" />
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:','geodirlocation');?>
					
				 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
							<option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest','geodirlocation'); ?></option>
							 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured','geodirlocation'); ?></option>
							<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review','geodirlocation'); ?></option>
							<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating','geodirlocation'); ?></option>
					</select>
					</label>
			</p>
							
			<p>
			
				<label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:','geodirlocation');?>
				
				<input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
				</label>
			</p>
							
			<p>
				<label for="<?php echo $this->get_field_id('layout'); ?>">
			<?php _e('Layout:','geodirlocation');?>
					<select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
						<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)','geodirlocation'); ?></option>
              <option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)','geodirlocation'); ?></option>
							<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)','geodirlocation'); ?></option>
							<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)','geodirlocation'); ?></option>
							<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view','geodirlocation'); ?></option>
					</select>    
					</label>
			</p>
			
			<p>
            <label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :','geodirlocation');?>
            <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
            </label>
        </p>
							
			<p style="display:none;">
					<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
			<?php _e('Enable Location Filter:','geodirlocation');?>
						<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
						</label>
				</p>
			
			<script type="text/javascript">
				function geodir_change_hood_category_list(obj,selected){
				
					var ajax_url = '<?php echo geodir_get_ajax_url(); ?>'
					
					var hood_post_type = obj.value;
					
					var myurl = ajax_url+"&geodir_ajax=admin_ajax&ajax_action=get_cat_dl&post_type="+hood_post_type+"&selected="+selected;
					
					jQuery.ajax({
						type: "GET",
						url: myurl,
						success: function(data){
							
							jQuery(obj).closest('form').find('#hood_post_categories select').html(data);
							
						}
					});
					
					}
					
					<?php if(is_active_widget( false, false, $this->id_base, true )){ ?>
					var hood_post_type = jQuery('#<?php echo $this->get_field_id('hood_post_type'); ?>').val();
					
					<?php } ?>
			
			</script>
	
			<?php   
		} 
	}
	
	register_widget('geodir_neighbourhood_posts'); 

}

/* LOCATION DESCRIPTION WIDGET */
if (!function_exists('register_geodir_location_description_widgets')) {
    /**
     *
     */
    function register_geodir_location_description_widgets(){
        /**
        * Geodirectory location description widget *
        **/
        class geodir_location_description extends WP_Widget {

            function __construct() {
                $widget_ops = array('classname' => 'geodir_location_description', 'description' => __('GD > Location Description', 'geodirlocation') );
                parent::__construct(
                    'location_description', // Base ID
                    __('GD > Location Description', 'geodirlocation'), // Name
                    $widget_ops// Args
                );
            }

            /**
             * @global object $wpdb WordPress Database object.
             *
             * @since 1.0.0
             * @since 1.5.1 Fix: use of wpautop() is messing up the location description.
             *
             * @param array $args
             * @param array $instance
             * @return null
             */
            public function widget($args, $instance) {
                global $wpdb, $wp;
                
                extract($args, EXTR_SKIP);
                
                $gd_country = isset($wp->query_vars['gd_country']) ? $wp->query_vars['gd_country'] : '';
                $gd_region = isset($wp->query_vars['gd_region']) ? $wp->query_vars['gd_region'] : '';
                $gd_city = isset($wp->query_vars['gd_city']) ? $wp->query_vars['gd_city'] : '';

                $location_title = '';
                $seo_desc = '';
                if ($gd_city) {
                    $info = geodir_city_info_by_slug($gd_city, $gd_country, $gd_region);
                    if (!empty($info)) {
                        $location_title =  $info->city;
                        $seo_desc = $info->city_desc;
                    }
                } else if (!$gd_city && $gd_region) {
                    $info = geodir_location_seo_by_slug($gd_region, 'region', $gd_country);
                    if (!empty($info)) {
                        $seo_desc = $info->seo_desc;
                        $location_title = $wpdb->get_var( $wpdb->prepare( "SELECT region FROM ".POST_LOCATION_TABLE." WHERE region_slug!='' AND region_slug=%s ORDER BY location_id ASC", array($gd_region) ) );
                    }
                } else if (!$gd_city && !$gd_region && $gd_country) {
                    $info = geodir_location_seo_by_slug($gd_country, 'country');
                    if (!empty($info)) {
                        $seo_desc = $info->seo_desc;
                        $location_title = $wpdb->get_var( $wpdb->prepare( "SELECT country FROM ".POST_LOCATION_TABLE." WHERE country_slug!='' AND country_slug=%s ORDER BY location_id ASC", array($gd_country) ) );
                    }
                }
                
                $seo_desc = $seo_desc != '' ? stripslashes( __( $seo_desc, 'geodirlocation' ) ) : '';
                
                /**
                 * Filter location description text..
                 *
                 * @since 1.4.0
                 *
                 * @param string $seo_desc The location description text.
                 * @param string $gd_country The current country slug.
                 * @param string $gd_region The current region slug.
                 * @param string $gd_city The current city slug.
                 */
                $location_desc = apply_filters('geodir_location_description',$seo_desc,$gd_country,$gd_region,$gd_city);
                if ($location_desc=='') {
                    return NULL;
                }
                
                $title = empty($instance['title']) ? __('Location Description', 'geodirectory') : apply_filters('geodir_location_description_widget_title', __($instance['title'], 'geodirectory'));
                $title = str_replace('%s', $location_title, $title);
                $title = str_replace('%%location%%', $location_title, $title);
                $title = str_replace('%location%', $location_title, $title);
                
                echo $before_widget;
                if ( ! empty( $title ) ) {
                    echo $before_title . __($title) . $after_title;
                }
                $location_desc = str_replace('%%location%%', $location_title, $location_desc);
                $location_desc = str_replace('%location%', $location_title, $location_desc);
                echo '<div class="geodir-category-list-in clearfix geodir-location-desc">' . $location_desc . '</div>';
                echo $after_widget;
            }
            
            public function update($new_instance, $old_instance) {
                
                $instance = $old_instance;
                $instance['title'] = strip_tags($new_instance['title']);
                return $instance;
            }
            
            public function form($instance)
            {
                $instance = wp_parse_args( (array) $instance, array('title' => ''));
                
                $title = strip_tags($instance['title']);

                ?>
                <p>
                  <label for="<?php echo $this->get_field_id('title'); ?>">
                  <?php _e('Title:', 'geodirlocation');?>
                  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
                  </label>
                </p>
                <?php  
            } 
        }
        register_widget('geodir_location_description');
    }
}

// =============================== Near Me Widget ======================================
/**
 * Class geodir_near_me_button_widget
 */
class geodir_near_me_button_widget extends WP_Widget {
    /**
     *
     */
    function __construct() {
        $widget_ops = array('classname' => 'geodir_near_me_button', 'description' =>__('GD > Near Me Button', 'geodirectory') );
        parent::__construct(
            'geodir_near_me_button', // Base ID
            __('GD > Near Me Button', 'geodirlocation'), // Name
            $widget_ops// Args
        );
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
		// prints the widget
		extract($args, EXTR_SKIP );
		
		$title = empty( $instance['title'] ) ? __( 'Near Me', 'geodirectory' ) : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );
		//$count = empty( $instance['count'] ) ? '5' : apply_filters( 'widget_count', $instance['count'] );
		
		//$comments_li = geodir_get_recent_reviews( 30, $count, 100, false );?>
<script type="text/javascript">
gdShareLocationOptions = {
	enableHighAccuracy: true,
	timeout: 5000,
	maximumAge: 0
};
function gdGetLocationNearMe() {
	//jQuery('.snear').removeClass("near-country near-region near-city");// remove any location classes
	//if(box && box.prop('checked') != true){gdClearUserLoc();return;}
	// Try HTML5 geolocation
	if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			lat = position.coords.latitude;
			lon = position.coords.longitude;
			my_location = 1;
			if(typeof gdSetupUserLoc === 'function') {
				gdSetupUserLoc();
			} else {
				gdLocationSetupUserLoc();
			}
			jQuery.ajax({
				// url: url,
				url: "<?php echo admin_url('admin-ajax.php'); ?>",
				type: 'POST',
				dataType: 'html',
				data: {
					action: 'gd_location_manager_set_user_location',
					lat: lat,
					lon: lon,
					myloc: 1
				},
				beforeSend: function() {},
				success: function(data, textStatus, xhr) {
					window.location.href = "<?php echo geodir_get_location_link('base').'me/';?>";
				},
				error: function(xhr, textStatus, errorThrown) {
					alert(textStatus);
				}
			});
		}, gdShareLocationError, gdShareLocationOptions);
	} else {
		// Browser doesn't support Geolocation
		alert(geodir_location_all_js_msg.DEFAUTL_ERROR);
	}
}
</script>
		
			<?php
			echo $before_widget;
			?>
			<button type="button" onclick=" gdGetLocationNearMe();"><?php echo $title;?></button>
			<?php 
			echo $after_widget;
		
	}

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance) {
	//save the widget
		$instance = $old_instance;		
		$instance['title'] = strip_tags($new_instance['title']);
 		return $instance;
	}

    /**
     * @param array $instance
     */
    public function form($instance) {
	//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );		
		$title = stripslashes(strip_tags($instance['title']));
 ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        
<?php
	}}
	

add_action('widgets_init', 'register_geodir_near_me_button_widget');
/**
 *
 */
function register_geodir_near_me_button_widget() {
   register_widget('geodir_near_me_button_widget');
}

/**
 * Get the popular location widget content.
 *
 * @since 1.5.0
 *
 * @param array $args The widget parameters. 
 * @param bool $echo If true it prints output else return content. Default true.
 * @return string The popular locations content.
 */
function geodir_popular_location_widget_output($args = array(), $echo = true) {
    $widget_atts = isset($args['widget_atts']) ? $args['widget_atts'] : $args;
    
    // get all the cities in current region
    $location_args = array(
                'what' => 'city',
                'city_val' => '', 
                'region_val' => '',
                'country_val' =>'',
                'country_non_restricted' =>'',
                'region_non_restricted' =>'',
                'city_non_restricted' =>'',
                'filter_by_non_restricted' => true, 
                'compare_operator' =>'like',
                'country_column_name' => 'country_slug',
                'region_column_name' => 'region_slug',
                'city_column_name' => 'city_slug',
                'location_link_part' => true,
                'order_by' => ' asc ',
                'no_of_records' => '',
                'format' => array( 
                                'type' => 'list',
                                'container_wrapper' => 'ul',
                                'container_wrapper_attr' => '',
                                'item_wrapper' => 'li',
                                'item_wrapper_attr' => ''
                            )
            );
            
    $args = wp_parse_args($args, $location_args);
    
    $per_page = !empty($args['per_page']) ? absint($args['per_page']) : '';
    $top_pagination = !empty($args['pagi_t']) ? true : false;
    $bottom_pagination = !empty($args['pagi_b']) ? true : false;
    $pagination_info = !empty($args['pagi_info']) ? $args['pagi_info'] : '';
    $no_location_filter = !empty($args['no_loc']) ? true : false;

    $args['no_of_records'] = $per_page;
    $args['counts_only'] = true;
    
    if (!$no_location_filter) {
        $location_terms = geodir_get_current_location_terms() ; // locations in sessions
        
        if (!empty($location_terms)) {
            if (isset($location_terms['gd_region']) && $location_terms['gd_region'] != '') {
                $args['region_val']= isset($location_terms['gd_region']) ? $location_terms['gd_region'] : '';
                $args['country_val']= isset($location_terms['gd_country']) ? $location_terms['gd_country'] : '';
            } else if(isset($location_terms['gd_country']) && $location_terms['gd_country'] != '') {
                $args['country_val']= $location_terms['gd_country'] ;
            }
        }
    }
    
    $total = geodir_get_location_array($args, false);
    
    $geodir_ajax = !empty($args['geodir_ajax']) ? true : false;
        
    if ($total > 0) {
        $identifier = ' gd-wgt-pagi-' . mt_rand();
        $pageno = $geodir_ajax && !empty($args['pageno']) ? $args['pageno'] : 1;
    
        $pagi_args = array(
                    'pagination_info' => __('Showing locations %1$s-%2$s of %3$s', 'geodirlocation'),
                    'more_info' => $pagination_info,
                    'class' => 'gd-pagi-pop-loc',
                );
                
        $content = '';
        $args['counts_only'] = false;
        $args['spage'] = $pageno > 0 ? $pageno - 1 : 0;
        
        if (!$geodir_ajax) {
            $content .= '<div class="gd-rows-popular-locations' . $identifier . '">';
        }
        
        if ($per_page > 0 && $top_pagination) {
            $content .= geodir_popular_location_pagination($total, $per_page, $pageno, $pagi_args);
        }
        
        $content .= geodir_get_location_array($args, false);
        
        if ($per_page > 0 && $bottom_pagination) {
            $content .= geodir_popular_location_pagination($total, $per_page, $pageno, $pagi_args);
        }
        
        if (!$geodir_ajax) {
            $content .= '</div><p style="display:none;" class="gd-ajax-wgt-loading"><i class="fa fa-cog fa-spin"></i></p>';
ob_start();
?>
<script type="text/javascript">
jQuery(document).on('click', '.<?php echo trim($identifier);?> .gd-wgt-page', function(e) {
    var obj = this;
    var pid = parseInt(jQuery(this).data('page'));
    var container = jQuery(obj).closest('.gd-rows-popular-locations');
    var loading = jQuery('.gd-ajax-wgt-loading', jQuery(container).closest('.geodir-widget'));
    
    if (!pid > 0 || !(container && typeof container != 'undefined')) {
        return false;
    }
    
    var scatts = "<?php echo addslashes(json_encode($widget_atts));?>";
    
    var data = {
        'action': 'gd_popular_location_list',
        '_nonce': '<?php echo wp_create_nonce("geodir-popular-location-nonce");?>',
        'geodir_ajax': true,
        'pageno': pid,
        'scatts': scatts,
    };
    
    jQuery(document).ajaxStop(function() {
        jQuery('ul', container).css({'opacity': '1'});
        loading.hide();
    });

    jQuery('ul', container).css({'opacity': '0.4'});
    loading.show();

    jQuery.post(geodir_var.geodir_ajax_url, data, function(response) {
        if (response && response != '0') {
            loading.hide();
            jQuery(container).html(response);
        }
    });
});
</script>
<?php
        $content .= ob_get_clean();
        }
    } else {
        $content = apply_filters('geodir_popular_location_widget_no_location', '', $args);
    }
    
    if (!$echo) {
        return $content;
    }
    
    echo $content;
}

/**
 * Get the popular location list by using ajax request.
 *
 * @since 1.5.0
 *
 * @return string Locations HTML content.
 */
function geodir_ajax_popular_location_list() {
    check_ajax_referer('geodir-popular-location-nonce', '_nonce');
    
    //set variables
    $scatts = isset($_POST['scatts']) ? $_POST['scatts'] : NULL;
    $pageno = isset($_POST['pageno']) ? absint($_POST['pageno']) : 1;
    
    $widget_atts = !empty($scatts) ? (array)json_decode(stripslashes_deep($scatts), true) : NULL;

    if (!empty($widget_atts) && is_array($widget_atts)) {
        $widget_atts['pageno'] = $pageno;
        $widget_atts['geodir_ajax'] = true;
        $widget_atts['widget_atts'] = $widget_atts;
        
        geodir_popular_location_widget_output($widget_atts, true);
    } else {
        echo 0;
    }
    
    wp_die();
}
add_action('wp_ajax_gd_popular_location_list', 'geodir_ajax_popular_location_list');
add_action('wp_ajax_nopriv_gd_popular_location_list', 'geodir_ajax_popular_location_list');

/**
 * Get the popular location pagination.
 *
 * @since 1.5.0
 *
 * @param int $total Total number of results.
 * @param int $per_page Total number of results per each page.
 * @param int $pageno Current page number.
 * @param array $params Extra pagination parameters. 
 * @return string Pagination HTML content.
 */
function geodir_popular_location_pagination($total, $per_page, $pageno, $params = array()) {
    $defaults = array(
                    'more_info' => '',
                    'pagination_info' => __('Showing locations %1$s-%2$s of %3$s', 'geodirlocation'),
                    'before' => '',
                    'after' => '',
                    'prelabel' => '',
                    'nxtlabel' => '',
                    'pages_to_show' => 5,
                    'always_show' => false,
                    'class' => 'gd-widget-pagination',
                    'pagi_function' => 'gd_popular_location_gopage',
                );
                
    $params = wp_parse_args($params, $defaults);
    $params = apply_filters('geodir_popular_location_pagination_params', $params, $total, $per_page, $pageno);

    $more_info = $params['more_info'];
    $pagination_info = $params['pagination_info'];
    $before = $params['before'];
    $after = $params['after'];
    $prelabel = $params['prelabel'];
    $nxtlabel = $params['nxtlabel'];
    $pages_to_show = $params['pages_to_show'];
    $always_show = $params['always_show'];
    $class = !empty($params['class']) ? sanitize_html_class($params['class']) : '';

    if (empty($prelabel)) {
        $prelabel = '<strong>&laquo;</strong>';
    }

    if (empty($nxtlabel)) {
        $nxtlabel = '<strong>&raquo;</strong>';
    }

    $half_pages_to_show = round($pages_to_show / 2);

    $max_page = ceil($total / $per_page);

    if (empty($pageno)) {
        $pageno = 1;
    }

    ob_start();
    if ($max_page > 1 || $always_show) {
        $start_no = ( $pageno - 1 ) * $per_page + 1;
        $end_no = min($pageno * $per_page, $total);
        
        if ($more_info != '' && !empty($pagination_info)) {
            $pagination_info = '<div class="gd-pagination-details gd-pagination-details-' . $more_info . '">' . wp_sprintf($pagination_info, $start_no, $end_no, $total) . '</div>';
            
            if ($more_info == 'before') {
                $before = $before . $pagination_info;
            } else if ($more_info == 'after') {
                $after = $pagination_info . $after;
            }
        }
            
        echo "<div class='gd-pagi-container'> $before <div class='Navi geodir-ajax-pagination " . $class . "'>";
        if ($pageno > 1) {
            echo '<a class="gd-page-sc-fst gd-wgt-page" data-page="1" href="javascript:void(0);">&laquo;</a>&nbsp;';
        }
        
        if (($pageno - 1) > 0) {
            echo '<a class="gd-page-sc-prev gd-wgt-page" href="javascript:void(0);">' . $prelabel . '</a>&nbsp;';
        }
        
        for ($i = $pageno - $half_pages_to_show; $i <= $pageno + $half_pages_to_show; $i++) {
            if ($i >= 1 && $i <= $max_page) {
                if ($i == $pageno) {
                    echo "<strong class='on' class='gd-page-sc-act'>$i</strong>";
                } else {
                    echo ' <a class="gd-page-sc-no gd-wgt-page" data-page="' . (int)$i . '" href="javascript:void(0);">' . $i . '</a> ';
                }
            }
        }
        
        if (($pageno + 1) <= $max_page) {
            echo '&nbsp;<a class="gd-page-sc-nxt gd-wgt-page" data-page="' . (int)($pageno + 1) . '" href="javascript:void(0);">' . $nxtlabel . '</a>';
        }
        
        if ($pageno < $max_page) {
            echo '&nbsp;<a class="gd-page-sc-lst gd-wgt-page" data-page="' . (int)$max_page . '" href="javascript:void(0);">&raquo;</a>';
        }
        echo "</div> $after </div>";
    }
    $output = ob_get_contents();
    ob_end_clean();

    return trim($output);
}