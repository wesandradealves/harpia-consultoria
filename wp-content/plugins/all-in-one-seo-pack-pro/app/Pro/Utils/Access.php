<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Manages capabilities for our users.
 *
 * @since 4.0.0
 */
class Access extends CommonUtils\Access {
	/**
	 * List of allowed options for each capability.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $capabilityOptions = [
		'aioseo_general_settings'           => [ 'general', 'webmasterTools', 'rssContent', 'advanced', 'breadcrumbs' ],
		'aioseo_search_appearance_settings' => [ 'searchAppearance', 'image' ],
		'aioseo_social_networks_settings'   => [ 'social' ],
		'aioseo_sitemap_settings'           => [ 'sitemap' ],
		'aioseo_redirects_settings'         => [ 'redirects' ],
		'aioseo_tools_settings'             => [ 'tools' ],
		'aioseo_local_seo_settings'         => [ 'localBusiness' ],
		'aioseo_admin'                      => [ 'accessControl' ],
	];

	/**
	 * List of allowed page options for each capability.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $capabilityPage = [
		'aioseo_page_analysis'           => [
			'page_analysis',
		],
		'aioseo_page_general_settings'   => [
			'title',
			'description',
			'keyphrases',
			'keywords',
			'pillar_content',
			'seo_score',
		],
		'aioseo_page_advanced_settings'  => [
			'canonical_url',
			'robots_default',
			'robots_noindex',
			'robots_noarchive',
			'robots_nosnippet',
			'robots_nofollow',
			'robots_noimageindex',
			'robots_noodp',
			'robots_notranslate',
			'robots_max_snippet',
			'robots_max_videopreview',
			'robots_max_imagepreview',
			'priority',
			'frequency',
		],
		'aioseo_page_schema_settings'    => [
			'schema',
			'schema_type',
			'schema_type_options'
		],
		'aioseo_page_social_settings'    => [
			'og_title',
			'og_description',
			'og_object_type',
			'og_image_type',
			'og_image_custom_url',
			'og_image_custom_fields',
			'og_custom_image_width',
			'og_custom_image_height',
			'og_video',
			'og_custom_url',
			'og_article_section',
			'og_article_tags',
			'twitter_use_og',
			'twitter_card',
			'twitter_image_type',
			'twitter_image_custom_url',
			'twitter_image_custom_fields',
			'twitter_title',
			'twitter_description'
		],
		'aioseo_page_local_seo_settings' => [
			'local_seo',
		],
		'aioseo_manage_seo'              => [
			'images',
			'image_scan_date',
			'videos',
			'video_thumbnail',
			'video_scan_date',
			'created',
			'updated'
		]
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'updated_option', [ $this, 'maybeUpdateOptions' ], 10, 3 );
	}

	/**
	 * Sets the roles on the instance.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function setRoles() {
		parent::setRoles();

		$customRoles = array_keys( aioseo()->helpers->getCustomRoles() );

		$this->roles = array_merge( $this->roles, [
			'editor'         => 'editor',
			'author'         => 'author',
			'aioseo_manager' => 'seoManager',
			'aioseo_editor'  => 'seoEditor'
		], array_combine( $customRoles, $customRoles ) );
	}

	/**
	 * Update our Access Control options when a third party plugin changes the roles.
	 *
	 * @since 4.1.3
	 *
	 * @param string $option   The option name.
	 * @param mixed  $oldValue The old value.
	 * @param mixed  $value    The new value.
	 * @return void
	 */
	public function maybeUpdateOptions( $option, $oldValue = '', $value = '' ) {
		// Only performs when updating roles.
		if ( wp_roles()->role_key !== $option ) {
			return;
		}

		// Check if we already are updating the roles.
		if ( ! empty( $this->isUpdatingRoles ) ) {
			return;
		}

		$options        = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		foreach ( $this->roles as $wpRole => $role ) {
			if ( empty( $value[ $wpRole ]['capabilities'] ) ) {
				continue;
			}

			// Update role.
			if ( $options->accessControl->has( $role ) ) {
				$useDefault = true;
				foreach ( $options->accessControl->$role->all() as $capability => $enabled ) {
					if ( 'useDefault' === $capability ) {
						continue;
					}

					$enabled = in_array(
						'aioseo_' . aioseo()->helpers->toSnakeCase( $capability ),
						array_keys( $value[ $wpRole ]['capabilities'] ),
						true
					);

					if ( $useDefault && $enabled !== $options->accessControl->$role->getDefault( $capability ) ) {
						$useDefault = false;
					}

					$options->accessControl->$role->$capability = $enabled;
				}
				$options->accessControl->$role->useDefault = $useDefault;
			}

			// Update dynamic role.
			if ( $dynamicOptions->accessControl->has( $role ) ) {
				$useDefault = true;
				foreach ( $dynamicOptions->accessControl->$role->all() as $capability => $enabled ) {
					if ( 'useDefault' === $capability ) {
						continue;
					}

					$enabled = in_array(
						'aioseo_' . aioseo()->helpers->toSnakeCase( $capability ),
						array_keys( $value[ $wpRole ]['capabilities'] ),
						true
					);

					if ( $useDefault && $enabled !== $dynamicOptions->accessControl->$role->getDefault( $capability ) ) {
						$useDefault = false;
					}

					$dynamicOptions->accessControl->$role->$capability = $enabled;
				}
				$dynamicOptions->accessControl->$role->useDefault = $useDefault;
			}
		}

		// Re-init the WordPress roles before we use it again.
		wp_roles()->for_site();

		// Run our method again so it will ensure all our capabilities are right.
		$this->addCapabilities();
	}

	/**
	 * Checks if the current user has the capability.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $capability The capability to check against.
	 * @param  string|null $checkRole  A role to check against.
	 * @return bool                    Whether or not the user has this capability.
	 */
	public function hasCapability( $capability, $checkRole = null ) {
		// Administrators always have access.
		if ( $this->isAdmin( $checkRole ) ) {
			return true;
		}

		static $isAllowed = [];

		$capabilityName = aioseo()->helpers->toCamelCase( str_replace( 'aioseo_', '', $capability ) );
		$hasCapability  = false;
		$options        = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		foreach ( $this->roles as $wpRole => $role ) {
			// Skip other roles if is checking for a specific role.
			if ( $checkRole && $checkRole !== $role ) {
				continue;
			}

			if ( ! $checkRole && ! current_user_can( $wpRole ) ) {
				continue;
			}

			if ( isset( $isAllowed[ $role ][ $capability ] ) ) {
				return $isAllowed[ $role ][ $capability ];
			}

			if ( $options->accessControl->has( $role ) ) { // Check for default role.
				$hasCapability = $options->accessControl->{ $role }->{ $capabilityName };
				if ( $options->accessControl->$role->useDefault ) {
					$hasCapability = $options->accessControl->{ $role }->getDefault( $capabilityName );
				}
			} elseif ( $dynamicOptions->accessControl->has( $role ) ) { // Check for dynamic role.
				$hasCapability = $dynamicOptions->accessControl->{ $role }->{ $capabilityName };
				if ( $dynamicOptions->accessControl->$role->useDefault ) {
					$hasCapability = $dynamicOptions->accessControl->{ $role }->getDefault( $capabilityName );
				}
			}

			$isAllowed[ $role ][ $capability ] = $hasCapability;

			if ( $hasCapability ) {
				return true;
			}
		}

		return $hasCapability;
	}

	/**
	 * Adds capabilities into WordPress for the current user.
	 * Only on activation or settings saved.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addCapabilities() {
		parent::addCapabilities();

		foreach ( $this->roles as $wpRole => $role ) {
			// Role doesn't exist, let's add it in.
			if ( in_array( $wpRole,
				[
					'aioseo_manager',
					'aioseo_editor'
				], true
			) ) {
				add_role( $wpRole, ucwords( str_replace( 'aioseo_', 'SEO ', $wpRole ) ), [
					'edit_others_posts'    => true,
					'edit_others_pages'    => true,
					'edit_pages'           => true,
					'edit_posts'           => true,
					'edit_private_pages'   => true,
					'edit_private_posts'   => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories'    => true,
					'read_private_pages'   => true,
					'read_private_posts'   => true,
					'read'                 => true
				] );
			}

			$roleObject = get_role( $wpRole );
			if ( ! is_object( $roleObject ) ) {
				continue;
			}

			foreach ( $this->getAllCapabilities( $role ) as $capability => $enabled ) {
				if ( $enabled ) {
					$roleObject->add_cap( $capability );
				} else {
					$roleObject->remove_cap( $capability );
				}
			}
		}

		// Let addons add their own Access Control.
		foreach ( aioseo()->addons->getLoadedAddons() as $addon ) {
			if ( isset( $addon->access ) && method_exists( $addon->access, 'addCapabilities' ) ) {
				$addon->access->addCapabilities();
			}
		}
	}

	/**
	 * Checks if the current user can manage AIOSEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $checkRole A role to check against.
	 * @return bool                   Whether or not the user can manage AIOSEO.
	 */
	public function canManage( $checkRole = null ) {
		// Administrators always can manage.
		if ( $this->isAdmin( $checkRole ) ) {
			return true;
		}

		foreach ( $this->roles as $wpRole => $role ) {
			// Skip other roles if is checking for a specific role.
			if ( $checkRole && $checkRole !== $role ) {
				continue;
			}

			if ( ! $checkRole && ! current_user_can( $wpRole ) ) {
				continue;
			}

			$isCustomRole = aioseo()->dynamicOptions->accessControl->has( $role );

			if ( ! aioseo()->options->accessControl->has( $role ) && ! $isCustomRole ) {
				continue;
			}

			$roleSettings = $isCustomRole ? aioseo()->dynamicOptions->accessControl->$role->all() : aioseo()->options->accessControl->$role->all();

			// If is set to use default settings, let's get the default values.
			if ( true === $roleSettings['useDefault'] ) {
				foreach ( $roleSettings as $capability => $enabled ) {
					$roleSettings[ $capability ] = $isCustomRole ?
						aioseo()->dynamicOptions->accessControl->$role->getDefault( $capability ) :
						aioseo()->options->accessControl->$role->getDefault( $capability );
				}
			}

			unset( $roleSettings['useDefault'] );

			foreach ( $roleSettings as $capability => $enabled ) {
				// We are not looking for page settings here.
				if ( false !== strpos( $capability, 'page' ) ) {
					continue;
				}

				if ( $enabled ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Gets all options that the user does not have access to manage.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $role The given role.
	 * @return array        An array with the option names.
	 */
	public function getNotAllowedOptions( $role = null ) {
		return $this->getNotAllowedOnList( $this->capabilityOptions, $role );
	}

	/**
	 * Gets all page fields that the user does not have access to manage.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $role The given role.
	 * @return array        An array with the field names.
	 */
	public function getNotAllowedPageFields( $role = null ) {
		return $this->getNotAllowedOnList( $this->capabilityPage, $role );
	}

	/**
	 * Helper function to get all options user does not have access on the given mapped list.
	 *
	 * @since 4.1.3
	 *
	 * @param  array  $mappedCapabilities The mapped capabilities/options list.
	 * @param  string $role               The given role.
	 * @return array                      An array with the option names.
	 */
	private function getNotAllowedOnList( $mappedCapabilities, $role = null ) {
		$allCapabilities   = $this->getAllCapabilities( $role );
		$trueCapabilities  = array_filter( $allCapabilities );
		$falseCapabilities = array_diff_key( $mappedCapabilities, $trueCapabilities );

		if ( empty( $falseCapabilities ) ) {
			return [];
		}

		$notAllowedOptions = call_user_func_array( 'array_merge', array_values( $falseCapabilities ) );

		return array_combine( $notAllowedOptions, $notAllowedOptions );
	}
}