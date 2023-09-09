<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;

/**
 * To check whether SEO is enabled for the queried object.
 *
 * @since 4.0.0
 */
class Included extends CommonMeta\Included {}