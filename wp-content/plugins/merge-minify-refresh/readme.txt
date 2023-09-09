=== Merge + Minify + Refresh ===
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VL5VBHN57FVS2
Contributors:launchinteractive
Tags: merge, concatenate, minify, closure, refresh, litespeed, apache
Requires at least: 3.6.1
Stable tag: trunk
Tested up to: 6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Merges/Concatenates CSS & Javascript and then minifies using Minify (for CSS) and Google Closure (for JS with Minify as a fallback).

== Description ==

This plugin merges/concatenates Cascading Style Sheets & Javascript files into groups. It then minifies the generated files using Minify (for CSS) and Google Closure (for JS - fallback to Minify when not available). Minification is done via WP-Cron so that it doesn't slow down the website. When JS or CSS changes files are re-processed. No need to empty cache!

Inspired by [MinQueue](https://wordpress.org/plugins/minqueue/) and [Dependency Minification](https://wordpress.org/plugins/dependency-minification) plugins.

Minification by [Minify](https://github.com/matthiasmullie/minify) and [Google Closure](https://developers.google.com/closure/)

In order to ensure fast loading times its recommended to set long expiry dates for CSS and JS as well as make sure gzip or deflate is on.

Version 1.10 added should_mmr filter which allows developers to disable MMR when needed. You could use this to disable MMR for certain WordPress roles or with some Page Builders. Three new plugins have been created that utilise this:
[Merge + Minify + Refresh Check DIVI](https://wordpress.org/plugins/mmr-disable-for-divi), [Merge + Minify + Refresh Check Visual Composer](https://wordpress.org/plugins/mmr-disable-for-visual-composer-editor) and [Merge + Minify + Refresh Check Beaver Builder](https://wordpress.org/plugins/mmr-disable-for-beaver-builder-editor). Please install these when required.

Version 1.6 added support for HTTP2 Server Push to further speed up your requests (thanks to [Daniel Aleksandersen](https://www.slightfuture.com)).

Version 1.6.9 added the ability to specify the cache directory. Set MMR_CACHE_DIR & MMR_CACHE_URL constants in wp-config.php. MMR_CACHE_DIR must be full server path and MMR_CACHE_URL must be absolute URL for this to work correctly (thanks to [Daniel Aleksandersen](https://www.slightfuture.com)).

Version 1.6.11 added the ability to specify seperate cache urls for javascript and CSS. Use MMR_JS_CACHE_URL & MMR_CSS_CACHE_URL to replace MMR_CACHE_URL.

Version 1.7.0 added the ability to generate .css.gz & .js.gz files. Your webserver may need to be configured to use these files. Here is how to use these files in Apache:
`
&#35;Serve gzip compressed CSS files if they exist and the client accepts gzip.
RewriteCond %{HTTP:Accept-encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -s
RewriteRule ^(.*)\.css $1\.css\.gz [QSA]

&#35;Serve gzip compressed JS files if they exist and the client accepts gzip.
RewriteCond %{HTTP:Accept-encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -s
RewriteRule ^(.*)\.js $1\.js\.gz [QSA]

&#35;Serve correct content types, and prevent mod_deflate double gzip.
RewriteRule \.css\.gz$ - [T=text/css,E=no-gzip:1,E=is_gzip:1]
RewriteRule \.js\.gz$ - [T=text/javascript,E=no-gzip:1,E=is_gzip:1]
Header set Content-Encoding "gzip" env=is_gzip
`
Version 1.8.8 added a "merge_minify_refresh_done" hook that fires when JS or CSS has changed.

Version 1.11 added two new constants:
MMR_USE_CLOSURE - allows Google Closure Javascript minification to be disabled.
MMR_REMOVE_EXPIRED - disables removing expired files.

Version 1.13 added two new filters which can be used to exclude specific files:
mmr_ignored_css_sources
mmr_ingnored_js_sources
Version 1.13 also automatically moves the WordPress inline styles to a global stylesheet which it then enqueues.

**Note** Installing this plugin on a server with the eAccellerator module installed has the potential to break as Minify uses anonymous functions which return NULL. [View eAccellerator Issue Thread](https://github.com/eaccelerator/eaccelerator/issues/12)

**Features**

*	Merges JS and CSS files to reduce the number of HTTP requests
*	Handles scripts loaded in the header & footer
*	Compatable with localized scripts
*	Creates WP-Cron for minification as this can take some time to complete
*	Minifies JS with Google Closure (requires php exec) with fallback to Minify
*	Minifies CSS with Minify
*	Failed minification doesn't break the site. Visitors will instead only see the merged results
*	Stores Assets in /wp-content/mmr/ folder
*	Uses last modified date in filename so any changes to JS or CSS automatically get re-processed and downloaded on browser refresh
*	View status of merge and minify on settings page in WordPress admin
*	Option to enable http2 server push (thanks to [Daniel Aleksandersen](https://www.slightfuture.com))
*	Option to enable output buffering for compatability and so footer scripts can be HTTP2 pushed
*	Ability to turn off minification
*	Ability to turn off concatination
*	Ability to manually ignore scripts or css
*	Ignores conditional scripts and styles
*	Ability to specify cache directories
*	Ability to generate .css.gz & .js.gz files (Thanks to Marcus Svensson)
*	Works with WordPress Multisite

== Installation ==

1. Upload the `merge-minify-refresh` folder to the `/wp-content/plugins/` directory or upload the zip within WordPress
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.14.2 =
* Add support for a decoupled WordPress configuration - Thanks to AbdulRaheem

= 1.14.1 =
* Upgrade Closure and PHP Minify

= 1.14.0 =
* Added ability to disable merging WordPress Global Styles
* Fixed issue with missing WordPress Global Styles

= 1.13.3 =
* Fix "String offset cast occurred" php notice

= 1.12.1 =
* Fix css minmax minifying incorrectly

= 1.12 =
* Upgrade Closure and PHP Minify

= 1.11.3 =
* Fix issue with css inline svgs breaking

= 1.11.2 =
* Ensure push urls include scheme for backwards compatability

= 1.11.1 =
* Disable push for external files

= 1.11 =
* Disable Google Closure Javascript minification with MMR_USE_CLOSURE constant
* Disable removing expired files with MMR_REMOVE_EXPIRED constant
* Enable push for ignored files

= 1.10.11 =
* Disable MMR when using WordPress Customiser

= 1.10.10 =
* Fix issue where JS/CSS would minify even if minification has been disabled

= 1.10.9 =
* Upgraded to latest version of closure
* Re-enabled HTTP2 push for when the server supports it

= 1.10.8 =
* Prevent php warning showing when handle path is invalid

= 1.10.7 =
* Fix Reported Security Vulnerability
* Upgrade Minify to latest version

= 1.10.6 =
* Prevent error if mbstring php extension isn't installed. Thanks @zelon

= 1.10.5 =
* Convert Western Latin 1 Encoding to UTF8
* Added upgrade notice callback for future use

= 1.10.4 =
* Fixed Undefined variable: ext (thanks to @imsadhappy)
* Changed HTTP2 push to only preload

= 1.10.3 =
* Fixed issue with invalid file size calculations

= 1.10.2 =
* Fixed issue with merge_minify_refresh_done action not working correctly

= 1.10.1 =
* Code Cleanup
* Added ability to http2 push CSS or JS seperately
* Improved reliability of minification + added hourly cron to check
* Fix issue with mmr_minify action running multiple times

= 1.10 =
* Added should_mmr filter and split DIVI, Beaver Builder and Visual Composer checks into seperate plugins
* Code Cleanup
* Fix warning when running wp.cron.php directly
* Prevent failed to open stream error
* Added merge_minify_refresh_merged action

= 1.9.0 =
* Disable MMR when Divi theme is enabled and page is being edited
* Disable MMR when Beaver Builder is in use and page is being edited
* Upgrade Google Closure to the latest version
* Upgrade PHP Minify to the latest version
* Add check to see if @import is used in CSS file.

= 1.8.12 =
* Upgraded Closure to latest version
* Fixed MMR so it works when WordPress and wp-content are in non-standard locations
* Fixed MMR admin unecessarily updating log text

= 1.8.11 =
* Upgraded Minify and Closure to latest versions

= 1.8.10 =
* Fix for PHP < 7. Thanks to @Roy

= 1.8.9 =
* Fix for incorrect function name

= 1.8.8 =
* Multisite URL Fixes
* Added action that fires when JS or CSS has updated. Thanks to @lucasbustamante
* Update Closure to latest

= 1.8.7 =
* Update Java detection for Java 9+
* Clear scheduled hooks on purge and plugin deactivation
* Update Closure to latest

= 1.8.6 =
* Multisite Network Support

= 1.8.5 =
* Closure now works with string continuations (removed compatibility code)
* Fix potential bug with minification

= 1.8.4 =
* Fix issue with Last Accessed
* Fix issue with css files not being enqueued

= 1.8.3 =
* Fix issue with wp_localize_script data not being set correctly

= 1.8.2 =
* Massive code cleanup.
* Disable MMR in frontend edit mode of Visual Composer

= 1.8.1 =
* Ensure enqueued css/js have unique handles (fix for NEX-Forms)

= 1.8 =
* Updated Closure and Minify to latest versions
* Code cleanup
* MMR now checks file extension so that plugins that enqueue php files work (motopress etc.)

= 1.7.6 =
* Added Support for Gonzales. (https://tomasz-dobrzynski.com/wordpress-gonzales)

= 1.7.5 =
* Fix WordPress in subfolder identification issue. (Bedrock compatibility - Thanks plankguy)

= 1.7.4 =
* Fix usage of clone for php7 compatibility check (Thanks for the heads up mariodabek)

= 1.7.3 =
* Improve CSS concatenation (Thanks to fhoech)

= 1.7.2 =
* older versions of gzip don’t have the -keep flag

= 1.7.1 =
* Fix HTTP2 server push only adding header for one file & ignoring settings

= 1.7.0 =
* Added the ability to generate .css.gz & .js.gz files (Thanks to Marcus Svensson)

= 1.6.14 =
* Fix strange characters message when activating plugin

= 1.6.13 =
* Improvements to http2 push (thanks to Daniel Aleksandersen - https://ctrl.blog)

= 1.6.12 =
* Initialise wp_scripts & wp_styles if they haven't loaded (Thanks Andrew Miguelez)
* Replace depreciated wp_clone with clone
* Improved java detection
* Upgrade to latest version of Minify
* Upgrade to latest version of Closure

= 1.6.11 =
* Ability to seperate cache urls for javascript and CSS (MMR_JS_CACHE_URL & MMR_CSS_CACHE_URL)

= 1.6.10 =
* Fix concatenation bug (thanks fhoech)

= 1.6.9 =
* Better support for CSS output to the page. This should fix some themes that break.
* Ability to specify cache directory added. Thanks to Daniel Aleksandersen for help with this.
* Minor code cleanup

= 1.6.8 =
* Fix External Styles/Scripts ordering

= 1.6.7 =
* Check Java version is sufficient for Google Closure to work

= 1.6.6 =
* Fix bugs introduced by 1.6.4

= 1.6.5 =
* Fix bugs introduced by 1.6.4

= 1.6.4 =
* Fix MMR when WordPress installed in a sub folder

= 1.6.3 =
* Ignore conditional scripts and styles

= 1.6.2 =
* Upgraded Minify to latest version
* Upgraded Closure to latest version

= 1.6.1 =
* MMR looks for non minified scripts and styles by default (eg. script.min.js = script.js). If found it will use them. This improves compatibility and can fix minification errors.

= 1.6 =
* Option to enable http2 server push (thanks to Daniel Aleksandersen - https://ctrl.blog)
* Option to enable output buffering for compatability and so footer scripts can be HTTP2 pushed
* Ability to turn off minification
* Ability to turn off concatination
* Reduced plugin memory usage by only including Minify when required
* Ability to manually ignore scripts or css
* Changed hashes from md5 to adler32 as it is faster

= 1.5.2 =
* Upgrade Minify to latest version

= 1.5.1 =
* Only load admin js & css on the mmr options page

= 1.5 =
* Display last accessed date in admin
* Remove unused variables
* Option to turn off merging

= 1.4.3 =
* Fix wrong variable name

= 1.4.2 =
* Append ; to merged script files to prevent javascript errors

= 1.4.1 =
* woocommerce compatability

= 1.4 =
* Remove unused code in insepect_scripts()
* Resolved issues with late enqued scripts and styles
* Changed code for removal of string continuations
* Stopped dequeing styles and scripts as we now use done

= 1.3 =
* Process styles/scriptes enqued within body of page in the footer
* Prevent scripts enqued within body of page outputting twice
* Clear previous processed files fully when purge all clicked

= 1.2 =
* Bugfix

= 1.1 =
* Only write admin ajax response when it has changed
* CSS now compressed using Minify
* JS compressed with Minify when Closure not available

= 1.0 =
* Don't remove unminified files anymore for rare occasions when css or js return a 404 error
* Admin now updates automatically.

= 0.9 =
* Fix issue with scripts failing to compile because of remove_continuations

= 0.8 =
* Fix bug when javascript and css has same handle

= 0.7 =
* Bugfix

= 0.6 =
* Remove Javascript String Continuations
* Show queued scripts/css in admin
* Prevent YUI Compressor stripping 0 second units (minified transitions now work)

= 0.5 =
* Ensure file paths are absolute
* Use ABSPATH instead of DOCUMENT_ROOT

= 0.4 =
* Ignore CSS url paths that start with http

= 0.3 =
* Minor code refactoring and cleanup

= 0.2 =
* Log error when exec not available
* Fix remote url detection
* Fix admin header redirect

= 0.1 =
* Initial Release

== Upgrade Notice ==

= 1.10.4 =
If your site uses DIVI, Visual Composer or Beaver Builder please make sure you install one of the additional MMR plugins.

= 1.10.3 =
If your site uses DIVI, Visual Composer or Beaver Builder please make sure you install one of the additional MMR plugins.

= 1.10.2 =
If your site uses DIVI, Visual Composer or Beaver Builder please make sure you install one of the additional MMR plugins.

= 1.10.1 =
If your site uses DIVI, Visual Composer or Beaver Builder please make sure you install one of the additional MMR plugins.

= 1.10 =
If your site uses DIVI, Visual Composer or Beaver Builder please make sure you install one of the additional MMR plugins.
