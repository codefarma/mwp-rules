<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\ECA;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Event Class
 */
class Event extends BaseDefinition
{
	/**
	 * @brief	Deferred Action Stack
	 */
	public $actionStack = array();
	
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
	 * Thread tracking stack
	 */
	public $grandparentThreads = array();
		
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * @var bool
	 */
	protected $event_wrapped = false;
	
	/**
	 * @var	array
	 */
	protected $rules = array();
	
	/**
	 * Deploy a rule to wordpress
	 *
	 * @param	Rule		$rule			The rule to deploy
	 * @return	void
	 */
	public function deployRule( $rule )
	{
		if ( ! $this->event_wrapped ) {
			if ( $this->type == "filter" ) {
				add_filter( $this->hook, array( $this, 'begin' ), -999 );
				add_filter( $this->hook, array( $this, 'finish' ), 999 );
			} else {
				add_action( $this->hook, array( $this, 'begin' ), -999 );
				add_action( $this->hook, array( $this, 'finish' ), 999 );			
			}
			
			$this->event_wrapped = true;
		}
		
		if ( ! in_array( $rule, $this->rules ) ) {
			switch( $rule->event_type ) {
				case 'action': add_action( $rule->event_hook, array( $rule, 'invoke' ), $rule->priority, 999 ); break;
				case 'filter': add_filter( $rule->event_hook, array( $rule, 'invoke' ), $rule->priority, 999 ); break;
			}
			$this->rules[] = $rule;
		}
		
		return true;
	}

	/**
	 * Begin an event cycle
	 *
	 * @return	mixed
	 */
	public function begin()
	{
		if ( ! $this->locked )
		{
			/**
			 * Give each new event triggered a unique thread id so
			 * logs can be tied back to the event that generated them
			 */
			$this->grandparentThreads[] = $this->parentThread;
			$this->parentThread = $this->thread;
			$this->thread = md5( uniqid() . mt_rand() );
		}
		
		if ( $this->type == 'filter' ) {
			return func_get_arg(0);
		}
	}
	
	/**
	 * Complete the event cycle
	 *
	 * @return	mixed
	 */
	public function finish()
	{
		if ( ! $this->locked )
		{
			$this->thread = $this->parentThread;
			$this->parentThread = array_shift( $this->grandparentThreads );
			
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
		
		if ( $this->type == 'filter' ) {
			return func_get_arg(0);
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
		$plugin = \MWP\Rules\Plugin::instance();
		$this->locked = TRUE;
		
		while ( $deferred = array_shift( $actions ) )
		{
			$action             = $deferred[ 'action' ];
			$this->thread       = isset( $deferred[ 'thread' ] ) ? $deferred[ 'thread' ] : NULL;
			$this->parentThread = isset( $deferred[ 'parentThread' ] ) ? $deferred[ 'parentThread' ] : NULL;
			
			/**
			 * Execute the action
			 */					
			try
			{
				$action->locked = TRUE;
				
				$definition = $action->definition();
				$result = call_user_func_array( $definition->callback, array_merge( $deferred[ 'args' ], array( $action->data[ 'configuration' ][ 'data' ], $deferred[ 'event_args' ], $action ) ) );					
				
				$action->locked = FALSE;
				
				if ( $rule = $action->rule() and $rule->debug )
				{
					$plugin->rulesLog( $this, $rule, $action, $result, 'Evaluated' );
				}
			}
			catch( \Exception $e )
			{
				/**
				 * Log Exceptions
				 */
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				$plugin->rulesLog( $this, $action->rule(), $action, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
			}
		}
		
		$this->locked = FALSE;
		
		/* Reset threads */
		$this->thread = $this->parentThread = $this->rootThread = NULL;	
	}
}
