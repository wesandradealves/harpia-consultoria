=== WP Nav Plus ===
Contributors: mattkeys
Tags: split menu, start depth, divided menu, tertiary menu, secondary menu
Requires at least: 3.0.1
Tested up to: 5.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Nav Plus fills gaps in the WordPress menu system. Use for split menus, divided menus, menu segments, or to limit and/or offset the menu output.

== Description ==

WP Nav Plus has been designed to fill gaps in the WordPress menu system which make it difficult to accomplish many popular website design patterns. **This is a tool built for developers** to help get the right menu items output onto the page. This plugin applies no additional CSS styling or JS interaction to menus. Styling and interaction are the job of the theme, and may need to be altered to achieve your desired appearance.

This plugin integrates with the native WordPress [WP Nav Menu function](https://developer.wordpress.org/reference/functions/wp_nav_menu/) which means you can access all of the features of this plugin in your templates. There is also an included widget as an alternative integration method.

=== Split Menus ===

Many website designs call for a submenu, often right below the primary navigation in the header, or in a sidebar on interior pages. These submenu's are designed to show the children of the currently active menu item. WP Nav Plus makes it super simple to build out these types of menus using the widget, or by using the 'start_depth' argument in your wp_nav_menu() function.

=== Divided Menus ===

A fairly common website design pattern you may see online calls for the header navigation menu to be placed left and right of a central object, like the site logo. WP Nav Plus makes it very easy to build out these menus using the widget, or by using the 'divider_html' argument in your wp_nav_menu() function.

=== Limit and Offset ===

Often website designs call for a menu to be split up into multiple columns, or rows. Such as a multi-column footer sitemap. WP Nav Plus provides the capabilities you need to quickly build these custom menu layouts using the widget, or by using the 'limit' and 'offset' argument in your wp_nav_menu() function.

=== Menu Segments ===

It is sometimes useful to display a particular segment of your WordPress menu on its own. Menu Segments allow you to specify a portion of your menu for display based on the parent menu item. Uses for menu segments could include Footer Sitemaps, Mega Menus, or all sorts of other custom menu layout needs. Accomplish menu segments by using the widget, or by using the 'segment' argument in your wp_nav_menu() function.

== Installation & Configuration ==

1. Login to your Wordpress Admin page (usually http://yourdomain.com/wp-admin)
2. Navigate to the Plugins screen and then click the "Add New" button
3. Click on the "Upload" link near the top of the page and browse for the WP Nav Plus zip file
4. Upload the file, and click "Activate Plugin" after the installation completes
5. See the documentation which includes some video recordings of the included functionality to help you get started

This plugin can be used either with the provided "WP Nav Plus" widget, or in your PHP templates, by using the new arguments which this plugin adds to the [WP Nav Menu function](https://developer.wordpress.org/reference/functions/wp_nav_menu/).

For help getting started please see the included documentation in the /documentation directory or online at:  [http://mattkeys.me/documentation/wp-nav-plus/](http://mattkeys.me/documentation/wp-nav-plus/)

== Screenshots ==

1. Split menu in PHP template usage
2. Split menu with widget example configuration and output
3. Split menu with widget example configuration and output depth 2
4. Using two widgets together to create multiple split menus
5. Divided menu example with logo in center
6. Split menu widget options
7. Divided menu widget options
8. Limit/Offset menu widget options
9. Menu segment widget options

== Changelog ==

= 3.4.9 =
* Bugfix: Menu segment doesn't work on HTML encoded menu titles, refactored to rely on object IDs instead of title strings

= 3.4.8 =
* Allow for multiple menu classes to be assigned with the widget

= 3.4.7 =
* WP Nav Plus is no longer a 'premium' plugin and is now open source software available for free to the community through the WordPress plugin repository.
* Added languages POT file for translation
* Added deprecated warning for any users still using the old wp_nav_plus function
* Refactored/cleaned up a bit of code for better performance, readability, and WP standards compliance

= 3.4.6 =
* Bugfix: Fix problem with UTF-8 decoding in divided menus with non-latin characters
* Bugfix: Fix bug when trying to locate the correct parent menu item of a single post

= 3.4.5 =
* Bugfix: Force UTF-8 entity decode of divided menu layouts on PHP 5.3 and earlier.

= 3.4.4 =
* Bugfix: Better handling of multiple post type archives in menu
* Bugfix: Check to see if menu_class key is set before accessing it

= 3.4.3 =
* Bugfix: Properly find parent menu item for single posts in custom post types
* New Feature: Set menu class from widget
* New Feature: Support menu items added from "Post Type Archive Link" plugin

= 3.4.2 =
* Bugfix: Allow for custom fallback_cb call in WP Nav Menu

= 3.4.1 =
* Bugfix: set proper UTF-8 encoding in divided menu handling to make sure accented characters are properly displayed.

= 3.4 =
* New Feature: added 'segment' argument. Use segment to specify a portion of your menu for display, regardless of the currently active page. Accepts Object ID or Menu Name.

= 3.3 =
* New Feature: added 'limit' argument. Use limit to specify the maximum number of parent menu items to return.
* New Feature: added 'offset' argument. Use offset to skip over a number of parent menu items (and their children).
* New Feature: added new arguments for easy creation of divided menus, such as menus with a logo in middle. New arguments include: 'divider_html', 'divider_class', 'divider_id', 'divider_offset', and 'divider_container'
* Addressed the funky way WooCommerce adds pages to the menu system so that WP Nav Plus can find them correctly
* Modified code to meet WordPress PHP Coding standards

= 3.2 =
* Fixed issues with menus disappearing on taxonomy pages
* Improved function for finding custom links in menu to be able to find relative URLs
* Fixed PHP notice due to improper calling of a non-static function statically
* Fixed undefined $wp_nav_plus_options bug in find_category_ids()

= 3.1 =
* Added in an updater class that will allow customers to update WP Nav Plus from the plugins page

= 3.0 =
* This is a complete top to bottom rewrite of WP Nav Plus
* New Feature: the wp_nav_plus function has been depreciated, WP Nav Plus has been redesigned to work with the standard wp_nav_menu function built into WordPress
* New Feature: WP Nav Plus is now able to locate the menu position of "link" menu items. Example: a link to a custom post type archive page.
* Bug Fix: the widget now properly utilizes the before_title and after_title strings instead of hardcoded H3's
* Improvement: Massive performance improvements to the methods used to calculate the menu children

= 2.2.5 =
* Fixed a bug that cause the menu to disappear on post pages when only one category was assigned. In some cases this bug could also produce a PHP error.

= 2.2.4 =
* Set default start_depth to 0
* Fixed a bug that was causing an infinite loop in rare situations where the object id returned results in the postmeta that were not associated with any menu in wp_term_relationships

= 2.2.3 =
* Fixed a bug that was causing the menu to not show then the menu term_id did not match the term_taxonomy_id.

= 2.2.2 =
* Tweaked widget output to make sure that no menu container is shown on the screen when there are no menu items.

= 2.2.1 =
* Tweaked logic to support Gecka Submenu plugin functionality

= 2.2 =
* This release adds widget functionality to WP Nav Plus to make it much easier for non developers to use the power of WP Nav Plus!
* Advanced users can continue to use the wp_nav_plus function in their templates as always

= 2.1 =
* This release includes a couple of bug fixes related to some less common menu configurations, including: multiple menus showing duplicate content, and fixing a couple of PHP notices being shown when the menu was included on pages like the 404 page.
* Fixed bug causing menu not to show on multisite installations
* This release expands the logic/ability of WP Nav Plus to allow users to show 3+ split menu's on a page. Meaning you could have independent menus for 1st level links, 2nd level links, and 3rd level links, all on the same page at once.
* This release also expands the logic/ability of WP Nav Plus to continue showing the menu even after users click into a blog post (something I have not seen another solution do yet)

= 2.0 =
* This release is a complete rethinking and rewrite of WP Nav Plus. Versions 1.x were too dependent on the page structure configured in the WordPress pages admin area.
* Added support for persistent menu on blog post pages (where most other solutions would disappear)

= 1.1 =
* Fixed a bug that was preventing Custom & Category menu item types from appearing

= 1.0 =
* This is the first public release

== Upgrade Notice ==

= 3.4.9 =
* Bugfix: Menu segment doesn't work on HTML encoded menu titles, refactored to rely on object IDs instead of title strings

= 3.4.8 =
Allow for multiple menu classes to be assigned with the widget

= 3.4.7 =
WP Nav Plus is no longer a 'premium' plugin and is now open source software available for free to the community through the WordPress plugin repository.
Added deprecated warning for any users still using the old wp_nav_plus function
Added languages POT file for translation
Refactored/cleaned up a bit of code for better performance, readability, and WP standards compliance

= 3.4.6 =
Bugfix: Fix problem with UTF-8 decoding in divided menus with non-latin characters
Bugfix: Fix bug when trying to locate the correct parent menu item of a single post

= 3.4.5 =
Bugfix: Force UTF-8 entity decode of divided menu layouts on PHP 5.3 and earlier.

= 3.4.4 =
Bugfix: Better handling of multiple post type archives in menu
Bugfix: Check to see if menu_class key is set before accessing it

= 3.4.3 =
Bugfix: Properly find parent menu item for single posts in custom post types
New Feature: Set menu class from widget
New Feature: Support menu items added from "Post Type Archive Link" plugin

= 3.4.2 =
Bugfix: Allow for custom fallback_cb call in WP Nav Menu

= 3.4.1 =
Bugfix: set proper UTF-8 encoding in divided menu handling to make sure accented characters are properly displayed.

= 3.4 =
Added a new feature which allows for a chunk or segment of the specified menu to be displayed on its own, regardless of the currently active page.

= 3.3 =
Added a bundle of major new functionality which will aid in the creation of many more menu design patterns such as divided menus, or menu's which span multiple columns. See the [product page](http://mattkeys.me/products/wp-nav-plus/) for full details. Please make a backup before upgrading in case you have trouble with any of the new functionality.