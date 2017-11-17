<?php

/*********************
WP_HEAD GOODNESS
The default wordpress head is
a mess. Let's clean it up by
removing all the junk we don't
need.
*********************/
if (!function_exists('geodirf_head_cleanup')) {
function geodirf_head_cleanup() {
	// category feeds
	// remove_action( 'wp_head', 'feed_links_extra', 3 );
	// post and comment feeds
	// remove_action( 'wp_head', 'feed_links', 2 );
	// EditURI link
	remove_action( 'wp_head', 'rsd_link' );
	// windows live writer
	remove_action( 'wp_head', 'wlwmanifest_link' );
	// index link
	remove_action( 'wp_head', 'index_rel_link' );
	// previous link
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head', 'wp_generator' );
	// remove WP version from css
	add_filter( 'style_loader_src', 'geodirf_remove_wp_ver_css_js', 9999 );
	// remove Wp version from scripts
	add_filter( 'script_loader_src', 'geodirf_remove_wp_ver_css_js', 9999 );
	
	global $gdf;
	if(!empty($gdf) && $gdf['head-wp-adminbar']){add_filter( 'show_admin_bar', '__return_false' );}

} /* end GeoDirectory Framework head cleanup */
}

// A better title
// http://www.deluxeblogtips.com/2012/03/better-title-meta-tag.html
if (!function_exists('rw_title')) {
function rw_title( $title, $sep, $seplocation ) {
  global $page, $paged;

  // Don't affect in feeds.
  if ( is_feed() ) return $title;

  // Add the blog's name
  if ( 'right' == $seplocation ) {
    $title .= get_bloginfo( 'name' );
  } else {
    $title = get_bloginfo( 'name' ) . $title;
  }

  // Add the blog description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );

  if ( $site_description && ( is_home() || is_front_page() ) ) {
    $title .= " {$sep} {$site_description}";
  }

  // Add a page number if necessary:
  if ( $paged >= 2 || $page >= 2 ) {
    $title .= " {$sep} " . sprintf( __( 'Page %s', 'dbt' ), max( $paged, $page ) );
  }

  return $title;

} // end better title
}

// remove WP version from RSS
if (!function_exists('geodirf_rss_version')) {
function geodirf_rss_version() { return ''; }
}

// remove WP version from scripts
if (!function_exists('geodirf_remove_wp_ver_css_js')) {
function geodirf_remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, 'ver=' ) )
		//$src = remove_query_arg( 'ver', $src ); // removed so js/css are not cached after updates
		$src = $src;
	return $src;
}
}

// remove injected CSS for recent comments widget
if (!function_exists('geodirf_remove_wp_widget_recent_comments_style')) {
function geodirf_remove_wp_widget_recent_comments_style() {
	if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
		remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
	}
}
}

// remove injected CSS from recent comments widget
if (!function_exists('geodirf_remove_recent_comments_style')) {
function geodirf_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action( 'wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style') );
	}
}
}

// remove injected CSS from gallery
if (!function_exists('geodirf_gallery_style')) {
function geodirf_gallery_style($css) {
	return preg_replace( "!<style type='text/css'>(.*?)</style>!s", '', $css );
}
}


/*********************
SCRIPTS & ENQUEUEING
*********************/
// loading modernizr and jquery, and reply script
if (!function_exists('geodirf_scripts_and_styles_admin')) {
function geodirf_scripts_and_styles_admin() {
wp_register_style( 'geodirectory-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css', array(), GDF_VERSION );
	wp_enqueue_style( 'geodirectory-font-awesome');	
}
}
// loading modernizr and jquery, and reply script
if (!function_exists('geodirf_scripts_and_styles')) {
function geodirf_scripts_and_styles() {

  global $wp_styles,$gdf; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
//print_r($gdf);
	wp_register_style( 'geodirectory-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css', array(), GDF_VERSION );
	wp_enqueue_style( 'geodirectory-font-awesome');
		
  if (!is_admin()) {

		// modernizr (without media query polyfill)
		wp_register_script( 'geodirf-modernizr', get_template_directory_uri() . '/library/js/libs/modernizr.custom.min.js', array(), GDF_VERSION, false );

		// register main stylesheet
		wp_register_style( 'geodirf-stylesheet', get_stylesheet_directory_uri() . '/style.css', array(), GDF_VERSION, 'all' );
		
		// register main custom stylesheet
      $blog_id = '';
      $blog_id = get_current_blog_id();
      if($blog_id=='1'){$blog_id='';}//dont change for main blog.
      $style_file = get_template_directory_uri() . '/admin/style'.$blog_id.'.css';
		wp_register_style( 'geodirf-custom-stylesheet', $style_file, array(), $gdf['REDUX_last_saved'], 'all' );

		// ie-only style sheet
		wp_register_style( 'geodirf-ie-only', get_template_directory_uri() . '/library/css/ie.css', array(), GDF_VERSION );

    // comment reply script for threaded comments
    if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
		  wp_enqueue_script( 'comment-reply' );
    }

		//adding scripts file in the footer
		wp_register_script( 'geodirf-js', get_template_directory_uri() . '/library/js/scripts.js', array( 'jquery' ), GDF_VERSION, true );

		// enqueue styles and scripts
		wp_enqueue_script( 'geodirf-modernizr' );
		wp_enqueue_style( 'geodirf-stylesheet' );
		wp_enqueue_style( 'geodirf-custom-stylesheet' );

		
		wp_enqueue_style( 'geodirf-ie-only' );

		$wp_styles->add_data( 'geodirf-ie-only', 'conditional', 'lt IE 9' ); // add conditional wrapper around ie stylesheet

		/*
		I recommend using a plugin to call jQuery
		using the google cdn. That way it stays cached
		and your site will load faster.
		*/
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'geodirf-js' );
		
		wp_enqueue_script( 'wpgeodirectory-mmenu-js', get_template_directory_uri() . '/library/js/jquery.mmenu.min.js',  array( 'jquery' ), GDF_VERSION );
		wp_enqueue_style( 'wpgeodirectory-mmenu-css', get_template_directory_uri() . '/library/css/jquery.mmenu.css',  NULL, GDF_VERSION );
		wp_enqueue_style( 'wpgeodirectory-mmenu-pos-css', get_template_directory_uri() . '/library/css/jquery.mmenu.positioning.css',  NULL, GDF_VERSION );
		
		/*wp_register_style('redux-elusive-icon',get_template_directory_uri() . '/options/ReduxCore/assets/css/vendor/elusive-icons/elusive-webfont.css',array());
		wp_register_style('redux-elusive-icon-ie7',get_template_directory_uri() . '/options/ReduxCore/assets/css/vendor/elusive-icons/elusive-webfont-ie7.css',array());
		wp_enqueue_style( 'redux-elusive-icon' );
        wp_enqueue_style( 'redux-elusive-icon-ie7' );*/ // Depreciated for awesomefont
		 global $wp_styles;
		$wp_styles->add_data( 'redux-elusive-icon-ie7', 'conditional', 'lte IE 7' );
	}
}
}

/*********************
THEME SUPPORT
*********************/

// Adding WP 3+ Functions & Theme Support
if ( !function_exists( 'geodirf_theme_support' ) ) {
	function geodirf_theme_support() {
		// wp thumbnails (sizes handled in functions.php)
		add_theme_support( 'post-thumbnails' );
	
		// default thumb size
		set_post_thumbnail_size( 125, 125, true );
	
		// rss thingy
		add_theme_support( 'automatic-feed-links' );
	
		// to add header image support go here: http://themble.com/support/adding-header-background-image-support/
	
		// adding post format support
		add_theme_support( 'post-formats',
			array(
				'aside',             // title less blurb
				'gallery',           // gallery of images
				'link',              // quick link to other site
				'image',             // an image
				'quote',             // a quick quote
				'status',            // a Facebook like status update
				'video',             // video
				'audio',             // audio
				'chat'               // chat transcript
			)
		);
	
		// wp menus
		add_theme_support( 'menus' );
	
		// registering wp3+ menus
		register_nav_menus(
			array(
				'main-nav' => __( 'The Main Menu', GEODIRECTORY_FRAMEWORK ),   // main nav in header
				'footer-links' => __( 'Footer Links', GEODIRECTORY_FRAMEWORK ) // secondary nav in footer
			)
		);
	}
	
	// Enables plugins and themes to manage the document title.
	add_theme_support( 'title-tag' );
	
	// Enables Custom_Headers support for a theme
	$args = array(
		'default-image'          => '', // image.
		'random-default'         => false, // Random image rotation off by default.
		'width'                  => 0, // Set width.
		'height'                 => 0, // Set height.
		'max-width'              => 0, // Set a maximum value for the width.
		'flex-height'            => false,
		'flex-width'             => false,
		'default-text-color'     => '', // Text color.
		'header-text'            => true,
		'uploads'                => true,
		'wp-head-callback'       => '', // Callbacks for styling the header.
		'admin-head-callback'    => '', // Callbacks for styling the admin preview.
		'admin-preview-callback' => '', // Callbacks for styling the admin preview.
	);
	add_theme_support( 'custom-header', $args );
	remove_theme_support( 'custom-header' );
	
	// Enables Custom_Backgrounds support for a theme
	$args = array(
		'default-color'          => '',
		'default-image'          => '',
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => ''
	);
	add_theme_support( 'custom-background', $args );
	remove_theme_support( 'custom-background' );
	
	/**
	 * Add callback for custom TinyMCE editor stylesheets. (editor-style.css)
	 * @see http://codex.wordpress.org/Function_Reference/add_editor_style
	 */
	add_editor_style();
	
	/* end GeoDirectory Framework theme support */
}

/*********************
RELATED POSTS FUNCTION
*********************/

// Related Posts Function (call using geodirf_related_posts(); )
if (!function_exists('geodirf_related_posts')) {
function geodirf_related_posts() {
	echo '<ul id="geodirf-related-posts">';
	global $post;
	$tags = wp_get_post_tags( $post->ID );
	if($tags) {
		foreach( $tags as $tag ) {
			$tag_arr .= $tag->slug . ',';
		}
		$args = array(
			'tag' => $tag_arr,
			'numberposts' => 5, /* you can change this to show more */
			'post__not_in' => array($post->ID)
		);
		$related_posts = get_posts( $args );
		if($related_posts) {
			foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>
				<li class="related_post"><a class="entry-unrelated" href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>
			<?php endforeach; }
		else { ?>
			<?php echo '<li class="no_related_post">' . __( 'No Related Posts Yet!', GEODIRECTORY_FRAMEWORK ) . '</li>'; ?>
		<?php }
	}
	wp_reset_postdata();
	echo '</ul>';
} /* end GeoDirectory Framework related posts function */
}

/*********************
PAGE NAVI
*********************/

// Numeric Page Navi (built into the theme by default)
if (!function_exists('geodirf_page_navi')) {
function geodirf_page_navi() {
  global $wp_query;
  $bignum = 999999999;
  if ( $wp_query->max_num_pages <= 1 )
    return;
  echo '<nav class="pagination">';
  echo paginate_links( array(
    'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
    'format'       => '',
    'current'      => max( 1, get_query_var('paged') ),
    'total'        => $wp_query->max_num_pages,
    'prev_text'    => '&larr;',
    'next_text'    => '&rarr;',
    'type'         => 'list',
    'end_size'     => 3,
    'mid_size'     => 3
  ) );
  echo '</nav>';
} /* end page navi */
}

/*********************
RANDOM CLEANUP ITEMS
*********************/

// remove the p from around imgs (http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
if (!function_exists('geodirf_filter_ptags_on_images')) {
function geodirf_filter_ptags_on_images($content){
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}
}

// This removes the annoying […] to a Read More link
if (!function_exists('geodirf_excerpt_more')) {
function geodirf_excerpt_more($more) {
	global $post;
	// edit here if you like
	return '...  <a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="'. __( 'Read ', GEODIRECTORY_FRAMEWORK ) . get_the_title($post->ID).'">'. __( 'Read more &raquo;', GEODIRECTORY_FRAMEWORK ) .'</a>';
}
}



/*********************
EXTENDED CUSTOMIZER STUFF
*********************/
if (!function_exists('geodirf_theme_customizer')) {
function geodirf_theme_customizer( $wp_customize ) {
    
	$wp_customize->add_section( 'geodirf_header_section' , array(
    'title'       => __( 'Header Styling Options', 'geodirf' ),
    'priority'    => 30,
    'description' => 'These are basic settings, advanced settings can be found in the <a href="'.admin_url('/admin.php?page=_options').'">theme options page</a>',
	) );
	
	
	// SITE LOGO
	$wp_customize->add_setting( 'geodirf_site_logo' );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'geodirf_site_logo', array(
    'label'    => __( 'Logo', GEODIRECTORY_FRAMEWORK ),
    'section'  => 'geodirf_header_section',
    'settings' => 'geodirf_site_logo',
	) ) );
	
	// SITE TITLE TEXT COLOR
	$wp_customize->add_setting( 'geodirf_head-title-color' );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 	'geodirf_head-title-color', array(
		'label'      => __( 'Site Title Color', GEODIRECTORY_FRAMEWORK ),
		'section'    => 'geodirf_header_section',
		'settings'   => 'geodirf_head-title-color',
	) ) );
	
	
	
}
}
//add_action('customize_register', 'geodirf_theme_customizer'); // DISABLES FOR NOW 


add_action( 'customize_save_after', 'geodirf_customize_save_after',10);


if (!function_exists('geodirf_customize_save_after')) {
function geodirf_customize_save_after($c='')
{

        $values = json_decode( wp_unslash( $_POST['customized'] ), true );
//print_r($values);exit;

// update site logo
global $reduxConfig,$gdf;
//print_r($gdf);exit;
if($values['geodirf_site_logo']){
$logo = geodirf_get_image_params( $values['geodirf_site_logo'] );
if($logo){$reduxConfig->ReduxFramework->set('site_logo', $logo);}
}

if($values['geodirf_head-title-color']){
$gdf['head-title-color']['color'] = $values['geodirf_head-title-color'];
$reduxConfig->ReduxFramework->set('head-title-color', $gdf['head-title-color']);
}



}
}


if (!function_exists('geodirf_options_save_after')) {
function geodirf_options_save_after($old_value,$value) { 
  //print_r($old_value);
if(isset($value['site_logo']['url']) && !empty($old_value['site_logo']['url']) && $old_value['site_logo']['url']!=$value['site_logo']['url']){
    set_theme_mod( 'geodirf_site_logo', $value['site_logo']['url'] );
} 
} 
}

add_action('update_option_gdf', 'geodirf_options_save_after',10,2);

function geodirf_css_out($css){
if(!empty($css)){echo "<style type=\"text/css\" >".implode(" ", $css)."</style>";}	
}

function geodirf_admin_notice() {
	  $filename = realpath(dirname(__FILE__) . '/..') . '/admin/style' . '.css';
	if(!is_writable($filename)){
    ?>
    <div id="message" class="error">
        <p><strong><?php _e( 'GeoDirectory Framework:', GEODIRECTORY_FRAMEWORK );?></strong> <?php _e( 'File is not writable: ', GEODIRECTORY_FRAMEWORK ); echo $filename;?></p>
    </div>
    <?php
	}
}
add_action( 'admin_notices', 'geodirf_admin_notice' );

//add_action('wp_head','geodirf_css'); /// removed and added to compiler stylesheet

if (!function_exists('geodirf_css')) {
function geodirf_css()
{ global $gdf;
//print_r($gdf);
$styles = array();

if(!empty($gdf) && $gdf['home_top_widget']=='0'){$styles[] ="#geodir_wrapper .geodir_full_page{max-width:1040px;}";}

if(!empty($gdf['body-background-gradient'])){$styles[] ="body{background: -webkit-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to'].");background: linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to'].");}";}

if(!empty($gdf['head-background-gradient'])){$styles[] =".header{background: -webkit-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to'].");background: linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to'].");}";}

if(!empty($gdf['footer-background-gradient'])){$styles[] =".footer{background: -webkit-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to'].");background: linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to'].");}";}

if(!empty($gdf['head-menu-background-gradient'])){$styles[] ="header nav{background: -webkit-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to'].");background: linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to'].");}";}

if(!empty($gdf['head-menu-radius'])){$styles[] ="header nav{-webkit-border-top-left-radius: ".$gdf['head-menu-radius'].";-webkit-border-top-right-radius: ".$gdf['head-menu-radius'].";-moz-border-radius-topleft: ".$gdf['head-menu-radius'].";-moz-border-radius-topright: ".$gdf['head-menu-radius'].";border-top-left-radius: ".$gdf['head-menu-radius'].";border-top-right-radius: ".$gdf['head-menu-radius'].";}";}

if(!empty($gdf) && $gdf['head-menu-background']=='' && empty($gdf['head-menu-background-gradient']['from'])){$styles[] = "header nav ul li:first-child a{padding-left:0px;}";}

if(!empty($gdf) && !empty($gdf['home_site_width']['width']) ){$styles[] = "@media only screen and (min-width: 1040px){ .wrap, .geodir-common,.geodir-breadcrumb, #geodir_wrapper .geodir_full_page .geodir-search,#geodir_wrapper h1, #geodir_wrapper .term_description{width:".$gdf['home_site_width']['width'].";}}";}


#############################################
#### FIX FOR ADVANCED SEARCH START ##########
#############################################
/*
//$adv_out = geodir_advance_search_button();
if(function_exists('geodir_advance_search_button')){ob_start();$adv_out = geodir_advance_search_button();if($adv_out){$adv_search = true;}else{$adv_search = false;}	ob_end_clean();}else{$adv_search = false;}
$post_types = geodir_get_posttypes('object');
if(!empty($post_types) && count((array)$post_types) > 1 && $adv_search){ // both multi post type and advanced filters active
	echo '###1';
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search input[type=\"text\"]{width:27.5%;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search .geodir_submit_search{width:15.5%;font-size:16px;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper #showFilters{height:38px;width:100%;padding-left: 0px;padding-right: 0px;text-align: center;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir-link-left{width:15%;padding-left: 0px;padding-right: 0px;text-align: center;margin-left: 1%;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir-search select{width:10%;margin-left: 0px;margin-right: 1%;}}";

}elseif(empty($post_types) && $adv_search) { // only advanced search active
	echo '###2';
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search input[type=\"text\"]{width:33%;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search .geodir_submit_search{width:15.5%;font-size:16px;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper #showFilters{height:38px;width:100%;padding-left: 0px;padding-right: 0px;text-align: center;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir-link-left{width:15%;padding-left: 0px;padding-right: 0px;text-align: center;margin-left: 1%;}}";

}elseif(!empty($post_types) && count((array)$post_types) > 1  && !$adv_search) { // only multi post type active
	echo '###3';
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search input[type=\"text\"]{width:33%;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir_full_page .geodir-search .geodir_submit_search{width:15.5%;font-size:16px;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper #showFilters{height:38px;width:100%;padding-left: 0px;padding-right: 0px;text-align: center;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir-link-left{width:15%;padding-left: 0px;padding-right: 0px;text-align: center;margin-left: 1%;}}";
  $styles[] = "@media only screen and (min-width: 1040px){ #geodir_wrapper .geodir-search select{width:15.5%;margin-left: 0px;margin-right: 1%;}}";

}*/
#############################################
#### FIX FOR ADVANCED SEARCH END ############
#############################################



if(!empty($styles)){echo "<style type=\"text/css\" >".implode(" ", $styles)."</style>";}



}
}

if (!function_exists('geodirf_css_override')) {
function geodirf_css_override()
{ global $gdf;
if(!empty($gdf) && $gdf['override-css']){echo "<style type=\"text/css\" >".$gdf['override-css']."</style>";}
}
}


add_action('wp_head','geodirf_css_override',1001);

if (!function_exists('geodirf_js_override')) {
function geodirf_js_override()
{ global $gdf;
if(!empty($gdf) && $gdf['override-js']){echo "<script type=\"text/javascript\">".$gdf['override-js']."</script>";}
}
}

add_action('wp_head','geodirf_js_override',1002);








// =============================== Login Widget ======================================
class gdf_welcome_loginwidget extends WP_Widget {
    function __construct() {
        $widget_ops = array('classname' => 'Loginbox', 'description' => __('Welcome Login Widget', GEODIRECTORY_FRAMEWORK) );
        parent::__construct(
            'widget_gdf_welcome_loginwidget', // Base ID
            __('GDF > Welcome Login', GEODIRECTORY_FRAMEWORK), // Name
            $widget_ops// Args
        );
    }
	public function widget($args, $instance) {
	// prints the widget
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		 					
		global $current_user;
		if (function_exists('geodir_getlink')) {
		$login_url = geodir_login_url();
		$logout_url = wp_logout_url( home_url() );
		$add_listurl = get_permalink( get_option('geodir_add_listing_page') );
		$add_listurl = geodir_getlink( $add_listurl, array('listing_type'=>'gd_place') );
		if(get_current_user_id()) { 
			$author_link = get_author_posts_url( $current_user->data->ID );
			$author_link = geodir_getlink($author_link,array('geodir_dashbord'=>'true','stype'=>'gd_place'),false);
			$authorfav_link = geodir_getlink($author_link,array('stype'=>'gd_place','list'=>'favourite'),false);
		}
		}else{
		$login_url = wp_login_url( get_permalink() );
		$logout_url = wp_logout_url( home_url() );
		
			
		}
?><div class="gdf_welcome_login_wrap"	><ul class="gdf_welcome_login"><?php			
if(get_current_user_id()) { $display_name = $current_user->data->display_name; ?>
<li class="welcome"> <span><?php _e('Welcome',GEODIRECTORY_FRAMEWORK);?>, </span>  <a href="<?php echo $author_link;?>" title="<?php echo $display_name;?>">  <?php echo $display_name;?></a></li>
<li class="userin"><a href="<?php echo $logout_url;?>" class="signin"><?php _e('Logout',GEODIRECTORY_FRAMEWORK);?></a></li>
<?php }else{ ?>
<li class="welcome"><span><?php _e('Welcome',GEODIRECTORY_FRAMEWORK);?>, <strong><?php _e('Guest',GEODIRECTORY_FRAMEWORK);?></strong></span> </li>
<li class="userin"><a href="<?php echo $login_url;?>" class="signin"><?php _e('Sign in',GEODIRECTORY_FRAMEWORK);?></a></li>
<?php }?>
</ul>
</div>
        
 	<?php
	}
	public function update($new_instance, $old_instance) {
	//save the widget
		$instance = $old_instance;		
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	public function form($instance) {
	//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );		
		$title = strip_tags($instance['title']);
?>
		<p>No settings for this widget</p>
     
    
<?php
	}}
register_widget('gdf_welcome_loginwidget');



?>
