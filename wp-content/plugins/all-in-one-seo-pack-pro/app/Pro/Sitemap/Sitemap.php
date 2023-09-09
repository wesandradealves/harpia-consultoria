<?php
namespace AIOSEO\Plugin\Pro\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Sitemap as CommonSitemap;

/**
 * Handles our sitemaps.
 *
 * @since 4.0.0
 */
class Sitemap extends CommonSitemap\Sitemap {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// We need to instantiate the classes here separately and cannot call the parent constructor because the
		// base class with otherwise parse the request first and not give the Pro class a chance to parse it.
		$this->content       = new CommonSitemap\Content();
		$this->root          = new CommonSitemap\Root();
		$this->file          = new CommonSitemap\File();
		$this->image         = new CommonSitemap\Image\Image();
		$this->ping          = new CommonSitemap\Ping();
		$this->output        = new CommonSitemap\Output();
		$this->xsl           = new CommonSitemap\Xsl();
		$this->query         = new Query();
		$this->priority      = new Priority();
		$this->helpers       = new Helpers();
		$this->requestParser = new RequestParser();

		new CommonSitemap\Localization();

		$this->disableWpSitemap();
	}

	/**
	 * Checks if static sitemap files prevent dynamic sitemap generation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function detectStatic() {
		$isGeneralSitemapStatic = aioseo()->options->sitemap->general->advancedSettings->enable &&
			in_array( 'staticSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic;

		$isVideoSitemapStatic = aioseo()->pro && aioseo()->options->sitemap->video->advancedSettings->enable &&
			in_array( 'staticVideoSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->sitemap->video->advancedSettings->dynamic;

		if ( $isGeneralSitemapStatic && $isVideoSitemapStatic ) {
			Models\Notification::deleteNotificationByName( 'sitemap-static-files' );

			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$files = list_files( get_home_path(), 1 );
		if ( ! count( $files ) ) {
			return;
		}

		$detectedFiles = [];
		foreach ( $files as $filename ) {
			if ( preg_match( '#.*sitemap.*#', $filename ) ) {
				$isVideoSitemap = preg_match( '#.*video.*#', $filename ) ? true : false;
				if ( $isVideoSitemap && $isVideoSitemapStatic ) {
					continue;
				}
				if ( $isVideoSitemap || ! $isGeneralSitemapStatic ) {
					$detectedFiles[] = $filename;
				}
			}
		}

		$this->maybeShowStaticSitemapNotification( $detectedFiles );
	}
}