<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs\Music;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Music class
 *
 * @since 4.2.5
 */
class Music extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  array $graphData The graph data.
	 * @return array            The parsed graph data.
	 */
	public function get( $graphData = [] ) {
		return [
			'@type'       => '',
			'@id'         => aioseo()->schema->context['url'] . $graphData->id,
			'name'        => $graphData->properties->name,
			'description' => $graphData->properties->description,
			'url'         => aioseo()->schema->context['url'],
			'image'       => $graphData->properties->image ? $this->image( $graphData->properties->image ) : ''
		];
	}
}