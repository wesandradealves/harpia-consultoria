<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The License class to validate/activate/deactivate license keys.
 *
 * @since 4.2.5
 */
class NetworkLicense extends License {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.5
	 *
	 * @param boolean $maybeValidate Whether or not to run the validation.
	 */
	public function __construct( $maybeValidate = true ) {
		$this->options         = aioseo()->networkOptions;
		$this->internalOptions = aioseo()->internalNetworkOptions;

		if ( $maybeValidate ) {
			$this->maybeValidate();
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (
			is_network_admin() &&
			! is_plugin_active_for_network( plugin_basename( AIOSEO_FILE ) )
		) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || 'aioseo-settings' !== wp_unslash( $_GET['page'] ) ) { // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			add_action( 'network_admin_notices', [ $this, 'notices' ] );
		}

		add_action( 'after_plugin_row_' . AIOSEO_PLUGIN_BASENAME, [ $this, 'pluginRowNotice' ] );
		add_action( 'in_plugin_update_message-' . AIOSEO_PLUGIN_BASENAME, [ $this, 'updateRowNotice' ] );
	}

	/**
	 * Validate the license keys for a multisite setup.
	 *
	 * @since 4.2.5
	 *
	 * @param  array    $domains Domains for activation and deactivation.
	 * @return boolean           Whether or not it was activated.
	 */
	public function multisite( $domains ) {
		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );

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
				'addons'
			]
		);

		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		$site    = aioseo()->helpers->getSite();
		$domains = ! empty( $domains )
			? $domains
			: [
				[
					'domain' => $site->domain,
					'path'   => $site->path
				]
			];

		$response = $this->sendLicenseRequest( 'multisite', $licenseKey, $domains );

		if ( empty( $response ) ) {
			// Something bad happened, error unknown.
			$this->internalOptions->internal->license->connectionError = true;

			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		if ( ! empty( $response->error ) ) {
			if ( 'missing-key-or-domain' === $response->error ) {
				$this->internalOptions->internal->license->requestError = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'missing-license' === $response->error ) {
				$this->internalOptions->internal->license->invalid = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'disabled' === $response->error ) {
				$this->internalOptions->internal->license->disabled = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'activations' === $response->error ) {
				$this->internalOptions->internal->license->activationsError = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'expired' === $response->error ) {
				$this->internalOptions->internal->license->expires = strtotime( $response->expires );
				$this->internalOptions->internal->license->expired = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}
		}

		// Something bad happened, error unknown.
		if ( empty( $response->success ) || empty( $response->level ) ) {
			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		$this->internalOptions->internal->license->level    = $response->level;
		$this->internalOptions->internal->license->addons   = wp_json_encode( $response->addons );
		$this->internalOptions->internal->license->expires  = strtotime( $response->expires );
		$this->internalOptions->internal->license->features = wp_json_encode( $response->features );
		$this->internalOptions->internal->sites->active     = wp_json_encode( $response->all_activations_and_paths );

		aioseo()->helpers->restoreCurrentBlog();

		return true;
	}


	/**
	 * Validate the license key.
	 *
	 * @since 4.2.5
	 *
	 * @param  array   $newDomains New domains to activate.
	 * @return boolean             Whether or not it was activated.
	 */
	public function activate( $newDomains = [] ) {
		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );

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
				'addons'
			]
		);

		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		$site    = aioseo()->helpers->getSite();
		$domains = ! empty( $newDomains )
			? $newDomains
			: [
				[
					'domain' => $site->domain,
					'path'   => $site->path
				]
			];

		$response = $this->sendLicenseRequest( 'activate', $licenseKey, $domains );

		if ( empty( $response ) ) {
			// Something bad happened, error unknown.
			$this->internalOptions->internal->license->connectionError = true;

			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		if ( ! empty( $response->error ) ) {
			if ( 'missing-key-or-domain' === $response->error ) {
				$this->internalOptions->internal->license->requestError = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'missing-license' === $response->error ) {
				$this->internalOptions->internal->license->invalid = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'disabled' === $response->error ) {
				$this->internalOptions->internal->license->disabled = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'activations' === $response->error ) {
				$this->internalOptions->internal->license->activationsError = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'expired' === $response->error ) {
				$this->internalOptions->internal->license->expires = strtotime( $response->expires );
				$this->internalOptions->internal->license->expired = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}
		}

		// Something bad happened, error unknown.
		if ( empty( $response->success ) || empty( $response->level ) ) {
			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		$this->internalOptions->internal->license->level    = $response->level;
		$this->internalOptions->internal->license->addons   = wp_json_encode( $response->addons );
		$this->internalOptions->internal->license->expires  = strtotime( $response->expires );
		$this->internalOptions->internal->license->features = wp_json_encode( $response->features );
		$this->internalOptions->internal->sites->active     = wp_json_encode( $response->all_activations_and_paths );

		aioseo()->helpers->restoreCurrentBlog();

		return true;
	}

	/**
	 * Deactivate the license key.
	 *
	 * @since 4.2.5
	 *
	 * @param  array   $newDomains New domains to activate.
	 * @return boolean Whether or not it was deactivated.
	 */
	public function deactivate( $domains = [] ) {
		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );

		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		$site    = aioseo()->helpers->getSite();
		$domainsToDeactivate = ! empty( $domains )
			? $domains
			: [
				[
					'domain' => $site->domain,
					'path'   => $site->path
				]
			];

		$response = $this->sendLicenseRequest( 'deactivate', $licenseKey, $domainsToDeactivate );

		if ( empty( $response ) ) {
			// Something bad happened, error unknown.
			$this->internalOptions->internal->license->connectionError = true;

			aioseo()->helpers->restoreCurrentBlog();

			return false;
		}

		if ( ! empty( $response->error ) ) {
			if ( 'missing-key-or-domain' === $response->error || 'not-activated' === $response->error ) {
				$this->internalOptions->internal->license->requestError = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'missing-license' === $response->error ) {
				$this->internalOptions->internal->license->invalid = true;

				aioseo()->helpers->restoreCurrentBlog();

				return false;
			}

			if ( 'disabled' === $response->error ) {
				$this->internalOptions->internal->license->disabled = true;

				aioseo()->helpers->restoreCurrentBlog();

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
				'addons'
			]
		);

		$this->internalOptions->internal->license->level    = $response->level;
		$this->internalOptions->internal->license->addons   = wp_json_encode( $response->addons );
		$this->internalOptions->internal->license->expires  = strtotime( $response->expires );
		$this->internalOptions->internal->license->features = wp_json_encode( $response->features );
		$this->internalOptions->internal->sites->active     = wp_json_encode( $response->all_activations_and_paths );

		aioseo()->helpers->restoreCurrentBlog();

		return true;
	}

	/**
	 * Checks to see if the current license is expired.
	 *
	 * @since 4.2.5
	 *
	 * @return bool True if expired, false if not.
	 */
	public function isExpired() {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
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
	 * @since 4.2.5
	 *
	 * @return bool True if disabled, false if not.
	 */
	public function isDisabled() {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
		}

		return $this->internalOptions->internal->license->disabled;
	}

	/**
	 * Checks to see if the current license is invalid.
	 *
	 * @since 4.2.5
	 *
	 * @return bool True if invalid, false if not.
	 */
	public function isInvalid() {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
		}

		return $this->internalOptions->internal->license->invalid;
	}

	/**
	 * Checks to see if the current license is disabled.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_Site $site The site to check if the the license is active on.
	 * @return bool           True if disabled, false if not.
	 */
	public function isActive( $site = null ) {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return false;
		}

		if ( ! $this->isSiteActive( $site ) ) {
			return false;
		}

		return ! $this->isExpired() && ! $this->isDisabled() && ! $this->isInvalid();
	}

	/**
	 * Get the license level for the activated license.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_Site $site The site to check if the the license is active on.
	 * @return string         The license level.
	 */
	public function getLicenseLevel( $site = null ) {
		$licenseKey = $this->options->general->licenseKey;
		if ( empty( $licenseKey ) ) {
			return 'Unknown';
		}

		if ( ! $this->isSiteActive( $site ) ) {
			return 'Unknown';
		}

		return $this->internalOptions->internal->license->level;
	}

	/**
	 * Checks if the current site is licensed at the network level.
	 *
	 * @since 4.2.5
	 *
	 * @return bool True if licensed at the network level.
	 */
	public function isNetworkLicensed() {
		// If we are already locally activated, then no it's not network licensed.
		if ( aioseo()->license->isActive() ) {
			return false;
		}

		if ( $this->isActive() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the current site is active in the network activations.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_Site $site The site to check against.
	 * @return bool           True if active, false if not.
	 */
	private function isSiteActive( $site = null ) {
		if ( empty( $site ) ) {
			$site = \WP_Site::get_instance( get_current_blog_id() );
		}

		$activeSites = json_decode( aioseo()->internalNetworkOptions->internal->sites->active );
		if ( empty( $activeSites ) ) {
			return false;
		}

		foreach ( $activeSites as $as ) {
			if ( $site->domain === $as->domain && $site->path === $as->path ) {
				return true;
			}

			if ( ! empty( $site->aliases ) ) {
				foreach ( $site->aliases as $alias ) {
					if ( $as->domain === $alias['domain'] ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Adds a notice to the update row for unlicensed users.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function updateRowNotice() {
		if ( $this->isActive() ) {
			return;
		}

		$this->outputUpdateRowNotice();
	}

	/**
	 * Add row to Plugins page with licensing information, if license key is invalid or not found.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function pluginRowNotice() {
		if ( $this->isActive() ) {
			return;
		}

		$this->outputPluginRowNotice();
	}
}