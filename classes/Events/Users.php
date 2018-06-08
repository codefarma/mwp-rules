<?php
/**
 * Plugin Class File
 *
 * Created:   January 12, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Events;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * System Class
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
		rules_register_events( array(
			
			/* User Created */
			array( 'action', 'user_register', array(
				'title' => 'User Has Been Created',
				'description' => 'Access data for a new user immediately after they are added to the database.',
				'group' => 'User',
				'arguments' => array(
					'user_id' => array( 
						'argtype' => 'int',
						'class' => 'WP_User',
						'label' => 'User ID',
						'description' => 'The id of the newly created user',
					),
				),
			)),
			
			/* User Updated */
			array( 'action', 'profile_update', array(
				'title' => 'User Profile Has Been Updated',
				'description' => 'This hook allows you to access data for a user immediately after their database information is updated.',
				'group' => 'User',
				'arguments' => array(
					'user_id' => array( 
						'argtype' => 'int',
						'class' => 'WP_User',
						'label' => 'User ID',
						'description' => 'The id of the updated user',
					),
					'old_user' => array(
						'argtype' => 'object',
						'class' => 'WP_User',
						'label' => 'Old User',
						'description' => 'The object containing the old user data',
					),
				),
			)),
			
			/* User Deleted */
			array( 'action', 'delete_user', array(
				'title' => 'User Is Being Deleted',
				'description' => 'This event occurs just before a user is deleted from the database.',
				'group' => 'User',
				'arguments' => array(
					'user_id' => array(
						'argtype' => 'int',
						'class' => 'WP_User',
						'label' => 'User ID',
						'description' => 'The ID of the user being deleted',
					),
				),
			)),
			
			/* User Logged In */
			array( 'action', 'wp_login', array( 
				'title' => 'User Has Logged In',
				'description' => 'This event occurs when a user logs into the site.',
				'group' => 'User',
				'arguments' => array(
					'user_login' => array(
						'argtype' => 'string',
						'label' => 'User Login Name',
						'description' => 'The login name of the user who logged in',
					),
					'user' => array(
						'argtype' => 'object',
						'class' => 'WP_User',
						'label' => 'Logged In User',
						'description' => 'The user object for the user who logged in',
					),
				),
			)),
			
			/* User Login Failed */
			array( 'action', 'wp_login_failed', array( 
				'title' => 'Login Attempt Has Failed',
				'description' => 'This event occurs when a user has failed a login attempt.',
				'group' => 'User',
				'arguments' => array(
					'username' => array(
						'argtype' => 'string',
						'label' => 'Login Name or Email',
						'description' => 'The login name or email used in the login attempt',
					),
				),
			)),
			
			/* User Logging Out */
			array( 'action', 'clear_auth_cookie', array( 
				'title' => 'User Is Logging Out',
				'description' => 'This event occurs just before a user has their authentication cleared.',
				'group' => 'User',
			)),
			
			/* User Meta Added */
			array( 'action', 'added_user_meta', array(
				'title' => 'User Meta Has Been Added',
				'description' => 'This event occurs when user meta data is added for the first time.',
				'group' => 'User',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'user_id' => array( 'argtype' => 'int', 'class' => 'WP_User', 'label' => 'User ID', 'description' => 'The ID of the user that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was added' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* User Meta Updated */
			array( 'action', 'updated_user_meta', array(
				'title' => 'User Meta Has Been Updated',
				'description' => 'This event occurs after user meta data has been successfully updated.',
				'group' => 'User',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'user_id' => array( 'argtype' => 'int', 'class' => 'WP_User', 'label' => 'User ID', 'description' => 'The ID of the user that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was updated' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* User Meta Deleted */
			array( 'action', 'deleted_user_meta', array(
				'title' => 'User Meta Has Been Deleted',
				'description' => 'This event occurs after user meta data has been deleted.',
				'group' => 'User',
				'arguments' => array(
					'meta_ids' => array( 'argtype' => 'array', 'label' => "Meta IDs", 'description' => 'The IDs of the meta data rows that were deleted' ),
					'user_id' => array( 'argtype' => 'int', 'class' => 'WP_User', 'label' => 'User ID', 'description' => 'The ID of the user that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was deleted' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was matched' ),
				),				
			)),
			
		));
	}
}
