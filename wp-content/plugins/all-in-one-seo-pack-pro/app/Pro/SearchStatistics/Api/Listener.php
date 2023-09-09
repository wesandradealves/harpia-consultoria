<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended

/**
 * Class that holds our listeners for the microservice.
 *
 * @since 4.3.0
 */
class Listener {
	/**
	 * The redirect URL after receiving the response from microservice.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $redirectUrl = 'admin.php?page=aioseo-search-statistics/#settings';

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'listenForAuthentication' ] );
		add_action( 'admin_init', [ $this, 'listenForReauthentication' ] );

		add_action( 'wp_ajax_nopriv_aioseo_is_installed', [ $this, 'isInstalled' ] );
		add_action( 'wp_ajax_nopriv_aioseo_rauthenticate', [ $this, 'reauthenticate' ] );
	}

	/**
	 * Listens to the response from the microservice server.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function listenForAuthentication() {
		if ( empty( $_REQUEST['aioseo-oauth-action'] ) || 'auth' !== $_REQUEST['aioseo-oauth-action'] ) {
			return;
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_search_statistics_settings' ) ) {
			return;
		}

		if ( empty( $_REQUEST['tt'] ) || empty( $_REQUEST['key'] ) || empty( $_REQUEST['token'] ) || empty( $_REQUEST['authedsite'] ) ) {
			return;
		}

		if ( ! aioseo()->searchStatistics->api->trustToken->validate( sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) ) ) {
			return;
		}

		$profile = [
			'key'        => sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ),
			'token'      => sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ),
			'siteurl'    => site_url(),
			'neturl'     => network_admin_url(),
			'authedsite' => esc_url_raw( wp_unslash( $this->getAuthenticatedDomain() ) ),
		];

		$success = aioseo()->searchStatistics->api->auth->verify( $profile );
		if ( ! $success ) {
			return;
		}

		$this->saveProfileAndRedirect( $profile );
	}

	/**
	 * Listens to for the reauthentication response from the microservice.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function listenForReauthentication() {
		if ( empty( $_REQUEST['aioseo-oauth-action'] ) || 'reauth' !== $_REQUEST['aioseo-oauth-action'] ) {
			return;
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_search_statistics_settings' ) ) {
			return;
		}

		if ( empty( $_REQUEST['tt'] ) || empty( $_REQUEST['authedsite'] ) ) {
			return;
		}

		if ( ! aioseo()->searchStatistics->api->trustToken->validate( sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) ) ) {
			return;
		}

		$existingProfile = aioseo()->searchStatistics->api->auth->getProfile( true );
		if ( empty( $existingProfile['key'] ) || empty( $existingProfile['token'] ) ) {
			return;
		}

		$profile = [
			'key'        => $existingProfile['key'],
			'token'      => $existingProfile['token'],
			'siteurl'    => site_url(),
			'neturl'     => network_admin_url(),
			'authedsite' => esc_url_raw( wp_unslash( $this->getAuthenticatedDomain() ) ),
		];

		$this->saveProfileAndRedirect( $profile );
	}

	/**
	 * Return a success status code indicating that the plugin is installed.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function isInstalled() {
		wp_send_json_success( [
			'version' => aioseo()->version,
			'pro'     => aioseo()->pro
		] );
	}

	/**
	 * Validate the trust token and tells the microservice that we can reauthenticate.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function reauthenticate() {
		foreach ( [ 'key', 'token', 'tt', 'network' ] as $arg ) {
			if ( empty( $_REQUEST[ $arg ] ) ) {
				wp_send_json_error( [
					'error'   => 'authenticate_missing_arg',
					'message' => 'Authentication request missing parameter: ' . $arg,
					'version' => aioseo()->version,
					'pro'     => aioseo()->pro
				] );
			}
		}

		$trustToken = ! empty( $_REQUEST['tt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) : '';
		if ( ! aioseo()->searchStatistics->api->trustToken->validate( $trustToken ) ) {
			wp_send_json_error( [
				'error'   => 'authenticate_invalid_tt',
				'message' => 'Invalid TT sent',
				'version' => aioseo()->version,
				'pro'     => aioseo()->pro
			] );
		}

		// If the trust token is validated, send a success response to trigger the regular auth process.
		wp_send_json_success();
	}

	/**
	 * Saves the authenticated account, clear the existing data and redirect back to the settings page.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	private function saveProfileAndRedirect( $profile ) {
		aioseo()->searchStatistics->api->auth->setProfile( $profile );

		$url = admin_url( $this->redirectUrl );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Returns the authenticated domain.
	 *
	 * @since 4.3.0
	 *
	 * @return string The authenticated domain.
	 */
	private function getAuthenticatedDomain() {
		if ( empty( $_REQUEST['authedsite'] ) ) {
			return '';
		}

		$authedSite = sanitize_text_field( wp_unslash( $_REQUEST['authedsite'] ) );
		if ( false !== aioseo()->helpers->stringIndex( $authedSite, 'sc-domain:' ) ) {
			$authedSite = str_replace( 'sc-domain:', '', $authedSite );
		}

		return $authedSite;
	}
}