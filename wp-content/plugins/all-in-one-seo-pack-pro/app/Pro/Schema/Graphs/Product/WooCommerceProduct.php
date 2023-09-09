<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce product graph class.
 *
 * @since 4.0.13
 */
class WooCommerceProduct extends Product {
	/**
	 * The download object.
	 *
	 * @since 4.0.13
	 *
	 * @var WC_Product
	 */
	private $product = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.13
	 */
	public function __construct() {
		remove_action( 'wp_footer', [ WC()->structured_data, 'output_structured_data' ], 10 );
	}

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.13
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		$this->product   = wc_get_product( get_the_ID() );
		$this->graphData = $graphData;

		$data = [
			'@type'        => 'Product',
			'@id'          => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#wooCommerceProduct',
			'name'         => method_exists( $this->product, 'get_name' ) ? $this->product->get_name() : get_the_title(),
			'description'  => $this->getShortDescription(),
			'url'          => aioseo()->schema->context['url'],
			'brand'        => $this->getBrand(),
			'sku'          => method_exists( $this->product, 'get_sku' ) ? $this->product->get_sku() : '',
			'gtin'         => $this->getGtin(),
			'mpn'          => ! empty( $graphData->properties->identifiers->mpn ) ? $graphData->properties->identifiers->mpn : '',
			'isbn'         => ! empty( $graphData->properties->identifiers->isbn ) ? $graphData->properties->identifiers->isbn : '',
			'material'     => ! empty( $graphData->properties->attributes->material ) ? $graphData->properties->attributes->material : '',
			'color'        => ! empty( $graphData->properties->attributes->color ) ? $graphData->properties->attributes->color : '',
			'pattern'      => ! empty( $graphData->properties->attributes->pattern ) ? $graphData->properties->attributes->pattern : '',
			'size'         => ! empty( $graphData->properties->attributes->size ) ? $graphData->properties->attributes->size : '',
			'energyRating' => ! empty( $graphData->properties->attributes->energyRating ) ? $graphData->properties->attributes->energyRating : '',
			'image'        => $this->getImage()
		];

		$dataFunctions = [
			'offers'          => 'getOffers',
			'audience'        => 'getAudience',
			'aggregateRating' => 'getWooCommerceAggregateRating',
			'review'          => 'getWooCommerceReview'
		];

		$data = $this->getData( $data, $dataFunctions );

		return $data;
	}

	/**
	 * Returns the product short description.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product short description.
	 */
	protected function getShortDescription() {
		$description = method_exists( $this->product, 'get_short_description' ) ? $this->product->get_short_description() : $this->getDescription();

		return strip_shortcodes( wp_strip_all_tags( $description ) );
	}

	/**
	 * Returns the product description.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product description.
	 */
	protected function getDescription() {
		return method_exists( $this->product, 'get_description' ) ? $this->product->get_description() : aioseo()->schema->context['description'];
	}

	/**
	 * Returns the product brand.
	 *
	 * @since 4.0.13
	 *
	 * @return array The product brand.
	 */
	protected function getBrand() {
		$brand = ! empty( $this->graphData->properties->brand ) ? $this->graphData->properties->brand : '';
		if ( ! $brand ) {
			$brand = aioseo()->helpers->getWooCommerceBrand( $this->product->get_id() );
		}

		return ! empty( $brand )
			? [
				'@type' => 'Brand',
				'name'  => $brand
			]
			: [];
	}

	/**
	 * Returns the GTIN number for the product.
	 *
	 * @since 4.2.6
	 *
	 * @return string The GTIN number.
	 */
	private function getGtin() {
		$gtin = '';
		if ( aioseo()->helpers->isWooCommerceUpcEanIsbnActive() ) {
			if ( $this->product instanceof \WC_Product_Variable ) {
				$gtin = get_post_meta( get_the_ID(), 'hwp_var_gtin', true );
			} else {
				$gtin = get_post_meta( get_the_ID(), 'hwp_product_gtin', true );
			}
		}

		if ( ! $gtin ) {
			$gtin = ! empty( $this->graphData->properties->identifiers->gtin ) ? $this->graphData->properties->identifiers->gtin : '';
		}

		return $gtin;
	}

	/**
	 * Returns the product image.
	 *
	 * @since 4.0.13
	 *
	 * @return array The product image.
	 */
	protected function getImage() {
		$imageId = method_exists( $this->product, 'get_image_id' ) ? $this->product->get_image_id() : get_post_thumbnail_id();

		return $imageId ? $this->image( $imageId, 'productImage' ) : '';
	}

	/**
	 * Returns the offer data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The offer data.
	 */
	protected function getOffers() {
		$offer = [
			'@type' => 'Offer',
			'url'   => ! empty( $this->graphData->properties->id )
				? aioseo()->schema->context['url'] . '#wooCommerceOffer-' . $this->graphData->id
				: aioseo()->schema->context['url'] . '#wooCommerceOffer',
		];

		$dataFunctions = [
			'price'           => 'getPrice',
			'priceCurrency'   => 'getPriceCurrency',
			'priceValidUntil' => 'getPriceValidUntil',
			'availability'    => 'getAvailability',
			'shippingDetails' => 'getShippingDetails',
			'category'        => 'getCategory'
		];

		if ( 'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents ) {
			$homeUrl         = trailingslashit( home_url() );
			$offer['seller'] = [
				'@type' => 'Organization',
				'@id'   => $homeUrl . '#organization',
			];
		}

		return $this->getData( $offer, $dataFunctions );
	}

	/**
	 * Returns the product price.
	 *
	 * @since 4.0.13
	 *
	 * @return float The product price.
	 */
	protected function getPrice() {
		return function_exists( 'wc_get_price_to_display' )
			? wc_get_price_to_display( $this->product )
			: ( method_exists( $this->product, 'get_price' ) ? $this->product->get_price() : 0 );
	}

	/**
	 * Returns the lowest price of the product.
	 *
	 * @since 4.1.1
	 *
	 * @return float The lowest price.
	 */
	protected function getLowPrice() {
		return method_exists( $this->product, 'get_variation_price' ) ? $this->product->get_variation_price( 'min', true ) : '';
	}

	/**
	 * Returns the highest price of the product.
	 *
	 * @since 4.1.1
	 *
	 * @return float The highest price.
	 */
	protected function getHighPrice() {
		return method_exists( $this->product, 'get_variation_price' ) ? $this->product->get_variation_price( 'max', true ) : '';
	}

	/**
	 * Returns the product currency.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product currency.
	 */
	protected function getPriceCurrency() {
		return function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
	}

	/**
	 * Returns the offer count.
	 *
	 * @since 4.1.1
	 *
	 * @return string The offer count.
	 */
	protected function getOfferCount() {
		return count( $this->product->get_available_variations( 'objects' ) );
	}

	/**
	 * Returns the date the product price is valid until.
	 *
	 * @since 4.0.13
	 *
	 * @return string The date the product price is valid until.
	 */
	protected function getPriceValidUntil() {
		if ( ! method_exists( $this->product, 'get_date_on_sale_to' ) || ! $this->product->get_date_on_sale_to() ) {
			return '';
		}

		$date = $this->product->get_date_on_sale_to();

		return is_object( $date ) && method_exists( $date, 'date_i18n' ) ? $date->date_i18n() : '';
	}

	/**
	 * Returns the product availability.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product availability.
	 */
	protected function getAvailability() {
		if ( ! method_exists( $this->product, 'get_stock_status' ) || ! $this->product->get_stock_status() ) {
			return 'https://schema.org/InStock';
		}

		switch ( $this->product->get_stock_status() ) {
			case 'outofstock':
				return 'https://schema.org/OutOfStock';
			case 'onbackorder':
				return 'https://schema.org/PreOrder';
			case 'instock':
			default:
				return 'https://schema.org/InStock';
		}
	}

	/**
	 * Returns the shipping details.
	 *
	 * @since 4.2.7
	 *
	 * @return array The shipping details.
	 */
	public function getShippingDetails() {
		if (
			is_admin() ||
			wp_doing_ajax() ||
			wp_doing_cron() ||
			$this->product->is_virtual() ||
			apply_filters( 'aioseo_schema_woocommerce_shipping_disable', false )
		) {
			return [];
		}

		global $woocommerce;

		// To prevent fatal errors, we can only run this on the frontend.
		// We also only want to continue generating the shipping details schema if the cart is currently empty.
		// That way, we don't get in the way of any visitors that are actively shopping.
		if (
			! is_object( $woocommerce->customer ) ||
			! is_object( $woocommerce->cart ) ||
			! empty( $woocommerce->cart->cart_contents )
		) {
			return [];
		}

		$originalCustomer = clone $woocommerce->customer;

		if (
			class_exists( '\MonsterInsights_eCommerce_WooCommerce_Integration' ) &&
			method_exists( '\MonsterInsights_eCommerce_WooCommerce_Integration', 'get_instance' ) &&
			method_exists( '\MonsterInsights_eCommerce_WooCommerce_Integration', 'add_to_cart' )
		) {
			// Prevent MonsterInsights from tracking the add_to_cart event.
			remove_action( 'woocommerce_add_to_cart', [ \MonsterInsights_eCommerce_WooCommerce_Integration::get_instance(), 'add_to_cart' ] );
		}

		// First, clear the cart so that we can simulate the order.
		$woocommerce->cart->add_to_cart( $this->product->get_id() );

		// Load the zones.
		$dataStore = \WC_Data_Store::load( 'shipping-zone' );
		$rawZones  = $dataStore->get_zones();

		$zones   = [];
		$zones[] = new \WC_Shipping_Zone( 0 ); // The first zone needs to be instantiated manually.
		foreach ( $rawZones as $rawZone ) {
			$zones[] = new \WC_Shipping_Zone( $rawZone );
		}

		$shippingDetails = [];
		foreach ( $zones as $zone ) {
			$locations = $zone->get_zone_locations();

			// In a moment we'll loop over all the locations in the zone.
			// We'll use one address "$addressData" (as detailed as possible) to calculate the most detailed shipping rate.
			// We'll use a second variable "$locationData" to keep track of all the countries, states, postal codes, etc. that are included in the current zone.
			$addressData = [
				'country'  => '',
				'state'    => '',
				'postcode' => '',
				'city'     => ''
			];

			$locationData = [
				'country'  => [],
				'state'    => [],
				'postcode' => [],
				'city'     => [],
				'rate'     => []
			];

			foreach ( $locations as $location ) {
				$addressData[ $location->type ] = $location->code;

				// If the location is a state, group it under the relevant country code.
				if ( 'state' === $location->type ) {
					$countryCode = substr( $location->code, 0, 2 );
					$stateCode   = substr( $location->code, 3 );

					if ( ! isset( $locationData['state'][ $countryCode ] ) ) {
						$locationData['state'][ $countryCode ] = [];
					}

					$locationData['state'][ $countryCode ][] = $stateCode;
				} else {
					$locationData[ $location->type ][] = $location->code;
				}
			}

			// Set the address and get all shipping methods that are eligible for the "order".
			WC()->customer->set_shipping_location( $addressData['country'], $addressData['state'], $addressData['postcode'] );
			WC()->shipping->calculate_shipping( WC()->cart->get_shipping_packages() );
			$shippingMethods = WC()->shipping->packages;

			if ( empty( $shippingMethods[0]['rates'] ) ) {
				continue;
			}

			foreach ( $shippingMethods[0]['rates'] as $shippingMethod ) {
				// Ignore the free pickup method since this isn't relevant for Google.
				if ( 'local_pickup' === $shippingMethod->get_method_id() ) {
					continue 2;
				}

				$locationData['rate'] = number_format( $shippingMethod->cost, 2, '.', '' );
			}

			// Once we've got all shipping methods and their rates, loop over the countries/states that are included in the zone.
			foreach ( $locationData['country'] as $countryCode ) {
				$shippingDetail = [
					'@type'               => 'OfferShippingDetails',
					'shippingRate'        => [
						'@type'    => 'MonetaryAmount',
						'value'    => $locationData['rate'],
						'currency' => $this->getPriceCurrency()
					],
					'shippingDestination' => [
						'@type'          => 'DefinedRegion',
						'addressCountry' => $countryCode
					]
				];

				if ( ! empty( $locationData['postcode'] ) ) {
					$shippingDetail['shippingDestination']['postalCode'] = $locationData['postcode'];
				}

				$shippingDetails[] = $shippingDetail;
			}

			foreach ( $locationData['state'] as $countryCode => $stateCodes ) {
				$shippingDetails[] = [
					'@type'               => 'OfferShippingDetails',
					'shippingRate'        => [
						'@type'    => 'MonetaryAmount',
						'value'    => $locationData['rate'],
						'currency' => $this->getPriceCurrency()
					],
					'shippingDestination' => [
						'@type'          => 'DefinedRegion',
						'addressCountry' => $countryCode,
						'addressRegion'  => $stateCodes
					]
				];
			}
		}

		// Restore the original cart/class instance before returning the data.
		$woocommerce->cart->empty_cart();
		$woocommerce->customer = $originalCustomer;

		return $shippingDetails;
	}

	/**
	 * Returns the product category.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product category.
	 */
	protected function getCategory() {
		$categories = wp_get_post_terms( $this->product->get_id(), 'product_cat', [ 'fields' => 'names' ] );

		return ! empty( $categories ) && __( 'Uncategorized' ) !== $categories[0] ? $categories[0] : ''; // phpcs:ignore AIOSEO.Wp.I18n.MissingArgDomain
	}

	/**
	 * Returns the AggregateRating graph data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The graph data.
	 */
	protected function getWooCommerceAggregateRating() {
		if ( ! method_exists( $this->product, 'get_average_rating' ) || ! $this->product->get_average_rating() ) {
			return [];
		}

		if (
			apply_filters( 'aioseo_schema_product_check_reviews_allowed', false ) &&
			method_exists( $this->product, 'get_reviews_allowed' ) &&
			! $this->product->get_reviews_allowed()
		) {
			return [];
		}

		return [
			'@type'       => 'AggregateRating',
			'@id'         => aioseo()->schema->context['url'] . '#aggregrateRating',
			'worstRating' => 1,
			'bestRating'  => 5,
			'ratingValue' => (float) $this->product->get_average_rating(),
			'reviewCount' => $this->product->get_review_count()
		];
	}

	/**
	 * Returns the Review graph data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The graph data.
	 */
	protected function getWooCommerceReview() {
		$comments = get_comments( [
			'post_id' => $this->product->get_id(),
			'type'    => 'review',
			'status'  => 'approve',
			'number'  => 25
		] );

		if ( empty( $comments ) ) {
			return [];
		}

		$reviews = [];
		foreach ( $comments as $comment ) {
			$ratingValue = (float) get_comment_meta( $comment->comment_ID, 'rating', true );
			if ( ! is_numeric( $ratingValue ) ) {
				continue;
			}

			// If a review has no rating, WooCommerce falls back to a 1 star rating.
			if ( 0 === absint( $ratingValue ) ) {
				$ratingValue = 1;
			}

			$review = [
				'@type'         => 'Review',
				'reviewRating'  => [
					'@type'       => 'Rating',
					'ratingValue' => $ratingValue,
					'worstRating' => 1,
					'bestRating'  => 5
				],
				'author'        => [
					'@type' => 'Person',
					'name'  => $comment->comment_author
				],
				'datePublished' => mysql2date( DATE_W3C, $comment->comment_date_gmt, false )
			];

			if ( ! empty( $comment->comment_content ) ) {
				$review['reviewBody'] = $comment->comment_content;
			}

			$reviews[] = $review;
		}

		return $reviews;
	}
}