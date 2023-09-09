<?php
/**
 * =======================================
 * WP Nav Plus Widget
 * =======================================
 *
 * 
 * @author Matt Keys <matt@mattkeys.me>
 */

class WP_Nav_Plus_Widget extends WP_Widget {

	function __construct()
	{
		$widget_ops = array( 'description' => __( 'Add a custom WP Nav Plus menu to your sidebar.', 'wp-nav-plus' ) );
		parent::__construct( 'nav_plus_widget', __( 'WP Nav Plus Menu', 'wp-nav-plus' ), $widget_ops );
	}

	function widget( $args, $instance )
	{
		static $menu_id_slugs = array();

		$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;

		if ( ! $nav_menu ) {
			return;
		}

		$menu_args = array(
			'menu'				=> $nav_menu,
			'items_wrap'		=> '%3$s',
			'echo'				=> false,
			'container'			=> false,
			'depth'				=> (int) $instance['depth'],
			'start_depth'		=> ( 0 != $instance['start_depth'] ) ? (int) $instance['start_depth'] : false,
			'divider_html'		=> ( '' != $instance['divider_html'] ) ? wp_kses_post( $instance['divider_html'] ) : false,
			'divider_offset'	=> ( 0 != $instance['divider_offset'] ) ? (int) $instance['divider_offset'] : false,
			'limit'				=> ( 0 != $instance['limit'] ) ?  (int) $instance['limit'] : false,
			'offset'			=> ( 0 != $instance['offset'] ) ?  (int) $instance['offset'] : false,
			'segment'			=> ( '' != $instance['segment'] ) ?  esc_attr( $instance['segment'] ) : false
		);

		$wp_nav_menu = wp_nav_menu( $menu_args );

		if ( ! $wp_nav_menu ) {
			return;
		}

		// Attributes
		$wrap_id = 'menu-' . $nav_menu->slug;
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) ) {
				$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
			} else {
				$wrap_id = $wrap_id . '-1';
			}
		}
		$menu_id_slugs[] = $wrap_id;

		$instance['title']		= apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$instance['menu_class']	= ( isset( $instance['menu_class'] ) ) ? $instance['menu_class'] : 'menu';

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}

		?>
		<div class="menu-<?php echo $nav_menu->slug; ?>-container">
			<ul id="<?php echo $wrap_id; ?>" class="<?php echo esc_attr( $instance['menu_class'] ); ?>">
				<?php echo $wp_nav_menu; ?>
			</ul>
		</div>
		<?php

		echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance )
	{
		$instance['title'] 			= sanitize_text_field( $new_instance['title'] );
		$instance['menu_class'] 	= $this->sanitize_menu_classes( $new_instance['menu_class'] );
		$instance['nav_menu'] 		= (int) $new_instance['nav_menu'];
		$instance['depth'] 			= (int) $new_instance['depth'];
		$instance['start_depth'] 	= (int) $new_instance['start_depth'];
		$instance['divider_html'] 	= wp_filter_post_kses( $new_instance['divider_html'] );
		$instance['divider_offset'] = (int) $new_instance['divider_offset'];
		$instance['limit'] 			= (int) $new_instance['limit'];
		$instance['offset'] 		= (int) $new_instance['offset'];
		$instance['segment'] 		= sanitize_text_field( $new_instance['segment'] );

		return $instance;
	}

	function sanitize_menu_classes( $menu_classes, $sanitized_menu_classes = '' )
	{
		$menu_classes = explode( ' ', $menu_classes );
		if ( ! empty( $menu_classes ) ) {
			foreach ( $menu_classes as $menu_class ) {
				$sanitized_menu_classes .= sanitize_html_class( $menu_class ) . ' ';
			}
			$sanitized_menu_classes = rtrim( $sanitized_menu_classes );
		}

		return $sanitized_menu_classes;
	}

	function form( $instance )
	{
		// Get menus
		$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );

		$title 				= isset( $instance['title'] ) 			? $instance['title'] 			: '';
		$menu_class			= isset( $instance['menu_class'] ) 		? $instance['menu_class'] 		: 'menu';
		$depth 				= isset( $instance['depth'] ) 			? $instance['depth'] 			: 0;
		$start_depth 		= isset( $instance['start_depth'] ) 	? $instance['start_depth'] 		: 0;
		$divider_html 		= isset( $instance['divider_html'] ) 	? $instance['divider_html'] 	: false;
		$divider_offset 	= isset( $instance['divider_offset'] ) 	? $instance['divider_offset'] 	: 0;
		$limit 				= isset( $instance['limit'] ) 			? $instance['limit'] 			: false;
		$offset 			= isset( $instance['offset'] ) 			? $instance['offset'] 			: false;
		$segment 			= isset( $instance['segment'] ) 		? $instance['segment'] 			: false;

		// If no menus exists, direct the user to go and create some.
		if ( ! $menus ) {
			echo '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.', 'wp-nav-plus' ), admin_url('nav-menus.php') ) .'</p>';
			return;
		} else {
			$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : $menus[0]->term_id;

			// Get menu items
			$menu_items = wp_get_nav_menu_items( $nav_menu );
		}
		?>

		<div class="wpnp_section_title"><p><a class="toggle_wpnp_option"><strong><?php _e( 'General Options', 'wp-nav-plus' ); ?></strong></a></p></div>
		<div class="wpnp_section_wrap general">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'wp-nav-plus' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e( 'Menu Name:', 'wp-nav-plus' ); ?></label>
				<select id="<?php echo $this->get_field_id('nav_menu'); ?>" class="wpnp_menu_name" name="<?php echo $this->get_field_name('nav_menu'); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;', 'wp-nav-plus' ); ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('menu_class'); ?>"><?php _e( 'Menu Class:', 'wp-nav-plus' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id('menu_class'); ?>" name="<?php echo $this->get_field_name('menu_class'); ?>" value="<?php echo esc_attr( $menu_class ); ?>" />
				<span class="description"><?php _e( 'Separate multiple classes with spaces', 'wp-nav-plus' ) ?></span>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:', 'wp-nav-plus'); ?></label>
				<select id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>">
					<option value="0" <?php selected( $depth, 0 ); ?>><?php _e( '0 (all)', 'wp-nav-plus' ); ?></option>
					<?php for ( $x = 1; $x <= 10; $x++ ) : ?>
						<option value="<?php echo $x; ?>" <?php selected( $depth, $x ); ?>>
							<?php echo $x; ?>
						</option>
					<?php endfor; ?>
					<option value="-1" <?php selected( $depth, -1 ); ?>><?php _e( '-1 (flat)', 'wp-nav-plus' ); ?></option>
				</select>
			</p>
		</div>
		<div class="wpnp_section_title"><p><a class="toggle_wpnp_option"><strong><?php _e( 'Split Menu Options', 'wp-nav-plus' ); ?></strong></a></p></div>
		<div class="wpnp_section_wrap split">
			<p><?php _e( 'Choose a Start Depth for your menu to skip over a specified number of parent items.', 'wp-nav-plus' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id('start_depth'); ?>"><?php _e( 'Start Depth:', 'wp-nav-plus' ); ?></label>
				<select id="<?php echo $this->get_field_id('start_depth'); ?>" name="<?php echo $this->get_field_name('start_depth'); ?>">
					<?php for ( $x = 0; $x <= 10; $x++ ) : ?>
						<option value="<?php echo $x; ?>" <?php selected( $start_depth, $x ); ?>>
							<?php echo $x; ?>
						</option>
					<?php endfor; ?>
				</select>
			</p>
		</div>
		<div class="wpnp_section_title"><p><a class="toggle_wpnp_option"><strong><?php _e( 'Divided Menu Options', 'wp-nav-plus' ); ?></strong></a></p></div>
		<div class="wpnp_section_wrap divide">
			<p><?php _e( 'Enter any HTML into the Divider HTML area below to display that HTML with your menu.', 'wp-nav-plus' ); ?></p>
			<p><?php _e( 'Divider HTML will try to appear in the middle of your menu. Use the offset to change its position.', 'wp-nav-plus' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id('divider_html'); ?>"><?php _e( 'Divider HTML:', 'wp-nav-plus' ); ?></label>
				<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id('divider_html'); ?>" name="<?php echo $this->get_field_name('divider_html'); ?>"><?php echo wp_kses_post( $divider_html ); ?></textarea>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('divider_offset'); ?>"><?php _e( 'Divider Offset:', 'wp-nav-plus' ); ?></label>
				<select id="<?php echo $this->get_field_id('divider_offset'); ?>" name="<?php echo $this->get_field_name('divider_offset'); ?>">
					<?php for ( $x = -10; $x <= 10; $x++ ) : ?>
						<option value="<?php echo $x; ?>" <?php selected( $divider_offset, $x ); ?>>
							<?php echo $x; ?>
						</option>
					<?php endfor; ?>
				</select>
			</p>
		</div>
		<div class="wpnp_section_title"><p><a class="toggle_wpnp_option"><strong><?php _e( 'Limit / Offset Options', 'wp-nav-plus' ); ?></strong></a></p></div>
		<div class="wpnp_section_wrap limit_offset">
			<p><?php _e( 'Use limit to choose how many parent-level menu items to display.', 'wp-nav-plus' ); ?></p>
			<p><?php _e( 'Use offset to skip over a specified number of parent-level menu items.', 'wp-nav-plus' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e( 'Limit:', 'wp-nav-plus' ); ?></label>
				<select id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>">
					<option value="0" <?php selected( $limit, 0 ); ?>><?php _e( '0 (no limit)', 'wp-nav-plus' ); ?></option>
					<?php for ( $x = 1; $x <= 40; $x++ ) : ?>
						<option value="<?php echo $x; ?>" <?php selected( $limit, $x ); ?>>
							<?php echo $x; ?>
						</option>
					<?php endfor; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('offset'); ?>"><?php _e( 'Offset:', 'wp-nav-plus' ); ?></label>
				<select id="<?php echo $this->get_field_id('offset'); ?>" name="<?php echo $this->get_field_name('offset'); ?>">
					<?php for ( $x = 0; $x <= 40; $x++ ) : ?>
						<option value="<?php echo $x; ?>" <?php selected( $offset, $x ); ?>>
							<?php echo $x; ?>
						</option>
					<?php endfor; ?>
				</select>
			</p>
		</div>
		<div class="wpnp_section_title"><p><a class="toggle_wpnp_option"><strong><?php _e( 'Segment Options', 'wp-nav-plus' ); ?></strong></a></p></div>
		<div class="wpnp_section_wrap segment">
			<p><?php _e( 'Use segment to show a portion of your menu regardless of the active page.', 'wp-nav-plus' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id('segment'); ?>"><?php _e( 'Segment Parent:', 'wp-nav-plus' ); ?></label>
				<select class="segment_options" id="<?php echo $this->get_field_id('segment'); ?>" name="<?php echo $this->get_field_name('segment'); ?>" data-selected-option="<?php echo esc_attr( $segment ); ?>">
					<option value="" <?php selected( $segment, '' ); ?>><?php _e( 'N/A (disabled)', 'wp-nav-plus' ); ?></option>
					<?php foreach ( $menu_items as $menu_item ) {
						if ( $segment && ! is_numeric( $segment ) && $segment == $menu_item->title ) {
							$segment = $menu_item->object_id;
						}
						?>
						<option value="<?php echo $menu_item->object_id; ?>" <?php selected( $segment, $menu_item->object_id ); ?>><?php echo $menu_item->title; ?></option>
					<?php } ?>
				</select>
			</p>
		</div>
		<?php
	}
}

function register_wp_nav_plus_widget() {

	register_widget( 'WP_Nav_Plus_Widget' );
}

add_action( 'widgets_init', 'register_wp_nav_plus_widget' );

function enqueue_wp_nav_plus_scripts( $hook ) {
 
	if ( 'widgets.php' != $hook ) {
		return;
	}

	wp_enqueue_style( 'wp-nav-plus-widget-css', WPNP_PUBLIC_PATH . 'includes/widget.css' );
 
	wp_enqueue_script( 'wp-nav-plus-widget-js', WPNP_PUBLIC_PATH . 'includes/widget.js', array( 'jquery' ), '3.4.7' );
	wp_localize_script( 'wp-nav-plus-widget-js', 'WPNP', array(
		'nonce'				=> wp_create_nonce( 'wpnp_get_nav_items' ),
		'disabled_string'	=> __( 'N/A (disabled)', 'wp-nav-plus' )
	));
}
add_action( 'admin_enqueue_scripts', 'enqueue_wp_nav_plus_scripts' );

function wp_nav_plus_get_nav_items() {

	check_ajax_referer( 'wpnp_get_nav_items', 'nonce' );

	$menu_id = intval( $_POST['menu_id'] );

	if ( ! $menu_id ) {
		wp_die();
	}

	echo json_encode( wp_get_nav_menu_items( $menu_id ) );
	exit;
}
add_action( 'wp_ajax_wpnp_get_nav_items', 'wp_nav_plus_get_nav_items' );
