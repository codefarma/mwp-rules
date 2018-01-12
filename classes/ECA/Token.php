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
 * Token Class
 */
class Token
{
	/**
	 * @var	mixed
	 */
	protected $original;
	
	/**
	 * @var	array
	 */
	protected $argument;
	
	/**
	 * @var	string
	 */
	protected $tokenPath;
	
	/**
	 * @var	mixed
	 */
	protected $tokenValue;
	
	/**
	 * @var string
	 */
	protected $stringValue;
	
	/**
	 * @var	bool
	 */
	protected $tokenSet = false;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed			$original		The starting value to get the token value from
	 * @param	array			$argument		The starting argument definition
	 * @param	string|NULL		$tokenPath		The token path to take to get the value
	 * @return 	void
	 */
	public function __construct( $original, $argument=array(), $tokenPath=NULL )
	{
		$this->original = $original;
		$this->argument = $argument;
		$this->tokenPath = $tokenPath;
	}
	
	/**
	 * String Value
	 *
	 * @return	string
	 */
	public function __toString()
	{
		if ( isset( $this->stringValue ) ) {
			return $this->stringValue;
		}
		
		try {
			$tokenValue = $this->getTokenValue();
			
			if ( isset( $this->argument['stringValue'] ) and is_callable( $this->argument['stringValue'] ) ) {
				$tokenValue = call_user_func( $this->argument['stringValue'], $tokenValue );
			}
			
			/* Array auto stringification */
			if ( is_array( $tokenValue ) ) { $tokenValue = implode( ', ', array_map( 'strval', $tokenValue ) );	}
			
			/* Boolean auto stringification */
			if ( is_bool( $tokenValue ) ) { $tokenValue = $tokenValue ? 'true' : 'false'; }
			
			$this->stringValue = (string) $tokenValue;
			return $this->stringValue;
		}
		catch( \Exception $e ) { }
		
		$this->stringValue = '';
		return $this->stringValue;
	}
	
	/**
	 * Get Token Value
	 *
	 * @return	string		The token value
	 * @throws 	ErrorException
	 */
	public function getTokenValue()
	{
		if ( $this->tokenSet ) {
			return $this->tokenValue;
		}
		
		if ( isset( $this->tokenPath ) )
		{
			$rulesPlugin = \MWP\Rules\Plugin::instance();
			$current_argument = $this->argument;
			$currentValue = $this->original;
			$token_pieces = explode( ':', $this->tokenPath );
			
			/* Fetch the starting argument for global arguments */
			if ( $token_pieces[0] == 'global' ) {
				array_shift( $token_pieces );
				$global_arg = array_shift( $token_pieces );
				$current_argument = $rulesPlugin->getGlobalArguments( $global_arg );
				if ( ! $current_argument ) { throw new \ErrorException( 'Global argument does not exist: ' . $global_arg ); }
				if ( ! isset( $current_argument['getter'] ) or ! is_callable( $current_argument['getter'] ) ) { throw new \ErrorException( 'Global argument cannot be fetched: ' . $global_arg ); }
				$currentValue = call_user_func( $current_argument['getter'] );
			}
			
			while( $next_token = array_shift( $token_pieces ) ) 
			{
				if ( ! isset( $current_argument['class'] ) )  { throw new \ErrorException( 'Argument does not have an associated class: ' . print_r( $current_argument, true ) ); }
				list( $class_name, $class_key )               = $rulesPlugin->parseClassIdentifier( $current_argument['class'] );
				$current_argument_class                       = $rulesPlugin->getClassMappings( $class_name );
				if ( ! $current_argument_class )              { throw new \ErrorException( 'Class mappings not available for: ' . $class_name ); }
				
				/* Instantiate the argument if needed */
				if ( $current_argument['argtype'] !== 'object' ) {				
					if ( ! isset( $current_argument_class['loader'] ) )       { throw new \ErrorException( 'Class loader not available for: ' . $class_name ); }
					if ( ! is_callable( $current_argument_class['loader'] ) ) { throw new \ErrorException( 'Class loader is not callable for: ' . $class_name ); }
					
					/* This should turn the currentValue into the instance of the associated class */
					$currentValue = call_user_func( $current_argument_class['loader'], $currentValue, $current_argument['argtype'], $class_key );
				}
				
				/* If we don't have the correct object type, the process is broken */
				if ( ! is_object( $currentValue ) or ! is_a( $currentValue, $class_name ) ) 
				{
					$current_argument = array(); // This ensures that a 'stringValue' callback is not invoked with a null value
					$currentValue = NULL; 
					break; 
				}

				/* Make sure a mapping exists for the next token */
				if ( ! isset( $current_argument_class['mappings'][ $next_token ] ) ) { throw new \ErrorException( 'Class: "' . $class_name . '" does not have a mapping for: "' . $next_token . '"' ); }
				
				$current_argument = $current_argument_class['mappings'][ $next_token ];
				if ( ! isset( $current_argument['getter'] ) or ! is_callable( $current_argument['getter'] ) ) { throw new \ErrorException( 'Argument does not have a getter: ' . print_r( $current_argument, true ) ); }
				$currentValue = call_user_func( $current_argument['getter'], $currentValue );
			}
			
			$this->argument = $current_argument;			
			$this->tokenValue = $currentValue;
			$this->tokenSet = true;
		}
		else
		{
			$this->tokenValue = $this->original;
			$this->tokenSet = true;
		}
		
		return $this->tokenValue;
	}
	
	/**
	 * Get the argument
	 *
	 * @return	array
	 */
	public function getArgument()
	{
		return $this->argument;
	}
	
	/**
	 * Get the argument
	 *
	 * @return	array
	 */
	public function getTokenPath()
	{
		return $this->tokenPath;
	}
	
	/**
	 * Get the argument
	 *
	 * @return	array
	 */
	public function getOriginal()
	{
		return $this->original;
	}

}
