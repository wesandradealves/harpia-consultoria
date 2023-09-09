<?php
/**
 * CF7 Skins CF7 Connect Class.
 * 
 * Implement functionality where CF7 Skins interacts with CF7.
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */

class CF7_Skins_CF7_Connect {
	
	/**
	 * Class constructor
	 * 
	 * @since 0.1.0
	 */
	function __construct() {
		add_filter( 'wpcf7_form_class_attr', array( &$this, 'form_classes' ) );
		add_action( 'wpcf7_contact_form', array( &$this, 'cf7_form' ), 1 );
		
		// Priority should be not empty or default to have them in the right order.
		// Default is 10, we use 9 to allow other styles (registered wihtout setting priority/default) 
		// enqueued after this style, or before such as framework styles, default styling, etc.
		add_action( 'cf7skins_enqueue_styles', array( $this, 'enqueue_skins_style' ), 9, 1 );
		add_filter( 'wpcf7_form_tag', array( $this, 'add_shortcode_id' ), 1, 2 );
		add_filter( 'wpcf7_autop_or_not', array( $this, 'disable_wpcf7_autop' ), 1, 1 ); // @since 2.5.4		
	}
	
	/**
	 * Get Contact Form 7 form id.
	 * 
	 * Back compat for CF7 3.9 
	 * @see http://contactform7.com/2014/07/02/contact-form-7-39-beta/
	 * 
	 * @param {Object}	$cf7	Contact Form 7 object
	 * 
	 * @return contact form id
	 * 
	 * @since 0.1.0
	 */
	public static function get_form_id( $cf7 ) {
		
		if ( version_compare( WPCF7_VERSION, '3.9-alpha', '>' ) ) {
			return $cf7->id();
		}
		return $cf7->id;
	}
	
	/**
	 * Get Contact Form 7 form content.
	 * 
	 * Back compat for CF7 3.9
	 * 
	 * @param {Object}	$cf7	Contact Form 7 object
	 * 
	 * @return {HTML} contact form
	 * 
	 * @since 0.1.0
	 */
	function get_form_content( $cf7 ) {
		
		if ( version_compare( WPCF7_VERSION, '3.9-alpha', '>' ) ) {
			return $cf7->prop( 'form' );
		}
		return $cf7->form;
	}
	
	/**
	 * Stylesheet dependencies.
	 * 
	 * Check if CF7 default stylesheet is still registered
	 * as is used by other CF7 Skins plugins.
	 * 
	 * @return {Array}	$deps	array of style dependencies
	 * 
	 * @since 1.1.1
	 */
	public static function stylesheet_dependencies() {
		
		$deps = array();
		
		if ( wp_style_is( 'contact-form-7', 'registered' ) ) { // Check if CF7 default stylesheet is still registered
			$deps = array( 'contact-form-7' );
		}
		return $deps;
	}
	
	/**
	 * Script dependencies.
	 * 
	 * Check if CF7 default script is still registered
	 * as is used by other CF7 Skins plugins.
	 * 
	 * @return {Array}	$deps	array of script dependencies
	 * 
	 * @since 1.2.2
	 */
	public static function script_dependencies() {
		
		$deps = array();
		
		if ( wp_script_is( 'contact-form-7', 'enqueued' ) ) { // Check if CF7 default script is still registered
			$deps = array( 'contact-form-7' ); 
		}
		return $deps;
	}
	
	/**
	 * Enqueue cf7s-framework files to the frontend. 
	 * 
	 * @since 1.1.1
	 */
	function enqueue_frameworks() {
		
		wp_enqueue_style( "cf7s-framework-normalize",
			CF7SKINS_URL . 'css/framework/cf7s-normalize.css',
				$this->stylesheet_dependencies(), CF7SKINS_VERSION, 'all' );
		
		wp_enqueue_style( "cf7s-framework-default",
			CF7SKINS_URL . 'css/framework/cf7s-default.css',
				$this->stylesheet_dependencies(), CF7SKINS_VERSION, 'all' );
	}
	
	/**
	 * Allow other functions to run with this hook and enqueue styles to the front end.
	 * 
	 * @do_action 'cf7skins_enqueue_styles'
	 * 
	 * @param {String}	$style	CF7 Skins style slug name applied to current CF7 form
	 * 
	 * @since 1.1.0
	 */
	function enqueue_styles( $style ) {
		/**
		 * Enqueue styles after CF7 Skins Framework
		 * or before selected skin style.
		 * @since 1.1.1
		 */
		do_action( 'cf7skins_enqueue_styles', $style );
	}
	
	/**
	 * Enqueue selected skin style .css file to the frontend by using the 'cf7skins_enqueue_styles' hook.
	 * 
	 * @action 'cf7skins_enqueue_styles'
	 * 
	 * @param {String}	$style	CF7 Skins style slug name applied to current CF7 form
	 * 
	 * @since 1.1.0
	 */
	function enqueue_skins_style( $style ) {
		
		$styles = CF7_Skin_Style::cf7s_get_style_list(); // get all installed styles
		
		if ( isset( $styles[$style] ) ) { // check if style exists then enqueue it
			wp_enqueue_style( "cf7s-$style", $styles[$style]['url'] . trailingslashit( $styles[$style]['dir'] ) . $styles[$style]['index'], 
				$this->stylesheet_dependencies(), CF7SKINS_VERSION, 'all' );
		}
	}
	
	/**
	 * Add CF7 Skins classes to the CF7 HTML form class.
	 * 
	 * Based on selected template & style
	 * eg. class="wpcf7-form cf7t-fieldset cf7s-wild-west"
	 * 
	 * @uses 'wpcf7_form_class_attr' filter in WPCF7_ContactForm->form_html()
	 * @uses wpcf7_get_current_contact_form()
	 * @file wp-content\plugins\contact-form-7\includes\contact-form.php
	 * 
	 * @filter 'cf7skins_form_classes' - allows other CF7 Skins plugins to add their style classes
	 * 
	 * @param  {String}		$class	CF7 HTML form class
	 * @previous form_class_attr()
	 * 
	 * @return {String}		$class	modified CF7 HTML form class
	 * 
	 * @since 0.1.0
	 */
	function form_classes( $class ) {
		
		// Get the current CF7 form ID
		$cf7 = wpcf7_get_current_contact_form(); // Current contact form 7 object
		$form_id = $this->get_form_id( $cf7 );
		
		// Get current CF7 form template and style from post meta
		$template_class = get_post_meta( $form_id, 'cf7s_template', true ) ? ' cf7t-' . get_post_meta( $form_id, 'cf7s_template', true ) : '';
		$skin_class = get_post_meta( $form_id, 'cf7s_style', true ) ? ' cf7s-'. get_post_meta( $form_id, 'cf7s_style', true ) : '';
		
		// CF7 Skins default class
		$cf7skins_class = ( $template_class || $skin_class ) ? ' cf7skins' : '';
		
		// Apply filter to allow other functions to modify the class
		$cf7skins_classes = apply_filters( 'cf7skins_form_classes', $cf7skins_class );
		
		// Return the modified class
		return $class . $cf7skins_classes . $template_class . $skin_class;
	}
	
	/**
	 * Modify the CF7 form content.
	 * 
	 * Back compat for CF7 3.9 
	 * @see http://contactform7.com/2014/07/02/contact-form-7-39-beta/
	 * 
	 * @uses 'wpcf7_contact_form' action in WPCF7_ContactForm__construct()
	 * @uses WPCF7_ContactForm->set_properties() - CF7 3.9 & after
	 * @uses WPCF7_ContactForm->form - before CF7 3.9
	 * @file wp-content\plugins\contact-form-7\includes\contact-form.php
	 *
	 * @param 	{Object}		$cf7	Contact Form 7 object
	 *
	 * @previous contact_form()
	 * 
	 * @return 	{Object}		$cf7	Modified Contact Form 7 object
	 * 
	 * @since 0.1.0
	 */
	function cf7_form( $cf7 ) {
		
		if( ! is_admin() ) {
			
			// Return if no cf7skins template or style was selected
			if ( ! get_post_meta( $cf7->id(), 'cf7s_template', true ) && ! get_post_meta( $cf7->id(), 'cf7s_style', true ) ) {
				return $cf7;
			}

			// Enqueue CF7 Skins styles
			// @since 1.1
			if( $style = get_post_meta( $cf7->id(), 'cf7s_style', true ) ) {
				$this->enqueue_frameworks();
				$this->enqueue_styles( $style );
			}
			
			$form = $this->get_form_content( $cf7 );
			
			if ( version_compare( WPCF7_VERSION, '3.9-alpha', '>' ) ) {
				// uses WPCF7_ContactForm->set_properties() - CF7 3.9 & after
				$cf7->set_properties( array( 'form' => $this->modify_form( $form ) ) );
				}
			else {
				// uses WPCF7_ContactForm->form - before CF7 3.9
				$cf7->form = $this->modify_form( $form );
				}
		}
		
		return $cf7;
	}
	
	/**
	 * Modify current CF7 form content.
	 * 
	 * @param 	{HTML}	$form	Current CF7 form content
	 * 
	 * @return 	{HTML}	$form	Modified CF7 form content
	 * 
	 * @since 0.1.0
	 */
	function modify_form( $form ) {
		
		// Add label required
		$form = $this->mod_add_label_req( $form );
		
		return $form;
	}
	
	/**
	 * Add label and require <em> to CF7 form content.
	 * 
	 * e.g. <li>Name [text* cf7s-name]</li> changed to
	 * <li><label for="cf7s-name">Name <em class="cf7s-reqd">*</em></label> [text* cf7s-name]</li>
	 * 
	 * @param 	{HTML}	$form	Current CF7 form content
	 * 
	 * @return 	{HTML} contact form with additional element attributes( id, class, etc)
	 * 
	 * @since 1.1.1
	 */
	function mod_add_label_req( $form ) {
		// Get all current shortcode
		$scanned = $this->scan_shortcode( $form );
		
		// Get all shortcodes id with tag name as the index
		$ids = array();
		
		foreach ( $scanned as $tag ) {

			/**
			 * Backward compatibility for CF7 4.6
			 * @since 1.2.1
			 */
			if ( version_compare( WPCF7_VERSION, '4.6', '<' ) ) { // before CF7 4.6
				$tag = new WPCF7_Shortcode( $tag );
			}
			else { // CF7 4.6 or greater
				$tag = new WPCF7_FormTag( $tag );
			}
			$ids[$tag->name] = $tag->get_id_option() ? $tag->get_id_option() : $tag->name;
		}
		
		// Patterns for searching all list tag
		$pattern = "/<li ?.*>(.*)<\/li>/";
		preg_match_all( $pattern, $form, $matches );
		
		if ( $matches[0] ) {
		
			// Loop through each match
			foreach( $matches[0] as $list ) {
				
				// Process only if the list have a shortcode
				if( preg_match( "/\[.*?\]/", $list, $shortcode ) ) {
					
					// Explode shortcode by spaces to get the shortcode name
					$explode = explode( ' ', str_replace( array('[', ']'), '', $shortcode[0] ) );
					$name = $explode[1];
					$id = isset( $ids[$name] ) ? $ids[$name] : $name;
					
					// Add opening label tag
					$new_list = preg_replace( "/(<li.*?>)(.*)(<\/li>)/", "$1<label for='$id'>$2$3", $list );
					
					// Closing label tag with * required em tag
					if ( strpos( $shortcode[0], '*' ) !== false ) {
						$new_list = str_replace( '[', '<em class="cf7s-reqd">*</em> </label>[', $new_list );
					} else {
						$new_list = str_replace( '[', '</label>[', $new_list );
					}
					
					// Add label and em by replacing it
					$form = str_replace( $list, $new_list, $form );
				}
			}
		}
		
		return $form;
	}
	
	/**
	 * Scan shortcode with backward compatibility CF7 4.6.
	 * 
	 * @link https://bitbucket.org/cf7skins/cf7skins-dev-cf7skins/issues/28/wpcf7_shortcode-deprecated
	 * 
	 * @param 	{HTML}	$form	Current CF7 form content
	 * 
	 * @return 	{Object} CF7 shortcode object
	 * 
	 * @since 1.2.1
	 */
	public static function scan_shortcode( $form ) {
		
		if ( version_compare( WPCF7_VERSION, '4.6', '<' ) ) { // before CF7 4.6
			$manager = WPCF7_ShortcodeManager::get_instance();
			return $manager->scan_shortcode( $form );
		
		} else { // CF7 4.6 or greater
			$manager = WPCF7_FormTagsManager::get_instance();
			return $manager->scan( $form );
		}
	}
	
	/**
	 * Add tag id attribute by using its name if is not provided.
	 * The id will be used in front-end to create input id
	 * See contact-form-7/includes/form-tags-manager.php
	 * 
	 * @param (Array)		$scanned_tag	Current CF7 form content
	 * @param (Boolean)		$replace		true to use tag function
	 * 
	 * @since 2.1.3
	 */
	function add_shortcode_id( $scanned_tag, $replace ) {	
		if ( !! $scanned_tag['name'] ) { // only if has name				
			if ( count( $scanned_tag['options'] ) > 1 ) { // options array not empty
				foreach( $scanned_tag['options'] as $option ) { // loop					
					if ( strpos( $option, 'id:' ) != 0 ) { // has id attribute, use single =
						$scanned_tag['options'][] = "id:{$scanned_tag['name']}"; // add the id
					}
				}
			} else { // options array empty
				$scanned_tag['options'][] = "id:{$scanned_tag['name']}"; // add the id
			}				
		}
	
		return $scanned_tag;
	}
	
	/**
	 * Disable wpcf7_autop to avoid additional <p /> tag in form
	 * 
	 * @filter 'wpcf7_autop_or_not' contact-form-7/includes/formatting.php
	 * 
	 * @param $bool (boolean) use wpcf7_autop or not
	 *
	 * @since 2.5.4
	 */
	function disable_wpcf7_autop( $bool ) {
		return false;
	}	

	/**
	 * Get lists that contains shortcode tag in CF7 form content.
	 * Shortcode tags outside a list <li/> will be excluded.
	 *
	 * @param 	{HTML/String}	$form	Current CF7 form content
	 * 
	 * @return 	{Array}			$lists	array of <li></li>s
	 * 
	 * @since 2.1.5
	 */
	public static function get_lists( $form ) {
		
		$types = $lists = array(); // handle list contains shortcode
		
		// Get all current shortcode
		$scanned = self::scan_shortcode( $form );
		
		foreach( $scanned as $tag ) {
			
			/**
			 * Backward compatibility for CF7 4.6
			 * @since 1.2.1
			 */
			if ( version_compare( WPCF7_VERSION, '4.6', '<' ) ) { // before CF7 4.6
				$tag = new WPCF7_Shortcode( $tag );
			}
			else { // CF7 4.6 or greater
				$tag = new WPCF7_FormTag( $tag );
			}
			
			$types[] = $tag->type; // add shortcode for further text searching
		}	
		
		libxml_use_internal_errors( true ); // handle error
		$dom = new DOMDocument( '1.0', 'utf-8' );
		$dom->loadHTML( "<?xml encoding='UTF-8' ?>". $form );
		libxml_use_internal_errors( false ); // handle error
		
		// Get all lists, including nested list
		$get_lists = $dom->getElementsByTagName('li');
		
		if ( $get_lists->length ) { // only if list exists
		
			foreach( $get_lists as $list ) { // loop for each
			
				$shortcode_exist = false;
				
				foreach ( $list->childNodes as $node ) { // loop each child nodes
					
					$content = trim( $node->textContent ); // remove spaces from plain text
					
					// If is a text node and not empty (trim spaces/tabs)
					// https://www.php.net/manual/en/dom.constants.php#constant.xml-text-node
					if ( $node->nodeType === XML_TEXT_NODE && ! empty( $content ) ) {
						
						// Loop for each type to find if the content contains shortcode
						foreach( $types as $type ) {
							$shortcode_type = "[{$type} "; // bracket+type+required+space, i.e. '[select* '
							
							// Shortcode exists in content, set current node
							if ( strpos( $content, $shortcode_type ) !== false ) {
								$shortcode_exist = true;
							}
						}
					}
				}
				
				if ( !! $shortcode_exist ) {
					$lists[] = $dom->saveXML( $list );
				}
			}
		}
		
		return $lists;
	}
} // end class

/**
 * CF7_Skins_Contact class backcompat for older CF7 Skins versions (deprecated).
 * 
 * @since 1.1.1
 */
 class CF7_Skins_Contact extends CF7_Skins_CF7_Connect {
	function __construct() {
		parent::__construct();
	}
 } new CF7_Skins_Contact();
