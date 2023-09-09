<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils as CommonUtils;
use AIOSEO\Plugin\Pro\Traits\Helpers as TraitHelpers;

/**
 * Contains helper functions.
 *
 * @since 4.0.0
 */
class Helpers extends CommonUtils\Helpers {
	use TraitHelpers\ThirdParty;
	use TraitHelpers\Vue;

	/**
	 * Get the headers for internal API requests.
	 *
	 * @since 4.2.4
	 *
	 * @return array An array of headers.
	 */
	public function getApiHeaders() {
		return [
			'X-AIOSEO-License' => aioseo()->options->general->licenseKey
		];
	}

	/**
	 * Get the User Agent for internal API requests.
	 *
	 * @since 4.2.4
	 *
	 * @return string The User Agent.
	 */
	public function getApiUserAgent() {
		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; AIOSEO/Pro/' . AIOSEO_VERSION;
	}
}