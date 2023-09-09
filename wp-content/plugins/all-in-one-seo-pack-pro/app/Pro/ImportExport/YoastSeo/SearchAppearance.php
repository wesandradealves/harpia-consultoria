<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Search Appearance settings.
 *
 * @since 4.0.0
 */
class SearchAppearance {
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
		$this->options = get_option( 'wpseo_titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateTaxonomySettings();
	}

	/**
	 * Migrates the taxonomy settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTaxonomySettings() {
		$supportedSettings = [
			'title',
			'metadesc',
			'noindex',
			'display-metabox'
		];

		foreach ( aioseo()->helpers->getPublicTaxonomies( true ) as $taxonomy ) {
			foreach ( $this->options as $name => $value ) {
				preg_match( "#(.*)-tax-$taxonomy$#", $name, $match );
				if ( ! $match || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value ), null, 'term' );
						break;
					case 'metadesc':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value ), null, 'term' );
						break;
					case 'noindex':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->show = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->noindex = empty( $value ) ? false : true;
						break;
					case 'display-metabox':
						if ( empty( $value ) ) {
							aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->showMetaBox = false;
						}
						break;
					default:
						break;
				}
			}
		}
	}
}