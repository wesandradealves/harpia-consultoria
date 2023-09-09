<?php
namespace AIOSEO\Plugin\Pro\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle AI via OpenAI.
 *
 * @since 4.3.2
 */
class Ai {
	/**
	 * The temperature parameter controls randomness in Boltzmann distributions.
	 * The higher the temperature, the more random the completions.
	 *
	 * @since 4.3.2
	 *
	 * @var float
	 */
	private $temperature = 0.8;

	/**
	 * The AI model to use.
	 * @see https://platform.openai.com/docs/models/gpt-3 for a list of models.
	 *
	 * @since 4.3.2
	 *
	 * @var string
	 */
	public $model = '';

	/**
	 * The base URL for the API.
	 *
	 * @since 4.3.2
	 *
	 * @var string
	 */
	private $baseUrl = 'https://api.openai.com/v1/';

	/**
	 * Class constructor.
	 *
	 * @since 4.3.3
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'setModel' ] );
	}

	/**
	 * Checks the site's locale to see whether it is an English one or not.
	 * This determines the model we use.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function setModel() {
		$model  = 'gpt-3.5-turbo';
		$locale = get_locale();
		if ( 'en' !== substr( $locale, 0, 2 ) ) {
			$model = 'text-davinci-003';
		}

		$this->model = apply_filters( 'aioseo_ai_model', $model );
	}

	/**
	 * Sends a query to the AI.
	 *
	 * @since 4.3.2
	 *
	 * @param  string               $data       The data to send to the AI.
	 * @param  int                  $maxTokens  The maximum number of tokens to return.
	 * @param  int                  $maxResults The maximum number of suggestions to return.
	 * @return array|bool|\WP_Error             The suggestions or false if no API key is set or a WP_Error object if the request failed
	 */
	public function sendQuery( $data, $maxTokens = 64, $maxResults = 5 ) {
		$apiKey = aioseo()->options->advanced->openAiKey;
		if ( ! $apiKey ) {
			return false;
		}

		$args = [
			'timeout'   => 120,
			'headers'   => [
				'Authorization' => 'Bearer ' . $apiKey,
				'Content-Type'  => 'application/json'
			],
			'body'      => [
				'max_tokens'  => $maxTokens,
				'temperature' => $this->temperature,
				'model'       => $this->model,
				'stop'        => null,
				'n'           => $maxResults
			],
			'sslverify' => false
		];

		$slug = '';
		if ( 'gpt-3.5-turbo' === $this->model ) {
			$slug                     = 'chat/completions';
			$args['body']['messages'] = $data;
		} else {
			$slug                   = 'completions';
			$args['body']['prompt'] = $data;
		}

		$args['body'] = wp_json_encode( $args['body'] );

		$response = aioseo()->helpers->wpRemotePost( $this->getUrl() . $slug, $args );
		$body     = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );
		if ( isset( $data->error ) ) {
			return new \WP_Error(
				$data->error->type,
				$data->error->message
			);
		}

		$suggestions = [];
		foreach ( $data->choices as $choice ) {
			$suggestion = '';
			if ( 'gpt-3.5-turbo' === $this->model ) {
				$suggestion = $choice->message->content;
			} else {
				$suggestion = $choice->text;
			}

			$suggestion = stripslashes_deep( wp_filter_nohtml_kses( wp_strip_all_tags( $suggestion ) ) );
			$suggestion = preg_replace( '/\v+/', '', $suggestion );

			// Trim quotes from beginning/end and redundant whitespace.
			$suggestion = preg_replace( '/^["\']/', '', $suggestion );
			$suggestion = preg_replace( '/["\']$/', '', $suggestion );
			$suggestion = trim( normalize_whitespace( $suggestion ) );

			$suggestions[] = $suggestion;
		}

		return [
			'suggestions' => $suggestions,
			'usage'       => $data->usage->total_tokens
		];
	}

	/**
	 * Returns the API url.
	 *
	 * @since 4.3.2
	 *
	 * @return string The API url.
	 */
	public function getUrl() {
		if ( defined( 'AIOSEO_AI_URL' ) ) {
			return AIOSEO_AI_URL;
		}

		return $this->baseUrl;
	}
}