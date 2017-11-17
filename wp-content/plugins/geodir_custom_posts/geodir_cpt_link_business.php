<?php
// Link CPT
function geodir_get_linkable_cpt() {

    if (geodir_is_page('add-listing') && isset($_REQUEST['listing_type'])) {
        $post_type = $_REQUEST['listing_type'];
    } elseif (geodir_is_page('add-listing') && isset($_REQUEST['pid'])) {
        $post_type = get_post_type($_REQUEST['pid']);
    } else {
        global $post;
        $post_type = $post->post_type;
    }

    return $post_type;

}

add_action('geodir_payment_package_extra_fields','geodir_link_cpt_package_add_extra_fields', 3, 1);
function geodir_link_cpt_package_add_extra_fields( $priceinfo = array() ) {
    ?>
    <tr valign="top" class="single_select_page">
        <th class="titledesc" scope="row"><?php _e('CPT Features Only', 'geodir_custom_posts');?></th>
        <td class="forminp"><div class="gtd-formfield"> </div></td>
    </tr>
    <tr valign="top" class="single_select_page">
        <th class="titledesc" scope="row"><?php _e('Link CPTs', 'geodir_custom_posts');?></th>
        <td class="forminp"><div class="gtd-formfield">
                <select style="min-width:200px;" name="gd_link_business_cpt" >
                    <option value="0" <?php if((isset($priceinfo->link_business_cpt) && $priceinfo->link_business_cpt=='0')){ echo 'selected="selected"';}?> >
                        <?php _e("No", 'geodir_custom_posts');?>
                    </option>
                    <option value="1" <?php if(isset($priceinfo->link_business_cpt) && $priceinfo->link_business_cpt=='1'  || !isset($priceinfo->link_business_cpt) || $priceinfo->link_business_cpt == ''){ echo 'selected="selected"';}?> >
                        <?php _e("Yes", 'geodir_custom_posts');?>
                    </option>
                </select>
            </div></td>
    </tr>
    <?php
}

function geodir_cpt_plugin_url() {
    return plugins_url( '', __FILE__ );
    /*
    if (is_ssl()) :
        return str_replace('http://', 'https://', WP_PLUGIN_URL) . "/" . plugin_basename( dirname(__FILE__));
    else :
        return WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__));
    endif;
    */
}


function geodir_link_cpt_meta_box_add()
{
    global $post;


    if(!isset($post->post_type) || $post->post_type == 'gd_event')
        return false;

    $link_business = 0;
    $geodir_post_types = get_option( 'geodir_post_types' );
    if (isset($geodir_post_types[$post->post_type]['linkable_to']) && !empty($geodir_post_types[$post->post_type]['linkable_to'])){
        $link_business = $geodir_post_types[$post->post_type]['linkable_to'];
    }

    if (defined('GEODIRPAYMENT_VERSION')) {
        $package_info = array();
        $package_info = geodir_post_package_info($package_info , $post);
        if(isset($package_info->link_business_cpt) && $package_info->link_business_cpt  == '0'){
            $link_business = 0;
        }
    }

    if ($link_business) {
        $linked_post_types = get_option('geodir_linked_post_types');
        if (!empty($linked_post_types) && is_array($linked_post_types)) {
            $linked_post_types = array_flip($linked_post_types);
        }

        $linked_post_type = '';
        if (isset($post->post_type) && isset($linked_post_types[$post->post_type])) {
            $linked_post_type = $linked_post_types[$post->post_type];
        }

        $geodir_post_type = __( 'Businesses', 'geodir_custom_posts' );
        $geodir_post_types = get_option( 'geodir_post_types' );
        if ($linked_post_type && isset($geodir_post_types[$linked_post_type])) {
            $geodir_post_type = __($geodir_post_types[$linked_post_type]['labels']['name'], 'geodirectory');
        }

        add_meta_box('geodir_link_cpt_business', $geodir_post_type, 'geodir_link_cpt_business_setting', $post->post_type, 'side', 'high');
    }
}


function geodir_link_cpt_business_setting(){

    global $post,$post_id,$post_info;

    wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_link_cpt_business_setting_noncename' );

    do_action('geodir_link_cpt_business_fields_on_metabox');

}

function geodir_link_cpt_business_fields_html() {
    global $post,$wpdb,$current_user,$post_info, $gd_session;

    $linkable_post_type = geodir_get_linkable_cpt();

    $link_business = 0;
    $geodir_post_types = get_option( 'geodir_post_types' );
    if (isset($geodir_post_types[$linkable_post_type]['linkable_to']) && !empty($geodir_post_types[$linkable_post_type]['linkable_to'])){
        $link_business = $geodir_post_types[$linkable_post_type]['linkable_to'];
    }

    if (defined('GEODIRPAYMENT_VERSION')) {
        $package_info = array();
        $package_info = geodir_post_package_info($package_info , $post);
        if(isset($package_info->link_business_cpt) && $package_info->link_business_cpt  == '0'){
            $link_business = 0;
        }
    }

    $geodir_link_cpt_business = '';

    if ( isset( $_REQUEST['backandedit'] ) ) {
        $post = (object)$gd_session->get('listing');
        $geodir_link_cpt_business = isset($post->geodir_link_business) ? $post->geodir_link_business : '';
    } else if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
        $geodir_link_cpt_business = geodir_get_post_meta( $_REQUEST['pid'], 'geodir_link_business' );
    } else if ( isset( $post->geodir_link_business ) ) {
        $geodir_link_cpt_business = $post->geodir_link_business;
    } else if ( isset( $post_info->geodir_link_business ) ) {
        $geodir_link_cpt_business = $post_info->geodir_link_business;
    }

    if( $geodir_link_cpt_business == '' && isset( $post->ID ) ) {
        $geodir_link_cpt_business = geodir_get_post_meta( $post->ID, 'geodir_link_business' );
    }

    if ($link_business) {
        $linked_post_types = get_option('geodir_linked_post_types');
        if (!empty($linked_post_types) && is_array($linked_post_types)) {
            $linked_post_types = array_flip($linked_post_types);
        }

        $linked_post_type = '';
        if (isset($linkable_post_type) && isset($linked_post_types[$linkable_post_type])) {
            $linked_post_type = $linked_post_types[$linkable_post_type];
        }

        $geodir_link_cpt_business = apply_filters('geodir_cpt_link_business_id', $geodir_link_cpt_business, $linkable_post_type);

        $geodir_post_type = __( 'Businesses', 'geodir_custom_posts' );
        $field_title = __( 'Link Business', 'geodir_custom_posts' );
        $geodir_post_types = get_option( 'geodir_post_types' );
        
        if ($linked_post_type && isset($geodir_post_types[$linked_post_type])) {
            $geodir_post_type = __( $geodir_post_types[$linked_post_type]['labels']['name'], 'geodirectory' );
            $field_title = __( 'Link ', 'geodir_custom_posts' ) . __( $geodir_post_types[$linked_post_type]['labels']['singular_name'], 'geodirectory' );
        }

        if (!is_admin()) {
            echo '<h5>'.$geodir_post_type.'</h5>';
        }
        ?>
    <div id="geodir_link_cpt_business_row" class="geodir_form_row clearfix">
        <label>
            <?php
            echo $field_title;
            ?>
        </label>
        <div class="geodir_link_cpt_business_chosen_div" style="width:60%;float:left;margin-bottom:7px">
            <input type="hidden" name="geodir_link_cpt_business_val" value="<?php echo $geodir_link_cpt_business;?>" />
            <select id="geodir_link_cpt_business" name="geodir_link_cpt_business" class="geodir_link_cpt_business_chosen" data-post_type="<?php echo $linkable_post_type; ?>" data-location_type="link_business"data-placeholder="<?php echo esc_attr( __( 'Search business&hellip;', 'geodir_custom_posts' ) );?>" data-ajaxchosen="1"  data-addsearchtermonnorecord="0" data-autoredirect="0">
                <?php
                $selected = $geodir_link_cpt_business == '' || !$geodir_link_cpt_business > 0 ? 'selected="selected"' : '';
                $options = '<option ' . $selected . ' value=""> </option>';
                if( $geodir_link_cpt_business > 0 ) {
                    $listing_info = get_post( $geodir_link_cpt_business );
                    if( !empty( $listing_info ) ) {
                        $options .= '<option selected="selected" value="' . $geodir_link_cpt_business . '">' . $listing_info->post_title . '</option>';
                    }
                }
                echo $options;
                ?>
            </select>
            <?php $geodir_link_cpt_business = wp_create_nonce( 'geodir_link_cpt_business_autofill_nonce' );?>
            <input type="hidden" name="geodir_link_cpt_business_nonce" value="<?php echo $geodir_link_cpt_business;?>">
        </div>
        <input type="button" id="geodir_link_cpt_business_autofill" class="geodir_button button-primary" value="<?php echo __('Fill in Business Details','geodir_custom_posts'); ?>" style="float:none;margin-left:30%;" />
    </div>
    <?php
    }
}

function geodir_link_cpt_business_ajax(){

    $task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : '';
    switch( $task ) {
        case 'geodir_cpt_link_fill_listings' :
            $term = isset( $_REQUEST['term'] ) ? $_REQUEST['term'] : '';
            $post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'gd_place';
            $linked_post_type = $post_type;
            $geodir_post_types = get_option( 'geodir_post_types' );
            if (isset($geodir_post_types[$post_type]['linkable_to'])){
                $linked_post_type = $geodir_post_types[$post_type]['linkable_to'];
            }
            $post_type = $linked_post_type;
            echo geodir_cpt_link_fill_listings( $post_type, $term );
            exit;
            break;
    }

    if(isset($_REQUEST['auto_fill']) && $_REQUEST['auto_fill'] == 'geodir_cpt_business_autofill'){

        if(isset($_REQUEST['place_id']) && $_REQUEST['place_id'] != '' && isset($_REQUEST['_wpnonce']))
        {

            if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir_link_cpt_business_autofill_nonce' ) )
                exit;

            geodir_cpt_business_auto_fill($_REQUEST);
            exit;

        }else{

            wp_redirect(geodir_login_url());
            exit();
        }

    }

}

function geodir_cpt_link_biz_ajax_url(){
    return admin_url('admin-ajax.php?action=geodir_link_cpt_business_ajax');
}



add_action( 'add_meta_boxes', 'geodir_link_cpt_meta_box_add' );

add_action('geodir_link_cpt_business_fields_on_metabox', 'geodir_link_cpt_business_fields_html');

add_action('wp_footer','geodir_link_cpt_localize_vars',10);
add_action('admin_footer','geodir_link_cpt_localize_vars',10);
function geodir_link_cpt_localize_vars()
{
    global $pagenow;

    if(geodir_is_page('add-listing') || $pagenow == 'post.php' || $pagenow == 'post-new.php'){

        $arr_alert_msg = array(
            'geodir_cpt_link_ajax_url' => geodir_cpt_link_biz_ajax_url(),
            'CPT_LINK_PLEASE_WAIT' =>__( 'Please wait...', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_NO_RESULT_TEXT' =>__( 'No Business', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_SELECT_BUSINESS' =>__( 'Select Business', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_KEEP_TYPE_TEXT' =>__( 'Please wait...', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_LOOKING_FOR_TEXT' =>__( 'We are searching for', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_NO_RESULTS_MATCH_TEXT' =>__( 'No results match', 'geodir_custom_posts' ),
            'CPT_LINK_CHOSEN_SEARCHING' => __( 'Searching...', 'geodir_custom_posts' ),
        );

        foreach ( $arr_alert_msg as $key => $value )
        {
            if ( !is_scalar($value) )
                continue;
            $arr_alert_msg[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
        }

        $script = "var geodir_cpt_link_alert_js_var = " . json_encode($arr_alert_msg) . ';';
        echo '<script>';
        echo $script ;
        echo '</script>';

    }

}

add_action( 'wp_enqueue_scripts', 'geodir_cpt_link_templates_script' );
function geodir_cpt_link_templates_script() {
    if (is_page() && geodir_is_page('add-listing')) {
        wp_register_style('geodir-link-business-style', geodir_cpt_plugin_url() . '/css/link-business.css', array(), GEODIR_CP_VERSION);
        wp_enqueue_style('geodir-link-business-style');
        
        wp_register_script('geodir-link-business-js', geodir_cpt_plugin_url() . '/js/link_business.js', array ('jquery'), GEODIR_CP_VERSION);
        wp_enqueue_script('geodir-link-business-js');
    }

}

add_action( 'admin_enqueue_scripts', 'geodir_cpt_link_admin_templates_script' );
function geodir_cpt_link_admin_templates_script(){
    wp_register_style('geodir-link-business-style', geodir_cpt_plugin_url() . '/css/link-business.css', array(), GEODIR_CP_VERSION);
    wp_enqueue_style('geodir-link-business-style');
    
    wp_register_script('geodir-link-business-js', geodir_cpt_plugin_url().'/js/link_business.js', array ('jquery'), GEODIR_CP_VERSION);
    wp_enqueue_script( 'geodir-link-business-js' );
}

add_action('wp_ajax_geodir_link_cpt_business_ajax', "geodir_link_cpt_business_ajax");

add_action( 'wp_ajax_nopriv_geodir_link_cpt_business_ajax', 'geodir_link_cpt_business_ajax' );

add_filter( 'template_include', 'geodir_cpt_link_template_loader',0);

function geodir_cpt_link_template_loader($template) {

    if(geodir_get_current_posttype() != 'gd_event'){

        add_action('geodir_before_detail_fields','geodir_link_cpt_business_fields_html',1);

    }

    return $template;
}

function geodir_cpt_link_businesses( $post_id, $post_type, $arr = false ) {
    global $wpdb, $plugin_prefix;

    $geodir_linked_post_types = get_option('geodir_linked_post_types');

    if(isset($post_type) && isset($geodir_linked_post_types[$post_type])){
        $linked_post_type = $geodir_linked_post_types[$post_type];
    } else {
        $linked_post_type = 'gd_place';
    }

    $table = $plugin_prefix.$linked_post_type.'_detail';

    $sql = $wpdb->prepare(
        "SELECT post_id FROM " . $table . " WHERE post_status=%s AND geodir_link_business=%d", array( 'publish', $post_id )
    );

    $rows = $wpdb->get_results($sql);

    $result = array();
    if ( !empty( $rows ) ) {
        foreach ($rows as $row) {
            $result[] = $row->post_id;
        }
    }

    return $result;
}

// display linked business under detail page tabs
add_filter( 'geodir_detail_page_tab_list_extend', 'geodir_detail_page_link_cpt_business_tab' );
function geodir_detail_page_link_cpt_business_tab( $tabs_arr ) {
    global $post, $wpdb, $plugin_prefix, $character_count, $gridview_columns, $gd_query_args;

    $post_type = geodir_get_current_posttype();
    $all_postypes = geodir_get_posttypes();


    if ( !empty($post) && !empty($post->ID) && !empty( $tabs_arr ) && $post_type != 'gd_event' && in_array( $post_type, $all_postypes ) && ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) ) {
        $old_character_count = $character_count;
        $old_gridview_columns = $gridview_columns;
        $old_gd_query_args = $gd_query_args;

        $list_sort = apply_filters( 'geodir_cpt_linked_sortby', get_option( 'geodir_related_post_sortby', 'latest' ));
        $post_number = apply_filters( 'geodir_cpt_linked_count', get_option( 'geodir_related_post_count', '5' ) );
        $gridview_columns = apply_filters( 'geodir_cpt_linked_listing_view', get_option( 'geodir_related_post_listing_view', 'gridview_onehalf' ) );
        $gridview_columns = $gridview_columns == 'listview' ? '' : $gridview_columns;
        $character_count = apply_filters( 'geodir_cpt_linked_post_excerpt', get_option( 'geodir_related_post_excerpt', '20' ) );

        if ( empty( $gd_query_args ) ) {
            $gd_query_args = array();
        }
        $gd_query_args['is_geodir_loop'] = true;

        $listing_ids = geodir_cpt_link_businesses( $post->ID, $post_type );

        if ( !empty( $listing_ids ) ) {
            $html = geodir_cpt_link_businesses_data( $post_type, $listing_ids, $list_sort, $post_number, $character_count );
            if ( $html ) {
                $geodir_linked_post_types = get_option('geodir_linked_post_types');

                if(isset($post_type) && isset($geodir_linked_post_types[$post_type])){
                    $linked_post_type = $geodir_linked_post_types[$post_type];
                } else {
                    $linked_post_type = 'gd_place';
                }

                $geodir_post_type = 'Businesses';
                $geodir_post_types = get_option( 'geodir_post_types' );
                if (isset($geodir_post_types[$linked_post_type])){
                    $geodir_post_type = $geodir_post_types[$linked_post_type]['labels']['name'];
                }

                $post->link_business = '';
                $tabs_arr['link_business'] = array(
                    'heading_text' => $geodir_post_type,
                    'is_active_tab' => false,
                    'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'link_business', $post_type),
                    'tab_content' => $html
                );
            }
        }

        global $character_count, $gridview_columns;
        $old_gd_query_args = $gd_query_args;
        $character_count = $old_character_count;
        $gridview_columns = $old_gridview_columns;
    }
    return $tabs_arr;
}

function geodir_cpt_link_businesses_data( $post_type, $post_ids, $list_sort = 'latest', $post_number = 5 , $character_count = '20') {
    global $wpdb, $plugin_prefix;


    if ( $post_ids == '' || ( is_array( $post_ids ) && empty( $post_ids ) ) ) {
        return NULL;
    }

    $geodir_linked_post_types = get_option('geodir_linked_post_types');

    if(isset($post_type) && isset($geodir_linked_post_types[$post_type])){
        $linked_post_type = $geodir_linked_post_types[$post_type];
    } else {
        $linked_post_type = 'gd_place';
    }

    $query_args = array(
        'post_type' => $linked_post_type,
        'order_by' => $list_sort,
        'posts_per_page' => $post_number,
        'is_geodir_loop' => true
    );

    add_filter('geodir_filter_widget_listings_where', 'geodir_cpt_link_biz_where', 1, 2);
    $widget_listings = geodir_get_widget_listings($query_args);
    remove_filter('geodir_filter_widget_listings_where', 'geodir_cpt_link_biz_where', 1, 2);

    /** This filter is documented in geodirectory-functions/general_functions.php */
    $template = apply_filters("geodir_template_part-widget-listing-listview", geodir_locate_template('widget-listing-listview'));

    ob_start();

    global $post, $gd_session, $map_jason, $map_canvas_arr, $gridview_columns, $gridview_columns_widget, $geodir_is_widget_listing;
    
    $current_post = $post;
    $current_map_jason = $map_jason;
    $current_map_canvas_arr = $map_canvas_arr;
    $current_grid_view = $gridview_columns_widget;
    $gridview_columns_widget = $gridview_columns;

    $gd_listing_view_set = $gd_session->get('gd_listing_view') ? true : false;
    $gd_listing_view_old = $gd_listing_view_set ? $gd_session->get('gd_listing_view') : '';

    $geodir_is_widget_listing = true;

    /**
     * Includes the template for the listing listview.
     *
     * @since 1.3.9
     */
    include($template);

    $geodir_is_widget_listing = false;

    $GLOBALS['post'] = $current_post;
    if (!empty($current_post)) {
        setup_postdata($current_post);
    }
    if ($gd_listing_view_set) { // Set back previous value
        $gd_session->set('gd_listing_view', $gd_listing_view_old);
    } else {
        $gd_session->un_set('gd_listing_view');
    }
    $map_jason = $current_map_jason;
    $map_canvas_arr = $current_map_canvas_arr;
    $gridview_columns_widget = $current_grid_view;

    //wp_reset_query();

    $content = ob_get_contents();
    ob_end_clean();

    return $content;

}

add_action('geodir_after_save_listing','geodir_cpt_link_save_data',12,2);
function geodir_cpt_link_save_data( $post_id = '', $request_info ) {
    global $wpdb, $current_user;

    $gd_post_info = array();
    $last_post_id = $post_id;
    $post_type = get_post_type( $post_id );

    $link_business = 0;
    $geodir_post_types = get_option( 'geodir_post_types' );
    if (isset($geodir_post_types[$post_type]['linkable_to']) && !empty($geodir_post_types[$post_type]['linkable_to'])){
        $link_business = $geodir_post_types[$post_type]['linkable_to'];
    }

    if (!$link_business) {
        return false;
    }

    /* --- save businesses --- */
    if ( isset( $request_info['geodir_link_cpt_business' ]) ) {
        $gd_post_info['geodir_link_business'] = $request_info['geodir_link_cpt_business'];
    }

    // save post info
    geodir_save_post_info($last_post_id, $gd_post_info);

    return $last_post_id;
}

function geodir_cpt_link_biz_where($where, $post_type)
{
    global $wpdb, $post;

    $post_ids = geodir_cpt_link_businesses( $post->ID, $post->post_type );

    $post_ids = is_array( $post_ids ) ? implode( "','", $post_ids ) : '';

    $where .= " AND $wpdb->posts.ID IN ('" . $post_ids . "')";
    return $where;
}

function geodir_cpt_link_fill_listings( $post_type, $term ) {
    $listings = geodir_cpt_link_get_my_listings( $post_type, $term );
    $options = '<option value="">' . __( 'No Business', 'geodir_custom_posts' ) . '</option>';
    if( !empty( $listings ) ) {
        foreach( $listings as $listing ) {
            $options .= '<option value="' . $listing->ID . '">' . $listing->post_title . '</option>';
        }
    }
    return $options;
}

function geodir_cpt_link_get_my_listings( $post_type = 'all', $search = '', $limit = 5 ) {
    global $wpdb, $current_user;

    if( empty( $current_user->ID ) ) {
        return NULL;
    }
    $geodir_postypes = geodir_get_posttypes();

    $search = trim( $search );
    $post_type = $post_type != '' ? $post_type : 'all';

    if( $post_type == 'all' ) {
        $geodir_postypes = implode( ",", $geodir_postypes );
        $condition = $wpdb->prepare( " AND FIND_IN_SET( post_type, %s )" , array( $geodir_postypes ) );
    } else {
        $post_type = in_array( $post_type, $geodir_postypes ) ? $post_type : 'gd_place';
        $condition = $wpdb->prepare( " AND post_type = %s" , array( $post_type ) );
    }
    $condition .= !current_user_can( 'manage_options' ) ? $wpdb->prepare( "AND post_author=%d" , array( (int)$current_user->ID ) ) : '';
    $condition .= $search != '' ? $wpdb->prepare( " AND post_title LIKE %s", array( $search . '%%' ) ) : "";

    $orderby = " ORDER BY post_title ASC";
    $limit = " LIMIT " . (int)$limit;

    //$sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND (post_type != 'gd_event' OR post_type != '$linkable_post_type') " . $condition . $orderby . $limit, array( 'publish' ) );
    $sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND post_type != 'gd_event' " . $condition . $orderby . $limit, array( 'publish' ) );
    $rows = $wpdb->get_results($sql);

    return $rows;
}

function geodir_cpt_business_auto_fill($request){

    if(!empty($request)){

        $place_id = $request['place_id'];
        $post_type = get_post_type( $place_id );
        $package_id = geodir_get_post_meta($place_id,'package_id',true);
        $custom_fields = geodir_post_custom_fields($package_id,'all',$post_type);

        $json_array = array();

        $content_post = get_post($place_id);
        $content = $content_post->post_content;

        $excluded = apply_filters('geodir_cpt_business_auto_fill_excluded', array());

        $post_title_value = geodir_get_post_meta($place_id,'post_title',true);
        if (in_array('post_title', $excluded)) {
            $post_title_value = '';
        }

        $post_desc_value = $content;
        if (in_array('post_desc', $excluded)) {
            $post_desc_value = '';
        }

        $json_array['post_title'] = array('key' => 'text', 'value' => $post_title_value);

        $json_array['post_desc'] = array(	'key' => 'textarea', 'value' => $post_desc_value);


        foreach($custom_fields as $key=>$val){

            $type = $val['type'];

            switch($type){

                case 'phone':
                case 'email':
                case 'text':
                case 'url':
                    $value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
                    $json_array[$val['htmlvar_name']] = array('key' => 'text', 'value' => $value);

                    break;

                case 'html':
                case 'textarea':

                    $value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
                    $json_array[$val['htmlvar_name']] = array('key' => 'textarea', 'value' => $value);

                    break;

                case 'address':

                    $json_array['post_address'] = array('key' => 'text',
                        'value' => geodir_get_post_meta($place_id,'post_address',true));
                    $json_array['post_zip'] = array('key' => 'text',
                        'value' => geodir_get_post_meta($place_id,'post_zip',true));
                    $json_array['post_latitude'] = array('key' => 'text',
                        'value' => geodir_get_post_meta($place_id,'post_latitude',true));
                    $json_array['post_longitude'] = array('key' => 'text',
                        'value' => geodir_get_post_meta($place_id,'post_longitude',true));


                    $extra_fields = unserialize($val['extra_fields']);

                    $show_city = isset($extra_fields['show_city']) ? $extra_fields['show_city'] : '';

                    if($show_city){

                        $json_array['post_country'] = array('key' => 'text',
                            'value' => geodir_get_post_meta($place_id,'post_country',true));
                        $json_array['post_region'] = array('key' => 'text',
                            'value' => geodir_get_post_meta($place_id,'post_region',true));
                        $json_array['post_city'] = array('key' => 'text',
                            'value' => geodir_get_post_meta($place_id,'post_city',true));

                    }


                    break;
                case 'checkbox':
                case 'radio':
                case 'select':
                case 'datepicker':
                case 'time':
                    $value = geodir_get_post_meta( $place_id, $val['htmlvar_name'], true );
                    $json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
                    break;
                case 'multiselect':
                    $value = geodir_get_post_meta( $place_id, $val['htmlvar_name'] );
                    $value = $value != '' ? explode( ",", $value ) : array();
                    $json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
                    break;

            }

        }

    }

    if ( !empty( $json_array ) ) {
        // attach terms
        $post_tags = wp_get_post_terms( $place_id, $post_type . '_tags', array( "fields" => "names" ) );
        $post_tags = !empty( $post_tags ) && is_array( $post_tags ) ? implode( ",", $post_tags ) : '';
        $json_array['post_tags'] = array( 'key' => 'tags', 'value' => $post_tags );

        echo json_encode( $json_array );
    }
}

// display link business on listing detail page to go back to the linked listing
add_action( 'geodir_after_detail_page_more_info', 'geodir_cpt_link_display_link_business' );
function geodir_cpt_link_display_link_business() {
    global $post;
    $post_type = geodir_get_current_posttype();
    $all_postypes = geodir_get_posttypes();

    if ( !empty( $post ) && $post_type != 'gd_event' && geodir_is_page( 'detail' ) && isset( $post->geodir_link_business ) && !empty( $post->geodir_link_business ) ) {
        $linked_post_id = $post->geodir_link_business;
        $linked_post_info = get_post($linked_post_id);
        if( !empty( $linked_post_info ) ) {
            $linked_post_type_info = in_array( $linked_post_info->post_type, $all_postypes ) ? geodir_get_posttype_info( $linked_post_info->post_type )  : array();
            if( !empty( $linked_post_type_info ) ) {
                $linked_post_title = !empty( $linked_post_info->post_title ) ? $linked_post_info->post_title : __( 'Listing', 'geodirevents' );
                $linked_post_url = get_permalink($linked_post_id);

                $html_link_business = '<div class="geodir_more_info geodir_more_info_even geodir_link_business"><span class="geodir-i-website"><i class="fa fa-link"></i> <a title="' . esc_attr( $linked_post_title ) . '" href="'.$linked_post_url.'">' . wp_sprintf( __( 'Go to: %s', 'geodir_custom_posts' ), $linked_post_title ) . '</a></span></div>';

                echo apply_filters( 'geodir_more_info_link_business', $html_link_business, $linked_post_id, $linked_post_url );
            }
        }
    }
}