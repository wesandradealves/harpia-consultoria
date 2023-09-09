<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport\SeoPress as CommonSeoPress;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the post meta from SEOPress.
 *
 * @since 4.1.4
 */
class PostMeta extends CommonSeoPress\PostMeta {
	/**
	 * Maybe import redirects from post meta.
	 *
	 * @since 4.1.4
	 *
	 * @param object $postMeta The post meta from database.
	 * @return array           The meta data.
	 */
	public function getMetaData( $postMeta, $post_id ) {
		$meta = parent::getMetaData( $postMeta, $post_id );

		// Check if aioseoRedirects is active and try to import redirects from post meta.
		$redirectsAddon = aioseo()->addons->getAddon( 'aioseo-redirects' );
		if ( ! empty( $redirectsAddon ) && $redirectsAddon->isActive ) {
			$redirectMeta       = [];
			$mappedRedirectMeta = [
				'_seopress_redirections_type'    => 'type',
				'_seopress_redirections_value'   => 'target_url',
				'_seopress_redirections_enabled' => 'enabled',
			];

			foreach ( $postMeta as $record ) {
				if ( in_array( $record->meta_key, array_keys( $mappedRedirectMeta ), true ) ) {
					$redirectMeta[ $mappedRedirectMeta[ $record->meta_key ] ] = $record->meta_value; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}
			}

			if ( ! empty( $redirectMeta ) ) {
				$redirectMeta['source_url'] = get_permalink( $post_id );
				$this->migrateMetaRedirect( $redirectMeta );
			}
		}

		return $meta;
	}

	/**
	 * Import the redirects from post meta.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateMetaRedirect( $rule ) {
		// Double check if aioseoRedirects is active.
		if ( ! function_exists( 'aioseoRedirects' ) ) {
			return;
		}

		$urlFrom = wp_make_link_relative( $rule['source_url'] );
		$urlTo   = 0 === strpos( $rule['target_url'], 'http' ) || '/' === $rule['target_url'] ? $rule['target_url'] : '/' . $rule['target_url'];
		if ( empty( $urlTo ) ) {
			$urlTo = '/';
		}

		aioseoRedirects()->importExport->seoPress->importRule([
			'source_url'   => $urlFrom,
			'target_url'   => $urlTo,
			'type'         => $rule['type'],
			'query_param'  => json_decode( aioseoRedirects()->options->redirectDefaults->queryParam )->value,
			'group'        => 'manual',
			'regex'        => false,
			'ignore_slash' => aioseoRedirects()->options->redirectDefaults->ignoreSlash,
			'ignore_case'  => aioseoRedirects()->options->redirectDefaults->ignoreCase,
			'enabled'      => $rule['enabled']
		]);
	}
}