<?php

function geodir_advance_search_field_wrapper_start( $field_info ) {
	global $as_fieldset_start;

	if ( $as_fieldset_start > 0 ) {
		$html = '';
	} else {
		$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
		$htmlvar     = $field_info->site_htmlvar_name;
		$html        = '<div class="geodir-filter-cat gd-type-single gd-field-' . $htmlvar . '">';
		$html .= '<span>' . stripslashes( __( $field_label, 'geodirectory' ) ) . '</span>';
		$html .= '<ul>';
	}

	return $html;
}

function geodir_advance_search_field_wrapper_end( $field_info ) {
	global $as_fieldset_start;

	if ( $as_fieldset_start > 0 ) {
		$html = '';
	} else {
		$html = '</ul></div>';
	}

	return $html;
}

/**
 * Get the html output for the custom search field: checkbox.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_fieldset( $html, $field_info, $post_type ) {

	global $as_fieldset_start;

	if ( $as_fieldset_start == 0 ) {
		$as_fieldset_start ++;
		$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
		$htmlvar     = $field_info->site_htmlvar_name;
		$html        = '<div class="geodir-filter-cat gd-type-single gd-field-' . $htmlvar . '">';
		$html .= '<span>' . stripslashes( __( $field_label, 'geodirectory' ) ) . '</span>';
		$html .= '<ul>';
	} else {
		$as_fieldset_start ++;
		$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
		$htmlvar     = $field_info->site_htmlvar_name;
		$html        = '</ul></div>'; //end the prev
		$html .= '<div class="geodir-filter-cat gd-type-single gd-field-' . $htmlvar . '-' . $as_fieldset_start . '">';
		$html .= '<span>' . stripslashes( __( $field_label, 'geodirectory' ) ) . '</span>';
		$html .= '<ul>';

	}

	return apply_filters( 'geodir_search_filter_field_html_output_fieldset', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_fieldset', 'geodir_search_filter_field_output_fieldset', 10, 3 );

/**
 * Get the html output for the custom search field: checkbox.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_checkbox( $html, $field_info, $post_type ) {

	$search_val = geodir_search_get_field_search_param( $field_info->site_htmlvar_name );

	$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
	$field_label = stripslashes( __( $field_label, 'geodirectory' ) ); // via db translation.

	$checked = '';
	if ( $search_val == '1' ) {
		$checked = 'checked="checked"';
	}

	global $as_fieldset_start;

	if ( $as_fieldset_start > 0 ) {
		$field_label_text = __( $field_label, 'geodiradvancesearch' );
	} else {
		$field_label_text = __( 'Yes', 'geodiradvancesearch' );
	}

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= '<li><input ' . $checked . ' type="' . $field_info->field_site_type . '" class="cat_input" name="s' . $field_info->site_htmlvar_name . '"  value="1" /> ' . $field_label_text . '</li>';
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_checkbox', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_checkbox', 'geodir_search_filter_field_output_checkbox', 10, 3 );


/**
 * Get the html output for the custom search field: taxonomy.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_taxonomy( $html, $field_info, $post_type ) {

	if ( $field_info->field_input_type == 'SELECT' ) {
		$args = array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true );
	} else {
		$args = array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true );
	}

	/**
	 * Filters the `$args` get_terms function.
	 *
	 * @since 1.4.0
	 *
	 * @param array $args        Args array for get_terms function.
	 * @param string $field_info ->site_htmlvar_name Taxonomy name for get_terms function.
	 *
	 * @return array Modified $args array
	 */
	$args = apply_filters( 'geodir_filter_term_args', $args, $field_info->site_htmlvar_name );
	/**
	 * Filters the array returned by get_terms function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_info ->site_htmlvar_name Taxonomy name for get_terms function.
	 *
	 * @return array|int|WP_Error List of WP_Term instances and their children.
	 */
	$terms = apply_filters( 'geodir_filter_terms', get_terms( $field_info->site_htmlvar_name, $args ) );

	// let's order the child categories below the parent.
	$terms_temp = array();

	foreach ( $terms as $term ) {
		if ( $term->parent == '0' ) {
			$terms_temp[] = $term;

			foreach ( $terms as $temps ) {
				if ( $temps->parent != '0' && $temps->parent == $term->term_id ) {
					$temps->name  = '- ' . $temps->name;
					$terms_temp[] = $temps;
				}
			}
		}
	}

	$terms = $terms_temp;

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_taxonomy', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_taxonomy', 'geodir_search_filter_field_output_taxonomy', 10, 3 );

/**
 * Get the html output for the custom search field: datepicker.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_datepicker( $html, $field_info, $post_type ) {
	global $wpdb, $as_fieldset_start;
	
	$geodir_list_date_type = 'Y-m-d';
	
	if ($field_info->site_htmlvar_name == 'event' && function_exists('geodir_event_date_format')) {
		$geodir_list_date_type = geodir_event_date_format();
	} else {
		$datepicker_formate = $wpdb->get_var( "SELECT `extra_fields`  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE `htmlvar_name` = '" . $field_info->site_htmlvar_name . "' AND `post_type` = '" . $post_type . "'" );
		$datepicker_formate_arr = unserialize( $datepicker_formate );
		if ( !empty($datepicker_formate_arr['date_format']) ) {
			$geodir_list_date_type = $datepicker_formate_arr['date_format'];
		} else {
			$geodir_list_date_type  = 'yy-mm-dd';
		}
	}
	
	if (empty($geodir_list_date_type)) {
		$geodir_list_date_type = 'Y-m-d';
	}
	
	// Convert to jQuery UI datepicker format.
	$geodir_list_date_type  = geodir_date_format_php_to_jqueryui( $geodir_list_date_type  );
	
	$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
	if ( $as_fieldset_start > 0 ) {
		$field_label_text = stripslashes( __( $field_label, 'geodirectory' ) );
	} else {
		$field_label_text = '';
	}
	
	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	ob_start();
	?>
	<li>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function() {
			jQuery('.geodir_advance_search_widget form').each(function(index, obj){
				var $form = jQuery(this);
				jQuery(".s<?php echo $field_info->site_htmlvar_name;?>", $form).datepicker({
					changeMonth: true,
					changeYear: true,
					dateFormat: '<?php echo $geodir_list_date_type;?>'
				});
				
				jQuery(".smin<?php echo $field_info->site_htmlvar_name;?>", $form).datepicker({
					changeMonth: true,
					changeYear: true,
					dateFormat: '<?php echo $geodir_list_date_type;?>',
					onClose: function (selectedDate) {
						jQuery(".smax<?php echo $field_info->site_htmlvar_name;?>", $form).datepicker("option", "minDate", selectedDate);
					}
				});
				
				jQuery(".smax<?php echo $field_info->site_htmlvar_name;?>", $form).datepicker({
					changeMonth: true,
					changeYear: true,
					dateFormat: '<?php echo $geodir_list_date_type;?>'
				});
			});
		});
	</script>
	<?php
	if ( $field_info->search_condition == 'SINGLE' && $field_info->site_htmlvar_name != 'event' ) {
		$custom_value = isset( $_REQUEST[ 's' . $field_info->site_htmlvar_name ] ) ? stripslashes_deep( esc_attr( $_REQUEST[ 's' . $field_info->site_htmlvar_name ] ) ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?> 
			<input type="text" class="cat_input <?php echo $field_info->site_htmlvar_name; ?> s<?php echo $field_info->site_htmlvar_name; ?>" placeholder='<?php echo esc_attr( __( 'Select date', 'geodiradvancesearch' ) ); ?>' name="s<?php echo $field_info->site_htmlvar_name; ?>" value="<?php echo esc_attr( $custom_value ); ?>" />
		</div><?php
	} elseif ( $field_info->search_condition == 'SINGLE' && $field_info->site_htmlvar_name == 'event' ) {
		$smincustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?> 
			<input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>" placeholder='<?php echo esc_attr( __( 'Select date', 'geodiradvancesearch' ) ); ?>' class='cat_input s<?php echo $field_info->site_htmlvar_name; ?>' name="<?php echo $field_info->site_htmlvar_name; ?>_start" field_type="text" />
		</div>
		<?php
	} elseif ( $field_info->search_condition == 'FROM' && $field_info->site_htmlvar_name != 'event' ) {
		$smincustom_value = isset($_REQUEST[ 'smin' . $field_info->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smin' . $field_info->site_htmlvar_name ] ) : '';
		$smaxcustom_value = isset($_REQUEST[ 'smax' . $field_info->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smax' . $field_info->site_htmlvar_name ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?> 
			<input type='text' class='cat_input smin<?php echo $field_info->site_htmlvar_name; ?>' placeholder='<?php echo esc_attr( __( 'Start search date', 'geodiradvancesearch' ) ); ?>' name='smin<?php echo $field_info->site_htmlvar_name; ?>' value='<?php echo $smincustom_value; ?>'> 
			<input type='text' class='cat_input smax<?php echo $field_info->site_htmlvar_name; ?>' placeholder='<?php echo esc_attr( __( 'End search date', 'geodiradvancesearch' ) ); ?>' name='smax<?php echo $field_info->site_htmlvar_name; ?>' value='<?php echo $smaxcustom_value; ?>'>
		</div><?php
	}  elseif ( $field_info->search_condition == 'FROM' && $field_info->site_htmlvar_name == 'event' ) {
		$smincustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) : '';
		$smaxcustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_end' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_end' ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?> 
			<input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>" placeholder='<?php echo esc_attr( __( 'Start search date', 'geodiradvancesearch' ) ); ?>' class='cat_input smin<?php echo $field_info->site_htmlvar_name; ?>' name="<?php echo $field_info->site_htmlvar_name; ?>_start" field_type="text"/> 
			<input type="text" value="<?php echo esc_attr( $smaxcustom_value ); ?>" placeholder='<?php echo esc_attr( __( 'End search date', 'geodiradvancesearch' ) ); ?>' class='cat_input smax<?php echo $field_info->site_htmlvar_name; ?>' name="<?php echo $field_info->site_htmlvar_name; ?>_end" field_type="text"/>
		</div>
	<?php } ?>
	</li>
	<?php
	$html .= ob_get_clean();
	
	$html .= geodir_advance_search_field_wrapper_end( $field_info );
	
	return apply_filters( 'geodir_search_filter_field_html_output_datepicker', $html, $field_info, $post_type );
}
add_filter( 'geodir_search_filter_field_output_datepicker', 'geodir_search_filter_field_output_datepicker', 10, 3 );

/**
 * Get the html output for the custom search field: time.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_time( $html, $field_info, $post_type ) {
	global $as_fieldset_start;
	
	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	ob_start();
	?>
	<script type="text/javascript" language="javascript">
		jQuery(document).ready(function () {
			jQuery('.geodir_advance_search_widget form').each(function(index, obj){
				var $form = jQuery(this);
				jQuery(".s<?php echo $field_info->site_htmlvar_name;?>", $form).timepicker({
					showPeriod: true,
					showLeadingZero: true,
					showPeriod: true
				});

				jQuery(".smin<?php echo $field_info->site_htmlvar_name;?>", $form).timepicker({
					showPeriod: true,
					showLeadingZero: true,
					showPeriod: true,
					onClose: function (selectedTime) {
						jQuery(".smax<?php echo $field_info->site_htmlvar_name;?>", $form).timepicker("option", "minTime", selectedTime);
					}
				});

				jQuery(".smax<?php echo $field_info->site_htmlvar_name;?>", $form).timepicker({
					showPeriod: true,
					showLeadingZero: true,
					showPeriod: true
				});
			});
		});
	</script>
	<?php
	$html .= ob_get_clean();
	
	$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
	
	if ( $as_fieldset_start > 0 ) {
		$field_label_text = stripslashes( __( $field_label, 'geodirectory' ) );
	} else {
		$field_label_text = '';
	}
	
	ob_start();
	echo "<li>";
	if ( $field_info->search_condition == 'SINGLE' && $field_info->site_htmlvar_name != 'event' ) {
		$custom_value = isset( $_REQUEST[ 's' . $field_info->site_htmlvar_name ] ) ? stripslashes_deep( esc_attr( $_REQUEST[ 's' . $field_info->site_htmlvar_name ] ) ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?>
			<input type="text" class="cat_input <?php echo $field_info->site_htmlvar_name; ?> s<?php echo $field_info->site_htmlvar_name; ?>"
			       placeholder='<?php echo esc_attr( __( 'Select time', 'geodiradvancesearch' ) ); ?>'
			       name="s<?php echo $field_info->site_htmlvar_name; ?>"
			       value="<?php echo esc_attr( $custom_value ); ?>"/>
		</div>
		<?php
	} elseif ( $field_info->search_condition == 'SINGLE' && $field_info->site_htmlvar_name == 'event' ) {
		$smincustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?>
			<input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>"
			       placeholder='<?php echo esc_attr( __( 'Select time', 'geodiradvancesearch' ) ); ?>'
			       class='cat_input s<?php echo $field_info->site_htmlvar_name; ?>'
			       name="<?php echo $field_info->site_htmlvar_name; ?>_start"
			       field_type="text"/>
		</div>
		<?php
	} elseif ( $field_info->search_condition == 'FROM' && $field_info->site_htmlvar_name != 'event' ) {
		$smincustom_value = isset($_REQUEST[ 'smin' . $field_info->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smin' . $field_info->site_htmlvar_name ] ) : '';
		$smaxcustom_value = isset($_REQUEST[ 'smax' . $field_info->site_htmlvar_name ]) ? @esc_attr( $_REQUEST[ 'smax' . $field_info->site_htmlvar_name ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?>
			<input type='text' class='cat_input smin<?php echo $field_info->site_htmlvar_name; ?>'
			       placeholder='<?php echo esc_attr( __( 'Start search time', 'geodiradvancesearch' ) ); ?>'
			       name='smin<?php echo $field_info->site_htmlvar_name; ?>'
			       value='<?php echo $smincustom_value; ?>'>
			<input type='text' class='cat_input smax<?php echo $field_info->site_htmlvar_name; ?>'
			       placeholder='<?php echo esc_attr( __( 'End search time', 'geodiradvancesearch' ) ); ?>'
			       name='smax<?php echo $field_info->site_htmlvar_name; ?>'
			       value='<?php echo $smaxcustom_value; ?>'>
		</div><?php
	} elseif ( $field_info->search_condition == 'FROM' && $field_info->site_htmlvar_name == 'event' ) {
		$smincustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_start' ] ) : '';
		$smaxcustom_value = isset( $_REQUEST[ $field_info->site_htmlvar_name . '_end' ] ) ? esc_attr( $_REQUEST[ $field_info->site_htmlvar_name . '_end' ] ) : '';
		?>
		<div class='from-to'>
			<?php echo $field_label_text; ?>
			<input type="text" value="<?php echo esc_attr( $smincustom_value ); ?>"
			       placeholder='<?php echo esc_attr( __( 'Start search time', 'geodiradvancesearch' ) ); ?>'
			       class='cat_input smin<?php echo $field_info->site_htmlvar_name; ?>'
			       name="<?php echo $field_info->site_htmlvar_name; ?>_start"
			       field_type="text"/>
			<input type="text" value="<?php echo esc_attr( $smaxcustom_value ); ?>"
			       placeholder='<?php echo esc_attr( __( 'End search time', 'geodiradvancesearch' ) ); ?>'
			       class='cat_input smax<?php echo $field_info->site_htmlvar_name; ?>'
			       name="<?php echo $field_info->site_htmlvar_name; ?>_end"
			       field_type="text"/>
		</div>
		<?php
	}
	?>
	</li>
	<?php
	$html .= ob_get_clean();
	$html .= geodir_advance_search_field_wrapper_end( $field_info );
	
	return apply_filters( 'geodir_search_filter_field_html_output_time', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_time', 'geodir_search_filter_field_output_time', 10, 3 );


/**
 * Get the html output for the custom search field: radio
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_radio( $html, $field_info, $post_type ) {

	global $wpdb;
	$select_fields_result = $wpdb->get_row( $wpdb->prepare( "SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array(
		$post_type,
		$field_info->site_htmlvar_name
	) ) );
	if ( in_array( $field_info->field_input_type, array(
		'CHECK',
		'SELECT',
		'LINK',
		'RADIO'
	) ) ) {
		// optgroup
		$terms = geodir_string_values_to_options( $select_fields_result->option_values, true );
	} else {
		$terms = explode( ',', $select_fields_result->option_values );
	}

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_radio', $html, $field_info, $post_type );
}
add_filter( 'geodir_search_filter_field_output_radio', 'geodir_search_filter_field_output_radio', 10, 3 );

/**
 * Get the html output for the custom search field: multiselect
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_multiselect( $html, $field_info, $post_type ) {

	global $wpdb;
	$select_fields_result = $wpdb->get_row( $wpdb->prepare( "SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array(
		$post_type,
		$field_info->site_htmlvar_name
	) ) );
	if ( in_array( $field_info->field_input_type, array(
		'CHECK',
		'SELECT',
		'LINK',
		'RADIO'
	) ) ) {
		// optgroup
		$terms = geodir_string_values_to_options( $select_fields_result->option_values, true );
	} else {
		$terms = explode( ',', $select_fields_result->option_values );
	}

	global $as_fieldset_start;

	$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
	if ( $as_fieldset_start > 0 ) {
		$title = stripslashes( __( $field_label, 'geodirectory' ) );
	} else {
		$title = '';
	}

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type,$title );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_multiselect', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_multiselect', 'geodir_search_filter_field_output_multiselect', 10, 3 );


/**
 * Get the html output for the custom search field: select
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_select( $html, $field_info, $post_type ) {

	global $wpdb;
	$select_fields_result = $wpdb->get_row( $wpdb->prepare( "SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array(
		$post_type,
		$field_info->site_htmlvar_name
	) ) );
	if ( in_array( $field_info->field_input_type, array(
		'CHECK',
		'SELECT',
		'LINK',
		'RADIO'
	) ) ) {
		// optgroup
		$terms = geodir_string_values_to_options( $select_fields_result->option_values, true );
	} else {
		$terms = explode( ',', $select_fields_result->option_values );
	}

	global $as_fieldset_start;

	$field_label = $field_info->front_search_title ? $field_info->front_search_title : $field_info->field_site_name;
	if ( $as_fieldset_start > 0 ) {
		$title = stripslashes( __( $field_label, 'geodirectory' ) );
	} else {
		$title = '';
	}

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type,$title );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_select', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_select', 'geodir_search_filter_field_output_select', 10, 3 );


/**
 * Get the html output for the custom search field: text.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_text( $html, $field_info, $post_type ) {

	if ( $field_info->site_htmlvar_name == 'dist' ) {
		return '';
	}

	$terms = array( 1 );

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_text', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_text', 'geodir_search_filter_field_output_text', 10, 3 );

/**
 * Get the html output for the custom search field: textarea.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.4
 * @return string The html to output.
 */
function geodir_search_filter_field_output_textarea( $html, $field_info, $post_type ) {

	if ( $field_info->site_htmlvar_name == 'dist' ) {
		return '';
	}

	$terms = array( 1 );

	$html .= geodir_advance_search_field_wrapper_start( $field_info );
	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_textarea', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_textarea', 'geodir_search_filter_field_output_textarea', 10, 3 );


/**
 * Get the html output for the custom search field: distance.
 *
 * @param string $html       The html to be filtered.
 * @param object $field_info The field object info.
 * @param string $post_type  The post type being called.
 *
 * @since 1.4.2
 * @return string The html to output.
 */
function geodir_search_filter_field_output_distance( $html, $field_info, $post_type ) {



	$terms = array( 1 );

//	if ( ! empty( $field_info->field_desc ) ) {
//		echo "<ul><li>{$field_info->field_desc}</li></ul>";
//	}

	$html .= geodir_advance_search_field_wrapper_start( $field_info );

	ob_start();
	if ( $field_info->search_condition == "RADIO" ) {

		if ( $field_info->site_htmlvar_name == 'dist' && $field_info->extra_fields != '' ) {

			$extra_fields = unserialize( $field_info->extra_fields );

			$sort_options = '';

			if ( $extra_fields['is_sort'] == '1' ) {

				if ( $extra_fields['asc'] == '1' ) {

					$name     = ( ! empty( $extra_fields['asc_title'] ) ) ? $extra_fields['asc_title'] : 'Nearest';
					$selected = '';
					if ( isset( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] == 'nearest' ) {
						$selected = 'selected="selected"';
					}

					$sort_options .= '<option ' . $selected . ' value="nearest">' . $name . '</option>';
				}

				if ( $extra_fields['desc'] == '1' ) {
					$name     = ( ! empty( $extra_fields['desc_title'] ) ) ? $extra_fields['desc_title'] : 'Farthest';
					$selected = '';
					if ( isset( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] == 'farthest' ) {
						$selected = 'selected="selected"';
					}

					$sort_options .= '<option ' . $selected . ' value="farthest">' . $name . '</option>';
				}

			}

			if ( $sort_options != '' ) {
				echo '<ul><li><select id="" class="cat_select" name="sort_by">';
				echo '<option value="">' . __( 'Select Option', 'geodiradvancesearch' ) . '</option>';
				echo $sort_options;
				echo '</select></li></ul>';
			}
		}
	}
	$html .= ob_get_clean();

	$html .= geodir_advance_search_options_output( $terms, $field_info, $post_type );
	$html .= geodir_advance_search_field_wrapper_end( $field_info );

	return apply_filters( 'geodir_search_filter_field_html_output_distance', $html, $field_info, $post_type );
}

add_filter( 'geodir_search_filter_field_output_var_dist', 'geodir_search_filter_field_output_distance', 10, 3 );


