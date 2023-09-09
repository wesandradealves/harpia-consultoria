<?php
namespace AIOSEO\Plugin\Pro\ImportExport\RankMath;

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
		$this->options = get_option( 'rank-math-options-general' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateImageSeoSettings();
	}

	/**
	 * Migrates the image attribute formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateImageSeoSettings() {
		if ( isset( $this->options['img_title_format'] ) ) {
			$title = aioseo()->helpers->pregReplace( '/%title%/', '#post_seo_title', $this->options['img_title_format'] );
			aioseo()->options->image->format->title =
				aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $title ) );
		}

		if ( isset( $this->options['img_alt_format'] ) ) {
			$alt = aioseo()->helpers->pregReplace( '/%title%/', '#post_seo_title', $this->options['img_alt_format'] );
			aioseo()->options->image->format->alt =
				aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $alt ) );
		}
	}
}