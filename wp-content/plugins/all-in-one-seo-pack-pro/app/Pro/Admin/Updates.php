<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The updates class to check for updates from our server.
 *
 * @since 4.0.0
 */
class Updates {
	/**
	 * Plugin slug.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|string
	 */
	public $pluginSlug = false;

	/**
	 * Plugin path.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|string
	 */
	public $pluginPath = false;

	/**
	 * Version number of the plugin.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|int
	 */
	public $version = false;

	/**
	 * License key for the plugin.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|string
	 */
	public $key = false;

	/**
	 * Store the update data returned from the API.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|object
	 */
	public $update = false;

	/**
	 * Store the plugin info details for the update.
	 *
	 * @since 4.0.0
	 *
	 * @var bool|object
	 */
	public $info = false;

	/**
	 * Source of notifications content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $baseUrl = 'https://licensing.aioseo.com/v1/';

	/**
	 * Primary class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param array $config Array of updater config args.
	 */
	public function __construct( array $config ) {
		// Set class properties.
		$acceptedArgs = [
			'pluginSlug',
			'pluginPath',
			'version',
			'key',
		];

		foreach ( $acceptedArgs as $arg ) {
			$this->$arg = $config[ $arg ];
		}

		// If the user cannot update plugins, stop processing here.
		if ( ! current_user_can( 'update_plugins' ) && ! aioseo()->helpers->isDoingWpCli() ) {
			return;
		}

		// Load the updater hooks and filters.
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'updatePluginsFilter' ], 1000 );
		add_filter( 'http_request_args', [ $this, 'httpRequestArgs' ] );
		add_filter( 'plugins_api', [ $this, 'pluginsApi' ], 10, 3 );
	}

	/**
	 * Infuse plugin update details when WordPress runs its update checker.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed  $value The WordPress update object.
	 * @return object        Amended WordPress update object on success, default if object is empty.
	 */
	public function updatePluginsFilter( $value ) {
		// If no update object exists, bail to prevent errors.
		if ( empty( $value ) || ! is_object( $value ) ) {
			return $value;
		}

		// Run update check by pinging the external API. If it fails, return the default update object.
		if ( ! $this->update ) {
			$this->update = $this->checkForUpdates();

			if ( ! $this->update || ! empty( $this->update->error ) ) {
				$this->update = false;

				return $value;
			}

			$this->update->description = preg_replace( '/\s+/', ' ', $this->update->description );
			$this->update->changelog   = preg_replace( '/\s+/', ' ', $this->update->changelog );
		}

		$this->update->icons      = (array) $this->update->icons;
		$this->update->aioseo     = true;
		$this->update->plugin     = $this->pluginPath;
		$this->update->oldVersion = $this->version;

		// Infuse the update object with our data if the version from the remote API is newer.
		if ( isset( $this->update->new_version ) && version_compare( $this->version, $this->update->new_version, '<' ) ) {
			// The $plugin_update object contains new_version, package, slug, and last_update keys.
			$value->response[ $this->pluginPath ] = $this->update;
		} else {
			$this->update->new_version             = $this->version;
			$this->update->plugin                  = $this->pluginPath;
			$value->no_update[ $this->pluginPath ] = $this->update;
		}

		// Return the update object.
		return $value;
	}

	/**
	 * Check for updates request.
	 *
	 * @since 4.0.0
	 *
	 * @return Object an object with the update information.
	 */
	public function checkForUpdates() {
		$args = [
			'license'     => $this->key,
			'domain'      => aioseo()->helpers->getSiteDomain(),
			'sku'         => $this->pluginSlug,
			'version'     => $this->version,
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' )
		];

		return aioseo()->helpers->sendRequest( $this->getUrl() . 'update/', $args );
	}

	/**
	 * Disable SSL verification to prevent download package failures.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $args Array of request args.
	 * @param  string $url  The URL to be pinged.
	 * @return array        Amended array of request args.
	 */
	public function httpRequestArgs( $args ) {
		return $args;
	}

	/**
	 * Filter the plugins_api function to get our own custom plugin information
	 * from our private repo.
	 *
	 * @since 4.0.0
	 *
	 * @param  object $api    The original plugins_api object.
	 * @param  string $action The action sent by plugins_api.
	 * @param  object $args   Additional args to send to plugins_api.
	 * @return object         New stdClass with plugin information on success, default response on failure.
	 */
	public function pluginsApi( $api, $action = '', $args = null ) {
		$plugin = ( 'plugin_information' === $action ) && isset( $args->slug ) && ( $this->pluginSlug === $args->slug );

		// If our plugin matches the request, set our own plugin data, else return the default response.
		if ( $plugin ) {
			return $this->setPluginsApi( $api );
		}

		return $api;
	}

	/**
	 * Ping a remote API to retrieve plugin information for WordPress to display.
	 *
	 * @since 4.0.0
	 *
	 * @param  object $defaultApi The default API object.
	 * @return object             Return custom plugin information to plugins_api.
	 */
	public function setPluginsApi( $defaultApi ) {
		// Perform the remote request to retrieve our plugin information. If it fails, return the default object.
		if ( ! $this->info ) {
			$response = aioseo()->helpers->sendRequest( $this->getUrl() . 'info/', [
				'license'     => $this->key,
				'domain'      => aioseo()->helpers->getSiteDomain(),
				'sku'         => $this->pluginSlug,
				'version'     => $this->version,
				'php_version' => PHP_VERSION,
				'wp_version'  => get_bloginfo( 'version' )
			] );

			if ( empty( $response ) || ! empty( $response->error ) ) {
				$this->info = false;

				return $defaultApi;
			}

			$this->info = $response;
		}

		// Create a new stdClass object and populate it with our plugin information.
		$api                        = new \stdClass();
		$api->name                  = isset( $this->info->name ) ? $this->info->name : '';
		$api->slug                  = isset( $this->info->slug ) ? $this->info->slug : '';
		$api->version               = isset( $this->info->version ) ? $this->info->version : '';
		$api->author                = isset( $this->info->author ) ? $this->info->author : '';
		$api->author_profile        = isset( $this->info->author_profile ) ? $this->info->author_profile : '';
		$api->requires              = isset( $this->info->requires ) ? $this->info->requires : '';
		$api->tested                = isset( $this->info->tested ) ? $this->info->tested : '';
		$api->last_updated          = isset( $this->info->last_updated ) ? $this->info->last_updated : '';
		$api->homepage              = isset( $this->info->homepage ) ? $this->info->homepage : '';
		$api->sections['description'] = isset( $this->info->description ) ? $this->info->description : '';
		$api->sections['changelog'] = isset( $this->info->changelog ) ? $this->info->changelog : '';
		$api->download_link         = isset( $this->info->download_link ) ? $this->info->download_link : '';
		$api->active_installs       = isset( $this->info->active_installs ) ? $this->info->active_installs : '';
		$api->banners               = isset( $this->info->banners ) ? (array) $this->info->banners : '';

		// Return the new API object with our custom data.
		return $api;
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
}