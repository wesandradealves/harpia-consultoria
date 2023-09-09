<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the term meta from Yoast SEO.
 *
 * @since 4.0.0
 */
class TermMeta {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function scheduleImport() {
		try {
			if ( as_next_scheduled_action( aioseo()->importExport->yoastSeo->termActionName ) ) {
				return;
			}

			if ( ! aioseo()->core->cache->get( 'import_term_meta_yoast_seo' ) ) {
				aioseo()->core->cache->update( 'import_term_meta_yoast_seo', time(), WEEK_IN_SECONDS );
			}

			as_schedule_single_action( time(), aioseo()->importExport->yoastSeo->termActionName, [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Imports the term meta.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function importTermMeta() {
		if ( ! aioseo()->core->db->tableExists( 'yoast_indexable' ) ) {
			aioseo()->core->cache->delete( 'import_term_meta_yoast_seo' );

			return;
		}

		$termsPerAction   = 100;
		$publicTaxonomies = implode( "', '", aioseo()->helpers->getPublicTaxonomies( true ) );
		$timeStarted      = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_term_meta_yoast_seo' ) );

		$terms = aioseo()->core->db
			->start( 'yoast_indexable' . ' as yi' )
			->select( 'yi.*' )
			->leftJoin( 'aioseo_terms as at', '`yi`.`object_id` = `at`.`term_id`' )
			->where( 'yi.object_type', 'term' )
			->whereRaw( "( yi.object_sub_type IN ( '$publicTaxonomies' ) )" )
			->whereRaw( "( at.term_id IS NULL OR at.updated < '$timeStarted' )" )
			->orderBy( 'yi.object_id DESC' )
			->limit( $termsPerAction )
			->run()
			->result();

		if ( ! $terms || ! count( $terms ) ) {
			aioseo()->core->cache->delete( 'import_term_meta_yoast_seo' );

			return;
		}

		$mappedMeta = [
			'title'                   => 'title',
			'description'             => 'description',
			'canonical'               => 'canonical_url',
			'is_robots_noindex'       => 'robots_noindex',
			'is_robots_nofollow'      => 'robots_nofollow',
			'is_robots_noarchive'     => 'robots_noarchive',
			'is_robots_noimageindex'  => 'robots_noimageindex',
			'is_robots_nosnippet'     => 'robots_nosnippet',
			'open_graph_title'        => 'og_title',
			'open_graph_description'  => 'og_description',
			'open_graph_image'        => 'og_image_custom_url',
			'open_graph_image_source' => 'og_image_type',
			'open_graph_image_meta'   => '',
			'twitter_title'           => 'twitter_title',
			'twitter_description'     => 'twitter_description',
			'twitter_image'           => 'twitter_image_custom_url',
			'twitter_image_source'    => 'twitter_image_type',
		];

		foreach ( $terms as $term ) {
			$meta = [
				'term_id' => (int) $term->object_id,
			];

			foreach ( $mappedMeta as $name => $mapping ) {
				if ( empty( $term->$name ) ) {
					continue;
				}

				$value = $term->$name;
				switch ( $name ) {
					case 'is_robots_noindex':
					case 'is_robots_nofollow':
					case 'is_robots_noarchive':
					case 'is_robots_noimageindex':
					case 'is_robots_nosnippet':
						if ( (bool) $value ) {
							$meta[ $mapping ]       = true;
							$meta['robots_default'] = false;
						}
						break;
					case 'open_graph_image':
						$meta['og_image_type'] = 'custom_image';
						$meta[ $mapping ]      = esc_url( $value );
						break;
					case 'twitter_image':
						$meta['twitter_use_og']     = false;
						$meta['twitter_image_type'] = 'custom_image';
						$meta[ $mapping ]           = esc_url( $value );
						break;
					case 'open_graph_image_meta':
						$imageMeta = json_decode( $value );
						if ( ! empty( $imageMeta->width ) && intval( $imageMeta->width ) ) {
							$meta['og_image_width'] = intval( $imageMeta->width );
						}
						if ( ! empty( $imageMeta->height ) && intval( $imageMeta->height ) ) {
							$meta['og_image_height'] = intval( $imageMeta->height );
						}
						break;
					case 'title':
					case 'description':
					case 'open_graph_title':
					case 'open_graph_description':
						$value = aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, null, 'term' );
					default:
						$meta[ $mapping ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}

			$aioseoTerm = Models\Term::getTerm( (int) $term->object_id );
			$aioseoTerm->set( $meta );
			$aioseoTerm->save();
		}

		if ( count( $terms ) === $termsPerAction ) {
			try {
				as_schedule_single_action( time() + 5, aioseo()->importExport->yoastSeo->termActionName, [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'import_term_meta_yoast_seo' );
		}
	}
}