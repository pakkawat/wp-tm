=== GeoDirectory Custom Post Types ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.8
Stable tag: 1.3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.3.6 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
Location less CPT should hide map pinpoint on listings pages - FIXED

= 1.3.5 =
Post type slug should allow non english characters - CHANGED
DB Max index length variable added - ADDED

= 1.3.4 =
Link business field has incorrect CPT title - FIXED

= 1.3.4 =
On SSL enabled site GD Booster may cause problem if js/css included with WP_PLUGIN_URL - FIXED
JS error when going to delete custom post type - FIXED
Update script updated to use WP Easy Updates - CHANGED

= 1.3.3 =
Category url of location less CPT for WPML are broken - FIXED
Fix issue for plugin installation via WP-CLI - FIXED
Previous/Next post hooks no longer required - CHANGED

= 1.3.2 =
Unable to unlink linked post type - FIXED
Post excerpt character count option not working for linked cpt listings - FIXED
Linked businesses not always following the sort order of related listings settings - FIXED
Link business can not be linked from mobile devices - FIXED

= 1.3.1 =
Option added to remove plugin data on plugin delete - ADDED
Changes for advanced search hide CPT near me field if locationless - CHANGED

= 1.3.0 =
Filter added to exclude post title and description from link business - ADDED
CPT name added to CPT Listings widget - ADDED

= 1.2.9 =
Linked listings get displayed in listview - FIXED
Prev/Next lInks not ordered by submit time properly - FIXED
If CPT meta description set then we use that instead of titles and meta description - CHANGED
If CPT description set and not default then we show it on CPT root page - CHANGED

= 1.2.8 =
Feature added to link any two post types. - ADDED
dbDelta not working properly for new columns - FIXED
Add post type page layout can be changed to simple mode / advanced mode - CHANGED

= 1.2.7 =
Some fixes related to changes to the Term Edit Page in WordPress 4.5 - CHANGED

= 1.2.6 =
In CPT listings widget now able to set width in % or auto - CHANGED
Function geodir_cpt_listing_page_title() depreciated - CHANGED
Validating and sanitizing changes - FIXED

= 1.2.5 =
New update licence system implemented - CHANGED

= 1.2.4 =
CPT tags and category labels using CPT rather than CPT name - CHANGED

= 1.2.3 =
Shortcode added for CPT listings widget - ADDED
Post type archive link not working when "add city slug in listing urls" for non location post type - FIXED

= 1.2.2 =
Case conversion functions replaced with custom function to support unicode languages - CHANGED
GD update script sometimes not verifying SSL cert - FIXED

= 1.2.1 =
Added option to store post content revisions (not custom fields) - ADDED
White space at the end of geodir_cp_template_tags.php breaking some site maps - FIXED
CTP change to CPT in GD tools page - CHANGED

= 1.2.0 =
GD auto update script improved efficiency and security(https) - CHANGED
Changed the function that makes sure locationless CPT has not location in URL - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.1.9 =
All widgets changed from PHP4 style constructors to PHP5 __construct, for WordPress 4.3 - CHANGED
Changes made for WPML compatibility - CHANGED

= 1.1.8 =
Post tags has a DB limit of 254, removed this limit - FIXED
Changes for WPML custom posts slug translation fix - FIXED
Some PHP notices resolved - FIXED
Map tab showing on preview page on locationless CPT - FIXED

= 1.1.7 =
In CPT add/edit form field description of listing slug updated - CHANGED
dbDelta function used for db tables creation - CHANGED

= 1.1.6 =
Option and filter added to exclude CPT from the CPT widget - ADDED
fix for geodir_single_next_previous_fix() function - FIXED
Added option to disable physical location requirement per post type - ADDED

= 1.1.5 =
Prev/Next function checking for post_type when not needed - CHANGED
Widget added to list CPT types with image - ADDED

= 1.1.4 =
Prev/Next links can show attachments instead of posts - FIXED

= 1.1.3 =
Small textdomain fix - FIXED

= 1.1.2 =
Listing appear in wrong location if region and city have the same name - FIXED

= 1.1.1 =
prev/next links on details page can show link to original post - FIXED

= 1.0.8 =
WordPress multisite compatibility - ADDED

= 1.0.7 =
Customize text label display for tab in post detail page for Custom Post Types - ADDED
Option added to disable "show_in_nav_menus" for posts, categories and tags of CPT - ADDED
Blank index.php added to each directory - ADDED

= 1.0.5 =
Add option to hide latitude and longitude boxes from front end (just hide not remove) add this option to the place settings field for address. - ADDED
Next/Prev buttons on post will not stick to their own post type - ADDED

= 1.0.4 =
Fixed grammatically mistake of a delete confirmation message - Fixed
when delete post type also delete its terms from db.
Translated string can be entered in posttype name field - fixed
If there is ' or " (Single or double) quote in custom post type name or singular name , it appears with \ everywhere on the site - FIXED