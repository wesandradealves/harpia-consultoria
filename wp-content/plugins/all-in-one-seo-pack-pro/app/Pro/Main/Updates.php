<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;
use AIOSEO\Plugin\Common\Models as CommonModels;
use AIOSEO\Plugin\Pro\Models as ProModels;

/**
 * Updater class.
 *
 * @since 4.0.0
 */
class Updates extends CommonMain\Updates {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.5
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'aioseo_loaded', [ $this, 'runPreAddonUpdates' ], 1 );
		add_action( 'aioseo_v4_migrate_post_og_image', [ $this, 'migratePostOgImage' ] );
		add_action( 'aioseo_v4_migrate_term_og_image', [ $this, 'migrateTermOgImage' ] );
		add_action( 'aioseo_v432_unschedule_objects_scan', [ $this, 'cancelDuplicateObjectsActions' ] );
	}

	/**
	 * Runs our migrations.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function runUpdates() {
		parent::runUpdates();

		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		if ( version_compare( $lastActiveVersion, '4.0.0', '>=' ) && version_compare( $lastActiveVersion, '4.0.5', '<' ) ) {
			try {
				aioseo()->core->cache->update( 'v4_migrate_post_og_image', time(), WEEK_IN_SECONDS );
				aioseo()->core->cache->update( 'v4_migrate_term_og_image', time(), WEEK_IN_SECONDS );

				as_schedule_single_action( time() + 10, 'aioseo_v4_migrate_post_og_image', [], 'aioseo' );
				as_schedule_single_action( time() + 10, 'aioseo_v4_migrate_term_og_image', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}

		if ( version_compare( $lastActiveVersion, '4.0.0', '>=' ) && version_compare( $lastActiveVersion, '4.0.6', '<' ) ) {
			$this->migratedGoogleAnalyticsToDeprecated();
		}

		if ( version_compare( $lastActiveVersion, '4.0.6', '<' ) ) {
			$this->disableTwitterUseOgDefault();
			$this->updateMaxImagePreviewDefault();
		}

		if ( version_compare( $lastActiveVersion, '4.1.5', '<' ) ) {
			$this->removeUnusedColumns();
		}

		if ( version_compare( $lastActiveVersion, '4.1.8', '<' ) ) {
			$this->migrateRemovedQaSchema();
		}

		if ( version_compare( $lastActiveVersion, '4.1.9', '<' ) ) {
			// Clear addons cache to fix the PHP warning/error with the version_compare where the minimum version key for the REST API addon didn't exist yet locally.
			aioseo()->core->cache->delete( 'addons' );
		}

		if ( version_compare( $lastActiveVersion, '4.2.4', '<' ) ) {
			$this->migrateImageSeoOptions();
		}

		if ( version_compare( $lastActiveVersion, '4.3.0', '<' ) ) {
			$this->addSearchStatisticsTables();
			aioseo()->access->addCapabilities();

			// Clear addons cache to get the new features array to check for minimum plan levels.
			aioseo()->core->cache->delete( 'addons' );
		}

		if ( version_compare( $lastActiveVersion, '4.3.2', '<' ) ) {
			$this->addOpenAiColumns();
		}

		if ( version_compare( $lastActiveVersion, '4.3.3', '<' ) ) {
			aioseo()->actionScheduler->scheduleSingle( 'aioseo_v432_unschedule_objects_scan', 30 );
		}
	}

	/**
	 * Run Pre-addon updates/migrations.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function runPreAddonUpdates() {
		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		if ( version_compare( $lastActiveVersion, AIOSEO_VERSION, '<' ) ) {
			// Re-activate the license key before addons load each time the plugin is updated.
			aioseo()->license->activate();
		}
	}

	/**
	 * Adds custom tables for V4.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addInitialCustomTablesForV4() {
		parent::addInitialCustomTablesForV4();

		$db             = aioseo()->core->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		if ( ! aioseo()->core->db->tableExists( 'aioseo_terms' ) ) {
			$tableName = $db->prefix . 'aioseo_terms';

			// Incorrect defaults are adjusted below through migrations.
			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					term_id bigint(20) unsigned NOT NULL,
					title text DEFAULT NULL,
					description text DEFAULT NULL,
					keywords mediumtext DEFAULT NULL,
					keyphrases longtext DEFAULT NULL,
					page_analysis longtext DEFAULT NULL,
					canonical_url text DEFAULT NULL,
					og_title text DEFAULT NULL,
					og_description text DEFAULT NULL,
					og_object_type varchar(64) DEFAULT 'default',
					og_image_type varchar(64) DEFAULT 'default',
					og_image_custom_url text DEFAULT NULL,
					og_image_custom_fields text DEFAULT NULL,
					og_custom_image_width int(11) DEFAULT NULL,
					og_custom_image_height int(11) DEFAULT NULL,
					og_video varchar(255) DEFAULT NULL,
					og_custom_url text DEFAULT NULL,
					og_article_section text DEFAULT NULL,
					og_article_tags text DEFAULT NULL,
					twitter_use_og tinyint(1) DEFAULT 1,
					twitter_card varchar(64) DEFAULT 'default',
					twitter_image_type varchar(64) DEFAULT 'default',
					twitter_image_custom_url text DEFAULT NULL,
					twitter_image_custom_fields text DEFAULT NULL,
					twitter_title text DEFAULT NULL,
					twitter_description text DEFAULT NULL,
					seo_score int(11) DEFAULT 0 NOT NULL,
					pillar_content tinyint(1) DEFAULT NULL,
					robots_default tinyint(1) DEFAULT 1 NOT NULL,
					robots_noindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noarchive tinyint(1) DEFAULT 0 NOT NULL,
					robots_nosnippet tinyint(1) DEFAULT 0 NOT NULL,
					robots_nofollow tinyint(1) DEFAULT 0 NOT NULL,
					robots_noimageindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noodp tinyint(1) DEFAULT 0 NOT NULL,
					robots_notranslate tinyint(1) DEFAULT 0 NOT NULL,
					robots_max_snippet int(11) DEFAULT NULL,
					robots_max_videopreview int(11) DEFAULT NULL,
					robots_max_imagepreview varchar(20) DEFAULT 'none',
					priority tinytext DEFAULT NULL,
					frequency tinytext DEFAULT NULL,
					images longtext DEFAULT NULL,
					videos longtext DEFAULT NULL,
					video_scan_date datetime DEFAULT NULL,
					tabs mediumtext DEFAULT NULL,
					local_seo longtext DEFAULT NULL,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					KEY ndx_aioseo_terms_term_id (term_id)
				) {$charsetCollate};"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Sets the default social images.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setDefaultSocialImages() {
		parent::setDefaultSocialImages();

		$siteLogo = aioseo()->helpers->getSiteLogoUrl();
		if ( $siteLogo ) {
			if ( ! aioseo()->options->social->facebook->general->defaultImageTerms ) {
				aioseo()->options->social->facebook->general->defaultImageTerms = $siteLogo;
			}
			if ( ! aioseo()->options->social->twitter->general->defaultImageTerms ) {
				aioseo()->options->social->twitter->general->defaultImageTerms = $siteLogo;
			}
		}
	}

	/**
	 * Migrates the post OG images for users between 4.0.0 and 4.0.4 again to fix a bug.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function migratePostOgImage() {
		// If the v3 migration is still running, postpone this.
		if ( aioseo()->migration->isMigrationRunning() ) {
			try {
				as_schedule_single_action( time() + 300, 'aioseo_v4_migrate_post_og_image', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}

			return;
		}

		$postsPerAction  = 200;
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'v4_migrate_post_og_image' ) );

		$postsToMigrate = aioseo()->core->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->orderBy( 'p.ID DESC' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $postsToMigrate || ! count( $postsToMigrate ) ) {
			aioseo()->core->cache->delete( 'v4_migrate_post_og_image' );

			return;
		}

		foreach ( $postsToMigrate as $post ) {
			$postMeta = aioseo()->core->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereRaw( "`pm`.`meta_key` LIKE '_aioseop_opengraph_settings'" )
				->run()
				->result();

			$aioseoPost = CommonModels\Post::getPost( $post->ID );
			$meta       = [
				'post_id' => $post->ID,
			];

			if ( ! $postMeta || ! count( $postMeta ) ) {
				$aioseoPost->set( $meta );
				$aioseoPost->save();
				continue;
			}

			foreach ( $postMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				if ( '_aioseop_opengraph_settings' !== $name ) {
					continue;
				}

				$ogMeta = aioseo()->helpers->maybeUnserialize( $value );
				if ( ! is_array( $ogMeta ) ) {
					continue;
				}

				foreach ( $ogMeta as $name => $value ) {
					if (
						'aioseop_opengraph_settings_image' !== $name ||
						(
							in_array( 'aioseop_opengraph_settings_customimg', array_keys( $ogMeta ), true ) &&
							! empty( $post->og_image_custom_url ) &&
							$post->og_image_custom_url !== $ogMeta['aioseop_opengraph_settings_customimg']
						)
					) {
						continue;
					}

					$meta['og_image_type']       = 'custom_image';
					$meta['og_image_custom_url'] = esc_url( $value );
					break;
				}
			}

			$aioseoPost->set( $meta );
			$aioseoPost->save();
		}

		if ( count( $postsToMigrate ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 10, 'aioseo_v4_migrate_post_og_image', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'v4_migrate_post_og_image' );
		}
	}

	/**
	 * Migrates the term OG images for users between 4.0.0 and 4.0.4 again to fix a bug.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function migrateTermOgImage() {
		// If the v3 migration is still running, postpone this.
		if ( aioseo()->migration->isMigrationRunning() ) {
			try {
				as_schedule_single_action( time() + 300, 'aioseo_v4_migrate_term_og_image', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}

			return;
		}

		$termsPerAction   = 200;
		$publicTaxonomies = implode( "', '", aioseo()->helpers->getPublicTaxonomies( true ) );
		$timeStarted      = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'v4_migrate_term_og_image' ) );

		$termsToMigrate = aioseo()->core->db
			->start( 'terms' . ' as t' )
			->select( 't.term_id' )
			->leftJoin( 'aioseo_terms as at', '`t`.`term_id` = `at`.`term_id`' )
			->leftJoin( 'term_taxonomy as tt', '`t`.`term_id` = `tt`.`term_id`' )
			->whereRaw( "( at.term_id IS NULL OR at.updated < '$timeStarted' )" )
			->whereRaw( "( tt.taxonomy IN ( '$publicTaxonomies' ) )" )
			->orderBy( 't.term_id DESC' )
			->limit( $termsPerAction )
			->run()
			->result();

		if ( ! $termsToMigrate || ! count( $termsToMigrate ) ) {
			aioseo()->core->cache->delete( 'v4_migrate_term_og_image' );

			return;
		}

		foreach ( $termsToMigrate as $term ) {
			$termMeta = aioseo()->core->db
				->start( 'termmeta' . ' as tm' )
				->select( '`tm`.`meta_key`, `tm`.`meta_value`' )
				->where( 'tm.term_id', $term->term_id )
				->whereRaw( "`tm`.`meta_key` LIKE '_aioseop_opengraph_settings%'" )
				->run()
				->result();

			$aioseoTerm = ProModels\Term::getTerm( $term->term_id );
			$meta       = [
				'term_id' => $term->term_id
			];

			if ( ! $termMeta || ! count( $termMeta ) ) {
				$aioseoTerm->set( $meta );
				$aioseoTerm->save();
				continue;
			}

			foreach ( $termMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				if ( '_aioseop_opengraph_settings' !== $name ) {
					continue;
				}

				$ogMeta = aioseo()->helpers->maybeUnserialize( $value );
				if ( ! is_array( $ogMeta ) ) {
					continue;
				}

				foreach ( $ogMeta as $name => $value ) {
					if (
						'aioseop_opengraph_settings_image' !== $name ||
						(
							in_array( 'aioseop_opengraph_settings_customimg', array_keys( $ogMeta ), true ) &&
							! empty( $term->og_image_custom_url ) &&
							$term->og_image_custom_url !== $ogMeta['aioseop_opengraph_settings_customimg']
						)
					) {
						continue;
					}

					$meta['og_image_type']       = 'custom_image';
					$meta['og_image_custom_url'] = esc_url( $value );
					break;
				}
			}

			$aioseoTerm->set( $meta );
			$aioseoTerm->save();
		}

		if ( count( $termsToMigrate ) === $termsPerAction ) {
			try {
				as_schedule_single_action( time() + 10, 'aioseo_v4_migrate_term_og_image', [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'v4_migrate_term_og_image' );
		}
	}


	/**
	 * Deprecate already migrated googleAnalytics.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	private function migratedGoogleAnalyticsToDeprecated() {
		$options = $this->getRawOptions();
		if (
			! empty( $options['webmasterTools'] ) &&
			! empty( $options['webmasterTools']['googleAnalytics'] )
		) {
			foreach ( $options['webmasterTools']['googleAnalytics'] as $name => $value ) {
				aioseo()->options->deprecated->webmasterTools->googleAnalytics->$name = $value;
			}
		}
	}

	/**
	 * Retrieve the raw options from the database for migration.
	 *
	 * @since 4.0.6
	 *
	 * @return array An array of options.
	 */
	private function getRawOptions() {
		// Options from the DB.
		$commonOptions = json_decode( get_option( aioseo()->options->optionsName ), true );
		if ( empty( $commonOptions ) ) {
			$commonOptions = [];
		}

		$proOptions = json_decode( get_option( aioseo()->options->optionsName . '_pro' ), true );
		if ( empty( $proOptions ) ) {
			$proOptions = [];
		}

		return array_merge_recursive( $commonOptions, $proOptions );
	}

	/**
	 * Modifes the default value of the twitter_use_og column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	protected function disableTwitterUseOgDefault() {
		parent::disableTwitterUseOgDefault();

		if ( aioseo()->core->db->tableExists( 'aioseo_terms' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_terms';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY twitter_use_og tinyint(1) DEFAULT 0"
			);
		}
	}

	/**
	 * Modifes the default value of the robots_max_imagepreview column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	protected function updateMaxImagePreviewDefault() {
		parent::updateMaxImagePreviewDefault();

		if ( aioseo()->core->db->tableExists( 'aioseo_terms' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_terms';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY robots_max_imagepreview varchar(20) DEFAULT 'large'"
			);
		}
	}

	/**
	 * Deletes duplicate records in our custom tables.
	 *
	 * @since 4.0.13
	 *
	 * @return void
	 */
	public function removeDuplicateRecords() {
		parent::removeDuplicateRecords();

		$duplicates = aioseo()->core->db->start( 'aioseo_terms' )
			->select( 'term_id, min(id) as id' )
			->groupBy( 'term_id having count(term_id) > 1' )
			->orderBy( 'count(term_id) DESC' )
			->run()
			->result();

		if ( empty( $duplicates ) ) {
			return;
		}

		foreach ( $duplicates as $duplicate ) {
			$termId        = $duplicate->term_id;
			$firstRecordId = $duplicate->id;

			aioseo()->core->db->delete( 'aioseo_terms' )
				->whereRaw( "( id > $firstRecordId AND term_id = $termId )" )
				->run();
		}
	}

	/**
	 * Adds the new capabilities for all the roles.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function accessControlNewCapabilities() {
		$newCapabilities = [
			'dashboard',
			'setupWizard'
		];

		$options        = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		foreach ( aioseo()->access->getRoles() as $role ) {
			foreach ( $newCapabilities as $capability ) {
				if ( $options->accessControl->has( $role ) ) {
					$default = $options->accessControl->$role->getDefault( $capability );
					$options->accessControl->$role->$capability = $default;
				}

				if ( $dynamicOptions->accessControl->has( $role ) ) {
					$default = $dynamicOptions->accessControl->$role->getDefault( $capability );
					$dynamicOptions->accessControl->$role->$capability = $default;
				}
			}
		}

		aioseo()->access->addCapabilities();
	}

	/**
	 * Migrate dynamic settings to a separate options structure.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function migrateDynamicSettings() {
		parent::migrateDynamicSettings();

		$rawOptions = $this->getRawOptions();
		$options    = aioseo()->dynamicOptions->noConflict();

		// Facebook post type object types.
		if (
			! empty( $rawOptions['social']['faceboook']['general']['dynamic']['taxonomies'] )
		) {
			foreach ( $rawOptions['social']['faceboook']['general']['dynamic']['taxonomies'] as $taxonomyName => $data ) {
				if ( $options->social->facebook->general->taxonomies->has( $taxonomyName ) ) {
					$options->social->facebook->general->taxonomies->$taxonomyName->objectType = $data['objectType'];
				}
			}
		}

		// Breadcrumbs post type templates.
		if (
			! empty( $rawOptions['breadcrumbs']['dynamic']['postTypes'] )
		) {
			foreach ( $rawOptions['breadcrumbs']['dynamic']['postTypes'] as $postTypeName => $data ) {
				if ( $options->breadcrumbs->postTypes->has( $postTypeName ) ) {
					$options->breadcrumbs->postTypes->$postTypeName->useDefaultTemplate = $data['useDefaultTemplate'];
					$options->breadcrumbs->postTypes->$postTypeName->taxonomy           = $data['taxonomy'];
					$options->breadcrumbs->postTypes->$postTypeName->showArchiveCrumb   = $data['showArchiveCrumb'];
					$options->breadcrumbs->postTypes->$postTypeName->showTaxonomyCrumbs = $data['showTaxonomyCrumbs'];
					$options->breadcrumbs->postTypes->$postTypeName->showHomeCrumb      = $data['showHomeCrumb'];
					$options->breadcrumbs->postTypes->$postTypeName->showPrefixCrumb    = $data['showPrefixCrumb'];
					$options->breadcrumbs->postTypes->$postTypeName->showParentCrumbs   = $data['showParentCrumbs'];
					$options->breadcrumbs->postTypes->$postTypeName->template           = $data['template'];

					if ( ! empty( $data['parentTemplate'] ) ) {
						$options->breadcrumbs->postTypes->$postTypeName->parentTemplate = $data['parentTemplate'];
					}
				}
			}
		}

		// Breadcrumbs taxonomy templates.
		if (
			! empty( $rawOptions['breadcrumbs']['dynamic']['taxonomies'] )
		) {
			foreach ( $rawOptions['breadcrumbs']['dynamic']['taxonomies'] as $taxonomyName => $data ) {
				if ( $options->breadcrumbs->taxonomies->has( $taxonomyName ) ) {
					$options->breadcrumbs->taxonomies->$taxonomyName->useDefaultTemplate = $data['useDefaultTemplate'];
					$options->breadcrumbs->taxonomies->$taxonomyName->showHomeCrumb      = $data['showHomeCrumb'];
					$options->breadcrumbs->taxonomies->$taxonomyName->showPrefixCrumb    = $data['showPrefixCrumb'];
					$options->breadcrumbs->taxonomies->$taxonomyName->showParentCrumbs   = $data['showParentCrumbs'];
					$options->breadcrumbs->taxonomies->$taxonomyName->template           = $data['template'];

					if ( ! empty( $data['parentTemplate'] ) ) {
						$options->breadcrumbs->taxonomies->$taxonomyName->parentTemplate = $data['parentTemplate'];
					}
				}
			}
		}

		// Access control settings.
		if (
			! empty( $rawOptions['accessControl']['dynamic'] )
		) {
			foreach ( $rawOptions['accessControl']['dynamic'] as $role => $data ) {
				if ( $options->accessControl->has( $role ) ) {
					$options->accessControl->$role->useDefault               = $data['useDefault'];
					$options->accessControl->$role->dashboard                = $data['dashboard'];
					$options->accessControl->$role->generalSettings          = $data['generalSettings'];
					$options->accessControl->$role->searchAppearanceSettings = $data['searchAppearanceSettings'];
					$options->accessControl->$role->socialNetworksSettings   = $data['socialNetworksSettings'];
					$options->accessControl->$role->sitemapSettings          = $data['sitemapSettings'];
					$options->accessControl->$role->redirectsSettings        = $data['redirectsSettings'];
					$options->accessControl->$role->seoAnalysisSettings      = $data['seoAnalysisSettings'];
					$options->accessControl->$role->toolsSettings            = $data['toolsSettings'];
					$options->accessControl->$role->featureManagerSettings   = $data['featureManagerSettings'];
					$options->accessControl->$role->pageAnalysis             = $data['pageAnalysis'];
					$options->accessControl->$role->pageGeneralSettings      = $data['pageGeneralSettings'];
					$options->accessControl->$role->pageAdvancedSettings     = $data['pageAdvancedSettings'];
					$options->accessControl->$role->pageSchemaSettings       = $data['pageSchemaSettings'];
					$options->accessControl->$role->pageSocialSettings       = $data['pageSocialSettings'];
					$options->accessControl->$role->localSeoSettings         = $data['localSeoSettings'];
					$options->accessControl->$role->pageLocalSeoSettings     = $data['pageLocalSeoSettings'];
					$options->accessControl->$role->setupWizard              = $data['setupWizard'];
				}
			}
		}
	}

	/**
	 * Removes a number of columns that Terms do not use.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	private function removeUnusedColumns() {
		$columnsToRemove = [
			'pillar_content',
			'seo_score',
			'keyphrases',
			'page_analysis'
		];

		foreach ( $columnsToRemove as $columnName ) {
			if ( aioseo()->core->db->columnExists( 'aioseo_terms', $columnName ) ) {
				$tableName = aioseo()->core->db->db->prefix . 'aioseo_terms';
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName}
					DROP COLUMN $columnName"
				);
			}
		}
	}

	/**
	 * Add in image width/height columns and image URL for caching.
	 *
	 * @since 4.1.6
	 *
	 * @return void
	 */
	protected function migrateOgTwitterImageColumns() {
		parent::migrateOgTwitterImageColumns();

		if ( aioseo()->core->db->tableExists( 'aioseo_terms' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_terms';

			// OG Columns.
			if ( ! aioseo()->core->db->columnExists( 'aioseo_terms', 'og_image_url' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_url text DEFAULT NULL AFTER og_image_type"
				);
			}

			if ( aioseo()->core->db->columnExists( 'aioseo_terms', 'og_custom_image_height' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} CHANGE COLUMN og_custom_image_height og_image_height int(11) DEFAULT NULL AFTER og_image_url"
				);
			} elseif ( ! aioseo()->core->db->columnExists( 'aioseo_terms', 'og_image_height' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_height int(11) DEFAULT NULL AFTER og_image_url"
				);
			}

			if ( aioseo()->core->db->columnExists( 'aioseo_terms', 'og_custom_image_width' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} CHANGE COLUMN og_custom_image_width og_image_width int(11) DEFAULT NULL AFTER og_image_url"
				);
			} elseif ( ! aioseo()->core->db->columnExists( 'aioseo_terms', 'og_image_width' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_width int(11) DEFAULT NULL AFTER og_image_url"
				);
			}

			// Twitter image url columnn.
			if ( ! aioseo()->core->db->columnExists( 'aioseo_terms', 'twitter_image_url' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD twitter_image_url text DEFAULT NULL AFTER twitter_image_type"
				);
			}

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Migrates the removed QAPage schema if it's used anywhere as a default.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	private function migrateRemovedQaSchema() {
		$searchAppearance = aioseo()->dynamicOptions->searchAppearance->all();
		$postTypes        = array_keys( $searchAppearance['postTypes'] );

		foreach ( $postTypes as $postType ) {
			if ( 'qapage' === strtolower( aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->webPageType ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->webPageType = 'WebPage';
			}
		}
	}

	/**
	 * Remove the tabs column as it is unnecessary.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	protected function removeTabsColumn() {
		parent::removeTabsColumn();

		if ( aioseo()->core->db->columnExists( 'aioseo_terms', 'tabs' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_terms';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				DROP tabs"
			);
		}
	}

	/**
	 * Migrates the old Image SEO options to the new structure.
	 *
	 * @since 4.2.4
	 *
	 * @return void
	 */
	public function migrateImageSeoOptions() {
		$image            = aioseo()->options->image->all();
		$format           = ! empty( $image['format'] ) ? $image['format'] : '';
		$stripPunctuation = ! empty( $image['stripPunctuation'] ) ? $image['stripPunctuation'] : '';

		if ( $format ) {
			foreach ( $format as $attribute => $option ) {
				aioseo()->options->image->{$attribute}->format = $option;
			}
		}

		if ( $stripPunctuation ) {
			foreach ( $stripPunctuation as $attribute => $option ) {
				aioseo()->options->image->{$attribute}->stripPunctuation = $option;
			}
		}

		// If the user was already actively using the Image SEO addon, disable the upload settings.
		if ( ! aioseo()->addons->getLoadedAddon( 'imageSeo' ) ) {
			return;
		}

		aioseo()->options->image->caption->autogenerate     = false;
		aioseo()->options->image->description->autogenerate = false;
	}

	/**
	 * Adds the tables for Search Statistics.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function addSearchStatisticsTables() {
		$db             = aioseo()->core->db->db;
		$charsetCollate = '';
		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		// Check for search_statistics_objects table.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_search_statistics_objects' ) ) {
			$tableName = $db->prefix . 'aioseo_search_statistics_objects';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL auto_increment,
					object_id bigint(20) unsigned NOT NULL,
					object_type varchar(100) NOT NULL,
					object_subtype varchar(100) NOT NULL,
					object_path varchar(500) NOT NULL,
					object_path_hash varchar(40) NOT NULL,
					seo_score int(11) unsigned NOT NULL,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					KEY ndx_aioseo_object_id1 (object_id),
					KEY ndx_aioseo_object_path_hash1 (object_path_hash)
				) {$charsetCollate};"
			);
		}
	}

	/**
	 * Adds the post column for the OpenAI data.
	 *
	 * @since 4.3.2
	 *
	 * @return void
	 */
	private function addOpenAiColumns() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'open_ai' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD open_ai mediumtext DEFAULT NULL AFTER options"
			);

			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Cancels all duplicate aioseo_search_statistics_objects_scan actions.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function cancelDuplicateObjectsActions() {
		as_unschedule_all_actions( 'aioseo_search_statistics_objects_scan' );
	}
}