<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Image SEO settings from V3.
 *
 * @since 4.0.0
 */
class ImageSeo {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$oldOptions = aioseo()->migration->oldOptions;

		if ( empty( $oldOptions['modules']['aiosp_image_seo_options'] ) ) {
			return;
		}

		if (
			! empty( $oldOptions['modules']['aiosp_image_seo_options']['aiosp_image_seo_title_format'] ) &&
			! empty( $oldOptions['modules']['aiosp_image_seo_options']['aiosp_image_seo_alt_format'] )
		) {
			aioseo()->options->image->format->title  = aioseo()->migration->helpers->macrosToSmartTags( $oldOptions['modules']['aiosp_image_seo_options']['aiosp_image_seo_title_format'] );
			aioseo()->options->image->format->altTag = aioseo()->migration->helpers->macrosToSmartTags( $oldOptions['modules']['aiosp_image_seo_options']['aiosp_image_seo_alt_format'] );
		}

		$settings = [
			'aiosp_image_seo_title_strip_punc' => [ 'type' => 'boolean', 'newOption' => [ 'image', 'stripPunctuation', 'title' ] ],
			'aiosp_image_seo_alt_strip_punc'   => [ 'type' => 'boolean', 'newOption' => [ 'image', 'stripPunctuation', 'altTag' ] ]
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $oldOptions['modules']['aiosp_image_seo_options'] );
	}
}