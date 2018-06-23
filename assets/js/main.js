/**
 * Plugin Javascript Module
 *
 * Created     December 4, 2017
 *
 * @package    MWP Rules
 * @author     Kevin Carwile
 * @since      0.0.0
 */

/**
 * Controller Design Pattern
 *
 * Note: This pattern has a dependency on the "mwp" script
 * i.e. @Wordpress\Script( deps={"mwp"} )
 */
(function( $, undefined ) {
	
	"use strict";

	/* Assign the controller instance to a global module variable when it is instantiated */
	var rulesController;
	mwp.on( 'mwp-rules.ready', function(c){ rulesController = c; } );
	
	/**
	 * Main Controller
	 *
	 * The init() function is called after the page is fully loaded.
	 *
	 * Data passed into your script from the server side is available
	 * by the mainController.local property inside your controller:
	 *
	 * > var ajaxurl = mainController.local.ajaxurl;
	 *
	 * The viewModel of your controller will be bound to any HTML structure
	 * which uses the data-view-model attribute and names this controller.
	 *
	 * Example:
	 *
	 * <div data-view-model="mwp-rules">
	 *   <span data-bind="text: title"></span>
	 * </div>
	 */
	mwp.controller.model( 'mwp-rules', 
	{
		
		/**
		 * Initialization function
		 *
		 * @return	void
		 */
		init: function()
		{
			this.setupEnabledToggles();
			
			// set the properties on your view model which can be observed by your html templates
			this.viewModel = {};
		},
		
		/**
		 * Setup the listener for toggling things enabled/disabled
		 *
		 * @return	void
		 */
		setupEnabledToggles: function()
		{
			$(document).on( 'click', '[data-rules-enabled-toggle]', function() {
				var el = $(this);
				
				if ( ! el.hasClass('working') ) {
					var type = el.data('rules-enabled-toggle');
					var id = el.data('rules-id');
					
					$.post( mwp.local.ajaxurl, {
						action: 'mwp_rules_toggle_enabled',
						type: type,
						id: id,
						nonce: mwp.local.ajaxnonce
					}).done( function( response ) {
						if ( response.success ) {
							if ( response.status ) {
								el.removeClass('label-danger').addClass('label-success').text('ENABLED');
							} else {
								el.removeClass('label-success').addClass('label-danger').text('DISABLED');
							}
						}
						el.removeClass('working');
					});
					
					el.addClass('working').html( '<i class="glyphicon glyphicon-refresh glyphicon-spin"></i> ' + el.text() );
				}
			});
		},
		
		/**
		 * Callback for when a condition is relocated
		 *
		 * @param	object			event			The event
		 * @param	object			ui				The jquery ui object
		 * @return	void
		 */
		relocateRecords: function( event, ui, sortableElement, config ) 
		{
			var sortableElement = ui.item.closest('.ui-sortable');
			var listOrder = sortableElement.nestedSortable( 'toHierarchy' );
			
			$.post( rulesController.local.ajaxurl, {
				nonce: rulesController.local.ajaxnonce,
				action: 'mwp_rules_relocate_records',
				class: config.class,
				sequence: listOrder
			});
		},
		
		/**
		 * Open a token browser modal
		 *
		 * @param	object		opts			Browser config options
		 * @param	object		params			Params to configure token requests
		 * @return	void
		 */
		openTokenBrowser: function( opts, params )
		{
			opts = opts || {};
			params = params || {};
			
			var browser = $('<div id="token-browser"></div>');
			var _params = $.extend( { action: 'mwp_rules_get_tokens', nonce: mwp.local.ajaxnonce }, params );
			var dialog = $( this.local.templates.token_browser );
			var selectCallback = opts.callback || function() { };
			
			dialog.find('.modal-body').append(browser);
			dialog.find('.modal-title').html( ( opts.title ? opts.title : 'Browse Data' ) + ( opts.target && opts.target.label ? ' For ' + opts.target.label : '' ) );
			dialog.find('[role="done"]').html( opts.done_label ? opts.done_label : 'Select' );
			dialog.find('[role="cancel"]').html( opts.cancel_label ? opts.cancel_label : 'Cancel' );
			dialog.modal();
			
			var tree = browser.jstree({
				core: {
					data: function( node, callback ) {
						var argument = typeof node.original !== 'undefined' ? node.original.argument : undefined;
						$.post( mwp.local.ajaxurl, $.extend( { argument: argument }, _params ) ).done( function( response ) {
							if ( response.success ) {
								callback( response.nodes );
							}
						});
					}
				},
				types: this.local.types,
				plugins: [ 'types' ]
			});
			
			var instance = tree.jstree(true);
			
			tree.on('loaded.jstree', function() {
				instance.activate_node( instance.get_selected() );
			});
			
			var getTokenStack = function( node, tokens ) {
				tokens = tokens || [];
				if ( node.original && node.original.token ) { tokens.unshift( node.original.token ); }
				if ( node.parent ) { getTokenStack( tree.jstree(true).get_node( node.parent ), tokens ); }
				return tokens;
			};
			
			tree.on( 'activate_node.jstree', function( e, data ) {
				var selectEnabled = true;
				var tokenPath;
				
				if ( data.node.original ) {
					var tokens = getTokenStack( data.node );
					tokenPath = tokens.join(':');
					if ( opts.wrap_tokens ) {
						tokenPath = '{{' + tokenPath + '}}';
					}
					if ( data.node.original.selectable !== true ) {
						data.instance.deselect_node( data.node );
						selectEnabled = false;
						tokenPath = '';
					} 
				}
				
				dialog.find('[role="done"]').prop( 'disabled', ! selectEnabled );
				dialog.find('[role="token-path"]').val( tokenPath );
			});
			
			dialog.on( 'click', '[role="done"]', function() {
				var node = instance.get_node( instance.get_selected() );
				var tokens = getTokenStack( node );
				if ( selectCallback( node, tokens, tree, dialog ) !== false ) {
					dialog.modal('hide');
				}
			});
		}
	
	});
	
	/**
	 * Add forms related knockout bindings
	 *
	 */
	$.extend( ko.bindingHandlers, 
	{
		codemirror: {
			init: function( element, valueAccessor ) {
				if ( typeof CodeMirror !== 'undefined' ) {
					var options = ko.unwrap( valueAccessor() );
					var editor = CodeMirror.fromTextArea( element, options );
					$(element).data('codemirror', editor).attr('data-role', 'codemirror');
				}
			}
		},
		
		nestableRecords: {
			init: function( element, valueAccessor ) {
				var config = ko.unwrap( valueAccessor() );
				if ( typeof $.fn.nestedSortable !== 'undefined' ) 
				{
					var sortableElement = config.find ? $(element).find(config.find) : $(element);
					var options = $.extend( {
						placeholder: 'mwp-sortable-placeholder'
					}, config.options || {} );
					
					var updateCallback = config.callback || function( event, ui, sortableElement, config ) {
						rulesController.relocateRecords( event, ui, sortableElement, config );
					};
					
					try {
						sortableElement.nestedSortable( options );
						sortableElement.on( 'sortrelocate', function( event, ui ) {
							if ( typeof updateCallback === 'function' ) {
								updateCallback( event, ui, sortableElement, config );
							}
						});
					}
					catch(e) {
						console.log( e );
					}
				}
			}
		},
		
		tokenSelector: {
			init: function( element, valueAccessor ) {
				var config = ko.unwrap( valueAccessor() );
				var el = $(element);
				el.on( 'click', function() {
					if ( typeof config.callback == 'function' ) {
						config.options.callback = config.callback.bind( element );
					}
					var input = el.closest('.form-group.row').find('input.selectized').eq(0);
					if ( input.length ) { config.params.selected = input.val(); }
					rulesController.openTokenBrowser( config.options, config.params );
				});
			}
		}
		
	});
	
	/* jQuery Plugin */
	$.fn.extend({
		insertAtCaret: function(myValue){
			return this.each(function(i) {
				if (document.selection) {
					//For browsers like Internet Explorer
					this.focus();
					var sel = document.selection.createRange();
					sel.text = myValue;
					this.focus();
				}
				else if (this.selectionStart || this.selectionStart == '0') {
					//For browsers like Firefox and Webkit based
					var startPos = this.selectionStart;
					var endPos = this.selectionEnd;
					var scrollTop = this.scrollTop;
					this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
					this.focus();
					this.selectionStart = startPos + myValue.length;
					this.selectionEnd = startPos + myValue.length;
					this.scrollTop = scrollTop;
				} else {
					this.value += myValue;
					this.focus();
				}
			});
		}
	});	
	
	function refresh_codemirrors( scope ) {
		scope.find('[data-role="codemirror"]').each(function() {
			var element = $(this);
			var codemirror = element.data('codemirror');
			if ( codemirror ) {
				if ( $(codemirror.getWrapperElement()).is(':visible') ) {
					codemirror.refresh();
				}
			}
		});		
	}
	
	/* Refresh codemirror widgets when tabs are switched */
	$(document).on('shown.bs.tab', function(e) {
		var tab = $(e.target);
		var tab_content = $(tab.attr('href'));
		refresh_codemirrors( tab_content );
	});
	
	/* Refresh codemirror widgets when toggles are shown */
	mwp.on( 'forms.toggle.shown', function( selector ) {
		var selections = $(selector);
		refresh_codemirrors( selections );
	});
	
})( jQuery );
 