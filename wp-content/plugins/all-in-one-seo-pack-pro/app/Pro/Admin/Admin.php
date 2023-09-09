<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;
use AIOSEO\Plugin\Pro\Models;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Admin extends CommonAdmin\Admin {
	use Traits\Admin;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'aioseo_unslash_escaped_data_terms', [ $this, 'unslashEscapedDataTerms' ] );

		// This needs to run outside of the early return for ajax / cron requests in order for our updates
		// to work on bulk update requests.
		add_action( 'plugins_loaded', [ $this, 'loadUpdates' ] );
	}

	/**
	 * Actually adds the menu items to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addAdminBarMenuItems() {
		// Add an upsell to Pro.
		if ( current_user_can( 'update_plugins' ) && ! aioseo()->license->isActive() ) {
			$this->adminBarMenuItems['aioseo-pro-license'] = [
				'parent' => 'aioseo-main',
				'title'  => '<span class="aioseo-menu-highlight red">' . __( 'Add License Key', 'aioseo-pro' ) . '</span>',
				'id'     => 'aioseo-pro-license',
				'href'   => esc_url( admin_url( 'admin.php?page=aioseo-settings' ) )
			];
		}

		parent::addAdminBarMenuItems();
	}

	/**
	 * Get the required capability for given admin page.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $pageSlug The slug of the page.
	 * @return string           The required capability.
	 */
	public function getPageRequiredCapability( $pageSlug ) {
		$capabilityList = aioseo()->access->getCapabilityList();

		switch ( $pageSlug ) {
			case 'aioseo':
				$capability = 'aioseo_dashboard';
				break;
			case 'aioseo-settings':
				$capability = 'aioseo_general_settings';
				break;
			case 'aioseo-sitemaps':
				$capability = 'aioseo_sitemap_settings';
				break;
			case 'aioseo-about':
				$capability = 'aioseo_about_us_page';
				break;
			case 'aioseo-setup-wizard':
				$capability = 'aioseo_setup_wizard';
				break;
			case 'aioseo-search-statistics':
				$capability = 'aioseo_search_statistics_settings';
				break;
			case 'aioseo-redirects':
				$capability = current_user_can( 'aioseo_redirects_manage' ) ? 'aioseo_redirects_manage' : 'aioseo_redirects_settings';
				break;
			default:
				$capability = str_replace( '-', '_', $pageSlug . '-settings' );
				break;
		}

		if ( ! in_array( $capability, $capabilityList, true ) ) {
			$capability = apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );
		}

		return $capability;
	}

	/**
	 * Add the menu inside of WordPress.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addMenu() {
		parent::addMenu();

		// We use the global submenu, because we are adding an external link here.
		if ( current_user_can( 'aioseo_manage_seo' ) && ! aioseo()->license->isActive() ) {
			global $submenu;
			$submenu[ $this->pageSlug ][] = [
				'<span class="aioseo-menu-highlight red">' . esc_html__( 'Add License Key', 'aioseo-pro' ) . '</span>',
				apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
				esc_url( admin_url( 'admin.php?page=aioseo-settings' ) )
			];
		}
	}

	/**
	 * Update checks.
	 * This does user permission checks so we have to run it after plugins loaded.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function loadUpdates() {
		new Updates( [
			'pluginSlug' => 'all-in-one-seo-pack-pro',
			'pluginPath' => plugin_basename( AIOSEO_FILE ),
			'version'    => AIOSEO_VERSION,
			'key'        => aioseo()->options->general->licenseKey
		] );
	}

	/**
	 * Adds All in One SEO to the Admin Bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminBarMenu() {
		if ( aioseo()->options->advanced->adminBarMenu ) {
			parent::adminBarMenu();
		}
	}

	/**
	 * Adds the current post/term menu items to the admin bar.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	protected function addEditSeoMenuItem() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			parent::addEditSeoMenuItem();

			return;
		}

		$term = get_queried_object();
		if ( empty( $term ) ) {
			return;
		}

		$this->adminBarMenuItems[] = [
			'id'     => 'aioseo-edit-' . $term->term_id,
			'parent' => 'aioseo-main',
			'title'  => esc_html__( 'Edit SEO', 'aioseo-pro' ),
			'href'   => get_edit_term_link( $term->term_id, $term->taxonomy ) . '#aioseo-tabbed',
		];
	}

	/**
	 * Hooks for loading our pages.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function hooks() {
		parent::hooks();

		$currentScreen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		global $admin_page_hooks;

		if ( ! is_object( $currentScreen ) || empty( $currentScreen->id ) || empty( $admin_page_hooks ) ) {
			return;
		}

		$addScripts = false;
		if ( 'toplevel_page_aioseo' === $currentScreen->id ) {
			$addScripts = true;
		}

		if ( ! empty( $admin_page_hooks['aioseo'] ) && $currentScreen->id === $admin_page_hooks['aioseo'] ) {
			$addScripts = true;
		}

		if ( strpos( $currentScreen->id, 'aioseo-tools' ) !== false ) {
			$addScripts = true;
		}

		if ( ! $addScripts ) {
			return;
		}
	}

	/**
	 * Starts the cleaning procedure to fix escaped, corrupted data.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function scheduleUnescapeData() {
		parent::scheduleUnescapeData();

		aioseo()->core->cache->update( 'unslash_escaped_data_terms', time(), WEEK_IN_SECONDS );
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_unslash_escaped_data_terms', 120 );
	}

	/**
	 * Unlashes corrupted escaped data in terms.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function unslashEscapedDataTerms() {
		$termsToUnslash = apply_filters( 'aioseo_debug_unslash_escaped_terms', 200 );
		$timeStarted    = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'unslash_escaped_data_terms' ) );

		$terms = aioseo()->core->db->start( 'aioseo_terms' )
			->select( '*' )
			->whereRaw( "updated < '$timeStarted'" )
			->orderBy( 'updated ASC' )
			->limit( $termsToUnslash )
			->run()
			->result();

		if ( empty( $terms ) ) {
			aioseo()->core->cache->delete( 'unslash_escaped_data_terms' );

			return;
		}

		aioseo()->actionScheduler->scheduleSingle( 'aioseo_unslash_escaped_data_terms', 120, [], true );

		$postExclusiveColumns = [
			'keyphrases',
			'page_analysis',
			'schema_type_options',
			'local_seo'
		];

		$columns = array_diff( $this->getColumnsToUnslash(), $postExclusiveColumns );

		foreach ( $terms as $term ) {
			$aioseoTerm = Models\Term::getTerm( $term->term_id );
			foreach ( $columns as $columnName ) {
				$aioseoTerm->$columnName = aioseo()->helpers->pregReplace( '/\\\(?![uU][+]?[a-zA-Z0-9]{4})/', '', $term->$columnName );
			}
			$aioseoTerm->images          = null;
			$aioseoTerm->image_scan_date = null;
			$aioseoTerm->videos          = null;
			$aioseoTerm->video_scan_date = null;
			$aioseoTerm->save();
		}
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function loadTextDomain() {
		parent::loadTextDomain();
		aioseo()->helpers->loadTextDomain( 'aioseo-pro' );
	}
}