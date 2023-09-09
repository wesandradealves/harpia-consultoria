<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;

/**
 * Handles the keywords.
 *
 * @since 4.0.0
 */
class Keywords extends CommonMeta\Keywords {
	/**
	 * Returns the main keywords.
	 *
	 * @since 4.0.0
	 *
	 * @return string The keywords.
	 */
	public function getKeywords() {
		if ( ! aioseo()->options->searchAppearance->advanced->useKeywords ) {
			return '';
		}

		$keywords = parent::getKeywords();
		if ( ! empty( $keywords ) ) {
			return $keywords;
		}

		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return '';
		}

		return $this->getAllTermKeywords();
	}

	/**
	 * Returns the keywords.
	 *
	 * @since 4.0.0
	 *
	 * @return string A comma-separated list of unique keywords.
	 */
	public function getAllTermKeywords() {
		$keywords = [];
		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->keywords ) ) {
			$keywords = $this->extractMetaKeywords( $metaData->keywords );
		}

		return $this->prepareKeywords( $keywords );
	}
}