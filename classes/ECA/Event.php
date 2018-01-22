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

use MWP\Rules\ECA\Token;

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
	 * Get the event details display
	 *
	 * @return	string
	 */
	public function getDisplayDetails( $rule=NULL )
	{
		return \MWP\Rules\Plugin::instance()->getTemplateContent( 'rules/events/header_overview', array( 'event' => $this, 'rule' => $rule ) );
	}
	
	/**
	 * Get the argument info
	 *
	 * @return	string
	 */
	public function getDisplayArgInfo()
	{
		return \MWP\Rules\Plugin::instance()->getTemplateContent( 'rules/events/arg_info', array( 'event' => $this ) );
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
	 * @return	array								An associative array of token/var replacements
	 */
	public function getTokens( $arg_map=NULL )
	{
		$rulesPlugin = \MWP\Rules\Plugin::instance();
		$cache_key = isset( $arg_map ) ? $this->thread : 'descriptions';
		
		if ( isset ( static::$tokensCache[ $cache_key ] ) ) {
			return static::$tokensCache[ $cache_key ];
		}
		
		$tokens = $this->getArgumentTokens( array( 'argtypes' => array( 'string', 'int', 'float', 'bool' ) ), $arg_map, 2 );
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
	 * Get derivatives
	 *
	 * @param	array|NULL		$target_argument		The argument which is needed (or leave empty to return all derivatives)
	 * @param	array|NULL		$arg_map				An associative array of the event arguments, if NULL then token/descriptions will be generated
	 * @param	int				$depth					The depth of arguments to get tokens for
	 * @return	array
	 */
	public function getArgumentTokens( $target_argument=NULL, $arg_map=NULL, $depth=1 )
	{
		$rulesPlugin        = \MWP\Rules\Plugin::instance();
		$global_args 		= $rulesPlugin->getGlobalArguments();
		$tokens 		    = array();
		$target_types       = array();      
		
		if ( isset( $target_argument['argtypes'] ) ) {
			foreach( (array) $target_argument['argtypes'] as $k => $v ) {
				$target_types[] = is_array( $v ) ? $k : $v;
			}
		}

		$arg_groups = array(
			'event' => $this->arguments ?: array(),
			'global' => $global_args,
		);
		
		foreach( $arg_groups as $group => $all_arguments ) {
			foreach( $all_arguments as $arg_name => $argument ) {
				
				/* Create tokens for directly accessible arguments */
				if ( ! isset( $target_argument ) or in_array( 'mixed', $target_types ) or in_array( $argument['argtype'], $target_types ) ) {
					if ( ! isset( $target_argument['class'] ) or ( isset( $argument['class'] ) and $rulesPlugin->isClassCompliant( $argument['class'], $target_argument['class'] ) ) ) {					
						// Building token values
						if ( isset ( $arg_map ) ) {
							switch( $group ) {
								case 'event': $tokens[ $arg_name ] = new Token( $arg_map[ $arg_name ] ); break;
								case 'global': $tokens[ 'global:' . $arg_name ] = new Token( NULL, 'global:' . $arg_name, $argument ); break;
							}
						}
						
						// Building token description
						else {
							switch( $group ) {
								case 'event': $tokens[ $arg_name ] = '(' . $argument['argtype'] . ') ' . "The value of the '" . $arg_name . "' argument"; break;
								case 'global': $tokens[ 'global:' . $arg_name ] = isset( $argument['label'] ) ? '(' . $argument['argtype'] . ') ' . ucfirst( strtolower( $argument['label'] ) ) : "The global '" . $arg_name . "' value"; break;
							}
						}
					}
				}
				
				/* Create tokens for derivative arguments also */
				foreach ( $rulesPlugin->getDerivativeTokens( $argument, $target_argument, $depth ) as $tokenized_key => $derivative_argument ) {	
					if ( is_callable( $derivative_argument['getter'] ) ) {
						
						// Building token values
						if ( $arg_map !== NULL ) {
							switch( $group ) {
								case 'event':
									$tokens[ $arg_name . ':' . $tokenized_key ] = new Token( $arg_map[ $arg_name ], $tokenized_key, $argument );
									break;
								case 'global':
									if ( ! isset( $argument['getter'] ) or ! is_callable( $argument['getter'] ) ) {	continue; }
									$tokens[ 'global:' . $arg_name . ':' . $tokenized_key ] = new Token( NULL, 'global:' . $tokenized_key );
									break;
							}
						}
						
						// Building token descriptions
						else {
							switch ( $group ) {
								case 'event':
									$tokens[ $arg_name . ":" . $tokenized_key ] = '(' . $derivative_argument['argtype'] . ') ' . $derivative_argument['label'];
									break;
								case 'global':
									if ( ! isset( $argument['getter'] ) or ! is_callable( $argument['getter'] ) ) { continue; }
									$tokens[ 'global:' . $arg_name . ":" . $tokenized_key ] = '(' . $derivative_argument['argtype'] . ') ' . $derivative_argument['label'];
									break;
							}									
						}
					}
				}
			}
		}

		return $tokens;
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
				$rule = $action->rule();
				
				$result = call_user_func_array( $definition->callback, array_merge( $deferred[ 'args' ], array( $action->data, $deferred[ 'event_args' ], $action ) ) );					
				
				$action->locked = FALSE;
				
				if ( $rule and $rule->debug )
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
