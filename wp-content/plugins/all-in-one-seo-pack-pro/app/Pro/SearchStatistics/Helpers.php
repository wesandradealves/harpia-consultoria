<?php
namespace AIOSEO\Plugin\Pro\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Addon\LinkAssistant\Models as LinkAssistantModels;
use AIOSEO\Plugin\Addon\Redirects\Utils as RedirectsUtils;

/**
 * Contains helper functions specific to Search Statistics.
 *
 * @since 4.3.0
 */
class Helpers {
	/**
	 * Maps all rows to use to the given property.
	 *
	 * @since 4.3.0
	 *
	 * @param  array  $rows     The rows.
	 * @param  string $property The property,
	 * @return array            The mapped rows.
	 */
	public function setRowKey( $rows, $property = 'keyword' ) {
		$mappedRows = [];
		foreach ( $rows as $row ) {
			$value              = is_object( $row ) ? $row->$property : $row[ $property ];
			$key                = 'page' === $property ? aioseo()->searchStatistics->helpers->getPageSlug( $value ) : strtolower( $value );
			$key                = is_numeric( $key ) ? '_' . $key : $key; // To prevent sorting issues, the key can never be numeric.
			$mappedRows[ $key ] = $row;
		}

		return $mappedRows;
	}

	/**
	 * Returns the page slug from a URL.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $url The URL.
	 * @return string      The page slug.
	 */
	public function getPageSlug( $url ) {
		$baseUrl = aioseo()->searchStatistics->api->auth->getAuthedSite();
		$domain  = strtolower( wp_parse_url( $baseUrl, PHP_URL_HOST ) );
		$domain  = str_replace( [ 'www.', '.' ], [ '', '\.' ], $domain );
		$url     = strtolower( trim( $url ) );
		$url     = preg_replace( "/http[s]?:\/\/(www\.)?$domain/mU", '', $url );
		$url     = urldecode( $url ); // Decode URL as Google returns encoded URLs.

		return str_replace( $baseUrl, '', $url );
	}

	/**
	 * Returns the included Search Statistics post types.
	 *
	 * @since 4.3.0
	 *
	 * @return array The included post types.
	 */
	public function getIncludedPostTypes() {
		if ( aioseo()->options->searchStatistics->postTypes->all ) {
			return aioseo()->helpers->getPublicPostTypes( true );
		}

		return aioseo()->options->searchStatistics->postTypes->included;
	}

	/**
	 * Returns the Link Assistant data for the given post ID if the addon is active.
	 *
	 * @since 4.3.0
	 *
	 * @param  int $postId The post ID.
	 * @return array       The Link Assistant data.
	 */
	public function getLinkAssistantData( $postId ) {
		if ( ! $postId ) {
			return [];
		}

		$isLinkAssistantLoaded = function_exists( 'aioseoLinkAssistant' );
		$linkAssistantAddon    = aioseo()->addons->getAddon( 'aioseo-link-assistant' );

		if ( ! $isLinkAssistantLoaded || ! aioseo()->license->isActive() || ! $linkAssistantAddon->isActive ) {
			return [];
		}

		$totalOutboundSuggestions = LinkAssistantModels\Suggestion::getTotalOutboundSuggestions( $postId );
		$totalInboundSuggestions  = LinkAssistantModels\Suggestion::getTotalInboundSuggestions( $postId );

		return [
			'inboundInternal'  => LinkAssistantModels\Link::getTotalInboundInternalLinks( $postId ),
			'outboundInternal' => LinkAssistantModels\Link::getTotalOutboundInternalLinks( $postId ),
			'affiliate'        => LinkAssistantModels\Link::getTotalAffiliateLinks( $postId ),
			'external'         => LinkAssistantModels\Link::getTotalExternalLinks( $postId ),
			'linkSuggestions'  => $totalOutboundSuggestions + $totalInboundSuggestions
		];
	}

	/**
	 * Returns the Redirects data for the given post ID if the addon is active.
	 *
	 * @since 4.3.0
	 *
	 * @param  int $postId The post ID.
	 * @return array       The Redirects data.
	 */
	public function getRedirectsData( $postId ) {
		if ( ! $postId ) {
			return [];
		}

		$isRedirectsLoaded = function_exists( 'aioseoRedirects' );
		$redirectsAddon    = aioseo()->addons->getAddon( 'aioseo-redirects' );

		if (
			! $isRedirectsLoaded ||
			! aioseo()->license->isActive() ||
			! $redirectsAddon->isActive ||
			! method_exists( aioseoRedirects()->redirect, 'getRedirects' ) ||
			! method_exists( aioseoRedirects()->redirect, 'getRedirectsByTarget' )
		) {
			return [];
		}

		$from      = [];
		$to        = '';
		$toCode    = 0;
		$permalink = get_permalink( $postId );
		$targetUrl = RedirectsUtils\WpUri::excludeHomeUrl( $permalink );

		foreach ( aioseoRedirects()->redirect->getRedirects( $permalink ) as $redirect ) {
			$to     = RedirectsUtils\Request::formatTargetUrl( trim( $redirect->target_url ) );
			$toCode = $redirect->type;
			break;
		}

		foreach ( aioseoRedirects()->redirect->getRedirectsByTarget( $targetUrl ) as $redirect ) {
			$from[] = RedirectsUtils\Request::formatTargetUrl( trim( $redirect->target_url ) );
		}

		return [
			'from'   => $from,
			'to'     => $to,
			'toCode' => $toCode
		];
	}
}