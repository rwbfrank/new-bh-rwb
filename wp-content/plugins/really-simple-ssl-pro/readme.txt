=== Really Simple SSL pro ===
Contributors: RogierLankhorst
Tags: mixed content, insecure content, secure website, website security, ssl, https, tls, security, secure socket layers, hsts
Requires at least: 4.4
License: GPL2

Tested up to: 4.8
Stable tag: 2.0.9

Premium support and extra features for Really Simple SSL

== Description ==
Really Simple SSL Pro offers premium support, HTTP Strict Transport Security, an extensive scan, and more detailed feedback on the configuration page.

= Installation =
* Install the Really Simple SSL plugin, which is needed with this one.
* Go to “plugins” in your Wordpress Dashboard, and click “add new”
* Click “upload”, and select the zip file you downloaded after the purchase.
* Activate
* Navigate to “settings”, “SSL”.
* Click “license”
* Enter your license key, and activate.

For more information: go to the [website](https://www.really-simple-ssl.com/), or
[contact](https://www.really-simple-ssl.com/contact/) me if you have any questions or suggestions.

== Frequently Asked Questions ==

== Change log ==
= 2.0.9 =
* Fix: the plugin will no longer scan its own folder for mixed content
* Fix: the scan now contains a number of common false positives in the $safe_domains array to prevent them showing up in the scan

= 2.0.8 =
* Fix: added a notice when the HSTS header is set using PHP on NGINX servers

= 2.0.7 =
* Fix: fixed a bug where the $has_result in the external CSS and JS scan was set to true when there were no results

= 2.0.6 =
* Tweak: added a warning when inserting HSTS headers via PHP

= 2.0.5 =
* Fix: deleted unnecessary transient delete

= 2.0.4 =
* Added support for Widgets in the mixed content scan
* Tweak: changed icon classnames from icon to rsssl-icons

= 2.0.3 =
* Tweak: option to disable flushing of rewrite rules on activation

= 2.0.2 =
* Tweak: added secure cookie settings
* Tweak: added notice if secure cookie settings couldn't be applied automatically

= 2.0.1 =
* Fix: made the HSTS option available when using the pro plugin on a multisite installation
* Tweak: updated the Easy Digital Downloads plugin updater to version 1.6.14

= 2.0.0 =
* Fix: moved scan data to transients, so large scan data arrays won't clutter the database
* Updated the 'Scan for issues tab'
* Fix: scan results are now shown in a responsive layout
* Fix: fixed a bug where protocol independent (//) files and/or stylesheet were not being scanned

= 1.0.33 =
* Fix: adjusted the HSTS header so it will also work in three redirects
* Fix: not all hot linked images were matched correctly in the scan

= 1.0.32 =
* Fix: When mixed content fixer is activated, urls are replaced to https, which prevented the scanner from finding these urls. A replace to http fixes this.
* Fix: Regex pattern updated to match the pattern in the free version, to prevent cross elemen matches.
* Fix: Changed priority of main class instantiation to make sure it instantiates after the core plugin

= 1.0.31 =
* Removed direct redirect to primary URL, as preload list requires a two step redirect, first to https.

= 1.0.30 =
* Fixed issue where preload HSTS setting wasn't saved when HSTS header was already in place.
* limited .htaccess edit to settings save action.

= 1.0.28 =
* Added certificate expiration date admin notice
* Bypass redirect chain
* Fixed SSL per page compatibility issue

= 1.0.27 =
* Added dutch translation
* Fixed typo in copyright warning box
* Fixed spacing between buttons in copyright warning box
* Tweak: added settings link to pro plugin in plugins overview page
* Fix: fixed a bug where curl function did not increment iteration, so redirect loops were not circumvented. Use wp_remote_get instead.

= 1.0.26 =
* Added expiration warning functionality: enable it in the settings to get an email if your ssl certificate expires.
* Fixed a bug that detected if the HSTS should be added in the .htaccess or in the code
* HSTS now available for NGINX as well
* Fixed a bug where the last js and css files were not parsed for http urls.

= 1.0.25 =
* css styling fixes
= 1.0.24 =
* Bug fix in importer.php

= 1.0.23 =
* Bug fixes
= 1.0.22 =
* Added functionality to fix mixed content issues
* several bug fixes

= 1.0.21 =
* improve license validation

= 1.0.20 =
* Extended safe list of domains for scan
* fixed a bug in licensing for multisite.
* fixed a bug where the scan freezed because of not loading external url's

= 1.0.19 =
Tweak: added better feedback on license not being activated
Tweak: added HSTS preload list option
Tweak: added mixed content fixer for the back-end

= 1.0.18 =
Tweak: added possibility to deactivate license, for migration purposes
= 1.0.17 =
Tweak: also checking for http links in form actions
Fix: bug caused empty scan results to show, even when no scan had been executed
Fix: last queue item was shown as nr 0, instead of the last nr

= 1.0.16 =
Fix: bug where not all found blocked urls would show up
Fix: also searching for the escaped values of a url in the database.

= 1.0.15 =
Tweak: added option to enable or disable the curl function for file requests

= 1.0.14 =
Added option to disable brute force database scan

== Upgrade notice ==
On settings page load, the .htaccess file is no rewritten. If you have made .htaccess customizations to the RSSSL block and have not blocked the plugin from editing it, do so before upgrading.
Always back up before any upgrade. Especially .htaccess, wp-config.php and the plugin folder. This way you can easily roll back.

== Screenshots ==
* If SSL is detected you can enable SSL after activation.
* The Really Simple SSL configuration tab.
* List of detected items with mixed content.

== Frequently asked questions ==
* Really Simple SSL maintains an extensive knowledge-base at https://really-simple-ssl.com/knowledge-base-overview/
