<?php
/**
 * =======================================
 * WP Nav Plus - Legacy
 * =======================================
 *
 * Starting with version 3.0, using the wp_nav_plus function in place of the wp_nav_menu function is no longer
 * needed. This file is here to preserve functionality for those already using the wp_nav_plus function
 *
 * This function will be removed in a later version, you should switch to the wp_nav_menu function now.
 * Simply replace all calls to wp_nav_plus with wp_nav_menu, none of the arguments have changed.
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

function wp_nav_plus( $args ) {
	_deprecated_function( 'wp_nav_plus', '3.0', 'wp_nav_menu' );

	$echo = true;

	if ( isset( $args['echo'] ) ) {
		$echo = $args['echo'];
	}

	if ( $echo ) {
		wp_nav_menu( $args );
	} else {
		return wp_nav_menu( $args );
	}
}