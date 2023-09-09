<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;
use AIOSEO\Plugin\Pro\Meta;

/**
 * Handles our ouput in the document head.
 *
 * @since 4.0.0
 */
class Head extends CommonMain\Head {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->analytics = new GoogleAnalytics();
		$this->keywords  = new Meta\Keywords();

		new Meta\Included();

		add_action( 'init', [ $this, 'addAnalytics' ] );
	}

	/**
	 * Adds analytics to the views if needed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addAnalytics() {
		if ( $this->analytics->canShowScript() ) {
			$this->views['analytics'] = AIOSEO_DIR . '/app/Pro/Views/main/analytics.php';
		}
	}
}