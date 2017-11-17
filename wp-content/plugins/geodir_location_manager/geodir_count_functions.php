<?php
/**
 * Term and review count, common functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 */

/**
 * Get review count or term count.
 *
 * @since 1.0.0
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int|string $term_id The term ID.
 * @param string $taxonomy Taxonomy slug.
 * @param string $post_type The post type.
 * @param string $location_type Location type. Possible values 'gd_city','gd_region','gd_country'.
 * @param array $loc {
 *    Attributes of the location array.
 *
 *    @type string $gd_country The country slug.
 *    @type string $gd_region The region slug.
 *    @type string $gd_city The city slug.
 *
 * }
 * @param string $count_type Count type. Possible values are 'review_count', 'term_count'.
 * @return int|null|string
 */
function geodir_filter_listings_where_set_loc( $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type ) {
	global $wpdb, $plugin_prefix;

	$table = $plugin_prefix . $post_type . '_detail';

    if(!$loc){
        $loc = geodir_get_current_location_terms();
    }

	$country ='';
	$region ='';
	$city = '';
	$neighbourhood = '';
	if (isset($loc['gd_city']) && $loc['gd_city'] != '') {
		$city = $loc['gd_city'];
	}
	if (isset($loc['gd_region']) && $loc['gd_region'] != '') {
		$region = $loc['gd_region'];
	}
	if (isset($loc['gd_country']) && $loc['gd_country'] != '') {
		$country = $loc['gd_country'];
	}
	if ($city != '' && isset($loc['gd_neighbourhood']) && $loc['gd_neighbourhood'] != '') {
		$location_type = 'gd_neighbourhood';
		$neighbourhood = $loc['gd_neighbourhood'];
	}

	$where = '';
	if ( $country!= '') {
		$where .= " AND post_locations LIKE '%,[".$country."]' ";
	}

	if ( $region != '' && $location_type!='gd_country' ) {
		$where .= " AND post_locations LIKE '%,[".$region."],%' ";
	}

	if ( $city != '' && $location_type!='gd_country' && $location_type!='gd_region' ) {
		$where .= " AND post_locations LIKE '[".$city."],%' ";
	}
	
	if ($location_type == 'gd_neighbourhood' && $neighbourhood != '' && $wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = 'post_neighbourhood'")) {
		$where .= " AND post_neighbourhood = '" . $neighbourhood . "' ";
	}

	if ($count_type == 'review_count') {
		$sql = "SELECT COALESCE(SUM(rating_count),0) FROM  $table WHERE post_status = 'publish' $where AND FIND_IN_SET(" . $term_id . ", " . $taxonomy . ")";
	} else {
		$sql = "SELECT COUNT(post_id) FROM  $table WHERE post_status = 'publish' $where AND FIND_IN_SET(" . $term_id . ", " . $taxonomy . ")";
	}
	/**
	 * Filter terms count sql query.
	 *
	 * @since 1.3.8
	 * @param string $sql Database sql query..
	 * @param int $term_id The term ID.
	 * @param int $taxonomy The taxonomy Id.
	 * @param string $post_type The post type.
	 * @param string $location_type Location type .
	 * @param string $loc Current location terms.
	 * @param string $count_type The term count type.
	 */
	$sql = apply_filters('geodir_location_count_reviews_by_term_sql', $sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type);

	$count = $wpdb->get_var($sql);

	return $count;
    //todo: Following code is unreachable. remove it if not necessary.
    $count = 0;
	if ($count_type == 'review_count') {
		foreach($rows as $post) {
			$count = $count + $post->comment_count;
		}
	} elseif ($count_type == 'term_count') {
		$count = count($rows);
	}

	return $count;
}

/**
 * Insert term count for a location.
 *
 * @since 1.0.0
 * @since 1.4.1 Fix term data count for multiple location names with same name.
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @since 1.4.7 Fixed add listing page load time.
 * @since 1.5.0 Fixed location terms count for WPML languages.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global array $gd_update_terms The post term ids.
 * @global string $gd_term_post_type The post type.
 *
 * @param $location_name
 * @param string $location_type Location type. Possible values 'gd_city','gd_region','gd_country'.
 * @param array $loc {
 *    Attributes of the location array.
 *
 *    @type string $gd_country The country slug.
 *    @type string $gd_region The region slug.
 *    @type string $gd_city The city slug.
 *
 * }
 * @param string $count_type Count type. Possible values are 'review_count', 'term_count'.
 * @param null $row_id
 * @return array
 */
function geodir_insert_term_count_by_loc($location_name, $location_type, $loc, $count_type, $row_id=null) {
    global $wpdb, $gd_update_terms, $gd_term_post_type, $sitepress;
    
    $wpml = geodir_is_wpml() ? true : false;

    if (!empty($gd_update_terms) && $gd_term_post_type && $row_id > 0) {
        $term_array = $wpdb->get_var($wpdb->prepare( "SELECT `" . $count_type . "` FROM " . GEODIR_TERM_META . " WHERE id=%d", array($row_id)));
        $term_array = (array)maybe_unserialize($term_array);
        
        if (!empty($gd_update_terms)) {
            foreach ($gd_update_terms as $term_id) {
                if ($term_id > 0) {
                    $term_array[$term_id] = geodir_filter_listings_where_set_loc($term_id, $gd_term_post_type . 'category', $gd_term_post_type, $location_type, $loc, $count_type);
                }
            }
        }
    } else {
        $post_types = geodir_get_posttypes();
        if (geodir_is_wpml() && $row_id > 0) {
            $term_array = $wpdb->get_var($wpdb->prepare( "SELECT `" . $count_type . "` FROM " . GEODIR_TERM_META . " WHERE id=%d", array($row_id)));
            $term_array = (array)maybe_unserialize($term_array);
        } else {
            $term_array = array();
        }
        
        foreach($post_types as $post_type) {
            $taxonomy = geodir_get_taxonomies($post_type);
            $taxonomy = $taxonomy[0];

            $args = array(
                'hide_empty' => false,
                'gd_no_loop' => true
            );
            
            // Remove WPML term filters
            $switch_lang = false;
            if ($wpml) {
                $current_lang = $sitepress->get_current_language();
                
                if ($current_lang != 'all') {
                    $switch_lang = $current_lang;
                    $sitepress->switch_lang('all', true);
                }
            }

            $terms = get_terms($taxonomy, $args);
            
            // Restore WPML term filters
            if ($switch_lang) {
                $sitepress->switch_lang($switch_lang, true);
            }
            foreach ($terms as $term) {
                $count = geodir_filter_listings_where_set_loc($term->term_id, $taxonomy, $post_type, $location_type, $loc, $count_type);
                $term_array[$term->term_id] = $count;
            }
        }
    }

    $data = maybe_serialize($term_array);
    
    $save_data = array();
    $save_data[$count_type] = $data;
    
    if ( $row_id ) {
        // Update term data.
        $wpdb->update(GEODIR_TERM_META, $save_data, array('id' => $row_id));
    } else {
        $gd_country = !empty($loc) && isset($loc['gd_country']) ? $loc['gd_country'] : '';
        $gd_region = !empty($loc) && isset($loc['gd_region']) ? $loc['gd_region'] : '';
        
        $save_data['location_type'] = $location_type;
        $save_data['location_name'] = urldecode($location_name);
        
        switch($location_type) {
            case 'gd_country':
                $save_data['country_slug'] = urldecode($location_name);
            break;
            case 'gd_region':
                $save_data['region_slug'] = urldecode($location_name);
                $save_data['country_slug'] = urldecode($gd_country);
            break;
            case 'gd_city':
            case 'gd_neighbourhood':
                $save_data['region_slug'] = urldecode($gd_region);
                $save_data['country_slug'] = urldecode($gd_country);
            break;
        }

        // Insert term data.
        $wpdb->insert(GEODIR_TERM_META, $save_data);
    }
    return $term_array;
}

/**
 * Get term count for a location.
 *
 * @since 1.0.0
 * @since 1.4.1 Fix term data count for multiple location names with same name.
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @package GeoDirectory_Location_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global bool $gd_use_query_vars If true then use query vars to get current location terms.
 *
 * @param string $count_type Count type. Possible values are 'review_count', 'term_count'.
 * @param null|string $location_name Location name slug. Ex: new-york.
 * @param null|string $location_type Location type. Possible values 'gd_city','gd_region','gd_country'.
 * @param bool|array $loc {
 *    Attributes of the location array.
 *
 *    @type string $gd_country The country slug.
 *    @type string $gd_region The region slug.
 *    @type string $gd_city The city slug.
 *
 * }
 * @param bool $force_update Do you want to force update? default: false.
 * @return array|mixed|void
 */
function geodir_get_loc_term_count($count_type = 'term_count', $location_name=null, $location_type=null, $loc=false, $force_update=false ) {
	global $wpdb, $gd_use_query_vars;
	
	if (!$location_name || !$location_type || empty($loc)) {
        if ($gd_use_query_vars) {
            $loc = geodir_location_get_full_current_location_terms('query_vars');
        } else {
            $loc = geodir_location_get_full_current_location_terms();
        }

        if (isset($loc['gd_city']) && $loc['gd_city'] != '') {
            $location_name = $loc['gd_city'];
            $location_type = 'gd_city';
        } elseif (isset($loc['gd_region']) && $loc['gd_region'] != '') {
            $location_name = $loc['gd_region'];
            $location_type = 'gd_region';
        } elseif (isset($loc['gd_country']) && $loc['gd_country'] != '') {
            $location_name = $loc['gd_country'];
            $location_type = 'gd_country';
        }
    }

    if ($location_name && $location_type) {
		$gd_country = isset($loc['gd_country']) ? $loc['gd_country'] : '';
		$gd_region = isset($loc['gd_region']) ? $loc['gd_region'] : '';
		
		if ($location_type == 'gd_city' && !empty($loc['gd_neighbourhood'])) {
			$location_type = 'gd_neighbourhood';
			$location_name = $location_name . '::' . $loc['gd_neighbourhood'];
		}

		$where = '';
		switch($location_type) {
			case 'gd_country':
				$where .= " AND country_slug='" . urldecode($location_name) . "'";
			break;
			case 'gd_region':
				$where .= " AND region_slug='" . urldecode($location_name) . "'";
				$where .= " AND country_slug='" . urldecode($gd_country) . "'";
			break;
			case 'gd_city':
			case 'gd_neighbourhood':
				$where .= " AND region_slug='" . urldecode($gd_region) . "'";
				$where .= " AND country_slug='" . urldecode($gd_country) . "'";
			break;
		}
		
		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_TERM_META . " WHERE location_type=%s AND location_name=%s " . $where . " LIMIT 1", array( $location_type, urldecode($location_name) ) );
        $row = $wpdb->get_row( $sql );


        if ( $row ) {
            if ( $force_update || !$row->{$count_type}) {
	            return geodir_insert_term_count_by_loc( $location_name, $location_type, $loc, $count_type, $row->id );
            } else {
                $data = maybe_unserialize( $row->{$count_type} );
                return $data;
            }
        } else {
            return geodir_insert_term_count_by_loc( $location_name, $location_type, $loc, $count_type, null );
        }
    } else {
        return;
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Term count functions
/*-----------------------------------------------------------------------------------*/

/**
 * Update post term count for the given post id.
 *
 * @since 1.0.0
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @since 1.4.7 Fixed add listing page load time.
 * @package GeoDirectory_Location_Manager
 *
 * @global array $gd_update_terms The post term ids.
 * @global string $gd_term_post_type The post type.
 *
 * @param int $post_id The post ID.
 * @param array $post {
 *    Attributes of the location array.
 *
 *    @type string $post_type The post type.
 *    @type string $post_country The country name.
 *    @type string $post_region The region name.
 *    @type string $post_city The city name.
 *
 * }
 */
function geodir_term_post_count_update($post_id, $post) {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'geodir_import_export') {
		return; //do not run if importing listings
	}
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	$geodir_posttypes = geodir_get_posttypes();

    if (!isset($post['post_type'])) {
        $post['post_type'] = get_post_type( $post_id );
    }

    if ( !wp_is_post_revision( $post_id ) && isset($post['post_type']) && in_array($post['post_type'], $geodir_posttypes )) {
		$post_locations = geodir_get_post_meta($post_id, 'post_locations');
		$post_locations = $post_locations != '' ? explode(',', $post_locations) : '';
		
		if (count($post_locations) < 3) {
			return;
		}
        
        global $gd_update_terms, $gd_term_post_type;
        $gd_term_post_type = get_post_type($post_id);
        $terms = wp_get_object_terms($post_id, $gd_term_post_type . 'category', array('fields' => 'ids'));
        if (!empty($terms) && !is_wp_error($terms)) {
            $gd_update_terms = (array)$terms;
        }

		$loc = array();
		$loc['gd_city'] = str_replace( array( '[', ']' ), '', $post_locations[0] );
		$loc['gd_region'] = str_replace( array( '[', ']' ), '', $post_locations[1] );
		$loc['gd_country'] = str_replace( array( '[', ']' ), '', $post_locations[2] );

		foreach($loc as $key => $value) {
			if ($value != '') {
				geodir_get_loc_term_count('term_count', $value, $key, $loc, true);
				
				if ($key == 'gd_city' && !empty($post['post_neighbourhood'])) { // Update terms post meta count for neighbourhood also.
					$location = $loc;
					$location['gd_neighbourhood'] = $post['post_neighbourhood'];
					
					geodir_get_loc_term_count('term_count', $value . '::' . $post['post_neighbourhood'], 'gd_neighbourhood', $location, true);
				}
			}
		}
        
        unset($gd_update_terms);
        unset($gd_term_post_type);
	}
}

add_action('after_setup_theme','geodir_maybe_disable_auto_term_count_update');
/**
 * 
 */
function geodir_maybe_disable_auto_term_count_update(){
	if(!get_option('geodir_location_disable_term_auto_count')){
		add_action( 'geodir_after_save_listing', 'geodir_term_post_count_update', 100, 2);
	}
}


/**
 * Returns the term count array.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @return array|mixed|void
 */
function geodir_get_loc_term_count_filter() {
    $data = geodir_get_loc_term_count('term_count');
    return $data;
}
add_filter( 'geodir_get_term_count_array', 'geodir_get_loc_term_count_filter' );

if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
	add_filter('get_terms', 'gd_get_terms', 10, 3);
}

/**
 * Get terms with term count.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @param array $arr Term array.
 * @param string $tax Taxonomy name.
 * @param array $args GD args.
 * @return mixed
 */
function gd_get_terms($arr, $tax, $args) {
	if (isset($args['gd_no_loop'])) {
		return $arr; // so we don't do an infinite loop
	}

	if (!empty($arr)) {
		$term_count = geodir_get_loc_term_count('term_count');
		/**
		 * Filter the terms count by location.
		 *
		 * @since 1.3.4
		 *
		 * @param array $terms_count Array of term count row.
		 * @param array $terms Array of terms.
		 */
		$term_count = apply_filters( 'geodir_loc_term_count', $term_count, $arr );

        $is_everywhere = geodir_get_current_location_terms();

		foreach ($arr as $term) {
			if (isset($term->term_id) && isset($term_count[$term->term_id])) {
				$term->count = $term_count[$term->term_id];
			}elseif(isset($term->term_id) && !empty($is_everywhere)){// if we dont have a term count for it then it's probably wrong.
                $term->count = 0;
            }
		}
	}

	return $arr;
}

/*-----------------------------------------------------------------------------------*/
/*  Review count functions
/*-----------------------------------------------------------------------------------*/

/**
 * Update review count for each location.
 *
 * @since 1.0.0
 * @since 1.4.4 Updated for the neighbourhood system improvement.
 * @package GeoDirectory_Location_Manager
 *
 * @param int $post_id The post ID.
 */
function geodir_term_review_count_update($post_id) {
	$geodir_posttypes = geodir_get_posttypes();
    $post = get_post($post_id);
    
	if (isset($post->post_type) && in_array($post->post_type, $geodir_posttypes )) {
        $locations = geodir_get_post_meta( $post_id, 'post_locations' );
        if ( $locations ) {
            $array = explode( ',', $locations );

            $loc = array();
            $loc['gd_city'] = str_replace( array( '[', ']' ), '', $array[0] );
            $loc['gd_region'] = str_replace( array( '[', ']' ), '', $array[1] );
            $loc['gd_country'] = str_replace( array( '[', ']' ), '', $array[2] );


			global $gd_update_terms, $gd_term_post_type;
			$gd_term_post_type = get_post_type($post_id);
			$terms = wp_get_object_terms($post_id, $gd_term_post_type . 'category', array('fields' => 'ids'));
			if (!empty($terms) && !is_wp_error($terms)) {
				$gd_update_terms = (array)$terms;
			}

            foreach($loc as $key => $value) {
                if ($value != '') {
                    geodir_get_loc_term_count('review_count', $value, $key, $loc, true);
					
					if ($key == 'gd_city' && $gd_neighbourhood = geodir_get_post_meta($post_id, 'post_neighbourhood')) { // Update terms review meta count for neighbourhood also.
						$location = $loc;
						$location['gd_neighbourhood'] = $gd_neighbourhood;
						
						geodir_get_loc_term_count('review_count', $value . '::' . $gd_neighbourhood, 'gd_neighbourhood', $location, true);
					}
                }
            }
        }
    }
    return;
}

add_action( 'geodir_update_postrating', 'geodir_term_review_count_update', 100, 1);


/**
 * Returns the review count array.
 *
 * @since 1.0.0
 * @package GeoDirectory_Location_Manager
 *
 * @return array|mixed|void
 * @param string $blank	A empty string.
 * @param bool	$force_update If we should force an update.
 */
function geodir_get_loc_review_count_action($blank='',$force_update = false,$post_id=0) {
	if($post_id){
		$hood = geodir_get_post_meta($post_id, 'post_neighbourhood');
		$post_info['post_neighbourhood'] = ($hood) ? $hood : '';
		geodir_term_post_count_update($post_id, $post_info);
		//$force_update = false;
	}

	if($force_update ){return null;}

    $data = geodir_get_loc_term_count('review_count',null,null, false,$force_update);

    return $data;
}
add_filter( 'geodir_count_reviews_by_terms_before', 'geodir_get_loc_review_count_action',10,3 );