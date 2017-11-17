=== GeoDirectory Location Manager ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.5.62
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.5.62 =
Load more not working for ajax loaded content in location switcher shortcode - FIXED

= 1.5.61 =
Added version check for advanced search which can cause error if not updated - FIXED

= 1.5.60 =
Hook added to customize Everywhere text in location description - ADDED
Error in location + category sitemap xml - FIXED
WPML v3.2+ compatibility changes - CHANGED
Problem in merging locations for location less CPTs - FIXED
Blank search results in 500 error on PHP 7.1.x - FIXED
Option added in location neighbourhood widget/shortcode to use viewing CPT in links - CHANGED
Searching when in a location when advanced search is not active does not limit search to that location - FIXED
Fix country translation during merge locations - FIXED
Add listing autocomplete now restricted by country choice - CHANGED
Added option to disable location terms count on post save - ADDED

= 1.5.50 =
Search by hood issue if "Add location slug in listing urls" is unticked - FIXED
Location seo meta tagline can be translated via WPML string translation - CHANGED
Map ajax now sends location info to bust some caching problems - FIXED
Yoast SEO XML sitemap results in timeout for large data - CHANGED
CPT > Categories not hiding empty categories on details page - FIXED
Option added in CPT custom field settings address section to show/hide neighbourhood in address - ADDED

= 1.5.40 =
Set location code restructured to fix broken location links - FIXED
Backward compatibility for multibyte string functions - CHANGED
Added change for Canarias islands to use Canarias as region name - CHANGED
Removed Yoast WPSEO deprecated functions - CHANGED
In add listing not able to add new city/region on mobile devices - FIXED
Location less listings shows 404 error - FIXED
Category top description not shows correct description when default country is on - FIXED
Allow to import/export category + location descriptions - ADDED
Pull neighbourhood if Google provides it in address response - ADDED
Added fix for Czechia regions not returning from google maps api - FIXED
Allow to translate location SEO meta titles, meta descriptions & location descriptions via WPML string translation - ADDED
Fix Tax Meta Class conflicts - FIXED
Update script updated to use WP Easy Updates - CHANGED

= 1.5.31 =
Categories permalinks bug - FIXED

= 1.5.3 =
Location terms count does not counted properly with WPML installed - FIXED
Fix ISO2 for translated country - FIXED
Neighbourhood page showing wrong meta description - FIXED
Field added to set seo meta title for the neighbourhood - ADDED
Homepage should go to current location not working when WP installed on directory - FIXED
The region set incorrectly for the translated country - FIXED
Denmark region code added to resolve regions better - ADDED
Geocode address add listing page on FireFox can cause ZERO_RESULTS in some circumstances - FIXED
Category top description added for country & regions - ADDED
New title variables added to display country/region/city/neighbourhood - CHANGED
Strip slashes added to neighbourhood names - FIXED
Neighbourhoods tab not showing in location switcher when default city enabled - FIXED
Neighbourhood widget does not show neighbourhoods when default city enabled - FIXED
Archive vs detail page problem when listing name and location name are unique - FIXED
Sometimes city field becomes blank, when non English map is selected - FIXED
Loadmore feature added for [gd_location_switcher] shortcode locations - CHANGED
The translated country creates new slug - FIXED
Fix issue for plugin installation via WP-CLI - FIXED
WMPL switch language looses the current location terms - FIXED
Previous/Next post hooks no longer required - CHANGED

= 1.5.2 =
Translate countries creates the duplicate countries - FIXED
Setting for changing add listing form map/marker/address only applying to frontend form - FIXED
DB SEO meta column names changed - CHANGED
Location SEO meta title field added - ADDED
WPSEO will now use location meta title/desc if present and fall back to location page seo title - CHANGED
Geolocate function does not take into account do not show X in url paths - FIXED

= 1.5.1 =
Neighbourhood urls not throwing 404 after being deleted - FIXED
Use of wpautop() is messing up the location description - FIXED
Address autocompleter does not working with non default language when WPML is active - FIXED
Should not loose previously saved settings when plugin is reactivated - CHANGED
Option added to remove plugin data on plugin delete - ADDED
Price package manager description limit can affect the cat description - FIXED

= 1.5.0 =
Details table limiting neighbourhood name changed to 100 char - FIXED
Categories page doesn't showing correct listings for neighborhood - FIXED
Duplicate page slug creates problem for WPML translation - FIXED
GD tool "Location category counts" doesn't updates location terms for each WPML language - FIXED
Pagination options added in popular location widget settings - ADDED
Importing locations with same city name breaks the import location process - FIXED

= 1.4.9 =
If country slug hidden some region category counts can be 0 - FIXED
Option added to manage meta title & description on neighbourhood pages - ADDED
Prev/Next lInks not ordered by submit time properly - FIXED

= 1.4.8 =
If page has items such as image that throw 404 it can reset location data - FIXED
Location drill down arrows can be hidden for first item - FIXED
Added tool to refresh the location category counts via ajax - ADDED
WPML copy content to new translation JS error  - FIXED
Changes for Turkey regions - FIXED
On first load show results in home page setting changed and moved in advance search addon - FIXED

= 1.4.7 =
Home page map does not filters listing markers when current location is neighbourhood - FIXED
Yoast SEO v3.2.x sitemap does not shows Last Modified date - FIXED
Large number of categories slow down the add listing page - FIXED
Search for country can cause errors if country name contains apostrophe - FIXED
OpenStreetMap integration added for maps - ADDED

= 1.4.6 =
Some fixes related to changes to the Term Edit Page in WordPress 4.5 - CHANGED
Location name with apostrophe(') adds "\" before apostrophe - FIXED

= 1.4.5 =
Neighbourhood field should be independent from the set address on map button - CHANGED
Function geodir_get_location_seo() added to get location SEO from DB - ADDED
Added filter for after location switcher ul tag - ADDED
Import/export neighbourhoods data via GD import/export - ADDED
Location description widget not working on location page if urls not set to use location info - FIXED
In location SEO setting the default image can be shown for following locations - FIXED
Some PHP7 compatibility changes - CHANGED

= 1.4.4 =
The neighbourhood system is improved - CHANGED
Site in subfolder and url to main domain can break location links - FIXED
Made few changes for W3C validation - CHANGED
WooCommerce shop page can sometimes show no products - FIXED
New update licence system implemented - CHANGED
Function geodir_get_limited_country_dl() fails to select translated country names - FIXED
Location filter added in back-end post type listing pages - ADDED
Options added to exclude location pages from Yoast SEO XML sitemap - CHANGED
Image and tagline option added to SEO page - ADDED
[gd_current_location_name] shortcode should display "Near Me" when near me location is selected - CHANGED
Confirm message text displayed on delete location updated - CHANGED
Option added to import/export location data via GD import/export - ADDED
Sessions managed by GeoDirectory Session class - CHANGED
Validating and sanitizing changes - FIXED

= 1.4.3 =
Settings Region -> Enable Selected Regions the countries listings page shows 404 not found - FIXED
Select Cityx renamed to Select City - FIXED
Location/me sometimes returns no map results - FIXED
location/me not setting as GD page - FIXED
Blog paged link can contain location info - FIXED
Added rewrite rules for details page comments paging - ADDED
If GD is not set as homepage the location is not reset when visiting homepage - FIXED
Now able to generate sitemap links for location pages via Yoast SEO plugin - ADDED

= 1.4.2 =
case conversion functions replaced with custom function to support unicode languages - CHANGED
Homepage page_id query var not set and can cause problems with Yoast SEO - FIXED
If default country set, it can set a session on non location urls - FIXED
Manage seo settings customized by using pagination and search filter - CHANGED

= 1.4.1 =
Locations with apostrophes can cause problems with locations when adding listings - FIXED
Some error pages not using geodir_login_url() as redirect - FIXED
City/region values goes blank in listing form when clicking go back and edit on the preview page - FIXED
Settings added to hide country/region part from urls – ADDED
Fixed term data count for multiple location names with same name - FIXED
Fix dbDelta sql for upgrades - FIXED
Limiting location on add listing page not throwing errors sometimes - FIXED
Trailing slash add for home_url() and site_url() urls using trailingslashit() - CHANGED

= 1.4.0 =
Added filter, geodir_location_description to be able to filter the location description text - ADDED
Show 404 page not found on location page if location not exists - CHANGED
Limiting countries and having them translated can cause duplicate location entries - FIXED
Changing location switcher tab loads all locations and then continues to ajax load them again - FIXED
Fixed conflict canonical url on location pages with Yoast WordPress SEO plugin - FIXED
Location is now automatically unset if user searches a "near" location that is not a autosuggestion of actual location - CHANGED

= 1.3.9 =
Some servers can interpret the merge fields ajax call as json which means the submit button is never shown - FIXED
GD auto update script improved efficiency and security(https) - CHANGED
If page set as blog page WPML page_id being set will break page for translation - FIXED
Geo-locate user now works if default location is set to load on first load - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.3.8 =
Filters added in reviews count query - CHANGED
All widgets changed from PHP4 style constructors to PHP5 __construct, for WordPress 4.3 - CHANGED
`Select Neighbourhood` missing textdomain and not translatable - FIXED
In some circumstances the location switcher can have one location not selectable - FIXED
Added filter to filter the default location tab when no location selected - ADDED
Changes made for WPML compatibility - CHANGED

= 1.3.7 =
Function geodir_get_current_location() made much more efficient when being called multiple times on one page - CHANGED
Set location not working correctly when only city added to place urls - FIXED
Some docblocks added - ADDED

= 1.3.6 =
dbDelta function used for db tables creation - CHANGED

= 1.3.5 =
Added option to stop add listing map pin move from changing the address - ADDED
In backend searching for location not redirecting properly - FIXED
Pagination not working in backend manage location - FIXED
Function codeAddress changed to geodir_codeAddress for compatibility - CHANGED

= 1.3.4 =
fix for geodir_single_next_previous_fix() function - FIXED
Pagination and filter option added in admin manage location page - ADDED
Checked for XSS vulnerabilities as per latest WP security update, found none but updated the code to new standards - SECURITY
New filter added for count location terms - ADDED

= 1.3.3 =
term description sometimes not showing - FIXED
Country/Region/City add listing page titles not translatable form .po file - FIXED

= 1.3.2 =
Location specific category counts can be wrong/not updated correctly - FIXED

= 1.3.1 =
Popular category link not working with ajax - FIXED
Prev/Next function checking for post_type when not needed - CHANGED

= 1.3.0 =
Location/me page can loop when GD Booster is installed - FIXED
Near me button widget title can add slash in front of apostrophe - FIXED
After clicking near me button value on search page displays "1" when no advanced search - FIXED
Prev/Next links can show attachments instead of posts - FIXED

= 1.2.9 =
Location switcher can show wrong locations when drilling down if similar country names present - FIXED
Add listing page address labels get reset to default on upgrade - FIXED
Near me button widget not working if advanced search is not installed - FIXED

= 1.2.8 =
Prefixed all shortcodes with gd_ - CHANGED

= 1.2.6 =
Listing appear in wrong location if region and city have the same name - FIXED
added change to allow address autocomplete work with add-listing shortcode - FIXED
Added more shortcodes and fixed the ones that were there - ADDED

= 1.2.5 =
Location selector will now do split word search (you can search 'kingdom' for 'united kingdom' now)- CHANGED
added more class filters for location switcher for menu - ADDED
Show default location results on home page now working - FIXED

= 1.2.4 =
changed $ to jQuery in some scripts for compatibility - CHANGED

= 1.2.3 =
added filter to add class to location switcher menu item (required for X theme) - ADDED

= 1.2.2 =
prev/next links on details page can show link to original post - FIXED

= 1.2.1 =
extended mobile location switcher alternative to iPad - FIXED

= 1.2.0 =
Removed the need for shortcode option of autoredirect on location shortcode - CHANGED
Option to List all Countries, Regions, Cities in location switcher now working - FIXED
Check added to see if core GeoDirectory is active before loading the rest of the plugin - ADDED
Location switcher not working on avada or themes where mobile menu is auto generated - FIXED
Url redirect problem for crawler if location url has not trailing slash - FIXED
Ajax search not working in location tab switcher in mobile device - FIXED
WordPress multisite compatibility - ADDED
Country translate page, added instructions - ADDED
added option to show all location in add listing dropdown - ADDED
added option to stop "set address on map" from changing address fields on add listing page - ADDED

= 1.1.3 =
Unique category description for each location seems to be displayed depending on SESSION which is not good as crawlers will not see the correct description - FIXED
Added ability to correct region data from google api - ADDED
Slovakian regions array added - ADDED
Location url prefix meta title "Location" not translated - FIXED
Little fix when displaying listings by neighbourhood - FIXED
Location switcher shortcode doesn't redirect if placed in a sidebar - FIXED
Added translation for region & city in breadcrumb - ADDED