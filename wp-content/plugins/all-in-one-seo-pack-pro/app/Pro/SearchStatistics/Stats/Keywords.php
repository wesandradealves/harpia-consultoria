<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our keyword statistics.
 *
 * @since 4.3.0
 */
class Keywords {
	/**
	 * Returns the filters for the keywords table.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $filter     The current filter.
	 * @param  string $searchTerm The current search term.
	 * @return array              The list of filters.
	 */
	public function getFilters( $filter, $searchTerm ) {
		return [
			[
				'slug'   => 'all',
				'name'   => __( 'All', 'aioseo-pro' ),
				'active' => ( ! $filter || 'all' === $filter ) && ! $searchTerm
			],
			[
				'slug'   => 'topLosing',
				'name'   => __( 'Top Losing', 'aioseo-pro' ),
				'active' => 'topLosing' === $filter
			],
			[
				'slug'   => 'topWinning',
				'name'   => __( 'Top Winning', 'aioseo-pro' ),
				'active' => 'topWinning' === $filter
			]
		];
	}
}