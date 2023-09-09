<?php
namespace AIOSEO\Plugin\Pro\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Social as CommonSocial;

/**
 * Handles our social meta.
 *
 * @since 4.0.0
 */
class Output extends CommonSocial\Output {
	/**
	 * Checks if the current page should have social meta.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether or not the page should have social meta.
	 */
	public function isAllowed() {
		if (
			! is_category() &&
			! is_tag() &&
			! is_tax()
		) {
			return parent::isAllowed();
		}

		return true;
	}
}