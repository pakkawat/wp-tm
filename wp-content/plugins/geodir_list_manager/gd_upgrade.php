<?php 
global $wpdb;

if(get_option('geodirlists_db_version') != GEODIRLISTS_VERSION){
	add_action( 'plugins_loaded', 'geodir_list_manager_upgrade_all' );
	update_option( 'geodirlists_db_version',  GEODIRLISTS_VERSION );
}

function geodir_list_manager_upgrade_all(){
	//update functions
}




