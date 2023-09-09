<?php
/**
 * =======================================
 * WP Nav Plus
 * =======================================
 *
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

class WP_Nav_Plus_Menu_Segment
{

	public function init()
	{
		add_filter( 'wp_nav_menu_args', array( $this, 'current_nav_args' ), 5, 1 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'filter_nav_menu_items' ), 5, 3 );
	}

	public function current_nav_args( $args )
	{
		global $wp_nav_plus_menu_segment_options;

		$wp_nav_plus_menu_segment_options = array(
			'segment' => false
		);

		if ( isset( $args['segment'] ) && ! empty( $args['segment'] ) ) {
			$wp_nav_plus_menu_segment_options['segment'] = $args['segment'];

			if ( ! isset( $args['fallback_cb'] ) || 'wp_page_menu' == $args['fallback_cb'] ) {
				$args['fallback_cb'] = 'WP_Nav_Plus_Start_Depth::fallback';
			}
		}

		return $args;
	}

	public function filter_nav_menu_items( $items, $menu, $args )
	{
		global $wp_nav_plus_menu_segment_options;

		if ( false == $wp_nav_plus_menu_segment_options['segment'] ) {
			return $items;
		} else {
			$segment = $wp_nav_plus_menu_segment_options['segment'];
		}
		
		$object_menu_id	= $this->find_menu_id( $items, $segment );

		if ( empty( $object_menu_id ) ) {
			return array();
		}

		$items = apply_filters( 'wp_nav_plus_find_children', $items, $object_menu_id );

		return $items;
	}

	private function find_menu_id( $items, $segment )
	{
		if ( is_numeric( $segment ) ) {
			$search_key = 'object_id';
		} else {
			$search_key = 'title';
			$segment = strtolower( $segment );
		}

		foreach ( $items as $item )
		{
			if ( $segment == strtolower( $item->$search_key ) ) {
				return $item->ID;
			}
		}

		return false;
	}

}

add_action( 'wp', array( new WP_Nav_Plus_Menu_Segment, 'init' ) );
