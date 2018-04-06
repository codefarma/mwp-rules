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
			// ajax actions can be made to the ajaxurl, which is automatically provided to your controller
			var ajaxurl = this.local.ajaxurl;
			
			// set the properties on your view model which can be observed by your html templates
			this.viewModel = {};
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
			init: function( element, valueAccessor ) 
			{
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
 