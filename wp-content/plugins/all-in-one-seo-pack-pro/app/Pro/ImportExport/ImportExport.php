<?php
namespace AIOSEO\Plugin\Pro\ImportExport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport as CommonImportExport;
use AIOSEO\Plugin\Common\Models;

/**
 * Handles the importing/exporting of settings and SEO data.
 *
 * @since 4.0.0
 */
class ImportExport extends CommonImportExport\ImportExport {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->yoastSeo = new YoastSeo\YoastSeo( $this );
		$this->rankMath = new RankMath\RankMath( $this );
		$this->seoPress = new SeoPress\SeoPress( $this );
	}
}