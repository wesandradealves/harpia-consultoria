<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Contains helper methods specific to the addons.
 *
 * @since 4.0.0
 */
class Addons extends CommonUtils\Addons {
	/**
	 * The licensing URL.
	 *
	 * @since 4.0.13
	 *
	 * @var string
	 */
	protected $licensingUrl = 'https://licensing.aioseo.com/v1/';

	/**
	 * The addons URL.
	 *
	 * @since 4.1.8
	 *
	 * @var string
	 */
	protected $addonsUrl = 'https://licensing-cdn.aioseo.com/keys/pro/aioseo.json';

	/**
	 * Returns our addons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return array               An array of addon data.
	 */
	public function getAddons( $flushCache = false ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$addons = aioseo()->core->networkCache->get( 'addons' );
		if ( false ) {
			$response = aioseo()->helpers->wpRemoteGet( $this->getAddonsUrl() );
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$addons = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( ! $addons || ! empty( $addons->error ) ) {
				$addons = $this->getDefaultAddons();
			}

			aioseo()->core->networkCache->update( 'addons', $addons );
		}

		// Compute some data we need elsewhere.
		$allPlugins            = get_plugins();
		$installedPlugins      = array_keys( $allPlugins );
		$shouldCheckForUpdates = false;
		$currentUpdates        = get_site_transient( 'update_plugins' );
		foreach ( $addons as $key => $addon ) {
			$addons[ $key ]->basename          = $this->getAddonBasename( $addon->sku );
			$addons[ $key ]->installed         = in_array( $addons[ $key ]->basename, $installedPlugins, true );
			$addons[ $key ]->isActive          = is_plugin_active( $addons[ $key ]->basename );
			$addons[ $key ]->canInstall        = $this->canInstall();
			$addons[ $key ]->canActivate       = $this->canActivate();
			$addons[ $key ]->canUpdate         = $this->canUpdate();
			$addons[ $key ]->capability        = $this->getManageCapability( $addon->sku );
			$addons[ $key ]->minimumVersion    = $this->getMinimumVersion( $addon->sku );
			$addons[ $key ]->installedVersion  = ! empty( $allPlugins[ $addons[ $key ]->basename ]['Version'] ) ? $allPlugins[ $addons[ $key ]->basename ]['Version'] : '';
			$addons[ $key ]->hasMinimumVersion = version_compare( $addons[ $key ]->installedVersion, $addons[ $key ]->minimumVersion, '>=' );
			$addons[ $key ]->requiresUpgrade   = ! aioseo()->license->isAddonAllowed( $addon->sku );

			// Get some details from the update info.
			$updateDetails                 = isset( $currentUpdates->response[ $addons[ $key ]->basename ] ) ? $currentUpdates->response[ $addons[ $key ]->basename ] : null;
			$addons[ $key ]->updateVersion = ! empty( $updateDetails ) ? $updateDetails->version : null;

			if ( ! $addons[ $key ]->hasMinimumVersion ) {
				if ( ! isset( $currentUpdates->response[ $addons[ $key ]->basename ] ) ) {
					$shouldCheckForUpdates = true;
				}
			}
		}

		// If we don't have a minimum version set, let's force a check for updates.
		if ( $shouldCheckForUpdates && null === aioseo()->core->networkCache->get( 'addon_check_for_updates' ) ) {
			aioseo()->core->networkCache->update( 'addon_check_for_updates', true, HOUR_IN_SECONDS );
			delete_site_transient( 'update_plugins' );
		}

		return $addons;
	}

	/**
	 * Updates a given addon or plugin.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $name    The addon name/sku.
	 * @param  bool   $network Whether or not we are in a network environment.
	 * @return bool            Whether or not the installation was succesful.
	 */
	public function upgradeAddon( $name, $network ) {
		if ( ! $this->canUpdate() ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';

		// The plugins site transient may not be set, so make sure it is.
		wp_update_plugins();

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_aioseo' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				[
					'page' => 'aioseo-settings',
				],
				admin_url( 'admin.php' )
			)
		);

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new CommonUtils\PluginUpgraderSilentAjax( new CommonUtils\PluginUpgraderSkin() );

		// Activate the plugin silently.
		$pluginSlug = ! empty( $installer->pluginSlugs[ $name ] ) ? $installer->pluginSlugs[ $name ] : null;

		// Using output buffering to prevent the FTP form from being displayed in the screen.
		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		$fs = aioseo()->core->fs->noConflict();
		$fs->init( $creds );
		if ( false === $creds || ! $fs->isWpfsValid() ) {
			return false;
		}

		// Error check.
		if ( ! method_exists( $installer, 'upgrade' ) ) {
			return false;
		}

		// Check if this is an addon and if we have a download link.
		if ( empty( $pluginSlug ) ) {
			$addon = aioseo()->addons->getAddon( $name, true );
			if ( empty( $addon->basename ) ) {
				return false;
			}

			$pluginSlug = $addon->basename;
		}

		$installer->upgrade( $pluginSlug );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$pluginBasename = $installer->plugin_info();
		if ( ! $pluginBasename ) {
			return false;
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $pluginBasename, '', $network );

		if ( is_wp_error( $activated ) ) {
			return false;
		}

		return $pluginBasename;
	}

	/**
	 * Get the download URL for the given addon.
	 *
	 * @since 4.1.8
	 *
	 * @param  string $sku The addon sku.
	 * @return string      The download url for the addon.
	 */
	public function getDownloadUrl( $sku ) {
		$downloadUrl = aioseo()->core->networkCache->get( 'addons_' . $sku . '_download_url' );
		if ( null !== $downloadUrl ) {
			return $downloadUrl;
		}

		$downloadUrl = '';
		$payload     = [
			'license'     => aioseo()->options->general->licenseKey,
			'domain'      => aioseo()->helpers->getSiteDomain(),
			'sku'         => defined( 'AIOSEO_ADDON_SKU' ) ? AIOSEO_ADDON_SKU : $sku,
			'version'     => AIOSEO_VERSION,
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' )
		];

		if ( defined( 'AIOSEO_INTERNAL_ADDONS' ) && AIOSEO_INTERNAL_ADDONS ) {
			$payload['internal'] = true;
		}

		$response = aioseo()->helpers->sendRequest( $this->getLicensingUrl() . 'addons/download-url/', $payload );

		if ( ! empty( $response->downloadUrl ) ) {
			$downloadUrl = $response->downloadUrl;
		}

		$cacheTime = empty( $downloadUrl ) ? 10 * MINUTE_IN_SECONDS : HOUR_IN_SECONDS;
		aioseo()->core->networkCache->update( 'addons_' . $sku . '_download_url', $downloadUrl, $cacheTime );

		return $downloadUrl;
	}

	/**
	 * Get the URL to check licenses.
	 *
	 * @since 4.1.8
	 *
	 * @return string The URL.
	 */
	private function getLicensingUrl() {
		if ( defined( 'AIOSEO_LICENSING_URL' ) ) {
			return AIOSEO_LICENSING_URL;
		}

		return $this->licensingUrl;
	}

	/**
	 * Check to see if there are unlicensed addons installed and activated.
	 *
	 * @since 4.1.3
	 *
	 * @return boolean True if there are unlicensed addons, false if not.
	 */
	public function unlicensedAddons() {
		$unlicensed = [
			'addons'  => [],
			'message' => ''
		];

		$addons = $this->getAddons();
		foreach ( $addons as $addon ) {
			if ( ! $addon->isActive ) {
				continue;
			}

			if ( aioseo()->license->isExpired() ) {
				$message = sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					__( 'The following addons cannot be used, because your plan has expired. To renew your subscription, please %1$svisit our website%2$s.', 'aioseo-pro' ),
					'<a target="_blank" href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/subscriptions/', $addon->name, 'notifications-fail-plan-expired' ) . '">', // phpcs:ignore WordPress.Security.EscapeOutput, Generic.Files.LineLength.MaxExceeded
					'</a>'
				);

				$unlicensed['addons'][] = $addon;
				$unlicensed['message']  = $message;
				continue;
			}

			if ( aioseo()->license->isInvalid() || aioseo()->license->isDisabled() ) {
				$message = sprintf(
					// Translators: 1 - "All in One SEO", 2 - Opening HTML link tag, 3 - Closing HTML link tag.
					__( 'The following addons cannot be used, because they require an active license for %1$s. Your license is missing or has expired. To verify your subscription, please %2$svisit our website%3$s.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					esc_html( AIOSEO_PLUGIN_NAME ),
					'<a target="_blank" href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/', $addon->name, 'notifications-fail-valid-license' ) . '">', // phpcs:ignore WordPress.Security.EscapeOutput, Generic.Files.LineLength.MaxExceeded
					'</a>'
				);

				$unlicensed['addons'][] = $addon;
				$unlicensed['message']  = $message;
				continue;
			}

			if ( ! aioseo()->license->isAddonAllowed( $addon->sku ) ) {
				$level   = aioseo()->internalOptions->internal->license->level;
				$level   = empty( $level ) ? __( 'Unlicensed', 'aioseo-pro' ) : $level;
				$message = sprintf(
					// Translators: 1 - The current plan name, 2 - Opening HTML link tag, 3 - Closing HTML link tag.
					__( 'The following addons cannot be used, because your plan level %1$s does not include access to these addons. To upgrade your subscription, please %2$svisit our website%3$s.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					'<strong>(' . wp_kses_post( ucfirst( $level ) ) . ')</strong>',
					'<a target="_blank" href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'pro-upgrade/', $addon->name, 'notifications-fail-plan-level' ) . '">', // phpcs:ignore WordPress.Security.EscapeOutput, Generic.Files.LineLength.MaxExceeded
					'</a>'
				);

				$unlicensed['addons'][] = $addon;
				$unlicensed['message']  = $message;
			}
		}

		return $unlicensed;
	}

	/**
	 * Returns the minimum versions needed for addons.
	 * If the version is lower, we need to display a warning and disable the addon.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $slug A slug to check minimum versions for.
	 * @return string       The minimum version.
	 */
	public function getMinimumVersion( $slug ) {
		$minimumVersions = [
			'aioseo-image-seo'      => '1.1.7',
			'aioseo-link-assistant' => '1.0.15',
			'aioseo-local-business' => '1.2.16',
			'aioseo-news-sitemap'   => '1.0.15',
			'aioseo-redirects'      => '1.2.10',
			'aioseo-video-sitemap'  => '1.1.13',
			'aioseo-index-now'      => '1.0.10',
			'aioseo-rest-api'       => '1.0.5'
		];

		if ( ! empty( $slug ) && ! empty( $minimumVersions[ $slug ] ) ) {
			return $minimumVersions[ $slug ];
		}

		return '0.0.1';
	}

	/**
	 * Check for updates for all addons.
	 *
	 * @since 4.1.6
	 *
	 * @return void
	 */
	public function registerUpdateCheck() {
		foreach ( $this->getAddons() as $addon ) {
			// No need to check for updates if the addon is not installed.
			if ( ! $addon->installed ) {
				continue;
			}

			new \AIOSEO\Plugin\Pro\Admin\Updates( [
				'pluginSlug' => $addon->sku,
				'pluginPath' => $addon->basename,
				'version'    => $addon->installedVersion,
				'key'        => aioseo()->options->general->licenseKey
			] );
		}
	}
}