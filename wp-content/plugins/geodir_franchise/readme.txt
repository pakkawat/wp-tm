=== GeoDirectory Franchise Manager ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Integrates franchise service feature with GeoDirectory listings.

== Requirements ==

* 

== Changelog ==

= 1.0.7 = 
With default country enabled after saving the franchise, the "View franchise" contains wrong link - FIXED

= 1.0.6 =
Can't remove franchise option for the listing - FIXED

= 1.0.5 =
Update script updated to use WP Easy Updates - CHANGED

= 1.0.4 =
Fix issue for plugin installation via WP-CLI - FIXED

= 1.0.3 =
Options added to show/hide the main/franchise listing from franchises tab - ADDED
Email headers changed from string to array() and MIME-Version removed - CHANGED

= 1.0.2 =
If there are no franchise locked fields then the expire date not updated for franchises - FIXED
New custom fields are not displayed in locked fields list - FIXED
Should not loose previously saved settings when plugin is reactivated - CHANGED
Option added to remove plugin data on plugin delete - ADDED

= 1.0.1 =
WP_PLUGIN_URL not working correctly in https - FIXED

= 1.0.0 =
The price package should not display when adding franchise listing - FIXED

= 0.0.6 =
Sometimes pay for franchises button does not appear - FIXED
New franchise listings are immediately published even if the setting for new listings is draft - FIXED

= 0.0.5 =
Custom columns added in backend listing page to identify main and franchise listing - ADDED
Option added in price package to limit number of franchises that can be added - ADDED
Sessions managed by GeoDirectory Session class - CHANGED
Button added on add/edit franchise page to duplicate images from main franchise listing - ADDED
Some vulnerabilities security issues fixed - FIXED
function geodir_get_current_posttype called too early - FIXED

= 0.0.4 =
Display currency symbol position as it set in payment addon - CHANGED
Fieldset conflicts the franchise fields locking - FIXED
Custom css class added to post listing to differentiate main and franchise listings - ADDED
After franchise saving expire time doubled with compared to what user have in price settings - FIXED
On front end add franchise page the address field should not use value from map if address saved already - FIXED
Options added to show/hide main listing and franchise listings links on detail page sidebar - ADDED
On add franchise form should show message/error after submit franchise - CHANGED
New update licence system implemented - CHANGED

= 0.0.3 =
Added checks for core and payment manager installed - ADDED
Franchise feature should work without payment manager addon - CHANGED
Claim feature disabled for franchises listings - CHANGED
Some new filters added to customized franchises tab text - ADDED

= 0.0.2 =
Beta release.

= 0.0.1 =
Beta release.