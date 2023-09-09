<?php
/**
 * =======================================
 * WP Nav Plus
 * =======================================
 *
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

class WP_Nav_Plus_Limit_Offset
{

	public function init()
	{
		add_filter( 'wp_nav_menu_args', array( $this, 'current_nav_args' ), 20, 1 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'filter_nav_menu_items' ), 15, 3 );
	}

	public function current_nav_args( $args )
	{
		global $wp_nav_plus_limit_offset_options;

		$wp_nav_plus_limit_offset_options = array(
			'limit'		=> false,
			'offset'	=> false
		);

		if ( isset( $args['limit'] ) && is_numeric( $args['limit'] ) ) {
			$wp_nav_plus_limit_offset_options['limit'] = $args['limit'];
		}

		if ( isset( $args['offset'] ) && is_numeric( $args['offset'] ) ) {
			$wp_nav_plus_limit_offset_options['offset'] = $args['offset'];
		}

		return $args;
	}

	public function filter_nav_menu_items( $items, $menu, $args )
	{
		global $wp_nav_plus_limit_offset_options;

		if ( false == $wp_nav_plus_limit_offset_options['limit'] && false == $wp_nav_plus_limit_offset_options['offset'] ) {
			return $items;
		}

		$top_level_items = $this->find_top_level_items( $items );
		$filtered_items = $this->remove_orphan_items( $items, $top_level_items );

		return $filtered_items;
	}

	public function find_top_level_items( $items )
	{
		global $wp_nav_plus_limit_offset_options;

		$limit = $wp_nav_plus_limit_offset_options['limit'];
		$offset = ( false != $wp_nav_plus_limit_offset_options['offset'] ) ? $wp_nav_plus_limit_offset_options['offset'] : 0;

		$offset_count = $limit_count = 0;
		$top_level_items = array();

		foreach ( $items as $item )
		{
			if ( 0 != $item->menu_item_parent ) {
				continue;
			}

			if ( $offset_count < $offset ) {
				$offset_count++;
				continue;
			}

			if ( is_numeric( $limit ) && $limit == $limit_count ) {
				break;
			}

			$top_level_items[ $item->ID ] = $item;

			$limit_count++;
		}
		
		return $top_level_items;
	}

	private function remove_orphan_items( &$items, &$top_level_items )
	{
		$keep_looping = false;

		foreach ( $items as $key => $item )
		{
			if ( isset( $top_level_items[ $item->menu_item_parent ] ) ) {
				$top_level_items[ $item->ID ] = $item;
				unset( $items[ $key ] );
				$keep_looping = true;
			}
		}

		if ( $keep_looping ) {
			$this->remove_orphan_items( $items, $top_level_items );
		}

		return $top_level_items;
	}

}

add_action( 'wp', array( new WP_Nav_Plus_Limit_Offset, 'init' ) );
