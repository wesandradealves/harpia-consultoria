<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;

/**
 * Handles the robots meta tag.
 *
 * @since 4.0.0
 */
class Robots extends CommonMeta\Robots {
	/**
	 * Returns the robots meta tag value.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed The robots meta tag value or false.
	 */
	public function meta() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return parent::meta();
		}

		$this->term();

		return parent::metaHelper();
	}

	/**
	 * Returns the robots meta tag value for the current term.
	 *
	 * @since 4.1.7
	 *
	 * @param  \WP_Term|null $term The term object if any.
	 * @return void
	 */
	public function term( $term = null ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		$term           = is_a( $term, 'WP_Term' ) ? $term : get_queried_object();
		$metaData       = aioseo()->meta->metaData->getMetaData( $term );

		if ( ! empty( $metaData ) && ! $metaData->robots_default ) {
			$this->metaValues( $metaData );

			return;
		}

		if ( ! empty( $term->term_id ) && $dynamicOptions->searchAppearance->taxonomies->has( $term->taxonomy ) ) {
			$this->globalValues( [ 'taxonomies', $term->taxonomy ], true );

			return;
		}

		$this->globalValues();
	}
}