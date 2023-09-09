(function($) {	
	cf7sSettings = {
		init : function( url ) {
			var t = this;
			
			// Select color scheme
			$(".color-option").click( function() {
				t.selectScheme($(this));
			});
			
			// Disable Submit via Enter Key on License Input box
			$(".license-key").keydown( function(event) {
				if(event.keyCode == 13) {
					event.preventDefault();
					return false;
				}
			});
		},
		
		// Update selected color scheme
		selectScheme : function(el) {
			$(el).siblings( '.selected' ).removeClass( 'selected' );
			$(el).addClass( 'selected' ).find( 'input[type="radio"]' ).prop( 'checked', true );
		}
	};
	
	$(document).ready(function(){cf7sSettings.init();});
})(jQuery);