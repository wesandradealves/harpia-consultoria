<?php
namespace AIOSEO\Plugin\Pro\Options;

use AIOSEO\Plugin\Common\Options as CommonOptions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DynamicBackup extends CommonOptions\DynamicBackup {
	/**
	 * The custom roles.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	protected $customRoles = [];

	/**
	 * Checks whether data from the backup has to be restored.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->customRoles = aioseo()->helpers->getCustomRoles();

		$this->restoreRoles();
	}

	/**
	 * Restores the dynamic Roles options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	private function restoreRoles() {
		foreach ( $this->customRoles as $customRoleName => $customRoleLabel ) {
			// Restore the roles.
			if ( ! empty( $this->backup['roles'][ $customRoleName ] ) ) {
				$this->restoreOptions( $this->backup['roles'][ $customRoleName ], [ 'accessControl', $customRoleName ] );
				unset( $this->backup['roles'][ $customRoleName ] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Post Types options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restorePostTypes() {
		parent::restorePostTypes();

		foreach ( $this->postTypes as $postType ) {
			// Restore the post types for Breadcrumbs.
			if ( ! empty( $this->backup['postTypes'][ $postType ]['breadcrumbs'] ) ) {
				$this->restoreOptions( $this->backup['postTypes'][ $postType ]['breadcrumbs'], [ 'breadcrumbs', 'postTypes', $postType ] );
				unset( $this->backup['postTypes'][ $postType ]['breadcrumbs'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Taxonomies options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restoreTaxonomies() {
		parent::restoreTaxonomies();

		foreach ( $this->taxonomies as $taxonomy ) {
			// Restore the taxonomies for Social Networks.
			if ( ! empty( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'], [ 'social', 'facebook', 'general', 'taxonomies', $taxonomy ] );
				unset( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'] );
				$this->shouldBackup = true;
			}

			// Restore the taxonomies for Breadcrumbs.
			if ( ! empty( $this->backup['taxonomies'][ $taxonomy ]['breadcrumbs'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $taxonomy ]['breadcrumbs'], [ 'breadcrumbs', 'taxonomies', $taxonomy ] );
				unset( $this->backup['taxonomies'][ $taxonomy ]['breadcrumbs'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Archives options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restoreArchives() {
		parent::restoreArchives();

		foreach ( $this->archives as $postType ) {
			// Restore the archives for Breadcrumbs.
			if ( ! empty( $this->backup['archives'][ $postType ]['breadcrumbs'] ) ) {
				$this->restoreOptions( $this->backup['archives'][ $postType ]['breadcrumbs'], [ 'searchAppearance', 'archives', $postType ] );
				unset( $this->backup['archives'][ $postType ]['breadcrumbs'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the options if it has disappeared.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	public function maybeBackup( $newOptions ) {
		parent::maybeBackup( $newOptions );

		$this->maybeBackupRoles( $newOptions['accessControl'] );
	}

	/**
	 * Maybe backup the roles.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $dynamicRoles An array of dynamic roles to check.
	 * @return void
	 */
	private function maybeBackupRoles( $dynamicRoles ) {
		// Remove the skipped roles.
		$roles = apply_filters( 'aioseo_access_control_excluded_roles', array_merge( [
			'subscriber'
		], aioseo()->helpers->isWooCommerceActive() ? [ 'customer' ] : [] ) );

		foreach ( $roles as $role ) {
			if ( array_key_exists( $role, $dynamicRoles ) ) {
				unset( $dynamicRoles[ $role ] );
			}
		}

		$missing = [];
		foreach ( $dynamicRoles as $role => $data ) {
			if ( empty( $this->customRoles[ $role ] ) ) {
				$missing[ $role ] = $data;
			}
		}

		foreach ( $missing as $roleName => $roleSettings ) {
			$this->backup['roles'][ $roleName ] = aioseo()->dynamicOptions->convertOptionsToValues( $roleSettings, 'value' );
			$this->shouldBackup = true;
		}
	}

	/**
	 * Maybe backup the Post Types.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	protected function maybeBackupPostType( $newOptions ) {
		parent::maybeBackupPostType( $newOptions );

		// Maybe backup the post types for Breadcrumbs.
		foreach ( $newOptions['breadcrumbs']['postTypes'] as $dynamicPostTypeName => $dynamicPostTypeSettings ) {
			$found = in_array( $dynamicPostTypeName, $this->postTypes, true );
			if ( ! $found ) {
				$this->backup['postTypes'][ $dynamicPostTypeName ]['breadcrumbs'] = $dynamicPostTypeSettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Taxonomies.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $dynamicRoles An array of dynamic roles to check.
	 * @return void
	 */
	protected function maybeBackupTaxonomy( $newOptions ) {
		parent::maybeBackupTaxonomy( $newOptions );

		// Maybe backup the taxonomies for Social Networks.
		foreach ( $newOptions['social']['facebook']['general']['taxonomies'] as $dynamicTaxonomyName => $dynamicTaxonomySettings ) {
			$found = in_array( $dynamicTaxonomyName, $this->taxonomies, true );
			if ( ! $found ) {
				$this->backup['taxonomies'][ $dynamicTaxonomyName ]['social']['facebook'] = $dynamicTaxonomySettings;
				$this->shouldBackup = true;
			}
		}

		// Maybe backup the taxonomies for Breadcrumbs.
		foreach ( $newOptions['breadcrumbs']['taxonomies'] as $dynamicTaxonomyName => $dynamicTaxonomySettings ) {
			$found = in_array( $dynamicTaxonomyName, $this->taxonomies, true );
			if ( ! $found ) {
				$this->backup['taxonomies'][ $dynamicTaxonomyName ]['breadcrumbs'] = $dynamicTaxonomySettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Archives.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	protected function maybeBackupArchives( $newOptions ) {
		parent::maybeBackupArchives( $newOptions );

		// Maybe backup the archives for Breadcrumbs.
		foreach ( $newOptions['breadcrumbs']['archives']['postTypes'] as $archiveName => $archiveSettings ) {
			$found = in_array( $archiveName, $this->archives, true );
			if ( ! $found ) {
				$this->backup['archives'][ $archiveName ]['breadcrumbs'] = $archiveSettings;
				$this->shouldBackup = true;
			}
		}
	}
}