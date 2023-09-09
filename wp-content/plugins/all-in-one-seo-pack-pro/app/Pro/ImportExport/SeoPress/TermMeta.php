<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the term meta from SEOPress.
 *
 * @since 4.1.4
 */
class TermMeta {
	/**
	 * Schedules the term meta import.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function scheduleImport() {
		if ( aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->seoPress->termActionName, 0 ) ) {
			if ( ! aioseo()->core->cache->get( 'import_term_meta_seopress' ) ) {
				aioseo()->core->cache->update( 'import_term_meta_seopress', time(), WEEK_IN_SECONDS );
			}
		}
	}

	/**
	 * Imports the term meta.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function importTermMeta() {
		$termsPerAction   = 100;
		$publicTaxonomies = implode( "', '", aioseo()->helpers->getpublicTaxonomies( true ) );
		$timeStarted      = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_term_meta_seopress' ) );

		$terms = aioseo()->core->db
			->start( 'terms as t' )
			->select( 't.term_id' )
			->join( 'termmeta as tm', '`t`.`term_id` = `tm`.`term_id`' )
			->join( 'term_taxonomy as tt', '`t`.`term_id` = `tt`.`term_id`' )
			->leftJoin( 'aioseo_terms as at', '`t`.`term_id` = `at`.`term_id`' )
			->whereRaw( "tm.meta_key LIKE '_seopress_%'" )
			->whereRaw( "( tt.taxonomy IN ( '$publicTaxonomies' ) )" )
			->whereRaw( "( at.term_id IS NULL OR at.updated < '$timeStarted' )" )
			->orderBy( 't.term_id DESC' )
			->limit( $termsPerAction )
			->run()
			->result();

		if ( ! $terms || ! count( $terms ) ) {
			aioseo()->core->cache->delete( 'import_term_meta_seopress' );

			return;
		}

		$mappedMeta = [
			'_seopress_robots_archive'       => 'robots_noarchive',
			'_seopress_robots_canonical'     => 'canonical_url',
			'_seopress_robots_imageindex'    => 'robots_noimageindex',
			'_seopress_robots_odp'           => 'robots_noodp',
			'_seopress_robots_snippet'       => 'robots_nosnippet',
			'_seopress_social_fb_desc'       => 'og_description',
			'_seopress_social_fb_img'        => 'og_image_custom_url',
			'_seopress_social_fb_title'      => 'og_title',
			'_seopress_social_twitter_desc'  => 'twitter_description',
			'_seopress_social_twitter_img'   => 'twitter_image_custom_url',
			'_seopress_social_twitter_title' => 'twitter_title',
			'_seopress_titles_desc'          => 'description',
			'_seopress_titles_title'         => 'title',
		];

		foreach ( $terms as $term ) {
			$termMeta = aioseo()->core->db
			->start( 'termmeta as tm' )
			->select( 'tm.meta_key, tm.meta_value' )
			->where( 'tm.term_id', $term->term_id )
			->whereRaw( "`tm`.`meta_key` LIKE '_seopress_%'" )
			->run()
			->result();

			if ( ! $termMeta || ! count( $termMeta ) ) {
				continue;
			}

			$meta = [
				'term_id' => $term->term_id,
			];

			foreach ( $termMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
					continue;
				}

				switch ( $name ) {
					case '_seopress_robots_odp':
					case '_seopress_robots_imageindex':
					case '_seopress_robots_archive':
					case '_seopress_robots_snippet':
					case '_seopress_robots_canonical':
						if ( 'yes' === $value ) {
							$meta['robots_default']       = false;
							$meta[ $mappedMeta[ $name ] ] = true;
						}
						break;
					case '_seopress_social_fb_img':
						$meta['og_image_type']        = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_seopress_social_twitter_img':
						$meta['twitter_image_type']   = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_seopress_titles_title':
					case '_seopress_titles_desc':
						$value = aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $value, 'term' );
					default:
						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}

			$aioseoterm = Models\Term::getTerm( $term->term_id );
			$aioseoterm->set( $meta );
			$aioseoterm->save();
		}

		if ( count( $terms ) === $termsPerAction ) {
			aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->seoPress->termActionName, 5, [], true );
		} else {
			aioseo()->core->cache->delete( 'import_term_meta_seopress' );
		}
	}
}