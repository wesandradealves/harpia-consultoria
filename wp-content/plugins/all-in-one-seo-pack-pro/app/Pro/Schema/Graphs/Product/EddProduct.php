<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD product graph class.
 *
 * @since 4.0.13
 */
class EddProduct extends Product {
	/**
	 * The download object.
	 *
	 * @since 4.0.13
	 *
	 * @var EDD_Download
	 */
	private $download = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.13
	 */
	public function __construct() {
		if ( version_compare( EDD_VERSION, '3.0.0', '<' ) ) {
			add_filter( 'edd_add_schema_microdata', '__return_false' );

			if ( aioseo()->helpers->isEddReviewsActive() ) {
				add_filter( 'edd_reviews_json_ld_data', [ $this, 'unsetEddHeadSchema' ] );
				remove_action( 'the_content', [ \EDD_Reviews::get_instance(), 'microdata' ] );
			}
		}

		if ( version_compare( EDD_VERSION, '3.0.0', '>=' ) ) {
			remove_action( 'wp_footer', [ \Easy_Digital_Downloads::instance()->structured_data, 'output_structured_data' ] );
		}

		$this->download = edd_get_download( get_the_id() );
	}

	/**
	 * Unsets the AggregateRating graph EDD outputs in the HEAD.
	 *
	 * @since 4.0.13
	 *
	 * @param  array $data The graph data.
	 * @return array $data The neutralized graph data.
	 */
	public function unsetEddHeadSchema( $data ) {
		if ( isset( $data['aggregateRating']['ratingCount'] ) ) {
			$data['aggregateRating']['ratingCount'] = 0;
		}

		return $data;
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
		$this->download = edd_get_download( get_the_id() );
		$this->graphData = $graphData;

		$data = [
			'@type'           => 'Product',
			'@id'             => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#eddProduct',
			'name'            => get_the_title(),
			'description'     => ! empty( $graphData->properties->description ) ? $graphData->properties->description : aioseo()->helpers->getDescriptionFromContent( $this->download ),
			'url'             => aioseo()->schema->context['url'],
			'brand'           => '',
			'sku'             => ! empty( $graphData->properties->identifiers->sku ) ? $graphData->properties->identifiers->sku : '',
			'gtin'            => ! empty( $graphData->properties->identifiers->gtin ) ? $graphData->properties->identifiers->gtin : '',
			'mpn'             => ! empty( $graphData->properties->identifiers->mpn ) ? $graphData->properties->identifiers->mpn : '',
			'isbn'            => ! empty( $graphData->properties->identifiers->isbn ) ? $graphData->properties->identifiers->isbn : '',
			'image'           => ! empty( $graphData->properties->image ) ? $graphData->properties->image : $this->getFeaturedImage(),
			'aggregateRating' => aioseo()->helpers->isEddReviewsActive() ? $this->getEddAggregateRating() : $this->getAggregateRating(),
			'review'          => aioseo()->helpers->isEddReviewsActive() ? $this->getEddReview() : $this->getReview(),
			'audience'        => $this->getAudience()
		];

		if ( ! empty( $graphData->properties->brand ) ) {
			$data['brand'] = [
				'@type' => 'Brand',
				'name'  => $graphData->properties->brand
			];
		}

		$dataFunctions = [
			'offers' => 'getOffers',
		];

		return $this->getData( $data, $dataFunctions );
	}

	/**
	 * Returns the offer(s) data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The offer(s) data.
	 */
	protected function getOffers() {
		$isVariable = method_exists( $this->download, 'has_variable_prices' ) && method_exists( $this->download, 'get_prices' ) ? $this->download->has_variable_prices() : false;

		$defaultOffer = [
			'@type'           => 'Offer',
			'url'             => ! empty( $this->graphData->properties->id )
				? aioseo()->schema->context['url'] . '#eddOffer-' . $this->graphData->id
				: aioseo()->schema->context['url'] . '#eddOffer',
			'priceValidUntil' => ! empty( $this->graphData->properties->offer->validUntil )
				? aioseo()->helpers->dateToIso8601( $this->graphData->properties->offer->validUntil )
				: '',
			'availability'    => ! empty( $this->graphData->properties->offer->availability )
				? $this->graphData->properties->offer->availability
				: 'https://schema.org/InStock'
		];

		if ( 'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents ) {
			$homeUrl         = trailingslashit( home_url() );
			$defaultOffer['seller'] = [
				'@type' => 'Organization',
				'@id'   => $homeUrl . '#organization',
			];
		}

		if ( ! $isVariable ) {
			$dataFunctions = [
				'price'         => 'getPrice',
				'priceCurrency' => 'getPriceCurrency',
				'category'      => 'getCategory'
			];

			$defaultOffer = $this->getData( $defaultOffer, $dataFunctions );

			return $defaultOffer;
		}

		$offers = [];
		$prices = $this->download->get_prices();
		foreach ( $prices as $priceObject ) {
			$offer                = $defaultOffer;
			$offer['itemOffered'] = $this->download->post_title . ' - ' . $priceObject['name'];
			$offer['price']       = (float) $priceObject['amount'];

			$dataFunctions = [
				'priceCurrency' => 'getPriceCurrency',
				'category'      => 'getCategory'
			];

			$offer = $this->getData( $offer, $dataFunctions );

			$offers[] = $offer;
		}

		return $offers;
	}

	/**
	 * Returns the product price.
	 *
	 * @since 4.0.13
	 *
	 * @return float The product price.
	 */
	protected function getPrice() {
		if ( method_exists( $this->download, 'is_free' ) && $this->download->is_free() ) {
			return '0';
		}

		return method_exists( $this->download, 'get_price' ) ? $this->download->get_price() : '';
	}

	/**
	 * Returns the product currency.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product currency.
	 */
	protected function getPriceCurrency() {
		return function_exists( 'edd_get_currency' ) ? edd_get_currency() : 'USD';
	}

	/**
	 * Returns the product category.
	 *
	 * @since 4.0.13
	 *
	 * @return string The product category.
	 */
	protected function getCategory() {
		$categories = wp_get_post_terms( $this->download->get_id(), 'download_category', [ 'fields' => 'names' ] );

		return ! empty( $categories ) && __( 'Uncategorized' ) !== $categories[0] ? $categories[0] : ''; // phpcs:ignore AIOSEO.Wp.I18n.MissingArgDomain
	}

	/**
	 * Returns the AggregateRating graph data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The graph data.
	 */
	protected function getEddAggregateRating() {
		return [
			'@type'       => 'AggregateRating',
			'@id'         => aioseo()->schema->context['url'] . '#aggregrateRating',
			'worstRating' => 1,
			'bestRating'  => 5,
			'ratingValue' => (float) get_post_meta( $this->download->get_id(), 'edd_reviews_average_rating', true ),
			'reviewCount' => get_comments_number( $this->download->get_id() )
		];
	}

	/**
	 * Returns the Review graph data.
	 *
	 * @since 4.0.13
	 *
	 * @return array The graph data.
	 */
	protected function getEddReview() {
		// Because get_comments() doesn't seem to work for EDD, we use our own DB class here.
		$comments = aioseo()->core->db->start( 'comments' )
			->where( 'comment_post_ID', $this->download->get_id() )
			->where( 'comment_type', 'edd_review' )
			->limit( 25 )
			->run()
			->result();

		if ( empty( $comments ) ) {
			return [];
		}

		$reviews = [];
		foreach ( $comments as $comment ) {
			$approved = get_comment_meta( $comment->comment_ID, 'edd_review_approved', true );
			if ( empty( $approved ) ) {
				continue;
			}

			$review = [
				'@type'         => 'Review',
				'reviewRating'  => [
					'@type'       => 'Rating',
					'ratingValue' => (float) get_comment_meta( $comment->comment_ID, 'edd_rating', true ),
					'worstRating' => 1,
					'bestRating'  => 5
				],
				'author'        => [
					'@type' => 'Person',
					'name'  => $comment->comment_author
				],
				'datePublished' => mysql2date( DATE_W3C, $comment->comment_date_gmt, false )
			];

			$reviewTitle = get_comment_meta( $comment->comment_ID, 'edd_review_title', true );
			if ( ! empty( $reviewTitle ) ) {
				$review['headline'] = $reviewTitle;
			}

			if ( ! empty( $comment->comment_content ) ) {
				$review['reviewBody'] = $comment->comment_content;
			}

			$reviews[] = $review;
		}

		return $reviews;
	}
}