<?php
/**
* Template Name: Suggestion
* Template URI: http://
* Author: Neil Murray
* Author URI: http://cf7skins.com
* Description:
* Instructions:
* Version: 1.1
* Version Date: 2018-03-30
* Tags: 
**/
?>
<fieldset>
	<legend><?php _e( 'Suggestion Form', 'contact-form-7-skins'); ?></legend>
	<p><strong><?php _e( 'Please let us know what you think.', 'contact-form-7-skins'); ?></strong></p>
	<ol>
		<li> <?php _e( 'In which of the following areas do you have a suggestion?', 'contact-form-7-skins'); ?> [select cf7s-select1 multiple"<?php _e( 'Area 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Area 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Area 3', 'contact-form-7-skins' ); ?>" "<?php _e( 'Area 4', 'contact-form-7-skins' ); ?>"] </li>
		<li> <p><?php _e( 'Note: You can select multiple items (Use Shift or Ctrl/Cmd + Click)', 'contact-form-7-skins'); ?></p> </li>
		<li> <?php _e( 'Suggestion', 'contact-form-7-skins'); ?> [text cf7s-suggestion] </li>
		<li> <?php _e( 'Details', 'contact-form-7-skins'); ?> [textarea cf7s-details] </li>
		<li> <?php _e( 'Your Email - please enter your email if you would like us to follow up with you.', 'contact-form-7-skins'); ?> [email cf7s-email] </li>
		<li> <?php _e( 'Radio buttons', 'contact-form-7-skins'); ?> [radio cf7s-radio1 default:1 "<?php _e( 'Option 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 3', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Checkboxes', 'contact-form-7-skins'); ?> [checkbox cf7s-checkbox1 "<?php _e( 'Option 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Option 3', 'contact-form-7-skins' ); ?>"] </li>
	</ol>
	[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
</fieldset>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tempus pharetra vehicula. Aliquam pellentesque mi non scelerisque placerat.</p>