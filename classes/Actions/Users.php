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
namespace MWP\Rules\Actions;

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
		
		$user_lang = 'User';
		
		rules_register_actions( array(
		
			/* Create A User 
			array( 'rules_create_user', array(
				'title' => 'Create A User',
				'description' => 'Create a new site user',
				'arguments' => array(
					'password' => array(
						'label' => 'User Password',
						'default' => 'manual',
						'required' => true,
						'argtypes' => array(
							'string' => array( 'description' => 'The title of the post' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'rules_post_title', array( 'label' => 'Post Title', 'attr' => array( 'placeholder' => __( 'Enter title', 'mwp-rules' ) ) ) ),
					),
					
				),
			)),
			*/
			
			/* Update A User */
			array( 'rules_update_user', array(
				'title' => 'Update User Profile',
				'description' => 'Change the attributes of a user profile',
				'group' => $user_lang,
				'configuration' => array(
					'form' => function( $form, $values ) {
						$update_options = array(
							'Login Name' => 'user_login',
							'Password' => 'user_pass',
							'Display Name' => 'display_name',
							'Email' => 'user_email',
							'Website URL' => 'user_url',
							'Slug' => 'user_nicename',
							'First Name' => 'first_name',
							'Last Name' => 'last_name',
							'Description' => 'description',
						);
						$update_toggles = array(
							'user_login' => array( 'show' => array( '#rules-update-user_user_login_form_wrapper' ) ),
							'user_pass' => array( 'show' => array( '#rules-update-user_user_pass_form_wrapper' ) ),
							'display_name' => array( 'show' => array( '#rules-update-user_display_name_form_wrapper' ) ),
							'user_email' => array( 'show' => array( '#rules-update-user_user_email_form_wrapper' ) ),
							'user_url' => array( 'show' => array( '#rules-update-user_user_url_form_wrapper' ) ),
							'user_nicename' => array( 'show' => array( '#rules-update-user_user_nicename_form_wrapper' ) ),
							'first_name' => array( 'show' => array( '#rules-update-user_first_name_form_wrapper' ) ),
							'last_name' => array( 'show' => array( '#rules-update-user_last_name_form_wrapper' ) ),
							'description' => array( 'show' => array( '#rules-update-user_description_form_wrapper' ) ),
						);
						$form->addField( 'attribute_names', 'choice', array(
							'label' => __( 'User Attributes To Update', 'mwp-rules' ),
							'description' => __( 'Select the attributes of the user profile that you want to update.', 'mwp-rules' ),
							'choices' => $update_options,
							'required' => true,
							'expanded' => true,
							'multiple' => true,
							'data' => isset( $values['attribute_names'] ) ? $values['attribute_names'] : [],
							'toggles' => $update_toggles,
						));
					},
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to update', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User', 'description' => 'Select the user whose profile you want to update.' ) ),
					),
					'user_login' => array(
						'label' => 'Login Name',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The login name for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'user_login', array( 'label' => 'Login Name' ) ),
					),
					'user_pass' => array(
						'label' => 'Password',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The display name for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'user_pass', array( 'label' => 'Password' ) ),
					),
					'display_name' => array(
						'label' => 'Display Name',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The display name for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'display_name', array( 'label' => 'Display Name' ) ),
					),
					'user_email' => array(
						'label' => 'Email Address',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The email address for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'user_email', array( 'label' => 'Email' ) ),
					),
					'user_url' => array(
						'label' => 'Website Address',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The url to the website for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'user_url', array( 'label' => 'URL' ) ),
					),
					'user_nicename' => array(
						'label' => 'Nicename (slug)',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The friendly url slug for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'user_nicename', array( 'label' => 'Slug' ) ),
					),
					'first_name' => array(
						'label' => 'First Name',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The first name for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'first_name', array( 'label' => 'First Name' ) ),
					),
					'last_name' => array(
						'label' => 'Last Name',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The last name for the user' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'last_name', array( 'label' => 'Last Name' ) ),
					),
					'description' => array(
						'label' => 'User Description',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'A description for the user profile' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_post_content', array( 'label' => 'Post Content', 'attr' => array( 'style' => 'min-height: 250px' ) ) ),
					),
				),
				'callback' => function( $user, $user_login, $user_pass, $display_name, $user_email, $user_url, $user_nicename, $first_name, $last_name, $description, $values ) {
					if ( ! $user instanceof \WP_User ) {
						return array( 'success' => false, 'message' => 'Invalid user provided.', 'user' => $user );
					}
					
					$update_attributes = array(
						'ID' => $user->ID,
					);
					
					if ( isset( $values['attribute_names'] ) ) {
						foreach( (array) $values['attribute_names'] as $name ) {
							switch( $name ) {
								case 'user_login':
									$update_attributes['user_login'] = $user_login;
									break;
								case 'user_pass':
									$update_attributes['user_pass'] = $user_pass;
									break;
								case 'display_name':
									$update_attributes['display_name'] = $display_name;
									break;
								case 'user_email':
									$update_attributes['user_email'] = $user_email;
									break;
								case 'user_url':
									$update_attributes['user_url'] = $user_url;
									break;
								case 'user_nicename':
									$update_attributes['user_nicename'] = $user_nicename;
									break;
								case 'first_name':
									$update_attributes['first_name'] = $first_name;
									break;
								case 'last_name':
									$update_attributes['last_name'] = $last_name;
									break;
								case 'description':
									$update_attributes['description'] = $description;
									break;
							}
						}
					}
					
					$result = wp_update_user( $update_attributes );
					
					if ( is_wp_error( $result ) ) {
						return array( 'success' => false, 'message' => $result->get_error_message() );
					}
					
					return array( 'success' => true, 'message' => 'User profile updated', 'attribute_name' => $attribute_name, 'attribute_value' => $attribute_value );
				}
			)),
		
			/* Delete A User */
			array( 'rules_delete_user', array(
				'title' => 'Delete A User',
				'description' => 'Delete a user from the database.',
				'group' => $user_lang,
				'configuration' => array(
					'form' => function( $form, $values ) {
						$form->addField( 'keep_posts', 'checkbox', array(
							'label' => __( 'Reassign Posts?', 'mwp-rules' ),
							'description' => __( 'If posts from this user are not reassigned to another user, they will be deleted.', 'mwp-rules' ),
							'value' => 1,
							'data' => isset( $values['keep_posts'] ) ? (bool) $values['keep_posts'] : false,
							'toggles' => array(
								1 => array( 'show' => array( '#rules-delete-user_reassign_user_form_wrapper' ) ),
							),
						));
					}
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to delete', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User' ) ),
					),
					'reassign_user' => array(
						'label' => 'Reassignment User',
						'required' => false,
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The user to reassign posts to', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'reassignment_user', array( 'label' => 'Reassignment User' ) ),
					),
				),
				'callback' => function( $user, $reassign_user ) {
					if ( ! $user instanceof \WP_User or ! $user->exists() ) {
						return array( 'success' => false, 'message' => 'Invalid user provided.', 'user' => $user );
					}
					
					include_once( ABSPATH . '/wp-admin/includes/user.php' );
					
					$reassign_id = NULL;
					
					if ( isset( $values['keep_posts'] ) and $values['keep_posts'] ) {
						$reassign_id = ( isset( $reassign_user ) and $reassign_user instanceof \WP_User ) ? $reassign_user->ID : NULL;
					}
					
					wp_delete_user( $user->ID, $reassign_id );
				}
			)),
			
			/* Change User Role */
			array( 'rules_update_user_roles', array(
				'title' => 'Update User Roles',
				'description' => 'Change the roles assigned to a user.',
				'group' => $user_lang,
				'configuration' => array(
					'form' => function( $form, $values ) {
						$update_options = array(
							'Add Roles' => 'add',
							'Remove Roles' => 'remove',
							'Set Roles Explicitly' => 'set',
						);
						$form->addField( 'update_type', 'choice', array(
							'label' => __( 'Update Type', 'mwp-rules' ),
							'description' => __( 'Select how you would like to update the roles of the user.', 'mwp-rules' ),
							'choices' => $update_options,
							'required' => true,
							'expanded' => true,
							'data' => isset( $values['update_type'] ) ? $values['update_type'] : 'add',
						));
					},
				),
				'arguments' => array(
					'user' => array(
						'label' => 'User',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The user to update roles for', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'user', array( 'label' => 'User', 'description' => 'Select the user whose roles you want to update.' ) ),
					),
					'roles' => array(
						'label' => 'User Role',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of role names to update for the user' ),
							'string' => array( 'description' => 'A role name to update for the user' ),
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
									'description' => __( 'Select the roles to update', 'mwp-rules' ),
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
						return array( 'success' => false, 'message' => 'Invalid user provided.', 'user' => $user );
					}
					
					if ( isset( $values['update_type'] ) and isset( $roles ) ) {
						switch( $values['update_type'] ) {
							case 'add': 
								foreach( (array) $roles as $role ) {
									$user->add_role( $role );
								}
								break;
							case 'remove':
								foreach( (array) $roles as $role ) {
									$user->remove_role( $role );
								}
								break;
							case 'set':
								$user->set_role('');
								foreach( (array) $roles as $role ) {
									$user->add_role( $role );
								}
								break;
							default:
								return array( 'success' => false, 'message' => 'Unknown update type', 'update_type' => $values['update_type'] );
						}
						
						return array( 'success' => true, 'message' => 'User roles updated', 'update_type' => $values['update_type'], 'user_roles' => $roles );
					}
					
					return array( 'success' => false, 'message' => 'Action configuration cannot be processed.', 'values' => $values );
				}
			)),
			
		));
		
	}	
}
