<?php
namespace AIOSEO\Plugin\Pro\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Pro\Traits;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.1.4
 */
class DynamicOptions extends CommonOptions\DynamicOptions {
	use Traits\Options;

	/**
	 * Defaults options for Pro.
	 *
	 * @since 4.1.4
	 *
	 * @var array
	 */
	private $proDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'social'        => [
			'facebook' => [
				'general' => [
					'taxonomies' => []
				]
			]
		],
		'breadcrumbs'   => [
			'postTypes'  => [],
			'taxonomies' => [],
			'archives'   => [
				'postTypes' => [],
				'date'      => [],
				'search'    => [],
				'notFound'  => [],
				'author'    => []
			]
		],
		'accessControl' => [],
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		$options = $this->maybeRemoveUnfilteredHtmlFields( $options );

		parent::sanitizeAndSave( $options );

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );

		aioseo()->dynamicBackup->maybeBackup( $cachedOptions );

		// Since capabilities may have changed, let's update those now.
		aioseo()->access->addCapabilities();
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function addDynamicDefaults() {
		parent::addDynamicDefaults();

		$this->addDynamicAccessControlRolesDefaults();

		if ( isset( $this->defaults['searchAppearance']['postTypes']['product']['schemaType'] ) && aioseo()->helpers->isWooCommerceActive() ) {
			$this->defaults['searchAppearance']['postTypes']['product']['schemaType']['default'] = 'Product';
		}

		if ( isset( $this->defaults['searchAppearance']['postTypes']['download']['schemaType'] ) && aioseo()->helpers->isEddActive() ) {
			$this->defaults['searchAppearance']['postTypes']['download']['schemaType']['default'] = 'Product';
		}

		$breadcrumbTemplateOption = [
			'useDefaultTemplate' => [
				'type'    => 'boolean',
				'default' => true
			]
		];

		if ( isset( $this->proDefaults['breadcrumbs']['postTypes'] ) ) {
			$postTypes = aioseo()->helpers->getPublicPostTypes();
			foreach ( $postTypes as $postType ) {
				if ( 'type' === $postType['name'] ) {
					$postType['name'] = '_aioseo_type';
				}

				$this->proDefaults['breadcrumbs']['postTypes'][ $postType['name'] ] = array_merge( $breadcrumbTemplateOption,
					[
						'taxonomy'           => [
							'type'    => 'string',
							'default' => ''
						],
						'showArchiveCrumb'   => [
							'type'    => 'boolean',
							'default' => true
						],
						'showTaxonomyCrumbs' => [
							'type'    => 'boolean',
							'default' => true
						],
						'showHomeCrumb'      => [
							'type'    => 'boolean',
							'default' => true
						],
						'showPrefixCrumb'    => [
							'type'    => 'boolean',
							'default' => true
						],
						'showParentCrumbs'   => [
							'type'    => 'boolean',
							'default' => true
						],
						'template'           => [
							'type'    => 'html',
							'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'single', $postType ) )
						]
					],
					$postType['hierarchical']
						? [
							'parentTemplate' => [
								'type'    => 'html',
								'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'single', $postType ) )
							]
						]
						: []
				);
			}
		}
		if ( isset( $this->proDefaults['breadcrumbs']['taxonomies'] ) ) {
			$taxonomies = aioseo()->helpers->getPublicTaxonomies();
			foreach ( $taxonomies as $taxonomy ) {
				if ( 'type' === $taxonomy['name'] ) {
					$taxonomy['name'] = '_aioseo_type';
				}

				$this->proDefaults['breadcrumbs']['taxonomies'][ $taxonomy['name'] ] = array_merge( $breadcrumbTemplateOption,
					[
						'showHomeCrumb'    => [
							'type'    => 'boolean',
							'default' => true
						],
						'showPrefixCrumb'  => [
							'type'    => 'boolean',
							'default' => true
						],
						'showParentCrumbs' => [
							'type'    => 'boolean',
							'default' => true
						],
						'template'         => [
							'type'    => 'html',
							'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'taxonomy', $taxonomy ) )
						]
					], $taxonomy['hierarchical']
						? [
							'parentTemplate' => [
								'type'    => 'html',
								'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'taxonomy', $taxonomy ) )
							]
						]
						: []
				);
			}
		}
		if ( isset( $this->proDefaults['breadcrumbs']['archives']['postTypes'] ) ) {
			$archives = aioseo()->helpers->getPublicPostTypes( false, true, true );
			foreach ( $archives as $archive ) {
				$this->proDefaults['breadcrumbs']['archives']['postTypes'][ $archive['name'] ] = array_merge( $breadcrumbTemplateOption,
					[
						'showHomeCrumb'   => [
							'type'    => 'boolean',
							'default' => true
						],
						'showPrefixCrumb' => [
							'type'    => 'boolean',
							'default' => true
						],
						'template'        => [
							'type'    => 'html',
							'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'postTypeArchive', $archive ) )
						]
					]
				);
			}
		}
		if ( isset( $this->proDefaults['breadcrumbs']['archives']['date'] ) ) {
			$options = [
				'useDefaultTemplate' => [
					'type'    => 'boolean',
					'default' => true
				],
				'template'           => [
					'year'  => [
						'type'    => 'html',
						'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'year' ) )
					],
					'month' => [
						'type'    => 'html',
						'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'month' ) )
					],
					'day'   => [
						'type'    => 'html',
						'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'day' ) )
					]
				],
				'showHomeCrumb'      => [
					'type'    => 'boolean',
					'default' => true
				],
				'showPrefixCrumb'    => [
					'type'    => 'boolean',
					'default' => true
				]
			];

			$this->proDefaults['breadcrumbs']['archives']['date'] = $options;
		}

		$breadcrumbTemplates = [ 'search', 'notFound', 'author', 'blog' ];
		foreach ( $breadcrumbTemplates as $breadcrumbTemplate ) {
			$this->proDefaults['breadcrumbs']['archives'][ $breadcrumbTemplate ] = array_merge( $breadcrumbTemplateOption,
				[
					'showHomeCrumb'   => [
						'type'    => 'boolean',
						'default' => true
					],
					'showPrefixCrumb' => [
						'type'    => 'boolean',
						'default' => true
					],
					'template'        => [
						'type'    => 'html',
						'default' => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( $breadcrumbTemplate ) )
					]
				]
			);
		}
	}

	/**
	 * Add the dynamic defaults for the Access Control roles.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function addDynamicAccessControlRolesDefaults() {
		$customRoles = aioseo()->helpers->getCustomRoles();
		foreach ( $customRoles as $roleName => $role ) {
			// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$defaultOptions = [
				'useDefault'                => [ 'type' => 'boolean', 'default' => true ],
				'dashboard'                 => [ 'type' => 'boolean', 'default' => false ],
				'generalSettings'           => [ 'type' => 'boolean', 'default' => false ],
				'searchAppearanceSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'socialNetworksSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'sitemapSettings'           => [ 'type' => 'boolean', 'default' => false ],
				'redirectsManage'           => [ 'type' => 'boolean', 'default' => false ],
				'pageRedirectsManage'       => [ 'type' => 'boolean', 'default' => false ],
				'redirectsSettings'         => [ 'type' => 'boolean', 'default' => false ],
				'seoAnalysisSettings'       => [ 'type' => 'boolean', 'default' => false ],
				'toolsSettings'             => [ 'type' => 'boolean', 'default' => false ],
				'featureManagerSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => false ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => false ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => false ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => false ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => false ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => false ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => false ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => false ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => false ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => false ]
			];
			// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

			$this->proDefaults['accessControl'][ $roleName ] = $defaultOptions;
		}
	}

	/**
	 * Adds the dynamic defaults for the public taxonomies.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function addDynamicTaxonomyDefaults() {
		parent::addDynamicTaxonomyDefaults();

		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'type' === $taxonomy['name'] ) {
				$taxonomy['name'] = '_aioseo_type';
			}
			$this->setDynamicSocialOptions( 'taxonomies', $taxonomy['name'] );
			$this->setDynamicSitemapOptions( 'taxonomies', $taxonomy['name'] );
		}
	}

	/**
	 * If the user does not have access to unfiltered HTML, we need to remove them from saving.
	 *
	 * @since 4.2.3
	 *
	 * @param  array $options An array of options.
	 * @return array          An array of options.
	 */
	private function maybeRemoveUnfilteredHtmlFields( $options ) {
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $options;
		}

		// Post type templates.
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			if ( isset( $options['breadcrumbs']['postTypes'][ $postType['name'] ]['template'] ) ) {
				unset( $options['breadcrumbs']['postTypes'][ $postType['name'] ]['template'] );
			}

			if ( isset( $options['breadcrumbs']['postTypes'][ $postType['name'] ]['parentTemplate'] ) ) {
				unset( $options['breadcrumbs']['postTypes'][ $postType['name'] ]['parentTemplate'] );
			}
		}

		// Taxonomy templates.
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'type' === $taxonomy['name'] ) {
				$taxonomy['name'] = '_aioseo_type';
			}

			if ( isset( $options['breadcrumbs']['taxonomies'][ $taxonomy['name'] ]['template'] ) ) {
				unset( $options['breadcrumbs']['taxonomies'][ $taxonomy['name'] ]['template'] );
			}

			if ( isset( $options['breadcrumbs']['taxonomies'][ $taxonomy['name'] ]['parentTemplate'] ) ) {
				unset( $options['breadcrumbs']['taxonomies'][ $taxonomy['name'] ]['parentTemplate'] );
			}
		}

		// Archive templates.
		$archives = aioseo()->helpers->getPublicPostTypes( false, true, true );
		foreach ( $archives as $archive ) {
			if ( isset( $options['breadcrumbs']['archives']['postTypes'][ $archive['name'] ]['template'] ) ) {
				unset( $options['breadcrumbs']['archives']['postTypes'][ $archive['name'] ]['template'] );
			}
		}

		// Date templates.
		if ( isset( $options['breadcrumbs']['archives']['date']['template'] ) ) {
			unset( $options['breadcrumbs']['archives']['date']['template'] );
		}

		// Additional templates.
		$breadcrumbTemplates = [ 'search', 'notFound', 'author', 'blog' ];
		foreach ( $breadcrumbTemplates as $breadcrumbTemplate ) {
			if ( isset( $options['breadcrumbs']['archives'][ $breadcrumbTemplate ]['template'] ) ) {
				unset( $options['breadcrumbs']['archives'][ $breadcrumbTemplate ]['template'] );
			}
		}

		return $options;
	}
}