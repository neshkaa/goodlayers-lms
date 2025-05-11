=== Participants Database Pretty Permalinks ===
Contributors: xnau
Donate link: https://xnau.com/work/wordpress-plugins/
Requires at least: 5.1
Tested up to: 5.5.1
License: GPLv2
License URI: https://wordpress.org/about/gpl/

Use human-readable and SEO-friendly permalinks for Participants Database records

== Description ==
This plugin adds a configurable permalink setup for all Participants Database records. Records can be accessed using a human-readable permalink that is memorable with improved SEO. Links to the records in the list display are updated to use the permalinks.  

== Installation ==
* Download the plugin zip file.
* Unzip the file
* Upload the resulting directory to your plugins folder (typically located at wp-content/plugins/)
* Log in to your site admin, then visit the plugins page
* Locate the plugin in the list of installed plugins, and activate.

== Changelog ==

= 1.9.3 =
clear the participant cache when updating slugs
optimized getting the record slugs

= 1.9.2 =
added support for Member Payments profile payment shortcode

= 1.9.1 =
"Update All Record Slugs" now done as a background process #18

= 1.8 =
fixed bug where the target page could not be a child page #17
minor change to the slug field datatype for compatibility with innodb

= 1.7 =
slugs may be manually edited #15
empty slug won't cause error #14
all records get unique slugs with update all command #13

= 1.6 =
records manually added in the admin are now getting slugs #8

= 1.5 =
non-ASCII characters in the slug are working now

= 1.4 =
slug field definition is deleted when the plugin is deactivated to prevent unique field db error #6 

= 1.3 =
fixed bug in Destinations class

= 1.2 =
adds permalink for record edit page #5

= 0.3 =
uninstall completely removes the slug column
handle blank slug field in CSV import #4

= 0.2 =
removed single record page settings
PHP 5.3 compatibility

= 0.1 =
Initial release of the plugin

== Upgrade Notice ==

1.6 is a bugfix release for all users