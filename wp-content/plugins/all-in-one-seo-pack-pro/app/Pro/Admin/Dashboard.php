<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Class that holds our dashboard widget.
 *
 * @since 4.0.0
 */
class Dashboard extends CommonAdmin\Dashboard {
	/**
	 * Whether or not to show the widget.
	 *
	 * @since   4.0.0
	 * @version 4.2.8
	 *
	 * @param  string  $widget The widget to check if can show.
	 * @return boolean         True if yes, false otherwise.
	 */
	protected function canShowWidget( $widget ) {
		if ( ! aioseo()->license->isActive() ) {
			return true;
		}

		// If it's a boolean, return it early.
		// https://github.com/awesomemotive/aioseo/issues/4280
		$dashboardWidgets = aioseo()->options->advanced->dashboardWidgets;
		if ( is_bool( $dashboardWidgets ) ) {
			return $dashboardWidgets;
		}

		// Check if the widget is displayable.
		return in_array( $widget, $dashboardWidgets, true );
	}
}