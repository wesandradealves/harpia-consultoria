<?php
namespace AIOSEO\Plugin\Pro\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Title & Meta settings.
 *
 * @since 4.0.0
 */
class TitleMeta {
	/**
	 * Our robot meta settings.
	 *
	 * @since 4.0.0
	 */
	private $robotMetaSettings = [
		'noindex',
		'nofollow',
		'noarchive',
		'noimageindex',
		'nosnippet'
	];

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
		$this->options = get_option( 'rank-math-options-titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateTaxonomySettings();
		$this->migrateDefaultTermSocialImage();
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
			'description',
			'custom_robots',
			'robots',
			'advanced_robots',
			'add_meta_box'
		];

		foreach ( aioseo()->helpers->getPublicTaxonomies( true ) as $taxonomy ) {
			// Reset existing values first.
			foreach ( $this->robotMetaSettings as $robotsMetaName ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->$robotsMetaName = false;
			}

			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#^tax_${taxonomy}_(.*)$#", $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value, 'term' ) );
						break;
					case 'description':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value, 'term' ) );
						break;
					case 'custom_robots':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default = 'off' === $value;
						break;
					case 'robots':
						if ( ! empty( $value ) ) {
							foreach ( $value as $robotsName ) {
								if ( 'index' === $robotsName ) {
									continue;
								}

								if ( 'noindex' === $robotsName ) {
									aioseo()->dynamicOptions->searchAppearance->taxonomies->{$taxonomy}->show = false;
								}

								aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->$robotsName = true;
							}
						}
						break;
					case 'advanced_robots':
						if ( ! empty( $value['max-snippet'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->maxSnippet = intval( $value['max-snippet'] );
						}
						if ( ! empty( $value['max-video-preview'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->maxVideoPreview = intval( $value['max-video-preview'] );
						}
						if ( ! empty( $value['max-image-preview'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->maxVideoPreview =
								aioseo()->helpers->sanitizeOption( $value['max-image-preview'] );
						}
						break;
					case 'add_meta_box':
						aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->showMetaBox = 'on' === $value;
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Migrates the default social image for terms.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDefaultTermSocialImage() {
		if ( ! empty( $this->options['open_graph_image'] ) ) {
			$defaultImage = esc_url( $this->options['open_graph_image'] );
			aioseo()->options->social->facebook->general->defaultImageTerms = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImageTerms  = $defaultImage;
		}
	}
}