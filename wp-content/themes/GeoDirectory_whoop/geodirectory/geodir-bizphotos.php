<?php
header("X-XSS-Protection: 0"); // IE requirement
// call header
get_header();

###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'details-page', 'geodir-wrapper','');

###### TOP CONTENT ######
// action called before the main content and the page specific content
do_action('geodir_top_content', 'details-page');
// template specific, this can add the sidebar top section and breadcrums
do_action('geodir_detail_before_main_content');
// action called before the main content
do_action('geodir_before_main_content', 'details-page');

do_action('geodir_biz_photos_main_content');

###### BOTTOM SECTION WIDGET AREA ######
// adds the details bottom section widget area, you can add more classes via ''
do_action( 'geodir_sidebar_detail_bottom_section', '' );

###### WRAPPER CLOSE ######
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'details-page');


get_footer();