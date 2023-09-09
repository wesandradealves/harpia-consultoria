<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * FactCheck graph class.
 *
 * @since 4.2.5
 */
class FactCheck extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		$data = [
			'@type'         => 'ClaimReview',
			'@id'           => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#claimReview',
			'name'          => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'claimReviewed' => ! empty( $graphData->properties->claimReviewed ) ? $graphData->properties->claimReviewed : '',
			'url'           => aioseo()->schema->context['url'],
			'reviewRating'  => [],
			'author'        => [],
			'itemReviewed'  => [],
			'datePublished' => ! empty( $graphData->properties->datePublished ) ? $graphData->properties->datePublished : ''
		];

		if ( ! empty( $graphData->properties->claimRating ) ) {
			$alternateName = '';
			switch ( $graphData->properties->claimRating ) {
				case 1:
					$alternateName = __( 'False', 'aioseo-pro' );
					break;
				case 2:
					$alternateName = __( 'Mostly False', 'aioseo-pro' );
					break;
				case 3:
					$alternateName = __( 'Half True', 'aioseo-pro' );
					break;
				case 4:
					$alternateName = __( 'Mostly True', 'aioseo-pro' );
					break;
				case 5:
					$alternateName = __( 'True', 'aioseo-pro' );
					break;
				default:
					break;
			}

			$data['reviewRating'] = [
				'@type'         => 'Rating',
				'ratingValue'   => (float) $graphData->properties->claimRating,
				'bestRating'    => 5,
				'worstRating'   => 1,
				'alternateName' => $alternateName
			];
		}

		if ( ! empty( $graphData->properties->author->type ) ) {
			$data['author'] = [
				'@type' => $graphData->properties->author->type,
				'name'  => $graphData->properties->author->name,
				'url'   => $graphData->properties->author->url
			];

			// If name is empty and the type is organization, fall back to the global one.
			if (
				empty( $graphData->properties->author->name ) &&
				'Organization' === $graphData->properties->author->type &&
				'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents
			) {
				$homeUrl          = trailingslashit( home_url() );
				$data['author'] = [
					'@type' => 'Organization',
					'@id'   => $homeUrl . '#organization',
				];
			}
		}

		if ( ! empty( $graphData->properties->claim ) ) {
			$data['itemReviewed'] = [
				'@type'           => 'Claim',
				'appearance'      => [],
				'firstAppearance' => [],
				'author'          => [],
				'datePublished'   => ! empty( $graphData->properties->claim->datePublished ) ? $graphData->properties->claim->datePublished : ''
			];

			if ( ! empty( $graphData->properties->claim->appearance ) ) {
				$appearance = [
					'@type' => 'CreativeWork'
				];

				if ( aioseo()->helpers->isUrl( $graphData->properties->claim->appearance ) ) {
					$appearance['url'] = $graphData->properties->claim->appearance;
				} else {
					$appearance['description'] = $graphData->properties->claim->appearance;
				}

				$data['itemReviewed']['appearance']      = $appearance;
				$data['itemReviewed']['firstAppearance'] = $appearance;
			}

			if ( ! empty( $graphData->properties->claim->author->type ) && ! empty( $graphData->properties->claim->author->name ) ) {
				$author = [
					'@type' => $graphData->properties->author->type,
					'name'  => $graphData->properties->author->name,
					'url'   => $graphData->properties->author->url
				];

				$data['itemReviwed']['author'] = $author;
				if ( ! empty( $data['itemReviewed'] ) ) {
					$data['itemReviewed']['author'] = $author;
				}
			}
		}

		return $data;
	}
}