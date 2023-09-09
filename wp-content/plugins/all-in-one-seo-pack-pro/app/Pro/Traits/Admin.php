<?php
namespace AIOSEO\Plugin\Pro\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin trait.
 *
 * @since 4.3.6
 */
trait Admin {
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