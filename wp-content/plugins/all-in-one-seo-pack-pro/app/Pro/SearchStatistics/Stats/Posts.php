<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics\Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our post statistics.
 *
 * @since 4.3.0
 */
class Posts {
	/**
	 * Returns the filters for the posts table.
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

	/**
	 * Returns the additional filters for the posts table.
	 *
	 * @since 4.3.0
	 *
	 * @return array The list of additional filters.
	 */
	public function getAdditionalFilters() {
		$postTypes = aioseo()->searchStatistics->helpers->getIncludedPostTypes();
		if ( empty( $postTypes ) ) {
			return [];
		}

		$postTypeOptions = [
			[
				'label' => __( 'All Content Types', 'aioseo-pro' ),
				'value' => ''
			]
		];

		$additionalFilters = [];
		foreach ( $postTypes as $postType ) {
			$postTypeObject = get_post_type_object( $postType );
			if ( ! is_object( $postTypeObject ) ) {
				continue;
			}

			$postTypeOptions[] = [
				'label' => $postTypeObject->labels->singular_name,
				'value' => $postTypeObject->name
			];
		}

		$additionalFilters[] = [
			'name'    => 'postType',
			'options' => $postTypeOptions
		];

		return $additionalFilters;
	}

	/**
	 * Adds post objects to the row data.
	 *
	 * @since 4.3.0
	 *
	 * @param  array  $data The data.
	 * @param  string $type The type of data.
	 * @return array        The data with objects.
	 */
	public function addPostData( $data, $type ) {
		if ( 'statistics' === $type ) {
			$pages      = aioseo()->searchStatistics->helpers->setRowKey( $data['pages']['paginated']['rows'], 'page' );
			$topPages   = aioseo()->searchStatistics->helpers->setRowKey( $data['pages']['topPages']['rows'], 'page' );
			$topWinning = aioseo()->searchStatistics->helpers->setRowKey( $data['pages']['topWinning']['rows'], 'page' );
			$topLosing  = aioseo()->searchStatistics->helpers->setRowKey( $data['pages']['topLosing']['rows'], 'page' );

			$data['pages']['paginated']['rows']  = $this->mergeObjects( $pages );
			$data['pages']['topPages']['rows']   = $this->mergeObjects( $topPages );
			$data['pages']['topWinning']['rows'] = $this->mergeObjects( $topWinning );
			$data['pages']['topLosing']['rows']  = $this->mergeObjects( $topLosing );
		}

		if ( 'keywords' === $type ) {
			$pagesWithObjects = [];
			foreach ( $data as $keyword => $data ) {
				$pagesWithObjects[ $keyword ] = aioseo()->searchStatistics->helpers->setRowKey( $data, 'page' );
				$pagesWithObjects[ $keyword ] = $this->mergeObjects( $pagesWithObjects[ $keyword ] );
			}

			$data = $pagesWithObjects;
		}

		if ( 'contentRankings' === $type ) {
			$pages = aioseo()->searchStatistics->helpers->setRowKey( $data['paginated']['rows'], 'page' );

			$data['paginated']['rows'] = $this->mergeObjects( $pages );
		}

		return $data;
	}

	/**
	 * Returns the objects for the given rows by merging them into the rows.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $rows The rows.
	 * @return array       The modified rows.
	 */
	private function mergeObjects( $rows ) {
		$objects = $this->getObjects( array_keys( $rows ) );

		foreach ( $objects as $page => $object ) {
			if ( ! isset( $rows[ $page ] ) ) {
				$rows[ $page ] = [];
			}

			$rows[ $page ] = array_merge( (array) $rows[ $page ], (array) $object );
		}

		return $rows;
	}

	/**
	 * Adds Pro specific data to the objects.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $pages List of paths.
	 * @return array        The post objects.
	 */
	private function getObjects( $pages ) {
		if ( empty( $pages ) ) {
			return [];
		}

		$query = aioseo()->core->db->start( 'aioseo_search_statistics_objects as asso' )
			->select( 'asso.object_id, asso.object_subtype, asso.object_path, asso.seo_score', 'p.post_title' )
			->join( 'posts as p', 'asso.object_id = p.ID' )
			->whereIn( 'object_path_hash', array_map( 'sha1', array_unique( $pages ) ) );

		$objects = $query->run()
			->result();

		$objects = aioseo()->searchStatistics->helpers->setRowKey( $objects, 'object_path' );

		$newObjects = [];
		foreach ( $pages as $path ) {
			$newObjects[ $path ] = [
				'context'   => [],
				'postId'    => 0,
				'postTitle' => $path,
				'seoScore'  => 0
			];

			if ( empty( $objects[ $path ] ) ) {
				continue;
			}

			$postTitle = ! empty( $objects[ $path ]->post_title ) ? $objects[ $path ]->post_title : __( '(no title)' ); // phpcs:ignore AIOSEO.Wp.I18n.MissingArgDomain
			$postTitle = aioseo()->helpers->decodeHtmlEntities( $postTitle );

			static $postTypeObjects = [];
			if ( empty( $postTypeObjects[ $objects[ $path ]->object_subtype ] ) ) {
				$postTypeObjects[ $objects[ $path ]->object_subtype ] = aioseo()->helpers->getPostType( get_post_type_object( $objects[ $path ]->object_subtype ) );
			}

			$newObjects[ $path ]['postId']    = (int) $objects[ $path ]->object_id;
			$newObjects[ $path ]['postTitle'] = $postTitle;
			$newObjects[ $path ]['seoScore']  = (int) $objects[ $path ]->seo_score;
			$newObjects[ $path ]['context']   = [
				'postType'    => $postTypeObjects[ $objects[ $path ]->object_subtype ],
				'permalink'   => get_permalink( $objects[ $path ]->object_id ),
				'editLink'    => get_edit_post_link( $objects[ $path ]->object_id, '' ),
				'lastUpdated' => get_the_modified_date( get_option( 'date_format' ), $objects[ $path ]->object_id )
			];

			$newObjects[ $path ]['linkAssistant'] = aioseo()->searchStatistics->helpers->getLinkAssistantData( (int) $objects[ $path ]->object_id );
		}

		return $newObjects;
	}

	/**
	 * Returns a list of posts with their slugs, based on a given search term.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $searchTerm The current search term.
	 * @return array              The post data.
	 */
	public function getPostData( $searchTerm = '' ) {
		$searchTerm = esc_sql( aioseo()->core->db->db->esc_like( strtolower( $searchTerm ) ) );
		if ( strlen( $searchTerm ) < 3 ) {
			return [];
		}

		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_post_data_{$searchTerm}" );
		if ( $cachedData ) {
			return $cachedData;
		}

		$postData = aioseo()->db->start( 'aioseo_search_statistics_objects as asso' )
			->select( 'p.ID', 'p.post_title', 'asso.object_path' )
			->join( 'posts as p', 'asso.object_id = p.ID' )
			->where( 'asso.object_type', 'post' )
			->whereRaw(
				"asso.object_path LIKE '%{$searchTerm}%' OR p.post_title LIKE '%{$searchTerm}%'"
			)
			->run()
			->result();

		$postData = aioseo()->searchStatistics->helpers->setRowKey( $postData, 'object_path' );

		aioseo()->core->cache->update( "aioseo_search_statistics_post_data_{$searchTerm}", $postData, 15 * MINUTE_IN_SECONDS );

		return $postData;
	}

	/**
	 * Returns the paths for all post objects.
	 *
	 * @since 4.3.6
	 *
	 * @param  string $postType The post type to get the paths for.
	 * @return array            The list of paths.
	 */
	public function getPostObjectPaths( $postType = '' ) {
		$cachedData = aioseo()->core->cache->get( "aioseo_search_statistics_post_paths_{$postType}" );
		if ( $cachedData ) {
			return $cachedData;
		}

		$displayableObjects = aioseo()->db->start( 'aioseo_search_statistics_objects as asso' )
			->select( 'asso.object_path' )
			->where( 'asso.object_type', 'post' );

		if ( $postType ) {
			$displayableObjects = $displayableObjects->where( 'asso.object_subtype', $postType );
		}

		$displayableObjects = $displayableObjects->run()->result();
		$displayableObjects = wp_list_pluck( $displayableObjects, 'object_path' );

		aioseo()->core->cache->update( "aioseo_search_statistics_post_paths_{$postType}", $displayableObjects, WEEK_IN_SECONDS );

		return $displayableObjects;
	}
}