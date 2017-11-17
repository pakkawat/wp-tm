=== GeoDirectory Payment Manager ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.9
Stable tag: 2.0.33
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog =

= 2.0.33 =
PHP 7.1 compatibility change - FIXED

= 2.0.32 =
Alive days showing incorrect value for free trial - FIXED
Invoicing compatibility functions added - CHANGED
Handle old payment manager IPN - FIXED
Listing 2nd pre-expire notification sends wrong email content - FIXED

= 2.0.31 =
Video and special offer tabs show even when no content - FIXED
Some strings updated - CHANGED

= 2.0.3 =
WPML v3.2+ compatibility changes - CHANGED
Listing should be marked as expired on refund payment - FIXED
Option added to manage listing status on payment received for the invoice - CHANGED
Detail page still shows video tab even field is disabled in package settings - FIXED

= 2.0.2 =
PHP syntax error on some activation situations - FIXED

= 2.0.1 =
Default package shows coupon field even disabled for package - FIXED
Sets invalid trial days if package has different unit for trial & regular period - FIXED

= 2.0.0 =
Now able to send multiple pre-expiry notifications - ADDED
Changes for Invoicing plugin integration - ADDED
Display notice if Invoicing plugin is not active - ADDED
Special offer tab can show even if price package is set to not show special offer - FIXED
Pre & post actions added when deleting package - ADDED
Email headers changed from string to array() and MIME-Version removed - CHANGED
On renewing the active listing the expire date is increased twice - FIXED
Listings with active subscription should not check expire & downgrade the package - CHANGED
With WPML, switching package redirect to add listing page with the default language - FIXED
HTML editor for description field can be disabled for particular package(s) - ADDED
Single quote in description breaks description when description limit set - FIXED
Fix issue for plugin installation via WP-CLI - FIXED
All info is lost if user changes price package before first submit - FIXED
Disabled coupon use does not working for new listing with default package - FIXED
Cancel subscription should downgrade the package for the listing if allowed - FIXED
Display expired listing content to avoid 404s for expired listings with draft status - CHANGED
Downgrade listing looses the previously stored listing information - FIXED

= 1.4.4 =
Authorize checkout form not getting displayed if there is only one gateway - FIXED
Backend price package alivedays, expire date and feature settings do not set for new listings - FIXED
Option added to remove plugin data on plugin delete - ADDED

= 1.4.3 =
Free listing does not get published on renew - FIXED

= 1.4.2 =
Free trial text spelling fixed - FIXED
Paypal item name doesn't showing Cyrillic symbols properly - FIXED

= 1.4.1 =
Show page content for all GD pages - ADDED
login_url and username not working in invoice - FIXED
If paypal custom field invoice id missing we check for post id - ADDED

= 1.4.0 =
For recurring payments the checkout page shows message if discount for first installment only - CHANGED
Function geodir_get_post_meta() returns false value when field has value 0(zero) - FIXED
Added filters for client and admin emails - ADDED

= 1.3.9 =
Checkout page mentions info of listing package free trial if available - CHANGED
Using paypal id as a merchant id detects verified payment as a fraud payment - FIXED

= 1.3.8 =
Info page does not shows bank info if pre bank transfer payment method selected to claim a listing - FIXED
With some settings the alive days can be doubled - FIXED
Some PHP7 compatibility changes - CHANGED

= 1.3.7 =
Price package message does not state 2CO supports recurring - FIXED
Hooks added for when price package is changed or downgraded - ADDED
For recurring package text added to indicate that going to pay for recurring installment - CHANGED
GD checkout page title now used page title and not `GeoDirectory Checkout` - CHANGED

= 1.3.6 =
It should not loose saved settings when plugin activate -> deactivate -> activate - FIXED
Option added to customize invoice displayed - CHANGED
For recurring price package coupon is not applied correctly - FIXED
Filter box added in backend invoice list page to filter results for status & invoice id / listing id - CHANGED
Option added to manage currency symbol position - ADDED
Notifications and package names are translatable using GD Tools -> Custom fields translation - FIXED
PHP warning message when submitting new coupon - FIXED
Some times coupon do not work unless coupon limit set to unlimited - FIXED
New update licence system implemented - CHANGED
Implemented 2Checkout recurring payments - CHANGED
Sessions managed by GeoDirectory Session class - CHANGED
When a email field is disabled the send to friend is not working even it is enabled - FIXED
Validating and sanitizing changes - FIXED

= 1.3.5 =
Option added to limit coupon usage and coupon usage count - ADDED
Option added to enable/disable coupon use per price package - CHANGED
Payment status replaced with Incomplete if checkout not completed - CHANGED

= 1.3.4 =
Expire check can restore trashed posts if they still have time left to run - FIXED

= 1.3.3 =
Login link updated to use geodir_login_url() function - FIXED
Subscription payments not extending listing time - FIXED
case conversion functions replaced with custom function to support unicode languages - CHANGED

= 1.3.2 =
Changes made to be able to use new GD info page for payment return information - CHANGED
Invoice not processed correctly when used 100% discount coupon - FIXED
Upgrading listing not adding previous days left to expire date - FIXED
Tinymce editor changes broke the description limit on the add listing page - FIXED
Option added to show/hide upgrade link on listings & listing detail page - ADDED
Language file not scanning for esc_attr_e so missing some translations - FIXED

= 1.3.1 =
Invoice page displays ###.INVOICE_PAGE_ID at the bottom - FIXED

= 1.3.0 =
GD auto update script improved efficiency and security(https) - CHANGED
Payment system upgraded with some new features - ADDED
Changed textdomain from defined constant to a string - CHANGED

= 1.2.5 =
Price description are translatable using GD Tools -> Custom fields translation - FIXED
Category limit no longer counting child categories - FIXED
Changes made for WPML compatibility - CHANGED
dbDelta function not running on version clear - FIXED

= 1.2.4 =
If ID not set the package limits can default to the post_type default package - FIXED
Expire date doesn't use WP settings default date format - FIXED
Some PHP warnings/notices resolved - FIXED

= 1.2.3 =
Limit tags description changed to make it more clear if not used the default 40 characters will be used - CHANGED
geodir_update_post_meta used instead of geodir_save_post_meta - FIXED
Option added to hide the related listing tab on detail page - ADDED
dbDelta function used for tables creation - CHANGED
function geodir_update_invoice_status() used to update invoice statuses - CHANGED
publish the property changed to publish the listing for more coherent with the description - CHANGED
Added font awesome icons to upgrade and renew links - ADDED
If only one payment gateway active the gateway is not checked - FIXED

= 1.2.2 =
Link to add listing page fixed - FIXED
Note added to explain recurring for field can be left blank for no limit - ADDED

= 1.2.1 =
Add listing shortcode bug for prices not showing - FIXED

= 1.2.0 =
Some changes for AffiliateWP integration - CHANGED
Renew listing not changing listing expire date - FIXED

= 1.1.9 =
Some changes for AffiliateWP integration - CHANGED

= 1.1.8 =
Hook to update DB changes change to plugins_loaded - CHANGED

= 1.1.7 =
Recurring payment times note added to show minimum and maximum allowed values - ADDED
Upgrading a listing sometimes does not set it to draft first - FIXED

= 1.1.6 =
Authorize.net select card type option removed as no longer required - CHANGED

= 1.1.5 =
Error message text updated for "Category limit" in price package - CHANGED
default price packaged have cat_limit set to 0 which causes problems - FIXED
added fix for change package for add-listing shortcode - FIXED

= 1.1.4 =
Recurring times warning added if less than 2 - ADDED
Recurring units has range 1 missing - FIXED

= 1.1.3 =
recurring payment number of times range change from 1-x to 2-x as 1 should not be a recurring payment - CHANGED
if free trial is offered with sub then the listing is not published automatically - FIXED

= 1.1.2 =
Admins can now set is_featured regardless of price package - CHANGED

= 1.1.1 =
Video not showing if description limit is set - FIXED
Invoice function can sometime fail to return the correct ID causing the paypal payment page to be invalid - FIXED

= 1.0.9 =
When this user adds a new listing and enters alive days & expire date "Never", after saving the expire date goes to 1970 - FIXED
Template files can't be replaced in child theme - FIXED
Expiry date displayed in listings in my dashboard and detail page (for owner only) - ADDED
Option added to show/hide expiry date to listings in my dashboard and detail page - ADDED
New hook "geodir_payment_filter_payable_amount_with_coupon" added to function "geodir_get_payable_amount_with_coupon" - ADDED
Feature added to show link text "Upgrade Listing" to "Renew Listing" if listing is going to expire in next days - ADDED
If renewing to same package that the days remaining on your package will added to your next package - ADDED
WordPress multisite compatibility - ADDED
User can now delete invoiced - ADDED
Invoices are deleted when the post is is for is deleted - ADDED
Added option to coupon to allow to discount subscription only first payment - ADDED
Added error checking to authorize.net inputs - ADDED
Added error message and redirect for authorize.net payment failure - ADDED

= 1.0.8 =
IPN can send 404 not found depending on homepage settings - FIXED
Removed PHP warning message - FIXED
Custom css classes added for each the listing row to identify paid and free listing - ADDED
There seems to be a little bug when as admin you only add alive days. If you do that, the systems seems to think today is 1 January 1970 - FIXED
Days, Weeks, Months and Years added in options for paypal free trial limit units - ADDED
Filter geodir_package_info updated for better support - ADDED
Notification of listing submission for bank transfer doesn't seem to be translatable. - FIXED
Option of character limitation added for post tags and description for custom price packages. - ADDED
BCC option added for admin to receive notification of expire listings - ADDED
Blank index.php added to each directory - ADDED