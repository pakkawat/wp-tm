jQuery(document).ready(function(){
    geodir_init_rating();
});

function geodir_init_rating(){

    // Customize reating star image & style color for featured listings on listings page.
    if (typeof geodir_reviewrating_all_js_msg.geodir_reviewrating_rating_img_featured != 'undefined') {
        var rating_img_featured = geodir_reviewrating_all_js_msg.geodir_reviewrating_rating_img_featured;
        var rating_color_featured = geodir_reviewrating_all_js_msg.geodir_reviewrating_rating_color_featured;
        if (rating_img_featured || rating_color_featured) {
            jQuery('.gd-post-featured').each(function() {
                if (rating_img_featured !== '') {
                    jQuery(this).find('.gd_rating_show .geodir_Star img').attr('src', rating_img_featured);
                }
                if (rating_color_featured !== '') {
                    jQuery(this).find('.gd_rating_show .geodir_RatingAverage').css({
                        'background-color': rating_color_featured
                    });
                }
            });
        }
    }

    var min_lablewidth = 100;

    jQuery('.overall-more-rating').click(function(){
        jQuery(this).closest('.gdreview_section').find('.comment_more_ratings').slideToggle();
    });



    jQuery('.gd-rate-cat-in span.lable').each(function(i){
        if(jQuery(this).width() > min_lablewidth)
            min_lablewidth = jQuery(this).width();
    });

    jQuery('.gd-rate-cat-in span.lable').css({'width':min_lablewidth+'px'});

    jQuery('.gdrr-comment-replaylink a').bind('click',function(){
        jQuery('#commentform #err_no_rating').remove();
        jQuery('#commentform #gd_ratings_module').hide();
    });

    jQuery('.gdrr-cancel-replaylink a').bind('click',function(){
        jQuery('#commentform #err_no_rating').remove();
        jQuery('#commentform #gd_ratings_module').show();
    });

    jQuery('#gd_ratings_module').each(function() {
        var rat_obj = this;
        var $frm_obj = jQuery(rat_obj).closest('#commentform');
        if (parseInt($frm_obj.find('#comment_parent').val()) > 0) {
            jQuery('#commentform #err_no_rating').remove();
            jQuery('#commentform #gd_ratings_module').hide();
        }
        jQuery(rat_obj).closest('form#commentform').find('input[name="submit"]').click(function(e) {
            var $comment = jQuery('textarea[name="comment"]', $frm_obj);
            jQuery('#err_no_comment', $frm_obj).remove();
            jQuery(rat_obj).find('#err_no_rating').remove();
            var is_review = jQuery(rat_obj).closest('form#commentform').find('#comment_parent').val();
            is_review = parseInt(is_review) == 0 ? true : false;

            if (is_review) {
                var btn_obj = this;
                var invalid = 0;
                var optional_multirating = geodir_reviewrating_all_js_msg.geodir_reviewrating_optional_multirating;

                jQuery(rat_obj).find('[name^=geodir_rating]').each(function() {
                    var star_obj = this;
                    var mandatory = optional_multirating && jQuery(star_obj).attr('name') != 'geodir_rating[overall]' ? false : true;
                    var star = parseInt(jQuery(star_obj).val());
                    if (!star > 0 && mandatory) {
                        invalid++;
                    }
                });

                if (invalid > 0) {
                    jQuery(rat_obj).append('<div id="err_no_rating" class="err-no-rating">' + geodir_all_js_msg.gd_cmt_err_no_rating + '</div>');
                    return false;
                }
                
                if (jQuery.trim($comment.val()) == '') {
                    $comment.before('<div id="err_no_comment" class="err-no-rating">' + geodir_reviewrating_all_js_msg.err_empty_review + '</div>');
                    $comment.focus();
                    return false;
                }
                return true;
            } else {
                if (jQuery.trim($comment.val()) == '') {
                    $comment.before('<div id="err_no_comment" class="err-no-rating">' + geodir_reviewrating_all_js_msg.err_empty_reply + '</div>');
                    $comment.focus();
                    return false;
                }
            }
        });
    });

    jQuery('#rating_frm ul li').hover(
        function(){
            var $star_lable = jQuery(this).attr('data-star-label');

            jQuery(this).closest('ul').find('li').removeClass('active');

            jQuery(this).addClass('active');
            jQuery(this).prevAll().addClass('active');

            if(jQuery(this).closest('div').find('input').val() === '')
                jQuery(this).closest('div').find('input').val('0');

            jQuery(this).closest('div').find('.gd-rank').html($star_lable);

        },
        function(){
            jQuery(this).closest('div').find('.gd-rank').html('');

            jQuery(this).removeClass('active');
            jQuery(this).prevAll().removeClass('active');
        }
    );

    jQuery('#rating_frm ul li').click(function(){
        var $star_lable = jQuery(this).attr('data-star-label');
        var $star_rating = jQuery(this).attr('data-star-rating');

        jQuery(this).closest('ul').find('li').removeClass('active');

        jQuery(this).prevAll().addClass('active');
        jQuery(this).addClass('active');
        jQuery(this).closest('div').find('.gd-rank').html($star_lable);
        jQuery(this).closest('div').find('input').val($star_rating);
    });

    jQuery('#rating_frm ul').mouseleave(function(){

        var star_rating = jQuery(this).closest('div').find('input').val();

        if(star_rating !== '' || star_rating != '0')
        {
            jQuery(this).find('li:lt('+star_rating+')').addClass('active');
        }


        var $star_lable = jQuery(this).find('li.active:last').attr('data-star-label');
        var $star_rating = jQuery(this).find('li.active:last').attr('data-star-rating');
        jQuery(this).closest('div').find('.gd-rank').html($star_lable);

        if(jQuery(this).closest('div').find('input').val() === '')
            jQuery(this).closest('div').find('input').val($star_rating);
    });


    /* --- admin overall rating script --- */
    jQuery('#geodir_reviewrating_overall_settings').click(function(){

        var validate = true;
        if(!jQuery(this).closest('#form_div').find('input[name="file_off"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="file_off"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_overall_rating_off_img);
                validate = false;
                return false;
            }
        }

        if(!jQuery(this).closest('#form_div').find('input[name="file_on"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="file_on"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_overall_rating_on_img);
                validate = false;
                return false;
            }
        }

        if(!jQuery(this).closest('#form_div').find('input[name="file_half"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="file_half"]').closest('tr').find('img').attr('src') === '')
            {

                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_overall_rating_half_img);
                validate = false;
                return false;
            }
        }


        jQuery(this).closest('#form_div').find('.overall_rating_text').each(function(i){

            if(jQuery.trim(jQuery(this).val()) === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter+' '+(i+1)+' '+geodir_reviewrating_all_js_msg.geodir_reviewrating_score_text);
                validate = false;
                return false;
            }

        });

        if(validate == true)
        {
            if(chek_overall_category_firs_make_or_not() == true){
                jQuery(this).closest('form').submit();}
        }

    });


    /* --- admin overall rating script --- */
    jQuery('#manage_rating_submit').click(function(){

        var validate = true;
        if(!jQuery(this).closest('#form_div').find('input[name="multi_rating_category"]').val())
        {
            alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_enter_title);
            validate = false;
            return false;
        }


        jQuery(this).closest('#form_div').find('.star_rating_text').each(function(i){

            if(jQuery.trim(jQuery(this).val()) === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter+' '+(i+1)+' '+geodir_reviewrating_all_js_msg.geodir_reviewrating_star_text);
                validate = false;
                return false;
            }

        });

        if(!jQuery(this).closest('#form_div').find('input[name="s_file_off"]').val() && validate)
        {

            if(jQuery(this).closest('#form_div').find('input[name="s_file_off"]').closest('tr').find('img').attr('src') === '' || jQuery(this).closest('#form_div').find('input[name="s_file_off"]').closest('tr').find('img').length == '0')
            {

                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_rating_off_img);
                validate = false;
                return false;
            }

        }

        if(validate == false)
        {
            return false;
        }



        if(!jQuery(this).closest('#form_div').find('input[name="s_file_on"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="s_file_on"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_overall_rating_on_img);
                validate = false;
                return false;
            }
        }

        if(!jQuery(this).closest('#form_div').find('input[name="s_file_half"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="s_file_half"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_overall_rating_half_img);
                validate = false;
                return false;
            }
        }


        if(validate == true)
        {
            if(chek_category_firs_make_or_not() == true)
                jQuery(this).closest('form').submit();
        }

    });


    /* --- admin create rating script --- */
    jQuery('#create_rating_submit').click(function(){

        var validate = true;

        if(jQuery(this).closest('#form_div').find('select[id="geodir_rating_style_dl"]').val() == 0)
        {
            alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_multirating_style);
            validate = false;
            return false;
        }

        if(!jQuery(this).closest('#form_div').find('input[name="rating_title"]').val())
        {
            alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_enter_rating_title);
            validate = false;
            return false;
        }

        if(jQuery(this).closest('#form_div').find('.rating_checkboxs:checked').length == 0)
        {
            alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_post_type);
            validate = false;
            return false;
        }

        jQuery(this).closest('#form_div').find('.rating_checkboxs:checked').each(function(i){

            var ids = jQuery(this).attr('id');
            if(jQuery('#categories_type'+ids+' option:selected').length == 0)
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_select+' '+jQuery('#'+ids).val()+' '+geodir_reviewrating_all_js_msg.geodir_reviewrating_categories_text);
                validate = false;
                return false;
            }


        });

        if(validate == true)
        {
            jQuery(this).closest('form').submit();
        }

    });

    jQuery('.rating_checkboxs').click(function(){

        var val = jQuery(this).val();
        var ids = jQuery(this).attr('id');

        if(jQuery(this).is(':checked')){

            jQuery.post(geodir_reviewrating_all_js_msg.geodir_reviewrating_admin_ajax_url+"&ajax_action=ajax_tax_cat",{post_type: val})
                .done(function(data) {

                    jQuery('#categories_type'+ids).show();
                    jQuery('#categories_type'+ids).html(data);

                });

        }else{
            jQuery('#categories_type'+ids).hide();
        }

    });


    /* --- admin review settings script --- */
    jQuery('#geodir_review_settings').click(function(){

        var validate = true;
        if(!jQuery(this).closest('#form_div').find('input[name="file_like"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="file_like"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_review_like_img);
                validate = false;
                return false;
            }
        }

        if(!jQuery(this).closest('#form_div').find('input[name="file_unlike"]').val())
        {
            if(jQuery(this).closest('#form_div').find('input[name="file_unlike"]').closest('tr').find('img').attr('src') === '')
            {
                alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_select_review_unlike_img);
                validate = false;
                return false;
            }
        }

        if(validate == true)
        {
            jQuery(this).closest('form').submit();
        }

    });

}

function delete_rating(){
		if(!confirm(geodir_reviewrating_all_js_msg.geodir_reviewrating_rating_delete_confirmation))
		return false;
	}

//************************FUNCTION FOR CREATE THE DEFAULT TEXT BOX***************
function create_the_text_box_default()
{
	 var numeric = 5;
	 var n_default = '';
	 var input_box_default;
	for(var cond = 1;cond <= numeric ;cond++)
	{
		input_box_default = "<td>"+cond+" star text </td><td><input type = 'text' name = 'star_rating_text[]' value='' style='width:247px;'></td><br>";
		
		n_default = n_default + input_box_default; 
	}
			
	document.getElementById('one_two').innerHTML = n_default;
}
//*************************END OF THE DEFAULT TEXT BOX***************************

//*******************************CREATE TEXT BOX WHEN USER WANT******************
function create_the_text_box(check_cond)
{
	
	var numeric = isNaN(document.getElementById('star_count').value);
	var numeric_value = document.getElementById('star_count').value;
	//alert(numeric);
	if(numeric==false)
	{
		if(numeric_value >= 8)
		{
				alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_below+' 8');
				return false;
		}
		else if(numeric_value < 5)
		{
			alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_above+' 4');
			return false;
		}
		else
		{
			numeric = document.getElementById('star_count').value;
			var input_box; 
			var n='';
			
			for(var cond = 1;cond <= numeric;cond++)
			{
				input_box = "<td>"+cond+" star text </td><td><input type = 'text' name = 'star_rating_text[]' value='' style='width:247px;'></td><br>";
				
				n = n+input_box; 
			}
		}
		
		document.getElementById('one_two').innerHTML = n;
		
	}
	else if(numeric==true)
	{
		alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_numeric_validation);
		document.getElementById('star_count').focus;
		document.getElementById('star_count').value ='';
	}
}
	

function show_the_tr_of_input_star_text()
{
	//if(id=='select_star')
	{
		//jQuery('#show_the_count_text_box').hide('slow');
	}
	//else if(id=='select_text')
	{
		jQuery('#show_the_count_text_box').show('slow');
	}
}

function count_the_input_type_text()
{
	var count_of_input = jQuery('#input_type_text_on_request').find('input[type=text]').length;
	var star_count = jQuery('#star_count').val();
	
	if((star_count) == 8)
	{
		alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_maximum_star_rating_validation);
		return false;
	}
	else 
	{

		if(count_of_input != star_count)
		{
			var new_count_of_input = Number(count_of_input) + 1;
			var text_box = document.getElementById('input_type_text_on_request').innerHTML;
			var div_value = "disabled_div_"+new_count_of_input;	
			var input_type_id = "input_div_"+new_count_of_input;
			var delete_button_id = "div_"+new_count_of_input;
			var n_id  = '<div id='+div_value+'>'+new_count_of_input + 'star <input type="text" name="star_rating_text[]" id='+input_type_id+' value = ""  style="width:247px;"><input type="button" id='+delete_button_id+' value="Delete text box" onclick="delete_the_input_text_box(id)"></div>';
			n_id = text_box + n_id; 
			document.getElementById('input_type_text_on_request').innerHTML = n_id;
		}
	}
}

function delete_the_input_text_box(id)
{
	jQuery("#input_"+id).prop('disabled', true);
	jQuery("#input_"+id).remove();
	jQuery("#disabled_"+id).remove();
}

function chek_category_firs_make_or_not()
{
	var count_of_input = jQuery('#style_texts').find('input[type=text]:visible').length;
	
	var star_count = jQuery('#style_count').val();
	
		if(star_count != count_of_input)	
		{
			alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_star_and_input_box_validation);
			return false;
		}
		else
		{
			return true;
		}
}
jQuery(document).ready(function(){
	jQuery('#geodir_rating_style_dl').change(function() {
		if(jQuery('#geodir_rating_style_dl').val() != 0){
			jQuery('#multi_rating_category_tr').fadeOut();
		}
		else if(jQuery('#geodir_rating_style_dl').val() == 0)
		{
			jQuery('#multi_rating_category_tr').fadeIn();
		}
	});								
})


function chek_overall_category_firs_make_or_not()
{
	var count_of_input = jQuery('#overall_texts').find('input[type=text]:visible').length;
	
	var star_count = jQuery('#overall_count').val();
	
	
		if(star_count != count_of_input)	
		{
			alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_star_and_score_text_validation);
			return false;
		}
		else
		{
			return true;
		}
		
}

function overall_the_text_box(check_cond){	
		var total= document.getElementById('hidden-text').value;
		var serialized = jQuery('#hidden-serialized').val();
		
		if(serialized=='1' && total!=''){
			totalarr = geodir_reviewrating_unserialize(total);
		}else{
			totalarr = total.split(",");
		}
		len = totalarr.length;
		
		
		var numeric = isNaN(document.getElementById('overall_count').value);
		var numeric_value = document.getElementById('overall_count').value;
		
		if(numeric==false)
		{
			if(numeric_value > 10)
			{
					alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_below+' 10');
					return false;
			}
			else if(numeric_value < 3)
			{
				alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_above+' 2');
				return false;
			}
			else
			{
				numeric = document.getElementById('overall_count').value;
				var input_box; 
				var n='';
				
				
				
				var num =0;
				for(var cond = 1;cond <=numeric;cond++)
				{	
					if(len>=cond)
					{
						var rat_text = totalarr[num];
						rat_text = rat_text!== '' ? rat_text.replace(/\\'/g, "'") : rat_text;
						rat_text = rat_text!== '' ? rat_text.replace(/\\"/g, '&quot;') : rat_text;
						input_box = +cond+'&nbsp;'+geodir_reviewrating_all_js_msg.geodir_reviewrating_score_text+' &nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="'+rat_text+'" style="width:247px;"><br>';
					
					}else
					{
						input_box = +cond+'&nbsp;'+geodir_reviewrating_all_js_msg.geodir_reviewrating_score_text+' &nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="" style="width:247px;"><br>';
					}
					num++;
					n = n+input_box; 
				}
				
			}
			
			jQuery('#overall_texts').html(n);
			
		}
		else if(numeric==true)
		{
			alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_numeric_validation);
			document.getElementById('overall_count').focus;
			document.getElementById('overall_count').value ='';
		}
	}
	
// Mechanism ratint text fielsd

function style_the_text_box(check_cond){	
		
		
		var total= document.getElementById('hidden-stles-text').value;
		var serialized = jQuery('#hidden-stles-serialized').val();
		
		if(serialized=='1' && total!=''){
			totalarr = geodir_reviewrating_unserialize(total);
		}else{
			totalarr = total.split(",");
		}
		len = totalarr.length;
		var numeric = isNaN(document.getElementById('style_count').value);
		var numeric_value = document.getElementById('style_count').value;
		
		if(numeric==false)
		{
			if(numeric_value > 10)
			{
					alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_below+' 10');
					return false;
			}
			else if(numeric_value < 3)
			{
				alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_please_enter_above+' 2');
				return false;
			}
			else
			{
				numeric = document.getElementById('style_count').value;
				var input_box; 
				var n='';
				
				var num =0;
				for(var cond = 1;cond <=numeric;cond++)
				{	
					if(len>=cond)
					{
						var rat_text = totalarr[num];
						rat_text = rat_text!== '' ? rat_text.replace(/\\'/g, "'") : rat_text;
						rat_text = rat_text!== '' ? rat_text.replace(/\\"/g, '&quot;') : rat_text;
						input_box = +cond+'&nbsp;'+geodir_reviewrating_all_js_msg.geodir_reviewrating_star_text+' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="'+rat_text+'" style="width:247px;"><br>';
					
					}else
					{
						input_box = +cond+'&nbsp;'+geodir_reviewrating_all_js_msg.geodir_reviewrating_star_text+' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="" style="width:247px;"><br>';
					}
					num++;
					n = n+input_box; 
				}
				
			}
			
			jQuery('#style_texts').html(n);	
			
		}
		else if(numeric==true)
		{
			alert(geodir_reviewrating_all_js_msg.geodir_reviewrating_numeric_validation);
			document.getElementById('overall_count').focus;
			document.getElementById('overall_count').value ='';
		}
	}
	
function geodir_reviewrating_unserialize(data) {
	var that = this,
		utf8Overhead = function(chr) {
			var code = chr.charCodeAt(0);
			if(code < 0x0080) {
				return 0;
			}
			if(code < 0x0800) {
				return 1;
			}
			return 2;
		};
	error = function(type, msg, filename, line) {
		throw new that.window[type](msg, filename, line);
	};
	read_until = function(data, offset, stopchr) {
		var i = 2,
			buf = [],
			chr = data.slice(offset, offset + 1);
		while(chr != stopchr) {
			if((i + offset) > data.length) {
				error('Error', 'Invalid');
			}
			buf.push(chr);
			chr = data.slice(offset + (i - 1), offset + i);
			i += 1;
		}
		return [buf.length, buf.join('')];
	};
	read_chrs = function(data, offset, length) {
		var i, chr, buf;
		buf = [];
		for(i = 0; i < length; i++) {
			chr = data.slice(offset + (i - 1), offset + i);
			buf.push(chr);
			length -= utf8Overhead(chr);
		}
		return [buf.length, buf.join('')];
	};
	_unserialize = function(data, offset) {
		var dtype, dataoffset, keyandchrs, keys, contig,
			length, array, readdata, readData, ccount,
			stringlength, i, key, kprops, kchrs, vprops,
			vchrs, value, chrs = 0,
			typeconvert = function(x) {
				return x;
			};
		if(!offset) {
			offset = 0;
		}
		dtype = (data.slice(offset, offset + 1)).toLowerCase();
		dataoffset = offset + 2;
		switch(dtype) {
			case 'i':
				typeconvert = function(x) {
					return parseInt(x, 10);
				};
				readData = read_until(data, dataoffset, ';');
				chrs = readData[0];
				readdata = readData[1];
				dataoffset += chrs + 1;
				break;
			case 'b':
				typeconvert = function(x) {
					return parseInt(x, 10) !== 0;
				};
				readData = read_until(data, dataoffset, ';');
				chrs = readData[0];
				readdata = readData[1];
				dataoffset += chrs + 1;
				break;
			case 'd':
				typeconvert = function(x) {
					return parseFloat(x);
				};
				readData = read_until(data, dataoffset, ';');
				chrs = readData[0];
				readdata = readData[1];
				dataoffset += chrs + 1;
				break;
			case 'n':
				readdata = null;
				break;
			case 's':
				ccount = read_until(data, dataoffset, ':');
				chrs = ccount[0];
				stringlength = ccount[1];
				dataoffset += chrs + 2;
				readData = read_chrs(data, dataoffset + 1, parseInt(stringlength, 10));
				chrs = readData[0];
				readdata = readData[1];
				dataoffset += chrs + 2;
				if(chrs != parseInt(stringlength, 10) && chrs != readdata.length) {
					error('SyntaxError', 'String length mismatch');
				}
				break;
			case 'a':
				readdata = {};
				keyandchrs = read_until(data, dataoffset, ':');
				chrs = keyandchrs[0];
				keys = keyandchrs[1];
				dataoffset += chrs + 2;
				length = parseInt(keys, 10);
				contig = true;
				for(i = 0; i < length; i++) {
					kprops = _unserialize(data, dataoffset);
					kchrs = kprops[1];
					key = kprops[2];
					dataoffset += kchrs;
					vprops = _unserialize(data, dataoffset);
					vchrs = vprops[1];
					value = vprops[2];
					dataoffset += vchrs;
					if(key !== i) contig = false;
					readdata[key] = value;
				}
				if(contig) {
					array = new Array(length);
					for(i = 0; i < length; i++) array[i] = readdata[i];
					readdata = array;
				}
				dataoffset += 1;
				break;
			default:
				error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
				break;
		}
		return [dtype, dataoffset - offset, typeconvert(readdata)];
	};
	return _unserialize((data + ''), 0)[2];
}