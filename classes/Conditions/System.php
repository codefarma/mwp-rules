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
		
		$plugin->registerCondition( 'rules_truth', function() {
			return array(
				'title' => __( 'Truth condition check', 'mwp-rules' ),
				'description' => __( 'Checks if a value is equivalent to a boolean truth.', 'mwp-rules' ),
				'configuration' => array
				(
					'form' => function( $form, $values, $condition )
					{
						$compare_options = array(
							'true' 		=> 'Value is TRUE',
							'false'		=> 'Value is FALSE',
							'truthy'	=> 'Value is TRUE or equivalent to TRUE (any non-empty string/array, number not 0)',
							'falsey'	=> 'Value is FALSE or equivalent to FALSE (including NULL, 0, empty string/array)',
							'null'		=> 'Value is NULL',
							'notnull'	=> 'Value is NOT NULL',
						);
						
						$form->addField( 'compare_type', 'choice', array(
							'label' => __( 'Comparison Method', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['compare_type'] ) ? $values['compare_type'] : 'true',
						));
					},
				),
				'arguments'	=> array
				(
					'value' => array
					(
						'label' => __( 'Value to Compare', 'mwp-rules' ),
						'argtypes' => array(
							'mixed' => array( 'description' => 'the value to compare' ),
						),		
						'required'	=> FALSE,
						'configuration' => array
						(
							'form' => function( $form, $values, $condition ) 
							{
								$form->addField( 'compare_value', 'text', array(
									'label' => __( 'Value', 'mwp-rules' ),
									'data' => isset( $values['compare_value'] ) ? $values['compare_value'] : '',
								));
								
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
			);
		});

		$plugin->registerCondition( 'rules_execute_php', function() 
		{
			return array(
				'title' => __( 'Execute custom PHP code', 'mwp-rules' ),
				'description' => __( 'Run a custom block of php code.', 'mwp-rules' ),
				'configuration' => array(
					'form' => function( $form, $saved_values, $action ) {
						$form->addField( 'rules_custom_phpcode', 'textarea', array(
							'row_prefix' => '<hr>',
							'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $saved_values['rules_custom_phpcode'] ) ? $saved_values['rules_custom_phpcode'] : "// <?php\n\nreturn;",
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
			);
		});
	}	
}
