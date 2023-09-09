<h3><?php echo esc_html( $this->valid_filters[ 'filter_post_types' ] ); ?></h3>
<p><?php esc_html_e( 'Select post types', 'quick-featured-images' ); ?></p>
<p>
<?php
foreach ( $this->valid_post_types as $key => $label ) {
?>
	<input type="checkbox" id="<?php printf( 'qfi_%s', $key ); ?>" name="post_types[]" value="<?php echo $key; ?>"  <?php checked( in_array( $key, $this->selected_post_types ) ); ?> />
	<label for="<?php printf( 'qfi_%s', $key ); ?>"><?php echo esc_html( $label ); ?></label><br />
<?php
}
?>
</p>
<p class="qfi_ad_for_pro"><?php esc_html_e( 'Do you miss post types? Custom post types are supported with', 'quick-featured-images' ); ?> <a href="https://www.quickfeaturedimages.com/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=quick-featured-images&utm_content=go_pro" target="_blank">Quick Featured Images Pro</a>.</p>