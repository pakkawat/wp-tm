<?php
/**
* GeoDirectory Events related posts widget *
**/

function geodir_event_register_widgets() {
	class geodir_event_related_listing_postview extends WP_Widget {

        function __construct() {
            $widget_ops = array( 'classname' => 'geodir_event_related_listing_post_view', 'description' => __( 'GD > Related Events Listing','geodirevents' ) );
            parent::__construct(
                'event_related_listing', // Base ID
                __('GD > Related Events Listing', 'geodirectory'), // Name
                $widget_ops// Args
            );
        }
		
		public function widget( $args, $instance ) {
			// prints the widget
			extract( $args, EXTR_SKIP );
			
			$post_number = empty( $instance['post_number'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_number'] );
			$layout = empty( $instance['layout'] ) ? 'gridview_onehalf' : apply_filters( 'widget_layout', $instance['layout'] );
			$event_type = empty( $instance['event_type'] ) ? 'all' : apply_filters( 'widget_event_type', $instance['event_type'] );
			$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_layout', $instance['add_location_filter'] );
			$listing_width = empty( $instance['listing_width'] ) ? '' : apply_filters( 'widget_layout', $instance['listing_width'] );
			$list_sort = empty( $instance['list_sort'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['list_sort'] );
			$character_count = isset( $instance['character_count'] ) && $instance['character_count']=='' ? 20 : apply_filters( 'widget_character_count', $instance['character_count'] );
			
			global $post;
			
			$post_id = '';
			$post_type = '';
			
			if ( isset($_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
				$post = geodir_get_post_info( $_REQUEST['pid'] );
				$post_type = $post->post_type;
				$post_id = $_REQUEST['pid'];
			} else if ( isset( $post->post_type ) && $post->post_type != '' ) {
				$post_type = $post->post_type;
				$post_id = $post->ID;
			}
			
			$all_postypes = geodir_get_posttypes();
			
			if ( !in_array( $post_type, $all_postypes ) ) {
				return false;
			}			
			if ( $post_type == 'gd_place' && $post_id != '' ) {
				$query_args = array(
									'geodir_event_type' => $event_type,
									'event_related_id' => $post_id,
									'posts_per_page' => $post_number,
									'is_geodir_loop' => true,
									'gd_location' 	 => $add_location_filter ? true : false,
									'post_type' => 'gd_event',
									'order_by' => $list_sort,
									'excerpt_length' => $character_count,
									'character_count' => $character_count,
									'listing_width' => $listing_width
								);
					
				echo $before_widget;
				echo geodir_get_post_widget_events( $query_args, $layout );
				echo $after_widget;			
			}			
		}
		
		public function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			
			$instance['post_number'] = strip_tags($new_instance['post_number']);
			$instance['layout'] = strip_tags($new_instance['layout']);
			$instance['listing_width'] = strip_tags($new_instance['listing_width']);
			$instance['list_sort'] = strip_tags($new_instance['list_sort']);
			$instance['event_type'] = isset($new_instance['event_type']) ?  $new_instance['event_type'] : '';
			$instance['character_count'] = $new_instance['character_count'];
			if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
			$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
			else
			$instance['add_location_filter'] = '0';
			
			
			return $instance;
		}
		
		public function form($instance)
		{
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, 
										array('list_sort'=>'', 
												'list_order'=>'',
												'event_type'=>'',
												'post_number' => '5',
												'layout'=> 'gridview_onehalf',
												'listing_width' => '',
												'add_location_filter'=>'1',
												'character_count'=>'20') 
									 );
			
			$list_sort = strip_tags($instance['list_sort']);
			
			$list_order = strip_tags($instance['list_order']);
			
			$event_type = $instance['event_type'];
			
			$post_number = strip_tags($instance['post_number']);
			
			$layout = strip_tags($instance['layout']);
			
			$listing_width = strip_tags($instance['listing_width']);
			
			$add_location_filter = strip_tags($instance['add_location_filter']);
			
			$character_count = $instance['character_count'];
			
			?>
				
					<p>
						<label for="<?php echo $this->get_field_id('event_type'); ?>"><?php _e('Display Events:','geodirevents');?>
							
						 <select  class="widefat" id="<?php echo $this->get_field_id('event_type'); ?>" name="<?php echo $this->get_field_name('event_type'); ?>">
															 	
								<option <?php if(isset($event_type) &&  $event_type=='feature'){ echo 'selected="selected"'; } ?> value="feature"><?php _e('Feature Events','geodirevents'); ?></option>
								
								<option <?php if(isset($event_type) && $event_type=='past'){ echo 'selected="selected"'; } ?> value="past"><?php _e('Past Events','geodirevents'); ?></option>
                                
								<option <?php if(isset($event_type) && $event_type=='upcoming' ){ echo 'selected="selected"'; } ?> value="upcoming"><?php _e('Upcoming Events','geodirevents'); ?></option>
							
							</select>
							</label>
					</p>
				 
					<p>
								<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:','geodirevents');?>
									
								 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
										
											<option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest','geodirevents'); ?></option>
										 
											 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured','geodirevents'); ?></option>
											
											<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review','geodirevents'); ?></option>
											
											<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating','geodirevents'); ?></option>
											
											<option <?php if($list_sort == 'random'){ echo 'selected="selected"'; } ?> value="random"><?php _e('Random','geodirevents'); ?></option>
											
									</select>
									</label>
							</p>
					
					<p>
					
							<label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:','geodirevents');?>
							
							<input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
							</label>
					</p>
				
					<p>
						<label for="<?php echo $this->get_field_id('layout'); ?>">
				<?php _e('Layout:','geodirevents');?>
							<select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
								<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)','geodirevents'); ?></option>
								<option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)','geodirevents'); ?></option>
								<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)','geodirevents'); ?></option>
								<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)','geodirevents'); ?></option>
								<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view','geodirevents'); ?></option>
									
							</select>    
							</label>
					</p>
					
					<p>
							<label for="<?php echo $this->get_field_id('listing_width'); ?>"><?php _e('Listing width:','geodirevents');?>
							
								<input class="widefat" id="<?php echo $this->get_field_id('listing_width'); ?>" name="<?php echo $this->get_field_name('listing_width'); ?>" type="text" value="<?php echo esc_attr($listing_width); ?>" />
							</label>
					</p>
					
					<p>
							<label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :','geodirevents');?>
							<input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
							</label>
					</p>
					
					 <p>
						<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
				<?php _e('Enable Location Filter:','geodirevents');?>
							<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
							</label>
					</p>
				
		<?php  
		} 
	}
	register_widget('geodir_event_related_listing_postview');	
	
	
	/* --- Geodir Event calender widget --- */
	
	class geodir_event_calendar_widget extends WP_Widget {

        function __construct() {
            $widget_ops = array('classname' => 'geodir_event_listing_calendar', 'description' =>  __('GD > Event Listing Calendar','geodirevents') );
            parent::__construct(
                'geodir_event_listing_calendar', // Base ID
                __('GD > Event Listing Calendar', 'geodirevents'), // Name
                $widget_ops// Args
            );
        }
		
		public function widget($args, $instance) {

			geodir_event_calendar_widget_output($args, $instance);
			
		}
		
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['day'] = strip_tags($new_instance['day']);
			$instance['week_day_format'] = (int)$new_instance['week_day_format'];
			$instance['add_location_filter'] = !empty($new_instance['add_location_filter']) ? 1 : 0;
			return $instance;
		}
		
		public function form($instance) {
			$instance =	wp_parse_args(
									(array)$instance, 
									array( 
										'title' => '',
										'day' => '',
										'week_day_format' => 0, // 0 => M, 1 => Mo, 2 => Mon, 3 => Monday
										'add_location_filter' => 0
									)
								);
					
			$title = strip_tags($instance['title']);
			$day = strip_tags($instance['day']);
			$week_day_format = (int)$instance['week_day_format'];
			$add_location_filter = !empty($instance['add_location_filter']) ? 1 : 0;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'geodirevents')?>:
				
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
					
				</label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('day'); ?>"><?php _e('Start day?', 'geodirevents');?>
					
					<select class="widefat" id="<?php echo $this->get_field_id('day'); ?>" name="<?php echo $this->get_field_name('day'); ?>">
					<option value="1" <?php if(esc_attr($day)=='1'){ echo 'selected="selected"';} ?>><?php _e('Monday', 'geodirevents');?></option>
					<option value="0" <?php if(esc_attr($day)=='0'){ echo 'selected="selected"';} ?>><?php _e('Sunday', 'geodirevents');?></option>
					</select>
					
				</label>
			</p> 
			<p>
			  <label for="<?php echo $this->get_field_id( 'week_day_format' );?>"><?php _e( 'Week day format:', 'geodirevents' );?></label>
			  <select class="widefat" id="<?php echo $this->get_field_id( 'week_day_format' ); ?>" name="<?php echo $this->get_field_name( 'week_day_format' ); ?>">
				<option value="0" <?php selected( $week_day_format, 0 ); ?>><?php _e( 'M', 'geodirevents' );?></option>
				<option value="1" <?php selected( $week_day_format, 1 ); ?>><?php _e( 'Mo', 'geodirevents' );?></option>
				<option value="2" <?php selected( $week_day_format, 2 ); ?>><?php _e( 'Mon', 'geodirevents' );?></option>
				<option value="3" <?php selected( $week_day_format, 3 ); ?>><?php _e( 'Monday', 'geodirevents' );?></option>
			  </select>
			  <small><?php _e( 'M => 1 digit, Mo = 2 digits, Mon => 3 digits, Monday => full.', 'geodirevents' );?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('add_location_filter'); ?>"><input type="checkbox" value="1" name="<?php echo $this->get_field_name('add_location_filter'); ?>" id="<?php echo $this->get_field_id('add_location_filter'); ?>" <?php checked( (int)$add_location_filter, 1 ); ?>><?php _e('Enable Location Filter', 'geodirevents');?></label>
			</p>
			<?php
		}
		
	}
	
	register_widget('geodir_event_calendar_widget');  
	
	
/* --- Geodir Event popular posts widget --- */
class geodir_event_postview extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'geodir_event_listing', 'description' => __('GD > Event Listing', 'geodirevents'));
        parent::__construct('event_post_listing', __('GD > Event Listing', 'geodirevents'), $widget_ops);
    }
	
	public function widget($args, $instance) {
		geodir_event_postview_output($args, $instance);
	}
	
	public function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		
		if ($new_instance['title'] == '') {
			$title = geodir_ucwords(strip_tags($new_instance['category_title']));
			//$instance['title'] = $title;
		}
		$instance['title'] = strip_tags($new_instance['title']);	
		
		//$instance['category'] = strip_tags($new_instance['category']);
		$instance['category'] = isset($new_instance['category']) ?  $new_instance['category'] : '';
		$instance['category_title'] = strip_tags($new_instance['category_title']);
		$instance['post_number'] = strip_tags($new_instance['post_number']);
		$instance['layout'] = strip_tags($new_instance['layout']);
		$instance['listing_width'] = strip_tags($new_instance['listing_width']);
		$instance['list_sort'] = strip_tags($new_instance['list_sort']);
		$instance['list_filter'] = strip_tags($new_instance['list_filter']);
		$instance['character_count'] = $new_instance['character_count'];
		if (isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
			$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
		else
			$instance['add_location_filter'] = '0';
		$instance['show_featured_only'] = isset($new_instance['show_featured_only']) && $new_instance['show_featured_only'] ? 1 : 0;
        $instance['show_special_only'] = isset($new_instance['show_special_only']) && $new_instance['show_special_only'] ? 1 : 0;
        $instance['with_pics_only'] = isset($new_instance['with_pics_only']) && $new_instance['with_pics_only'] ? 1 : 0;
        $instance['with_videos_only'] = isset($new_instance['with_videos_only']) && $new_instance['with_videos_only'] ? 1 : 0;
		
		return $instance;
	}
	
	public function form($instance) {
		// widget form in backend
		$instance = wp_parse_args( (array)$instance, 
									array(
										'title' => '', 
										'category' => array(),
										'category_title' => '',
										'list_sort' => '', 
										'list_filter' => '', 
										'list_order' => '',
										'post_number' => '5',
										'layout'=> 'gridview_onehalf',
										'listing_width' => '',
										'add_location_filter' => '1',
										'character_count' => '20',
										'show_featured_only' => '',
										'show_special_only' => '',
										'with_pics_only' => '',
										'with_videos_only' => ''
									)
								 );
		
		$title = strip_tags($instance['title']);
		
		$category = $instance['category'];
		
		$category_title = strip_tags($instance['category_title']);
		
		$list_sort = strip_tags($instance['list_sort']);
		
		$list_filter = strip_tags($instance['list_filter']);
		
		$list_order = strip_tags($instance['list_order']);
		
		$post_number = strip_tags($instance['post_number']);
		
		$layout = strip_tags($instance['layout']);
		
		$listing_width = strip_tags($instance['listing_width']);
		
		$add_location_filter = strip_tags($instance['add_location_filter']);
		
		$character_count = $instance['character_count'];
		$show_featured_only = isset($instance['show_featured_only']) && $instance['show_featured_only'] ? true : false;
        $show_special_only = isset($instance['show_special_only']) && $instance['show_special_only'] ? true : false;
        $with_pics_only = isset($instance['with_pics_only']) && $instance['with_pics_only'] ? true : false;
        $with_videos_only = isset($instance['with_videos_only']) && $instance['with_videos_only'] ? true : false;		
		?>        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','geodirevents');?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	
        
        <p id="post_type_cats">
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Post Category:','geodirevents');?>

         <?php 
				 
				$category_taxonomy = geodir_get_taxonomies('gd_event'); 
				$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
				
			?>
					
            <select multiple="multiple" class="widefat" name="<?php echo $this->get_field_name('category'); ?>[]" onchange="geodir_event_widget_cat_title(this)" >
            	
                <option <?php if(is_array($category)  && in_array( '0', $category)){ echo 'selected="selected"'; } ?> value="0"><?php _e('All','geodirevents'); ?></option>
				<?php foreach($categories as $category_obj){ 
					$selected = '';
					 if(is_array($category)  && in_array( $category_obj->term_id, $category))
					 	echo $selected = 'selected="selected"';
					 
					?>
            		
                    <option <?php echo $selected; ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
                
				<?php } ?>
                
            </select>
									
           <input type="hidden" name="<?php echo $this->get_field_name('category_title'); ?>" id="<?php echo $this->get_field_id('category_title'); ?>" value="<?php if($category_title != '') echo $category_title; else echo __('All','geodirevents');?>" />
					 
            </label>
        </p>
        
				<p>
							<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:','geodirevents');?>
								
							 <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
									
										<option <?php if($list_sort == 'upcoming'){ echo 'selected="selected"'; } ?> value="upcoming"><?php _e('Upcoming','geodirevents'); ?></option>
									 
										 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured','geodirevents'); ?></option>
										
										<option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review','geodirevents'); ?></option>
										
										<option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating','geodirevents'); ?></option>
										
										<option <?php if($list_sort == 'random'){ echo 'selected="selected"'; } ?> value="random"><?php _e('Random','geodirevents'); ?></option>
										
								</select>
								</label>
						</p>
				
				<p>
							<label for="<?php echo $this->get_field_id('list_filter'); ?>"><?php _e('Filter by:','geodirevents');?>
								
							 <select class="widefat" id="<?php echo $this->get_field_id('list_filter'); ?>" name="<?php echo $this->get_field_name('list_filter'); ?>">
									
										<option <?php if($list_filter == 'all'){ echo 'selected="selected"'; } ?> value="all"><?php _e('All Events','geodirevents'); ?></option>
									 
										 <option <?php if($list_filter == 'today'){ echo 'selected="selected"'; } ?> value="today"><?php _e('Today','geodirevents'); ?></option>
										
										<option <?php if($list_filter == 'upcoming'){ echo 'selected="selected"'; } ?> value="upcoming"><?php _e('Upcoming','geodirevents'); ?></option>
										
										<option <?php if($list_filter == 'past'){ echo 'selected="selected"'; } ?> value="past"><?php _e('Past','geodirevents'); ?></option>
										
								</select>
								</label>
						</p>
        
        <p>
        
            <label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:','geodirevents');?>
            
            <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
            </label>
        </p>
       
        <p>
        	<label for="<?php echo $this->get_field_id('layout'); ?>">
			<?php _e('Layout:','geodirevents');?>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
            	<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)','geodirevents'); ?></option>
              <option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)','geodirevents'); ?></option>
							<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)','geodirevents'); ?></option>
							<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)','geodirevents'); ?></option>
							<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view','geodirevents'); ?></option>
								
            </select>    
            </label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('listing_width'); ?>"><?php _e('Listing width:','geodirevents');?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('listing_width'); ?>" name="<?php echo $this->get_field_name('listing_width'); ?>" type="text" value="<?php echo esc_attr($listing_width); ?>" />
            </label>
        </p>
				
				<p>
            <label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :','geodirevents');?>
            <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
            </label>
        </p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>"<?php checked( $add_location_filter ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('add_location_filter'); ?>"><?php _e( 'Enable Location Filter:', 'geodirevents' ); ?></label>
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_featured_only'); ?>" name="<?php echo $this->get_field_name('show_featured_only'); ?>"<?php checked( $show_featured_only ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('show_featured_only'); ?>"><?php _e( 'Show only featured events:', 'geodirevents' ); ?></label>
		</p>
		<p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_special_only'); ?>" name="<?php echo $this->get_field_name('show_special_only'); ?>"<?php checked( $show_special_only ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('show_special_only'); ?>"><?php _e( 'Show only events with special offers:', 'geodirevents' ); ?></label>
		</p>
		<p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('with_pics_only'); ?>" name="<?php echo $this->get_field_name('with_pics_only'); ?>"<?php checked( $with_pics_only ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('with_pics_only'); ?>"><?php _e( 'Show only events with pics:', 'geodirevents' ); ?></label>
		</p>
		<p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('with_videos_only'); ?>" name="<?php echo $this->get_field_name('with_videos_only'); ?>"<?php checked( $with_videos_only ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('with_videos_only'); ?>"><?php _e( 'Show only events with videos:', 'geodirevents' ); ?></label>
        <p>
		<script type="text/javascript">
			function geodir_event_widget_cat_title(val) {
				jQuery(val).find("option:selected").each(function(i) {
					if (i == 0) jQuery(val).closest('form').find('#post_type_cats input').val(jQuery(this).html());
				});
			}
		</script>
	<?php  
	} 
}
register_widget('geodir_event_postview');	

}
