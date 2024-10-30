=== CSS JS Files ===
Contributors: jamesdlow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=PGV92BZCFTDL4&item_name=Donation%20to%20jameslow%2ecom&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: css, page, post, custom css, file css, js, javascript, file js, custom js, custom, global, cdn
Requires at least: 3.0
Tested up to: 6.5.5
Stable tag: 1.5.2
License: MIT Licens
License URI: https://opensource.org/licenses/MIT

Select files CSS/JS and/or write CSS/JS rules to any single page or post or globally

== Description ==
A simple Wordpress plugin that allows users to select files CSS and/or write CSS rules to any single page or post or globally

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload entire `css-js-files` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it, you'll see a `Select CSS/JS files and/or write your custom CSS/JS rules` metabox on all add or edit posts/pages.

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.5.2 =
* Additional security fixes and improvements to editor

= 1.5.1 =
* Additional fix to vulnerability in editor
* Remove PHP 8.X warning

= 1.5.0 =
* Fix vulnerability in editor
* Bug fixes and improvements

= 1.4.9 =
* Fix count() error on PHP 8+ when post/page has no meta

= 1.4.8 =
* Remove code that deletes meta on move to trash as this was not working anyway and we may want to restore if restored from trash

= 1.4.7 =
* Prevent output of blank CSS/JS links

= 1.4.6 =
* Restore original sanetization for fields

= 1.4.5 =
* Fix saving of head/body/footer fields

= 1.4.4 =
* Compatibility and sanetizing

= 1.4.3 =
* Add admin css/js

= 1.4.2 =
* Ability to add custom html in the head/body/footer hooks

= 1.4.1 =
* Fix JavaScript for latest Wordpress JQuery version

= 1.4.0 =
* Allow remote JavaScript and CSS files and fonts

= 1.3.5 =
* Sort filenames in drop down

= 1.3.4 =
* Fix not echoing global rules introduced in 1.3.3

= 1.3.3 =
* Fix "PHP message: PHP Warning: count(): Parameter must be an array or an object that implements Countable"

= 1.3.2 =
* Fix "Parameter must be an array or an object that implements" warnings

= 1.3.1 =
* Fix for assumed constant warning

= 1.3.0 =
* Bug fix for showing global CSS/JS on other pages like home/search etc.
* Show post specific CSS/JS on front page if it is a static page
* Show post specific CSS/JS on posts page

= 1.2.0 =
* Bug fix to make global js files actually include
* Put global JS files first so that post specific comes after

= 1.1.1 =
* Bug fix for initial global CSS/JS text option

= 1.1.0 =
* Global CSS/JS files and rules
* Change CSS/JS search path
* Editor for CSS/JS files

= 1.0.0 =
* Initital version