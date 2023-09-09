<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Book graph class.
 *
 * @since 4.2.5
 */
class Book extends CommonGraphs\Graph {
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
			'@type'       => 'Book',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#book',
			'name'        => $graphData->properties->name,
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'author'      => '',
			'url'         => aioseo()->schema->context['url'],
			'image'       => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : '',
			'inLanguage'  => aioseo()->helpers->currentLanguageCodeBCP47(),
			'publisher'   => [ '@id' => trailingslashit( home_url() ) . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ],
			'hasPart'     => []
		];

		if ( ! empty( $graphData->properties->author ) ) {
			$data['author'] = [
				'@type' => 'Person',
				'name'  => $graphData->properties->author
			];
		}

		if ( ! empty( $graphData->properties->editions ) ) {
			foreach ( $graphData->properties->editions as $editionData ) {
				if ( empty( $editionData->name ) ) {
					continue;
				}

				$edition = [
					'@type'         => 'Book',
					'name'          => ! empty( $editionData->name ) ? $editionData->name : '',
					'bookEdition'   => ! empty( $editionData->bookEdition ) ? $editionData->bookEdition : '',
					'author'        => '',
					'isbn'          => ! empty( $editionData->isbn ) ? $editionData->isbn : '',
					'bookFormat'    => ! empty( $editionData->format ) ? $editionData->format : '',
					'datePublished' => ! empty( $editionData->dataPublished ) ? $editionData->dataPublished : ''
				];

				if ( ! empty( $editionData->author ) ) {
					$edition['author'] = [
						'@type' => 'Person',
						'name'  => $editionData->author
					];
				}

				$data['hasPart'][] = $edition;
			}
		}

		return $data;
	}
}