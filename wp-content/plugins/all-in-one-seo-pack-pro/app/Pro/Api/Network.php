<?php
namespace AIOSEO\Plugin\Pro\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Api as CommonApi;

/**
 * Route class for the API.
 *
 * @since 4.2.5
 */
class Network extends CommonApi\Network {
	/**
	 * Fetch network sites.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function fetchSites( $request ) {
		$filter     = $request->get_param( 'filter' );
		$body       = $request->get_json_params();
		$orderBy    = ! empty( $body['orderBy'] ) ? sanitize_text_field( $body['orderBy'] ) : 'id';
		$orderDir   = ! empty( $body['orderDir'] ) && ! empty( $body['orderBy'] ) ? strtoupper( sanitize_text_field( $body['orderDir'] ) ) : 'DESC';
		$limit      = ! empty( $body['limit'] ) ? intval( $body['limit'] ) : null;
		$offset     = ! empty( $body['offset'] ) ? intval( $body['offset'] ) : 0;
		$searchTerm = ! empty( $body['searchTerm'] ) ? sanitize_text_field( $body['searchTerm'] ) : null;

		return new \WP_REST_Response( [
			'success' => true,
			'sites'   => aioseo()->helpers->getSites( $limit, $offset, $searchTerm, $filter, $orderBy, $orderDir )
		], 200 );
	}

	/**
	 * Fetch network robots.txt.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function fetchSiteRobots( $request ) {
		$isNetwork = 'network' === $request->get_param( 'siteId' );
		$siteId    = $isNetwork ? aioseo()->helpers->getNetworkId() : (int) $request->get_param( 'siteId' );

		aioseo()->helpers->switchToBlog( $siteId );

		// Re-initialize the options for this site.
		aioseo()->options->init();

		return new \WP_REST_Response( [
			'success' => true,
			'rules'   => $isNetwork
				? aioseo()->networkOptions->tools->robots->rules
				: aioseo()->options->tools->robots->rules
		], 200 );
	}
}