<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;
use AIOSEO\Plugin\Pro\Models;

/**
 * Handles fetching metadata for the current object.
 *
 * @since 4.0.0
 */
class MetaData extends CommonMeta\MetaData {
	/**
	 * The cached meta data for terms.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	private $terms = [];

	/**
	 * Returns the metadata for the current object.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Term $term The term object (optional).
	 * @return array         The meta data.
	 */
	public function getMetaData( $term = null ) {
		if (
			( ! empty( $term ) && is_a( $term, 'WP_Term' ) ) ||
			is_category() ||
			is_tag() ||
			is_tax() ||
			( is_admin() && aioseo()->helpers->isScreenBase( 'term' ) )
		) {
			$term   = is_a( $term, 'WP_Term' ) ? $term : get_queried_object();
			$termId = ! empty( $term->term_id ) ? $term->term_id : null;
			if ( empty( $termId ) ) {
				return parent::getMetaData( $term );
			}

			if ( isset( $this->terms[ $termId ] ) ) {
				return $this->terms[ $termId ];
			}
			$this->terms[ $termId ] = Models\Term::getTerm( $termId );

			if ( ! $this->terms[ $termId ]->exists() ) {
				$migratedMeta = aioseo()->migration->meta->getMigratedTermMeta( $termId );
				if ( ! empty( $migratedMeta ) ) {
					foreach ( $migratedMeta as $k => $v ) {
						$this->terms[ $termId ]->{$k} = $v;
					}

					$this->terms[ $termId ]->save();
				}
			}

			return $this->terms[ $termId ];
		}

		return parent::getMetaData( $term );
	}

	/**
	 * Busts the meta data cache for a given term.
	 *
	 * @since 4.1.7
	 *
	 * @param  int  $termId   The term ID.
	 * @param  Term $metaData The meta data.
	 * @return void
	 */
	public function bustTermCache( $termId, $metaData = null ) {
		if ( null === $metaData || ! is_a( $metaData, 'AIOSEO\Plugin\Pro\Models\Term' ) ) {
			unset( $this->terms[ $termId ] );
		}
		$this->terms[ $termId ] = $metaData;
	}
}