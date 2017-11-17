<?php
global $wpdb;

if (get_option('geodiradvancesearch' . '_db_version') != GEODIRADVANCESEARCH_VERSION) {
    

    add_action('plugins_loaded', 'geodiradvancesearch_upgrade_all');
    update_option('geodiradvancesearch' . '_db_version', GEODIRADVANCESEARCH_VERSION);
}

function geodiradvancesearch_upgrade_all()
{
    geodir_advance_search_field();
    geodiradvancesearch_upgrade_1_4_3();
}

function geodiradvancesearch_upgrade_1_4_3(){
    global $wpdb;

    $has_run = get_option('geodiradvancesearch_upgrade_1_4_3');
    if($has_run){return;}
    $wpdb->query("UPDATE ".GEODIR_ADVANCE_SEARCH_TABLE." SET field_site_type = 'datepicker' WHERE site_htmlvar_name = 'event'");
    update_option('geodiradvancesearch_upgrade_1_4_3',1);

}