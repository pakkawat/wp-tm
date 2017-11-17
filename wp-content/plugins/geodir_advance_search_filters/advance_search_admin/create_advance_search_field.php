<?php
$data_type = isset($_REQUEST['data_type']) ? sanitize_text_field($_REQUEST['data_type']) : '';
$field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
$field_id = isset($_REQUEST['field_id']) ? trim(sanitize_text_field($_REQUEST['field_id']), '_') : '';
$field_action = isset($_REQUEST['field_ins_upd']) ? sanitize_text_field($_REQUEST['field_ins_upd']) : '';
$site_field_title = isset($_REQUEST['site_field_title']) ? sanitize_text_field($_REQUEST['site_field_title']) : '';

/* ------- check nonce field ------- */
if (isset($_REQUEST['update']) && $_REQUEST['update'] == 'update') {
    $field_ids = array();
    if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
        foreach ($_REQUEST['licontainer'] as $lic_id) {
            $field_ids[] = sanitize_text_field($lic_id);
        }
    }
    
    $return = godir_set_advance_search_field_order($field_ids);

    if (is_array($return)) {
        $return = json_encode($return);
    }

    echo $return;
}

/* ---- Show field form in admin ---- */ 
if ($field_action == 'new') {
    $listing_type = isset($_REQUEST['listing_type']) ? sanitize_text_field($_REQUEST['listing_type']) : '';
    $fields = geodirectory_advance_search_fields($listing_type);
    
    $_REQUEST['site_field_id'] = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
    
    if (!empty($fields)){
        foreach ($fields as $val) {
            $val = stripslashes_deep($val);
            
            if ($val['htmlvar_name'] == $_REQUEST['htmlvar_name']) {
                $_REQUEST['field_type'] = $val['field_type'];
                $_REQUEST['site_field_title'] = $val['site_title'];
                $_REQUEST['field_data_type'] = $val['data_type'];
            }
        }
    }
    
    $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : '';
    $field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
    
    geodir_custom_advance_search_field_adminhtml($field_type, $htmlvar_name, $field_action);
 }

/* ---- Delete field ---- */
if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce'])) {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_advance_search_fields_' . $field_id))
        return;
    
    echo geodir_custom_advance_search_field_delete($field_id);
}

/* ---- Save field  ---- */
if ($field_id != '' && $field_action == 'submit' && isset($_REQUEST['_wpnonce'])) {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_advance_search_fields_' . $field_id))
        return;
    
    foreach ($_REQUEST as $pkey => $pval) {
        $tags = is_array($_REQUEST[$pkey]) ? 'skip_field' : '';
        
        if ($tags != 'skip_field') {
            $_REQUEST[$pkey] = strip_tags(sanitize_text_field($_REQUEST[$pkey]), $tags);
        }
    }
 
    $return = geodir_custom_advance_search_field_save($_REQUEST);
    
    if (is_int($return)) {
        $lastid = $return;
        
        geodir_custom_advance_search_field_adminhtml($field_type, $lastid, 'submit');
    } else {
        echo $return;
    }
}