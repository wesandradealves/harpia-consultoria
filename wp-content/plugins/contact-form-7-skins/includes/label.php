<?php

/**
 * Add label and require <em> to CF7 form content.
 * 
 * e.g. <li>Name [text* cf7s-name]</li> changed to
 * <li><label for="cf7s-name">Name <em class="cf7s-reqd">*</em></label> [text* cf7s-name]</li>
 * 
 * @param (HTML/String)		$form		Current CF7 form content
 * 
 * @since 2.1.3
 */
function cf7skins_add_label_req( $form ) {
	
	// Get all current shortcode
	$scanned = CF7_Skins_CF7_Connect::scan_shortcode( $form );
	
	// Get all shortcodes id with tag name as the index
	$ids = $tags = array();
	
	// Required feature setting
	$option = get_option( CF7SKINS_OPTIONS );
	$add_asterisk = isset( $option['add_asterisk'] ) ? (bool) $option['add_asterisk'] : true;
	
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
		$ids[$tag->name] = $tag->get_id_option() ? $tag->get_id_option() : $tag->name;
	}
	
	libxml_use_internal_errors( true ); // handle error
	$dom = new DOMDocument( '1.0', 'utf-8' );	
	$dom->loadHTML( "<?xml encoding='UTF-8' ?>". $form );
	libxml_use_internal_errors( false ); // handle error
	
	// Get all lists (and nested list)
	$lists = $dom->getElementsByTagName('li');
	
	if ( $lists->length ) { // only if list exists
	
		foreach( $lists as $list ) { // loop for each

			foreach ( $list->childNodes as $node ) { // loop each child nodes
				
				$content = trim( $node->textContent ); // remove spaces from plain text
				
				// If is a text node and not empty (trim spaces/tabs)
				// https://www.php.net/manual/en/dom.constants.php#constant.xml-text-node
				if ( $node->nodeType === XML_TEXT_NODE && ! empty( $content ) ) {
					
					// Loop for each type to find if the content contains shortcode
					foreach( $types as $type ) {
						$shortcode_type = "[{$type} "; // bracket+type+required+space, i.e. '[select* '
						
						// Shortcode exists in content, explode it and get the label
						if ( strpos( $content, $shortcode_type ) !== false ) {
							
							// Get the label by exploding the content with shortcode type
							// Explode to get label and rest text, 0 is the label and 1 is the rest
							$explode = explode( $shortcode_type, $content );
							
							$label = trim( $explode[0] ); // get the label, trim space
							
							$name = explode( ' ', $explode[1] )[0]; // explode by space to get the name
							$name = str_replace( ']', '', $name ); // remove ending bracket i.e. [date* date-597]
							$id = isset( $ids[$name] ) ? $ids[$name] : $name; // check existance in ids
							
							// Create <label/> tag and set for
							$label_el = $dom->createElement( 'label', $label );
							$label_el->setAttribute( 'for', $id );							
							
							// Add * required <em /> tag if enabled and set to required
							if ( $add_asterisk ) {
								if ( strpos( $type, '*' ) !== false ) { // is required
									$req_el = $dom->createElement( 'em', '*' );
									$req_el->setAttribute( 'class', 'cf7s-reqd' );
									$label_el->appendChild( $req_el );
								}
							}
							
							// Remove label text
							$node->nodeValue = str_replace( $label, '', $content );
						}
					}
					
					// Insert the label inside <li /> list
					if ( isset( $label_el ) ) {
						$list->insertBefore( $label_el, $node );
					}
				}
			}
		}
	}			
	
	// Filter to enable further modification to HTML form
	$dom = apply_filters( 'cf7skins_form_dom', $dom );
	
	return $dom->saveXML( $dom );
}
