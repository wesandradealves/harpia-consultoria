<?php
/**
 * Uninstall plugin.
 * 
 * @package cf7Skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */

 
// Exit if this file is not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Check if user wants to retain plugin data
$option = get_option( CF7SKINS_OPTIONS );  // @previous $option = get_option( 'cf7skins' );

if ( isset( $option['delete_data'] ) && $option['delete_data'] ) {

	// Delete plugin options from options table
	delete_option( 'cf7skins' );
	delete_option( 'cf7skins_version_installed' );
	delete_option( 'cf7skins_activated' );
	delete_option( 'cf7skins_get_version' );
	delete_option( 'cf7skins_activation' );
	delete_option( 'cf7skins_deactivation' );
	delete_option( 'cf7skins_license_status' );

	// Delete all post meta by key from postmeta table
	delete_post_meta_by_key( 'cf7s_template' );
	delete_post_meta_by_key( 'cf7s_style' );
	delete_post_meta_by_key( 'cf7s_postbox' );
}
