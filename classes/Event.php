<?php
/**
 * Plugin Class File
 *
 * Created:   December 4, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Event Class
 */
class Event extends ECABase
{
	/**
	 * @brief	Event Data
	 */
	public $data = NULL;
	
	/**
	 * @brief	Deferred Action Stack
	 */
	public $actionStack = array();
	
	/**
	 * Multiton Cache
	 */
	public static $multitons = array();
	
	/**
	 * Placeholder Flag
	 */
	public $placeholder = FALSE;
	
	/**
	 * API Response Params
	 */
	public $apiResponse = array();
	
	/**
	 * Events Cache
	 */
	protected static $eventsCache = array();
	
	/**
	 * Root Thread ID
	 *
	 * This is the thread for which deferred actions should be executed
	 */
	public $rootThread = NULL;
		
	/**
	 * Thread ID
	 */
	public $thread = NULL;
	
	/**
	 * Parent Thread ID
	 */
	public $parentThread = NULL;
		
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;

	/**
	 * Trigger An Event
	 */
	public function trigger()
	{
		if ( ! $this->locked )
		{
			/* Don't do this during an upgrade */
			if( \IPS\Dispatcher::hasInstance() AND \IPS\Dispatcher::i()->controllerLocation === 'setup' )
			{
				return;
			}
			
			/**
			 * Give each new event triggered a unique thread id so
			 * logs can be tied back to the event that generated them
			 */
			$parentThread = $this->parentThread;
			$this->parentThread = $this->thread;
			$this->thread = md5( uniqid() . mt_rand() );
			
			foreach ( $this->rules() as $rule )
			{
				if ( ! $rule->ruleset() or $rule->ruleset()->enabled )
				{
					if ( $rule->enabled )
					{
						$result = call_user_func_array( array( $rule, 'invoke' ), func_get_args() );
						
						if ( $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $this, $rule, NULL, $result, 'Rule evaluated' );
						}
					}
					else
					{
						if ( $rule->debug )
						{
							\IPS\rules\Application::rulesLog( $this, $rule, NULL, '--', 'Rule not evaluated (disabled)' );
						}
					}
				}
				else
				{
					if ( $rule->debug )
					{
						\IPS\rules\Application::rulesLog( $this, $rule, NULL, '--', 'Rule not evaluated (rule set disabled)' );
					}				
				}
			}
			
			$this->thread = $this->parentThread;
			$this->parentThread = $parentThread;
			
			/** 
			 * Deferred Actions
			 *
			 * Only execute deferred actions at the root thread level
			 */
			if ( $this->thread === $this->rootThread )
			{
				$actions = $this->actionStack;
				$this->actionStack = array();
				$this->executeDeferred( $actions );
			}			
		}
	}
	
	/**
	 * Execute Deferred
	 *
	 * @param	array		$actions		Deferred actions to execute
	 * @return	void
	 */
	public function executeDeferred( $actions )
	{
		$this->locked = TRUE;
		
		while ( $deferred = array_shift( $actions ) )
		{
			$action 		= $deferred[ 'action' ];
			$this->thread 		= isset( $deferred[ 'thread' ] ) ? $deferred[ 'thread' ] : NULL;
			$this->parentThread 	= isset( $deferred[ 'parentThread' ] ) ? $deferred[ 'parentThread' ] : NULL;
			
			/**
			 * Execute the action
			 */					
			try
			{
				$action->locked = TRUE;
				
				$result = call_user_func_array( $action->definition[ 'callback' ], array_merge( $deferred[ 'args' ], array( $action->data[ 'configuration' ][ 'data' ], $deferred[ 'event_args' ], $action ) ) );					
				
				$action->locked = FALSE;
				
				if ( $rule = $action->rule() and $rule->debug )
				{
					\IPS\rules\Application::rulesLog( $this, $rule, $action, $result, 'Evaluated' );
				}
			}
			catch( \Exception $e )
			{
				/**
				 * Log Exceptions
				 */
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				\IPS\rules\Application::rulesLog( $this, $action->rule(), $action, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
			}
		}
		
		$this->locked = FALSE;
		
		/* Reset threads */
		$this->thread = $this->parentThread = $this->rootThread = NULL;	
	}
	
	/**
	 * Get Event Title
	 */
	public function title()
	{
		$lang = \IPS\Member::loggedIn()->language();
		
		if ( $lang->checkKeyExists( $this->app . '_' . $this->class . '_event_' . $this->key ) )
		{
			return $lang->get( $this->app . '_' . $this->class . '_event_' . $this->key );
		}
		
		return 'Untitled ( ' . $this->app . ' / ' . $this->class . ' / ' . $this->key . ' )';
	}
	
	/**
	 * @brief 	Cache for rules
	 */
	protected $rulesCache = NULL;
	
	/**
	 * Get rules attached to this event
	 */
	public function rules()
	{
		if ( isset( $this->rulesCache ) )
		{
			return $this->rulesCache;
		}
		
		try
		{
			return $this->rulesCache = \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_event_app=? AND rule_event_class=? AND rule_event_key=?', $this->app, $this->class, $this->key ) ) );
		}
		catch ( \Exception $e )
		{
			/* Uninstalled */
			return $this->rulesCache = array();
		}
	}
	
	/* hasRules Cache */
	public static $hasRules = array();
		
	/**
	 * Check if rules are attached to an event
	 *
	 * @param 	string	$app		App that defines the action
	 * @param	string	$class		Extension class where action is defined
	 * @param	string	$key		Action key
	 * @param	bool	$enabled	Whether to only count enabled rules
	 * @return	bool
	 */
	public static function hasRules( $app, $class, $key, $enabled=TRUE )
	{	
		if ( isset( static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] ) )
		{
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ];
		}
		
		try
		{
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] = (bool) \IPS\rules\Rule::roots( NULL, NULL, array( array( 'rule_event_app=? AND rule_event_class=? AND rule_event_key=? AND rule_enabled=1', $app, $class, $key ) ) );
		}
		catch( \Exception $e )
		{
			/* Uninstalled */
			return static::$hasRules[ $app ][ $class ][ $key ][ (int) $enabled ] = FALSE;
		}
	}
	
}
