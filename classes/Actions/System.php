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
namespace MWP\Rules\Actions;

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
		
		$plugin->defineAction( 'rules_execute_php', array
		(
			'title' => __( 'Execute custom PHP code', 'mwp-rules' ),
			'configuration' => array(
				'form' => function( $form, $saved_values, $action ) {
					// form add phpcode editor
				},
				'saveValues' => function( &$form_values, $action ) {
					
				},
			),
			'callback' => function( $saved_values, $event_args, $operation ) {
				$evaluate = function( $phpcode ) use ( $event_args, $operation )
				{
					extract( $event_args );
					return @eval( $phpcode );
				};
				
				return $evaluate( $saved_values[ 'rules_custom_phpcode' ] );
			},
		));
		
		$plugin->defineAction( 'rules_modify_filtered_value', array
		(
			'title' => __( 'Modify the filtered value' ),
			'description' => __( 'Change the value being filtered in a wordpress filter hook.', 'mwp-rules' ),
			'arguments' => array(
				'new_value' => array(
					'required' => true,
					'default' => 'manual',
					'argtypes' => array(
						'mixed' => array( 'description' => 'the new filtered value' ),
					),
					'configuration' => array(
						'form' => function( $form, $saved_values ) {
							
						}
					),
				),
			),
			'callback' => function( $new_value, $saved_values, $event_args, $operation ) {
				$rule = $operation->rule();
				$rule->filtered_values[ $rule->event()->thread ] = $new_value;
				return 'filter value changed';
			}
		));
	}
}
