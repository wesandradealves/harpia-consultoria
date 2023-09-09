<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Lite\Main as LiteMain;

/**
 * Filters class with methods that are called.
 *
 * @since 4.0.0
 */
class Filters extends LiteMain\Filters {
	/**
	 * Registers our action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions    List of existing actions.
	 * @param  string $pluginFile The plugin file.
	 * @return array              List of action links.
	 */
	public function pluginActionLinks( $actions, $pluginFile = '' ) {
		$actionLinks = parent::pluginActionLinks( $actions, $pluginFile );

		// We don't need a Pro upgrade link here so we can unset it.
		unset( $actionLinks['proupgrade'] );

		return $actionLinks;
	}
}