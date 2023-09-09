<?php
namespace AIOSEO\Plugin\Pro\Traits\Helpers;

use AIOSEO\Plugin\Pro\Models;
use AIOSEO\Plugin\Common\Models as CommonModels;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all Vue related helper methods.
 *
 * @since 4.1.4
 */
trait Vue {
	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $page         The current page.
	 * @param  int    $staticPostId Data for a specific post.
	 * @param  string $integration  Data for a integration ( builder ).
	 * @return array                The data.
	 */
	public function getVueData( $page = null, $staticPostId = null, $integration = null ) { // phpcs:ignore
		$data = parent::getVueData( $page, $staticPostId, $integration );

		// Check if user has a custom filename from the V3 migration.
		$sitemapFilename      = aioseo()->sitemap->helpers->filename( 'general' );
		$sitemapFilename      = $sitemapFilename ? $sitemapFilename : 'sitemap';
		$videoSitemapFilename = aioseo()->sitemap->helpers->filename( 'video' );
		$videoSitemapFilename = $videoSitemapFilename ? $videoSitemapFilename : 'video-sitemap';
		$newsIndex            = apply_filters( 'aioseo_news_sitemap_index_name', 'news' );

		$data['urls']['videoSitemapUrl'] = home_url( "/$videoSitemapFilename.xml" );
		$data['urls']['newsSitemapUrl']  = home_url( "/$newsIndex-sitemap.xml" );

		$data['translationsPro'] = $this->getJedLocaleData( 'aioseo-pro' );

		$license = is_network_admin()
			? aioseo()->networkLicense
			: aioseo()->license;

		$internalOptions = is_network_admin()
			? aioseo()->internalNetworkOptions
			: aioseo()->internalOptions;

		$data['license'] = [
			'isActive'   => $license->isActive(),
			'isExpired'  => $license->isExpired(),
			'isDisabled' => $license->isDisabled(),
			'isInvalid'  => $license->isInvalid(),
			'expires'    => $internalOptions->internal->license->expires,
			'features'   => $license->getLicenseFeatures()
		];

		// Check if this site is network licensed.
		$data['data']['isNetworkLicensed'] = aioseo()->license->isNetworkLicensed();

		$screen = aioseo()->helpers->getCurrentScreen();
		if ( ! empty( $screen ) && 'term' === $screen->base ) {
			$termId              = isset( $_GET['tag_ID'] ) ? abs( (int) wp_unslash( $_GET['tag_ID'] ) ) : 0; // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			$term                = Models\Term::getTerm( $termId );
			$taxonomy            = get_term( $termId );
			$data['currentPost'] = [
				'context'                     => 'term',
				'tags'                        => aioseo()->tags->getDefaultTermTags( $termId ),
				'id'                          => $termId,
				'priority'                    => ! empty( $term->priority ) ? $term->priority : 'default',
				'frequency'                   => ! empty( $term->frequency ) ? $term->frequency : 'default',
				'permalink'                   => get_term_link( $termId ),
				'title'                       => ! empty( $term->title ) ? $term->title : aioseo()->meta->title->getTaxonomyTitle( $taxonomy->taxonomy ),
				'description'                 => ! empty( $term->description ) ? $term->description : aioseo()->meta->description->getTaxonomyDescription( $taxonomy->taxonomy ),
				'keywords'                    => ! empty( $term->keywords ) ? $term->keywords : wp_json_encode( [] ),
				'type'                        => get_taxonomy( $screen->taxonomy )->labels->singular_name,
				'termType'                    => 'type' === $screen->taxonomy ? '_aioseo_type' : $screen->taxonomy,
				'canonicalUrl'                => $term->canonical_url,
				'default'                     => ( (int) $term->robots_default ) === 0 ? false : true,
				'noindex'                     => ( (int) $term->robots_noindex ) === 0 ? false : true,
				'noarchive'                   => ( (int) $term->robots_noarchive ) === 0 ? false : true,
				'nosnippet'                   => ( (int) $term->robots_nosnippet ) === 0 ? false : true,
				'nofollow'                    => ( (int) $term->robots_nofollow ) === 0 ? false : true,
				'noimageindex'                => ( (int) $term->robots_noimageindex ) === 0 ? false : true,
				'noodp'                       => ( (int) $term->robots_noodp ) === 0 ? false : true,
				'notranslate'                 => ( (int) $term->robots_notranslate ) === 0 ? false : true,
				'maxSnippet'                  => null === $term->robots_max_snippet ? -1 : (int) $term->robots_max_snippet,
				'maxVideoPreview'             => null === $term->robots_max_videopreview ? -1 : (int) $term->robots_max_videopreview,
				'maxImagePreview'             => $term->robots_max_imagepreview,
				'modalOpen'                   => false,
				'generalMobilePrev'           => false,
				'socialMobilePreview'         => false,
				'og_object_type'              => ! empty( $term->og_object_type ) ? $term->og_object_type : 'default',
				'og_title'                    => $term->og_title,
				'og_description'              => $term->og_description,
				'og_image_custom_url'         => $term->og_image_custom_url,
				'og_image_custom_fields'      => $term->og_image_custom_fields,
				'og_image_type'               => ! empty( $term->og_image_type ) ? $term->og_image_type : 'default',
				'og_video'                    => ! empty( $term->og_video ) ? $term->og_video : '',
				'og_article_section'          => ! empty( $term->og_article_section ) ? $term->og_article_section : '',
				'og_article_tags'             => ! empty( $term->og_article_tags ) ? $term->og_article_tags : wp_json_encode( [] ),
				'twitter_use_og'              => ( (int) $term->twitter_use_og ) === 0 ? false : true,
				'twitter_card'                => $term->twitter_card,
				'twitter_image_custom_url'    => $term->twitter_image_custom_url,
				'twitter_image_custom_fields' => $term->twitter_image_custom_fields,
				'twitter_image_type'          => $term->twitter_image_type,
				'twitter_title'               => $term->twitter_title,
				'twitter_description'         => $term->twitter_description,
				'redirects'                   => [
					'modalOpen' => false
				]
			];

			if ( ! $term->exists() ) {
				$data['currentPost'] = array_merge( $data['currentPost'], aioseo()->migration->meta->getMigratedTermMeta( $termId ) );
			}
		}

		if ( 'post' === $page ) {
			$postId = $staticPostId ? $staticPostId : get_the_ID();
			$wpPost = get_post( $postId );
			if ( is_object( $wpPost ) ) {
				$dynamicOptions                            = aioseo()->dynamicOptions->noConflict();
				$data['currentPost']['defaultSchemaType']  = '';
				$data['currentPost']['defaultWebPageType'] = '';
				if ( $dynamicOptions->searchAppearance->postTypes->has( $wpPost->post_type ) ) {
					$data['currentPost']['defaultSchemaType']  = $dynamicOptions->searchAppearance->postTypes->{$wpPost->post_type}->schemaType;
					$data['currentPost']['defaultWebPageType'] = $dynamicOptions->searchAppearance->postTypes->{$wpPost->post_type}->webPageType;
				}
			}

			static $validatorSchema = null;
			if ( null !== $validatorSchema ) {
				$clonedSchema    = json_decode( wp_json_encode( $data['currentPost']['schema'] ) );
				$validatorSchema = aioseo()->schema->getValidatorOutput(
					$postId,
					$clonedSchema->graphs,
					$clonedSchema->blockGraphs,
					$clonedSchema->default
				);
			}

			$data['schema']['output'] = $validatorSchema;

			$aioseoPost                     = CommonModels\Post::getPost( $postId );
			$data['currentPost']['open_ai'] = ! empty( $aioseoPost->open_ai )
				? CommonModels\Post::getDefaultOpenAiOptions( $aioseoPost->open_ai )
				: CommonModels\Post::getDefaultOpenAiOptions();
			$data['data']['aiModel'] = aioseo()->ai->model;

			$data['currentPost']['primary_term'] = ! empty( $aioseoPost->primary_term ) ? $aioseoPost->primary_term : [];
		}

		$wpPost = $this->getPost();
		if ( $wpPost && in_array( $wpPost->post_type, [ 'product', 'download' ], true ) ) {
			$isWooCommerceActive = $this->isWooCommerceActive();
			$isEddActive         = $this->isEddActive();
			$data['data']       += [
				'isWooCommerceActive' => $isWooCommerceActive,
				'isEddActive'         => $isEddActive
			];

			if ( $isWooCommerceActive ) {
				$data['data']['wooCommerce'] = [
					'currencySymbol'                => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
					'isPerfectBrandsActive'         => $this->isPerfectBrandsActive(),
					'isWooCommerceBrandsActive'     => $this->isWooCommerceBrandsActive(),
					'isWooCommerceUpcEanIsbnActive' => $this->isWooCommerceUpcEanIsbnActive()
				];
			}

			if ( $isEddActive ) {
				$data['data']['edd']['isEddReviewsActive'] = $this->isEddReviewsActive();
			}
		}

		if ( 'settings' === $page ) {
			// Default breadcrumb templates.
			$data['breadcrumbs']['defaultTemplates'] = [];
			$postTypes = aioseo()->helpers->getPublicPostTypes();
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				$data['breadcrumbs']['defaultTemplates']['postTypes'][ $postType['name'] ] =
					aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'single', $postType ) );
			}

			$taxonomies = aioseo()->helpers->getPublicTaxonomies();
			foreach ( $taxonomies as $taxonomy ) {
				if ( 'type' === $taxonomy['name'] ) {
					$taxonomy['name'] = '_aioseo_type';
				}

				$data['breadcrumbs']['defaultTemplates']['taxonomies'][ $taxonomy['name'] ] =
					aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'taxonomy', $taxonomy ) );
			}

			$data['breadcrumbs']['defaultTemplates']['archives'] = [
				'blog'     => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'blog' ) ),
				'author'   => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'author' ) ),
				'search'   => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'search' ) ),
				'notFound' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'notFound' ) ),
				'date'     => [
					'year'  => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'year' ) ),
					'month' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'month' ) ),
					'day'   => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'day' ) )
				]
			];

			$archives = aioseo()->helpers->getPublicPostTypes( false, true, true );
			foreach ( $archives as $archive ) {
				$data['breadcrumbs']['defaultTemplates']['archives']['postTypes'][ $archive['name'] ] =
					aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'postTypeArchive', $archive ) );
			}

			$data['searchStatistics']['isConnected'] = aioseo()->searchStatistics->api->auth->isConnected();
		}

		if ( is_multisite() && is_network_admin() ) {
			if ( 'tools' === $page || 'settings' === $page ) {
				$activeSites = json_decode( aioseo()->internalNetworkOptions->internal->sites->active );

				$data['data']['network']['activeSites'] = empty( $activeSites ) ? [] : $activeSites;
			}

			if ( 'tools' === $page && aioseo()->license->hasCoreFeature( 'tools', 'network-tools-import-export' ) ) {
				foreach ( aioseo()->helpers->getSites()['sites'] as $site ) {
					aioseo()->helpers->switchToBlog( $site->blog_id );
					$data['data']['network']['backups'][ $site->blog_id ] = array_reverse( aioseo()->backup->all() );
				}

				aioseo()->helpers->restoreCurrentBlog();
			}
		}

		$this->maybeCheckForPluginUpdates( $page );

		$loadedAddons = aioseo()->addons->getLoadedAddons();
		if ( ! empty( $loadedAddons ) ) {
			foreach ( $loadedAddons as $addon ) {
				if ( isset( $addon->helpers ) && method_exists( $addon->helpers, 'getVueData' ) ) {
					$data = $addon->helpers->getVueData( $data, $page );
				}
			}
		}

		return $data;
	}

	/**
	 * We may need to force a check for plugin updates.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $page The page slug for the AIOSEO page.
	 * @return void
	 */
	private function maybeCheckForPluginUpdates( $page ) {
		// If we aren't on one of the addon pages, return early.
		if ( ! in_array( $page, [
			'feature-manager',
			'link-assistant',
			'local-seo',
			'redirects',
			'search-appearance',
			'sitemaps'
		], true ) ) {
			return;
		}

		$shouldCheckForUpdates = false;

		// Loop through all addons and see if the addon needing an update matches the current page.
		foreach ( aioseo()->addons->getAddons() as $addon ) {
			if ( $addon->hasMinimumVersion ) {
				continue;
			}

			if ( 'feature-manager' === $page ) {
				$shouldCheckForUpdates = true;
				continue;
			}

			if ( 'aioseo-' . $page === $addon->sku ) {
				$shouldCheckForUpdates = true;
				continue;
			}

			if ( 'sitemaps' === $page && in_array( $addon->sku, [ 'aioseo-video-sitemap', 'aioseo-news-sitemap' ], true ) ) {
				$shouldCheckForUpdates = true;
			}
		}

		// We want to force checks for updates, so let's go ahead and do that now.
		if ( $shouldCheckForUpdates ) {
			delete_site_transient( 'update_plugins' );
		}
	}
}