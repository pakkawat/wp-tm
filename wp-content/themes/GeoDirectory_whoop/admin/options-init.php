<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('geodirectory_framework_Redux_Framework_config')) {

    class geodirectory_framework_Redux_Framework_config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        public function initSettings() {

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            // If Redux is running as a plugin, this will remove the demo notice and links
            add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
            
            // Function to test the compiler hook and demo CSS output.
            // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
            add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2);
            
            // Change the arguments after they've been declared, but before the panel is created
            //add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
            
            // Change the default value of a field after it's been set, but before it's been useds
            //add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
            
            // Dynamically add a section. Can be also used to modify sections/fields
            //add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css) {
            //echo '<h1>The compiler hook has run!';
            //print_r($options); //Option values
			//echo '####';
          //  print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )
			
$gdf = $options;		
$styles = array();
if(!empty($gdf) && $gdf['home_top_widget']=='0'){$styles[] ="#geodir_wrapper .geodir_full_page{max-width:".$gdf['home_site_width']['width'].";}";}

if(!empty($gdf['body-background-gradient']) && !empty($gdf['body-background-gradient']['from']) && !empty($gdf['body-background-gradient']['to'])){$styles[] ="body{background: -webkit-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to'].");background: linear-gradient(".$gdf['body-background-gradient']['from'].", ".$gdf['body-background-gradient']['to'].");}";}

if(!empty($gdf['head-background-gradient']) && !empty($gdf['head-background-gradient']['from']) && !empty($gdf['head-background-gradient']['to'])){$styles[] =".header{background: -webkit-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to'].");background: linear-gradient(".$gdf['head-background-gradient']['from'].", ".$gdf['head-background-gradient']['to'].");}";}

if(!empty($gdf['footer-background-gradient']) && !empty($gdf['footer-background-gradient']['from']) && !empty($gdf['footer-background-gradient']['to'])){$styles[] =".footer{background: -webkit-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to'].");background: linear-gradient(".$gdf['footer-background-gradient']['from'].", ".$gdf['footer-background-gradient']['to'].");}";}

if(!empty($gdf['head-menu-background-gradient']) && !empty($gdf['head-menu-background-gradient']['from']) && !empty($gdf['head-menu-background-gradient']['to'])){$styles[] ="header nav{background: -webkit-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to']."); background: -o-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to']."); background: -moz-linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to'].");background: linear-gradient(".$gdf['head-menu-background-gradient']['from'].", ".$gdf['head-menu-background-gradient']['to'].");}";}

if(!empty($gdf['head-menu-radius']) && $gdf['head-menu-radius']!='2px'){$styles[] ="header nav{-webkit-border-top-left-radius: ".$gdf['head-menu-radius'].";-webkit-border-top-right-radius: ".$gdf['head-menu-radius'].";-moz-border-radius-topleft: ".$gdf['head-menu-radius'].";-moz-border-radius-topright: ".$gdf['head-menu-radius'].";border-top-left-radius: ".$gdf['head-menu-radius'].";border-top-right-radius: ".$gdf['head-menu-radius'].";}";}

if(!empty($gdf) && (!empty($gdf['head-menu-background']) || !empty($gdf['head-menu-background-gradient']['from']))){$styles[] = "header nav ul li:first-child a{padding-left:0.75em;}";}

if(!empty($gdf) && !empty($gdf['home_site_width']['width']) && $gdf['home_site_width']['width']!='1040px'){$styles[] = "@media only screen and (min-width: 1040px){ .wrap, .geodir-common,.geodir-breadcrumb, #geodir_wrapper .geodir_full_page .geodir-search,#geodir_wrapper h1, #geodir_wrapper .term_description{width:".$gdf['home_site_width']['width'].";}}";}

$css = implode(" ", $styles).' '.$css;
            
			// Demo of how to use the dynamic CSS and write your own static CSS file
            $blog_id = '';
            $blog_id = get_current_blog_id();
            if($blog_id=='1'){$blog_id='';}//dont change for main blog.
            $filename = dirname(__FILE__) . '/style'. $blog_id . '.css';
			global $wp_filesystem;
			if( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}
			
			if ( $wp_filesystem ) {
				if ( $wp_filesystem->put_contents( $filename, $css, FS_CHMOD_FILE ) ) {  // predefined mode settings for WP files
				} else { // if wordpress write to file fails used PHP fall back
					/*
					$myfile = fopen($filename, "w") or die("Unable to open file!");
					fwrite($myfile, $css);
					fclose($myfile);
					*/
				}
			}
             
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => __('Section via hook', GEODIRECTORY_FRAMEWORK),
                'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', GEODIRECTORY_FRAMEWORK),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        /**

          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }

        /**

          Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {

            /**
              Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
             * */
            // Background Patterns Reader
            $sample_patterns_path   = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url    = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns        = array();

            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();

                    while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {

                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode('.', $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[]  = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;

            ob_start();

            $ct             = wp_get_theme();
            $this->theme    = $ct;
            $item_name      = $this->theme->get('Name');
            $tags           = $this->theme->Tags;
            $screenshot     = $this->theme->get_screenshot();
            $class          = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(__('Customize &#8220;%s&#8221;', GEODIRECTORY_FRAMEWORK), $this->theme->display('Name'));
            
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
                <?php endif; ?>

                <h4><?php echo $this->theme->display('Name'); ?></h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(__('By %s', GEODIRECTORY_FRAMEWORK), $this->theme->display('Author')); ?></li>
                        <li><?php printf(__('Version %s', GEODIRECTORY_FRAMEWORK), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . __('Tags', GEODIRECTORY_FRAMEWORK) . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
            <?php
            if ($this->theme->parent()) {
                printf(' <p class="howto">' . __('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.') . '</p>', 'http://codex.wordpress.org/Child_Themes', $this->theme->parent()->display('Name'));
            }
            ?>

                </div>
            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            $sampleHTML = '';
            if (file_exists(dirname(__FILE__) . '/info-html.html')) {
                /** @global WP_Filesystem_Direct $wp_filesystem  */
                global $wp_filesystem;
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                $sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__) . '/info-html.html');
            }



            if(get_option('page_on_front')==get_option('geodir_home_page')){
                $h_class = 'hide';
            }else{$h_class = '';}
            $home_set_err =	array(
                'id'    => 'err-home_set',
                'type'  => 'info',
                'required'  => array('msg-disable-switch', '=', '1'),
                'style' => 'critical',
                'class' => $h_class,
                'icon'  => 'el-icon-info-sign',
                'title' => __('Homepage not set as GeoDirectory (settings on this page may not work)', GEODIRECTORY_FRAMEWORK),
                'desc'  => sprintf( __( 'Please set it at Setings>Reading>Front page>GD Home Page, <a href="%s">here</a>', GEODIRECTORY_FRAMEWORK ), admin_url( 'options-reading.php' ) )
            );
			
			$theme_info =  array(
                        'id'    => 'msg-content-widths',
                        'type'  => 'info',
						'required'  => array('msg-disable-switch', '=', '1'),
                        'style' => 'warning',
                        'title' => __('Optimal content settings for GeoDirectory.', GEODIRECTORY_FRAMEWORK),
                        'desc'  => __('Please set Content to 67% and Sidebars to 30%', GEODIRECTORY_FRAMEWORK)
             );

            // ACTUAL DECLARATION OF SECTIONS
            $this->sections[] = array(
                'title'     => __('Home Settings', GEODIRECTORY_FRAMEWORK),
                'desc'      => __('GeoDirectory Framework was designed to be used with the GeoDirectory plugin, though it can be used without it.  You can enable and disable widget areas and also style almost any area of your website from here.', GEODIRECTORY_FRAMEWORK),
                'icon'      => 'el-icon-home',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => array(
						
					array(
                        'id'        => 'msg-disable-switch',
                        'type'      => 'switch',
                        'title'     => __('Warning and Info messages', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('You can disable warning and info messages.', GEODIRECTORY_FRAMEWORK),
                        'default'   => 1,
                        'on'        => 'Enabled',
                        'off'       => 'Disabled',
                    ),
                   
				$home_set_err,// display error if home page is not set to GeoDirectory
				$theme_info,
				
                 
					
					array(
                        'id'                => 'home_site_width',
                        'type'              => 'dimensions',
                        'units'             => array('%','px','em'),    // You can specify a unit value. Possible: px, em, %
                        'units_extended'    => 'true',  // Allow users to select any type of unit
                        'title'             => __('Site Width', 'redux_demo'),
                        'subtitle'          => __('Select the maximum layout width', 'redux_demo'),
                        'desc'              => __('You can set this to 98% for a nice wide layout (default is 1040px)', 'redux_demo'),
						'height'    => false,
						'compiler'  => 'true',
                        'default'           => array(
                            'width'     => '1040px', 
                           // 'height'    => 100,
                        )
                    ),
					
					array(
                        'id'        => 'home_top_widget',
                        'type'      => 'switch',
                        'title'     => __('GD Home Top Section - Width', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('You can set this to be full-width or standard, (you can also set the map widget to be 100% from it\'s widget)', GEODIRECTORY_FRAMEWORK),
                        'default'   => 1,
                        'on'        => 'Full-width',
                        'off'       => 'Standard',
						'compiler'  => 'true',
                    ),

                ),
            );

            $this->sections[] = array(
                'type' => 'divide',
            );


            $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('Header Styling Options', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
                    
					array(
                        'id'        => 'site_logo',
                        'type'      => 'media',
                        'title'     => __('Site Logo', GEODIRECTORY_FRAMEWORK),
                        'compiler'  => 'true',
                        'mode'      => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc'      => __('You can upload your site logo here', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('This will replace your site title and tag line', GEODIRECTORY_FRAMEWORK),
                        //'hint'      => array(
                            //'title'     => '',
                           // 'content'   => 'This is a <b>hint</b> tool-tip for the webFonts field.<br/><br/>Add any HTML based text you like here.',
                       // )
                    ),
					
					array(
                        'id'        => 'site_favicon',
                        'type'      => 'media',
                        'title'     => __('Site Favicon', GEODIRECTORY_FRAMEWORK),
                        'compiler'  => 'true',
                        'mode'      => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc'      => __('You can upload your site favicon.ico here', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('must be a .ico', GEODIRECTORY_FRAMEWORK),
						'preview'	=> false,
						'url'		=> true,
						'default'       => array(
								'url' =>  get_template_directory_uri().'/favicon.ico'				 
						)
                        //'hint'      => array(
                            //'title'     => '',
                           // 'content'   => 'This is a <b>hint</b> tool-tip for the webFonts field.<br/><br/>Add any HTML based text you like here.',
                       // )
                    ),
					
					array(
                        'id'        => 'site_apple_touch_icon',
                        'type'      => 'media',
                        'title'     => __('Apple device Favicon', GEODIRECTORY_FRAMEWORK),
                        'compiler'  => 'true',
                        'mode'      => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc'      => __('You can upload your site apple-touch-icon here', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('must be 129px x 129px .png', GEODIRECTORY_FRAMEWORK),
						//'preview'	=> false,
						'url'		=> true,
						'default'       => array(
								'url' =>  get_template_directory_uri().'/library/images/apple-icon-touch.png',
						)
                        //'hint'      => array(
                            //'title'     => '',
                           // 'content'   => 'This is a <b>hint</b> tool-tip for the webFonts field.<br/><br/>Add any HTML based text you like here.',
                       // )
                    ),
					array(
                        'id'            => 'head-site-logo-margins',
                        'type'          => 'spacing',
                        'compiler'        => array('.site-logo'), // An array of CSS selectors to apply this font style to
                        'mode'          => 'margin',    // absolute, padding, margin, defaults to padding
                        //'all'           => true,        // Have one field that applies to all
                        'top'           => true,     // Disable the top
                        'right'         => true,     // Disable the right
                        'bottom'        => true,     // Disable the bottom
                        'left'          => true,     // Disable the left
                        'units'         => 'px',      // You can specify a unit value. Possible: px, em, %
                       // 'units_extended'=> 'true',    // Allow users to select any type of unit
                        //'display_units' => true,   // Set to false to hide the units if the units are specified
                        'title'         => __('Site Logo Margins', 'redux_demo'),
                        'subtitle'      => __('Position your logo', 'redux_demo'),
                        'desc'          => __('You can position your logo exactly how you want it.(px)', 'redux_demo'),
                        'default'       => array(
                            'margin-top'    => '0', 
                            'margin-right'  => '0', 
                            'margin-bottom' => '0', 
                            'margin-left'   => '0'
                        )
                    ),
                    array(
                        'id'        => 'header-gdf-fixed',
                        'type'      => 'button_set',
                        'title'     => __('Fixed position Header?', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('No validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This set the position of the header as fixed.', GEODIRECTORY_FRAMEWORK),

                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default'   => '1'
                    ),
					array(
                        'id'        => 'head-title-color',
                        'type'      => 'typography',
                        'title'     => __('Site Title Text', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('h1.site-title a','h1.site-title a:hover'),
                        'subtitle'  => __('Specify the title font properties. Only applies to text (if no logo)', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
						//'compiler'  => array('h1.site-title a','h1.site-title a:hover'),
                        'default'   => array(
                            'color'         => '#FFFFFF',
                            'font-size'     => '40px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '32px'
                        )
					),
						array(
                        'id'        => 'head-tag-color',
                        'type'      => 'typography',
                        'title'     => __('Tagline Text Color', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('h2.site-description'),
                        'subtitle'  => __('Specify the tagline font properties. Only applies to text (if no logo)', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#FFFFFF',
                            'font-size'     => '28px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '22px'
                        )
					),
					
                    array(
                        'id'        => 'head-background',
                        'type'      => 'background',
						'compiler'    => array('.header'),
						//'mode' => 'background-color',
                        'title'     => __('Header Background', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Pick a background color for the theme header(default: #af0606).', GEODIRECTORY_FRAMEWORK),
						'default'  => array(
       					 'background-color' => '#af0606'
						 )
    
                        //'validate'  => 'color',
                    ),
					array(
                        'id'        => 'head-background-gradient',
                        'type'      => 'color_gradient',
						'compiler'    => 'true',
                        'title'     => __('Header Gradient Color Option', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Background for the theme header.(replaces header background)', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This is not supported in IE9 or below.', GEODIRECTORY_FRAMEWORK),
                        'default'   => array(
                            'from'      => '', 
                            'to'        => ''
                        )
                    ),
                    array(
                        'id'        => 'header-button-color',
                        'type'      => 'color',
                        'compiler'    => array('.header-right-area .geodir_submit_search, a.whoop-account-dd-link, a.whoop-signup-btn, #mobile-navigation-left ul li a:hover, a.whoop-login-btn:hover'),
                        'mode' 		=> 'background-color',
                        'title'     => __('Header Button Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the header buttons (default: #e34f43).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#e34f43',
                        'validate'  => 'color',
                    ),
                    array(
                        'id'        => 'header-button-color-hover',
                        'type'      => 'color',
                        'compiler'    => array('.header-right-area .geodir_submit_search:hover, a.whoop-account-dd-link:hover, a.whoop-signup-btn:hover'),
                        'mode' 		=> 'background-color',
                        'title'     => __('Header Button Hover Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the header buttons on hover (default: #dc2f21).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#dc2f21',
                        'validate'  => 'color',
                    ),
                    array(
                        'id'        => 'header-button-border-color',
                        'type'      => 'color',
                        'compiler'    => array('.header-right-area .geodir_submit_search, a.whoop-account-dd-link'),
                        'mode' => 'border-color',
                        'title'     => __('Header Button Border Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a Header Button border color (default: #B64425).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#B64425',
                        'validate'  => 'color',
                    ),
		array(
				'id'        => 'section-menu-start',
				'type'      => 'section',
				'title'     => __('Menu Options', GEODIRECTORY_FRAMEWORK),
				//'subtitle'  => __('With the "section" field you can create indent option sections.', GEODIRECTORY_FRAMEWORK),
				'indent'    => false // Indent all options below until the next 'section' option is set.
			),		array(
                        'id'        => 'head-menu-font',
                        'type'      => 'typography',
                        'title'     => __('Menu Font', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('header nav .nav li a,header nav .nav li a:hover'),
                        'subtitle'  => __('Specify the menu font properties.', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#f5f5f1',
                            'font-size'     => '13px',
                            'font-family'   => '',
                            'font-weight'   => 'bold',
							'line-height'	=> '24px'
                        ),
                    ),
					array(
                        'id'        => 'head-menu-background',
                        'type'      => 'color',
						'compiler'    => array('header nav'),
						'mode' => 'background-color',
                        'title'     => __('Menu Background Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the menu.', GEODIRECTORY_FRAMEWORK),
                        'default'   => '',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'head-menu-background-gradient',
                        'type'      => 'color_gradient',
						'compiler'    => 'true',
                        'title'     => __('Menu Gradient Color Option', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Background for the menu.', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This is not supported in IE9 or below.', GEODIRECTORY_FRAMEWORK),
                        'default'   => array(
                            'from'      => '', 
                            'to'        => ''
                        )
                    ),
					array(
                        'id'        => 'head-sub-menu-background',
                        'type'      => 'color',
						'compiler'    => array('.top-nav .sub-menu'),
						'mode' => 'background-color',
                        'title'     => __('Sub Menu Background Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a sub menu background color for the menu.', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#ed6d62',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'head-sub-menu-border-color',
                        'type'      => 'color',
						'compiler'    => array('.nav li ul.sub-menu, .nav li ul.children','.nav li ul.sub-menu li a, .nav li ul.children li a, .nav li ul.sub-menu li ul.sub-menu li a'),
						'mode' => 'border-color',
                        'title'     => __('Sub Menu Border Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a sub menu border color for the menu.', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#DA5A34',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'head-menu-border',
                        'type'      => 'border',
                        'title'     => __('Menu Border Option', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('Only color validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'compiler'    => array('header nav'), // An array of CSS selectors to apply this font style to
                        'desc'      => __('Enter the px value for each border, ie: 1', GEODIRECTORY_FRAMEWORK),
						'left'		=> true,
						'right'		=> true,
						'top'		=> true,
						'bottom'	=> true,
						'all'		=> false,
                        'default'   => array(
                            'border-color'  => '', 
                            'border-style'  => 'solid', 
                            'border-top'    => '0', 
                            'border-right'  => '0', 
                            'border-bottom' => '0', 
                            'border-left'   => '0'
                        )
                    ),
					array(
                        'id'        => 'head-menu-border-right',
                        'type'      => 'border',
                        'title'     => __('Menu Button Border Option', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('Only color validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'compiler'    => array('header nav .nav li'), // An array of CSS selectors to apply this font style to
                        'desc'      => __('Enter the px value for the border, ie: 1', GEODIRECTORY_FRAMEWORK),
						'all'		=> false,
						'left'		=> false,
						'right'		=> true,
						'top'		=> false,
						'bottom'	=> false,
						'default'   => array(
                            'border-right'  => '0'
						)
                    ),
					
					array(
                        'id'        => 'head-menu-radius',
						'compiler'  => 'true',
                        'type'      => 'select',
                        'title'     => __('Menu Border Radius', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('top-left and top-right border radius', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This sets the corner radius of the menu', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value pairs for select options
                        'options'   => array(
                            '1px' => '1px', 
                            '2px' => '2px', 
                            '3px' => '3px', 
                            '4px' => '4px', 
                            '5px' => '5px', 
                            '6px' => '6px', 
                            '7px' => '7px', 
                            '8px' => '8px', 
                            '9px' => '9px', 
                            '10px' => '10px'
                        ),
                        'default'   => '2px'
                    ),
					
		
		
		array(
			  'id'        => 'section-menu-end',
			  'type'      => 'section',
			  'indent'    => false // Indent all options below until the next 'section' option is set.
		  ),
		
		array(
				'id'        => 'section-adminbar-start',
				'type'      => 'section',
				'title'     => __('Admin Bar\'s and Mobile options', GEODIRECTORY_FRAMEWORK),
				//'subtitle'  => __('With the "section" field you can create indent option sections.', GEODIRECTORY_FRAMEWORK),
				'indent'    => false // Indent all options below until the next 'section' option is set.
			),
					array(
                        'id'        => 'head-wp-adminbar',
                        'type'      => 'button_set',
                        'title'     => __('WordPress Admin Bar', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Here you can disable the WP admin bar.', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled', 
                            '1' => 'Disabled' 
                        ), 
                        'default'   => '1'
                    ),
					
					array(
                        'id'        => 'head-gdf-adminbar',
                        'type'      => 'button_set',
                        'title'     => __('GDF Admin Bar', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('No validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This will enable the GDF admin bar and widget areas, which are good for search and GDF welcome login widgets.', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ), 
                        'default'   => '1'
                    ),
					
					array(
                        'id'        => 'head-gdf-adminbar-fixed',
                        'type'      => 'button_set',
                        'title'     => __('Fixed position GDF Admin Bar?', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('No validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This set the position of the admin bar as fixed.', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled', 
                            '1' => 'Disabled' 
                        ), 
                        'default'   => '1'
                    ),
					
					array(
                        'id'        => 'head-mobile-login',
                        'type'      => 'button_set',
                        'title'     => __('Mobile User Menu', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('No validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This will show a user menu to be able to login and manage user functions on mobile', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled', 
                            '1' => 'Disabled' 
                        ), 
                        'default'   => '0'
                    ),
		
		array(
			  'id'        => 'section-adminbar-end',
			  'type'      => 'section',
			  'indent'    => false // Indent all options below until the next 'section' option is set.
		  )
			  )
            );		
					
       $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('Body Styling Options', GEODIRECTORY_FRAMEWORK),
                'heading'     => __('Background Options', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
					
					array(
                        'id'        => 'body-background',
                        'type'      => 'background',
						'compiler'    => array('body'),
						//'mode' => 'background-color',
                        'title'     => __('Body Background', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background for the theme body.', GEODIRECTORY_FRAMEWORK),
						'default'  => array(
       					 'background-color' => '#eaedf2'
						 )
    
                        //'validate'  => 'color',
                    ),
					array(
                        'id'        => 'body-background-gradient',
						'compiler'    => 'true',
                        'type'      => 'color_gradient',
                        'title'     => __('Body Gradient Color Option', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Background for the theme body.', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This is not supported in IE9 or below.', GEODIRECTORY_FRAMEWORK),
                        'default'   => array(
                            'from'      => '', 
                            'to'        => ''
                        )
                    ),
					array(
                        'id'        => 'body-content-background',
                        'type'      => 'color',
						'compiler'    => array('.geodir-onethird','#geodir_content'),
						'mode' => 'background-color',
                        'title'     => __('Content Background Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the content (default: #FFFFFF).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#FFFFFF',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'body-sidebar-background',
                        'type'      => 'color',
						'compiler'    => array('.geodir-content-right','.geodir-content-left','.gd-third-left','.gd-third-right','#gd-sidebar-wrapper'),
						'mode' => 'background-color',
                        'title'     => __('Sidebar Background Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the sidebars (default: #FFFFFF).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#FFFFFF',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'body-button-color',
                        'type'      => 'color',
						'compiler'    => array('.geodir_button, .geodir_submit_search, .blue-btn, .comment-reply-link, #submit, button, input[type="button"], input[type="submit"],#simplemodal-container .button,#geodir_wrapper #showFilters'),
						'mode' 		=> 'background-color',
                        'title'     => __('Button Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the buttons (default: #e34f43).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#e34f43',
                        'validate'  => 'color',
                    ),
						array(
                        'id'        => 'body-button-color-hover',
                        'type'      => 'color',
						'compiler'    => array('.geodir_button:hover, .geodir_submit_search:hover, .blue-btn:hover, .comment-reply-link:hover, #submit:hover, .blue-btn:focus, .comment-reply-link:focus, #submit:focus, button:focus, input[type="button"]:focus, input[type="submit"]:focus,#simplemodal-container .button:focus','.geodir_button:active, .geodir_submit_search:active, .blue-btn:active, .comment-reply-link:active, #submit:active, button:active, input[type="button"]:active, input[type="submit"]:active,#simplemodal-container .button:active','.geodir_button:hover, .geodir_submit_search:hover, .blue-btn:hover, .comment-reply-link:hover, #submit:hover, button:hover, input[type="button"]:hover, input[type="submit"]:hover,#simplemodal-container .button:hover,#geodir_wrapper #showFilters:hover, #geodir_wrapper #showFilters:focus,#geodir_wrapper  #showFilters:active'),
						'mode' 		=> 'background-color',
                        'title'     => __('Button Hover Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background color for the buttons on hover (default: #dc2f21).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#dc2f21',
                        'validate'  => 'color',
                    ),
					
					
			array(
				'id'        => 'section-text-start',
				'type'      => 'section',
				'title'     => __('Text Options', GEODIRECTORY_FRAMEWORK),
				//'subtitle'  => __('With the "section" field you can create indent option sections.', GEODIRECTORY_FRAMEWORK),
				'indent'    => false // Indent all options below until the next 'section' option is set.
			),
					array(
                        'id'        => 'body-font',
                        'type'      => 'typography',
                        'title'     => __('Body Font', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('body','#geodir_wrapper .geodir-common p','.entry-content p','.hreview-aggregate span'),
                        'subtitle'  => __('Specify the body font properties.', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#757575',
                            'font-size'     => '14px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '22px'
                        ),
                    ),
                    array(
                        'id'        => 'body-link-color',
                        'type'      => 'link_color',
                        'title'     => __('Links Color Option', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('a','.geodir_link_span, .geodir-more-contant li a span.geodir_link_span, #geodir_wrapper #geodir-category-list a span.geodir_link_span'),
                       // 'subtitle'  => __('Change the colour of your site links', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Change the colour of your site links', GEODIRECTORY_FRAMEWORK),
                        //'regular'   => false, // Disable Regular Color
                        //'hover'     => false, // Disable Hover Color
                        //'active'    => false, // Disable Active Color
                        'visited'   => true,  // Enable Visited Color
                        'default'   => array(
                            'regular'   => '#f01d4f',
                            'hover'     => '#d10e3c',
                            'active'    => '#d10e3c',
                            'visited'   => '#f01d4f',
                        )
                    ),
					
					array(
                        'id'        => 'body-h1',
                        'type'      => 'typography',
                        'title'     => __('Body h1 Font', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('h1'),
                        'subtitle'  => __('Specify the h1 font properties.', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#8b8b8b',
                            'font-size'     => '25px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '21px'
                        ),
                    ),
					array(
                        'id'        => 'body-h2',
                        'type'      => 'typography',
                        'title'     => __('Body h2 Font', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('h2'),
                        'subtitle'  => __('Specify the h2 font properties.', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#8b8b8b',
                            'font-size'     => '28px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '22px'
                        ),
                    ),
					array(
                        'id'        => 'body-h3',
                        'type'      => 'typography',
                        'title'     => __('Body h3 Font', GEODIRECTORY_FRAMEWORK),
						'compiler'      => array('h3','#simplemodal-container h3'),
                        'subtitle'  => __('Specify the h3 font properties.', GEODIRECTORY_FRAMEWORK),
                        'google'    => true,
                        'default'   => array(
                            'color'         => '#8b8b8b',
                            'font-size'     => '18px',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'font-weight'   => '400',
							'line-height'	=> '24px'
                        ),
                    ),
					
					
					
		  array(
			  'id'        => 'section-text-end',
			  'type'      => 'section',
			  'indent'    => false // Indent all options below until the next 'section' option is set.
		  ),
          
                   
                )
            );


					
       $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('Footer Styling Options', GEODIRECTORY_FRAMEWORK),
               // 'heading'     => __('Background Options', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
					
					array(
                        'id'        => 'footer-background',
                        'type'      => 'background',
						'compiler'    => array('.footer'),
						//'mode' => 'background-color',
                        'title'     => __('Footer Background', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a background for the theme footer.', GEODIRECTORY_FRAMEWORK),
						'default'  => array(
       					 'background-color' => '#323944'
						 )
    
                        //'validate'  => 'color',
                    ),
					array(
                        'id'        => 'footer-background-gradient',
                        'type'      => 'color_gradient',
						'compiler'    => 'true',
                        'title'     => __('Footer Gradient Color Option', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Background for the theme footer.', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('This is not supported in IE9 or below.', GEODIRECTORY_FRAMEWORK),
                        'default'   => array(
                            'from'      => '', 
                            'to'        => ''
                        )
                    ),
					array(
                        'id'        => 'footer-font-color',
                        'type'      => 'color',
						'compiler'    => array('.footer, .footer a, .footer a:focus, .footer a:hover','.footer .nav li a, .footer .nav li a:focus, .footer .nav li a:hover'),
						'mode' 		=> 'color',
                        'title'     => __('Footer text Color', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Select a color for the footer text (default: #FFFFFF).', GEODIRECTORY_FRAMEWORK),
                        'default'   => '#FFFFFF',
                        'validate'  => 'color',
                    ),
					array(
                        'id'        => 'footer-widgets',
                        'type'      => 'image_select',
                        'title'     => __('Enable footer widget areas', GEODIRECTORY_FRAMEWORK),
                       // 'subtitle'  => __('No validation can be done on this field type', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Select how many footer widget areas to show.none, one, two, three, four', GEODIRECTORY_FRAMEWORK),
                        
                        //Must provide key => value(array:title|img) pairs for radio options
                        'options'   => array(
                            '0' => array('alt' => '0 widgets',        'img' => ReduxFramework::$_url . 'assets/img/1col.png'),
                            '1' => array('alt' => '1 widget',   'img' => ReduxFramework::$_url . 'assets/img/1col.png'),
                            '2' => array('alt' => '2 widgets',  'img' => ReduxFramework::$_url . 'assets/img/2-col-portfolio.png'),
                            '3' => array('alt' => '3 widgets', 'img' => ReduxFramework::$_url . 'assets/img/3-col-portfolio.png'),
                            '4' => array('alt' => '4 widgets',   'img' => ReduxFramework::$_url . 'assets/img/4-col-portfolio.png'),
                        ), 
                        'default' => '0'
                    ),
					array(
                        'id'        => 'footer-copyright-text',
                        'type'      => 'textarea',
                        'title'     => __('Copyright text', GEODIRECTORY_FRAMEWORK),
                        'subtitle'  => __('Enter your copyright text', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('You can enter HTML here or plain text', GEODIRECTORY_FRAMEWORK),
                        'validate'  => 'html',
						'default'   => '&copy; '.date('Y').' <a href="https://wpgeodirectory.com/" target="_blank">GeoDirectory.</a>',
                        
                    ),
									 
									 
		  )
        );

            ob_start();
            echo '<h4>' . __( 'Delete Profile Custom Fields', GEODIRECTORY_FRAMEWORK ) . '</h4>';
            echo '<div class="redux-section-desc">';

            $query_args = array( 'page' => 'bp-profile-setup');
            $link = add_query_arg( $query_args, admin_url('/users.php') );

            echo '<p class="description">' . __( 'This option deletes all BuddyPress profile custom fields created by Whoop theme.', GEODIRECTORY_FRAMEWORK ) . '</p>';
            echo '</div>';

            $query_args2 = array( 'xaction' => 'xprofile-fields-delete');
            $link2 = add_query_arg( $query_args2, admin_url() );
            echo '<p><a href="' . $link2 . '" class="button-primary">' . __( 'Delete', GEODIRECTORY_FRAMEWORK ) . '</a></p>';

            $xprofile_delete_html = ob_get_contents();
            ob_end_clean();

            ob_start();
            echo '<div class="hr"/><div class="inner"><span>&nbsp;</span></div></div>';
            echo '<h4>' . __( 'Create Profile Custom Fields', GEODIRECTORY_FRAMEWORK ) . '</h4>';
            echo '<div class="redux-section-desc">';

            $query_args = array( 'page' => 'bp-profile-setup');
            $link = add_query_arg( $query_args, admin_url('/users.php') );

            echo '<p class="description">' . __( 'This option creates all Whoop BuddyPress profile custom fields. ( Only creates if the field not exists )', GEODIRECTORY_FRAMEWORK ) . '</p>';
            echo '</div>';

            $query_args2 = array( 'xaction' => 'xprofile-fields-create');
            $link2 = add_query_arg( $query_args2, admin_url() );
            echo '<p><a href="' . $link2 . '" class="button-primary">' . __( 'ReInstall', GEODIRECTORY_FRAMEWORK ) . '</a></p>';

            $xprofile_reset_html = ob_get_contents();
            ob_end_clean();

            $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('BuddyPress Options', GEODIRECTORY_FRAMEWORK),
                // 'heading'     => __('Background Options', GEODIRECTORY_FRAMEWORK),
                'desc'      => __('Some components can be enabled / disabled from Settings -> BuddyPress -> Components', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
                    array(
                        'id'        => 'whoop-bp-activiy-tab',
                        'type'      => 'button_set',
                        'title'     => __('BuddyPress "Activity" Tab', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Here you can disable the BuddyPress "Activity" Tab.', GEODIRECTORY_FRAMEWORK),

                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default'   => '1'
                    ),
                    array(
                        'id'        => 'whoop-bp-review-tab',
                        'type'      => 'button_set',
                        'title'     => __('BuddyPress "Reviews" Tab', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Here you can disable the BuddyPress "Reviews" Tab.', GEODIRECTORY_FRAMEWORK),

                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default'   => '0'
                    ),
                    array(
                        'id'        => 'whoop-bp-bookmarks-tab',
                        'type'      => 'button_set',
                        'title'     => __('BuddyPress "Bookmarks" Tab', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('Here you can disable the BuddyPress "Bookmarks" Tab.', GEODIRECTORY_FRAMEWORK),

                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default'   => '0'
                    ),
                    array(
                        'id'        => 'section-text-start',
                        'type'      => 'section',
                        'title'     => __('BuddyPress Profile Field Options', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('With the "section" field you can create indent option sections.', GEODIRECTORY_FRAMEWORK),
                        'indent'    => false // Indent all options below until the next 'section' option is set.
                    ),
                    array(
                        'id'       => 'xprofile-fields-delete',
                        'type'     => 'raw',
                        'markdown' => true,
                        'content'  => $xprofile_delete_html
                    ),
                    array(
                        'id'       => 'xprofile-fields-reset',
                        'type'     => 'raw',
                        'markdown' => true,
                        'content'  => $xprofile_reset_html
                    ),
                )
            );

	        $this->sections[] = array(
		        'icon'      => 'el-icon-website',
		        'title'     => __('bbPress Options', GEODIRECTORY_FRAMEWORK),
		        // 'heading'     => __('Background Options', GEODIRECTORY_FRAMEWORK),
		        'desc'      => __('bbPress Options', GEODIRECTORY_FRAMEWORK),
		        'fields'    => array(
			        array(
				        'id'        => 'whoop-bbp-show-reply-header',
				        'type'      => 'button_set',
				        'title'     => __('Enable header on bbPress replies?', GEODIRECTORY_FRAMEWORK),
				        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
				        'desc'      => __('Header contains link like EDIT | CLOSE | MERGE | TRASH | SPAM | REPLY etc.', GEODIRECTORY_FRAMEWORK),

				        //Must provide key => value pairs for radio options
				        'options'   => array(
					        '0' => 'Enabled',
					        '1' => 'Disabled'
				        ),
				        'default'   => '1'
			        ),
		        )
	        );

            $this->sections[] = array(
                'icon'      => 'el-icon-website',
                'title'     => __('Review Settings', GEODIRECTORY_FRAMEWORK),
                // 'heading'     => __('Background Options', GEODIRECTORY_FRAMEWORK),
                'desc'      => __('Review Settings', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
                    array(
                        'id'        => 'whoop-limit-review',
                        'type'      => 'button_set',
                        'title'     => __('Limit 1 Review / User / Post', GEODIRECTORY_FRAMEWORK),
                        //'subtitle'  => __('', GEODIRECTORY_FRAMEWORK),
                        'desc'      => __('When enabled user can write only one review in each post', GEODIRECTORY_FRAMEWORK),

                        //Must provide key => value pairs for radio options
                        'options'   => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default'   => '1'
                    ),
                    array(
                        'id'      => 'whoop-event-review',
                        'type'    => 'button_set',
                        'title'   => __( 'Enable Reviews on Events?', GEODIRECTORY_FRAMEWORK ),
                        'desc'    => __( 'Enable Reviews on Events?. (Applicable only if GD Events addon installed)', GEODIRECTORY_FRAMEWORK ),
                        'options' => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default' => '1'
                    ),
                    array(
                        'id'      => 'whoop-author-reply',
                        'type'    => 'button_set',
                        'title'   => __( 'Enable author comment on reviews?', GEODIRECTORY_FRAMEWORK ),
                        'desc'    => __( 'When enabled, a "Reply" button will be visible under each review for listing author', GEODIRECTORY_FRAMEWORK ),
                        'options' => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default' => '1'
                    ),
                    array(
                        'id'      => 'whoop-user-reply',
                        'type'    => 'button_set',
                        'title'   => __( 'Enable user comment on reviews?', GEODIRECTORY_FRAMEWORK ),
                        'desc'    => __( 'When enabled, "Reply" button will be visible under each review for normal users too.', GEODIRECTORY_FRAMEWORK ),
                        'options' => array(
                            '0' => 'Enabled',
                            '1' => 'Disabled'
                        ),
                        'default' => '1'
                    ),
                ),
            );
            /**
             *  Note here I used a 'heading' in the sections array construct
             *  This allows you to use a different title on your options page
             * instead of reusing the 'title' value.  This can be done on any
             * section - kp
             */
            $this->sections[] = array(
                'icon'      => 'el-icon-scissors',
                'title'     => __('Quick Code', GEODIRECTORY_FRAMEWORK),
                'heading'   => __('Override CSS, Add JS', GEODIRECTORY_FRAMEWORK),
               // 'desc'      => __('<p class="description">This is the Description. Again HTML is allowed2</p>', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
									 
				
                    array(
                        'id'        => 'override-css',
                        'type'      => 'ace_editor',
                        'title'     => __('CSS Code', 'redux-framework-demo'),
                        'subtitle'  => __('Paste your CSS code here.', 'redux-framework-demo'),
                        'mode'      => 'css',
                        'theme'     => 'monokai',
                        'desc'      => 'CSS here can override any of the theme settings CSS',
                        'default'   => "#header{\nmargin: 0 auto;\n}"
                    ),
                    array(
                        'id'        => 'override-js',
                        'type'      => 'ace_editor',
                        'title'     => __('JS Code', 'redux-framework-demo'),
                        'subtitle'  => __('Paste your JS code here.', 'redux-framework-demo'),
                        'mode'      => 'javascript',
                        'theme'     => 'monokai',
                        'desc'      => 'You can add any JS code here, such as google tracking code',
                        'default'   => "jQuery(document).ready(function(){\n\n});"
                    ),
		
                    
                )
            );
			

			


            $theme_info  = '<div class="redux-framework-section-desc">';
            $theme_info .= '<p class="redux-framework-theme-data description theme-uri">' . __('<strong>Theme URL:</strong> ', GEODIRECTORY_FRAMEWORK) . '<a href="' . $this->theme->get('ThemeURI') . '" target="_blank">' . $this->theme->get('ThemeURI') . '</a></p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-author">' . __('<strong>Author:</strong> ', GEODIRECTORY_FRAMEWORK) . $this->theme->get('Author') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-version">' . __('<strong>Version:</strong> ', GEODIRECTORY_FRAMEWORK) . $this->theme->get('Version') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-description">' . $this->theme->get('Description') . '</p>';
            $tabs = $this->theme->get('Tags');
            if (!empty($tabs)) {
                $theme_info .= '<p class="redux-framework-theme-data description theme-tags">' . __('<strong>Tags:</strong> ', GEODIRECTORY_FRAMEWORK) . implode(', ', $tabs) . '</p>';
            }
            $theme_info .= '</div>';
			
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once(ABSPATH . '/wp-admin/includes/file.php');
				WP_Filesystem();
			}			

            if (file_exists(dirname(__FILE__) . '/../README.md')) {
                $this->sections['theme_docs'] = array(
                    'icon'      => 'el-icon-list-alt',
                    'title'     => __('Documentation', GEODIRECTORY_FRAMEWORK),
                    'fields'    => array(
                        array(
                            'id'        => '17',
                            'type'      => 'raw',
                            'markdown'  => true,
                            'content'   => $wp_filesystem->get_contents(dirname(__FILE__) . '/../README.md')
                        ),
                    ),
                );
            }
            
            // You can append a new section at any time.
				
				
            $this->sections[] = array(
                'title'     => __('Import / Export', GEODIRECTORY_FRAMEWORK),
                'desc'      => __('Import and Export your GeoDirectory Framework settings from file, text or URL.', GEODIRECTORY_FRAMEWORK),
                'icon'      => 'el-icon-refresh',
                'fields'    => array(
                    array(
                        'id'            => 'opt-import-export',
                        'type'          => 'import_export',
                        'title'         => 'Import Export',
                        'subtitle'      => 'Save and restore your GeoDirectory Framework options',
                        'full_width'    => false,
                    ),
                ),
            );                     
                    
            $this->sections[] = array(
                'type' => 'divide',
            );

            $this->sections[] = array(
                'icon'      => 'el-icon-info-sign',
                'title'     => __('Theme Information', GEODIRECTORY_FRAMEWORK),
                'desc'      => __('<p class="description">GeoDirectory Framework was designed to be used with the GeoDirectory plugin, though it can be used without it. You can enable and disable widget areas and also style almost any area of your website from here.</p>', GEODIRECTORY_FRAMEWORK),
                'fields'    => array(
                    array(
                        'id'        => 'opt-raw-info',
                        'type'      => 'raw',
                        'content'   => $item_info,
                    )
                ),
            );
			
            if (file_exists(trailingslashit(dirname(__FILE__)) . 'README.html')) {
                $tabs['docs'] = array(
                    'icon'      => 'el-icon-book',
                    'title'     => __('Documentation', GEODIRECTORY_FRAMEWORK),
                    'content'   => nl2br( $wp_filesystem->get_contents( trailingslashit(dirname(__FILE__)) . 'README.html' ) )
                );
            }
        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-1',
                'title'     => __('Theme Information 1', GEODIRECTORY_FRAMEWORK),
                'content'   => __('<p>This is the tab content, HTML is allowed.</p>', GEODIRECTORY_FRAMEWORK)
            );

            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-2',
                'title'     => __('Theme Information 2', GEODIRECTORY_FRAMEWORK),
                'content'   => __('<p>This is the tab content, HTML is allowed.</p>', GEODIRECTORY_FRAMEWORK)
            );

            // Set the help sidebar
            $this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', GEODIRECTORY_FRAMEWORK);
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array (
                'opt_name' => 'gdf',
                'allow_sub_menu' => '1',
                'customizer' => false,
                'default_mark' => '*',
                //'footer_text' => '<p>This text is displayed below the options panel. It isn\\’t required, but more info is always better! The footer_text field accepts all HTML.</p>',
                'hint-icon' => 'el-icon-question-sign',
                'icon_position' => 'right',
                'icon_color' => 'lightgray',
                'icon_size' => 'normal',
                'tip_style_color' => 'light',
                'tip_position_my' => 'top left',
                'tip_position_at' => 'bottom right',
                'tip_show_duration' => '500',
                'tip_show_event' => 
                array (
                  0 => 'mouseover',
                ),
                'tip_hide_duration' => '500',
                'tip_hide_event' => 
                array (
                  0 => 'mouseleave',
                  1 => 'unfocus',
                ),
                'intro_text' => '<p>Whoop is a theme designed to be used with the GeoDirectory plugin.</p>',
                'menu_title' => 'Whoop Options',
                'menu_type' => 'menu',
                'output' => '1',
                'output_tag' => '1',
                'page_icon' => 'icon-themes',
                'page_parent_post_type' => 'your_post_type',
                'page_permissions' => 'manage_options',
                'page_slug' => '_options',
                'page_title' => 'Whoop Options',
                'save_defaults' => '1',
                'show_import_export' => '1',
                'update_notice' => '1',
				'menu_icon' => get_template_directory_uri().'/favicon.ico',
            );

            $theme = wp_get_theme(); // For use with some settings. Not necessary.
            $this->args["display_name"] = $theme->get("Name");
            $this->args["display_version"] = $theme->get("Version");

            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
            /*$this->args['share_icons'][] = array(
                'url'   => 'https://github.com/ReduxFramework/ReduxFramework',
                'title' => 'Visit us on GitHub',
                'icon'  => 'el-icon-github'
                //'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
            );*/
            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/pages/GeoDirectory/287065118123358',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'https://twitter.com/wpGeoDirectory',
                'title' => 'Follow us on Twitter',
                'icon'  => 'el-icon-twitter'
            );
           /* $this->args['share_icons'][] = array(
                'url'   => 'http://www.linkedin.com/company/redux-framework',
                'title' => 'Find us on LinkedIn',
                'icon'  => 'el-icon-linkedin'
            );*/

        }

    }
    
    global $reduxConfig;
    //$reduxConfig = new geodirectory_framework_Redux_Framework_config();
}



function whoop_init(){
    global $reduxConfig;
    $reduxConfig = new geodirectory_framework_Redux_Framework_config();
}

add_action('after_setup_theme','whoop_init',10);

/**
  Custom function for the callback referenced above
 */
if (!function_exists('geodirectory_framework_my_custom_field')):
    function geodirectory_framework_my_custom_field($field, $value) {
        print_r($field);
        echo '<br/>';
        print_r($value);
    }
endif;

/**
  Custom function for the callback validation referenced above
 * */
if (!function_exists('geodirectory_framework_validate_callback_function')):
    function geodirectory_framework_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';

        /*
          do your validation

          if(something) {
            $value = $value;
          } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
