<?php 
global $wpdb;

if(get_option('geodir_custom_posts'.'_db_version') != GEODIR_CP_VERSION){
	add_action( 'plugins_loaded', 'geodir_custom_posts_upgrade_all' );
	update_option( 'geodir_custom_posts'.'_db_version',  GEODIR_CP_VERSION );
}

function geodir_custom_posts_upgrade_all(){
	geodir_custom_posts_upgrade_1_0_8();
}

function geodir_custom_posts_upgrade_1_0_8(){
	global $wpdb,$plugin_prefix;

	$geodir_custom_post_types = geodir_get_posttypes();
	
	if(!empty($geodir_custom_post_types))
		{
			foreach($geodir_custom_post_types as $key)
			{
			// rename tables if we need to
			if($wpdb->query("SHOW TABLES LIKE 'geodir_".$key."_detail'")>0){$wpdb->query("RENAME TABLE geodir_".$key."_detail TO ".$wpdb->prefix."geodir_".$key."_detail");}
			$wpdb->query("ALTER TABLE ".$wpdb->prefix."geodir_".$key."_detail MODIFY `post_title` text NULL");	
				
			}
			
		}
	
}


