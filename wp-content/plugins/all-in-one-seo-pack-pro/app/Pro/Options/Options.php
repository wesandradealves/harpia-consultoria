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
 * @since 4.0.0
 */
class Options extends CommonOptions\Options {
	use Traits\Options;

	/**
	 * Defaults options for Pro.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $proDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal'         => [],
		'general'          => [
			'licenseKey' => [ 'type' => 'string' ]
		],
		'breadcrumbs'      => [
			'advanced' => [
				'taxonomySkipUnselected' => [ 'type' => 'boolean', 'default' => false ],
				'showPaged'              => [ 'type' => 'boolean', 'default' => true ],
				'pagedFormat'            => [ 'type' => 'string', 'default' => 'Page #breadcrumb_format_page_number', 'localized' => true ]
			]
		],
		'accessControl'    => [
			// Admin Access Controls.
			'administrator' => [
				'useDefault'                => [ 'type' => 'boolean', 'default' => true ],
				'dashboard'                 => [ 'type' => 'boolean', 'default' => true ],
				'generalSettings'           => [ 'type' => 'boolean', 'default' => true ],
				'searchAppearanceSettings'  => [ 'type' => 'boolean', 'default' => true ],
				'socialNetworksSettings'    => [ 'type' => 'boolean', 'default' => true ],
				'sitemapSettings'           => [ 'type' => 'boolean', 'default' => true ],
				'redirectsManage'           => [ 'type' => 'boolean', 'default' => true ],
				'pageRedirectsManage'       => [ 'type' => 'boolean', 'default' => true ],
				'redirectsSettings'         => [ 'type' => 'boolean', 'default' => true ],
				'seoAnalysisSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'toolsSettings'             => [ 'type' => 'boolean', 'default' => true ],
				'featureManagerSettings'    => [ 'type' => 'boolean', 'default' => true ],
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => true ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => true ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => true ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => true ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => true ]
			],
			// Editor Access Controls.
			'editor'        => [
				'useDefault'                => [ 'type' => 'boolean', 'default' => true ],
				'dashboard'                 => [ 'type' => 'boolean', 'default' => false ],
				'generalSettings'           => [ 'type' => 'boolean', 'default' => true ],
				'searchAppearanceSettings'  => [ 'type' => 'boolean', 'default' => true ],
				'socialNetworksSettings'    => [ 'type' => 'boolean', 'default' => true ],
				'sitemapSettings'           => [ 'type' => 'boolean', 'default' => false ],
				'redirectsManage'           => [ 'type' => 'boolean', 'default' => true ],
				'pageRedirectsManage'       => [ 'type' => 'boolean', 'default' => true ],
				'redirectsSettings'         => [ 'type' => 'boolean', 'default' => false ],
				'seoAnalysisSettings'       => [ 'type' => 'boolean', 'default' => false ],
				'toolsSettings'             => [ 'type' => 'boolean', 'default' => false ],
				'featureManagerSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => false ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => false ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => false ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => false ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => false ]
			],
			// Author Access Controls.
			'author'        => [
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
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => false ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => false ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => false ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => false ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => false ]
			],
			// Contributor Access Controls.
			'contributor'   => [
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
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => false ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => false ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => false ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => false ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => false ]
			],
			// SEO Manager Access Controls.
			'seoManager'    => [
				'useDefault'                => [ 'type' => 'boolean', 'default' => true ],
				'dashboard'                 => [ 'type' => 'boolean', 'default' => true ],
				'generalSettings'           => [ 'type' => 'boolean', 'default' => true ],
				'searchAppearanceSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'socialNetworksSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'sitemapSettings'           => [ 'type' => 'boolean', 'default' => true ],
				'redirectsManage'           => [ 'type' => 'boolean', 'default' => true ],
				'pageRedirectsManage'       => [ 'type' => 'boolean', 'default' => true ],
				'redirectsSettings'         => [ 'type' => 'boolean', 'default' => true ],
				'seoAnalysisSettings'       => [ 'type' => 'boolean', 'default' => false ],
				'toolsSettings'             => [ 'type' => 'boolean', 'default' => false ],
				'featureManagerSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => true ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => true ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => true ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => true ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => true ]
			],
			// SEO Editor Access Controls.
			'seoEditor'     => [
				'useDefault'                => [ 'type' => 'boolean', 'default' => true ],
				'dashboard'                 => [ 'type' => 'boolean', 'default' => false ],
				'generalSettings'           => [ 'type' => 'boolean', 'default' => false ],
				'searchAppearanceSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'socialNetworksSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'sitemapSettings'           => [ 'type' => 'boolean', 'default' => false ],
				'redirectsManage'           => [ 'type' => 'boolean', 'default' => false ],
				'pageRedirectsManage'       => [ 'type' => 'boolean', 'default' => true ],
				'redirectsSettings'         => [ 'type' => 'boolean', 'default' => false ],
				'seoAnalysisSettings'       => [ 'type' => 'boolean', 'default' => false ],
				'toolsSettings'             => [ 'type' => 'boolean', 'default' => false ],
				'featureManagerSettings'    => [ 'type' => 'boolean', 'default' => false ],
				'pageAnalysis'              => [ 'type' => 'boolean', 'default' => true ],
				'searchStatisticsSettings'  => [ 'type' => 'boolean', 'default' => false ],
				'pageGeneralSettings'       => [ 'type' => 'boolean', 'default' => true ],
				'pageAdvancedSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'pageSchemaSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'pageSocialSettings'        => [ 'type' => 'boolean', 'default' => true ],
				'localSeoSettings'          => [ 'type' => 'boolean', 'default' => false ],
				'pageLocalSeoSettings'      => [ 'type' => 'boolean', 'default' => true ],
				'linkAssistantSettings'     => [ 'type' => 'boolean', 'default' => false ],
				'pageLinkAssistantSettings' => [ 'type' => 'boolean', 'default' => true ],
				'setupWizard'               => [ 'type' => 'boolean', 'default' => false ]
			]
		],
		'advanced'         => [
			'adminBarMenu'  => [ 'type' => 'boolean', 'default' => true ],
			'usageTracking' => [ 'type' => 'boolean', 'default' => true ],
			'autoUpdates'   => [ 'type' => 'string', 'default' => 'all' ],
			'openAiKey'     => [ 'type' => 'string', 'default' => '' ]
		],
		'sitemap'          => [
			'video' => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'filename'         => [ 'type' => 'string', 'default' => 'video-sitemap' ],
				'indexes'          => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex'    => [ 'type' => 'number', 'default' => 1000 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'attachment' ] ],
				],
				'taxonomies'       => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'product_cat', 'product_tag' ] ],
				],
				/*'embed'            => [
					'playDirectly' => [ 'type' => 'boolean', 'default' => true ],
					'responsive'   => [ 'type' => 'boolean', 'default' => false ],
					'width'        => [ 'type' => 'integer' ],
					'wistia'       => [ 'type' => 'string' ],
					'embedlyApi'   => [ 'type' => 'string' ]
				], */
				'additionalPages'  => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'pages'  => [ 'type' => 'array', 'default' => [] ]
				],
				'advancedSettings' => [
					'enable'       => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts' => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms' => [ 'type' => 'array', 'default' => [] ],
					'dynamic'      => [ 'type' => 'boolean', 'default' => true ],
					'customFields' => [ 'type' => 'boolean', 'default' => false ],
				]
			],
			'news'  => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'publicationName'  => [ 'type' => 'string' ],
				'genre'            => [ 'type' => 'string' ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => false ],
					'included' => [ 'type' => 'array', 'default' => [ 'post' ] ],
				],
				'additionalPages'  => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'pages'  => [ 'type' => 'array', 'default' => [] ]
				],
				'advancedSettings' => [
					'enable'       => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts' => [ 'type' => 'array', 'default' => [] ],
					'priority'     => [
						'homePage'   => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'postTypes'  => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'taxonomies' => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						]
					]
				]
			],
		],
		'social'           => [
			'facebook' => [
				'general' => [
					'defaultImageSourceTerms' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImageTerms'   => [ 'type' => 'string' ],
					'defaultImageTerms'       => [ 'type' => 'string', 'default' => '' ],
					'defaultImageTermsWidth'  => [ 'type' => 'number', 'default' => '' ],
					'defaultImageTermsHeight' => [ 'type' => 'number', 'default' => '' ]
				],
			],
			'twitter'  => [
				'general' => [
					'defaultImageSourceTerms' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImageTerms'   => [ 'type' => 'string' ],
					'defaultImageTerms'       => [ 'type' => 'string', 'default' => '' ]
				],
			]
		],
		'searchAppearance' => [
			'advanced' => [
				'removeCatBase' => [ 'type' => 'boolean', 'default' => false ]
			]
		],
		'image'            => [
			// TODO: Remove the "format" and "stripPunctuation" groups in a future update after we've confirmed that the update went well.
			// Start of options to remove.
			'format'           => [
				'title'  => [ 'type' => 'string', 'default' => '#image_title #separator_sa #site_title', 'localized' => true ],
				'altTag' => [ 'type' => 'string', 'default' => '#alt_tag', 'localized' => true ]
			],
			'stripPunctuation' => [
				'title'  => [ 'type' => 'boolean', 'default' => false ],
				'altTag' => [ 'type' => 'boolean', 'default' => false ]
			],
			// End of options to remove.
			'title'            => [
				'format'              => [ 'type' => 'string', 'default' => '#image_title #separator_sa #site_title', 'localized' => true ],
				'stripPunctuation'    => [ 'type' => 'boolean', 'default' => true ],
				'charactersToKeep'    => [
					'dashes'      => [ 'type' => 'boolean', 'default' => false ],
					'underscores' => [ 'type' => 'boolean', 'default' => false ],
					'numbers'     => [ 'type' => 'boolean', 'default' => true ],
					'plus'        => [ 'type' => 'boolean', 'default' => true ],
					'apostrophe'  => [ 'type' => 'boolean', 'default' => false ],
					'pound'       => [ 'type' => 'boolean', 'default' => false ],
					'ampersand'   => [ 'type' => 'boolean', 'default' => false ]
				],
				'charactersToConvert' => [
					'dashes'      => [ 'type' => 'boolean', 'default' => false ],
					'underscores' => [ 'type' => 'boolean', 'default' => false ],
				],
				'casing'              => [ 'type' => 'string', 'default' => '' ],
				'advancedSettings'    => [
					'excludePosts' => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms' => [ 'type' => 'array', 'default' => [] ],
				],
			],
			'altTag'           => [
				'format'              => [ 'type' => 'string', 'default' => '#alt_tag', 'localized' => true ],
				'stripPunctuation'    => [ 'type' => 'boolean', 'default' => true ],
				'charactersToKeep'    => [
					'dashes'      => [ 'type' => 'boolean', 'default' => false ],
					'underscores' => [ 'type' => 'boolean', 'default' => false ],
					'numbers'     => [ 'type' => 'boolean', 'default' => true ],
					'plus'        => [ 'type' => 'boolean', 'default' => true ],
					'apostrophe'  => [ 'type' => 'boolean', 'default' => false ],
					'pound'       => [ 'type' => 'boolean', 'default' => false ],
					'ampersand'   => [ 'type' => 'boolean', 'default' => false ]
				],
				'charactersToConvert' => [
					'dashes'      => [ 'type' => 'boolean', 'default' => true ],
					'underscores' => [ 'type' => 'boolean', 'default' => true ],
				],
				'casing'              => [ 'type' => 'string', 'default' => '' ],
				'advancedSettings'    => [
					'excludePosts' => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms' => [ 'type' => 'array', 'default' => [] ],
				],
			],
			'caption'          => [
				'autogenerate'        => [ 'type' => 'boolean', 'default' => true ],
				'format'              => [ 'type' => 'string', 'default' => '#image_title', 'localized' => true ],
				'stripPunctuation'    => [ 'type' => 'boolean', 'default' => true ],
				'charactersToKeep'    => [
					'dashes'      => [ 'type' => 'boolean', 'default' => false ],
					'underscores' => [ 'type' => 'boolean', 'default' => false ],
					'numbers'     => [ 'type' => 'boolean', 'default' => true ],
					'plus'        => [ 'type' => 'boolean', 'default' => true ],
					'apostrophe'  => [ 'type' => 'boolean', 'default' => false ],
					'pound'       => [ 'type' => 'boolean', 'default' => false ],
					'ampersand'   => [ 'type' => 'boolean', 'default' => false ]
				],
				'charactersToConvert' => [
					'dashes'      => [ 'type' => 'boolean', 'default' => true ],
					'underscores' => [ 'type' => 'boolean', 'default' => true ],
				],
				'casing'              => [ 'type' => 'string', 'default' => '' ],
			],
			'description'      => [
				'autogenerate'        => [ 'type' => 'boolean', 'default' => true ],
				'format'              => [ 'type' => 'string', 'default' => '#image_title', 'localized' => true ],
				'stripPunctuation'    => [ 'type' => 'boolean', 'default' => true ],
				'charactersToKeep'    => [
					'dashes'      => [ 'type' => 'boolean', 'default' => false ],
					'underscores' => [ 'type' => 'boolean', 'default' => false ],
					'numbers'     => [ 'type' => 'boolean', 'default' => true ],
					'plus'        => [ 'type' => 'boolean', 'default' => true ],
					'apostrophe'  => [ 'type' => 'boolean', 'default' => false ],
					'pound'       => [ 'type' => 'boolean', 'default' => false ],
					'ampersand'   => [ 'type' => 'boolean', 'default' => false ]
				],
				'charactersToConvert' => [
					'dashes'      => [ 'type' => 'boolean', 'default' => true ],
					'underscores' => [ 'type' => 'boolean', 'default' => true ],
				],
				'casing'              => [ 'type' => 'string', 'default' => '' ],
			],
			'filename'         => [
				'stripPunctuation' => [ 'type' => 'boolean', 'default' => true ],
				'charactersToKeep' => [
					'dashes'      => [ 'type' => 'boolean', 'default' => true ],
					'underscores' => [ 'type' => 'boolean', 'default' => true ],
					'numbers'     => [ 'type' => 'boolean', 'default' => true ],
					'plus'        => [ 'type' => 'boolean', 'default' => true ],
					'apostrophe'  => [ 'type' => 'boolean', 'default' => false ],
					'pound'       => [ 'type' => 'boolean', 'default' => false ],
					'ampersand'   => [ 'type' => 'boolean', 'default' => false ]
				],
				'casing'           => [ 'type' => 'string', 'default' => '' ],
				'wordsToStrip'     => [
					'type'    => 'html',
					'default' => ''
				]
			],
		],
		'localBusiness'    => [
			'locations'    => [
				'general'  => [
					'multiple'              => [ 'type' => 'boolean', 'default' => false ],
					'display'               => [ 'type' => 'string' ],
					'singleLabel'           => [ 'type' => 'string' ],
					'pluralLabel'           => [ 'type' => 'string' ],
					'permalink'             => [ 'type' => 'string' ],
					'categoryPermalink'     => [ 'type' => 'string' ],
					'useCustomSlug'         => [ 'type' => 'boolean', 'default' => false ],
					'customSlug'            => [ 'type' => 'string' ],
					'useCustomCategorySlug' => [ 'type' => 'boolean', 'default' => false ],
					'customCategorySlug'    => [ 'type' => 'string' ],
					'enhancedSearch'        => [ 'type' => 'boolean', 'default' => false ],
					'enhancedSearchExcerpt' => [ 'type' => 'boolean', 'default' => false ],
				],
				'business' => [
					'name'         => [ 'type' => 'string' ],
					'businessType' => [ 'type' => 'string', 'default' => 'LocalBusiness' ],
					'image'        => [ 'type' => 'string' ],
					'areaServed'   => [ 'type' => 'string' ],
					'urls'         => [
						'website'     => [ 'type' => 'string' ],
						'aboutPage'   => [ 'type' => 'string' ],
						'contactPage' => [ 'type' => 'string' ]
					],
					'address'      => [
						'streetLine1'   => [ 'type' => 'string' ],
						'streetLine2'   => [ 'type' => 'string' ],
						'zipCode'       => [ 'type' => 'string' ],
						'city'          => [ 'type' => 'string' ],
						'state'         => [ 'type' => 'string' ],
						'country'       => [ 'type' => 'string' ],
						'addressFormat' => [ 'type' => 'html' ]
					],
					'contact'      => [
						'email'          => [ 'type' => 'string' ],
						'phone'          => [ 'type' => 'string' ],
						'phoneFormatted' => [ 'type' => 'string' ],
						'fax'            => [ 'type' => 'string' ],
						'faxFormatted'   => [ 'type' => 'string' ]
					],
					'ids'          => [
						'vat'               => [ 'type' => 'string' ],
						'tax'               => [ 'type' => 'string' ],
						'chamberOfCommerce' => [ 'type' => 'string' ]
					],
					'payment'      => [
						'priceRange'         => [ 'type' => 'string' ],
						'currenciesAccepted' => [ 'type' => 'string' ],
						'methods'            => [ 'type' => 'string' ]
					]
				]
			],
			'openingHours' => [
				'show'         => [ 'type' => 'boolean', 'default' => true ],
				'display'      => [ 'type' => 'string' ],
				'alwaysOpen'   => [ 'type' => 'boolean', 'default' => false ],
				'use24hFormat' => [ 'type' => 'boolean', 'default' => false ],
				'timezone'     => [ 'type' => 'string' ],
				'labels'       => [
					'closed'     => [ 'type' => 'string' ],
					'alwaysOpen' => [ 'type' => 'string' ]
				],
				'days'         => [
					'monday'    => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'tuesday'   => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'wednesday' => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'thursday'  => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'friday'    => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'saturday'  => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					],
					'sunday'    => [
						'open24h'   => [ 'type' => 'boolean', 'default' => false ],
						'closed'    => [ 'type' => 'boolean', 'default' => false ],
						'openTime'  => [ 'type' => 'string', 'default' => '09:00' ],
						'closeTime' => [ 'type' => 'string', 'default' => '17:00' ]
					]
				]
			],
			'maps'         => [
				'apiKey'              => [ 'type' => 'string' ],
				'apiKeyValid'         => [ 'type' => 'boolean' ],
				'mapsEmbedApiEnabled' => [ 'type' => 'boolean', 'default' => false ],
				'mapOptions'          => [
					'center'            => [
						'lat' => [ 'type' => 'float', 'default' => 47.6205063 ], // Space Needle, Seattle - WA
						'lng' => [ 'type' => 'float', 'default' => - 122.3492774 ]
					],
					'zoom'              => [ 'type' => 'float', 'default' => 16 ],
					'mapTypeId'         => [ 'type' => 'string', 'default' => 'roadmap' ],
					'streetViewControl' => [ 'type' => 'boolean', 'default' => false ]
				],
				'customMarker'        => [ 'type' => 'string' ],
				'placeId'             => [ 'type' => 'string' ]
			]
		],
		'deprecated'       => [
			'sitemap'        => [
				'video' => [
					'advancedSettings' => [
						'dynamic' => [ 'type' => 'boolean', 'default' => false ],
					],
				]
			],
			'webmasterTools' => [
				'googleAnalytics' => [
					'trackOutboundForms' => [ 'type' => 'boolean', 'default' => false ],
					'trackEvents'        => [ 'type' => 'boolean', 'default' => false ],
					'trackUrlChanges'    => [ 'type' => 'boolean', 'default' => false ],
					'trackVisibility'    => [ 'type' => 'boolean', 'default' => false ],
					'trackMediaQueries'  => [ 'type' => 'boolean', 'default' => false ],
					'trackImpressions'   => [ 'type' => 'boolean', 'default' => false ],
					'trackScrollbar'     => [ 'type' => 'boolean', 'default' => false ],
					'trackSocial'        => [ 'type' => 'boolean', 'default' => false ],
					'trackCleanUrl'      => [ 'type' => 'boolean', 'default' => false ],
					'gtmContainerId'     => [ 'type' => 'string' ]
				],
			],
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class constructor
	 *
	 * @since 4.0.0
	 */
	public function __construct( $optionsName = 'aioseo_options' ) {
		parent::__construct( $optionsName );

		// Now that we are initialized, let's run an update routine.
		$validLicenseKey = aioseo()->internalOptions->internal->validLicenseKey;
		if ( $validLicenseKey ) {
			// Save the key to our settings.
			$this->general->licenseKey = $validLicenseKey;// @phpstan-ignore-line
			$this->save( true );

			// Reset the key coming in from lite.
			aioseo()->internalOptions->internal->validLicenseKey = null;
		}
	}

	/**
	 * For our defaults array, some options need to be translated, so we do that here.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function translateDefaults() {
		parent::translateDefaults();

		$this->proDefaults['breadcrumbs']['advanced']['pagedFormat']['default'] = sprintf( '%1$s #breadcrumb_format_page_number', __( 'Page', 'aioseo-pro' ) );
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		$videoOptions           = ! empty( $options['sitemap']['video'] ) ? $options['sitemap']['video'] : null;
		$deprecatedOldOptions   = aioseo()->options->deprecated->sitemap->video->all();
		$deprecatedVideoOptions = ! empty( $options['deprecated']['sitemap']['video'] )
				? $options['deprecated']['sitemap']['video']
				: null;
		$oldPhoneOption     = aioseo()->options->localBusiness->locations->business->contact->phone;
		$phoneNumberOptions = isset( $options['localBusiness']['locations']['business']['contact']['phone'] )
				? $options['localBusiness']['locations']['business']['contact']['phone']
				: null;
		$oldCountryOption = aioseo()->options->localBusiness->locations->business->address->country;
		$countryOption    = isset( $options['localBusiness']['locations']['business']['address']['country'] )
				? $options['localBusiness']['locations']['business']['address']['country']
				: null;
		$imageOptions = ! empty( $options['image'] ) ? $options['image'] : null;
		// Remove category base.
		$removeCategoryBase    = isset( $options['searchAppearance']['advanced']['removeCatBase'] ) ? $options['searchAppearance']['advanced']['removeCatBase'] : null;
		$removeCategoryBaseOld = aioseo()->options->searchAppearance->advanced->removeCatBase;

		// Local business - multiple locations.
		// Changes that require reload.
		$requireReload = [ 'multiple', 'singleLabel', 'pluralLabel' ];
		foreach ( $requireReload as $item ) {
			if (
				isset( $options['localBusiness']['locations']['general'][ $item ] ) &&
				aioseo()->options->localBusiness->locations->general->{$item} !== $options['localBusiness']['locations']['general'][ $item ]
			) {
				aioseo()->options->setRedirection( 'reload' );
				break;
			}
		}

		// Changes that require flush_rewrite_rules().
		$requireRewrite = [
			'multiple',
			'useCustomSlug',
			'customSlug',
			'useCustomCategorySlug',
			'customCategorySlug'
		];

		foreach ( $requireRewrite as $item ) {
			if (
				isset( $options['localBusiness']['locations']['general'][ $item ] ) &&
				aioseo()->options->localBusiness->locations->general->{$item} !== $options['localBusiness']['locations']['general'][ $item ]
			) {
				aioseo()->options->flushRewriteRules();
				break;
			}
		}

		parent::sanitizeAndSave( $options );

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );

		if ( $imageOptions && isset( $imageOptions['filename']['wordsToStrip'] ) ) {
			$cachedOptions['image']['filename']['wordsToStrip']['value'] = preg_replace( '/\h/', "\n", $imageOptions['filename']['wordsToStrip'] );
		}

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $cachedOptions );

		$this->save( true );

		// If sitemap settings were changed, static files need to be regenerated.
		if (
			! empty( $deprecatedVideoOptions ) &&
			! empty( $videoOptions ) &&
			aioseo()->helpers->arraysDifferent( $deprecatedOldOptions, $deprecatedVideoOptions ) &&
			$videoOptions['advancedSettings']['enable'] &&
			! $deprecatedVideoOptions['advancedSettings']['dynamic']
		) {
			aioseo()->sitemap->scheduleRegeneration();
		}

		// If phone settings have changed, let's see if we need to dump the phone number notice.
		if (
			$phoneNumberOptions &&
			$phoneNumberOptions !== $oldPhoneOption
		) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-local-business-number' );
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'v3-migration-local-business-number' );
			}
		}

		if (
			$countryOption &&
			$countryOption !== $oldCountryOption
		) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-local-business-country' );
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'v3-migration-local-business-country' );
			}
		}

		if (
			null !== $removeCategoryBase &&
			$removeCategoryBase !== $removeCategoryBaseOld
		) {
			aioseo()->options->flushRewriteRules();
		}
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function setInitialDefaults() {
		parent::setInitialDefaults();
		$this->proDefaults['sitemap']['news']['publicationName']['default']                        = aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
		$this->proDefaults['localBusiness']['locations']['business']['urls']['website']['default'] = home_url();
	}
}