<?php
/**
* Template Name: Fieldset - Multiple
* Template URI: http://
* Author: Neil Murray
* Author URI: http://cf7skins.com
* Description:
* Instructions:
* Version: 1.1
* Version Date: 2018-03-30
* Tags: fieldset
**/
?>
<fieldset>
	<legend><?php _e( 'Your Details', 'contact-form-7-skins' ); ?></legend>
	<ol>
		<li> <?php _e( 'Name', 'contact-form-7-skins' ); ?> [text cf7s-name] </li>
		<li> <?php _e( 'Email', 'contact-form-7-skins' ); ?> [email* cf7s-email] </li>
		<li> <?php _e( 'Phone', 'contact-form-7-skins' ); ?> [text cf7s-phone] </li>
		<li> <?php _e( 'Message', 'contact-form-7-skins' ); ?> [textarea cf7s-message] </li>
	</ol>
</fieldset>
<p>Use paragraphs for text that is not a form field.</p>
<fieldset>
	<legend><?php _e( 'Your Requirements', 'contact-form-7-skins'); ?></legend>
	<ol>
		<li> <?php _e( 'Checkboxes', 'contact-form-7-skins'); ?> [checkbox cf7s-checkbox-01 "<?php _e( 'Option 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 3', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Radio Buttons', 'contact-form-7-skins'); ?> [radio cf7s-radio-01 default:1 "<?php _e( 'Yes', 'contact-form-7-skins' ); ?>" "<?php _e( 'No', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Dropdown Select', 'contact-form-7-skins'); ?> [select cf7s-select-01 "<?php _e( 'Item 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Item 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Item 3', 'contact-form-7-skins' ); ?>"] </li>
	</ol>
</fieldset>
[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
<p>* <?php _e( 'Required', 'contact-form-7-skins' ); ?></p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempus pharetra vehicula. Aliquam pellentesque mi non scelerisque placerat.</p>