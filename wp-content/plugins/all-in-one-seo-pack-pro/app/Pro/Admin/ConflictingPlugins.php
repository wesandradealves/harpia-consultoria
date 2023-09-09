<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Checks for conflicting plugins.
 *
 * @since 4.0.0
 */
class ConflictingPlugins extends CommonAdmin\ConflictingPlugins {
	/**
	 * Get a list of all conflicting plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of conflicting plugins.
	 */
	protected function getAllConflictingPlugins() {
		$conflictingPlugins        = parent::getAllConflictingPlugins();
		$conflictingSitemapPlugins = [];

		$canCheck     = false;
		$videoSitemap = aioseo()->addons->getAddon( 'aioseo-video-sitemap' );
		$newsSitemap  = aioseo()->addons->getAddon( 'aioseo-news-sitemap' );

		if ( ! empty( $videoSitemap ) && $videoSitemap->isActive && aioseo()->options->sitemap->video->enable ) {
			$canCheck = true;
		}

		if ( ! empty( $newsSitemap ) && $newsSitemap->isActive && aioseo()->options->sitemap->news->enable ) {
			$canCheck = true;
		}

		if ( $canCheck ) {
			$conflictingSitemapPlugins = $this->getConflictingPlugins( 'sitemap' );
		}

		return array_merge( $conflictingPlugins, $conflictingSitemapPlugins );
	}
}