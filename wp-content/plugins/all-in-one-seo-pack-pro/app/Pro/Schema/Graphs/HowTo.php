<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * HowTo graph class.
 *
 * @since 4.2.5
 */
class HowTo extends CommonGraphs\Graph {
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
			'@type'       => 'HowTo',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#how-to',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'image'       => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : $this->getFeaturedImage(),
			'totalTime'   => ! empty( $graphData->properties->totalTime )
				? aioseo()->helpers->timeToIso8601DurationFormat( $graphData->properties->totalTime->days, $graphData->properties->totalTime->hours, $graphData->properties->totalTime->minutes )
				: '',
			'supply'      => [],
			'tool'        => [],
			'step'        => []
		];

		if ( ! empty( $graphData->properties->supplies ) ) {
			$supplies = json_decode( $graphData->properties->supplies, true );
			$supplies = array_map( function ( $supplyObject ) {
				return $supplyObject['value'];
			}, $supplies );

			foreach ( $supplies as $supply ) {
				$data['supply'][] = [
					'@type' => 'HowToSupply',
					'name'  => $supply
				];
			}
		}

		if ( ! empty( $graphData->properties->tools ) ) {
			$tools = json_decode( $graphData->properties->tools, true );
			$tools = array_map( function ( $toolObject ) {
				return $toolObject['value'];
			}, $tools );

			foreach ( $tools as $tool ) {
				$data['tool'][] = [
					'@type' => 'HowToTool',
					'name'  => $tool
				];
			}
		}

		if ( ! empty( $graphData->properties->steps ) ) {
			foreach ( $graphData->properties->steps as $step ) {
				$data['step'][] = [
					'@type' => 'HowToStep',
					'name'  => ! empty( $step->name ) ? $step->name : '',
					'text'  => ! empty( $step->text ) ? $step->text : '',
					'url'   => ! empty( $step->url ) ? $step->url : '',
					'image' => ! empty( $step->image ) ? $this->image( $step->image ) : ''
				];
			}
		}

		return $data;
	}
}