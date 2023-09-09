<?php
namespace AIOSEO\Plugin\Pro\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin\Notices as CommonNotices;
use AIOSEO\Plugin\Common\Models;

/**
 * Pro version of the notices class.
 *
 * @since 4.0.0
 */
class Notices extends CommonNotices\Notices {
	/**
	 * Initialize the internal notices.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function initInternalNotices() {
		parent::initInternalNotices();

		$this->maybeDeleteNotices();
		$this->localBusinessOrganization();
		$this->newsPublicationName();
		$this->wooUpsellNotice();
	}

	/**
	 * Validates the notification type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $type The notification type we are targeting.
	 * @return boolean       True if yes, false if no.
	 */
	public function validateType( $type ) {
		$validated = parent::validateType( $type );

		// Any pro notification should pass here.
		if ( 'all-pro' === $type ) {
			$validated = true;
		}

		// If we are targeting unlicensed users.
		if ( 'unlicensed' === $type && ! aioseo()->license->isActive() ) {
			$validated = true;
		}

		// If we are targeting licensed users.
		if ( 'licensed' === $type && aioseo()->license->isActive() ) {
			$validated = true;
		}

		// If we are targeting a specific user level.
		if ( aioseo()->internalOptions->internal->license->level === $type ) {
			$validated = true;
		}

		return $validated;
	}

	/**
	 * Possibly delete notices that are not needed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function maybeDeleteNotices() {
		$addons = aioseo()->addons->getAddons();
		foreach ( $addons as $addon ) {
			if ( ! $addon->installed ) {
				return;
			}

			$notification = Models\Notification::getNotificationByName( 'install-' . $addon->sku );
			if ( $notification->exists() ) {
				$notification->delete();
			}
		}
	}

	/**
	 * Add a notice if the local business is enabled, but the schema is set to "Person".
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function localBusinessOrganization() {
		$notification   = Models\Notification::getNotificationByName( 'local-business-organization' );
		$addon          = aioseo()->addons->getAddon( 'aioseo-local-business' );
		$siteRepresents = aioseo()->options->searchAppearance->global->schema->siteRepresents;

		if (
			'organization' === $siteRepresents ||
			! $addon->isActive ||
			! aioseo()->license->isActive() ||
			$addon->requiresUpgrade
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'local-business-organization' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'local-business-organization',
			'title'             => __( 'Local Business Organization', 'aioseo-pro' ),
			'content'           => __( 'Your site is currently set to represent a Person. In order to use Local Business schema, you must set your site to represent an Organization.', 'aioseo-pro' ),
			'type'              => 'error',
			'level'             => [ 'pro' ],
			'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
			'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=schema-graph-site-represents&aioseo-highlight=schema-graph-site-represents:global-settings',
			'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
			'button2_action'    => 'http://action#notification/local-business-organization-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Add a notice if the local business is enabled, but the schema is set to "Person".
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function newsPublicationName() {
		$notification    = Models\Notification::getNotificationByName( 'news-publication-name' );
		$addon           = aioseo()->addons->getAddon( 'aioseo-news-sitemap' );
		$publicationName = aioseo()->options->sitemap->news->publicationName;
		$blogName        = get_bloginfo( 'name' );

		if (
			! empty( $publicationName ) ||
			! empty( $blogName ) ||
			! $addon->isActive ||
			! aioseo()->license->isActive() ||
			$addon->requiresUpgrade
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'news-publication-name' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'news-publication-name',
			'title'             => __( 'News Sitemap Publication Name', 'aioseo-pro' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'You have not set the Google News Publication Name or the Site Title. %1$s requires at least one of these for the Google News sitemap to be valid.', 'aioseo-pro' ),
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'error',
			'level'             => [ 'pro' ],
			'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
			'button1_action'    => 'http://route#aioseo-sitemaps&aioseo-scroll=news-sitemap-publication-name&aioseo-highlight=news-sitemap-publication-name:news-sitemap',
			'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
			'button2_action'    => 'http://action#notification/news-publication-name-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Add a notice if WooCommerce is detected and not licensed or running Lite.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function wooUpsellNotice() {
		$notification = Models\Notification::getNotificationByName( 'woo-upsell' );

		if (
			! class_exists( 'WooCommerce' ) ||
			aioseo()->license->isActive()
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'woo-upsell' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'woo-upsell',
			// Translators: 1 - "WooCommerce".
			'title'             => sprintf( __( 'Advanced %1$s Support', 'aioseo-pro' ), 'WooCommerce' ),
			// Translators: 1 - "WooCommerce", 2 - The plugin short name ("AIOSEO").
			'content'           => sprintf( __( 'We have detected you are running %1$s. Upgrade to %2$s to unlock our advanced eCommerce SEO features, including SEO for Product Categories and more.', 'aioseo-pro' ), 'WooCommerce', AIOSEO_PLUGIN_SHORT_NAME . ' Pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'type'              => 'info',
			'level'             => [ 'all' ],
			// Translators: 1 - "Pro".
			'button1_label'     => sprintf( __( 'Upgrade to %1$s', 'aioseo-pro' ), 'Pro' ),
			'button1_action'    => html_entity_decode( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'pricing/', 'woo-notification-upsell-unlicensed' ) ),
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}
}