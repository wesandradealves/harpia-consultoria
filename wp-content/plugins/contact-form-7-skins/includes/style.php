<?php
/**
 * CF7 Skins Style Class.
 * 
 * @package cf7skins
 * @author Neil Murray 
 * 
 * @since 0.1.0
 */

class CF7_Skin_Style extends CF7_Skin {

	/**
	 * Class constructor
	 * 
	 * @since 0.1.0
	 */
	function __construct() {
		parent::__construct(); // Run parent class
		add_action( 'cf7skins_tabs', array( $this, 'style_tab' ) );
		add_action( 'cf7skins_tab_content', array( &$this, 'style_content' ) );
	}
	
	/**
	 * Get list of styles - sorted alphabetically [a-z].
	 * 
	 * @filter cf7skins_styles
	 * @return (array) array of styles
	 * 
	 * @since 0.0.1
	 */
	public static function cf7s_get_style_list() {
		$styles = self::read_styles( CF7SKINS_STYLES_PATH, CF7SKINS_STYLES_URL );
		$styles = apply_filters( 'cf7skins_styles', $styles ); // add filter for other plugins
		ksort( $styles ); // sort by array keys	
		return $styles;
	}
	
	/**
	* Get list of styles from the styles directory.
	* 
	* @param $path (TYPE) current plugin styles directory path
	* @param $url (TYPE) current plugin styles directory url
	* @return (array) arrays of style information
	* 
	* @since 0.1.0
	*/
	public static function read_styles( $path, $url ) {
	
		$styles = array();
		
		if ( $handle = opendir( $path ) ) {
		
			// Uses WP file system for reading description.txt and instruction.txt
			if( is_admin() ) {
				WP_Filesystem();
			}
			global $wp_filesystem;
			
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				
				if ( $entry != '.' && $entry != '..' ) {
				
					// Add default instructions
					ob_start();
					include( CF7SKINS_PATH . 'includes/style-instructions.php' );
					$instructions = ob_get_contents();
					$instructions = str_replace( "\r", "<br />", $instructions ); // replace newline as HTML <br />
					ob_end_clean();
					
					// Step up default headers
					$default_headers = array(
						'Style Name' 	=> 'Style Name',
						'Style URI' 	=> 'Style URI',
						'Author' 		=> 'Author',
						'Author URI' 	=> 'Author URI',
						'Description' 	=> 'Description',
						'Instructions' 	=> 'Instructions',
						'Version' 		=> 'Version',
						'Version Date'	=> 'Version Date',	// with format '2012-02-23 06:12:45'
						'License' 		=> 'License',
						'License URI' 	=> 'License URI',
						'Tags' 			=> 'Tags'
					);
					
					// Start reading files
					$files = scandir( $path . $entry );
					$styles[$entry]['dir'] = $entry;
					$styles[$entry]['path'] = $path;
					$styles[$entry]['url'] = $url;
					foreach( $files as $file ) {
						if ( $file != '.' && $file != '..' ) {
							$styles[$entry]['files'][] = $file;
							$file_path = $path . trailingslashit($entry) . $file;
							$file_data = get_file_data( $file_path, $default_headers, 'test' );
							
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
							
							if( $file_data['Style Name'] ) {
								$styles[$entry]['index'] = $file;
								$styles[$entry]['details'] = $file_data;
							}
						}
					}
					
					$styles[$entry]['tags'] = array_map('trim', explode( ',', $styles[$entry]['details']['Tags'] ) );
				}
			}
			closedir($handle);
		}
		
		return $styles;
	}
	
	/**
	* Returns a list of style filter tags. // NRM - move to style-feature.php
	* 
	* Uses the same method as get_theme_feature_list() function
	* @see http://codex.wordpress.org/Function_Reference/get_theme_feature_list
	* 
	* @filter style_filter_tags
	* @return (array)
	* 
	* @since 0.1.0
	*/
	function filter_tags() {
		$filter_tags = array(
		
			__( 'Colors', 'contact-form-7-skins' ) => array (
				'black'		=> __( 'Black', 'contact-form-7-skins' ),
				'brown'		=> __( 'Brown', 'contact-form-7-skins' ),
				'gray'		=> __( 'Gray', 'contact-form-7-skins' ),
				'green'		=> __( 'Green', 'contact-form-7-skins' ),
				'orange'	=> __( 'Orange', 'contact-form-7-skins' ),
				'pink'		=> __( 'Pink', 'contact-form-7-skins' ),
				'purple'	=> __( 'Purple', 'contact-form-7-skins' ),
				'red'		=> __( 'Red', 'contact-form-7-skins' ),
				'silver'	=> __( 'Silver', 'contact-form-7-skins' ),
				'tan'		=> __( 'Tan', 'contact-form-7-skins' ),
				'white'		=> __( 'White', 'contact-form-7-skins' ),
				'yellow'	=> __( 'Yellow', 'contact-form-7-skins' ),
				'dark'		=> __( 'Dark', 'contact-form-7-skins' ),
				'light'		=> __( 'Light', 'contact-form-7-skins' ),
			),
			
			__( 'Layout', 'contact-form-7-skins' ) => array (
				'fixed-layout' 			=> __( 'Fixed Layout', 'contact-form-7-skins' ),
				'fluid-layout' 			=> __( 'Fluid Layout', 'contact-form-7-skins' ),
				'responsive-layout' 	=> __( 'Responsive Layout', 'contact-form-7-skins' ),
				'one-column' 			=> __( 'One Column', 'contact-form-7-skins' ),
				'one-two-columns'		=> __( 'One or Two Column', 'contact-form-7-skins' ),
				'one-two-three-columns'	=> __( 'One, Two or Three Column', 'contact-form-7-skins' ),
			),
			
			__( 'Features', 'contact-form-7-skins' ) => array (
				'Fieldsets'		=> __( 'Fieldsets', 'contact-form-7-skins' ),
				'Background'	=> __( 'Background', 'contact-form-7-skins' ),
				'Gradients' 	=> __( 'Gradients', 'contact-form-7-skins' ),
			),
			
			__( 'Subject', 'contact-form-7-skins' ) => array (
				'business'		=> __( 'Business', 'contact-form-7-skins' ),
				'event'			=> __( 'Event', 'contact-form-7-skins' ),
				'holiday' 		=> __( 'Holiday', 'contact-form-7-skins' ),
				'individual' 	=> __( 'Individual', 'contact-form-7-skins' ),
				'seasonal' 		=> __( 'Seasonal', 'contact-form-7-skins' ),
			)
		);
		
		return apply_filters( 'style_filter_tags', $filter_tags );
	}	
	
	/**
	 * Output style filter tags for backend // NRM - move to style-feature.php
	 * 
	 * @compat WP3.9
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function cf7s_show_style_list_39() {
		$val = get_post_meta( $this->get_id(), 'cf7s_style', true ); // Get current post selected style
		?>
		<div class="theme-navigation theme-install-php">
			<span class="theme-count"><?php echo count( $this->cf7s_get_style_list() ); ?></span>
			<a class="theme-section skin-sort current balloon" href="#" data-sort="all" title="<?php _e( 'All available Styles', 'contact-form-7-skins' ); ?>"><?php _e('All', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" href="#" data-sort="featured" title="<?php _e( 'Selected by the CF7 Skins team', 'contact-form-7-skins' ); ?>"><?php _e('Featured', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" href="#" data-sort="popular" title="<?php _e( 'Commonly used', 'contact-form-7-skins' ); ?>"><?php _e('Popular', 'contact-form-7-skins'); ?></a>
			<a class="theme-section skin-sort balloon" href="#" data-sort="new" title="<?php _e( 'Recently added', 'contact-form-7-skins' ); ?>"><?php _e('Latest', 'contact-form-7-skins'); ?></a>
			
			<div class="theme-top-filters">
				<?php if( CF7SKINS_FEATURE_FILTER ) : ?>
					<a class="more-filters balloon" title="<?php _e( 'Narrow your choices based on your specific requirements',  'contact-form-7-skins' ); ?>" href="#">Feature Filter</a>
				<?php endif; ?>
			</div>
			
			<div class="more-filters-container">
				<a class="apply-filters button button-secondary balloon" href="#" title="<?php _e('Check all the boxes that meet your specific requirements and then click apply filters.', 'contact-form-7-skins'); ?>"><?php _e('Apply Filters', 'contact-form-7-skins'); ?><span></span></a>
				<a class="clear-filters button button-secondary balloon" href="#"><?php _e('Clear', 'contact-form-7-skins'); ?></a>
				<br class="clear">
				
				<?php
				$feature_list = $this->filter_tags();
				
				foreach ( $feature_list as $feature_name => $features ) {
				
					if ( $feature_name === 'Colors' || $feature_name === __( 'Colors' ) ) { // hack hack hack
						echo '<div class="filters-group wide-filters-group">';
					} else {
						echo '<div class="filters-group">';
					}

					echo '<h4 class="feature-name">' . esc_attr( $feature_name ) . '</h4>';
					echo '<ol class="feature-group">';
					foreach ( $features as $feature => $feature_name ) {
						echo '<li><input type="checkbox" id="tab-style-' . esc_attr( $feature ) . '" value="' . esc_attr( $feature ) . '" /> ';
						echo '<label for="tab-style-' . esc_attr( $feature ) . '">' . esc_attr( $feature_name ) . '</label></li>';
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
				<label class="balloon" for="skins-sort" title="<?php _e( 'Sort by Name, Date and License (free or pro) – use arrow to reverse sort order', 'contact-form-7-skins' ); ?>"><?php _e('Sort by', 'contact-form-7-skins'); ?></label>
				<select class="sort-by balloon" name="sort-by" title="" autocomplete="off">
					<option value="name" selected="selected"><?php _e( 'Name', 'contact-form-7-skins' ); ?></option>
					<option value="date"><?php _e( 'Date', 'contact-form-7-skins' ); ?></option>
					<option value="license"><?php _e( 'License', 'contact-form-7-skins' ); ?></option>
				</select>
				<a href="javascript:void(0)" class="dashicons dashicons-arrow-down-alt"></a>
			</div>
			<label class="screen-reader-text" for="theme-search-input"><?php _e('Search Styles', 'contact-form-7-skins'); ?></label>
			<input type="search" class="theme-search skins-search" id="theme-search-input" placeholder="<?php _e('Search styles...', 'contact-form-7-skins'); ?>" />
		</div>

		<div class="skin-list wp-clearfix">
			<span class="spinner"></span>
			<?php $this->styles_list(); ?>
		</div>

		<div class="skin-details wp-wp-clearfix hidden">
		<?php foreach( $this->cf7s_get_style_list() as $style )
			$this->cf7s_details_view( $style ); ?>
		</div>
		<input type="hidden" value="<?php echo esc_attr( $val ); ?>" name="cf7s-style" id="cf7s-style" />
		<?php
	}
	
	/**
	 * Output style filter tags for backend. // NRM - move to style-feature.php?
	 * 
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function cf7s_show_style_list() {
		$val = get_post_meta( $this->get_id(), 'cf7s_style', true ); // Get current post selected style
		?>
		<div class="wp-filter">
			<div class="filter-count"><span class="count"><?php echo count( $this->cf7s_get_style_list() ); ?></span></div>
			
			<ul class="filter-links">
				<li><a class="theme-section skin-sort current balloon" href="#" data-sort="all" title="<?php _e( 'All available Styles', 'contact-form-7-skins' ); ?>"><?php _e('All', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" href="#" data-sort="featured" title="<?php _e( 'Selected by the CF7 Skins team', 'contact-form-7-skins' ); ?>"><?php _e('Featured', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" href="#" data-sort="popular" title="<?php _e( 'Commonly used', 'contact-form-7-skins' ); ?>"><?php _e('Popular', 'contact-form-7-skins'); ?></a></li>
				<li><a class="theme-section skin-sort balloon" href="#" data-sort="new" title="<?php _e( 'Recently added', 'contact-form-7-skins' ); ?>"><?php _e('Latest', 'contact-form-7-skins'); ?></a></li>
			</ul>
			
			<div class="selected-skin">
					<span class="selected-template"><?php _e( 'Template', 'contact-form-7-skins' ); ?>: [<span><?php echo esc_attr( $this->get_skin_name( 'template' ) ); ?></span>]</span>
					<span class="selected-style"><?php _e( 'Style', 'contact-form-7-skins' ); ?>: [<span><?php echo esc_attr( $this->get_skin_name( 'style' ) ); ?></span>]</span>
			</div>
			
			<?php if( CF7SKINS_FEATURE_FILTER ) : ?>
				<a class="drawer-toggle balloon" title="<?php _e( 'Narrow your choices based on your specific requirements',  'contact-form-7-skins' ); ?>" href="#">
					<?php _e('Feature Filter', 'contact-form-7-skins'); ?>
				</a>
			<?php endif; ?>
			
			<div class="search-form">
				<label class="screen-reader-text" for="theme-search-input"><?php _e('Search Styles', 'contact-form-7-skins'); ?></label>
				<input type="search" class="theme-search skins-search" id="theme-search-input" placeholder="<?php _e('Search styles...', 'contact-form-7-skins'); ?>" />
			</div>
			
			<div class="filter-drawer">
				<div class="buttons">
					<a class="apply-filters button button-secondary balloon" href="#" title="<?php _e('Check all the boxes that meet your specific requirements and then click apply filters.', 'contact-form-7-skins'); ?>">
						<?php _e('Apply Filters', 'contact-form-7-skins'); ?>
						<span></span>
					</a>
					<a class="clear-filters button button-secondary balloon" href="#"><?php _e('Clear', 'contact-form-7-skins'); ?></a>
				</div>
				
				<?php
				$feature_list = $this->filter_tags();
				foreach ( $feature_list as $feature_name => $features ) {
					if ( $feature_name === 'Colors' || $feature_name === __( 'Colors' ) ) { // hack hack hack
						echo '<div class="filter-group wide-filters-group">';
					} else {
						echo '<div class="filter-group">';
					}

					echo '<h4 class="feature-name">' . esc_attr( $feature_name ) . '</h4>';
					echo '<ol class="feature-group">';
					foreach ( $features as $feature => $feature_name ) {
						echo '<li><input type="checkbox" id="tab-style-' . esc_attr( $feature ) . '" value="' . esc_attr( $feature ) . '" /> ';
						echo '<label for="tab-style-' . esc_attr( $feature ) . '">' . esc_attr( $feature_name ) . '</label></li>';
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
				<label class="balloon" for="skins-sort" title="<?php _e( 'Sort by Name, Date and License (free or pro) – use arrow to reverse sort order', 'contact-form-7-skins' ); ?>">
					<?php _e('Sort by', 'contact-form-7-skins'); ?>
				</label>
				<select class="sort-by balloon" name="sort-by" title="" autocomplete="off">
					<option value="name" selected="selected"><?php _e( 'Name', 'contact-form-7-skins' ); ?></option>
					<option value="date"><?php _e( 'Date', 'contact-form-7-skins' ); ?></option>
					<option value="license"><?php _e( 'License', 'contact-form-7-skins' ); ?></option>
				</select>
				<a href="javascript:void(0)" class="dashicons dashicons-arrow-down-alt"></a>
			</div>
		</div>
		
		<div class="skin-list wp-clearfix">
			<span class="spinner"></span>
			<?php $this->styles_list(); ?>
		</div>
		<div class="skin-details wp-clearfix hidden">
		<?php foreach( $this->cf7s_get_style_list() as $style ) 
			$this->cf7s_details_view( $style ); ?>
		</div>
		
		<input type="hidden" value="<?php echo esc_attr( $val ); ?>" name="cf7s-style" id="cf7s-style" />
		<?php
	}
	
	/**
	 * Output each style in the style tab.
	 * 
	 * @deprecated cf7s_show_style_inlist
	 * @param $styles (array) of all the styles
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */
	function styles_list( $styles = array() ) {
		if( ! $styles )
			$styles = $this->cf7s_get_style_list();
		//print_r( $styles );
		
		// Get the current contact form ID, check if request comes from AJAX
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : $this->get_id();
		
		foreach( $styles as $key => $style ) {
			$class = $style['dir'] == get_post_meta( $id, 'cf7s_style', true ) ? ' selected' : '';
			$select_text = $style['dir'] == get_post_meta( $id, 'cf7s_style', true ) ? __( 'Selected', 'contact-form-7-skins' ) : __( 'Select', 'contact-form-7-skins' );
			
			$skin_class = $style['dir'] == get_post_meta( $id, 'cf7s_style', true ) ? 'skin skin-selected' : 'skin';
			$style_date = explode( "//", $style['details']['Version Date'] );
			$date = mysql2date( 'U', $style_date[0] );
			
			// Check if skin is free or pro version
			$license = 'free';
			if ( defined( 'CF7SKINSPRO_PATH' ) )
				$license = strpos( $style['path'], CF7SKINSPRO_PATH ) !== false ? 'pro' : $license;
			?>
			<div class="<?php echo esc_attr( $skin_class ); ?>" data-name="<?php echo esc_attr( $key ); ?>" data-date="<?php echo esc_attr( $date ); ?>" data-license="<?php echo esc_attr( $license ); ?>">
				<div class="wrapper">
					<h4 class="skin-name"><?php echo esc_attr( $style['details']['Style Name'] ); ?></h4>
					<div class="thumbnail">
						<?php $imgpath = $style['path'] . $style['dir'] . '/thumbnail.png'; ?>
						<?php $imgurl = $style['url'] . $style['dir'] . '/thumbnail.png'; ?>
						<img src="<?php echo file_exists( $imgpath ) ? esc_url( $imgurl ) : CF7SKINS_URL . 'images/no-preview.png'; ?>" />
					</div>
					<ul class="wp-clearfix skin-action">
						<li><a class="select<?php echo esc_attr( $class ); ?> balloon" title="<?php _e( 'Select to apply the Style to your form - is applied to your form once you Save.','contact-form-7-skins' ); ?>" data-value="<?php $this->get_slug_name( $style ); ?>" href="#cf7s-style"><?php echo esc_attr( $select_text ); ?></a></li>
						<li><a class="detail balloon" title="<?php _e( 'Show detailed information about this Style - overview of the appearance and layout with description and usage details.' ,'contact-form-7-skins' ); ?>" href="#<?php $this->get_slug_name( $style ); ?>"><?php _e('Details', 'contact-form-7-skins' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}
	}
	
	/**
	 * Output expanded and details view of selected style.
	 * 
	 * @TODO Display in pop-over window
	 * @param $style current processed style
	 * @output (HTML)
	 * 
	 * @since 0.1.0
	 */ 
	 function cf7s_details_view( $style ) {
		global $themes_allowedtags;
		$class = $style['dir'] == get_post_meta( $this->get_id(), 'cf7s_style', true ) ? ' selected' : ''; // set link class
		$select_text = $style['dir'] == get_post_meta( $this->get_id(), 'cf7s_style', true ) ? __('Selected', 'contact-form-7-skins') : __('Select', 'contact-form-7-skins');
		?>
		<div id="<?php esc_attr( $this->get_slug_name( $style ) ); ?>" class="details hidden">
			<div class="details-view">
				<div class="block-thumbnail">
					<img src="<?php echo esc_url( $style['url'] . $style['dir'] . '/thumbnail.png' ); ?>" />
				</div>
				<div class="block-details"><div>
					<ul class="wp-clearfix skin-action">
						<li><a class="balloon view" data-value="<?php esc_attr( $this->get_slug_name( $style ) ); ?>" href="#cf7s-style" title="<?php _e( 'Use Expanded View to view Styles features - shows all form fields available in Contact Form 7.', 'contact-form-7-skins' ); ?>"><?php _e('Expanded View', 'contact-form-7-skins' ); ?></a></li>
						<li><a class="balloon select<?php echo esc_attr( $class ); ?>" data-value="<?php esc_attr( $this->get_slug_name( $style ) ); ?>" href="#cf7s-style" title="<?php _e( 'Select to apply the Style to your form - is applied to your form once you Save.', 'contact-form-7-skins' ); ?>"><?php echo esc_attr( $select_text ); ?></a></li>
						<li><a class="balloon close" href="#" title="<?php _e( 'Return to Style Gallery/Grid view.', 'contact-form-7-skins' ); ?>"><?php _e('Close', 'contact-form-7-skins' ); ?></a></li>
					</ul>
					<?php // print_r( $style ); ?>
					<h1><?php echo esc_attr( $style['details']['Style Name'] ); ?></h1>

					<h4><strong><?php _e('Description', 'contact-form-7-skins' ); ?></strong></h4>			
					<p class="description"><?php echo wp_kses( $style['details']['Description'], $themes_allowedtags ); ?></p>
					
					<h4><strong><?php _e('Instructions', 'contact-form-7-skins' ); ?></strong></h4>
					<p class="description"><?php echo wp_kses( $style['details']['Instructions'], $themes_allowedtags ); ?></p>
				</div></div>
			</div>
			
			<div class="expanded-view">
				<ul class="wp-clearfix skin-action">
					<li><a class="balloon view" data-value="<?php esc_attr( $this->get_slug_name( $style ) ); ?>" href="#cf7s-style" title="<?php _e( 'Return to Details View', 'contact-form-7-skins' ); ?>"><?php _e('Details View', 'contact-form-7-skins' ); ?></a></li>
					<li><a class="balloon select<?php echo esc_attr( $class ); ?>" data-value="<?php esc_attr( $this->get_slug_name( $style ) ); ?>" href="#cf7s-style" title="<?php _e( 'Select to apply the Style to your form - is applied to your form once you Save', 'contact-form-7-skins' ); ?>"><?php echo esc_attr( $select_text ); ?></a></li>
					<li><a class="balloon close" href="#" title="<?php _e( 'Return to Style Gallery/Grid View', 'contact-form-7-skins' ); ?>"><?php _e('Close', 'contact-form-7-skins' ); ?></a></li>
				</ul>
				
				<h1><?php echo esc_attr( $style['details']['Style Name'] ); ?></h1>
			
				<div class="large-thumbnail">
					<img src="<?php echo esc_attr( $this->get_skin_modal( $style ) ); ?>" />
				</div>
				<h4><strong><?php _e('Description', 'contact-form-7-skins' ); ?></strong></h4>
				<p class="description"><?php echo wp_kses( $style['details']['Description'], $themes_allowedtags ); ?></p>
				
				<h4><strong><?php _e('Instructions', 'contact-form-7-skins' ); ?></strong></h4>
				<p class="description"><?php echo wp_kses( $style['details']['Instructions'], $themes_allowedtags ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Add Style tab.
	 * 
	 * @param {Array}		$tabs	CF7 Skins tabs
	 * 
	 * @return {Array}		Style tab
	 * 
	 * @since 4.0.0
	 */
	function style_tab( $tabs ) {
		return array ( 'style' => array(
			'name' => 'style',
			'order' => 4,
			'label' => __( 'Style', 'contact-form-7-skins' ),
			'note' => __( 'Styles are pre-created designs (CSS code) that are automatically applied to your entire form. They cover all standard Contact Form 7 elements.', 'contact-form-7-skins' ),
			'help' => __( 'Select a style that aligns with your site’s design. To see the style applied, save and go to the page that has the form. To change the style, select another any time.', 'contact-form-7-skins' )
		) ) + $tabs;
	}

	/**
	 * Add Style tab content.
	 * 
	 * @param  {String}	$tab	Tab name
	 * 
	 * @since 4.0.0
	 */
	function style_content( $tab ) {
		if ( 'style' != $tab )
			return;
		if ( version_compare( get_bloginfo( 'version' ), '4', '<' ) )
			$this->cf7s_show_style_list_39();
		else
			$this->cf7s_show_style_list();
	}

} // End class
