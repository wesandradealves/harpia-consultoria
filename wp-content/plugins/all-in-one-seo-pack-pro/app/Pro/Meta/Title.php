<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;

/**
 * Handles the page title.
 *
 * @since 4.0.0
 */
class Title extends CommonMeta\Title {
	/**
	 * Returns the term title.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Term $term    The term object.
	 * @param  boolean $default Whether we want the default value, not the post one.
	 * @return string           The term title.
	 */
	public function getTermTitle( $term, $default = false ) {
		if ( ! is_a( $term, 'WP_Term' ) ) {
			return '';
		}

		static $terms = [];
		if ( isset( $terms[ $term->term_id ] ) ) {
			return $terms[ $term->term_id ];
		}

		$title    = '';
		$metaData = aioseo()->meta->metaData->getMetaData( $term );
		if ( ! empty( $metaData->title ) && ! $default ) {
			$title = $metaData->title;
			// Since we might be faking the term, let's replace the title ourselves.
			$title = aioseo()->helpers->pregReplace( '/#taxonomy_title/', $term->name, $title );
			$title = $this->helpers->prepare( $title, $term->term_id );
		}

		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		if ( ! $title && $dynamicOptions->searchAppearance->taxonomies->has( $term->taxonomy ) ) {
			$newTitle = aioseo()->dynamicOptions->searchAppearance->taxonomies->{$term->taxonomy}->title;
			$newTitle = preg_replace( '/#taxonomy_title/', $term->name, $newTitle );
			$title    = $this->helpers->prepare( $newTitle, $term->term_id, $default );
		}

		$terms[ $term->term_id ] = $title;

		return $terms[ $term->term_id ];
	}

	/**
	 * Retrieve the default title for the taxonomy.
	 *
	 * @since 4.0.17
	 *
	 * @param  string $taxonomy The taxonomy name.
	 * @return string           The title.
	 */
	public function getTaxonomyTitle( $taxonomy ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		return $dynamicOptions->searchAppearance->taxonomies->has( $taxonomy, false ) ?
			$dynamicOptions->{$taxonomy}->title :
			'';
	}
}