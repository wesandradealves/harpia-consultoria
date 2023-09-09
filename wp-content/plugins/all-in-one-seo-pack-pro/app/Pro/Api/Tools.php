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
 * @since 4.0.0
 */
class Tools extends CommonApi\Tools {
	/**
	 * Restore a settings backup.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function restoreBackup( $request ) {
		$body   = $request->get_json_params();
		$siteId = ! empty( $body['siteId'] ) ? (int) $body['siteId'] : get_current_blog_id();

		aioseo()->helpers->switchToBlog( $siteId );

		$response = parent::restoreBackup( $request );

		$response->data['license'] = [
			'isActive'   => aioseo()->license->isActive(),
			'isExpired'  => aioseo()->license->isExpired(),
			'isDisabled' => aioseo()->license->isDisabled(),
			'isInvalid'  => aioseo()->license->isInvalid(),
			'expires'    => aioseo()->internalOptions->internal->license->expires
		];

		return $response;
	}

	/**
	 * Clear the passed in log.
	 *
	 * @since 4.1.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function clearLog( $request ) {
		$response = parent::clearLog( $request );

		return Api::addonsApi( $request, $response, '\\Api\\Tools', 'clearLog' );
	}

	/**
	 * Create a settings backup.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function createBackup( $request ) {
		$body   = $request->get_json_params();
		$siteId = ! empty( $body['siteId'] ) ? (int) $body['siteId'] : get_current_blog_id();

		aioseo()->helpers->switchToBlog( $siteId );

		return parent::createBackup( $request );
	}

	/**
	 * Delete a settings backup.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteBackup( $request ) {
		$body   = $request->get_json_params();
		$siteId = ! empty( $body['siteId'] ) ? (int) $body['siteId'] : get_current_blog_id();

		aioseo()->helpers->switchToBlog( $siteId );

		return parent::deleteBackup( $request );
	}
}