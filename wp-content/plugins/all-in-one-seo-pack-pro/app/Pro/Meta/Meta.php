<?php
namespace AIOSEO\Plugin\Pro\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta as CommonMeta;
use AIOSEO\Plugin\Pro\Models;

/**
 * Instantiates the Meta classes.
 *
 * @since 4.0.0
 */
class Meta extends CommonMeta\Meta {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->metaData     = new MetaData();
		$this->title        = new Title();
		$this->description  = new Description();
		$this->keywords     = new CommonMeta\Keywords();
		$this->robots       = new Robots();

		new CommonMeta\Amp();
		new CommonMeta\Links();

		add_action( 'delete_post', [ $this, 'deletePostMeta' ], 1000 );
		add_action( 'delete_term', [ $this, 'deleteTermMeta' ], 1000 );
	}

	/**
	 * When we delete the meta, we want to delete our post model.
	 *
	 * @since 4.0.1
	 *
	 * @param  int  $termId The post ID.
	 * @return void
	 */
	public function deleteTermMeta( $termId ) {
		$aioseoTerm = Models\Term::getTerm( $termId );
		if ( $aioseoTerm->exists() ) {
			$aioseoTerm->delete();
		}
	}
}