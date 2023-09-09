<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Service graph class.
 *
 * @since 4.2.5
 */
class Service extends CommonGraphs\Graph {
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
			'@type'       => 'Service',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#service',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'serviceType' => ! empty( $graphData->properties->serviceType ) ? $graphData->properties->serviceType : '',
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

		if ( isset( $graphData->properties->price ) && isset( $graphData->properties->currency ) ) {
			$data['offers'] = [
				'@type'         => 'Offer',
				'price'         => $graphData->properties->price ? (float) $graphData->properties->price : 0,
				'priceCurrency' => $graphData->properties->currency
			];
		}

		return $data;
	}
}