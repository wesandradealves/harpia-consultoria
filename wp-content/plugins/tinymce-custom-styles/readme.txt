=== TinyMCE Custom Styles ===
Contributors:  Tim Reeves, Blackbam, rawrly
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NBW9FDZHW42GY
Tags: tinymce, visual, editor, style, format, custom
Requires at least: 5.0
Requires PHP: 5.6
Tested up to: 6.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhance the TinyMCE visual editor with a dedicated stylesheet, a stylesheet shared with the frontend, and custom styles in the 'Formats' dropdown.

== Description ==

**Please someone take over maintaining this plugin, or it will get abandoned - with over 9.000 active installations.**

I'm now 68 and although retired, all my time is taken up with other projects, while I still have the energy for them. Why you might want to take it over? It's really useful:

Make your editing experience as simple and good as possible by improving the way you work with the TinyMCE visual editor (including Gutenberg Classic block). This plugin adds custom CSS file(s) to the frontend and to the TinyMCE editor; and it allows you to populate TinyMCE's 'Formats' dropdown with your own styles. The features in more detail:

**1.** Installs two CSS stylesheet files into your chosen location (so you can still do updates of the active theme and this plugin and even switch to another theme). In general you will need to fetch the auto-created stub files via FTP, edit them locally and upload them, overwriting the previous versions.

- *editor-style.css* is used for styling your TinyMCE Editor
- *editor-style-shared.css* is used for styles to be used by the frontend AND in your TinyMCE editor (so you do not have to copy)

To use this feature, you have to write your own CSS code into these files.

**2.** The main feature of this Plugin is to offer a Backend-GUI to create custom styles for TinyMCE ('Formats' dropdown) dynamically.

- Easy to add, change and delete styles
- No editing of source code required (excepting the CSS stylesheets)
- Allows you to add block- and inline elements which are not provided by TinyMCE, e.g. &lt;figure&gt;, &lt;cite&gt;, &lt;q&gt; etc.
- The plugin's backend page contains a lot of description and some links to help you define your styles correctly
- Note that each style can have both CSS Classes and/or CSS Inline-Styles. The latter (excepting color) will even be applied to the Formats dropdown menu items. However, I do not recommend inline styles as they are inserted into the text of the post/page and remain unchanged if you later alter the style in the plugin settings - so in general stick to Classes!

**How the two stylesheets are applied**
The *shared style sheet* file is enqueued to be included on frontend pages (via the usual &lt;link&gt; tag in the &lt;head&gt; area) using the standard WordPress function <a href="https://developer.wordpress.org/reference/functions/wp_enqueue_style/" target="_blank">wp_enqueue_style</a>.

So, as with most other stylesheets, the statements in it will automatically apply to the whole HTML page. So define class names which will not collide with any already in use by the theme – and do not define styles for HTML elements without a limiting class name unless you want them to apply to all elements of that tag type (including in header, footer, sidebar...).

*Both stylesheets* are passed to TinyMCE by calling: add_filter(‘mce_css’, …)

What this causes to happen is that they are linked in to the HTML document which is the source for an &lt;iframe&gt;, which is the editing area of TinyMCE. So they should definitely only apply to HTML in the iframe - although I have heard that some situations, e.g. a cache plugin, may break this mechanism.

**Gutenberg classic blocks**
As of version 1.1.1 this plugin works for the Gutenberg classic block.

**WordPress MultiSite**
Although it does not check for MultiSite, the plugin works in the MultiSite environment, since WordPress uses a separate Options table for each MultiSite. You can reuse the same CSS files (by supplying the same custom directory in each Site), or add separate ones for each Site.

**Current Languages**
- en_US
- de_DE (Tim Reeves)

== Background Information ==

**Then and Now**
This plugin was originally written by David Stöckl in 2013 - long before Gutenberg had been conceived, and at a time when several different plugins all tried to enhance the TinyMCE editor in different ways. It was abandoned a year later by David and I forked it in 2016, renamed to TinyMCE Custom Styles" ("TCS"). Most of those other TinyMCE-related plugins, notably WP Edit, are now abandoned; apart from this plugin, only TinyMCE Advanced, now also handling Gutenberg and renamed to <a href="https://wordpress.org/plugins/tinymce-advanced/" target="_blank">Advanced Editor Tools</a> (abbreviated hereafter to "AET"), and <a href="https://wordpress.org/plugins/just-tinymce-styles/">Just TinyMCE Custom Styles</a>, seem to still be notably active in this area. AET is a great plugin - you can also check out its companion for developers <a href="https://wordpress.org/plugins/advanced-tinymce-configuration/" target="_blank">Advanced TinyMCE Configuration</a>. I use AET myself on most websites - but there are a few things it's not designed to do, and that's where this plugin fills the gap. You can consider it as AET's sidekick :) If you do not need the feature with the two CSS stylesheet files, then you might consider using "Just TinyMCE Custom Styles", which offers only the 'Formats' and has a modern user interface (however, it currently looks abandoned, and the description here of how to use the features is more exhaustive).

**TinyMCE and the WordPress theme**
The goal is to configure the TinyMCE backend editor so that its 'Visual' tab displays content as closely as possible to how it will look on the content area of the actual website. To this end, WordPress has for years provided a feature which can be used by themes, called 'editor styles'. This allows a theme to make known to WordPress one or more CSS files, which should contain a subset of the theme's styles, those which apply to the display of content in the content area (i.e. excluding styles applying to headers, sidebars, footers, archives, comments, ...). If the theme provides this feature, that CSS file (or files) are loaded to TinyMCE to achieve the goal. The default location is one file named 'editor-style.css' in the theme's root directory. In fact, WordPress seems to find this file, if present, even if the theme does not register it. All good modern themes provide this feature.

**Advanced Editor Tools**
The really good plugin 'Advanced Editor Tools' ("AET", from and maintained by Andrew Ozz, a WordPress core developer, now attributed to Automattic, the company effectively behind WordPress) helps with one of the problems noted above: It gives you complete freedom to select which buttons and dropdowns are displayed in TinyMCEs header - in particular you can just drag the 'Formats' dropdown into one of the bar areas, exactly where you want it. AET also has a number of other options, for example to prevent TinyMCE from removing tags and minifying the text in the 'Text' tab, which is very useful when you need to look at the HTML and do any manual adjustment.

**Shortcomings in the standard setup**
There are some regrettable weaknesses in the unenhanced situation:

- Any custom CSS which you set in the WordPress Customizer is not applied to TinyMCE (it is written out as direct styles in the &lt;head&gt; of each website page).

- The styles which the theme provides for TinyMCE are applied to the HTML displayed by the editor. This is fine with fixed styling of elements like &lt;p&gt;, &lt;ul&gt; or &lt;blockquote&gt;. But a theme may also include optional styles to change or enhance the display of an element - e.g. '.small', '.screen-reader-text', '.beforelist', '.hilitebox' and so on. In this case, and without help from any plugin, there is no way to select any of them from the menu or toolbar of TinyMCE in order to apply them to an element, so a user would need to know the style names and apply them manually in the 'Text' tab - not good. See below 'importcss', but note that it overwrites anything other plugins have put in the 'Formats' dropdown.

- In the standard configuration (i.e. without enhancer plugins) TinyMCE is not even configured to show the 'Formats' dropdown, which we need to apply custom styles to elements in the text.

**The TinyMCE Formats dropdown**
Internal name: 'styleselect'. By default, this dropdown is not displayed. You can <a href="https://codex.wordpress.org/TinyMCE_Custom_Styles" target="_blank">add code</a> e.g. to your theme's functions.php, to have it shown. Its default contents are 4 entries with corresponding submenus: Headings, Inline, Blocks and Alignement.

TCS always registers the 'Formats' dropdown to TinyMCE's second toolbar, this does not seem to be a problem for AET.

It is in this area that TCS is really usefull: It allows you to create *named* styles, so you can name them descriptively, e.g. to show if the style is a block or inline style, if it uses the Wrapper option, and so on. Basically you will be adding styles to the dropdown which are defined in a stylesheet from the theme, or in your 'editor-style-shared.css', to allow you (or your customer) convenient and understandable access to them while editing.

**The TinyMCE JavaScript plugin 'importcss'**
When the AET plugin is active, it offers an option "Create CSS classes menu" (subtitle: Load the CSS classes used in editor-style.css and replace the Formats menu). When checked, a TinyMCE JavaScript plugin called 'importcss' is loaded to the frontend, which parses the CSS loaded to TinyMCE (i.e. from your theme's 'editor styles' and this plugins 'editor-style.css') and populates the 'Formats' dropdown with a selection of those styles. Styles applying directly to HTML elements without a class name are skipped. Styles applying to a tag and containing a class, e.g. "h1.page-title" will be included and work as expected. But for classes not limited to a tag, it has no way of knowing if the class is intended to be applied to a block element or as an inline element, nor to which tags it should apply or should use, so it simply offers them as inline elements, which may or may not be their intended use. Styles with a pseudo-class or pseudo-element (e.g. ':hover', '::before') will be omitted. The bottom line is, that however good the themes editor styles are, we end up with a sub-optimal population of the Formats dropdown: It will be too long (including many styles we don't need or don't understand), and some valuable styles will be missing or wrongly classified as inline styles. Given all these shortcomings, I have not found this feature to be of much use in practice.

== Credits ==

This plugin is a fork of <a href="https://wordpress.org/plugins/tinymce-and-tinymce-advanced-professsional-formats-and-styles/" target="_blank">TinyMCE Advanced Professsional Formats and Styles</a> which has been abandoned by the original author. Initially I just fixed a JavaScript bug so that it worked again, and cleaned up the code and messages a bit. Since then, a number of further improvements, see the changelog. I was born in 1954 and would be glad if someone else would now take over this plugin and further improve it. Translations are also very welcome.

- <a href="https://blog.blackbam.at/" target="_blank">David Stöckl</a>, Vienna, the original author. Many thanks!
- The plugin icon (<a href="https://timreeves.de/kompetenz/" target="_blank">Der Bitkönig</a>) was drawn by Gabriele Meischner, muchas Gracias!
- <a href="https://profiles.wordpress.org/rawrly/" target="_blank">Rawrly</a> for providing security updates.

== Report Security Bugs ==

Please report all security bugs found in this project by following the <a href="https://patchstack.com/database/vdp/tinymce-custom-styles">vulnerability disclosure process</a>.

== Installation ==
1. Upload the Plugin to your wp-content/plugins/ folder
2. Activate the Plugin
3. Go to Settings -> TinyMCE Custom Styles
4. Follow the instructions on the screen - write your CSS and create your custom formats

Important: Some Settings of TinyMCE or certain TinyMCE Plugins require you to do some manual settings for the Plugin to work. The Plugin WILL work, if you configure it correctly - check the FAQ for help.

== Frequently Asked Questions ==
= I cannot edit editor-style.css and editor-style-shared.css. What is wrong? =

The Plugin was probably not able to create the files, due to problems with your server filesystem settings. Please create these files in the selected directory manually, and make sure the directory read/write access is set to 777.

= I have edited the files editor-style.css and editor-style-shared.css, but my visual editor is not styled. What is the problem? =

1. You should empty the cache of your Web Browser, this is often the reason for the styles being applied with some delay.
2. Check this with simple styles like body { background-color:black; } to see if it basically works.
3. Maybe there are some functions inside of your Theme / other Plugin, which overwrite the settings of this Plugin. Please check this out as it seems to work in most cases.

= The file editor-style.css is not working in the frontend of my website, but it is applied to the backend editor. Why? =

Make sure that your Theme calls the function wp_head(); inside the header of your template.

= I have created some custom formats/styles, I can see the dropdown, but the formats/styles which I have created on the settings Page just do not work. What is wrong? =

You have to be careful when creating custom styles if you are doing it for the first time. If you make a row with an HTML blockquote element and you choose "Inline" from the radio buttons, this style will NOT work, as blockquote is not an HTML inline element.

Try something easy like:
- Name: My red text
- Type: inline
- Type value: span
- CSS style: color / #f00

Check if this style works. If so, proceed to other styles. They will only work if you use correct semantics.

= Does it work with shortcodes? =

In general, no, because the shortcode is only expanded to HTML in the frontend - in the backend (editor) the shortcode is normally displayed as a shortcode, to allow it to be seen and edited (e.g. change shortcode parameters, position on the page etc.). Styles in editor-style-shared.css which match the HTML generated by the shortcode will of course work - in the frontend. There is no point putting such styles in editor-style-shared.css, so if you prefer, put them in the themes custom CSS (if provided, but not WordPress customizer, as that inserts the CSS inline on every page).

= What about media from the media library? =

An image for example, when inserted via the media library, will show as the [caption] shortcode in the "Text" tab of the editor. To allow the page to be viewed visually, an exception is made and on the visual tab the image and caption are displayed, as TinyMCE itself replaces the shortcode with HTML. The HTML generated by TinyMCE (div dl dt a img br dd) is very different to that generated by WordPress at the frontend (div a img p). So in general, I recommend to use the text tab to fix image styles / encapsulation - my experience is that it's a real hit and miss affair with styles in the 'Formats' dropdown (more miss than hit).

== Screenshots ==

1. /assets/screenshot-1.png
2. /assets/screenshot-2.png

== Changelog ==

= 1.1.4 (2023-06-06) =
* Security - More sanitize and escape admin panel settings inputs, as requested by Jetpack Support

= 1.1.3 (2023-01-27) =
* Security - Sanitize and escape admin panel settings inputs (rawrly)
* Documentation - Add note in description to report security bugs through Patchstack

= 1.1.2 (2023-01-24) =
* Removed broken link in credits

= 1.1.1 (2021-10-25) =
* Bugfix - call of wp_enqueue_scripts corrected to wp_enqueue_style
* Enhancement - enqueue both stylesheets also as Gutenberg block assets

= 1.1.0 (2021-04-07) =
* editor-style.css and editor-style-shared.css: Both files must be present or a (non-empty) stub will be created; but if a file is zero bytes long, it will not be loaded / enqueued at all
* Styles defined for the 'Formats' dropdown which do not have a class now do not create an empty class attribute in HTML
* Regrouping of items on the settings page to make clearer which elements belong to which functionality
* Very major rework of the documentation - both in the description page and on the settings page (backend)

= 1.0.10 (2021-03-07) =
* Fixed a typo which prevented custom style deletion
* Documented that Gutenberg classic blocks not supported

= 1.0.9 (2020-07-01) =
* Typo-correction in plugin description

= 1.0.8 (2018-01-13) =
* Improved plugin description

= 1.0.7 (2017-12-28) =
* Learned more on SVN and really deleted/added language files

= 1.0.6 (2017-12-28) =
* Removed unneccesary and outdated translation files

= 1.0.5 (2017-12-28) =
* Small formal improvements re translation
* Added .pot string translation file

= 1.0.4 (2017-08-10) =
* Standard entries and any other previous entries (from other plugins) in TinyMCE's Formats dropdown are now preserved
* Added a checkbox option to allow inserting the custom styles in a submenu "Custom Styles" in the Formats dropdown
* Added a checkbox option to govern whether to preserve the standard Formats entries or remove them. This option gets overwritten (to 'preserve') by the 'WP Edit' plugin with option 'Add Pre-defined Styles' checked.
* Minor updates to german translation
* Outdated spanish and serbien translations removed
* Tested and described on WordPress MultiSite
* Various other improvements in the description

= 1.0.3 =
* Updated german translation
* Made some table header names translatable
* Shortened some table input fields for better layout

= 1.0.2 =
* This update improves the settings page as follows
* Added second save button
* Complete rework of admin notices
* Many minor layout and message improvements
* Corrected several loop counter irritations
* Improved how missing style files are tested
* Improved logic and messages around custom directory
* Added more explanation and sample content in auto-created editor-style-shared.css
* Reduced PHP Warnings and Notices to zero

= 1.0.1 =
* Initial release as fork.

= ToDos =
* Warn admin user when leaving changed settings page
* Add a JS function to insert a row after another row
* OR better: Make style rows sortable by drag and drop
* Nice to have: Rewrite the code from procedural to OOP
* Get more translations

== Upgrade Notice ==

= 1.0.11 =
Major rework and extension of the description page (wordpress.org) and the settings page (backend) helps you get more out of TinyMCE. Minor code improvements.
