<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * WP Site Health class.
 *
 * @since 4.0.0
 */
class SiteHealth extends CommonAdmin\SiteHealth {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_health-check-aioseo-test_connection', [ $this, 'testCheckConnection' ] );
	}

	/**
	 * Add AIOSEO WP Site Health tests.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $tests The current filters array.
	 * @return array
	 */
	public function registerTests( $tests ) {
		$tests = parent::registerTests( $tests );

		$tests['direct']['aioseo_automatic_updates'] = [
			// Translators: 1 - The plugin short name ("AIOSEO").
			'label' => sprintf( __( '%1$s Automatic Updates', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME ),
			'test'  => [ $this, 'testCheckAutoUpdates' ],
		];

		$tests['direct']['aioseo_license'] = [
			'label' => __( 'AIOSEO License', 'aioseo-pro' ),
			'test'  => [ $this, 'testCheckLicense' ],
		];

		$tests['async']['aioseo_connection'] = [
			'label' => __( 'AIOSEO Connection', 'aioseo-pro' ),
			'test'  => 'aioseo_test_connection',
		];

		return $tests;
	}

	/**
	 * Tests that run to check if autoupdates are enabled.
	 *
	 * @since 4.0.12
	 *
	 * @return array A results array for the test.
	 */
	public function testCheckAutoUpdates() {
		$label       = __( 'Your website is receiving automatic updates', 'aioseo-pro' );
		$status      = 'good';
		$actions     = '';
		$description = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__( '%1$s automatic updates are enabled and you are getting the latest features, bugfixes, and security updates as they are released.', 'aioseo-pro' ),
			AIOSEO_PLUGIN_SHORT_NAME
		);

		$updatesOption = aioseo()->options->advanced->autoUpdates;

		if ( 'minor' === $updatesOption ) {
			$label       = __( 'Your website is receiving minor updates', 'aioseo-pro' );
			$description = sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( '%1$s minor updates are enabled and you are getting the latest bugfixes and security updates, but not major features.', 'aioseo-pro' ),
				AIOSEO_PLUGIN_SHORT_NAME
			);
		}
		if ( 'none' === $updatesOption ) {
			$status      = 'recommended';
			$label       = __( 'Automatic updates are disabled', 'aioseo-pro' );
			$description = sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__(
					'%1$s automatic updates are disabled. We recommend enabling automatic updates so you can get access to the latest features, bugfixes, and security updates as they are released.',
					'aioseo-pro'
				),
				AIOSEO_PLUGIN_SHORT_NAME
			);
			$actions = $this->actionLink( add_query_arg( 'page', 'aioseo-settings#/advanced', admin_url( 'admin.php' ) ), __( 'Update Settings', 'aioseo-pro' ) );
		}

		return $this->result(
			'aioseo_automatic_updates',
			$status,
			$label,
			$description,
			$actions
		);
	}

	/**
	 * Check if the license is properly set up.
	 *
	 * @since 4.0.0
	 *
	 * @return array A results array for the test.
	 */
	public function testCheckLicense() {
		// Translators: 1 - The plugin name ("All in One SEO").
		$label       = sprintf( __( 'Your %1$s license key is valid.', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME );
		$status      = 'good';
		// Translators: 1 - The license type.
		$description = sprintf( __( 'Your license key type for this site is %1$s.', 'aioseo-pro' ), '<strong>' . ucfirst( aioseo()->license->getLicenseLevel() ) . '</strong>' );
		$actions     = '';

		if ( ! aioseo()->license->isActive() ) {
			// Translators: 1 - The plugin name ("All in One SEO").
			$label  = sprintf( __( '%1$s is not licensed', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME );
			$status = 'critical';
			// Translators: 1 - The plugin name ("All in One SEO").
			$description = sprintf( __( '%1$s is not licensed which means you can\'t access automatic updates, and other advanced features', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME );
			$actions     = sprintf(
				'<p><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
				add_query_arg( 'page', 'aioseo-settings', admin_url( 'admin.php' ) ),
				__( 'Add License now', 'aioseo-pro' )
			);
		}

		return $this->result(
			'aioseo_license',
			$status,
			$label,
			$description,
			$actions
		);
	}

	/**
	 * Checks if there are errors communicating with aioseo.com.
	 *
	 * @since 4.0.0
	 *
	 * @return array A results array for the test.
	 */
	public function testCheckConnection() {
		$label  = __( 'Can connect to aioseo.com correctly', 'aioseo-pro' );
		$status = 'good';
		// Translators: 1 - The plugin name ("All in One SEO").
		$description = sprintf( __( 'The %1$s API is reachable and no connection issues have been detected.', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME );

		$url      = aioseo()->license->getUrl() . 'ping/';
		$response = wp_remote_get( $url, [
			'timeout'    => 10,
			'headers'    => aioseo()->helpers->getApiHeaders(),
			'user-agent' => aioseo()->helpers->getApiUserAgent(),
			'body'       => ''
		] );

		if ( is_wp_error( $response ) || $response['response']['code'] < 200 || $response['response']['code'] > 300 ) {
			$status = 'critical';
			// Translators: 1 - The plugin name ("All in One SEO").
			$label       = sprintf( __( 'The %1$s server is not reachable.', 'aioseo-pro' ), AIOSEO_PLUGIN_SHORT_NAME );
			$description = __( 'Your server is blocking external requests to aioseo.com, please check your firewall settings or contact your host for more details.', 'aioseo-pro' );

			if ( is_wp_error( $response ) ) {
				// Translators: 1 - The description of the error.
				$description .= ' ' . sprintf( __( 'Error message: %1$s', 'aioseo-pro' ), $response->get_error_message() );
			}
		}

		wp_send_json_success( [
			'label'       => $label,
			'status'      => $status,
			'badge'       => [
				'label' => AIOSEO_PLUGIN_SHORT_NAME,
				'color' => 'good' === $status ? 'blue' : 'red',
			],
			'description' => $description,
			'test'        => 'aioseo_connection'
		] );
	}

	/**
	 * Checks whether the required settings for our schema markup are set.
	 *
	 * @since 4.0.0
	 *
	 * @return array The test result.
	 */
	public function testCheckPluginUpdate() {
		$updates = new Updates( [
			'pluginSlug' => 'all-in-one-seo-pack-pro',
			'pluginPath' => plugin_basename( AIOSEO_FILE ),
			'version'    => AIOSEO_VERSION,
			'key'        => aioseo()->options->general->licenseKey
		] );

		$shouldUpdate = false;
		$update       = $updates->checkForUpdates();
		if ( isset( $update->new_version ) && version_compare( AIOSEO_VERSION, $update->new_version, '<' ) ) {
			$shouldUpdate = true;
		}

		if ( $shouldUpdate ) {
			return $this->result(
				'aioseo_plugin_update',
				'critical',
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( '%1$s needs to be updated', 'aioseo-pro' ),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( 'An update is available for %1$s. Upgrade to the latest version to receive all the latest features, bug fixes and security improvements.', 'aioseo-pro' ),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				$this->actionLink( admin_url( 'plugins.php' ), __( 'Go to Plugins', 'aioseo-pro' ) )
			);
		}

		return $this->result(
			'aioseo_plugin_update',
			'good',
			sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( '%1$s is updated to the latest version', 'aioseo-pro' ),
				AIOSEO_PLUGIN_SHORT_NAME
			),
			__( 'Fantastic! By updating to the latest version, you have access to all the latest features, bug fixes and security improvements.', 'aioseo-pro' )
		);
	}

	/**
	 * Returns a list of nofollowed content.
	 *
	 * @since 4.0.0
	 *
	 * @return array $nofollowed A list of nofollowed content.
	 */
	protected function nofollowed() {
		$nofollowed = parent::nofollowed();

		foreach ( aioseo()->helpers->getPublicPostTypes( false, true ) as $postType ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->archives->has( $postType['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->archives->{ $postType['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->archives->{ $postType['name'] }->advanced->robotsMeta->nofollow
			) {
				$nofollowed[] = $postType['label'] . ' ' . __( 'Archives', 'aioseo-pro' ) . ' (' . $postType['name'] . ')';
			}
		}

		return $nofollowed;
	}

	/**
	 * Returns a list of noindexed content.
	 *
	 * @since 4.0.0
	 *
	 * @return array $noindexed A list of noindexed content.
	 */
	protected function noindexed() {
		$noindexed = parent::noindexed();

		foreach ( aioseo()->helpers->getPublicPostTypes( false, true ) as $postType ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->archives->has( $postType['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->archives->{ $postType['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->archives->{ $postType['name'] }->advanced->robotsMeta->noindex
			) {
				$noindexed[] = $postType['label'] . ' ' . __( 'Archives', 'aioseo-pro' ) . ' (' . $postType['name'] . ')';
			}
		}

		return $noindexed;
	}
}