<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils;

/**
 * Class to replace tag values with their data counterparts.
 *
 * @since 4.0.0
 */
class Tags extends Utils\Tags {
	/**
	 * An array of contexts to separate tags.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $proContext = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.6
	 */
	public function __construct() {
		parent::__construct();

		$this->tags = array_merge( $this->tags, [
			[
				'id'          => 'woocommerce_sku',
				'name'        => __( 'WooCommerce SKU', 'aioseo-pro' ),
				'description' => __( 'The SKU of the WooCommerce product.', 'aioseo-pro' )
			],
			[
				'id'          => 'woocommerce_price',
				'name'        => __( 'WooCommerce Price', 'aioseo-pro' ),
				'description' => __( 'The price of the WooCommerce product.', 'aioseo-pro' )
			],
			[
				'id'          => 'woocommerce_brand',
				'name'        => __( 'WooCommerce Brand', 'aioseo-pro' ),
				'description' => __( 'The brand of the WooCommerce product (compatible with WooCommerce Brands and Perfect WooCommerce Brands plugins).', 'aioseo-pro' )
			]
		] );
	}

	/**
	 * Add the context for all the post/page types.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of contextual data.
	 */
	public function getContext() {
		$context = parent::getContext() + $this->proContext;

		$wooCommerceTags = [
			'woocommerce_sku',
			'woocommerce_brand',
			'woocommerce_price'
		];

		if ( isset( $context['productTitle'] ) ) {
			$context['productTitle'] = array_merge( $context['productTitle'], $wooCommerceTags );
		}

		if ( isset( $context['productDescription'] ) ) {
			$context['productDescription'] = array_merge( $context['productDescription'], $wooCommerceTags );
		}

		return $context;
	}

	/**
	 * Get the default tags for the current term.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $termId The Term ID.
	 * @return array           An array of tags.
	 */
	public function getDefaultTermTags( $termId ) {
		$term = get_term( $termId );

		return [
			'title'       => aioseo()->meta->title->getTermTitle( $term, true ),
			'description' => aioseo()->meta->description->getTermDescription( $term, true )
		];
	}

	/**
	 * Get the value of the tag to replace.
	 *
	 * @since 4.0.6
	 *
	 * @param  string $tag        The tag to look for.
	 * @param  int    $id         The post ID.
	 * @param  bool   $sampleData Whether or not to fill empty values with sample data.
	 * @return string             The value of the tag.
	 */
	public function getTagValue( $tag, $id, $sampleData = false ) {
		$post     = aioseo()->helpers->getPost( $id );
		$postId   = null;
		$product  = null;
		if ( $post ) {
			$postId = empty( $id ) ? $post->ID : $id;
			if ( 'product' === $post->post_type && aioseo()->helpers->isWooCommerceActive() ) {
				$product = wc_get_product( $postId );
			}
		}

		switch ( $tag['id'] ) {
			case 'woocommerce_sku':
				if ( ! is_object( $product ) ) {
					return $sampleData ? __( 'Sample SKU', 'aioseo-pro' ) : '';
				}

				return $product ? $product->get_sku() : '';
			case 'woocommerce_price':
				if ( ! is_object( $product ) ) {
					return $sampleData ? __( '$5.99', 'aioseo-pro' ) : '';
				}

				if (
					apply_filters( 'aioseo_woocommerce_variable_product_price_range', true ) &&
					$product instanceof \WC_Product_Variable &&
					method_exists( $product, 'get_variation_sale_price' )
				) {
					$minPrice = $this->formatPrice( $product->get_variation_sale_price( 'min' ) );
					$maxPrice = $this->formatPrice( $product->get_variation_sale_price( 'max' ) );
					if ( $minPrice && $maxPrice && $minPrice !== $maxPrice ) {
						return sprintf( '%1$s-%2$s', $minPrice, $maxPrice );
					}
				}

				$productPrice = $product->get_price() ? $product->get_price() : 0;

				return $this->formatPrice( $productPrice );
			case 'woocommerce_brand':
				if ( ! is_object( $product ) ) {
					return $sampleData ? __( 'Sample Brand', 'aioseo-pro' ) : '';
				}

				return aioseo()->helpers->getWooCommerceBrand( $product->get_id() );
			default:
				return parent::getTagValue( $tag, $id, $sampleData );
		}
	}

	/**
	 * Formats a price with a dot or comma as decimal character, based on the locale.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $price The price.
	 * @return string        The formatted price.
	 */
	private function formatPrice( $price ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price );
		}

		$currencySymbol = get_woocommerce_currency_symbol();
		if ( false !== strpos( get_locale(), 'en', 0 ) ) {
			return $currencySymbol . number_format( $price, 2, '.', ',' );
		}

		return $currencySymbol . number_format( $price, 2, ',', '.' );
	}
}