// JavaScript Document

jQuery(document).ready(function(){	
	jQuery('input[name="geodir_save_post_type"]').click(function(){
		
		var custom_post_type = jQuery('input[name="geodir_custom_post_type"]').val();
		var listing_slug = jQuery('input[name="geodir_listing_slug"]').val();
		var listing_order = jQuery('input[name="geodir_listing_order"]').val();
		
		if(jQuery.trim(custom_post_type) != '')
		{	
			if(custom_post_type.length>17){
				alert(geodir_custom_post_type_all_js_msg.geodir_cp_post_type_char_validation);
				return false;
			}
			/*if(/^[a-zA-Z0\_9_]*$/.test(custom_post_type) == false) */
			if(/^[a-z\_9_]*$/.test(custom_post_type) == false) {
				alert(geodir_custom_post_type_all_js_msg.geodir_cp_post_type_illegal_characters_validation);
				return false;
			}
		}
		else
		{
			alert(geodir_custom_post_type_all_js_msg.geodir_cp_post_type_blank_validation);
			return false;
		}
		
		if(jQuery.trim(listing_slug) != '')
		{
			/*if(/^[a-zA-Z0\_9_-]*$/.test(listing_slug) == false) {*/
			if (gd_slug_illegal_char(listing_slug)) {
				alert(geodir_custom_post_type_all_js_msg.geodir_cp_listing_slug_illegal_characters_validation);
				return false;
			}
		}
		else
		{
			alert(geodir_custom_post_type_all_js_msg.geodir_cp_listing_slug_blank_validation);
			return false;
		}
	
		
		if(listing_order=='' ||  isNaN(listing_order)  )
		{
			alert(geodir_custom_post_type_all_js_msg.geodir_cp_listing_order_value_validation) ;
			return false;
		}
		else
		{
			if(parseInt( listing_order) <= 0 )
			{
				alert(geodir_custom_post_type_all_js_msg.geodir_cp_listing_order_value_validation) ;
				return false;
			}
		}
		
	});
	
	

	
});

function gd_slug_illegal_char(slug) {
    var regEx = /^\S+$/i;
    
    if (!regEx.test(slug)) {
        console.log( "\S" );
        return true;
    }
    
    var illegalChars = "<>@!#$^&*()+[]{}?:;|'\"\\,./~`=";
    
    for (i = 0; i < illegalChars.length; i++) {
        if (slug.indexOf(illegalChars[i]) > -1) {
            console.log( illegalChars[i] );
            return true;
        }
    }
    
    return false;
}