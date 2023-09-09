<?php
/**
 * CF7 Skins Admin Tab Class.
 * 
 * Implement Tab functionality on CF7 Skins Admin.
 * 
 * @package cf7skins
 * @author Neil Murray
 * 
 * @since 0.1.0
 */

class CF7_Skins_Admin_Tab extends CF7_Skins_Admin {
	
	// Class variables
	var $tabs, $template, $style;
	
	/**
	 * Class constructor
	 *
	 * @since 0.1.0
	 */
	function __construct() {
		parent::__construct(); // run parent class
		
		$this->template = new CF7_Skin_Template();
		$this->style = new CF7_Skin_Style();
		
		add_action( 'wp_ajax_cf7s_sort_skin', array( &$this, 'sort_skin' ) );
	}
	
	/**
	 * Sort styles/templates based on selected filter using AJAX.
	 * 
	 * @options all, new, search, tag
	 * @output HTML
	 * 
	 * @since 0.1.0
	 */	
	function sort_skin() {
		// Check the nonce and if not isset the id, just die.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cf7s' ) || ! isset( $_POST['tab'] ) || ! isset( $_POST['sort'] ) )
			die();
		
		if( strpos( $_POST['tab'], 'template' ) ) {	// templates
			
			$templates = $this->template->cf7s_get_template_list();
			$dates = $new_templates = array();
			
			switch ( $_POST['sort'] ) :
				
				case 'all': // display all
					$new_templates = $templates;
					break;
					
				case 'new':	// sort by date
					foreach( $templates as $key => $template ) {
						if( $template['details']['Version Date'] ) {
							$template_date = explode( "//", $template['details']['Version Date'] );
							$template_date = $template_date[0];
							$dates[$key] = mysql2date( 'U', $template_date );
						}
					}
					
					arsort( $dates );
					$dates = array_slice( $dates, 0, 10, true ); // get only the first 10
					
					foreach( $dates as $key => $date )
						$new_templates[$key] = $templates[$key];
					break;
					
				case 'search':
					if( ! isset( $_POST['keyword'] ) && empty( $_POST['keyword'] ) )
						return;
								
					$keyword = sanitize_text_field( $_POST['keyword'] );

					foreach( $templates as $key => $template ) {
						$match = false;
						foreach( $template['details'] as $details )
							if( stristr( $details, $keyword ) !== false )
								$match = true;
						
						if( $match )
							$new_templates[$key] =  $template;
					}
					break;
					
				case 'tag':
					if( ! isset( $_POST['tags'] ) && empty( $_POST['tags'] ) ) 
						return;
					
					foreach( $templates as $key => $template ) {
						if( empty( $template['details']['Tags'] ) ) // bail early if empty
							continue;
						
						$stripped_tags = str_replace( ' ', '', $template['details']['Tags'] ); // strip all spaces before exploding
						$template_tags = explode( ',', $stripped_tags );
						$match = false;
						
						foreach( $_POST['tags'] as $tag )
							if( in_array( sanitize_text_field( $tag ), $template_tags ) )
								$match = true;
								
						if( $match )
							$new_templates[$key] = $template;
					}
					break;
					
				default:
					foreach( $templates as $key => $template )
						if( in_array( $_POST['sort'], $template['tags'] ) )
							$new_templates[$key] = $template;
					break;
			
			endswitch;
			
			if( $new_templates )
				$this->template->templates_list( $new_templates );
			else
				echo '<p class="no-themes no-skins">'. __( 'No templates found. Try a different search.', 'contact-form-7-skins' ) . '</p>';
		
		} elseif( strpos( $_POST['tab'], 'style' ) ) {	// styles
			
			$styles = $this->style->cf7s_get_style_list();
			$dates = $new_styles = array();
			
			switch ( $_POST['sort'] ) :
				
				case 'all':	// display all
					$new_styles = $styles;
					break;
				
				case 'new':	// sort by date
					foreach( $styles as $key => $style ) {
						if( $style['details']['Version Date'] ) {
							$skin_date = explode( "//", $style['details']['Version Date'] );
							$skin_date = $skin_date[0];
							$dates[$key] = mysql2date( 'U', $skin_date );
						}
					}
					
					arsort( $dates );
					$dates = array_slice( $dates, 0, 10, true ); // get only the first 10
					foreach( $dates as $key => $date )
						$new_styles[$key] = $styles[$key];
					break;
					
				case 'search':
					if( ! isset( $_POST['keyword'] ) && empty( $_POST['keyword'] ) )
						return;
					
					$keyword = sanitize_text_field( $_POST['keyword'] );
					foreach( $styles as $key => $style ) {
						$match = false;
						foreach( $style['details'] as $details )
							if( stristr( $details, $keyword ) !== false )
								$match = true;
								
						if( $match )
							$new_styles[$key] = $style;
					}
					break;
					
				case 'tag':
					if( ! isset( $_POST['tags'] ) && empty( $_POST['tags'] ) )
						return;
					
					foreach( $styles as $key => $style ) {
						if( empty( $style['details']['Tags'] ) ) // bail early if empty
							continue;
							
						$stripped_tags = str_replace( ' ', '', $style['details']['Tags'] ); // strip all spaces before exploding
						$style_tags = explode( ',', $stripped_tags );
						$match = false;
						
						foreach( $_POST['tags'] as $tag )	
							if( in_array( sanitize_text_field( $tag ), $style_tags ) )
								$match = true;
								
						if( $match )
							$new_styles[$key] = $style;	
					}
					break;
					
				default:
					foreach( $styles as $key => $style )
						if( in_array( $_POST['sort'], $style['tags'] ) )
							$new_styles[$key] = $style;	
					break;
			
			endswitch;
			
			if( $new_styles )
				$this->style->styles_list( $new_styles );
			else
				echo '<p class="no-themes no-skins">'. __( 'No styles found. Try a different search.', 'contact-form-7-skins' ) . '</p>';
		}
		
		exit();
	}
	
	/**
	 * Create tabs for styles or templates.
	 * 
	 * Function called in parent class admin.php
	 * 
	 * @output HTML
	 * @filter 'cf7skins_tabs'
	 * @do_action 'cf7skins_tab_content'
	 * 
     * @param $post current post object
     * @param $box metabox arguments
	 * 
	 * @since 0.1.0
	 */
	function generate_tab( $post, $box ) {
		$option = get_option( CF7SKINS_OPTIONS );
		$color_scheme = isset( $option['color_scheme'] ) ? $option['color_scheme'] : '';
		
		$tabs = array();
		
		// Add tabs from CF7 Skins plugins & Visual React components
		$this->tabs = apply_filters( 'cf7skins_tabs', $tabs ); // tabs filter @since 1.2.0

		// Sort tab order NOTE: Original $key is lost when use usort
		usort( $this->tabs, function($a, $b) {
			return $a['order'] - $b['order'];
		} );

		// Get current post ID, check if CF7 version < 4.2 
		$post_id = isset( $post->ID ) ? $post->ID : $post->id();
		
		// Get selected template/style
		$template = get_post_meta( $post_id, 'cf7s_template', true );
		$style = get_post_meta( $post_id, 'cf7s_style', true );
		
		$template_name = $style_name = ''; // selected template/style string
		
		?>
		<div class="cf7s">
			<h2 class="nav-tab-wrapper <?php echo esc_attr( $color_scheme ); ?>">
				<?php foreach( $this->tabs as $key => $value ) : ?>
					<a class="nav-tab nav-tab-<?php echo esc_attr( $value['name'] ); ?>" href="#tab-<?php echo esc_attr( $value['name'] ); ?>">
						<?php echo esc_attr( $value['label'] ); ?>
						<span class="help balloon-hover balloon" title="<?php echo esc_attr( $value['note'] ); ?>">!</span>
						<span class="help balloon-hover balloon" title="<?php echo esc_attr( $value['help'] ); ?>">?</span>
					</a>
				<?php endforeach; ?>
				
				<a class="nav-tab nav-tab-add-ons" href="#tab-add-ons"><?php _e( 'Add-ons', 'contact-form-7-skins' ); ?></a>			
				<a class="nav-tab nav-tab-getting-started" href="#tab-getting-started"><?php _e( 'Getting Started', 'contact-form-7-skins' ); ?></a>						
				
			</h2>
			
			<div class="nav-tab-content <?php echo esc_attr( $color_scheme ); ?>">
				<?php foreach( $this->tabs as $key => $value ) : ?>
					<div id="tab-<?php echo esc_attr( $value['name'] ); ?>" class="tab-content wp-clearfix">
						<?php do_action( 'cf7skins_tab_content', $value['name'] ); ?>
					</div>
				<?php endforeach; ?>
				
				<div id="tab-getting-started" class="tab-content info-tab wp-clearfix">
					<div class="info-wrapper"><?php require_once( CF7SKINS_PATH . 'includes/getting-started.php' ); ?></div>
				</div>
				<div id="tab-add-ons" class="tab-content info-tab wp-clearfix">
					<div class="info-wrapper"><?php require_once( CF7SKINS_PATH . 'includes/pro-version.php' ); ?></div>
				</div>
			</div>
		</div>
		<?php
	}

} new CF7_Skins_Admin_Tab();
