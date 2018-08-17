<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\ECA;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

/**
 * Event Class
 */
class _Event extends BaseDefinition
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
		/**
		 * The event needs to be wrapped with beginning and ending callbacks so that all
		 * actions from executed rules can be successfully accumulated until the end of the event
		 */
		if ( ! $this->event_wrapped ) {
			if ( $this->type == "filter" ) {
				add_filter( $this->hook, array( $this, 'begin' ), -2147483645 );
				add_filter( $this->hook, array( $this, 'finish' ), 2147483645 );
			} else {
				add_action( $this->hook, array( $this, 'begin' ), -2147483645 );
				add_action( $this->hook, array( $this, 'finish' ), 2147483645 );			
			}
			
			$this->event_wrapped = true;
		}
		
		if ( ! in_array( $rule, $this->rules ) ) {
			switch( $rule->event_type ) {
				case 'action': add_action( $rule->event_hook, array( $rule, 'invoke' ), $rule->priority, 2147483645 ); break;
				case 'filter': add_filter( $rule->event_hook, array( $rule, 'invoke' ), $rule->priority, 2147483645 ); break;
			}
			$this->rules[] = $rule;
		}
		
		return true;
	}
	
	/**
	 * Get the event details display
	 *
	 * @return	string
	 */
	public function getDisplayDetails( $rule=NULL )
	{
		return Rules\Plugin::instance()->getTemplateContent( 'rules/events/header_overview', array( 'event' => $this, 'rule' => $rule ) );
	}
	
	/**
	 * Get the argument info
	 *
	 * @return	string
	 */
	public function getDisplayArgInfo()
	{
		return Rules\Plugin::instance()->getTemplateContent( 'rules/events/arg_info', array( 'event' => $this ) );
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
	 * @var	array
	 */
	public static $tokensCache = array();

	/**
	 * Build Event Tokens
	 *
	 * @param	array|NULL				$arg_map	An associative array of the event arguments, if NULL then token/descriptions will be generated
	 * @param	MWP\Rules\Rule|NULL		$rule		The associated rule
	 * @return	array								An associative array of token/var replacements
	 */
	public function getTokens( $arg_map=NULL, $rule=NULL )
	{
		$rulesPlugin = Rules\Plugin::instance();
		$cache_key = isset( $arg_map ) ? $this->thread : 'descriptions';
		$cache_key .= ( isset( $rule ) ? $rule->id() : '' );
		
		if ( isset ( static::$tokensCache[ $cache_key ] ) ) {
			return static::$tokensCache[ $cache_key ];
		}
		
		$tokens = $this->getArgumentTokens( array( 'argtypes' => array( 'string', 'int', 'float', 'bool' ) ), $arg_map, 2, FALSE, $rule );
		$_tokens = array();
		
		foreach( $tokens as $key => $value ) {
			$_tokens[ '{{' . $key . '}}' ] = $value;
			if ( $arg_map ) {
				$_tokens[ '~~' . $key . '~~' ] = $value;
			}
		}
		
		return static::$tokensCache[ $cache_key ] = $_tokens;
	}
	
	/**
	 * Get event arguments
	 *
	 * @return	array
	 */
	public function getArguments()
	{
		return $this->arguments ?: array();
	}
	
	/**
	 * Get a specific argument definition for the event if it exists
	 * 
	 * @param	string				$arg_key 				The argument key
	 * @return	array|NULL
	 */
	public function getArgument( $arg_key )
	{
		$arguments = $this->getArguments();
		
		if ( isset( $arguments[ $arg_key ] ) ) {
			return $arguments[ $arg_key ];
		}
		
		return NULL;
	}
	
	/**
	 * Get derivatives
	 *
	 * @param	array|NULL		$target_argument		The argument which is needed (or leave empty to return all derivatives)
	 * @param	array|NULL		$arg_map				An associative array of the event arguments, if NULL then token/descriptions will be generated
	 * @param	int				$depth					The depth of arguments to get tokens for
	 * @param	bool			$include_arbitrary		Include an arbitrary keys representation token in the results
	 * @param	MWP\Rules\Rule	$rule					The associated rule
	 * @return	array
	 */
	public function getArgumentTokens( $target_argument=NULL, $arg_map=NULL, $depth=1, $include_arbitrary=FALSE, $rule=NULL )
	{
		$rulesPlugin        = Rules\Plugin::instance();
		$global_args 		= $rulesPlugin->getGlobalArguments();
		$tokens 		    = array();
		$bundle_args       = array();
		
		if ( $rule and ( $bundle = $rule->getBundle() ) ) {
			foreach( $bundle->getArguments() as $argument ) {
				$bundle_args[ $argument->varname ] = $argument->getProvidesDefinition();
			}
		}
		
		$arg_groups = array(
			'event' => $this->arguments ? $rulesPlugin->getExpandedArguments( $this->arguments ) : array(),
			'bundle' => $bundle_args,
			'global' => $global_args,
		);
		
		foreach( $arg_groups as $group => $all_arguments ) {
			foreach( $all_arguments as $arg_name => $argument ) {
				
				/* Create tokens for directly accessible arguments */
				if ( $rulesPlugin->isArgumentCompliant( $argument, $target_argument ) ) {
					switch( $group ) {
						case 'event': $tokens[ 'event:' . $arg_name ] = isset( $argument['label'] ) ? '(' . $argument['argtype'] . ') ' . ucfirst( strtolower( $argument['label'] ) ) : '(' . $argument['argtype'] . ') ' . "The value of the '" . $arg_name . "' argument"; break;
						case 'global': $tokens[ 'global:' . $arg_name ] = isset( $argument['label'] ) ? '(' . $argument['argtype'] . ') ' . ucfirst( strtolower( $argument['label'] ) ) : "The global '" . $arg_name . "' value"; break;
						case 'bundle': $tokens[ 'bundle:' . $arg_name ] = isset( $argument['label'] ) ? '(' . $argument['argtype'] . ') ' . ucfirst( strtolower( $argument['label'] ) ) : "The bundle '" . $arg_name . "' setting value"; break;
					}
				}
				
				foreach ( $rulesPlugin->getDerivativeTokens( $argument, $target_argument, $depth, $include_arbitrary ) as $tokenized_key => $derivative_argument ) {						
					switch ( $group ) {
						case 'global':
							if ( ! isset( $argument['getter'] ) or ! is_callable( $argument['getter'] ) ) { continue; }
							$tokens[ 'global:' . $arg_name . ":" . $tokenized_key ] = '(' . $derivative_argument['argtype'] . ') ' . $derivative_argument['label'];
							break;
						default:
							$tokens[ $group . ':' . $arg_name . ":" . $tokenized_key ] = '(' . $derivative_argument['argtype'] . ') ' . $derivative_argument['label'];
							break;
					}
				}
			}
		}

		return $tokens;
	}

	/**
	 * Execute Deferred
	 *
	 * @param	array		$actions		Deferred actions to execute
	 * @return	void
	 */
	public function executeDeferred( $actions )
	{
		$plugin = Rules\Plugin::instance();
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
				$rule = $action->rule();
				
				$result = call_user_func_array( $definition->callback, array_merge( $deferred[ 'args' ], array( $action->data, $deferred[ 'event_args' ], $action ) ) );					
				
				$action->locked = FALSE;
				
				if ( $rule and $rule->debug ) {
					$plugin->rulesLog( $this, $rule, $action, $result, 'Evaluated' );
				}
			}
			catch( \Throwable $t ) { $exception = $t; }
			catch( \Exception $e ) { $exception = $e; }
			finally {
				if ( isset( $exception ) ) {
					/**
					 * Log Exceptions
					 */
					$paths = explode( '/', str_replace( '\\', '/', $exception->getFile() ) );
					$file = array_pop( $paths );
					$plugin->rulesLog( $this, $action->rule(), $action, $exception->getMessage() . '<br>Line: ' . $exception->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
					unset( $exception );
				}
			}
		}
		
		$this->locked = FALSE;
		
		/* Reset threads */
		$this->thread = $this->parentThread = $this->rootThread = NULL;	
	}
}
