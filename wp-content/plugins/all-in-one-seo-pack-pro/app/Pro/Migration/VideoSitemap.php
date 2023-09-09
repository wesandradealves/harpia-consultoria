<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Video Sitemap settings from V3.
 *
 * @since 4.0.0
 */
class VideoSitemap {
	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $regenerate Whether or not to regenerate only.
	 */
	public function __construct( $regenerate = false ) {
		$this->oldOptions = aioseo()->migration->oldOptions;

		if ( empty( $this->oldOptions['modules']['aiosp_video_sitemap_options'] ) ) {
			return;
		}

		if ( $regenerate ) {
			$this->regenerateSitemap();

			return;
		}

		$this->checkIfStatic();
		$this->migrateLinksPerIndex();
		$this->migrateExcludedPages();
		$this->migrateIncludedObjects();
		$this->regenerateSitemap();

		$settings = [
			'aiosp_video_sitemap_indexes'       => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'video', 'indexes' ] ],
			'aiosp_video_sitemap_rewrite'       => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'sitemap', 'video', 'advancedSettings', 'dynamic' ] ],
			'aiosp_video_sitemap_filename'      => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'video', 'filename' ] ],
			'aiosp_video_sitemap_custom_fields' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'video', 'advancedSettings', 'customFields' ] ],
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $this->oldOptions['modules']['aiosp_video_sitemap_options'] );

		if (
			aioseo()->options->sitemap->video->advancedSettings->excludePosts ||
			aioseo()->options->sitemap->video->advancedSettings->excludeTerms ||
			aioseo()->options->sitemap->video->advancedSettings->customFields ||
			( in_array( 'staticVideoSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) && ! aioseo()->options->deprecated->sitemap->video->advancedSettings->dynamic )
		) {
			aioseo()->options->sitemap->video->advancedSettings->enable = true;
		}
	}

	/**
	 * Check if the sitemap is statically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function checkIfStatic() {
		if (
			isset( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_rewrite'] ) &&
			empty( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_rewrite'] )
		) {
			$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
			array_push( $deprecatedOptions, 'staticVideoSitemap' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;

			aioseo()->options->deprecated->sitemap->video->advancedSettings->dynamic = false;
		}
	}

	/**
	 * Migrates the amount of links per sitemap index.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLinksPerIndex() {
		if ( ! empty( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_max_posts'] ) ) {
			$value = intval( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_max_posts'] );
			if ( ! $value ) {
				return;
			}
			$value = $value > 50000 ? 50000 : $value;
			aioseo()->options->sitemap->video->linksPerIndex = $value;
		}
	}

	/**
	 * Migrates the excluded object settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateExcludedPages() {
		if ( empty( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_excl_pages'] ) ) {
			return;
		}

		$excludedPosts = aioseo()->options->sitemap->video->advancedSettings->excludePosts;
		if ( ! empty( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_excl_pages'] ) ) {
			$pages = explode( ',', $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_excl_pages'] );
			if ( count( $pages ) ) {
				foreach ( $pages as $page ) {
					$page = trim( $page );
					$id   = intval( $page );
					if ( ! $id ) {
						$post = get_page_by_path( $page, OBJECT, aioseo()->helpers->getPublicPostTypes( true ) );
						if ( $post && is_object( $post ) ) {
							$id = $post->ID;
						}
					}

					if ( $id ) {
						$post = get_post( $id );
						if ( ! is_object( $post ) ) {
							continue;
						}

						$excludedPost        = new \stdClass();
						$excludedPost->value = $id;
						$excludedPost->type  = $post->post_type;
						$excludedPost->label = $post->post_name;
						$excludedPost->link  = get_permalink( $id );

						array_push( $excludedPosts, wp_json_encode( $excludedPost ) );
					}
				}
			}
		}
		aioseo()->options->sitemap->video->advancedSettings->excludePosts = $excludedPosts;
	}

	/**
	 * Migrates the objects that are included in the video sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateIncludedObjects() {
		if ( ! isset( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_posttypes'] ) ) {
			return;
		}

		$publicPostTypes = aioseo()->helpers->getPublicPostTypes( true );

		if ( in_array( 'all', $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_posttypes'], true ) ) {
			aioseo()->options->sitemap->video->postTypes->all      = true;
			aioseo()->options->sitemap->video->postTypes->included = array_values( $publicPostTypes );
		} else {
			aioseo()->options->sitemap->video->postTypes->all      = false;
			aioseo()->options->sitemap->video->postTypes->included =
				array_values( array_intersect( $publicPostTypes, $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_posttypes'] ) );
		}
	}

	/**
	 * Regenerates the sitemap if it is static.
	 *
	 * We need to do this since the stylesheet URLs have changed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function regenerateSitemap() {
		if (
			isset( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_rewrite'] ) &&
			empty( $this->oldOptions['modules']['aiosp_video_sitemap_options']['aiosp_video_sitemap_rewrite'] )
		) {
			$files         = aioseo()->sitemap->file->files();
			$detectedFiles = [];
			foreach ( $files as $filename ) {
				// We don't want to delete the video sitemap here at all.
				$isVideoSitemap = preg_match( '#.*video.*#', $filename ) ? true : false;
				if ( $isVideoSitemap ) {
					$detectedFiles[] = $filename;
				}
			}

			$fs = aioseo()->core->fs;
			if ( count( $detectedFiles ) && $fs->isWpfsValid() ) {
				foreach ( $detectedFiles as $file ) {
					$fs->fs->delete( $file, false, 'f' );
				}
			}

			$isVideoLoaded = function_exists( 'aioseoVideoSitemap' );
			$videoAddon    = aioseo()->addons->getAddon( 'aioseo-video-sitemap' );
			if ( $isVideoLoaded && aioseo()->license->isActive() && $videoAddon->isActive ) {
				try {
					if ( ! as_next_scheduled_action( 'aioseo_regenerate_video_sitemap' ) ) {
						as_schedule_single_action( time() + 5, 'aioseo_regenerate_video_sitemap', [], 'aioseo' );
					}
				} catch ( \Exception $e ) {
					// Do nothing.
				}

				return;
			}

			aioseo()->sitemap->file->generate( true );
		}
	}
}