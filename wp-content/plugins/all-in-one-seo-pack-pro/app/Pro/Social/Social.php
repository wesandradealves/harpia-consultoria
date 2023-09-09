<?php
namespace AIOSEO\Plugin\Pro\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Social as CommonSocial;

/**
 * Handles our social meta.
 *
 * @since 4.0.0
 */
class Social extends CommonSocial\Social {
	/**
	 * The name of the action to bust the OG cache.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	private $bustOgCacheActionName = 'aioseo_og_cache_bust_term';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->image = new Image();

		add_action( $this->bustOgCacheActionName, [ $this, 'bustOgCacheTerm' ] );

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$this->facebook = new Facebook();
		$this->twitter  = new Twitter();
		$this->output   = new Output();

		add_action( 'edited_terms', [ $this, 'scheduleBustOgCacheTerm' ] );
	}

	/**
	 * Schedule a ping to bust the OG cache.
	 *
	 * @since 4.2.0
	 *
	 * @param  int  $termId The term ID.
	 * @return void
	 */
	public function scheduleBustOgCacheTerm( $termId ) {
		$term              = get_term( $termId );
		$customAccessToken = apply_filters( 'aioseo_facebook_access_token', '' );

		if (
			! is_a( $term, 'WP_Term' ) ||
			( ! aioseo()->helpers->isSbCustomFacebookFeedActive() && ! $customAccessToken )
		) {
			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->bustOgCacheActionName, [ 'termId' => $termId ] ) ) {
			return;
		}

		// Schedule the new ping.
		aioseo()->actionScheduler->scheduleAsync( $this->bustOgCacheActionName, [ 'termId' => $termId ] );
	}

	/**
	 * Pings Facebook and asks them to bust the OG cache for a particular term.
	 *
	 * @since 4.2.0
	 *
	 * @see https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
	 *
	 * @param  int  $termId The term ID.
	 * @return void
	 */
	public function bustOgCacheTerm( $termId ) {
		$term = get_term( $termId );
		if ( ! is_a( $term, 'WP_Term' ) || ! aioseo()->helpers->isSbCustomFacebookFeedActive() ) {
			return;
		}

		$permalink = get_term_link( $termId );
		$this->bustOgCacheHelper( $permalink );
	}
}