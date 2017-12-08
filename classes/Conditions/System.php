<?php
/**
 * Plugin Class File
 *
 * Created:   December 5, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * System Class
 */
class System
{
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
	 * Register ECA's
	 * 
	 * @Wordpress\Action( for="rules_register_ecas" )
	 * 
	 * @return	void
	 */
	public function registerECAs()
	{
		$plugin = $this->getPlugin();
		
		$plugin->registerCondition( 'rules_truth', array
		(
			'title' => 'Check a truth condition',
			'configuration' => array
			(
				'form' => function( $form, $values, $condition )
				{
					$compare_options = array
					(
						'true' 		=> 'Value is TRUE',
						'false'		=> 'Value is FALSE',
						'truthy'	=> 'Value is TRUE or equivalent to TRUE (any non-empty string/array, number not 0)',
						'falsey'	=> 'Value is FALSE or equivalent to FALSE (including NULL, 0, empty string/array)',
						'null'		=> 'Value is NULL',
						'notnull'	=> 'Value is NOT NULL',
					);
					
					$form->add( new \IPS\Helpers\Form\Radio( 'compare_type', isset( $values[ 'compare_type' ] ) ? $values[ 'compare_type' ] : NULL, TRUE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'compare_type' ) );
				},
			),
			'arguments'	=> array
			(
				'value' => array
				(
					'argtypes' => array
					(
						'mixed' => array( 'description' => 'the value to compare' ),
					),		
					'required'	=> FALSE,
					'configuration' => array
					(
						'form' => function( $form, $values, $condition ) 
						{
							$form->add( new \IPS\Helpers\Form\Text( 'compare_value', isset( $values[ 'compare_value' ] ) ? $values[ 'compare_value' ] : NULL, TRUE, array(), NULL, NULL, NULL, 'compare_value' ) );
							return array( 'compare_value' );
						},
						'getArg' => function( $values, $condition )
						{
							return $values[ 'compare_value' ];
						},
					),
				),
			),
			'callback' 	=> function ( $value, $values ) {		
				switch ( $values[ 'compare_type' ] )
				{
					case 'true'    :	return $value === TRUE;
					case 'false'   :	return $value === FALSE;
					case 'truthy'  :	return (bool) $value;
					case 'falsey'  :	return ! ( (bool) $value );
					case 'null'    :	return $value === NULL;
					case 'notnull' :	return $value !== NULL;
					default        :	return FALSE;
				}
			},
		));

		$plugin->defineAction( 'rules_execute_php', array
		(
			'title' => 'Execute custom PHP code',
			'configuration' => array(
				'form' => function( $form, $saved_values, $action ) {
					// form add phpcode editor
				},
				'saveValues' => function( &$form_values, $action ) {
					
				},
			),
			'callback' => function( $saved_values, $arg_map, $operation ) 
			{
				$evaluate = function( $phpcode ) use ( $arg_map, $operation )
				{
					extract( $arg_map );								
					return @eval( $phpcode );
				};
				
				return $evaluate( $saved_values[ 'rules_custom_phpcode' ] );
			},
		));
	}	
}
