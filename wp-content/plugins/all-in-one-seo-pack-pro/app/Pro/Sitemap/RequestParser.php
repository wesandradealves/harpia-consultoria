<?php
namespace AIOSEO\Plugin\Pro\Sitemap;

use AIOSEO\Plugin\Common\Sitemap as CommonSitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parses the current request and checks whether we need to serve a sitemap or a stylesheet.
 *
 * @since 4.2.1
 */
class RequestParser extends CommonSitemap\RequestParser {
	/**
	 * Checks whether we need to serve a sitemap or related stylesheet.
	 *
	 * @since 4.2.1
	 *
	 * @param  WP   $wp The main WordPress environment instance.
	 * @return void
	 */
	public function checkRequest( $wp ) {
		$this->slug = $wp->request
			? $this->cleanSlug( $wp->request )
			// We must fallback to the REQUEST URI in case the site uses plain permalinks.
			: $this->cleanSlug( $_SERVER['REQUEST_URI'] );

		// Check if we need to remove the trailing slash or redirect another sitemap URL like "wp-sitemap.xml".
		$this->maybeRedirect();

		foreach ( aioseo()->addons->getLoadedAddons() as $loadedAddon ) {
			if ( ! empty( $loadedAddon->requestParser ) && method_exists( $loadedAddon->requestParser, 'checkRequest' ) ) {
				$loadedAddon->requestParser->checkRequest();
			}
		}

		// The addons need to run before Core does, since the Video and News Sitemap will otherwise be mistaken for the regular one.
		parent::checkRequest( $wp );
	}
}