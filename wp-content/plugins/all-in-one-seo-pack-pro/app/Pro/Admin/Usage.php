<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Usage tracking class.
 *
 * @since 4.0.0
 */
class Usage extends CommonAdmin\Usage {
	/**
	 * Class Constructor
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->enabled = true;
	}

	/**
	 * Get the type for the request.
	 *
	 * @since 4.0.0
	 *
	 * @return string The install type.
	 */
	public function getType() {
		return 'pro';
	}

	/**
	 * Retrieves the data to send in the usage tracking.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of data to send.
	 */
	protected function getData() {
		$data = parent::getData();
		$data['aioseo_license_key']  = aioseo()->options->general->licenseKey;
		$data['aioseo_license_type'] = aioseo()->internalOptions->internal->license->level;
		$data['aioseo_is_pro']       = true;
		$data['addon_data']          = [];

		// Get usage tracking data from the addons.
		foreach ( aioseo()->addons->getLoadedAddons() as $addonSlug => $loadedAddon ) {
			if ( ! empty( $loadedAddon->usage ) && method_exists( $loadedAddon->usage, 'getData' ) ) {
				$data['addon_data'][ $addonSlug ] = $loadedAddon->usage->getData();
			}
		}

		return $data;
	}
}