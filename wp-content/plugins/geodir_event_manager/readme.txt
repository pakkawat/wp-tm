=== GeoDirectory Events ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 1.4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 1.4.8 =
Recently happened events should be displayed first in listing page past events - CHANGED
Event recurring end date shows incorrect date if event date end selected before - FIXED
GD user favs function updated - CHANGED
View all link should go to search page with near me selected - CHANGED

= 1.4.7 =
Don't translate GD post types/taxonomies if disabled in WPML settings - CHANGED
WPML v3.2+ compatibility changes - CHANGED
geodir_date() function not working for already translated date - FIXED
Popular post view widget now adds "upcoming" sort by for events - ADDED
Group the recurring events in search proximity results - CHANGED
Event related listing query speed improved - CHANGED

= 1.4.6 =
AYI widget functions added - ADDED
Same day events should just show date and from - to time - CHANGED
DB Max index length variable added - ADDED

= 1.4.5 =
Event listing widget featured sort now secondarily ordered by event date - CHANGED
On SSL enabled site GD Booster may cause problem if js/css included with WP_PLUGIN_URL - FIXED
Event start & event end fields shows incorrect date format in search form - FIXED
Show notice when PHP version is older than minimum required - CHANGED
Event calendar widget ids conflicts with custom page builder - FIXED
Clicking on event calendar date loses the current location filter - FIXED
It allows to search events between two dates - CHANGED
Added option to allow users to link any business not just their own - ADDED
Added more event schemas for categories - ADDED
Fix Tax Meta Class conflicts - FIXED
Update script updated to use WP Easy Updates - CHANGED

= 1.4.4 =
When WP Super Cache installed, JS error breaks event calendar in add event page - FIXED
Fix issue for plugin installation via WP-CLI - FIXED

= 1.4.3 =
For weekly events "Repeat every" & "Repeat on" options not working as expected - FIXED
Event tags page event filter can create 404 pages if the tag name is the same as a location - FIXED
Dummy data tab converted to new system from core tab - CHANGED
WPML bug with related posts on events page - FIXED
Location filter added to the calendar widget - ADDED
Related listing widget should not show past events - CHANGED 

= 1.4.2 =
Should not loose previously saved settings when plugin is reactivated - CHANGED
Option added to remove plugin data on plugin delete - ADDED

= 1.4.1 =
The date format d/m/Y not working when going for Go Back & Edit event - FIXED
Use geodir_date() function for date format conversion - CHANGED

= 1.4.0 =
Event Search by date advanced custom field not showing date input - FIXED

= 1.3.9 =
Filter added to exclude post title and description from link business - ADDED
Changes to event settings install for new custom fields setup - CHANGED

= 1.3.8 =
"Fill in Business Details" doesn't filling the correct address fields value - FIXED
Globally setting $geodir_post_type when it should not be - FIXED
Recurring event fields lost the field values after Go Back and Edit event - FIXED
Detail page related listing layout setting not working for events - FIXED
Detail map displays multiple listings - FIXED

= 1.3.7 =
Date strings not working correctly with custom recurring type - FIXED
Some date formats does not supported in event calendar datepicker sets incorrect dates - FIXED
New settings added to manage date format in add event fields and view events dates - ADDED
Some times createFromFormat() function causes error in date formatting - FIXED
Warning: Missing argument 2 for geodir_event_title_recurring_event() - FIXED

= 1.3.6 =
Related listing not working correctly in detail page - FIXED
Time Field showing up in Listing page when disabled - FIXED
Use wordpress date settings for event form date field - CHANGED
Removed session_start() - CHANGED

= 1.3.5 =
Changing add listing date format can sometime cause recurring dates not to save - FIXED
Event calendar not showing ongoing events which are started in previous month - FIXED

= 1.3.4 =
Date links in calendar not working with wpml language selected - FIXED
Event meta variables added for titles & metas description - CHANGED

= 1.3.3 =
Event calendar not working if there are lots of event schedules - FIXED

= 1.3.2 =
Event end date added to event schema - FIXED
Event search sort by not showing correct results - FIXED
order by rsvp_count sorting option added - ADDED

= 1.3.1 =
Event schema altered for new GD core schema function - CHANGED
The Event calendar widget title has a double h3 tag - FIXED
Sessions managed by GeoDirectory Session class - CHANGED
function geodir_get_current_posttype called too early - FIXED
Validating and sanitizing changes - FIXED

= 1.3.0 =
Fixed post term count for neighbourhood locations - FIXED
Event shortcode not showing recurring dates in order - FIXED
Map markers doesn't working for non-recurring old events when recurring feature disabled - FIXED
Listing width option does not working in related events listing widget - FIXED
New update licence system implemented - CHANGED

= 1.2.9 =
Event cal query wrong on location/me page - FIXED

= 1.2.8 =
New actions added to customize listing not found message - CHANGED
In event listing widget new options added to filter events with featured, special offers, pics and videos only - CHANGED
Event cal shortcode not accepting `monday` as day to start - FIXED

= 1.2.7 =
Event calender not always responsive on narrow locations - FIXED
Event widget can set the main listing view if it is placed before main listing - FIXED
Shortcode gd_listings category attribute doesn't filter correctly for events - FIXED
Event dates for long events not showing correct dates - FIXED
Event end date now shown on listing pages if different from start date - CHANGED
case conversion functions replaced with custom function to support unicode languages - CHANGED
GD Core Version check - ADDED
Use get_site_url() instead of home_url() - CHANGED
GD update script sometimes not verifying SSL cert - FIXED

= 1.2.6 =
Change to accept custom recurring imports with only one date - FIXED
Unused css image classes removed - REMOVED
Add listing go back and edit dome setting not saving - FIXED

= 1.2.5 =
Event calendar not working when events spans several months - FIXED
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.2.4 =
Best of widget total reviews count for events does not shown correctly - FIXED
All widgets changed from PHP4 style constructors to PHP5 __construct, for WordPress 4.3 - CHANGED
Category parameter not working for gd_events_listing shortcode - FIXED
In event listing widget View All link in not correct if location filter disabled - FIXED
Changes made for WPML compatibility - CHANGED

= 1.2.3 =
All date/time functions updated with WP standards - FIXED
Post tags has a DB limit of 254, removed this limit - FIXED
Changes for WPML custom posts slug translation fix - FIXED
Some calendar dates are not linked to any event even there is an event on that date - FIXED

= 1.2.2 =
dbDelta not adding schedule_id as PRIMARY KEY - FIXED
Missing JS files in wp-admin from old version - FIXED

= 1.2.1 =
Fixed js & css problems - FIXED

= 1.2.0 =
Some texts are not translatable in link business - FIXED
Option added to hide event past dates in the detail page sidebar - ADDED
DB install changed to dbDelta function - CHANGED

= 1.1.9 =
We remove options for DB table upgrade on deactivation so it will run again on activation - CHANGED

= 1.1.9 =
Span element with css class added to separate date style form the rest title - ADDED
Current date taken as a default event date if start date not selected - FIXED
In linked events view by listview layout not working - FIXED
Query filters some times applied to other post types, now specifically removed if non GD post type - FIXED
'the_title' filter only runs on gd_event post type - FIXED
Popular category widget should count today's and upcoming events - FIXED
Checked for XSS vulnerabilities as per latest WP security update, found none but updated the code to new standards - SECURITY
Sort by upcoming not working in GD > Event Listing widget - FIXED
Option added to customize display week of the day in calendar widget - ADDED
Sorting by upcoming date added as second order in events widget - ADDED
Recurring feature not working after re-installing addon - FIXED

= 1.1.8 =
Linked business title displaying with the title All Events - ADDED

= 1.1.7 =
Displaying no events when clicking on past day in the events calendar widget - FIXED
Settings added to manage linked events - ADDED
Recurring event feature added - ADDED
For linked business on event sidebar text "Go to Listing Type" changed to "Go to: Listing Title" - ADDED
Sort by most reviews not working in widget and shortcode popular post view - FIXED
Few changes to add gd_listings shortcode option - CHANGED
Sort events list by upcoming event first - ADDED
In event listing shortcode use of list_sort has not any effect - FIXED

= 1.1.6 =
Dummy data can sometimes not delete post_details info - FIXED

= 1.1.5 =
Prefixed all shortcodes with gd_ - CHANGED

= 1.1.4 =
Link business info doesn't import radio or multiselect field types - FIXED
Calendar CSS changed from ID to Class so more than on can be used on the one page - CHANGED
Event calendar widget and shortcode can not be used more than once on the same page - CHANGED

= 1.1.3 =
inline css removed from event calendar - CHANGED
Event calendar more responsive - ADDED

= 1.1.2 =
popular category count not working for event categories - FIXED

= 1.1.0 =
dates now translatable - FIXED
Check added to see if core GeoDirectory is active before loading the rest of the plugin - ADDED
Event Listing widget not working on non-GD page - FIXED
Distance units(meters, miles, km, feet) in search results page are not translatable - FIXED
WordPress multisite compatibility - ADDED
date format on add listing page can be changed with filter 'geodir_add_event_calendar_date_format' 'Y-m-d' - ADDED

= 1.0.9 =
December events not showing - FIXED
On listing detail page profile & reviews tab blank after update - FIXED
function query_posts() conflicts with GD > Related events listing widget - FIXED

= 1.0.8 =
Link business now searches all post types and also pulls all info such as phone, twitter - FIXED
On listing detail page profile & reviews tab blank after update - FIXED
function query_posts() conflicts with GD > Related events listing widget - FIXED