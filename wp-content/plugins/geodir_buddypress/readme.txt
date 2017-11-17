=== GeoDirectory BuddyPress Integration ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.8
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The GeoDirectory BuddyPress Integration plugin allows you to integrate GeoDirectory with BuddyPress. 

== Requirements ==

* GeoDirectory plugin
* BuddyPress plugin

== Changelog ==

= 1.2.1 =
GD user favs function updated - CHANGED

= 1.2.0 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
Load GD core textdomain before WPML load breaks the translation - FIXED
WPML v3.2+ compatibility changes - CHANGED

= 1.1.9 =
Can not able to delete plugin when BuddyPress is not active - FIXED
AYI support added - ADDED
Language file improvement - CHANGED
show only future events if the member is not author - CHANGED

= 1.1.7 =
Update script updated to use WP Easy Updates - CHANGED

= 1.1.6 = 
Login redirection not working properly when WPML installed - FIXED
Some WPML compatibility changes - FIXED
Fix issue for plugin installation via WP-CLI - FIXED

= 1.1.5 =
Custom post types labels are not translated in BuddyPress member menu - FIXED

= 1.1.4 =
Multirating addon strips author link from reviews - FIXED
gdbp_comment_meta_before and gdbp_comment_meta_after hooks - ADDED
Option added to remove plugin data on plugin delete - ADDED

= 1.1.3 =
Options added to manage default layout, no of listings and content excerpt - ADDED
WP_PLUGIN_URL not working correctly in https - FIXED

= 1.1.2 =
Added check if BuddyPress is active - ADDED

= 1.1.1 =
Reviews integration not working without multi rating addon - FIXED

= 1.1.0 =
Fixed conflicts between bp profile reviews and woocommerce comments - FIXED
Expire date not displayed on bp user dashboard listings page - FIXED
Filter added to modify review avatar size - ADDED

= 1.0.9 =
Buddypress activity tracking having a problem for a reviews - FIXED
New update licence system implemented - CHANGED
Tabs labels are not translated due to missing gdbuddypress textdomain - FIXED

= 1.0.8 =
Comments on listing activity should not taken as a listing reviews - FIXED
case conversion functions replaced with custom function to support unicode languages - CHANGED

= 1.0.7 =
New login page now compatible with buddypress registration form for redirect option - CHANGED
Option added to link blog author page to buddypress profile page - ADDED

= 1.0.6 =
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED
Show featured image in activity for CPT does not work - FIXED

= 1.0.5 =
Option added to show featured image in activity for new listing submitted - ADDED
Load plugin files only when BuddyPress active - ADDED
Changes made for WPML compatibility - CHANGED

= 1.0.4 =
Some PHP notices resolved - FIXED

= 1.0.3 =
Option for login redirect added - ADDED
Filter added for favourite text 'gdbuddypress_favourites_text' - ADDED
Docblocks - ADDED

= 1.0.2 =
Checked for XSS vulnerabilities as per latest WP security update for add_query_arg(), vulnerability found if using category sort_by options - SECURITY UPDATE


= 1.0.1 =
Buddypress profile menu not translated - FIXED

= 1.0.0 =
Initial release