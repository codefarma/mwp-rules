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
	 * @var	array
	 */
	protected $history;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed			$original		The starting value to get the token value from
	 * @param	array|NULL		$argument		The starting argument definition
	 * @param	string|NULL		$tokenPath		The token path to take to get the value
	 * @return 	void
	 */
	public function __construct( $original, $tokenPath=NULL, $argument=NULL )
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
		
		$this->history = array();
		
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
				$this->history[] = 'Fetched the global argument: ' . $global_arg;
			}
			
			/* Extrapolate the starting argument if not otherwise provided */
			if ( ! isset( $current_argument ) ) {
				if ( is_object( $currentValue ) ) {
					$objClass = get_class( $currentValue );
					$current_argument = array(
						'argtype' => 'object',
						'class' => $objClass,
					);
				}
			}
			
			while( $token_identifier = array_shift( $token_pieces ) ) 
			{
				list( $next_token, $token_key ) = $rulesPlugin->parseIdentifier( $token_identifier );
				
				/* Load the class map for the current argument */
				if ( ! isset( $current_argument['class'] ) )  { throw new \ErrorException( 'Argument does not have an associated class: ' . print_r( $current_argument, true ) ); }
				list( $class_name, $class_key )               = $rulesPlugin->parseIdentifier( $current_argument['class'] );
				$current_argument_class                       = $rulesPlugin->getClassMappings( $class_name );
				if ( ! $current_argument_class )              { throw new \ErrorException( 'Class mappings not available for: ' . $class_name ); }
				
				/* Instantiate the argument if needed */
				if ( $current_argument['argtype'] !== 'object' ) {				
					if ( ! isset( $current_argument_class['loader'] ) )       { throw new \ErrorException( 'Class loader not available for: ' . $class_name ); }
					if ( ! is_callable( $current_argument_class['loader'] ) ) { throw new \ErrorException( 'Class loader is not callable for: ' . $class_name ); }
					
					/* This should turn the currentValue into an instance of the associated class */
					$currentValue = call_user_func( $current_argument_class['loader'], $currentValue, $current_argument['argtype'], $class_key );
					$this->history[] = 'Loaded object instance of: ' . $class_name;
				}
				
				/* If we don't have the correct object type, the process is broken */
				if ( ! is_object( $currentValue ) or ! is_a( $currentValue, $class_name ) ) {
					$current_argument = array(); // This ensures that an incorrect 'stringValue' callback is not invoked when this token is stringified
					$currentValue = NULL;
					$final_class = is_object( $currentValue ) ? get_class( $currentValue ) : gettype( $currentValue );
					$this->history[] = 'Process broken. Expected to have a ' . $class_name . ' but ended up with a ' . $final_class;
					break; 
				}
				
				/* Prepare to get the next value */
				if ( ! isset( $current_argument_class['mappings'][ $next_token ] ) ) { throw new \ErrorException( 'Class: "' . $class_name . '" does not have a mapping for: "' . $next_token . '"' ); }
				$current_argument = $current_argument_class['mappings'][ $next_token ];
				if ( ! isset( $current_argument['getter'] ) or ! is_callable( $current_argument['getter'] ) ) { throw new \ErrorException( 'Argument does not have a getter: ' . print_r( $current_argument, true ) ); }
				
				/* Save value for possible use later */
				$sourceObj = $currentValue;

				/**
				 * For arrays, we have the option to directly get the value of a specific array key. 
				 * This requires for a key to have been specified in the token identifier. Also, array
				 * argument definitions can name a 'key_getter' callback to allow array key values to be
				 * directly fetched
				 */
				if ( $current_argument['argtype'] == 'array' and isset( $token_key ) ) 
				{
					// Use key getter if possible, or fallback to plucking the key from the whole array
					if ( isset( $current_argument['key_getter'] ) and is_callable( $current_argument['key_getter'] ) ) {
						$currentValue = call_user_func( $current_argument['key_getter'], $currentValue, $token_key );
						$this->history[] = 'Used key getter to fetch array key: ' . $token_key . ' for token: ' . $next_token;
					} else {
						$currentValue = call_user_func( $current_argument['getter'], $currentValue );
						$this->history[] = 'Loaded whole array for token: ' . $next_token;
						if ( is_array( $currentValue ) ) {
							$currentValue = isset( $currentValue[ $token_key ] ) ? $currentValue[ $token_key ] : null;
							$this->history[] = 'Plucked the value for the array key: ' . $token_key;
						}
					}
					
					// Use a provided key mapping if available, or fallback to auto detection of the new value
					if ( isset( $current_argument['mappings'][ $token_key ] ) ) {
						$current_argument = $current_argument['mappings'][ $token_key ];
						if ( isset( $current_argument['mappings'][ $token_key ]['converter'] ) and is_callable( isset( $current_argument['mappings'][ $token_key ]['converter'] ) ) ) {
							$currentValue = call_user_func( isset( $current_argument['mappings'][ $token_key ]['converter'] ), $currentValue, $sourceObj );
						}
					} else {
						switch( gettype( $currentValue ) ) {
							case 'string':  $current_argument['argtype'] = 'string'; break;
							case 'integer': $current_argument['argtype'] = 'int'; break;
							case 'double':  $current_argument['argtype'] = 'float'; break;
							case 'array':   $current_argument['argtype'] = 'array'; break;
							case 'object':  $current_argument['argtype'] = 'object'; break;
							case 'boolean': $current_argument['argtype'] = 'bool'; break;
							default: 
								$current_argument = array();
								$currentValue = null;
								break 2;
						}
						
						// Update getters
						unset( $current_argument['key_getter'] );
						$current_argument['getter'] = function( $val ) { return $val; };
					}
					
				} 
				
				/* For everything else, just use the standard getter */
				else 
				{
					$currentValue = call_user_func( $current_argument['getter'], $currentValue );					
					$this->history[] = 'Got the new value for token: ' . $next_token;
				}
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

	/**
	 * Get the token history
	 *
	 * @return	array
	 */
	public function getHistory()
	{
		return $this->history;
	}

}
