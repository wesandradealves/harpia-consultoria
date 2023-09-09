<?php
namespace AIOSEO\Plugin\Pro\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Standalone\DetailsColumn as CommonDetailsColumn;
use AIOSEO\Plugin\Pro\Models;

/**
 * Handles the AIOSEO Details term column.
 *
 * @since 4.2.0
 */
class DetailsColumn extends CommonDetailsColumn {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		parent::__construct();

		if ( wp_doing_ajax() ) {
			add_action( 'init', [ $this, 'addTaxonomyColumnsAjax' ], 1 );
		}

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'current_screen', [ $this, 'addTaxonomyColumns' ], 1 );
	}

	/**
	 * Registers the AIOSEO Details column for taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return void
	 */
	public function addTaxonomyColumns( $screen ) {
		if ( ! $this->isTaxonomyColumn( $screen->base, $screen->taxonomy ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		add_filter( "manage_edit-{$screen->taxonomy}_columns", [ $this, 'addColumn' ], 10, 1 );
		add_filter( "manage_{$screen->taxonomy}_custom_column", [ $this, 'renderTaxonomyColumn' ], 10, 3 );
	}

	/**
	 * Registers our taxonomy columns after a term has been quick-edited.
	 *
	 * @since 4.2.3
	 *
	 * @returns void
	 */
	public function addTaxonomyColumnsAjax() {
		if (
			! isset( $_POST['_inline_edit'], $_POST['tax_ID'] ) ||
			! wp_verify_nonce( $_POST['_inline_edit'], 'taxinlineeditnonce' )
		) {
			return;
		}

		$termId = (int) $_POST['tax_ID'];
		if ( ! $termId ) {
			return;
		}

		$term     = get_term( $termId );
		$taxonomy = $term->taxonomy;

		add_filter( "manage_edit-{$taxonomy}_columns", [ $this, 'addColumn' ] );
		add_filter( "manage_{$taxonomy}_custom_column", [ $this, 'renderTaxonomyColumn' ], 10, 3 );
	}

	/**
	 * Renders the column in the taxonomy table.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $out        The output to display.
	 * @param  string $columnName The column name.
	 * @param  int    $termId     The current term id.
	 * @return string             A rendered html.
	 */
	public function renderTaxonomyColumn( $out, $columnName = '', $termId = 0 ) {
		if ( 'aioseo-details' !== $columnName ) {
			return $out;
		}

		// Add this column/post to the localized array.
		global $wp_scripts, $wp_query;

		$data = $wp_scripts->get_data( 'aioseo/js/' . $this->scriptSlug, 'data' );

		if ( ! is_array( $data ) ) {
			$data = json_decode( str_replace( 'var aioseo = ', '', substr( $data, 0, -1 ) ), true );
		}

		$nonce   = wp_create_nonce( "aioseo_meta_{$columnName}_{$termId}" );
		$terms   = $data['terms'];
		$theTerm = Models\Term::getTerm( $termId );

		// Turn on the tax query so we can get specific tax data.
		$originalTax      = $wp_query->is_tax;
		$wp_query->is_tax = true;

		$terms[] = [
			'id'                => $termId,
			'columnName'        => $columnName,
			'nonce'             => $nonce,
			'title'             => ! empty( $theTerm->title ) ? $theTerm->title : '',
			'titleParsed'       => aioseo()->meta->title->getTermTitle( get_term( $termId ) ),
			'description'       => ! empty( $theTerm->description ) ? $theTerm->description : '',
			'descriptionParsed' => aioseo()->meta->description->getTermDescription( get_term( $termId ) )
		];

		$wp_query->is_tax = $originalTax;
		$data['terms']    = $terms;

		$wp_scripts->add_data( 'aioseo/js/' . $this->scriptSlug, 'data', '' );
		wp_localize_script( 'aioseo/js/' . $this->scriptSlug, 'aioseo', $data );

		ob_start();
		require AIOSEO_DIR . '/app/Common/Views/admin/terms/columns.php';
		$out = ob_get_clean();

		return $out;
	}

	/**
	 * Check if the taxonomy should show AIOSEO column.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy The taxonomy slug.
	 * @return bool             Whether the taxonomy should show AIOSEO column.
	 */
	private function isTaxonomyColumn( $screen, $taxonomy ) {
		if ( 'type' === $taxonomy ) {
			$taxonomy = '_aioseo_type';
		}

		if ( 'edit-tags' === $screen ) {
			if (
				aioseo()->options->advanced->taxonomies->all
				&& in_array( $taxonomy, aioseo()->helpers->getPublicTaxonomies( true ), true )
			) {
				return true;
			}

			$taxonomies = aioseo()->options->advanced->taxonomies->included;
			if ( in_array( $taxonomy, $taxonomies, true ) ) {
				return true;
			}
		}

		return false;
	}
}