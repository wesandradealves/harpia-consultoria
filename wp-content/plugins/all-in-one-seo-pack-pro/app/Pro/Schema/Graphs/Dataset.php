<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Dataset class
 *
 * @since 4.2.5
 */
class Dataset extends CommonGraphs\Graph {
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
			'@type'               => 'Dataset',
			'@id'                 => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#dataset',
			'name'                => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description'         => ! empty( $graphData->properties->description ) ? $graphData->properties->description : aioseo()->schema->context['description'],
			'url'                 => aioseo()->schema->context['url'],
			'sameAs'              => ! empty( $graphData->properties->sameAs ) ? $graphData->properties->sameAs : '',
			'alternateName'       => '',
			'identifier'          => ! empty( $graphData->properties->identifier ) ? $graphData->properties->identifier : '',
			'license'             => ! empty( $graphData->properties->license ) ? $graphData->properties->license : '',
			'isAccessibleForFree' => isset( $graphData->properties->free ) ? $graphData->properties->free : true,
			'keywords'            => '',
			'temporalCoverage'    => ! empty( $graphData->properties->temporalCoverage ) ? $graphData->properties->temporalCoverage : '',
			'spatialCoverage'     => ! empty( $graphData->properties->spatialCoverage ) ? $graphData->properties->spatialCoverage : '',
			'hasPart'             => [],
			'distribution'        => []
		];

		if ( ! empty( $graphData->properties->alternateName ) ) {
			$alternateNames = json_decode( $graphData->properties->alternateName, true );
			$alternateNames = array_map( function ( $alternateNameObject ) {
				return $alternateNameObject['value'];
			}, $alternateNames );
			$data['alternateName'] = implode( ', ', $alternateNames );
		}

		if ( ! empty( $graphData->properties->keywords ) ) {
			$keywords = json_decode( $graphData->properties->keywords, true );
			$keywords = array_map( function ( $keywordObject ) {
				return $keywordObject['value'];
			}, $keywords );
			$data['keywords'] = implode( ', ', $keywords );
		}

		if ( ! empty( $graphData->properties->dataCatalog ) ) {
			$data['includedInDataCatalog'] = [
				'@type' => 'DataCatalog',
				'name'  => $graphData->properties->dataCatalog
			];
		}

		if ( ! empty( $graphData->properties->subDatasets ) ) {
			foreach ( $graphData->properties->subDatasets as $subDataset ) {
				$data['hasPart'][] = [
					'@type'       => 'Dataset',
					'name'        => $subDataset->name,
					'description' => $subDataset->description,
					'license'     => ! empty( $graphData->properties->license ) ? $graphData->properties->license : '',
				];
			}
		}

		if ( ! empty( $graphData->properties->downloads ) ) {
			foreach ( $graphData->properties->downloads as $download ) {
				$data['distribution'][] = [
					'@type'          => 'DataDownload',
					'encodingFormat' => $download->encodingFormat,
					'url'            => $download->url
				];
			}
		}

		return $data;
	}
}