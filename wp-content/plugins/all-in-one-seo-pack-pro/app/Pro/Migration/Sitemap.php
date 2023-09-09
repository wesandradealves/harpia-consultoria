<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the XML Sitemap settings from V3.
 *
 * @since 4.0.0
 */
class Sitemap {
	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->oldOptions = aioseo()->migration->oldOptions;

		if (
			empty( $this->oldOptions['modules']['aiosp_sitemap_options'] ) ||
			! isset( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes_news'] ) ||
			! aioseo()->license->isActive()
		) {
			return;
		}

		if ( ! empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_publication_name'] ) ) {
			aioseo()->options->sitemap->news->publicationName = aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_publication_name'] );
		}

		$publicPostTypes = aioseo()->helpers->getPublicPostTypes( true );

		if ( in_array( 'all', (array) $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes_news'], true ) ) {
			aioseo()->options->sitemap->news->postTypes->all      = true;
			aioseo()->options->sitemap->news->postTypes->included = array_values( array_diff( [ 'attachment' ], $publicPostTypes ) );
		} else {
			aioseo()->options->sitemap->news->postTypes->all      = false;
			aioseo()->options->sitemap->news->postTypes->included =
				array_values( array_diff(
					array_intersect( $publicPostTypes, (array) $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes_news'] ),
					[ 'attachment' ]
				) );
		}
	}
}