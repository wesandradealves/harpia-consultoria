<?php
/**
* Template Name: Testimonial
* Description:
* Instructions:
* Version: 1.0
* Version Date: 2020-05-31
* Tags:
**/
?>
<fieldset>
	<legend><?php _e( 'Testimonial Form', 'contact-form-7-skins'); ?></legend>
	<p><?php _e( 'Please let us know what you think.', 'contact-form-7-skins'); ?></p>
	<ol>
		<li> <?php _e( 'Which product did you buy?', 'contact-form-7-skins'); ?> [radio cf7s-testimonial1 default:1 "<?php _e( 'Product 1', 'contact-form-7-skins' ); ?>" "<?php _e( 'Product 2', 'contact-form-7-skins' ); ?>" "<?php _e( 'Product 3', 'contact-form-7-skins' ); ?>" "<?php _e( 'Other', 'contact-form-7-skins' ); ?>"] [text cf7s-other]</li>
		<li> <?php _e( 'What did you find as a result of buying this product?', 'contact-form-7-skins'); ?> [textarea cf7s-testimonial2] </li>
		<li> <?php _e( 'What specific feature did you like most about this product?', 'contact-form-7-skins'); ?> [textarea cf7s-testimonial3] </li>
		<li> <?php _e( 'What would be three other benefits about this product?', 'contact-form-7-skins'); ?> [textarea cf7s-testimonial4] </li>
		<li> <?php _e( 'Would you recommend this product? If so why?', 'contact-form-7-skins'); ?> [textarea cf7s-testimonial5] </li>
		<li> <?php _e( "Is there anything you'd like to add?", 'contact-form-7-skins'); ?> [textarea cf7s-testimonial6] </li>
	</ol>
	<fieldset>
		<legend><?php _e( 'Contact Information', 'contact-form-7-skins'); ?></legend>
		<p><?php _e( "Please enter your contact information if you'd like for us to follow up with you.", 'contact-form-7-skins'); ?></p>
		<ol>
			<li>
				<p>
					<label for="cf7s-name"><?php _e( 'Name', 'cf7skins-pro'); ?></label>
				</p>
			</li>
			<li>
				<ol class="singleline">
					<li> <?php _e( 'First', 'contact-form-7-skins'); ?> [text cf7s-name-first placeholder "<?php _e( 'First Name', 'contact-form-7-skins' ); ?>"] </li>
					<li> <?php _e( 'Last', 'contact-form-7-skins'); ?> [text cf7s-name-last] </li>
				</ol>
			</li>
			<li> <?php _e( "Email", 'contact-form-7-skins'); ?> [email cf7s-email] </li>
		</ol>
	</fieldset>
	[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
</fieldset>
