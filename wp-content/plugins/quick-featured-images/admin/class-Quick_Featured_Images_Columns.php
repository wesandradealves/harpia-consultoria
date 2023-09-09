<?php
/**
 * Quick Featured Images
 *
 * @package   Quick_Featured_Images_Columns
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins/quick-featured-images/
 * @copyright 2014 
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @package Quick_Featured_Images_Columns
 * @author    Kybernetik Services <wordpress@kybernetik.com.de>
 */
class Quick_Featured_Images_Columns {

	/**
	 * Instance of this class.
	 *
	 * @since    7.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Name of this plugin.
	 *
	 * @since    7.0
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
	 * @since    7.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = null;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since    7.0
	 *
	 * @var     string
	 */
	protected $plugin_version = null;

	/**
	 * Unique identifier in the WP options table
	 *
	 *
	 * @since    7.0
	 *
	 * @var      string
	 */
	protected $settings_db_slug = null;

	/**
	 * Stored settings in an array
	 *
	 *
	 * @since    7.0
	 *
	 * @var      array
	 */
	protected $stored_settings = array();

	/**
	 * Name of the additional thumbnail column.
	 *
	 * @since    7.0
	 *
	 * @var      string
	 */
	protected $thumbnail_column_name = 'qfi-thumbnail';

	/**
	 * Name of the additional post list column.
	 *
	 * @since    13.4.0
	 *
	 * @var      string
	 */
	protected $post_list_column_name = 'qfi-post-list';

	/**
	 * Required user capability to use this plugin
	 *
	 * @since   12.0
	 *
	 * @var     string
	 */
	protected $required_user_cap = null;

	/**
	 * Required user capability to use this plugin
	 *
	 * @since   13.3.5
	 *
	 * @var     boolean
	 */
	protected $is_capable_user = null;

	/**
	 * Width of thumbnail images in the current WordPress settings
	 *
	 * @since    2.0
	 *
	 * @var      integer
	 */
	protected $used_thumbnail_width = null;
	
	/**
	 * Storage for computed thumbnail HTML codes to improve performance
	 *
	 * @since     13.3.5
	 *
	 * @var      array
	 */
	protected $thumbnail_cache = null;

	/**
	 * Storage for computed translations to improve performance
	 *
	 * @since     13.3.5
	 *
	 * @var      array
	 */
	protected $translation_cache = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     7.0
	 *
	 */
	private function __construct() {

		// Call variables from public plugin class.
		$plugin = Quick_Featured_Images_Admin::get_instance();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_version = $plugin->get_plugin_version();
		$this->settings_db_slug = $plugin->get_settings_db_slug();

		// add featured image columns if desired
		$add_column_function = array( $this, 'add_thumbnail_column' );
		$display_column_function = array( $this, 'display_thumbnail_in_column' );
		$add_sort_function = array( $this, 'add_sortable_column' );
		// set default width of thumbnails
		$this->used_thumbnail_width = 80; // or: $width  = absint( get_option( 'thumbnail_size_w', $default_value ) / 2 ); $height = absint( get_option( 'thumbnail_size_h', $default_value ) / 2 );
		// get current or default settings
		$this->stored_settings = get_option( $this->settings_db_slug, array() );
		// add Featured Image column in desired posts lists
		foreach ( $this->stored_settings as $key => $value ) {
			if ( '1' == $value ) {
				if ( 'column_post_list' == $key ) {
					// print post list column in media library
					add_action( 'manage_media_columns', array( $this, 'add_post_list_column' ) );
					// show content of post list column
					add_action( 'manage_media_custom_column', array( $this, 'display_post_list_column' ), 10, 2 );
					// print style for post list column
					add_action( 'admin_head', array( $this, 'display_post_list_column_style' ) );
				} elseif ( preg_match('/^column_thumb_([a-z0-9_\-]+)$/', $key, $matches ) ) {
					// make the following lines more readable
					$post_type = $matches[ 1 ];
					
					// get the hook name for the columns filter
					$hook = sprintf( 'manage_%s_posts_columns', $post_type );
					// add a column to list of desired post type and
					// sanitizing: check with has_filter() to prevent multiple columns in a row
					if ( ! has_filter( $hook, $add_column_function ) ) {
						add_filter( $hook, $add_column_function );
					}
					
					// get the hook name for the sortable columns filter
					$hook = sprintf( 'manage_edit-%s_sortable_columns', $post_type );
					// add the column to list of sortable columns
					// sanitizing: check with has_filter() to prevent more than 1 call
					if ( ! has_filter( $hook, $add_sort_function ) ) {
						add_filter( $hook, $add_sort_function );
					}
					
					// get the hook name for the column edit action
					$hook = sprintf( 'manage_%s_posts_custom_column', $post_type );
					// add thumbnail in column per post
					// sanitizing: check with has_filter() to prevent multiple contents in a column
					if ( ! has_action( $hook, $display_column_function ) ) {
						add_action( $hook, $display_column_function, 10, 2 );
					}
				} // if ()
			} // if ( value == 1 )
		} // foreach( stored_settings )

		// set required user capability
		if ( isset( $this->stored_settings[ 'minimum_role_all_pages' ] ) ) {
			switch ( $this->stored_settings[ 'minimum_role_all_pages' ] ) {
				case 'administrator':
					$this->required_user_cap = 'manage_options';
					break;
				default:
					$this->required_user_cap = 'edit_others_posts';
			}
		} else {
			$this->required_user_cap = 'edit_others_posts';
		}
		// define general capatibility once
		$this->is_capable_user = current_user_can( $this->required_user_cap );
		
		// specify translations
		add_action( 'admin_init', array( $this, 'set_dynamic_values' ) );
		// load admin style sheet
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		// load admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// print style for thumbnail column
		add_action( 'admin_head', array( $this, 'display_thumbnail_column_style' ) );
		// define image column sort order
		add_filter( 'pre_get_posts', array( $this, 'sort_column_by_image_id' ) );
		// define ajax function to set featured images
		add_action( 'wp_ajax_qfi_set_thumbnail', array( $this, 'set_thumbnail' ) );
		// define ajax function to set featured images
		add_action( 'wp_ajax_qfi_delete_thumbnail', array( $this, 'delete_thumbnail' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     7.0
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
	 * Set translations, cache and variables
	 *
	 * @since     13.3.5
	 *
	 */
	public function set_dynamic_values() {
		// preset translations once
		$this->translation_cache = array();
		$text = 'Change image';				$this->translation_cache[ 'Change image' ]			= esc_html__( $text );
		$text = 'Delete %s';				$this->translation_cache[ 'Delete x' ]				= _x( $text, 'plugin' );
		$text = 'Delete';					$this->translation_cache[ 'Delete' ]				= esc_html__( $text );
		$text = 'Edit &#8220;%s&#8221;';	$this->translation_cache[ 'Edit x' ]				= __( $text );
		$text = 'Edit Image';				$this->translation_cache[ 'Edit Image' ]			= esc_html__( $text );
		$text = 'Error';					$this->translation_cache[ 'Error' ]					= esc_attr__( $text );
		$text = 'Item deleted.';			$this->translation_cache[ 'Item deleted.' ]			= esc_html__( $text );
		$text = 'Item not added.';			$this->translation_cache[ 'Item not added.' ]		= esc_html__( $text );
		$text = 'Item not updated.';		$this->translation_cache[ 'Item not updated.' ]		= esc_html__( $text );
		$text = 'Meta';						$this->translation_cache[ 'Meta' ]					= __( $text );
		$text = 'No file was uploaded.';	$this->translation_cache[ 'No file was uploaded.' ]	= __( $text );
		$text = 'No image set';				$this->translation_cache[ 'No image set' ]			= esc_html__( $text );
		$text = 'Remove featured image';	$this->translation_cache[ 'Remove featured image' ]	= esc_html( _x( $text, 'post' ) );
		$text = 'Set featured image';		$this->translation_cache[ 'Set featured image' ]	= esc_html( _x( $text, 'post' ) );
		$this->translation_cache[ '(external image)' ]	= esc_html__( '(external image)', 'quick-featured-images' );
		$this->translation_cache[ 'Change x' ]			= __( 'Change &#8220;%s&#8221;', 'quick-featured-images' );
		$this->translation_cache[ 'Remove x' ]			= __( 'Remove &#8220;%s&#8221;', 'quick-featured-images' );
		$this->translation_cache[ 'Set image for x' ]	= __( 'Set image for &#8220;%s&#8221;', 'quick-featured-images' );
		// preset the "broken image" thumbnail once
		$esc_path = esc_url( plugin_dir_url( __FILE__ ) );
		$this->thumbnail_cache = array();
		$this->thumbnail_cache[ 'No file was uploaded.' ] = sprintf(
			'<img src="%sassets/images/no-file.png" alt="%s" width="48" height="64" class="qfi-no100p"><br />%s',
			$esc_path,
			$this->translation_cache[ 'Error' ],
			$this->translation_cache[ 'No file was uploaded.' ]
		);
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     12.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		// load JS file in posts list pages only
		$screen = get_current_screen();
		if ( 'edit' == $screen->base ) {
			// define handle once
			$handle = $this->plugin_slug . '-admin-script';

			// load script
			wp_enqueue_script( $handle, plugins_url( 'assets/js/admin-column.js', __FILE__ ), array( 'jquery' ), $this->plugin_version );

			// trick: use nonce as translated string to implement random values in JS
			$translations = array(
				'nonce' => wp_create_nonce( 'qfi-image-column' ),
			);
			wp_localize_script( $handle, 'qfi_i18n', $translations );

			// Enqueue all stuff to use media API, requires at least WP 3.5
			wp_enqueue_media();

		}

	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     7.0
	 *
	 * @return    null
	 */
	public function enqueue_admin_styles() {
		// load CSS file in posts list pages only
		$screen = get_current_screen();
		if ( 'edit' == $screen->base ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin-column.css', __FILE__ ), array( ), $this->plugin_version );
		}
 	}

	/* 
	 * ======================================================
	 * Methods for the thumbnail column
	 * on post overview pages
	 * ======================================================
	 */
	 
	/**
	 * Add a column with the title "Featured image" in the post lists
	 *
	 * @since     7.0
	 *
	 * @return    array	list of columns    
	 */
    public function add_thumbnail_column( $cols ) {
		$text = "Featured image";
		$cols[ $this->thumbnail_column_name ] = _x( $text, 'post' );
        return $cols;
    }
	
    /**
     * Add the Featured Image column to sortable columns
     *
	 * @since     9.0
	 *
	 * @return    array	extended list of sortable columns    
     */
    public function add_sortable_column( $cols ) {
        $cols[ $this->thumbnail_column_name ] = $this->thumbnail_column_name;

        return $cols;
    }

	/**
	 * Print the featured image in the column
	 *
	 * @since     7.0
	 *
	 * @return    array	extended list of columns    
	 */
    public function display_thumbnail_in_column( $column_name, $post_id ) {
		if ( $this->thumbnail_column_name == $column_name ) {
			$width = $height = $this->used_thumbnail_width;
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			// check if image file exists, omit filters in get_attached_file() ('true')
			if ( $thumbnail_id ) {
				// if thumbnail HTML was not yet created, create it now
				if ( empty( $this->thumbnail_cache[ $thumbnail_id ] ) ) {
					// if the image file does exist
					if ( file_exists( get_attached_file( $thumbnail_id, true ) ) && $thumb = wp_get_attachment_image( $thumbnail_id, array( $width, $height ) ) ) {
						// cache the thumb
						$this->thumbnail_cache[ $thumbnail_id ] = $thumb;
					} else {
						// cache it as not available
						$this->thumbnail_cache[ $thumbnail_id ] = false;
					}
				}
				// if there is an image, display it
				if ( $this->thumbnail_cache[ $thumbnail_id ] ) {
					// print thumbnail HTML
					if ( current_user_can( $this->required_user_cap, $thumbnail_id ) ) {
						// show image linked to media selection box
						$thumb_title = _draft_or_post_title( $thumbnail_id );
						printf(
							'<a href="%s" id="qfi_set_%d" class="qfi_set_fi" title="%s">%s<br />%s</a>',
							esc_url( get_upload_iframe_src( 'image', $post_id ) ),
							$post_id,
							esc_attr( sprintf( $this->translation_cache[ 'Change x' ], $thumb_title ) ),
							$this->thumbnail_cache[ $thumbnail_id ],
							$this->translation_cache[ 'Change image' ]
						);
						// display 'edit' link
						printf(
							'<br /><a href="%s" title="%s">%s</a>',
							esc_url( get_edit_post_link( $thumbnail_id ) ),
							esc_attr( sprintf( $this->translation_cache[ 'Edit x' ], $thumb_title ) ),
							$this->translation_cache[ 'Edit Image' ]
						);
						// display removal link
						printf(
							'<br /><a href="#" id="qfi_delete_%d" class="qfi_delete_fi hide-if-no-js" title="%s">%s</a>',
							$post_id,
							esc_attr( sprintf( $this->translation_cache[ 'Remove x' ], $thumb_title ) ),
							$this->translation_cache[ 'Remove featured image' ]
						);
					} else {
						// if no edit capatibilities, show image only
						echo $this->thumbnail_cache[ $thumbnail_id ];
					} // if ( current_user_can( $this->required_user_cap, $thumbnail_id ) )
				// thumbnail ID is orphaned ("file-less", outdated), so create HTML for a "broken image" symbol
				} else {
					// print "broken" icon
					echo $this->thumbnail_cache[ 'No file was uploaded.' ];
					if ( $this->is_capable_user ) {
						// display removal link
						printf(
							'<br /><a href="#" id="qfi_delete_%d" class="qfi_delete_fi hide-if-no-js" title="%s">%s</a>',
							$post_id,
							esc_attr( sprintf( $this->translation_cache[ 'Delete x' ], $this->translation_cache[ 'Meta' ] ) ),
							$this->translation_cache[ 'Delete' ]
						);
						// print creation link
						printf(
							'<br /><a href="%s" id="qfi_set_%d" class="qfi_set_fi" title="%s">%s</a>',
							esc_url( get_upload_iframe_src( 'image', $post_id ) ),
							$post_id,
							esc_attr( sprintf( $this->translation_cache[ 'Set image for x' ], _draft_or_post_title( $post_id ) ) ),
							$this->translation_cache[ 'Set featured image' ]
						);
					} // if ( $this->is_capable_user )
				} // if ( $this->thumbnail_cache[ $thumbnail_id ] )
			} else {
				// print note
				echo $this->translation_cache[ 'No image set' ];
				if ( $this->is_capable_user ) {
					// print
					printf(
						'<br /><a href="%s" id="qfi_set_%d" class="qfi_set_fi" title="%s">%s</a>',
						esc_url( get_upload_iframe_src( 'image', $post_id ) ),
						$post_id,
						esc_attr( sprintf( $this->translation_cache[ 'Set image for x' ], _draft_or_post_title( $post_id ) ) ),
						$this->translation_cache[ 'Set featured image' ]
					);
				} // if ( $this->is_capable_user )
			} // if thumbnail_id
		} // if this column name == column_name
    }
	
	/**
	 * Print CSS for image column
	 *
	 * @since     7.0
	 *
	 * @return    null    
	 */
	public function display_thumbnail_column_style(){
		echo '<style type="text/css">';
		echo "\n";
		echo "/* Quick Featured Images plugin styles */\n";
		echo "/* Fit thumbnails in posts list column */\n";
		printf( '.column-%s img {', $this->thumbnail_column_name );
		echo 'width:100%;height:auto;';
		printf( 'max-width:%dpx;', $this->used_thumbnail_width );
		printf( 'max-height:%dpx;', $this->used_thumbnail_width );
		echo "}\n";
		/* hide image column in small displays in WP version smaller than 4.3 */
		if ( version_compare( get_bloginfo( 'version' ), '4.3', '<' ) ) {
			echo "/* Auto-hiding of the thumbnail column in posts lists */\n";
			echo '@media screen and (max-width:782px) {';
			printf( '.column-%s {', $this->thumbnail_column_name );
			echo "display:none;}}\n";
		} // if WP < 4.3
		echo '</style>';
	}

    /**
     * Define sort order: order posts by featured image id
     *
	 * @since     9.0
	 *
     * @param $query
     */
    public function sort_column_by_image_id( $query ) {
	
		// if user wants to get rows sorted by featured image
        if ( $query->get( 'orderby' ) === $this->thumbnail_column_name ) {
			// set thumbnail id as sort value
            $query->set( 'meta_key', '_thumbnail_id' );
			// change sorting from alphabetical to numeric
            $query->set( 'orderby', 'meta_value_num' );
        }
    }

    /**
     * Set post featured image per Ajax request
     *
	 * @since     12.0
	 *
     */
    public function set_thumbnail () {

		if ( ! isset( $_POST[ 'qfi_nonce' ] ) or ! wp_verify_nonce( $_POST[ 'qfi_nonce' ], 'qfi-image-column' ) ) {
			$text = 'Sorry, you are not allowed to edit this item.';
			die( __( $text ) );
		}
		if ( isset( $_POST[ 'post_id' ] ) and isset( $_POST[ 'thumbnail_id' ] ) ) {
			// sanitze ids
			$post_id		= absint( $_POST[ 'post_id' ][ 0 ] );
			$thumbnail_id	= absint( $_POST[ 'thumbnail_id' ] );
			// try to set thumbnail; returns true if successful
			$success = set_post_thumbnail( $post_id, $thumbnail_id );
			if ( $success ) {

				/*
				 * build the HTML response
				 */
				 
				$thumb_title = _draft_or_post_title( $thumbnail_id );
				
				// 'change thumbnail' link
				$html = sprintf(
					'<a href="%s" id="qfi_set_%d" class="qfi_set_fi" title="%s">%s<br />%s</a>',
					esc_url( get_upload_iframe_src( 'image', $post_id ) ),
					$post_id,
					esc_attr( sprintf( $this->translation_cache[ 'Change x' ], $thumb_title ) ),
					get_the_post_thumbnail( $post_id, array( $this->used_thumbnail_width, $this->used_thumbnail_width ) ),
					$this->translation_cache[ 'Change image' ]
				);

				// 'edit image' link
				$html .= sprintf(	
					'<br /><a href="%s" title="%s">%s</a>',
					esc_url( get_edit_post_link( $thumbnail_id ) ),
					esc_attr( sprintf( $this->translation_cache[ 'Edit x' ], $thumb_title ) ),
					$this->translation_cache[ 'Edit Image' ]
				);

				// 'remove thumbnail' link
				$html .= sprintf(
					'<br /><a href="#" id="qfi_delete_%d" class="qfi_delete_fi hide-if-no-js" title="%s">%s</a>',
					$post_id,
					esc_attr( sprintf( $this->translation_cache[ 'Remove x' ], $thumb_title ) ),
					$this->translation_cache[ 'Remove featured image' ]
				);
				
				// return response to Ajax script
				echo $html;
				
			} else {
				// return error message to Ajax script
				echo $this->translation_cache[ 'Item not added.' ];
			}
		}
		die();
    }

    /**
     * Remove post featured image per Ajax request
     *
	 * @since     12.0
	 *
     */
    public function delete_thumbnail () {
		if ( ! isset( $_POST[ 'qfi_nonce' ] ) or ! wp_verify_nonce( $_POST[ 'qfi_nonce' ], 'qfi-image-column' ) ) {
			$text = 'Sorry, you are not allowed to delete this item.';
			die( __( $text ) );
		}
		if ( isset( $_POST[ 'post_id' ] ) ) {
			// sanitze post id
			$post_id = absint( $_POST[ 'post_id' ][ 0 ] );
			// try to delete thumbnail; returns true if successful
			$success = delete_post_thumbnail( $post_id );
			if ( $success ) {
				/*
				 * build the HTML response
				 */
				
				$post_title = _draft_or_post_title( $post_id );

				// 'set thumbnail' link
				$html = sprintf(
					'%s<br /><a href="%s" id="qfi_set_%d" class="qfi_set_fi" title="%s">%s</a>',
					$this->translation_cache[ 'Item deleted.' ],
					esc_url( get_upload_iframe_src( 'image', $post_id ) ),
					$post_id,
					esc_attr( sprintf( $this->translation_cache[ 'Set image for x' ], $post_title ) ),
					$this->translation_cache[ 'Set featured image' ]
				);

				// return response to Ajax script
				echo $html;
				
			} else {
				// return error message to Ajax script
				echo $this->translation_cache[ 'Item not updated.' ];
			}
		}
		die();
    }

	/**
	 *
	 * Render HTML image element for the thumbnail of the external image
	 *
	 * @access   private
	 * @since    13.3.5
	 */
	private function get_html_external_thumbnail( $url, $alt, $size ) {
		$image_height = $size[ 1 ] / $size[ 0 ] * $this->used_thumbnail_width;
		return sprintf(
			'<img width="%s" height="%s" src="%s" class="attachment-thumbnail" alt="%s"><br />%s',
			absint( $this->used_thumbnail_width / 2 ),
			absint( $image_height / 2 ),
			$url,
			$alt,
			$this->translation_cache[ '(external image)' ]
		);
	}

	/* 
	 * ======================================================
	 * Methods for the post list column
	 * on the media library pages
	 * ======================================================
	 */
	 
	/**
	 * Add a column with the title "Featured image" in the post lists
	 *
	 * @since     13.4.0
	 *
	 * @return    array	list of columns    
	 */
    public function add_post_list_column( $cols ) {
		$cols[ $this->post_list_column_name ] = __( 'Featured image for', 'quick-featured-images' );
        return $cols;
    }
	
	/**
	 * Print the post titles which has the current image as featured image
	 *
	 * @since     13.4.0
	 *
	 * @return    array	extended list of columns    
	 */
    public function display_post_list_column( $column_name, $attachment_id ) {
		// quit if not the desired column
		if ( $this->post_list_column_name !== $column_name ) {
			return;
		}
		
		// look up the posts for which the attachment was set as featured image 
		// returns an array of post IDs if any, else an empty array
		global $wpdb;
		$post_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT	`post_id` 
			FROM	$wpdb->postmeta 
			WHERE	`meta_key` = '_thumbnail_id' 
				AND	`meta_value` = %d", $attachment_id
		) ); 
		
		// if posts were found
		if ( $post_ids ) {
			// if there is more than one post
			if ( 1 < sizeof( $post_ids ) ) {
				// print a list
				echo '<ul>';
				foreach ( $post_ids as $id ) {
					echo '<li>';
					// print the link to the edit page of the post
					edit_post_link( get_the_title( $id ), '', '', $id );
					echo '</li>';
				}
				echo '</ul>';
			} else {
				// print in a single line the link to the edit page of the found post
				edit_post_link( get_the_title( $post_ids[ 0 ] ), '', '', $post_ids[ 0 ] );
			}
		}
    }

	/**
	 * Print CSS for post list column
	 *
	 * @since     13.4.0
	 *
	 * @return    null    
	 */
	public function display_post_list_column_style(){
		echo '<style type="text/css">';
		echo "\n";
		echo "/* Quick Featured Images plugin styles */\n";
		echo ".widefat td.column-qfi-post-list ul { margin: 0; }\n";
		echo "</style>\n";
	}


}
