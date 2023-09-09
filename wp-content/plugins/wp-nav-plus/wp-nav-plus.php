<?php
/*
Plugin Name: WP Nav Plus
Description: WP Nav Plus provides “missing” functionality from the WordPress menu system. Use WP Nav Plus for split menus, divided menus, menu segments, or to limit and/or offset the menu output.
Version: 3.4.9
Author: mattkeys
Author URI: http://mattkeys.me
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-nav-plus
Domain Path: /languages
*/

// Path to this file
if ( ! defined( 'WPNP_PLUGIN_FILE' ) ) {
	define( 'WPNP_PLUGIN_FILE', __FILE__ );
}

// Path to the plugin's directory
if ( ! defined( 'WPNP_DIRECTORY' ) ) {
	define( 'WPNP_DIRECTORY', dirname( __FILE__ ) );
}

// Publicly Accessible path
if ( ! defined( 'WPNP_PUBLIC_PATH' ) ) {
	define( 'WPNP_PUBLIC_PATH', plugin_dir_url( __FILE__ ) );
}

// Load the plugin
require_once 'feature_classes/start_depth.php';
require_once 'feature_classes/divided_menu.php';
require_once 'feature_classes/limit_offset.php';
require_once 'feature_classes/menu_segment.php';
require_once 'widget.php';
require_once 'legacy.php';
