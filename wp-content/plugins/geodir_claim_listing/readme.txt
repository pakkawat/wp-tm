=== GeoDirectory Claim Manager ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.8
Stable tag: 1.3.22
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.3.22 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
Claim listing invisible can show wrong error message - FIXED

= 1.3.21
gd-claim-link class added to claim link - CHANGED

= 1.3.2 =
Update script updated to use WP Easy Updates - CHANGED

= 1.3.1 =
Listing displays as a owner verified when claimed has NULL value - FIXED
WMPL claim listing email is now uses WPML links - FIXED
WPML duplicate listing should redirect to the original post for claim - CHANGED
When claim a listing the price packages should be filtered based on current listing categories - FIXED
Fix issue for plugin installation via WP-CLI - FIXED
Confirmation link is broken if link sent from WPML language - FIXED

= 1.3.0 =
Email headers changed from string to array() and MIME-Version removed - CHANGED
Author name not get displayed - FIXED

= 1.2.9 =
After deactivate and reactivate the saved options are lost - FIXED
Option added to remove plugin data on plugin delete - ADDED
Business claim popup disappears after login - FIXED
Some changes for WPML - FIXED

= 1.2.8 =
Sign in to claim message can show twice - FIXED
System should send only one verification email when auto approve claim listing is enabled - CHANGED

= 1.2.7 =
Emails not translatable via po file - FIXED
Claim form changed to placeholders and html5 validation - CHANGED

= 1.2.6 =
Misspells fixed in claim listing notification messages - FIXED

= 1.2.5 =
WPML compatibility change for popup - CHANGED

= 1.2.4 =
Validating and sanitizing changes  - FIXED

= 1.2.3 =
New update licence system implemented - CHANGED
Claim emails sending to address in headers twice - FIXED

= 1.2.2 =
New actions added to manage claim request status change - ADDED
Show message on login page when guest user click on claim link and after login claim form should auto popup - CHANGED

= 1.2.1 =
Email address with .coop TLD not validated - FIXED
case conversion functions replaced with custom function to support unicode languages - CHANGED
Use get_site_url() instead of home_url() - CHANGED

= 1.2.0 =
Login link updated to use geodir_login_url() function - FIXED

= 1.1.9 =
Some urls updated to use the new gd-info page - CHANGED

= 1.1.8 =
GD auto update script improved efficiency and security(https) - CHANGED
Option to force payment added during claim process if prices and payments installed - ADDED
Changed textdomain from defined constant to a string - CHANGED

= 1.1.7 =
Undefined variable in language.php - FIXED

= 1.1.6 =
Changes made for WPML compatibility - CHANGED

= 1.1.5 =
Added CSS class to author link on details sidebar - ADDED

= 1.1.4 =
dbDelta function used for db tables creation - CHANGED

= 1.1.3 =
Slashes not stripped from message of claim listing email - FIXED

= 1.1.2 =
New hook added for author link of verified listings page - ADDED

= 1.1.1 =
New hook actions added in claim listing form - ADDED

= 1.1.0 =
WordPres multisite compatability - ADDED

= 1.0.6 =
Blank index.php added to each directory of plugin - ADDED

= 1.0.5 =
When CPT addon installed the dropdown select can be hide some PT becasue of overflow - FIXED
A Notice in backend when claim manager is on and try to Edit a wordpress comment on any geodirectory post type - Fixed