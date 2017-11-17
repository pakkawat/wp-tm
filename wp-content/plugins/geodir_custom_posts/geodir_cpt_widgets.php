<?php 
if(!function_exists('register_geodir_cpt_widgets')){
	function register_geodir_cpt_widgets(){	
		/**
		* Geodirectory CPT image listings widget *
		**/
		class geodir_cpt_listings extends WP_Widget {

            function __construct() {
                $widget_ops = array('classname' => 'geodir_cpt_listings', 'description' => __('GD > GeoDirectory Custom Post Type Listings', 'geodir_custom_posts') );
                parent::__construct(
                    'cpt_listings', // Base ID
                    __('GD > CPT Listings', 'geodir_custom_posts'), // Name
                    $widget_ops// Args
                );
            }
			
			public function widget($args, $instance)  {
				global $wp, $load_cpt_widget;
				extract($args, EXTR_SKIP);
				
				$title = empty($instance['title']) ? __('GD Listings','geodir_custom_posts') : apply_filters('geodir_cpt_widget_title', __($instance['title'],'geodir_custom_posts'));
				$cpt_img_width = !isset($instance['cpt_img_width']) ? 90 : apply_filters('geodir_cpt_widget_img_width', $instance['cpt_img_width']);
				$cpt_img_height = !isset($instance['cpt_img_height']) ? 90 : apply_filters('geodir_cpt_widget_img_height', $instance['cpt_img_height']);
				$cpt_hide_name = !isset($instance['cpt_hide_name']) ? false : apply_filters('geodir_cpt_widget_hide_name', $instance['cpt_hide_name']);
				$cpt_exclude = empty($instance['cpt_exclude']) ? array() : apply_filters('geodir_cpt_widget_exclude', $instance['cpt_exclude']);
				
				$post_types = geodir_get_posttypes('array');
				
				// Exclude CPT to hide from display.
				if ( !empty( $cpt_exclude ) ) {
					foreach ( $cpt_exclude as $cpt ) {
						if ( isset( $post_types[$cpt] ) )
							unset( $post_types[$cpt] );
					}
				}
				
				if ( empty( $post_types ) ) {
					return; // If no CPT to display
				}
				
				echo $before_widget;
				
				$img_width = geodir_parse_style_width($cpt_img_width);
				$img_height = geodir_parse_style_width($cpt_img_height);
				
				if ($img_width !== '' && strpos($img_width, '%') !== false) {
					$img_width = 'calc(' . $img_width . ' - 8px)';
				}
				
				$img_width = $img_width !== '' ? "width:" . $img_width . ";" : '';
				$img_height = $img_height !== '' ? "height:" . $img_height . ";" : '';

				?>
				<?php echo $before_title.__($title).$after_title;?>
				<div class="gd-cpt-widget-box clearfix">
					<div class="gd-cpt-widget-list clearfix">
					<?php
					foreach ($post_types as $cpt => $cpt_info ) {
						$cpt_name = $cpt_info['labels']['name'];
						$cpt_url = get_post_type_archive_link($cpt);
						$image_url = get_option('geodir_cpt_img_' . $cpt);
						$image_url = apply_filters('geodir_cpt_img_url', $image_url, $cpt);
						$cpt_image = $image_url ? '<img alt="' . esc_attr($cpt_name) . '" class="gd-cpt-img" src="' . $image_url . '"/>' : ($cpt_hide_name ? $cpt_name : '');
						$show_cpt_name = !$cpt_hide_name ? '<div class="gd-cpt-name">' . $cpt_name . '</div>' : '';
						
						echo '<div class="gd-cpt-wrow gd-cpt-wrow-'.$cpt.' clearfix" style="' . $img_width . $img_height . '"><a href="' . esc_url($cpt_url) . '" title="' . esc_attr($cpt_name) . '">' . $cpt_image . $show_cpt_name . '</a></div>';
					}
					?>
					</div>
				</div>
				<?php
				$load_cpt_widget = true;
				echo $after_widget;
			}
			
			public function update($new_instance, $old_instance) {
				$instance = $old_instance;												
				$instance['title'] = strip_tags($new_instance['title']);
				$instance['cpt_exclude'] = isset($new_instance['cpt_exclude']) ? $new_instance['cpt_exclude'] : '';
				$instance['cpt_img_width'] = geodir_parse_style_width($new_instance['cpt_img_width']);
				$instance['cpt_img_height'] = geodir_parse_style_width($new_instance['cpt_img_height']);
				$instance['cpt_hide_name'] = isset($new_instance['cpt_hide_name']) ? (bool)$new_instance['cpt_hide_name'] : false;
				return $instance;
			}
			
			public function form($instance) {
				$instance = wp_parse_args( (array)$instance, array( 'title' => '', 'cpt_exclude' => array(), 'cpt_img_width' => 90, 'cpt_img_height' => 90, 'cpt_hide_name' => false ) );
				
				$title = strip_tags($instance['title']);
				$cpt_exclude = $instance['cpt_exclude'];
				$cpt_img_width = geodir_parse_style_width($instance['cpt_img_width']);
				$cpt_img_height = geodir_parse_style_width($instance['cpt_img_height']);
				$cpt_hide_name = (bool)$instance['cpt_hide_name'];
				$cpt_img_width = geodir_parse_style_width($cpt_img_width);
				$cpt_img_height = geodir_parse_style_width($cpt_img_height);
				
				$post_types = geodir_get_posttypes( 'array' );
				?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodir_custom_posts');?>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
					</label>
				</p>
				<p id="wgt_cpt_exclude" style="margin-bottom:3px">
					<label for="<?php echo $this->get_field_id('cpt_exclude'); ?>"><?php _e('Exclude CPT:', 'geodir_custom_posts');?>
					<?php if ( !empty( $post_types ) ) { foreach ( $post_types as $post_type => $cpt_info ) { $checked = !empty( $cpt_exclude ) && in_array( $post_type, $cpt_exclude ) ? 'checked="checked"' : ''; $cpt_name = __( $cpt_info['labels']['name'], 'geodir_custom_posts' ); ?>
					<p style="margin:0;padding:0 0 0 20px">
					<label for="<?php echo $this->get_field_id('cpt_exclude');?>_<?php echo $post_type;?>">
					<input type="checkbox" id="<?php echo $this->get_field_id('cpt_exclude');?>_<?php echo $post_type;?>" name="<?php echo $this->get_field_name('cpt_exclude'); ?>[]" <?php echo $checked;?> value="<?php echo $post_type;?>"/>&nbsp;<?php echo wp_sprintf( __( 'Exclude %s', 'geodir_custom_posts' ), $cpt_name );?>
					</label>
					</p>
					<?php } } ?>
					</label>
				</p>
				<p style="padding:0" class="description"><?php _e('Exclude CPT to hide from CPT listings.', 'geodir_custom_posts');?></p>
				<p>
				  <label for="<?php echo $this->get_field_id('cpt_img_width'); ?>">
				  <?php _e('Image width:', 'geodir_custom_posts');?>
				  <input class="widefat" id="<?php echo $this->get_field_id('cpt_img_width'); ?>" name="<?php echo $this->get_field_name('cpt_img_width'); ?>" type="text" value="<?php echo $cpt_img_width; ?>"/>
				  </label>
				</p>
				<p style="padding:0" class="description"><?php _e('Width of image to display in widget. Ex: 90px, 25%, auto', 'geodir_custom_posts');?></p>
				<p>
				  <label for="<?php echo $this->get_field_id('cpt_img_height'); ?>">
				  <?php _e('Image height:', 'geodir_custom_posts');?>
				  <input class="widefat" id="<?php echo $this->get_field_id('cpt_img_height'); ?>" name="<?php echo $this->get_field_name('cpt_img_height'); ?>" type="text" value="<?php echo $cpt_img_height; ?>"/>
				  </label>
				</p>
				<p style="padding:0" class="description"><?php _e('Height of image to display in widget. Ex: 90px, auto', 'geodir_custom_posts');?></p>
				<p>
					<label for="<?php echo $this->get_field_id('cpt_hide_name'); ?>">
						<?php _e('Hide CPT name:', 'geodir_custom_posts');?>
						<input type="checkbox" id="<?php echo $this->get_field_id('cpt_hide_name'); ?>" name="<?php echo $this->get_field_name('cpt_hide_name'); ?>" <?php if ($cpt_hide_name) echo 'checked="checked"';?> value="1"/>
					</label>
				</p>
				<p style="padding:0" class="description"><?php _e('If checked then custom post type name will not displayed.', 'geodir_custom_posts');?></p>
				
				<?php  
			} 
		}
		register_widget('geodir_cpt_listings');
	}
}

/**
 * The CPT listings widget shortcode.
 *
 * This implements the functionality of the CPT categories widget shortcode for displaying
 * all CPT listings.
 *
 * @since 1.2.3
 *
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $title         The title of the widget displayed.
 *     @type string $cpt_exclude   Post type to exclude. Default empty.
 *     @type float  $cpt_img_width CPT thumb image width(in px or % or auto or inherit). Default 90.
 *     @type float  $cpt_img_height CPT thumb image height(in px or % or auto or inherit). Default 90.
 *     @type bool   $cpt_hide_name Hide CPT name? Default FALSE.
 *     @type string $before_widget HTML content to prepend to each widget's HTML output.
 *                                 Default is an opening list item element.
 *     @type string $after_widget  HTML content to append to each widget's HTML output.
 *                                 Default is a closing list item element.
 *     @type string $before_title  HTML content to prepend to the widget title when displayed.
 *                                 Default is an opening h3 element.
 *     @type string $after_title   HTML content to append to the widget title when displayed.
 *                                 Default is a closing h3 element.
 * }
 * @param string $content The enclosed content. Optional.
 * @return string HTML content to display CPT listings.
 */
function geodir_sc_cpt_listings_widget($atts, $content = '') {
	$defaults = array(
		'title' => '',
		'cpt_exclude' => '',
		'cpt_img_width' => 90,
		'cpt_img_height' => 90,
		'cpt_hide_name' => FALSE,
		'before_widget' => '<section id="cpt_listings-1" class="widget geodir-widget geodir_cpt_listings geodir_sc_cpt_listings">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
	);
	$params = shortcode_atts($defaults, $atts);

    /**
     * Validate our incoming params
     */
	// Make sure we have an array
    $params['cpt_exclude'] = !is_array($params['cpt_exclude']) && trim($params['cpt_exclude']) != '' ? explode(',', trim($params['cpt_exclude'])) : array();
	 
	$params['cpt_img_width'] = geodir_parse_style_width($params['cpt_img_width']);
	$params['cpt_img_height'] = geodir_parse_style_width($params['cpt_img_height']);
    $params['cpt_hide_name'] = gdsc_to_bool_val($params['cpt_hide_name']);
	
	ob_start();
	the_widget('geodir_cpt_listings', $params, $params);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}
add_shortcode('gd_cpt_listings', 'geodir_sc_cpt_listings_widget');

/**
 * Validate & get the valid style width.
 *
 * @since 1.2.6
 *
 * @param string $width Width.
 * @return float|null Valid width or empty value.
 */
function geodir_parse_style_width($width) {
    if ($width !== '') {
		$width = geodir_strtolower($width);
		
		if (strpos($width, 'auto') !== false) {
			$width = 'auto';
		} else if (strpos($width, 'inherit') !== false) {
			$width = 'inherit';
		} else if (strpos($width, '%') !== false) {
			$width = abs((float)$width) . '%';
		} else if (strpos($width, 'px') !== false || abs((float)$width) > 0) {
			$width = abs((float)$width) . 'px';
		} else {
			$width = '';
		}
	}
	
    return $width;
}
