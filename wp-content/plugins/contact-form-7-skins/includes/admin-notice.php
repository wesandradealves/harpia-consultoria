<?php
/**
 * CF7 Skins Admin Notice Class.
 * 
 * Implement all functionality for CF7 Skins Admin notice on all admin pages
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 2.0.0
 */

class CF7_Skins_Admin_Notice {
		
	private $skip_interval;
	
	/**
     * Class constructor
	 * 
     * @since 2.0.0
     */	
    function __construct() {
		$this->skip_interval = WEEK_IN_SECONDS * 2; // skip for 2 weeks
		
		add_action( 'upgrader_process_complete', array( &$this, 'add_update_notice' ), 1, 2 );
		add_action( 'admin_notices', array( &$this, 'dom_extension_notice' ) );
		add_action( 'admin_notices', array( &$this, 'update_notice' ) );
		add_action( 'wp_ajax_cf7skins_dismiss_update_notice', array( &$this, 'dismiss_update_notice' ) );
		add_action( 'admin_head', array( &$this, 'update_notice_css' ), 9 );
		add_action( 'admin_footer', array( &$this, 'dismiss_update_notice_js' ), 9 );
	}
	
	/**
	 * Upgrade plugin version number after upgrading
	 * 
	 * See https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-wp-upgrader.php#L615
	 * @param $object	WP upgrader class object
	 * @param (array) $args Example:
	 *		Single update: array (
	 *			[plugin] => contact-form-7-skins/index.php
	 *			[type] => plugin
	 *			[action] => update
	 *		)
	 *		Bulk update: array(
	 *			[action] => update
	 *			[type] => plugin
	 *			[bulk] => 1
	 *			[plugins] => array(
	 *					[0] => akismet/akismet.php
	 *					[1] => contact-form-7-skins/index.php
	 *				)
	 *		)
	 * @since 2.0.0
	 */	
	function add_update_notice( $object, $args ) {
		
		// Bail early if this is not plugin update process
		if ( 'update' !== $args['action'] || 'plugin' !== $args['type'] ) {
			return;
		}
		
		// Do version update if this is a single update and the plugin file is $args['plugin']
		if ( isset( $args['plugin'] ) && plugin_basename( __FILE__ ) === $args['plugin'] ) {
			add_option( 'cf7skins_update_notice', 1 );
		}
		
		// Do version update if this is a bulk update and the plugin file is in the $args['plugins']
		if ( isset( $args['plugins'] ) && in_array( plugin_basename( __FILE__ ), $args['plugins'] ) ) {
			add_option( 'cf7skins_update_notice', 1 );
		}
	}
	
	/**
	 * Display admin notice after update or skipped interval reached
	 * 
	 * @since 2.0.0
	 */
	function update_notice() {
		$notice_option = get_option( 'cf7skins_update_notice' );
		
		// If this is the first update notice, or skipped notice
		if ( 1 === $notice_option || current_time( 'timestamp' ) > $notice_option ) {
		?>
		<div id="cf7skins-update-notice" class="notice notice-success is-dismissible">
			<img class="logo" src="<?php echo CF7SKINS_URL . 'images/cf7skins-icon-128x128.png'; ?>" alt="" />
			<h3><?php _e( 'Introducing our new drag & drop Visual Editor for Contact Form 7 forms.', 'contact-form-7-skins' ); ?></h3>
			<p>
				<?php _e( 'Fast, easy form creation on the new CF7 Skins Form tab. Try it out on a new form.', 'contact-form-7-skins' ); ?>
				<br />
				<a href="http://kb.cf7skins.com/cf7-skins-visual-editor-tour/?utm_source=plugin&utm_medium=link&utm_campaign=update-notice"><?php _e( 'Take a tour', 'contact-form-7-skins' ); ?></a> –  
				<a href="http://kb.cf7skins.com/edit-cf7-skins-form-visual-editor/?utm_source=plugin&utm_medium=link&utm_campaign=update-notice"><?php _e( 'Visit our documentation', 'contact-form-7-skins' ); ?></a>
			</p>
		 </div>
		<?php
		}
	}

	/**
	 * Check DOM extension if exists and create admin notification to every admin page.
	 * 
	 * @since 2.5.3
	 */
	function dom_extension_notice() {
		if ( ! class_exists( 'DOMDocument' ) ) {
			?>
			<div class="notice notice-error">			
				<h3><?php _e( 'Contact Form 7 Skins', 'contact-form-7-skins' ); ?></h3>
				<p>
					<?php _e( 'Your server does not have PHP DOM or disabled it.', 'contact-form-7-skins' ); ?>
					<br />
					<?php _e( 'Please ask your server provider to install or enable it to be able to use this plugin.', 'contact-form-7-skins' ); ?>
					<br />
					<a href="https://www.php.net/manual/en/dom.setup.php" target="_blank"><?php _e( 'Read this documentation for installing DOM extension.', 'contact-form-7-skins' ); ?></a>					
				</p>
			 </div>
			<?php
		}
	}

	/**
	 * Update notice via AJAX after clicking dismiss button
	 * 
	 * @since 2.0.0
	 */	
	function dismiss_update_notice() {
		// Check the nonce and if not isset the id, just die.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'cf7skins_update_notice' ) ) {
			die();
		}

		// Set skip interval based on current time
		update_option( 'cf7skins_update_notice', current_time( 'timestamp' ) + $this->skip_interval );

		exit();
	}
	
	/**
	 * Custom CSS for update admin notice
	 *
	 * @since 2.0.0
	 */	
	function update_notice_css() {
		?>
<style type="text/css">
#cf7skins-update-notice {
    padding-left: 110px;
    position: relative;
    min-height: 84px;
}
#cf7skins-update-notice .logo {
	position: absolute;
	left: 12px;
	top: 12px;
	width: 80px;
	height: 80px;
}
#cf7skins-update-notice p {
	padding: 0;
	margin: 1em 0;
}
#cf7skins-update-notice a {
	text-decoration: none;
}
</style>
		<?php
	}
	
	/**
	 * Dismiss update notice script
	 * 
	 * @since 2.0.0
	 */	
	function dismiss_update_notice_js() {
		if ( wp_script_is( 'jquery', 'done' ) ) {
		?>
<script type="text/javascript">
(function ($) {
    $(function () { // shorthand for ready event
		$(document).on( 'click', '#cf7skins-update-notice button.notice-dismiss', function(e) {
            e.preventDefault();
            var data = {
                'action': 'cf7skins_dismiss_update_notice',
                'nonce': '<?php echo wp_create_nonce( 'cf7skins_update_notice' ); ?>'
            };
            $.post( ajaxurl, data ); // update notice
        });
    })
}(jQuery));
</script>
		<?php
		}
	}
}	

new CF7_Skins_Admin_Notice();
