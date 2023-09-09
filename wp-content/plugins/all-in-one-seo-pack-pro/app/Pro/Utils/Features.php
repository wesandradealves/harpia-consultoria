<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Contains helper methods specific to the addons.
 *
 * @since 4.3.0
 */
class Features extends CommonUtils\Features {
	/**
	 * The features URL.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $featuresUrl = 'https://licensing-cdn.aioseo.com/keys/pro/all-in-one-seo-pack-pro-features.json';
}