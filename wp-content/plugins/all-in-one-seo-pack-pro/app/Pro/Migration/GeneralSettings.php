<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Video Sitemap settings from V3.
 *
 * @since 4.0.0
 */
class GeneralSettings {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->migrateGoogleAnalytics();

		$settings = [
			'aiosp_ga_track_outbound_forms' => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackOutboundForms' ] ],
			'aiosp_ga_track_events'         => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackEvents' ] ],
			'aiosp_ga_track_url_changes'    => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackUrlChanges' ] ],
			'aiosp_ga_track_visibility'     => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackVisibility' ] ],
			'aiosp_ga_track_media_query'    => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackMediaQueries' ] ],
			'aiosp_ga_track_impressions'    => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackImpressions' ] ],
			'aiosp_ga_track_scroller'       => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackScrollbar' ] ],
			'aiosp_ga_track_social'         => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackSocial' ] ],
			'aiosp_ga_track_clean_url'      => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'trackCleanUrl' ] ],
			'aiosp_gtm_container_id'        => [ 'type' => 'string', 'newOption' => [ 'deprecated', 'webmasterTools', 'googleAnalytics', 'gtmContainerId' ] ],
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, aioseo()->migration->oldOptions );
	}

	/**
	 * Enables deprecated Google Analytics if there is an existing GTM id.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	private function migrateGoogleAnalytics() {
		$oldOptions = aioseo()->migration->oldOptions;
		if ( empty( $oldOptions['aiosp_gtm_container_id'] ) ) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
		if ( ! in_array( 'googleAnalytics', $deprecatedOptions, true ) ) {
			array_push( $deprecatedOptions, 'googleAnalytics' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
		}
	}
}