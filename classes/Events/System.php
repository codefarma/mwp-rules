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
		rules_describe_events( array(
			
			/* Pre setup theme */
			array( 'action', 'setup_theme', array(
				'title' => 'Pre Setup Theme',
				'description' => 'Fires before the theme is loaded.',
			)),
			
			/* Post setup theme */
			array( 'action', 'after_setup_theme', array(
				'title' => 'Post Setup Theme',
				'description' => 'Fires after the theme is loaded.',
			)),
			
			/* Init */
			array( 'action', 'init', array(
				'title' => 'Wordpress Initialized',
				'description' => 'The wordpress init hook is fired just after all plugins have been loaded.',
			)),
			
			/* Environment Loaded */
			array( 'action', 'wp', array( 
				'title' => 'Wordpress Environment Ready',
				'description' => 'Fires once the WordPress environment and page query has been set up.',
				'arguments' => array(
					'wp' => array(
						'argtype' => 'object',
						'class' => 'WP',
					)
				)
			)),
			
			/* Wordpress Loaded */
			array( 'action', 'wp_loaded', array( 
				'title' => 'Wordpress Loaded',
				'description' => 'This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.',
			)),
			
			/* Template Redirect */
			array( 'action', 'template_redirect', array(
				'title' => 'Template Ready',
				'description' => 'The template_redirect hook runs just before determining which page template to load.',
			)),
			
			/* Wordpress Shutdown */
			array( 'action', 'shutdown', array( 
				'title' => 'Wordpress Shutdown',
				'description' => 'Fires just before PHP shuts down execution.',
			)),
			
		));
	}
}
