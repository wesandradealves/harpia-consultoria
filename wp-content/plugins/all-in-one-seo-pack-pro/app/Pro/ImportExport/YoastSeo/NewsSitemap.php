<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the News Sitemap settings.
 *
 * @since 4.0.0
 */
class NewsSitemap {
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
		$this->options = get_option( 'wpseo_news' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateIncludedPostTypes();

		$settings = [
			'news_sitemap_name' => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'news', 'publicationName' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the included post types.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateIncludedPostTypes() {
		if ( isset( $this->options['news_sitemap_include_post_types'] ) ) {
			$allowedPostTypes  = array_diff(
				aioseo()->helpers->getPublicPostTypes( true ), aioseo()->helpers->getNoindexedPostTypes(), [ 'attachment' ]
			);
			$includedPostTypes = array_values(
				array_intersect( $allowedPostTypes, array_keys( $this->options['news_sitemap_include_post_types'] ) )
			);

			aioseo()->options->sitemap->news->postTypes->included = $includedPostTypes;

			if ( count( $includedPostTypes ) !== count( $allowedPostTypes ) ) {
				aioseo()->options->sitemap->news->postTypes->all = false;
			}
		}
	}
}