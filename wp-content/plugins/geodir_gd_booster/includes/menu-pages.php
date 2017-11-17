<?php
namespace geodir_gd_booster // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	class menu_pages // Plugin options.
	{
		protected $plugin; // Set by constructor.

		public function __construct()
		{
			$this->plugin = plugin();
		}

		public function options()
		{
			echo '<form id="plugin-menu-page" class="plugin-menu-page" method="post" enctype="multipart/form-data"'.
			     ' action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce())), self_admin_url('/admin.php'))).'">'."\n";

			echo '<div class="plugin-menu-page-heading">'."\n";

			if(is_multisite()) // Wipes entire cache (e.g. this clears ALL sites in a network).
				echo '   <button type="button" class="plugin-menu-page-wipe-cache" style="float:right; margin-left:15px;" title="'.esc_attr(__('Wipe Cache (Start Fresh); clears the cache for all sites in this network at once!', $this->plugin->text_domain)).'"'.
				     '      data-action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce(), __NAMESPACE__ => array('wipe_cache' => '1'))), self_admin_url('/admin.php'))).'">'.
				     '      '.__('Wipe', $this->plugin->text_domain).' <img src="'.esc_attr($this->plugin->url('/client-s/images/wipe.png')).'" style="width:16px; height:16px;" /></button>'."\n";

			echo '   <button type="button" class="plugin-menu-page-clear-cache" style="float:right;" title="'.esc_attr(__('Clear Cache (Start Fresh)', $this->plugin->text_domain).((is_multisite()) ? __('; affects the current site only.', $this->plugin->text_domain) : '')).'"'.
			     '      data-action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce(), __NAMESPACE__ => array('clear_cache' => '1'))), self_admin_url('/admin.php'))).'">'.
			     '      '.__('Clear', $this->plugin->text_domain).' <img src="'.esc_attr($this->plugin->url('/client-s/images/clear.png')).'" style="width:16px; height:16px;" /></button>'."\n";

			echo '   <button type="button" class="plugin-menu-page-restore-defaults"'. // Restores default options.
			     '      data-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure about this?', $this->plugin->text_domain)).'"'.
			     '      data-action="'.esc_attr(add_query_arg(urlencode_deep(array('page' => __NAMESPACE__, '_wpnonce' => wp_create_nonce(), __NAMESPACE__ => array('restore_default_options' => '1'))), self_admin_url('/admin.php'))).'">'.
			     '      '.__('Restore', $this->plugin->text_domain).' <i class="fa fa-ambulance"></i></button>'."\n";

			echo '   <div class="plugin-menu-page-panel-togglers" title="'.esc_attr(__('All Panels', $this->plugin->text_domain)).'">'."\n";
			echo '      <button type="button" class="plugin-menu-page-panels-open"><i class="fa fa-chevron-down"></i></button>'."\n";
			echo '      <button type="button" class="plugin-menu-page-panels-close"><i class="fa fa-chevron-up"></i></button>'."\n";
			echo '   </div>'."\n";
			echo '<h1>'.__('GD Booster Cache Options', $this->plugin->text_domain).'</h1></div>'."\n";

			if(!empty($_REQUEST[__NAMESPACE__.'__updated'])) // Options updated successfully?
			{
				echo '<div class="plugin-menu-page-notice notice">'."\n";
				echo '   <i class="fa fa-thumbs-up"></i> '.__('Options updated successfully.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__restored'])) // Restored default options?
			{
				echo '<div class="plugin-menu-page-notice notice">'."\n";
				echo '   <i class="fa fa-thumbs-up"></i> '.__('Default options successfully restored.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__cache_wiped']))
			{
				echo '<div class="plugin-menu-page-notice notice">'."\n";
				echo '   <img src="'.esc_attr($this->plugin->url('/client-s/images/wipe.png')).'" /> '.__('Cache wiped across all sites; recreation will occur automatically over time.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__cache_cleared']))
			{
				echo '<div class="plugin-menu-page-notice notice">'."\n";
				echo '   <img src="'.esc_attr($this->plugin->url('/client-s/images/clear.png')).'" /> '.__('Cache cleared for this site; recreation will occur automatically over time.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__wp_config_wp_cache_add_failure']))
			{
				echo '<div class="plugin-menu-page-notice error">'."\n";
				echo '   <i class="fa fa-thumbs-down"></i> '.__('Failed to update your <code>/wp-config.php</code> file automatically. Please add the following line to your <code>/wp-config.php</code> file (right after the opening <code>&lt;?php</code> tag; on it\'s own line). <pre class="code"><code>&lt;?php<br />define(\'WP_CACHE\', TRUE);</code></pre>', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__wp_config_wp_cache_remove_failure']))
			{
				echo '<div class="plugin-menu-page-notice error">'."\n";
				echo '   <i class="fa fa-thumbs-down"></i> '.__('Failed to update your <code>/wp-config.php</code> file automatically. Please remove the following line from your <code>/wp-config.php</code> file, or set <code>WP_CACHE</code> to a <code>FALSE</code> value. <pre class="code"><code>define(\'WP_CACHE\', TRUE);</code></pre>', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__advanced_cache_add_failure']))
			{
				echo '<div class="plugin-menu-page-notice error">'."\n";
				if($_REQUEST[__NAMESPACE__.'__advanced_cache_add_failure'] === 'gdb-advanced-cache')
					echo '   <i class="fa fa-thumbs-down"></i> '.sprintf(__('Failed to update your <code>/wp-content/advanced-cache.php</code> file. Cannot write stat file: <code>%1$s/gdb-advanced-cache</code>. Please be sure this directory exists (and that it\'s writable): <code>%1$s</code>. Please use directory permissions <code>755</code> or higher (perhaps <code>777</code>). Once you\'ve done this, please try again.', $this->plugin->text_domain), esc_html($this->plugin->cache_dir()))."\n";
				else echo '   <i class="fa fa-thumbs-down"></i> '.__('Failed to update your <code>/wp-content/advanced-cache.php</code> file. Most likely a permissions error. Please create an empty file here: <code>/wp-content/advanced-cache.php</code> (just an empty PHP file, with nothing in it); give it permissions <code>644</code> or higher (perhaps <code>666</code>). Once you\'ve done this, please try again.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'__advanced_cache_remove_failure']))
			{
				echo '<div class="plugin-menu-page-notice error">'."\n";
				echo '   <i class="fa fa-thumbs-down"></i> '.__('Failed to remove your <code>/wp-content/advanced-cache.php</code> file. Most likely a permissions error. Please delete (or empty the contents of) this file: <code>/wp-content/advanced-cache.php</code>.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!empty($_REQUEST[__NAMESPACE__.'_pro_preview']))
			{
				echo '<div class="plugin-menu-page-notice info">'."\n";
				echo '<a href="'.add_query_arg(urlencode_deep(array('page' => __NAMESPACE__)), self_admin_url('/admin.php')).'" class="pull-right" style="margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
				echo '   <i class="fa fa-eye"></i> '.__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="http://www.websharks-inc.com/product/quick-cache/" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.<br /><small>NOTE: the free version of GD Booster (this LITE version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			if(!$this->plugin->options['enable']) // Not enabled yet?
			{
				echo '<div class="plugin-menu-page-notice warning">'."\n";
				echo '   <i class="fa fa-warning"></i> '.__('GD Booster is currently disabled; please review options below.', $this->plugin->text_domain)."\n";
				echo '</div>'."\n";
			}
			echo '<div class="plugin-menu-page-body">'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading'.((!$this->plugin->options['enable']) ? ' open' : '').'">'."\n";
			echo '      <i class="fa fa-flag"></i> '.__('Enable/Disable', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body'.((!$this->plugin->options['enable']) ? ' open' : '').' clearfix">'."\n";
			echo '      <p style="float:right; margin:-5px 0 0 0; font-weight:bold;">GD Booster = <i class="fa fa-tachometer fa-4x"></i> SPEED<em>!!</em></p>'."\n";
			echo '      <p style="margin-top:1em;"><label class="switch-primary"><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="1"'.checked($this->plugin->options['enable'], '1', FALSE).' /> <i class="fa fa-magic fa-flip-horizontal"></i> '.__('Yes, enable GD Booster!', $this->plugin->text_domain).'</label> &nbsp;&nbsp;&nbsp; <label><input type="radio" name="'.esc_attr(__NAMESPACE__).'[save_options][enable]" value="0"'.checked($this->plugin->options['enable'], '0', FALSE).' /> '.__('No, disable.', $this->plugin->text_domain).'</label></p>'."\n";
			echo '      <hr />'."\n";
			echo '      <p class="info">'.__('<strong>HUGE Time-Saver:</strong> Approx. 95% of all WordPress sites running GD Booster, simply enable it here; and that\'s it :-) <strong>No further configuration is necessary (really).</strong> All of the other options (down below) are already tuned for the BEST performance on a typical WordPress installation. Simply enable GD Booster here and click "Save All Changes". If you get any warnings please follow the instructions given. Otherwise, you\'re good <i class="fa fa-smile-o"></i>. This plugin is designed to run just fine like it is. Take it for a spin right away; you can always fine-tune things later if you deem necessary.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <hr />'."\n";
			echo '      <img src="'.esc_attr($this->plugin->url('/client-s/images/db-screenshot.png')).'" class="screenshot" />'."\n";
			echo '      <h3>'.__('How Can I Tell GD Booster is Working?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('First of all, please make sure that you\'ve enabled GD Booster here; then scroll down to the bottom of this page and click "Save All Changes". All of the other options (below) are already pre-configured for typical usage. Feel free to skip them all for now. You can go back through all of these later and fine-tune things the way you like them.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('Once GD Booster has been enabled, <strong>you\'ll need to log out (and/or clear browser cookies)</strong>. By default, cache files are NOT served to visitors who are logged-in, and that includes you too ;-) Cache files are NOT served to recent comment authors either. If you\'ve commented (or replied to a comment lately); please clear your browser cookies before testing.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('<strong>To verify that GD Booster is working</strong>, navigate your site like a normal visitor would. Right-click on any page (choose View Source), then scroll to the very bottom of the document. At the bottom, you\'ll find comments that show GD Booster stats and information. You should also notice that page-to-page navigation is <i class="fa fa-flash"></i> <strong>lightning fast</strong> now that GD Booster is running; and it gets faster over time!', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][debugging_enable]">'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['debugging_enable'], '1', FALSE).'>'.__('Yes, enable notes in the source code so I can see it\'s working (recommended).', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="2"'.selected($this->plugin->options['debugging_enable'], '2', FALSE).'>'.__('Yes, enable notes in the source code AND show debugging details (not recommended for production).', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['debugging_enable'], '0', FALSE).'>'.__('No, I don\'t want my source code to contain any of these notes.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-shield"></i> '.__('Plugin Deletion Safeguards', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-shield fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Uninstall on Plugin Deletion; or Safeguard Options?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('<strong>Tip:</strong> By default, if you delete GD Booster using the plugins menu in WordPress, nothing is lost. However, if you want to completely uninstall GD Booster you should set this to <code>Yes</code> and <strong>THEN</strong> deactivate &amp; delete GD Booster from the plugins menu in WordPress. This way GD Booster will erase your options for the plugin, erase directories/files created by the plugin, remove the <code>advanced-cache.php</code> file, terminate CRON jobs, etc. It erases itself from existence completely.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][uninstall_on_deletion]">'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['uninstall_on_deletion'], '0', FALSE).'>'.__('Safeguard my options and the cache (recommended).', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['uninstall_on_deletion'], '1', FALSE).'>'.__('Yes, uninstall (completely erase) GD Booster on plugin deletion.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('Directory / Expiration Time', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <h3>'.__('Base Cache Directory (Must be Writable; e.g. <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">Permissions</a> <code>755</code> or Higher)', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('This is where GD Booster will store the cached version of your site. If you\'re not sure how to deal with directory permissions, don\'t worry too much about this. If there is a problem, GD Booster will let you know about it. By default, this directory is created by GD Booster and the permissions are setup automatically. In most cases there is nothing more you need to do.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <table style="width:100%;"><tr><td style="width:1px; font-weight:bold; white-space:nowrap;">'.esc_html(WP_CONTENT_DIR).'/</td><td><input type="text" name="'.esc_attr(__NAMESPACE__).'[save_options][base_dir]" value="'.esc_attr($this->plugin->options['base_dir']).'" /></td><td style="width:1px; font-weight:bold; white-space:nowrap;">/</td></tr></table>'."\n";
			echo '      <hr />'."\n";
			echo '      <i class="fa fa-clock-o fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Automatic Expiration Time (Max Age)', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('If you don\'t update your site much, you could set this to <code>6 months</code> and optimize everything even further. The longer the Cache Expiration Time is, the greater your performance gain. Alternatively, the shorter the Expiration Time, the fresher everything will remain on your site. A default value of <code>7 days</code> (recommended); is a good conservative middle-ground.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('Keep in mind that your Expiration Time is only one part of the big picture. GD Booster will also clear the cache automatically as changes are made to the site (i.e. you edit a post, someone comments on a post, you change your theme, you add a new navigation menu item, etc., etc.). Thus, your Expiration Time is really just a fallback; e.g. the maximum amount of time that a cache file could ever possibly live.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('All of that being said, you could set this to just <code>60 seconds</code> and you would still see huge differences in speed and performance. If you\'re just starting out with GD Booster (perhaps a bit nervous about old cache files being served to your visitors); you could set this to something like <code>30 minutes</code>, and experiment with it while you build confidence in GD Booster. It\'s not necessary to do so, but many site owners have reported this makes them feel like they\'re more-in-control when the cache has a short expiration time. All-in-all, it\'s a matter of preference <i class="fa fa-smile-o"></i>.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><input type="text" name="'.esc_attr(__NAMESPACE__).'[save_options][cache_max_age]" value="'.esc_attr($this->plugin->options['cache_max_age']).'" /></p>'."\n";
			echo '      <p class="info">'.__('<strong>Tip:</strong> the value that you specify here MUST be compatible with PHP\'s <a href="http://php.net/manual/en/function.strtotime.php" target="_blank" style="text-decoration:none;"><code>strtotime()</code></a> function. Examples: <code>30 seconds</code>, <code>2 hours</code>, <code>7 days</code>, <code>6 months</code>, <code>1 year</code>.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Note:</strong> GD Booster will never serve a cache file that is older than what you specify here (even if one exists in your cache directory; stale cache files are never used). In addition, a WP Cron job will automatically cleanup your cache directory (once daily); purging expired cache files periodically. This prevents a HUGE cache from building up over time, creating a potential storage issue.', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('Client-Side Cache', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-desktop fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Allow Double-Caching In The Client-Side Browser?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('Recommended setting: <code>No</code> (for membership sites, very important). Otherwise, <code>Yes</code> would be better (if users do NOT log in/out of your site).', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('GD Booster handles content delivery through its ability to communicate with a browser using PHP. If you allow a browser to (cache) the caching system itself, you are momentarily losing some control; and this can have a negative impact on users that see more than one version of your site; e.g. one version while logged-in, and another while NOT logged-in. For instance, a user may log out of your site, but upon logging out they report seeing pages on the site which indicate they are STILL logged in (even though they\'re not — that\'s bad). This can happen if you allow a client-side cache, because their browser may cache web pages they visited while logged into your site which persist even after logging out. Sending no-cache headers will work to prevent this issue.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('All of that being said, if all you care about is blazing fast speed and users don\'t log in/out of your site (only you do); you can safely set this to <code>Yes</code> (recommended in this case). Allowing a client-side browser cache will improve speed and reduce outgoing bandwidth when this option is feasible.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][allow_browser_cache]">'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['allow_browser_cache'], '0', FALSE).'>'.__('No, prevent a client-side browser cache (safest option).', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['allow_browser_cache'], '1', FALSE).'>'.__('Yes, I will allow a client-side browser cache of pages on the site.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '      <p class="info">'.__('<strong>Tip:</strong> Setting this to <code>No</code> is highly recommended when running a membership plugin like <a href="http://wordpress.org/plugins/s2member/" target="_blank">s2Member</a> (as one example). In fact, many plugins like s2Member will send <a href="http://codex.wordpress.org/Function_Reference/nocache_headers" target="_blank">nocache_headers()</a> on their own, so your configuration here will likely be overwritten when you run such plugins (which is better anyway). In short, if you run a membership plugin, you should NOT allow a client-side browser cache.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Tip:</strong> Setting this to <code>No</code> will NOT impact static content; e.g. CSS, JS, images, or other media. This setting pertains only to dynamic PHP scripts which produce content generated by WordPress.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Advanced Tip:</strong> if you have this set to <code>No</code>, but you DO want to allow a few special URLs to be cached by the browser; you can add this parameter to your URL <code>?qcABC=1</code>. This tells GD Booster that it\'s OK for the browser to cache that particular URL. In other words, the <code>qcABC=1</code> parameter tells GD Booster NOT to send no-cache headers to the browser.', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('GET Requests', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-question-circle fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Caching Enabled for GET (Query String) Requests?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('This should almost ALWAYS be set to <code>No</code>. UNLESS, you\'re using unfriendly Permalinks. In other words, if all of your URLs contain a query string (e.g. <code>/?key=value</code>); you\'re using unfriendly Permalinks. Ideally, you would refrain from doing this; and instead, update your Permalink options immediately; which also optimizes your site for search engines. That being said, if you really want to use unfriendly Permalinks, and ONLY if you\'re using unfriendly Permalinks, you should set this to <code>Yes</code>; and don\'t worry too much, the sky won\'t fall on your head :-)', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][get_requests]">'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['get_requests'], '0', FALSE).'>'.__('No, do NOT cache (or serve a cache file) when a query string is present.', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['get_requests'], '1', FALSE).'>'.__('Yes, I would like to cache URLs that contain a query string.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '      <p class="info">'.__('<strong>Note:</strong> POST requests (i.e. forms with <code>method=&quot;post&quot;</code>) are always excluded from the cache, which is the way it should be. Any <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html" target="_blank">POST/PUT/DELETE</a> request should NEVER (ever) be cached. CLI (and self-serve) requests are also excluded from the cache (always). A CLI request is one that comes from the command line; commonly used by CRON jobs and other automated routines. A self-serve request is an HTTP connection established from your site -› to your site. For instance, a WP Cron job, or any other HTTP request that is spawned not by a user, but by the server itself.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Advanced Tip:</strong> If you are NOT caching GET requests (recommended), but you DO want to allow some special URLs that include query string parameters to be cached; you can add this special parameter to any URL <code>?qcAC=1</code>. This tells GD Booster that it\'s OK to cache that particular URL, even though it contains query string arguments. If you ARE caching GET requests and you want to force GD Booster to NOT cache a specific request, you can add this special parameter to any URL <code>?qcAC=0</code>.', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('404 Requests', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-question-circle fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Caching Enabled for 404 Requests?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('When this is set to <code>No</code>, GD Booster will ignore all 404 requests and no cache file will be served. While this is fine for most site owners, caching the 404 page on a high-traffic site may further reduce server load. When this is set to <code>Yes</code>, GD Booster will cache the 404 page (see <a href="https://codex.wordpress.org/Creating_an_Error_404_Page" target="_blank">Creating an Error 404 Page</a>) and then serve that single cache file to all future 404 requests.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][cache_404_requests]">'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['cache_404_requests'], '0', FALSE).'>'.__('No, do NOT cache (or serve a cache file) for 404 requests.', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['cache_404_requests'], '1', FALSE).'>'.__('Yes, I would like to cache the 404 page and serve the cached file for 404 requests.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '      <p class="info">'.__('<strong>How does GD Booster cache 404 requests?</strong> GD Booster will create a special cache file (<code>----404----.html</code>, see Advanced Tip below) for the first 404 request and then <a href="http://www.php.net/manual/en/function.symlink.php" target="_blank">symlink</a> future 404 requests to this special cache file. That way you don\'t end up with lots of 404 cache files that all contain the same thing (the contents of the 404 page). Instead, you\'ll have one 404 cache file and then several symlinks (i.e., references) to that 404 cache file.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Advanced Tip:</strong> The default 404 cache filename (<code>----404----.html</code>) is designed to minimize the chance of a collision with a cache file for a real page with the same name. However, if you want to override this default and define your own 404 cache filename, you can do so by adding <code>define(\'GEODIR_GD_BOOSTER_404_CACHE_FILENAME\', \'your-404-cache-filename\');</code> to your <code>wp-config.php</code> file (note that the <code>.html</code> extension should be excluded when defining a new filename).', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('RSS, RDF, and Atom Feeds', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-question-circle fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Caching Enabled for RSS, RDF, Atom Feeds?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('This should almost ALWAYS be set to <code>No</code>. UNLESS, you\'re sure that you want to cache your feeds. If you use a web feed management provider like Google® Feedburner and you set this option to <code>Yes</code>, you may experience delays in the detection of new posts. <strong>NOTE:</strong> If you do enable this, it is highly recommended that you also enable automatic Feed Clearing too. Please see the section above: "Clearing the Cache". Find the sub-section titled: "Auto-Clear RSS/RDF/ATOM Feeds" (available only in the pro version).', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p><select name="'.esc_attr(__NAMESPACE__).'[save_options][feeds_enable]">'."\n";
			echo '            <option value="0"'.selected($this->plugin->options['feeds_enable'], '0', FALSE).'>'.__('No, do NOT cache (or serve a cache file) when displaying a feed.', $this->plugin->text_domain).'</option>'."\n";
			echo '            <option value="1"'.selected($this->plugin->options['feeds_enable'], '1', FALSE).'>'.__('Yes, I would like to cache feed URLs.', $this->plugin->text_domain).'</option>'."\n";
			echo '         </select></p>'."\n";
			echo '      <p class="info">'.__('<strong>Note:</strong> This option affects all feeds served by WordPress, including the site feed, the site comment feed, post-specific comment feeds, author feeds, search feeds, and category and tag feeds. See also: <a href="http://codex.wordpress.org/WordPress_Feeds" target="_blank">WordPress Feeds</a>.', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('GZIP Compression', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <img src="'.esc_attr($this->plugin->url('/client-s/images/gzip.png')).'" class="screenshot" />'."\n";
			echo '      <h3>'.__('<a href="https://developers.google.com/speed/articles/gzip" target="_blank">GZIP Compression</a> (Optional; Highly Recommended)', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('You don\'t have to use an <code>.htaccess</code> file to enjoy the performance enhancements provided by this plugin; caching is handled automatically by WordPress/PHP alone. That being said, if you want to take advantage of the additional speed enhancements associated w/ GZIP compression (and we do recommend this), then you WILL need an <code>.htaccess</code> file to accomplish that part.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('GD Booster fully supports GZIP compression on its output. However, it does not handle GZIP compression directly. We purposely left GZIP compression out of this plugin, because GZIP compression is something that should really be enabled at the Apache level or inside your <code>php.ini</code> file. GZIP compression can be used for things like JavaScript and CSS files as well, so why bother turning it on for only WordPress-generated pages when you can enable GZIP at the server level and cover all the bases!', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p>'.__('If you want to enable GZIP, create an <code>.htaccess</code> file in your WordPress® installation directory, and put the following few lines in it. Alternatively, if you already have an <code>.htaccess</code> file, just add these lines to it, and that is all there is to it. GZIP is now enabled in the recommended way! See also: <a href="https://developers.google.com/speed/articles/gzip" target="_blank"><i class="fa fa-youtube-play"></i> video about GZIP Compression</a>.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <pre class="code"><code>'.esc_html(file_get_contents(dirname(__FILE__).'/gzip-htaccess.tpl.txt')).'</code></pre>'."\n";
			echo '      <hr />'."\n";
			echo '      <p class="info" style="display:block;"><strong>Or</strong>, if your server is missing <code>mod_deflate</code>/<code>mod_filter</code>; open your <strong>php.ini</strong> file and add this line: <a href="http://php.net/manual/en/zlib.configuration.php" target="_blank" style="text-decoration:none;"><code>zlib.output_compression = on</code></a></p>'."\n";
			echo '   </div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('Theme/Plugin Developers', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-puzzle-piece fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Developing a Theme or Plugin for WordPress?', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('<strong>Tip:</strong> GD Booster can be disabled temporarily. If you\'re a theme/plugin developer, you can set a flag within your PHP code to disable the cache engine at runtime. Perhaps on a specific page, or in a specific scenario. In your PHP script, set: <code>$_SERVER[\'GEODIR_GD_BOOSTER_ALLOWED\'] = FALSE;</code> or <code>define(\'GEODIR_GD_BOOSTER_ALLOWED\', FALSE)</code>. GD Booster is also compatible with: <code>define(\'DONOTCACHEPAGE\', TRUE)</code>. It does\'t matter where or when you define one of these, because GD Booster is the last thing to run before script execution ends.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <hr />'."\n";
			echo '      <h3>'.__('Writing "Advanced Cache" Plugins Specifically for GD Booster', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('Theme/plugin developers can take advantage of the GD Booster plugin architecture by creating PHP files inside this special directory: <code>/wp-content/ac-plugins/</code>. There is an <a href="https://github.com/WebSharks/Quick-Cache/blob/000000-dev/quick-cache/includes/ac-plugin.example.php" target="_blank">example plugin file @ GitHub</a> (please review it carefully and ask questions). If you develop a plugin for GD Booster, please share it with the community by publishing it in the plugins respository at WordPress.org.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <p class="info">'.__('<strong>Why does GD Booster have it\'s own plugin architecture?</strong> WordPress loads the <code>advanced-cache.php</code> drop-in file (for caching purposes) very early-on; before any other plugins or a theme. For this reason, GD Booster implements it\'s own watered-down version of functions like <code>add_action()</code>, <code>do_action()</code>, <code>add_filter()</code>, <code>apply_filters()</code>.', $this->plugin->text_domain).'</p>'."\n";
			echo '   </div>'."\n";



			echo '<div class="plugin-menu-page-panel">'."\n";

			echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
			echo '      <i class="fa fa-gears"></i> '.__('MaxCDN Settings', $this->plugin->text_domain)."\n";
			echo '   </a>'."\n";

			echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
			echo '      <i class="fa fa-puzzle-piece fa-4x" style="float:right; margin: 0 0 0 25px;"></i>'."\n";
			echo '      <h3>'.__('Use MaxCDN for Content Delivery Network', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('<strong>Tip:</strong> GD Booster and MaxCDN can work very well together to help your site load faster from many locations around the world.', $this->plugin->text_domain).'</p>'."\n";
			echo '      <h3>'.__('<a href="//tracking.maxcdn.com/c/258301/3968/378">Click here For 25% off your first month with MaxCDN</a><img height="0" width="0" src="//tracking.maxcdn.com/i/258301/3968/378" style="position:absolute;visibility:hidden;" border="0" />', $this->plugin->text_domain).'</h3>'."\n";
			echo '      <p>'.__('<strong>Defaults:</strong> bmp|bz2|gif|ico|gz|jpg|jpeg|mp3|pdf|png|rar|rtf|swf|tar|tgz|txt|wav|zip|js|css', $this->plugin->text_domain).'</p>'."\n";
			echo '      <hr />'."\n";

			echo '<p><select name="'.esc_attr(__NAMESPACE__).'[save_options][cdn_enabled]">'."\n";
			$cdn_enabled = isset($this->plugin->options['cdn_enabled']) ? $this->plugin->options['cdn_enabled'] : 0;
			echo '<option value="0" '.selected($cdn_enabled, 0, FALSE).'>'.__('Disabled', $this->plugin->text_domain).'</option>'."\n";
			echo '<option value="1" '.selected($cdn_enabled, 1, FALSE).'>'.__('Enabled', $this->plugin->text_domain).'</option>'."\n";
			echo '</select></p>'."\n";

			echo '      <h3>'.__('CDN Root URL', $this->plugin->text_domain).'</h3>'."\n";
			echo '<p><input type="url" name="'.esc_attr(__NAMESPACE__).'[save_options][cdn_root_url]" value="'.esc_attr($this->plugin->options['cdn_root_url']).'" /></p>';

			echo '      <h3>'.__('CDN File Extensions', $this->plugin->text_domain).'</h3>'."\n";
			echo '<p><input type="text" name="'.esc_attr(__NAMESPACE__).'[save_options][cdn_file_ext]" value="'.esc_attr($this->plugin->options['cdn_file_ext']).'" /></p>';

			echo '   </div>'."\n";



			
			echo '<div class="plugin-menu-page-panel">'."\n";

				echo '   <a href="#" class="plugin-menu-page-panel-heading">'."\n";
				echo '      <i class="fa fa-gears"></i> '.__('JS/CSS Files Exclusion From Combines', $this->plugin->text_domain)."\n";
				echo '   </a>'."\n";

				echo '   <div class="plugin-menu-page-panel-body clearfix">'."\n";
				echo '      <h3>'.__('Excludes JS/CSS Files when combine in one file.', $this->plugin->text_domain).'</h3>'."\n";
				echo '      <p>'.__('Sometimes there are certain cases where a particular js/css file you don\'t want to combine. Please enter file names per line to exclude; e.g. <code>autocomplete.js</code>;<code>autocomplete.css</code> OR for same file names use <code>myplugin/js/script.js</code>;<code>myplugin/css/style.css</code>.', $this->plugin->text_domain).'</p>'."\n";
				echo '      <p><textarea name="'.esc_attr(__NAMESPACE__).'[save_options][exclude_combines]" rows="5" spellcheck="false" class="monospace">'.(isset($this->plugin->options['exclude_combines']) && $this->plugin->options['exclude_combines']!= '' ? $this->plugin->options['exclude_combines'] : '').'</textarea></p>'."\n";
				echo '      <p class="info">'.__('<strong>Note:</strong> please remember that each file name must be per line.', $this->plugin->text_domain).'</p>'."\n";
                echo '<hr /><h3>'.__('Max url length to split combined js/css urls', $this->plugin->text_domain).'</h3>'."\n";
                echo '<p>'.__('When GD Booster combines javascript file urls into one combined js url and css file urls into one combined css url, the combined url length becomes larger. Extremely long URLs are usually a mistake. URLs over 2,000 characters will not work in the most popular web browser. Although the specification of the HTTP protocol does not specify any maximum length of urls, practical limits are imposed by web browser and server software.', $this->plugin->text_domain).'</p>'."\n";
                echo '<p><select name="'.esc_attr(__NAMESPACE__).'[save_options][max_url_length]">'."\n";
                $max_url_length = (int)$this->plugin->options['max_url_length'] > 200 ? $this->plugin->options['max_url_length'] : 2000;
                echo '<option value="250" '.selected($max_url_length, 250, FALSE).'>'.__('250 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="500" '.selected($max_url_length, 500, FALSE).'>'.__('500 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="750" '.selected($max_url_length, 750, FALSE).'>'.__('750 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="1000" '.selected($max_url_length, 1000, FALSE).'>'.__('1000 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="1250" '.selected($max_url_length, 1250, FALSE).'>'.__('1250 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="1500" '.selected($max_url_length, 1500, FALSE).'>'.__('1500 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="1750" '.selected($max_url_length, 1750, FALSE).'>'.__('1750 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="2000" '.selected($max_url_length, 2000, FALSE).'>'.__('2000 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="2250" '.selected($max_url_length, 2250, FALSE).'>'.__('2250 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="2500" '.selected($max_url_length, 2500, FALSE).'>'.__('2500 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="2750" '.selected($max_url_length, 2750, FALSE).'>'.__('2750 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '<option value="3000" '.selected($max_url_length, 3000, FALSE).'>'.__('3000 Characters', $this->plugin->text_domain).'</option>'."\n";
                echo '</select></p>'."\n";
                echo '<p class="info">'.__('<strong>@see: </strong><a href="http://stackoverflow.com/questions/7724270/max-size-of-url-parameters-in-get#answers" target="_blank">www.stackoverflow.com/questions/7724270/max-size-of-url-parameters-in-get#answers</a> & <a href="http://www.boutell.com/newfaq/misc/urllength.html" target="_blank">www.boutell.com/newfaq/misc/urllength.html</a>', $this->plugin->text_domain).'</p>'."\n";
				echo '   </div>'."\n";

				echo '</div>'."\n";

			echo '</div>'."\n";

			echo '<div class="plugin-menu-page-save">'."\n";
			echo '   <input type="hidden" name="'.esc_attr(__NAMESPACE__).'[save_options][crons_setup]" value="'.esc_attr($this->plugin->options['crons_setup']).'" autocomplete="off" />'."\n";
			echo '   <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
			echo '</div>'."\n";

			echo '</div>'."\n";
			echo '</form>';
		}
	}
}