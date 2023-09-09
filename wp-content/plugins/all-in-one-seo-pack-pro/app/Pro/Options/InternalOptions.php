<?php
namespace AIOSEO\Plugin\Pro\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Class that holds all internal options for AIOSEO.
 *
 * @since 4.0.0
 */
class InternalOptions extends CommonOptions\InternalOptions {
	use Traits\Options;

	/**
	 * Defaults options for Pro.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $proDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal' => [
			'activated'      => [ 'type' => 'number', 'default' => 0 ],
			'firstActivated' => [ 'type' => 'number', 'default' => 0 ],
			'installed'      => [ 'type' => 'number', 'default' => 0 ],
			'license'        => [
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
			'schema'         => [
				'templates' => [ 'type' => 'array', 'default' => [] ]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];
}