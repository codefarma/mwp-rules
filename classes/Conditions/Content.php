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
		
		$post_lang = 'Post';
		
		rules_register_conditions( array(
			
			/* Check if post type exists */
			array( 'rules_post_type_exists', array(
				'title' => 'Check If A Post Type Exists',
				'description' => 'Check if a particular post type has been registered.',
				'group' => $post_lang,
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
		
			/* Check if post has certain type */
			array( 'rules_post_has_type', array(
				'title' => 'Check Post Type',
				'description' => 'Check if a post is of a certain post type.',
				'group' => $post_lang,
				'arguments' => array(
					'post' => array(
						'label' => 'Post To Check',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post to check', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'post', array( 'label' => 'Post' ) ),
					),
					'post_types' => array(
						'label' => 'Post Types',
						'required' => false,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of post type names to check for' ),
							'object' => array( 'description' => 'A post type to check for', 'classes' => array( 'WP_Post_Type' ) ),
							'string' => array( 'description' => 'A post type name to check for' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$choices = array();
								foreach( get_post_types( [], 'objects' ) as $post_type ) {
									$choices[ $post_type->label ] = $post_type->name;
								}
								$form->addField( 'post_types', 'choice', array(
									'label' => __( 'Post Types', 'mwp-rules' ),
									'description' => __( 'Choose the post types you wish to count. Or leave blank to count all post types.', 'mwp-rules' ),
									'choices' => $choices,
									'expanded' => true,
									'multiple' => true,
									'data' => isset( $values['post_types'] ) ? $values['post_types'] : [],
									'required' => false,
								));
							},
							'getArg' => function( $values ) {
								return isset( $values['post_types'] ) ? $values['post_types'] : [];
							},
						),
					),
				),
				'callback' => function( $post, $post_types ) {
					
					if ( ! $post instanceof \WP_Post ) {
						throw new \ErrorException( 'An invalid post was provided' );
					}
					
					/* Ensure we have an array */
					if ( ! is_array( $post_types ) ) {
						$post_types = array( $post_types );
					}
					
					/* Standardize to an array of strings */
					$post_types = array_map( function( $t ) { return $t instanceof \WP_Post_Type ? $t->name : $t; }, $post_types );
					
					return in_array( $post->post_type, $post_types );
				}
			)),
			
			/* Check if post has certain status */
			array( 'rules_post_has_status', array(
				'title' => 'Check Post Status',
				'description' => 'Check if a post has a certain status.',
				'group' => $post_lang,
				'arguments' => array(
					'post' => array(
						'label' => 'Post To Check',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post to check', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'post', array( 'label' => 'Post' ) ),
					),
					'post_statuses' => array(
						'label' => 'Post Statuses',
						'required' => false,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of post statuses to check for' ),
							'object' => array( 'description' => 'A post status to check for' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$statuses = get_post_stati( [], 'objects' );
								$status_options = [];
								foreach( $statuses as $name => $status ) {
									$status_options[ $status->label ] = $status->name;
								}
								$form->addField( 'post_statuses', 'choice', array(
									'label' => __( 'Post Statuses', 'mwp-rules' ),
									'description' => __( 'Select the post statuses you wish to filter by. Leave blank for all statuses.', 'mwp-rules' ),
									'choices' => $status_options,
									'expanded' => true,
									'multiple' => true,
									'data' => isset( $values['post_statuses'] ) ? $values['post_statuses'] : [],
								));
							},
							'getArg' => function( $values ) {
								return isset( $values['post_statuses'] ) ? $values['post_statuses'] : [];
							},
						),
					),
				),
				'callback' => function( $post, $post_statuses ) {
					
					if ( ! $post instanceof \WP_Post ) {
						throw new \ErrorException( 'An invalid post was provided' );
					}
					
					/* Ensure we have an array */
					if ( ! is_array( $post_statuses ) ) {
						$post_statuses = array( $post_statuses );
					}
					
					/* Standardize to an array of strings */
					$post_statuses = array_map( function( $t ) { return is_object( $t ) ? $t->name : $t; }, $post_statuses );
					
					return in_array( $post->post_status, $post_statuses );
				}
			)),

		));

	}
}
