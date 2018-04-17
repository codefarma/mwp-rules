<?php
/**
 * Plugin Class File
 *
 * Created:   December 5, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * General Class
 */
class _Content
{
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
	 * Register ECA's
	 * 
	 * @MWP\WordPress\Action( for="rules_register_ecas" )
	 * 
	 * @return	void
	 */
	public function registerECAs()
	{
		$plugin = $this->getPlugin();
		
		rules_register_conditions( array(
			
			/* Check if post type exists */
			array( 'rules_post_type_exists', array(
				'title' => 'Check If A Post Type Exists',
				'description' => 'Check if a particular post type has been registered.',
				'arguments' => array(
					'post_type' => array(
						'label' => 'Post Type',
						'default' => 'manual',
						'configuration' => $plugin->configPreset( 'text', 'rules_post_type', array( 'label' => 'Post Type Slug' ) ),
					),
				),
				'callback' => function( $post_type ) {
					return post_type_exists( $post_type );
				}
			)),
		
		));

	}
}
