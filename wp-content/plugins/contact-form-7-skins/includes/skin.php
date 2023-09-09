<?php
/**
 * CF7 Skins - Skins Class.
 * 
 * Base class for templates and styles.
 *
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */

/**
 * Return if class already exists in theme or other plugins to avoid errors.
 * 
 * @since 0.2.0
 */
if ( class_exists( 'CF7_Skin' ) ) {
	return;
}

class CF7_Skin {
	
	// Class variables
	var $name, $version;
	
	/**
	 * Class constructor
	 * 
	 * @since 0.1.0
	 */
	function __construct() {
		// Set class variables
		$this->name 	  = CF7SKINS_OPTIONS;
		$this->version 	  = CF7SKINS_VERSION;
	}
	
	
	/**
	 * Get post id in post edit screen.
	 * 
	 * @since 0.1.0
	 */
	function get_id() {
		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		return $post_id;
	}
	
	/**
	 * Get slug name for template or style based on directory name.
	 * 
	 * @param $skin (TYPE) current processed template or style reading data
	 * 
	 * @since 0.1.0
	 */
	function get_slug_name( $skin ) {
		echo str_replace(' ', '-', sanitize_text_field( $skin['dir'] ) );
	}
	
	/**
	 * Return the skin thumbnail image url.
	 * 
	 * @param $skin (TYPE) current processed template or style reading data
	 * 
	 * @since 0.1.0
	 */
	function get_skin_thumbnail( $skin ) {
		$imgpath = $skin['path'] . $skin['dir'] . '/thumbnail.png';
		
		// Check if thumbnail.png exists or load default thumbnail
		if( file_exists( $imgpath ) ) {
			$imgurl = $skin['url'] . $skin['dir'] . '/thumbnail.png';
		}
		else {
			$imgurl = CF7SKINS_URL . 'images/no-preview.png';
		}
		return $imgurl;
	}
	
	/**
	 * Return the skin modal image url, if does not exist thumbnail.png will be returned.
	 * 
	 * @parameter $skin is the current processed skin reading data
	 * 
	 * @since 0.1.0
	 */
	function get_skin_modal( $skin ) {
		$imgpath = $skin['path'] . $skin['dir'] . '/modal.png';
		
		// Check if modal.png exists
		if( file_exists( $imgpath ) ) {
			$imgurl = $skin['url'] . $skin['dir'] . '/modal.png';
		}
		else {
			$imgurl = $this->get_skin_thumbnail( $skin );
		}
		return $imgurl;
	}

	/**
	 * Get current selected Template & Style name.
	 * 
	 * @param text $skin
	 * 
	 * @return text
	 */

	function get_skin_name( $skin ) {

		if (!$post_id = $this->get_id() ) {
			return '';
		}
	
		// Get current selected template & style
		$template = get_post_meta( $post_id, 'cf7s_template', true );
		$style = get_post_meta( $post_id, 'cf7s_style', true );

		$template_name = $style_name = '';

				// Get template name
				if ( $template ) {
					$templates = CF7_Skin_Template::cf7s_get_template_list();
					$template_name = isset( $templates[$template] ) ? 
						$templates[$template]['details']['Template Name'] : 
						sprintf( __( "%s-missing", 'contact-form-7-skins' ), $template ) .
						'<span class="help balloon-hover balloon" title="'. __( 'Selected CF7 Skins Template is not available', 'contact-form-7-skins' ) .'">?</span>';
				}
				
				// Get style name
				if ( $style ) {
					$styles = CF7_Skin_Style::cf7s_get_style_list();
					$style_name = isset( $styles[$style] ) ? 
						$styles[$style]['details']['Style Name'] : 
						sprintf( __( "%s-missing", 'contact-form-7-skins' ), $style ) .
						'<span class="help balloon-hover balloon" title="'. __( 'Selected CF7 Skins Style is not available', 'contact-form-7-skins' ) .'">?</span>';
				}
		
				switch ($skin) {
					case "template":
						return $template_name;
					break;
					case "style":
						return $style_name;
					break;
					default: 
						echo "bug";
				}
	}

} // End class
