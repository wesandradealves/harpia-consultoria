<?php
namespace AIOSEO\Plugin\Pro\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Local Business settings.
 *
 * @since 4.1.4
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
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_pro_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		if ( ! empty( $this->options['seopress_local_business_type'] ) ) {
			$this->migrateLocalBusinessType( $this->options['seopress_local_business_type'] );
		}

		$this->migrateLocalBusinessAddress();
		$this->migrateLocalBusinessPriceRange();
		$this->migrateOpeningHourSettings();
	}

	/**
	 * Migrates the Local Business price range.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateLocalBusinessPriceRange() {
		if ( ! empty( $this->options['seopress_local_business_price_range'] ) ) {
			$priceRange = $this->preparePriceRange( $this->options['seopress_local_business_price_range'] );
			if ( ! empty( $priceRange ) ) {
				aioseo()->options->localBusiness->locations->business->payment->priceRange = $priceRange;
			}
		}
	}

	/**
	 * Migrates the Local Business address.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateLocalBusinessAddress() {
		if ( ! empty( $this->options['seopress_local_business_address_country'] ) ) {
			$this->migrateLocalBusinessCountry( $this->options['seopress_local_business_address_country'] );
		}

		$settings = [
			'seopress_local_business_street_address'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'streetLine1' ] ],
			'seopress_local_business_address_locality' => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'city' ] ],
			'seopress_local_business_address_region'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'state' ] ],
			'seopress_local_business_postal_code'      => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'zipCode' ] ],
			'seopress_local_business_phone'            => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'contact', 'phone' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Local Business Opening Hours settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateOpeningHourSettings() {
		$openingHours = $this->options['seopress_local_business_opening_hours'];
		if ( empty( $openingHours ) ) {
			return;
		}

		aioseo()->options->localBusiness->openingHours->use24hFormat = true;

		$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

		foreach ( $openingHours as $key => $settings ) {
			if ( ! aioseo()->options->localBusiness->openingHours->days->has( $days[ $key ] ) ) {
				continue;
			}

			if ( ! empty( $settings['open'] ) ) {
				aioseo()->options->localBusiness->openingHours->days->{ $days[ $key ] }->open24h = true;
				continue;
			}

			$meridiem = 'am';
			$openTime = $settings[ $meridiem ]['start']['hours'] . ':' . $settings[ $meridiem ]['start']['mins'];

			if ( ! empty( $settings['pm']['open'] ) ) {
				$meridiem = 'pm';
			}

			$closeTime = $settings[ $meridiem ]['end']['hours'] . ':' . $settings[ $meridiem ]['end']['mins'];

			aioseo()->options->localBusiness->openingHours->days->{ $days[ $key ] }->openTime = $openTime;
			aioseo()->options->localBusiness->openingHours->days->{ $days[ $key ] }->closeTime = $closeTime;
		}
	}
}