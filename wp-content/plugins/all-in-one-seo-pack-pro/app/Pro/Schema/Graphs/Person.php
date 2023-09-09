<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Person graph class.
 *
 * @since 4.2.5
 */
class Person extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		return [
			'@type'       => 'Person',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#person',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'email'       => ! empty( $graphData->properties->email ) ? $graphData->properties->email : '',
			'jobTitle'    => ! empty( $graphData->properties->jobTitle ) ? $graphData->properties->jobTitle : '',
			'image'       => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : $this->getFeaturedImage(),
			'address'     => [
				'@type'           => 'PostalAddress',
				'streetAddress'   => ! empty( $graphData->properties->location->streetAddress ) ? $graphData->properties->location->streetAddress : '',
				'addressLocality' => ! empty( $graphData->properties->location->locality ) ? $graphData->properties->location->locality : '',
				'postalCode'      => ! empty( $graphData->properties->location->postalCode ) ? $graphData->properties->location->postalCode : '',
				'addressRegion'   => ! empty( $graphData->properties->location->region ) ? $graphData->properties->location->region : '',
				'addressCountry'  => ! empty( $graphData->properties->location->country ) ? $graphData->properties->location->country : ''
			]
		];
	}
}