<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\SearchStatistics\Api;

/**
 * The main statistics class.
 *
 * @since 4.3.0
 */
class Stats {
	/**
	 * The start date.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public $startDate = '';

	/**
	 * The end date.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public $endDate = '';

	/**
	 * The number of days to compare.
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	public $days = 0;

	/**
	 * The start date timestamp.
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	public $startTimestamp = '';

	/**
	 * The end date timestamp.
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	public $endTimestamp = '';

	/**
	 * The comparison start date.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public $compareStartDate = '';

	/**
	 * The comparison end date.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public $compareEndDate = '';

	/**
	 * Holds the instance of the Posts class.
	 *
	 * @since 4.3.0
	 *
	 * @var Posts
	 */
	public $posts = null;

	/**
	 * Holds the instance of the Keywords class.
	 *
	 * @since 4.3.0
	 *
	 * @var Keywords
	 */
	public $keywords = null;

	/**
	 * The latest available date Google has data for.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	public $latestAvailableDate = '';

	/**
	 * Whether or not the site is unverified.
	 *
	 * @since 4.3.0
	 *
	 * @var bool
	 */
	public $unverifiedSite = false;

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->posts    = new Posts();
		$this->keywords = new Keywords();

		add_action( 'admin_init', [ $this, 'setLatestAvailableDate' ], 10 );
		add_action( 'admin_init', [ $this, 'setDefaultDateRange' ], 11 );
	}

	/**
	 * Sets the latest available date.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function setLatestAvailableDate() {
		$authedSite = aioseo()->searchStatistics->api->auth->getAuthedSite();
		if ( ! $authedSite ) {
			$this->latestAvailableDate = date( 'Y-m-d', strtotime( '-2 days' ) );

			return;
		}

		$hash                = sha1( $authedSite );
		$cacheKey            = "aioseo_search_statistics_latest_date_{$hash}";
		$latestAvailableDate = aioseo()->core->cache->get( $cacheKey );
		if ( $latestAvailableDate ) {
			$this->latestAvailableDate = $latestAvailableDate;

			return;
		}

		try {
			$api      = new Api\Request( 'google-search-console/newest-date/', [], 'GET' );
			$response = $api->request();
			if ( is_wp_error( $response ) || ! empty( $response['error'] ) || empty( $response['data']['date'] ) ) {
				if (
					is_array( $response ) &&
					! empty( $response['data']['message'] ) &&
					preg_match( '/403 Forbidden/i', $response['data']['message'] )
				) {
					$this->unverifiedSite      = true;
					$this->latestAvailableDate = date( 'Y-m-d', strtotime( '-2 days' ) );

					return;
				}

				$this->latestAvailableDate = date( 'Y-m-d', strtotime( '-2 days' ) );
				aioseo()->core->cache->update( $cacheKey, $this->latestAvailableDate, 2 * HOUR_IN_SECONDS );

				return;
			}
		} catch ( \Exception $e ) {
			$this->latestAvailableDate = date( 'Y-m-d', strtotime( '-2 days' ) );
			aioseo()->core->cache->update( $cacheKey, $this->latestAvailableDate, 2 * HOUR_IN_SECONDS );

			return;
		}

		$this->latestAvailableDate = $response['data']['date'];
		aioseo()->core->cache->update( $cacheKey, $this->latestAvailableDate, 6 * HOUR_IN_SECONDS );
	}

	/**
	 * Sets the default date range.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function setDefaultDateRange() {
		$baseTimestamp = strtotime( 'today' );
		if ( ! empty( $this->latestAvailableDate ) ) {
			$baseTimestamp = strtotime( $this->latestAvailableDate );
		}

		// Set the default start and end date to the last 28 days.
		$startDate = date( 'Y-m-d', strtotime( '-28 days', $baseTimestamp ) );
		$endDate   = date( 'Y-m-d', $baseTimestamp );

		$rolling = aioseo()->internalOptions->searchStatistics->rolling;
		if ( aioseo()->internalOptions->searchStatistics->rolling ) {
			switch ( $rolling ) {
				case 'last7Days':
					$startDate = date( 'Y-m-d', strtotime( '-7 days', $baseTimestamp ) );
					$endDate   = date( 'Y-m-d', $baseTimestamp );
					break;
				case 'last28Days':
					$startDate = date( 'Y-m-d', strtotime( '-28 days', $baseTimestamp ) );
					$endDate   = date( 'Y-m-d', $baseTimestamp );
					break;
				case 'last3Months':
					$startDate = date( 'Y-m-d', strtotime( '-90 days', $baseTimestamp ) );
					$endDate   = date( 'Y-m-d', $baseTimestamp );
					break;
				case 'last6Months':
					$startDate = date( 'Y-m-d', strtotime( '-180 days', $baseTimestamp ) );
					$endDate   = date( 'Y-m-d', $baseTimestamp );
					break;
				default:
					break;
			}
		}

		$this->setDateRange( $startDate, $endDate );
	}

	/**
	 * Updates the date range.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $startDate The start date.
	 * @param  string $endDate   The end date.
	 * @return void
	 */
	public function setDateRange( $startDate, $endDate ) {
		$this->startDate = $startDate;
		$this->endDate   = $endDate;

		// Timestamp.
		$this->startTimestamp = strtotime( $startDate );
		$this->endTimestamp   = strtotime( $endDate );

		// Compare date.
		$this->days       = ceil( abs( $this->endTimestamp - $this->startTimestamp ) / DAY_IN_SECONDS ) + 1;
		$compareEndDate   = $this->startTimestamp - DAY_IN_SECONDS;
		$compareStartDate = $compareEndDate - ( $this->days * DAY_IN_SECONDS );

		$this->compareStartDate = date( 'Y-m-d', $compareStartDate );
		$this->compareEndDate   = date( 'Y-m-d', $compareEndDate );
	}

	/**
	 * Returns the current date range.
	 *
	 * @since 4.3.0
	 *
	 * @return array The current date range.
	 */
	public function getDateRange() {
		return [
			'start'        => $this->startDate,
			'end'          => $this->endDate,
			'compareStart' => $this->compareStartDate,
			'compareEnd'   => $this->compareEndDate
		];
	}
}