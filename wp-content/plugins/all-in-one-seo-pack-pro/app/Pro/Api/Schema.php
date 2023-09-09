<?php
namespace AIOSEO\Plugin\Pro\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains our schema related REST API endpoint callbacks.
 *
 * @since 4.2.5
 */
class Schema {
	/**
	 * Stores a new schema template.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function addTemplate( $request ) {
		$template = $request['template'];
		if ( empty( $template ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => 'No template was passed.'
			], 400 );
		}

		$templates   = aioseo()->internalOptions->internal->schema->templates;
		$templates[] = wp_json_encode( $template );

		aioseo()->internalOptions->internal->schema->templates = $templates;

		$templateObjects = self::templatesToObjects( $templates );

		return new \WP_REST_Response( [
			'success'   => true,
			'templates' => $templateObjects // NOTE: We need to send the templates as objects because the REST API will otherwise encode them a second time.
		], 200 );
	}

	/**
	 * Deletes an existing template.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteTemplate( $request ) {
		$templateId = $request['templateId'];
		if ( empty( $templateId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => 'No template ID was passed.'
			], 400 );
		}

		$templateObjects = self::templatesToObjects( aioseo()->internalOptions->internal->schema->templates );
		foreach ( $templateObjects as $index => $data ) {
			if ( $data->id === $templateId ) {
				unset( $templateObjects[ $index ] );
				break;
			}
		}

		// Reset the indexes.
		$templateObjects = array_values( $templateObjects );

		$templateJson = self::templatesToJson( $templateObjects );
		aioseo()->internalOptions->internal->schema->templates = $templateJson;

		return new \WP_REST_Response( [
			'success'   => true,
			'templates' => $templateObjects // NOTE: We need to send the templates as objects because the REST API will otherwise encode them a second time.
		], 200 );
	}

	/**
	 * Updates an existing template.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updateTemplate( $request ) {
		$template = $request['template'];
		if ( empty( $template ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => 'No template was passed.'
			], 400 );
		}

		$templateObjects = self::templatesToObjects( aioseo()->internalOptions->internal->schema->templates );
		foreach ( $templateObjects as $index => $data ) {
			if ( $data->id === $template['id'] ) {
				$templateObjects[ $index ] = $template;
				break;
			}
		}

		$templateJson = self::templatesToJson( $templateObjects );
		aioseo()->internalOptions->internal->schema->templates = $templateJson;

		return new \WP_REST_Response( [
			'success'   => true,
			'templates' => $templateObjects // NOTE: We need to send the templates as objects because the REST API will otherwise encode them a second time.
		], 200 );
	}

	/**
	 * Converts the templates from JSON to objects.
	 *
	 * @since 4.2.5
	 *
	 * @param  array[string] $templates A list of templates in JSON format.
	 * @return array[object]            A list of templates as objects.
	 */
	private static function templatesToObjects( $templates ) {
		return array_map( function ( $template ) {
			return json_decode( $template );
		}, $templates );
	}

	/**
	 * Converts the templates from objects to JSON.
	 *
	 * @since 4.2.5
	 *
	 * @param  array[object] $templates A list of templates as objects.
	 * @return array[string]            A list of templates in JSON format.
	 */
	private static function templatesToJson( $templates ) {
		return array_map( function ( $template ) {
			return wp_json_encode( $template );
		}, $templates );
	}

	/**
	 * Returns the output for a given post based on the given graphs.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getValidatorOutput( $request ) {
		$postId       = $request['postId'];
		$customGraphs = $request['customGraphs'];
		$graphs       = json_decode( wp_json_encode( $request['graphs'] ) );
		$default      = json_decode( wp_json_encode( $request['default'] ) );
		if ( empty( $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => 'No post ID was passed.'
			], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'output'  => aioseo()->schema->getValidatorOutput( $postId, $graphs, $customGraphs, $default )
		], 200 );
	}
}