=== GeoDirectory Re-Captcha ===
Contributors: GeoDirectory Team
Donate link: https://wpgeodirectory.com
Requires at least: 3.1
Tested up to: 4.7
Stable tag: 1.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The GeoDirectory Re-Captcha plugin allows you to implement a super security captcha form into GeoDirectory addon including registration, comments, send to friend, send enquiry and claim listing forms. GeoDirectory Re-Captcha is a free CAPTCHA service that protects your site against spam, malicious registrations and other forms of attacks where computers try to disguise themselves as a human.
This captcha can be used for GeoDirectory registration, comments, send to friend, send enquiry and claim listing forms.

GeoDirectory Re-Captcha provides some of the smartest anti-spam security by protecting your site against spam and malicious registrations available today for WordPress, modded to be 100% GeoDirectory compatible.

[Learn how reCAPTCHA works](https://www.google.com/recaptcha/) and join [forum](https://groups.google.com/forum/#!forum/recaptcha).

== Requirements ==

* You need the Google reCAPTCHA keys from [here](https://www.google.com/recaptcha/admin#whyrecaptcha).

== Changelog ==

= 1.1.6 =
Claim listing can show error message if using invisible - FIXED

= 1.1.5 =
Option added to use recaptcha on standard WordPress registration page - ADDED
Option added to use recaptcha invisible (requires new keys) - ADDED
Update script updated to use WP Easy Updates - CHANGED

= 1.1.4 =
Network admin should be treated as an administrator role - FIXED
Recaptcha compact version will be shown if screen width is less than 1200px - CHANGED
Fix issue for plugin installation via WP-CLI - FIXED
Support for invisible reCAPTCHA - ADDED
Option added to choose between reCAPTCHA V2 and invisible - ADDED

= 1.1.3 =
Bug when editing a listing - FIXED

= 1.1.2 =
Remove ajax recaptcha validation to harden recaptcha validation - CHANGED

= 1.1.1 =
Option added to remove plugin data on plugin delete - ADDED

= 1.1.0 =
Re-Captcha not working with ajax loading on add listing form - FIXED
WP_PLUGIN_URL not working correctly in https - FIXED

= 1.0.9 =
Some PHP7 compatibility changes - CHANGED

= 1.0.8 =
Fix success recaptcha response on ajax forms - FIXED
New special language tag zh-HK included - ADDED
New update licence system implemented - CHANGED

= 1.0.7 =
GD update script sometimes not verifying SSL cert - FIXED

= 1.0.6 =
GD auto update script improved efficiency and security(https) - CHANGED
Changed textdomain from defined constant to a string - CHANGED

= 1.0.5 =
Changes made for WPML compatibility - CHANGED

= 1.0.4 =
GD captcha widget not loads on first time for slow connection - FIXED
Docblocks added - ADDED

= 1.0.3 =
Re-captcha added for BuddyPress registration - FIXED

= 1.0.2 =
Re-captcha not working on GD signup page - FIXED

= 1.0.1 =
In the setting page, Claim Listing checkbox is available even if Claim Listing addon is not installed - CHANGED
Send to friend not working if not logged in - FIXED