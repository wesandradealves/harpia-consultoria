<?php
/**
 * CF7 Skins Export Utility Class.
 *
 * Method:
 * 1. Create a Export button in CF7 Status section.
 * 2. Setup export_wp() parameters by using post status and 'page' post type.
 * 3. Modify SQL query to replace post status with post ID, and 'page' post type with
 *    CF7 post type.
 * 4. Setup different export file name.
 *
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 2.1.3
 */

class CF7_Skins_Export {

	/**
	 * Class constructor
	 *
	 * @since 2.1.3
	 */
	function __construct() {
		
		add_action( 'wpcf7_admin_misc_pub_section', array( $this, 'export_button' ), 1, 1 );
		add_action( 'wpcf7_admin_init', array( $this, 'export_wxr' ) );

		add_filter( 'query', array( $this, 'export_query' ) );
		add_filter( 'export_wp_filename', array( $this, 'export_filename' ), 1, 3 );
	}

	/**
	 * Add export button in CF7 Status section - not for a new form.
	 *
	 * @param	{String}	$post_id	current form id
	 *
	 * @since 2.1.3
	 */
	function export_button( $post_id ) {
		if ( $post_id == '-1' ) { // not for a new form
			return;
		}

		// Create the button, and set onclick to set action value different from 'save'.
		// https://plugins.trac.wordpress.org/browser/contact-form-7/trunk/admin/edit-contact-form.php#L18
		// The 'save' action is used to show the spinner, thus avoid the spinner being shown.
		?>
		<div style="padding:0 10px 10px 10px; text-align:right">
			<input type="submit" name="cf7skins-export" 
				title="<?php esc_attr_e( 'Export this form and data to WXR format.', 'contact-form-7-skins' ); ?>"
				class="button" value="<?php esc_attr_e( 'Export Form', 'contact-form-7-skins' ); ?>" 
				onclick="this.form.action.value = 'export'; return true;"
			/>
		</div>
		<?php
	}
	
	/**
	 * Do export to WXR (WordPress eXtended RSS) after updating form.
	 *
	 * WordPress WXR Export works based on post_ids array.
	 * It loops for the post_ids and generete the WXR content.
	 * Unfortunately there is no filter/hook to change it.
	 * https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/export.php#L141.
	 * The post_ids can be changed by modifying the SQL statement before retrieving the post_ids,
	 * https://github.com/WordPress/WordPress/blob/master/wp-includes/wp-db.php#L1871.
	 * To help distinguish which one is the export query, we provide custom export parameter
	 * by adding post status 'cf7skins_export'. The good news, the post status accepts any status, 
	 * even if it is not registered, but needs to use 'post/page' post type.
	 * https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/export.php#L113
	 *
	 * @param	{Class/Object}		$cf7	WPCF7_ContactForm
	 *
	 * @since 2.1.3
	 */
	function export_wxr() {

		// Return if this is not exporting
		if ( ! isset( $_POST['cf7skins-export'] ) ) {
			return;
		}

		require_once( ABSPATH . 'wp-admin/includes/export.php' );

		$args = array();

		// The post type, will be replaced with 'wpcf7_contact_form' later.
		$args['content'] = 'page';

		// Post status, will be replaced with ID and current form ID later.
		$args['status'] = 'cf7skins_export';

		export_wp( $args ); // do export
		die(); // exit after downloading exported file
	}

	/**
	 * Modify the export query.
	 * 
	 * Query: SELECT ID FROM wp_posts WHERE wp_posts.post_type = 'page' 
	 *		  AND wp_posts.post_status = 'cf7skins_export'
	 *
	 * @param	{String}		$query		SQL query
	 *
	 * @return modified query with cf7 post type and current form id, for example:
	 *		   ... wp_posts.post_type = 'wpcf7_contact_form' AND wp_posts.ID = '1821'
	 *
	 * @since 2.1.3
	 */
	function export_query( $query ) {

		// Make sure this is requested in CF7 edit form and not a new created form
		if ( isset( $_GET['page'] ) && isset( $_GET['post'] ) ) {

			$form_id = (int) $_GET['post']; // set form id

			// Check if this query is our export query.
			// Query contains 'SELECT ID FROM' and 'cf7skins_export' string in it.
			// Take attention for double quotes in query.
			if ( strpos( $query, 'SELECT ID FROM' ) !== false && strpos( $query, 'cf7skins_export' ) ) {

				// Replace post type from page with wpcf7_contact_form
				$query = str_replace( ".post_type = 'page'", ".post_type = 'wpcf7_contact_form'", $query );

				// Replace post status with ID and set the value to  current form ID
				$query = str_replace( ".post_status = 'cf7skins_export'", ".ID = '{$form_id}'", $query );						
			}
		}

		return $query;
	}

	/**
	 * Modify the exported file name.
	 * 
	 * Default: mysite.wordpress.2019-06-26.xml;
	 *
	 * @param {String}		$wp_filename	The name of the file for download.
	 * @param {String}		$sitename		The site name.
	 * @param {String}		$date			Today's date, formatted.
	 *
	 * @return modified filename cf7skins.mysite.2019-06-26.xml
	 *
	 * @since 2.1.3
	 */
	function export_filename( $wp_filename, $sitename, $date ) {

		// Only in CF7 edit page, and not for new form.
		// Logic is used twice (see above), consider using a new function?
		if ( isset( $_GET['page'] ) && isset( $_GET['post'] ) ) {
			$wp_filename = 'cf7skins.' . $sitename . $date . '.xml';
		}

		return $wp_filename;
	}
}

// Get CF7 Skins settings
$option = get_option( CF7SKINS_OPTIONS );

// Run this class if feature is enabled
if ( isset( $option['export'] ) && !! $option['export'] ) {
	new CF7_Skins_Export();
}
