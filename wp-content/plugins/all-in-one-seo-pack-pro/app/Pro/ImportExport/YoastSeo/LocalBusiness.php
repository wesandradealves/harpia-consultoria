<?php
namespace AIOSEO\Plugin\Pro\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\ImportExport;
use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Local Business settings.
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
		$this->options = get_option( 'wpseo_local' );
		if ( empty( $this->options ) ) {
			return;
		}

		// Yoast SEO doesn't have a setting for this, so we'll use the Organization Name.
		aioseo()->options->localBusiness->locations->business->name = aioseo()->options->searchAppearance->global->schema->organizationName;

		if ( isset( $this->options['business_type'] ) ) {
			$this->migrateLocalBusinessType( $this->options['business_type'] );
		}
		if ( isset( $this->options['location_country'] ) ) {
			$this->migrateLocalBusinessCountry( $this->options['location_country'] );
		}
		$this->migrateLocalBusinessPhoneNumber();
		$this->migrateLocalBusinessFaxNumber();
		$this->migrateCurrenciesAccepted();
		$this->migrateOpeningHourSettings();

		$settings = [
			'location_address'          => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'streetLine1' ] ],
			'location_address_2'        => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'streetLine2' ] ],
			'location_city'             => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'city' ] ],
			'location_state'            => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'state' ] ],
			'location_zipcode'          => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'zipCode' ] ],
			'location_vat_id'           => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'ids', 'vat' ] ],
			'location_tax_id'           => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'ids', 'tax' ] ],
			'location_coc_id'           => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'ids', 'chamberOfCommerce' ] ],
			'location_price_range'      => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'payment', 'priceRange' ] ],
			'location_payment_accepted' => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'payment', 'methods' ] ],
			'location_area_served'      => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'areaServed' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Local Business phone number.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessPhoneNumber() {
		if ( empty( $this->options['location_phone'] ) ) {
			return;
		}

		$phoneNumber = $this->options['location_phone'];
		if ( ! preg_match( '#\+\d+#', $phoneNumber ) ) {
			$notification = Models\Notification::getNotificationByName( 'import-local-business-number' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'import-local-business-number',
				'title'             => __( 'Invalid Phone Number for Local SEO', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - The phone number.
					__( 'The phone number that you previously entered for your Local Business schema markup is invalid.
					As it needs to be internationally formatted, please enter it (%1$s) again with the country code, e.g. +1 (555) 555-1234.', 'aioseo-pro' ),
					"<strong>$phoneNumber</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
				'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-contact-row&aioseo-highlight=aioseo-local-business-phone-number:business-info',
				'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
				'button2_action'    => 'http://action#notification/import-local-business-number-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}
		aioseo()->options->localBusiness->locations->business->contact->phone = $phoneNumber;
	}

	/**
	 * Migrates the Local Business fax number.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessFaxNumber() {
		if ( empty( $this->options['location_fax'] ) ) {
			return;
		}

		$faxNumber = $this->options['location_fax'];
		if ( ! preg_match( '#\+\d+#', $faxNumber ) ) {
			$notification = Models\Notification::getNotificationByName( 'import-local-business-fax' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'import-local-business-fax',
				'title'             => __( 'Invalid Fax Number for Local SEO', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - The fax number.
					__( 'The fax number that you previously entered for your Local Business schema markup is invalid.
					As it needs to be internationally formatted, please enter it (%1$s) again with the country code, e.g. +1 (555) 555-1234.', 'aioseo-pro' ),
					"<strong>$faxNumber</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
				'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-contact-row&aioseo-highlight=aioseo-local-business-fax-number:business-info',
				'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
				'button2_action'    => 'http://action#notification/import-local-business-fax-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}
		aioseo()->options->localBusiness->locations->business->contact->fax = $faxNumber;
	}

	/**
	 * Migrates the Local Business accepted currencies.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateCurrenciesAccepted() {
		if ( empty( $this->options['location_currencies_accepted'] ) ) {
			return;
		}

		$currencies         = array_filter( explode( ',', $this->options['location_currencies_accepted'] ) );
		$dropdownCurrencies = json_decode( $this->getLocalBusinessCurrencies() );

		$supportedCurrencies    = [];
		$nonSupportedCurrencies = [];
		foreach ( $currencies as $currency ) {
			$currency = trim( $currency );

			$foundCurrency = false;
			foreach ( $dropdownCurrencies as $dropdownCurrency ) {
				if ( in_array( $currency, (array) $dropdownCurrency, true ) ) {
					$supportedCurrencies[] = $dropdownCurrency;
					$foundCurrency = true;
					continue;
				}
			}

			if ( $foundCurrency ) {
				continue;
			}
			$nonSupportedCurrencies[] = $currency;
		}

		aioseo()->options->localBusiness->locations->business->payment->currenciesAccepted = wp_json_encode( $supportedCurrencies );

		if ( count( $nonSupportedCurrencies ) ) {
			$notification = Models\Notification::getNotificationByName( 'import-local-business-currencies' );
			if ( $notification->notification_name ) {
				return;
			}

			$currencies = '<ul>';
			foreach ( $nonSupportedCurrencies as $currency ) {
				$currencies .= '<li>' . esc_html( $currency ) . '<li>';
			}
			$currencies .= '</ul>';

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'import-local-business-currencies',
				'title'             => __( 'Invalid Currencies for Local SEO', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - The phone number.
					__( 'One or more currencies that you previously entered for your Local Business schema markup are invalid.
					Please select these again using our dropdown menu.</br>%1$s', 'aioseo-pro' ),
					"<strong>$currencies</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
				'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-payment-info-row&aioseo-highlight=aioseo-local-business-currencies-accepted:business-info',
				'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
				'button2_action'    => 'http://action#notification/import-local-business-currencies-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );
		}
	}

	/**
	 * Migrates the Local Business opening hour settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateOpeningHourSettings() {
		if ( isset( $this->options['hide_opening_hours'] ) ) {
			aioseo()->options->localBusiness->openingHours->show = empty( $this->options['hide_opening_hours'] );
		}

		if ( isset( $this->options['open_247'] ) ) {
			aioseo()->options->localBusiness->openingHours->alwaysOpen = ! empty( $this->options['open_247'] );
		}

		if ( isset( $this->options['opening_hours_24h'] ) ) {
			aioseo()->options->localBusiness->openingHours->use24hFormat = ! empty( $this->options['opening_hours_24h'] );
		}

		$days = aioseo()->options->localBusiness->openingHours->days->all();
		foreach ( $days as $name => $values ) {
			if ( isset( $this->options[ "opening_hours_${name}_24h" ] ) ) {
				aioseo()->options->localBusiness->openingHours->days->$name->open24h = 'on' === $this->options[ "opening_hours_${name}_24h" ];
			}

			if ( ! empty( $this->options[ "opening_hours_${name}_from" ] ) ) {
				if ( 'closed' === $this->options[ "opening_hours_${name}_from" ] ) {
					aioseo()->options->localBusiness->openingHours->days->$name->closed = true;
					continue;
				}
				aioseo()->options->localBusiness->openingHours->days->$name->openTime = $this->options[ "opening_hours_${name}_from" ];
			}

			if ( ! empty( $this->options[ "opening_hours_${name}_to" ] ) ) {
				if ( 'closed' === $this->options[ "opening_hours_${name}_to" ] ) {
					aioseo()->options->localBusiness->openingHours->days->$name->closed = true;
					continue;
				}
				aioseo()->options->localBusiness->openingHours->days->$name->closeTime = $this->options[ "opening_hours_${name}_to" ];
			}
		}
	}
}