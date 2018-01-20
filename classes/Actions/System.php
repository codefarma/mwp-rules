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
		
		rules_define_actions( array(
			
			/* Execute Custom PHP Code */
			array( 'rules_execute_php', array(
				'title' => 'Execute custom PHP code',
				'description' => 'Run a custom block of php code.',
				'configuration' => array(
					'form' => function( $form, $saved_values, $operation ) use ( $plugin ) {
						$form->addField( 'rules_custom_phpcode', 'textarea', array(
							'row_prefix' => '<hr>',
							'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $saved_values['rules_custom_phpcode'] ) ? $saved_values['rules_custom_phpcode'] : "// <?php\n\nreturn;",
							'description' => $plugin->getTemplateContent( 'rules/phpcode_description', array( 'operation' => $operation, 'event' => $operation->event() ) ),
						));
					}
				),
				'callback' => function( $saved_values, $event_args, $operation ) {
					$evaluate = function( $phpcode ) use ( $event_args, $operation ) {
						extract( $event_args );
						return @eval( $phpcode );
					};
					
					return $evaluate( $saved_values[ 'rules_custom_phpcode' ] );
				},
			)),
			
			/* Modify the value being filtered */
			array( 'rules_modify_filtered_value', array(
				'title' => 'Modify the filtered value',
				'description' => 'Change the value being filtered in a hook.',
				'updates_filter' => true,
				'arguments' => array(
					'new_value' => array(
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'mixed' => array( 'description' => 'The new value' ),
						),
						'configuration' => array(
							'form' => function( $form, $saved_values, $operation ) {
								
							}
						),
					),
				),
				'callback' => function( $new_value, $saved_values, $event_args, $operation ) {
					$operation->rule()->setReturnValue( $new_value );
					return $new_value;
				}
			)),
			
		));
		
	}
}
