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
class NewsSitemap {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_pro_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migratePostTypes();
		$this->migrateExclusions();

		$settings = [
			'seopress_news_enable' => [ 'type' => 'boolean', 'newOption' => [ 'news', 'enable' ] ],
			'seopress_news_name'   => [ 'type' => 'string', 'newOption' => [ 'news', 'publicationName' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Post Types to include.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migratePostTypes() {
		$postTypes = $this->options['seopress_news_name_post_types_list'];
		if ( empty( $postTypes ) ) {
			return;
		}

		$included = wp_list_filter( $postTypes, [ 'include' => true ] );

		aioseo()->options->sitemap->news->postTypes->included = array_keys( $included );
	}

	/**
	 * Migrates the posts that are excluded from the News Sitemap.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateExclusions() {
		$excludedPosts   = aioseo()->options->sitemap->news->advancedSettings->excludePosts;
		$toExclude       = aioseo()->core->db
			->start( 'postmeta as pm' )
			->select( 'pm.post_id' )
			->whereRaw( "`pm`.`meta_key` = '_seopress_news_disabled'" )
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
			aioseo()->options->sitemap->news->advancedSettings->enable = true;
		}
		aioseo()->options->sitemap->news->advancedSettings->excludePosts = $excludedPosts;
	}
}