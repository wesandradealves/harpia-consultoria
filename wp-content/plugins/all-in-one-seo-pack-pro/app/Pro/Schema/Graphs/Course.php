<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Course class
 *
 * @since 4.2.5
 */
class Course extends CommonGraphs\Graph {
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
			'@type'       => 'Course',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#course',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : aioseo()->schema->context['description'],
			'provider'    => [
				'@type'  => 'Organization',
				'name'   => ! empty( $graphData->properties->provider->name ) ? $graphData->properties->provider->name : '',
				'sameAs' => ! empty( $graphData->properties->provider->url ) ? $graphData->properties->provider->url : '',
				'image'  => ! empty( $graphData->properties->provider->image ) ? $this->image( $graphData->properties->provider->image ) : ''
			]
		];

		if ( 'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents ) {
			if ( empty( $graphData->properties->provider->name ) ) {
				$homeUrl          = trailingslashit( home_url() );
				$data['provider'] = [
					'@type' => 'Organization',
					'@id'   => $homeUrl . '#organization',
				];
			}
		} else {
			unset( $data['provider'] );
		}

		return $data;
	}
}