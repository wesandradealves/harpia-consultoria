<?php
namespace AIOSEO\Plugin\Pro\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils\Templates as CommonTemplates;

/**
 * Class Templates
 *
 * @since 4.2.5
 *
 * @package AIOSEO\Plugin\Pro\Utils
 */
class Templates extends CommonTemplates {
	/**
	 * Paths were our template files are located.
	 *
	 * @since 4.2.5
	 *
	 * @var string Array of paths.
	 */
	protected $paths = [
		'app/Common/Views',
		'app/Pro/Views',
	];
}