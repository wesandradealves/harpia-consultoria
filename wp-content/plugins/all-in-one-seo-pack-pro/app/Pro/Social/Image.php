<?php
namespace AIOSEO\Plugin\Pro\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Social as CommonSocial;
use AIOSEO\Plugin\Pro\Models;

/**
 * Handles the Open Graph and Twitter Image.
 *
 * @since 4.0.0
 */
class Image extends CommonSocial\Image {
	/**
	 * The term object.
	 *
	 * @since 4.1.6.2
	 *
	 * @var WP_Term
	 */
	private $term;

	/**
	 * Returns the Facebook or Twitter image.
	 *
	 * @since 4.0.0
	 *
	 * @param  string          $type        The type ("Facebook" or "Twitter").
	 * @param  string          $imageSource The image source.
	 * @param  WP_Term|WP_Post $object      The queried object.
	 * @return string|array                 The image data.
	 */
	public function getImage( $type, $imageSource, $object ) {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return parent::getImage( $type, $imageSource, $object );
		}

		$this->type          = $type;
		$this->term          = $object;
		$this->thumbnailSize = apply_filters( 'aioseo_thumbnail_size', 'fullsize' );

		static $images = [];
		if ( isset( $images[ $this->type ] ) ) {
			return $images[ $this->type ];
		}

		switch ( $imageSource ) {
			case 'custom':
				$image = $this->getCustomFieldImage();
				break;
			case 'custom_image':
				$metaData = aioseo()->meta->metaData->getMetaData( $this->term );
				if ( empty( $metaData ) ) {
					break;
				}

				return ( 'facebook' === strtolower( $this->type ) )
					? $metaData->og_image_custom_url
					: $metaData->twitter_image_custom_url;
			case 'default':
			default:
				$image = aioseo()->options->social->{$this->type}->general->defaultImageTerms;
		}

		if ( empty( $image ) ) {
			$image = aioseo()->options->social->{$this->type}->general->defaultImageTerms;
		}

		if ( is_array( $image ) ) {
			$images[ $type ] = $image;

			return $images[ $type ];
		}

		$imageWithoutDimensions = aioseo()->helpers->removeImageDimensions( $image );
		$attachmentId           = aioseo()->helpers->attachmentUrlToPostId( $imageWithoutDimensions );
		$images[ $type ]        = $attachmentId
			? wp_get_attachment_image_src( $attachmentId, $this->thumbnailSize )
			: $image;

		return $images[ $type ];
	}

	/**
	 * Returns the first available image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getFirstAvailableImage() {
		$image = $this->getCustomFieldImage();

		if ( ! $image && 'twitter' === strtolower( $this->type ) ) {
			$image = aioseo()->options->social->twitter->homePage->image;
		}

		return $image ? $image : aioseo()->options->social->facebook->homePage->image;
	}

	/**
	 * Returns the image from a custom field.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getCustomFieldImage() {
		$cachedImage = $this->getCachedImage( $this->term );
		if ( $cachedImage ) {
			return $cachedImage;
		}

		$prefix = 'facebook' === strtolower( $this->type ) ? 'og_' : 'twitter_';

		$aioseoTerm   = Models\Term::getTerm( $this->term->term_id );
		$customFields = ! empty( $aioseoTerm->{ $prefix . 'image_custom_fields' } )
			? $aioseoTerm->{ $prefix . 'image_custom_fields' }
			: aioseo()->options->social->{$this->type}->general->customFieldImageTerms;

		if ( ! $customFields ) {
			return '';
		}

		$customFields = explode( ',', $customFields );
		foreach ( $customFields as $customField ) {
			$image = get_term_meta( $this->term->term_id, $customField, true );

			if ( ! empty( $image ) ) {
				return $image;
			}
		}

		return '';
	}
}