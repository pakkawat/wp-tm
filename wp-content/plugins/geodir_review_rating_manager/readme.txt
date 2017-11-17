=== GeoDirectory Review Rating Manager ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.3.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.4.0 =
Show validation message for empty review/reply text - CHANGED
Rating images can be off if non standard 5 rating not used - FIXED

= 1.3.9 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
WPML v3.2+ compatibility changes - CHANGED

= 1.3.8 =
Detail page reviews pagination not working - FIXED

= 1.3.7 =
Update script updated to use WP Easy Updates - CHANGED

= 1.3.6 =
Synchronize the post reviews if "Synchronize comment on duplicate content" is set - CHANGED
Fix issue for plugin installation via WP-CLI - FIXED

= 1.3.5 =
Font awesome rating validation not working - FIXED

= 1.3.4 =
Should not loose previously saved settings when plugin is reactivated - CHANGED
Option added to remove plugin data on plugin delete - ADDED

= 1.3.3 =
If one comment is approved other non approved comments can be seen - FIXED

= 1.3.3 =
WP_PLUGIN_URL not working correctly in https - FIXED

= 1.3.2 =
Rich snippet info removed as now added by core - CHANGED

= 1.3.1 =
Fix lightbox slideshow for reviews images & minor style fix - FIXED

= 1.3.0 =
Minor typing error fix - FIXED

= 1.2.9 =
$geodir_post_type sometimes not defined - FIXED

v.1.2.8
Like option should working with user_id only - CHANGED
Like option images replaced with font awesome icon - CHANGED
Font Awesome settings for rating images moved under GD core - CHANGED

= 1.2.7 =
Made few changes for W3C validation - CHANGED
Assign rating style to cat not saving all cats - FIXED
Clearing version numbers not firing dbDelta - FIXED
New update licence system implemented - CHANGED

= 1.2.6 =
Font Awesome support added for rating images - ADDED
Font awesome icon color setting - ADDED
Multirating count can be affected by reply comments - FIXED
Font awesome icon font size issue - FIXED

= 1.2.5 =
Some new hooks added for ratings - ADDED

= 1.2.4 =
Some multrating images not responsive - FIXED
Login link updated to use geodir_login_url() function - FIXED
With multi ratings the comment sorting is reversed then selected under settings -> discussion - FIXED
Review image max upload size now uses the GD max upload file size setting - CHANGED
When comment sort order used new user comments can't see their unapproved comments - FIXED
case conversion functions replaced with custom function to support unicode languages - CHANGED
Should not hide default rating if multirating for comment on post is disabled - FIXED
Added some filters to use font awesome rating icons as images - ADDED

= 1.2.3 =
wp cron added to clear orphaned images - ADDED
Unused css image classes removed - REMOVED

= 1.2.2 =
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.2.1 =
Changes made for WPML compatibility - CHANGED

= 1.2.0 =
Compatibility changes for whoop theme - ADDED
Changed the remove filter priority for core plugin change in priority - CHANGED
Overall rating title wrapped inside span - ADDED

= 1.1.9 =
"alt" attribute added to all star rating images - FIXED
Multiple ratings not showing on other languages with WPML - FIXED
dbDelta function used for db tables creation - CHANGED
Compatibility changes for whoop theme - ADDED

= 1.1.8 =
Saving admin pages not redirecting to original page - FIXED
Sorting comments strings are not translatable - FIXED
Option added to disable mandatory select stars for multiratings - ADDED

= 1.1.7 =
Sorting comments on listing detail page not working - FIXED
Checked for XSS vulnerabilities as per latest WP security update, found none but updated the code to new standards - SECURITY
Option added to customize rating star image & style color for featured listings - ADDED
On sorting comment the comment text goes disappeared - FIXED

= 1.1.6 =
Rating star label are now translatable using GD tool - FIXED

= 1.1.5 =
Custom ratings average not updated on comment post, only on comment approve - FIXED

= 1.1.4 =
Star rating images have a line at the end on safari (fixed with jQuery) - FIXED
Photo count text in review not translatable - FIXED

= 1.1.3 =
Comment image has hard coded url path - FIXED

= 1.1.2 =
Like comment count not working with new DB structure - FIXED
Comment sorting and average review info can sometimes not show above reviews - FIXED
comment sorting broken due to new DB structure - FIXED
Added warning about disabling star summery in sidebar will break Google rich snippets - ADDED

= 1.1.1 =
changed functions to deal with new core DB ratings structure - CHANGED

= 1.0.7 =
If post types share same category names custom ratings can be shown on wrong post type - FIXED
Check added to see if core GeoDirectory is active before loading the rest of the plugin - ADDED
Before submitting review it must required to select rating star - ADDED
In overall rating text and custom rating styles text if uses commas, quotation marks(',") it breaks the values - FIXED
In edit custom rating styles translation added for text "Star Text" - FIXED
Rating stars have a style issue in Safari - FIXED
With the MulitRatings plugin activated the sidebar rating stars not displays rating summary - FIXED
Option added to show/hide rating summary on detail page sidebar - ADDED
WordPress multisite compatibility - ADDED

= 1.0.6 =
Multiple media images upload in reviews not working - FIXED
Disable GD modal for comment images, can be disabled via GD core settings - ADDED 
If star rating is other then 5 then rating stars not display correctly - FIXED
Google rich snippets for reviews - ADDED
If the review form gets moved out of the tabs, the drag and drop image upload stops working and the css to highlight stars not working - FIXED