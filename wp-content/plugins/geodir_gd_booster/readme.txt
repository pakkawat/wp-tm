=== GD Booster ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.8
Stable tag: 1.2.51
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

GT Booster wraps some of the smartest caching, compression and minifying methods available today for WordPress, modded to be 100% GeoDirectory compatible.

Just activate and see google pagespeed and load times dramatically improve!

IMPORTANT: Not to be used with other 3rd party caching plugins.

== Changelog ==

= 1.2.51 =
Flickr widget can be pushed to footer - FIXED

= 1.2.5 =
HTML5 Maps JS conflicts and breaks the image loading on page - FIXED
Problem with sites when multisite installed - FIXED
Fix Amazon associate widget script conflict - FIXED
Multisite and location manager can cause problems finding file locations - FIXED

= 1.2.4 =
LinkedIn share inline + external JS breaks Google map - FIXED
Jetpack files break jQuery when combining - FIXED
GD Booster does not make Images set via setting CDN compatible - FIXED
Update script updated to use WP Easy Updates - CHANGED

= 1.2.3 =
Some changed for some multisite setups not adding local files to booster script - FIXED
SiteOrigin CSS can cause problems with GD Booster - FIXED

= 1.2.2 =
Fix for beaver builder problem when editing page - FIXED
Improved caching for servers that have gzip enabled - CHANGED
text/template scripts break combine script - FIXED

= 1.2.1 =
Bug fixes PHP function swap for strtolower - FIXED

= 1.2.0 =
Now minify html if debugging is set to not show - CHANGED

= 1.1.9 =
Using CDN can break 1st image on a listing if edited from frontend - FIXED

= 1.1.8 =
Settings added to allow GDB to use MaxCDN to serve files - ADDED
Few changes to load OpenStreetMap JS api dynamically - ADDED
Revolution slider text size effected - FIXED

= 1.1.7 =
Html comments in inline javascript causes javascript error - FIXED

= 1.1.6 =
Option added to manage max url length to split long combined urls - CHANGED
GD Booster not working with backbone text/html templates - FIXED

= 1.1.5 =
Multinews theme breaks CSS, excluded multinews/css/print.css - FIXED
Fontawesome star rating can break further CSS - FIXED

= 1.1.4 =
bbpress.css breaks css on safari browser so is excluded by default now - FIXED
buddypress profile image upload not working - FIXED
woocommerce can break some JS combining files - FIXED
content_url() now used to get wp-content folder url - CHANGED
displaying GD email addresses end up at bottom of the page - FIXED
It causes problems due to inline css not maintaining order with the file style - FIXED

= 1.1.3 =
Split combined js/css urls if url characters exceeds 2000 characters - FIXED
@import can leave and extra ; breaking further css - FIXED
New update licence system implemented - CHANGED

= 1.1.2 =
Different fix applied for Divi css problem from last version - FIXED
case conversion functions replaced with custom function to support unicode languages - CHANGED
mb_strtolower replaced with strtolower - CHANGED

= 1.1.1 =
Latest Avada theme breaks WYSIWYG editor on add listing page - FIXED
Latest Divi style.css can cause problems - FIXED

= 1.1.0 =
Some servers think files with // are external - FIXED
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED
dashicons.min.css causing problems with invalid URL - FIXED

= 1.0.9 =
Inline javascript with /* <![CDATA[ */ breaks JS - FIXED
Google ads not working with GD Booster - FIXED
Added filter to the booster out for JS `gd_booster_booster_out_js` - ADDED
Added filter to the booster out for JS `gd_booster_out` - ADDED
S2member not compatible with GD Booster - FIXED
Added fix for this WP bug https://core.trac.wordpress.org/ticket/18525 - FIXED

= 1.0.8 =
Inline javascript with img src tag breaks JS - FIXED

= 1.0.7 =
added hook geodir_booster_script_continue - ADDED

= 1.0.6 =
Can't able to exclude one file when more then one files has same name - FIXED
JSON objects added using "application/ld+json" break JS - FIXED
added hook geodir_booster_external_check - ADDED

= 1.0.5 =
Problem with files that do not have a semicolon at the end - FIXED

= 1.0.4 =
Style problem with inline style in theme - FIXED

= 1.0.3 =
Minification scripts can show PHP warnings breaking script - FIXED
Has a problem to find files when file url start with // and not http - FIXED 

= 1.0.2 =
does not add JS to file if it contains src in the text even if its in the script or and iframe - FIXED
added fix for non closing JS files - FIXED
https file not found problem fixed - FIXED


= 1.0.0 =
First release