<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Activate class with methods that are called.
 *
 * @since 4.0.0
 */
class Activate extends CommonMain\Activate {
	/**
	 * Runs on activate.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool $networkWide Whether or not this is a network wide activation.
	 * @return void
	 */
	public function activate( $networkWide ) {
		if ( is_multisite() && $networkWide ) {
			foreach ( aioseo()->helpers->getSites()['sites'] as $site ) {
				aioseo()->helpers->switchToBlog( $site->blog_id );
				aioseo()->access->addCapabilities();
				aioseo()->helpers->restoreCurrentBlog();
			}
		}

		parent::activate( $networkWide );

		// Let's re-sync the license.
		if ( aioseo()->license->isActive() ) {
			aioseo()->license->activate();
		}
	}
}