<?php
/**
* Template Name: Event
* Template URI: http://
* Author: Neil Murray
* Author URI: http://cf7skins.com
* Description:
* Instructions:
* Version: 1.1
* Version Date: 2018-03-30
* Tags: featured, event
**/
?>
<fieldset>
	<legend><?php _e( 'Event', 'contact-form-7-skins'); ?></legend>
	<ol>
		<li> <?php _e( 'Name', 'contact-form-7-skins'); ?> [text cf7s-name] </li>
		<li> <?php _e( 'Phone Number', 'contact-form-7-skins'); ?> [tel cf7s-phone] </li>
		<li> <?php _e( 'Email', 'contact-form-7-skins'); ?> [email* cf7s-email] </li>
		<li> <?php _e( 'Which workshops will you be attending?', 'contact-form-7-skins' ); ?> [checkbox cf7s-checkbox1 "<?php _e( 'Option 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 3', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Are you an existing customer?', 'contact-form-7-skins' ); ?> [radio cf7s-radio1 default:1 "<?php _e( 'Yes', 'contact-form-7-skins' ); ?>" "<?php _e( 'No', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'How did find out about this event?', 'contact-form-7-skins'); ?> [select cf7s-select1 "<?php _e( 'Option 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 3', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Comments or Questions', 'contact-form-7-skins'); ?> [textarea cf7s-comments] </li>
	</ol>
	[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
	<p>* <?php _e( 'Required', 'contact-form-7-skins' ); ?></p>
</fieldset>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempus pharetra vehicula. Aliquam pellentesque mi non scelerisque placerat.</p>