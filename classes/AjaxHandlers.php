<?php
/**
 * Plugin Class File
 *
 * Created:   January 5, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * AjaxHandlers Class
 */
class AjaxHandlers extends \Modern\Wordpress\Pattern\Singleton
{
	/**
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
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
	public function setPlugin( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Rules\Plugin::instance() );
	}
	
	/**
	 * Load available studio projects
	 *
	 * @Wordpress\AjaxHandler( action="mwp_rules_relocate_records", for={"users"} )
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
				and is_subclass_of( $recordClass, 'Modern\Wordpress\Pattern\ActiveRecord' ) 
				and isset( $recordClass::$sequence_col ) 
				and isset( $recordClass::$parent_col ) 
			) {
				$sequence_col = $recordClass::$sequence_col;
				$parent_col = $recordClass::$parent_col;
				
				$recursiveRelocate = function( $record, $index, $parent ) use ( $recordClass, $sequence_col, $parent_col, &$recursiveRelocate ) 
				{
					$_record = $recordClass::load( $record['id'] );
					$_record->$sequence_col = $index;
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
}
