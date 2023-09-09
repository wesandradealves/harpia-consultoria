<?php
/**
 * CF7 Skins Visual Admin Class.
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 2.0.0
 */
 
Class CF7_Skins_Admin_Visual {

	var $dom;
	var $form_id; // @since 2.3.0
	
	/**
	 * Class constructor
	 * 
	 * @since 2.0.0
	 */
	function __construct() {
		
		// For debugging purpose, should be deleted for live
		// Add in .htaccess file php_flag display_errors 1
		if ( 'development' === CF7SKINS_ENV ) {
			ini_set( 'display_errors', 1 );
		}
		
		$this->nonce = CF7SKINS_OPTIONS;
		$this->version = CF7SKINS_VERSION;
		$this->url = CF7SKINS_URL;
		$this->path = CF7SKINS_PATH; // @since 2.3.0
		
		$this->dom = new DOMDocument( '1.0', 'utf-8' );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'vendor_scripts' ), 10 ); // @since 2.3.0
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'translation_scripts' ), 12 );
		add_action( 'admin_print_footer_scripts', array( $this, 'log_scripts' ) );
		add_action( 'cf7skins_update', array( $this, 'save_visual' ), 10, 1 );
		add_action( 'cf7skins_copy', array( $this, 'copy_visual' ) );
		add_action( 'cf7skins_tabs', array( $this, 'visual_tab' ) );
		add_action( 'cf7skins_tab_content', array( $this, 'visual_content' ) );
		add_action( 'wpcf7_admin_notices', array( &$this, 'admin_notice' ) );
		add_action( 'wp_ajax_cf7skins_visual_update', array( &$this, 'cf7skins_visual_update' ) );
		add_action( 'wp_ajax_visual_select_template', array( &$this, 'select_template' ) );
		add_filter( 'cf7skins_setting_fields', array( &$this, 'setting_fields' ) );
	}
	
	/**
	 * Add visual form tab.
	 * 
	 * @param {Array}		$tabs	CF7 Skins tabs
	 * 
	 * @return {Array}		visual tab
	 * 
	 * @since 2.0.0
	 */
	function visual_tab( $tabs ) {
		return array ( 'visual' => array(
			'name' => 'visual',
			'order' => 1,
			'label' => __( 'Form', 'contact-form-7-skins' ),
			'note'  => __( 'Drag and drop fields up and down  to re-order them. Any field content can be changed by clicking Edit on the field. Click Save Visual to update your Contact Form 7 form.', 'contact-form-7-skins' ),
			'help'  => __( 'Create a new form by selecting a template. Or, build your own. Drag and drop fields into the visual form editor from the Fields section. Edit, duplicate, or delete a field using the field’s icons.', 'contact-form-7-skins' )
		) ) + $tabs;
	}
	
	/**
	 * Add visual form content/section.
	 * 
	 * @param {Array}		$tabs	CF7 Skins tabs
	 * 
	 * @since 2.0.0
	 */
	function visual_content( $tabs ) {
		// TODO
	}
	
	/**
	 * Save the visual form by clicking Save Visual button.
	 * 
	 * @param {Object}		$cf7	Contact Form 7 object
	 * 
	 * @since 2.0.0
	 */
	function save_visual( $cf7 ) {
		// Debug statement to work with PHP Console, to log PHP data to Chrome DevTools for
		// use with a local wordpress install https://github.com/unfulvio/wp-php-console
		
		// Return if visual data does not exist
		if ( ! isset( $_POST['cf7s-visual'] ) ) {
			return;
		}
		
		$this->form_id = (int) CF7_Skins_Contact::get_form_id( $cf7 );

		// Sanitize and remove extra backslashes, decode Visual JSON data
		$visual = json_decode( wpcf7_sanitize_form( wp_unslash( $_POST['cf7s-visual'] ) ) );

		// Remove visual meta data if is emptied by user or form does not have visual data
		if ( ! $visual ) {
			delete_post_meta( $this->form_id, 'cf7s_visual' );
			return;
		}
		
		// Save visual meta data for further use
		update_post_meta( $this->form_id, 'cf7s_visual', $visual );
		
		// Update CF7 textarea form content
		$visual_content = trim( $this->extract_visual( $visual ) );
		update_post_meta( $this->form_id, '_form', $visual_content );
	}
	
	/**
	 * Create <div /> with specific ID for ReactDOM to render admin notification after Save
	 * 
	 * @since 2.0.0
	 */
	function admin_notice() {
		?><div id="cf7s-visual-notice"></div><?php
	}
	
	/**
	 * Saves the visual form using AJAX call.
	 * 
	 * This function is intended for Visual template development
	 * 
	 * @since 2.0.0
	 */
	function cf7skins_visual_update() {
		// Security check
		if ( ! wp_verify_nonce( $_POST['nonce'], $this->nonce ) 
			&& ! isset( $_POST['visual'] ) && ! isset( $_POST['form_id'] ) ) {
				die();
		}
		
		$cf7svisual = cf7skins_sanitize_visual_data( json_decode( wp_unslash( $_POST['visual'] ) ) ); // treeData @since 2.3.0
		
		// Callback function right before doing any update @since 2.3.0
		do_action( 'cf7s_visual_before_update', $cf7svisual , $_POST );

		$form_id = (int) $_POST['form_id'];
		$this->form_id = $form_id; // setup class form id @since 2.3.0
		
		// Save visual meta data for further use
		update_post_meta( $form_id, 'cf7s_visual', $cf7svisual );
	
		// Uncomment line below to generate visual template object
		// print_r( str_replace("'", "\\'", stripslashes( $_POST['visual'] )) );
		
		// Update form content
		$visual_html = trim( $this->extract_visual( $cf7svisual ) );
		update_post_meta( $form_id, '_form', $visual_html );
		
		// Update form title if is set.
		if ( isset( $_POST['title'] ) ) {
			wp_update_post( array(
				'ID' => $form_id,
				'post_title' => sanitize_title( $_POST['title'] ),
			) );
		}
		
		// Update select/deselect template
		
		if ( $_POST['template'] ) { // selected
			update_post_meta( $form_id, 'cf7s_template', sanitize_text_field( $_POST['template'] ) );
		} else { // deselected or empty
			delete_post_meta( $form_id, 'cf7s_template' );
		}
		
		// Update select/deselect style
		
		if ( $_POST['style'] ) { // selected
			update_post_meta( $form_id, 'cf7s_style', sanitize_text_field( $_POST['style'] ) );
		} else { // deselected or empty
			delete_post_meta( $form_id, 'cf7s_style' );
		}
		
		// Output visual HTML content
		$output = array();
		$output['form'] = $visual_html;
		$output['treeData'] = get_post_meta( $form_id, 'cf7s_visual', true ); // debugging

		/**
		 * Run add-ons JavaScript callback functions
		 * Can be function within namespace or standalone function
		 * For example: myNameSpace.myFunction() or anotherFunction();
		
		 */
		$output['callbacks'] = apply_filters( 'cf7s_visual_update_js_callbacks', array() );
		
		echo json_encode( $output );
		
		exit();
	}
	
	/**
	 * Extract visual items to CF7 form tag.
	 * 
	 * @param {Object}		$item	cf7sItems properties
	 * 
	 * @return ADD INFO
	 * 
	 * @since 2.0.0
	 */
	function extract_visual( $items ) {
		
		// Return if visual is empty or invalid
		if ( ! is_array( $items ) ) {
			return false;
		}
		
		// Create new DOM
		$dom = $this->dom;
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		foreach( $items as $item ) {
			$parent = $this->create_element( $item ); // parent item
			$dom->appendChild( $parent ); // insert to DOM
			
			if ( isset( $item->children ) && $item->children ) {
				$this->loop_children( $item, $parent );
			}
		}
		
		// Replace two spaces to single tab @since 0.6.7 and save XML
		$xml = preg_replace_callback( '/^( +)</m', function( $a ) {
			return str_repeat( "\t", intval( strlen( $a[1]) / 2 ) ) . '<';
		}, $dom->saveXML( $dom ) );
		
		$xml = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml ); // remove XML declaration
		$xml = str_replace( "cf7s&amp;", "&", $xml ); // replace "cf7s&amp;" helper back to "&" @since 0.7.0
		$xml = str_replace( array("<tag>","</tag>"), "", $xml ); // remove <tag> helper
		$xml = preg_replace( "/<cf7content>\s*|\s*<\/cf7content>/", "", $xml ); // remove <cf7content> helper
		$xml = preg_replace( "/<cf7content\/>/", "", $xml ); // remove <cf7content/> helper	
		return $xml;
	}
	
	/**
	 * Fetch loop recursive through item children.
	 * 
	 * @param {Object}		$item		cf7sItems properties
	 * @param {DOM Object}	$parent		parent DOM element
	 * 
	 * @since 2.0.0
	 */
	function loop_children( $item, $parent ) {
		foreach ( $item->children as $child ) {
			$_item = $this->create_element( $child );
			
			if ( isset( $child->children ) ) {
				$this->loop_children( $child, $_item );	
			}
			
			// If item uses selfClosing, append nested children next to it @since 2.1
			if ( isset( $item->selfClosing ) && $item->selfClosing ) {
				$parent->ownerDocument->appendChild( $_item ); // insert after parent
			} else {
				$parent->appendChild( $_item ); // insert to parent
			}
		}
	}
	
	/**
	 * Append or insert child allowed HTML content to parent.
	 * 
	 * DOM createDocumentFragment() can't handle malformed HTML
	 * DOM loadHTML() can handle malformed HTML using libxml_use_internal_errors() before and after insertion
	 * Malformed HTML tag will be fixed automatically
	 * 
	 * @param {DOM Object}	$node		parent node to insert the child nodes
	 * @param {HTML}		$content	content contains HTML as child nodes
	 * 
	 * @return {DOM Object}	DOM parent with child nodes
	 * 
	 * @since 2.0.0
	 */
	function import_node( $parent, $content ) {
		libxml_use_internal_errors( true ); // handle error
		$html = new DOMDocument();

		// It should be noted that when any text is provided within the body tag 
		// outside of a containing element, the DOMDocument will encapsulate that 
		// text into a paragraph tag (<p>). So we need to encapsulate it with our own tag
		// and replace it later in extract_visual()
		$html->loadHTML( "<?xml encoding='UTF-8' ?><cf7content>$content</cf7content>" );
		
		foreach ( $html->getElementsByTagName('body')->item(0)->childNodes as $node ) {
			$parent = $parent->ownerDocument->importNode( $node, true ); // overwrite
		}
		
		libxml_use_internal_errors( false ); // handle error
		return $parent;
	}
	
	/**
	 * Create element based on item type.
	 * 
	 * @todo Add data validation/sanitazion routine
	 * 
	 * @param {Object}		$item	cf7sItems properties
	 * 
	 * @return {DOM Object}	element DOM
	 * 
	 * @since 2.0.0
	 */
	function create_element( $item ) {
		
		$dom = $this->dom; // assign this class DOM, see __construct()
		
		// CF7 Skins Visual items
		$cf7_tags = array( 
			'acceptance', 'checkbox', 'date', 'email', 'file', 'number', 'quiz', 'radio',
			 'select', 'submit', 'tel', 'text', 'textarea', 'url', 'recaptcha',
		);
		$cf7s_items = array( 'fieldset', 'paragraph', 'list-ol', 'list-li' );
		
		// Parse CF7 Skins items
		switch( $item->cf7sType ) {
			case 'fieldset':
				$skin_item = $dom->createElement( 'fieldset' );
				$label = isset( $item->cf7sLabel ) ? wp_strip_all_tags( $item->cf7sLabel ) : ''; // validate, set default to empty
				
				// Replace "&" to "cf7s&amp;" to avoid "Unterminated Entity Reference" issue,
				// and will be replaced back in extract_visual().
				// @link https://bugs.php.net/bug.php?id=39521
				// @since 0.7.2
				$label = str_replace( '&', 'cf7s&amp;', $label );
				
				$legend = $dom->createElement( 'legend', $label );
				$skin_item->appendChild( $legend );
				break;
				
			case 'paragraph':
				$skin_item = $dom->createElement( 'p' );
				
				// Bail early if not set or empty content, no need to parse child nodes
				if ( ! isset( $item->cf7sContent ) || ! $item->cf7sContent ) {
					break;
				}
				
				// Append content to paragraph with allowed HTML
				$cf7sContent = $this->import_node( $skin_item, $item->cf7sContent );
				$skin_item->appendChild( $cf7sContent );
				break;
				
			case 'list-ol':
				$skin_item = $dom->createElement( 'ol' );
				break;
				
			case 'list-li':
				$skin_item = $dom->createElement( 'li' );
				
				// Make list one line if <li> value is a text
				// Nested <ol> will have tab indent
				$_bool = array();
				
				if ( isset( $item->children ) && $item->children ) {
					foreach ( $item->children as $k => $child ) {
						if ( in_array( $child->cf7sType, $cf7_tags ) ) {
							$_bool[] = false;
						} elseif ( in_array( $child->cf7sType, $cf7s_items ) ) { // contain HTML
							$_bool[] = true;
						}
					}
				}
				
				if ( ! in_array( true, $_bool ) ) { // not true, display in one line
					$skin_item->nodeValue = ''; 
				}
				break;
				
			case in_array( $item->cf7sType, $cf7_tags ) :
				// Put text as label before shortcode tag
				// For submit button, value is used for the button text
				$label = 'submit' != $item->cf7sType && isset( $item->cf7sLabel ) ? esc_attr( $item->cf7sLabel ) . ' ' : '';
				$tags = $label . $this->create_cf7_tags( $item );
				
				// Replace "&" to "cf7s&amp;" to avoid "Unterminated Entity Reference" issue,
				// and will be replaced back in extract_visual().
				// @link https://bugs.php.net/bug.php?id=39521
				// @since 0.7.0
				$tags = str_replace( '&', 'cf7s&amp;', $tags );
				
				// Create tag helper, will be removed before saving
				// Inserted as a child node rather than text for formatting
				$skin_item = $dom->createElement( 'tag', $tags );
				
				// Condition attributes is used for shortcode content that need a closing tag
				// Check backward compatibility
				if ( isset( $item->cf7Content ) && $item->cf7Content ) {
					
					// Import the content
					$cf7Content = $this->import_node( $skin_item, $item->cf7Content );
					$skin_item->appendChild( $cf7Content );
					
					// Add closing shortcode tag
					$closing_tag = $dom->createTextNode( "[/{$item->cf7sType}]" );
					$skin_item->appendChild( $closing_tag ); 
				}
				
				break;
				
			default :
				$skin_item = $dom->createElement( 'cf7content' );
				break;
		}
		
		/**
		 * Element creation filter.
		 * 
		 * @param {DOM Object}	$skin_item	visual DOM elements
		 * @param {Object}		$item		visual item
		 * 
		 * @since 2.0.0
		 */
		return apply_filters( 'cf7skins_create_element', $skin_item, $item, $this );
	}
	
	/**
	 * Create CF7 form tag/shortcode based on visual item.
	 * 
	 * @param {Object]		$item	cf7sItems properties
	 * 
	 * @return {String}		CF7 shortcode tag attributes
	 * 
	 * @since 2.0.0
	 */
	function create_cf7_tags( $item ) {
		
		$arr = $tag = array();
		
		// Set default value if exists, not empty and allow zero (0)
		$defaultValue = null;
		if ( isset( $item->cf7Values ) ) {
			if ( ! empty( $item->cf7Values ) || $item->cf7Values === '0' ) {
				$defaultValue = '"'. esc_attr( trim( $item->cf7Values ) ) . '"'; // trim space
			}
		}
		
		// Name attributes for each item, no HTML tag
		$cf7Name = wp_strip_all_tags( $item->cf7Name );
		
		// Id attribute strips white space
		$cf7IdAttribute = $item->cf7IdAttribute ? 'id:'. str_replace( ' ', '', esc_attr( $item->cf7IdAttribute ) ) : null; // remove space
		
		// Split class name by white space
		$cf7ClassAttribute = $this->class_attribute( $item );
		
		switch( $item->cf7sType ) {
			case 'acceptance': // [acceptance acceptance-46 id:myid class:myclass default:on invert]
				$arr[] = 'acceptance';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7DefaultOn ? "default:on" : null;
				$arr[] = (bool) $item->cf7Invert ? "invert" : null;
				$arr[] = (bool) $item->cf7Optional ? "optional" : null; // @since 2.1
				break;
				
			case 'checkbox': // [checkbox* checkbox-991 id:my-id class:my-class label_first use_label_element exclusive "option 1" "option 2" "option 3"]
				// @link https://contactform7.com/checkboxes-radio-buttons-and-menus/
				$arr[] = (bool) $item->cf7Required ? 'checkbox*' : 'checkbox';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7LabelFirst ? "label_first" : null;
				$arr[] = (bool) $item->cf7UseLabelElement ? "use_label_element" : null;
				$arr[] = (bool) $item->cf7Exclusive ? "exclusive" : null;
				$default = $this->default_tag( $item );
				$arr = array_merge( $arr, $default );
				break;
				
			case 'radio': // [radio radio-699 id:my-id class:my-class label_first use_label_element default:1 "option 1" "option 2"]
				$arr[] = 'radio';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7LabelFirst ? "label_first" : null;
				$arr[] = (bool) $item->cf7UseLabelElement ? "use_label_element" : null;
				$default = $this->default_tag( $item );
				$arr = array_merge( $arr, $default );
				break;
				
			case 'select': // [select* menu-624 id:my-id class:my-class multiple include_blank "option 1" "option 2" "option 3"]
				$arr[] = (bool) $item->cf7Required ? 'select*' : 'select';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Multiple ? "multiple" : null;
				$arr[] = (bool) $item->cf7IncludeBlank ? "include_blank" : null;
				$default = $this->default_tag( $item );
				$arr = array_merge( $arr, $default );
				break;
				
			case 'date': // [date* date-838 min:2016-12-07 max:2016-12-31 id:my-id class:my-class placeholder "my-default-value"]
				$arr[] = (bool) $item->cf7Required ? 'date*' : 'date';
				$arr[] = $cf7Name;
				$arr[] = $item->cf7Min ? "min:{$item->cf7Min}" : null; // validation ??
				$arr[] = $item->cf7Max ? "max:{$item->cf7Max}" : null; // validation ??
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'email': // [email* email-421 id:my-id class:my-class placeholder akismet:author_email "email@domain.com"]
				$arr[] = (bool) $item->cf7Required ? 'email*' : 'email';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = (bool) $item->cf7AkismetAuthorEmail ? "akismet:author_email" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'file': // [file* file-535 limit:55556 filetypes:png|type id:my-id class:my-class]
				$arr[] = (bool) $item->cf7Required ? 'file*' : 'file';
				$arr[] = $cf7Name;
				$arr[] = $item->cf7Limit ? 'limit:'. (int) $item->cf7Limit : null; // integer, file size limit (bytes)
				$arr[] = $item->cf7FileTypes ? 'filetypes:'. str_replace( ' ', '|', $item->cf7FileTypes ) : null; // replace space to pipe |
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				break;
				
			case 'number': // [range* number-83 min:14 max:33 id:my-id class:my-class placeholder "my-default-value"]
				$required = $item->cf7Required ? '*' : '';
				$arr[] = $item->cf7TagType === 'number' ? "number$required" : "range$required";	
				$arr[] = $cf7Name;
				$arr[] = $item->cf7Min || 0 === $item->cf7Min ? 'min:'. (int) $item->cf7Min : null; // use isset to allow zero (0)
				$arr[] = $item->cf7Max || 0 === $item->cf7Max ? 'max:'. (int) $item->cf7Max : null;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'quiz': // [quiz quiz-135 id:my-id class:my-class "The capital of Japan?|Tokyo" "The capital of France?|Paris"]
				$arr[] = 'quiz';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				foreach( $item->cf7Options as $option )
					if ( isset( $option->question ) && $option->question && isset( $option->answer ) && $option->answer  ) // check if both question & answers are set and not empty
							$arr[] = '"'. $option->question . '|' . $option->answer . '"'; // put double quotes for each option
				break;
				
			case 'submit': // [submit id:my-id class:my-class "my-label"]
				$arr[] = 'submit';
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = $item->cf7sLabel ? '"'. esc_attr( $item->cf7sLabel ) .'"' : null;
				break;
				
			case 'tel': // [tel* tel-123 id:my-id class:my-class placeholder "my-default-value"]
				$arr[] = (bool) $item->cf7Required ? 'tel*' : 'tel';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'text': // [text* text-893 id:my-id class:my-class placeholder akismet:author "my-default-value"]
				$arr[] = (bool) $item->cf7Required ? 'text*' : 'text';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = $item->cf7AkismetAuthor ? "akismet:author" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'textarea': // [textarea* textarea-619 id:my-id class:my-class placeholder "my-default-value"]
				$arr[] = (bool) $item->cf7Required ? 'textarea*' : 'textarea';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'url': // [url* url-18 id:my-id class:my-class placeholder akismet:author_url "https://www.google.com/"]
				$arr[] = (bool) $item->cf7Required ? 'url*' : 'url';
				$arr[] = $cf7Name;
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = (bool) $item->cf7Placeholder ? "placeholder" : null;
				$arr[] = (bool) $item->cf7AkismetAuthorUrl ? "akismet:author_url" : null;
				$arr[] = $defaultValue;
				break;
				
			case 'recaptcha': // [recaptcha id:my-id class:my-class size:compact theme:dark]
				$arr[] = 'recaptcha';
				$arr[] = $cf7IdAttribute;
				$arr[] = $cf7ClassAttribute;
				$arr[] = $item->cf7Size && 'normal' !== $item->cf7Size ? 'size:'. esc_attr( $item->cf7Size ) : null;
				$arr[] = $item->cf7Theme &&'light' !== $item->cf7Theme ? 'theme:'. esc_attr( $item->cf7Theme ) : null;
				break;
		}
		
		// Remove empty array
		foreach( $arr as $ar ) {
			if( $ar ) {
				$tag[] = $ar;
			}
		}

		// Add a text field for select/radio if cf7sOther enabled
		$cf7sOther = isset( $item->cf7sOther ) ? "[text other-{$item->cf7Name} class:cf7s-other]" : '';

		// Output CF7 tag
		return '[' . implode( " ", $tag ) . ']' . $cf7sOther;
	}
	
	/**
	 * Split class name by space.
	 * 
	 * For example: foo bar -> class:foo class:bar
	 * 
	 * @param {Object}		$item	cf7sItems properties
	 * 
	 * @return {String}		joined className
	 * 
	 * @since 2.0.0
	 */
	function class_attribute( $item ) {
		$cf7ClassAttribute = null;
		
		// If class attribute is filled
		if ( $item->cf7ClassAttribute ) {
			$array = explode( " ", $item->cf7ClassAttribute ); // split by space
			
			$className = array();
			
			foreach( $array as $class ) {
				$className[] = 'class:'. esc_attr( $class ); // class attribute with validation
			}
			
			$cf7ClassAttribute = implode( " ", $className ); // overwrite, join each className by space
		}
		
		return $cf7ClassAttribute;
	}
	
	/**
	 * Pre checked/selected options for checkbox, radio and select.
	 * Show default first, than all values
	 * 
	 * @link https://contactform7.com/checkboxes-radio-buttons-and-menus/
	 * 
	 * @param {Object}		$item	cf7sItems properties
	 * 
	 * @return {String}		CF7 default attribute
	 * 
	 * @since 2.0.0
	 */
	function default_tag( $item ) {
		$default = $arr = $values = array(); 
		$i = 1;
		
		foreach( $item->cf7Options as $option ) {
			$values[] = '"'. $option->value . '"'; // put double quotes for each option
			if ( $option->isChecked ) {
				$default[$i] = $i;
			}
			$i++;
		}
		
		if ( $default ) { // pre-checked options
			$arr[] = 'default:'. implode( "_", $default );
		}
		
		$arr = array_merge( $arr, $values ); // merge, default first, then values
		
		return $arr;
	}
	
	/**
	 * ADD DESCRIPTION
	 * 
	 * Fires while selecting template
	 * Add visual template script object to the template list
	 * This is done in the admin page during AJAX call
	 * 
	 * @since 2.0.0
	 */
	function select_template() {
		// Check nonce for security	
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $this->nonce ) ) {
			die();
		}

		// Deselect template return empty JSON
		// @since 0.6.2
		if ( 'true' === $_POST['deselect'] ) { // becomes a string from JavaScript
			echo json_encode( array() ); // ouput empty JSON for Visual checking to avoid error
			exit();
		}

		// Read visual template file
		$templates = CF7_Skin_Template::cf7s_get_template_list();
		$template = $templates[ sanitize_text_field( $_POST['template'] ) ];

		// Load visual file if available
		$visual_file = 'visual.json';

		// https://codex.wordpress.org/Filesystem_API
		global $wp_filesystem;

		// Check if visual.json exists in directory
		if ( in_array( $visual_file , $template['files'] ) ) {
			echo $wp_filesystem->get_contents( $template['path'] . trailingslashit( $template['dir'] ) . $visual_file );

		// File visual.json is not found in directory
		// Return empty JSON to avoid error and empty Visual treeData as CF7 textarea changed
		} else {
			echo json_encode( array() );
		}

		exit();
	}
	
	/**
	 * Register vendor scripts for < WP 5.
	 * 
	 * @link https://make.wordpress.org/core/2018/12/06/javascript-packages-and-interoperability-in-5-0-and-beyond/
	 * 
	 * @since 2.3.0
	 */
	function vendor_scripts() {
			
		// Check if React is registered using 'wp-element', a thin abstraction over 
		// the React interface, and implements most all of the same functionality.
		if ( ! wp_script_is( 'wp-element' , 'registered' ) ) {
			
			// Enqueue our own copy of React and React DOM. 
			wp_register_script( 'react', $this->url . 'js/react.production.min.js',
				array(), '16.4.2', true );
				
			wp_register_script( 'react-dom', $this->url . 'js/react-dom.production.min.js',
				array(), '16.4.2', true );
		}
		
		// Enqueue our own copy of Lodash
		if ( ! wp_script_is( 'lodash' , 'registered' ) ) {
			wp_register_script( 'lodash', $this->url . 'js/lodash.min.js',
				array(), '4.17.4', true );
			
			// Add lodash into window only if is not defined
			// See Gutenberg approach https://github.com/WordPress/gutenberg/blob/master/lib/client-assets.php#L562
			wp_add_inline_script( 'lodash', implode( "\n", array(
				'if ( ! window.lodash ) {',
				'	window.lodash = _.noConflict();',
				'}',
			)));
		}
		
		// Enqueue wp-data if is not shipped with current WordPress version @since 2.5
		if ( ! wp_script_is( 'wp-data', 'registered' ) ) {
			wp_register_script( 'wp-data', $this->url . 'js/wp-data.min.js', array(), '4.4.0', true );
		}

		// Enqueue wp-components if is not shipped with current WordPress version @since 2.5
		if ( ! wp_script_is( 'wp-components', 'registered' ) ) {
			wp_register_script( 'wp-components', $this->url . 'js/wp-components.min.js', array(), '4.4.0', true );
		}
	}
	
	/**
	 * Add backend scripts and styles.
	 * 
	 * Priority set to 11, right after wpcf7_admin_enqueue_scripts() where priority default: 10.
	 * 
	 * @since 2.0.0
	 */
	function enqueue_scripts() {
		
		if ( ! CF7_Skins_Admin::edit_page() ) { // return if this is not CF7 editing page
			return;
		}

		wp_enqueue_style( 'visual',
			$this->url . 'css/visual.min.css',
			array(), $this->version );

		// Enqueue wp-i18n if is not shipped with current WordPress version @since 2.3.0
		if ( ! wp_script_is( 'wp-i18n', 'registered' ) ) {
			wp_enqueue_script( 'wp-i18n', $this->url . 'js/wp-i18n.min.js',
				array(), '3.1.0', true );
		}

		wp_enqueue_script( 'cf7skins',
			CF7SKINS_URL . 'js/cf7skins.min.js',
			array( 'cf7s-admin', 'lodash', 'react', 'react-dom', 'wp-data', 'wp-i18n', 'wp-components' ), CF7SKINS_VERSION, false );	

		wp_enqueue_script( 'visual',
			$this->url . 'js/visual.min.js',
			array( 'cf7skins' ), $this->version, true ); // added after 'wpcf7-admin'

		// Current contact form 7 object, null for non valid post ID
		$cf7 = wpcf7_get_current_contact_form();

		// Get visual meta data based on current editing CF7 form id
		$items = array();
		if ( isset( $_GET['post'] ) ) {
			$items = get_post_meta( (int) $_GET['post'], 'cf7s_visual', true );
		}

		// Add options from CF7 Skins settings page
		// @since 0.6.3
		$options = array();
		$cf7s_option = get_option( CF7SKINS_OPTIONS );
		$options['showName'] = isset( $cf7s_option['show_name'] ) && $cf7s_option['show_name'] ? true : false;
		$options['showCopyPaste'] = isset( $cf7s_option['show_copy_paste'] ) && $cf7s_option['show_copy_paste'] ? true : false;

		// Add integration @since 0.5.4
		$integration = array();

		$recaptcha = WPCF7_RECAPTCHA::get_instance();

		if ( $recaptcha->is_active() ) {
			$integration['reCAPTCHA'] =  $recaptcha->is_active();
		}

		// Add versions @since 2.1
		global $wp_version;
		$versions = array();
		$versions['wp'] = $wp_version;
		$versions['cf7'] = WPCF7_VERSION;

		// Localize script filter
		$localize = apply_filters( 'cf7svisual_localize_script', array( 
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'versions'		=> $versions, // @since 2.1
			'nonce'			=> wp_create_nonce( $this->nonce ), // generate a nonce for security checking
			'update'		=> 'cf7skins_visual_update', // post action for saving
			'options'		=> $options,
			'id' 			=> $cf7 ? $cf7->id() : '', // @since 2.3.0
			'title' 		=> $cf7 ? $cf7->title() : '',
			'items' 		=> $items,
			'integration' 	=> $integration,
			'environment'	=> defined('CF7SKINS_ENV') ? CF7SKINS_ENV : null,  // @since 2.1
			'elements' 		=> array( // Forms element IDs
				'form' 		=> CF7SKINS_ELEMENTS_FORM,
				'textarea' 	=> CF7SKINS_ELEMENTS_TEXTAREA,
			),
		) );

		// Output visual items as a JS var
		$localize_handle = apply_filters( 'cf7svisual_localize_script_handle', 'visual' ); // @since 2.3
		wp_localize_script( $localize_handle, 'cf7svisual', $localize );
	}
	
	/**
	 * Add translation script with backward compatibility for WP < 5
	 * 
	 * Priority set to 12, right after the handle script 'visual': 11.
	 * 
	 * @since 2.3.0
	 */
	function translation_scripts() {
		$domain = 'contact-form-7-skins'; // should match in JS file domain
		$this->print_translation_scripts( 'visual', $domain, CF7SKINS_PATH );
	}
	
	/**
	 * Add translation script with backward compatibility for WP < 5
	 * 
	 * Priority set to 12, right after the handle script 'visual': 11.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_set_script_translations/
	 * @link https://developer.wordpress.org/reference/classes/wp_scripts/print_translations/
	 *
	 * @param {String} 	$handle		Script handle the textdomain will be attached to.
	 * @param {String} 	$domain 	Text domain. Default 'default'.
	 * @param {String} 	$path   	The full file path to the directory containing translation files.
	 * 
	 * @since 2.3.0
	 */
	public static function print_translation_scripts( $handle, $domain, $path ) {

		if ( ! CF7_Skins_Admin::edit_page() ) { // return if this is not CF7 editing page
			return;
		}

		// Tell WordPress 5.x our script contains translations @since 2.3.0
		if ( function_exists( 'wp_set_script_translations' ) ) {

			// https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			// WordPres check if a file in the format of ${domain}-${locale}-${handle}.json or md5 filename
			// before defaulting to the WordPress languages directory
			wp_set_script_translations( $handle, $domain, $path . 'languages' );

		// For WordPress 4, follow WP_Scripts::print_translations() approach
		} else {
			$locale = get_locale(); // or use user locale get_user_locale() ?

			$translation_file = $path . "languages/{$domain}-{$locale}-{$handle}.json";

			// Set default empty locale data object to ensure the domain still exists.
			$json_translations = '{ "locale_data": { "messages": { "": {} } } }';

			global $wp_filesystem; // https://codex.wordpress.org/Filesystem_API

			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}

			if ( $wp_filesystem->exists( $translation_file ) ) { // check if .json exists in directory
				$json_translations = $wp_filesystem->get_contents( $translation_file );
			}

			$jscript = <<<JS
( function( domain, translations ) {
    var localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
    localeData[""].domain = domain;
    wp.i18n.setLocaleData( localeData, domain );
} )( "{$domain}", {$json_translations} );
JS;
			wp_add_inline_script( 'visual', $jscript, $position = 'before' );
		}
	}
	
	/**
	 * Copy/duplicate visual form.
	 * 
	 * @param {Object}		$cf7	CF7 object
	 * 
	 * @since 2.0.0
	 */
	function copy_visual( $cf7 ) {
		$meta = get_post_meta( $cf7->copy_id, 'cf7s_visual', true ); // get original logic
		update_post_meta( $cf7->id(), 'cf7s_visual', $meta ); // do copy
	}
	
	/**
	 * Add Visual setting fields
	 * 
	 * @param {Array}	$fields		All CF7 Skins registered fields
	 * 
	 * @return {Array}	CF7 Skins fields
	 * 
	 * @since 2.0.0
	 */
	function setting_fields( $fields ) {
		$fields['show_copy_paste'] = array( // @ since 2.1
			'section' => 'advanced',
			'label' => __( 'Visual Data', 'contact-form-7-skins' ),
			'type' => 'checkbox',
			'default' => false,
			'detail' => __( 'Enable copy & paste of visual data.', 'contact-form-7-skins' ),
		);
		
		$fields['show_name'] = array( 
			'section' => 'advanced',
			'label' => __( 'Show Field Names', 'contact-form-7-skins' ),
			'type' => 'checkbox',
			'default' => false,
			'detail' => __( 'Show field names on each form field.', 'contact-form-7-skins' ),
		);
		
		return $fields;
	}
	
	/**
	 * Development purpose, should be deleted for production
	 * 
	 * @since 2.0.0
	 */
	function log_scripts() {
		// Return if this is not CF7 editing page
		if ( ! CF7_Skins_Admin::edit_page() || ! defined('CF7SKINS_ENV') ) {
			return;
		}
		
		// Return if is not in development environment (production) // @since 0.7.0
		if ( 'development' !== CF7SKINS_ENV ) {
			return;
		}
		?>
		<script type="text/javascript">
			// logs the whole global cf7svisual object to the console
			// console.log(JSON.stringify(cf7svisual,null,2));
		</script>
		<?php
	}
	
} // end class

/**
 * Visual admin panel will be available only for users with edit capability.
 * 
 * @link http://contactform7.com/restricting-access-to-the-administration-panel/
 * 
 * @since 2.0.0
 */
if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
	new CF7_Skins_Admin_Visual();
}
