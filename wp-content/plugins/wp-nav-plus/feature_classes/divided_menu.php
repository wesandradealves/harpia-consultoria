<?php
/**
 * =======================================
 * WP Nav Plus
 * =======================================
 *
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

class WP_Nav_Plus_Divided_Menu
{

	public function init()
	{
		add_filter( 'wp_nav_menu_args', array( $this, 'current_nav_args' ), 15, 1 );
		add_filter( 'wp_nav_menu_items', array( $this, 'filter_wp_nav_menu_items' ), 10, 2 );
	}

	public function current_nav_args( $args )
	{
		global $wp_nav_plus_divided_menu_options;

		$wp_nav_plus_divided_menu_options = array(
			'divider_html'		=> false,
			'divider_container'	=> 'li',
			'divider_class'		=> 'menu-divider-item',
			'divider_id'		=> false,
			'divider_offset'	=> 0
		);

		if ( isset( $args['divider_html'] ) && is_string( $args['divider_html'] ) ) {
			$wp_nav_plus_divided_menu_options['divider_html'] = $args['divider_html'];
		}

		if ( isset( $args['divider_container'] ) && is_string( $args['divider_container'] ) ) {
			$wp_nav_plus_divided_menu_options['divider_container'] = $args['divider_container'];
		}

		if ( isset( $args['divider_class'] ) && is_string( $args['divider_class'] ) ) {
			$wp_nav_plus_divided_menu_options['divider_class'] = $args['divider_class'];
		}

		if ( isset( $args['divider_id'] ) && is_string( $args['divider_id'] ) ) {
			$wp_nav_plus_divided_menu_options['divider_id'] = $args['divider_id'];
		}

		if ( isset( $args['divider_offset'] ) && is_numeric( $args['divider_offset'] ) ) {
			$wp_nav_plus_divided_menu_options['divider_offset'] = $args['divider_offset'];
		}

		return $args;
	}

	public function filter_wp_nav_menu_items( $items, $args )
	{
		global $wp_nav_plus_divided_menu_options;

		if ( ! $wp_nav_plus_divided_menu_options['divider_html'] ) {
			return $items;
		}

		$document = new DomDocument();
		$document->encoding = 'utf-8';
		$document->loadHTML( mb_convert_encoding( $items, 'HTML-ENTITIES', 'UTF-8') );
		$xpath = new DOMXPath( $document );

		// Calculate 'middle' of menu, then apply offset if applicable
		$menu_children = $xpath->query('/html/body/*');
		$divider_point = floor( $menu_children->length / 2 ) + 1 + $wp_nav_plus_divided_menu_options['divider_offset'];
		$divider_parent = $xpath->query( '/html/body' );
		$divider_child = $xpath->query( '/html/body/li[position()=' . $divider_point . ']' );

		// Create divider element and attributes
		$divider_html = $document->createElement( $wp_nav_plus_divided_menu_options['divider_container'], $wp_nav_plus_divided_menu_options['divider_html'] ); 
		$divider_html->setAttribute( 'class', $wp_nav_plus_divided_menu_options['divider_class'] );
		if ( $wp_nav_plus_divided_menu_options['divider_id'] ) {
			$divider_html->setAttribute( 'id', $wp_nav_plus_divided_menu_options['divider_id'] ); 
		}

		// Insert divider element into menu structure
		$divider_parent->item(0)->insertbefore( $divider_html, $divider_child->item(0) ); 

		// Clean DTD/HTML/Body tags the > php 5.3 friendly way
		$items = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $document->saveHTML() );

		return html_entity_decode( $items, ENT_QUOTES, 'UTF-8' );
	}

}

add_action( 'wp', array( new WP_Nav_Plus_Divided_Menu, 'init' ) );
