<?php
/**
 * Class for handling contact form 7 redirection pro extensions
 */

defined( 'ABSPATH' ) || exit;

class WPCF7R_Extension {
	public function __construct( $extension ) {

		$this->name                = $extension['name'];
		$this->title               = $extension['title'];
		$this->description         = $extension['description'];
		$this->icon                = $extension['icon'];
		$this->type                = isset( $extension['type'] ) ? $extension['type'] : 'extension';
		$this->btn_text            = isset( $extension['btn_text'] ) ? $extension['btn_text'] : '';
		$this->external_url        = isset( $extension['external_url'] ) ? $extension['external_url'] : '';
		$this->badge               = isset( $extension['badge'] ) ? $extension['badge'] : '';
		$this->classname           = '';
		$this->extension_file_name = '';
		$this->sku                 = $this->get_sku();
		$this->slug                = '';

		if ( 'affiliate' !== $this->type ) {
			$this->serial              = $this->get_serial();
			$this->extension_file_name = isset( $extension['filename'] ) ? $extension['filename'] : '';
			$this->slug                = str_replace( '.php', '', $this->extension_file_name );
			$this->extension_ver       = $this->get_ver();
			$this->classname           = $extension['classname'];

			if ( ! $this->extension_file_exists() ) {
				$this->reset_activation();

				$this->deactivate_license();
			}
		}
	}

	/**
	 * Get affiliate url (if the extension is affiliated)
	 */
	public function get_aff_url() {
		return isset( $this->external_url ) ? $this->external_url : '';
	}

	public function get_extension_file_name() {
		return $this->extension_file_name;
	}
	/**
	 * Get the extension slug
	 */
	public function get_slug() {
		return $this->slug;
	}
	/**
	 * Get the btn label
	 */
	public function get_btn_text() {
		return $this->btn_text;
	}

	/**
	 * Get the extension version
	 */
	public function get_ver() {

		$ver = '2.0';

		if ( $this->extension_file_exists() ) {
			$info_headers = array(
				'ver' => 'ver',
			);

			$info = get_file_data( $this->get_extension_file_path(), $info_headers );

			$ver = $info['ver'];
		}

		return $ver;
	}

	/**
	 * Get the extension name
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the extension type
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the installed extension version
	 */
	public function get_extension_ver() {
		return isset( $this->extension_ver ) ? $this->extension_ver : '';
	}

	/**
	 * Get the path of the extension
	 */
	public function get_extension_file_path() {
		$path = '';

		if ( class_exists( $this->get_class_name() ) && class_exists( 'ReflectionClass' ) ) {
			$reflector = new ReflectionClass( $this->get_class_name() );
			$path      = $reflector->getFileName();
		} elseif ( $this->extension_file_name ) {
			$path = wpcf7r_get_addon_path( $this->extension_file_name );
		}

		return $path;
	}

	/**
	 * Get the path of the extension for plugin activation
	 */
	public function get_extension_relative_path() {

		$relative_path = $this->get_name() . '/' . $this->extension_file_name;

		return $relative_path;
	}

	/**
	 * Get the extension file name
	 *
	 * @return void
	 */
	public function get_class_name() {
		return $this->classname;
	}

	/**
	 * Check if the file required for the extension exists
	 */
	public function extension_file_exists() {
		return file_exists( $this->get_extension_file_path() );
	}

	/**
	 * Set the required update flag
	 */
	public function set_updated() {
		delete_option( 'wpcf7r_extension-needs-update-' . $this->get_name() );
	}

	/**
	 * Check if an extension requires an update
	 *
	 * @return boolean
	 */
	public function has_update() {
		return get_option( 'wpcf7r_extension-needs-update-' . $this->get_name() ) && $this->is_active();
	}

	/**
	 * Get the version of the update
	 */
	public function update_version() {
		return get_option( 'wpcf7r_extension-needs-update-' . $this->get_name() );
	}

	/**
	 * Set the required update flag
	 */
	public function set_needs_update( $new_ver ) {
		update_option( 'wpcf7r_extension-needs-update-' . $this->get_name(), $new_ver );
	}

	/**
	 * Get the HTML box to display the promo box
	 */
	public function get_action_promo() {
		include( WPCF7_PRO_REDIRECT_TEMPLATE_PATH . 'extensions/extension-promo-box.php' );
	}

	/**
	 * If the plugin file exists but license was not activated
	 *
	 * @return void
	 */
	public function needs_activation() {
		return ! $this->is_active() && $this->extension_file_exists();
	}
	/**
	 * Check if the license is active
	 */
	public function is_active() {
		return $this->get_activation_data() && $this->extension_file_exists();
	}

	/**
	 * Get the saved serial number
	 */
	public function get_serial() {
		return get_option( 'wpcf7r_activation_serial-' . $this->get_name() );
	}

	/**
	 * Get the saved license activation data
	 */
	public function get_activation_data() {
		return get_option( 'wpcf7r_activation_data-' . $this->get_name() );
	}

	/**
	 * Activate the serial via API
	 *
	 * Deprecated on ver 2.9.0
	 * @param $serial
	 */
	public function activate( $serial ) {
		return null;
	}

	public function get_destination_path() {
		return WPCF7_PRO_REDIRECT_PLUGINS_PATH . $this->get_name();
	}

	/**
	 * Get the extension download link
	 * 
	 * Deprecated on version 2.9.0
	 */
	public function get_extension_remote_file() {
		return null;
	}
	/**
	 * Save the Newly downloaded file to the plugins dir
	 * 
	 * Deprecated on version 2.9.0
	 */
	public function save_extension_file() {
		return null;
	}

	/**
	 * Activate new downloaded extension
	 *
	 * Deprecated on version 2.9.0
	 * @return void
	 */
	public function activate_new_plugin() {
		return null;
	}
	/**
	 * Get extension badge text
	 *
	 * @return void
	 */
	public function get_badge() {
		return $this->badge ? "<span class='badge'>" . $this->badge . '</span>' : '';
	}

	/**
	 * Set all data related with the plugin activation
	 *
	 * @param $validation_data
	 * @param $serial
	 */
	public function set_activation( $validation_data, $serial ) {
		update_option( 'wpcf7r_activation_id-' . $this->get_name(), $validation_data->activation_id );
		update_option( 'wpcf7r_activation_expiration-' . $this->get_name(), $validation_data->expire );
		update_option( 'wpcf7r_activation_data-' . $this->get_name(), $validation_data );
		update_option( 'wpcf7r_activation_serial-' . $this->get_name(), $serial );
		update_option( 'wpcf7r_activation_' . $this->get_name() . '-sku', $this->sku );
	}

	/**
	 * Clear all activation data
	 */
	public function reset_activation() {
		delete_option( 'wpcf7r_activation_id-' . $this->get_name() );
		delete_option( 'wpcf7r_activation_expiration-' . $this->get_name() );
		delete_option( 'wpcf7r_activation_data-' . $this->get_name() );
		delete_option( 'wpcf7r_activation_serial-' . $this->get_name() );
		update_option( 'wpcf7r_activation_' . $this->get_name() . '-sku', $this->sku );
	}

	public function get_sku() {
		return isset( $this->sku ) && $this->sku ? $this->sku : get_option( 'wpcf7r_activation_' . $this->get_name() . '-sku' );
	}
	/**
	 * Get the activation key
	 */
	public function get_activation_id() {
		return get_option( 'wpcf7r_activation_id-' . $this->get_name() );
	}

	/**
	 * Deactivate extension license
	 */
	public function deactivate_license( $serial = '' ) {

		$api = new Qs_Api();

		$response = array();

		$serial = $serial ? $serial : $this->get_serial();

		$activation_id = $this->get_activation_id();

		if ( ! $activation_id ) {
			$this->reset_activation();
		} else {
			$results = $api->deactivate_liscense( $activation_id, $serial, $this->get_name() );

			$this->reset_activation();
		}

		$response['extension_html'] = $this->ajax_extension_html();

		return $response;
	}

	/**
	 * Ajax function for getting the extension html box
	 */
	public function ajax_extension_html() {
		ob_start();

		$this->get_action_promo();

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Get the link to purchase the extension
	 */
	public function get_purchase_link() {
		return WPCF7_PRO_REDIRECT_PLUGIN_PAGE_URL . $this->name;
	}

	/**
	 * Get the extension description
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the extension icon
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 * Get the extension name
	 */
	public function get_title() {
		return $this->title;
	}
}
