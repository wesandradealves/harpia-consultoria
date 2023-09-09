/**
 * CSS Properties based on WP versions
 * WP-3.9            				|  WP4.0
 * -----------------------------------------------
 * a.more-filters    				|  a.drawer-toggle
 * div.filtering-by    				|  div.filtered-by
**/
			
(function($) {
	
	var l10n = cf7s.l10n; // translation
	
	cf7sAdmin = {
		init : function( url ) {
			var t = this, post_id;
			this.post_id = $("#post_ID").val();
			
			// Check if hidden input for template/style is inside the form			
			if ( $("#cf7s-template").parents("#wpcf7-admin-form-element").length == 1 ) { 
				// input is inside form
			} else {
				$("#wpcf7-admin-form-element").append( $("#cf7s-template") );
				$("#wpcf7-admin-form-element").append( $("#cf7s-style") );
				$("#wpcf7-admin-form-element").append( $(".cf7s-postbox") );
			}
			
			$("#cf7s .nav-tab:nth-child(1)").addClass("nav-tab-active");
			$("#cf7s .nav-tab-content > div:nth-child(1)").addClass("tab-active");
			$("#cf7s .nav-tab").click( function(e) {
				t.tab(this); return false;
			});
			$( "#cf7s" ).on( "click", "a.select", function() {
				t.select(this); return false;
			});
			$( "#cf7s" ).on( "mouseenter mouseleave", "a.select", function(m) {
				t.selectHover(m,this);
			});
			$( "#cf7s" ).on( "click", "a.selected", function() {
				t.deselect(this); return false;
			});
			$( "#cf7s" ).on( "click", "a.detail", function() {
				t.details(this); return false;
			});
			$( "#cf7s" ).on( "click", "a.close", function() {
				t.close(this); return false;
			});
			$("#cf7s a.view").click( function() {
				t.view(this); return false;
			});
			$("#cf7s a.skin-sort").click( function() {
				t.sort(this); return false;
			});			
			$("#cf7s a.more-filters").add("#cf7s a.drawer-toggle").click( function() {
				t.filters(this); return false;
			});
			$("#cf7s input[type='checkbox']").click( function() {
				t.addFilter(this);
			});
			$("#cf7s .clear-filters").add("#cf7s a.drawer-toggle").add("#cf7s a.more-filters").click( function(e) {
				t.clearFilters(e);
			});
			$("#cf7s .apply-filters").click( function(e) {
				t.applyFilters(e);
			});
			$("#cf7s .filtered-by a").click( function(e) {
				t.backToFilters(e);
			});
			$("#cf7s .skins-search").keyup( function() {
				t.skinsSearch(this); 
			});
			$("#cf7s .skins-search").keypress( function(event) {
				if ( event.keyCode == 13 )
					event.preventDefault();
			});
			
			// Skin ordering for template and style tab
			$("#cf7s .skins-sort .dashicons").on( "click", function(e) {
				t.orderSkin(e);
			});
			$("#cf7s select.sort-by").change( function(e) {
				var tab = $(this).closest(".tab-content");
				$(".dashicons", tab).trigger( "click" );
			});
			
			// Check if any changes have been made
			var formmodified = false;
			var submitted = false;
 
			$('#wpcf7-form, #cf7s-style, #cf7s-template').change(function(){
				$('#message').slideToggle( "slow", function() {
					$(this).remove();
				});			
				
				formmodified = true;
			});
			
			// Set new variable value when submitting form
			$("form").submit(function() {
				submitted = true;
			});			
			
			// Display saving notification
			window.onbeforeunload = confirmExit;
			function confirmExit() {
				if ( formmodified && ! submitted ) {					
					return 'Changes have been made, are you sure you want to leave?';
				}
			}
			
			// Expand collapse skin box for CF7 >= 4.2
			$('.cf7skins-metabox  .handlediv').click( function(e) {
				e.stopPropagation();
				var postbox = $(this).closest('.postbox');
				
				postbox.toggleClass('closed');
			});
		},
		
		tab : function(e) {
			var id = $(e).attr("href");
			$(e).siblings("a").removeClass("nav-tab-active");
			$(e).addClass("nav-tab-active");
			$(".nav-tab-content > div").removeClass("tab-active");
			$(id).addClass("tab-active");
		},
		
		getTextarea : function() {
			// Default contact form editor id is #wpcf7-form
			if ( ! cf7s.textarea ) { // @since 2.1 vis-0.6.4
				cf7s.textarea = document.getElementById( "wpcf7-form" );
			}
			
			// Returns HTML DOM not a jQuery object,
			// can be use with jQuery, $( cf7sAdmin.getTextarea() ).
			return cf7s.textarea;
		},
		
		select : function(e) {		
			var inp, pos, wrap, details, skin, textarea, name, title, deselect_tip, isDeselect;
			skin = $(e).attr("data-value"),
			inp = $(e).attr("href"); // this is the hidden input for storing selected template/style
			wrap = $(e).closest(".tab-content");
			$(inp).val(skin).trigger('change'); // update the skin hidden input value and trigger change for saving
			details = $(e).closest(".details");
			name = $(e).closest(".skin").find(".skin-name").text();
			title =  $(e).attr("data-title"); // get initial tooltip text
			isDeselect = $(e).hasClass("selected"); // @since 2.4.5

			textarea = this.getTextarea(); // updated @since 2.1 vis-0.6.4			
			
			// Remove and add highlight to the selected skin
			$(".skin", wrap).removeClass("skin-selected");
			//$(e).closest(".skin").addClass("skin-selected");
			$('a[data-value="'+skin+'"]').closest(".skin").addClass("skin-selected");
			
			// Remove link decoration and change text
			$("a.select", wrap).removeClass("selected").text( l10n.select ); // remove all selected class
			
			$("a.select", wrap).attr( "data-balloon", title ); // set all to initial tooltip text
			
			$('a.select[data-value="'+skin+'"]', wrap).addClass("selected").text( l10n.selected ); // add selected class to tab content
			$('a.select[data-value="'+skin+'"]', details).addClass("selected").text( l10n.selected ); // add selected class to details content
			
			deselect_tip = $(e).attr("href").indexOf( "template" ) != -1 ? l10n.deselect_template : l10n.deselect_style; // get deselect tip text for style or template
			$(e).attr( "data-balloon", deselect_tip ); // set all to initial tooltip text
			
			// Update selected skin info
			if ( inp.indexOf( "template" ) != -1 )
				$(".selected-template > span").text(name);
			else
				$(".selected-style > span").text(name);	

			// Only for template
			if ( inp.indexOf( "template" ) != -1 && ! isDeselect ) { 
				
				// Get the CF7 content position and animate to top
				// pos = $("#wpcf7-form").position();
				// $("body, html").animate({ scrollTop: pos.top }, 800 );
				
				$(textarea).val( l10n.loading );
				
				$.post( ajaxurl, { 
					action: cf7s.load, 					
					template: skin, 
					post: $(e).attr("data-post"), 
					locale: $(e).attr("data-locale"), 
					nonce: cf7s.nonce 
				}, function( data ) {					
					$(textarea).val( data ).trigger('change');
					$('body').trigger( 'select-template', [{template:skin,post:$(e).attr("data-post")}] );
				});
			}
		},
		
		selectHover : function(m,e) {	
			if ( ! $(e).hasClass("selected") ) // only for selected
				return;
			
			var select_text, deselect_text;			
			select_tip = $(e).attr("data-balloon"); // save original select text					
			deselect_tip = $(e).attr("href").indexOf( "template" ) != -1 ? l10n.deselect_template : l10n.deselect_style; // get deselect tip text for style or template
			
			if( m.type == "mouseenter" ) {
				$(e).attr("data-balloon", deselect_tip); // replace select tooltip with deselect text
				$(e).text(l10n.deselect); // replace Select text with Deselect		
			} else {
				$(e).attr("data-balloon", select_tip); // replace deselect tooltip with select text
				$(e).text(l10n.selected); // replace Deselect text with Select
			}
		},
		
		deselect : function(e) {
			var inp, skin, wrap;
			
			inp = $(e).attr("href"); // this is the hidden input for storing selected template/style
			skin = $(e).attr("data-value"); // get template/style name slug
			wrap = $(e).closest(".tab-content"); // get current template/style tab content			
			$(".skin", wrap).removeClass("skin-selected"); // remove all class 'skin-selected'
			
			$(".selected", wrap).text( l10n.select ); // change all a.selected text in tab-content to 'select' 
			$(".selected", wrap).removeClass( "selected" ); // remove all 'selected' class in tab-content
			
			$(inp).val("").trigger('change'); // empty the skin hidden input value and trigger change for saving
			
			$(e).attr( "data-balloon", $(e).attr( "data-title" ) ); // back to original tooltip
			
			// Update selected skin info
			if ( inp.indexOf( "template" ) != -1 )
				$(".selected-template > span").text("");
			else
				$(".selected-style > span").text("");				
		},
		
		details : function(e) {
			var id = $(e).attr("href");
			$(e).closest(".tab-content").find(".skin-details").show();
			$(e).closest(".tab-content").find(".skin-list").hide();
			$(id).removeClass("hidden");
		},
		
		close : function(e) {
			$(".details").addClass("hidden");
			$(".skin-list").show();
			$(".skin-details").hide();
		},
		
		view : function(e) {
			var inp, pos, wrap;
			skin = $(e).attr("data-value"),
			wrap = $(e).closest(".details");
			
			if( l10n.expanded == $(e).text() ) {			
				$(".expanded-view", wrap).show();
				$(".details-view", wrap).hide();
			} else {
				$(".expanded-view", wrap).hide();
				$(".details-view", wrap).show();
			}
		},

		sort : function(e) {
			if( cf7sAdmin.load )
				return false;
			
			cf7sAdmin.load = true;
			
			var tab = $(e).closest(".tab-content");
			
			// add current class
			$(".skin-sort", tab).removeClass("current");
			$( tab ).removeClass( 'filters-applied more-filters-opened' );
			$(e).closest(".tab-content").find(".skin-list").show();
			$(e).addClass("current");
			
			// empty tab content
			$(".skin-list .skin", tab).remove();
			$(".skin-list .spinner", tab).show();
			$(".skin-list .no-skins", tab).remove();	

			// hide current if detailed/expanded view if visible
			$(".skin-details", tab).hide(); 	
			$(".skin-details .details", tab).addClass("hidden"); 	
			
			// sort it
			$.post( ajaxurl, { 
				action: cf7s.sort, 					
				tab: $(tab).attr("id"), 					
				sort: $(e).attr( "data-sort" ),
				id: cf7sAdmin.post_id,
				nonce: cf7s.nonce 
			}, function( data ) {
				$(".skin-list .spinner", tab).hide();				
				$(".skin-list", tab).append(data);
				$(".dashicons", tab).trigger( "click" );
				$(".theme-count", tab).text( $(e).closest(".tab-content").find(".skin").length );
				cf7sAdmin.load = false;
			});
		},
		
		filters : function(e) {
			var activetab = $(e).closest(".tab-content");
			if ( $( activetab ).hasClass( 'filters-applied' ) ) {
				return this.backToFilters();
			}
			if ( $( activetab ).hasClass( 'more-filters-opened' ) && this.filtersChecked() ) {
				return this.addFilter();
			}
			$( activetab ).toggleClass( 'more-filters-opened' );
			$(activetab).find(".skin-list").toggle();
		},
			
		// Clicking on a checkbox to add another filter to the request
		addFilter: function() {
			this.filtersChecked();
		},		
			
		// Applying filters triggers a tag request
		applyFilters: function( event ) {
			var tab = $(event.currentTarget).closest(".tab-content"),
			tabID = $(tab).attr("id");

			var name,
				tags = this.filtersChecked(),
				request = { tag: tags },
				filteringBy = $( '.filtered-by .tags', tab );

			if ( event ) {
				event.preventDefault();
			}
			
			if( ! tags ) {
				alert( l10n.emptyfilter );
				return;
			}

			$( 'body' ).addClass( 'filters-applied' );
			$( '.theme-section.current' ).removeClass( 'current' );
			filteringBy.empty();

			_.each( tags, function( tag ) {
				name = $( 'label[for="' + tabID + '-' + tag + '"]' ).text();
				filteringBy.append( '<span class="tag">' + name + '</span>' );
			});

			$(".skin-list .skin", tab).remove();
			$(".skin-list .no-skins", tab).remove();
			$(".skin-list .spinner", tab).show();
			
			if( tags )
				$.post( ajaxurl, { 
					action: cf7s.sort, 					
					tab: $(tab).attr("id"), 					
					sort: "tag",
					tags: tags,
					nonce: cf7s.nonce 
				}, function( data ) {		
					$(".skin-list .spinner", tab).hide();
					$(".skin-list", tab).append(data).show();
					$(".theme-count", tab).text( $(tab).find(".skin").length );
					cf7sAdmin.load = false;
				});
		},		
		
		filtersChecked: function() {
			var items = $( '.feature-group' ).find( ':checkbox' ),
				tags = [];

			_.each( items.filter( ':checked' ), function( item ) {
				tags.push( $( item ).prop( 'value' ) );
			});

			// When no filters are checked, restore initial state and return
			if ( tags.length === 0 ) {
				$( '.apply-filters' ).find( 'span' ).text( '' );
				$( '.clear-filters' ).hide();
				$( 'body' ).removeClass( 'filters-applied' );
				return false;
			}

			$( '.apply-filters' ).find( 'span' ).text( tags.length );
			$( '.clear-filters' ).css( 'display', 'inline-block' );
			
			return tags;
		},
		
		clearFilters: function( event ) {
			var items = $( '.feature-group' ).find( ':checkbox' ),
				self = this;

			event.preventDefault();

			_.each( items.filter( ':checked' ), function( item ) {
				$( item ).prop( 'checked', false );
				return self.filtersChecked();
			});
		},

		backToFilters: function( event ) {
			if ( event ) {
				event.preventDefault();
			}

			$( 'body' ).removeClass( 'filters-applied' );
		},
			
		skinsSearch: function(e) {
			if( cf7sAdmin.load || $(e).val().length < 3 ) 
				return;
				
			var tab;
			tab = $(e).closest(".tab-content");	
		
			$(".skin-list .skin", tab).remove();
			$(".skin-list .spinner", tab).show();
			$(".skin-list .no-skins", tab).remove();
			
			clearTimeout( $.data( e, "cf7sadmin" ) );
			
			var wait = setTimeout( function() {					
				$.post( ajaxurl, { 
					action: cf7s.sort, 					
					tab: $(tab).attr("id"), 					
					sort: "search",
					id: cf7sAdmin.post_id,
					keyword: $(e).val(),
					nonce: cf7s.nonce 
				}, function( data ) {					
					$(".skin-list", tab).append(data);
					$(".skin-list .spinner", tab).hide();
					$(".theme-count", tab).text( $(e).closest(".tab-content").find(".skin").length );
					cf7sAdmin.load = false;
				});				
			}, 750);
			
			$(e).data( "cf7sadmin", wait );		
		},
		
		orderSkin : function(e) {
			var icon, tab, skinlist, sortby, skins, attrs;
			
			icon = $(e.currentTarget);
			
			// stackoverflow.com/questions/6674669/in-jquery-how-can-i-tell-between-a-programatic-and-user-click#answer-6674806
			if( e.hasOwnProperty('originalEvent') ) {
				if( icon.hasClass("dashicons-arrow-up-alt") )
					icon.removeClass("dashicons-arrow-up-alt").addClass("dashicons-arrow-down-alt");
				else
					icon.removeClass("dashicons-arrow-down-alt").addClass("dashicons-arrow-up-alt");
			}
				
			tab = icon.closest(".tab-content");
			skinlist = $(tab).find(".skin-list");
			sortby = $(tab).find("select.sort-by").val();
			
			skins = [];			
			skinlist.children('.skin').each( function(i,e) {
				skins.push( $(e).attr("data-"+sortby) );
			});
						
			skins.sort();
			
			if( icon.hasClass("dashicons-arrow-up-alt") )
				skins.reverse();
				
			console.log( "["+sortby+"] "+skins );  // Use to inspect sort in console.log
			
			$.each( skins, function(i,e){
				skinlist.append( $(".skin[data-"+sortby+"='"+e+"']", skinlist) );
			});
		},
		
		updateMailtags : function() {
			// Form data for AJAX post
			var postData = {};
			postData.nonce = cf7s.nonce;
			postData.action = 'cf7s_update_mailtags';			
			postData.post_ID = document.getElementById( 'post_ID' ).value;
			postData.form = document.getElementById( 'wpcf7-form' ).value;

			// AJAX post with JSON output to replace mailtags
			$.post( ajaxurl, postData, function( data ) {							
				
				let wpcf7Mail = document.getElementById('wpcf7-mail'); // get wpcf7-mail element
				let legend = wpcf7Mail.getElementsByTagName('legend')[0]; // find legend
				let mailtags = legend.getElementsByClassName('mailtag'); // find element with mailtag class
				for ( let i = mailtags.length - 1; i >= 0; i-- ) { 
					legend.removeChild( mailtags[i] ); // remove all mailtags
				}
				legend.insertAdjacentHTML( 'beforeend', data ); // insert new mailtags
			}, "json" );
		},
	};
	
	$(document).ready(function(){
		cf7sAdmin.init();
	});
})(jQuery);