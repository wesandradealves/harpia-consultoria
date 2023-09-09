<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * The License class to validate/activate/deactivate license keys.
 *
 * @since 4.0.0
 */
class License {
	/**
	 * Source of notifications content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $baseUrl = 'https://licensing.aioseo.com/v1/';

	/**
	 * Holds the options. We need this so the network options can override.
	 *
	 * @since 4.2.5
	 *
	 * @var Options\Options
	 */
	protected $options;

	/**
	 * Holds the options. We need this so the network options can override.
	 *
	 * @since 4.2.5
	 *
	 * @var Options\InternalOptions
	 */
	protected $internalOptions;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $maybeValidate Whether or not to run the validation.
	 */
	public function __construct( $maybeValidate = true ) {
		if ( ! isset( $_GET['page'] ) || 'aioseo-settings' !== wp_unslash( $_GET['page'] ) ) { // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			add_action( 'admin_notices', [ $this, 'notices' ] );
		}

		add_action( 'after_plugin_row_' . AIOSEO_PLUGIN_BASENAME, [ $this, 'pluginRowNotice' ] );
		add_action( 'in_plugin_update_message-' . AIOSEO_PLUGIN_BASENAME, [ $this, 'updateRowNotice' ] );

		$this->options         = aioseo()->options;
		$this->internalOptions = aioseo()->internalOptions;

		if ( $maybeValidate ) {
			$this->maybeValidate();
		}
	}

	/**
	 * Checks if we should validate the license key or not.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeValidate() {
		if ( ! $this->options->general->licenseKey ) {
			if ( $this->needsReset() ) {
				$this->internalOptions->internal->license->reset(
					[
						'expires',
						'expired',
						'invalid',
						'disabled',
						'activationsError',
						'connectionError',
						'requestError',
						'level',
						'addons',
						'features'
					]
				);
			}

			return;
		}

		// Validate notices.
		if ( is_admin() ) {
			$this->validateNotifications();
		}

		// Perform a request to validate the key  - Only run every 12 hours.
		$timestamp = $this->internalOptions->internal->license->lastChecked;
		if ( time() < $timestamp ) {
			return;
		}

		$success = $this->activate();
		if ( $success || aioseo()->core->cache->get( 'failed_update' ) ) {
			aioseo()->core->cache->delete( 'failed_update' );
			$this->internalOptions->internal->license->lastChecked = strtotime( '+12 hours' );

			return;
		}

		// If update failed, check again after one hour. If the second check fails too, we'll wait 12 hours.
		aioseo()->core->cache->update( 'failed_update', time() );
		$this->internalOptions->internal->license->lastChecked = strtotime( '+1 hour' );
	}

	/**
	 * Validate plugin notifications for expired licenses.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	private function validateNotifications() {
		$notification = Models\Notification::getNotificationByName( 'license-expired' );
		if ( $this->isExpired() ) {
			if ( $notification->exists() ) {
				// Force the notice to reappear always.
				$notification->dismissed = false;
				$notification->save();

				return;
			}

			// Let user know we've found an error.
			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'license-expired',
				'title'             => __( 'Your License is expired and your SEO is at risk!', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - "Pro", 2 - The plugin name ("All in One SEO"), 3 - Opening bold tag, 4 - Closing bold tag.
					__( 'An active license is needed to use any of the %1$s features of %2$s, including %3$sVideo and News sitemaps, Redirection Manager, Breadcrumb Templates%4$s and more. It also provides access to new features & addons, plugin updates (including security improvements), and our world class support!', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					'Pro',
					AIOSEO_PLUGIN_SHORT_NAME,
					'<strong>',
					'</strong>'
				),
				'type'              => 'error',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Renew Now', 'aioseo-pro' ),
				'button1_action'    => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/downloads/', 'admin-notice', 'renew-now' ),
				'button2_label'     => __( 'Learn More', 'aioseo-pro' ),
				'button2_action'    => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/how-to-renew-your-aioseo-license/', 'admin-notice', 'learn-more' ),
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}

		if ( $notification->exists() ) {
			Models\Notification::deleteNotificationByName( 'license-expired' );
		}
	}

	/**
	 * Validate the license key.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not it was activated.
	 */
	public function activate() {
		$this->internalOptions->internal->license->reset(
			[
				'expires',
				'expired',
				'invalid',
				'disabled',
				'activationsError',
				'connectionError',
				'requestError',
				'level',
				'addons',
				'features'
			]
		);

		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
		}

		$site    = aioseo()->helpers->getSite();
		$domains = [
			'domain' => $site->domain,
			'path'   => $site->path
		];

		$response = $this->sendLicenseRequest( 'activate', $licenseKey, [ $domains ] );

		if ( empty( $response ) ) {
			// Something bad happened, error unknown.
			$this->internalOptions->internal->license->connectionError = true;

			return false;
		}

		if ( ! empty( $response->error ) ) {
			if ( 'missing-key-or-domain' === $response->error ) {
				$this->internalOptions->internal->license->requestError = true;

				return false;
			}

			if ( 'missing-license' === $response->error ) {
				$this->internalOptions->internal->license->invalid = true;

				return false;
			}

			if ( 'disabled' === $response->error ) {
				$this->internalOptions->internal->license->disabled = true;

				return false;
			}

			if ( 'activations' === $response->error ) {
				$this->internalOptions->internal->license->activationsError = true;

				return false;
			}

			if ( 'expired' === $response->error ) {
				$this->internalOptions->internal->license->expires = strtotime( $response->expires );
				$this->internalOptions->internal->license->expired = true;

				return false;
			}
		}

		// Something bad happened, error unknown.
		if ( empty( $response->success ) || empty( $response->level ) ) {
			return false;
		}

		$this->internalOptions->internal->license->level    = $response->level;
		$this->internalOptions->internal->license->addons   = wp_json_encode( $response->addons );
		$this->internalOptions->internal->license->expires  = strtotime( $response->expires );
		$this->internalOptions->internal->license->features = wp_json_encode( $response->features );

		return true;
	}

	/**
	 * Deactivate the license key.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not it was deactivated.
	 */
	public function deactivate() {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
		}

		$site    = aioseo()->helpers->getSite();
		$domains = [
			'domain' => $site->domain,
			'path'   => $site->path
		];

		$response = $this->sendLicenseRequest( 'deactivate', $licenseKey, [ $domains ] );

		if ( empty( $response ) ) {
			// Something bad happened, error unknown.
			$this->internalOptions->internal->license->connectionError = true;

			return false;
		}

		if ( ! empty( $response->error ) ) {
			if ( 'missing-key-or-domain' === $response->error || 'not-activated' === $response->error ) {
				$this->internalOptions->internal->license->requestError = true;

				return false;
			}

			if ( 'missing-license' === $response->error ) {
				$this->internalOptions->internal->license->invalid = true;

				return false;
			}

			if ( 'disabled' === $response->error ) {
				$this->internalOptions->internal->license->disabled = true;

				return false;
			}
		}

		$this->internalOptions->internal->license->reset(
			[
				'expires',
				'expired',
				'invalid',
				'disabled',
				'activationsError',
				'connectionError',
				'requestError',
				'level',
				'addons',
				'features'
			]
		);

		return true;
	}

	/**
	 * Output any notices generated by the class.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $belowH2
	 */
	public function notices( $belowH2 = false ) {
		// Double check we're actually in the admin before outputting anything.
		if ( ! is_admin() ) {
			return;
		}

		// Grab the option and output any nag dealing with license keys.
		$isActive = $this->isActive();
		$expired  = $this->internalOptions->internal->license->expired;
		$invalid  = $this->internalOptions->internal->license->invalid;
		$disabled = $this->internalOptions->internal->license->disabled;
		$belowH2  = $belowH2 ? 'below-h2' : '';

		// If there is no license key, output nag about ensuring key is set for automatic updates.
		if ( ! $isActive ) {
			?>
			<div class="notice notice-info <?php echo esc_attr( $belowH2 ); ?> aioseo-license-notice">
				<p>
					<?php
					echo wp_kses(
						sprintf(
								// Translators: 1 - Opening link tag, 2 - Closing link tag, 4 - The plugin name ("All in One SEO").
							esc_html__( 'Please %1$senter and activate%2$s your license key for %3$s to enable automatic updates.', 'aioseo-pro' ),
							sprintf( '<a href="%1$s">', esc_url( add_query_arg( [ 'page' => 'aioseo-settings' ], admin_url( 'admin.php' ) ) ) ),
							'</a>',
							esc_html( AIOSEO_PLUGIN_NAME )
						),
						[
							'a' => [
								'href' => [],
							],
						]
					)
					?>
				</p>
			</div>
			<?php

			return;
		}

		// If a key has expired, output nag about renewing the key.
		if ( $expired ) {
			$renewNowUrl  = aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/downloads/', 'admin-notice', 'renew-now' );
			$learnMoreUrl = aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/how-to-renew-your-aioseo-license/', 'admin-notice', 'learn-more' );
			?>
			<div class="error notice <?php echo esc_attr( $belowH2 ); ?> aioseo-notice aioseo-license-notice">
				<h3 style="margin: .75em 0 0 0;">
					<svg xmlns="http://www.w3.org/2000/svg" width="24.067" height="24" style="vertical-align: text-top; width: 24.067px; margin-right: 7px;">
						<defs>
							<style>.b{fill:#231f20}</style>
						</defs>
						<g transform="translate(-.066)"><path d="M1.6 24a1.338 1.338 0 01-1.3-2.1L11 .9c.6-1.2 1.6-1.2 2.2 0l10.7 21c.6 1.2 0 2.1-1.3 2.1z" fill="#ffce31" />
							<path class="b" d="M10.3 8.6l1.1 7.4a.605.605 0 001.2 0l1.1-7.4a1.738 1.738 0 10-3.4 0z" />
							<circle class="b" cx="1.7" cy="1.7" r="1.7" transform="translate(10.3 17.3)" />
						</g>
					</svg>
					<?php
					// Translators: 1 - The plugin name ("All in One SEO"). */
					printf( esc_html__( 'Heads up! Your %1$s license has expired and your SEO is at risk!', 'aioseo-pro' ), esc_html( AIOSEO_PLUGIN_NAME ) );
					?>
				</h3>
				<p>
					<?php
					// Translators: 1 - "Pro", 2 - The plugin name ("All in One SEO"), 3 - Opening bold tag, 4 - Closing bold tag.
					printf( esc_html__( 'An active license is needed to use any of the %1$s features of %2$s, including %3$sVideo and News sitemaps, Redirection Manager, Breadcrumb Templates%4$s and more. It also provides access to new features & addons, plugin updates (including security improvements), and our world class support!', 'aioseo-pro' ), 'Pro', esc_html( AIOSEO_PLUGIN_NAME ), '<strong>', '</strong>' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $renewNowUrl ); ?>" class="button-primary"><?php esc_html_e( 'Renew Now', 'aioseo-pro' ); ?></a> &nbsp
					<a href="<?php echo esc_url( $learnMoreUrl ); ?>" class="button-secondary"><?php esc_html_e( 'Learn More', 'aioseo-pro' ); ?></a>
				</p>
			</div>
			<?php
		}

		// If a key has been disabled, output nag about using another key.
		if ( $disabled ) {
			?>
			<div class="error notice <?php echo esc_attr( $belowH2 ); ?> aioseo-license-notice">
				<p>
					<?php
					printf(
						// Translators: 1 - The plugin name ("All in One SEO").
						esc_html__( 'Your license key for %1$s has been disabled. Please use a different key to continue receiving automatic updates.', 'aioseo-pro' ),
						esc_html( AIOSEO_PLUGIN_NAME )
					);
					?>
				</p>
			</div>
			<?php
		}

		// If a key is invalid, output nag about using another key.
		if ( $invalid ) {
			?>
			<div class="error notice <?php echo esc_attr( $belowH2 ); ?> aioseo-license-notice">
				<p>
					<?php
						printf(
							// Translators: 1 - The plugin name ("All in One SEO").
							esc_html__( 'Your license key for %1$s is invalid. The key no longer exists or the user associated with the key has been deleted. Please use a different key to continue receiving automatic updates.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
							esc_html( AIOSEO_PLUGIN_NAME )
						);
					?>
					<a href="admin.php?page=aioseo-settings"><?php esc_html_e( 'Manage Licenses', 'aioseo-pro' ); ?>.</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Adds a notice to the update row for unlicensed users.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function updateRowNotice() {
		if ( $this->isActive() || is_network_admin() ) {
			return;
		}

		$this->outputUpdateRowNotice();
	}

	/**
	 * Outputs the update row notice.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	protected function outputUpdateRowNotice() {
		echo '<br><span style="margin-left:26px;">' . sprintf(
			// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag, 3 - The plugin name ("All in One SEO").
			esc_html__( 'A %1$svalid license key%2$s is required to download updates for %3$s.', 'aioseo-pro' ),
			'<strong>',
			'</strong>',
			esc_html( AIOSEO_PLUGIN_NAME )
		) . '</span>';
	}

	/**
	 * Add row to Plugins page with licensing information, if license key is invalid or not found.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function pluginRowNotice() {
		if ( $this->isActive() || is_network_admin() ) {
			return;
		}

		$this->outputPluginRowNotice();
	}

	/**
	 * Outputs the plugin row notice.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	protected function outputPluginRowNotice() {
		$message = esc_html__( 'has not been entered', 'aioseo-pro' );
		$pre     = sprintf(
			// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
			' %1$sClick here to enter one now!%2$s',
			'<a href="' . admin_url( 'admin.php?page=aioseo-settings' ) . '">',
			'</a>'
		);

		$licenseKey = $this->options->general->licenseKey;
		if ( ! empty( $licenseKey ) ) {
			$pre = '';
			if ( $this->isExpired() ) {
				$message = esc_html__( 'is expired', 'aioseo-pro' );
			}
			if ( $this->isDisabled() ) {
				$message = esc_html__( 'is disabled', 'aioseo-pro' );
			}
			if ( $this->isInvalid() ) {
				$message = esc_html__( 'is invalid', 'aioseo-pro' );
			}
		}

		// Translators: 1 - HTML Line break tags, 2 - "Pro", 3 - Opening bold tag, 4 - Closing bold tag, 5 - HTML Line break tags.
		$end = sprintf( esc_html__( 'and your SEO is at risk!%1$sAn active license is needed to use any of the %2$s features including %3$sVideo and News sitemaps, Redirection Manager, Breadcrumb Templates%4$s and more. It also provides access to new features & addons, plugin updates (including security improvements), and our world class support! %5$s', 'aioseo-pro' ), $pre . '<br><br>', 'Pro', '<strong>', '</strong>', '<br><br>' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded

		echo '
			<tr class="plugin-update-tr active">
				<td colspan="4" class="plugin-update colspanchange">
					<div class="update-message notice notice-warning notice-error inline">
						<p>
							<span>' .
								sprintf(
									'%1$s <strong>%2$s</strong> %3$s %4$s',
									esc_html__( 'Your', 'aioseo-pro' ),
									esc_html__( 'license key', 'aioseo-pro' ),
									$message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$end // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								) .
							'</span>
						</p>
						<p>
							<a href="admin.php?page=aioseo-settings">' . esc_html__( 'Manage Licenses', 'aioseo-pro' ) . '</a> |
							<a
								href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'pricing/', 'invalid-license' ) . '"' // phpcs:ignore WordPress.Security.EscapeOutput, Generic.Files.LineLength.MaxExceeded
								. 'target="_blank"
							>' . esc_html__( 'Purchase one now.', 'aioseo-pro' ) . '</a>
						</p>
					</div>
				</td>
			</tr>
		';
	}

	/**
	 * Get the URL to check licenses.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL.
	 */
	public function getUrl() {
		if ( defined( 'AIOSEO_LICENSING_URL' ) ) {
			return AIOSEO_LICENSING_URL;
		}

		return $this->baseUrl;
	}

	/**
	 * Checks to see if the current license is expired.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if expired, false if not.
	 */
	public function isExpired() {
		$networkIsExpired = $this->isNetworkLicensed() && aioseo()->networkLicense->isExpired();
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return $networkIsExpired;
		}

		$expired = $this->internalOptions->internal->license->expired;
		if ( $expired ) {
			return true;
		}

		$expires = $this->internalOptions->internal->license->expires;

		return 0 !== $expires && $expires < time();
	}

	/**
	 * Checks to see if the current license is disabled.
	 *
	 * @return bool True if disabled, false if not.
	 */
	public function isDisabled() {
		$networkIsDisabled = $this->isNetworkLicensed() && aioseo()->networkLicense->isDisabled();
		$licenseKey        = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return $networkIsDisabled;
		}

		return $this->internalOptions->internal->license->disabled;
	}

	/**
	 * Checks to see if the current license is invalid.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if invalid, false if not.
	 */
	public function isInvalid() {
		$networkIsInvalid = $this->isNetworkLicensed() && aioseo()->networkLicense->isInvalid();
		$licenseKey       = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return $networkIsInvalid;
		}

		return $this->internalOptions->internal->license->invalid;
	}

	/**
	 * Checks to see if the current license is disabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if disabled, false if not.
	 */
	public function isActive() {
		$networkIsActive = $this->isNetworkLicensed() && aioseo()->networkLicense->isActive();
		$licenseKey      = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return $networkIsActive;
		}

		return ! $this->isExpired() && ! $this->isDisabled() && ! $this->isInvalid();
	}

	/**
	 * Get the license level for the activated license.
	 *
	 * @since 4.0.0
	 *
	 * @return string The license level.
	 */
	public function getLicenseLevel() {
		$networkLicenseLevel = $this->isNetworkLicensed() ? aioseo()->networkLicense->getLicenseLevel() : 'Unknown';
		$licenseKey          = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return $networkLicenseLevel;
		}

		return $this->internalOptions->internal->license->level;
	}

	/**
	 * Get the license features for the activated license.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $type The feature type.
	 * @return array        The license features.
	 */
	public function getLicenseFeatures( $type = '' ) {
		$features    = $this->isNetworkLicensed() && empty( $this->options->general->licenseKey )
			? aioseo()->internalNetworkOptions->internal->license->features
			: $this->internalOptions->internal->license->features;
		$allFeatures = json_decode( $features, true ) ?: [];
		if ( ! empty( $type ) ) {
			$allFeatures = ! empty( $allFeatures[ $type ] ) ? $allFeatures[ $type ] : [];
		}

		return $allFeatures;
	}

	/**
	 * Get the core feature for the activated license.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $sectionSlug The section name.
	 * @param  string $feature     The feature name.
	 * @return bool                The license has access to a core feature.
	 */
	public function hasCoreFeature( $sectionSlug, $feature = '' ) {
		$coreFeatures = $this->getLicenseFeatures( 'core' );
		foreach ( $coreFeatures as $section => $features ) {
			if ( $sectionSlug === $section && empty( $feature ) ) {
				return true;
			}

			if ( $sectionSlug === $section && in_array( $feature, $features, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the addon feature for the activated license.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $addonName The addon name.
	 * @param  string $feature   The feature name.
	 * @return bool              The license has access to an addon feature.
	 */
	public function hasAddonFeature( $addonName, $feature ) {
		$addons = $this->getLicenseFeatures( 'addons' );
		foreach ( $addons as $addon => $features ) {
			if ( $addon === $addonName && in_array( $feature, $features, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether a given addon can be used with the current license plan.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $addonName The addon name.
	 * @return boolean            Whether the addon can be used.
	 */
	public function isAddonAllowed( $addonName ) {
		return true;
		
		$addons = $this->isNetworkLicensed() && empty( $this->options->general->licenseKey )
			? aioseo()->internalNetworkOptions->internal->license->addons
			: $this->internalOptions->internal->license->addons;

		if ( is_string( $addons ) ) {
			$addons = json_decode( $addons );
		}

		if ( empty( $addons ) ) {
			return false;
		}

		return in_array( $addonName, $addons, true );
	}

	/**
	 * Checks if the license data needs to be reset.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if a reset is needed, false if not.
	 */
	private function needsReset() {
		if ( ! empty( $this->options->general->licenseKey ) ) {
			return false;
		}

		if ( $this->internalOptions->internal->license->level ) {
			return true;
		}

		if ( $this->internalOptions->internal->license->invalid ) {
			return true;
		}

		if ( $this->internalOptions->internal->license->disabled ) {
			return true;
		}

		$expired = $this->internalOptions->internal->license->expired;
		if ( $expired ) {
			return true;
		}

		$expires = $this->internalOptions->internal->license->expires;

		return 0 !== $expires;
	}

	/**
	 * Send the license request.
	 *
	 * @since 4.2.5
	 *
	 * @param  string      $type       The type of request, either activate or deactivate.
	 * @param  string      $licenseKey The license key we are using for this request.
	 * @param  array       $domains    An array of domains to activate or deactivate.
	 * @return Object|null             The JSON response as an object.
	 */
	public function sendLicenseRequest( $type, $licenseKey, $domains ) {
		$payload = [
			'sku'         => 'all-in-one-seo-pack-pro',
			'version'     => AIOSEO_VERSION,
			'license'     => $licenseKey,
			'domains'     => $domains,
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' )
		];

		return aioseo()->helpers->sendRequest( $this->getUrl() . $type . '/', $payload );
	}

	/**
	 * Checks if the current site is licensed at the network level.
	 *
	 * @since 4.2.5
	 *
	 * @return bool True if licensed at the network level and not licensed locally.
	 */
	public function isNetworkLicensed() {
		if ( ! aioseo()->networkLicense ) {
			return false;
		}

		return aioseo()->networkLicense->isActive();
	}
}