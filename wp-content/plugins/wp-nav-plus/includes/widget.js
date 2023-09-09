( function( $ ) {

	$(document).on( 'click', '.toggle_wpnp_option', function() {
		$( this ).closest('.wpnp_section_title').next('.wpnp_section_wrap').toggle();
	});

	$(document).on( 'change', '.wpnp_menu_name', function() {
		var parent_widget = $( this ).closest('.widget-content');
		var current_option = $( '.segment_options', parent_widget ).data('selected-option');

		$.post(
			ajaxurl,
			{
				'action'		: 'wpnp_get_nav_items',
				'nonce'			: WPNP.nonce,
				'menu_id'		: $( this ).val()
			},
			function( response ) {
				var items = $.parseJSON( response );

				var options = '<option value="0">' + WPNP.disabled_string + '</option>';

				$( items ).each( function( index, el ) {
					if ( el.title == current_option ) {
						options = options + '<option value="' + el.title + '" selected="selected">' + el.title + '</option>';
					} else {
						options = options + '<option value="' + el.title + '">' + el.title + '</option>';
					}
				});

				$( '.segment_options', parent_widget ).html( options );
			}
		);

	});

	$(document).on( 'change', '.segment_options', function() {
		$( this ).data( 'selected-option', $( this ).val() );
	});

})( jQuery );