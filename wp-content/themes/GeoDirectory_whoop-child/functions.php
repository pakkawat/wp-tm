<?php
function my_theme_enqueue_styles() {

    $parent_style = 'whoop'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( 'bootstrap',
    get_stylesheet_directory_uri() . '/bootstrap.min.css',
    array( 'whoop' ),
    wp_get_theme()->get('Version')
    );
    wp_enqueue_style( 'bootstrap-theme',
        get_stylesheet_directory_uri() . '/bootstrap-theme.min.css',
        array( 'bootstrap' ),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'bootstrap-theme' ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_action( 'wpcf7_init', 'custom_add_form_tag_buttonLatLong' );

function custom_add_form_tag_buttonLatLong() {
    wpcf7_add_form_tag( 'buttonlatlong', 'custom_buttonLatLong_tag_handler' );
}

function custom_buttonLatLong_tag_handler( $tag ) {
  $scriptSrc = get_stylesheet_directory_uri() . '/js/getLaLong.js';
  wp_enqueue_script( 'myhandle', $scriptSrc , array(), '1.0',  false );
  return '<div style="width: 130px;color:white;"><button id="myLatLong">แนบที่อยู่</button><div id="geoStatus" style="float: right;"></div></div>';
}

function get_all_regions($atts){
  //set default attributes and values
  $values = shortcode_atts( array(
      'records'   	=> '10',
  ), $atts );
  $records = intval($values['records']);
  $region_args = array(
    'what' => 'region',
    'city_val' => '',
    'region_val' => '',
    'country_val' => '',
    'compare_operator' =>'in',
    'country_column_name' => 'country',
    'region_column_name' => 'region',
    'city_column_name' => 'city',
    'location_link_part' => true,
    'order_by' => ' asc ',
    'no_of_records' => $no_of_records,
    'format' => array('type' => 'array')
  );
  $region_loc_array = geodir_get_location_array($region_args);
  $i = 0;
  ?>
  <ul class="locations_list">
  <?php
  foreach($region_loc_array as $region_item) {
    if($i % $records == 0) echo '</ul><ul class="locations_list">';
    ?>
    <li class="region">
      <a href="<?php echo home_url('/places/').$region_item->location_link;?>"><?php echo __( $region_item->region, 'geodirectory' ) ;?></a>
    </li>
    <?php
    $i += 1;
  }
  ?>
  </ul>
  <?php
}

add_shortcode('all_regions', 'get_all_regions');

?>
