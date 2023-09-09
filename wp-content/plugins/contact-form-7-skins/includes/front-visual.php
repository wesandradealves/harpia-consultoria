<?php
/**
 * CF7 Skins Visual Front End Class.
 * 
 * @since 2.0.0
 */
Class CF7_Skins_Front_Visual {

	var $form_id; 	 // CF7 form ID

	/**
	 * Sets properties and hooks.
	 * 
	 * @since 2.0.0
	 */
	function __construct() {
		add_filter( 'wpcf7_contact_form_properties', array( $this, 'set_form_id' ), 10, 2 );
	}
	
	/**
	 * Set class form id.
	 * 
	 * This function does nothing to the current CF7 form, just add this class variable for further use.
	 * 
	 * @param $properties (array) current contact form properties
	 * @param $cf7 (object) current contact form object
	 * 
	 * @since 2.0.0
	 */	
	function set_form_id( $properties, $cf7 ) {
		$this->form_id = $cf7->id();
		return $properties;
	}
	
} new CF7_Skins_Front_Visual();
