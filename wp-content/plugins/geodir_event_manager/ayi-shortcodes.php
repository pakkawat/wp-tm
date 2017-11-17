<?php
function geodir_ayi_widget_display_sc($atts) {
    $args = array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    );
    $instance = array();
    $shortcode = true;

    if (is_user_logged_in()) {
        return geodir_ayi_widget_display($args, $instance, $shortcode);
    } else {
        return apply_filters('ayi_gd_login_box_output', do_shortcode('[gd_login_box]'));
    }
}
add_shortcode( 'ayi', 'geodir_ayi_widget_display_sc' );