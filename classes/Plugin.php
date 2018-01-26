<?php
/**
 * Plugin Class File
 *
 * @vendor: Miller Media
 * @package: MWP Rules
 * @author: Kevin Carwile
 * @link: http://millermedia.io
 * @since: December 4, 2017
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

const ACTION_STANDARD = 0;
const ACTION_ELSE = 1;

use Modern\Wordpress\Framework;
use Modern\Wordpress\Task;
use MWP\Rules\ECA\Loader;
use MWP\Rules\ECA\Token;
use MWP\Rules\Rule;
use MWP\Rules\ScheduledAction;

/**
 * Plugin Class
 */
class Plugin extends \Modern\Wordpress\Plugin
{
	/**
	 * Instance Cache - Required
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string		Plugin Name
	 */
	public $name = 'MWP Rules';
	
	/**
	 * @var	array
	 */
	protected $events = array();
		
	/**
	 * @var	array
	 */
	protected $conditions = array();
	
	/**
	 * @var	array
	 */
	protected $actions = array();
	
	/**
	 * @var	bool
	 */
	public $shuttingDown = FALSE;
	
	/**
	 * @var	array
	 */
	public $actionQueue = array();
	
	/**
	 * @Wordpress\Script( deps={"mwp"} )
	 */
	public $mainController = 'assets/js/main.js';
	
	/**
	 * @Wordpress\Script( deps={"jquery-ui-sortable"} )
	 */
	public $nestedSortable = 'assets/js/jquery.mjs.nestedSortable.js';
	
	/**
	 * @Wordpress\Script( deps={"jquery"} )
	 */
	public $selectizeJS = 'assets/js/selectize/js/selectize.min.js';
	
	/**
	 * @Wordpress\Stylesheet
	 */
	public $selectizeCSS = 'assets/js/selectize/css/selectize.bootstrap3.css';
	
	/**
	 * Admin Stylesheet
	 *
	 * @Wordpress\Stylesheet
	 */
	public $adminStyle = 'assets/css/admin_style.css';
	
	/**
	 * @Wordpress\Script( handle="codemirror" )
	 */
	public $codeMirror = 'assets/js/codemirror/codemirror.js';
	
	/**
	 * @Wordpress\Script( handle="codemirror-xml" )
	 */
	public $codeMirrorXML = 'assets/js/codemirror/mode/xml/xml.js';

	/**
	 * @Wordpress\Script( handle="codemirror-css" )
	 */
	public $codeMirrorCSS = 'assets/js/codemirror/mode/css/css.js';

	/**
	 * @Wordpress\Script( handle="codemirror-javascript" )
	 */
	public $codeMirrorJS = 'assets/js/codemirror/mode/javascript/javascript.js';

	/**
	 * @Wordpress\Script( handle="codemirror-clike" )
	 */
	public $codeMirrorCLIKE = 'assets/js/codemirror/mode/clike/clike.js';

	/**
	 * @Wordpress\Script( handle="codemirror-htmlmixed", deps={"codemirror-xml","codemirror-javascript","codemirror-css"} )
	 */
	public $codeMirrorHTML = 'assets/js/codemirror/mode/htmlmixed/htmlmixed.js';
	
	/**
	 * @Wordpress\Script( handle="codemirror-php", deps={"codemirror","codemirror-htmlmixed","codemirror-clike"} )
	 */
	public $codeMirrorPHP = 'assets/js/codemirror/mode/php/php.js';
	
	/**
	 * @Wordpress\Stylesheet
	 */
	public $codeMirrorStyle = 'assets/css/codemirror.css';
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @Wordpress\Action( for="admin_enqueue_scripts" )
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{
		$this->useScript( $this->mainController );
		$this->useScript( $this->nestedSortable );
		$this->useScript( $this->codeMirror );
		$this->useStyle( $this->codeMirrorStyle );
		$this->useScript( $this->codeMirrorPHP );
		$this->useStyle( $this->adminStyle );
		$this->useScript( $this->selectizeJS );
		$this->useStyle( $this->selectizeCSS );
	}
	
	/**
	 * Give plugins a common hook to register ECA's
	 *
	 * @Wordpress\Action( for="plugins_loaded", priority=1 )
	 *
	 * @return	void
	 */
	public function whenPluginsLoaded()
	{
		/* Allow plugins to register their own ECA's */
		do_action( 'rules_register_ecas' );
		
		/* Connect all enabled first level rules to their hooks */
		foreach( Rule::loadWhere( array( 'rule_enabled=1 AND rule_parent_id=0' ), 'rule_priority ASC, rule_weight ASC' ) as $rule ) {
			$rule->deploy();
		}
	}
	
	/**
	 * Run scheduled actions
	 *
	 * @Wordpress\Action( for="mwp_rules_run_scheduled_actions" )
	 *
	 * @return	void
	 */
	public function runScheduledActions( $task )
	{
		$_next_action = ScheduledAction::getNextAction();
		
		if ( ! $_next_action ) {
			return $task->complete();
		}
		
		if ( $_next_action->time > time() ) {
			$task->next_start = $_next_action->time;
			return;
		}
		
		$_next_action->execute();
	}
	
	/**
	 * Describe an event that rules can be created for
	 *
	 * @param	string					$type				The event type (action, filter)
	 * @param	string					$hook_name			The event hook name
	 * @param	array|object|closure	$definition			The event definition
	 * @return	void
	 */
	public function describeEvent( $type, $hook_name, $definition=array() )
	{
		$this->events[ $type ][ $hook_name ] = new \MWP\Rules\ECA\Loader( 'MWP\Rules\ECA\Event', $definition, array( 
			'type' => $type,
			'hook' => $hook_name,
		));
	}
	
	/**
	 * Register a condition that can be added to rules
	 *
	 * @param	string			$condition_key		The condition key
	 * @param	mixed			$definition			The condition definition
	 * @return	void
	 */
	public function registerCondition( $condition_key, $definition )
	{
		$this->conditions[ $condition_key ] = new \MWP\Rules\ECA\Loader( 'MWP\Rules\ECA\Condition', $definition, array(
			'key' => $condition_key,
		));
	}
	
	/**
	 * Define an action that can be added to rules
	 *
	 * @param	string			$action_key			The action key
	 * @param	mixed			$definition			The action definition
	 * @return	void
	 */
	public function defineAction( $action_key, $definition )
	{
		$this->actions[ $action_key ] = new \MWP\Rules\ECA\Loader( 'MWP\Rules\ECA\Action', $definition, array(
			'key' => $action_key,
		));
	}
	
	/**
	 * Get all events
	 * 
	 * @param	string		$type			The events type
	 * @return	array
	 */
	public function getEvents( $type )
	{
		if ( isset( $this->events[ $type ] ) ) {
			return array_map( function( $eca ) { return $eca->instance(); }, $this->events[ $type ] );
		}
		
		return array();
	}
	
	/**
	 * Get a specific event
	 * 
	 * @param	string		$type			The events type
	 * @param	string		$hook_name		The event name
	 * @return	object|NULL
	 */
	public function getEvent( $type, $hook_name )
	{
		if ( isset( $this->events[ $type ][ $hook_name ] ) ) {
			return $this->events[ $type ][ $hook_name ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * Get all conditions
	 * 
	 * @return	array
	 */
	public function getConditions()
	{
		return array_map( function( $eca ) { return $eca->instance(); }, $this->conditions );
	}
	
	/**
	 * Get a specific condition
	 * 
	 * @param	string		$condition_key			The condition key
	 * @return	object|NULL
	 */
	public function getCondition( $condition_key )
	{
		if ( isset( $this->conditions[ $condition_key ] ) ) {
			return $this->conditions[ $condition_key ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * Get all actions
	 * 
	 * @return	array
	 */
	public function getActions()
	{
		return array_map( function( $eca ) { return $eca->instance(); }, $this->actions );
	}
	
	/**
	 * Get a specific action
	 * 
	 * @param	string		$action_key			The action key
	 * @return	object|NULL
	 */
	public function getAction( $action_key )
	{
		if ( isset( $this->actions[ $action_key ] ) ) {
			return $this->actions[ $action_key ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * @var ActiveRecordController
	 */
	public $rulesController;
	
	/**
	 * Get the rules controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getRulesController()
	{
		if ( isset( $this->rulesController ) ) {
			return $this->rulesController;
		}
		
		$this->rulesController = new \MWP\Rules\Controllers\RulesController( 'MWP\Rules\Rule' );
		
		return $this->rulesController;
	}
	
	/**
	 * @var ActiveRecordController
	 */
	public $conditionsController;
	
	/**
	 * Get the conditions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getConditionsController( $rule=null )
	{
		if ( isset( $this->conditionsController ) ) {
			if ( $rule ) {
				$this->conditionsController->setRule( $rule );
			}
			return $this->conditionsController;
		}
		
		$this->conditionsController = new \MWP\Rules\Controllers\ConditionsController( 'MWP\Rules\Condition' );
		
		return $this->getConditionsController( $rule );
	}
	
	/**
	 * @var ActiveRecordController
	 */
	public $actionsController;
	
	/**
	 * Get the actions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getActionsController( $rule=null )
	{
		if ( isset( $this->actionsController ) ) {
			if ( $rule ) {
				$this->actionsController->setRule( $rule );
			}
			return $this->actionsController;
		}
		
		$this->actionsController = new \MWP\Rules\Controllers\ActionsController( 'MWP\Rules\Action' );
		
		return $this->getActionsController( $rule );
	}
	
	/**
	 * @var ActiveRecordController
	 */
	public $logsController;
	
	/**
	 * Get the actions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getLogsController()
	{
		if ( isset( $this->logsController ) ) {
			return $this->logsController;
		}
		
		$this->logsController = new \MWP\Rules\Controllers\LogsController( 'MWP\Rules\Log' );
		
		return $this->logsController;
	}
		
	/**
	 * @var ActiveRecordController
	 */
	public $scheduleController;
	
	/**
	 * Get the actions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getScheduleController()
	{
		if ( isset( $this->scheduleController ) ) {
			return $this->scheduleController;
		}
		
		$this->scheduleController = new \MWP\Rules\Controllers\ScheduleController( 'MWP\Rules\ScheduledAction' );
		
		return $this->scheduleController;
	}
	
	/**
	 * Global Arguments
	 */
	public $globalArguments;
	
	/**
	 * Get Global Arguments
	 *
	 * @param	arg_name	Optional name of an argument definition to return
	 * @return 	array		Keyed array of global arguments
	 */
	public function getGlobalArguments( $arg_name=NULL )
	{
		if ( isset ( $this->globalArguments ) ) {
			return isset( $arg_name ) ? ( isset( $this->globalArguments[ $arg_name ] ) ? $this->globalArguments[ $arg_name ] : NULL ) : $this->globalArguments;
		}
		
		$this->globalArguments = apply_filters( 'rules_global_arguments', array() );
		
		return $this->globalArguments;
	}
	
	/**
	 * Class map
	 */
	public $classMap;
	
	/**
	 * Get Class Conversion Mappings
	 * 
	 * @param 	string|NULL		$class		A specific class to return conversions for, NULL for all
	 * @return	array						Class conversion definitions
	 */
	public function getClassMappings( $class=NULL )
	{
		if ( isset( $this->classMap ) ) {
			return isset( $class ) ? ( isset( $this->classMap[ $class ] ) ? $this->classMap[ $class ] : NULL ) : $this->classMap;
		}
		
		$this->classMap = apply_filters( 'rules_class_map', array() );
		
		return $this->getClassMappings( $class );
	}

	/**
	 * Get possible derivative arguments using the class map
	 *
	 * Based on the arguments provided, returns a map of subsequent arguments that can be derived
	 *
	 * @param	array	$source_argument		The starting argument
	 * @param	array	$target_argument		The definition which derivative arguments must match (or leave empty to return all derivatives)
	 * @param	int		$max_levels				The number of levels of recursion to dive
	 * @param	bool	$include_arbitrary		Include an arbitrary keys representation token in the results
	 * @param	string	$token_prefix			Prefix to apply to the tokenized keys (for internal use)
	 * @param	int		$level					The current level of recursion (for internal use)
	 * @return	array							Class converter methods
	 */
	public function getDerivativeTokens( $source_argument, $target_argument=NULL, $max_levels=1, $include_arbitrary=TRUE, $token_prefix='', $level=1 )
	{
		/* Depth limit */
		if ( $level > $max_levels ) {
			return array();
		}
		
		$derivative_arguments = array();
		$mappings             = array();
		$source_class         = NULL;

		if ( $token_prefix ) {
			$token_prefix .= ':';
		}
		
		if ( isset( $source_argument['class'] ) ) {
			list( $source_class, $source_key ) = $this->parseIdentifier( $source_argument['class'] );
		}
		
		/* If the source argument doesn't point to any specific class... it can't map to anything */
		if ( ! $source_class ) {
			return array();
		}
		
		if ( $source_argument['argtype'] !== 'object' ) {			
			/* If the source argument can't be used to load an instance... it can't map to anything */
			$source_class_map = $this->getClassMappings( $source_class );
			if ( ! $source_class_map or ! isset( $source_class_map['loader'] ) or ! is_callable( $source_class_map['loader'] ) ) {
				return array();
			}
		}
		
		/**
		 * Compile a list of all the classes in our class map that are compliant 
		 * with the argument, meaning that the argument can be used to load it, 
		 * or a subclass of it
		 */
		foreach ( $this->getClassMappings() as $classname => $class ) {
			if ( $this->isClassCompliant( $source_argument['class'], $classname ) ) {
				$augmented_class = in_array( $source_argument['argtype'], array( 'object', 'array' ) ) ? array() : array(
					'mappings' => array(
						'*' => array(
							'argtype' => 'object',
							'class' => $classname,
							'label' => ( isset( $class['label'] ) ? $class['label'] : $classname ) . ' Object',
							'getter' => function( $object ) { return $object; },
						),
					),
				);
				
				$mappings[ $classname ] = array_replace_recursive( $augmented_class, $class );
			}
		}
		
		/**
		 * Now for every class that has conversions available, we look at each of the 
		 * conversion options and see if they are compatible with our target argument. 
		 */
		foreach ( $mappings as $classname => $class ) {
			if ( isset( $class['mappings'] ) ) {
				foreach ( $class['mappings'] as $argument_key => $converted_argument ) {
					$original_converted_argtype = $converted_argument['argtype'];
					
					/* Source arrays are always going to produce another array */
					if ( $source_argument['argtype'] == 'array' ) {
						$converted_argument['subtype'] = $converted_argument['argtype'] != 'array' ? $converted_argument['argtype'] : ( isset( $converted_argument['class'] ) ? 'object' : '' );
						$converted_argument['argtype'] = 'array';
					}
					
					if ( $this->isArgumentCompliant( $converted_argument, $target_argument ) ) {
						$derivative_arguments[ $token_prefix . $argument_key ] = $converted_argument;
					}
					
					/* For arrays that have key mappings, let's look at those too to see what we have */
					if ( $converted_argument['argtype'] == 'array' ) {
						
						$default_array_argument = array( 'argtype' => $original_converted_argtype, 'label' => isset( $converted_argument['label'] ) ? $converted_argument['label'] : '' );
						$arbitrary_key_indicator = ( isset( $converted_argument['keys']['associative'] ) and $converted_argument['keys']['associative'] ) ? 'a-z' : '0-9';
						
						// Default for arrays with a class specification
						if ( isset( $converted_argument['class'] ) ) {
							$default_array_argument = array_merge( $default_array_argument, array( 'argtype' => 'object', 'class' => $converted_argument['class'] ) );
						}
						
						// Default override for arbitrary keys
						if ( isset( $converted_argument['keys']['default'] ) ) {
							$default_array_argument = array_merge( $default_array_argument, $converted_argument['keys']['default'] );
						}

						// Add tokens for arbitrary array keys
						if ( $include_arbitrary and ( ! isset( $converted_argument['keys']['fixed'] ) or ! $converted_argument['keys']['fixed'] ) ) {
							if ( $source_argument['argtype'] == 'array' and $original_converted_argtype == 'array' ) {
								$default_array_argument['argtype'] = 'array';
							}
							if ( $this->isArgumentCompliant( $default_array_argument, $target_argument ) ) {
								$derivative_arguments[ $token_prefix . $argument_key . '[' . $arbitrary_key_indicator . ']' ] = $default_array_argument;
							}
							
							/* Go deep on arbitrary keys */
							$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $default_array_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key . '[' . $arbitrary_key_indicator . ']', $level + 1 ) );
						}
						
						// Add tokens for specific array keys
						if ( isset( $converted_argument['keys']['mappings'] ) ) {
							foreach( $converted_argument['keys']['mappings'] as $converted_array_key => $converted_array_argument ) {
								if ( ! empty( $default_array_argument ) ) {
									$converted_array_argument = array_merge( $default_array_argument, $converted_array_argument );
								}
								if ( $source_argument['argtype'] == 'array' and $original_converted_argtype == 'array' ) {
									$converted_array_argument['argtype'] = 'array';
								}
								
								if ( $this->isArgumentCompliant( $converted_array_argument, $target_argument ) ) {
									$derivative_arguments[ $token_prefix . $argument_key . '[' . $converted_array_key . ']' ] = $converted_array_argument;
								}
								
								/* Go deep on specific keys */
								$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $converted_array_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key . '[' . $converted_array_key . ']', $level + 1 ) );
							}
						}
					}
					
					/* Go deep on token */
					if ( $argument_key !== '*' ) {
						$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $converted_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key, $level + 1 ) );
					}
				}
			}
		}
		
		return $derivative_arguments;
	}
	
	/**
	 * Get the identifier and optional key for an argument
	 *
	 * @param	string			$identifier			The identifier in the form of identifier[key]
	 * @return	array
	 */
	public function parseIdentifier( $identifier )
	{
		if ( strstr( $identifier, '[' ) !== FALSE ) {
			$components = explode( '[', $identifier );
			return array( $components[0], str_replace( ']', '', $components[1] ) );
		}
		
		return array( $identifier, NULL );
	}
	
	/**
	 * Check For Argument Compliance
	 *
	 * @param	array	$source_argument		The argument definition to map
	 * @param	array	$target_argument		The argument which is needed (or leave empty to return all derivatives)
	 * @return	bool
	 */
	public function isArgumentCompliant( $source_argument, $target_argument=NULL )
	{
		if ( ! isset( $source_argument['argtype'] ) ) {
			return false;
		}
		
		if ( ! isset( $target_argument ) ) {
			return true;
		}
		
		$target_types = array();

		if ( isset( $target_argument['argtypes'] ) ) {
			foreach( (array) $target_argument['argtypes'] as $k => $v ) {
				if ( is_array( $v ) ) {
					$target_types[ $k ] = $v;
				} else {
					$target_types[ $v ] = array();
				}
			}
		}
		
		if ( in_array( 'mixed', array_keys( $target_types ) ) or in_array( $source_argument['argtype'], array_keys( $target_types ) ) ) {
			$is_compliant = true;
			$target_type = ! empty( $target_types ) ? ( in_array( $source_argument['argtype'], array_keys( $target_types ) ) ? $target_types[ $source_argument['argtype'] ] : $target_types['mixed'] ) : array();
			if ( isset( $target_type['classes'] ) ) {
				$is_compliant = false;
				if ( isset( $source_argument['class'] ) ) {
					foreach( (array) $target_type['classes'] as $target_class ) {
						if ( $this->isClassCompliant( $source_argument['class'], $target_class ) ) {
							$is_compliant = true;
							break;
						}
					}
				}
			}
			
			return $is_compliant;
		}
		
		return false;
	}

	/**
	 * Check For Class Compliance
	 *
	 * @param	string 		$class		Class to check compliance
	 * @param	string|array	$classes	A classname or array of classnames to validate against
	 * @return	bool				Will return TRUE if $class is the same as or is a subclass of any $classes
	 */
	public function isClassCompliant( $class, $classes )
	{
		list( $class, $class_key ) = $this->parseIdentifier( $class );
		$compliant = FALSE;
		
		foreach ( (array) $classes as $_class )
		{
			if ( ltrim( $_class, '\\' ) === ltrim( $class, '\\' ) )
			{
				$compliant = TRUE;
				break;
			}
			
			if ( is_subclass_of( $class, $_class ) )
			{
				$compliant = TRUE;
				break;
			}
		}
		
		return $compliant;
	}
	
	/**
	 * Configuration Form Presets
	 *
	 * @param	string	$key			The key for the configuration preset to retrieve
	 * @param	string	$field_name		The name of the field
	 * @param	array	$options		Additional config options
	 * @return	array					The argument preset definition
	 */
	public function configPreset( $key, $field_name, $options=array() )
	{
		$config = array();
		
		switch ( $key ) {
			
			/* Simple Text Field */
			case 'text':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'label' => __( 'Text', 'mwp-rules' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values ) use ( $field_name ) {
						return $values[ $field_name ];
					}
				);
				break;
			
			/* Simple Textarea Field */
			case 'textarea':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Text', 'mwp-rules' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values ) use ( $field_name ) {
						return $values[ $field_name ];
					}
				);
				break;
				
			/* Date/Time */
			case 'datetime':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$values[ $field_name ] = isset( $values[ $field_name ] ) ? $values[ $field_name ] : time();
						$form->addField( $field_name, 'datetime', array_replace_recursive( array(
							'label' => ucwords( str_replace( '_', ' ', $field_name ) ),
							'view_timezone' => get_option( 'timezone_string' ) ?: 'UTC',
							'input' => 'timestamp',
							'data' => $values[ $field_name ],
						), 
						$options ));
					},
					'saveValues' => function( &$values, $operation ) use ( $field_name ) {	
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \DateTime ) {
							$values[ $field_name ] = $values[ $field_name ]->getTimestamp();
						}
					},
					'getArg' => function( $values ) use ( $field_name ) {
						$date = new \DateTime();
						$date->setTimestamp( $values[ $field_name ] );
						
						return $date;
					},
				);
				break;

			/* Individual User */
			case 'user':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'label' => __( 'User', 'mwp-rules' ),
							'description' => __( 'Select a user by field value (id, slug, email, or login). i.e. "id: 1" or "login: administrator"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$user_string = $operation->event()->replaceTokens( $values[$field_name], $arg_map );
						$pieces = explode( ':', $user_string );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id', 'slug', 'email', 'login' ) ) ) {
							return get_user_by( $field, $attribute );
						}
					},
				);
				break;
				
			/* Multiple Users */
			case 'users':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Users', 'mwp-rules' ),
							'description' => __( 'Enter each user selection on a new line identified by field value (id, slug, email, or login). i.e. "id: 1" or "login: administrator"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$user_strings = explode( "\n", $operation->event()->replaceTokens( $values[ $field_name ], $arg_map ) );
						$users = array();
						foreach( $user_strings as $user_string ) {
							$pieces = explode( ':', $user_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id', 'slug', 'email', 'login' ) ) ) {
								$users[] = get_user_by( $field, $attribute );
							}
						}
						
						return array_filter( $users );
					},
				);
				break;
				
			/* Individual Post */
			case 'post':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'label' => __( 'Post', 'mwp-rules' ),
							'description' => __( 'Select a post by field value (id). i.e. "id: 1"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->event()->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id' ) ) ) {
							return get_post( $attribute );
						}
					},
				);
				break;
			
			/* Multiple Posts */
			case 'posts':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Posts', 'mwp-rules' ),
							'description' => __( 'Enter each post selection on a new line identified by field value (id). i.e. "id: 1"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$post_strings = explode( "\n", $operation->event()->replaceTokens( $values[ $field_name ], $arg_map ) );
						$posts = array();
						foreach( $post_strings as $post_string ) {
							$pieces = explode( ':', $post_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id' ) ) ) {
								$posts[] = get_post( $attribute );
							}
						}
						
						return array_filter( $posts );
					},
				);
				break;
				
			/* Individual Comment */
			case 'comment':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'label' => __( 'Comment', 'mwp-rules' ),
							'description' => __( 'Select a comment by field value (id). i.e. "id: 1"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->event()->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id' ) ) ) {
							return get_comment( $attribute );
						}
					},
				);
				break;
			
			/* Multiple Comments */
			case 'comments':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Comments', 'mwp-rules' ),
							'description' => __( 'Enter each comment selection on a new line identified by field value (id). i.e. "id: 1"', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$comment_strings = explode( "\n", $operation->event()->replaceTokens( $values[ $field_name ], $arg_map ) );
						$comments = array();
						foreach( $comment_strings as $comment_string ) {
							$pieces = explode( ':', $comment_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id' ) ) ) {
								$comments[] = get_comment( $attribute );
							}
						}
						
						return array_filter( $comments );
					},
				);
				break;
				
			/* Meta Data */
			case 'meta_values':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Meta Values', 'mwp-rules' ),
							'description' => __( 'Enter meta values one per line, in the format of "meta_key: meta_value".', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'meta_key: meta_value' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$meta_values = array();
						$meta_strings = explode( "\n", $operation->event()->replaceTokens( $values[ $field_name ], $arg_map ) );
						foreach( $meta_strings as $meta_string ) {
							$pieces = explode( ':', $meta_string );
							$key = trim( array_shift( $pieces ) );
							$value = trim( implode( ':', $pieces ) );
							if ( $key ) {
								$meta_values[ $key ] = $value;
							}
						}
						
						return $meta_values;
					}
				);
				break;
				
			/* Individual Term */
			case 'term':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'label' => __( 'Taxonomy Term', 'mwp-rules' ),
							'description' => "<div class='alert alert-info'>" . __( 'Select a term by field value (id, slug, or name). When identifying a term by it\'s slug or name, you must also specify the taxonomy to get it from.', 'mwp-rules' ) . "</div>" . __( 'Examples:', 'mwp-rules' ) . "<br><br><pre>id: 1&#10;slug: taxonomy_name/term-slug&#10;name: taxonomy_name/Term Name</pre>",
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->event()->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id', 'slug', 'name' ) ) ) {
							if ( $field == 'id' ) {
								return get_term( (int) $attribute ) ?: null;
							}
							$more_pieces = explode( '/', $attribute );
							$taxonomy = array_shift( $more_pieces );
							$attribute = implode( '/', $more_pieces );
							return get_term_by( $field, $attribute, $taxonomy ) ?: null;
						}
					},
				);
				break;
			
			/* Multiple Terms */
			case 'terms':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'label' => __( 'Taxonomy Terms', 'mwp-rules' ),
							'description' => "<div class='alert alert-info'>" . __( 'Enter each term selection on a new line identified by field value (id, slug, or name). When identifying a term by it\'s slug or name, you must also specify the taxonomy to get it from.', 'mwp-rules' ) . "</div>" . __( 'Examples:', 'mwp-rules' ) . "<br><br><pre>id: 1&#10;slug: taxonomy_name/term-slug&#10;name: taxonomy_name/Term Name</pre>",
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$term_strings = explode( "\n", $operation->event()->replaceTokens( $values[ $field_name ], $arg_map ) );
						$terms = array();
						foreach( $term_strings as $term_string ) {
							$pieces = explode( ':', $term_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id', 'slug', 'name' ) ) ) {
								if ( $field == 'id' ) {
									$terms[] = get_term( (int) $attribute );
									continue;
								}
								$more_pieces = explode( '/', $attribute );
								$taxonomy = array_shift( $more_pieces );
								$attribute = implode( '/', $more_pieces );
								$terms[] = get_term_by( $field, $attribute, $taxonomy );
							}
						}
						
						return array_filter( $terms );
					},
				);
				break;
			
		}
		
		return apply_filters( 'rules_config_preset', $config, $key, $field_name, $options );
	}

	/**
	 * Schedule An Action
	 *
	 * @param 	\MWP\Rules\Action	$action			The action to schedule
	 * @param	int					$time			The timestamp of when the action is scheduled
	 * @param	array				$args			The arguments to send to the action
	 * @param	array				$event_args		The arguments from the event
	 * @param	string				$thread			The event thread to tie the action back to (for debugging)
	 * @param	string				$parentThread	The events parent thread to tie the action back to (for debugging)
	 * @param	string|NULL			$unique_key		A unique key to identify the action for later updating/removal
	 * @return	mixed								A message to log to the database if debugging is on
	 */
	public function scheduleAction( $action, $time, $args, $event_args, $thread, $parentThread, $unique_key=NULL )
	{
		/**
		 * Delete any existing action with the same unique key
		 */
		if ( isset( $unique_key ) and trim( $unique_key ) != '' ) {
			foreach( ScheduledAction::loadWhere( 'schedule_unique_key=?', trim( $unique_key ) ) as $existing ) {
				$existing->delete();
			}
		}
		
		$scheduled_action                = new \MWP\Rules\ScheduledAction;		
		$scheduled_action->time          = $time;
		$scheduled_action->action_id     = $action->id;
		$scheduled_action->thread        = $thread;
		$scheduled_action->parent_thread = $parentThread;
		$scheduled_action->created       = time();
		$scheduled_action->unique_key    = trim( $unique_key );
		
		$db_args = array();
		foreach ( $args as $arg ) {
			$db_args[] = $this->storeArg( $arg );
		}
		
		$db_event_args = array();
		foreach ( $event_args as $key => $arg ) {
			$db_event_args[ $key ] = $this->storeArg( $arg );
		}
		
		$scheduled_action->data = array(
			'args' => $db_args,
			'event_args' => $db_event_args,
		);
		
		$scheduled_action->save();
		
		$_next_action = ScheduledAction::getNextAction();
		$task = Task::loadWhere( array( 'task_action=%s AND task_completed=0', 'mwp_rules_run_scheduled_actions' ) )[0];
		
		if ( $task ) {
			if ( ! $task->running ) {
				$task->next_start = $_next_action->time;
				$task->save();
			}
		} else {
			Task::queueTask( array( 'action' => 'mwp_rules_run_scheduled_actions', 'next_start' => $_next_action->time ) );
		}
		
		return "Action Scheduled (ID#{$scheduled_action->id})";
	}

	/**
	 * Prepare an argument for database storage
	 *
	 * Known objects are stored in a way that they can be easily reconstructed
	 * into original form. All other objects will be cast into stdClass when restored.
	 *
	 * @param 	mixed		$arg		The argument to store
	 * @return	mixed					An argument which can be json encoded
	 */
	public function storeArg( $arg )
	{
		/* Walk through arrays recursively to store arguments */
		if ( is_array( $arg ) )
		{
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg )
			{
				$arg_array[ $k ] = $this->storeArg( $_arg );
			}
			
			return $arg_array;
		}
		
		if ( ! is_object( $arg ) )
		{
			return $arg;
		}
		
		$arg_class = get_class( $arg );
		$data = apply_filters( 'rules_store_object', NULL, $arg, $arg_class );

		return ( $data !== NULL ) ? array( '_obj_class' => $arg_class, 'data' => $data ) : array( '_obj_class' => 'stdClass', 'data' => (array) $arg );
	}

	/**
	 * Restore an argument from database storage
	 *
	 * @param 	object		$arg		The argument to restore
	 * @return	mixed					The restored argument
	 */
	public function restoreArg( $arg )
	{
		if ( ! is_array( $arg ) )
		{
			return $arg;
		}
		
		/* If the array is not a stored object reference, walk through elements recursively to restore values */
		if ( ! isset ( $arg[ '_obj_class' ] ) )
		{
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg )
			{
				$arg_array[ $k ] = $this->restoreArg( $_arg );
			}

			return $arg_array;
		}
		
		return apply_filters( 'rules_restore_object', NULL, $arg['data'], $arg['_obj_class'] ) ?: (object) $arg['data'];		
	}

	/**
	 * Recursion Protection
	 */
	public $logLocked = FALSE;
	
	/**
	 * Create a Rules Log
	 *
	 * @param	\IPS\rules\Event		$event		The event associated with the log
	 * @param	\IPS\rules\Rule|NULL	$rule		The rule associated with the log
	 * @param	\IPS\rules\Action		$operation	The condition or action associated with the log
	 * @param	mixed					$result		The value returned by the operation or log event
	 * @param	string					$message	The reason for the log
	 * @param	int						$error		The error code, or zero indicating a debug log
	 * @return 	void
	 */
	public function rulesLog( $event, $rule, $operation, $result, $message='', $error=0 )
	{
		if ( ! $this->logLocked )
		{
			$this->logLocked = TRUE;
			
			$log 				= new \MWP\Rules\Log;
			$log->thread 		= is_object( $event ) 		? $event->thread			: NULL;
			$log->parent		= is_object( $event )		? $event->parentThread		: NULL;
			$log->event_type    = is_object( $event )       ? $event->type              : NULL;
			$log->event_hook	= is_object( $event ) 		? $event->hook				: NULL;
			$log->rule_id		= is_object( $rule )		? $rule->id					: 0;
			$log->rule_parent 	= is_object ( $rule ) 		? $rule->parent_id			: 0; 
			$log->op_id			= is_object( $operation ) 	? $operation->id			: 0;
			$log->type 			= is_object( $operation ) 	? get_class( $operation )	: NULL;
			$log->result 		= json_encode( $result );
			$log->message 		= $message;
			$log->error			= $error;
			$log->time 			= time();
			
			$log->save();
			
			$this->logLocked = FALSE;
		}
	}
	
	/**
	 * Shutdown Rules: Execute queued actions
	 *
	 * @return	void
	 */ 
	public function shutDown()
	{
		if ( ! $this->shuttingDown )
		{
			/* No more actions should be queued from this point forward */
			$this->shuttingDown = TRUE;
			
			/**
			 * Run end of page queued actions
			 */
			while( $queued = array_shift( $this->actionQueue ) )
			{
				$event = $queued[ 'event' ];
				$action = array( $queued[ 'action' ] );
				
				$event->executeDeferred( $action );
			}
		}
	}
}

register_shutdown_function( function() { 
	\MWP\Rules\Plugin::instance()->shutDown(); 
});
