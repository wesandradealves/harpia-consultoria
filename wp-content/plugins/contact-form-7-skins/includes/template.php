<?php
/**
 * CF7 Skins Template Class.
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */

class CF7_Skin_Template extends CF7_Skin {

	/**
	 * Class constructor
	 * 
	 * @since 0.1.0
	 */
	function __construct() {
		parent::__construct(); // Run parent class
		add_action( 'wp_ajax_load_template', array( &$this, 'load_template' ) );
		add_action( 'cf7skins_tabs', array( $this, 'template_tab' ) );
		add_action( 'cf7skins_tab_content', array( &$this, 'template_content' ) );
	}
	
	/**
	 * Load selected template and translate.
	 * 
	 * @uses wpcf7_load_textdomain
	 * 
	 * @since 0.1.0
	*/
	function load_template() {
		// Check the nonce and if not valid, just die.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cf7s' ) ) {
			die();
		}
		// Get translation if locale is set and exists in the Contact Form 7 l10n
		if( isset( $_POST['locale'] ) 
			&& ! empty( $_POST['locale'] ) 
			&& array_key_exists( $_POST['locale'], wpcf7_l10n() ) ) {
				wpcf7_load_textdomain( $_POST['locale'] );
		}
		// Get translation based on post ID
		if( isset( $_POST['post'] ) && ! empty( $_POST['post'] ) ) {
			$wpcf7 = wpcf7_contact_form( (int) $_POST['post'] ); // current CF7 form
			wpcf7_load_textdomain( $wpcf7->locale );
		}
		
		// Load selected template file
		$templates = $this->cf7s_get_template_list();
		$template = $templates[ sanitize_text_field( $_POST['template'] ) ];
		require_once( $template['path'] . trailingslashit( $template['dir'] ) . $template['index'] );
		exit();
	}
	
	/**
	 * Get list of templates - sorted alphabetically [a-z].
	 * 
	 * @filter cf7skins_templates
	 * @return (array) array of templates
	 
	 * @since 0.1.0
	 */
	public static function cf7s_get_template_list() {
		$templates = self::read_templates( CF7SKINS_TEMPLATES_PATH, CF7SKINS_TEMPLATES_URL );
		$templates = apply_filters( 'cf7skins_templates', $templates ); // add filter for other plugins
		ksort( $templates ); // sort by array keys
		return $templates;
	}
	
	/**
	* Get list of templates from the templates directory.
	* 
	* @param $path current plugin templates directory path
	* @param $url  current plugin templates directory url
	* @return (array) arrays of template information
	* 
	* @since 0.1.0
	*/	
	public static function read_templates( $path, $url ) {
		
		$templates = array();
		
		if ( $handle = opendir( $path ) ) {
		
			// Uses WP file system for reading description.txt and instruction.txt
			if( is_admin() ) {
				WP_Filesystem();
			}
			global $wp_filesystem;
			
			while (false !== ( $entry = readdir( $handle ) ) ) {
				
				if ( $entry != '.' && $entry != '..' ) {
					
					// Add default instructions
					ob_start();
					include( CF7SKINS_PATH . 'includes/template-instructions.php' );
					$instructions = ob_get_contents();
					$instructions = str_replace( "\r", "<br />", $instructions ); // replace newline as HTML <br />
					ob_end_clean();
					
					// Step up default headers
					$default_headers = array(
						'Template Name' => 'Template Name',
						'Template URI' 	=> 'Template URI',
						'Author' 		=> 'Author',
						'Author URI' 	=> 'Author URI',
						'Description' 	=> 'Description',
						'Instructions' 	=> 'Instructions',
						'Version' 		=> 'Version',
						'Version Date'	=> 'Version Date',	// with format '2012-02-23 06:12:45'
						'License' 		=> 'License',
						'License URI' 	=> 'License URI',
						'Tags' 			=> 'Tags',
						'Text Domain' 	=> 'Text Domain'  // for external translation slug
					);
					
					// Start reading files
					$files = scandir( $path . $entry );
					$templates[$entry]['dir'] = $entry;
					$templates[$entry]['path'] = $path;
					$templates[$entry]['url'] = $url;
					foreach( $files as $file ) {
						if ( $file != '.' && $file != '..' ) {
							$templates[$entry]['files'][] = $file;
							$file_path = $path . trailingslashit($entry) . $file;
							$file_data = get_file_data( $file_path, $default_headers );
							
							// Load description from description.txt if it exists
							// Inline description will be overwrited if description.txt if it exists
							$description_file = $path . trailingslashit($entry) . 'description.txt';
							if ( is_admin() && $wp_filesystem->is_file( $description_file ) ) {
								$_description = $wp_filesystem->get_contents( $description_file );
								$file_data['Description'] = str_replace( "\r", "<br />", $_description ); // replace newline as HTML <br />
							}
							
							// Use default instruction if there is no instruction
							$file_data['Instructions'] = $file_data['Instructions'] ? $file_data['Instructions'] : $instructions;
							
							// Load instruction from instruction.txt if it exists
							// Inline or default description will be overwrited if description.txt if it exists
							$instructions_file = $path . trailingslashit($entry) . 'instruction.txt';
							if ( is_admin() && $wp_filesystem->is_file( $instructions_file ) ) {
								$_instructions = $wp_filesystem->get_contents( $instructions_file );
								$file_data['Instructions'] = str_replace( "\r", "<br />", $_instructions ); // replace newline as HTML <br />
							}
							
							if( $file_data['Template Name'] ) {
								$templates[$entry]['index'] = $file;
								$templates[$entry]['details'] = $file_data;
							}
						}
					}
					
					$templates[$entry]['tags'] = array_map('trim', explode( ',', $templates[$entry]['details']['Tags'] ) );
				}
			}
			closedir( $handle );
		}
		
		return $templates;
	}
	
	/**
	* Returns a list of template filter tags. // NRM - move to template-feature.php
	*
	* Uses the same method as get_theme_feature_list() function
	* @see http://codex.wordpress.org/Function_Reference/get_theme_feature_list
	* 
	* @filter template_filter_tags
	* @return (array)
	* 
	* @since 0.1.0
	*/
	function filter_tags() {
		$filter_tags = array(
		
			'Layout' => array (
				'fixed-layout' 			=> __( 'Fixed Layout', 'contact-form-7-skins' ),
				'fluid-layout' 			=> __( 'Fluid Layout', 'contact-form-7-skins' ),
				'responsive-layout' 	=> __( 'Responsive Layout', 'contact-form-7-skins' ),
				'one-column' 			=> __( 'One Column', 'contact-form-7-skins' ),
				'one-two-columns'		=> __( 'One or Two Column', 'contact-form-7-skins' ),
				'one-two-three-columns'	=> __( 'One, Two or Three Column', 'contact-form-7-skins' ),
			),
			
			'Features' => array (
				'fieldsets'		=> __( 'Fieldsets', 'contact-form-7-skins' ),
				'background'	=> __( 'Background', 'contact-form-7-skins' ),
				'gradients' 	=> __( 'Gradients', 'contact-form-7-skins' ),
			),
			
			'Subject' => array (
				'business'		=> __( 'Business', 'contact-form-7-skins' ),
				'event'			=> __( 'Event', 'contact-form-7-skins' ),
				'holiday' 		=> __( 'Holiday', 'contact-form-7-skins' ),
				'individual' 	=> __( 'Individual', 'contact-form-7-skins' ),
				'seasonal' 		=> __( 'Seasonal', 'contact-form-7-skins' ),
			)
		);
		
		return apply_filters( 'template_filter_tags', $filter_tags );
	}
	
	/**
	 * Output template filter tags for backend // NRM - move to template-filter.php
	 * 
	 * @compat WP3.9
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function cf7s_show_template_list_39() {
		$val = get_post_meta( $this->get_id(), 'cf7s_template', true ); // Get current post selected template
		?>
		<div class="theme-navigation theme-install-php">
			<span class="theme-count"><?php echo count( $this->cf7s_get_template_list() ); ?></span>
			<a class="theme-section skin-sort balloon current" title="<?php _e( 'All available Templates',  'contact-form-7-skins' ); ?>" href="#" data-sort="all"><?php _e('All', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" title="<?php _e( 'Selected by CF7 Skins Team',  'contact-form-7-skins' ); ?>" href="#" data-sort="featured"><?php _e('Featured', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" title="<?php _e( 'Commonly used',  'contact-form-7-skins' ); ?>" href="#" data-sort="popular"><?php _e('Popular', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" title="<?php _e( 'Recently added',  'contact-form-7-skins' ); ?>" href="#" data-sort="new"><?php _e('Latest', 'contact-form-7-skins'); ?></a>

			<div class="theme-top-filters">
				<?php if( CF7SKINS_FEATURE_FILTER ) : ?>
					<a class="more-filters balloon" title="<?php _e( 'Narrow your choices based on your specific requirements',  'contact-form-7-skins' ); ?>" href="#"><?php _e('Feature Filter', 'contact-form-7-skins'); ?></a>
				<?php endif; ?>
			</div>

			<div class="more-filters-container">
				<a class="apply-filters button button-secondary" href="#"><?php _e('Apply Filters', 'contact-form-7-skins'); ?><span></span></a>
				<a class="clear-filters button button-secondary" href="#"><?php _e('Clear', 'contact-form-7-skins'); ?></a>
				<br class="clear">

				<?php
				$feature_list = $this->filter_tags();

				foreach ( $feature_list as $key => $features ) {

					echo '<div class="filters-group">';

					echo '<h4 class="feature-name">' . esc_attr( $key ) . '</h4>';
					echo '<ol class="feature-group">';
					foreach ( $features as $feature => $feature_name ) {
						echo '<li><input type="checkbox" id="tab-template-' . esc_attr( $feature ) . '" value="' . esc_attr( $feature ) . '" /> ';
						echo '<label for="tab-template-' . esc_attr( $feature ) . '">' . esc_attr( $feature_name ) . '</label></li>';
					}
					echo '</ol>';
					echo '</div>';
				}
				?>

				<div class="filtering-by filtered-by">
					<span><?php _e('Filtering by:', 'contact-form-7-skins'); ?></span>
					<div class="tags"></div>
					<a href="#"><?php _e('Edit', 'contact-form-7-skins'); ?></a>
				</div>
			</div>
			
			<div class="skins-sort">
				<label class="balloon" title="<?php _e( 'Sort by Name, Date and License (free or pro) – use arrow to reverse sort order', 'contact-form-7-skins' ); ?>" for="skins-sort"><?php _e('Sort by', 'contact-form-7-skins'); ?></label>
				<select class="balloon sort-by" name="sort-by" title="" autocomplete="off">
					<option value="name" selected="selected"><?php _e( 'Name', 'contact-form-7-skins' ); ?></option>
					<option value="date"><?php _e( 'Date', 'contact-form-7-skins' ); ?></option>
					<option value="license"><?php _e( 'License', 'contact-form-7-skins' ); ?></option>
				</select>
				<a href="javascript:void(0)" class="dashicons dashicons-arrow-down-alt"></a>
			</div>
			<label class="screen-reader-text" for="theme-search-input"><?php _e('Search Templates', 'contact-form-7-skins'); ?></label>
			<input type="search" class="theme-search skins-search" id="theme-search-input" placeholder="<?php _e('Search templates...', 'contact-form-7-skins'); ?>" />
		</div>
		
		<div class="skin-list wp-clearfix">
			<span class="spinner"></span>
			<?php $this->templates_list() ?>
		</div>
		
		<div class="skin-details wp-clearfix hidden">
			<?php foreach( $this->cf7s_get_template_list() as $template )
				$this->cf7s_details_view( $template ); ?>
		</div>
		<input type="hidden" value="<?php echo esc_attr( $val ); ?>" name="cf7s-template" id="cf7s-template" />
		<?php
	}
	
	 /**
	 * Output template filter tags for backend. // NRM - move to template-filter.php?
	 * 
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function cf7s_show_template_list() {
		$val = get_post_meta( $this->get_id(), 'cf7s_template', true ); // Get current post selected template
		?>
		<div class="wp-filter">
			<div class="filter-count"><span class="count"><?php echo count( $this->cf7s_get_template_list() ); ?></span></div>
			
			<ul class="filter-links">
				<li><a class="theme-section skin-sort balloon current" title="<?php _e( 'All available Templates',  'contact-form-7-skins' ); ?>" href="#" data-sort="all"><?php _e('All', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" title="<?php _e( 'Selected by CF7 Skins Team',  'contact-form-7-skins' ); ?>" href="#" data-sort="featured"><?php _e('Featured', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" title="<?php _e( 'Commonly used',  'contact-form-7-skins' ); ?>" href="#" data-sort="popular"><?php _e('Popular', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" title="<?php _e( 'Recently added',  'contact-form-7-skins' ); ?>" href="#" data-sort="new"><?php _e('Latest', 'contact-form-7-skins'); ?></a></li>
			</ul>

			<div class="selected-skin">
					<span class="selected-template"><?php _e( 'Template', 'contact-form-7-skins' ); ?>: [<span><?php echo esc_attr( $this->get_skin_name( 'template' ) ); ?></span>]</span>
					<span class="selected-style"><?php _e( 'Style', 'contact-form-7-skins' ); ?>: [<span><?php echo esc_attr( $this->get_skin_name( 'style' ) ); ?></span>]</span>
			</div>
			
			<?php if( CF7SKINS_FEATURE_FILTER ) : ?>
				<a class="drawer-toggle balloon" title="<?php _e( 'Narrow your choices based on your specific requirements',  'contact-form-7-skins' ); ?>" href="#"><?php _e('Feature Filter', 'contact-form-7-skins'); ?></a>
			<?php endif; ?>
			
			<div class="search-form">
				<label class="screen-reader-text" for="theme-search-input"><?php _e('Search Templates', 'contact-form-7-skins'); ?></label>
				<input type="search" class="theme-search skins-search" id="theme-search-input" placeholder="<?php _e('Search templates...', 'contact-form-7-skins'); ?>" />
			</div>
			
			<div class="filter-drawer">
				<div class="buttons">
					<a class="apply-filters button button-secondary" href="#"><?php _e('Apply Filters', 'contact-form-7-skins'); ?><span></span></a>
					<a class="clear-filters button button-secondary" href="#"><?php _e('Clear', 'contact-form-7-skins'); ?></a>
				</div>
				
				<?php
				$feature_list = $this->filter_tags();
				foreach ( $feature_list as $feature_name => $features ) {
					echo '<div class="filter-group">';
					$feature_name = esc_html( $feature_name );
					echo '<h4 class="feature-name">' . esc_attr( $feature_name ) . '</h4>';
					echo '<ol class="feature-group">';
					foreach ( $features as $feature => $feature_name ) {
						$feature = esc_attr( $feature );
						echo '<li><input type="checkbox" id="tab-template-' . esc_attr( $feature ) . '" value="' . esc_attr( $feature ) . '" /> ';
						echo '<label for="tab-template-' . esc_attr( $feature ) . '">' . esc_attr( $feature_name ) . '</label></li>';
					}
					echo '</ol>';
					echo '</div>';
				}
				?>
				<div class="filtered-by">
					<span><?php _e('Filtering by:', 'contact-form-7-skins'); ?></span>
					<div class="tags"></div>
					<a href="#"><?php _e('Edit', 'contact-form-7-skins'); ?></a>
				</div>
			</div>
			
			<div class="skins-sort">
				<label class="balloon" title="<?php _e( 'Sort by Name, Date and License (free or pro) – use arrow to reverse sort order', 'contact-form-7-skins' ); ?>" for="skins-sort"><?php _e('Sort by', 'contact-form-7-skins'); ?></label>
				<select class="balloon sort-by" name="sort-by" title="" autocomplete="off">
					<option value="name" selected="selected"><?php _e( 'Name', 'contact-form-7-skins' ); ?></option>
					<option value="date"><?php _e( 'Date', 'contact-form-7-skins' ); ?></option>
					<option value="license"><?php _e( 'License', 'contact-form-7-skins' ); ?></option>
				</select>
				<a href="javascript:void(0)" class="dashicons dashicons-arrow-down-alt"></a>
			</div>
		</div>
		
		<div class="skin-list wp-clearfix">
			<span class="spinner"></span>
			<?php $this->templates_list() ?>
		</div>
		<div class="skin-details wp-clearfix hidden">
			<?php foreach( $this->cf7s_get_template_list() as $template ) 		
				$this->cf7s_details_view( $template ); ?>
		</div>
		
		<input type="hidden" value="<?php echo esc_attr( $val ); ?>" name="cf7s-template" id="cf7s-template" />
		<?php
	}
	
	/**
	 * Output each template in the template tab.
	 * 
	 * @deprecated cf7s_show_template_inlist
	 * @param $templates (array) of all the templates
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function templates_list( $templates = array() ) {
		if( ! $templates )
			$templates = $this->cf7s_get_template_list();
		//print_r( $templates );
		
		// Get the current contact form ID, check if request comes from AJAX
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : $this->get_id();
		
		foreach( $templates as $key => $template ) {
			$class = $template['dir'] == get_post_meta( $id, 'cf7s_template', true ) ? ' selected' : '';
			$select_text = $template['dir'] == get_post_meta( $id, 'cf7s_template', true ) ? __('Selected', 'contact-form-7-skins' ) : __('Select', 'contact-form-7-skins' );
			$locale = isset( $_GET['locale'] ) ? sanitize_text_field( $_GET['locale'] ) : '';
			$post = isset( $_GET['post'] ) ? (int) $_GET['post'] : '';
			
			$skin_class = $template['dir'] == get_post_meta( $id, 'cf7s_template', true ) ? 'skin skin-selected' : 'skin';
			$date = mysql2date( 'U', $template['details']['Version Date'] );
			
			// Check if skin is free or pro version
			$license = 'free';
			if ( defined( 'CF7SKINSPRO_PATH' ) )
				$license = strpos( $template['path'], CF7SKINSPRO_PATH ) !== false ? 'pro' : $license;
			?>
			<div class="<?php echo esc_attr( $skin_class ); ?>" data-name="<?php echo esc_attr( $key ); ?>" data-date="<?php echo esc_attr( $date ); ?>" data-license="<?php echo esc_attr( $license ); ?>">
				<div class="wrapper">
					<h4 class="skin-name"><?php echo esc_attr( $template['details']['Template Name'] ); ?></h4>
					<div class="thumbnail">
						<?php $imgpath = $template['path'] . $template['dir'] . '/thumbnail.png'; ?>
						<?php $imgurl = $template['url'] . $template['dir'] . '/thumbnail.png'; ?>
						<img src="<?php echo file_exists( $imgpath ) ? esc_url( $imgurl ) : CF7SKINS_URL . 'images/no-preview.png'; ?>" />
					</div>
					<ul class="wp-clearfix skin-action">
						<li><a class="select<?php echo esc_attr( $class ); ?> balloon" title="<?php _e( 'Select to apply the Template to your form - appears in the form editing area, where you can edit your requirements.','contact-form-7-skins' ); ?>" data-post="<?php echo esc_attr( $post ); ?>" data-locale="<?php echo esc_attr( $locale ); ?>" data-value="<?php esc_attr( $this->get_slug_name( $template ) ); ?>" href="#cf7s-template"><?php echo esc_attr( $select_text ); ?></a></li>
						<li><a class="detail balloon" title="<?php _e( 'Show detailed information about this Template, with layout, description and usage details.' ,'contact-form-7-skins' ); ?>" href="#tpl-<?php esc_attr( $this->get_slug_name( $template ) ); ?>-detail"><?php _e('Details', 'contact-form-7-skins' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}
	}
	
	/**
	 * Output expanded and details view of selected template.
	 * 
	 * @TODO Display in pop-over window
	 * @param $template current processed template
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */ 
	function cf7s_details_view( $template ) {
		global $themes_allowedtags;
		$class = $template['dir'] == get_post_meta( $this->get_id(), 'cf7s_template', true ) ? ' selected' : ''; // set link class
		$select_text = $template['dir'] == get_post_meta( $this->get_id(), 'cf7s_template', true ) ? __('Selected', 'contact-form-7-skins') : __('Select', 'contact-form-7-skins');
		?>
		<div id="tpl-<?php esc_attr( $this->get_slug_name( $template ) ); ?>-detail" class="details hidden">
			<div class="details-view">
				<div class="block-thumbnail">
					<img src="<?php echo esc_url( $template['url'] ) . esc_attr( $template['dir'] ) . '/thumbnail.png'; ?>" />
				</div>
				<div class="block-details"><div>
					<ul class="wp-clearfix skin-action">
						<li><a class="balloon view" data-value="<?php esc_attr( $this->get_slug_name( $template ) ); ?>" href="#cf7s-template" title="<?php _e( 'Use Expanded View to view Template features - shows layout, description & usage details.', 'contact-form-7-skins' ); ?>"><?php _e('Expanded View', 'contact-form-7-skins' ); ?></a></li>
						<li><a class="balloon select<?php echo esc_attr( $class ); ?>" data-value="<?php esc_attr( $this->get_slug_name( $template ) ); ?>" href="#cf7s-template" title="<?php _e( 'Select to apply the Template to your form - appears in the Form Editing area, where you can edit to your requirements.', 'contact-form-7-skins' ); ?>"><?php echo esc_attr( $select_text ); ?></a></li>
						<li><a class="balloon close" href="#" title="<?php _e( 'Return to Template Gallery/Grid view.', 'contact-form-7-skins' ); ?>"><?php _e( 'Close', 'contact-form-7-skins' ); ?></a></li>
					</ul>
					<?php // print_r( $template ); ?>
					<h1><?php echo esc_attr( $template['details']['Template Name'] ); ?></h1>
					
					<h4><strong><?php _e('Description', 'contact-form-7-skins' ); ?></strong></h4>
					<p class="description"><?php echo wp_kses( $template['details']['Description'], $themes_allowedtags ); ?></p>
					
					<h4><strong><?php _e('Instructions', 'contact-form-7-skins' ); ?></strong></h4>
					<p class="description"><?php echo wp_kses( $template['details']['Instructions'], $themes_allowedtags ); ?></p>
				</div></div>
			</div>
			
			<div class="expanded-view">
				<ul class="wp-clearfix skin-action">
					<li><a class="balloon view" data-value="<?php esc_attr( $this->get_slug_name( $template ) ); ?>" href="#cf7s-template" title="<?php _e( 'Return to Details View', 'contact-form-7-skins' ); ?>"><?php _e('Details View', 'contact-form-7-skins' ); ?></a></li>
					<li><a class="balloon select<?php echo esc_attr( $class ); ?>" data-value="<?php esc_attr( $this->get_slug_name( $template ) ); ?>" href="#cf7s-template" title="<?php _e( 'Select to apply the Template to your form - appears in the Form editing area, where you can edit to your requirements.', 'contact-form-7-skins' ); ?>"><?php echo esc_attr( $select_text ); ?></a></li>
					<li><a class="balloon close" href="#" title="<?php _e( 'Return to Template Gallery/ Grid View', 'contact-form-7-skins' ); ?>"><?php _e('Close', 'contact-form-7-skins' ); ?></a></li>
				</ul>
				
				<h1><?php echo esc_attr( $template['details']['Template Name'] ); ?></h1>
				
				<div class="large-thumbnail">
					<img src="<?php echo esc_url( $this->get_skin_modal( $template ) ); ?>" />
				</div>
				<h4><strong><?php _e('Description', 'contact-form-7-skins' ); ?></strong></h4>
				<p class="description"><?php echo wp_kses( $template['details']['Description'], $themes_allowedtags ); ?></p>
				
				<h4><strong><?php _e('Instructions', 'contact-form-7-skins' ); ?></strong></h4>
				<p class="description"><?php echo wp_kses( $template['details']['Instructions'], $themes_allowedtags ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Add Template tab.
	 * 
	 * @param {Array}		$tabs	CF7 Skins tabs
	 * 
	 * @return {Array}		Template tab
	 * 
	 * @since 4.0.0
	 */
	function template_tab( $tabs ) {
		return array ( 'template' => array(
			'name' => 'template',
			'order' => 3,
			'label' => __( 'Template', 'contact-form-7-skins' ),
			'note' => __( 'Templates are pre-created forms that automatically create the form’s structure and content for you. Each template works as an easy to follow guide.', 'contact-form-7-skins' ),
			'help' => __( 'Select a template that closely matches your needs then switch to the CF7 Skins Form  tab to add, duplicate or remove fields to match your requirements. Any field content can be changed by clicking Edit on the field.', 'contact-form-7-skins' ),	
		) ) + $tabs;
	}

	/**
	 * Add Template tab content.
	 * 
	 * @param {String}	$tab	Tab name
	 * 
	 * @since 4.0.0
	 */
	function template_content( $tab ) {
		if ( 'template' != $tab )
			return;
		if ( version_compare( get_bloginfo( 'version' ), '4', '<' ) )
			$this->cf7s_show_template_list_39();
		else
			$this->cf7s_show_template_list();
	}

} // End class
