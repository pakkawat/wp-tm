function delete_rec(claimid, nonce) {
    if (confirm(geodir_claim_all_js_msg.geodir_want_to_delete_claim)) {
        window.location.href = geodir_claim_all_js_msg.geodir_claim_admin_ajax_url + "?action=geodir_claim_ajax_action&manage_action=true&pagetype=delete&id=" + claimid + '&_wpnonce=' + nonce;
        return true;
    } else {
        return false;
    }
}

function approve_rec(claimid, nonce) {
    if (confirm(geodir_claim_all_js_msg.geodir_want_to_approve_claim)) {
        window.location.href = geodir_claim_all_js_msg.geodir_claim_admin_ajax_url + "?action=geodir_claim_ajax_action&manage_action=true&pagetype=approve&id=" + claimid + '&_wpnonce=' + nonce;
        return true;
    } else {
        return false;
    }
}

function reject_rec(claimid, nonce) {
    if (confirm(geodir_claim_all_js_msg.geodir_want_to_reject_claim)) {
        window.location.href = geodir_claim_all_js_msg.geodir_claim_admin_ajax_url + "?action=geodir_claim_ajax_action&manage_action=true&pagetype=reject&id=" + claimid + '&_wpnonce=' + nonce;
        return true;
    } else {
        return false;
    }
}

function undo_rec(claimid, nonce) {
    if (confirm(geodir_claim_all_js_msg.geodir_want_to_undo_claim)) {
        window.location.href = geodir_claim_all_js_msg.geodir_claim_admin_ajax_url + "?action=geodir_claim_ajax_action&manage_action=true&pagetype=undo&id=" + claimid + '&_wpnonce=' + nonce;
        return true;
    } else {
        return false;
    }
}

function geodir_claimtoggle() {
    var ele = document.getElementById("gd-claimtoggleText");
    var text = document.getElementById("gd-claimdisplayText");
    if (ele.style.display == "block" || ele.style.display == "") {
        jQuery("#gd-claimtoggleText").hide('slow');
        text.innerHTML = geodir_claim_all_js_msg.geodir_what_is_claim_process;
    }
    if (ele.style.display == "none") {
        jQuery("#gd-claimtoggleText").show('slow');
        text.innerHTML = geodir_claim_all_js_msg.geodir_claim_process_hide;
    }
}

/* --- geodir claim popup script --- */
var claimPopup;
function geodir_get_claim_popup_forms(e, clk_class, popup_id) {
    if (typeof claimPopup !== 'undefined' && claimPopup) {
        return false; // bail if already open
    }
    claimPopup = true;
    var ajax_url = geodir_claim_all_js_msg.geodir_claim_admin_ajax_url_form;
    var post_id = jQuery('input[name="geodir_claim_popup_post_id"]').val()
    var append_class = jQuery('.' + clk_class).closest('.geodir-company_info');
    jQuery.gdmodal('<div id="basic-modal-content" class="clearfix simplemodal-data" style="display: block;"><div class="geodir-modal-loading"><i class="fa fa-refresh fa-spin "></div></div>'); // show popup right away
    jQuery.post(ajax_url, {
            popuptype: clk_class,
            post_id: post_id
        })
        .done(function(data) {
            append_class.find('.geodir_display_claim_popup_forms').append(data);
            e.preventDefault();
            jQuery.gdmodal.close(); // close popup and show new one with new data, will be so fast user will not see it
            jQuery('#' + popup_id).gdmodal({
                persist: true,
                onClose: function() {
                    jQuery.gdmodal.close({
                        overlayClose: true
                    });
                    append_class.find('.geodir_display_claim_popup_forms').html('');
                    claimPopup = false;
                },
            });
        });
}

jQuery(document).ready(function() {
    var geodir_popup_claim_timer;
    jQuery('a.geodir_claim_enable').click(function(e) {
        e.preventDefault();
        geodir_get_claim_popup_forms(e, 'geodir_claim_enable', 'gd-basic-modal-content4');
    });
});

function geodir_claim_popup_validate_field(field) {
    var is_error = true;
    erro_msg = '';
    switch (jQuery(field).attr('field_type')) {
        case 'email':
            var filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (field.value == '') {
                erro_msg = geodir_claim_all_js_msg.geodir_claim_field_id_required;
            }
            if (field.value != '' && !filter.test(field.value)) {
                erro_msg = geodir_claim_all_js_msg.geodir_claim_valid_email_address_msg;
            }
            if (field.value != '' && filter.test(field.value)) {
                is_error = false;
            }
            break;
        case 'text':
        case 'textarea':
            if (field.value != '') {
                is_error = false;
            } else {
                erro_msg = geodir_claim_all_js_msg.geodir_claim_field_id_required;
            }
            break;
    }
    if (is_error) {
        if (erro_msg) {
            jQuery(field).closest('div').find('span.message_error2').html(erro_msg)
        }
        jQuery(field).closest('div').find('span.message_error2').fadeIn();
        return false;
    } else {
        jQuery(field).closest('div').find('span.message_error2').html('');
        jQuery(field).closest('div').find('span.message_error2').fadeOut();
        return true;
    }
}

jQuery(document).ready(function() {
    jQuery(document).delegate("#geodir_claim_form .is_required:visible", "blur", function(ele) {
        geodir_claim_popup_validate_field(this);
    });
    jQuery(document).delegate("#geodir_claim_form", "submit", function(ele) {
        var claim_popup_is_validate = true;
        jQuery(this).find(".is_required:visible").each(function() {
            if (!geodir_claim_popup_validate_field(this))
                claim_popup_is_validate = geodir_claim_popup_validate_field(this);
        });
        if (claim_popup_is_validate) {
            return true;
        } else {
            return false;
        }
    });
});

function gd_claim_change_pmethod(el, keys) {
    var method = jQuery(el).val();
    if (keys && typeof keys == 'object') {
        for (i = 0; i < keys.length; i++) {
            if (jQuery('#' + keys[i] + 'options').is(':visible') && keys[i] != method) {
                jQuery('#' + keys[i] + 'options').hide();
            }
        }
        if (!jQuery('#' + method + 'options').is(':visible')) {
            jQuery('#' + method + 'options').show();
            if (jQuery('#' + method + 'options').length) {
                jQuery('input[type="text"]', '#' + method + 'options').each(function() {
                    jQuery(this).attr('field_type', 'text').addClass('is_required');
                    if (!jQuery('#' + jQuery(this).attr('name') + 'Info.message_error2').length) {
                        jQuery(this).after('<span class="message_error2" id="' + jQuery(this).attr('name') + 'Info"></span>');
                    }
                });
                jQuery('textarea', '#' + method + 'options').each(function() {
                    jQuery(this).attr('field_type', 'textarea').addClass('is_required');
                    if (!jQuery('#' + jQuery(this).attr('name') + 'Info.message_error2').length) {
                        jQuery(this).after('<span class="message_error2" id="' + jQuery(this).attr('name') + 'Info"></span>');
                    }
                });
                jQuery('select', '#' + method + 'options').each(function() {
                    jQuery(this).attr('field_type', 'text').addClass('is_required');
                    if (!jQuery('#' + jQuery(this).attr('name') + 'Info.message_error2').length) {
                        jQuery(this).after('<span class="message_error2" id="' + jQuery(this).attr('name') + 'Info"></span>');
                    }
                });
            }
        }
    }
}