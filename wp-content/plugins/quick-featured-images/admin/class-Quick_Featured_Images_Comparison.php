<?php
/**
 * Quick Featured Images
 *
 * @package   Quick_Featured_Images_Comparison
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins/quick-featured-images/
 * @copyright 2022
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @package Quick_Featured_Images_Settings
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 */
class Quick_Featured_Images_Comparison {

	/**
	 * Instance of this class.
	 *
	 * @since    13.6.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Required user capability to use this plugin
	 *
	 * @since   13.6.0
	 *
	 * @var     string
	 */
	protected $required_user_cap = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    13.6.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Name of this plugin.
	 *
	 * @since    13.6.0
	 *
	 * @var      string
	 */
	protected $plugin_name = null;

	/**
	 * Unique identifier for this plugin.
	 *
	 * It is the same as in class Quick_Featured_Images_Admin
	 * Has to be set here to be used in non-object context, e.g. callback functions
	 *
	 * @since    13.6.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = null;

	/**
	 * Unique identifier for the admin page of this class.
	 *
	 * @since    13.6.0
	 *
	 * @var      string
	 */
	protected $page_slug = null;

	/**
	 * Unique identifier for the admin parent page of this class.
	 *
	 * @since    13.6.0
	 *
	 * @var      string
	 */
	protected $parent_page_slug = null;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since    13.6.0
	 *
	 * @var     string
	 */
	protected $plugin_version = null;

	/**
	 * Slug of the menu page on which to display the form sections
	 *
	 *
	 * @since    13.6.0
	 *
	 * @var      array
	 */
	protected $main_options_page_slug = 'quick-featured-images-optionspage';

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * comparison page and menu.
	 *
	 * @since     13.6.0
	 */
	private function __construct() {

		// Call variables from public plugin class.
		$plugin = Quick_Featured_Images_Admin::get_instance();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->page_slug = $this->plugin_slug . '-comparison';
		$this->parent_page_slug = $plugin->get_page_slug();
		$this->plugin_version = $plugin->get_plugin_version();

        // set capabilities
        if ( isset( $settings[ 'minimum_role_all_pages' ] ) ) {
            switch ( $settings[ 'minimum_role_all_pages' ] ) {
                case 'administrator':
                    $this->required_user_cap = 'manage_options';
                    break;
                default:
                    $this->required_user_cap = 'manage_options';
            }
        } else {
            $this->required_user_cap = 'manage_options';
        }

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

        // Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

	}

	/**
	 * Render the comparison page for this plugin.
	 *
	 * @since    13.6.0
	 */
	public function main() {
		$this->display_header();
		include_once( 'views/section_comparison.php' );
		$this->display_footer();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     13.6.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Return the page headline.
	 *
	 * @since    13.6.0
	 *
	 *@return    string headline variable.
	 */
	public function get_page_headline() {
		return __( 'Free vs. Pro', 'quick-featured-images' );
	}

	/**
	 * Return the page description.
	 *
	 * @since    8.0
	 *
	 *@return    string description variable.
	 */
	public function get_page_description() {
		return __( 'Compare Free versus Pro features', 'quick-featured-images' );
	}

	/**
	 * Return the page slug.
	 *
	 * @since    13.6.0
	 *
	 *@return    string slug variable.
	 */
	public function get_page_slug() {
		return $this->page_slug;
	}

	/**
	 * Return the required user capability.
	 *
	 * @since    13.6.0
	 *
	 *@return    required user capability variable.
	 */
	public function get_required_user_cap() {
		return $this->required_user_cap;
	}

    /**
     * Register and enqueue admin-specific style sheet.
     *
     * @since     13.6.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {

        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }

        // request css only if this plugin was called
        $screen = get_current_screen();
        if ( $this->plugin_screen_hook_suffix == $screen->id ) {
            wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.min.css', __FILE__ ), array( ), $this->plugin_version );
        }

    }

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    13.6.0
	 */
	public function add_plugin_admin_menu() {

		// get translated string of the menu label and page headline
		$label = $this->get_page_headline();
		
		// Add a comparison page for this plugin to the Settings menu.
		$this->plugin_screen_hook_suffix = add_submenu_page( 
			$this->parent_page_slug, // parent_slug
			sprintf( '%s: %s', $this->plugin_name, $label ), // page_title
			$label, // menu_title
			$this->required_user_cap, // capability to use the following function
			$this->page_slug, // menu_slug
			array( $this, 'main' ) // function to execute when loading this page
		);

    }

	/**
	 * Add comparison action link to the plugins page.
	 *
	 * @since    13.6.0
	 */
	public function add_action_links( $links ) {
		$url = sprintf( 'admin.php?page=%s', $this->page_slug );
		return array_merge(
			array(
				'comparison' => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( $url ) ), esc_html( $this->get_page_headline() ) )
			),
			$links
		);

	}

	/**
	 *
	 * Render the header of the admin page
	 *
	 * @access   private
	 * @since    13.6.0
	 */
	private function display_header() {
		include_once( 'views/section_header.php' );
	}
	
	/**
	 *
	 * Render the footer of the admin page
	 *
	 * @access   private
	 * @since    13.6.0
	 */
	private function display_footer() {
		include_once( 'views/section_footer.php' );
	}

}
