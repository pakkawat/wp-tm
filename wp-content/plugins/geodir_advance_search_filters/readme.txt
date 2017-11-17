=== GeoDirectory Advance Search Filters ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.4.92
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.4.92 =
Ajax not working properly due to VARCHAR being posted on some servers - FIXED

= 1.4.91 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
Compass icon disappear when switching CPT from search form - FIXED
Mariadb uses rows as a keyword and causes an error - FIXED
Bug in showing autocomplete search results - FIXED
list tag missing in the price range custom fields outputs - FIXED
If autocomplete has long click held down it will not fill the result - FIXED

= 1.4.9 =
Search by hood issue if "Add location slug in listing urls" is unticked - FIXED
Near Me on search page does not clear when user location cleared - FIXED
Select custom field values not translating in advanced search form  - FIXED
Some caching plugins can cache the onload redirect on one page - FIXED

= 1.4.8 = 
Date fields shows incorrect date format in search form - FIXED
Show notice when PHP version is older than minimum required - CHANGED
Corrected the mistakes in hook names - FIXED

= 1.4.7 =
When user location is present in Near search box, changing CPT alters the value - FIXED
Location search fields should show translated country - FIXED
Advanced search date range format sometimes not working - FIXED
Category in main search bar not showing child cats under parent - FIXED
Search values with & not working with Link Output - FIXED
Fix issue for plugin installation via WP-CLI - FIXED
Changed language domain to "geodirectory" for "In:" text - CHANGED

= 1.4.6 =
Search Autocomplete can show prev CPT results div behind current - FIXED
Advance search title not translated by db translation - FIXED
New advance fields labels are added to db translation - CHANGED

= 1.4.5 =
Search post type now a global value - CHANGED

= 1.4.4 =
Upgrade table function sometime not run - FIXED
Advance search input CSS changes - CHANGED
You can now filter listings that have special offers - ADDED
Advanced search style changes - CHANGED
Category select can now be added to the main search bar - ADDED
Select, radio, checkbox, date and decimal custom fields can be added to the main search bar - ADDED
Option added to remove plugin data on plugin delete - ADDED
Near me range now updates while dragging not after drag end - CHANGED

= 1.4.3 =
PHP notice on save new custom field - FIXED
Event Search by date advanced custom field not showing date input - FIXED

= 1.4.2 =
Location filter feature added to filter results in search autocompleter - ADDED
Changes for core GD custom fields settings - CHANGED
Advanced search now has fieldset for titles and better default layout - CHANGED
Some generic CSS rules removed for better compatibility - REMOVED
WP_PLUGIN_URL not working correctly in https - FIXED

= 1.4.1 =
Custom classes added to the advance search box for the selected CPT - CHANGED
Stop advanced search button animation on first load only - CHANGED
NEAR search box does not show the current location when default country enabled and country hidden from url - FIXED

= 1.4.0 =
The JS message array can now be filtered - ADDED
The date picker not working for event dates search when multiple advance search form used on page - FIXED
Min Characters and Max results setting - ADDED
Option updated to redirect on first time load - CHANGED
geodir_filter_term_args filter added - ADDED

= 1.3.9 =
Compass active colour now able to be edited via hook - ADDED
OpenStreetMap integration added for maps - ADDED

= 1.3.8 =
Customize search form not working on page load - FIXED
Hook added to display title for searched variables - ADDED

= 1.3.7 =
ac_results div shows 1px in footer when no results are present - FIXED
Safari can throw a generic 'error' message, replaced with more accurate message - FIXED
If other CPT is first in advanced search widget, advanced search button not shown on homepage - FIXED

= 1.3.6 =
New $gd_session called too early when Near autocomplete is called - FIXED

= 1.3.5 =
Some search filters not being applied depending on other queries on page - FIXED

= 1.3.4 =
Sessions managed by GeoDirectory Session class - CHANGED
Filter added for search autocomplete search results geodir_advance_search_autocompleters - ADDED
There is a conflicts between GD CPT and non GD CPT queries - FIXED

= 1.3.3 =
Changes for the neighbourhood system improvement - CHANGED
Made few changes for W3C validation - CHANGED
Advanced search fields not showing if contain "tag" in htmlvar name - FIXED
Search form field labels and option values should be translatable - FIXED
New update licence system implemented - CHANGED

= 1.3.2 =
Selecting the exact match found in search autocompleter should take to the listing detail page - CHANGED

= 1.3.1 =
CPT advanced search button can be disabled if switching from and to CPT with advanced fields - FIXED

= 1.3.0 =
Advance search submit button fixed - FIXED
Advanced search now shows animation on every ajax action - ADDED

= 1.2.9 =
Option added for multiselect advanced search to search with OR or AND operator - ADDED
In listing search form spinner icon customized with font-awesome spinner - CHANGED
Added filter to modify placeholder text - ADDED
Changed input type to number for FROM type - CHANGED
Near by field works with location specific urls like /country/city/ & /region/city/ – CHANGED
case conversion functions replaced with custom function to support unicode languages - CHANGED
Use get_site_url() instead of home_url() - CHANGED

= 1.2.8 =
Advanced sort terms can not be reordered due to change in 1.2.7 - FIXED
Multiple cities/regions with same name do not appear in autocomplete suggestions of near field - FIXED
ac_results div z-index lower than menu items so is hidden in some occasions - FIXED
Unused css image classes removed - REMOVED

= 1.2.7 =
Conflict when using jQuery UI autocomplete - FIXED
Possible XSS vulnerability if advanced search labels activated - FIXED
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.2.6 =
Changes made for WPML compatibility - CHANGED
dbDelta function not running on version clear - FIXED

= 1.2.5 =
Spinner icon added while performing operation in advance search from - ADDED
dbDelta function used for db tables creation - CHANGED

= 1.2.4 =
frontend.min.js file empty (failed to minify) - FIXED

= 1.2.3 =
Option added to show/hide customise my search form - ADDED
Custom field with apostrophe sign not translated properly - FIXED
Autocomplete field "Search For" not working correctly when location is selected - FIXED

= 1.2.2 =
Search listings not working if Near Me text translated - FIXED
Blue dot on map and on mobile shows a doubled dot - FIXED
After changing CPT the collapsable advance search looks messed - FIXED
RADIO option added for Field Data Type - ADDED
In advance search for select data type categories options sort by alphabetically - CHANGED

= 1.2.1 =
More link not working correctly in front end customize my search - FIXED
Customize my search not loading all options on first load - FIXED
Not able to translate range text field placeholders in advance search form - FIXED
Conflicts with the visual composer's front end editor - FIXED

= 1.2.0 =
class .dropdown changed to .gd-dropdown for theme compatibility - CHANGED
class .clr changed to .gd-clr to avoid conflict with other plugin & theme style - CHANGED

= 1.1.9 =
Problem of enqueuing js/css files when plugin folder changed - FIXED

= 1.1.8 =
Search deselect script can cause JS error if element does not exist - FIXED

= 1.1.7 =
Option added to add optgroup for SELECT custom field - ADDED
Added customized deselect search options after search is performed - ADDED

= 1.1.6 =
Can cause problems with li.dropdown items disappearing on click - FIXED
User position will update every 30 seconds on page load - CHANGED

= 1.1.5 =
User marker not shown on map when selecting "Near me" if multilocations not installed - FIXED
First load location setting hidden if multilocations is not installed - CHANGED

= 1.1.4 =
Near: Me sometimes not clearing between searches - FIXED
Autocompleter can sometimes not work for new posts if SQL server time is different to WP time - FIXED
Near me limitation distance not showing km/miles values - FIXED

= 1.1.3 =
Reposition compass after images load to avoid missalignment - ADDED

= 1.1.2 =
if map is not on page when doing near search and selecting autocomplete, error is thrown - FIXED
CSS changes to help theme compatability and strange alignment issues - FIXED

= 1.1.1 =
check added to GT Tools for table custom_advance_search_fields - ADDED
compass icon for near search field can be out of position - FIXED

= 1.1.0 =
Check added to see if core GeoDirectory is active before loading the rest of the plugin - ADDED
Datepicker not working for "filter by date" when customize my search option for events putted on page more then one time - FIXED
If the search text box wasn't in used '' removed form page title - ADDED
Option added to display searched parameters with page title in design>search>Display searched parameters with title - ADDED

= 1.0.6 =
Database error fixed on listings search page - FIXED
Blank index.php added to each directory - ADDED

= 1.0.5 =
JS error of undefined variable on Non admin Geodirectory pages - Fixed



