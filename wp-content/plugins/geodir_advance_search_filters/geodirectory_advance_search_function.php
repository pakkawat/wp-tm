<?php
/**
 * Plugin activation hook.
 *
 * @package GeoDirectory_Advance_Search_Filters
 * @since 1.0.0
 * @since 1.4.4 Don't loose previously saved settings when plugin is reactivated.
 */
function geodir_advance_search_filters_activation() {
    if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
        if ( is_plugin_active( 'geodir_advance_search_filters/geodir_advance_search_filters.php' ) ) {
            deactivate_plugins( 'geodir_advance_search_filters/geodir_advance_search_filters.php' );
            
            if ( isset( $_GET[ 'activate' ] ) ) {
                unset( $_GET[ 'activate' ] );
            }
            
            add_action( 'admin_notices', 'geodir_search_PHP_version_notice' );
        }
        return;
    }
    
    if (get_option('geodir_installed')) {
        $options = geodir_advance_search_resave_settings(geodir_autocompleter_options());
        geodir_update_options($options, true);

        update_option('geodir_autocompleter_matches_label', 's');

        geodir_advance_search_field();
        add_option('geodir_advance_search_activation_redirect_opt', 1);
    }
}

/**
 * Plugin deactivation hook.
 *
 * @package GeoDirectory_Advance_Search_Filters
 * @since 1.0.0
 */
function geodir_advance_search_filters_deactivation() {
    // Plugin deactivation stuff here
}

function geodir_advance_search_activation_redirect()
{
    if (get_option('geodir_advance_search_activation_redirect_opt', false)) {
        delete_option('geodir_advance_search_activation_redirect_opt');
        wp_redirect(admin_url('admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=advance_search&listing_type=gd_place'));
    }
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.4.4
 * @package GeoDirectory_Advance_Search_Filters
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_advance_search_resave_settings($settings = array()) {
    if (!empty($settings) && is_array($settings)) {
        $c = 0;
        
        foreach ($settings as $setting) {
            if (!empty($setting['id']) && false !== ($value = get_option($setting['id']))) {
                $settings[$c]['std'] = $value;
            }
            $c++;
        }
    }

    return $settings;
}

function geodir_advace_search_manager_tabs($tabs)
{
    $geodir_post_types = get_option('geodir_post_types');

    foreach ($geodir_post_types as $geodir_post_type => $geodir_posttype_info) {

        $originalKey = $geodir_post_type . '_fields_settings';

        if (array_key_exists($originalKey, $tabs)) {

            if (array_key_exists('subtabs', $tabs[$originalKey])) {

                $insertValue = array('subtab' => 'advance_search',
                    'label' => __('Advance Search', 'geodiradvancesearch'),
                    'request' => array('listing_type' => $geodir_post_type)
                );

                $new_array = array();
                foreach ($tabs[$originalKey]['subtabs'] as $key => $val) {

                    $new_array[] = $val;

                    if ($val['subtab'] == 'custom_fields')
                        $new_array[] = $insertValue;

                }

                $tabs[$originalKey]['subtabs'] = $new_array;

            }

        }

    }

    return $tabs;
}

function geodir_manage_advace_search_available_fields($sub_tab)
{
    switch ($sub_tab) {
        case 'advance_search':
            geodir_advance_search_available_fields();
            break;
    }
}

function geodir_manage_advace_search_selected_fields($sub_tab)
{
    switch ($sub_tab) {
        case 'advance_search':
            geodir_advace_search_selected_fields();
            break;
    }
}

function geodir_advance_admin_custom_fields($field_info, $cf) {
    $radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);
    $hide_cat_sort = (isset($cf['defaults']['cat_filter']) && $cf['defaults']['cat_filter']===false) ? "style='display:none;'" : '';

    $value = 0;
    if (isset($field_info->cat_filter)) {
        $value = (int)$field_info->cat_filter;
    } else if(isset($cf['defaults']['cat_filter']) && $cf['defaults']['cat_filter']) {
        $value = ($cf['defaults']['cat_filter']) ? 1 : 0;
    }
    ?>
    <li <?php echo $hide_cat_sort ;?>>
        <label for="cat_sort" class="gd-cf-tooltip-wrap">
            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Include this field in advanced search :', 'geodiradvancesearch');?>
            <div class="gdcf-tooltip">
                <?php _e('Lets you use this filed as an advanced search, set from advanced search tab above.', 'geodiradvancesearch');?>
            </div>
        </label>

        <div class="gd-cf-input-wrap gd-switch">
            <input type="radio" id="cat_filter_yes<?php echo $radio_id;?>" name="cat_filter" class="gdri-enabled"  value="1" <?php checked(1, $value);?> />
            <label for="cat_filter_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodiradvancesearch'); ?></span></label>

            <input type="radio" id="cat_filter_no<?php echo $radio_id;?>" name="cat_filter" class="gdri-disabled" value="0" <?php checked(0, $value);?> />
            <label for="cat_filter_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodiradvancesearch'); ?></span></label>
        </div>
    </li>
<?php
}

function geodir_get_cat_sort_fields($sort_fields)
{
    global $wpdb;

    $post_type = geodir_get_current_posttype();

    $custom_sort_fields = array();

    if ($custom_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE cat_sort <> '' AND field_type NOT IN ('html','multiselect','file','textarea') AND post_type = %s ORDER BY sort_order", array($post_type)))) {
        foreach ($custom_fields as $custom_field) {
            switch ($custom_field->field_type):
                case 'address':
                    $custom_sort_fields[$custom_field->htmlvar_name . '_address'] = __($custom_field->site_title);
                    break;
                default:
                    $custom_sort_fields[$custom_field->htmlvar_name] = __($custom_field->site_title);
                    break;
            endswitch;
        }
    }

    return array_merge($sort_fields, $custom_sort_fields);
}

function geodir_advance_search_filter()
{
    global $wp_query;

    if ((is_search() && isset($wp_query->query_vars['is_geodir_loop']) && $wp_query->query_vars['is_geodir_loop'] && isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search'])) {
        add_filter('posts_where', 'geodir_advance_search_where', 10, 2);
    }
}

function geodirectory_advance_search_fields($listing_type)
{
    $fields = array();
    // fieldset option
    $fields[] = array('field_type' => 'fieldset',
                      'site_title' => __('Fieldset (section separator)', 'geodiradvancesearch'),
                      'htmlvar_name' => 'fieldset',
                      'data_type' => 'FLOAT',
                      'field_icon' => 'fa fa-arrows-h'
    );
    // search by distance
    $fields[] = array('field_type' => 'distance',
                      'site_title' => __('Search By Distance', 'geodiradvancesearch'),
                      'htmlvar_name' => 'dist',
                      'data_type' => 'FLOAT',
                      'field_icon' => 'fa fa-map-marker'
    );
    return apply_filters('geodir_show_filters', $fields, $listing_type);
}

function geodirectory_advance_search_custom_fields($fields, $listing_type)
{
    global $wpdb;
    $records = $wpdb->get_results($wpdb->prepare("select id,field_type,data_type,site_title,htmlvar_name,field_icon from " . GEODIR_CUSTOM_FIELDS_TABLE . " where post_type = %s and cat_filter=%s order by sort_order asc", array($listing_type, '1')));

    foreach ($records as $row) {
        $field_type = $row->field_type;
        if ($row->field_type == 'taxonomy') {
            $field_type = 'taxonomy';
        }
        $fields[] = array('field_type' => $field_type, 'site_title' => $row->site_title, 'htmlvar_name' => $row->htmlvar_name, 'data_type' => $row->data_type, 'field_icon' => $row->field_icon);
    }
    return $fields;
}

function geodir_is_geodir_search($where, $query = array())
{
    global $wpdb;

    $return = true;

    if ($where != '') {
        $match_where = geodir_strtolower("and" . $wpdb->posts . ".post_type='post'");
        $check_where = geodir_strtolower($where);
        $check_where = preg_replace('/\s/', '', $check_where);

        if (strpos($check_where, $match_where) !== false) {
            $return = false;
        }
    }

    if (!empty($query)) {
        if (empty($query->query_vars['is_geodir_loop'])) {
            $return = false;
        }

        if (!empty($query->query_vars['post_type']) && $query->query_vars['post_type'] != 'any' && !in_array($query->query_vars['post_type'], geodir_get_posttypes())) {
            $return = false;
        }
    }

    return $return;
}

function geodir_advance_search_where($where, $query = array())
{
    global $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $mylat, $mylon, $s, $snear, $s, $s_A, $s_SA, $search_term;

    if (isset($_REQUEST['stype'])) {
        $post_types = esc_attr($_REQUEST['stype']);
    } else {
        $post_types = 'gd_place';
    }

    /* check for post type other then geodir post types */
    if (!geodir_is_geodir_search($where, $query)) {
        return $where;
    }

    /* Add categories filters */
    $geodir_custom_search = '';
    $category_search_range = '';

    $sql = $wpdb->prepare("SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type = %s ORDER BY sort_order", array($post_types));
    $taxonomies = $wpdb->get_results($sql);

    //echo '###';print_r($taxonomies);
    if (!empty($taxonomies)) {
        foreach ($taxonomies as $taxonomy_obj) {
            $taxonomy_obj = stripslashes_deep($taxonomy_obj); // strip slashes

            // Search query operator.
            $extra_fields = isset($taxonomy_obj->extra_fields) && $taxonomy_obj->extra_fields != '' ? maybe_unserialize($taxonomy_obj->extra_fields) : NULL;
            $search_operator = !empty($extra_fields) && isset($extra_fields['search_operator']) && $extra_fields['search_operator'] == 'OR' ? 'OR' : 'AND';

            switch ($taxonomy_obj->field_input_type) {
                case 'RANGE':
                    // SEARCHING BY RANGE FILTER
                    switch ($taxonomy_obj->search_condition) {
                        case 'SINGLE':
                            $value = esc_attr($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]);

                            if (!empty($value)) {
                                $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " = $value) ";
                            }
                            break;

                        case 'FROM':
                            $minvalue = @esc_attr($_REQUEST['smin' . $taxonomy_obj->site_htmlvar_name]);
                            $smaxvalue = @esc_attr($_REQUEST['smax' . $taxonomy_obj->site_htmlvar_name]);

                            if (!empty($minvalue)) {
                                $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " >= '" . $minvalue . "') ";
                            }

                            if (!empty($smaxvalue)) {
                                $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " <= '" . $smaxvalue . "') ";
                            }
                            break;

                        case 'RADIO':
                            // This code in main geodirectory listing filter
                            break;

                        default :
                            if (isset($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) && $_REQUEST['s' . $taxonomy_obj->site_htmlvar_name] != '') {
                                $serchlist = explode("-", esc_attr($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]));
                                $first_value = @$serchlist[0];//100 200
                                $second_value = @trim($serchlist[1], ' ');
                                $rest = substr($second_value, 0, 4);

                                if ($rest == 'Less') {
                                    $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " <= $first_value ) ";

                                } else if ($rest == 'More') {
                                    $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " >= $first_value) ";

                                } else if ($second_value != '') {
                                    $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " between $first_value and $second_value ) ";
                                }
                            }
                            break;
                    }
                    // END SEARCHING BY RANGE FILTER
                    break;

                case 'DATE' :
                    $single = '';
                    $value = isset($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) ? esc_attr($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) : '';
                    if (isset($value) && !empty($value)) {
                        $minvalue = $value;
                        $maxvalue = '';
                        $single = '1';
                    } else {
                        $minvalue = isset($_REQUEST['smin' . $taxonomy_obj->site_htmlvar_name]) ? esc_attr($_REQUEST['smin' . $taxonomy_obj->site_htmlvar_name]) : '';
                        $maxvalue = isset($_REQUEST['smax' . $taxonomy_obj->site_htmlvar_name]) ? esc_attr($_REQUEST['smax' . $taxonomy_obj->site_htmlvar_name]) : '';
                    }

                    if ($taxonomy_obj->site_htmlvar_name == 'event') {
                        $category_search_range .= " ";
                    } else if ($taxonomy_obj->field_data_type == 'DATE') {
                        $date_extra = $wpdb->get_var($wpdb->prepare("SELECT extra_fields FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s AND htmlvar_name=%s LIMIT 1",$post_types,$taxonomy_obj->site_htmlvar_name));

                        if ($date_extra){
                            $date_extra = maybe_unserialize($date_extra);
                            
                            if (isset($date_extra['date_format'])){
                                $date_format = $date_extra['date_format'];
                            }
                        }
                        if (empty($date_format)){
                            $date_format = 'Y-m-d';
                        }

                        if ($minvalue){
                            $minvalue = geodir_maybe_untranslate_date($minvalue);
                            $temp_minvalue = DateTime::createFromFormat($date_format, $minvalue);
                            $minvalue  = !empty($temp_minvalue) ? $temp_minvalue->format('Y-m-d') : $minvalue;
                        }

                        if ($maxvalue){
                            $maxvalue = geodir_maybe_untranslate_date($maxvalue);
                            $temp_maxvalue = DateTime::createFromFormat($date_format, $maxvalue);
                            $maxvalue  = !empty($temp_maxvalue) ? $temp_maxvalue->format('Y-m-d') : $maxvalue;
                        }

                        if ($single == '1') {
                            $category_search_range .= " AND ( unix_timestamp(" . $table . '.' . $taxonomy_obj->site_htmlvar_name . ") = unix_timestamp('" . $minvalue . "') )";
                        } else {
                            if (!empty($minvalue)) {
                                $category_search_range .= " AND ( unix_timestamp(" . $table . '.' . $taxonomy_obj->site_htmlvar_name . ") >= unix_timestamp('" . $minvalue . "') )";
                            }
                            if (!empty($maxvalue)) {
                                $category_search_range .= " AND ( unix_timestamp(" . $table . '.' . $taxonomy_obj->site_htmlvar_name . ") <= unix_timestamp('" . $maxvalue . "') )";
                            }
                        }
                    } else if ($taxonomy_obj->field_data_type == 'TIME') {
                        if ($single == '1') {
                            $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " = '" . $minvalue . ":00' )";
                        } else {
                            if (!empty($minvalue)) {
                                $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " >= '" . $minvalue . ":00' )";
                            }
                            if (!empty($maxvalue)) {
                                $category_search_range .= " AND ( " . $table . '.' . $taxonomy_obj->site_htmlvar_name . " <= '" . $maxvalue . ":00' )";
                            }
                        }
                    }
                    break;
                default:
                    $category_search = '';
                    if (isset($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) && is_array($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name])) {
                        $i = 0;
                        $add_operator = '';
                        foreach ($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name] as $val) {

                            if ($val != '') {
                                if ($i != 0) {
                                    $add_operator = $search_operator;
                                }

                                $category_search .= $wpdb->prepare( $add_operator . " FIND_IN_SET(%s, " . $table . "." . $taxonomy_obj->site_htmlvar_name . " ) ", $val);
                                $i++;
                            }
                        }

                        if (!empty($category_search)) {
                            $geodir_custom_search .= " AND (" . $category_search . ")";
                        }
                    } else if (isset($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name])) {
                        $site_htmlvar_name = $taxonomy_obj->site_htmlvar_name;

                        if ($site_htmlvar_name == 'post') {
                            $site_htmlvar_name = $site_htmlvar_name . '_address';
                        }

                        if ($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) {
                            $geodir_custom_search .= " AND " . $table . "." . $site_htmlvar_name . " LIKE '%" . esc_attr($_REQUEST['s' . $taxonomy_obj->site_htmlvar_name]) . "%' ";
                        }
                    }
                    break;
            }
        }
    }
    if (!empty($geodir_custom_search)) {
        $where .= $geodir_custom_search;
    }
    if (!empty($category_search_range)) {
        $where .= $category_search_range;
    }

    $where = apply_filters('advance_search_where_query', $where);

    return $where;
}

function geodir_advance_search_available_fields()
{
    global $wpdb;
    $listing_type = ($_REQUEST['listing_type'] != '') ? esc_attr($_REQUEST['listing_type']) : 'gd_place';

    $allready_add_fields = $wpdb->get_results("select site_htmlvar_name from " . GEODIR_ADVANCE_SEARCH_TABLE . "     where post_type ='" . $listing_type . "'");

    $allready_add_fields_ids = array();
    if (!empty($allready_add_fields)) {
        foreach ($allready_add_fields as $allready_add_field) {
            $allready_add_fields_ids[] = $allready_add_field->site_htmlvar_name;
        }
    }
    ?>
    <input type="hidden" name="listing_type" id="new_post_type" value="<?php echo $listing_type;?>"/>
    <input type="hidden" name="manage_field_type" class="manage_field_type"
           value="<?php echo esc_attr($_REQUEST['subtab']); ?>"/>
    <ul><?php

        $fields = geodirectory_advance_search_fields($listing_type);

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $field = stripslashes_deep($field); // strip slashes


                $fieldset_width = '';
                if($field['field_type']=='fieldset') {
                    $fieldset_width = 'width:100%;';
                }

                $display = '';
                if (in_array($field['htmlvar_name'], $allready_add_fields_ids) && $field['field_type']!='fieldset')
                    $display = 'display:none;';

                $style = 'style="'.$display .$fieldset_width.'"';
                ?>
                <li <?php echo $style; ?> >

                    <a id="gd-<?php echo $field['htmlvar_name'];?>"
                                               class="gd-draggable-form-items gd-<?php echo $field['field_type'];?>"
                                               href="javascript:void(0);">

                        <?php if (isset($field['field_icon']) && strpos($field['field_icon'], 'fa fa-') !== false) {
                            echo '<i class="'.$field['field_icon'].'" aria-hidden="true"></i>';
                        }elseif(isset($field['field_icon']) && $field['field_icon']){
                            echo '<b style="background-image: url("'.$field['field_icon'].'")"></b>';
                        }else{
                            echo '<i class="fa fa-cog" aria-hidden="true"></i>';
                        }?>

                        <?php echo $field['site_title'];?>


                    </a>
                </li>


            <?php
            }
        }
        ?>
    </ul>
<?php
}

function geodir_advace_search_selected_fields()
{
    global $wpdb;
    $listing_type = ($_REQUEST['listing_type'] != '') ? esc_attr($_REQUEST['listing_type']) : 'gd_place';

    ?>
    <input type="hidden" name="manage_field_type" class="manage_field_type"
           value="<?php echo esc_attr($_REQUEST['subtab']); ?>"/>
    <ul class="advance"><?php

        $fields = $wpdb->get_results(
            $wpdb->prepare(
                "select * from  " . GEODIR_ADVANCE_SEARCH_TABLE . " where post_type = %s order by sort_order asc",
                array($listing_type)
            )
        );

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $result_str = $field;
                $field_type = $field->field_site_type;
                $field_ins_upd = 'display';

                $default = false;

                geodir_custom_advance_search_field_adminhtml($field_type, $result_str, $field_ins_upd, $default);
            }
        }?>
    </ul>
<?php
}

function geodir_custom_advance_search_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $default = false)
{
    global $wpdb;

    $cf = $result_str;
    if (!is_object($cf)) {

        $field_info = $wpdb->get_row($wpdb->prepare("select * from " . GEODIR_ADVANCE_SEARCH_TABLE . " where id= %d", array($cf)));

    } else {
        $field_info = $cf;
        $result_str = $cf->id;
    }

    include('advance_search_admin/custom_advance_search_field_html.php');
}


if (!function_exists('geodir_custom_advance_search_field_save')) {
    function geodir_custom_advance_search_field_save($request_field = array())
    {

        global $wpdb, $plugin_prefix;

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        $cf = trim($result_str, '_');

        /*-------- check duplicate validation --------*/

        $site_htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
        $post_type = $request_field['listing_type'];

        $check_html_variable = $wpdb->get_var($wpdb->prepare("select site_htmlvar_name from " . GEODIR_ADVANCE_SEARCH_TABLE . " where id <> %d and site_htmlvar_name = %s and post_type = %s ",
            array($cf, $site_htmlvar_name, $post_type)));


        if (!$check_html_variable) {

            if ($cf != '') {

                $post_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . GEODIR_ADVANCE_SEARCH_TABLE . " where id = %d",
                        array($cf)
                    )
                );

            }

            if ($post_type == '') $post_type = 'gd_place';


            $field_site_type = $request_field['field_type'];
            $site_field_title = $request_field['site_field_title'];
            $site_htmlvar_name = $request_field['site_htmlvar_name'];
            $data_type = $request_field['data_type'];
            $field_desc = $request_field['field_desc'];
            $field_data_type = $request_field['field_data_type'];
            if ($field_data_type == 'XVARCHAR') {
                $field_data_type = 'VARCHAR';
            }
            $field_id = (isset($request_field['field_id']) && $request_field['field_id']) ? str_replace('new', '', $request_field['field_id']) : '';

            $expand_custom_value = $request_field['expand_custom_value'];


            $searching_range_mode = isset($request_field['searching_range_mode']) ? $request_field['searching_range_mode'] : '';
            $expand_search = isset($request_field['expand_search']) ? $request_field['expand_search'] : '';

            $front_search_title = isset($request_field['front_search_title']) ? $request_field['front_search_title'] : '';

            $first_search_value = isset($request_field['first_search_value']) ? $request_field['first_search_value'] : '';

            $first_search_text = isset($request_field['first_search_text']) ? $request_field['first_search_text'] : '';
            $last_search_text = isset($request_field['last_search_text']) ? $request_field['last_search_text'] : '';
            $search_condition = isset($request_field['search_condition']) ? $request_field['search_condition'] : '';
            $search_min_value = isset($request_field['search_min_value']) ? $request_field['search_min_value'] : '';
            $search_max_value = isset($request_field['search_max_value']) ? $request_field['search_max_value'] : '';
            $search_diff_value = isset($request_field['search_diff_value']) ? $request_field['search_diff_value'] : '';
            $main_search = isset($request_field['main_search']) ? $request_field['main_search'] : '0';
            $main_search_priority = isset($request_field['main_search_priority']) ? $request_field['main_search_priority'] : '0';


            $extra_fields = '';
            if (isset($request_field['search_asc_title'])) {
                $arrays_sorting = array();
                $arrays_sorting['is_sort'] = isset($request_field['geodir_distance_sorting']) ? $request_field['geodir_distance_sorting'] : '';
                $arrays_sorting['asc'] = isset($request_field['search_asc']) ? $request_field['search_asc'] : '';
                $arrays_sorting['asc_title'] = isset($request_field['search_asc_title']) ? $request_field['search_asc_title'] : '';
                $arrays_sorting['desc'] = isset($request_field['search_desc']) ? $request_field['search_desc'] : '';
                $arrays_sorting['desc_title'] = isset($request_field['search_desc_title']) ? $request_field['search_desc_title'] : '';

                $extra_fields = serialize($arrays_sorting);
            }

            if ($search_diff_value != 1) {
                $searching_range_mode = 0;
            }
            if ($site_htmlvar_name == 'dist') {
                $data_type = 'RANGE';
                $search_condition = 'RADIO';
            }

            $data_type_change = isset($request_field['data_type_change']) ? $request_field['data_type_change'] : '';

            if ($data_type_change == 'SELECT')
                $data_type = 'RANGE';

            if (isset($request_field['search_operator'])) {
                $search_operator = $request_field['search_operator'] == 'OR' ? 'OR' : 'AND';

                if ($extra_fields != '') {
                    $extra_fields = (array)maybe_unserialize($extra_fields);
                } else {
                    $extra_fields = array();
                }
                $extra_fields['search_operator'] = $search_operator;
                $extra_fields = maybe_serialize($extra_fields);
            }

            if (!empty($post_meta_info)) {

                $wpdb->query(
                    $wpdb->prepare(
                        "update " . GEODIR_ADVANCE_SEARCH_TABLE . " set
					post_type = %s,
					field_site_name = %s,
					field_site_type = %s,
					site_htmlvar_name = %s,
					field_input_type = %s,
					field_data_type = %s,
					sort_order = %s,
					field_desc = %s,
					expand_custom_value=%d,
					searching_range_mode=%d,
					expand_search=%d,
					front_search_title=%s,
					first_search_value=%d,
					first_search_text=%s,
					last_search_text=%s,
					search_condition = %s,
					search_min_value = %d,
					search_max_value = %d,
					search_diff_value = %d,
					extra_fields = %s,
					main_search = %s,
					main_search_priority = %s
					where id = %d",
                        array($post_type, $site_field_title, $field_site_type, $site_htmlvar_name, $data_type, $field_data_type, $field_id, $field_desc, $expand_custom_value, $searching_range_mode, $expand_search, $front_search_title, $first_search_value, $first_search_text, $last_search_text, $search_condition, $search_min_value, $search_max_value, $search_diff_value, $extra_fields,$main_search,$main_search_priority, $cf)

                    )

                );

                $lastid = trim($cf);


            } else {


                $wpdb->query(
                    $wpdb->prepare(

                        "insert into " . GEODIR_ADVANCE_SEARCH_TABLE . " set
					post_type = %s,
					field_site_name = %s,
					field_site_type = %s,
					site_htmlvar_name = %s,
					field_input_type = %s,
					field_data_type = %s,
					sort_order = %s,
					field_desc = %s,
					expand_custom_value=%d,
					searching_range_mode=%d,
					expand_search=%d,
					front_search_title=%s,
					first_search_value=%d,
					first_search_text=%s,
					last_search_text=%s,
					search_condition = %s,
					search_min_value = %d,
					search_max_value = %d,
					search_diff_value = %d,
					main_search = %d,
					main_search_priority = %d,
					extra_fields = %s
					 ",
                        array($post_type, $site_field_title, $field_site_type, $site_htmlvar_name, $data_type, $field_data_type, $field_id, $field_desc, $expand_custom_value, $searching_range_mode,
                            $expand_search, $front_search_title, $first_search_value, $first_search_text, $last_search_text, $search_condition, $search_min_value, $search_max_value, $search_diff_value,$main_search,$main_search_priority, $extra_fields)
                    )
                );
                $lastid = $wpdb->insert_id;
                $lastid = trim($lastid);
            }

            return (int)$lastid;


        } else {
            return 'HTML Variable Name should be a unique name';
        }
    }
}

function godir_set_advance_search_field_order($field_ids = array())
{
    global $wpdb;

    $count = 0;
    if (!empty($field_ids)):
        foreach ($field_ids as $id) {

            $cf = trim($id, '_');

            $wpdb->query(
                $wpdb->prepare(
                    "update " . GEODIR_ADVANCE_SEARCH_TABLE . " set
															sort_order=%d
															where id= %d",
                    array($count, $cf)
                )
            );
            $count++;
        }

        return $field_ids;
    else:
        return false;
    endif;
}

if (!function_exists('geodir_custom_advance_search_field_delete')) {
    function geodir_custom_advance_search_field_delete($field_id = '')
    {

        global $wpdb, $plugin_prefix;
        if ($field_id != '') {
            $cf = trim($field_id, '_');

            $wpdb->query($wpdb->prepare("delete from " . GEODIR_ADVANCE_SEARCH_TABLE . " where id= %d ", array($cf)));

            return $field_id;

        } else
            return 0;


    }
}

//---------advance search ajax-----
function geodir_advance_search_ajax_handler()
{
    if (isset($_REQUEST['create_field'])) {
        include_once(GEODIRADVANCESEARCH_PLUGIN_PATH . 'advance_search_admin/create_advance_search_field.php');
    }
    gd_die();
}

//-----------create advance search field table----------
function geodir_advance_search_field()
{
    global $plugin_prefix, $wpdb;

    /**
     * Include any functions needed for upgrades.
     *
     * @since 1.2.5
     */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


    $collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }
    $advance_search_table = "CREATE TABLE " . GEODIR_ADVANCE_SEARCH_TABLE . " (
									  id int(11) NOT NULL AUTO_INCREMENT,
									  post_type varchar(255) NOT NULL,
									  field_site_name varchar(255) NOT NULL,
									  field_site_type varchar(255) NOT NULL,
									  site_htmlvar_name varchar(255) NOT NULL,
									  expand_custom_value int(11) NOT NULL,
									  searching_range_mode int(11) NOT NULL,
									  expand_search int(11) NOT NULL,
									  front_search_title varchar(255) CHARACTER SET utf8 NOT NULL,
									  first_search_value int(11) NOT NULL,
									  first_search_text varchar(255) CHARACTER SET utf8 NOT NULL,
									  last_search_text varchar(255) CHARACTER SET utf8 NOT NULL,
									  search_min_value int(11) NOT NULL,
									  search_max_value int(11) NOT NULL,
									  search_diff_value int(11) NOT NULL DEFAULT '0',
									  search_condition varchar(100) NOT NULL,
									  field_input_type varchar(255) NOT NULL,
									  field_data_type varchar(255) NOT NULL,
									  sort_order int(11) NOT NULL,
									  main_search int(11) NOT NULL,
									  main_search_priority int(11) NOT NULL,
									  field_desc varchar(255) NOT NULL,
										extra_fields TEXT NOT NULL,
									  PRIMARY KEY  (id)
									) $collate AUTO_INCREMENT=1 ;";

    dbDelta($advance_search_table);
}

//-----------------------------------------------------
function geodir_search_get_field_search_param($htmlvar_name){
    $search_val = '';
    if ( isset( $_REQUEST[ 's' . $htmlvar_name ] ) && $_REQUEST[ 's' . $htmlvar_name ] != '' ) {

        $search_val = isset( $_REQUEST[ 's' . $htmlvar_name] ) ? stripslashes_deep( $_REQUEST[ 's' .$htmlvar_name ] ) : '';
        if ( is_array(  $search_val ) ) {
            $search_val = array_map( 'esc_attr', $search_val );
        } else {
            $search_val = esc_attr(  $search_val );
        }
    }

    return $search_val;
}

function geodir_show_filters_fields($post_type) {
    global $wpdb;
    $post_types = geodir_get_posttypes();

    $post_type = $post_type && in_array($post_type, $post_types) ? $post_type : $post_types[0];
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        var gd_datepicker_loaded = $('body').hasClass('gd-multi-datepicker') ? true : false;
        if (!gd_datepicker_loaded) {
            $('body').addClass('gd-multi-datepicker');
        }
    });
    </script>
    <?php
    $taxonomies = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type = %s  ORDER BY sort_order", array($post_type)));

    ob_start();
    if (!empty($taxonomies)):
        global $as_fieldset_start;
        $as_fieldset_start = 0;
        foreach ($taxonomies as $taxonomy_obj) {
            $taxonomy_obj = stripslashes_deep( $taxonomy_obj ); // strip slashes

            $html         = '';
            $htmlvar_name = $taxonomy_obj->site_htmlvar_name;
            $field_type   = $taxonomy_obj->field_site_type;
            $field_info   = $taxonomy_obj;

            /**
             * Filter the output for search custom fields by htmlvar_name.
             *
             * Here we can remove or add new functions depending on the htmlvar_name.
             *
             * @param string $html       The html to be filtered (blank).
             * @param object $field_info The field object info.
             * @param string $post_type  The post type.
             */
            $html = apply_filters( "geodir_search_filter_field_output_var_{$htmlvar_name}", $html, $field_info, $post_type );

            if ( $html == '' && ( ! isset( $field_info->main_search ) || ! $field_info->main_search ) ) {
                /**
                 * Filter the output for search custom fields by $field_type.
                 *
                 * Here we can remove or add new functions depending on the $field_type.
                 *
                 * @param string $html       The html to be filtered (blank).
                 * @param object $field_info The field object info.
                 * @param string $post_type  The post type.
                 */
                $html = apply_filters( "geodir_search_filter_field_output_{$field_type}", $html, $field_info, $post_type );
            }

            echo $html;

        }

    if($as_fieldset_start>0){
        echo '</ul></div>'; //end the prev fieldset
    }
    endif;
    echo $html = ob_get_clean();
}

$geodir_search_main_array = array();
function geodir_search_add_to_main(){
    global $wpdb,$geodir_search_main_array,$geodir_search_post_type;

    $post_type = $geodir_search_post_type;
    if(!$post_type){$post_type = 'gd_place';}
    $acf = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type = %s AND main_search=1 ORDER BY sort_order", array($post_type)));

    if(empty( $acf )){return;}



    foreach( $acf as $cf){

        $site_htmlvar_name = $cf->site_htmlvar_name;
        $priority = (isset($cf->main_search_priority) && $cf->main_search_priority!='') ? $cf->main_search_priority : 10;
        $geodir_search_main_array[$priority][] = $cf;
        add_action( 'geodir_search_form_inputs', 'geodir_search_output_to_main', $priority );
    }
  // print_r($acf);
//    echo '###'.$post_type;


}
function geodir_search_output_to_main(){
    global $wpdb,$geodir_search_main_array,$geodir_search_post_type;

    $post_type = $geodir_search_post_type;
    if(!$post_type){$post_type = geodir_get_default_posttype();}


    if(empty($geodir_search_main_array)){return;}

    $tmp = array_values($geodir_search_main_array);
    $acf = array_shift($tmp );
    $geodir_search_main_array = $tmp;
    if(empty($acf)){return;}

    foreach($acf as $cf){
            echo apply_filters('geodir_search_output_to_main_'.$cf->field_site_type,'',$cf,$post_type);
    }
}

add_action( 'geodir_before_search_form', 'geodir_search_add_to_main', 0 );

add_filter( 'geodir_search_output_to_main_taxonomy', 'geodir_search_output_to_main_taxonomy', 10,3 );
function geodir_search_output_to_main_taxonomy($html,$cf,$post_type){

    $cf->field_input_type = 'SELECT';

    $args = array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true );

    $args = apply_filters( 'geodir_filter_term_args', $args, $cf->site_htmlvar_name );

    $terms = apply_filters( 'geodir_filter_terms', get_terms( $cf->site_htmlvar_name, $args ) );

    // let's order the child categories below the parent.
    $terms_temp = array();

    foreach ( $terms as $term ) {
        if ( $term->parent == '0' ) {
            $terms_temp[] = $term;

            foreach ( $terms as $temps ) {
                if ( $temps->parent != '0' && $temps->parent == $term->term_id ) {
                    $temps->name  = '- ' . $temps->name;
                    $terms_temp[] = $temps;
                }
            }
        }
    }

    $terms = $terms_temp;

    $html .= "<div class='gd-search-input-wrapper gd-search-field-cpt gd-search-field-taxonomy'>";
    $html .= str_replace(array('<li>','</li>'),'',geodir_advance_search_options_output( $terms, $cf, $post_type, stripslashes( __( $cf->front_search_title, 'geodirectory' ) )));
    $html .= "</div>";

    return $html;
}

add_filter( 'geodir_search_output_to_main_select', 'geodir_search_output_to_main_select', 10,3 );
add_filter( 'geodir_search_output_to_main_radio', 'geodir_search_output_to_main_select', 10,3 );
function geodir_search_output_to_main_select($html,$cf,$post_type){

    $cf->field_input_type = 'SELECT';

    global $wpdb;
    $select_fields_result = $wpdb->get_row( $wpdb->prepare( "SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array(
        $post_type,
        $cf->site_htmlvar_name
    ) ) );
    if ( in_array( $cf->field_input_type, array(
        'CHECK',
        'SELECT',
        'LINK',
        'RADIO'
    ) ) ) {
        // optgroup
        $terms = geodir_string_values_to_options( $select_fields_result->option_values, true );
    } else {
        $terms = explode( ',', $select_fields_result->option_values );
    }

    $html .= "<div class='gd-search-input-wrapper gd-search-field-cpt gd-search-" . $cf->site_htmlvar_name . "'>";
    $html .= str_replace(array('<li>','</li>'),'',geodir_advance_search_options_output( $terms, $cf, $post_type, stripslashes( __( $cf->front_search_title, 'geodirectory' ) )));
    $html .= "</div>";

    return $html;
}

add_filter( 'geodir_search_output_to_main_text', 'geodir_search_output_to_main_text', 10,3 );
function geodir_search_output_to_main_text($html,$cf,$post_type){

    //$cf->field_input_type = 'SELECT';

    $terms = array( 1 );

    $html .= "<div class='gd-search-input-wrapper gd-search-field-cpt gd-search-" . $cf->site_htmlvar_name . "'>";
    $html .= str_replace(array('<li>','</li>'),'',geodir_advance_search_options_output( $terms, $cf, $post_type, stripslashes( __( $cf->front_search_title, 'geodirectory' ) )));
    $html .= "</div>";

    return $html;
}

add_filter( 'geodir_search_output_to_main_checkbox', 'geodir_search_output_to_main_checkbox', 10,3 );
function geodir_search_output_to_main_checkbox($html,$cf,$post_type){

    $cf->field_input_type = 'SELECT';

    $terms = array();
    $terms[] = array(
        'label' => __('Yes','geodiradvancesearch'),
        'value' => 1,
        'optgroup' => ''
    );

    $html .= "<div class='gd-search-input-wrapper gd-search-field-cpt gd-search-" . $cf->site_htmlvar_name . "'>";
    $html .= str_replace(array('<li>','</li>'),'',geodir_advance_search_options_output( $terms, $cf, $post_type, stripslashes( __( $cf->front_search_title, 'geodirectory' ) )));
    $html .= "</div>";

    return $html;
}

add_filter( 'geodir_search_output_to_main_datepicker', 'geodir_search_output_to_main_datepicker', 10,3 );
function geodir_search_output_to_main_datepicker($html, $cf, $post_type){
    global $wpdb;

    $geodir_list_date_type = 'Y-m-d';

    if ($cf->site_htmlvar_name == 'event' && function_exists('geodir_event_date_format')) {
        $geodir_list_date_type = geodir_event_date_format();
    } else {
        $datepicker_formate = $wpdb->get_var( "SELECT `extra_fields`  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE `htmlvar_name` = '" . $cf->site_htmlvar_name . "' AND `post_type` = '" . $post_type . "'" );
        $datepicker_formate_arr = unserialize( $datepicker_formate );
        if ( !empty($datepicker_formate_arr['date_format']) ) {
            $geodir_list_date_type = $datepicker_formate_arr['date_format'];
        } else {
            $geodir_list_date_type  = 'yy-mm-dd';
        }
    }

    if (empty($geodir_list_date_type)) {
        $geodir_list_date_type = 'Y-m-d';
    }

    // Convert to jQuery UI datepicker format.
    $geodir_list_date_type  = geodir_date_format_php_to_jqueryui( $geodir_list_date_type  );

    ob_start();
    ?>
    <script type="text/javascript" language="javascript">
        jQuery(document).ready(function () {
            jQuery('.geodir_advance_search_widget form').each(function(index, obj){
                var $form = jQuery(this);
                jQuery(".s<?php echo $cf->site_htmlvar_name;?>", $form).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: '<?php echo $geodir_list_date_type;?>'
                });

                jQuery(".smin<?php echo $cf->site_htmlvar_name;?>", $form).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: '<?php echo $geodir_list_date_type;?>',
                    onClose: function (selectedDate) {
                        jQuery(".smax<?php echo $cf->site_htmlvar_name;?>", $form).datepicker("option", "minDate", selectedDate);
                    }
                });

                jQuery(".smax<?php echo $cf->site_htmlvar_name;?>", $form).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: '<?php echo $geodir_list_date_type;?>'
                });
            });
        });

    </script>
    <?php
    $html .= ob_get_clean();
    
    $field_label = $cf->front_search_title ? stripslashes( __( $cf->front_search_title, 'geodirectory' ) ) : __('Start date','geodiradvancesearch');
    $field_desc = $cf->field_desc ? stripslashes( __( $cf->field_desc, 'geodirectory' ) ) : __('End date','geodiradvancesearch');

    ob_start();
    if ( $cf->search_condition == 'SINGLE' && $cf->site_htmlvar_name != 'event' ) {
        $custom_value = isset( $_REQUEST[ 's' . $cf->site_htmlvar_name ] ) ? stripslashes_deep( esc_attr( $_REQUEST[ 's' . $cf->site_htmlvar_name ] ) ) : '';
        ?>
        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
        <input type="text" class="cat_input <?php echo $cf->site_htmlvar_name; ?> s<?php echo $cf->site_htmlvar_name; ?>"
               placeholder='<?php echo esc_attr( $field_label ); ?>'
               name="s<?php echo $cf->site_htmlvar_name; ?>"
               value="<?php echo esc_attr( $custom_value ); ?>"/>
        </div>
        <?php
    } elseif ( $cf->search_condition == 'SINGLE' && $cf->site_htmlvar_name == 'event' ) {
        $smincustom_value = isset( $_REQUEST[ $cf->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $cf->site_htmlvar_name . '_start' ] ) : '';
        ?>
        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
            <input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>"
                   placeholder='<?php echo esc_attr( $field_label ); ?>'
                   class='cat_input s<?php echo $cf->site_htmlvar_name; ?>'
                   name="<?php echo $cf->site_htmlvar_name; ?>_start"
                   field_type="text"/>
        </div>
        <?php
    } elseif ( $cf->search_condition == 'FROM' && $cf->site_htmlvar_name != 'event' ) {
        $smincustom_value = isset($_REQUEST[ 'smin' . $cf->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smin' . $cf->site_htmlvar_name ] ) : '';
        $smaxcustom_value = isset($_REQUEST[ 'smax' . $cf->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smax' . $cf->site_htmlvar_name ] ) : '';
        ?>
        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
        <input type='text' class='cat_input smin<?php echo $cf->site_htmlvar_name; ?>'
               placeholder='<?php echo esc_attr( $field_label ); ?>'
               name='smin<?php echo $cf->site_htmlvar_name; ?>'
               value='<?php echo $smincustom_value; ?>'>
        </div>
        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
        <input type='text' class='cat_input smax<?php echo $cf->site_htmlvar_name; ?>'
               placeholder='<?php echo esc_attr( $field_desc); ?>'
               name='smax<?php echo $cf->site_htmlvar_name; ?>'
               value='<?php echo $smaxcustom_value; ?>'>
        </div><?php
    }  elseif ( $cf->search_condition == 'FROM' && $cf->site_htmlvar_name == 'event' ) {
        $smincustom_value = isset( $_REQUEST[ $cf->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $cf->site_htmlvar_name . '_start' ] ) : '';
        $smaxcustom_value = isset( $_REQUEST[ $cf->site_htmlvar_name . '_end' ] ) ? esc_attr( $_REQUEST[ $cf->site_htmlvar_name . '_end' ] ) : '';
        ?>

        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
            <input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>"
                   placeholder='<?php echo esc_attr( $field_label ); ?>'
                   class='cat_input smin<?php echo $cf->site_htmlvar_name; ?>'
                   name="<?php echo $cf->site_htmlvar_name; ?>_start"
                   field_type="text"/>
            </div>
        <div class='gd-search-input-wrapper gd-search-field-cpt gd-search-<?php echo $cf->site_htmlvar_name; ?>'>
            <input type="text" value="<?php echo esc_attr( $smaxcustom_value ); ?>"
                   placeholder='<?php echo esc_attr( $field_desc ); ?>'
                   class='cat_input smax<?php echo $cf->site_htmlvar_name; ?>'
                   name="<?php echo $cf->site_htmlvar_name; ?>_end"
                   field_type="text"/>
        </div>
        <?php
    }

    $html .= ob_get_clean();

    return $html;
}

function geodir_advance_search_options_output($terms,$taxonomy_obj,$post_type,$title=''){
    ob_start();

    $geodir_search_field_begin = '';
    $geodir_search_field_end   = '';

    if ( $taxonomy_obj->field_input_type == 'SELECT' ) {

        if($title!=''){
            $select_default = __( $title, 'geodiradvancesearch' );
        }else{
            $select_default = __( 'Select option', 'geodiradvancesearch' );
        }

        $geodir_search_field_begin = '<li><select name="s' . $taxonomy_obj->site_htmlvar_name . '[]' . '" class="cat_select"> <option value="" >' . $select_default  . '</option>';
        $geodir_search_field_end   = '</select></li>';
    }
    if ( ! empty( $terms ) ) {

        $expand_custom_value = $taxonomy_obj->expand_custom_value;
        $field_input_type    = $taxonomy_obj->field_input_type;

        $expand_search = 0;
        if ( ! empty( $taxonomy_obj->expand_search ) && ( $field_input_type == 'LINK' || $field_input_type == 'CHECK' || $field_input_type == 'RADIO' || $field_input_type == 'RANGE' ) ) {
            $expand_search = (int) $taxonomy_obj->expand_search;
        }

        $moreoption = '';
        if ( ! empty( $expand_search ) && $expand_search > 0 ) {
            if ( $expand_custom_value ) {
                $moreoption = $expand_custom_value;
            } else {
                $moreoption = 5;
            }
        }


        $classname = '';
        $increment = 1;

        echo $geodir_search_field_begin;
        
        foreach ( $terms as $term ) {

           if(is_array( $term ) && isset($term['value']) && $term['value']==''){ continue;}

            $custom_term = is_array( $term ) && ! empty( $term ) && isset( $term['label'] ) ? true : false;

            $option_label = $custom_term ? $term['label'] : false;
            $option_value = $custom_term ? $term['value'] : false;
            $optgroup     = $custom_term && ( $term['optgroup'] == 'start' || $term['optgroup'] == 'end' ) ? $term['optgroup'] : null;

            if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                $classname = 'class="more"';
            }

            if ( $taxonomy_obj->field_site_type != 'taxonomy' ) {
                if ( $custom_term ) {
                    $term          = (object) $option_value;
                    $term->term_id = $option_value;
                    $term->name    = $option_label;
                } else {
                    $select_arr = array();
                    if ( isset( $term ) && ! empty( $term ) ) {
                        $select_arr = explode( '/', $term );
                    }

                    $value         = $term;
                    $term          = (object) $term;
                    $term->term_id = $value;
                    $term->name    = $value;

                    if ( isset( $select_arr[0] ) && $select_arr[0] != '' && isset( $select_arr[1] ) && $select_arr[1] != '' ) {
                        $term->term_id = $select_arr[1];
                        $term->name    = $select_arr[0];

                    }
                }
            }

            $geodir_search_field_selected     = false;
            $geodir_search_field_selected_str = '';
            $geodir_search_custom_value_str   = '';
            if ( isset( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) && is_array( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) && in_array( $term->term_id, $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) ) {
                $geodir_search_field_selected = true;
            }
            if ( isset( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) && $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] != '' ) {

                $geodir_search_custom_value_str = isset( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) ? stripslashes_deep( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) : '';
                if ( is_array( $geodir_search_custom_value_str ) ) {
                    $geodir_search_custom_value_str = array_map( 'esc_attr', $geodir_search_custom_value_str );
                } else {
                    $geodir_search_custom_value_str = esc_attr( $geodir_search_custom_value_str );
                }
            }
            switch ( $taxonomy_obj->field_input_type ) {
                case 'CHECK' :
                    if ( $custom_term && $optgroup != '' ) {
                        if ( $optgroup == 'start' ) {
                            echo '<li ' . $classname . '>' . __( $term->name, 'geodirectory' )  . '</li>';
                        }
                    } else {
                        if ( $geodir_search_field_selected ) {
                            $geodir_search_field_selected_str = ' checked="checked" ';
                        }
                        echo '<li ' . $classname . '><input type="checkbox" class="cat_check" name="s' . $taxonomy_obj->site_htmlvar_name . '[]" ' . $geodir_search_field_selected_str . ' value="' . $term->term_id . '" /> ' . __( $term->name, 'geodirectory' )  . '</li>';
                        $increment ++;
                    }
                    break;
                case 'RADIO' :
                    if ( $custom_term && $optgroup != '' ) {
                        if ( $optgroup == 'start' ) {
                            echo '<li ' . $classname . '>' . __( $term->name, 'geodirectory' )  . '</li>';
                        }
                    } else {
                        if ( $geodir_search_field_selected ) {
                            $geodir_search_field_selected_str = ' checked="checked" ';
                        }
                        echo '<li ' . $classname . '><input type="radio" class="cat_check" name="s' . $taxonomy_obj->site_htmlvar_name . '[]" ' . $geodir_search_field_selected_str . ' value="' . $term->term_id . '" /> ' . __( $term->name, 'geodirectory' ) . '</li>';
                        $increment ++;
                    }
                    break;
                case 'SELECT' :
                    if ( $custom_term && $optgroup != '' ) {
                        if ( $optgroup == 'start' ) {
                            echo '<optgroup label="' . esc_attr( __( $term->name, 'geodirectory' )  ) . '">';
                        } else {
                            echo '</optgroup>';
                        }
                    } else {
                        if ( $geodir_search_field_selected ) {
                            $geodir_search_field_selected_str = ' selected="selected" ';
                        }
                        if($term->term_id!=''){
                            echo '<option value="' . $term->term_id . '" ' . $geodir_search_field_selected_str . ' >' . __( $term->name, 'geodirectory' )  . '</option>';
                            $increment ++;
                        }

                    }
                    break;
                case 'LINK' :
                    if ( $custom_term && $optgroup != '' ) {
                        if ( $optgroup == 'start' ) {
                            echo '<li ' . $classname . '> ' . __( $term->name, 'geodirectory' )  . '</li>';
                        }
                    } else {
                        echo '<li ' . $classname . '><a href="' . trailingslashit( get_site_url() ) . '?geodir_search=1&stype=' . $post_type . '&s=+&s' . $taxonomy_obj->site_htmlvar_name . '%5B%5D=' . urlencode( $term->term_id ) . '">' . __( $term->name, 'geodirectory' ) . '</a></li>';
                        $increment ++;
                    }
                    break;
                case 'RANGE': ############# RANGE VARIABLES ##########

                {
                    $search_starting_value_f = $taxonomy_obj->search_min_value;
                    $search_starting_value   = $taxonomy_obj->search_min_value;
                    $search_maximum_value    = $taxonomy_obj->search_max_value;
                    $search_diffrence        = $taxonomy_obj->search_diff_value;

                    if ( empty( $search_starting_value ) ) {
                        $search_starting_value = 10;
                    }
                    if ( empty( $search_maximum_value ) ) {
                        $search_maximum_value = 50;
                    }
                    if ( empty( $search_diffrence ) ) {
                        $search_diffrence = 10;
                    }

                    $first_search_text  = $taxonomy_obj->first_search_text ? stripslashes( __( $taxonomy_obj->first_search_text, 'geodirectory' ) ) : '';
                    $last_search_text   = $taxonomy_obj->last_search_text ? stripslashes( __( $taxonomy_obj->last_search_text, 'geodirectory' ) ) : '';
                    $first_search_value = $taxonomy_obj->first_search_value;

                    if ( ! empty( $first_search_value ) ) {
                        $search_starting_value = $first_search_value;
                    }

                    if ( empty( $first_search_text ) ) {
                        $first_search_text = __( 'Less than', 'geodiradvancesearch' );
                    }
                    if ( empty( $last_search_text ) ) {
                        $last_search_text = __( 'More than', 'geodiradvancesearch' );
                    }

                    $j = $search_starting_value_f;
                    $k = 0;

                    $i                        = $search_starting_value_f;
                    $moreoption               = '';
                    $expand_custom_value      = $taxonomy_obj->expand_custom_value;
                    $expand_search            = $taxonomy_obj->expand_search;
                    if ( ! empty( $expand_search ) && $expand_search > 0 ) {
                        if ( $expand_custom_value ) {
                            $moreoption = $expand_custom_value;
                        } else {
                            $moreoption = 5;
                        }
                    }

                    switch ( $taxonomy_obj->search_condition ) {

                        case 'SINGLE':
                            $custom_value = isset( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) ? stripslashes_deep( esc_attr( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) ) : '';
                            ?>
                            <input type="text" class="cat_input"
                                   name="s<?php echo $taxonomy_obj->site_htmlvar_name; ?>"
                                   value="<?php echo esc_attr( $custom_value ); ?>"/> <?php
                            break;

                        case 'FROM':
                            $smincustom_value = isset($_REQUEST[ 'smin' . $taxonomy_obj->site_htmlvar_name ]) ? esc_attr( $_REQUEST[ 'smin' . $taxonomy_obj->site_htmlvar_name ] ) : '';
                            $smaxcustom_value = isset($_REQUEST[ 'smax' . $taxonomy_obj->site_htmlvar_name ]) ? esc_attr( $_REQUEST[ 'smax' . $taxonomy_obj->site_htmlvar_name ] ) : '';

                            $start_placeholder = apply_filters( 'gd_adv_search_from_start_ph_text', esc_attr( __( 'Start search value', 'geodiradvancesearch' ) ), $taxonomy_obj );
                            $end_placeholder   = apply_filters( 'gd_adv_search_from_end_ph_text', esc_attr( __( 'End search value', 'geodiradvancesearch' ) ), $taxonomy_obj );
                            ?>
                            <div class='from-to'>
                            <input type='number' min="0" step="1"
                                   class='cat_input <?php echo $taxonomy_obj->site_htmlvar_name; ?>'
                                   placeholder='<?php echo $start_placeholder; ?>'
                                   name='smin<?php echo $taxonomy_obj->site_htmlvar_name; ?>'
                                   value='<?php echo $smincustom_value; ?>'>
                            <input type='number' min="0" step="1"
                                   class='cat_input <?php echo $taxonomy_obj->site_htmlvar_name; ?>'
                                   placeholder='<?php echo $end_placeholder; ?>'
                                   name='smax<?php echo $taxonomy_obj->site_htmlvar_name; ?>'
                                   value='<?php echo $smaxcustom_value; ?>'>
                            </div><?php
                            break;
                        case 'LINK':

                            $link_serach_value = @esc_attr( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] );
                            $increment        = 1;
                            while ( $i <= $search_maximum_value ) {
                                if ( $k == 0 ) {
                                    $value = $search_starting_value . '-Less';
                                    ?>
                                    <li class=" <?php if ( $link_serach_value == $value ) {
                                        echo 'active';
                                    } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                        echo 'more';
                                    } ?>"><a
                                            href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php echo $first_search_text . ' ' . $search_starting_value; ?></a>
                                    </li>
                                    <?php
                                    $k ++;
                                } else {
                                    if ( $i <= $search_maximum_value ) {
                                        $value = $j . '-' . $i;
                                        if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 ) {
                                            $display_value = $j;
                                            $value         = $j . '-Less';
                                        } else {
                                            $display_value = '';
                                        }
                                        ?>
                                        <li class=" <?php if ( $link_serach_value == $value ) {
                                            echo 'active';
                                        } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                            echo 'more';
                                        } ?>"><a
                                                href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php if ( $display_value ) {
                                                    echo $display_value;
                                                } else {
                                                    echo $value;
                                                } ?></a></li>
                                        <?php
                                    } else {


                                        $value = $j . '-' . $i;
                                        if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 ) {
                                            $display_value = $j;
                                            $value         = $j . '-Less';
                                        } else {
                                            $display_value = '';
                                        }

                                        ?>
                                        <li class=" <?php if ( $link_serach_value == $value ) {
                                            echo 'active';
                                        } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                            echo 'more';
                                        } ?>"><a
                                                href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php if ( $display_value ) {
                                                    echo $display_value;
                                                } else {
                                                    echo $value;
                                                } ?></a>
                                        </li>
                                        <?php
                                    }
                                    $j = $i;
                                }

                                $i = $i + $search_diffrence;

                                if ( $i > $search_maximum_value ) {
                                    if ( $j != $search_maximum_value ) {
                                        $value = $j . '-' . $search_maximum_value;
                                        ?>
                                    <li class=" <?php if ( $link_serach_value == $value ) {
                                        echo 'active';
                                    } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                        echo 'more';
                                    } ?>"><a
                                            href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php echo $value; ?></a>
                                        </li><?php }
                                    if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 && $j == $search_maximum_value ) {
                                        $display_value = $j;
                                        $value         = $j . '-Less';
                                        ?>
                                        <li class=" <?php if ( $link_serach_value == $value ) {
                                            echo 'active';
                                        } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                            echo 'more';
                                        } ?>"><a
                                                href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php if ( $display_value ) {
                                                    echo $display_value;
                                                } else {
                                                    echo $value;
                                                } ?></a>
                                        </li>
                                        <?php
                                    }

                                    $value = $search_maximum_value . '-More';

                                    ?>
                                    <li class=" <?php if ( $link_serach_value == $value ) {
                                        echo 'active';
                                    } ?><?php if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                        echo 'more';
                                    } ?>"><a
                                            href="<?php echo trailingslashit( get_site_url() ); ?>?geodir_search=1&stype=<?php echo $post_type; ?>&s=+&s<?php echo $taxonomy_obj->site_htmlvar_name; ?>=<?php echo $value; ?>"><?php echo $last_search_text . ' ' . $search_maximum_value; ?></a>

                                    </li>

                                    <?php
                                }

                                $increment ++;

                            }
                            break;
                        case 'SELECT':

                            global $wpdb;
                            $cf =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array(
                                $post_type,
                                $taxonomy_obj->site_htmlvar_name
                            ) ) ,ARRAY_A );

                            $is_price = false;
                            if($cf){
                                $extra_fields = maybe_unserialize($cf['extra_fields']);
                                if(isset($extra_fields['is_price']) && $extra_fields['is_price']){
                                    $is_price = true;
                                }
                            }

                            if($title!=''){
                                $select_default = __( $title, 'geodiradvancesearch' );
                            }else{
                                $select_default = __( 'Select option', 'geodiradvancesearch' );
                            }

                            $custom_search_value = isset($_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 's' . $taxonomy_obj->site_htmlvar_name ] ) : '';

                            ?>
                            <li><select name="s<?php echo $taxonomy_obj->site_htmlvar_name; ?>"
                                    class="cat_select"
                                    id="">
                                <option
                                    value=""><?php echo esc_attr( $select_default); ?></option><?php

                                if ( $search_maximum_value > 0 ) {
                                    while ( $i <= $search_maximum_value ) {
                                        if ( $k == 0 ) {
                                            $value = $search_starting_value . '-Less';
                                            ?>
                                            <option
                                                value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                echo 'selected="selected"';
                                            } ?> ><?php echo $first_search_text . ' '; echo ($is_price ) ? geodir_currency_format_number($search_starting_value,$cf) :$search_starting_value; ?></option>
                                            <?php
                                            $k ++;
                                        } else {
                                            if ( $i <= $search_maximum_value ) {

                                                $jo = ($is_price ) ? geodir_currency_format_number($j,$cf) : $j;
                                                $io = ($is_price ) ? geodir_currency_format_number($i,$cf) : $i;
                                                $value = $j . '-' . $i;
                                                $valueo = $jo . '-' . $io;
                                                if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 ) {
                                                    $display_value = $jo;
                                                    $value         = $j . '-Less';
                                                } else {
                                                    $display_value = '';
                                                }
                                                ?>
                                                <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                    echo 'selected="selected"';
                                                } ?> ><?php if ( $display_value ) {
                                                        echo $display_value;
                                                    } else {
                                                        echo $valueo;
                                                    } ?></option>
                                                <?php
                                            } else {
                                                $jo = ($is_price ) ? geodir_currency_format_number($j,$cf) : $j;
                                                $io = ($is_price ) ? geodir_currency_format_number($i,$cf) : $i;
                                                $value = $j . '-' . $i;
                                                $valueo = $jo . '-' . $io;
                                                if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 ) {
                                                    $display_value = $jo;
                                                    $value         = $j . '-Less';
                                                } else {
                                                    $display_value = '';
                                                }
                                                ?>
                                                <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                    echo 'selected="selected"';
                                                } ?> ><?php if ( $display_value ) {
                                                        echo $display_value;
                                                    } else {
                                                        echo $valueo;
                                                    } ?></option>
                                                <?php
                                            }
                                            $j = $i;
                                        }


                                        $i = $i + $search_diffrence;

                                        if ( $i > $search_maximum_value ) {

                                            $jo = ($is_price ) ? geodir_currency_format_number($j,$cf) : $j;
                                            $io = ($is_price ) ? geodir_currency_format_number($i,$cf) : $i;
                                            $search_maximum_valueo = ($is_price ) ? geodir_currency_format_number($search_maximum_value,$cf) : $search_maximum_value;

                                            if ( $j != $search_maximum_value ) {
                                                $value = $j . '-' . $search_maximum_value;
                                                $valueo = $jo . '-' . $search_maximum_value;
                                                ?>
                                                <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                    echo 'selected="selected"';
                                                } ?> ><?php echo $valueo; ?></option>
                                                <?php
                                            }
                                            if ( $search_diffrence == 1 && $taxonomy_obj->searching_range_mode == 1 && $j == $search_maximum_value ) {
                                                $display_value = $j;
                                                $value         = $j . '-Less';
                                                $valueo         = $jo . '-Less';
                                                ?>
                                                <option
                                                    value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                    echo 'selected="selected"';
                                                } ?> ><?php if ( $display_value ) {
                                                        echo $display_value;
                                                    } else {
                                                        echo $valueo;
                                                    } ?></option>
                                                <?php
                                            }
                                            $value = $search_maximum_value . '-More';

                                            ?>
                                            <option
                                                value="<?php echo esc_attr( $value ); ?>" <?php if ( $custom_search_value == $value ) {
                                                echo 'selected="selected"';
                                            } ?> ><?php echo $last_search_text . ' ' . $search_maximum_valueo; ?></option>
                                            <?php
                                        }

                                    }
                                }
                                ?>
                            </select></li>
                            <?php
                            break;
                        case 'RADIO':


                            $uom      = get_option( 'geodir_search_dist_1' );
                            $dist_dif = $search_diffrence;

                            for ( $i = $dist_dif; $i <= $search_maximum_value; $i = $i + $dist_dif ) :
                                $checked = '';
                                if ( isset( $_REQUEST['sdist'] ) && $_REQUEST['sdist'] == $i ) {
                                    $checked = 'checked="checked"';
                                }
                                if ( $increment > $moreoption && ! empty( $moreoption ) ) {
                                    $classname = 'class="more"';
                                }
                                echo '<li ' . $classname . '><input type="radio" class="cat_check" name="sdist" ' . $checked . ' value="' . $i . '" />' . __( 'Within', 'geodiradvancesearch' ) . ' ' . $i . ' ' . __( $uom, 'geodirectory' ) . '</li>';
                                $increment ++;
                            endfor;
                            break;


                    }
                }
                    #############Range search###############
                    break;

                case "DATE":


                    break;

                default:

                    //print_r($taxonomy_obj);

                    if ( (isset( $taxonomy_obj->field_site_type ) && ( $taxonomy_obj->field_site_type == 'checkbox' ))
                         || (isset($taxonomy_obj->site_htmlvar_name) && $taxonomy_obj->site_htmlvar_name=='geodir_special_offers') ) {

                        if(isset($taxonomy_obj->site_htmlvar_name) && $taxonomy_obj->site_htmlvar_name=='geodir_special_offers') {
                            $field_type = 'checkbox';
                        }else{
                            $field_type = $taxonomy_obj->field_site_type;
                        }

                        $checked = '';
                        if ( $geodir_search_custom_value_str == '1' ) {
                            $checked = 'checked="checked"';
                        }

                        echo '<li><input ' . $checked . ' type="' . $field_type . '" class="cat_input" name="s' . $taxonomy_obj->site_htmlvar_name . '"  value="1" /> ' . __( 'Yes', 'geodiradvancesearch' ) . '</li>';

                    } else {
                        echo '<li><input type="' . $taxonomy_obj->field_input_type . '" class="cat_input" name="s' . $taxonomy_obj->site_htmlvar_name . '"  value="' . esc_attr( $geodir_search_custom_value_str ) . '" /></li>';
                    }
            }

        }

        echo $geodir_search_field_end;

        if ( ( $increment - 1 ) > $moreoption && ! empty( $moreoption ) && $moreoption > 0 ) {
            echo '<li class="bordernone"><span class="expandmore" onclick="javascript:geodir_search_expandmore(this);"> ' . __( 'More', 'geodiradvancesearch' ) . '</span></li>';
        }

        
    }

    return ob_get_clean();
}

function geodir_advance_search_button()
{
    global $wpdb,$geodir_search_post_type;
    $stype = $geodir_search_post_type;
    if (empty($stype)){
        $stype = geodir_get_default_posttype();
    }


    $rows = $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " where post_type= %s AND main_search!='1' ",$stype) );
    if ($rows > 0) {
        $new_style = get_option( 'geodir_show_search_old_search_from' ) ? false : true;
        if ( $new_style ) {
            $default_btn_value = '<i class="fa fa-cog" aria-hidden="true"></i>';
        }else{
            $default_btn_value = __('Customize My Search','geodiradvancesearch');
        }
        $btn_value = apply_filters('gd_adv_search_btn_value', $default_btn_value);
        $fa_class = '';
        if(strpos($btn_value, '&#') !== false){
            $fa_class = 'fa';
        }


        if ( $new_style ) {
            echo '<button class="showFilters ' . $fa_class . '" onclick="gdShowFilters(this); return false;">' . $btn_value . '</button>';
        }else{
            echo '<input type="button" value="' . esc_attr( $btn_value ) . '"  class="showFilters ' . $fa_class . '" onclick="gdShowFilters(this);">';
        }

        add_filter('body_class', 'geodir_advance_search_body_class'); // let's add a class to the body so we can style the new addition to the search
    }

}

function geodir_advance_search_body_class($classes)
{
    global $wpdb;

    $stype = geodir_get_current_posttype();
    if (empty($stype)){
        $post_types = geodir_get_posttypes();
        $stype = $post_types[0];
    }

    $rows = $wpdb->get_var("SELECT count(id) FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " where post_type= '" . $stype . "'");
    if ($rows > 0) {
        $classes[] = 'geodir_advance_search';
    }
    return $classes;
}

add_filter('body_class', 'geodir_advance_search_body_class'); // let's add a class to the body so we can style the new addition to the search

function geodir_advance_search_form()
{
    global $geodir_search_post_type;

    $stype = $geodir_search_post_type;


    global $current_term;


    // if no post type found then find the default
    if($stype==''){
        $stype = geodir_get_default_posttype();
    }


    if (!empty($current_term))
        $_REQUEST['scat'][] = $current_term->term_id;


    $style = 'style="display:none;"';

    ?>
    <div class="geodir-filter-container">
        <div class="customize_filter customize_filter-in clearfix <?php echo 'gd-filter-' . $stype;?>" <?php echo $style;?>>
            <div class="customize_filter_inner">
                <div class="clearfix">
                    <?php do_action('geodir_search_fields_before', $stype);?>
                    <?php do_action('geodir_search_fields', $stype);?>
                    <?php do_action('geodir_search_fields_after', $stype);?>
                </div>
            </div>
            <div class="geodir-advance-search">
                <?php echo geodir_search_form_submit_button();?>
            </div>
        </div>
    </div>
    <?php
}

function geodir_advance_search_after_post_type_deleted($post_type = '')
{
    global $wpdb;
    if ($post_type != '') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type=%s", array($post_type)));
    }
}

function geodir_advance_search_after_custom_field_deleted($id, $site_htmlvar_name, $post_type)
{
    global $wpdb;

    if ($site_htmlvar_name != '' && $post_type != '') {
        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE site_htmlvar_name=%s AND  post_type=%s", array($site_htmlvar_name, $post_type)));
    }
}

function geodir_advance_search_get_advance_search_fields($post_type)
{
    global $wpdb;

    $post_type = $post_type != '' ? $post_type : 'gd_place';

    $sql = $wpdb->prepare("SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " WHERE post_type = %s ORDER BY sort_order ASC", array($post_type));
    $fields = $wpdb->get_results($sql);
    return $fields;
}

function geodir_advance_search_field_option_values($post_type, $htmlvar_name)
{
    global $wpdb;

    $post_type = $post_type != '' ? $post_type : 'gd_place';

    $sql = $wpdb->prepare("SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array($post_type, $htmlvar_name));

    $option_values = $wpdb->get_var($sql);

    return $option_values;
}

function geodir_set_near_me_range()
{
    global $gd_session;

    $near_me_range = get_option('geodir_search_dist_1') == 'km' ? (int)$_POST['range'] * 0.621371192 : (int)$_POST['range'];

    $gd_session->set('near_me_range', $near_me_range);

    $json = array();
    $json['near_me_range'] = $near_me_range;
    wp_send_json($json);
}

###########################################################
############# SHARE LOCATION FUNCTIONS START ##############
###########################################################

function geodir_get_request_param()
{
    global $current_term, $wp_query;

    $request_param = array();

    if (is_tax() && geodir_get_taxonomy_posttype() && is_object($current_term)) {

        $request_param['geo_url'] = 'is_term';
        $request_param['geo_term_id'] = $current_term->term_id;
        $request_param['geo_taxonomy'] = $current_term->taxonomy;

    } elseif (is_post_type_archive() && in_array(get_query_var('post_type'), geodir_get_posttypes())) {

        $request_param['geo_url'] = 'is_archive';
        $request_param['geo_posttype'] = get_query_var('post_type');

    } elseif (is_author() && isset($_REQUEST['geodir_dashbord'])) {
        $request_param['geo_url'] = 'is_author';
        $request_param['geo_posttype'] = esc_attr($_REQUEST['stype']);
    } elseif (is_search() && isset($_REQUEST['geodir_search'])) {
        $request_param['geo_url'] = 'is_search';
        $request_param['geo_request_uri'] = esc_attr($_SERVER['QUERY_STRING']);
    } else {
        $request_param['geo_url'] = 'is_location';
    }

    return json_encode($request_param);
}

function geodir_localize_all_share_location_js_msg()
{
    global $geodir_addon_list, $wpdb;

    if ($default_near_text = get_option('geodir_near_field_default_text')) {
    } else {
        $default_near_text = NEAR_TEXT;
    }
    
    $redirect = geodir_search_onload_redirect();
    
    $arr_alert_msg = array(
        'geodir_advanced_search_plugin_url' => GEODIRADVANCESEARCH_PLUGIN_URL,
        'geodir_plugin_url' => geodir_plugin_url(),
        'geodir_admin_ajax_url' => admin_url('admin-ajax.php'),
        'request_param' => geodir_get_request_param(),
        'msg_Near' => __("Near:", 'geodiradvancesearch'),
        'default_Near' => $default_near_text,
        'msg_Me' => __("Me", 'geodiradvancesearch'),
        'unom_dist' => (get_option('geodir_search_dist_1') == 'km') ? __("km", 'geodiradvancesearch') : __("miles", 'geodiradvancesearch'),
        'autocomplete_field_name' => (get_option('geodir_autocompleter_matches_label')) ? get_option('geodir_autocompleter_matches_label') : 's',
        'geodir_enable_autocompleter_near' => get_option('geodir_enable_autocompleter_near'),
        'geodir_enable_autocompleter' => get_option('geodir_enable_autocompleter'),
        'geodir_autocompleter_autosubmit_near' => get_option('geodir_autocompleter_autosubmit_near'),
        'geodir_autocompleter_autosubmit' => get_option('geodir_autocompleter_autosubmit'),
        'geodir_location_manager_active' => (isset($geodir_addon_list['geodir_location_manager'])) ? '1' : '0',
        'msg_User_defined' => __("User defined", 'geodiradvancesearch'),
        'ask_for_share_location' => ($redirect == 'nearest' && apply_filters('geodir_ask_for_share_location', false)),
        //'geodir_autolocate_disable' => get_option('geodir_autolocate_disable'),
        'geodir_autolocate_ask' => ($redirect == 'nearest' && get_option('geodir_autolocate_ask')),
        'geodir_autolocate_ask_msg' => __('Do you wish to be geolocated to listings near you?', 'geodiradvancesearch'),
        'UNKNOWN_ERROR' => __('Unable to find your location.', 'geodiradvancesearch'),
        'PERMISSION_DENINED' => __('Permission denied in finding your location.', 'geodiradvancesearch'),
        'POSITION_UNAVAILABLE' => __('Your location is currently unknown.', 'geodiradvancesearch'),
        'BREAK' => __('Attempt to find location took too long.', 'geodiradvancesearch'),
        'GEOLOCATION_NOT_SUPPORTED' => __('Geolocation is not supported by this browser.', 'geodiradvancesearch'),
        // start not show alert msg
        'DEFAUTL_ERROR' => __('Browser unable to find your location.', 'geodiradvancesearch'),
        // end not show alert msg
        'text_more' => __('More', 'geodiradvancesearch'),
        'text_less' => __('Less', 'geodiradvancesearch'),
        'msg_In' => __('In:', 'geodirectory'),
        'txt_in_country' => __('(Country)', 'geodiradvancesearch'),
        'txt_in_region' => __('(Region)', 'geodiradvancesearch'),
        'txt_in_city' => __('(City)', 'geodiradvancesearch'),
        'txt_in_hood' => __('(Neighbourhood)', 'geodiradvancesearch'),
        'compass_active_color' => '#087CC9',
        'onload_redirect' => $redirect,
        'onload_askRedirect' => (bool)geodir_search_ask_onload_redirect(),
        'onload_redirectLocation' => $redirect == 'location' ? geodir_location_permalink_url( geodir_get_location_link() ) : '',
        'search_new_style' => get_option('geodir_show_search_old_search_from') ? '0' : '1'
    );

    /**
     * Filter the JS message array before it is out put.
     *
     * @since 1.4.0
     * @param array $arr_alert_msg The array of messages to be output.
     */
    $arr_alert_msg = apply_filters('geodir_advanced_search_js_msg',$arr_alert_msg );


    foreach ($arr_alert_msg as $key => $value) {
        if (!is_scalar($value))
            continue;
        $arr_alert_msg[$key] = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
    }

    $script = "var geodir_advanced_search_js_msg = " . json_encode($arr_alert_msg) . ';';
    echo '<script>';
    echo $script;
    echo '</script>';
}

function geodir_share_location() {
    $redirect_url = apply_filters('geodir_share_location', get_site_url());
    echo wp_validate_redirect($redirect_url, 'OK');
    die;
}

function geodir_do_not_share_location() {
    global $gd_session;
    $gd_session->set('gd_onload_redirect_done', 1);
    $gd_session->set('gd_location_shared', 1);
    echo 'OK';
    exit;
}

###########################################################
############# SHARE LOCATION FUNCTIONS END ################
###########################################################


###########################################################
############# AUTOCOMPLETE FUNCTIONS START ################
###########################################################
function geodir_autocompleter_options($arr = array()) {
    $is_location_plugin = geodir_search_location_is_active();

    $arr[] = array('name' => __('Autocompleter for GeoDirectory', 'geodiradvancesearch'), 'type' => 'no_tabs', 'desc' => '', 'id' => 'geodir_autocompleter_options');

    $arr[] = array('name' => __('Search Autocompleter Settings', 'geodiradvancesearch'), 'type' => 'sectionstart', 'id' => 'geodir_ajax_autocompleter_alert_options');

    $arr[] = array(
        'name' => __('Enable Search autocompleter:', 'geodiradvancesearch'),
        'desc' => __('If an option is selected, the autocompleter for Search is enabled.', 'geodiradvancesearch'),
        'id' => 'geodir_enable_autocompleter',
        'type' => 'checkbox',
        'css' => '',
        'std' => '1'
    );

    $arr[] = array(
        'name' => __('Autosubmit the form on select a Search option:', 'geodiradvancesearch'),
        'desc' => __('If an option is selected, the search form automatically is triggered when selecting a Search option.', 'geodiradvancesearch'),
        'id' => 'geodir_autocompleter_autosubmit',
        'type' => 'checkbox',
        'css' => '',
        'std' => '1'
    );

    $arr[] = array(
        'name' => __('Min chars needed to trigger autocomplete', 'geodiradvancesearch'),
        'desc' => __('Enter the minimum characters users need to be typed to trigger auto complete ex. 2', 'geodiradvancesearch'),
        'id' => 'geodir_autocompleter_min_chars',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '3'
    );

    $arr[] = array(
        'name' => __('Max Results to be returned by autocomplete', 'geodiradvancesearch'),
        'desc' => __('Enter the maximum number of results to be returned by autocomplete ex. 10', 'geodiradvancesearch'),
        'id' => 'geodir_autocompleter_max_results',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '10'
    );
    
    if ($is_location_plugin) {
        $arr[] = array(
            'name' => __('Enable Location Filter:', 'geodiradvancesearch'),
            'desc' => __('If an option is selected, the autocompleter search results will be filtered with current location.', 'geodiradvancesearch'),
            'id' => 'geodir_autocompleter_filter_location',
            'type' => 'checkbox',
            'css' => '',
            'std' => ''
        );
    }

    $arr[] = array('type' => 'sectionend', 'id' => 'geodir_ajax_autocompleter_alert_options');

    if ($is_location_plugin) {
        $arr[] = array('name' => __('Near Autocompleter Settings', 'geodiradvancesearch'), 'type' => 'sectionstart', 'id' => 'geodir_autocompleter_options_near');

        $arr[] = array(
            'name' => __('Enable Near autocompleter:', 'geodiradvancesearch'),
            'desc' => __('If an option is selected, the autocompleter for Near is enabled.', 'geodiradvancesearch'),
            'id' => 'geodir_enable_autocompleter_near',
            'type' => 'checkbox',
            'css' => '',
            'std' => '1'
        );

        $arr[] = array(
            'name' => __('Autosubmit the form on select a Near option:', 'geodiradvancesearch'),
            'desc' => __('If an option is selected, the search form automatically is triggered when selecting a Near option.', 'geodiradvancesearch'),
            'id' => 'geodir_autocompleter_autosubmit_near',
            'type' => 'checkbox',
            'css' => '',
            'std' => '0'
        );


        $arr[] = array('type' => 'sectionend', 'id' => 'geodir_autocompleter_options_near');
    
        // First time load redirect settings
        $arr[] = array( 'name' => __('Redirect Settings On First Time Load', 'geodiradvancesearch'), 'type' => 'sectionstart', 'id' => 'geodir_search_redirect_settings');
        $arr[] = array(
            'name'          => __('First time load redirect', 'geodiradvancesearch'),
            'desc'          => __('No redirect', 'geodiradvancesearch'),
            'id'            => 'geodir_first_load_redirect',
            'std'           => 'no',
            'type'          => 'radio',
            'value'         => 'no',
            'radiogroup'    => 'start'
        );
        $arr[] = array(
            'name'          => __('Redirect to nearest location', 'geodiradvancesearch'),
            'desc'          => __('Redirect to nearest location <i>(on first time load users will be auto geolocated and redirected to nearest geolocation found)</i>', 'geodiradvancesearch'),
            'id'            => 'geodir_first_load_redirect',
            'std'           => 'no',
            'type'          => 'radio',
            'value'         => 'nearest',
            'radiogroup'    => ''
        );
        $arr[] = array(
            'name'          => __('Redirect to default location', 'geodiradvancesearch'),
            'desc'          => __('Redirect to default location <i>(on first time load users will be redirected to default location</i>', 'geodiradvancesearch)'),
            'id'            => 'geodir_first_load_redirect',
            'std'           => 'no',
            'type'          => 'radio',
            'value'         => 'location',
            'radiogroup'    => 'end'
        );
        $arr[] = array('type' => 'sectionend', 'id' => 'geodir_search_redirect_settings');
    }

    $arr[] = array('name' => __('GeoLocation Settings', 'geodiradvancesearch'), 'type' => 'sectionstart', 'id' => 'geodir_ajax_geolocation_options');
    /*
    if (defined('POST_LOCATION_TABLE')) {
        $arr[] = array(
            'name' => __('Disable geolocate on first load:', 'geodiradvancesearch'),
            'desc' => __('If this option is selected, users will not be auto geolocated on first load.', 'geodiradvancesearch'),
            'id' => 'geodir_autolocate_disable',
            'type' => 'checkbox',
            'css' => '',
            'std' => '0'
        );
    }
    */

    if ($is_location_plugin) {
        $arr[] = array(
            'name' => __('Ask user if they wish to be geolocated', 'geodiradvancesearch'),
            'desc' => __('If this option is selected, users will be asked if they with to be geolocated via a popup', 'geodiradvancesearch'),
            'id' => 'geodir_autolocate_ask',
            'type' => 'checkbox',
            'css' => '',
            'std' => '0'
        );
    }
    
    $arr[] = array(
        'name' => __('Default Near Me miles limit (1-200)', 'geodiradvancesearch'),
        'desc' => __('Enter whole number only ex. 40 (Tokyo is largest city in the world @40 sq miles) LEAVE BLANK FOR NO DISTANCE LIMIT', 'geodiradvancesearch'),
        'id' => 'geodir_near_me_dist',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '40' // Default value for the page title - changed in settings
    );

    $arr[] = array('type' => 'sectionend', 'id' => 'geodir_autocompleter_options');

    $arr = apply_filters('geodir_ajax_geolocation_options', $arr);

    return $arr;
}

function geodir_adminpage_advanced_search($tabs)
{
    $tabs['advanced_search_fields'] = array('label' => __('Advanced Search', 'geodiradvancesearch'));

    return $tabs;
}

function geodir_autocompleter_options_form($tab)
{
    switch ($tab) {
        case 'advanced_search_fields':
            geodir_admin_fields(geodir_autocompleter_options()); ?>
			<p class="submit">
        <input class="button-primary" type="submit" name="geodir_autocompleter_save"  value="<?php _e('Save changes', 'geodiradvancesearch');?>">
        </p>
			</div> <?php
            break;

        case 'geolocation_fields':
            geodir_admin_fields(geodir_autocompleter_options()); ?>
			<p class="submit">
        <input class="button-primary" type="submit" name="geodir_autocompleter_save"  value="<?php _e('Save changes', 'geodiradvancesearch');?>">
        </p>
			</div> <?php
            break;
    }
}

function geodir_autocompleter_adminmenu()
{
    add_options_page('Autocompleter Options', 'Autocompleter', 8, __FILE__, 'geodir_autocompleter_options');
}

function geodir_autocompleter_ajax_actions()
{
    global $autocompleter_post_type;

    if (isset($_REQUEST['q']) && $_REQUEST['q'] && isset($_REQUEST['post_type'])) {
        autocompleters();
    }
    exit;
}

function geodir_autocompleter_near_ajax_actions()
{
    global $autocompleter_post_type;

    if (isset($_REQUEST['q']) && $_REQUEST['q']) {
        autocompleters_near();
    }
    exit;
}

function autocompleters() {
    global $wpdb, $plugin_prefix;
    $is_location_plugin = geodir_search_location_is_active();

    $geodir_terms_autocomplete = "''";

    $post_types = geodir_get_posttypes('array');

    $post_type_tax = array();
    $words = array();

    $gd_post_type = isset($_REQUEST['post_type']) ? esc_attr($_REQUEST['post_type']) : 'gd_place';

    if (!empty($post_types) && is_array($post_types) && array_key_exists($gd_post_type, $post_types)) {
        if (!empty($post_types[$gd_post_type]) && is_array($post_types[$gd_post_type]) && array_key_exists('taxonomies', $post_types[$gd_post_type])) {
            foreach ($post_types[$gd_post_type]['taxonomies'] as $geodir_taxonomy) {
                $post_type_tax[] = $geodir_taxonomy;
            }
        }
    }

    if (!empty($post_type_tax)) {
        $geodir_terms_autocomplete = "'" . implode("','", $post_type_tax) . "'";
    }

    $gt_posttypes_autocomplete = "'" . $gd_post_type . "'";
    $results = (get_option('gd_autocompleter_results') != false) ? get_option('autocompleter_results') : 1;
    $search = isset($_GET['q']) ? $_GET['q'] : '';
    
    $location_filter = $is_location_plugin && get_option('geodir_autocompleter_filter_location') ? true : false;
    if ($location_filter && function_exists('geodir_cpt_no_location') && geodir_cpt_no_location($gd_post_type)) {
        $location_filter = false;
    }

    if (strlen($search)) {
        switch ($results) {
            case 1:
                $limit = get_option('geodir_autocompleter_max_results', 10);
                $limit_q = " LIMIT $limit ";
                
                $term_where = '';
                $join = '';
                $where = '';
                $where_params = array();
                $where_params[] = '%' . $search . '%';
                
                $gd_country = '';
                $gd_region = '';
                $gd_city = '';
                $gd_neighbourhood = '';

                if ($location_filter && !empty($_REQUEST['_ltype']) && !empty($_REQUEST['_lval'])) {
                    $location_type = (int)$_REQUEST['_ltype'];
                    $location_id = (int)$_REQUEST['_lval'];
                    $location = geodir_get_location_by_id('', $location_id);
                    
                    if (!empty($location)) {
                        $gd_country = $location->country_slug;
                        
                        if ($location_type == 2) {
                            $gd_region = $location->region_slug;
                        } else if ($location_type == 3 || $location_type == 4) {
                            $gd_region = $location->region_slug;
                            $gd_city = $location->city_slug;
                            
                            if ($location_type == 4 && !empty($_REQUEST['_lhood']) && get_option('location_neighbourhoods') && $neighbourhood = geodir_location_get_neighbourhood_by_id((int)$_REQUEST['_lhood'])) {
                                $gd_neighbourhood = $neighbourhood->neighbourhood_slug;
                            }
                        }
                    }
                }


                $join = '';
                if ($gd_country || $gd_region || $gd_city || $gd_neighbourhood) {                    
                    if ($gd_country) {
                        $where .= " AND pd.post_locations LIKE '%%,[" . $gd_country . "]'";
                    }
                    
                    if ($gd_region) {
                        $where .= " AND pd.post_locations LIKE '%%,[" . $gd_region . "],%%'";
                    }

                    if ($gd_city) {
                        $where .= " AND pd.post_locations LIKE '[" . $gd_city . "],%%'";
                    }
                    
                    if ($gd_neighbourhood) {
                        $where .= " AND pd.post_neighbourhood LIKE '" .  $gd_neighbourhood . "'";
                    }

                    //$join .= " LEFT JOIN " . $plugin_prefix . $gd_post_type . "_detail pd ON tr.term_taxonomy_id = tt.term_taxonomy_id "; //@todo see below note
                }
                
                //$term_where = $where;//@todo limiting category searches by location has problems on large DB, find better way to do it.

                ########### WPML ###########
                if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE && (geodir_wpml_is_taxonomy_translated($gd_post_type . 'category') || geodir_wpml_is_taxonomy_translated($gd_post_type . '_tags'))) {
                    $join .= " JOIN " . $wpdb->prefix . "icl_translations AS icl_t ON icl_t.element_id = tt.term_id";

                    $term_where .= " AND icl_t.element_type IN ('tax_" . $gd_post_type . "category', 'tax_" . $gd_post_type . "_tags') AND icl_t.language_code='" . ICL_LANGUAGE_CODE . "' ";
                }
                ########### WPML ###########

                // get matching terms
                $query = $wpdb->prepare(
                            "SELECT concat( t.name, '|', sum( count ) ) name, sum( count ) cnt 
                             FROM " . $wpdb->prefix . "terms t, " . $wpdb->prefix . "term_taxonomy tt 
                             LEFT JOIN " . $wpdb->prefix . "term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                             " . $join . " 
                             WHERE t.term_id = tt.term_id 
                             AND t.name LIKE %s 
                             AND tt.taxonomy in (" . $geodir_terms_autocomplete . ") " . $term_where . " 
                             GROUP BY t.name ORDER BY cnt 
                             DESC $limit_q",
                            $where_params
                        );

                /**
                 * Lets you filter if terms should be included in the advanced search autocompleter or not.
                 *
                 * @since 1.4.93
                 */
                $include_terms = apply_filters('geodir_advance_search_autocompleters_terms',true);
                if($include_terms){
                    $words1 = $wpdb->get_results($query);
                }else{
                    $words1 = array();
                }


                $join = '';
                ########### WPML ###########
                if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE && geodir_wpml_is_post_type_translated($gd_post_type)) {
                    $join .= " JOIN " . $wpdb->prefix . "icl_translations AS icl_t ON icl_t.element_id = p.ID";
                    $where .= " AND icl_t.element_type='post_" . $gd_post_type . "' AND icl_t.language_code='" . ICL_LANGUAGE_CODE . "' ";
                }
                ########### WPML ###########
                
                $query = $wpdb->prepare(
                        "SELECT p.post_title AS name, p.ID FROM $wpdb->posts AS p INNER JOIN " . $plugin_prefix . $gd_post_type . "_detail AS pd ON pd.post_id = p.ID " . $join . " WHERE p.post_status = 'publish' AND p.post_type = " . $gt_posttypes_autocomplete . " AND p.post_date < '" . current_time('mysql') . "' AND p.post_title LIKE %s " . $where . " GROUP BY p.ID ORDER BY p.post_title $limit_q",
                        $where_params
                    );
                $words2 = $wpdb->get_results($query);

                $words = array_merge((array)$words1, (array)$words2);
                asort($words);
                break;
        }

        $keywords = array();
        foreach ($words as $word) {
            $keyword = $word->name;
            if ($results > 0) {
                $keyword .= isset($word->ID) && isset($word->ID) > 0 ? '|' . get_permalink($word->ID) : '|';
            } else {
                $keyword = $word->name;
            }

            if (!in_array($keyword, $keywords)) {
                $keywords[] = $keyword;

                if (count($keywords) == 100) {
                    break;
                }
            }
        }

        /*
         * Filter the autocomplete search for results array.
         *
         * @since 1.3.4
         * @param array $keywords The keywords array of results to return.
         * @param string $gd_post_type The post type being queried.
         * @param array $words The array of results from the search query.
         */
        $keywords = apply_filters('geodir_advance_search_autocompleters', $keywords, $gd_post_type, $words);
        echo implode("\n", $keywords);
    }
    exit;
}



function autocompleters_near()
{
    global $wpdb;

    if (!defined('POST_LOCATION_TABLE')) {
        return;
    }

    $search = isset($_GET['q']) ? $_GET['q'] : '';
    if (!$search) {
        return;
    }
    
    $country_found = geodir_get_country_by_name($search);

    if ( $country_found && $country_found != __($country_found, 'geodirectory') ) {
        $countries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . POST_LOCATION_TABLE . " WHERE country LIKE %s OR country_slug LIKE %s OR country LIKE %s GROUP BY country LIMIT 3",
                array($search . '%', $search . '%', __($country_found, 'geodirectory') . '%')
            )
        );
    } else {
        $countries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . POST_LOCATION_TABLE . " WHERE country LIKE %s OR country_slug LIKE %s GROUP BY country LIMIT 3",
                array($search . '%', $search . '%')
            )
        );
    }

    if (!empty($countries)) {
        foreach ($countries as $country) {
            echo __($country->country, 'geodirectory') . " <small class='gd-small-country'>" . __('(Country)', 'geodiradvancesearch') . "</small> |" . __($country->country, 'geodirectory') . "|" . $country->location_id . "|1 \n";
        }
    }

    $regions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT location_id, region, country FROM " . POST_LOCATION_TABLE . " WHERE CONCAT(region, ' ', country) LIKE %s OR CONCAT(region, ',', country) LIKE %s OR CONCAT(region, ', ', country) LIKE %s GROUP BY country, region ORDER BY region, country LIMIT 3",
            array($search . '%', $search . '%', $search . '%')
        )
    );

    if (!empty($regions)) {
        foreach ($regions as $region) {
            $location_name = $region->region;
            if ((int)geodir_location_check_duplicate('region', $location_name) > 1) {
                $country_iso2 = geodir_location_get_iso2($region->country);
                $country = $country_iso2 != '' ? $country_iso2 : __($region->country, 'geodirectory');
                $location_name .= ', ' . $country;
            }

            echo $location_name . " <small class='gd-small-region'>" . __('(Region)', 'geodiradvancesearch') . "</small> |" . $region->region . "|" . $region->location_id . "|2 \n";
        }
    }

    $cities = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT location_id, region, city FROM " . POST_LOCATION_TABLE . " WHERE CONCAT(city, ' ', region) LIKE %s OR CONCAT(city, ',', region) LIKE %s OR CONCAT(city, ', ', region) LIKE %s GROUP BY country, region, city ORDER BY city, region, country LIMIT 3",
            array($search . '%', $search . '%', $search . '%')
        )
    );

    if (!empty($cities)) {
        foreach ($cities as $city) {
            $location_name = $city->city;
            if ((int)geodir_location_check_duplicate('city', $location_name) > 1) {
                $location_name .= ', ' . $city->region;
            }

            echo $location_name . " <small class='gd-small-city'>" . __('(City)', 'geodiradvancesearch') . "</small> |" . $city->city . "|" . $city->location_id . "|3 \n";
        }
    }

    if (get_option('location_neighbourhoods')) {
        $sql = $wpdb->prepare("SELECT hood_id, hood_name, location_id, city FROM " . POST_NEIGHBOURHOOD_TABLE . " AS h LEFT JOIN " . POST_LOCATION_TABLE . " As l ON l.location_id = h.hood_location_id WHERE CONCAT(hood_name, ' ', city) LIKE %s OR CONCAT(hood_name, ',', city) LIKE %s OR CONCAT(hood_name, ', ', city) LIKE %s GROUP BY country, region, city, hood_name ORDER BY hood_name, city, region, country LIMIT 3", array($search . '%', $search . '%', $search . '%'));
        $neighbourhoods = $wpdb->get_results($sql);

        if (!empty($neighbourhoods)) {
            foreach ($neighbourhoods as $neighbourhood) {
                $location_name = $neighbourhood->hood_name;
                if ((int)geodir_location_check_duplicate('neighbourhood', $location_name) > 1) {
                    $location_name .= ', ' . $neighbourhood->city;
                }

                echo $location_name . " <small class='gd-small-neighbourhood'>" . __('(Neighbourhood)', 'geodiradvancesearch') . "</small> |" . $neighbourhood->hood_name . "|" . $neighbourhood->location_id . "|4|" . $neighbourhood->hood_id . " \n";
            }
        }
    }
    exit;
}

function geodir_autocompleter_from_submit_handler()
{
    if (isset($_REQUEST['geodir_autocompleter_save']))
        geodir_update_options(geodir_autocompleter_options());
}

function geodir_autocompleter_taxonomies()
{
    $taxonomies_array = array();
    $args = array(
        'public' => true,
        '_builtin' => false
    );
    $output = 'names'; // or objects
    $operator = 'or'; // can be#, and || or
    $taxonomies = get_taxonomies($args, $output, $operator);

    if (!empty($taxonomies)):
        foreach ($taxonomies as $term_que):
            $taxonomies_array[$term_que] = $term_que;
        endforeach;
    endif;

    return $taxonomies_array;
}

function geodir_autocompleter_post_types()
{
    $post_type_arr = array();

    $post_types = geodir_get_posttypes('object');

    foreach ($post_types as $key => $post_types_obj) {
        $post_type_arr[$key] = $post_types_obj->labels->singular_name;
    }
    return $post_type_arr;
}

function geodir_autocompleter_admin_script()
{
    if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'advanced_search_fields') {
        wp_register_script('geodir-autocompleter-admin-js', GEODIRADVANCESEARCH_PLUGIN_URL . '/js/autocomplete-admin.min.js', array('jquery'), GEODIRADVANCESEARCH_VERSION);
        wp_enqueue_script('geodir-autocompleter-admin-js');
    }
}

function geodir_autocompleter_ajax_url($type = '', $near = false)
{
    if ($near) {
        return admin_url('admin-ajax.php?action=geodir_autocompleter_near_ajax_action');
    } else {
        return admin_url('admin-ajax.php?action=geodir_autocompleter_ajax_action');
    }
}




###########################################################
############# AUTOCOMPLETE FUNCTIONS END ##################
###########################################################

/**
 * @since 1.4.0
 */
function geodir_search_onload_redirect() {
    global $gd_first_redirect;
    
    if (defined('POST_LOCATION_TABLE')) {
        if (empty($gd_first_redirect)) {
            $gd_first_redirect = get_option('geodir_first_load_redirect', 'no');
        }
        
        if (!in_array($gd_first_redirect, array('no', 'nearest', 'location'))) {
            $gd_first_redirect = 'no';
        }
    } else {
        $gd_first_redirect = 'no';
    }
    
    return $gd_first_redirect;
}

/**
 * @since 1.4.0
 */
function geodir_search_ask_onload_redirect() {
    $mode = false;
    if (!defined('POST_LOCATION_TABLE')) {
        return $mode;
    }
    global $gd_session;
    
    $redirect = geodir_search_onload_redirect();
    if ($redirect == 'no') {
        $gd_session->set('gd_onload_redirect_done', 1);
    }
    
    if (!$gd_session->get('gd_onload_redirect_done')) {
        if ($redirect == 'location') {
            $default_location   = geodir_get_default_location();
            $gd_country         = isset($default_location->country_slug) ? $default_location->country_slug : '';
            $gd_region          = isset($default_location->region_slug) ? $default_location->region_slug : '';
            $gd_city            = isset($default_location->city_slug) ? $default_location->city_slug : '';
            
            $gd_session->set('gd_country', $gd_country);
            $gd_session->set('gd_region', $gd_region);
            $gd_session->set('gd_city', $gd_city);
            $gd_session->set('gd_multi_location', 1);
            $gd_session->set('gd_onload_redirect_done', 1); // Redirect done on first time load
            $gd_session->set('gd_location_default_loaded', 1); // Default location loaded on first time load
        }
        
        $mode = true;
    }
    
    return apply_filters('geodir_search_ask_onload_redirect', $mode, $redirect);
}

/**
 * @since 1.4.2
 */
function geodir_search_location_is_active() {
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    return is_plugin_active('geodir_location_manager/geodir_location_manager.php') ? true : false;
}

/**
 * @since 1.4.7
 */
function geodir_search_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}

/**
 * @since 1.4.8
 */
function geodir_search_PHP_version_notice() {
    echo '<div class="error" style="margin:12px 0"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by <b>GeoDirectory Advance Search Filters</b>. Please contact your host and request that your PHP version be upgraded to <b>5.3 or later</b>.', 'geodiradvancesearch' ) . '</p></div>';
}