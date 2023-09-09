<?php
namespace AIOSEO\Plugin\Pro\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Pro class.
 *
 * @since 4.2.5
 */
class NetworkAdmin extends CommonAdmin\NetworkAdmin {
	use Traits\Admin;

	/**
	 * Construct method.
	 *
	 * @since 4.2.5
	 */
	public function __construct() {
		parent::__construct();

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (
			is_network_admin() &&
			! is_plugin_active_for_network( plugin_basename( AIOSEO_FILE ) )
		) {
			return;
		}

		add_action( 'network_site_new_form', [ $this, 'newSiteForm' ] );
		add_action( 'wp_initialize_site', [ $this, 'cloneSeoSettings' ], 1000 );
		add_filter( 'wpmu_drop_tables', [ $this, 'dropTables' ] );
	}

	/**
	 * Add the network menu inside of WordPress.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function addNetworkMenu() {
		parent::addNetworkMenu();

		// We use the global submenu, because we are adding an external link here.
		if ( current_user_can( 'aioseo_manage_seo' ) && ! aioseo()->networkLicense->isActive() ) {
			global $submenu;
			$submenu[ $this->pageSlug ][] = [
				'<span class="aioseo-menu-highlight red">' . esc_html__( 'Add License Key', 'aioseo-pro' ) . '</span>',
				apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' ),
				esc_url( network_admin_url( 'admin.php?page=aioseo-settings' ) )
			];
		}
	}

	/**
	 * Add options to the network new site form.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function newSiteForm() {
		aioseo()->templates->getTemplate( 'admin/add-network-site.php' );
	}

	/**
	 * When the form is submitted, check if we should clone SEO settings for the new site.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_Site $site The new site.
	 * @return void
	 */
	public function cloneSeoSettings( $site ) {
		// A few preliminary failsafes.
		if (
			! isset( $_REQUEST['action'] ) ||
			'add-site' !== $_REQUEST['action'] ||
			empty( $_POST['aioseo-import-site'] )
		) {
			return;
		}

		// Copied from the add new site settings.
		check_admin_referer( 'add-blog', '_wpnonce_add-blog' );

		// Get the blog ID of the site we want to import data from.
		$seoSettingsBlogId = intval( wp_unslash( $_POST['aioseo-import-site'] ) );

		// Switch to the blog to grab the options from.
		aioseo()->helpers->switchToBlog( $seoSettingsBlogId );

		// Grab the options.
		$options        = aioseo()->options->all();
		$dynamicOptions = aioseo()->dynamicOptions->all();

		// Switch to the new site.
		aioseo()->helpers->restoreCurrentBlog();
		aioseo()->helpers->switchToBlog( (int) $site->blog_id );

		// Save the options!
		aioseo()->options->sanitizeAndSave( $options );
		aioseo()->dynamicOptions->sanitizeAndSave( $dynamicOptions );

		// Switch back to the original network one before going on.
		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Drop blog tables when a blog is deleted.
	 *
	 * @since 4.2.5
	 *
	 * @param  array $tables An array of WP tables to drop.
	 * @return array         An array of tables (including our own).
	 */
	public function dropTables( $tables ) {
		$tables = array_merge( aioseo()->core->getDbTables(), $tables );

		return $tables;
	}

	/**
	 * Outputs the element we can mount our footer promotion standalone Vue app on.
	 * In Pro we do nothing.
	 *
	 * @since 4.3.6
	 *
	 * @return void
	 */
	public function addFooterPromotion() {}
}