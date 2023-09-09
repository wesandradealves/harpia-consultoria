<?php
namespace AIOSEO\Plugin\Pro\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Api as CommonApi;
use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Sitemaps extends CommonApi\Sitemaps {
	/**
	 * Delete all static sitemap files.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function deleteStaticFiles() {
		$response = parent::deleteStaticFiles();

		$files = list_files( get_home_path(), 1 );
		if ( ! count( $files ) ) {
			return;
		}

		$isVideoSitemapStatic = aioseo()->pro && aioseo()->options->sitemap->video->advancedSettings->enable &&
			in_array( 'staticVideoSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->sitemap->video->advancedSettings->dynamic;

		$detectedFiles = [];
		if ( ! $isVideoSitemapStatic ) {
			foreach ( $files as $filename ) {
				if ( preg_match( '#.*sitemap.*#', $filename ) ) {
					$isVideoSitemap = preg_match( '#.*video.*#', $filename ) ? true : false;
					if ( $isVideoSitemap ) {
						$detectedFiles[] = $filename;
					}
				}
			}
		}

		if ( ! count( $detectedFiles ) ) {
			return $response;
		}

		$fs = aioseo()->core->fs;
		if ( ! $fs->isWpfsValid() ) {
			return $response;
		}

		foreach ( $detectedFiles as $file ) {
			$fs->fs->delete( $file, false, 'f' );
		}

		Models\Notification::deleteNotificationByName( 'sitemap-static-files' );

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}
}