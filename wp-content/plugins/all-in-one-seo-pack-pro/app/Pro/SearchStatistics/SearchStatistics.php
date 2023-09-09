<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\SearchStatistics as CommonSearchStatistics;

/**
 * Class that holds our Search Statistics feature.
 *
 * @since 4.3.0
 */
class SearchStatistics extends CommonSearchStatistics\SearchStatistics {
	/**
	 * Holds the instance of the API class.
	 *
	 * @since 4.3.0
	 *
	 * @var Api\Api
	 */
	public $api = null;

	/**
	 * Holds the instance of the Stats class.
	 *
	 * @since 4.3.0
	 *
	 * @var Stats\Stats
	 */
	public $stats = null;

	/**
	 * Holds the instance of the Helpers class.
	 *
	 * @since 4.3.0
	 *
	 * @var Helpers
	 */
	public $helpers = null;

	/**
	 * Holds the instance of the Objects class.
	 *
	 * @since 4.3.3
	 *
	 * @var Objects
	 */
	public $objects = null;

	/**
	 * Holds the instance of the PageSpeed class.
	 *
	 * @since 4.3.0
	 *
	 * @var PageSpeed
	 */
	public $pageSpeed;

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->api       = new Api\Api();
		$this->stats     = new Stats\Stats();
		$this->helpers   = new Helpers();
		$this->pageSpeed = new PageSpeed();
		$this->objects   = new Objects();
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.3.0
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		$dateRange = aioseo()->searchStatistics->stats->getDateRange();

		$data = [
			'isConnected'         => aioseo()->searchStatistics->api->auth->isConnected(),
			'latestAvailableDate' => aioseo()->searchStatistics->stats->latestAvailableDate,
			'unverifiedSite'      => aioseo()->searchStatistics->stats->unverifiedSite,
			'range'               => $dateRange,
			'rolling'             => aioseo()->internalOptions->internal->searchStatistics->rolling,
			'authedSite'          => aioseo()->searchStatistics->api->auth->getAuthedSite(),
			'data'                => [
				'seoStatistics'   => $this->getSeoOverviewData( $dateRange ),
				'keywords'        => $this->getKeywordsData( $dateRange ),
				'contentRankings' => $this->getContentRankingsData( $dateRange )
			]
		];

		return $data;
	}

	/**
	 * Returns the SEO Overview data.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The SEO Overview data.
	 */
	protected function getSeoOverviewData( $dateRange = [] ) {
		if (
			! aioseo()->license->hasCoreFeature( 'search-statistics', 'seo-statistics' ) ||
			! aioseo()->searchStatistics->api->auth->isConnected()
		) {
			return parent::getSeoOverviewData( $dateRange );
		}

		$cacheArgs = [
			aioseo()->searchStatistics->api->auth->getAuthedSite(),
			$dateRange['start'],
			$dateRange['end'],
			aioseo()->settings->tablePagination['searchStatisticsSeoStatistics'],
			'0',
			'all',
			'',
			'DESC',
			'clicks',
			''
		];

		$cacheHash  = sha1( implode( ',', $cacheArgs ) );
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_seo_statistics_{$cacheHash}" );
		if ( $cachedData ) {
			if ( ! empty( $cachedData['pages']['paginated']['rows'] ) ) {
				$cachedData = aioseo()->searchStatistics->stats->posts->addPostData( $cachedData, 'statistics' );

				$cachedData['pages']['paginated']['filters']           = aioseo()->searchStatistics->stats->posts->getFilters( 'all', '' );
				$cachedData['pages']['paginated']['additionalFilters'] = aioseo()->searchStatistics->stats->posts->getAdditionalFilters();
			}

			return $cachedData;
		}

		return [];
	}

	/**
	 * Returns the Keywords data.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The Keywords data.
	 */
	protected function getKeywordsData( $dateRange = [] ) {
		if (
			! aioseo()->license->hasCoreFeature( 'search-statistics', 'keyword-rankings' ) ||
			! aioseo()->searchStatistics->api->auth->isConnected()
		) {
			return parent::getKeywordsData( $dateRange );
		}

		$cacheArgs = [
			aioseo()->searchStatistics->api->auth->getAuthedSite(),
			$dateRange['start'],
			$dateRange['end'],
			aioseo()->settings->tablePagination['searchStatisticsKeywordRankings'],
			'0',
			'all',
			'',
			'DESC',
			'clicks'
		];

		$cacheHash  = sha1( implode( ',', $cacheArgs ) );
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_keywords_{$cacheHash}" );
		if ( $cachedData ) {
			return $cachedData;
		}

		return [];
	}

	/**
	 * Returns the Content Rankings data.
	 *
	 * @since 4.3.6
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The Content Rankings data.
	 */
	protected function getContentRankingsData( $dateRange = [] ) {
		if (
			! aioseo()->license->hasCoreFeature( 'search-statistics', 'content-rankings' ) ||
			! aioseo()->searchStatistics->api->auth->isConnected()
		) {
			return parent::getContentRankingsData( $dateRange );
		}

		$endDate    = aioseo()->searchStatistics->stats->latestAvailableDate; // We do last available date for the end date.
		$startDate  = date( 'Y-m-d', strtotime( $endDate . ' - 1 year' ) );

		$cacheArgs = [
			aioseo()->searchStatistics->api->auth->getAuthedSite(),
			$startDate,
			$endDate,
			aioseo()->settings->tablePagination['searchStatisticsContentRankings'],
			0,
			'',
			'',
			'ASC',
			'decay'
		];

		$cacheHash  = sha1( implode( ',', $cacheArgs ) );
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_cont_rankings_{$cacheHash}" );
		if ( $cachedData ) {
			if ( ! empty( $cachedData['paginated']['rows'] ) ) {
				$cachedData = aioseo()->searchStatistics->stats->posts->addPostData( $cachedData, 'contentRankings' );

				$cachedData['paginated']['additionalFilters'] = aioseo()->searchStatistics->stats->posts->getAdditionalFilters();
			}

			return $cachedData;
		}

		return [];
	}

	/**
	 * Cancels all scheduled Search Statistics related actions.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function cancelActions() {
		$actions = [
			'aioseo_search_statistics_objects_scan'
		];

		foreach ( $actions as $actionName ) {
			as_unschedule_all_actions( $actionName );
		}
	}
}