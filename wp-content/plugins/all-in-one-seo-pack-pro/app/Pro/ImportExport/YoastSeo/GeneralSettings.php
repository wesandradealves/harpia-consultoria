<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings.
 *
 * @since 4.0.0
 */
class GeneralSettings {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo' );
		if ( empty( $this->options ) ) {
			return;
		}

		$settings = [
			'enable_admin_bar_menu' => [ 'type' => 'boolean', 'newOption' => [ 'advanced', 'adminBarMenu' ] ],
			'tracking'              => [ 'type' => 'boolean', 'newOption' => [ 'advanced', 'usageTracking' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}
}