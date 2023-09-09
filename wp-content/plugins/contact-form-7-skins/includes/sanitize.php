<?php

/**
 * Sanitize every value in visual data
 * 
 * @param {Array}	$data	array contains of visual data node object
 * 
 * @return {Array}	sanitized $data
 *
 * @since 2.5.0
 */
function cf7skins_sanitize_visual_data( $raw_data ) {
	
	// Test unsafe data, uncheck to apply
	// $dataLength = count( $data );
	// $data[ rand( 0, $dataLength - 1 ) ]->cf7Name = "<em>italic</em>cf7-email";
	// $data[ rand( 0, $dataLength - 1 ) ]->cf7sLabel = "<script>alert(1)</script>Show only this text";
	$data = array();

	foreach( $raw_data as $index => $node ) { // loop each node
		
		$data[ $index ] = new stdClass();
		
		foreach( $node as $key => $value ) { // loop each node prop

			switch ( $key ) {
				case 'children': // recursive
					$data[ $index ]->$key = cf7skins_sanitize_visual_data( $value );
					break;
				case 'cf7Required': // boolean
				case 'cf7DefaultOn':
				case 'cf7Invert':
				case 'noChildren':
				case 'cf7LabelFirst':
				case 'cf7UseLabelElement':
				case 'cf7Exclusive':
				case 'cf7Placeholder':
				case 'cf7AkismetAuthor':
				case 'cf7AkismetAuthorUrl':
				case 'cf7AkismetAuthorEmail':
				case 'cf7Multiple':
				case 'cf7IncludeBlank':
				case 'expanded':
					$data[ $index ]->$key = (bool) $value;
					break;
				case 'cf7sContent':
				case 'cf7Content': // acceptance
					$data[ $index ]->$key = sanitize_textarea_field( $value );
					break;
				case 'cf7Options':
					$data[ $index ]->$key = cf7skins_sanitize_cf7_options( $value );
					break;					
				case 'cf7TagOptions':
					$data[ $index ]->$key = cf7skins_sanitize_cf7_tag_options( $value );
					break;
				case 'cf7sType':
				case 'cf7Name':
				case 'cf7sSelectGroup':
				case 'cf7Name':
				case 'cf7sLabel':
				case 'cf7sIcon':
				default:
					// Check if value is an array (non-multidimensional) or string
					if ( is_array( $value ) ) {
						foreach( $value as $k => $v ) {
							$data[ $index ]->$key[$k] = sanitize_text_field( $v );
						}			
					} else {
						$data[ $index ]->$key = sanitize_text_field( $value );
					}
					break;
			}
		}
	}		
	
	return apply_filters( 'cf7s_visual_sanitize_data', $data, $raw_data ); // @since @2.5.3
}


/**
 * 
 * Tag options sanitazion for select, checkboxes etc.
 * 
 * @param {Array}	$options	options array contains of option object
 *
 * @return {Array}	sanitized options
 *
 * @since 2.5.0
 */
function cf7skins_sanitize_cf7_options( $options ) {

	foreach( $options as $index => $option ) {
		foreach( $option as $key => $value ) {
			switch ( $key ) {
				case 'isChecked':
					$options[ $index ]->$key = (bool) $value;
					break;
				case 'question':
				case 'answer':
				case 'value':
				default:
					$options[ $index ]->$key = sanitize_text_field( $value );
					break;
			}
		}
	}

	return $options;
}

/**
 * 
 * Tag attribute options sanitazion.
 * 
 * @param {Array}	$options	options array contains of option object
 *
 * @return {Array}	sanitized options
 *
 * @since 2.5.0
 */
function cf7skins_sanitize_cf7_tag_options( $options ) {

	foreach( $options as $index => $option ) {
		foreach( $option as $key => $value ) {
			switch ( $key ) {
				case 'isChecked':
					$options[ $index ]->$key = (bool) $value;
					break;
				case 'cf7Option':
				case 'optionLabel':
				case 'optionType':
				case 'optionLabeloptionLabel': // translation ?
				default:
					$options[ $index ]->$key = sanitize_text_field( $value );
					break;
			}
		}
	}

	return $options;
}
