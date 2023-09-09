<?php
namespace AIOSEO\Plugin\Pro\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Standalone\PrimaryTerm as CommonPrimaryTerm;
use AIOSEO\Plugin\Common\Models as CommonModels;

/**
 * Handles the Primary Term feature.
 *
 * @since 4.3.6
 */
class PrimaryTerm extends CommonPrimaryTerm {
	/**
	 * Returns the primary post term for the given taxonomy name.
	 *
	 * @since 4.3.6
	 *
	 * @param  int            $postId       The post ID.
	 * @param  string         $taxonomyName The taxonomy name.
	 * @return \WP_Term|false               The term or false.
	 */
	public function getPrimaryTerm( $postId, $taxonomyName ) {
		$aioseoPost   = CommonModels\Post::getPost( $postId );
		$primaryTerms = ! empty( $aioseoPost->primary_term ) ? $aioseoPost->primary_term : false;

		if ( ! $primaryTerms || empty( $primaryTerms->{$taxonomyName} ) ) {
			return apply_filters( 'aioseo_post_primary_term', false, $taxonomyName );
		}

		$term = get_term( $primaryTerms->{$taxonomyName}, $taxonomyName );
		if ( is_wp_error( $term ) ) {
			return apply_filters( 'aioseo_post_primary_term', false, $taxonomyName );
		}

		return apply_filters( 'aioseo_post_primary_term', $term, $taxonomyName );
	}
}