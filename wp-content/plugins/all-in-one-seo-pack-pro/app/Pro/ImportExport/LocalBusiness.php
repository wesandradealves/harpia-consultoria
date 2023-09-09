<?php
namespace AIOSEO\Plugin\Pro\ImportExport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Local Business settings from other plugins.
 *
 * @since 4.0.0
 */
abstract class LocalBusiness {
	/**
	 * Migrates the Local Business business type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $businessType The business type.
	 * @return void
	 */
	protected function migrateLocalBusinessType( $businessType ) {
		if ( ! $businessType ) {
			return;
		}

		if ( in_array( $businessType, $this->getLocalBusinessTypes(), true ) ) {
			aioseo()->options->localBusiness->locations->business->businessType = $businessType;

			return;
		}

		$addon = aioseo()->addons->getAddon( 'aioseo-local-business' );
		if ( ! $addon->installed ) {
			return;
		}

		$notification = Models\Notification::getNotificationByName( 'import-local-business-type' );
		if ( $notification->notification_name ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'import-local-business-type',
			'title'             => __( 'Re-Enter Business Type in Local Business', 'aioseo-pro' ),
			'content'           => sprintf(
				// Translators: 1 - The business type.
				__( 'For technical reasons, we were unable to migrate the business type you entered for your Local Business schema markup.
				Please enter it (%1$s) again by using the dropdown menu.', 'aioseo-pro' ),
				"<strong>$businessType</strong>"
			),
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
			'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-type&aioseo-highlight=info-business-type:locations',
			'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
			'button2_action'    => 'http://action#notification/import-local-business-type-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Migrates the Local Business country.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $country The country.
	 * @return void
	 */
	protected function migrateLocalBusinessCountry( $country ) {
		if ( ! $country ) {
			return;
		}

		foreach ( \AIOSEO\Plugin\Pro\Migration\LocalBusiness::getSupportedCountries() as $value => $label ) {
			if ( $country === $label ) {
				aioseo()->options->localBusiness->locations->business->address->country = $value;

				return;
			}
		}

		$notification = Models\Notification::getNotificationByName( 'import-local-business-country' );
		if ( $notification->notification_name ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'import-local-business-country',
			'title'             => __( 'Re-Enter Country in Local Business', 'aioseo-pro' ),
			'content'           => sprintf(
				// Translators: 1 - The country.
				__( 'For technical reasons, we were unable to migrate the country you entered for your Local Business schema markup.
				Please enter it (%1$s) again by using the dropdown menu.', 'aioseo-pro' ),
				"<strong>$country</strong>"
			),
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'aioseo-pro' ),
			'button1_action'    => 'http://route#aioseo-local-seo&aioseo-scroll=info-business-address-row&aioseo-highlight=aioseo-local-business-business-country:business-info',
			'button2_label'     => __( 'Remind Me Later', 'aioseo-pro' ),
			'button2_action'    => 'http://action#notification/import-local-business-country-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Returns our supported Local Business types.
	 *
	 * @since 4.0.0
	 *
	 * @return array The list of supported business types.
	 */
	protected function getLocalBusinessTypes() {
		return [
			'LocalBusiness',
			'AnimalShelter',
			'ArchiveOrganization',
			'AutomotiveBusiness',
			'ChildCare',
			'Dentist',
			'DryCleaningOrLaundry',
			'EmergencyService',
			'EmploymentAgency',
			'EntertainmentBusiness',
			'FinancialService',
			'FoodEstablishment',
			'GovernmentOffice',
			'HealthAndBeautyBusiness',
			'HomeAndConstructionBusiness',
			'InternetCafe',
			'LegalService',
			'Library',
			'LodgingBusiness',
			'MedicalBusiness',
			'RadioStation',
			'RealEstateAgent',
			'RecyclingCenter',
			'SelfStorage',
			'ShoppingCenter',
			'SportsActivityLocation',
			'Store',
			'TelevisionStation',
			'TouristInformationCenter',
			'TravelAgency'
		];
	}

	/**
	 * Returns our supported Local Business currencies.
	 *
	 * @since 4.0.0
	 *
	 * @return array The list of supported currencies in JSON.
	 */
	protected function getLocalBusinessCurrencies() {
		return '[{ "symbol": "$", "label": "US Dollar", "value": "USD" },
		{ "symbol": "CA$", "label": "Canadian Dollar", "value": "CAD" },
		{ "symbol": "€", "label": "Euro", "value": "EUR" },
		{ "symbol": "Ƀ", "label": "Bitcoin", "value": "BTC" },
		{ "symbol": "AED", "label": "United Arab Emirates Dirham", "value": "AED" },
		{ "symbol": "Af", "label": "Afghan Afghani", "value": "AFN" },
		{ "symbol": "ALL", "label": "Albanian Lek", "value": "ALL" },
		{ "symbol": "AMD", "label": "Armenian Dram", "value": "AMD" },
		{ "symbol": "AR$", "label": "Argentine Peso", "value": "ARS" },
		{ "symbol": "AU$", "label": "Australian Dollar", "value": "AUD" },
		{ "symbol": "man.", "label": "Azerbaijani Manat", "value": "AZN" },
		{ "symbol": "KM", "label": "Bosnia-Herzegovina Convertible Mark", "value": "BAM" },
		{ "symbol": "Tk", "label": "Bangladeshi Taka", "value": "BDT" },
		{ "symbol": "BGN", "label": "Bulgarian Lev", "value": "BGN" },
		{ "symbol": "BD", "label": "Bahraini Dinar", "value": "BHD" },
		{ "symbol": "FBu", "label": "Burundian Franc", "value": "BIF" },
		{ "symbol": "BN$", "label": "Brunei Dollar", "value": "BND" },
		{ "symbol": "Bs", "label": "Bolivian Boliviano", "value": "BOB" },
		{ "symbol": "R$", "label": "Brazilian Real", "value": "BRL" },
		{ "symbol": "BWP", "label": "Botswanan Pula", "value": "BWP" },
		{ "symbol": "Br", "label": "Belarusian Ruble", "value": "BYN" },
		{ "symbol": "BZ$", "label": "Belize Dollar", "value": "BZD" },
		{ "symbol": "CDF", "label": "Congolese Franc", "value": "CDF" },
		{ "symbol": "CHF", "label": "Swiss Franc", "value": "CHF" },
		{ "symbol": "CL$", "label": "Chilean Peso", "value": "CLP" },
		{ "symbol": "CN¥", "label": "Chinese Yuan", "value": "CNY" },
		{ "symbol": "CO$", "label": "Colombian Peso", "value": "COP" },
		{ "symbol": "₡", "label": "Costa Rican Colón", "value": "CRC" },
		{ "symbol": "CV$", "label": "Cape Verdean Escudo", "value": "CVE" },
		{ "symbol": "Kč", "label": "Czech Republic Koruna", "value": "CZK" },
		{ "symbol": "Fdj", "label": "Djiboutian Franc", "value": "DJF" },
		{ "symbol": "Dkr", "label": "Danish Krone", "value": "DKK" },
		{ "symbol": "RD$", "label": "Dominican Peso", "value": "DOP" },
		{ "symbol": "DA", "label": "Algerian Dinar", "value": "DZD" },
		{ "symbol": "Ekr", "label": "Estonian Kroon", "value": "EEK" },
		{ "symbol": "EGP", "label": "Egyptian Pound", "value": "EGP" },
		{ "symbol": "Nfk", "label": "Eritrean Nakfa", "value": "ERN" },
		{ "symbol": "Br", "label": "Ethiopian Birr", "value": "ETB" },
		{ "symbol": "£", "label": "British Pound Sterling", "value": "GBP" },
		{ "symbol": "GEL", "label": "Georgian Lari", "value": "GEL" },
		{ "symbol": "GH₵", "label": "Ghanaian Cedi", "value": "GHS" },
		{ "symbol": "FG", "label": "Guinean Franc", "value": "GNF" },
		{ "symbol": "GTQ", "label": "Guatemalan Quetzal", "value": "GTQ" },
		{ "symbol": "HK$", "label": "Hong Kong Dollar", "value": "HKD" },
		{ "symbol": "HNL", "label": "Honduran Lempira", "value": "HNL" },
		{ "symbol": "kn", "label": "Croatian Kuna", "value": "HRK" },
		{ "symbol": "Ft", "label": "Hungarian Forint", "value": "HUF" },
		{ "symbol": "Rp", "label": "Indonesian Rupiah", "value": "IDR" },
		{ "symbol": "₪", "label": "Israeli New Sheqel", "value": "ILS" },
		{ "symbol": "Rs", "label": "Indian Rupee", "value": "INR" },
		{ "symbol": "IQD", "label": "Iraqi Dinar", "value": "IQD" },
		{ "symbol": "IRR", "label": "Iranian Rial", "value": "IRR" },
		{ "symbol": "Ikr", "label": "Icelandic Króna", "value": "ISK" },
		{ "symbol": "J$", "label": "Jamaican Dollar", "value": "JMD" },
		{ "symbol": "JD", "label": "Jordanian Dinar", "value": "JOD" },
		{ "symbol": "¥", "label": "Japanese Yen", "value": "JPY" },
		{ "symbol": "Ksh", "label": "Kenyan Shilling", "value": "KES" },
		{ "symbol": "KHR", "label": "Cambodian Riel", "value": "KHR" },
		{ "symbol": "CF", "label": "Comorian Franc", "value": "KMF" },
		{ "symbol": "₩", "label": "South Korean Won", "value": "KRW" },
		{ "symbol": "KD", "label": "Kuwaiti Dinar", "value": "KWD" },
		{ "symbol": "KZT", "label": "Kazakhstani Tenge", "value": "KZT" },
		{ "symbol": "LB£", "label": "Lebanese Pound", "value": "LBP" },
		{ "symbol": "SLRs", "label": "Sri Lankan Rupee", "value": "LKR" },
		{ "symbol": "Lt", "label": "Lithuanian Litas", "value": "LTL" },
		{ "symbol": "Ls", "label": "Latvian Lats", "value": "LVL" },
		{ "symbol": "LD", "label": "Libyan Dinar", "value": "LYD" },
		{ "symbol": "MAD", "label": "Moroccan Dirham", "value": "MAD" },
		{ "symbol": "MDL", "label": "Moldovan Leu", "value": "MDL" },
		{ "symbol": "MGA", "label": "Malagasy Ariary", "value": "MGA" },
		{ "symbol": "MKD", "label": "Macedonian Denar", "value": "MKD" },
		{ "symbol": "MMK", "label": "Myanma Kyat", "value": "MMK" },
		{ "symbol": "MOP$", "label": "Macanese Pataca", "value": "MOP" },
		{ "symbol": "MURs", "label": "Mauritian Rupee", "value": "MUR" },
		{ "symbol": "MX$", "label": "Mexican Peso", "value": "MXN" },
		{ "symbol": "RM", "label": "Malaysian Ringgit", "value": "MYR" },
		{ "symbol": "MTn", "label": "Mozambican Metical", "value": "MZN" },
		{ "symbol": "N$", "label": "Namibian Dollar", "value": "NAD" },
		{ "symbol": "₦", "label": "Nigerian Naira", "value": "NGN" },
		{ "symbol": "C$", "label": "Nicaraguan Córdoba", "value": "NIO" },
		{ "symbol": "Nkr", "label": "Norwegian Krone", "value": "NOK" },
		{ "symbol": "NPRs", "label": "Nepalese Rupee", "value": "NPR" },
		{ "symbol": "NZ$", "label": "New Zealand Dollar", "value": "NZD" },
		{ "symbol": "OMR", "label": "Omani Rial", "value": "OMR" },
		{ "symbol": "B/.", "label": "Panamanian Balboa", "value": "PAB" },
		{ "symbol": "S/.", "label": "Peruvian Nuevo Sol", "value": "PEN" },
		{ "symbol": "₱", "label": "Philippine Peso", "value": "PHP" },
		{ "symbol": "PKRs", "label": "Pakistani Rupee", "value": "PKR" },
		{ "symbol": "zł", "label": "Polish Zloty", "value": "PLN" },
		{ "symbol": "₲", "label": "Paraguayan Guarani", "value": "PYG" },
		{ "symbol": "QR", "label": "Qatari Rial", "value": "QAR" },
		{ "symbol": "RON", "label": "Romanian Leu", "value": "RON" },
		{ "symbol": "din.", "label": "Serbian Dinar", "value": "RSD" },
		{ "symbol": "RUB", "label": "Russian Ruble", "value": "RUB" },
		{ "symbol": "RWF", "label": "Rwandan Franc", "value": "RWF" },
		{ "symbol": "SR", "label": "Saudi Riyal", "value": "SAR" },
		{ "symbol": "SDG", "label": "Sudanese Pound", "value": "SDG" },
		{ "symbol": "Skr", "label": "Swedish Krona", "value": "SEK" },
		{ "symbol": "S$", "label": "Singapore Dollar", "value": "SGD" },
		{ "symbol": "Ssh", "label": "Somali Shilling", "value": "SOS" },
		{ "symbol": "SY£", "label": "Syrian Pound", "value": "SYP" },
		{ "symbol": "฿", "label": "Thai Baht", "value": "THB" },
		{ "symbol": "DT", "label": "Tunisian Dinar", "value": "TND" },
		{ "symbol": "T$", "label": "Tongan Paʻanga", "value": "TOP" },
		{ "symbol": "TL", "label": "Turkish Lira", "value": "TRY" },
		{ "symbol": "TT$", "label": "Trinidad and Tobago Dollar", "value": "TTD" },
		{ "symbol": "NT$", "label": "New Taiwan Dollar", "value": "TWD" },
		{ "symbol": "TSh", "label": "Tanzanian Shilling", "value": "TZS" },
		{ "symbol": "₴", "label": "Ukrainian Hryvnia", "value": "UAH" },
		{ "symbol": "USh", "label": "Ugandan Shilling", "value": "UGX" },
		{ "symbol": "$U", "label": "Uruguayan Peso", "value": "UYU" },
		{ "symbol": "UZS", "label": "Uzbekistan Som", "value": "UZS" },
		{ "symbol": "Bs.F.", "label": "Venezuelan Bolívar", "value": "VEF" },
		{ "symbol": "₫", "label": "Vietnamese Dong", "value": "VND" },
		{ "symbol": "FCFA", "label": "CFA Franc BEAC", "value": "XAF" },
		{ "symbol": "CFA", "label": "CFA Franc BCEAO", "value": "XOF" },
		{ "symbol": "YR", "label": "Yemeni Rial", "value": "YER" },
		{ "symbol": "R", "label": "South African Rand", "value": "ZAR" },
		{ "symbol": "ZK", "label": "Zambian Kwacha", "value": "ZMK" },
		{ "symbol": "ZWL$", "label": "Zimbabwean Dollar", "value": "ZWL" }]';
	}

	/**
	 * Prepare Price Range value.
	 *
	 * @since 4.1.3
	 *
	 * @param string $priceRange The price range to prepare.
	 * @return string            The prepared price range.
	 */
	protected function preparePriceRange( $priceRange ) {
		$count = substr_count( $priceRange, '$' );
		if ( 0 === $count ) {
			return '';
		}

		if ( 5 < $count ) {
			$count = 5;
		}

		$preparedPriceRange = '';
		for ( $i = 1; $i <= $count; $i++ ) {
			$preparedPriceRange .= '$';
		}

		return $preparedPriceRange;
	}
}