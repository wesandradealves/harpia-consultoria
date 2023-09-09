<?php
namespace AIOSEO\Plugin\Pro\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Pro\Ai\Ai as Client;
use AIOSEO\Plugin\Common\Models;

/**
 * Contains the OpenAI class.
 *
 * @since 4.3.2
 */
class Ai {
	/**
	 * Generate title or description suggestions.
	 *
	 * @since 4.3.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generate( $request ) {
		$body        = $request->get_json_params();
		$type        = ! empty( $body['type'] ) ? sanitize_text_field( $body['type'] ) : '';
		$postId      = ! empty( $body['postId'] ) ? intval( $body['postId'] ) : 0;
		$postContent = ! empty( $body['postContent'] ) ? sanitize_text_field( $body['postContent'] ) : '';
		if ( ! $type || ! $postId || ! $postContent ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		$postContent = strip_shortcodes( wp_strip_all_tags( $postContent ) );
		$postContent = normalize_whitespace( $postContent );
		$postContent = preg_replace( '/\v+/', ' ', $postContent ); // Remove new lines.
		$postContent = wp_trim_words( $postContent, 800 );
		$postContent = html_entity_decode( $postContent );

		$maxTokens = 64;
		if ( 'description' === $type ) {
			$maxTokens = 120;
		}

		// Data for Davinci model.
		$prompt = 'Generate a short, engaging SEO title in one sentence, between 50 to 70 characters, in title casing, for this text: ' . $postContent;
		if ( 'description' === $type ) {
			$prompt = 'Generate a short, engaging meta description, counting less than 160 characters, in sentence casing, for this text: ' . $postContent;
		}

		// Data for GPT-3.5 Turbo model.
		$messages = [
			[
				'role'    => 'system',
				'content' => 'You generate short, engaging SEO title or meta description suggestions for new articles.'
			],
			[
				'role'    => 'user',
				'content' => $prompt
			]
		];

		$result = null;
		try {
			$data   = 'gpt-3.5-turbo' === aioseo()->ai->model ? $messages : $prompt;
			$result = aioseo()->ai->sendQuery( $data, $maxTokens, 5 );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage()
			], 400 );
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => [
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message()
				]
			], 200 );
		}

		if ( 'title' === $type ) {
			$result['suggestions'] = array_map( function( $suggestion ) {
				return aioseo()->helpers->toTitleCase( $suggestion );
			}, $result['suggestions'] );
		}

		// Save the data to prevent redundant costs/API calls.
		$aioseoPost          = Models\Post::getPost( $postId );
		$aioseoPost->open_ai = ! empty( $aioseoPost->open_ai )
			? Models\Post::getDefaultOpenAiOptions( $aioseoPost->open_ai )
			: Models\Post::getDefaultOpenAiOptions();

		$aioseoPost->open_ai->{$type} = [
			'suggestions' => $result['suggestions'],
			'usage'       => $result['usage']
		];

		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'     => true,
			'suggestions' => $result['suggestions'],
			'usage'       => $result['usage']
		], 200 );
	}

	/**
	 * Saves the OpenAI API key to the options.
	 *
	 * @since 4.3.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveApiKey( $request ) {
		$body   = $request->get_json_params();
		$apiKey = ! empty( $body['apiKey'] ) ? sanitize_text_field( $body['apiKey'] ) : '';
		if ( ! $apiKey ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No API key is missing.'
			], 400 );
		}

		aioseo()->options->advanced->openAiKey = $apiKey;

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}