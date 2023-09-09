<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Social Meta.
 *
 * @since 4.0.0
 */
class SocialMeta {
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
		$this->options = get_option( 'wpseo_social' );

		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateFacebookSettings();
	}

	/**
	 * Migrates the Facebook settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateFacebookSettings() {
		if ( ! empty( $this->options['og_default_image'] ) ) {
			$defaultImage = esc_url( $this->options['og_default_image'] );
			aioseo()->options->social->facebook->general->defaultImageTerms       = $defaultImage;
			aioseo()->options->social->facebook->general->defaultImageSourceTerms = 'default';

			aioseo()->options->social->twitter->general->defaultImageTerms       = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImageSourceTerms = 'default';
		}
	}
}