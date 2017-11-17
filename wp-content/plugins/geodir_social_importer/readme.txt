=== GeoDirectory Social Importer ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.3.3 =
Added Facebook OAuth redirect URI that needs to be entered for new facebook apps - ADDED

= 1.3.2 =
Facebook updated to use API v2.9 - CHANGED
Stop import adding name as the first tag - CHANGED
Event videos depreciated from api version 2.10 - FIXED

= 1.3.1 =
New Yelp API v3 not working with older version of PHP - FIXED

= 1.3.0 =
Yelp API V2 depreciated V3 settings added - FIXED
Update script updated to use WP Easy Updates - CHANGED

= 1.2.73 =
Facebook Oauth api response changed which broke authentications - FIXED

= 1.2.72 =
JS error breaks set address on map if selected cities enabled - FIXED

= 1.2.61 =
PHP 5.3 compatibility fix - FIXED

= 1.2.6 =
More defined error messages shown on error - CHANGED
JS error due to apostrophe in city name breaks the import - FIXED
Option added to choose post type to be post to facebook - ADDED
Import from FB does not working with OpenStreetMap - FIXED 
Fix issue for plugin installation via WP-CLI - FIXED

= 1.2.5 =
Posts only posting to facebook automatically when a GD place is posted from frontend - FIXED
Added cover photo to the photos api request for facebook - ADDED

= 1.2.4 =
Hooks added for plugin activation & deactivation - ADDED
Option added to remove plugin data on plugin delete - ADDED
If cities are limited social importer can still add new city to list - FIXED

= 1.2.3 =
If social import city name has a space in it the import can partially fail - FIXED

= 1.2.2 =
If city missing from import it will use default city - FIXED

= 1.2.1 =
GD Core events format change can break event import custom recurring cal - FIXED
Event date fields sets invalid date after import event from facebook - FIXED
Facebook event importing wrong time - FIXED
Only import one video as more can slow down the page load - CHANGED

= 1.2.0 =
Yelp auth class file missing in some cases - FIXED
Some facebook page IDs only 11 digits when we check for 12 - FIXED

= 1.1.9 =
Post to facebook elements double escaped - FIXED
Import details link changed from span to button - CHANGED
Post to page list only shows 25 pages - FIXED

= 1.1.8 =
Language file path in plugin wrong - FIXED

= 1.1.7 =
New update licence system implemented - CHANGED

= 1.1.6 =
GD update script sometimes not verifying SSL cert - FIXED

= 1.1.5 =
Facebook changed the url structure of most pages breaking the import script - FIXED

= 1.1.4 =
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.1.3 =
Change for new facebook API 2.4 - CHANGED
Added more detailed error notices on failure - CHANGED
Changes made for WPML compatibility - CHANGED
Posting to facebook can't be disabled once set - FIXED
Importing new posts via CSV tries to post to facebook - FIXED
Added tool in GT>Tools to clear the option that stores the post IDs array - ADDED
Added button to post to facebook when adding or editing posts from the backend - ADDED

= 1.1.2 =
New facebook apps are forced to use new API version and break social connect - FIXED

= 1.1.1 =
Yelp OAuth class exists fatal error can be caused by class autoloaders - FIXED

= 1.1.0 =
sometimes causes JS error when editing a listing - FIXED

= 1.0.9 =
Function codeAddress changed to geodir_codeAddress for compatibility - CHANGED

= 1.0.8 =
Increased api timeouts from 5 seconds to 15 as some users were seeing timeouts - CHANGED
Fix for facebook API changes breaking authorisation for new users - FIXED

= 1.0.7 =
Facebook changed the way non approved permissions are assigned breaking post to facebook - FIXED
Sometimes links posted to Facebook don't contain the location details - FIXED

= 1.0.6 =
Import details not working if events addon not active - FIXED

= 1.0.5 =
Events dates not imported correctly - FIXED

= 1.0.4 =
added fix for OAuthException already declared - FIXED
Event times can be 1 hour wrong if on BST - FIXED

= 1.0.3 =
Street address can be changed from import address my change of marker position - FIXED

= 1.0.2 =
line breaks not working in visual editor - FIXED

= 1.0.1 =
plugin breaks with GD Booster enabled - FIXED

= 1.0.0 =
posting to facebook can post twice - FIXED
import from facebook does not fill tinyMCE visual editor - FIXED
import from facebook image limit not applied - FIXED
import description limit not applied - FIXED
import tag limit not applied - FIXED

= 0.0.3 =
Posting to facebook can cause 404, added slight delay to fix - FIXED

= 0.0.2 =
listings not posting to facebook page - FIXED

= 0.0.1 =
Initial release