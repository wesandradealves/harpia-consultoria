<?php
/**
 * =======================================
 * WP Nav Plus
 * =======================================
 *
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

class WP_Nav_Plus_Start_Depth
{

	private $menu_ids_info  	= array();
	private $menu_objects		= array();
	private $custom_menu_items	= array();
	private $current_object;

	public function init()
	{
		add_filter( 'wp_nav_menu_args', array( $this, 'current_nav_args' ), 10, 1 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'filter_nav_menu_items' ), 10, 3 );
		add_filter( 'wp_nav_plus_find_children', array( $this, 'find_children' ), 10, 4 );
	}

	public function current_nav_args( $args )
	{
		global $wp_nav_plus_start_depth_options;

		$wp_nav_plus_start_depth_options = array(
			'start_depth' 		=> false,
			'default_category'	=> false
		);
		
		if ( isset( $args['start_depth'] ) && ( 0 < (int) $args['start_depth'] ) ) {
			$wp_nav_plus_start_depth_options['start_depth'] = (int) $args['start_depth'];

			if ( ! isset( $args['fallback_cb'] ) || 'wp_page_menu' == $args['fallback_cb'] ) {
				$args['fallback_cb'] = 'WP_Nav_Plus_Start_Depth::fallback';
			}
		}

		if ( isset( $args['default_category'] ) && is_numeric( $args['default_category'] ) ) {
			$wp_nav_plus_start_depth_options['default_category'] = (int) $args['default_category'];
		}

		return $args;
	}

	public function filter_nav_menu_items( $items, $menu, $args )
	{
		global $wp_nav_plus_start_depth_options;

		if ( false == $wp_nav_plus_start_depth_options['start_depth'] ) {
			return $items;
		}

		$start_depth = $wp_nav_plus_start_depth_options['start_depth'] - 1;

		$this->current_object = get_queried_object();

		if ( empty( $this->current_object ) ) {
			return array();
		}

		if ( isset( $this->current_object->ID ) ) {
			$object_id = $this->current_object->ID;
		} else if ( isset( $this->current_object->term_id ) ) {
			$object_id = $this->current_object->term_id;
		} else {
			$object_id = $this->find_cpt_archive_page_object_id( $items );
		}

		if ( is_category() ) {
			$parent_object_id 	= isset( $this->current_object->category_parent ) ? $this->current_object->category_parent : -1;
		} else if ( is_tax() ) {
			$parent_object_id 	= isset( $this->current_object->parent ) ? $this->current_object->parent : -1;
		} else {
			$parent_object_id 	= isset( $this->current_object->post_parent ) ? $this->current_object->post_parent : -1;
		}

		if ( $parent_object_id < 0 ) {
			return array();
		}
		
		$object_menu_id	= $this->menu_ids_info( $items, $object_id, $parent_object_id );

		if ( ! $object_menu_id && is_single() ) {
			$category_ids 	= $this->find_category_ids();
			$object_menu_id = $this->find_category_menu_id( $category_ids );
		}

		if ( ! $object_menu_id ) {
			$object_menu_id = $this->find_custom_menu_id();
		}

		if ( empty( $object_menu_id ) ) {
			return array();
		}

		$object_menu_id_array = array( $object_menu_id );

		$ancestors		= $this->find_menu_ancestors( $object_menu_id, $object_menu_id_array );
		$start_menu_id	= isset( $ancestors[ $start_depth ] ) ? $ancestors[ $start_depth ] : false;

		if ( $start_menu_id ) {
			$items = $this->find_children( $items, $start_menu_id );
		} else if ( 0 == $start_depth ) {
			$items = $this->find_children( $items, $object_menu_id );
		} else {
			$items = array();
		}

		return $items;
	}

	private function menu_ids_info( $items, $object_id, $parent_object_id )
	{
		$possible_matches = array();
		$match = false;

		foreach ( $items as $menu_item )
		{
			$this->menu_objects[ $menu_item->object_id ] 				= $menu_item->ID;
			$this->menu_ids_info[ $menu_item->ID ]['object_id'] 		= $menu_item->object_id;
			$this->menu_ids_info[ $menu_item->ID ]['parent_menu_id'] 	= $menu_item->menu_item_parent;

			if ( 'custom' === $menu_item->type || 'post_type_archive' === $menu_item->type ) {
				$custom_url = $this->normalize_url( $menu_item->url );
				$this->custom_menu_items[ $menu_item->ID ] = $custom_url;
			}

			if ( $menu_item->object_id != $object_id ) {
				continue;
			}

			if ( ( is_tax() || is_category() ) && $menu_item->object != $this->current_object->taxonomy ) {
				continue;
			}

			if ( $match ) {
				continue;
			}

			$possible_matches[] = $menu_item->ID;
			$match = $this->check_match( $menu_item, $menu_item->menu_item_parent, $parent_object_id );
		}

		if ( $match ) {
			return $match;
		}

		if ( isset( $possible_matches[0] ) ) {
			return $possible_matches[0];
		}
	}

	public function find_menu_ancestors( $last_menu_id, &$ancestors = array() )
	{
		$keep_looping = false;

		foreach ( $this->menu_ids_info as $menu_id => $info )
		{
			if ( $menu_id == $last_menu_id ) {
				if ( 0 != $info['parent_menu_id'] ) {
					$ancestors[] = $info['parent_menu_id'];
					$last_menu_id = $info['parent_menu_id'];
					$keep_looping = true;
				}
			}
		}

		if ( $keep_looping ) {
			$this->find_menu_ancestors( $last_menu_id, $ancestors );
		}

		return array_reverse( $ancestors );
	}

	private function check_match( $menu_item, $parent_menu_id, $parent_object_id )
	{
		if ( 0 == $parent_menu_id && 0 == $parent_object_id ) {
			return $menu_item->ID;
		}

		if ( isset( $this->menu_ids_info[ $parent_menu_id ]['object_id'] ) ) {
			$associated_parent_object_id = $this->menu_ids_info[ $parent_menu_id ]['object_id'];
		} else {
			$associated_parent_object_id = get_post_meta( $parent_menu_id, '_menu_item_object_id', true );			
		}

		if ( $associated_parent_object_id == $parent_object_id ) {
			return $menu_item->ID;
		}

		if ( is_post_type_archive() && isset( $menu_item->object ) ) {
			$post_type = get_query_var('post_type');
			if ( $menu_item->object == $post_type ) {
				return $menu_item->ID;
			}
		}

		return false;
	}

	public function find_children( $menu_items, $parent_ID = 0, &$children = array(), &$depth = 0 )
	{
		foreach ( $menu_items as $key => $menu_item )
		{
			if ( $menu_item->menu_item_parent == $parent_ID ) {
				
				$menu_item->depth = $depth;

				if ( 0 == $depth ) {
					$menu_item->menu_item_parent = 0;
				}

				array_push( $children, $menu_item );
				unset( $menu_items[ $key ] );
				$oldParent = $parent_ID; 
				$parent_ID = $menu_item->ID;
				$depth++;
				$this->find_children( $menu_items, $parent_ID, $children, $depth );
				$parent_ID = $oldParent;
				$depth--;
			}
		}

		return $children;
	}

	private function find_category_ids()
	{
		global $wp_nav_plus_start_depth_options;

		$possible_matches = array();

		$category_ids = get_the_category();

		if ( ! empty( $category_ids ) ) {
			foreach ( $category_ids as $category ) {
				$possible_matches[] = $category->term_id;
			}
		}

		if ( false != $wp_nav_plus_start_depth_options['default_category'] ) {
			$possible_matches[] = $wp_nav_plus_start_depth_options['default_category'];
		}

		$posts_page_id = get_option('page_for_posts');

		if ( ! empty( $posts_page_id ) ) {
			$possible_matches[] = $posts_page_id;
		}

		return $possible_matches;
	}

	private function find_category_menu_id( $category_ids )
	{
		foreach ( $category_ids as $category_id ) {
			if ( isset( $this->menu_objects[ $category_id ] ) ) {
				return $this->menu_objects[ $category_id ];
			}
		}
	}

	private function find_custom_menu_id()
	{
		global $post;
		$url = false;

		if ( is_post_type_archive() && $url = get_post_type_archive_link( get_query_var('post_type') ) ) :
		elseif ( is_single() && $this->is_custom_post_type( $post ) && $url = get_post_type_archive_link( $post->post_type ) ) :
		elseif ( is_tax() && $url = get_term_link( $this->current_object ) ) :
		else :
			$url = get_permalink();
		endif;

		$url = $this->normalize_url( $url );

		$menu_id = array_search( $url, $this->custom_menu_items );

		return $menu_id;
	}

	private function find_cpt_archive_page_object_id( $items )
	{
		$cpt_archive_url = get_post_type_archive_link( get_query_var('post_type') );

		foreach ( $items as $item ) {
			if ( $item->url == $cpt_archive_url ) {
				$this->current_object->post_parent = $item->post_parent;

				return $item->object_id;
			}
		}
	}

	private function normalize_url( $url )
	{
		$url = str_replace( site_url( '', 'http' ), '', $url );

		$url = str_replace( site_url( '', 'https' ), '', $url );

		if ( '' == $url ) {
			$url = '/';
		}

		if ( '/' != substr( $url, -1 ) ) {
			$url .= '/';
		}

		return $url;
	}

	private function is_custom_post_type( $post )
	{
		$all_custom_post_types = get_post_types( array( '_builtin' => false ) );

		if ( empty ( $all_custom_post_types ) ) {
			return false;
		}

		$custom_types      = array_keys( $all_custom_post_types );
		$current_post_type = get_post_type( $post );

		if ( ! $current_post_type ) {
			return false;
		}

		return in_array( $current_post_type, $custom_types );
	}

	static function fallback()
	{
		return false;
	}

}

add_action( 'wp', array( new WP_Nav_Plus_Start_Depth, 'init' ) );
