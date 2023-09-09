<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Outputs the Google Analytics to the head. (Also GTM if it is enabled).
 *
 * @since 4.0.0
 */
class GoogleAnalytics extends CommonMain\GoogleAnalytics {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'addGtm' ] );
	}

	/**
	 * Adds GTM if needed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addGtm() {
		if ( $this->canShowGtm() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueueGtmAssets' ] );
		}
	}

	/**
	 * Get analytics options.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of options.
	 */
	public function getOptions() {
		$options = parent::getOptions();
		if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->advanced && ! $this->canShowGtm() ) {
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackOutboundForms ) {
				$options['options'][] = [
					'require',
					'outboundFormTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackEvents ) {
				$options['options'][] = [
					'require',
					'eventTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackUrlChanges ) {
				$options['options'][] = [
					'require',
					'urlChangeTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackVisibility ) {
				$options['options'][] = [
					'require',
					'pageVisibilityTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackMediaQueries ) {
				$options['options'][] = [
					'require',
					'mediaQueryTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackImpressions ) {
				$options['options'][] = [
					'require',
					'impressionTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackScrollbar ) {
				$options['options'][] = [
					'require',
					'maxScrollTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackSocial ) {
				$options['options'][] = [
					'require',
					'socialWidgetTracker'
				];
			}
			if ( aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackCleanUrl ) {
				$options['options'][] = [
					'require',
					'cleanUrlTracker'
				];
			}
		}

		if ( $this->canShowGtm() ) {
			$optionsToRemove = [
				'ec',
				'outboundLinkTracker'
			];

			foreach ( $optionsToRemove as $option ) {
				foreach ( $options['options'] as $key => $opt ) {
					$index = array_search( $option, $opt, true );
					if ( false !== $index ) {
						unset( $options['options'][ $key ] );
						continue 2;
					}
				}
			}
		}

		return $options;
	}

	/**
	 * Check if autotrack JS should be included.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if so, false if not.
	 */
	public function autoTrack() {
		if ( ! aioseo()->options->deprecated->webmasterTools->googleAnalytics->advanced ) {
			return false;
		}

		if (
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackOutboundLinks ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackOutboundForms ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackEvents ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackUrlChanges ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackVisibility ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackMediaQueries ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackImpressions ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackScrollbar ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackSocial ||
			aioseo()->options->deprecated->webmasterTools->googleAnalytics->trackCleanUrl
		) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if we can show GTM on the site.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether or not we can show GTM.
	 */
	public function canShowGtm() {
		if ( aioseo()->helpers->isAmpPage() ) {
			return false;
		}

		$containerId = aioseo()->options->deprecated->webmasterTools->googleAnalytics->gtmContainerId;

		if (
			in_array( 'googleAnalytics', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! $containerId
		) {
			return false;
		}

		$disable = apply_filters( 'aioseo_disable_google_tag_manager', false );

		if (
			$disable ||
			is_admin() ||
			empty( $containerId ) ||
			! preg_match( '/GTM-.{6}/', $containerId )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueues the GTM assets when needed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueGtmAssets() {
		aioseo()->core->assets->load( 'src/app/gtm/main.js', [], [
			'containerId' => aioseo()->options->deprecated->webmasterTools->googleAnalytics->gtmContainerId
		], 'aioseoGtm' );
	}
}