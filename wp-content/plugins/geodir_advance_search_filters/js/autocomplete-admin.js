// JavaScript Document

function geodir_autocompleter_enable(ele){

	if(ele.is(':checked') == false){
			jQuery("#geodir_autocompleter_autosubmit").closest('tr').hide();
			jQuery("#geodir_autocompleter_autosubmit").attr('checked',false);
		}

	if(ele.is(':checked') == true)
		jQuery("#geodir_autocompleter_autosubmit").closest('tr').show();

}


jQuery(document).ready(function(){

	jQuery("#geodir_enable_autocompleter").click(function(){
		geodir_autocompleter_enable(jQuery("#geodir_enable_autocompleter"));
	});

	geodir_autocompleter_enable(jQuery("#geodir_enable_autocompleter"));

});