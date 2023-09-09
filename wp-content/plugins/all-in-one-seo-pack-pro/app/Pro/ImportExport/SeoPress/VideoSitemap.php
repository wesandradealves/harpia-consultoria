<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Breadcrumb settings.
 *
 * @since 4.1.4
 */
class VideoSitemap {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->migrateExclusions();
	}

	/**
	 * Migrates the posts that are excluded from the News Sitemap.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateExclusions() {
		$excludedPosts   = aioseo()->options->sitemap->video->advancedSettings->excludePosts;
		$toExclude       = aioseo()->core->db
			->start( 'postmeta as pm' )
			->select( 'pm.post_id' )
			->whereRaw( "`pm`.`meta_key` = '_seopress_video_disabled'" )
			->whereRaw( "`pm`.`meta_value` = 'yes'" )
			->run()
			->result();

		if ( count( $toExclude ) ) {
			foreach ( $toExclude as $record ) {
				$post = aioseo()->helpers->getPost( $record->post_id );
				if ( ! is_object( $post ) ) {
					continue;
				}

				$excludedPost        = new \stdClass();
				$excludedPost->value = $post->ID;
				$excludedPost->type  = $post->post_type;
				$excludedPost->label = $post->post_title;
				$excludedPost->link  = get_permalink( $post->ID );

				array_push( $excludedPosts, wp_json_encode( $excludedPost ) );
			}
			aioseo()->options->sitemap->video->advancedSettings->enable = true;
		}
		aioseo()->options->sitemap->video->advancedSettings->excludePosts = $excludedPosts;
	}
}