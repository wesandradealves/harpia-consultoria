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
class Notifications extends CommonApi\Notifications {
	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function localBusinessOrganizationReminder() {
		return self::reminder( 'local-business-organization' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function newsPublicationNameReminder() {
		return self::reminder( 'news-publication-name' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function migrationLocalBusinessNumberReminder() {
		return self::reminder( 'v3-migration-local-business-number' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function migrationLocalBusinessCountryReminder() {
		return self::reminder( 'v3-migration-local-business-country' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public function importLocalBusinessCountryReminder() {
		return self::reminder( 'import-local-business-country' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public function importLocalBusinessTypeReminder() {
		return self::reminder( 'import-local-business-type' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public function importLocalBusinessNumberReminder() {
		return self::reminder( 'import-local-business-number' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public function importLocalBusinessFaxReminder() {
		return self::reminder( 'import-local-business-fax' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public function importLocalBusinessCurrenciesReminder() {
		return self::reminder( 'import-local-business-currencies' );
	}
}