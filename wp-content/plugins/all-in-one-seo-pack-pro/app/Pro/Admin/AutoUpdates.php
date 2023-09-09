<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto updater class.
 *
 * Notes about the autoupdater:
 * This runs on the normal WordPress auto-update sequence:
 * 1. In wp-includes/update.php, wp_version_check() is called by the WordPress update cron (every 8 or 12 hours; can be overriden to be faster/long or turned off by plugins)
 * 2. In wp-includes/update.php, wp_version_check() ends with a action call to do_action( 'wp_maybe_auto_update' ) if cron is running
 * 3. In wp-includes/update.php, wp_maybe_auto_update() hooks into wp_maybe_auto_update action, creates a new WP_Automatic_Updater instance and calls WP_Automatic_Updater->run
 * 4. In wp-admin/includes/class-wp-automatic-updater.php $this->run() checks to make sure we're on the main site if on a network,
 *    and also if the autoupdates are disabled (by plugin, by being on a version controlled site, etc )
 * 5. In wp-admin/includes/class-wp-automatic-updater.php $this->run() then checks to see which plugins have new versions (version/update check)
 * 6. In wp-admin/includes/class-wp-automatic-updater.php $this->run() then calls $this->update() for each plugin installed who has an upgrade.
 * 7. In wp-admin/includes/class-wp-automatic-updater.php $this->update() double checks filesystem access and then installs the plugin if able
 *
 * Notes:
 * - This autoupdater only works if WordPress core detects no version control. If you want to test this, do it on a new WP site without any .git folders anywhere.
 * - This autoupdater only works if the file access is able to be written to
 * - This autoupdater only works if a new version has been detected, and will run not the second the update is released,
 *   but whenever the cron for wp_version_check is next released. This is generally run every 8-12 hours.
 * - However, that cron can be disabled, the autoupdater can be turned off via constant or filter, version control or file lock can be detected,
 *   and other plugins can be installed (incl in functions of theme) that turn off all automatic plugin updates.
 * - If you want to test this is working, you have to manually run the wp_version_check cron. Install the WP Crontrol plugin or Core Control plugin, and run the cron manually using it.
 * - Again, because you skimmed over it the first time, if you want to test this manually you need to test this on a new WP install without version control for core,
 *   plugins, etc, without file lock, with license key entered (for pro only)
 *   and use the WP Crontrol or Core Control plugin to run wp_version_check
 * - You may have to manually remove an option called "auto_update.lock" from the WP options table
 * - You may need to run wp_version_check multiple times (note though that they must be spaced at least 60 seconds apart)
 * - Because WP's updater asks the OS if the file is writable, make sure you do not have any files/folders for the plugin you are trying to autoupdate open when testing.
 * - You may need to delete the plugin info transient to get it to hard refresh the plugin info.
 *
 * @since 4.0.12
 */
class AutoUpdates {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.12
	 */
	public function __construct() {
		add_filter( 'auto_update_plugin', [ $this, 'automaticUpdates' ], 10, 2 );
		add_filter( 'plugin_auto_update_setting_html', [ $this, 'filterWordPressAutoUpdateSetting' ], 10, 3 );
	}

	/**
	 * Filters the auto update plugin routine to allow All in One SEO to be automatically updated.
	 *
	 * @since 4.0.12
	 *
	 * @param  bool   $update Flag to update the plugin or not.
	 * @param  object $item   Update data about a specific plugin.
	 * @return bool           The new update state.
	 */
	public function automaticUpdates( $update, $item = null ) {
		$item = (array) $item;
		if ( empty( $item['plugin'] ) ) {
			return $update;
		}

		$pluginInfo = get_site_transient( 'update_plugins' );
		$response   = ! empty( $pluginInfo->response ) ? $pluginInfo->response : [];
		$noUpdate   = ! empty( $pluginInfo->no_update ) ? $pluginInfo->no_update : [];
		$plugin     = isset( $item['plugin'] ) && isset( $response[ $item['plugin'] ] )
			? (array) $response[ $item['plugin'] ]
			: (
				isset( $noUpdate[ $item['plugin'] ] )
					? (array) $noUpdate[ $item['plugin'] ]
					: null
			);

		if ( empty( $plugin ) ) {
			return $update;
		}

		$isFree  = 'all-in-one-seo-pack' === $item['slug'];
		$isPaid  = isset( $plugin['aioseo'] ); // see updater class
		$isAddon = $this->isPluginOurAddon( $item['plugin'] );

		$automaticUpdates = aioseo()->options->advanced->autoUpdates;

		// When used in the context of Plugins page.
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( ! empty( $screen ) &&
				! empty( $screen->id ) &&
				in_array( $screen->id, [ 'plugins', 'plugins-network' ], true )
			) {
				$isPro     = aioseo()->pro;
				$isMainPro = $isPro && plugin_basename( AIOSEO_FILE ) === $item['plugin'];

				if (
					$isFree ||
					$isMainPro ||
					(
						$isAddon &&
						$isPro
					)
				) {
					return in_array( $automaticUpdates, [ 'all', 'minor' ], true );
				} elseif ( $isAddon && ! $isPro ) {
					return false;
				}
			}
		}

		// If this is multisite and is not on the main site, return early.
		if ( is_multisite() && ! is_main_site() ) {
			return $update;
		}

		// If we don't have everything we need, return early.
		if ( ! isset( $item['new_version'] ) || ! isset( $item['slug'] ) ) {
			return $update;
		}

		// If the plugin isn't ours, return early.

		if ( ! $isFree && ! $isPaid || ( $isFree && ! defined( 'AIOSEO_VERSION' ) ) ) {
			return $update;
		}

		$version      = $isFree ? AIOSEO_VERSION : $plugin['oldVersion'];
		$currentMajor = $this->getMajorVersion( $version );
		$newMajor     = $this->getMajorVersion( $plugin['new_version'] );

		// If the opt in update allows major updates but there is no major version update, return early.
		if ( $currentMajor < $newMajor ) {
			if ( 'all' === $automaticUpdates ) {
				return true;
			}

			return $update;
		}

		// If the opt in update allows minor updates but there is no minor version update, return early.
		if ( $currentMajor === $newMajor ) {
			if ( 'all' === $automaticUpdates || 'minor' === $automaticUpdates ) {
				return true;
			}

			return $update;
		}

		// All our checks have passed - this plugin can be updated!
		return true;
	}

	/**
	 * Add Manage Auto-updates link for All in One SEO Pro and its add-ons on Plugins page
	 *
	 * @since 4.0.12
	 *
	 * @param  string $html
	 * @param  string $pluginFile
	 * @return string
	 */
	public function filterWordPressAutoUpdateSetting( $html, $pluginFile = '', $pluginData = [] ) {
		if ( empty( $pluginData['slug'] ) ) {
			return $html;
		}

		$isPro         = aioseo()->pro;
		$isAddon       = $this->isPluginOurAddon( $pluginFile );
		$isMainFree    = 'all-in-one-seo-pack' === $pluginData['slug'];
		$isMainPro     = $isPro && plugin_basename( AIOSEO_FILE ) === $pluginFile;
		$hasPermission = current_user_can( aioseo()->admin->getPageRequiredCapability( 'aioseo-settings' ) );

		if ( $isAddon && ! $isPro ) {
			$html = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/how-to-upgrade-from-all-in-one-seo-lite-to-pro', 'plugins-autoupdate', 'upgrade-to-autoupdate' ),
				sprintf(
					// Translators: 1 - "AIOSEO Pro"
					__( 'Enable the %1$s plugin to manage auto-updates', 'aioseo-pro' ),
					'AIOSEO Pro'
				)
			);
			add_filter( "aioseo_is_autoupdate_setting_html_filtered_$pluginFile", '__return_true' );
		} elseif ( $hasPermission &&
				( $isMainFree || $isMainPro || ( $isAddon && $isPro ) )
		) {
			$text = __( 'Manage auto-updates', 'aioseo-pro' );
			$html .= '<br>' . sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=aioseo-settings#/advanced' ), $text );
			add_filter( "aioseo_is_autoupdate_setting_html_filtered_$pluginFile", '__return_true' );
		}

		return $html;
	}

	/**
	 * Checks if the plugin is one of ours.
	 *
	 * @since 4.0.12
	 *
	 * @param  string $pluginFile The plugin file to check against.
	 * @return bool               True if it is, false if not.
	 */
	private function isPluginOurAddon( $pluginFile ) {
		$addons = aioseo()->addons->getAddons();

		if ( ! is_array( $addons ) ) {
			return false;
		}

		foreach ( $addons as $addon ) {
			if ( $pluginFile === $addon->basename ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Split out a version number and return the major version portion.
	 *
	 * @since 4.0.12
	 *
	 * @param  string $version The version to check.
	 * @return string          The major version portion of the version.
	 */
	private function getMajorVersion( $version ) {
		$version = explode( '.', $version );
		if ( isset( $version[2] ) ) {
			return $version[0] . '.' . $version[1] . '.' . $version[2];
		}

		return $version[0] . '.' . $version[1] . '.0';
	}
}