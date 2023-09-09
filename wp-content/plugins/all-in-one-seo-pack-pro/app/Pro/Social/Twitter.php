<?php
namespace AIOSEO\Plugin\Pro\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Social as CommonSocial;

/**
 * Handles the Twitter meta.
 *
 * @since 4.0.0
 */
class Twitter extends CommonSocial\Twitter {
	/**
	 * Returns the Twitter image URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $postId The post ID (optional).
	 * @return string         The image URL.
	 */
	public function getImage( $postId = null ) {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return parent::getImage( $postId );
		}

		$term     = get_queried_object();
		$metaData = aioseo()->meta->metaData->getMetaData( $term );

		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getImage();
		}

		$image = '';
		if ( ! empty( $metaData ) ) {
			$imageSource = ! empty( $metaData->twitter_image_type ) && 'default' !== $metaData->twitter_image_type
				? $metaData->twitter_image_type
				: aioseo()->options->social->twitter->general->defaultImageSourceTerms;

			$image = aioseo()->social->image->getImage( 'twitter', $imageSource, $term );
		}

		return $image ? $image : aioseo()->social->facebook->getImage();
	}

	/**
	 * Returns the Twitter title for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|integer $post The post object or ID (optional).
	 * @return string                The Twitter title.
	 */
	public function getTitle( $post = null ) {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return parent::getTitle( $post );
		}

		$term     = get_queried_object();
		$metaData = aioseo()->meta->metaData->getMetaData( $term );

		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getTitle();
		}

		$title = '';
		if ( ! empty( $metaData->twitter_title ) ) {
			$title = aioseo()->meta->title->helpers->prepare( $metaData->twitter_title, $term->term_id );
		}

		return $title ? $title : aioseo()->social->facebook->getTitle();
	}

	/**
	 * Returns the Twitter description for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|integer $post The post object or ID (optional).
	 * @return string                The Twitter description.
	 */
	public function getDescription( $post = null ) {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return parent::getDescription( $post );
		}

		$term     = get_queried_object();
		$metaData = aioseo()->meta->metaData->getMetaData( $term );
		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getDescription();
		}

		$description = '';
		if ( ! empty( $metaData->twitter_description ) ) {
			$description = aioseo()->meta->description->helpers->prepare( $metaData->twitter_description, $term->term_id );
		}

		return $description ? $description : aioseo()->social->facebook->getDescription();
	}
}