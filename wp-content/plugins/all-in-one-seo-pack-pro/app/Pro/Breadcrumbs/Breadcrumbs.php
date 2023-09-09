<?php
namespace AIOSEO\Plugin\Pro\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Breadcrumbs
 *
 * @since 4.1.1
 */
class Breadcrumbs extends \AIOSEO\Plugin\Common\Breadcrumbs\Breadcrumbs {
	/**
	 * Breadcrumbs constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		parent::__construct();

		$this->frontend = new Frontend();
	}

	/**
	 * Overrides a post taxonomy crumbs to allow the selection of a taxonomy.
	 *
	 * @since 4.1.1
	 *
	 * @param  int|\WP_Post $post     An ID or a WP_Post object.
	 * @param  null|string  $taxonomy A taxonomy to use. If none is provided the first one with terms selected will be used.
	 * @return array                  An array of term crumbs.
	 */
	public function getPostTaxonomyCrumbs( $post, $taxonomy = null ) {
		$postType = get_post_type( $post );
		if (
			aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
			! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate
		) {

			// Hide crumbs.
			if ( ! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->showTaxonomyCrumbs ) {
				return [];
			}

			// Use the configured taxonomy.
			if ( aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->taxonomy ) {
				$taxonomy = aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->taxonomy;
			}
		}

		return parent::getPostTaxonomyCrumbs( $post, $taxonomy );
	}

	/**
	 * Overrides the post archive crumb to allow for it to be disabled.
	 *
	 * @since 4.1.1
	 *
	 * @param  int|\WP_Post $post An ID or a WP_Post object.
	 * @return array              A crumb.
	 */
	public function getPostArchiveCrumb( $post ) {
		$postType = get_post_type( $post );
		if (
			$postType &&
			aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
			! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate &&
			! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->showArchiveCrumb
		) {
			return [];
		}

		return parent::getPostArchiveCrumb( $post );
	}

	/**
	 * Overrides the post's term hierarchy to allow skipping unselected terms.
	 *
	 * @since 4.1.1
	 *
	 * @param  int|\WP_Post $post                An ID or a WP_Post object.
	 * @param  array        $taxonomies          An array of taxonomy names.
	 * @param  false        $skipUnselectedTerms Allow unselected terms to be filtered out from the crumbs.
	 * @return array                             An array of the taxonomy name + a term hierarchy.
	 */
	public function getPostTaxTermHierarchy( $post, $taxonomies = [], $skipUnselectedTerms = false ) {
		if ( aioseo()->options->breadcrumbs->advanced->taxonomySkipUnselected ) {
			$skipUnselectedTerms = true;
		}

		return parent::getPostTaxTermHierarchy( $post, $taxonomies, $skipUnselectedTerms );
	}

	/**
	 * Gets a home page crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  string     $type      The type of breadcrumb.
	 * @param  mixed      $reference The breadcrumb reference.
	 * @return array|void            The home crumb.
	 */
	public function maybeGetHomePageCrumb( $type = null, $reference = null ) {
		$show = parent::maybeGetHomePageCrumb( $type, $reference );
		switch ( $type ) {
			case 'post':
			case 'single':
			case 'page':
				$postType = get_post_type( $reference );
				if (
					$postType &&
					aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
					! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate
				) {
					$show = aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->showHomeCrumb;
				}
				break;
			case 'category':
			case 'tag':
			case 'taxonomy':
				if ( is_a( $reference, 'WP_Term' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->taxonomies->has( $reference->taxonomy ) &&
						! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$reference->taxonomy}->useDefaultTemplate
					) {
						$show = aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$reference->taxonomy}->showHomeCrumb;
					}
				}
				break;
			case 'postTypeArchive':
				if ( is_a( $reference, 'WP_Post_Type' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->has( $reference->name ) &&
						! aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->{$reference->name}->useDefaultTemplate
					) {
						$show = aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->{$reference->name}->showHomeCrumb;
					}
				}
				break;
			case 'date':
			case 'author':
			case 'blog':
			case 'search':
			case 'notFound':
				if (
					aioseo()->dynamicOptions->breadcrumbs->archives->has( $type ) &&
					! aioseo()->dynamicOptions->breadcrumbs->archives->{$type}->useDefaultTemplate
				) {
					$show = aioseo()->dynamicOptions->breadcrumbs->archives->{$type}->showHomeCrumb;
				}
				break;
		}

		if ( $show ) {
			return parent::getHomePageCrumb();
		}
	}

	/**
	 * Overrides the home page crumb to allow to disable it.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $type      The type of breadcrumb.
	 * @param  mixed  $reference The breadcrumb reference.
	 * @return array             The home crumb.
	 */
	public function getPrefixCrumb( $type, $reference ) {
		switch ( $type ) {
			case 'post':
			case 'single':
			case 'page':
				$postType = get_post_type( $reference );
				if (
					$postType &&
					aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
					! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate &&
					! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->showPrefixCrumb
				) {
					return [];
				}
				break;
			case 'category':
			case 'tag':
			case 'taxonomy':
				if ( is_a( $reference, 'WP_Term' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->taxonomies->has( $reference->taxonomy ) &&
						! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$reference->taxonomy}->useDefaultTemplate &&
						! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$reference->taxonomy}->showPrefixCrumb
					) {
						return [];
					}
				}
				break;
			case 'postTypeArchive':
				if ( is_a( $reference, 'WP_Post_Type' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->has( $reference->name ) &&
						! aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->{$reference->name}->useDefaultTemplate &&
						! aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->{$reference->name}->showPrefixCrumb
					) {
						return [];
					}
				}
				break;
			case 'date':
			case 'author':
			case 'blog':
			case 'search':
			case 'notFound':
				if (
					aioseo()->dynamicOptions->breadcrumbs->archives->has( $type ) &&
					! aioseo()->dynamicOptions->breadcrumbs->archives->{$type}->useDefaultTemplate &&
					! aioseo()->dynamicOptions->breadcrumbs->archives->{$type}->showPrefixCrumb
				) {
					return [];
				}
				break;
		}

		return parent::getPrefixCrumb( $type, $reference );
	}

	/**
	 * Gets the post's parent crumbs.
	 *
	 * @since 4.1.1
	 *
	 * @param  int|\WP_Post $post An ID or a WP_Post object.
	 * @param  string       $type The crumb type.
	 * @return array              An array of the post parent crumbs.
	 */
	public function getPostParentCrumbs( $post, $type = 'single' ) {
		$postType = get_post_type( $post );
		if (
			$postType &&
			aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
			! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate &&
			! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->showParentCrumbs
		) {
			return [];
		}

		return parent::getPostParentCrumbs( $post, $type );
	}

	/**
	 * Overrides an array of crumbs parents for the term.
	 *
	 * @since 4.1.1
	 *
	 * @param  \WP_Term $term A WP_Term object.
	 * @return array          An array of parent crumbs.
	 */
	public function getTermTaxonomyParentCrumbs( $term ) {
		if (
			! empty( $term->taxonomy ) &&
			aioseo()->dynamicOptions->breadcrumbs->taxonomies->has( $term->taxonomy ) &&
			! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$term->taxonomy}->useDefaultTemplate &&
			! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$term->taxonomy}->showParentCrumbs
		) {
			return [];
		}

		return parent::getTermTaxonomyParentCrumbs( $term );
	}

	/**
	 * Gets the paged crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  array      $reference The paged array for reference.
	 * @return array|void            A crumb.
	 */
	public function getPagedCrumb( $reference ) {
		if ( ! aioseo()->options->breadcrumbs->advanced->showPaged ) {
			return;
		}

		return $this->makeCrumb( sprintf( aioseo()->options->breadcrumbs->advanced->pagedFormat, $reference['paged'] ), $reference['link'], 'paged', $reference );
	}

	/**
	 * Function to extend on pro for extra functionality.
	 *
	 * @since 4.1.1
	 *
	 * @param string $type      The type of breadcrumb.
	 * @param mixed  $reference The breadcrumb reference.
	 * @return bool              Show current item.
	 */
	public function showCurrentItem( $type = null, $reference = null ) {
		$showCurrentItem = parent::showCurrentItem( $type, $reference );
		switch ( $type ) {
			case 'post':
			case 'single':
			case 'page':
				$postType = get_post_type( $reference );
				if (
					$postType &&
					aioseo()->dynamicOptions->breadcrumbs->postTypes->has( $postType ) &&
					! aioseo()->dynamicOptions->breadcrumbs->postTypes->{$postType}->useDefaultTemplate
				) {
					$showCurrentItem = true;
				}
				break;
			case 'category':
			case 'tag':
			case 'taxonomy':
				if ( is_a( $reference, 'WP_Term' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->taxonomies->has( $reference->taxonomy ) &&
						! aioseo()->dynamicOptions->breadcrumbs->taxonomies->{$reference->taxonomy}->useDefaultTemplate
					) {
						$showCurrentItem = true;
					}
				}
				break;
			case 'postTypeArchive':
				if ( is_a( $reference, 'WP_Post_Type' ) ) {
					if (
						aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->has( $reference->name ) &&
						! aioseo()->dynamicOptions->breadcrumbs->archives->postTypes->{$reference->name}->useDefaultTemplate
					) {
						$showCurrentItem = true;
					}
				}
				break;
			case 'date':
			case 'author':
			case 'blog':
			case 'search':
			case 'notFound':
				if (
					aioseo()->dynamicOptions->breadcrumbs->archives->has( $type ) &&
					! aioseo()->dynamicOptions->breadcrumbs->archives->{$type}->useDefaultTemplate
				) {
					$showCurrentItem = true;
				}
				break;
		}

		return apply_filters( 'aioseo_breadcrumbs_show_current_item', $showCurrentItem, $type, $reference );
	}
}