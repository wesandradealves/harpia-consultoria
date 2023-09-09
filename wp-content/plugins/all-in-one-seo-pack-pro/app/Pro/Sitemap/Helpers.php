<?php
namespace AIOSEO\Plugin\Pro\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Sitemap as CommonSitemap;

/**
 * Contains general helper methods specific to the sitemap.
 *
 * @since 4.0.0
 */
class Helpers extends CommonSitemap\Helpers {

	/**
	 * Returns the taxonomies that should be included in the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return array The included taxonomies.
	 */
	public function includedTaxonomies() {
		$taxonomies = [];
		if ( aioseo()->options->sitemap->{aioseo()->sitemap->type}->taxonomies->all ) {
			$taxonomies = get_taxonomies();
		} else {
			$taxonomies = aioseo()->options->sitemap->{aioseo()->sitemap->type}->taxonomies->included;
		}

		if ( ! $taxonomies ) {
			return [];
		}

		$options          = aioseo()->options->noConflict();
		$dynamicOptions   = aioseo()->dynamicOptions->noConflict();
		$publicTaxonomies = aioseo()->helpers->getPublicTaxonomies( true );
		foreach ( $taxonomies as $taxonomy ) {
			// Check if taxonomy is no longer registered.
			if ( ! in_array( $taxonomy, $publicTaxonomies, true ) || ! $dynamicOptions->searchAppearance->taxonomies->has( $taxonomy ) ) {
				$taxonomies = aioseo()->helpers->unsetValue( $taxonomies, $taxonomy );
				continue;
			}

			// Check if taxonomy isn't noindexed.
			if ( aioseo()->helpers->isTaxonomyNoindexed( $taxonomy ) ) {
				if ( ! $this->checkForIndexedTerm( $taxonomy ) ) {
					$taxonomies = aioseo()->helpers->unsetValue( $taxonomies, $taxonomy );
					continue;
				}
			}

			if (
				$dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default &&
				! $options->searchAppearance->advanced->globalRobotsMeta->default &&
				$options->searchAppearance->advanced->globalRobotsMeta->noindex
			) {
				if ( ! $this->checkForIndexedTerm( $taxonomy ) ) {
					$taxonomies = aioseo()->helpers->unsetValue( $taxonomies, $taxonomy );
					continue;
				}
			}
		}

		return $taxonomies;
	}

	/**
	 * Checks if any term is explicitly indexed when the taxonomy is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy The taxonomy to check for.
	 * @return bool             Whether or not there is an indexed term.
	 */
	private function checkForIndexedTerm( $taxonomy ) {
		$terms = aioseo()->core->db
			->start( aioseo()->core->db->db->term_taxonomy . ' as tt', true )
			->select( 'tt.term_id' )
			->join( 'aioseo_terms as at', '`tt`.`term_id` = `at`.`term_id`' )
			->where( 'tt.taxonomy', $taxonomy )
			->whereRaw( '( `at`.`robots_default` = 0 AND `at`.`robots_noindex` = 0 )' )
			->limit( 1 )
			->run()
			->result();

		if ( $terms && count( $terms ) ) {
			return true;
		}

		return false;
	}
}