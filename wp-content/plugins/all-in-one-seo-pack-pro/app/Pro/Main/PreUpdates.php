<?php
namespace AIOSEO\Plugin\Pro\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;
/**
 * Pre Updates class.
 *
 * @since 4.1.5
 */
class PreUpdates extends CommonMain\PreUpdates {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.5
	 */
	public function __construct() {
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		parent::__construct();
	}
}