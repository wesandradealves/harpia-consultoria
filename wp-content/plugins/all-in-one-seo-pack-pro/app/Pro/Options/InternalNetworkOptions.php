<?php
namespace AIOSEO\Plugin\Pro\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Class that holds all internal network options for AIOSEO.
 *
 * @since 4.2.5
 */
class InternalNetworkOptions extends CommonOptions\InternalNetworkOptions {
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
		'internal' => [
			'license' => [
				'expires'          => [ 'type' => 'number', 'default' => 0 ],
				'expired'          => [ 'type' => 'boolean', 'default' => false ],
				'invalid'          => [ 'type' => 'boolean', 'default' => false ],
				'disabled'         => [ 'type' => 'boolean', 'default' => false ],
				'connectionError'  => [ 'type' => 'boolean', 'default' => false ],
				'activationsError' => [ 'type' => 'boolean', 'default' => false ],
				'requestError'     => [ 'type' => 'boolean', 'default' => false ],
				'lastChecked'      => [ 'type' => 'number', 'default' => 0 ],
				'level'            => [ 'type' => 'string' ],
				'addons'           => [ 'type' => 'string', 'default' => '' ],
				'features'         => [ 'type' => 'string', 'default' => '' ]
			],
			'sites'   => [
				'active' => [ 'type' => 'string' ],
				'failed' => [ 'type' => 'string' ]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];
}