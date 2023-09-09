<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs\Music;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Music Album class
 *
 * @since 4.2.5
 */
class MusicAlbum extends Music {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  array $graphData The graph data.
	 * @return array            The parsed graph data.
	 */
	public function get( $graphData = [] ) {
		$data          = parent::get( $graphData );
		$data['@type'] = 'MusicAlbum';

		return $data;
	}
}