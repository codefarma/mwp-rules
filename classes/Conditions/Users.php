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
 * Users Class
 */
class _Users
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
		
			/* User Exists */
			array( 'rules_user_exists', array(
				'title' => 'Check If A User Exists',
				'description' => 'Check if a given user exists in the database.',
				'arguments' => array(
					'users' => array(
						'label' => 'User',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The user to check for existance in the database', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'rules_user', array( 'label' => 'User' ) ),
					),
				),
				'callback' => function( $user, $values ) {
					if ( ! $user instanceof \WP_User ) {
						return false;
					}
					
					return $user->exists();
				},
			)),
			
			/* User Capability */
			array( 'rules_user_has_capability', array(
				'title' => 'Check The Capabilities Of A User',
				'description' => 'Check if a user has a specific capability.',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$choices = array(
							'Any Designated Capability' => 'any',
							'All Designated Capabilities' => 'all',
						);
						$form->addField( 'condition_type', 'choice', array(
							'label' => __( 'Capability Check Type', 'mwp-rules' ),
							'description' => __( 'Select how you would like to check the capabilities of the user.', 'mwp-rules' ),
							'choices' => $choices,
							'required' => true,
							'expanded' => true,
							'data' => isset( $values['condition_type'] ) ? $values['condition_type'] : 'any',
						));
					},
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to check capabilities for', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User', 'description' => 'Select the user whose capabilities you want to check.' ) ),
					),
					'capabilities' => array(
						'label' => 'User Capability',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of capabilities to check for the user' ),
							'string' => array( 'description' => 'An individual capability to check for the user' ),
						),
						'configuration' => $plugin->configPreset( 'array', 'user_capabilities', array( 'label' => 'User Capabilities', 'description' => 'Enter the capabilities to check, one per line. Stock WordPress capabilities are <a target="_blank" href="https://codex.wordpress.org/Roles_and_Capabilities">documented here</a>.' ) ),
					),
				),
				'callback' => function( $user, $capabilities, $values ) {
					if ( ! $user instanceof \WP_User ) {
						return false;
					}
					
					if ( isset( $values['condition_type'] ) and isset( $capabilities ) ) {
						switch( $values['condition_type'] ) {
							case 'any': 
								foreach( (array) $capabilities as $cap ) {
									if ( $user->has_cap( $cap ) ) {
										return true;
									}
								}
								return false;

							case 'all':
								$has_all = true;
								foreach( (array) $capabilities as $cap ) {
									if ( ! $user->has_cap( $cap ) ) {
										$has_all = false;
										break;
									}
								}
								return $has_all;

						}
					}
					
					return false;
				},
			)),	
			
			/* User Role */
			array( 'rules_user_has_role', array(
				'title' => 'Check The Role Of A User',
				'description' => 'Check if a user has been assigned particular role(s).',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$choices = array(
							'Any Selected Roles' => 'any',
							'All Selected Roles' => 'all',
						);
						$form->addField( 'condition_type', 'choice', array(
							'label' => __( 'Role Check Type', 'mwp-rules' ),
							'description' => __( 'Select how you would like to check the roles of the user.', 'mwp-rules' ),
							'choices' => $choices,
							'required' => true,
							'expanded' => true,
							'data' => isset( $values['condition_type'] ) ? $values['condition_type'] : 'any',
						));
					},
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to check roles for', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User', 'description' => 'Select the user whose roles you want to check.' ) ),
					),
					'roles' => array(
						'label' => 'User Role',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of role names to check for the user' ),
							'string' => array( 'description' => 'A role name to check for the user' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$role_options = array();
								$roles = wp_roles();
								foreach( $roles->roles as $slug => $role ) {
									$role_options[ $role['name'] ] = $slug;
								}
								$form->addField( 'user_roles', 'choice', array(
									'label' => __( 'User Roles', 'mwp-rules' ),
									'description' => __( 'Select the roles to check', 'mwp-rules' ),
									'choices' => $role_options,
									'expanded' => true,
									'multiple' => true,
									'data' => isset( $values['user_roles'] ) ? $values['user_roles'] : [],
								));
							},
							'getArg' => function( $values ) {
								return isset( $values['user_roles'] ) ? $values['user_roles'] : [];
							}
						)
					),
				),
				'callback' => function( $user, $roles, $values ) {
					if ( ! $user instanceof \WP_User ) {
						return false;
					}
					
					if ( isset( $values['condition_type'] ) and isset( $roles ) ) {
						$user_roles = (array) $user->roles;
						switch( $values['condition_type'] ) {
							case 'any': 
								foreach( (array) $roles as $role ) {
									if ( in_array( $role, $user_roles ) ) {
										return true;
									}
								}
								return false;

							case 'all':
								$has_all = true;
								foreach( (array) $roles as $role ) {
									if ( ! in_array( $role, $user_roles ) ) {
										$has_all = false;
										break;
									}
								}
								return $has_all;

						}
					}
					
					return false;
				},
			)),	
			
			/* Count Posts By User */
			array( 'rules_count_user_posts', array(
				'title' => 'Check The Role Of A User',
				'description' => 'Check if a user has been assigned particular role(s).',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$choices = array(
							'Greater Than' => 'greater',
							'Greater Than Or Equal To' => 'greaterequal',
							'Less Than' => 'lesser',
							'Less Than Or Equal To' => 'lesserequal',
							'Equal To' => 'equal',
						);
						$form->addField( 'condition_type', 'choice', array(
							'label' => __( 'Count Check Type', 'mwp-rules' ),
							'description' => __( 'Select how you would like to check the posts count for the user.', 'mwp-rules' ),
							'choices' => $choices,
							'required' => true,
							'expanded' => true,
							'data' => isset( $values['condition_type'] ) ? $values['condition_type'] : 'any',
						));
					},
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to check roles for', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User', 'description' => 'Select the user whose roles you want to check.' ) ),
					),
					'post_types' => array(
						'label' => 'Post Types',
						'required' => false,
						'argtypes' => array(
							'array' => array( 'description' => 'An array of post types to filter by', 'classes' => array( 'WP_Post_Type' ) ),
							'object' => array( 'description' => 'A post type to filter by', 'classes' => array( 'WP_Post_Type' ) ),
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
					'post_statuses' => array(
						'label' => 'Post Statuses',
						'required' => false,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of post statuses to filter by' ),
							'object' => array( 'description' => 'A post status to filter by' ),
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
					'threshold' => array(
						'label' => 'Count Threshold',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'int' => array( 'description' => 'The number of posts at which the threshold should be set.' ),
						),
						'configuration' => $plugin->configPreset( 'integer', 'count_threshold', array( 'label' => 'Threshold' ) ),
					),
				),
				'callback' => function( $user, $post_types, $post_statuses, $threshold, $values ) {
					if ( ! $user instanceof \WP_User or ! $user->ID ) {
						return false;
					}
					
					if ( ! isset( $values['condition_type'] ) ) {
						return false;
					}
					
					if ( ! isset( $threshold ) ) {
						return false;
					}
					
					$args = array(
						'post_type' => $post_types ?: NULL,
						'post_status' => $post_statuses ?: NULL,
						'author' => $user->ID,
						'posts_per_page' => -1,
					);
					
					$query = new \WP_Query( $args );
					$found_posts = $query->found_posts;
					
					switch( $values['condition_type'] ) {
						case 'greater':
							return $found_posts > $threshold;
						case 'greaterequal':
							return $found_posts >= $threshold;
						case 'lesser':
							return $found_posts < $threshold;
						case 'lesserequal':
							return $found_posts <= $threshold;
						case 'equal':
							return $found_posts == $threshold;
					}
					
					return false;
				}
			)),

		));
	}
}
