<?php
namespace AIOSEO\Plugin\Pro\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Migration as CommonMigration;

/**
 * Updates and holds the old options from V3.
 *
 * @since 4.0.0
 */
class OldOptions extends CommonMigration\OldOptions {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param array $oldOptions The old options. We pass it in directly via the Importer/Exporter.
	 */
	public function __construct( $oldOptions = [] ) {
		parent::__construct( $oldOptions );
		$this->oldOptions = ! empty( $oldOptions ) ? $oldOptions : get_option( 'aioseop_options' );

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		$this->doFeatureUpdates();
		$this->fixSettingValues();
	}

	/**
	 * Runs a number of updates that are based on factors other than the plugin version.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function doFeatureUpdates() {
		if (
			isset( $this->oldOptions['version_feature_flags'] ) ||
			! isset( $this->oldOptions['version_feature_flags']['term_meta_migrated'] ) ||
			'yes' !== $this->oldOptions['version_feature_flags']['term_meta_migrated']
		) {
			$this->migrateTermMeta201603();
		}
	}

	/**
	 * Migrate old term meta to use native WP functions.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTermMeta201603() {
		global $wpdb;

		// Quit if the the term meta table is not present in the database.
		if ( intval( get_option( 'db_version' ) ) < 34370 ) {
			return false;
		}

		// Migrate tax_meta_% entries from options table.
		$termMeta = $wpdb->query(
			$wpdb->prepare(
				"SELECT option_name, option_value
				FROM `{$wpdb->prefix}options`
				WHERE option_name LIKE %s", [ 'tax_meta_%' ]
			)
		);

		if ( is_array( $termMeta ) ) {
			foreach ( $termMeta as $meta ) {
				$name         = $meta->option_name;
				$optionValue  = aioseo()->helpers->maybeUnserialize( $meta->option_value );
				$termid       = intval( str_replace( 'tax_meta_', '', $name ) );
				foreach ( $optionValue as $k => $v ) {
					add_term_meta( $termid, $k, $v, true );
				}
				delete_option( $name );
			}
		}

		$termMetaTable = "{$wpdb->prefix}taxonomymeta";
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', [ $termMetaTable ] ) ) === $termMetaTable ) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->termmeta} (term_id, meta_key, meta_value)
					SELECT taxm.taxonomy_id as term_id, taxm.meta_key as meta_key, taxm.meta_value as meta_value
					FROM {$wpdb->termmeta} termm
					RIGHT JOIN `{$wpdb->prefix}taxonomymeta` taxm
					ON (termm.term_id=taxm.taxonomy_id AND termm.meta_key=taxm.meta_key)
					WHERE %d = %d AND termm.meta_id IS NULL",
					[ 1, 1 ]
				)
			);
		}
	}
}