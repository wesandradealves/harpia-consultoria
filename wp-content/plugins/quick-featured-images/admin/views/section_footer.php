<?php
/**
 * Represents the footer for the admin page
 *
 * @package   Quick_Featured_Images
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 * @license   GPL-2.0+
 * @link      https://www.kybernetik-services.com
 * @copyright 2013 Kybernetik Services
 */

$ks_logo = '<img src="' . QFI_ROOT_URL . 'admin/assets/images/ks_logo.png" style="width:150px" />';
$tick=QFI_ROOT_URL.'admin/assets/images/tick.svg';
$pro_upgarde_features=array(
    __('Supports custom post type.', 'quick-featured-images'),
    __('Supports WooCommerce products.', 'quick-featured-images'),
    __('Sets the first image as featured image.', 'quick-featured-images'),
    __('Takes the thumbnail of the first embedded content (e.g. YouTube, Vimeo, Instagram) as featured image in new posts.','quick-featured-images'),
    __('Removes the first content image automatically after the featured image was set successfully.', 'quick-featured-images'),
    __('Supports audio and video.', 'quick-featured-images'),
);
$screen = get_current_screen();

// check if file is called in an object context
// else use non-object context
if ( isset($this->plugin_slug ) ) {
    $text_domain = $this->plugin_slug;
} else {
    $text_domain = self::$plugin_slug;
}
// get and set locale code for paypal button
// source: https://developer.paypal.com/docs/classic/archive/buttons/
// source: http://wpcentral.io/internationalization/
$paypal_locale = get_locale();
// if locale is not in registered locale code try to find the nearest match
if ( ! in_array( $paypal_locale, array( 'en_US', 'en_AU', 'es_ES', 'fr_FR', 'de_DE', 'ja_JP', 'it_IT', 'pt_PT', 'pt_BR', 'pl_PL', 'ru_RU', 'sv_SE', 'tr_TR', 'nl_NL', 'zh_CN', 'zh_HK', 'he_IL' ) ) ) {
    if ( 'ja' == $paypal_locale ) { // japanese language
        $paypal_locale = 'ja_JP';
    } else {
        $language_codes = explode( '_', $paypal_locale );
        // test the language
        switch ( $language_codes[ 0 ] ) {
            case 'en':
                $paypal_locale = 'en_US';
                break;
            case 'nl':
                $paypal_locale = 'nl_NL';
                break;
            case 'es':
                $paypal_locale = 'es_ES';
                break;
            case 'de':
                $paypal_locale = 'de_DE';
                break;
            default:
                $paypal_locale = 'en_US';
        } // switch()
    } // if ('ja')
} // if !in_array()
?>
			</div><!-- .qfi_content -->
		</div><!-- #qfi_main -->
		<div id="qfi_footer">
            <div class="qfi_content">
                <h2><?php esc_html_e( 'Credits and information', 'quick-featured-images' ); ?></h2>
                <dl>
                    <dt><?php esc_html_e( 'Do you like the plugin?', 'quick-featured-images' ); ?></dt><dd><a href="http://wordpress.org/support/view/plugin-reviews/quick-featured-images" target="_blank"><?php esc_html_e( 'Please rate it at wordpress.org!', 'quick-featured-images' ); ?></a></dd>
                    <dt><?php esc_html_e( 'The plugin is for free. But the plugin author would be delighted to your small contribution.', 'quick-featured-images' ); ?></dt><dd><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=NSEQX73VHXKS8" target="_blank"><img src="https://www.paypalobjects.com/<?php echo $paypal_locale; ?>/i/btn/btn_donateCC_LG.gif" alt="(<?php esc_html_e( 'Donation Button', $text_domain ); ?>)" id="paypal_button" /><br /><?php esc_html_e( 'Donate with PayPal', 'quick-featured-images' ); ?></a><img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" /></dd>
                    <dt><?php esc_html_e( 'Do you need support or have an idea for the plugin?', 'quick-featured-images' ); ?></dt><dd><a href="http://wordpress.org/support/plugin/quick-featured-images" target="_blank"><?php esc_html_e( 'Post your questions and ideas about Quick Featured Images in the forum at wordpress.org!', 'quick-featured-images' ); ?></a></dd>
                    <dt><?php esc_html_e( 'Let the thumbnails appear in the widget of the most recent posts with this fast plugin', 'quick-featured-images' ); ?></dt><dd><a href="http://wordpress.org/plugins/recent-posts-widget-with-thumbnails/" target="_blank"><?php printf( esc_html__( 'Download plugin %s at %s!', 'quick-featured-images' ), '<strong>Recent Posts Widget With Thumbnails</strong>', 'wordpress.org' ); ?></a></dd>
                    <dt><?php esc_html_e( 'Get the Pro version', 'quick-featured-images' ); ?> <a href="https://www.quickfeaturedimages.com/?utm_source=wordpress_org&utm_medium=plugin&utm_campaign=quick-featured-images&utm_content=go_pro" target="_blank">Quick Featured Images Pro</a></dd>
                </dl>
                <div class="ks_branding" style="float:right; text-align:end;">
                    <div class="ks_branding_label" style="width:100%;padding-top:10px;font-size:11px;font-weight:600;">
                        <?php _e('Developed by', 'quick-featured-images'); ?>
                    </div>
                    <div style="width: 100%; padding-bottom: 10px;"><?php echo $ks_logo; ?></div>
                </div>
            </div><!-- .qfi_content -->
		</div><!-- #qfi_footer -->
	</div><!-- .qfi_wrapper -->
<?php if( strstr( $screen->id, 'comparison' ) || strstr( $screen->id, 'overview' ) ) : ?>
    <div class="upgrade_to_pro_bottom_banner">
        <div class="upgrade_to_pro_bottom_banner_hd">
            <?php _e('Upgrade to Quick Featured Images Pro to get hold of advanced features.', 'quick-featured-images');?>
        </div>
        <a class="upgrade_to_pro_bottom_banner_btn" href="https://www.kybernetik-services.com/shop/wordpress/plugin/quick-featured-images-pro/?utm_source=wordpress_org&utm_medium=pro_vs_free&utm_campaign=section-footer&utm_content=<?php echo QFI_VERSION;?>" target="_blank">
            <?php _e('UPGRADE TO PRO', 'quick-featured-images'); ?>
        </a>
        <div class="upgrade_to_pro_bottom_banner_feature_list_main">
            <?php
            foreach($pro_upgarde_features as $pro_upgarde_feature)
            {
                ?>
                <div class="upgrade_to_pro_bottom_banner_feature_list">
                    <?php echo $pro_upgarde_feature;?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <style type="text/css">
        .upgrade_to_pro_bottom_banner_feature_list{ background:url(<?php echo esc_url($tick); ?>) no-repeat left 5px; }
    </style>
<?php endif; ?>
</div><!-- .wrap -->
