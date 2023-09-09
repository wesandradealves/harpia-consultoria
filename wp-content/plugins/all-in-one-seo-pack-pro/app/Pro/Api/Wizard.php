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
class Wizard extends CommonApi\Wizard {
	/**
	 * Save the wizard information.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveWizard( $request ) {
		$response = parent::saveWizard( $request );
		$body     = $request->get_json_params();
		$section  = ! empty( $body['section'] ) ? sanitize_text_field( $body['section'] ) : null;
		$wizard   = ! empty( $body['wizard'] ) ? $body['wizard'] : null;
		$network  = ! empty( $body['network'] ) ? $body['network'] : false;

		if ( 'additionalInformation' === $section && ! empty( $wizard['additionalInformation'] ) ) {
			$additionalInformation = $wizard['additionalInformation'];
			if ( ! empty( $additionalInformation['socialShareImage'] ) ) {
				aioseo()->options->social->facebook->general->defaultImageTerms = $additionalInformation['socialShareImage'];
				aioseo()->options->social->twitter->general->defaultImageTerms  = $additionalInformation['socialShareImage'];
			}
		}

		if (
			(
				'features' === $section ||
				'license-key' === $section
			) &&
			! empty( $wizard['features'] )
		) {
			$features = $wizard['features'];

			$cantInstall = [];
			$addons      = [
				'local-seo'      => 'aioseo-local-business',
				'image-seo'      => 'aioseo-image-seo',
				'video-sitemap'  => 'aioseo-video-sitemap',
				'news-sitemap'   => 'aioseo-news-sitemap',
				'redirects'      => 'aioseo-redirects',
				'link-assistant' => 'aioseo-link-assistant',
				'index-now'      => 'aioseo-index-now',
				'rest-api'       => 'aioseo-rest-api'
			];

			foreach ( $addons as $slug => $addonSlug ) {
				if ( in_array( $slug, $features, true ) ) {
					$addon = aioseo()->addons->getAddon( $addonSlug, true );
					if ( ! $addon->isActive && ! $addon->requiresUpgrade ) {
						if ( $addon->installed || $addon->canInstall ) {
							aioseo()->addons->installAddon( $addon->basename, $network );
						} else {
							$cantInstall[] = $addon->name;
						}
					}
				}
			}

			if ( ! empty( $cantInstall ) ) {
				$notification = Models\Notification::getNotificationByName( 'install-addons' );
				if ( ! $notification->exists() ) {
					$content = '';
					foreach ( $cantInstall as $pluginName ) {
						$content .= '<li><strong>' . $pluginName . '</strong></li>';
					}
					Models\Notification::addNotification( [
						'slug'              => uniqid(),
						'notification_name' => 'install-addons',
						'title'             => sprintf(
							// Translators: 1 - The plugin short name ("AIOSEO").
							__( 'Install %1$s Addons', 'aioseo-pro' ),
							AIOSEO_PLUGIN_SHORT_NAME
						),
						'content'           => sprintf(
							// Translators: 1 - The plugin short name ("AIOSEO"), 2 - A list of addons.
							__( 'You selected to install the following addons during the setup of %1$s, but there was an issue during installation:%2$s', 'aioseo-pro' ),
							AIOSEO_PLUGIN_SHORT_NAME,
							'<ul>' . $content . '</ul>'
						),
						'type'              => 'info',
						'level'             => [ 'all' ],
						'button1_label'     => __( 'Install Addons', 'aioseo-pro' ),
						'button1_action'    => html_entity_decode( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/downloads/', 'wizard-features', 'cant-install-addons' ) ),
						'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
						'button2_action'    => 'http://action#notification/install-addons-reminder',
						'start'             => gmdate( 'Y-m-d H:i:s' )
					] );
				}
			}
		}

		return $response;
	}
}