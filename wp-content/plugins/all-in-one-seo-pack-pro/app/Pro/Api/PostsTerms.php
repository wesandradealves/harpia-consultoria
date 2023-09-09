<?php
namespace AIOSEO\Plugin\Pro\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Api as CommonApi;
use AIOSEO\Plugin\Common\Models as CommonModels;
use AIOSEO\Plugin\Pro\Models as ProModels;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class PostsTerms extends CommonApi\PostsTerms {
	/**
	 * Update post settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePosts( $request ) {
		$body    = $request->get_json_params();
		$postId  = ! empty( $body['id'] ) ? intval( $body['id'] ) : null;
		$context = ! empty( $body['context'] ) ? sanitize_text_field( $body['context'] ) : 'post';

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		$body['id']                  = $postId;
		$body['context']             = $context;
		$body['title']               = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : null;
		$body['description']         = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : null;
		$body['keywords']            = ! empty( $body['keywords'] ) ? sanitize_text_field( $body['keywords'] ) : null;
		$body['og_title']            = ! empty( $body['og_title'] ) ? sanitize_text_field( $body['og_title'] ) : null;
		$body['og_description']      = ! empty( $body['og_description'] ) ? sanitize_text_field( $body['og_description'] ) : null;
		$body['og_article_section']  = ! empty( $body['og_article_section'] ) ? sanitize_text_field( $body['og_article_section'] ) : null;
		$body['og_article_tags']     = ! empty( $body['og_article_tags'] ) ? sanitize_text_field( $body['og_article_tags'] ) : null;
		$body['twitter_title']       = ! empty( $body['twitter_title'] ) ? sanitize_text_field( $body['twitter_title'] ) : null;
		$body['twitter_description'] = ! empty( $body['twitter_description'] ) ? sanitize_text_field( $body['twitter_description'] ) : null;

		$saveStatus = ( 'post' === $context ) ? CommonModels\Post::savePost( $postId, $body ) : ProModels\Term::saveTerm( $postId, $body );

		if ( ! empty( $saveStatus ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed update query: ' . $saveStatus
			], 401 );
		}

		$response = new \WP_REST_Response( [
			'success' => true,
			'posts'   => $postId
		], 200 );

		return Api::addonsApi( $request, $response, '\\Api\\PostsTerms', 'updatePosts' );
	}

	/**
	 * Update term settings from Term screen.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request  The REST Request
	 * @return \WP_REST_Response $response The response.
	 */
	public static function updateTermFromScreen( $request ) {
		$body   = $request->get_json_params();
		$termId = ! empty( $body['termId'] ) ? intval( $body['termId'] ) : null;

		if ( ! $termId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Term ID is missing.'
			], 400 );
		}

		$theTerm = aioseo()->core->db
			->start( 'aioseo_terms' )
			->where( 'term_id', $termId )
			->run()
			->model( 'AIOSEO\\Plugin\\Pro\\Models\\Term' );

		if ( $theTerm->exists() ) {
			$theTerm->title       = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : '';
			$theTerm->description = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : '';
			$theTerm->updated     = gmdate( 'Y-m-d H:i:s' );
		} else {
			$theTerm->term_id     = $termId;
			$theTerm->title       = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : '';
			$theTerm->description = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : '';
			$theTerm->created     = gmdate( 'Y-m-d H:i:s' );
			$theTerm->updated     = gmdate( 'Y-m-d H:i:s' );
		}
		$theTerm->save();

		$lastError = aioseo()->core->db->lastError();
		if ( ! empty( $lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed update query: ' . $lastError
			], 401 );
		}

		global $wp_query;
		$isTax            = $wp_query->is_tax;
		$wp_query->is_tax = true;

		$response = new \WP_REST_Response( [
			'success'     => true,
			'terms'       => $termId,
			'title'       => aioseo()->meta->title->getTermTitle( get_term( $termId ) ),
			'description' => aioseo()->meta->description->getTermDescription( get_term( $termId ) )
		], 200 );

		$wp_query->is_tax = $isTax;

		return $response;
	}
}