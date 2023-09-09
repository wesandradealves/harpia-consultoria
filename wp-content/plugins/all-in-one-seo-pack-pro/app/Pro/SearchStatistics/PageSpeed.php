<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The PageSpeed class.
 *
 * @since 4.3.0
 */
class PageSpeed {
	/**
	 * Prefix for the cache entries.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $cachePrefix = 'search_statistics_pagespeed';

	/**
	 * Returns the results for both Desktop and Mobile Page Speed Insights.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $url   The page URL to fetch the data.
	 * @param  bool   $force Whether to force the request or not.
	 * @return array         The results.
	 */
	public function getResults( $url, $force = false ) {
		$cacheKey = $this->getCacheKey( $url );
		$cache    = aioseo()->core->cache->get( $cacheKey );

		if ( null !== $cache && ! $force ) {
			return $cache;
		}

		$data = [
			'desktop'   => $this->request( $url, 'desktop' ),
			'mobile'    => $this->request( $url, 'mobile' ),
			'refreshed' => current_time( 'mysql' ),
		];

		aioseo()->core->cache->update( $cacheKey, $data, DAY_IN_SECONDS * 7 );

		return $data;
	}

	/**
	 * Request the data for the giving URL according to the device.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $url      The URL to fetch the data.
	 * @param  string $strategy Data for desktop or mobile.
	 * @return array            The page speed data.
	 */
	private function request( $url, $device = 'desktop' ) {
		$endpoint = 'sitespeed';
		if ( 'mobile' === $device ) {
			$endpoint = 'sitespeedmobile';
		}

		$api = new Api\Request( "analytics/reports/$endpoint/", [], 'GET' );
		$api->setAdditionalData( [
			'url' => esc_url_raw( urldecode( $url ) )
		] );

		$response = $api->request();

		if ( is_wp_error( $response ) || ! empty( $response['error'] ) || empty( $response['data'] ) ) {
			return [];
		}

		return $response['data'];
	}

	/**
	 * Returns the cache key.
	 *
	 * @since 4.3.0
	 *
	 * @param  mixed  $args The args to create the cache key.
	 * @return string       The cache key.
	 */
	private function getCacheKey( $url, $args = [] ) {
		$key = $this->cachePrefix . '_' . sha1( $url );

		if ( ! empty( $args ) ) {
			$key .= '_' . implode( '_', (array) $args );
		}

		return $key;
	}
}