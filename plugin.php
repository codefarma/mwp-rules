<?php
/**
 * Plugin Name: MWP Rules
 * Plugin URI: 
 * Description: A rules engine for wordpress
 * Author: Kevin Carwile
 * Author URI: http://millermedia.io
 * Depends: lib-modern-framework
 * Version: 0.9.1
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( ! class_exists( 'MWPRulesPlugin' ) )
{
	class MWPRulesPlugin
	{
		public static function init()
		{
			/* Plugin Core */
			$plugin	= \MWP\Rules\Plugin::instance();
			$plugin->setPath( rtrim( plugin_dir_path( __FILE__ ), '/' ) );
			
			/* Plugin Settings */
			$settings = \MWP\Rules\Settings::instance();
			$plugin->addSettings( $settings );
			
			$ajaxHandlers = \MWP\Rules\AjaxHandlers::instance();
			
			/* Connect annotated resources to wordpress core */
			$framework = \Modern\Wordpress\Framework::instance()
				->attach( $plugin )
				->attach( $settings )
				->attach( $ajaxHandlers )
				
				->attach( new \MWP\Rules\Events\System )
				->attach( new \MWP\Rules\Events\Content )
				->attach( new \MWP\Rules\Events\Users )
				
				->attach( new \MWP\Rules\Conditions\Content )
				->attach( new \MWP\Rules\Conditions\System )
				
				->attach( new \MWP\Rules\Actions\Content )
				->attach( new \MWP\Rules\Actions\System )
				;
				
			$plugin->getRulesController()->registerAdminPage( array( 
				'title' => __( 'Rules', 'mwp-rules' ),
				'type' => 'menu', 
				'slug' => 'mwp-rules', 
				'menu' => __( 'Rules', 'mwp-rules' ), 
				'icon' => $plugin->fileUrl( 'assets/img/gavel.png' ), 
				'position' => 76,
			));
			
			$plugin->getLogsController()       ->registerAdminPage( array( 'type' => 'submenu', 'menu' => __( 'Rules Logs', 'mwp-rules' ), 'parent' => 'mwp-rules' ) );
			$plugin->getScheduleController()   ->registerAdminPage( array( 'type' => 'submenu', 'menu' => __( 'Scheduled Actions', 'mwp-rules' ), 'parent' => 'mwp-rules' ) );
			$plugin->getConditionsController() ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ) );
			$plugin->getActionsController()    ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ) );
			
			/* Backwards Compat */
			include_once( 'includes/backwards.functions.php' );
			
			/* Core class map */
			include_once( 'includes/rules.core.maps.php' );
		}
		
		public static function status() {
			if ( ! class_exists( 'ModernWordpressFramework' ) ) {
				echo '<td colspan="3" class="plugin-update colspanchange">
						<div class="update-message notice inline notice-error notice-alt">
							<p><strong style="color:red">INOPERABLE.</strong> Please activate <a href="' . admin_url( 'plugins.php?page=tgmpa-install-plugins' ) . '"><strong>Modern Framework for Wordpress</strong></a> to enable the operation of this plugin.</p>
						</div>
					  </td>';
			}
		}
	}
	
	/* Autoload Classes */
	require_once 'vendor/autoload.php';
	
	/* Bundled Framework */
	if ( file_exists( __DIR__ . '/framework/plugin.php' ) ) {
		include_once 'framework/plugin.php';
	}

	/* Register plugin dependencies */
	include_once 'includes/plugin-dependency-config.php';
	
	/* Register plugin status notice */
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( 'MWPRulesPlugin', 'status' ) );
	
	/**
	 * Add a new event to rules
	 * 
	 * @param  string          (required)   $type        Event type: 'action' or 'filter'
	 * @param  string          (required)   $hook        The name of the hook
	 * @param  array|callable  (required)   $definition  {
	 *
	 *     The event definition. If a callback is provided, it must return the definition array. 
	 *     
	 *     @type   string (required)   'title'       The event title to be displayed in summaries
	 *     @type   string (optional)   'description' A possibly more descriptive event summary
	 *     @type   array  (optional)   'arguments'   {
	 *
	 *         An associative array of argument descriptions (if any) which are used in the hook.
	 *         The keys for each array item are the name of the argument (which will be used as a 
	 *         variable name). The value of each key is another associative array describing the 
	 *         argument. The arguments need to be in the same order that they are passed in the
	 *         event hook.
	 *
	 *         arg_name => array {
	 *
	 *           Argument description.
	 *
	 *           @type  string (required)  'argtype'      The type of the argument. Possible choices:
	 *                                                    'int', 'float', 'bool', 'string', 'mixed', 
	 *                                                    'array', 'object'.
	 *           @type  string (required)  'label'        The label of the argument used for generating
	 *                                                    form elements.
	 *           @type  string (optional)  'description'  A more detailed description of the argument 
	 *                                                    value.
	 *           @type  string (optional)  'class'        A mapped class that this argument represents.
	 *                                                    For example, a user id integer would have the
	 *                                                    class of 'WP_User'.
	 *           @type  string (optional)  'nullable'     This indicates of the value of this argument
	 *                                                    is sometimes purposely NULL.
	 *                                                    Default: false
	 *           @type  array  (optional)  'keys'         {
	 *
	 *               An associative array used to describe the key/value pairs for array argtypes.
	 *
	 *               @type  bool     (optional) 'associative'  Indicates the array keys are associative.
	 *               @type  bool     (optional) 'fixed'        Indicates the only keys available for the
	 *                                                         array are those which are mapped.
	 *               @type  callable (optional) 'getter'       {
	 *
	 *                   A callback function that can be used to retrieve the value for a given key.
	 *                   
	 *                   param  object   $object   The object that the array belongs to
	 *                   param  string   $key      The key to retrieve from the array
	 *                   return mixed
	 *               }
	 *               @type  array    (optional) 'default'      A default argument description which applies
	 *                                                         to all array values not specifically mapped.
	 *               @type  array    (optional) 'mappings'     {
	 *                   An associative array of argument descriptions associated with specific array keys.
	 *               }
	 *           }
	 *         }
	 *     }
	 * }
	 * @return void
	 */
	function rules_describe_event( $type, $hook, $definition ) {
		\MWP\Rules\Plugin::instance()->describeEvent( $type, $hook, $definition );
	}
	
	function rules_describe_events( $events ) {
		foreach( $events as $event ) {
			call_user_func_array( 'rules_describe_event', $event );
		}
	}
	
	/**
	 * Add a new condition to rules
	 * 
	 * @param  string          (required)   $key         A unique key that identifies the condition
	 * @param  array|callable  (required)   $definition  {
	 *
	 *     The condition definition. If a callback is provided, it must return the definition array. 
	 *     
	 *     @type   string   (required)   'title'         The condition title to be displayed in summaries
	 *     @type   string   (optional)   'description'   A possibly more descriptive condition summary
	 *     @type   callable (required)   'callback'      {
	 *         The callback which performs the condition check.
	 *         
	 *         params  ...                  The arguments from this definition
	 *         params  array   $values      The saved operation configuration values
	 *         params  array   $arg_map     The event arguments mapped by key
	 *         params  object  $operation   The instance of the MWP\Rules\Condition being invoked
	 *         return  bool
	 *     }
	 *     @type   array    (optional)    'configuration' {
	 *         @type   callable  (optional)   'form'       {
	 *             This callback allows you to add any configuration fields needed to control the 
	 *             overall behavior of the condition.
	 *
	 *             param  object   $form       The form builder (MWP\Framework\Helpers\Form)
	 *             param  array    $values     The values previously saved in the config
	 *             param  object   $operation  The instance of the MWP\Rules\Condition being configured
	 *             return void
	 *         }
	 *         @type   callable  (optional)   'saveValues' {
	 *             This callback can be used to process form submission values before they are saved.
	 * 
	 *             param  array    $values     The values previously saved in the config
	 *             param  object   $operation  The instance of the MWP\Rules\Condition being configured
	 *             return void
	 *         }
	 *     }
	 *     @type   array    (optional)   'arguments'     {
	 * 
	 *         An associative array that describes the parameters which your callback function uses.
	 *         The keys for each array item are the name of the argument (which will also be used as 
	 *         a variable name). The value of each key is another associative array describing the 
	 *         argument. The arguments need to be in the same order that they will be passed to your
	 *         callback function.
	 *
	 *         arg_name => array {
	 *           Parameter description.
	 *
	 *           @type  string (required)  'label'        The label of the argument used for generating
	 *                                                    form elements.
	 *           @type  array  (optional)  'argtypes'     {
	 *
	 *               An associative array of the argument types that are acceptable to your callback
	 *               for this parameter. Array keys correspond to acceptable argument types, i.e.
	 *               'int', 'float', 'bool', 'string', 'mixed', 'array', 'object'
	 *
	 *               argtype => array {
	 *
	 *                   Argument details.
	 *
	 *                   @type  string   'description'    A description of what this argtype is
	 *                   @type  array    'classes'        {
	 *                       An array of acceptable classes (fully qualified classnames) for this argtype.
	 *                       @type  string  
	 *                   }
	 *               }
	 *           } 
	 *           @type  string (optional)  'default'      The default configuration method for the argument.
	 *                                                    Choices: 'event', 'manual', 'phpcode'
	 *                                                    Default: event
	 *           @type  bool   (optional)  'required'     Indicates if a NULL value for this argument is 
	 *                                                    acceptable or not.
	 *                                                    Default: false
	 *           @type  array  (optional) 'configuration' {
	 *               @type  callable  (optional)  'form'  {
	 *                   This callback (if provided) will enable the user to select the 'manual' config option
	 *                   for this condition parameter. Add any necessary fields to the form to allow the user
	 *                   to manually set the value for this parameter.
	 *
	 *                   param  object   $form       The form builder (MWP\Framework\Helpers\Form)
	 *                   param  array    $values     The values previously saved in the config
	 *                   param  object   $operation  The instance of the MWP\Rules\Condition being configured
	 *                   return void
	 *               }
	 *               @type  callable  (optional)  'saveValues'  {
	 *                   This callback can be used to process form submission values before they are saved.
	 * 
	 *                   param  array    $values     The values previously saved in the config
	 *                   param  object   $operation  The instance of the MWP\Rules\Condition being configured
	 *                   return void
	 *               }
	 *               @type  callable  (required)  'getArg'  {
	 *                   This callback is called to get the value to use for the parameter.
	 * 
	 *                   param  array    $values     The values previously saved in the config
	 *                   params  array   $arg_map     The event arguments mapped by key
	 *                   param  object   $operation  The instance of the MWP\Rules\Condition being configured
	 *                   return mixed
	 *               }
	 *           }
	 *         }
	 *     }
	 * }
	 * @return void
	 */
	function rules_register_condition( $key, $definition ) {
		\MWP\Rules\Plugin::instance()->registerCondition( $key, $definition );
	}
	
	function rules_register_conditions( $conditions ) {
		foreach( $conditions as $condition ) {
			call_user_func_array( 'rules_register_condition', $condition );
		}
	}
	
	/**
	 * Add a new action to rules
	 * 
	 * @param  string          (required)   $key         A unique key that identifies the condition
	 * @param  array|callable  (required)   $definition  The action definition (same format as in 'rules_register_condition')
	 * @return void
	 */
	function rules_define_action( $key, $definition ) {
		\MWP\Rules\Plugin::instance()->defineAction( $key, $definition );
	}
	
	function rules_define_actions( $actions ) {
		foreach( $actions as $action ) {
			call_user_func_array( 'rules_define_action', $action );
		}
	}
	
	/**
	 * DO NOT REMOVE
	 *
	 * This plugin depends on the modern wordpress framework.
	 * This block ensures that it is loaded before we init.
	 */
	if ( class_exists( 'ModernWordpressFramework' ) ) {
		MWPRulesPlugin::init();
	}
	else {
		add_action( 'modern_wordpress_init', array( 'MWPRulesPlugin', 'init' ) );
	}	
}

