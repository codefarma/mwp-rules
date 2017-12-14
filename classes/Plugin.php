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

use MWP\Rules\ECA\Loader;
use MWP\Rules\ECA\Token;
use MWP\Rules\Rule;

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
	 * Main Stylesheet
	 *
	 * @Wordpress\Stylesheet
	 */
	public $mainStyle = 'assets/css/style.css';
	
	/**
	 * Main Javascript Controller
	 *
	 * @Wordpress\Script( deps={"mwp"} )
	 */
	public $mainScript = 'assets/js/main.js';
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @Wordpress\Action( for="wp_enqueue_scripts" )
	 *
	 * @return	void
	 */
	public function enqueueScripts()
	{
		
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
	 * Invoke An Operation
	 *
	 * @param	\IPS\Node\Model		$operation		A condition/action object to evaluate
	 * @param	string				$optype			The type of operation which the operation is (actions/conditions)
	 * @param	array				$args			The arguments the operation was invoked with
	 * @return	mixed
	 */
	public function opInvoke( $operation, $optype, $args )
	{		
		if ( ( $definition = $operation->definition() ) !== NULL )
		{	
			$arg_map         = array();
			$operation_args  = array();
			$event_arg_index = array();
			$i               = 0;
			$event           = $operation->event();
			
			if ( isset( $event->arguments ) and count( $event->arguments ) )
			{
				// Name each argument in the argument map
				foreach ( $event->arguments as $event_arg_name => $event_arg )
				{
					$arg_map[ $event_arg_name ] = $args[ $i ];
					$event_arg_index[ $event_arg_name ] = $i++;
				}
			}
			
			try
			{
				if ( isset( $definition->arguments ) and is_array( $definition->arguments ) )
				{
					/* Put together the argument list needed by this operation */
					foreach ( $definition->arguments as $arg_name => $arg )
					{
						$argument_missing 	= FALSE;
						$argNameKey 		= $operation->key . '_' . $arg_name;
						
						/* Check which source the user has configured for the argument data */
						switch ( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] )
						{
							/**
							 * Grab argument from event
							 */
							case 'event':
							
								/**
								 * Determine which argument index to use and if the argument
								 * needs class conversion or not
								 */
								$parts = explode( ':', $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg' ] );
								$event_arg_name = isset( $parts[ 0 ] ) ? $parts[ 0 ] : NULL;
								$converter_class = isset( $parts[ 1 ] ) ? $parts[ 1 ] : NULL;
								$converter_key = isset( $parts[ 2 ] ) ? $parts[ 2 ] : NULL;
								
								$_operation_arg	= NULL;
								$input_arg 	= NULL;
								$input_arg_type	= NULL;
								
								/**
								 * Get input argument from global arguments
								 */
								if ( mb_substr( $event_arg_name, 0, 9 ) === '__global_' )
								{
									$global_arguments = $this->getGlobalArguments();
									if ( isset ( $global_arguments[ $event_arg_name ] ) )
									{
										if ( is_callable( $global_arguments[ $event_arg_name ][ 'getArg' ] ) )
										{
											$input_arg = call_user_func_array( $global_arguments[ $event_arg_name ][ 'getArg' ], array() );
										}
										$input_arg_type = $global_arguments[ $event_arg_name ][ 'argtype' ];
									}
								}
								
								/**
								 * Get input argument from event arguments
								 */
								else
								{
									if ( isset( $event_arg_index[ $event_arg_name ] ) )
									{
										$_i = $event_arg_index[ $event_arg_name ];
										$input_arg = $args[ $_i ];
										$input_arg_type = $event->arguments[ $event_arg_name ][ 'argtype' ];
									}
								}
								
								/**
								 * Check if argument is present in the event
								 */
								if ( isset ( $input_arg ) )
								{									
									/**
									 * If an argument has been chosen which is a "derivative" of an actual event argument,
									 * then we need to pass the event argument to the conversion function to get the
									 * correct derivative value.
									 */
									if ( $converter_class and $converter_key )
									{
										$classConverters = $this->getConversions();
										if 
										( 
											isset ( $classConverters[ $converter_class ][ $converter_key ] ) and 
											is_callable( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ] ) 
										)
										{									
											$event_arg 	= call_user_func( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ], $input_arg );
											$event_arg_type	= $classConverters[ $converter_class ][ $converter_key ][ 'argtype' ];
										}
										else
										{
											$event_arg 	= NULL;
											$event_arg_type = NULL;
										}
									}
									else
									{
										$event_arg 	= $input_arg;
										$event_arg_type = $input_arg_type;
									}
									
									/**
									 * Argtypes must be defined to use event arguments
									 */
									if ( is_array( $arg[ 'argtypes' ] ) )
									{
										/* Simple definitions with no processing callbacks */
										if ( in_array( $event_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
										{
											$_operation_arg = $event_arg;
										}
										
										/* Complex definitions, check for processing callbacks */
										else if ( isset( $arg[ 'argtypes' ][ $event_arg_type ] ) )
										{
											if ( isset ( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) )
											{
												$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ], array( $event_arg, $operation->data[ 'configuration' ][ 'data' ] ) );
											}
											else
											{
												$_operation_arg = $event_arg;
											}
										}
										else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
										{
											if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) )
											{
												$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $event_arg, $operation->data[ 'configuration' ][ 'data' ] ) );
											}
											else
											{
												$_operation_arg = $event_arg;
											}
										
										}
									}
								}
								
								/**
								 * After all that, check if we have an argument to pass
								 */
								if ( isset( $_operation_arg ) )
								{
									$operation_args[] = $_operation_arg;
								}
								else
								{
									$argument_missing = TRUE;
								}			
								break;
							
							/**
							 * Grab manual entry argument
							 */
							case 'manual':
							
								/**
								 * Arguments received from manual configuration callbacks are not passed through any processing callbacks
								 * because it is expected that the designer of the operation will return an argument that is
								 * already in a state that can be passed directly to the operation callback.
								 */
								if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
								{
									$operation_args[] = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
								}
								else
								{
									$argument_missing = TRUE;
								}
								break;
							
							/**
							 * Calculate an argument using PHP
							 */
							case 'phpcode':
							
								$evaluate = function( $phpcode ) use ( $arg_map )
								{
									extract( $arg_map );								
									return @eval( $phpcode );
								};
								
								$argVal = $evaluate( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] );
								
								if ( isset( $argVal ) )
								{
									if ( is_array( $arg[ 'argtypes' ] ) )
									{
										$type_map = array
										( 
											'integer' 	=> 'int',
											'double'	=> 'float',
											'boolean' 	=> 'bool',
											'string' 	=> 'string',
											'array'		=> 'array',
											'object'	=> 'object',
										);
										
										$php_arg_type = $type_map[ gettype( $argVal ) ];
										
										/* Simple definitions with no value processing callbacks */
										if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
										{
											$operation_args[] = $argVal;
										}
										
										/* Complex definitions, check for value processing callbacks */
										else if ( isset( $arg[ 'argtypes' ][ $php_arg_type ] ) )
										{
											if ( isset ( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) )
											{
												$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
											}
											else
											{
												$operation_args[] = $argVal;
											}
										}
										else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
										{
											if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) )
											{
												$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
											}
											else
											{
												$operation_args[] = $argVal;
											}
										
										}
										else
										{
											$argument_missing = TRUE;
										}
									}
									else
									{
										/**
										 * The argument cannot be processed because argtypes aren't supported
										 */
										$argument_missing = TRUE;
									}
									
								}
								else
								{
									$argument_missing = TRUE;
								}
								break;
								
							default:
							
								$argument_missing = TRUE;
						}
						
						/**
						 * If we haven't obtained a usable argument, use the manual default configuration if applicable
						 */
						if 
						( 
							$argument_missing and 
							$operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] == 'event' and
							$operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_eventArg_useDefault' ]
						)	
						{
							/**
							 * Get the default value from manual configuration setting
							 */
							if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
							{
								$argVal = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $operation->data[ 'configuration' ][ 'data' ], $operation ) );
								if ( isset( $argVal ) )
								{
									$argument_missing = FALSE;
									$operation_args[] = $argVal;
								}
							}
							
							/**
							 * Get the default value from phpcode
							 */
							else
							{
								/* Only if we haven't already attempted to get the argument from phpcode */
								if ( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_source' ] !== 'phpcode' )
								{
									/**
									 * This code is getting a little redundant. I know.
									 */
									$evaluate = function( $phpcode ) use ( $arg_map )
									{
										extract( $arg_map );								
										return @eval( $phpcode );
									};
									
									$argVal = $evaluate( $operation->data[ 'configuration' ][ 'data' ][ $argNameKey . '_phpcode' ] );
									
									if ( isset( $argVal ) )
									{
										if ( is_array( $arg[ 'argtypes' ] ) )
										{
											$type_map = array
											( 
												'integer' 	=> 'int',
												'double'	=> 'float',
												'boolean' 	=> 'bool',
												'string' 	=> 'string',
												'array'		=> 'array',
												'object'	=> 'object',
											);
											
											$php_arg_type = $type_map[ gettype( $argVal ) ];
											
											/* Simple definitions with no processing callbacks */
											if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) )
											{
												$operation_args[] = $argVal;
												$argument_missing = FALSE;
											}
											
											/* Complex definitions, check for processing callbacks */
											else if ( isset( $arg[ 'argtypes' ][ $php_arg_type ] ) )
											{
												if ( isset ( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) )
												{
													$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
												}
												else
												{
													$operation_args[] = $argVal;
												}
												$argument_missing = FALSE;
											}
											else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
											{
												if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) )
												{
													$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $argVal, $operation->data[ 'configuration' ][ 'data' ] ) );
												}
												else
												{
													$operation_args[] = $argVal;
												}
												$argument_missing = FALSE;
											}
										}
									}							
								}
							}
						}

						if ( $argument_missing )
						{
							if ( $arg[ 'required' ] )
							{
								/* Operation cannot be invoked because we're missing a required argument */
								if ( $rule = $operation->rule() and $rule->debug )
								{
									$this->rulesLog( $event, $operation->rule(), $operation, "No argument available for: " . $arg_name, 'Operation skipped (missing argument)' );
								}
								return NULL;
							}
							else
							{
								$operation_args[] = NULL;
							}
						}
					}
				}
				
				/**
				 * Now that we have our argument list, time to execute the operation callback
				 */
				if ( isset( $definition->callback ) and is_callable( $definition->callback ) )
				{
					/**
					 * Perform token replacements on string value arguments
					 */
					$tokens = $this->getTokens( $event, $arg_map );
					foreach ( $operation_args as &$_operation_arg )
					{
						if ( in_array( gettype( $_operation_arg ), array( 'string' ) ) )
						{
							$_operation_arg = $this->replaceTokens( $_operation_arg, $tokens );
						}
					}
					
					try
					{
						/**
						 * Check to see if actions have a future scheduling
						 */
						if ( $operation instanceof \MWP\Rules\Action and $operation->schedule_mode )
						{
							$future_time = 0;
							switch ( $operation->schedule_mode )
							{
								/**
								 * Defer to end of rule processing
								 */
								case 1:
									$result = '__suppress__';
									$event->actionStack[] = array
									(
										'action' 	=> $operation,
										'args' 	 	=> $operation_args,
										'event_args' 	=> $arg_map,
										'thread' 	=> $event->thread,
										'parent' 	=> $event->parentThread,
									);
									break;
									
								/**
								 * Set amount of time in the future 
								 */
								case 2:
									$future_time = \strtotime
									( 
										'+' . intval( $operation->schedule_months ) . ' months ' . 
										'+' . intval( $operation->schedule_days ) . ' days ' .
										'+' . intval( $operation->schedule_hours ) . ' hours ' .
										'+' . intval( $operation->schedule_minutes ) . ' minutes '
									);
									break;
									
								/**
								 * On a specific date/time
								 */
								case 3:
									$future_time = $operation->schedule_date;
									break;
									
								/**
								 * On a calculated date
								 */
								case 4:
									$evaluate = function( $phpcode ) use ( $arg_map )
									{
										extract( $arg_map );
										return @eval( $phpcode );
									};
									
									$custom_time = $evaluate( $operation->schedule_customcode );
									
									if ( is_numeric( $custom_time ) )
									{
										$future_time = intval( $custom_time );
									}
									else if ( is_object( $custom_time ) )
									{
										if ( $custom_time instanceof \IPS\DateTime )
										{
											$future_time = $custom_time->getTimestamp();
										}
									}
									else if ( is_string( $custom_time ) )
									{
										$future_time = strtotime( $custom_time );
									}
									break;
									
								/**
								 * At the end of the page load
								 */
								case 5:
								
									if ( ! $this->shuttingDown )
									{
										$result = '__suppress__';
										$this->actionQueue[] = array
										(
											'event'	=> $event,
											'action' => array
											(
												'action' 	=> $operation,
												'args' 	 	=> $operation_args,
												'event_args' 	=> $arg_map,
												'thread' 	=> $event->thread,
												'parent' 	=> $event->parentThread,
											),
										);
									}
									else
									{
										$result = 'Action skipped. Page shut down already initiated.';
									}
									break;
									
							}
							
							if ( $future_time > time() )
							{
								$thread = $parentThread = NULL;
								
								if ( $rule = $operation->rule() )
								{
									$thread 	= $rule->event()->thread;
									$parentThread 	= $rule->event()->parentThread;
								}
								
								$unique_key = $operation->schedule_key ? $this->replaceTokens( $operation->schedule_key, $tokens ) : NULL;
								$result = $this->scheduleAction( $operation, $future_time, $operation_args, $arg_map, $thread, $parentThread, $unique_key );
							}
							
						}
					
						/**
						 * If our operation was scheduled, then it will have a result already from the scheduler
						 */
						if ( ! isset ( $result ) )
						{
							$result = call_user_func_array( $definition->callback, array_merge( $operation_args, array( $operation->data[ 'configuration' ][ 'data' ], $arg_map, $operation ) ) );					
						}
						
						/**
						 * Conditions have a special setting to invert their result with NOT, so let's check that 
						 */
						if ( $operation instanceof \MWP\Rules\Condition and $operation->not )
						{
							$result = ! $result;
						}
						
						if ( $rule = $operation->rule() and $rule->debug and $result !== '__suppress__' )
						{
							$this->rulesLog( $rule->event(), $rule, $operation, $result, 'Evaluated' );
						}
						
						return $result;
					}
					catch ( \Exception $e ) 
					{
						/**
						 * Log exceptions that happen during operation execution
						 */
						$event = $operation->rule() ? $operation->rule()->event() : NULL;
						$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
						$file = array_pop( $paths );
						$this->rulesLog( $event, $operation->rule(), $operation, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
					}
				}
				else
				{
					if ( $rule = $operation->rule() )
					{
						$this->rulesLog( $rule->event(), $rule, $operation, FALSE, 'Missing Callback', 1  );
					}
				}
			}
			catch ( \Exception $e )
			{
				/**
				 * Log exceptions that happen during argument preparation
				 */
				$event = $operation->rule() ? $operation->rule()->event() : NULL;
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				$this->rulesLog( $event, $operation->rule(), $operation, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, "Argument Callback Exception ({$arg_name})", 1 );
			}
		}
		else
		{
			/**
			 * Log non-invokable action
			 */
			$event = $operation->rule() ? $operation->rule()->event() : NULL;
			$this->rulesLog( $event, $operation->rule(), $operation, FALSE, 'Operation aborted. (Missing Definition)', 1 );		
		}
	}	
	
	/**
	 * Get Usable Event Arguments
	 *
	 * @param	array			$arg		The argument definition
	 * @param	\IPS\Node\Model		$operation	The condition or action node
	 * @return	array					An array of additional arguments that can be derived from the event
	 */
	public function usableEventArguments( $arg, $operation )
	{
		$_usable_arguments = array();
		$event = $operation->event();
		
		if ( isset( $arg[ 'argtypes' ] ) )
		{
			if ( isset( $event->arguments ) )
			{
				/* Add in global arguments */
				$all_arguments = array_merge( $event->arguments ?: array(), $this->getGlobalArguments() );
				
				if ( is_array( $all_arguments ) and count( $all_arguments ) )
				{
					/**
					 * Create an array of argtypes that are acceptable as an
					 * operation argument
					 */
					$_types = array();
					foreach ( $arg[ 'argtypes' ] as $type => $typedata )
					{
						$_types[] = is_array( $typedata ) ? $type : $typedata;
					}
						
					/**
					 * For every available event/global argument, see if we can use it
					 * by comparing it to the acceptable argtypes
					 */
					foreach( $all_arguments as $event_arg_name => $event_argument )
					{
						$type_def = array();
						
						/**
						 * Check if the event argument itself is supported
						 */
						if ( in_array( 'mixed', $_types ) or in_array( $event_argument[ 'argtype' ], $_types ) )
						{
							$can_use = TRUE;
							
							/* Our operation argument type definition */
							$type_def = isset( $arg[ 'argtypes' ][ $event_argument[ 'argtype' ] ] ) ? $arg[ 'argtypes' ][ $event_argument[ 'argtype' ] ] : $arg[ 'argtypes' ][ 'mixed' ];
							
							/* If it's not an array, then it doesn't have any special needs */
							if ( is_array( $type_def ) and ! empty ( $type_def ) )
							{
								/* If a special class of argument is required, see if the event argument is compliant */
								if ( isset( $type_def[ 'class' ] ) )
								{
									if ( ! isset( $event_argument[ 'class' ] ) or ! $this->classCompliant( $event_argument[ 'class' ], $type_def[ 'class' ] ) )
									{
										$can_use = FALSE;
									}
								}
							}
							
							/* So can we use it or what! */
							if ( $can_use )
							{
								$_usable_arguments[ $event_arg_name ] = $event_argument;
							}
						}
						
						/**
						 * Add in any other arguments that we can derive from the event argument as options also
						 */
						if ( $event_argument[ 'argtype' ] == 'object' and isset( $event_argument[ 'class' ] ) )
						{
							if ( $derivative_arguments = $this->classConverters( $event_argument, $type_def ) )
							{
								foreach ( $derivative_arguments as $map_key => $derivative_argument )
								{
									if ( in_array( 'mixed', $_types ) or in_array( $derivative_argument[ 'argtype' ], $_types ) )
									{
										$_usable_arguments[ $event_arg_name . ":" . $map_key ] = $derivative_argument;
									}
								}
							}						
						}				
					}
				}
			}
		}
		
		return $_usable_arguments;
	}

	/**
	 * @var	array
	 */
	public static $tokensCache = array();

	/**
	 * Build Event Tokens
	 *
	 * @param 	\IPS\rules\Event 	$event 		The rules event
	 * @param	array|NULL		$arg_map	An associative array of the event arguments, if NULL then token/descriptions will be generated
	 * @return	array					An associative array of token/var replacements
	 */
	public function getTokens( $event, $arg_map=NULL )
	{
		$cache_key = isset ( $arg_map ) ? $event->thread : 'descriptions';
		
		if ( isset ( static::$tokensCache[ $cache_key ] ) )
		{
			return static::$tokensCache[ $cache_key ];
		}
		
		$global_args 		= $this->getGlobalArguments();
		$classConverters 	= $this->getConversions();
		$replacements 		= array();		
		$string_types 		= array( 'string', 'int', 'float' );
		
		$arg_groups = array
		(
			'event' => $event->arguments ?: array(),
			'global' => $global_args,
		);
		
		foreach ( $arg_groups as $group => $all_arguments )
		{
			foreach( $all_arguments as $arg_name => $argument )
			{
				/**
				 * Check if the event argument is string replaceable
				 */
				if ( in_array( $argument[ 'argtype' ], $string_types ) )
				{
					/* Building token values */
					if ( isset ( $arg_map ) )
					{
						$replacements[ '[' . $arg_name . ']' ] = $replacements[ '~' . $arg_name . '~' ] = (string) $arg_map[ $arg_name ];
					}
					/* Building token description */
					else
					{
						$replacements[ '[' . $arg_name . ']' ] = "The value of the '" . $arg_name . "' argument";
					}
				}

				/**
				 * Add in any other arguments that we can derive from the event argument as options also
				 */
				if ( in_array( $argument[ 'argtype' ], array( 'object', 'array' ) ) and isset( $argument[ 'class' ] ) )
				{				
					if ( $derivative_arguments = $this->classConverters( $argument ) )
					{
						foreach ( $derivative_arguments as $map_key => $derivative_argument )
						{
							list( $converter_class, $converter_key ) = explode( ':', $map_key );
							
							if ( in_array( $derivative_argument[ 'argtype' ], $string_types ) or isset( $classConverters[ $converter_class ][ $converter_key ][ 'tokenValue' ] ) )
							{
								if 
								( 
									isset ( $classConverters[ $converter_class ][ $converter_key ][ 'token' ] ) and 
									is_callable( $classConverters[ $converter_class ][ $converter_key ][ 'converter' ] ) 
								)
								{
									$input_arg = NULL;
									$arg_name_token = NULL;
									$arg_name_description = NULL;
									$tokenValue = '';
									
									/**
									 * Building Token Values
									 */
									if ( isset ( $arg_map ) )
									{
										switch( $group )
										{
											case 'event':
											
												$input_arg = $arg_map[ $arg_name ];
												$arg_name_token = $arg_name;
												break;
												
											case 'global':
										
												if 
												( 
													isset( $global_args[ $arg_name ] ) and 
													isset( $global_args[ $arg_name ][ 'token' ] ) and
													is_callable( $global_args[ $arg_name ][ 'getArg' ] ) )
												{
													$arg_name_token = 'global:' . $global_args[ $arg_name ][ 'token' ];
													$input_arg = call_user_func( $global_args[ $arg_name ][ 'getArg' ] );
												}
												break;
										}
										
										if ( isset( $arg_name_token ) )
										{
											/* Tokens will only be calculated if needed */
											$tokenValue = new Token( $input_arg, $classConverters[ $converter_class ][ $converter_key ] );	
											$replacements[ '[' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . ']' ] = $replacements[ '~' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . '~' ] = $tokenValue;
										}
									}
									
									/**
									 * Building Token Descriptions
									 */
									else
									{
										switch ( $group )
										{
											case 'event':
												$arg_name_token = $arg_name;
												break;
											
											case 'global':
												if ( 
													isset( $global_args[ $arg_name ] ) and 
													isset( $global_args[ $arg_name ][ 'token' ] )
												)
												{
													$arg_name_token = 'global:' . $global_args[ $arg_name ][ 'token' ];
													$arg_name_description = ( isset( $global_args[ $arg_name ][ 'description' ] ) and $global_args[ $arg_name ][ 'description' ] ) ? ' for ' . $global_args[ $arg_name ][ 'description' ] : '';
												}
												break;
										}
										
										if ( isset( $arg_name_token ) )
										{
											$replacements[ '[' . $arg_name_token . ":" . $classConverters[ $converter_class ][ $converter_key ][ 'token' ] . ']' ] = $classConverters[ $converter_class ][ $converter_key ][ 'description' ] . $arg_name_description;
										}
									}
								}
							}
						}
					}						
				}				
			}
		}
				
		return static::$tokensCache[ $cache_key ] = $replacements;
	}

	/**
	 * Replace Tokens
	 * 
	 * @param 	string		$string				The string with possible tokens to replace
	 * @param	array		$replacements		An array of string replacement values
	 * @return	string							The string with tokens replaced
	 */
	public function replaceTokens( $string, $replacements )
	{
		if ( empty( $replacements ) or ! is_array( $replacements ) )
		{
			return $string;
		}
		
		return strtr( $string, $replacements );
	}

	/**
	 * Global Arguments
	 */
	public $globalArguments;
	
	/**
	 * Get Global Arguments
	 *
	 * @return 	array		Keyed array of global arguments
	 */
	public function getGlobalArguments()
	{
		if ( isset ( $this->globalArguments ) ) {
			return $this->globalArguments;
		}
		
		$this->globalArguments = array();
		
		foreach( apply_filters( 'rules_global_arguments', array() ) as $arg_name => $arg ) {
			$this->globalArguments[ '__global_' . $arg_name ] = $arg;
		}
		
		return $this->globalArguments;
	}
	
	/**
	 * Class Converters
	 *
	 * Based on the argument provided, returns an array map of alternative arguments that it can
	 * be converted into
	 *
	 * @param	array	$event_argument		The argument definition provided by the event
	 * @param	array	$type_def		The argument definition required by the operation
	 * @return	array				Class converter methods
	 */
	public function classConverters( $event_argument, $type_def=array() )
	{
		if ( ! isset( $event_argument[ 'class' ] ) )
		{
			return array();
		}
		
		$conversion_arguments   = array();
		$mappings               = array();
		$current_class          = $event_argument[ 'class' ]; 
		$acceptable_classes     = isset( $type_def[ 'class' ] ) ? (array) $type_def[ 'class' ] : array();
		
		/**
		 * If the operation argument does not require any specific
		 * class(es) of object, then any class is acceptable
		 */
		if ( empty ( $acceptable_classes ) )
		{
			$acceptable_classes = array( '*' );
		}

		/**
		 * Build a map of all the classes in our converter map that are compliant 
		 * with our event argument, meaning our event argument is the same as or a
		 * subclass of the convertable class
		 */
		foreach ( $this->getConversions() as $base_class => $conversions )
		{
			if ( $this->classCompliant( $current_class, $base_class ) )
			{
				$mappings[ $base_class ] = $conversions;
			}
		}
		
		/**
		 * For every class that has conversions available and that our event argument is compliant with,
		 * we look at each of the conversion options available and see if any of them convert into a class
		 * that can then be used as an operation argument. 
		 */
		foreach ( $mappings as $base_class => $conversions )
		{
			foreach ( $conversions as $conversion_key => $argument )
			{
				foreach ( $acceptable_classes as $acceptable_class )
				{
					if ( $acceptable_class === '*' or ( isset( $argument[ 'class' ] ) and $this->classCompliant( $argument[ 'class' ], $acceptable_class ) ) )
					{
						$conversion_arguments[ $base_class . ':' . $conversion_key ] = $argument;
					}
				}
			}
		}
		
		return $conversion_arguments;
	}

	/**
	 * Check For Class Compliance
	 *
	 * @param	string 		$class		Class to check compliance
	 * @param	string|array	$classes	A classname or array of classnames to validate against
	 * @return	bool				Will return TRUE if $class is the same as or is a subclass of any $classes
	 */
	public function classCompliant( $class, $classes )
	{
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
	 * Class map
	 */
	public $classMap;
	
	/**
	 * Get Class Conversion Mappings
	 * 
	 * @param 	string|NULL		$class		A specific class to return conversions for, NULL for all
	 * @return	array						Class conversion definitions
	 */
	public function getConversions( $class=NULL )
	{
		if ( isset ($this->classMap ) ) {
			return isset( $class ) ? $this->classMap[ $class ] : $this->classMap;
		}
		
		$this->classMap = apply_filters( 'rules_conversions_map', array() );
		
		return $this->getConversions( $class );		
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
			foreach( \MWP\Rules\ScheduledAction::loadWhere( 'schedule_unique_key=?', trim( $unique_key ) ) as $existing ) {
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
		return; // @TODO: implement for MWP Rules
		print_r( $event->hook . "({$event->thread})" . ( $rule ? " / " . $rule->title : "") . ( $operation ? " / " . $operation->title : "") . " --> " . $message . ": " . json_encode( $result ) . "\n" );
		
		if ( ! $this->logLocked )
		{
			$this->logLocked = TRUE;
			
			$log 				= new \MWP\Rules\ECA\Log;
			$log->thread 		= is_object( $event ) 		? $event->thread			: NULL;
			$log->parent		= is_object( $event )		? $event->parentThread		: NULL;
			$log->event_type    = is_object( $event )       ? $event->type              : NULL;
			$log->event_name	= is_object( $event ) 		? $event->hook				: NULL;
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
