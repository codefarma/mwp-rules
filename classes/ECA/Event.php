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
		
		if ( isset ( static::$tokensCache[ $cache_key ] ) )
		{
			return static::$tokensCache[ $cache_key ];
		}
		
		$global_args 		= $rulesPlugin->getGlobalArguments();
		$classConverters 	= $rulesPlugin->getConversions();
		$replacements 		= array();		
		$string_types 		= array( 'string', 'int', 'float' );
		
		$arg_groups = array
		(
			'event' => $this->arguments ?: array(),
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
					if ( $derivative_arguments = $rulesPlugin->getClassConverters( $argument ) )
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
