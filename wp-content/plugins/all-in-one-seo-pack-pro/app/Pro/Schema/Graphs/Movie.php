<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Movie graph class.
 *
 * @since 4.2.5
 */
class Movie extends CommonGraphs\Graph {
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
			'@type'       => 'Movie',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#video',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : aioseo()->schema->context['description'],
			'image'       => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : $this->getFeaturedImage(),
			'director'    => ! empty( $graphData->properties->director ) ? $graphData->properties->director : '',
			'dateCreated' => ! empty( $graphData->properties->releaseDate ) ? mysql2date( DATE_W3C, $graphData->properties->releaseDate, false ) : ''
		];

		if (
			! empty( $graphData->properties->review->author ) &&
			! empty( $graphData->properties->rating->minimum ) &&
			! empty( $graphData->properties->rating->maximum ) &&
			! empty( $graphData->properties->rating->value )
		) {
			$data['review'] = [
				'@type'        => 'Review',
				'headline'     => $graphData->properties->review->headline,
				'reviewBody'   => $graphData->properties->review->content,
				'reviewRating' => [
					'@type'       => 'Rating',
					'ratingValue' => (float) $graphData->properties->rating->value,
					'worstRating' => (float) $graphData->properties->rating->minimum,
					'bestRating'  => (float) $graphData->properties->rating->maximum
				],
				'author'       => [
					'@type' => 'Person',
					'name'  => $graphData->properties->review->author
				]
			];

			$data['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) $graphData->properties->rating->value,
				'worstRating' => (float) $graphData->properties->rating->minimum,
				'bestRating'  => (float) $graphData->properties->rating->maximum,
				'reviewCount' => 1
			];
		}

		return $data;
	}
}