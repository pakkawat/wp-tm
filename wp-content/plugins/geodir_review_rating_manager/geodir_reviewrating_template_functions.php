<?php
/**
 * Review manager General Tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $tab_name GeoDirectory Tab name.
 */
function geodir_review_rating_general_options($tab_name){
	switch ($tab_name)
	{
		case 'geodir_multirating_options' :

			geodir_admin_fields( geodir_reviewrating_default_options() );?>
				<p class="submit">
				<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodir_reviewratings' ); ?>" />
				<input type="hidden" name="subtab" value="geodir_multirating_options" id="last_tab" />
				</p>
			</div>
			<?php

		break;
	}// end of switch
}

/**
 * Adds rating manager fields to the comment text.
 *
 * @since 1.0.0
 * @since 1.3.5 Fix rating validation for font-awesome star rating.
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global string $geodir_post_type The post type.
 *
 * @param string $content Comment text.
 * @param string|object $comment The comment object.
 * @return string Modified comment text.
 */
function geodir_reviewrating_wrap_comment_text($content,$comment=''){
	global $geodir_post_type;
	$all_postypes = geodir_get_posttypes();

	if(!in_array($geodir_post_type, $all_postypes))
		return $content;

	if (geodir_cpt_has_rating_disabled($geodir_post_type)) {
		return $content;
	}

	$like_unlike = '';
	$ratings_html = '';
	$rating_html = '';
	$overallrating_html = '';
	if(!empty($comment) && !is_admin() && !$comment->comment_parent){
		if(get_option('geodir_reviewrating_enable_rating')):
			$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
			$comment_rating_overall = isset($comment_ratings->overall_rating) ? $comment_ratings->overall_rating : '';
			$overall_html = geodir_reviewrating_draw_overall_rating($comment_rating_overall);
			$ratings = @unserialize($comment_ratings->ratings);
            $ratings_html = geodir_reviewrating_draw_ratings($ratings);
		endif;

		if(!is_admin()){
			$comment_images = geodir_reviewrating_get_comment_images($comment->comment_ID);
		}

		$images_show_hide = '';
		$comment_images_display = '';

		if(get_option('geodir_reviewrating_enable_images')):

			$total_images = 0;
			if(isset($comment_images->images) && $comment_images->images != ''){
				$total_images = explode(',',$comment_images->images);
			}
			// open lightbox on click
			$div_click = (int)get_option( 'geodir_disable_gb_modal' ) != 1 ? 'div.place-gallery' : 'div.overall-more-rating';
			$onclick = !empty($comment_images) && count($total_images)>0 ? 'onclick="javascript:jQuery(this).closest(\'.gdreview_section\').find(\''.$div_click.' a:first\').trigger(\'click\');"' : '';

			$images_show_hide = '<span class="showcommentimages" data-comment-id="'.$comment->comment_ID.'" '.$onclick.' ><i class="fa fa-camera"></i> <a href="javascript:void(0);">';

            if (empty($comment_images) || count($total_images) == 0)
                $images_show_hide .= __('No Photo', 'geodir_reviewratings');
            elseif (count($total_images) == 1)
                $images_show_hide .= sprintf(__('%d Photo', 'geodir_reviewratings'), 1);
            else
                $images_show_hide .= sprintf(__('%d Photos', 'geodir_reviewratings'), (int)count($total_images));

            $images_show_hide .= '</a></span>';

            $comment_images_display = $images_show_hide;

		endif;

		if (get_option('geodir_reviewrating_enable_rating')):
			$overallrating_html = '<div class="comment_overall">'.$overall_html.'</div>';
			$rating_html = $ratings_html;
		endif;

		if(get_option('geodir_reviewrating_enable_review') && !is_admin()):
			$like_unlike = geodir_reviewrating_comments_like_unlike($comment->comment_ID, false);
		endif;

		ob_start(); ?>
			<div class="gdreview_section">
                <div class="clearfix">
                    <?php echo $overallrating_html; ?>
                    <div  style="float:left;"><?php echo $comment_images_display; ?></div>
                    <?php echo $like_unlike; ?>
                    <div class="overall-more-rating"><a href="javascript:void(0)"><?php _e('more', 'geodir_reviewratings');?></a></div>
                </div>
                <div class="comment_more_ratings clearfix <?php if (!$comment_images) { echo "gdreview_comment_no_images"; }?>">
					<?php echo 	$rating_html; ?>
                    <?php
                    if(isset($comment_images->html)){ echo $comment_images->html;}?>
                </div>
          	</div>
            <div class="commenttext geodir-reviewrating-commenttext"><?php echo $content;?></div>

		<?php $content = ob_get_clean();

		return $content;
	}else
		return 	$content;

}

/**
 * Adds overall rating and sorting options to the comment list.
 *
 * @since 1.0.0
 * @since 1.2.4 Modified to fix review sorting.
 * @since 1.3.5 Don't show overall when rating disabled.
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $post The current post object.
 * @global string $geodir_post_type The post type.
 *
 * @return bool|void
 */
function geodir_reviewrating_show_post_ratings() {
    global $post, $geodir_post_type;

    $all_postypes = geodir_get_posttypes();

    if (!in_array($geodir_post_type, $all_postypes))
        return false;

    if (geodir_cpt_has_rating_disabled($geodir_post_type)) {
        return;
    }

    if (isset($_REQUEST['comment_sorting'])) { ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('#gd-tabs dl dd').removeClass('geodir-tab-active');
                jQuery('#gd-tabs dl dd').find('a').each(function(){
                    if (jQuery(this).attr('data-tab') == '#reviews')
                        jQuery(this).closest('dd').addClass('geodir-tab-active');

                });
            });

        </script>
        <?php
    }

    if (!isset($post->ID)) {
        return;
    }
    $post_link = get_permalink($post->ID);

    $comment_shorting_form_field_val = array(
        'latest' => __( 'Latest', 'geodir_reviewratings' ),
        'oldest' => __( 'Oldest', 'geodir_reviewratings' ),
        'low_rating' => __( 'Lowest Rating', 'geodir_reviewratings' ),
        'high_rating' => __( 'Highest Rating', 'geodir_reviewratings' )
    );

    $comment_shorting_form_field_val = apply_filters( 'geodir_reviews_rating_comment_shorting', $comment_shorting_form_field_val );
    ?>
    <form name="comment_shorting_form" id="comment_sorting_form" method="get" action="<?php echo $post_link; ?>">
        <?php
        $query_variables = $_GET;

        $hidden_vars = '';
        if (!empty($query_variables)) {
            foreach ($query_variables as $key => $val) {
                $key = sanitize_text_field($key);
                $val = sanitize_text_field($val);
                if ( $key != 'comment_sorting')
                    $hidden_vars .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
            }
        }

        echo $hidden_vars;
        ?>
        <select name="comment_sorting" class="comment_sorting" onchange="jQuery(this).closest('#comment_sorting_form').submit()">
        <?php
        $page_comments = get_option('page_comments');
        $default_shorting = 'latest';

        if ( $page_comments ) {
            $default_comments_page = get_option('default_comments_page');

            if ( $default_comments_page == 'newest' ) {
                $default_shorting = 'latest';
            } else if ( $default_comments_page == 'oldest' ) {
                $default_shorting = 'oldest';
            }
        }
        /**
         * Filter the default comments sorting.
         *
         * @since 1.1.7
         * @package GeoDirectory_Review_Rating_Manager
         *
         * @param string $comment_sorting Sorting name to sort comments.
         */
        $comment_sorting = apply_filters( 'geodir_reviewrating_comments_shorting_default', $default_shorting );
        $comment_sorting = isset( $_REQUEST['comment_sorting'] ) && !empty( $_REQUEST['comment_sorting'] ) && isset( $comment_shorting_form_field_val[$_REQUEST['comment_sorting']] ) ? $_REQUEST['comment_sorting'] : $comment_sorting;

        if (isset($comment_shorting_form_field_val) && !empty($comment_shorting_form_field_val)) {
            foreach($comment_shorting_form_field_val as $key => $value) {
                ?>
                <option <?php if($comment_sorting ==  $key){echo 'selected="selected"';} ?> value="<?php echo $key; ?>"><?php _e($value, 'geodir_reviewratings');?></option>
                <?php
            }
        }
        ?>
        </select>
    </form>
    <?php
    $ratings = array();
    $ratings = geodir_reviewrating_get_post_rating($post->ID);
    if (!$ratings['overall']) {
        $ratings = '';
    }
    if (!empty($ratings)) {
        $overall_html = geodir_reviewrating_draw_overall_rating($ratings['overall']);
        $ratings_html = geodir_reviewrating_draw_ratings($ratings);
        echo '<div class="gd-rating-overall-rating"><span class="gd-rating-overall-rating-title">'.__('Overall Rating:', 'geodir_reviewratings').'</span>'.$overall_html.'</div>'.$ratings_html;
    }
}

/**
 * Rating manager create ratings tab - 'Select multirating style' dropdown html.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $style_id Rating style ID.
 */
function geodir_reviewrating_style_dl($style_id = '') {
    global $wpdb;

    $select_styles = $wpdb->get_results("SELECT * FROM " . GEODIR_REVIEWRATING_STYLE_TABLE);

    if (empty($style_id)) {
        if ($wpdb->get_var("SHOW COLUMNS FROM " . GEODIR_REVIEWRATING_STYLE_TABLE . " WHERE field = 'is_default'")) {
            $style_id = $wpdb->get_var("SELECT id FROM " . GEODIR_REVIEWRATING_STYLE_TABLE . " WHERE is_default='1'");
        }
    }
    ?>
    <select id="geodir_rating_style_dl" name="geodir_rating_style_dl" style="width:298px;">
        <option value="0"><?php _e('--Select multirating style--', 'geodir_reviewratings');?></option>
        <?php foreach ($select_styles as $select_style) { ?>
        <option <?php selected((int)$select_style->id, (int)$style_id);?> value="<?php echo $select_style->id; ?>"><?php _e(stripslashes($select_style->name), 'geodirectory'); ?></option>
        <?php } ?>
    </select>
    <?php
}

/**
 * over all rating.
 *
 * @since 1.0.0
 * @since 1.3.5 Don't draw rating when rating disabled.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param float|int $rating Average post rating.
 * @return string
 */
function geodir_reviewrating_draw_overall_rating($rating) {
	$overall_star = get_option('geodir_reviewrating_overall_count');
	$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
	$overall_star_onimg = get_option('geodir_reviewrating_overall_on_img');
	$overall_star_halfimg = get_option('geodir_reviewrating_overall_half_img');
	$star_offimg = get_option('geodir_reviewrating_overall_off_img');
	$star_color = get_option('geodir_reviewrating_overall_color');
	$overall_star_offimg_size = get_option('geodir_reviewrating_overall_off_img_width');
	$star_height = get_option('geodir_reviewrating_overall_off_img_height');
	$star_top = 0;
	$rtn_str = '';

	$floor_rating = floor($rating);
	$ceil_rating = 	ceil($rating);
	$overall_rating_star = '';
	$label = '';

	if ($overall_star > 0) {
		$rating_percent = ($rating / $overall_star) * 100;
	} else {
		$rating_percent = 0;
	}
	if ($overall_star_offimg_size) {
	} else {
		$overall_star_offimg_size = 23;
	}
	$star_width = $overall_star_offimg_size * $overall_star;

	$x = 1;
	$img_width = 100 / 	$overall_star;
	$rating_img = '<img src="'.$star_offimg.'" alt="' . esc_attr( __( 'rating icon', 'geodir_reviewratings' ) ) . '" style="width:'.$img_width.'%;" />';
	$rating_imgs = '';

	while ($x<=$overall_star) {
		$rating_imgs .= $rating_img;
		$x++;
	}

	/* fix rating star for safari */
    if($star_width>0){$attach_style = 'max-width:'.$star_width.'px';}else{$attach_style ='';}
	$overall_rating = '<div class="geodir-rating" style="' . $attach_style . '"><div class="gd_rating_show" data-average="'.$rating.'" ><div class="geodir_RatingAverage" style="width: '.$rating_percent.'%;background-color:'.$star_color.'"></div><div class="geodir_Star">'.$rating_imgs.'</div></div></div>';

	return apply_filters('geodir_reviewrating_draw_overall_rating_html', $overall_rating, $rating, $overall_star);
}


/**
 * Adds 'Individually rated for' ratings to the detail page.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 *
 * @param string|array $ratings Individual rating array.
 * @return string Rating HTML.
 */
function geodir_reviewrating_draw_ratings($ratings = '') {
    global $wpdb,$post;

    $post_id = isset($post->ID) ? $post->ID : '';
    if (!empty($post_id) && geodir_cpt_has_rating_disabled((int)$post_id)) {
        return NULL;
    }
    $rating_ids = array(0);
    $format = '%d';
    if (!empty($ratings)) {
        if (array_key_exists('overall',$ratings))
            unset($ratings['overall']);

        if (!empty($ratings))
            $rating_ids = array_keys($ratings);

        $rating_ids_length = count($rating_ids);
        if ($rating_ids_length > 0) {
            $rating_ids_format = array_fill(0, $rating_ids_length, '%d');
            $format = implode(',', $rating_ids_format);
        }

        $styles = $wpdb->get_results($wpdb->prepare("SELECT rt.id as id,
                                    rt.title as title,
                                    rt.post_type as post_type,
                                    rt.category as category,
                                    rt.check_text_rating_cond as check_text_rating_cond,
                                    rs.s_img_off  as s_img_off,
                                    rs.s_img_width as s_img_width,
                                    rs.s_img_height as s_img_height,
                                    rs.star_color as star_color,
                                    rs.star_lables as star_lables,
                                    rs.star_number as star_number
                                    FROM " . GEODIR_REVIEWRATING_CATEGORY_TABLE . " rt," . GEODIR_REVIEWRATING_STYLE_TABLE . " rs
                                    WHERE rt.category_id= rs.id and rt.id IN($format) order by rt.id", $rating_ids));

        $rating_style = array();

        foreach($styles as $style){
            $rating_style[$style->id] = $style;
        }
    }
    $label = '';
    $rtn_str = '';
    $rating_html = '';
    $star_width_overall = get_option('geodir_reviewrating_overall_off_img_width');
    $star_width_overall = $star_width_overall > 0 ? $star_width_overall : 23;

    if (!empty($ratings)) :
        $rating_html .= '<div class="gd_ratings_module_box">
            <h4>' . __('Individually rated for:', 'geodir_reviewratings') . '</h4>
            <div class="gd-rating-box-in clearfix">
                <div class="gd-rating-box-in-right">
                    <div class="gd-rate-category clearfix">';
                    foreach ( $ratings as $id => $rating ) {
                        if ( isset( $rating_style[$id] ) ) {
                            $rating_style_category = isset( $rating_style[$id]->category ) ? $rating_style[$id]->category : '';
                            $rating_cat = explode( ",", trim( ",", $rating_style_category ) );

                            $post_cat = array();
                            $post_categories = isset( $post->categories ) ? $post->categories : '';
                            $post_cat  = explode( ",", trim( ",", $post_categories ) );
                            $showing_cat = array_intersect( $rating_cat, $post_cat );

                            if ( !empty( $showing_cat ) ) {
                                $title = isset($rating_style[$id]->title) ? __($rating_style[$id]->title, 'geodirectory') : '';
                                $max_star = isset($rating_style[$id]->star_number) ? $rating_style[$id]->star_number : '';
                                $rating_style_star_lables = isset($rating_style[$id]->star_lables) ? $rating_style[$id]->star_lables : '';
                                $star_lable = geodir_reviewrating_star_lables_to_arr( $rating_style_star_lables, true );
                                $star_width = isset($rating_style[$id]->s_img_width) ? $rating_style[$id]->s_img_width : '';
                                $star_height = isset($rating_style[$id]->s_img_height) ? $rating_style[$id]->s_img_height : '';
                                $star_color = isset($rating_style[$id]->star_color) ? $rating_style[$id]->star_color : '#ff9900';
                                $star_offimg = isset($rating_style[$id]->s_img_off) ? $rating_style[$id]->s_img_off : '';

                                $star_width = $star_width > 0 ? $star_width : $star_width_overall;
                                $star_width_single = $star_width;

                                if(is_array($rating)){
                                    $rating = $rating['c'] > 0 ? $rating['r']/$rating['c'] : 0;
                                }

                                ($max_star > 0) ? $rating_percent = ($rating/$max_star)*100 : $rating_percent = 0;
                                $star_width = $star_width*$max_star;
                                $star_top = 2*$star_height;

                                $floor_rating = floor($rating);
                                $ceil_rating = ceil($rating);

                                $rating_html .=	'<div class="clearfix gd-rate-cat-in"><span class="lable">'.__(stripslashes_deep($title), 'geodir_reviewratings').'</span>';

                                $x = 1;
                                $img_width = 100 / 	$max_star;
								$rating_img = '<img src="'.$star_offimg.'" alt="' . esc_attr( __( 'rating icon', 'geodir_reviewratings' ) ) . '" style="width:'.$img_width.'%;" />';
                                $rating_imgs = '';

                                while( $x <= $max_star ) {
                                    $rating_imgs .= $rating_img;
                                    $x++;
                                }

                                $rating_star_html ='<div class="geodir-rating" style="max-width:' . ( $star_width_single * $x ) . 'px"><div class="gd_rating_show" data-average="'.$rating.'" data-id="'.$post_id.'"><div class="geodir_RatingAverage" style="width: '.$rating_percent.'%;background-color:'.$star_color.'"></div><div class="geodir_Star">'.$rating_imgs.'</div></div></div>';
                                $rating_html .= apply_filters('geodir_reviewrating_draw_ratings_html','<ul title="'. __($label, 'geodir_reviewratings').'" class="rate-area-list">'.$rating_star_html.'</ul>', $rating, $max_star);
                                $rating_html .= '</div>';
                            } // end if
                        }
                    }
                    $rating_html .= '</div>
                </div>
            </div>
        </div>';

    endif;

    return $rating_html;
}


/**
 * Display reviews in GD settings 'reviews' tab.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $geodir_reviews Reviews array.
 */
function geodir_reviewrating_show_comments($geodir_reviews=array()){

	echo '<ul>';

	if(!empty($geodir_reviews)){

		foreach($geodir_reviews as $comment){

						?>
            <li id="comment-<?php echo $comment->comment_ID; ?>"  >
                <div class="clearfix">
                    <div class="comment-info">
                        <div class="clearfix">
                            <form>
                           		<input name="chk-action[]" type="checkbox" value="<?php echo $comment->comment_ID; ?>" />
                            </form>
                            <div class="post-info">
                            	<h2 class="comment-post-title"><?php echo $comment->post_title; ?></h2>

															<?php
																$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
																$overall_html = geodir_reviewrating_draw_overall_rating($comment_ratings->overall_rating);
																echo 	'<span>'.$overall_html.'</span>';
															?>

                            	<p><?php echo wpautop(stripslashes($comment->comment_content)); ?></p>



															 <div class="post-action clearfix">

															<?php
																if($comment->comment_approved == '0')
																{
																	?>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', 'geodir_reviewratings');?></a></span>
																	<?php
																}elseif($comment->comment_approved == '1')
																{
																	?>

																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', 'geodir_reviewratings');?></a></span>
																	<?php
																}elseif($comment->comment_approved == 'spam')
																{
																	?>

																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="unspamcomment"><a href="javascript:void(0);"><?php _e('Not Spam', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', 'geodir_reviewratings');?></a></span>
																	<?php
																}elseif($comment->comment_approved == 'trash')
																{
																	?>

																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="untrashcomment"><a href="javascript:void(0);"><?php _e('Restore', 'geodir_reviewratings');?></a></span>
																	<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', 'geodir_reviewratings');?></a></span>
																	<?php
																}?>

<?php

	$multirating_over_all = unserialize (($comment->ratings));

	   //if($multirating_over_all[1] != 0) {
		 if(is_array($multirating_over_all) && array_filter($multirating_over_all)) { /* if all values not empty */  ?>
                                <span data-comment-id="<?php echo $comment->comment_ID; ?>" action="ratingshowhide" ><a href="javascript:void(0);"><?php _e('Show Multi Ratings', 'geodir_reviewratings');?></a></span>

	<?php }	?>

                                                                					<?php




																if($comment->comment_images != '')
																{
																?>
																<span data-comment-id="<?php echo $comment->comment_ID; ?>" action="commentimages" ><a href="javascript:void(0);"><?php _e('Show Images', 'geodir_reviewratings');?></a></span>
																<?php
																}
																?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="author-info">
                        <div class="clearfix">
                            <div class="avtar-img">
                            	<?php echo get_avatar( $comment->user_id, 60); ?>
                            </div>
                            <div class="author-name">
                            	<?php echo $comment->comment_author; ?>
                            	<span><?php echo $comment->comment_author_email; ?></span>
                            	<span><?php if(isset($comment->comment_author_IP)){echo $comment->comment_author_IP;} ?></span>
                            </div>
                        </div>
                        <span class="time">Submitted on:
                        <?php if(!function_exists('how_long_ago')){echo get_comment_date('M d, Y',$comment->comment_ID); } else { echo get_comment_time('M d, Y'); } ?>
                        </span>
                    </div>

                </div>
								<?php

									$ratings = @unserialize($comment_ratings->ratings);

									$ratings_html = geodir_reviewrating_draw_ratings($ratings);

									echo 	'<div class="edit-form-comment-rating" style="display:none;">'.$ratings_html.'</div>';?>

									<div class="edit-form-comment-images" style="display:none;">

									<?php
									if($comment->comment_images != '')
									{
										$comment_data = geodir_reviewrating_get_comment_images($comment->comment_id);
										echo $comment_data->html;

									}
								?>
									</div>
            </li>
	<?php }

	}
	echo '</ul>';
}


/**cd GD settings 'reviews' tab
 * GD settings 'reviews' tab pagination.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param array $comments Comments array.
 */
function geodir_reviewrating_pagination($comments=array()){

	global $show_post, $paged;

	if($paged == 0)
	{
		$paged = 1;
	}

	if($show_post > 0 && $show_post < $comments && $paged > 0)
	{

		$total_pages_exp = explode('.', $comments/$show_post);

		$total_pages = $total_pages_exp[0];

		if(isset($total_pages_exp[1]) && $total_pages_exp[1] > 0)
			$total_pages = $total_pages_exp[0]+1;

		$previous_link = 1;
		if($paged > 1)
			$previous_link = $paged-1;


		$next_link = $paged+1;

		if($next_link > $total_pages)
			$next_link = $paged;
		?>

		<div id="gd_pagging">


			<spam><?php echo $comments;?> <?php _e('Items', 'geodir_reviewratings');?></spam>

			<spam>
			<a class="<?php if($paged == 1){echo "disabled";}?>" title="<?php _e('Go to the first page', 'geodir_reviewratings');?>" style="text-decoration:none;" href="<?php echo esc_url( remove_query_arg( 'paged', get_permalink() ));?>">&laquo;</a>
			</spam>

			<spam>
			<a class="<?php if($paged == 1){echo "disabled";}?>" title="<?php _e('Go to the previous page', 'geodir_reviewratings');?>" style="text-decoration:none;" href="<?php echo esc_url( add_query_arg( 'paged', $previous_link, get_permalink() ) );?>"> &lt;</a>
			</spam>

			<spam>
			<input type="text" value="<?php echo $paged; ?>" style="width:30px; text-align:center;" /> <?php _e('of', 'geodir_reviewratings');?> <?php echo $total_pages;?>
			</spam>

			<spam>
			<a class="<?php if($paged == $total_pages){echo "disabled";}?>" title="<?php _e('Go to the next page', 'geodir_reviewratings');?>" style="text-decoration:none;" href="<?php echo esc_url( add_query_arg( 'paged', $next_link, get_permalink() ));?>">&gt;</a>
			</spam>

			<spam>
			<a class="<?php if($paged == $total_pages){echo "disabled";}?>" title="<?php _e('Go to the last page', 'geodir_reviewratings');?>" style="text-decoration:none;" href="<?php echo esc_url( add_query_arg( 'paged', $total_pages, get_permalink()) );?>">&raquo;</a>
			</spam>



		</div><?php

	}

}


/**
 * Adds Sub Tabs to the GD settings 'Reviews' main Tab.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param $seleted
 */
function geodir_reviewrating_show_tab_head($seleted){

		?>
		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == ''){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=all');?>"><?php _e('All', 'geodir_reviewratings');?><?php echo ' ('.geodir_reviewrating_get_comments_count(),')';?></a>
		</dd>

		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'pending'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=pending');?>">
		<?php _e('Pending', 'geodir_reviewratings');?><?php echo ' ('.geodir_reviewrating_get_comments_count('pending'),')';?>
		</a>
		</dd>

		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'approved'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=approved');?>">
		<?php _e('Approve', 'geodir_reviewratings');?><?php echo ' ('.geodir_reviewrating_get_comments_count('approved'),')';?>
		</a>
		</dd>

		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'spam'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=spam');?>">
		<?php _e('Spam', 'geodir_reviewratings');?><?php echo ' ('.geodir_reviewrating_get_comments_count('spam'),')';?>
		</a>
		</dd>

		<dd id="geodir_reviewrating_listing" class="<?php if($seleted == 'trash'){echo 'gd-tab-active';}?>">
		<a href="<?php echo admin_url('admin.php?page=geodirectory&tab=reviews_fields&subtab=trash');?>">
		<?php _e('Trash', 'geodir_reviewratings');?><?php echo ' ('.geodir_reviewrating_get_comments_count('trash'),')';?>
		</a>
		</dd>
	<?php
}


/**
 * Adds CSS and JS for comments like / unlike
 *
 * @since 1.0.0
 * @since 1.2.8 Modified to use user_id in like/useful.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param int $comment_id Comment ID.
 * @param bool $echo Print HTML? Default true.
 * @param bool $login_alert Whether to check and alert the user to login? Default false.
 * @return void|string HTML.
 */
function geodir_reviewrating_comments_like_unlike($comment_id, $echo = true, $login_alert = false) {
    $has_liked = geodir_reviewrating_has_comment_liked($comment_id);
    $get_total_likes = geodir_reviewrating_get_total_liked($comment_id);

    $script = false;
    if ($login_alert && !get_current_user_id()) {
        $script = '<script type="text/javascript">alert("' . esc_attr(__('You must be logged-in to like a review comment.', 'geodir_reviewratings')) . '")</script>';
    }

    $like_action = 'like';
    $like_class = '';
    $like_text = wp_sprintf(__('%d people like this.', 'geodir_reviewratings'), $get_total_likes);
    if ($has_liked) {
        $like_action = 'unlike';
        $like_class = ' gdrr-liked';
        if ($get_total_likes > 1) {
            $like_text = $get_total_likes == 2 ? __('You and 1 other like this.', 'geodir_reviewratings') : wp_sprintf(__('You and %d others like this.', 'geodir_reviewratings'), ($get_total_likes - 1));
        } else {
            $like_text = __('You like this.', 'geodir_reviewratings');
        }
    }

    $like_button = '<i class="fa fa-thumbs-o-up gdrr-btn-like"></i>';
    /**
     * Filter the like/useful button html.
     *
     * @since 1.2.8
     * @package GeoDirectory_Review_Rating_Manager
     *
     * @param string $like_button Like/useful button content.
     * @param string $like_action Action to handle on button click (like or unlike).
     * @param bool $has_liked True if current user has liked this, otherwise false.
     */
    $like_button = apply_filters('geodir_reviewrating_like_unlike_button', $like_button, $like_action, $has_liked);

    $html = '<div class="comments_review_likeunlike' . $like_class . '" data-comment-id="' . $comment_id . '" data-like-action="' . $like_action . '" data-wpnonce="' . esc_attr(wp_create_nonce('gd-like-' . (int)$comment_id)) . '"><span class="like_count">' . $like_button . $like_text . '</span>' . $script . '</div>';

    if ($echo)
        echo $html;
    else
        return $html;
}

/**
 * Returns comment images using comment ID.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param $comment_id
 * @return object
 */
function geodir_reviewrating_get_comment_images( $comment_id ) {
	global $wpdb;

	$comment_imges = $wpdb->get_var( $wpdb->prepare( "SELECT comment_images  FROM " . GEODIR_REVIEWRATING_POSTREVIEW_TABLE . " WHERE comment_id = %d", array( $comment_id ) ) );

	if ( !empty( $comment_imges ) ) {
		$all_comment_images = array();

		$row_images = explode( ',', $comment_imges );
		if ( !empty( $row_images ) ) {
			foreach( $row_images as $row_image ) {
				$row_image_filetype = $row_image != '' ? wp_check_filetype($row_image) : NULL;

				if ( ( !empty( $row_image_filetype ) && isset( $row_image_filetype['ext'] ) && !empty( $row_image_filetype['ext'] ) ) ) {
					$all_comment_images[] = $row_image;
				}
			}
		}
		$comment_imges = !empty( $all_comment_images ) ? implode( ',', $all_comment_images ) : '';

		ob_start();
		?>
		<?php if( (int)get_option( 'geodir_disable_gb_modal' ) != 1 ) { /* disable gd modal */ ?>
		<script type="text/javascript">
		 jQuery(function() {
			jQuery('#place-gallery-<?php echo $comment_id; ?> a').lightBox({
				overlayOpacity : 0.5,
				imageLoading : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-ico-loading.gif';?>',
				imageBtnNext : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-next.gif';?>',
				imageBtnPrev : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-prev.gif';?>',
				imageBtnClose : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-btn-close.gif';?>',
				imageBlank : '<?php echo GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/lightbox-blank.gif';?>'
			});
		});
		</script>
		<?php } ?>
		<div id="place-gallery-<?php echo $comment_id; ?>" class="place-gallery">
		<?php
		if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
		?>
			<div class="clearfix reviews_rating_images_all_images">
				<ul class="reviews_rating_images_wrap_in_ul">
		<?php
		}

		foreach ( $all_comment_images as $comm_img ) {
			$comm_img_filetype = $comm_img != '' ? wp_check_filetype($comm_img) : NULL;

			if ( !( !empty( $comm_img_filetype ) && isset( $comm_img_filetype['ext'] ) && !empty( $comm_img_filetype['ext'] ) ) ) {
				continue;
			}

			$comm_img_title = geodir_reviewrating_get_image_name( $comm_img );

			if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
			?>
				<li>
					<div class="reviews_rating_images_backend reviews_rating_images_frontend">

					<?php $delimgwpnonce = wp_create_nonce( 'del_img_'.$comment_id );?>
					<div class="thumb">
						<span class="review_rating_thumb_remove review_rating_thumb_remove_link_frontend review_rating_thumb_remove_link_backend">&nbsp;&nbsp;<input type="hidden" name="comment_id" value="<?php  echo $comment_id; ?>" /><input type="hidden" name="delimgwpnonce" value="<?php  echo $delimgwpnonce ; ?>" /></span>
					</div>
					<div class="review_rating_images"><a href="<?php echo $comm_img;?>"><img width="125" height="115" title="<?php echo $comm_img_title;?>" alt="<?php echo $comm_img_title;?>" src="<?php echo $comm_img;?>"></a></div>
					</div>
				</li>
			<?php } else { ?><a href="<?php echo $comm_img;?>"><img width="125" height="115" title="<?php echo $comm_img_title;?>" alt="<?php echo $comm_img_title;?>" src="<?php echo $comm_img;?>"></a><?php
			}
		}

		if ( is_admin() && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'reviews_fields' ) {
			echo '</ul></div>';
		}
		?>
		</div>
		<?php
		$comment_img_html =	ob_get_clean();

		return (object)array( 'images' => $comment_imges, 'html' => $comment_img_html );
	}
}

/**
 * Adds rating manager 'Overall Rating Settings' tab - form fields.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_overall_settings_form(){
    $nonce = wp_create_nonce( 'geodir_overall_rating' );
    $rating_img_featured = get_option( 'geodir_reviewrating_overall_off_img_featured' );
    $rating_img_featured = $rating_img_featured != '' ? $rating_img_featured : get_option( 'geodir_reviewrating_overall_off_img' );

    $rating_color_featured = get_option( 'geodir_reviewrating_overall_color_featured' );
    $rating_color_featured = $rating_color_featured != '' ? $rating_color_featured : get_option( 'geodir_reviewrating_overall_color' );
?>
    <div class="gd-content-heading active">
        <h3><?php _e('Overall Rating Settings', 'geodir_reviewratings'); ?></h3>
        <input type="hidden" name="geodir_overall_rating_nonce" value="<?php echo $nonce; ?>" />
        <div id="form_div">
            <table class="form-table" id='tblSample1'>
                <tr>
                    <th><?php _e('Overall rating image.', 'geodir_reviewratings');?></th>
                    <td>
                    <?php if(get_option('geodir_reviewrating_overall_off_img')){?>
                    <img style="width:32px;"src="<?php echo get_option('geodir_reviewrating_overall_off_img'); ?>" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" />
                    <?php }?>
                    </td>
                    <td><input type="file" name="file_off" value="" /> </td>
                </tr>
                <tr>
                    <th><?php _e( 'Overall rating image for featured listing:', 'geodir_reviewratings' );?></th>
                    <td><?php if ( $rating_img_featured ) { ?><img style="width:32px;" src="<?php echo $rating_img_featured; ?>" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" /><?php } ?></td>
                    <td><input type="file" name="file_off_featured" value="" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Overall rating color code', 'geodir_reviewratings');?></th>
                    <td>&nbsp;</td>
                    <td><input class="" type="color" name="overall_color" id="overall_color" value="<?php if(get_option('geodir_reviewrating_overall_color')!=''){echo get_option('geodir_reviewrating_overall_color'); }else{echo '#ff9900';}?>"  autocomplete="off" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Overall rating color code for featured listing:', 'geodir_reviewratings' );?></th>
                    <td>&nbsp;</td>
                    <td><input class="" type="color" name="overall_color_featured" id="overall_color_featured" value="<?php echo ( $rating_color_featured != '' ? $rating_color_featured : '#ff9900' ); ?>"  autocomplete="off" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Overall rating setting score', 'geodir_reviewratings');?></th>
                    <td>&nbsp;</td>
                    <td>
                        <input class="" type="text" name="overall_count" id="overall_count" value="<?php if(get_option('geodir_reviewrating_overall_count')!=''){echo get_option('geodir_reviewrating_overall_count'); }else{echo '5';}?>" onBlur="overall_the_text_box()" autocomplete="off" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Overall rating score text.', 'geodir_reviewratings');?></th>
                    <td>
                        <?php
                        if ($overall_rating_texts = get_option('geodir_reviewrating_overall_rating_texts'))
                            $getoratting = $overall_rating_texts;

                        if (isset($getoratting) && count($getoratting) > 0) {
                            $impl = geodir_reviewrating_serialize_star_lables( $getoratting );
                        }
                        ?>
                        <input type="hidden" id="hidden-text" value="<?php echo ( isset( $impl ) ? esc_attr( $impl ) : '' ); ?>" />
                        <input type="hidden" id="hidden-serialized" value="<?php echo ( isset($impl) && is_serialized( $impl ) ? 1 : 0 ); ?>" />
                    </td>
                    <td id="overall_texts">
                        <?php
                        $i = 1;
                        if (isset($getoratting) && count($getoratting) > 0) {
                            foreach ($getoratting as $value) {
                                $value = stripslashes($value);
                                $value = esc_attr($value);
                                echo $i . ' '.__('Score text', 'geodir_reviewratings') . ' &nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="' . $value . '" style="width:247px;"/><br>';
                                $i++;
                            }
                        } else {
                            for($k = 1; $k <= 5; $k++) {
                                echo $k .' ' . __('Score text', 'geodir_reviewratings') . '&nbsp;&nbsp;<input class="overall_rating_text" type="text" name="overall_rating_text[]" value="" style="width:247px;"><br>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <p class="submit" style="padding-left:10px; margin-bottom:10px;"><input id="geodir_reviewrating_overall_settings" type="button" class="button-primary" value="<?php _e('Update Settings', 'geodir_reviewratings');?>" /></p>
        </div>
    </div>
    <?php
}

/**
 * Adds rating manager 'Ratings styles' tab - form fields.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_reviewrating_manage_rating_style_form()
{
    global $wpdb;
    $ajax_url = geodir_reviewrating_ajax_url();
    $category_select_info = array();

    $cat_id = '';
    if (isset($_REQUEST['cat_id'])) {
        $cat_id = (int)$_REQUEST['cat_id'];
        $category_select_info = geodir_reviewrating_get_style_by_id($cat_id);
    }
    $nonce = wp_create_nonce('geodir_update_rating_styles');

    $style_title = isset($category_select_info->name) ? esc_attr(stripslashes($category_select_info->name)) : '';
    $star_lables = isset($category_select_info->star_lables) ? $category_select_info->star_lables : '';
    $style_serialized = $star_lables != '' && is_serialized($star_lables) ? 1 : 0;
    $star_number = isset($category_select_info->star_number) && $category_select_info->star_number !== '' ? (int)$category_select_info->star_number : 5;
    $star_color = isset($category_select_info->star_color) && $category_select_info->star_color !== '' ? esc_attr($category_select_info->star_color) : '#ff9900';

    $star_lables = $star_lables != '' ? esc_attr($star_lables) : '';
    ?>
    <div class="gd-content-heading active">
        <div id="li_tab2_div" class="multi_rating_div">
            <h3><?php _e('Manage Rating Styles', 'geodir_reviewratings'); ?></h3>
            <div id="form_div">
                <input type="hidden" name="geodir_update_rating_styles_nonce" value="<?php echo $nonce; ?>" />
                <input type="hidden" name="update_category" value="<?php echo $cat_id; ?>" />
                <input type="hidden" name="cat_id_save" value="save" />
                <table class="widefat">
                    <tr id="multi_rating_category_tr">
                        <th scope="row"><?php _e('Title', 'geodir_reviewratings');?></th>
                        <td><input class="regular-text" type="text" name="multi_rating_category" id="multi_rating_category" value="<?php echo $style_title; ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Rating score (default 5)', 'geodir_reviewratings');?></th>
                        <td>
                            <input type="hidden" id="hidden-stles-text" value="<?php echo $star_lables; ?>" />
                            <input type="hidden" id="hidden-stles-serialized" value="<?php echo $style_serialized; ?>" />
                            <input class="regular-text" type="text" name="style_count" id="style_count" value="<?php echo $star_number; ?>" onBlur="style_the_text_box()" autocomplete="off" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Rating text', 'geodir_reviewratings');?></th>
                        <td id="style_texts">
                            <input type="hidden" name="update_category" value="<?php echo $cat_id; ?>" />
                            <?php
                            $values = isset($category_select_info->star_lables) ? $category_select_info->star_lables : '';
                            $arr = array();
                            if ($values != '') {
                                $arr = geodir_reviewrating_star_lables_to_arr($values);
                            }

                            if (count($arr) > 0) {
                                $i = 1;

                                foreach ($arr as $value) {
                                    $value = stripslashes($value);
                                    echo $i . ' ' . __('Star Text', 'geodir_reviewratings') . ' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="' . esc_attr($value) . '" style="width:247px;"/><br>';
                                    $i++;
                                }
                            } else {
                                for ($k = 1; $k <= 5; $k++) {
                                    echo $k . ' ' . __('Star Text', 'geodir_reviewratings') . ' &nbsp;&nbsp;<input class="star_rating_text" type="text" name="star_rating_text[]" value="" style="width:247px;"/><br>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Rating off image.&nbsp;', 'geodir_reviewratings');?>
                            <?php if (isset($category_select_info->s_img_off)) { ?>
                            <img style="width:32px;" src="<?php echo $category_select_info->s_img_off; ?>" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" />
                        <?php } ?>
                        </th>
                        <td><input type="file" name="s_file_off" value="" /></td>
                    </tr>
                    <tr>
                    <th scope="row"><?php _e('Rating color', 'geodir_reviewratings');?></th>
                    <td>
                        <input class="regular-text" type="color" name="style_color" id="style_color" value="<?php echo $star_color; ?>" autocomplete="off" />
                    </td>
                    </tr>
                </table>
                <p class="submit" style="padding-left:10px;"><input type="button" class="button-primary" value="<?php esc_attr_e('Save Style', 'geodir_reviewratings') ?>" name="submit_Categorie" id="manage_rating_submit" /></p>
            </div>
            <table class="widefat" >
                <thead>
                    <tr>
                        <th width="50"><strong><?php _e('S No.', 'geodir_reviewratings');?></strong></th>
                        <th><strong><?php _e('Style Title', 'geodir_reviewratings');?></strong></th>
                        <th><strong><?php _e('Rating Text', 'geodir_reviewratings');?></strong></th>
                        <th><strong><?php _e('Image', 'geodir_reviewratings');?></strong></th>
                        <th><strong><?php _e('Max Rating', 'geodir_reviewratings');?></strong></th>
                        <th><strong><?php _e('Action', 'geodir_reviewratings');?></strong></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $geodir_rating_styles = geodir_reviewrating_get_styles();

                if ($geodir_rating_styles) {
                    $counter = 1;

                    foreach ($geodir_rating_styles as $geodir_category_reviews ) {
                        $nonce = wp_create_nonce( 'geodir_delete_rating_styles_' . $geodir_category_reviews->id );
                        $bgcolor = $counter%2 == 0 ? '#FFF' : '#FCFCFC';

                        echo '<tr style="background-color:' . $bgcolor . ';height:40px;">';
                        echo '<td>' . $counter . '</td>';
                        echo '<td>' . __(stripslashes($geodir_category_reviews->name), 'geodirectory') . '</td>';
                        echo '<td>' . geodir_reviewrating_star_lables_to_str( $geodir_category_reviews->star_lables, true ) . '</td>';
                        echo '<td><img style="background-color:' . $geodir_category_reviews->star_color . '" src="' . $geodir_category_reviews->s_img_off . '" alt="' . esc_attr( __( 'rating icon', 'geodir_reviewratings' ) ) . '"/>
                        </td>';
                        echo '<td>' . $geodir_category_reviews->star_number . '</td>';

                        $url = admin_url('admin.php');
                        $edit_action = add_query_arg(array('page' => 'geodirectory&tab=multirating_fields&subtab=geodir_rating_style','cat_id' => $geodir_category_reviews->id), esc_url($url));
                        $delete_action = add_query_arg(array('ajax_action' => 'delete_style','cat_id' => $geodir_category_reviews->id, '_wpnonce' => $nonce), esc_url($ajax_url));

                        echo '<td><a href="' . $edit_action . '"> <img src="' . GEODIR_REVIEWRATING_PLUGINDIR_URL . '/images/edit.png" alt="' . esc_attr__('Edit Rating', 'geodir_reviewratings') . '" title="' . esc_attr__('Edit Rating', 'geodir_reviewratings') . '"/> </a>&nbsp;&nbsp;<a href="' . $delete_action . '" onclick="return delete_rating();"> <img src="' . GEODIR_REVIEWRATING_PLUGINDIR_URL . '/images/delete.png" alt="' . esc_attr__('Delete Rating', 'geodir_reviewratings') . '" title="' . esc_attr__('Delete Rating', 'geodir_reviewratings') . '"/> </a></td>';

                        echo '<td>&nbsp;</td>';
                        echo '</tr>';
                        $counter++;
                    }
                } else {
                    echo '<tr><td colspan="7" align="center">' . __('No Record Found.', 'geodir_reviewratings') . '</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}


/**
 * Adds rating manager 'create ratings' tab settings - form fields.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 */
function geodir_reviewrating_create_rating_form() {
    global $cat_display,$post_cat,$table_prefix, $wpdb;

    $ajax_url = geodir_reviewrating_ajax_url();

    $rating_cat_id = isset($_REQUEST['rating_cat_id']) ? (int)$_REQUEST['rating_cat_id'] : '';
    if ($rating_cat_id) {
        $sqlquery = $wpdb->prepare("SELECT * FROM ".GEODIR_REVIEWRATING_CATEGORY_TABLE." WHERE id = %d", array($rating_cat_id));
        $qry_result = $wpdb->get_row($sqlquery);
    }
    $nonce = wp_create_nonce('geodir_create_rating_nonce');
    $qry_result_category_id = isset($qry_result->category_id) ? $qry_result->category_id : '';
    $show_star = isset($qry_result->check_text_rating_cond) && (int)$qry_result->check_text_rating_cond == 0 ? 0 : 1;
		$show_rating = isset($qry_result->show_rating) && (int)$qry_result->show_rating == 0 ? 0 : 1;
    ?>
    <div class="gd-content-heading active">
        <h3><?php _e('Create Ratings', 'geodir_reviewratings');?></h3>
        <div id="form_div">
            <?php if ($rating_cat_id) { ?>
            <input type="hidden" value="<?php echo $rating_cat_id;?>" name="rating_cat_id" />
            <?php } ?>
            <input type="hidden" name="geodir_create_rating_nonce_field" value="<?php echo $nonce; ?>" />
            <table class="form-table" id='tblSample'>
                <tr>
                    <th scope="row"><?php _e('Select multirating style', 'geodir_reviewratings');?></th>
                    <td><?php echo geodir_reviewrating_style_dl($qry_result_category_id); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Rating title', 'geodir_reviewratings');?></th>
                    <td><input class="regular-text" type="text" name="rating_title" id="rating_title" value="<?php if (isset($qry_result->title)) { echo $qry_result->title; } ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Showing method', 'geodir_reviewratings');?></th>
                    <td>
                        <input type="radio" name="show_star" id="select_star" value="1" <?php checked($show_star, 1);?> />
                        <?php _e('Show star', 'geodir_reviewratings');?>
                        <input type="radio" name="show_star" id="select_text" value="0" <?php checked($show_star, 0);?> />
                        <?php _e('Show dropdown', 'geodir_reviewratings');?>
                 </td>
                </tr>
								<tr>
                    <th scope="row"><?php _e('Show rating', 'geodir_reviewratings');?></th>
                    <td>
												<input type="checkbox" name="show_rating" id="show_rating" <?php checked($show_rating, 1)?>/>
												<?php _e('Enabled', 'geodir_reviewratings');?>
                 </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Select post type', 'geodir_reviewratings');?></th>
                    <td>
                        <?php
                        $post_arr = array();
                        if(isset($qry_result->post_type) && $qry_result->post_type!='')
                            $post_arr = explode(',',$qry_result->post_type);

                        $geodir_post_types = get_option( 'geodir_post_types' );
                        $geodir_posttypes = geodir_get_posttypes();

                        $i = 1;
                        foreach ($geodir_posttypes as $p_type) {
                            $geodir_posttype_info = $geodir_post_types[$p_type];
                            $listing_slug = $geodir_posttype_info['labels']['singular_name'];
                            $checked = !empty($post_arr) && in_array($p_type, $post_arr) ? 1 : 0;
                            $display = !$checked ? 'display:none' : '';
                            ?>
                            <div style="float:left; border:1px solid #CCCCCC; margin:0 5px;" >
                                <input type="checkbox" name="post_type<?php echo $i; ?>" id="_<?php echo $i; ?>" value="<?php echo $p_type;?>" class="rating_checkboxs" <?php checked($checked, 1)?> /><b>&nbsp;<?php echo geodir_ucwords($listing_slug);?>&nbsp;</b>
                                <?php
                                $cat_display = 'select';
                                $post_cat = isset($qry_result->category) ? $qry_result->category : '';
                                ?><br/>
                                <select id="categories_type_<?php echo $i; ?>" name="categories_type_<?php echo $i; ?>[]"  multiple="multiple" style="<?php echo $display;?>">
                                <?php
                                // WPML
                                $is_wpml = geodir_is_wpml();
                                if ($is_wpml) {
                                    global $sitepress;
                                    $active_lang = ICL_LANGUAGE_CODE;

                                    $sitepress->switch_lang('all', true);
                                }
                                // WPML

                                echo geodir_custom_taxonomy_walker($p_type . 'category');

                                // WPML
                                if ($is_wpml) {
                                    $sitepress->switch_lang($active_lang, true);
                                }
                                // WPML
                                ?>
                                </select>
                            </div>
                            <?php
                            $i++;
                        }
                        ?>
                        <input type="hidden" value="<?php echo $i -= 1; ?>" name="number_of_post" />
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="button" id="create_rating_submit" class="button-primary" value="<?php _e('Save Changes', 'geodir_reviewratings') ?>"  /></p>
        </div>
        <table class="widefat" style="width:100%;">
            <thead>
                <tr>
                    <th width="50"><strong><?php _e('S No.', 'geodir_reviewratings');?></strong></th>
                    <th width="100"><strong><?php _e('Rating Style', 'geodir_reviewratings');?></strong></th>
                    <th width="100"><strong><?php _e('Rating Title', 'geodir_reviewratings');?></strong></th>
                    <th width="100"><strong><?php _e('Rating Image', 'geodir_reviewratings');?></strong></th>
                    <th width="125"><strong><?php _e('Post Types', 'geodir_reviewratings');?></strong></th>
                    <th width="125"><strong><?php _e('Categories', 'geodir_reviewratings');?></strong></th>
                    <th width="50"><strong><?php _e('Action', 'geodir_reviewratings');?></strong></th>
                </tr>
            </thead>
            <tbody>
            <?php
                $geodir_reviews = geodir_reviewrating_rating_categories();
                $check_multi_category_name = '';

                if (!empty($geodir_reviews)) {
                    $counter = 1;

                    foreach ($geodir_reviews as $wnw_review ) {
                        $nonce = wp_create_nonce( 'geodir_delete_rating_'.$wnw_review->id );
                        $bgcolor = $counter%2 == 0 ? '#FFF' : '#FCFCFC';
                        $rating_style = geodir_reviewrating_get_style_by_id($wnw_review->category_id);

                        echo '<tr style="background-color:' . $bgcolor . ';height:40px;">';
                            echo '<td>' . $counter . '</td>';
                            echo '<td>' . __(stripslashes($rating_style->name), 'geodirectory') . '</td>';
                            echo '<td>' . __(stripslashes($wnw_review->title), 'geodirectory') . '</td>';
                            echo '<td><img style="width:16px;background-color:' . $rating_style->star_color . '" src="' . $rating_style->s_img_off . '" alt="' . esc_attr( __( 'rating icon', 'geodir_reviewratings' ) ) . '" />';
                            echo '</td>';
                            echo '<td>';
                            if ($wnw_review->post_type != '') {
                                $post_types = explode(',', $wnw_review->post_type);

                                if (!empty($post_types)) {
                                    $j = 0;
                                    $comma = '';
                                    $get_post_types = '';

                                    foreach ($post_types as $ptype) {
                                        $post_typeinfo = get_post_type_object($ptype);

                                        if ($j != 0)
                                            $comma = ', ';

                                        $get_post_types .= $comma . geodir_ucwords($post_typeinfo->labels->singular_name);
                                        $j++;
                                    }
                                }
                                echo $get_post_types;
                            }
                            echo '</td>';
                            echo '<td>';

                            if (isset($wnw_review->multi_category_name))
                                $check_multi_category_name .= ',' . $wnw_review->multi_category_name . ',';

                            $category = trim($wnw_review->category, ",");

                            $terms = explode(",", $category);
                            $rating_term = '';

                            foreach ($terms as $termid)
                                $rating_term .= $wpdb->get_var($wpdb->prepare("SELECT name FROM " . $table_prefix . "terms WHERE term_id = %d", array($termid))) . ',';

                            echo trim($rating_term, ',');
                            echo '</td>';

                            $url = admin_url( 'admin.php');
                            $edit_action =  add_query_arg(array('page' => 'geodirectory&tab=multirating_fields&subtab=geodir_create_rating','rating_cat_id' => $wnw_review->id), esc_url($url));
                            $delete_action = add_query_arg(array('ajax_action' => 'delete_rating_category', 'rating_cat_id' => $wnw_review->id, '_wpnonce' => $nonce), esc_url($ajax_url));

                            echo '<td><a href="' . $edit_action . '"><img src="' . GEODIR_REVIEWRATING_PLUGINDIR_URL.'/images/edit.png" alt="' . esc_attr__('Edit Rating', 'geodir_reviewratings') . '" title="' . esc_attr__('Edit Rating', 'geodir_reviewratings') . '"/></a>&nbsp;&nbsp;<a href="' . $delete_action . '" onclick="return delete_rating();">&nbsp;<img src="' . GEODIR_REVIEWRATING_PLUGINDIR_URL . '/images/delete.png" alt="' . esc_attr__('Delete Rating', 'geodir_reviewratings') . '" title="' . esc_attr__('Delete Rating', 'geodir_reviewratings') . '"/></a></td>';
                        echo '</tr>';
                        $counter++;
                    }
                } else {
                    echo '<tr><td colspan="7" align="center">' . __('No Record Found.', 'geodir_reviewratings') . '</td></tr>';
                }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Adds Like / Unlike icon upload form fields to the GD settings page Like / Unlike Icons Tab.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_manage_review_form(){

	$nonce = wp_create_nonce( 'geodir_update_review_nonce' );
	?>
	<div class="gd-content-heading active">
	<h3><?php _e('Manage Like / Unlike Icons', 'geodir_reviewratings');?></h3>
		<div id="form_div">
			<input type="hidden" name="geodir_update_review_nonce_field" value="<?php echo $nonce; ?>" />
				<table class="form-table" id='tblSample1'>

						<tr>
							<th><?php _e('Review like image', 'geodir_reviewratings');?></th>
							<td><img style="width:32px;"src="<?php echo get_option('geodir_reviewrating_review_like_img'); ?>" alt="<?php esc_attr_e( 'like icon', 'geodir_reviewratings' ); ?>" /></td>
							<td><input type="file" name="file_like" value="" /> </td>
						</tr>
						<tr>
							<th><?php _e('Review unlike image', 'geodir_reviewratings');?></th>
							<td><img style="width:32px;" src="<?php echo get_option('geodir_reviewrating_review_unlike_img'); ?>" alt="<?php esc_attr_e( 'unlike icon', 'geodir_reviewratings' ); ?>" /></td>
							<td><input type="file" name="file_unlike" value="" /> </td>
						</tr>

				</table>
			<p class="submit" style="padding-left:10px;">
				<input id="geodir_review_settings" type="button" class="button-primary" value="<?php _e('Update Settings', 'geodir_reviewratings');?>"  />
				</p>
		</div>

	</div>
	<?php
}

/**
 * Adds comment image upload form field to the detail page.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_rating_img_html(){

	$id = "comment_images";
	$svalue = isset($curImages) ? $curImages : '';
	$multiple = true;
	$width = geodir_media_image_large_width();
	$height = geodir_media_image_large_height();?>

	<div class="gd-form_row clearfix" id="<?php echo $id; ?>dropbox" style="border:1px solid #ccc;min-height:100px;height:auto;padding:10px;text-align:center">
		<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />
			<div class="gd-plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>gd-plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-upload-ui">
				<h4><?php _e('Drop files to upload', 'geodir_reviewratings');?></h4><br/>
				<input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php _e('Select Files', 'geodir_reviewratings'); ?>" class="button"/>
					<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id.'pluploadan'); ?>"></span>
					<?php if ($width && $height): ?>
							<span class="plupload-resize"></span>
							<span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
							<span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
					<?php endif; ?>
					<div class="filelist"></div>
		</div>

			<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?> clearfix" id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
			</div>
			<span id="upload-msg" ><?php _e('Please drag &amp; drop the images to rearrange the order', 'geodir_reviewratings');?></span>
			<span id="upload-error" style="display:none"></span>
	</div><?php
}

/**
 * Adds comment multi rating form fields to the detail page.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $post The current post object.
 */
function geodir_reviewrating_rating_frm_html(){

	if($overall_star = get_option('geodir_reviewrating_overall_count')){
		$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
		$star_color = get_option('geodir_reviewrating_overall_color');
		$overall_star_offimg = get_option('geodir_reviewrating_overall_off_img');
		$star_width = get_option('geodir_reviewrating_overall_off_img_width');
		global $post;
		$star_texts = array();
		?>

		<div id="gd_ratings_module">
				<div id="rating_frm" style="margin-top:15px;">
					<div class="gd-rating-box-in clearfix">
						 <div class="gd-rating-box-in-left">
								 <div class="gd-rate-area clearfix">
										<style scoped>ul.rate-area-list li.active, ul.rate-area-list li.active{background-color:<?php echo $star_color;?>}</style>
										<span class="gd-ratehead"><?php printf(__('Rate this %s (overall):', 'geodir_reviewratings'),get_post_type_singular_label($post->post_type));?></span>
										<?php ob_start(); // Start buffering; ?>
										<ul class="rate-area-list">
											 <?php for($star=1; $star <= $overall_star; $star++){
											 	$overall_star_text = isset( $overall_star_lable[$star-1] ) ? esc_attr( $overall_star_lable[$star-1] ) : '';
												$overall_star_text = stripslashes_deep( __( $overall_star_text, 'geodirectory' ) );
												$star_texts[] = $overall_star_text;
												?>
												 <li data-star-rating="<?php echo $star;?>" data-star-label="<?php echo $overall_star_text;?>" class="gd-multirating-star"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px; height:auto;" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" /></a></li>
											 <?php } ?>
										</ul>
										<span class="gd-rank">&nbsp;</span>
										<?php
										$gd_reviewrating_overall = ob_get_clean();
										echo apply_filters('geodir_reviewratings_overall', $gd_reviewrating_overall, $star_texts);
										?>
										<input type="hidden" class="geodir_reviewratings_current_rating" name="geodir_rating[overall]" value="0"  />
								</div>
					<?php
                    $post_arr = (array)$post ;
                    if(isset($post_arr[$post->post_type.'category']))
                        $post_categories = explode(",",$post_arr[$post->post_type.'category']);
                    else
                        $post_categories = wp_get_post_categories( $post->ID, array( 'fields' => 'ids') );

                    $ratings = geodir_reviewrating_rating_categories();
                                if ($ratings) {
                                    $rating_style_html = '';
                                    foreach($ratings as $rating){
                                        if (!in_array($post->post_type, explode(",", $rating->post_type))) {
                                            continue;
                                        }// if not for this post type then skip.
                                        $rating->title = isset( $rating->title ) && $rating->title != '' ? __( $rating->title, 'geodirectory' ) : '';
                                        $star_lable = geodir_reviewrating_star_lables_to_arr( $rating->star_lables, true );
                                        $rating_cat = explode(",",$rating->category);

                                        // fix id's if wpml is active
                                        if ( geodir_wpml_is_taxonomy_translated( $post->post_type . 'category' ) ) {
                                            if (is_array($rating_cat)) {
                                                foreach ($rating_cat as $key => $std_cat) {
                                                    $rating_cat[$key] = geodir_wpml_object_id($std_cat, $post->post_type.'category', false);
                                                }
                                            }
                                        }

										$showing_cat = array_intersect($rating_cat,$post_categories);


											if(!empty($showing_cat)){

												//$show_rating = (int)$rating->show_rating == 0 ? 0 : 1;
												if($rating->show_rating){
														if($rating->check_text_rating_cond){

															$rating_style_html .= '<div class="clearfix gd-rate-cat-in">';
															$rating_style_html .= '<style scoped>ul.rate-area-list.rating-'.$rating->id.' li.active, body ul.rating-'.$rating->id.' li.active{background-color:'.$rating->star_color.'}</style>';
															$rating_style_html .= '<span class="lable">'. __(stripslashes_deep($rating->title), 'geodir_reviewratings').'</span>';

															$rating_style_html_filtered = '<ul class="rate-area-list rating-'.$rating->id.'">';
		                                                      $star_texts = array();
															for($star=1; $star <= $rating->star_number; $star++){
		                                                          $star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
		                                                          $star_lable_text = stripslashes_deep( $star_lable_text );
																$star_texts[] = $star_lable_text;
		                                                          $rating_style_html_filtered .= '<li data-star-rating="'.$star.'" data-star-label="'. $star_lable_text.'"  class="gd-multirating-star"><a><img src="'.$rating->s_img_off.'" style="width:'.$rating->s_img_width.'px;height:auto" alt="' . esc_attr( __( 'rating icon', 'geodir_reviewratings' ) ) . '" /></a></li>';
		                                                       }
		                                                      $rating_style_html_filtered .= '</ul>';
		                                                      $rating_style_html_filtered .= '<span class="gd-rank">&nbsp;</span>';

		                                                      $rating_style_html .= apply_filters('geodir_reviewratings_individual', $rating_style_html_filtered, $star_texts);

		                                                      $rating_style_html .= '<input type="hidden" class="geodir_reviewratings_current_rating" name="geodir_rating['.$rating->id.']" value="0"  />';
															$rating_style_html .= '</div>';

														}else{

															$rating_style_html .= '<div class="clearfix gd-rate-cat-in">';
															$rating_style_html .= '<span class="lable">'.stripslashes_deep($rating->title).'</span>';
															$rating_style_html .= '<select name="geodir_rating['.$rating->id.']" > ';
															for($star=1; $star <= $rating->star_number; $star++){
																$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
																$star_lable_text = stripslashes_deep( $star_lable_text );
																$rating_style_html .= '<option value="'.$star.'">';
																$rating_style_html .= $star_lable_text;
																$rating_style_html .= '</option>	';
															}
																$rating_style_html .= '</select>';
															$rating_style_html .= '</div>';

														}
													}
												}

											}

											if($rating_style_html != ''){?>

												<div class="gd-rate-category clearfix">
														<span class="gd-ratehead"><?php printf(__('Rate this %s individually for:', 'geodir_reviewratings'),get_post_type_singular_label($post->post_type));?></span>
														<div>
														<?php echo $rating_style_html; ?>
														</div>
												 </div><?php

											}
									 } ?>

						 </div>

				 </div>
				</div>
		</div><?php

	}
}


/**
 * Adds rating box to admin comment edit page.
 *
 * @since 1.0.0
 * @since 1.3.6 Changes for disable review stars for certain post type.
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param object $comment Comment object.
 * @return bool
 */
function geodir_reviewrating_comment_rating_box($comment) {

	if ($overall_star = get_option('geodir_reviewrating_overall_count')) {
		$post_type = get_post_type( $comment->comment_post_ID );

		$all_postypes = geodir_get_posttypes();

		if (!in_array($post_type, $all_postypes)) {
			return false;
		}

		if (!empty($post_type) && geodir_cpt_has_rating_disabled($post_type)) {
			return false;
		}

		$overall_star_lable = get_option('geodir_reviewrating_overall_rating_texts');
		$star_color = get_option('geodir_reviewrating_overall_color');
		$overall_star_offimg = get_option('geodir_reviewrating_overall_off_img');
		$star_width = get_option('geodir_reviewrating_overall_off_img_width');

		global $comment,$wpdb,$is_geodir_loop;

		$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
		if (!empty($comment_ratings)) {
		?>
		<div id="gd_ratings_module">
				<div id="rating_frm" style="margin-top:15px;">
					<lable for="rating"><?php _e('You can rate the area', 'geodir_reviewratings');?></lable>
					<div class="gd-rating-box-in clearfix">
						 <div class="gd-rating-box-in-left">
								 <div class="gd-rate-area clearfix">
										<style scoped>ul.rate-area-list li.active{background-color:<?php echo $star_color;?>}</style>
										<span class="gd-ratehead"><?php _e('Rate this area:', 'geodir_reviewratings');?></span>
											<?php ob_start(); ?>
											<ul class="rate-area-list">
											<?php
											$star_texts = array();
											for($star=1; $star <= $overall_star; $star++){

												if($comment_ratings->overall_rating && $star <= (int)$comment_ratings->overall_rating )
													$active = 'active';
												else
													$active = '';

												$overall_star_text = isset( $overall_star_lable[$star-1] ) ? esc_attr( $overall_star_lable[$star-1] ) : '';
												$overall_star_text = stripslashes_deep( __( $overall_star_text, 'geodirectory' ) );
												$star_texts[] = $overall_star_text;
												?>
													<li data-star-rating="<?php echo $star;?>" data-star-label="<?php echo $overall_star_text;?>"  class="gd-multirating-star <?php echo $active;?>"><a><img src="<?php echo $overall_star_offimg;?>" style="width:<?php echo $star_width;?>px" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" /></a></li>
											<?php } ?>
											</ul>
											<?php
											$rating_html = ob_get_clean();
											if ((int)get_option('geodir_reviewrating_enable_font_awesome') == 1) {
												echo geodir_font_awesome_rating_form_html('', $star_texts, $comment_ratings->overall_rating);
											} else {
												echo $rating_html;
												echo '<span class="gd-rank">' . ($comment_ratings->overall_rating ? $overall_star_text : '&nbsp;') . '</span>';
											}
											?>
                                          <?php
										  $overall_star_text = isset( $overall_star_lable[$comment_ratings->overall_rating - 1] ) ? esc_attr( $overall_star_lable[$comment_ratings->overall_rating - 1] ) : '';
										  $overall_star_text = stripslashes_deep( __( $overall_star_text, 'geodirectory' ) );
										  ?>
											<input type="hidden" name="geodir_rating[overall]" value="<?php echo ($comment_ratings->overall_rating) ? $comment_ratings->overall_rating : '0'; ?>"  />
								</div>
					<?php

						$post_type = get_post_type( $comment->comment_post_ID );

						$post_categories = wp_get_post_terms( $comment->comment_post_ID, $post_type.'category', array( 'fields' => 'ids') );

					$ratings = geodir_reviewrating_rating_categories();
					$old_ratings = @unserialize($comment_ratings->ratings);

					if($ratings):?>
					<div class="gd-rate-category clearfix">
									<span class="gd-ratehead"><?php _e('Rate this area:', 'geodir_reviewratings');?></span>
									<div><?php

													foreach($ratings as $rating):

													//$star_lable = explode(",",$rating->star_lables);
													$star_lable = geodir_reviewrating_star_lables_to_arr( $rating->star_lables, true );

													$rating->title = isset( $rating->title ) && $rating->title != '' ? __( $rating->title, 'geodirectory' ) : '';

													$rating_cat = explode(",",$rating->category);

													$showing_cat = array_intersect($rating_cat,$post_categories);

													if(!empty($showing_cat)){

														if($rating->check_text_rating_cond):
														?>
															<div class="clearfix gd-rate-cat-in">
																	<style scoped>ul.rate-area-list.rating-<?php echo $rating->id;?> li.active{background-color:<?php echo $rating->star_color;?>}</style>
																	<span class="lable"><?php echo $rating->title;?></span>
																	<?php ob_start(); ?>
																	<ul class="rate-area-list rating-<?php echo $rating->id;?>">
																	<?php
																	$star_texts = array();
																	for($star=1; $star <= $rating->star_number; $star++){

																		if($old_ratings[$rating->id] && $star <= (int)$old_ratings[$rating->id] )
																			$active = 'active';
																		else
																			$active = '';

																		$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
																		$star_lable_text = stripslashes_deep( $star_lable_text );
																		$star_texts[] = $star_lable_text;
																		?>
																		<li data-star-rating="<?php echo $star;?>" data-star-label="<?php echo $star_lable_text;?>"  class="gd-multirating-star <?php echo $active;?>"><a><img src="<?php echo $rating->s_img_off;?>" style="width:<?php echo $rating->s_img_width;?>px" alt="<?php esc_attr_e( 'rating icon', 'geodir_reviewratings' ); ?>" /></a></li>
																	<?php } ?>
																	</ul>
																	<?php
																	$rating_html = ob_get_clean();
																	if ((int)get_option('geodir_reviewrating_enable_font_awesome') == 1) {
																		echo geodir_font_awesome_rating_form_html('', $star_texts, $old_ratings[$rating->id]);
																	} else {
																		echo $rating_html;
																		$star_lable_text = isset( $star_lable[$old_ratings[$rating->id] - 1] ) ? esc_attr( $star_lable[$old_ratings[$rating->id] - 1] ) : '';
																		$star_lable_text = stripslashes_deep( $star_lable_text );
																		echo '<span class="gd-rank">' . ($old_ratings[$rating->id] ? $star_lable_text : '&nbsp;') . '</span>';
																	}
																	?>
																	<input type="hidden" name="geodir_rating[<?php echo $rating->id;?>]" value="<?php echo ($old_ratings[$rating->id]) ? $old_ratings[$rating->id] : '0'; ?>"  />
															</div>
						<?php
						else:
						?>
							<div class="clearfix gd-rate-cat-in">
									<span class="lable"><?php _e($rating->title, 'geodir_reviewratings');?></span>
									<select name="geodir_rating[<?php echo $rating->id;?>]" >
										<?php for($star=1; $star <= $rating->star_number; $star++){
											$star_lable_text = isset( $star_lable[$star-1] ) ? esc_attr( $star_lable[$star-1] ) : '';
											$star_lable_text = stripslashes_deep( $star_lable_text );
										?>
										<option value="<?php echo $star;?>" <?php if($old_ratings[$rating->id]) echo 'selected="selected"'; ?>  ><?php echo $star_lable_text;?></option>
									<?php } ?>
									</select>
							</div>
						<?php
						endif;

					}// endif
					endforeach;?>

									</div>
							 </div>
								<?php endif;?>
						 </div>
				 </div>
				</div>
		</div>
		<?php
		}
	}
}


/**
 * GeoDirectory settings page 'reviews' Tab comments.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_reviewrating_manage_comments() {
    $geodir_commentsearch = isset($_REQUEST['geodir_comment_search']) && $_REQUEST['geodir_comment_search'] != '' ? sanitize_text_field($_REQUEST['geodir_comment_search']) : '';
    $post_type = isset($_REQUEST['geodir_comment_posttype']) && $_REQUEST['geodir_comment_posttype'] != '' ? sanitize_text_field($_REQUEST['geodir_comment_posttype']) : '';
    $status = sanitize_text_field($_REQUEST['subtab']);

    $orderby = 'comment_date_gmt';
    $order = 'DESC';

    $geodir_comment_sort = isset($_REQUEST['geodir_comment_sort']) ? sanitize_text_field($_REQUEST['geodir_comment_sort']) : '';
    $paged = isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : 1;

    if ($geodir_comment_sort == 'oldest') {
        $orderby = 'comment_date_gmt';
        $order = 'ASC';
    } else if ($geodir_comment_sort == 'lowest_rating') {
        $orderby = 'overall_rating';
        $order = 'ASC';
    } else if($geodir_comment_sort == 'highest_rating') {
        $orderby = 'overall_rating';
        $order = 'DESC';
    }

    if ($comments_per_page = get_option('comments_per_page'))
        $show_post = $comments_per_page;
    else
        $show_post = 20;

    $defaults = array(
        'paged' => $paged,
        'show_post' => $show_post,
        'orderby' => $orderby,
        'order' => $order,
        'post_type' => $post_type,
        'comment_approved' => $status,
        'user_id' => '',
        'search' => $geodir_commentsearch,
    );

    $comments = geodir_reviewrating_get_comments($defaults);
    $nonce = wp_create_nonce('geodir_review_action_nonce');

    $geodir_commentsearch = $geodir_commentsearch != '' ? esc_attr(stripslashes($geodir_commentsearch)) : ''
    ?>
    <div style="float:right;margin-top:0px;"><?php echo geodir_reviewrating_pagination($comments['comment_count']);?></div>
    <div style="clear:both;"></div>
    <div class="gd-content-heading" style="display:block">
        <h3>
            <div class="clearfix">
                <input name="checkedall" type="checkbox" value="" style="float:left; margin-top:8px;" />
                <div class="three-tab">
                    <ul class="clearfix">
                        <?php if ($_REQUEST['subtab'] == 'pending') { ?>
                        <li action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', 'geodir_reviewratings');?></a></li>
                        <li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', 'geodir_reviewratings');?></a></li>
                        <li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', 'geodir_reviewratings');?></a></li>
                        <?php } else if ($_REQUEST['subtab'] == 'approved') { ?>
                        <li action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', 'geodir_reviewratings');?></a></li>
                        <li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', 'geodir_reviewratings');?></a></li>
                        <li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', 'geodir_reviewratings');?></a></li>
                        <?php } else if ($_REQUEST['subtab'] == 'spam') { ?>
                        <li action="unspamcomment"><a href="javascript:void(0);"><?php _e('Not Spam', 'geodir_reviewratings');?></a></li>
                        <li action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', 'geodir_reviewratings');?></a></li>
                        <?php } else if ($_REQUEST['subtab'] == 'trash') { ?>
                        <li action="untrashcomment"><a href="javascript:void(0);"><?php _e('Restore', 'geodir_reviewratings');?></a></li>
                        <li action="deletecomment"><a href="javascript:void(0);"><?php _e('Delete Permanently', 'geodir_reviewratings');?></a></li>
                        <?php } else { ?>
                        <li action="approvecomment"><a href="javascript:void(0);"><?php _e('Approve', 'geodir_reviewratings');?></a></li>
                        <li action="unapprovecomment"><a href="javascript:void(0);"><?php _e('Unapprove', 'geodir_reviewratings');?></a></li>
                        <li action="spamcomment"><a href="javascript:void(0);"><?php _e('Spam', 'geodir_reviewratings');?></a></li>
                        <li action="trashcomment"><a href="javascript:void(0);"><?php _e('Trash', 'geodir_reviewratings');?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <form>
                    <input type="hidden" name="geodir_review_action_nonce_field" value="<?php echo $nonce; ?>" />
                    <input type="hidden" name="review_url" value="<?php echo admin_url('admin.php?page=geodirectory');?>" />
                    <input type="hidden" name="geodir_review_paged" value="<?php echo $paged;?>" />
                    <input type="hidden" name="geodir_review_show_post" value="<?php echo $show_post;?>" />
                    <input type="hidden" name="tab" value="reviews_fields" />
                    <input type="hidden" name="subtab" value="<?php echo $status;?>" />
                    <div class="gd-search"><input name="geodir_comment_search" value="<?php echo $geodir_commentsearch;?>" type="text" /></div>
                    <div class="gd-search">
                        <?php
                        $geodir_post_types = get_option('geodir_post_types');
                        $geodir_posttypes = geodir_get_posttypes();
                        ?>
                        <select name="geodir_comment_posttype" id="commentposttype">
                            <option value=""><?php _e('Show all post types', 'geodir_reviewratings');?></option>
                            <?php
                            if (!empty($geodir_posttypes)) {
                                foreach ($geodir_posttypes as $p_type) {
                                    $geodir_posttype_info = $geodir_post_types[$p_type];
                                    $listing_slug = $geodir_posttype_info['labels']['singular_name'];

                                    echo '<option value="' . $p_type . '" ' . selected($p_type, $post_type, false). '>' . $listing_slug . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="gd-sort">
                        <label><?php _e('Sort :');?></label>
                        <select name="geodir_comment_sort">
                            <option <?php selected('newest', $geodir_comment_sort) ?> value="newest"><?php _e('Newest', 'geodir_reviewratings');?></option>
                            <option <?php selected('oldest', $geodir_comment_sort) ?> value="oldest"><?php _e('Oldest', 'geodir_reviewratings');?></option>
                            <option <?php selected('lowest_rating', $geodir_comment_sort) ?> value="lowest_rating"><?php _e('Lowest rating', 'geodir_reviewratings');?></option>
                            <option <?php selected('highest_rating', $geodir_comment_sort) ?> value="highest_rating"><?php _e('Highest rating', 'geodir_reviewratings');?></option>
                        </select>
                    </div>
                    <div class="gd-search" style="padding-top:2px;">
                        <input id="gdcomment-filter_button" class="button-primary" type="button" name="searchfilter" value="<?php _e('Filter', 'geodir_reviewratings');?>" />
                    </div>
                </form>
            </div>
        </h3>
        <div class="comment-listing"><?php geodir_reviewrating_show_comments($comments['comments']);?></div>
    </div>
    <?php
}

/**
 * Adds google rich snippets.
 *
 * @since 1.0.0
 * @deprecated 1.3.2 Use geodir_action_details_micordata()
 * @see geodir_action_details_micordata()
 * @package GeoDirectory_Review_Rating_Manager
 */
function geodir_review_rating_reviews_rich_snippets() {
	if ( geodir_is_geodir_page() && geodir_is_page( 'detail' ) ) {
		$post_id = get_the_ID();

		$geodir_post_info = geodir_get_post_info( $post_id );

		if ( !empty( $geodir_post_info ) ) {
			$post_title = $geodir_post_info->post_title;
			$post_thumbnail = '';
			$post_thumbnail_id = get_post_thumbnail_id( $post_id );

			$max_rating = get_option('geodir_reviewrating_overall_count');
			$average_rating = geodir_get_commentoverall_number( $post_id );
			$total_reviews = geodir_get_review_count_total( $post_id );

			if ( $total_reviews > 0 ) {
				if ( $post_thumbnail_id > 0 ) {
					$attachment_image = wp_get_attachment_image_src( $post_thumbnail_id, 'post-thumbnail' );
					$post_thumbnail = !empty( $attachment_image ) && isset( $attachment_image[0] ) && $attachment_image[0] != '' ? $attachment_image[0] : '';
				}

				$content = '';
				$content .= '<div style="height:0;width:0;margin:0;padding:0" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
				$content .= '<meta itemprop="itemreviewed" content="' . esc_attr( $post_title ) . '" />';
				if ( $post_thumbnail != '' ) {
					$content .= '<meta itemprop="photo" content="' . $post_thumbnail . '" />';
				}
				$content .= '<div itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
				$content .= '<meta itemprop="average" content="' . $average_rating . '" />';
				$content .= '<meta itemprop="best" content="' . $max_rating . '" />';
				$content .= '</div>';
				$content .= '<meta itemprop="count" content="' . $total_reviews . '" />';
				$content .= '</div>';

				echo $content;
			}
		}
	}
}

/**
 * Get image name from image src.
 *
 * @since 1.0.0
 * @package GeoDirectory_Review_Rating_Manager
 *
 * @param string $img_src Image url.
 * @return mixed|string Image name.
 */
function geodir_reviewrating_get_image_name( $img_src ) {
	$comm_img_title = '';
	if ( $img_src != '' ) {
		$comm_img_str = basename( $img_src );
		if ( $comm_img_str != '' ) {
			if ( strpos( $comm_img_str, '.' ) !== false ) {
				$comm_img_arr = explode( '.', $comm_img_str );
				if ( !empty( $comm_img_arr ) ) {
					unset( $comm_img_arr[( count( $comm_img_arr ) - 1 )] );
					if ( !empty( $comm_img_arr ) ) {
						$comm_img_str = implode( ".", $comm_img_arr );
					}
				}
			}

			if ( strpos( $comm_img_str, '_' ) !== false ) {
				$comm_img_arr = explode( '_', $comm_img_str );
				if ( !empty( $comm_img_arr ) ) {
					unset( $comm_img_arr[( count( $comm_img_arr ) - 1 )] );
					if ( !empty( $comm_img_arr ) ) {
						$comm_img_str = implode( "_", $comm_img_arr );
					}
				}
			}

			$comm_img_title = preg_replace( '/[_-]/', ' ', $comm_img_str );
		}
	}
	return $comm_img_title;
}
