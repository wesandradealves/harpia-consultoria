<?php
/**
* Template Name: Complaint
* Description:
* Instructions:
* Version: 1.0
* Version Date: 2020-05-31
* Tags:
**/
?>
<fieldset>
	<legend><?php _e( 'Complaint Form', 'contact-form-7-skins'); ?></legend>
	<p><?php _e( 'Please let us know what you think.', 'contact-form-7-skins'); ?></p>
	<ol>
		<li> <?php _e( 'Which Department did you do business with?', 'contact-form-7-skins'); ?> [radio cf7s-radio1 default:1 "<?php _e( 'Sales', 'contact-form-7-skins' ); ?>" "<?php _e( 'Marketing', 'contact-form-7-skins' ); ?>" "<?php _e( 'Accounting', 'contact-form-7-skins' ); ?>" "<?php _e( 'Customer Services', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'My complaint involves', 'contact-form-7-skins'); ?> [radio cf7s-radio2 default:1 "<?php _e( 'A pre-purchase problem', 'contact-form-7-skins' ); ?>" "<?php _e( 'A post-purchase problem', 'contact-form-7-skins' ); ?>" "<?php _e( 'A problem during purchase', 'contact-form-7-skins' ); ?>" "<?php _e( 'Other', 'contact-form-7-skins' ); ?>"] [text cf7s-other]</li>
		<li> <?php _e( 'State Of Residence', 'contact-form-7-skins'); ?> [select cf7s-select1 "<?php _e( 'Alabama', 'contact-form-7-skins' ); ?>" "<?php _e( 'California', 'contact-form-7-skins' ); ?>" "<?php _e( 'Florida', 'contact-form-7-skins' ); ?>"] </li>
		<li> <?php _e( 'Subject', 'contact-form-7-skins'); ?> [text* cf7s-subject] </li>
		<li> <?php _e( 'Message', 'contact-form-7-skins'); ?> [textarea* cf7s-message] </li>
		<li> <?php _e( "Please enter your email if you'd like us to follow up with you.", 'contact-form-7-skins'); ?> [email cf7s-email] </li>
	</ol>
	[submit "<?php _e( 'Submit', 'contact-form-7-skins'); ?>"]
</fieldset>
