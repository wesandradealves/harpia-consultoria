<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Video Sitemap settings.
 *
 * @since 4.0.0
 */
class VideoSitemap {
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
		$this->options = get_option( 'wpseo_video' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateIncludedPostTypes();
		$this->migrateIncludedTaxonomies();
	}

	/**
	 * Migrates the included post types.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateIncludedPostTypes() {
		if ( isset( $this->options['videositemap_posttypes'] ) ) {
			$allowedPostTypes = array_diff(
				aioseo()->helpers->getPublicPostTypes( true ), aioseo()->helpers->getNoindexedPostTypes()
			);
			$includedPostTypes = array_values(
				array_intersect( $allowedPostTypes, $this->options['videositemap_posttypes'] )
			);

			aioseo()->options->sitemap->video->postTypes->included = $includedPostTypes;

			if ( count( $includedPostTypes ) !== count( $allowedPostTypes ) ) {
				aioseo()->options->sitemap->video->postTypes->all = false;
			}
		}
	}

	/**
	 * Migrates the included taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateIncludedTaxonomies() {
		if ( isset( $this->options['videositemap_taxonomies'] ) ) {
			$allowedTaxonomies = array_diff(
				aioseo()->helpers->getPublicTaxonomies( true ), aioseo()->helpers->getNoindexedTaxonomies(), [ 'category', 'post_tag' ]
			);
			$includedTaxonomies = array_values(
				array_intersect( $allowedTaxonomies, $this->options['videositemap_taxonomies'] )
			);

			aioseo()->options->sitemap->video->taxonomies->included = $includedTaxonomies;

			if ( count( $includedTaxonomies ) !== count( $allowedTaxonomies ) ) {
				aioseo()->options->sitemap->video->taxonomies->all = false;
			}
		}
	}
}