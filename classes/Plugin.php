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
	 * @return 	array		Keyed array of global arguments
	 */
	public function getGlobalArguments()
	{
		if ( isset ( $this->globalArguments ) ) {
			return $this->globalArguments;
		}
		
		$globalArguments = array(
			'current_user_id' => array(
				'label' => __( 'Current logged in user ID', 'mwp-rules' ),
			),
		);
		
		foreach( apply_filters( 'rules_global_arguments', $globalArguments ) as $arg_name => $arg ) {
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
	public function getClassConverters( $event_argument, $type_def=array() )
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
			if ( $this->isClassCompliant( $current_class, $base_class ) )
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
					if ( $acceptable_class === '*' or ( isset( $argument[ 'class' ] ) and $this->isClassCompliant( $argument[ 'class' ], $acceptable_class ) ) )
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
	public function isClassCompliant( $class, $classes )
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
		if ( isset( $this->classMap ) ) {
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
