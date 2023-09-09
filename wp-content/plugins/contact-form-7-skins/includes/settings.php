<?php
/**
 * CF7 Skins Settings Class.
 * 
 * Implement all functionality on CF7 Skins Settings page.
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */
 
class CF7_Skins_Settings {
	
	// Holds the values to be used in the fields callbacks
	private $options;
	
	// Define class variables
	var $tabs, $section, $fields, $slug;
	
	/**
	 * Class constructor
	 * 
	 * @filter cf7skins_setting_tabs
	 * @filter cf7skins_color_scheme
	 * @filter cf7skins_section_tab
	 * do_action cf7skins_settings_enqueue_script
	 * 
	 * @since 0.1.0
	 */
    function __construct() {
		$this->slug = CF7SKINS_OPTIONS;
		
		$this->section = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'advanced';
		$this->options = get_option( $this->slug );

		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'cf7skins_section_getting-started', array( $this, 'getting_started_section' ) );
		add_action( 'cf7skins_section_add-ons', array( $this, 'add_ons_section' ) );
	}
	
	/**
	 * Add CF7 Skins Settings page as submenu under Contact Form 7 plugin menu item.
	 * 
	 * @filter 'cf7skins_setting_tabs'
	 * 
	 * @see add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	 *  
	 * @since 0.1.0
	 */
	function add_menu_page() {
		
		// Apply filter to allow other functions to add tabs
		$this->tabs = apply_filters( 'cf7skins_setting_tabs', array( 
			/* 'general'	=> __( 'General', 'contact-form-7-skins' ), */
			'advanced'	=> __( 'Options', 'contact-form-7-skins' ),
			'add-ons'	=> __( 'Add-ons', 'contact-form-7-skins' ),
			'getting-started'	=> __( 'Getting Started', 'contact-form-7-skins' )
		) );
		
		// Add the submenu page under the CF7 page
		$page = add_submenu_page( 'wpcf7',
			__( 'CF7 Skins Settings', 'contact-form-7-skins' ),
				__( 'CF7 Skins Settings', 'contact-form-7-skins' ), 'manage_options', 'cf7skins', array( $this, 'create_settings' ) );
		
		// Display our admin scripts
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'enqueue_script' ) );
	}
	
	/**
	 * Display the admin setting page script.
	 * 
	 * @do_action 'cf7skins_settings_enqueue_script'
	 * 
	 * @since 0.1.0
	 */
	function enqueue_script() {
		wp_enqueue_style( $this->slug, CF7SKINS_URL . 'css/admin.css', array(), CF7SKINS_VERSION );
		wp_enqueue_script( $this->slug, CF7SKINS_URL . 'js/jquery.settings.js', array( 'jquery', 'jquery-ui-sortable' ), CF7SKINS_VERSION );
		
		/**
		 * Allow other functions to enqueue scripts after the settings.js file
		 * @since 1.1.1
		 */
		do_action( 'cf7skins_settings_enqueue_script' );
	}
	
	/**
	 * Display CF7 Skins Settings page in Tabs.
	 * 
	 * Output nonce, action, and option_page fields for a settings page settings_fields( $option_group )
	 * @see settings_fields ( string $option_group = null )
	 * Print out the settings fields for a particular settings section
	 * @see do_settings_fields ( string $page = null, section $section = null )
	 * 
	 * @do_action 'cf7skins_section_$section'
	 * 
	 * @previous settings_fields()
	 * 
	 * @since 0.1.0
	 */
	function create_settings() {
		?>
		<div id="cf7skins-settings" class="wrap">
			<?php //echo '<pre style="font-size:10px;line-height:10px;">'. print_r( $this->options, true ) .'</pre>'; ?>
			<h2><?php _e( 'CF7 Skins Settings', 'contact-form-7-skins' ); ?></h2><br />
			<h2 class="nav-tab-wrapper">
				<?php
				foreach( $this->tabs as $tab => $name ) {
					$class = ( $tab == $this->section ) ? ' nav-tab-active' : '';
					printf( '<a class="nav-tab%1$s tab-%2$s" href="?page=%3$s&tab=%2$s">%4$s</a>',					
						esc_attr( $class ), esc_attr( $tab ), esc_attr( $this->slug ), esc_attr( $name ) );
				}
				?>
			</h2>
			<form method="post" action="options.php">
			<?php
				settings_fields( $this->slug );

				// Store current active section
				echo "<input name='". esc_attr( $this->slug ) . "[section]'  value='" . esc_attr( $this->section ) . "' type='hidden' />";

				echo '<table class="form-table">';
				do_settings_fields( $this->slug, $this->section );
				echo '</table>';

				/**
				 * Allow other functions to create options for the setting section
				 * @since 0.1.0
				 */
				do_action( "cf7skins_section_{$this->section}" ); 

				submit_button( __( 'Save Changes', 'contact-form-7-skins' ) );
			?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Register and add settings.
	 * 
	 * @see register_setting( $option_group, $option_name, $sanitize_callback );
	 * @see add_settings_section( $id, $title, $callback, $page );
	 * @see add_settings_field( $id, $title, $callback, $page, $section, $args );
	 * 
	 * @filter 'cf7skins_setting_fields'
	 * 
	 * @since 0.1.0
	 */
	function page_init() {
		if( ! isset( $this->tabs ) )
			return;
		
		register_setting( $this->slug, $this->slug, array( $this, 'sanitize_callback' ) );
		
		// Add section for each tab on Settings page
		foreach( $this->tabs as $tab => $name ) {
			add_settings_section( $tab, '',  '', $this->slug );
		}
		
		/*
		// Get styles list for the custom enqueue styles
		$styles = array();
		$get_styles = CF7_Skin_Style::cf7s_get_style_list();
		foreach( $get_styles as $k => $v )
			$styles[$k] = $v['details']['Style Name'];
		*/

		/**
		 * Add Initial Fields
		 * Licenses are added via apply_filters () in license.php
		 * @filter 'cf7skins_setting_fields'
		 * @since 0.2.0. 
		 */
		$fields = apply_filters( 'cf7skins_setting_fields', array( 
			'add_asterisk' => array( // @since 2.1.3
				'section' => 'advanced',
				'label' => __( 'Asterisk', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => true,
				'detail' => __( 'Add asterisk to required fields.', 'contact-form-7-skins' ),
			),
			'color_scheme' => array(
				'section' => 'advanced',
				'label' => __( 'Admin Color Scheme', 'contact-form-7-skins' ),
				'type' => 'color-scheme',
				'default' => 'default',
				'description' => __( 'Select color scheme for CF7 Skins admin interface. (WP Admin only not form colors) ', 'contact-form-7-skins' ),
			),
			/*'custom' => array( 
				'section' => 'advanced',
				'label' => __( 'Custom Styles & Scripts', 'contact-form-7-skins' ),
				'type' => 'textarea',
				'description' => __( 'Print your custom scripts or styles with the tag to push to the wp_head().', 'contact-form-7-skins' ),
			),
			'enqueue_styles' => array( 
				'section' => 'advanced',
				'label' => __( 'Enqueue Styles', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => array(),
				'detail' => $styles,
				'description' => __( 'Enqueue selected styles for whole site pages header.', 'contact-form-7-skins' ),
			),
			
			'cf7_stylesheet_dependency' => array(
				'section' => 'advanced',
				'label' => __( 'Stylesheet Dependency', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => false,
				'detail' => __( 'Remove CF7 default CSS stylesheet dependency.', 'contact-form-7-skins' ),
			),			
			'display_log' => array( 
				'section' => 'advanced',
				'label' => __( 'Display Log', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => false,
				'detail' => __( 'Displays plugin log tab.', 'contact-form-7-skins' ),
			),
			*/
			'export' => array( /* @since 2.1.3 */
				'section' => 'advanced',
				'label' => __( 'Export Form', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => false,
				'detail' => __( 'Enable export of individual CF7 Skins form.', 'contact-form-7-skins' ),
			),
			'delete_data' => array( 
				'section' => 'advanced',
				'label' => __( 'Delete Settings', 'contact-form-7-skins' ),
				'type' => 'checkbox',
				'default' => false,
				'detail' => __( 'Remove all plugin data on plugin deletion.', 'contact-form-7-skins' ),
			),
		) );
		
		$this->fields = $fields; // @since 0.5.0 set class object
		
		// add_settings_field( 'color_scheme', __('Color Scheme', 'contact-form-7-skins'), array( $this, 'setting_field' ), $this->slug, 'general', array( 'label_for' => 'color_scheme', 'type' => 'color-scheme', 'default' => true, 'detail' => __('Color Scheme', 'contact-form-7-skins') ) );
		
		// Set function setting_field () as callback for each field
		foreach( $fields as $key => $field ) {
			$field['label_for'] = $key;
			add_settings_field( $key, $field['label'], array( $this, 'setting_field' ), $this->slug, $field['section'], $field );		
		}
		
		// Create initialize settings if this is the first install
		if( ! get_option( $this->slug ) ) {
			global $wp_settings_fields;
			$sections = $wp_settings_fields[$this->slug];
			$array = array();
			foreach( $sections as $fields ) {
				foreach( $fields as $k => $field ) {
					$array[$k] = isset( $field['args']['default'] ) ? $field['args']['default'] : '';
				}
			}
			update_option( $this->slug, $array );
		}
	}
	
	/**
	 * Sanitize each setting field as needed.
	 * 
	 * @filter 'cf7skins_setting_sanitize'
	 * 
	 * @param array $input Contains all settings fields as array keys
	 * 
	 * @since 0.1.0
	 */
	function sanitize_callback( $inputs ) {
		// return if inputs are empty
		if( ! isset( $inputs['section'] ) )
			return $inputs;
		
		global $wp_settings_fields;
		$section = $wp_settings_fields[$this->slug][$inputs['section']];
		$old_option = $this->options;
		
		foreach( $inputs as $k => $input ) {
			if ( isset( $section[$k] ) ) { // make sure key input is existed
				$type = $section[$k]['args']['type'];
				
				if( 'text' == $type ) {
					$this->options[$k] = sanitize_text_field( $input );
				} elseif( 'number' == $type ) {
					$this->options[$k] = absint( $input );
				} elseif( 'url' == $type ) {
					$this->options[$k] = esc_url( $input );
				} else {
					$this->options[$k] = $input;
				}
			}
		}
		
		// Special case for checkbox, we need to loop through setting fields
		foreach( $section as $k => $field )
			if( 'checkbox' == $field['args']['type'] )
				if( ! isset( $inputs[$k] ) )
					$this->options[$k] = false;
		
		/**
		 * Sanitized Licenses are added via apply_filters () in license.php 
		 * $this->options is the new and $inputs is old
		 * @since 0.2.0
		 */
		return apply_filters( 'cf7skins_setting_sanitize', $this->options, $old_option, $inputs );
	}
	
	/**
	 * Display the option field in the section.
	 * 
	 * Public function, can be used in other files
	 * 
	 * @param $args ADD EXPLANATION
	 * 
	 * @since 0.1.0
	 */
	public function setting_field( $args ) {
		// echo '<pre style="font-size:10px;line-height:10px;">'. print_r( $this->options, true ) .'</pre>';
		// echo '<pre style="font-size:10px;line-height:10px;">'. print_r( $args, true ) .'</pre>';
		
		extract( $args );
		
		$id = isset( $label_for ) ? $label_for : ''; // Use label_for arg as id if set
		
		switch ( $type ) {
			case 'textarea':
				printf( '<textarea id="%1$s" name="'. $this->slug .'[%1$s]" cols="50" rows="5" class="large-text">%2$s</textarea>',
					esc_attr( $id ), isset( $this->options[ $id ] ) ? esc_attr( $this->options[ $id ] ) : '' );
				break;
				
			case 'checkbox':
				if ( is_array( $detail ) ) {
					$value = isset( $this->options[$id] ) ? $this->options[$id] : array();
					foreach( $detail as $k => $v )
						printf( '<label><input id="%1$s" name="'.$this->slug.'[%1$s][%2$s]" type="checkbox" value="1" %3$s />%4$s</label><br />',
							esc_attr( $id ), esc_attr( $k ), isset( $value[$k] ) ? 'checked="checked"' : '', esc_attr( $v ) );

				} else {
					$value = isset( $this->options[$id] ) ? $this->options[$id] : $this->fields[$id]['default'];
					printf( '<label><input id="%1$s" name="'.$this->slug.'[%1$s]" type="checkbox" value="1" %2$s />%3$s</label>',
						esc_attr( $id ), esc_attr( $value ) ? 'checked="checked"' : '', esc_attr( $detail ) );
					}
				break;
				
			case 'color-scheme':
				foreach ( $this->color_scheme() as $color => $color_info ) {
					$selected = $this->options[$id] == $color ? ' selected' : '';
					echo'
					<div class="color-option'. esc_attr( $selected ) .'">
						<input type="radio" '. checked( $this->options[$id], $color, false ) . ' class="tog" value="'. esc_attr( $color ) .'" name="'. $this->slug .'[color_scheme]" />
						<input type="hidden" value="'. esc_url( $color_info->url ) .'" class="css_url" />
						<label for="admin_color_fresh">'. esc_attr( $color_info->name ) . '</label>
						<table class="color-palette">
							<tbody>
								<tr>';
									foreach( $color_info->colors as $bgcolor )									
										echo '<td style="background-color: '. esc_attr( $bgcolor ) .'">&nbsp;</td>';
								
								echo'
								</tr>
							</tbody>
						</table>
					</div>';
				}
				break;
				
			case 'license':				
				$disable = ''; // disabled HTML attribute if status valid and license key exists				

				if ( isset( $this->options[$id] ) && ! empty( $this->options[ $id ] ) ) { // license key exists
					if ( $status !== false && $status == 'valid' ) { // license key valid
						$disable = ' disabled';
					}
				}

				if ( isset( $this->options[$id] ) && ! empty( $this->options[ $id ] ) ) { // license key exists
					printf( '<input id="%1$s" name="'.$this->slug.'[%1$s]" value="%2$s" class="regular-text license-key" type="text"'. $disable .'/>',
						esc_attr( $id ), isset( $this->options[$id] ) ? esc_attr( $this->options[$id] ) : '', esc_attr( $type ) );

					if ( $status !== false && $status == 'valid' ) {
						$deactivate_name = $this->slug . '[' . $id . '_deactivate]';
						echo '<span style="color:green;padding:0 10px;font-size:12px">'. __( 'active', 'contact-form-7-skins' ) .'</span>';
						echo '<input type="submit" class="button" name="'. esc_attr( $deactivate_name ) . '" value="'. __('Deactivate License','contact-form-7-skins') .'"/>';
					} else {
						if ( $status == 'invalid' ) {
							echo '<span style="color:red;padding:0 10px;font-size:12px">'. __( 'invalid', 'contact-form-7-skins' ) .'</span>';
						}
						
						$activate_name = $this->slug . '[' . $id . '_activate]';
						echo '&nbsp;<input type="submit" class="button" name="' . esc_attr( $activate_name ) . '" value="'. __('Activate License','contact-form-7-skins') .'"/>';
					}

				} else { // license key is not exist or broken
					printf( '<input id="%1$s" name="'.$this->slug.'[%1$s]" value="%2$s" class="regular-text license-key" type="text"'. $disable .'/>',
						esc_attr( $id ), isset( $this->options[$id] ) ? esc_attr( $this->options[$id] ) : '', esc_attr( $type ) );

					$activate_name = $this->slug . '[' . $id . '_activate]';
					echo '&nbsp;<input type="submit" class="button" name="' . esc_attr( $activate_name ) . '" value="'. __('Activate License','contact-form-7-skins') .'"/>';
				}
				
				break;
				
			case 'info':
				do_action( 'cf7skins_setting_info', $args );
				break;
				
			case 'url':			
				printf( '<input id="%1$s" name="'.$this->slug.'[%1$s]" value="%2$s" class="regular-text" type="%3$s" />',
					$id, isset( $this->options[$id] ) ? esc_url( $this->options[$id] ) : '', esc_attr( $type ) );
				break;
			
			case 'text':
			case 'number':
			default:				
				printf( '<input id="%1$s" name="'.$this->slug.'[%1$s]" value="%2$s" class="regular-text" type="%3$s" />',
					$id, isset( $this->options[$id] ) ? esc_attr( $this->options[$id] ) : '', esc_attr( $type ) );
				break;
		}
		
		if ( isset( $description ) ){
			switch ( $type ) {
				case 'license':  // Don't display activation instructions for valid license
					if ( isset( $this->options[$id] ) && ! empty( $this->options[ $id ] ) ) { // license key exists
						if ( $status !== false && $status == 'valid' ) { // license key valid
							break;
						}
					}

				default:
					echo '<p class="description">'. esc_html( $description ) .'</p>';
					break;
			}
		}
	}
	
	
	/**
	 * Custom option for the color scheme.
	 * 
	 * @filter 'cf7skins_color_scheme'
	 * 
	 * @since 0.1.0
	 */	
	function color_scheme() {
		$colors = array();
		
		// Default color scheme
		$color = new stdClass();
		$color->name   = __('Default', 'contact-form-7-skins');
		$color->url    = CF7SKINS_URL . 'css/admin.css';
		$color->colors = array( '#94B2CE', '#C4D9EE', '#70A74A', '#C9F4B0' );
		$colors['default'] = $color;
		
		// Wheat color scheme
		$color = new stdClass();
		$color->name   = __('Wheat', 'contact-form-7-skins');
		$color->url    = CF7SKINS_URL . 'css/admin.css';
		$color->colors = array( '#EEEEEE', '#E5E5E5', '#E5EAA8', '#DAE193' );
		$colors['wheat'] = $color;
		
		// Ocean color scheme
		$color = new stdClass();
		$color->name   = __('Ocean', 'contact-form-7-skins');
		$color->url    = CF7SKINS_URL . 'css/admin.css';
		$color->colors = array( '#ECF7FB', '#CDE8F1', '#D6F9C1', '#C2F0A5' );
		$colors['ocean'] = $color;		
		
		/**
		 * Allow other functions to add or modify the color scheme
		 * @param $colors (object) see above for example
		 * @since 0.1.0
		 */
		return apply_filters( 'cf7skins_color_scheme', $colors );
	}
	
	/**
	 * Getting started tab content section.
	 * 
	 * @since 1.1.2
	 */
	function getting_started_section() { ?>
		<div class="info-wrapper"><?php
			require_once( CF7SKINS_PATH . 'includes/getting-started.php' ); ?>
		</div><?php
	}
	
	/**
	 * Add-ons tab content section
	 * 
	 * @since 1.1.2
	 */
	function add_ons_section() { ?>
		<div class="info-wrapper"><?php
			require_once( CF7SKINS_PATH . 'includes/pro-version.php' ); ?>
		</div><?php
	}
	
} new CF7_Skins_Settings();
