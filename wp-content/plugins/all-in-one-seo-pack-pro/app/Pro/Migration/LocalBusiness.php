<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Local Business SEO settings from V3.
 *
 * @since 4.0.0
 */
class LocalBusiness {
	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->oldOptions = aioseo()->migration->oldOptions;

		if ( empty( $this->oldOptions['modules']['aiosp_schema_local_business_options'] ) ) {
			return;
		}

		$this->migrateLocalBusinessAddress();
		$this->migrateLocalBusinessPhoneNumber();
		$this->migrateLocalBusinessOpeningHours();
		$this->migrateLocalBusinessPriceRange();

		$settings = [
			'aiosp_schema_local_business_aioseo_business_name'  => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'name' ] ],
			'aiosp_schema_local_business_aioseo_business_type'  => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'businessType' ] ],
			'aiosp_schema_local_business_aioseo_business_image' => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'image' ] ],
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $this->oldOptions['modules']['aiosp_schema_local_business_options'] );
	}

	/**
	 * Migrates the Local Business address.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessAddress() {
		if ( empty( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_postal_address'] ) ) {
			return;
		}

		$settings = [
			'street_address'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'streetLine1' ] ],
			'address_locality' => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'city' ] ],
			'address_region'   => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'state' ] ],
			'postal_code'      => [ 'type' => 'string', 'newOption' => [ 'localBusiness', 'locations', 'business', 'address', 'zipCode' ] ],
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_postal_address'] );

		if ( ! empty( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_postal_address']['address_country'] ) ) {
			$country = $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_postal_address']['address_country'];

			foreach ( self::getSupportedCountries() as $value => $label ) {
				if ( $country === $label ) {
					aioseo()->options->localBusiness->locations->business->address->country = $value;

					return;
				}
			}

			$notification = Models\Notification::getNotificationByName( 'v3-migration-local-business-country' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'v3-migration-local-business-country',
				'title'             => __( 'Re-Enter Country in Local Business', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - The country.
					__( 'For technical reasons, we were unable to migrate the country you entered for your Local Business schema markup. Please enter it (%1$s) again by using the dropdown menu.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					"<strong>$country</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
				'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-address-row&aioseo-highlight=info-business-address-row:locations',
				'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
				'button2_action'    => 'http://action#notification/v3-migration-local-business-country-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );
		}
	}

	/**
	 * Migrates the Local Business phone number.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessPhoneNumber() {
		if ( empty( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_telephone'] ) ) {
			return;
		}

		$phoneNumber = $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_telephone'];
		if ( ! preg_match( '#\+\d+#', $phoneNumber ) ) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-local-business-number' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'v3-migration-local-business-number',
				'title'             => __( 'Invalid Phone Number for Local SEO', 'aioseo-pro' ),
				'content'           => sprintf(
					// Translators: 1 - The phone number.
					__( 'The phone number that you previously entered for your Local Business schema markup is invalid. As it needs to be internationally formatted, please enter it (%1$s) again with the country code, e.g. +1 (555) 555-1234.', 'aioseo-pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					"<strong>$phoneNumber</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
				'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-contact-row&aioseo-highlight=info-business-contact-row:global-settings',
				'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
				'button2_action'    => 'http://action#notification/v3-migration-local-business-number-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}
		aioseo()->options->localBusiness->locations->business->contact->phone = $phoneNumber;
	}

	/**
	 * Migrates the Local Business opening hours.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessOpeningHours() {
		if ( empty( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_time_0_opening_days'] ) ) {
			return;
		}

		$openedDays = array_map( function( $day ) {
			return lcfirst( $day );
		}, $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_time_0_opening_days'] );

		$days = aioseo()->options->localBusiness->openingHours->days->all();
		foreach ( $days as $day => $values ) {
			if ( ! in_array( $day, $openedDays, true ) ) {
				aioseo()->options->localBusiness->openingHours->days->$day->closed = true;
			}

			aioseo()->options->localBusiness->openingHours->days->$day->openTime  = $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_time_0_opens']; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			aioseo()->options->localBusiness->openingHours->days->$day->closeTime = $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_time_0_closes']; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}
	}

	/**
	 * Migrates the Local Business price range.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLocalBusinessPriceRange() {
		if ( empty( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_price_range'] ) ) {
			return;
		}

		$value = intval( $this->oldOptions['modules']['aiosp_schema_local_business_options']['aiosp_schema_local_business_aioseo_price_range'] );
		if ( ! $value ) {
			return;
		}

		$priceRange = '';
		for ( $i = 1; $i <= $value; $i++ ) {
			$priceRange .= '$';
		}
		aioseo()->options->localBusiness->locations->business->payment->priceRange = $priceRange;
	}

	/**
	 * Returns the countries from our dropdown in V4.
	 *
	 * @since 4.0.0
	 *
	 * @return array The list of supported countries.
	 */
	public static function getSupportedCountries() {
		return [
			'AD' => 'AD',
			'AE' => 'AE',
			'AF' => 'AF',
			'AG' => 'AG',
			'AI' => 'AI',
			'AL' => 'AL',
			'AM' => 'AM',
			'AO' => 'AO',
			'AQ' => 'AQ',
			'AR' => 'AR',
			'AS' => 'AS',
			'AT' => 'AT',
			'AU' => 'AU',
			'AW' => 'AW',
			'AX' => 'AX',
			'AZ' => 'AZ',
			'BA' => 'BA',
			'BB' => 'BB',
			'BD' => 'BD',
			'BE' => 'BE',
			'BF' => 'BF',
			'BG' => 'BG',
			'BH' => 'BH',
			'BI' => 'BI',
			'BJ' => 'BJ',
			'BL' => 'BL',
			'BM' => 'BM',
			'BN' => 'BN',
			'BO' => 'BO',
			'BQ' => 'BQ',
			'BR' => 'BR',
			'BS' => 'BS',
			'BT' => 'BT',
			'BV' => 'BV',
			'BW' => 'BW',
			'BY' => 'BY',
			'BZ' => 'BZ',
			'CA' => 'CA',
			'CC' => 'CC',
			'CD' => 'CD',
			'CF' => 'CF',
			'CG' => 'CG',
			'CH' => 'CH',
			'CI' => 'CI',
			'CK' => 'CK',
			'CL' => 'CL',
			'CM' => 'CM',
			'CN' => 'CN',
			'CO' => 'CO',
			'CR' => 'CR',
			'CU' => 'CU',
			'CV' => 'CV',
			'CW' => 'CW',
			'CX' => 'CX',
			'CY' => 'CY',
			'CZ' => 'CZ',
			'DE' => 'DE',
			'DJ' => 'DJ',
			'DK' => 'DK',
			'DM' => 'DM',
			'DO' => 'DO',
			'DZ' => 'DZ',
			'EC' => 'EC',
			'EE' => 'EE',
			'EG' => 'EG',
			'EH' => 'EH',
			'ER' => 'ER',
			'ES' => 'ES',
			'ET' => 'ET',
			'FI' => 'FI',
			'FJ' => 'FJ',
			'FK' => 'FK',
			'FM' => 'FM',
			'FO' => 'FO',
			'FR' => 'FR',
			'GA' => 'GA',
			'GB' => 'UK',
			'GD' => 'GD',
			'GE' => 'GE',
			'GF' => 'GF',
			'GG' => 'GG',
			'GH' => 'GH',
			'GI' => 'GI',
			'GL' => 'GL',
			'GM' => 'GM',
			'GN' => 'GN',
			'GP' => 'GP',
			'GQ' => 'GQ',
			'GR' => 'GR',
			'GS' => 'GS',
			'GT' => 'GT',
			'GU' => 'GU',
			'GW' => 'GW',
			'GY' => 'GY',
			'HK' => 'HK',
			'HM' => 'HM',
			'HN' => 'HN',
			'HR' => 'HR',
			'HT' => 'HT',
			'HU' => 'HU',
			'ID' => 'ID',
			'IE' => 'IE',
			'IL' => 'IL',
			'IM' => 'IM',
			'IN' => 'IN',
			'IO' => 'IO',
			'IQ' => 'IQ',
			'IR' => 'IR',
			'IS' => 'IS',
			'IT' => 'IT',
			'JE' => 'JE',
			'JM' => 'JM',
			'JO' => 'JO',
			'JP' => 'JP',
			'KE' => 'KE',
			'KG' => 'KG',
			'KH' => 'KH',
			'KI' => 'KI',
			'KM' => 'KM',
			'KN' => 'KN',
			'KP' => 'KP',
			'KR' => 'KR',
			'KW' => 'KW',
			'KY' => 'KY',
			'KZ' => 'KZ',
			'LA' => 'LA',
			'LB' => 'LB',
			'LC' => 'LC',
			'LI' => 'LI',
			'LK' => 'LK',
			'LR' => 'LR',
			'LS' => 'LS',
			'LT' => 'LT',
			'LU' => 'LU',
			'LV' => 'LV',
			'LY' => 'LY',
			'MA' => 'MA',
			'MC' => 'MC',
			'MD' => 'MD',
			'ME' => 'ME',
			'MF' => 'MF',
			'MG' => 'MG',
			'MH' => 'MH',
			'MK' => 'MK',
			'ML' => 'ML',
			'MM' => 'MM',
			'MN' => 'MN',
			'MO' => 'MO',
			'MP' => 'MP',
			'MQ' => 'MQ',
			'MR' => 'MR',
			'MS' => 'MS',
			'MT' => 'MT',
			'MU' => 'MU',
			'MV' => 'MV',
			'MW' => 'MW',
			'MX' => 'MX',
			'MY' => 'MY',
			'MZ' => 'MZ',
			'NA' => 'NA',
			'NC' => 'NC',
			'NE' => 'NE',
			'NF' => 'NF',
			'NG' => 'NG',
			'NI' => 'NI',
			'NL' => 'NL',
			'NO' => 'NO',
			'NP' => 'NP',
			'NR' => 'NR',
			'NU' => 'NU',
			'NZ' => 'NZ',
			'OM' => 'OM',
			'PA' => 'PA',
			'PE' => 'PE',
			'PF' => 'PF',
			'PG' => 'PG',
			'PH' => 'PH',
			'PK' => 'PK',
			'PL' => 'PL',
			'PM' => 'PM',
			'PN' => 'PN',
			'PR' => 'PR',
			'PS' => 'PS',
			'PT' => 'PT',
			'PW' => 'PW',
			'PY' => 'PY',
			'QA' => 'QA',
			'RE' => 'RE',
			'RO' => 'RO',
			'RS' => 'RS',
			'RU' => 'RU',
			'RW' => 'RW',
			'SA' => 'SA',
			'SB' => 'SB',
			'SC' => 'SC',
			'SD' => 'SD',
			'SE' => 'SE',
			'SG' => 'SG',
			'SH' => 'SH',
			'SI' => 'SI',
			'SJ' => 'SJ',
			'SK' => 'SK',
			'SL' => 'SL',
			'SM' => 'SM',
			'SN' => 'SN',
			'SO' => 'SO',
			'SR' => 'SR',
			'SS' => 'SS',
			'ST' => 'ST',
			'SV' => 'SV',
			'SX' => 'SX',
			'SY' => 'SY',
			'SZ' => 'SZ',
			'TC' => 'TC',
			'TD' => 'TD',
			'TF' => 'TF',
			'TG' => 'TG',
			'TH' => 'TH',
			'TJ' => 'TJ',
			'TK' => 'TK',
			'TL' => 'TL',
			'TM' => 'TM',
			'TN' => 'TN',
			'TO' => 'TO',
			'TR' => 'TR',
			'TT' => 'TT',
			'TV' => 'TV',
			'TW' => 'TW',
			'TZ' => 'TZ',
			'UA' => 'UA',
			'UG' => 'UG',
			'UM' => 'UM',
			'US' => 'US',
			'UY' => 'UY',
			'UZ' => 'UZ',
			'VA' => 'VA',
			'VC' => 'VC',
			'VE' => 'VE',
			'VG' => 'VG',
			'VI' => 'VI',
			'VN' => 'VN',
			'VU' => 'VU',
			'WF' => 'WF',
			'WS' => 'WS',
			'YE' => 'YE',
			'YT' => 'YT',
			'ZA' => 'ZA',
			'ZM' => 'ZM',
			'ZW' => 'ZW'
		];
	}
}