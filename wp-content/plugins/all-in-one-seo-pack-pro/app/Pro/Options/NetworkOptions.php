<?php
namespace AIOSEO\Plugin\Pro\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Class that holds all network options for AIOSEO.
 *
 * @since 4.2.5
 */
class NetworkOptions extends CommonOptions\NetworkOptions {
	use Traits\NetworkOptions;

	/**
	 * Defaults options for Pro.
	 *
	 * @since 4.2.5
	 *
	 * @var array
	 */
	private $proDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'general' => [
			'licenseKey' => [ 'type' => 'string' ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];
}