<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Breadcrumb settings.
 *
 * @since 4.1.4
 */
class Breadcrumbs {
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
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_pro_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateTax();
	}

	/**
	 * Migrates the Taxonomies settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateTax() {
		if ( ! empty( $this->options['seopress_breadcrumbs_remove_shop_page'] ) ) {
			if ( aioseo()->dynamicOptions->breadcrumbs->postTypes->has( 'product' ) ) {
				aioseo()->dynamicOptions->breadcrumbs->postTypes->product->useDefaultTemplate = false;
				aioseo()->dynamicOptions->breadcrumbs->postTypes->product->showArchiveCrumb = false;
			}
		}

		foreach ( $this->options['seopress_breadcrumbs_tax'] as $postType => $taxSettings ) {
			if ( aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) ) {
				aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->taxonomy = $taxSettings['tax'];
			}
		}
	}
}