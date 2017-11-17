// JavaScript Document

		
jQuery(document).ready(function(){
	
	var geodir_duplicate_field = geodir_duplicate_alert_js_var.geodir_duplicate_field_name;
	var geodir_duplicate_current_post_type = geodir_duplicate_alert_js_var.geodir_duplicate_current_posttype;
	var geodir_duplicate_post_types = geodir_duplicate_alert_js_var.geodir_post_types_duplicate;
	
	var geodir_duplicate_posttype = '';
	if( geodir_duplicate_post_types.indexOf(geodir_duplicate_current_post_type) > -1 ) {
		 geodir_duplicate_posttype = geodir_duplicate_current_post_type;
	}
	
	if(geodir_duplicate_field != '' && geodir_duplicate_posttype != ''){
		
		var timer;
		
		jQuery('#'+geodir_duplicate_field).on('keyup change', function() {
				
				if(jQuery('#'+geodir_duplicate_field).val() != ''){
					clearInterval(timer);
					timer = setTimeout(function() {
							
							geodir_show_duplicate_alert();
							
					}, 1000);
				}else{
					if(jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').length > 0){
						jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').html('').hide();
					}
				}
		});
	
	}
	
	function geodir_show_duplicate_alert(){
		
		var search_val = jQuery('#'+geodir_duplicate_field).val();
		
		jQuery.post( geodir_duplicate_alert_js_var.geodir_duplicate_alert_ajax_url, { post_type: geodir_duplicate_posttype, search_val: search_val, field: geodir_duplicate_field })
		.done(function( data ) {
			
			if(jQuery.trim(data) != ''){
				
				if(jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').length > 0){
					jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').html(data).show();
				}else{
					jQuery('#'+geodir_duplicate_field).after('<span class="geodir_duplicate_message_error" style="display: none;"></span>');
					jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').html(data).show();
				}
				
				jQuery('#'+geodir_duplicate_field).closest('.required_field').find('span.geodir_message_error').hide();
				
			}else{
				if(jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').length > 0){
						jQuery('#'+geodir_duplicate_field).next('span.geodir_duplicate_message_error').html('').hide();
					}
			}
			
		});
	
	}
	
});
	