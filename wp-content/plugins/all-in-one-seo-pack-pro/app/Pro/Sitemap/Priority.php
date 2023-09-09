<?php
namespace AIOSEO\Plugin\Pro\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Sitemap as CommonSitemap;

/**
 * Determines the sitemap priority/frequency.
 *
 * @since 4.0.0
 */
class Priority extends CommonSitemap\Priority {
	/**
	 * Returns the sitemap priority for a given page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $pageType   The type of page (e.g. homepage, blog, post, taxonomies, etc.).
	 * @param  stdClass $object     The post/term object (optional).
	 * @param  string   $objectType The post/term object type (optional).
	 * @return float    $priority   The priority.
	 */
	public function priority( $pageType, $object = false, $objectType = null ) {
		$priority = ! empty( $object->priority ) && null !== $object->priority ? $object->priority : parent::priority( $pageType, $object, $objectType );

		return $priority;
	}

	/**
	 * Returns the sitemap frequency for a given page.
	 *
	 * @since 4.0.0
	 *
	 * @param  string   $pageType   The type of page (e.g. homepage, blog, post, taxonomies, etc.).
	 * @param  stdClass $object     The post/term object (optional).
	 * @param  string   $objectType The post/term object type (optional).
	 * @return float    $frequency  The frequency.
	 */
	public function frequency( $pageType, $object = false, $objectType = null ) {
		$frequency = ! empty( $object->frequency ) && 'default' !== $object->frequency ? $object->frequency : parent::frequency( $pageType, $object, $objectType );

		return $frequency;
	}
}