<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\Events;

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
		
		$plugin->describeEvent( 'filter', 'test_it', array(
			'title' => 'A Test Value is Filtered',
			'description' => 'The test filter is used to provide an event for testing filtering rules.',
			'arguments' => array(
				'testing_arg' => array(
					'description' => 'The arbitrary value being filtered',
					'argtype' => 'mixed',
					'nullable' => true,
				),
			),
		));
		
		$plugin->describeEvent( 'action', 'init', function() {
			return array(
				'title' => __( 'Wordpress is Initialized', 'mwp-rules' ),
				'description' => __( 'The wordpress init hook is fired after all plugins have been loaded.', 'mwp-rules' ),
			);
		});
	}
}
