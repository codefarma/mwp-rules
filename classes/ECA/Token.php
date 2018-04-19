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
 * Token Class
 */
class _Token
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
	 * @var MWP\Rules\Bundle
	 */
	protected $bundle;
	
	/**
	 * @var MWP\Rules\ECA\Event
	 */
	protected $event;
	
	/**
	 * @var	array
	 */
	protected $event_args;
	
	/**
	 * Set the associated bundle
	 *
	 * @param	MWP\Rules\Bundle|NULL		$bundle			The bundle to associate with token
	 * @return	void
	 */
	public function setBundle( $bundle )
	{
		$this->bundle = $bundle;
	}
	
	/**
	 * Set the associated event
	 *
	 * @param	Event		$event				The event to associate with the token
	 * @param	array		$event_args			The event argument values
	 * @return	void
	 */
	public function setEvent( $event, $event_args )
	{
		$this->event = $event;
		$this->event_args = $event_args;
	}
	
	/**
	 * Constructor
	 *
	 * @param 	mixed			$original		The starting value to start the translation from
	 * @param	string|NULL		$tokenPath		The token path to take during translation
	 * @param	array|NULL		$argument		The starting argument definition
	 * @return 	void
	 */
	protected function __construct( $original, $tokenPath=NULL, $argument=NULL )
	{
		$typeMap = array(
			'object' => 'object',
			'integer' => 'int',
			'double' => 'float',
			'boolean' => 'bool',
			'string' => 'string',
			'array' => 'array',
			'NULL' => '',
		);
		
		/* Extrapolate starting argument properties */
		if ( is_object( $original ) ) {
			$argument = array_merge( array(
				'argtype' => 'object',
				'class' => get_class( $original ),
			), 
			( $argument ?: array() ));
		} else {
			if ( $argument !== NULL ) {
				$argument = array_merge( array(
					'argtype' => $typeMap[ gettype($original) ]
				), 
				( $argument ?: array() ));
			}
		}
		
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
			
			if ( is_object( $tokenValue ) ) {
				if ( is_callable( array( $tokenValue, '__toString' ) ) ) {
					$tokenValue = $tokenValue->__toString();
				} else {
					$tokenValue = 'Object[' . get_class( $tokenValue ) . ']';
				}
			}
			
			$this->stringValue = (string) $tokenValue;
			
			return $this->stringValue;
		}
		catch( \Exception $e ) { 
		
		}
		
		$this->stringValue = '';
		return $this->stringValue;
	}
	
	/**
	 * Get the sequence of variable conversions a token path will encounter
	 *
	 * @param	mixed		$argument			The starting argument or object
	 * @param	string		$tokenpath			The token path to take
	 * @return	array
	 */
	public static function getReflection( $argument, $tokenpath )
	{
		$plugin = Rules\Plugin::instance();
		$token_pieces = explode( ':', $tokenpath );
		
		if ( is_object( $argument ) ) {
			$argument = array(
				'argtype' => 'object',
				'class' => get_class( $argument ),
			);
		}
		
		$reflectionData = array( 
			'error' => false,
			'token_path' => $tokenpath,
			'starting_argument' => $argument,
			'final_argument' => $argument,
			'token_pieces' => $token_pieces,
		);
		
		while( $token_piece = array_shift( $token_pieces ) ) {
			$derivatives = $plugin->getDerivativeTokens( $argument );
			if ( ! isset( $derivatives[ $token_piece ] ) ) {
				$reflectionData['final_argument'] = null;
				$reflectionData['error'] = 'Missing derivative value for token piece: ' . $token_piece;
				break;
			}
			$argument = $derivatives[ $token_piece ];
			$reflectionData['steps'][] = array(
				'token' => $token_piece,
				'result' => $argument,
			);
			$reflectionData['final_argument'] = $argument;
		}
		
		return $reflectionData;
	}
	
	/**
	 * Create a new token
	 *
	 * @param 	mixed			$original		The starting value to start the translation from
	 * @param	string|NULL		$tokenPath		The token path to take during translation
	 * @param	array|NULL		$argument		The starting argument definition
	 * @return	Token
	 */
	public static function create( $original, $tokenPath=NULL, $argument=NULL )
	{
		return new static( $original, $tokenPath, $argument );
	}
	
	/**
	 * Create a token using given resources
	 *
	 * @param	string		$tokenString			The token string
	 * @param	array		$resources				The resources that hold values for the token to pull from
	 * @return	Token
	 */
	public static function createFromResources( $tokenString, $resources )
	{
		$data = static::parseDataFromResources( $tokenString, $resources );
		return static::create( $data['value'], $data['parsed']['token_path'], $data['argument'] );
	}
	
	/**
	 * Parse the data needed to create a token using given resources
	 *
	 * @param	string		$tokenString			The token string
	 * @param	array		$resources				The resources that hold values for the token to pull from
	 * @return	array
	 */
	public static function parseDataFromResources( $tokenString, $resources )
	{
		$token_pieces = explode( ':', $tokenString );
		$source = array_shift( $token_pieces );
		$source_key = array_shift( $token_pieces );
		$data = [
			'parsed' => [
				'source' => $source,
				'source_key' => $source_key,
				'token_path' => implode(':', $token_pieces),
			],
			'value' => NULL,
			'argument' => NULL,
			'errors' => [],
		];
		
		switch( $source ) {
			case 'event': 
				if ( isset( $resources['event'] ) and $resources['event'] instanceof Event ) {
					if ( $data['argument'] = $resources['event']->getArgument( $source_key ) ) {
						if ( isset( $resources['event_args'] ) and is_array( $resources['event_args'] ) and array_key_exists( $source_key, $resources['event_args'] ) ) {
							$data['value'] = $resources['event_args'][ $source_key ];
						} else {
							$data['errors'][] = 'The provided event args do not contain a value for the requested argument.';
						}
					} else {
						$data['errors'][] = 'The requested event argument does not exist.';
					}
				} else {
					$data['errors'][] = 'No event was provided as a resource.';
				}
				break;
				
			case 'global':
				if ( $argument = Rules\Plugin::instance()->getGlobalArguments( $source_key ) ) {
					$data['argument'] = $argument;
					if ( isset( $argument['getter'] ) and is_callable( $argument['getter'] ) ) {
						$data['value'] = call_user_func( $argument['getter'] );
					} else {
						$data['errors'][] = 'The global argument exists, but does not have a "getter" callback.';
					}
				} else {
					$data['errors'][] = 'The requested global argument does not exist.';
				}
				break;
				
			case 'bundle':
				if ( isset( $resources['bundle'] ) and $resources['bundle'] instanceof Rules\Bundle ) {
					if ( $argument = $resources['bundle']->getArgument( $source_key ) ) {
						$data['argument'] = $argument->getProvidesDefinition();
						$data['value'] = $argument->getValue();
					} else {
						$data['errors'][] = 'The provided bundle does not have the requested argument.';
					}
				} else {
					$data['errors'][] = 'No bundle was provided as a resource';
				}
				break;
		}
		
		return $data;
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
		
		$typeMap = array(
			'object' => 'object',
			'integer' => 'int',
			'double' => 'float',
			'boolean' => 'bool',
			'string' => 'string',
			'array' => 'array',
			'NULL' => '',
		);
		
		$this->history = array();
		
		if ( isset( $this->tokenPath ) )
		{
			$rulesPlugin = Rules\Plugin::instance();
			$current_argument = $this->argument;
			$currentValue = $this->original;
			$token_pieces = explode( ':', $this->tokenPath );
			
			while( $token_identifier = array_shift( $token_pieces ) ) 
			{
				list( $next_token, $token_key ) = $rulesPlugin->parseIdentifier( $token_identifier );
				
				/* Load the class map for the current argument */
				if ( ! isset( $current_argument['class'] ) )  { throw new \ErrorException( 'Argument does not have an associated class: ' . json_encode( $current_argument ) ); }
				list( $class_name, $class_key )               = $rulesPlugin->parseIdentifier( $current_argument['class'] );
				$current_argument_class                       = $rulesPlugin->getClassMappings( $class_name );
				if ( ! $current_argument_class )              { throw new \ErrorException( 'Class mappings not available for: ' . $class_name ); }
				
				/* Instantiate instances if needed */
				if ( $current_argument['argtype'] !== 'object' ) {				
					if ( ! isset( $current_argument_class['loader'] ) )       { throw new \ErrorException( 'Class loader not available for: ' . $class_name ); }
					if ( ! is_callable( $current_argument_class['loader'] ) ) { throw new \ErrorException( 'Class loader is not callable for: ' . $class_name ); }
					
					if ( $current_argument['argtype'] == 'array' ) {
						/* This should turn the currentValue into an array of instances of the associated class */
						$arrayValue = array();
						foreach( ( is_array( $currentValue ) ? $currentValue : array( $currentValue ) ) as $value ) {
							if ( ! is_object( $value ) or ! is_a( $value, $class_name ) ) {
								$_value = call_user_func( $current_argument_class['loader'], $currentValue, isset( $current_argument['subtype'] ) ? $current_argument['subtype'] : $typeMap[gettype($value)], $class_key );
								if ( is_object( $_value ) and is_a( $_value, $class_name ) ) {
									$arrayValue[] = $value;
								}
							} else {
								$arrayValue[] = $value;
							}
						}
						$currentValue = $arrayValue;
					} 
					else {
						/* This should turn the currentValue into an instance of the associated class */
						$currentValue = call_user_func( $current_argument_class['loader'], $currentValue, $current_argument['argtype'], $class_key );
						$this->history[] = 'Loaded object instance of: ' . $class_name;
					}
				}

				/* If we don't have the correct object type, the process is broken */
				if ( is_array( $currentValue ) ) {
					$currentValue = array_filter( $currentValue, function( $_value ) use ( $class_name ) { return is_object( $_value ) and is_a( $_value, $class_name ); } );
				} else if ( ! is_object( $currentValue ) or ! is_a( $currentValue, $class_name ) ) {
					$current_argument = array(); // This ensures that an incorrect 'stringValue' callback is not invoked when this token is stringified
					$currentValue = NULL;
					$final_class = is_object( $currentValue ) ? get_class( $currentValue ) : gettype( $currentValue );
					$this->history[] = 'Process broken. Expected to have a ' . $class_name . ' but ended up with ' . $final_class;
					break; 
				}
				
				/* Allow the asterik token to break the process and return the instantiated object(s) */
				if ( $next_token == '*' ) {
					$current_argument = array(
						'argtype' => is_array( $currentValue ) ? 'array' : 'object',
						'class' => $current_argument['class'],
						'label' => isset( $current_argument_class['label'] ) ? $current_argument_class['label'] : $current_argument['class'],
					);
					if ( is_array( $currentValue ) ) {
						$current_argument['subtype'] = 'object';
					}
					$this->history[] = 'Encountered the asterik token. Returning the loaded ' . ( is_array( $currentValue ) ? 'array' : 'object' ) . '.';
					break;
				}
				
				/* Prepare to get the next value. */
				if ( ! isset( $current_argument_class['mappings'][ $next_token ] ) ) { throw new \ErrorException( 'Class: "' . $class_name . '" does not have a mapping for: "' . $next_token . '"' ); }
				$next_argument = $current_argument_class['mappings'][ $next_token ];
				if ( ! isset( $next_argument['getter'] ) or ! is_callable( $next_argument['getter'] ) ) { throw new \ErrorException( 'Argument does not have a getter: ' . json_encode( $next_argument ) ); }
				
				/**
				 * When we get arrays, we have the option to pluck the value of a specific array key. 
				 * This requires for a key to have been specified in the token identifier. Also, array
				 * argument definitions can name a key getter callback to allow array key values to be
				 * directly fetched.
				 */
				if ( ( is_array( $currentValue ) or $next_argument['argtype'] == 'array' ) and isset( $token_key ) )
				{
					$key_mapped_argument = array( 'argtype' => isset( $next_argument['keys']['default']['argtype'] ) ? $next_argument['keys']['default']['argtype'] : ( isset( $next_argument['class'] ) ? 'object' : 'mixed' ), 'label' => isset( $next_argument['label'] ) ? $next_argument['label'] : '' );
					
					// Use a key getter if possible
					if ( isset( $next_argument['keys']['getter'] ) and is_callable( $next_argument['keys']['getter'] ) ) {
						if ( is_array( $currentValue ) ) {
							$nextValue = array();
							foreach( $currentValue as $value ) { 
								$_value = call_user_func( $next_argument['keys']['getter'], $value, $token_key );
								if ( $_value !== NULL ) {
									$nextValue = array_merge( $nextValue, is_array( $_value ) ? $_value : array( $_value ) );
								}
							}
							$currentValue = $nextValue;
							$key_mapped_argument['argtype'] = 'array';
							$this->history[] = 'Got new array of values using key getter to fetch array key: ' . $token_key . ' for token: ' . $next_token;
						} else {
							$currentValue = call_user_func( $next_argument['keys']['getter'], $currentValue, $token_key );
							$this->history[] = 'Used key getter to fetch array key: ' . $token_key . ' for token: ' . $next_token;
						}
					}
					// or fallback to plucking the key from the whole array
					else {
						if ( is_array( $currentValue ) ) {
							$nextValue = array();
							foreach( $currentValue as $value ) {
								$_value = call_user_func( $next_argument['getter'], $value );
								if ( $_value !== NULL ) {
									$nextValue = array_merge( $nextValue, is_array( $_value ) ? $_value : array( $_value ) );
								}
							}
							$currentValue = isset( $nextValue[ $token_key ] ) ? $nextValue[ $token_key ] : null;
							$this->history[] = 'Plucked the array key: ' . $token_key . ' from the merged results of token: ' . $next_token;
						} else {
							$currentValue = call_user_func( $next_argument['getter'], $currentValue );
							$this->history[] = 'Loaded whole array for token: ' . $next_token;
							if ( is_array( $currentValue ) ) {
								$currentValue = isset( $currentValue[ $token_key ] ) ? $currentValue[ $token_key ] : null;
								$this->history[] = 'Plucked the value for the array key: ' . $token_key;
							}
						}
					}
					
					if ( isset( $next_argument['class'] ) ) { $key_mapped_argument = array_merge( $key_mapped_argument, array( 'class' => $next_argument['class'] ) ); }
					if ( isset( $next_argument['keys']['default'] ) ) { $key_mapped_argument = array_merge( $key_mapped_argument, $next_argument['keys']['default'] ); }
					if ( isset( $next_argument['keys']['mappings'][ $token_key ] ) ) { $key_mapped_argument = array_merge( $key_mapped_argument, $next_argument['keys']['mappings'][ $token_key ] ); }
					
					$next_argument = $key_mapped_argument;
					$next_argument['getter'] = function( $val ) { return $val; };
				} 
				
				/* For everything else, just use the standard getter */
				else {
					if ( is_array( $currentValue ) ) {
						$nextValue = array();
						foreach( $currentValue as $value ) { 
							$_value = call_user_func( $next_argument['getter'], $value );
							if ( $_value !== NULL ) {
								$nextValue = array_merge( $nextValue, is_array( $_value ) ? $_value : array( $_value ) );
							}
						}
						$currentValue = $nextValue;
						$next_argument['subtype'] = $next_argument['argtype'] != 'array' ? $next_argument['argtype'] : ( isset( $next_argument['class'] ) ? 'object' : 'mixed' );
						$next_argument['argtype'] = 'array';
						$this->history[] = 'Got new array of values for token: ' . $next_token;
					}
					else {
						$currentValue = call_user_func( $next_argument['getter'], $currentValue );					
						$this->history[] = 'Got the new value for token: ' . $next_token;
					}
				}
				
				$current_argument = $next_argument;
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
