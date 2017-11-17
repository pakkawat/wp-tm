jQuery(document).ready(function() {
    setTimeout(function() {// add small delay incase mobile menu is created on the fly. 100 shoudl do it
        var keyup_timer;
        var gdlm_msgWait = geodir_location_all_js_msg.LOCATION_PLEASE_WAIT;
        var gdlm_ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
        
        jQuery('input[name="loc_pick_country_filter"]').keyup(function() {
            var $obj = jQuery(this);
            
            jQuery(this).parent().find('ul').html(gdlm_msgWait);
            clearInterval(keyup_timer);
            
            total = parseInt(jQuery(this).parent().data('limit'));
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            
            keyup_timer = setTimeout(function() {
                jQuery.post(gdlm_ajax_url, {
                    action: 'geodir_location_ajax',
                    gd_loc_ajax_action: 'get_location',
                    gd_formated_for: 'location_switcher',
                    gd_which_location: 'country',
                    gd_country_val: $obj.val(),
                    lscroll: total,
                }, function(data) {
                    $obj.parent().find('ul').html(data);
                });
            }, 500);
        });
        
        jQuery('input[name="loc_pick_region_filter"]').keyup(function() {
            var country_val = jQuery(this).parent().parent().find('input[name="loc_pick_country_filter"]').val();
            
            jQuery(this).parent().find('ul').html(gdlm_msgWait);
            var $obj = jQuery(this);
            total = parseInt(jQuery(this).parent().data('limit'));
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            
            clearInterval(keyup_timer);
            keyup_timer = setTimeout(function() {
                jQuery.post(gdlm_ajax_url, {
                    action: 'geodir_location_ajax',
                    gd_loc_ajax_action: 'get_location',
                    gd_formated_for: 'location_switcher',
                    gd_which_location: 'region',
                    gd_country_val: country_val,
                    gd_region_val: $obj.val(),
                    lscroll: total,
                }, function(data) {
                    $obj.parent().find('ul').html(data);
                });
            }, 500);
        });
        
        jQuery('input[name="loc_pick_city_filter"]').keyup(function() {
            jQuery(this).parent().find('ul').html(gdlm_msgWait);
            
            var country_val = jQuery(this).parent().parent().find('input[name="loc_pick_country_filter"]').val();
            var region_val = jQuery(this).parent().parent().find('input[name="loc_pick_region_filter"]').val();
            var $obj = jQuery(this);
            total = parseInt(jQuery(this).parent().data('limit'));
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            
            clearInterval(keyup_timer);
            keyup_timer = setTimeout(function() {
                jQuery.post(gdlm_ajax_url, {
                    action: 'geodir_location_ajax',
                    gd_loc_ajax_action: 'get_location',
                    gd_formated_for: 'location_switcher',
                    gd_which_location: 'city',
                    gd_country_val: country_val,
                    gd_region_val: region_val,
                    gd_city_val: $obj.val(),
                    lscroll: total,
                }, function(data) {
                    $obj.parent().find('ul').html(data);
                });
            }, 500);
        });
        
        jQuery('.geodir-locListing_column ul').on('click', '.geodir_loc_arrow a', function() {
            var which_location = '',country_val = '',region_val = '',city_val = '';
            
            var $mainDiv = jQuery(this).parents('.geodir_locListing_main');
            var $curDiv = jQuery(this).parents('.geodir-locListing_column');
            var $nextDiv = jQuery($curDiv, $mainDiv).next();
            var $curUl = jQuery(this).parents('ul');
            var $curLi = jQuery(this).parents('li');
            var sVal = jQuery('a', $curLi).text().trim();
                        
            jQuery('li', $curUl).removeClass('geodir_active');
            $curLi.addClass('geodir_active');
            jQuery('input[name^="loc_pick_"]', $curDiv).val(sVal);
            jQuery('input[name^="loc_pick_"]', $nextDiv).val('');
            
            if ($curUl.hasClass('geodir_country_column')) {
                which_location = 'region';
                country_val = sVal;
            } else if ($curUl.hasClass('geodir_city_column')) {
                which_location = 'neighbourhood';
                city_val = sVal;
            } else {
                which_location = 'city';
                region_val = sVal;
            }
            
            var ul_item = jQuery('ul', $nextDiv).html(gdlm_msgWait);
            total = parseInt($nextDiv.data('limit'));
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            jQuery.post(gdlm_ajax_url, {
                action: 'geodir_location_ajax',
                gd_loc_ajax_action: 'get_location',
                gd_formated_for: 'location_switcher',
                gd_which_location: which_location,
                gd_country_val: country_val,
                gd_region_val: region_val,
                gd_city_val: city_val,
                lscroll: total,
            }, function(data) {
                jQuery('ul', $nextDiv).html(data);
            });
        });
        
        jQuery('.geodir_location_tab_container .geodir_location_tabs').bind('click', function() {
            var tab = this;
            var tab_id = jQuery(this).data('location');
            var autoredirect = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('autoredirect');
            var show_every_where = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('showeverywhere');
            
            jQuery.post(gdlm_ajax_url + '?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect=' + autoredirect + '&gd_which_location=' + tab_id + "&show_every_where=" + show_every_where, function(data) {
                
                jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").html(data).chosen().trigger("chosen:updated");
                
                geodir_enable_click_on_chosen_list_item();
            });
            
            jQuery(tab).parents('.geodir_location_tab_container').find('.geodir_location_tabs').removeClass('gd-tab-active');
            jQuery(tab).addClass('gd-tab-active');
            
            /* alternative fix for chosen not supported on mobile device */
            if (!geodir_lm_chosen_supported()) {
                geodir_no_chosen_add_search(jQuery(tab).parents('.geodir_location_tab_container'));
            }
            /* alternative fix for chosen not supported on mobile device */
            
            geodir_location_switcher_chosen_ajax();
        });
        
        
        // now add an ajax function when value is entered in chose select text field
        
        /* alternative fix for chosen not supported on mobile device */
        if (!geodir_lm_chosen_supported()) {
            jQuery('body').removeClass('gd-chosen-no-support').addClass('gd-chosen-no-support');
            jQuery('.geodir_location_tab_container').each(function() {
                var $this = this;
                geodir_no_chosen_add_search($this);
            });
        } else {
            // Chosen selects
            if (jQuery("select.geodir_location_switcher_chosen").length > 0) {
                jQuery("select.geodir_location_switcher_chosen").chosen({
                    no_results_text: geodir_location_all_js_msg.LOCATION_CHOSEN_NO_RESULT_TEXT
                });
            }
            if (jQuery("select.geodir_location_add_listing_chosen").length > 0) {
                jQuery("select.geodir_location_add_listing_chosen").chosen({
                    no_results_text: geodir_location_all_js_msg.LOCATION_CHOSEN_NO_RESULT_TEXT
                });
            }
            geodir_location_switcher_chosen_ajax();
        }
        /* alternative fix for chosen not supported on mobile device */
        
        geodir_enable_click_on_chosen_list_item();
        
        /* alternative fix for chosen not supported on mobile device */
        if(!geodir_lm_chosen_supported()) {
            jQuery(document).click(function(e) {;
                var isSwitcher = jQuery(e.target).closest('.geodir_location_sugestion').html();
                if(typeof isSwitcher == 'undefined') {
                    jQuery(document).find('.geodir_location_sugestion').each(function() {
                        jQuery(this).find('select[name="gd_location"]').removeAttr('size');
                        jQuery(this).find('select[name="gd_location"]').removeClass('geodir-loc-select-list');
                    });
                }
            });
            jQuery(document).find('.geodir_location_sugestion select[name="gd_location"] option').click(function() {
                if(jQuery(this).attr('selected') == 'selected' || jQuery(this).attr('value') == geodir_location_all_js_msg.gd_base_location) {
                    jQuery(this).closest('select').removeAttr('size');
                    jQuery(this).closest('select').removeClass('geodir-loc-select-list');
                }
            });
        }
        /* alternative fix for chosen not supported on mobile device */
        
        // neighbourhood filter
        jQuery('input[name="loc_pick_neighbourhood_filter"]').keyup(function() {
            var $this = jQuery(this);
            var hoodDiv = $this.closest('.geodir-locListing_column');
            var mainDiv = jQuery(hoodDiv).parent();
            
            var country_val = jQuery('input[name="loc_pick_country_filter"]', mainDiv).val();
            var region_val = jQuery('input[name="loc_pick_region_filter"]', mainDiv).val();
            var city_val = jQuery('input[name="loc_pick_city_filter"]', mainDiv).val();
            total = parseInt(hoodDiv.data('limit'));
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            
            jQuery('ul', hoodDiv).html(gdlm_msgWait);
            
            clearInterval(keyup_timer);
            keyup_timer = setTimeout(function() {
                jQuery.post(gdlm_ajax_url, {
                    action: 'geodir_location_ajax',
                    gd_loc_ajax_action: 'get_location',
                    gd_formated_for: 'location_switcher',
                    gd_which_location: 'neighbourhood',
                    gd_country_val: country_val,
                    gd_region_val: region_val,
                    gd_city_val: city_val,
                    gd_neighbourhood_val: $this.val(),
                    lscroll: total,
                }, function(data) {
                    jQuery('ul', hoodDiv).html(data);
                });
            }, 500);
        });
        
        // Load more
        jQuery(document).on("click", ".gd-loc-loadmore button", function(e) {
            var $this = jQuery(this);
            if ($this.data('title')) {
                $this.find('font').text($this.data('title'));
            }
            $this.find('.fa').addClass('fa-spin');
            
            var $obj = $this.closest('.geodir-locListing_column'), $mainObj = $obj.closest('.geodir_locListing_main');
            var type = $obj.data('type'), total = parseInt($obj.data('limit')), pages = parseInt($this.data('pages')), next = parseInt($this.data('next'));
            
            if (isNaN(pages) || !pages > 0) {
                pages = 1;
            }
            if (isNaN(next)) {
                next = 1;
            }
            if (isNaN(total) || !total > 0) {
                total = 0;
            }
            if (!type) {
                return false;
            }
            
            var data = {
                action: 'geodir_location_ajax',
                gd_loc_ajax_action: 'get_location',
                gd_formated_for: 'location_switcher',
                gd_which_location: type,
                _task: 'loadmore',
                _pages: pages,
                lscroll: total,
                spage: next,
            }
            data.gd_country_val = jQuery('input[name="loc_pick_country_filter"]', $mainObj).val();
            
            if (type == 'region' || type == 'city' || type == 'neighbourhood') {
                data.gd_region_val = jQuery('input[name="loc_pick_region_filter"]', $mainObj).val();
                
                if (type == 'city' || type == 'neighbourhood') {
                    data.gd_city_val = jQuery('input[name="loc_pick_city_filter"]', $mainObj).val();
                    
                    if (type == 'neighbourhood') {
                        data.gd_neighbourhood_val = jQuery('input[name="loc_pick_neighbourhood_filter"]', $mainObj).val();
                    }
                }
            }
            
            jQuery.post(gdlm_ajax_url, data, function(response) {
                $obj.find('li.gd-loc-loadmore').remove();
                
                if (response && jQuery.trim(response) != '') {
                    $obj.find('ul').append(response);
                }
            });
        });
    }, 100);
}); // end of document.ready jquery

function geodir_location_switcher_chosen_ajax() {
    jQuery("select.geodir_location_switcher_chosen").each(function() {
        var curr_chosen = jQuery(this);
        var autoredirect = curr_chosen.data('autoredirect');
        var countrysearch = curr_chosen.data('countrysearch');
        var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
        
        if(curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined) {
            var listfor = curr_chosen.parents('.geodir_location_tab_container').find('.gd-tab-active').data('location');
            var show_every_where = curr_chosen.data('showeverywhere');
            
            curr_chosen.ajaxChosen({
                keepTypingMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_KEEP_TYPE_TEXT,
                lookingForMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_LOOKING_FOR_TEXT,
                type: 'GET',
                url: ajax_url + '?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect=' + autoredirect + '&gd_which_location=' + listfor + '&show_every_where=' + show_every_where,
                dataType: 'html',
                success: function(data) {
                    curr_chosen.html(data).chosen().trigger("chosen:updated");
                    geodir_enable_click_on_chosen_list_item();
                }
            }, null, {});
        }
    });
}

/* Script for the Add new listing page, country/Region/City chosen */
jQuery(document).ready(function() {
    geodir_location_add_listing_chosen();
    
    jQuery('select.geodir_location_add_listing_chosen').bind('change', function() {
        var curr_chosen = jQuery(this);
        var location_type = curr_chosen.data('location_type');
        var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
        var $loader = '<div class="location_dl_loader" align="center" style="width:100%;"><img src="' + geodir_all_js_msg.geodir_plugin_url + '/geodirectory-assets/images/loadingAnimation.gif"  /></div>';
        var gd_loc_chosen = curr_chosen.parents(".geodir_location_add_listing_all_chosen_container");
        var gd_country_el = gd_loc_chosen.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]');
        var gd_region_div = gd_loc_chosen.find('.geodir_location_add_listing_region_chosen_div');
        var gd_city_div = gd_loc_chosen.find('.geodir_location_add_listing_city_chosen_div');
        
        var country_val = '';
        var region_val = '';
        var city_val = '';
        
        if (location_type == 'country') {
            // update state/City and neighbour dropdown
            if (curr_chosen.attr('name') != 'post_country')
                return false;
            gd_country_el.val(curr_chosen.val());
            country_val = curr_chosen.val();
            
            gd_region_div.hide();
            gd_region_div.after($loader);
            
            jQuery.post(ajax_url, {
                action: 'geodir_location_ajax',
                gd_loc_ajax_action: 'fill_location_on_add_listing',
                gd_which_location: 'region',
                country_val: country_val
            }, function(data) {
                if (data) {
                    gd_region_div.next('.location_dl_loader').remove();
                    gd_region_div.show();
                    gd_region_div.find('select').html(data).chosen().trigger("chosen:updated");
                    gd_region_div.find('select').trigger("change");
                    geodir_location_add_listing_chosen();
                }
                if (!geodir_lm_chosen_supported()) {
                    jQuery('.gd-new-loc-div', gd_region_div).show();
                }
            });
        }
        
        if (location_type == 'region') {
            country_val = gd_country_el.val();
            // set value of hidden region feld to the selected one.
            gd_region_div.find('input[name="geodir_location_add_listing_region_val"]').val(curr_chosen.val());
            region_val = curr_chosen.val();
            
            gd_city_div.hide();
            gd_city_div.after($loader);
            
            jQuery.post(ajax_url, {
                action: 'geodir_location_ajax',
                gd_loc_ajax_action: 'fill_location_on_add_listing',
                gd_which_location: 'city',
                country_val: country_val,
                region_val: region_val
            }, function(data) {
                if (data) {
                    gd_city_div.next('.location_dl_loader').remove();
                    gd_city_div.show();
                    gd_city_div.find('select').html(data).chosen().trigger("chosen:updated");
                    gd_city_div.find('select').trigger("change");
                    geodir_location_add_listing_chosen();
                }
                if (!geodir_lm_chosen_supported()) {
                    jQuery('.gd-new-loc-div', gd_city_div).show();
                }
            });
        }
        
        if (location_type == 'city') {
            var gd_hood_chosen = gd_loc_chosen.find('.geodir_location_add_listing_neighbourhood_chosen_container');
            var gd_hood_div = gd_loc_chosen.find('.geodir_location_add_listing_neighbourhood_chosen_div');
            country_val = gd_country_el.val();
            // set value of hidden region feld to the selected one.
            region_val = gd_region_div.find('input[name="geodir_location_add_listing_region_val"]').val();
            gd_city_div.find('input[name="geodir_location_add_listing_city_val"]').val(curr_chosen.val());
            city_val = curr_chosen.val();
            if (!window.gdGeo) {
                window.neighbourhood = '';
            }
            neighbourhood_val = window.neighbourhood ? window.neighbourhood : '';
            
            gd_hood_chosen.show();
            gd_hood_div.hide();
            gd_hood_div.after($loader);
            jQuery.post(ajax_url, {
                action: 'geodir_location_ajax',
                gd_loc_ajax_action: 'fill_location_on_add_listing',
                gd_which_location: 'neighbourhood',
                country_val: country_val,
                region_val: region_val,
                city_val: city_val,
                neighbourhood_val: neighbourhood_val,
            }, function(data) {
                gd_hood_div.find('input[name="geodir_location_add_listing_neighbourhood_val"]').val(neighbourhood_val);
                if (data) {
                    var hood_val = gd_hood_div.find('select').val();
                    gd_hood_chosen.show();
                    gd_hood_div.next('.location_dl_loader').remove();
                    gd_hood_div.show();
                    gd_hood_div.find('select').html(data);
                    if (!neighbourhood_val && hood_val) {
                        gd_hood_div.find('select').val(hood_val);
                    }
                    gd_hood_div.find('select').chosen().trigger("chosen:updated");
                } else {
                    gd_hood_div.find('select').val('');
                    gd_hood_div.next('.location_dl_loader').remove();
                    gd_hood_chosen.hide();
                }
                window.gdGeo = false;
            });
        }
    });
});

function geodir_location_add_listing_chosen() {
    jQuery("select.geodir_location_add_listing_chosen").each(function() {
            var curr_chosen = jQuery(this);
            var gd_loc_chosen = curr_chosen.parents(".geodir_location_add_listing_all_chosen_container");
            var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
            var obj_name = curr_chosen.prop('name');
            var obbj_info = obj_name.split('_');
            listfor = obbj_info[1];
            var country_val = '';
            var region_val = '';
            var city_val = '';


            country_val = gd_loc_chosen.find('.geodir_location_add_listing_country_chosen_div').find('input[name="geodir_location_add_listing_country_val"]').val();
            if (country_val === undefined) {
                country_val = jQuery('#post_country').val();
            }
            region_val = gd_loc_chosen.find('.geodir_location_add_listing_region_chosen_div').find('input[name="geodir_location_add_listing_region_val"]').val();
            city_val = gd_loc_chosen.find('.geodir_location_add_listing_city_chosen_div').find('input[name="geodir_location_add_listing_city_val"]').val();
            
            if (curr_chosen.data('ajaxchosen') == '1' || curr_chosen.data('ajaxchosen') === undefined) {
                curr_chosen.ajaxChosen({
                        keepTypingMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_KEEP_TYPE_TEXT,
                        lookingForMsg: geodir_location_all_js_msg.LOCATION_CHOSEN_LOOKING_FOR_TEXT,
                        type: 'GET',
                        url: ajax_url + '?action=geodir_location_ajax&gd_loc_ajax_action=fill_location_on_add_listing&gd_which_location=' + listfor + '&country_val=' + country_val + '&region_val=' + region_val + '&city_val=' + city_val,
                        dataType: 'html',
                        success: function(data) {
                            curr_chosen.html(data).chosen().trigger("chosen:updated");
                            geodir_location_add_listing_chosen();
                        }
                    },
                    null, {}
                );
            }


            // add chosen search placeholder text
            var search_placeholder = curr_chosen.data("searchplaceholder");
            if(search_placeholder){
                setTimeout(function(){ // time out seems required
                    curr_chosen.parent().find('.chosen-drop .chosen-search input').attr("placeholder",search_placeholder);
                }, 200);
            }


        }
    );
}

// script to make everywhere link clickable if its already selected or when onchange event is not called
function geodir_enable_click_on_chosen_list_item() {
    jQuery('.chosen-results').bind('click', function() {
        var first_item_text = jQuery('.chosen-results').find('li[data-option-array-index="0"]').html();
        var selected_item_text = jQuery(this).parents('.geodir_location_sugestion').find('.chosen-single > span').html();
        
        if (first_item_text == selected_item_text) {
            jQuery(this).parents('.geodir_location_sugestion').find('select').trigger("change");
        }
    });
}

function geodir_set_map_default_location(mapid, lat, lng) {
    if(mapid != '' && lat != '' && lng != '') {
        jQuery("#" + mapid).goMap();
        jQuery.goMap.map.setCenter(new google.maps.LatLng(lat, lng));
        baseMarker.setPosition(new google.maps.LatLng(lat, lng));
        updateMarkerPosition(baseMarker.getPosition());
        geocodePosition(baseMarker.getPosition());
    }
}

/* alternative fix for chosen not supported on mobile device */
function geodir_lm_chosen_supported() {
    if(window.navigator.appName === "Microsoft Internet Explorer") {
        return document.documentMode >= 8;
    }
    if(/iP(od|hone|ad)/i.test(window.navigator.userAgent)) {
        return false;
    }
    if(/Android/i.test(window.navigator.userAgent)) {
        if(/Mobile/i.test(window.navigator.userAgent)) {
            return false;
        }
    }
    return true;
}

function geodir_no_chosen_add_search(cont, tab) {
    var contLoc = jQuery(cont).find('select[name="gd_location"]');
    jQuery(contLoc).removeAttr('size');
    jQuery(contLoc).removeClass('geodir-loc-select-list');
    jQuery(cont).find('.gd-no-chosen-seach').remove();
    var inputSearch = '<div class="chosen-search"><input type="text" name="term" class="gd-no-chosen-seach" value="" onkeyup="javascript:geodir_no_chosen_search(this);" /></div>';
    jQuery(contLoc).before(inputSearch);
    
    if(typeof tab == 'undefined' || tab == '') {
        tab = jQuery(cont).find('.geodir_location_tabs.gd-tab-active').attr('data-location');
    }
    
    var placeHold = '';
    
    if(tab == 'city') {
        placeHold = geodir_location_all_js_msg.gd_text_search_city;
    } else if(tab == 'region') {
        placeHold = geodir_location_all_js_msg.gd_text_search_region;
    } else if(tab == 'country') {
        placeHold = geodir_location_all_js_msg.gd_text_search_country;
    }
    placeHold = placeHold != '' ? placeHold : geodir_location_all_js_msg.gd_text_search_location;
    jQuery(cont).find('.gd-no-chosen-seach').attr('placeholder', placeHold);
}

function geodir_no_chosen_search(obj) {
    var term = jQuery(obj).val();
    if(typeof term != 'undefined' && term != '') {
        term = term.replace(/^\s+/, '');;
    } else {
        term = '';
    }
    var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
    var attach_term = term != '' ? '&term=' + term : '';
    var cont = jQuery(obj).closest(".geodir_location_tab_container");
    var tab = jQuery(cont).find('.geodir_location_tabs.gd-tab-active');
    var tab_id = jQuery(tab).data('location');
    var autoredirect = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('autoredirect');
    var show_every_where = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('showeverywhere');
    
    jQuery.post(ajax_url + '?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect=' + autoredirect + '&gd_which_location=' + tab_id + "&show_every_where=" + show_every_where + attach_term, function(data) {
        jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").html(data).chosen().trigger("chosen:updated");
        geodir_enable_click_on_chosen_list_item();
        geodir_expand_option(cont);
        jQuery(cont).find('select[name="gd_location"] option').each(function() {
            jQuery(this).bind('click', function() {
                if(jQuery(this).attr('selected') == 'selected' || jQuery(this).attr('value') == geodir_location_all_js_msg.gd_base_location) {
                    jQuery(this).closest('select').removeAttr('size');
                    jQuery(this).closest('select').removeClass('geodir-loc-select-list');
                }
            });
        });
    });
    
    geodir_location_switcher_chosen_ajax();
}

function geodir_expand_option(cont, one) {
    var objSel = jQuery(cont).find('select[name="gd_location"]');
    var optCount = jQuery(objSel).children('option').length;
    if(typeof one != 'undefined') {
        jQuery(objSel).removeAttr('size');
        jQuery(objSel).removeClass('geodir-loc-select-list');
    } else {
        if(parseInt(optCount) < 2) {
            jQuery(objSel).removeAttr('size');
            jQuery(objSel).removeClass('geodir-loc-select-list');
        } else {
            jQuery(objSel).attr('size', optCount);
            jQuery(objSel).addClass('geodir-loc-select-list');
        }
    }
}
/* alternative fix for chosen not supported on mobile device */

jQuery(document).ready(function() {
    locationSPage = [];
    locationSPage['city'] = 1;
    locationSPage['region'] = 1;
    locationSPage['country'] = 1;
    locationSPage['neighbourhood'] = 1;
    locationSActive = false;

    setTimeout(function() { // wait for JS chosen to have loaded
        jQuery('.geodir_location_sugestion .geodir-chosen-container .chosen-results').scroll(function() {
            if (jQuery(this).scrollTop() + jQuery(this).innerHeight() >= jQuery(this)[0].scrollHeight) {
                if (locationSActive) {
                    return;
                }
                
                if (jQuery(this).scrollTop() == 0) {
                    return;
                }
                
                obj = this;
                var tempScrollTop = jQuery(this).scrollTop();
                var term = '';
                var ajax_url = geodir_location_all_js_msg.geodir_location_admin_ajax_url;
                var attach_term = term != '' ? '&term=' + term : '';
                var cont = jQuery(obj).closest(".geodir_location_tab_container");
                var tab = jQuery(cont).find('.geodir_location_tabs.gd-tab-active');
                var tab_id = jQuery(tab).data('location');
                var autoredirect = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('autoredirect');
                var show_every_where = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").data('showeverywhere');
                
                if (locationSPage[tab_id] == '0') {
                    return;
                }
                
                locationSActive = true;
                
                jQuery(obj).addClass('loading_div_loc');
                
                jQuery.post(ajax_url + '?action=geodir_location_ajax&gd_loc_ajax_action=fill_location&autoredirect=' + autoredirect + '&gd_which_location=' + tab_id + "&show_every_where=" + show_every_where + attach_term + "&lscroll=true&spage=" + locationSPage[tab_id], function(data) {
                    jQuery(obj).removeClass('loading_div_loc');
                    locationSPage[tab_id]++;
                    
                    if (data == '') {
                        locationSPage[tab_id] = '0';
                    }
                    
                    var orig_data = jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").html();
                    
                    jQuery(tab).parents(".geodir_location_tab_container").find(".geodir_location_switcher_chosen").html(orig_data + data).chosen().trigger("chosen:updated");
                    geodir_enable_click_on_chosen_list_item();
                    geodir_expand_option(cont);
                    
                    jQuery(cont).find('select[name="gd_location"] option').each(function() {
                        jQuery(this).bind('click', function() {
                            if (jQuery(this).attr('selected') == 'selected' || jQuery(this).attr('value') == geodir_location_all_js_msg.gd_base_location) {
                                jQuery(this).closest('select').removeAttr('size');
                                jQuery(this).closest('select').removeClass('geodir-loc-select-list');
                            }
                        });
                    });
                    
                    jQuery(obj).scrollTop(tempScrollTop);
                    locationSActive = false;
                });
                geodir_location_switcher_chosen_ajax();
            }
        });
    }, 1000);
});

function gdShareLocationError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert(geodir_location_all_js_msg.PERMISSION_DENINED);
            break;
        case error.POSITION_UNAVAILABLE:
            alert(geodir_location_all_js_msg.POSITION_UNAVAILABLE);
            break;
        case error.TIMEOUT:
            alert(geodir_location_all_js_msg.DEFAUTL_ERROR);
            break;
        case error.UNKNOWN_ERROR:
            alert(geodir_location_all_js_msg.UNKNOWN_ERROR);
            break;
    }
}

function gdLocationSetupUserLoc() {
    if(my_location) {
        jQuery('.geodir-search .fa-compass').css("color", "#087CC9");
        jQuery('.gt_near_me_s').prop('checked', true);
        jQuery('.snear').val(geodir_location_all_js_msg.msg_Near + ' ' + geodir_location_all_js_msg.msg_Me);
        jQuery('.sgeo_lat').val(lat);
        jQuery('.sgeo_lon').val(lon);
    } else {
        if(lat && lon) {
            jQuery('.geodir-search .fa-compass').css("color", "#087CC9");
            jQuery('.gt_near_me_s').prop('checked', true);
            jQuery('.snear').val(geodir_location_all_js_msg.msg_Near + ' ' + geodir_location_all_js_msg.msg_User_defined);
            jQuery('.sgeo_lat').val(lat);
            jQuery('.sgeo_lon').val(lon);
        } else if(jQuery('.snear').length && jQuery('.snear').val().match("^" + geodir_location_all_js_msg.msg_Near)) {
            jQuery('.geodir-search .fa-compass').css("color", "");
            jQuery('.gt_near_me_s').prop('checked', false);
            jQuery('.snear').val('');
            jQuery('.snear').blur();
            jQuery('.sgeo_lat').val('');
            jQuery('.sgeo_lon').val('');
        }
    }
}

function gd_location_admin_filter(el, lt) {
    var o, data, v = jQuery(el).val(), f = jQuery(el).closest('form'), scn = jQuery('select[name="_gd_country"]', f), srg = jQuery('select[name="_gd_region"]', f), sct = jQuery('select[name="_gd_city"]', f), org = jQuery('option:first-child', srg), oct = jQuery('option:first-child', sct);
    switch (lt) {
        case 'cn':
            sct.html(oct).attr('disabled', 'disabled');
            if (!v) {
                srg.html(org).attr('disabled', 'disabled');
                return false;
            }
            data = {
                cn: v
            }
        break;
        case 'rg':
            if (!v) {
                sct.html(oct).attr('disabled', 'disabled');
                return false;
            }
            var cnv = scn.val();
            if (!cnv) {
                srg.html(org).attr('disabled', 'disabled');
                return false;
            }
            data = {
                cn: cnv,
                rg: v,
            }
        break;
        default:
            return false;
        break;
    }

    data.action = 'geodir_location_ajax';
    data.gd_loc_ajax_action = 'get_location_options';

    jQuery.ajax({
        type: "POST",
        data: data,
        dataType: "json",
        url: ajaxurl,
        success: function(res) {
            if (typeof res == 'object' && typeof res.options != 'undefined') {
                switch (lt) {
                    case 'cn':
                        srg.html(org).append(res.options).removeAttr('disabled');
                    break;
                    case 'rg':
                        sct.html(oct).append(res.options).removeAttr('disabled');
                    break;
                }
            }
        }
    });
}

function geodir_lm_type_new_location(el, type) {
    var $el = jQuery(el).parent('.gd-new-loc-div');
    var loc = jQuery('input[type="text"]', $el).val();
    loc = loc ? jQuery.trim(loc) : '';
    if (loc) {
        var $select = $el.closest('.geodir_form_row').find('select.geodir_location_add_listing_chosen');

        if (!jQuery('option[value=""]', $select).text()) {
            jQuery('option[value=""]', $select).remove();
        }
        if (jQuery("option:contains('" + loc + "')", $select).length == 0) {
            $select.append('<option value="' + loc + '">' + loc + '</option>');
        }

        jQuery('option[value="' + loc + '"]', $select).attr("selected", true);
        $select.trigger("change");
        jQuery('input[type="text"]', $el).val("");
        $el.hide();
    } else {
        jQuery('input[type="text"]', $el).focus();
    }
}