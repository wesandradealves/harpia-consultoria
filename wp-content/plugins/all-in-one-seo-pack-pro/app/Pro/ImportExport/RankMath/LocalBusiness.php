<?php
namespace AIOSEO\Plugin\Pro\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Local Business settings.
 *
 * These are contained in the Title & Meta section of Rank Math.
 *
 * @since 4.0.0
 */
class LocalBusiness extends ImportExport\LocalBusiness {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'rank-math-options-titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		if ( isset( $this->options['local_business_type'] ) ) {
			$this->migrateLocalBusinessType( $this->options['local_business_type'] );
		}
		$this->migrateLocalBusinessAddress();
		$this->migrateLocalBusinessPriceRange();
		$this->migrateOpeningHourSettings();
	}

		/**
	 * Migrates the Local Business settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessSettings() {
		$this->migrateLocalBusinessPriceRange();
		$this->migrateLocalBusinessAddress();
	}

	/**
	 * Migrates the Local Business price range.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessPriceRange() {
		if ( ! empty( $this->options['price_range'] ) ) {
			$priceRange = $this->preparePriceRange( $this->options['price_range'] );
			if ( ! empty( $priceRange ) ) {
				aioseo()->options->localBusiness->locations->business->payment->priceRange = $priceRange;
			}
		}
	}

	/**
	 * Migrates the Local Business address.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessAddress() {
		if ( empty( $this->options['local_address'] ) ) {
			return;
		}

		if ( isset( $this->options['local_address']['addressCountry'] ) ) {
			$this->migrateLocalBusinessCountry( $this->options['local_address']['addressCountry'] );
		}

		$settings = [
			'streetAddress'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'streetLine1' ] ],
			'addressLocality' => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'city' ] ],
			'addressRegion'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'state' ] ],
			'postalCode'      => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'zipCode' ] ],
		];

		aioseo()->importExport->rankMath->helpers->mapOldToNew( $settings, $this->options['local_address'] );
	}

	/**
	 * Migrates the Local Business opening hour settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateOpeningHourSettings() {
		if ( ! empty( $this->options['opening_hours_format'] ) ) {
			aioseo()->options->localBusiness->openingHours->use24hFormat = 'off' === $this->options['opening_hours_format'];
		}

		if ( empty( $this->options['opening_hours'] ) ) {
			return;
		}

		$migratedDays = [];
		foreach ( $this->options['opening_hours'] as $day ) {
			if ( empty( $day['day'] ) || empty( $day['time'] ) || in_array( $day['day'], $migratedDays, true ) ) {
				continue;
			}

			$dayName = lcfirst( $day['day'] );
			preg_match( '#^(\d{2}:\d{2})-(\d{2}:\d{2})$#', $day['time'], $matches );
			if ( ! empty( $matches[0] ) ) {
				aioseo()->options->localBusiness->openingHours->days->$dayName->openTime = $matches[0];
			}

			if ( ! empty( $matches[1] ) ) {
				aioseo()->options->localBusiness->openingHours->days->$dayName->closeTime = $matches[1];
			}

			$migratedDays[] = $day['day'];
		}
	}
}