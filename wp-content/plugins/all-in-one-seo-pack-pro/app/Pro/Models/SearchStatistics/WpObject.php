<?php
namespace AIOSEO\Plugin\Pro\Models\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models as CommonModels;

/**
 * The Object DB Model.
 * It's called WPObject because Object is a reserved word in PHP.
 *
 * @since 4.3.0
 */
class WpObject extends CommonModels\Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_search_statistics_objects';

	/**
	 * Fields that should be numeric values.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	protected $numericFields = [ 'id', 'object_id' ];

	/**
	 * List of fields that should be hidden when serialized.
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Updates (or inserts) a given row.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $data The new data.
	 * @return void
	 */
	public static function update( $data ) {
		if (
			empty( $data['object_id'] ) ||
			empty( $data['object_path'] )
		) {
			return;
		}

		$data += [
			'object_path_hash' => sha1( $data['object_path'] ),
		];

		$wpObject = aioseo()->core->db->start( 'aioseo_search_statistics_objects' )
			->where( 'object_id', $data['object_id'] )
			->where( 'object_type', $data['object_type'] )
			->run()
			->model( 'AIOSEO\\Plugin\\Pro\\Models\\SearchStatistics\\WpObject' );

		$wpObject->set( $data );
		$wpObject->save();
	}

	/**
	 * Bulk inserts a set of rows.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $rows The rows to insert.
	 * @return void
	 */
	public static function bulkInsert( $rows ) {
		$currentDate = gmdate( 'Y-m-d H:i:s' );

		$addValues = [];
		foreach ( $rows as $row ) {
			$row = json_decode( wp_json_encode( $row ), true );

			if (
				empty( $row['object_id'] ) ||
				empty( $row['object_type'] ) ||
				empty( $row['object_subtype'] ) ||
				empty( $row['object_path'] )
			) {
				continue;
			}

			$addValues[] = vsprintf(
				"(%d, '%s', '%s', '%s', '%s', %d, '$currentDate', '$currentDate')",
				[
					$row['object_id'],
					$row['object_type'],
					$row['object_subtype'],
					$row['object_path'],
					sha1( $row['object_path'] ),
					(int) $row['seo_score']
				]
			);
		}

		if ( empty( $addValues ) ) {
			return;
		}

		$tableName         = aioseo()->core->db->prefix . 'aioseo_search_statistics_objects';
		$implodedAddValues = implode( ',', $addValues );
		aioseo()->core->db->execute(
			"INSERT INTO $tableName (`object_id`, `object_type`, `object_subtype`, `object_path`, `object_path_hash`, `seo_score`, `created`, `updated`)
			VALUES $implodedAddValues"
		);
	}
}