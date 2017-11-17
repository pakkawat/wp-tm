<?php
/*
Plugin Name: GD Booster
Plugin URI: http://wpgeodirectory.com/
Description: GD Booster wraps some of the smartest caching, compression and minifying methods available today for WordPress, modded to be 100% GeoDirectory compatible.
Version: 1.2.51
Author: GeoDirectory
Author URI: http://wpgeodirectory.com/
Update URL: https://wpgeodirectory.com
Update ID: 65849
*/

if (!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

// If beaver builder editing then don't load GD Booster
if(isset($_REQUEST['fl_builder'])){return;}

define( 'GEODIR_GD_BOOSTER_VERSION', '1.2.51' );
if ( !defined( 'GEODIR_GD_BOOSTER_TEXTDOMAIN' ) ) {
    define( 'GEODIR_GD_BOOSTER_TEXTDOMAIN', 'geodir-gd-booster' );
}
if ( !defined( 'GD_BOOSTER_CACHE_DIR' ) ) {
    define( 'GD_BOOSTER_CACHE_DIR', str_replace('\\','/',dirname(__FILE__)).'/../../booster_cache' );
}

if (require(dirname(__FILE__) . '/includes/wp-php53.php')) { // TRUE if running PHP v5.3+.
    require_once dirname(__FILE__) . '/geodir-gd-booster.inc.php';

    if ( defined( 'GEODIR_GD_BOOSTER_ENABLE' ) && GEODIR_GD_BOOSTER_ENABLE && gd_booster_is_plugin_active() && !is_admin() ) {

        // we need to disable gzip compression so static caching can work.
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('output_buffering', 'Off');
        @ini_set('output_handler', '');

        /* gd-booster */
        require_once dirname(__FILE__) . '/booster_inc.php';
        remove_action('shutdown', 'wp_ob_end_flush_all', 1); // see https://core.trac.wordpress.org/ticket/22430#comment:4
        add_action('wp_footer', 'gd_booster_wp', 999999);
    }
} else {
    wp_php53_notice('GD Booster');
}

//GEODIRECTORY UPDATE CHECKS
if (is_admin()) {
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        require_once('gd_update.php'); // require update script
    }
}

function gd_booster_htaccess() {
    $wp_htacessfile = get_home_path().'.htaccess';
    $booster_htacessfile = rtrim(str_replace('\\','/',realpath(dirname(__FILE__))),'/').'/htaccess/.htaccess';
    if(file_exists($booster_htacessfile))
    {
        if(file_exists($wp_htacessfile) && is_writable($wp_htacessfile))
        {
            $wp_htacessfile_contents = file_get_contents($wp_htacessfile);
            $wp_htacessfile_contents = preg_replace('/#GEODIR-GD-Booster Start#################################################.*#GEODIR-GD-Booster End#################################################/ims','',$wp_htacessfile_contents);
            $wp_htacessfile_contents = $wp_htacessfile_contents.file_get_contents($booster_htacessfile);
        }
        else $wp_htacessfile_contents = file_get_contents($booster_htacessfile);
        try {
            file_put_contents($wp_htacessfile, $wp_htacessfile_contents);
        } catch(Exception $e) {
            error_log( 'GD Booster Error: ' . $e->getMessage() );
        }
    }
    try {
        mkdir(GD_BOOSTER_CACHE_DIR, 0777);
        chmod(GD_BOOSTER_CACHE_DIR, 0777);
    } catch(Exception $e) {
        error_log( 'GD Booster Error: ' . $e->getMessage() );
    }
}

function gd_booster_cleanup() {
    // Remove entries from .htaccess
    $wp_htacessfile = get_home_path().'.htaccess';
    if (file_exists($wp_htacessfile) && is_writable($wp_htacessfile)) {
        $wp_htacessfile_contents = file_get_contents($wp_htacessfile);
        $wp_htacessfile_contents = preg_replace('/#GEODIR-GD-Booster Start#################################################.*#GEODIR-GD-Booster End#################################################/ims', '', $wp_htacessfile_contents);
        try {
            file_put_contents($wp_htacessfile,$wp_htacessfile_contents);
        } catch(Exception $e) {
            error_log( 'GD Booster Error: ' . $e->getMessage() );
        }
    }

    // Remove all cache files
    $handle = opendir(GD_BOOSTER_CACHE_DIR);
    while(false !== ($file = readdir($handle))) {
        if ($file[0] != '.' && is_file(GD_BOOSTER_CACHE_DIR . '/' . $file)) 
            unlink(GD_BOOSTER_CACHE_DIR . '/' . $file);
    }
    closedir($handle);
}

function gd_booster_wp() {
    // Dump output buffer
    if($out = ob_get_contents())
    {
        // Check for right PHP version
        if(strnatcmp(phpversion(),'5.0.0') >= 0)
        { 
            $booster_cache_dir = GD_BOOSTER_CACHE_DIR;
            $html = $out;
            $js_plain = '';
            $booster_out = '';
            $booster_folder = explode('/',rtrim(str_replace('\\','/',realpath(dirname(__FILE__))),'/'));
            $booster_folder = $booster_folder[count($booster_folder) - 1];
            $booster_folder_url = content_url(). '/plugins/' . $booster_folder . '/';
            $booster = new GDBooster();
            if (!is_dir($booster_cache_dir)) {
                try {
                    mkdir($booster_cache_dir, 0777);
                    chmod($booster_cache_dir, 0777);
                } catch(Exception $e) {
                    error_log( 'GD Booster Error: ' . $e->getMessage() );
                }
            }
            if(is_dir($booster_cache_dir) && is_writable($booster_cache_dir) && substr(decoct(fileperms($booster_cache_dir)),1) == "0777")
            {
                $booster_cache_reldir = $booster->getpath(str_replace('\\','/',realpath($booster_cache_dir)),str_replace('\\','/',dirname(__FILE__)));
            }
            else 
            {
                $booster_cache_dir = rtrim(str_replace('\\','/',dirname(__FILE__)),'/').'/../../booster_cache';
                $booster_cache_reldir = '../../booster_cache';
            }
            $booster->booster_cachedir = $booster_cache_reldir;
            $booster->js_minify = TRUE;
            $booster->js_closure_compiler = FALSE;
            
            $max_url_length= $booster->max_url_length( 2000 ); // URLs over 2000 characters will not work in the most popular web browsers. @see http://www.boutell.com/newfaq/misc/urllength.html
            $max_url_length = (int)apply_filters('gd_booster_max_url_length', $max_url_length );
            $max_url_length = max( 250, $max_url_length );
            
            $site_url = strip_tags(str_replace(array('http://','https://'),'',site_url()));
            // Get Domainname
            if (MULTISITE && get_home_url()) {

                if (function_exists('geodir_location_geo_home_link')) {
                    remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
                }

                $host = strip_tags(str_replace(array('http://','https://'),'',get_home_url()));
                $site_url = strip_tags(str_replace(array('http://','https://'),'',network_site_url()));

                if (function_exists('geodir_location_geo_home_link')) {
                    add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
                }

            } elseif (isset($_SERVER['SCRIPT_URI'])) {
                $host = parse_url(strip_tags($_SERVER['SCRIPT_URI']), PHP_URL_HOST);
            } else {
                $host = strip_tags($_SERVER['HTTP_HOST']);
            }
            // Fix slash for multisite directory site.
            $site_url = rtrim($site_url, "/");
            $host = rtrim($host, "/");
            
            $http_host = $host;
            
            $booster_site_url = $booster->get_site_root_url();
            $cdn_enabled = $booster->cdn_enabled();
            $cdn_ext = $cdn_enabled ? $booster->cdn_file_ext() : '';
            $cdn_url = $cdn_enabled ? $booster->get_site_root_url() : '';
            
            // exclude js/css
            $exclude_js_css = $booster->geodir_exclude_js_css();
            $exclude_js = !empty($exclude_js_css) && isset($exclude_js_css['js']) ? $exclude_js_css['js'] : array();
            $exclude_css = !empty($exclude_js_css) && isset($exclude_js_css['css']) ? $exclude_js_css['css'] : array();

            // Calculate relative path from root to Booster directory
            $root_to_booster_path = $booster->getpath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname(realpath(ABSPATH))));
            
            if(preg_match_all('/<head.*<\/head>/ims',$out,$headtreffer,PREG_PATTERN_ORDER) > 0)
            {
                $pagetreffer = $out;
                // Prevent processing of (conditional) comments
                $pagetreffer = preg_replace_callback('/<script[^>]*>(.*?)<\/script>/ims', function($gdbm) { return str_replace(array("<!--", "-->"), array("GDBOOSTOPEN", "GDBOOSTCLOSE" ), $gdbm[0]); }, $pagetreffer);
                $pagetreffer = preg_replace('/<!--.+?-->/ims', '', $pagetreffer);
                $pagetreffer = preg_replace_callback('/<script[^>]*>(.*?)<\/script>/ims', function($gdbm) { return str_replace(array("GDBOOSTOPEN", "GDBOOSTCLOSE"), array("<!--", "-->" ), $gdbm[0]); }, $pagetreffer);
                
                // Detect charset
                if(preg_match('/<meta http-equiv="Content-Type" content="text\/html; charset=(.+?)" \/>/',$pagetreffer,$charset))
                {
                    $pagetreffer = str_replace($charset[1],'',$pagetreffer);
                    $charset = $charset[1];
                }
                else $charset = '';
                
                // CSS part
                $css_rel_files = array();
                $css_abs_files = array();
                $css_external_files = array();

                // Continue with external files
                preg_match_all('/<link[^>]*?href=[\'"]*?([^\'"]+?\.css)[\'"]*?[^>]*?>/ims',$pagetreffer,$treffer,PREG_PATTERN_ORDER);
                for($i=0;$i < count($treffer[0]);$i++) 
                {
                    // Get media-type
                    if(preg_match('/media=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$mediatreffer)) 
                    {
                        $media = preg_replace('/[^a-z]+/i','',$mediatreffer[1]);
                        if(trim($media) == '') $media = 'all';
                    }
                    else $media = 'all';

                    // splitting media types seems to break some css so we remove it.
                    $media = 'all';

                    // Get relation
                    if(preg_match('/rel=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$reltreffer)) $rel = $reltreffer[1];
                    else $rel = 'stylesheet';

                    // Convert file's URI into an absolute local path
                    if(MULTISITE && $host != $site_url){
                        $treffer[1][$i] = str_replace($http_host,$site_url, $treffer[1][$i]);
                    }
                    if(strpos($treffer[1][$i],'http') !== false){
                        // http or https
                        $protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
                        $protocol_regex = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? '/^https:\/\/[^\/]+/' : '/^http:\/\/[^\/]+/';
                        if ( strpos( $treffer[1][$i], "//" . $http_host ) === 0 ) {
                            $treffer[1][$i] = preg_replace( '/^\/\/'.$http_host.'[^\/]*/', $protocol . $http_host, $treffer[1][$i] );
                        }elseif ( strpos( $treffer[1][$i], $http_host ) === 0 ) {
                            $treffer[1][$i] = preg_replace( '/^'.$http_host.'[^\/]*/', $protocol . $http_host, $treffer[1][$i] );
                        }
                        $filename = preg_replace($protocol_regex ,rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
                    } else {
                        $filename = preg_replace('/^\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
                    }
                    //$filename = preg_replace('/^http:\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
                    // Remove any parameters from file's URI
                    $filename = preg_replace('/\?.*$/','',$filename);
                    // If file exists
                  // $booster_out .= "###".$filename."\n";

                    // If file is external
                    if( substr($filename,0,7) == 'http://' || substr($filename,0,8) == 'https://' || substr($filename,0,2) == '//' ) {
                        // exclude js files
                        if (basename($filename) != '' && gd_booster_exclude_file($treffer[1][$i], $exclude_css)) {
                            $css_exclude_files[] = $treffer[0][$i];
                                
                            if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                $out = str_replace($treffer[0][$i], '<!-- Excluded by GD Booster '.$treffer[0][$i].' -->', $out);
                            } else {
                                $out = str_replace(array($treffer[0][$i]."\r\n", $treffer[0][$i]."\r", $treffer[0][$i]."\n", $treffer[0][$i]),'',$out);
                            }
                        } else {
                            // Skip processing of external files altogether
                            $css_external_files[] = $treffer[0][$i];
                            $debug_text = '';
                            if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                $debug_text = '<!-- Processed by GD Booster external file1 '.$treffer[0][$i].' -->';
                            }
                            $out = str_replace( $treffer[0][$i], $debug_text, $out );
                        }
                    } else if(file_exists($filename)) {
                        // If its a normal CSS-file
                        if(substr($filename,strlen($filename) - 4,4) == '.css' && file_exists($filename))
                        {
                            // exclude css files
                            if (basename($filename) != '' && gd_booster_exclude_file($treffer[1][$i], $exclude_css)) {
                                $css_exclude_files[] = $treffer[0][$i];
                                    
                                if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                    $out = str_replace($treffer[0][$i], '<!-- Excluded by GD Booster '.$treffer[0][$i].' -->', $out);
                                } else {
                                    $out = str_replace(array($treffer[0][$i]."\r\n", $treffer[0][$i]."\r", $treffer[0][$i]."\n", $treffer[0][$i]),'',$out);
                                }
                            } else {
                                // Put file-reference inside a comment
                                if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                    $out = str_replace($treffer[0][$i],'<!-- Processed by GD Booster '.$treffer[0][$i].' -->',$out);
                                } else {
                                    $out = str_replace(array($treffer[0][$i]."\r\n", $treffer[0][$i]."\r", $treffer[0][$i]."\n", $treffer[0][$i]),'',$out);
                                }
            
                                // Calculate relative path from Booster to file
                                $booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
                                $filename = $booster_to_file_path.'/'.basename($filename);
                
                                // Create sub-arrays if not yet there
                                if(!isset($css_rel_files[$media])) $css_rel_files[$media] = array();
                                if(!isset($css_abs_files[$media])) $css_abs_files[$media] = array();
                                if(!isset($css_rel_files[$media][$rel])) $css_rel_files[$media][$rel] = array();
                                if(!isset($css_abs_files[$media][$rel])) $css_abs_files[$media][$rel] = array();
                                
                                $position = (int)strpos($html, $treffer[0][$i]);
                                // Enqueue file to respective array
                                array_push($css_rel_files[$media][$rel], array('file' => $filename, 'position' => $position));
                                array_push($css_abs_files[$media][$rel],rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);
                            }
                        }
                        else $out = str_replace($treffer[0][$i],$treffer[0][$i].'<!-- GD Booster skipped '.$filename.' -->',$out);
                    }
                    // Leave untouched but put calculated local file name into a comment for debugging
                    else $out = str_replace($treffer[0][$i],$treffer[0][$i].'<!-- GD Booster had a problems finding '.$filename.' -->',$out);
                }
                
                // Start width inline-files
                preg_match_all('/<style[^>]*>(.*?)<\/style>/ims',$pagetreffer,$treffer,PREG_PATTERN_ORDER);
                for($i=0;$i<count($treffer[0]);$i++)
                {

                    $element = $treffer[0][$i];
                    $inline_script = $treffer[1][$i];

                    $should_continue = apply_filters('geodir_booster_css_continue', false, $element,$inline_script );

                    if ($should_continue) {
                        continue;
                    }


                    // Get media-type
                    if(preg_match('/media=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$mediatreffer)) 
                    {
                        $media = preg_replace('/[^a-z]+/i','',$mediatreffer[1]);
                        if(trim($media) == '') $media = 'all';
                    }
                    else $media = 'all';
                    $rel = 'stylesheet';
                    
                    // Create sub-arrays if not yet there
                    if(!isset($css_rel_files[$media])) $css_rel_files[$media] = array();
                    if(!isset($css_abs_files[$media])) $css_abs_files[$media] = array();
                    if(!isset($css_rel_files[$media][$rel])) $css_rel_files[$media][$rel] = array();
                    if(!isset($css_abs_files[$media][$rel])) $css_abs_files[$media][$rel] = array();

                    // Save plain CSS to file to keep everything in line
                    $css_plain_filename = md5($treffer[1][$i]).'_plain.css';
                    
                    $filename = $booster_cache_dir.'/'.$css_plain_filename;
                    if ( !file_exists( $filename ) ) {
                        try {
                            $treffer_content = $treffer[1][$i];
                            
                            // CDN background image url fix
                            if ($cdn_url && $cdn_ext) {
                                $begin = $end = '';
                                $regex = '#(?<=[(\"\''.$begin.'])'.quotemeta($booster_site_url).'(?:(/[^\"\''.$end.')]+\.('.$cdn_ext.')))#';
                                $treffer_content = preg_replace_callback($regex, 'gd_booster_cdn_url_rewrite', $treffer_content);
                            }
                            
                            file_put_contents( $filename, $treffer_content );
                        } catch(Exception $e) {
                            error_log( 'GD Booster Error: ' . $e->getMessage() );
                        }
                    }
                    
                    try {
                        chmod($filename,0777);
                    } catch(Exception $e) {
                        error_log( 'GD Booster Error: ' . $e->getMessage() );
                    }
        
                    // Enqueue file to array
                    $booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
                    
                    // Calculate relative path from Booster to file
                    $booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
                    $filename = $booster_to_file_path.'/'.$css_plain_filename;
                    
                    $position = (int)strpos($html, $treffer[0][$i]);
                    array_push($css_rel_files[$media][$rel], array('file' => $filename, 'position' => $position));
                    array_push($css_abs_files[$media][$rel],rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);

                    $debug_text = '';
                    if ( GEODIR_GD_BOOSTER_DEBUGGING_ENABLE ) {
                        $debug_text = '<!-- Moved to file by GD Booster '.$css_plain_filename.' -->';
                    }
                    $pagetreffer = str_replace( $treffer[0][$i], $debug_text, $pagetreffer );
                    $out = str_replace( $treffer[0][$i], $debug_text, $out );					
                }

                $booster_css_base = $booster_folder_url . 'booster_css.php?dir=';
                $booster_css_extra = '&amp;cachedir=' . htmlentities(str_replace('..', '%3E', $booster_cache_reldir), ENT_QUOTES);
                $booster_css_extra .= $booster->debug ? '&amp;debug=1' : '';
                $booster_css_extra .= $booster->librarydebug ? '&amp;librarydebug=1' : '';
                        
                // Creating Booster markup for each media and relation separately
                $booster_css_files = '';
                $c = 0;
                foreach ($css_rel_files as $media => $css_media_files) {
                    foreach ($css_media_files as $rel => $css_files) {
                        $css_files = gd_booster_sort_files($css_files); // sort files
                        
                        $booster->getfilestime($css_files, 'css');
                        
                        $booster_css_extra_part = $booster_css_extra . '&amp;nocache=' . $booster->filestime;
                        $booster_split_files = gd_booster_parse_combine_files($max_url_length, $css_files, $booster_css_base, $booster_css_extra_part);
                        
                        foreach ($booster_split_files as $split_file) {
                            $c++;
                            $sep = $c > 1 ? "\r\n" : '';
                            
                            $booster_css_file = '<link type="text/css" rel="' . $rel . '" media="' . $media . '" href="' . $booster_css_base . $split_file . $booster_css_extra_part . '" />';
                            
                            if ($media == 'print') {
                                $booster_css_files .= $sep . '<noscript>' . $booster_css_file . '</noscript>';
                                $js_plain .= 'jQuery(document).ready(function (){jQuery("head").append("' . addslashes($booster_css_file) . '");});';
                            } else {
                                $booster_css_files .= $sep . $booster_css_file;
                            }
                        }
                    }
                    
                    $booster_css_files .= "\r\n";
                }

                // Insert markup for normal browsers and IEs (CC's now replacing former UA-sniffing)
                if ($charset != '') {
                    $booster_out .= '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />' . "\r\n";
                }
                $booster_out .= '<!--[if IE]><![endif]-->'."\r\n";
                $booster_out .= '<!--[if (gte IE 8)|!(IE)]><!-->'."\r\n";
                if (!empty($css_external_files)) {
                    $booster_out .= "\r\n" . implode("\r\n", $css_external_files) . "\r\n";
                }
                $booster_out .= $booster_css_files; // Combined css files.
                
                $booster_out .= '<!--<![endif]-->'."\r\n";
                $booster_out .= '<!--[if lte IE 7 ]>'."\r\n";
                $booster_out .= str_replace('booster_css.php', 'booster_css_ie.php', $booster_css_files);
                $booster_out .= '<![endif]-->'."\r\n";
                if (!empty($css_exclude_files)) {
                    $booster_out .= implode("\r\n", $css_exclude_files);
                }
                
                // Injecting the result
                $out = str_replace('</title>',"</title>\r\n".$booster_out,$out);
                $booster_out = '';				
                
                // JS-part
                $js_rel_files = array();
                $js_abs_files = array();
                $js_exclude_files = array();
                $js_external_files = array();
                
                preg_match_all('/<script[^>]*>(.*?)<\/script>/ims', $pagetreffer, $treffer, PREG_PATTERN_ORDER);
                
                for ($i = 0; $i < count($treffer[0]); $i++ ) {
                    $element = $treffer[0][$i];
                    $inline_script = $treffer[1][$i];

                    $should_continue = apply_filters('geodir_booster_script_continue', false, $element, $inline_script);
                    
                    if ($should_continue) {
                        continue;
                    }
                    if ( strpos($element, 'application/ld+json') !== false ) { // Skip for application/ld+json script
                        continue;
                    }
                    if ( strpos($element, 'text/template') !== false ) { // Skip for text/template script
                        continue;
                    }
                    
                    // Handle inline script
                    if (trim($inline_script) != '') {
                        if (gd_booster_exclude_file($inline_script, $exclude_js, true)) {
                            $js_external_files[] = $element;
                            
                            if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                $out = str_replace($element, '<!-- Excluded by GD Booster ' . $element . ' -->', $out);
                            } else {
                                $out = str_replace(array($element . "\r\n", $element . "\r", $element . "\n", $element), '', $out);
                            }
                        } else {
                            // Save plain JS to file to keep everything in line
                            $js_plain_filename = md5($inline_script) . '_plain.js';
                            
                            $filename = $booster_cache_dir . '/' . $js_plain_filename;
                            
                            if ( !file_exists( $filename ) ) {
                                try {
                                    file_put_contents( $filename, trim($inline_script) );
                                } catch(Exception $e) {
                                    error_log( 'GD Booster Error: ' . $e->getMessage() );
                                }
                            }
                            try {
                                chmod( $filename, 0777 );
                            } catch(Exception $e) {
                                error_log( 'GD Booster Error: ' . $e->getMessage() );
                            }
                            
                            // Enqueue file to array
                            $booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
                            $booster_filename = $booster_to_file_path . '/' . $js_plain_filename;
                            
                            array_push( $js_rel_files, $booster_filename );
                            array_push( $js_abs_files, rtrim( str_replace( '\\', '/', dirname( realpath( ABSPATH ) ) ), '/') . '/' . $root_to_booster_path . '/' . $booster_filename );
                            $debug_text = '';
                            if ( GEODIR_GD_BOOSTER_DEBUGGING_ENABLE ) {
                                $debug_text = '<!-- Moved to file by GD Booster ' . $js_plain_filename . ' -->';
                            }
                            
                            $out = str_replace( $element, $debug_text, $out, $replaced );
                            if (!(int)$replaced > 0) {
                                $out = str_replace( "<!-- /setting -->", "", $out );
                                $out = str_replace( $element, $debug_text, $out );
                            }
                        }
                    } else { // Handle script files
                        if ( preg_match( '/<script.*?src=[\'"]*([^\'"]+\.js)\??([^\'"]*)[\'"]*.*?<\/script>/ims', $element, $src_matches ) ) { // .js file
                            $filename = $src_matches[1];

                            if(MULTISITE && $host != $site_url){
                                $filename = str_replace($http_host,$site_url, $filename);
                            }
                            if(strpos($filename,'http') !== false){
                                // http or https
                                $protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
                                $protocol_regex = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? '/^https:\/\/[^\/]+/' : '/^http:\/\/[^\/]+/';
                                if ( strpos( $treffer[1][$i], "//" . $http_host ) === 0 ) {
                                    $treffer[1][$i] = preg_replace( '/^\/\/'.$http_host.'[^\/]*/', $protocol . $http_host, $filename );
                                }elseif ( strpos( $treffer[1][$i], $http_host ) === 0 ) {
                                    $treffer[1][$i] = preg_replace( '/^'.$http_host.'[^\/]*/', $protocol . $http_host, $filename );
                                }
                                $filename = preg_replace($protocol_regex ,rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$filename);
                            } else {
                                $filename = preg_replace('/^\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$filename);
                            }

                            //$out .= '###'.$filename." \n";
                            // exclude js files
                            if (basename($filename) != '' && gd_booster_exclude_file($filename, $exclude_js)) {
                                $js_exclude_files[] = $element;
                                
                                if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                    $out = str_replace($element, '<!-- Excluded by GD Booster ' . $element . ' -->', $out);
                                } else {
                                    $out = str_replace(array($element . "\r\n", $element . "\r", $element . "\n", $element), '', $out);
                                }
                            } else {
                                if ( is_file($filename) && file_exists($filename) ) {
                                    // Remove any parameters from file's URI
                                    $filename = preg_replace('/\?.*$/', '', $filename);
                                                    
                                    // Calculate relative path from Booster to file
                                    $booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
                                    $booster_filename = $booster_to_file_path . '/' . basename($filename);
                        
                                    array_push( $js_rel_files, $booster_filename );
                                    array_push( $js_abs_files, rtrim( str_replace( '\\', '/', dirname( realpath( ABSPATH ) ) ), '/') . '/' . $root_to_booster_path . '/' . $booster_filename );
                                    
                                    // Put file-reference inside a comment
                                    if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                        $out = str_replace($element, '<!-- Processed by GD Booster ' . $element . ' -->', $out);
                                    } else {
                                        $out = str_replace(array($element . "\r\n", $element . "\r", $element . "\n", $element), '', $out);
                                    }
                                } else { // External file
                                    // Skip processing of external files altogether
                                    $js_external_files[] = $element;
                                    
                                    $debug_text = '';
                                    if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                        $debug_text = '<!-- Processed by GD Booster external file2 ' . $element . ' -->';
                                    }
                                    $out = str_replace( $element, $debug_text, $out );
                                }
                            }
                        } else { // Not .js file
                            if ( preg_match( '/<script.*?src=[\'"]*([^\'"]+\.*)\??([^\'"]*)[\'"]*.*?<\/script>/ims', $element, $src_custom_matches ) ) {
                                $src_filename = $src_custom_matches[1];
                                $filename = $src_filename;
                                // Remove any parameters from file's URI
                                $filename = preg_replace('/\?.*$/', '', $filename);
                            
                                // Convert file's URI into an absolute local path
                                if ( strpos( $filename, 'https:' ) !== false ) {
                                    $filename = preg_replace( '~/^https:\/\/'.$host.'[^\/]*/~', rtrim($_SERVER['DOCUMENT_ROOT'], '/'), $filename );
                                } else {
                                    // http or https
                                    $protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
                                    if ( strpos( $filename, "//" . $http_host ) === 0 ) {
                                        $filename = preg_replace( '/^\/\/'.$http_host.'[^\/]*/', $protocol . $http_host, $filename );
                                    }
                                    
                                    if ( strpos( $filename, $http_host ) === 0 ) {
                                        $srctreffer[1] = preg_replace( '/^'.$http_host.'[^\/]*/', $protocol . $http_host, $filename );
                                    }
                                    $filename = preg_replace( '/^http:\/\/'.$host.'[^\/]*/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'), $filename );
                                }

                                if ( is_file($filename) && file_exists($filename) ) {
                                    // Save plain JS to file to keep everything in line
                                    $js_plain_filename = md5($src_filename) . '_plain_custom.js';
                                    
                                    $filename = $booster_cache_dir . '/' . $js_plain_filename;
                                    
                                    if ( !file_exists( $filename ) ) {
                                        try {
                                            file_put_contents( $filename, trim($src_filename) );
                                        } catch(Exception $e) {
                                            error_log( 'GD Booster Error: ' . $e->getMessage() );
                                        }
                                    }									
                                    try {
                                        chmod( $filename, 0777 );
                                    } catch(Exception $e) {
                                        error_log( 'GD Booster Error: ' . $e->getMessage() );
                                    }
                                    
                                    // Enqueue file to array
                                    $booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
                                    $booster_filename = $booster_to_file_path . '/' . $js_plain_filename;
                                    
                                    array_push( $js_rel_files, $booster_filename );
                                    array_push( $js_abs_files, rtrim( str_replace( '\\', '/', dirname( realpath( ABSPATH ) ) ), '/') . '/' . $root_to_booster_path . '/' . $booster_filename );
                                    
                                    $debug_text = '';
                                    if ( GEODIR_GD_BOOSTER_DEBUGGING_ENABLE ) {
                                        $debug_text = '<!-- Moved to file by GD Booster ' . $js_plain_filename . ' -->';
                                    }

                                    $out = str_replace( $element, $debug_text, $out );
                                } else { // External file
                                    // Skip processing of external files altogether
                                    $js_external_files[] = $element;
                                    
                                    $debug_text = '';
                                    if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                        $debug_text = '<!-- Processed by GD Booster external file3 ' . $element . ' -->';
                                    }
                                    $out = str_replace( $element, $debug_text, $out );
                                }
                            } else { // Skipped file
                                $debug_text = '';
                                
                                if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
                                    $debug_text = '<!-- GD Booster skipped ' . $element . ' -->';
                                }
                                
                                $out = str_replace( $element, $debug_text, $out );
                            }
                        }
                    }
                }
                
                //$js_abs_files = array_unique($js_abs_files);
                //$js_abs_files = implode(',', $js_abs_files);

                $booster_js_base = $booster_folder_url . 'booster_js.php?dir=';
                $booster_js_extra = '&amp;cachedir=' . htmlentities(str_replace('..', '%3E', $booster_cache_reldir), ENT_QUOTES);
                $booster_js_extra .= $booster->debug ? '&amp;debug=1' : '';
                $booster_js_extra .= !$booster->js_minify ? '&amp;js_minify=0' : '';
                $booster_js_extra .= $booster->js_closure_compiler ? '&amp;js_cc=1' : '';
                $booster_js_extra .= '&amp;nocache=' . $booster->filestime;
                
                $booster_split_files = gd_booster_parse_combine_files($max_url_length, $js_rel_files, $booster_js_base, $booster_js_extra);
                
                $booster_js_files = '';
                $c = 0;
                foreach ($booster_split_files as $split_file) {
                    $c++;
                    $sep = $c > 1 ? "\r\n" : '';
                    
                    $booster_js_files .= $sep . '<script type="text/javascript" src="' . $booster_js_base . $split_file . $booster_js_extra . '"></script>';
                }
                
                $js_plain = preg_replace('/\/\*.*?\*\//ims', '', $js_plain);
                $js_plain .= 'try {document.execCommand("BackgroundImageCache", false, true);} catch(err) {}';

                if (!empty($js_external_files)) {
                    $booster_out .= "\r\n" . implode("\r\n", $js_external_files) . "\r\n";
                }
                
                $booster_out .= $booster_js_files; // Combined js files.
                
                if (!empty($js_exclude_files)) {
                    $booster_out .= "\r\n" . implode("\r\n", $js_exclude_files) . "\r\n";
                }
                $booster_out .= '<script type="text/javascript">'.$js_plain.'</script>';
                $booster_out .= "\r\n";

                /*
                 * Filter the booster out js, allows you to add something before or after the JS output.
                 *
                 * @param string The JS script output contained in script tags.
                 * @since 1.0.9
                 */
                $booster_out = apply_filters('gd_booster_booster_out_js', $booster_out);
                #$booster_out .= "\r\n<!-- ".$js_abs_files." -->\r\n";
                
                // Injecting the result at the bottom
                //$out = str_replace('</head>',$booster_out.'</head>',$out);
                ///*

                /*
                 * Filter the page output html before the JS code is added.
                 *
                 * @param string The entire page HTML before the new JS file is added.
                 * @since 1.0.9
                 */
                $out = apply_filters('gd_booster_out', $out);
                if ( strpos( $out, "</body>" ) !== false ) {
                    $out = str_replace('</body>', $booster_out . '</body>', $out);
                } else {
                    $out .= $booster_out;
                }
                //*/
            }
        } else {
            $out = str_replace('<body', '<div style="display: block; padding: 1em; background-color: #FFF9D0; color: #912C2C; border: 1px solid #912C2C; font-family: Calibri, \'Lucida Grande\', Arial, Verdana, sans-serif; white-space: pre;">You need to upgrade to PHP 5 or higher to have CSS-JS-Booster work. You currently are running on PHP ' . phpversion() . '</div><body', $out);
        }
        
        // Recreate output buffer
        try {
            ob_end_clean();
        } catch(Exception $e) {
            error_log( 'GD Booster Error: ' . $e->getMessage() );
        }
        

            try {
                ob_start();
            } catch(Exception $e) {
                error_log( 'GD Booster Error: ' . $e->getMessage() );
            }


        //CDN stuff

        if(GEODIR_GD_BOOSTER_CDN_ENABLED && GEODIR_GD_BOOSTER_CDN_ROOT_URL){

            $root_url = $booster->get_site_root_url();
            $extensions = GEODIR_GD_BOOSTER_CDN_FILE_EXT;
            $xml_begin = $xml_end = '';
            if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
                $xml_begin = '>';
                $xml_end = '<';
            }

            // if on add/edit listing page then don't use CDN for images.
            if(isset($_REQUEST['pid']) || isset($_REQUEST['listing_type'])){
               // dont CDN files on add/edit page
            }else{
                $regex = '#(?<=[(\"\''.$xml_begin.'])'.quotemeta($root_url).'(?:(/[^\"\''.$xml_end.')]+\.('.$extensions.')))#';
                $out = preg_replace_callback($regex, 'gd_booster_cdn_url_rewrite', $out);
            }



            $extensions = explode('|',$extensions);

            //replace css
            if(in_array('js',$extensions)){
                $out = str_replace(
                    array(
                        trailingslashit($root_url).'wp-content/plugins/geodir_gd_booster/booster_css.php'
                    ),
                    array(
                        trailingslashit(GEODIR_GD_BOOSTER_CDN_ROOT_URL).'wp-content/plugins/geodir_gd_booster/booster_css.php')
                    ,
                    $out
                );
            }


            //replace JS
            if(in_array('css',$extensions)) {
                $out = str_replace(
                    array(
                        trailingslashit($root_url) . 'wp-content/plugins/geodir_gd_booster/booster_js.php'
                    ),
                    array(
                        trailingslashit(GEODIR_GD_BOOSTER_CDN_ROOT_URL) . 'wp-content/plugins/geodir_gd_booster/booster_js.php')
                    ,
                    $out
                );
            }
        }

        // minify html if not debugging
        if (!GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
            $search = array(
                '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
                '/[^\S ]+\</s',  // strip whitespaces before tags, except space
                '/(\s)+/s'       // shorten multiple whitespace sequences
            );

            $replace = array(
                '>',
                '<',
                '\\1'
            );

            $out = preg_replace($search, $replace, $out);
        }


        // Output page
        echo $out;
    }
}
function gd_booster_cdn_url_rewrite($match) {
    global $blog_id;
    $path = $match[1];
    //if is subfolder install and isn't root blog and path starts with site_url and isn't uploads dir
    if(is_multisite() && !is_subdomain_install() && $blog_id !== 1) {
        $bloginfo = get_blog_details($blog_id);
        if((0 === strpos($path, $bloginfo->path)) && (0 !== strpos($path, $bloginfo->path.'files/'))) {
            $path = '/'.substr($path, strlen($bloginfo->path));
        }
    }

    return GEODIR_GD_BOOSTER_CDN_ROOT_URL. $path;
}
// wordpress SEO fix
add_filter( 'wpseo_json_ld_search_output', 'gd_booster_wordpress_seo_fix', 10, 1 ); 

function gd_booster_wordpress_seo_fix($code){
    if (strpos($code,'[') !== false) {
        //they fixed it
    } else {
        //we fix it
        $code = str_replace('<script type="application/ld+json">', '<script type="application/ld+json">[', $code);
        $code = str_replace('</script>', ']</script>', $code);
    }
    return 	$code;
}

/**
 * Exclude javascript/css file from GD booster cache.
 *
 * @since 1.0.6
 *
 * @param string $fileurl Relative path of javascript/css file.
 * @param array $exclude_files Array of files to excludes from GD booster cache.
 * @param bool $inline True if file url is inline script else False.
 * @return bool If true file excluded from GD booster cache.
 */
function gd_booster_exclude_file($fileurl, $exclude_files = array(), $inline = false) {  
    $return = false;
    
    if ($inline) { // Fix leaflet js dynamic load.
        if (strpos($fileurl, 'geodirectory-leaflet-style-css') !== false || strpos($fileurl, 'geodirectory-leaflet-script') !== false || strpos($fileurl, 'gdcluster-leaflet-js') !== false || strpos($fileurl, 'gdcluster-leaflet-css') !== false) {
            $return = true;
        }
    }

    $exclude_files[] = "sitepress.js"; // WPML
    $exclude_files[] = "notes-common-v2.js"; // jetpack
    $exclude_files[] = "admin-bar-v2.js"; // jetpack
    $exclude_files[] = "dashicons.min.css";
    $exclude_files[] = "bbpress.css";
    $exclude_files[] = "multinews/css/print.css";//multinews theme
    if (!empty($exclude_files)) {
        foreach ($exclude_files as $exclude_file) {
            if ($exclude_file != '' && $fileurl != '' && strpos($fileurl, $exclude_file) !== false) {
                $return = true;
            }
        }
    }
    return $return;
}

/**
 * Exclude javascript from GD booster cache.
 *
 * @since 1.0.9
 * @since 1.2.4 LinkedIn share inline + external js breaks Google map - FIXED
 *
 * @param bool $continue Whether to exclude script element or not.
 * @param string $content Script element.
 * @param string $inline_content Inline script content.
 * @return bool If true script element excluded.
 */
function geodir_booster_exclude_js( $continue, $content, $inline_content = '' ) {
    // Amazon associate script
    if (strpos($content, 'amzn_assoc_linkid') !== false && strpos($content, 'amzn_assoc_placement') !== false && strpos($content, 'amzn_assoc_tracking_id') !== false) {
        return true;
    }
    if (strpos($content, 'amazon-adsystem.com/widgets/onejs') !== false) {
        return true;
    }

    // flickr widget
    if (strpos($content, 'badge_code_v2.gne') !== false) {
        return true;
    }
        
    // tinymce on add listing page
    if (strpos($content, 'tinymce.min.js') !== false || strpos($content, 'tinyMCEPreInit = {') !== false) {
        $continue = true;
    }
    
    // Skip google ads js file
    if (strpos($content, '/pagead/js/adsbygoogle.js') !== false || strpos($content, '/pagead/show_ads.js') !== false) {
        $continue = true; 
    }

    // Skip google ads inline script
    if (strpos($content, 'window.adsbygoogle') !== false || (strpos($content, 'google_ad_client') !== false && strpos($content, 'google_ad_slot') !== false)) {
        $continue = true; 
    }

    // s2member
    if (strpos($content, 's2member_js') ) {
        global $gdb_s2member_active;
        $gdb_s2member_active = $content;
        add_action('gd_booster_booster_out_js','gd_booster_s2member_fix_js',10,1);
        add_action('gd_booster_out','gb_booster_s2member_fix_out',10,1);
        $continue = true;
    }

    // BuddyPress
    if (strpos($content, '"{{data.') !== false || strpos($content, '<# ') !== false ) {
        $continue = true;
    }

    // woocommerce
    if (strpos($content, '{{{ data.') !== false || strpos($content, '<p>Sorry,') !== false ) {
        $continue = true;
    }
    
    // backbone js template
    if (strpos($content, ' type="text/html"') !== false || strpos($content, " type='text/html'") !== false ) {
        $continue = true;
    }

    // SiteOrigin CSS
    if (strpos($content, ' type="text/css"') !== false || strpos($content, " type='text/css'") !== false ) {
        $continue = true;
    }

    // GD email bot protection
    if (strpos($content, "document.write('<") !== false ) {
        $continue = true;
    }
    
    // Leaflet dynamic load fix
    if (strpos($content, "document.write('<") !== false && strpos($content, "typeof google.maps") !== false && (strpos($content, 'geodirectory-leaflet-style-css') !== false || strpos($content, 'geodirectory-leaflet-script') !== false || strpos($content, 'gdcluster-leaflet-js') !== false || strpos($content, 'gdcluster-leaflet-css') !== false)) {
        $continue = false;
    }

    //revolution slider
    if (strpos($content, "htmlDivCss") !== false ) {
        $continue = true;
    }

    // Google Analytics
    if (strpos($content, "google-analytics.com") !== false ) {
        $continue = true;
    }
    
    // Fix LinkedIn share inline + external js
    if ( strpos($content, 'platform.linkedin.com/in.js') !== false && strpos($content, ' src="') !== false && trim( $inline_content ) != '' ) {
        $continue = true;
    }
    
    // Fix HTML5 Maps conflicts
    if ( strpos($content, ' src=') !== false && ( strpos($content, '/static/js/raphael.min.js') !== false || strpos($content, 'index.php?freemap_js_data=true&map_id=') !== false) ) {
        $continue = true;
    }

    return $continue; 
}
add_filter( 'geodir_booster_script_continue', 'geodir_booster_exclude_js', 10, 3 );

/**
 * Exclude javascript from GD booster cache.
 *
 * @since 1.0.9
 *
 * @param bool $continue Whether to exclude script element or not.
 * @param string $content Script element.
 * @return bool If true script element excluded.
 */
function geodir_booster_exclude_css( $continue, $content, $inline_content ) {

    //stupid avada CSS
    if($inline_content==' iframe { visibility: hidden; opacity: 0; } '){
        $continue = true;
    }

    // GD fontawesome stars
    if (strpos($content, ".gd-star-rating i.fa {color:") !== false ) {
        $continue = true;
    }

    //revolution slider
    if (strpos($content, "htmlDivCss") !== false ) {
        $continue = true;
    }

    return $continue;
}
add_filter( 'geodir_booster_css_continue', 'geodir_booster_exclude_css', 10, 3 );



$gdb_s2member_active = false;

function gd_booster_s2member_fix_js($booster_out){
    global $gdb_s2member_active;


    return $booster_out. $gdb_s2member_active;
}

function gb_booster_s2member_fix_out($out){
    global $gdb_s2member_active;

    $out = str_replace($gdb_s2member_active,"",$out);

    return $out;
}


function gd_booster_delete_site_options( $options = array() ){
    if ( !is_multisite() ) {
        return false;
    }

    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

    if ( empty( $blog_ids ) ) {
        return false;
    }

    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );

        if ( !empty( $options ) ) {
            if ( is_array( $options ) ) {
                foreach ( $options as $option ) {
                    if ( $option != '' ) {
                        delete_site_option( $option );
                    }
                }
            } else {
                if ( $options != '' ) {
                    delete_site_option( $options );
                }
            }
        }
    }
    switch_to_blog( $original_blog_id );
}

function gd_booster_is_plugin_active() {
    $plugin_file = 'geodir_gd_booster/geodir_gd_booster.php';

    if ( !is_multisite() ) {
        return true;
    }

    if ( !function_exists( 'is_plugin_active' ) ) {
        /**
         * Detect plugin. For use on Front End only.
         */
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if ( is_network_admin() )
        $is_active = is_plugin_active_for_network( $plugin_file );
    else
        $is_active = is_plugin_active( $plugin_file );

    return $is_active;
}

function gd_booster_parse_tags($text) {
    $text = htmlentities(str_replace('..', '%3E', $text));

    return $text;
}

function gd_booster_parse_combine_files($max_url_length, $booster_files_arr, $booster_base, $booster_extra) {
    $booster_files_arr  = array_unique($booster_files_arr);
    $booster_files_arr  = array_map('gd_booster_parse_tags', $booster_files_arr);
    $booster_files      = implode(',', $booster_files_arr);
                
    $booster_files_len  = strlen($booster_files);
    $booster_base_len   = strlen($booster_base);
    $booster_extra_len  = strlen($booster_extra);

    $split_files = array();
    if (($booster_files_len + $booster_base_len + $booster_extra_len) > $max_url_length) {
        $split_files    = array();
        $split_file     = '';
        $count          = 0;
        
        foreach ($booster_files_arr as $booster_file) {
            $count++;
            $colon = $count > 1 ? ',' : '';
            
            $split_file_len = ($booster_base_len + $booster_extra_len + strlen($split_file) + strlen($colon . $booster_file));
            
            if ($split_file_len == $max_url_length) {
                $split_files[]  = $split_file . $colon . $booster_file;
                $split_file     = '';
                $count          = 0;
            } else if ($split_file_len > $max_url_length) {
                $split_files[]  = $split_file;
                $split_file     = $booster_file;
                $count          = 1;
            } else {
                $split_file .= $colon . $booster_file;
            }
        }
        
        if ($split_file != '') {
            $split_files[] = $split_file;
        }
    } else {
        $split_files[] = $booster_files;
    }

    return $split_files;
}

/**
 * Sorting the order of files.
 *
 * @since 1.1.5
 *
 * @param array $files Array of files to be sorted.
 * @return array Modified files array.
 */
function gd_booster_sort_files($files) {
    if (empty($files)) {
        return array();
    }
    
    $sort_files = array();
    foreach ($files as $i => $file) {
        $sort_files[$file['position']] = $file['file'];
    }
    ksort($sort_files);
    
    return $sort_files;
}