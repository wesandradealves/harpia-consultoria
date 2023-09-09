<?php
/**
* Template Name: Fieldset - Basic 
* Template URI: http://
* Author: Neil Murray
* Author URI: http://cf7skins.com
* Description:
* Instructions:
* Version: 1.1
* Version Date: 2018-03-30
* Tags: popular, featured, fieldset
**/
?>
<fieldset>
	<legend><?php _e( 'Your Details', 'contact-form-7-skins'); ?></legend>
	<ol>
		<li> <?php _e( 'Name', 'contact-form-7-skins'); ?> [text cf7s-name] </li>
		<li> <?php _e( 'Email', 'contact-form-7-skins'); ?> [email* cf7s-email] </li>
		<li> <?php _e( 'Phone', 'contact-form-7-skins'); ?> [text cf7s-phone] </li>
		<li> <?php _e( 'Message', 'contact-form-7-skins' ); ?> [textarea cf7s-message] </li>
	</ol>
	[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
	<p>* <?php _e( 'Required', 'contact-form-7-skins' ); ?></p>
</fieldset>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempus pharetra vehicula. Aliquam pellentesque mi non scelerisque placerat.</p>