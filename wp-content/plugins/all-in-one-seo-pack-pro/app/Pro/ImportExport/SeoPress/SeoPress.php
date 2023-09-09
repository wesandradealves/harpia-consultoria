<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport\SeoPress as CommonSeoPress;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

class SeoPress extends CommonSeoPress\SeoPress {
	/**
	 * The term action name.
	 *
	 * @since 4.1.4
	 *
	 * @var string
	 */
	public $termActionName = 'aioseo_import_term_meta_seopress';

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 *
	 * @param ImportExport $importer the ImportExport class.
	 */
	public function __construct( $importer ) {
		parent::__construct( $importer );

		// The Pro class will overrite the Common class, so we need to remove and add it again.
		remove_action( $this->postActionName, [ $this->postMeta, 'importPostMeta' ] );

		$this->postMeta = new PostMeta();
		$this->termMeta = new TermMeta();
		add_action( $this->postActionName, [ $this->postMeta, 'importPostMeta' ] );
		add_action( $this->termActionName, [ $this->termMeta, 'importTermMeta' ] );
	}

	/**
	 * Starts the import.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $options What the user wants to import.
	 * @return void
	 */
	public function doImport( $options = [] ) {
		parent::doImport( $options );
		if ( empty( $options ) ) {
			$this->termMeta->scheduleImport();

			return;
		}

		foreach ( $options as $optionName ) {
			switch ( $optionName ) {
				case 'termMeta':
					$this->termMeta->scheduleImport();
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Imports the settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function importSettings() {
		parent::importSettings();

		new LocalBusiness();
		new Breadcrumbs();
		new NewsSitemap();
		new VideoSitemap();
	}
}