<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

use AIOSEO\Plugin\Common\Migration as CommonMigration;
use AIOSEO\Plugin\Common\Models;

class Migration extends CommonMigration\Migration {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->meta    = new Meta();
		$this->helpers = new CommonMigration\Helpers();

		// NOTE: These need to go above the is_admin check in order for them to run at all.
		add_action( 'aioseo_migrate_post_meta', [ $this->meta, 'migratePostMeta' ] );
		add_action( 'aioseo_migrate_term_meta', [ $this->meta, 'migrateTermMeta' ] );
		add_action( 'aioseo_regenerate_video_sitemap', [ $this, 'regenerateSitemap' ] );

		if ( ! is_admin() ) {
			return;
		}

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ], 2000 );
	}

	/**
	 * Starts the migration.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function doMigration() {
		// If our tables do not exist, create them now.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_terms' ) ) {
			aioseo()->updates->addInitialCustomTablesForV4();
		}

		$this->oldOptions = ( new OldOptions() )->oldOptions;

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		update_option( 'aioseo_options_v3', $this->oldOptions );

		aioseo()->core->cache->update( 'v3_migration_in_progress_posts', time(), WEEK_IN_SECONDS );
		aioseo()->core->cache->update( 'v3_migration_in_progress_terms', time(), WEEK_IN_SECONDS );

		$this->migrateSettings();
		$this->meta->migrateMeta();
	}

	/**
	 * Reruns the post meta migration.
	 *
	 * This is meant for users on v4.0.0, v4.0.1 or v4.0.2 where the migration might have failed.
	 *
	 * @since 4.0.3
	 *
	 * @return void
	 */
	public function redoMetaMigration() {
		aioseo()->core->cache->update( 'v3_migration_in_progress_posts', time(), WEEK_IN_SECONDS );
		aioseo()->core->cache->update( 'v3_migration_in_progress_terms', time(), WEEK_IN_SECONDS );
		$this->meta->migrateMeta();
	}

	/**
	 * Migrates the plugin settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $oldOptions The old options. We pass it in directly via the Importer/Exporter.
	 * @return void
	 */
	public function migrateSettings( $oldOptions = [] ) {
		parent::migrateSettings( $oldOptions );

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		// Note: This only works at the local site level. If there is enough interest, we can also move this to the network level.
		if ( ! empty( $this->oldOptions['aiosp_license_key'] ) ) {
			aioseo()->options->general->licenseKey = trim( $this->oldOptions['aiosp_license_key'] );
			aioseo()->license->activate();
		}

		aioseo()->core->cache->update( 'v3_migration_in_progress_settings', time() );

		$addons = aioseo()->addons->getAddons( true );
		foreach ( $addons as $addon ) {
			if ( aioseo()->license->isAddonAllowed( $addon->sku ) ) {
				switch ( $addon->sku ) {
					case 'aioseo-image-seo':
						if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_image_seo'] ) ) {
							$this->installAddon( $addon );
						}
						break;
					case 'aioseo-video-sitemap':
						if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_video_sitemap'] ) ) {
							$this->installAddon( $addon );
						}
						break;
					case 'aioseo-news-sitemap':
						if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) ) {
							$this->installAddon( $addon );
						}
						break;
					case 'aioseo-local-business':
						if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_schema_local_business'] ) ) {
							$this->installAddon( $addon );
						}
						break;
					default:
						break;
				}
			}
		}

		new GeneralSettings();

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) ) {
			new Sitemap();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_opengraph'] ) ) {
			$this->migrateSocialTermImageSettings();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_video_sitemap'] ) ) {
			new VideoSitemap();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_image_seo'] ) ) {
			new ImageSeo();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_schema_local_business'] ) ) {
			new LocalBusiness();
		}

		aioseo()->core->cache->delete( 'v3_migration_in_progress_settings' );
	}

	/**
	 * Only regenerate the sitemap. Since this is run on a cron, we need to add another filter.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function regenerateSitemap() {
		add_action( 'wp_loaded', [ $this, 'startRegenerate' ] );
	}

	/**
	 * Start regenerating the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function startRegenerate() {
		$this->oldOptions = ( new OldOptions( [] ) )->oldOptions;

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		new VideoSitemap( true );
	}

	/**
	 * Migrates the Feature Manager settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateFeatureManager() {
		parent::migrateFeatureManager();

		if ( empty( $this->oldOptions['modules']['aiosp_feature_manager_options'] ) ) {
			return;
		}

		if ( empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_video_sitemap'] ) ) {
			aioseo()->options->sitemap->video->enable = false;
		}
	}

	/**
	 * Migrates the Open Graph default terms images.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSocialTermImageSettings() {
		if (
			! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_defimg'] ) &&
			! in_array( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_defimg'], [ 'featured', 'attach', 'content', 'author', 'auto' ], true )
		) {
			aioseo()->options->social->facebook->general->defaultImageSourceTerms =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_defimg'] );
			aioseo()->options->social->twitter->general->defaultImageSourceTerms  =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_defimg'] );
		}

		if (
			! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimg'] ) &&
			! preg_match( '/default-user-image.png$/', $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimg'] )
		) {
			aioseo()->options->social->facebook->general->defaultImageTerms = esc_url( wp_strip_all_tags( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimg'] ) );
			aioseo()->options->social->twitter->general->defaultImageTerms  = esc_url( wp_strip_all_tags( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimg'] ) );
		} else {
			aioseo()->options->social->facebook->general->defaultImageTerms = '';
			aioseo()->options->social->twitter->general->defaultImageTerms  = '';
		}

		if (
			! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimgwidth'] ) ||
			! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimgheight'] )
		) {
			aioseo()->options->social->facebook->general->defaultImageWidthTerms  =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimgwidth'] );
			aioseo()->options->social->facebook->general->defaultImageHeightTerms =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_dimgheight'] );
		}

		if ( ! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_meta_key'] ) ) {
			aioseo()->options->social->facebook->general->customFieldImageTerms =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_meta_key'] );
			aioseo()->options->social->twitter->general->customFieldImageTerms =
				aioseo()->helpers->sanitizeOption( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_meta_key'] );
		}
	}

	/**
	 * Installs a given addon.
	 *
	 * @since 4.0.0
	 *
	 * @param  Object $addon The addon object.
	 * @return void
	 */
	private function installAddon( $addon ) {
		if ( aioseo()->addons->canInstall() ) {
			$name = ! empty( $addon->basename ) ? $addon->basename : $addon->sku;
			aioseo()->addons->installAddon( $name, is_multisite() );
		} else {
			$notification = Models\Notification::getNotificationByName( 'install-' . $addon->sku );
			if ( ! $notification->exists() ) {
				Models\Notification::addNotification( [
					'slug'              => uniqid(),
					'notification_name' => 'install-' . $addon->sku,
					'title'             => sprintf(
						// Translators: 1 - The addon or plugin name.
						__( 'Install %1$s', 'aioseo-pro' ),
						$addon->name
					),
					'content'           => sprintf(
						// Translators: 1 - The addon name, 2 - The plugin short name ("AIOSEO").
						__( 'You previously had the %1$s module active in a previous version of %2$s. While trying to migrate, we ran into an issue with installing the new addon. Click below to manually install.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						$addon->name,
						AIOSEO_PLUGIN_SHORT_NAME
					),
					'type'              => 'error',
					'level'             => [ 'all' ],
					'button1_label'     => sprintf(
						// Translators: 1 - The addon or plugin name.
						__( 'Install %1$s', 'aioseo-pro' ),
						$addon->name
					),
					'button1_action'    => html_entity_decode( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'account/downloads/', 'migration-' . $addon->sku, 'cant-install-addons' ) ),
					'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
					'button2_action'    => 'http://action#notification/install-addons-reminder',
					'start'             => gmdate( 'Y-m-d H:i:s' )
				] );
			}
		}
	}

	/**
	 * Checks whether the V3 migration is running.
	 *
	 * @since 4.1.8
	 *
	 * @return bool Whether the V3 migration is running.
	 */
	public function isMigrationRunning() {
		return parent::isMigrationRunning() || aioseo()->core->cache->get( 'v3_migration_in_progress_terms' );
	}
}