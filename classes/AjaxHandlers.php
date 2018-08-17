<?php
/**
 * Plugin Class File
 *
 * Created:   January 5, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Framework;

/**
 * AjaxHandlers Class
 */
class _AjaxHandlers extends \MWP\Framework\Pattern\Singleton
{
	/**
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var 	\MWP\Framework\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\MWP\Framework\Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\MWP\Framework\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Rules\Plugin::instance() );
	}
	
	/**
	 * Relocate nestable records
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_rules_relocate_records", for={"users"} )
	 *
	 * @return	void
	 */
	public function resequenceRecords()
	{
		check_ajax_referer( 'mwp-ajax-nonce', 'nonce' );
		
		if ( current_user_can( 'administrator' ) ) 
		{
			$recordClass = wp_unslash( $_POST['class'] );
			
			if ( 
				class_exists( $recordClass ) 
				and is_subclass_of( $recordClass, 'MWP\Framework\Pattern\ActiveRecord' ) 
				and isset( $recordClass::$sequence_col ) 
				and isset( $recordClass::$parent_col ) 
			) {
				$sequence_col = $recordClass::$sequence_col;
				$parent_col = $recordClass::$parent_col;
				
				$recursiveRelocate = function( $record, $index, $parent ) use ( $recordClass, $sequence_col, $parent_col, &$recursiveRelocate ) 
				{
					$_record = $recordClass::load( $record['id'] );
					$_record->$sequence_col = $index + 1;
					$_record->$parent_col = $parent;
					$_record->save();
					$_record->flush();
					unset( $_record );
					if ( isset( $record['children'] ) ) {
						foreach( $record['children'] as $_index => $_child ) {
							$recursiveRelocate( $_child, $_index, $record['id'] );
						}
					}

				};
				
				foreach( $_POST['sequence'] as $index => $record ) {
					$recursiveRelocate( $record, $index, 0 );
				}
				
				wp_send_json( array( 'success' => true ) );
			}
		}
	}
	
	/**
	 * Enable/Disable Things
	 * 
	 * @MWP\WordPress\AjaxHandler( action="mwp_rules_toggle_enabled", for={"users"} )
	 * 
	 * @return	void
	 */
	public function toggleEnabled()
	{
		check_ajax_referer( 'mwp-ajax-nonce', 'nonce' );
		
		if ( current_user_can( 'administrator' ) ) 
		{
			$type    = $_REQUEST['type'];
			$type_id = $_REQUEST['id'];
			
			$classes = array(
				'app'       => 'MWP\Rules\App',
				'bundle'    => 'MWP\Rules\Bundle',
				'rule'      => 'MWP\Rules\Rule',
				'condition' => 'MWP\Rules\Condition',
				'action'    => 'MWP\Rules\Action',
			);
			
			if ( isset( $classes[ $type ] ) ) {
				$class = $classes[ $type ];
				try {
					$record = $class::load( $type_id );
					$record->enabled = ( $record->enabled ? 0 : 1 );
					$record->save();
					
					wp_send_json( array( 'success' => true, 'status' => $record->enabled ) );
				} 
				catch( \OutOfRangeException $e ) {
					wp_send_json( array( 'success' => false, 'message' => 'Record not found.' ) );
				}
			}
		}
		
		wp_send_json( array( 'success' => false, 'message' => 'Invalid request.' ) );
	}
	
	/**
	 * Load a set of available tokens
	 *
	 * @MWP\WordPress\AjaxHandler( action="mwp_rules_get_tokens", for={"users"} )
	 *
	 * @return	void
	 */
	public function getTokens()
	{
		check_ajax_referer( 'mwp-ajax-nonce', 'nonce' );
		
		$plugin = $this->getPlugin();
		$request = Framework::instance()->getRequest();
		$self = $this;
		
		/* Get derivative arguments when drilling down */
		if ( $argument = $request->get('argument') ) 
		{
			$nodes = array();
			if ( $derivatives = $plugin->getDerivativeTokens( $argument, NULL, 1, TRUE ) ) {
				$nodes = array_map( array( $this, 'getNode' ), array_keys( $derivatives ), $derivatives );
			}
			
			wp_send_json( array( 
				'success' => true, 
				'nodes' => $nodes,
			));
		}
		else 
		{
			$bundle = NULL;
			$event = NULL;
			$nodes = [];
			
			/* Event Tokens */
			if ( $event_type = $request->get('event_type') and $event_hook = $request->get('event_hook') ) {
				if ( $event = $plugin->getEvent( $event_type, $event_hook ) ) {
					if ( $event->arguments ) {
						$arguments = $plugin->getExpandedArguments( $event->arguments );
						$nodes[] = [
							'text' => 'Event Data',
							'selectable' => false,
							'token' => 'event',
							'state' => [ 'opened' => true ],
							'children' => array_map( array( $this, 'getNode' ), array_keys( $arguments ), $arguments ),
						];
					}
				}
			}
			
			/* Bundle Tokens */
			if ( $bundle_id = $request->get('bundle_id') ) {
				try { 
					$bundle = Bundle::load( $bundle_id );
					$nodes[] = [
						'text' => 'Bundle Data',
						'selectable' => false,
						'token' => 'bundle',
						'state' => [ 'opened' => true ],
						'children' => array_map( array( $this, 'getNode' ), array_column( $bundle->getArguments(), 'varname' ), array_map( function($a) { return $a->getProvidesDefinition(); }, $bundle->getArguments() ) ),
					];
				}
				catch( \OutOfRangeException $e ) { }
			}
			
			/* Global Tokens */
			$nodes[] = [ 
				'text' => 'Global Data',
				'selectable' => false,
				'token' => 'global',
				'state' => [ 'opened' => true ],
				'children' => array_map( array( $this, 'getNode' ), array_keys( $plugin->getGlobalArguments() ), $plugin->getGlobalArguments() ),
			];
			
			/* Load heirarchy for a pre-selected token path if possible */
			if ( $selected = $request->get('selected') ) {
				$tokens = explode( ':', $selected );
				$arg_group = array_shift( $tokens );
				
				/* Prepare to walk */
				foreach( $nodes as &$group ) {
					if ( $group['token'] == $arg_group ) {
						$_nodes = &$group['children'];
						break;
					}
				}
				
				while( $token = array_shift( $tokens ) ) {
					foreach( $_nodes as &$node ) {
						if ( $node['token'] == $token ) {
							$derivatives = $plugin->getDerivativeTokens( $node['argument'], NULL, 1, TRUE );
							$node['children'] = array_map( array( $this, 'getNode' ), array_keys( $derivatives ), $derivatives );
							$node['state']['opened'] = true;
							$node['state']['selected'] = empty( $tokens );
							$_nodes = &$node['children'];
							break;
						}
					}
				}
			}
			
			wp_send_json( array(
				'success' => true,
				'nodes' => $nodes,
			));
		}
		
		wp_send_json( array( 'success' => false ) );
	}
	
	/**
	 * Get a node for the tree json
	 *
	 * @param	string		$arg_name			The argument token key
	 * @param	array		$argument			The argument details
	 * @return	array
	 */
	public function getNode( $arg_name, $argument )
	{
		$request = Framework::instance()->getRequest();
		$target = $request->get('target');
		$plugin = $this->getPlugin();
		$compliant = $plugin->isArgumentCompliant( $argument, $target );
		
		/* Unset complex data */
		unset( $argument['getter'] );
		unset( $argument['keys'] );
		
		$node = [ 
			'text' => $arg_name, 
			'argument' => $argument,
			'token' => $arg_name,
			'type' => $argument['argtype'],
			'children' => (bool) $plugin->getDerivativeTokens( $argument, NULL, 1, TRUE ),
			'selectable' => $compliant,
			'a_attr' => [ 
				'class' => $compliant ? 'selectable' : 'unselectable', 
				'title' => ( isset( $argument['label'] ) ? $argument['label'] . ' ' : '' ) 
					. '(' . $argument['argtype'] . ')' 
					. ( isset( $argument['class'] ) && $argument['class'] ? '[' . $argument['class'] . ']' : '' )
			],
		];
		
		return $node;		
	}
}
