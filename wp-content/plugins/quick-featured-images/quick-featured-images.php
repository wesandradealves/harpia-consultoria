<?php
/**
 * @package   Quick_Featured_Images_Admin
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 * @license   GPL-2.0+
 * @link      https://www.kybernetik-services.com/
 * @copyright 2014 Kybernetik Services
 *
 * @wordpress-plugin
 * Plugin Name:       Quick Featured Images
 * Plugin URI:        http://wordpress.org/plugins/quick-featured-images
 * Description:       Your time-saving Swiss Army Knife for featured images: Set, replace and delete them in bulk, in posts lists and set default images for future posts.
 * Version:           13.7.0
 * Requires at least: 3.8
 * Requires PHP:      5.2
 * Author:            Kybernetik Services
 * Author URI:        https://www.kybernetik-services.com/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=quick-featured-images&utm_content=author
 * Text Domain:       quick-featured-images
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'QFI_ROOT', plugin_dir_path( __FILE__ ) );
define( 'QFI_ROOT_URL', plugin_dir_url( __FILE__ ) );
const QFI_VERSION = '13.7.0';

function qfi_autoloader( $class_name )
{
    if ( false !== strpos( $class_name, 'Quick_Featured_Images' ) ) {
        include QFI_ROOT . 'admin/class-' . $class_name . '.php';
    }
}
spl_autoload_register('qfi_autoloader');

/*
 * since 1.0: Make object instance of base class
 *
 */
add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Admin', 'get_instance' ) );


if ( is_admin() ) {

	/*
	 * Register hooks that are fired when the plugin is activated or deactivated.
	 * When the plugin is deleted, the uninstall.php file is loaded.
	 *
	 */
	register_activation_hook( __FILE__, array( 'Quick_Featured_Images_Admin', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Quick_Featured_Images_Admin', 'deactivate' ) );

	/*
	 * Make object instance of bulk tools class
	 *
	 */
	add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Tools', 'get_instance' ) );

}

/*
 * since 8.0: Make object instance of default images functions class
 *
 */
add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Defaults', 'get_instance' ) );


if ( is_admin() ) {
	/*
	 * since 7.0: Make object instance of options page class
	 */
	add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Settings', 'get_instance' ) );

	/*
	 * since 7.0: Make object instance of column functions class
	 */
	add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Columns', 'get_instance' ) );

    /*
     * since 13.6.0: Make object instance of comparison functions class
     */
    add_action( 'plugins_loaded', array( 'Quick_Featured_Images_Comparison', 'get_instance' ) );
}
