<?php
/**
 * Plugin Class File
 *
 * Created:   December 5, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * System Class
 */
class _System
{
	/**
	 * @var 	\MWP\Framework\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\MWP\Framework\Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\MWP\Framework\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Rules\Plugin::instance() );
	}
	
	/**
	 * Register ECA's
	 * 
	 * @MWP\WordPress\Action( for="rules_register_ecas" )
	 * 
	 * @return	void
	 */
	public function registerECAs()
	{
		$plugin = $this->getPlugin();
		
		rules_register_conditions( array(
			
			/* Truth comparison */
			array( 'rules_truth', array(
				'title' => 'Check A Truth',
				'description' => 'Checks if a value is equivalent to a boolean truth.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'true' 		=> 'Value is TRUE',
							'false'		=> 'Value is FALSE',
							'truthy'	=> 'Value is TRUE or equivalent to TRUE (any non-empty string/array, number not 0)',
							'falsey'	=> 'Value is FALSE or equivalent to FALSE (including NULL, 0, empty string/array)',
							'null'		=> 'Value is NULL',
							'notnull'	=> 'Value is NOT NULL',
						);
						
						$form->addField( 'compare_type', 'choice', array(
							'label' => __( 'Comparison Method', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['compare_type'] ) ? $values['compare_type'] : 'true',
						));
					},
				),
				'arguments'	=> array(
					'value' => array(
						'label' => 'Value to Compare',
						'argtypes' => array(
							'mixed' => array( 'description' => 'the value to compare' ),
						),		
						'required'	=> false,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'compare_value', 'text', array(
									'label' => __( 'Value', 'mwp-rules' ),
									'data' => isset( $values['compare_value'] ) ? $values['compare_value'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values[ 'compare_value' ];
							},
						),
					),
				),
				'callback' 	=> function ( $value, $values ) {		
					switch ( $values[ 'compare_type' ] )
					{
						case 'true'    :	return $value === TRUE;
						case 'false'   :	return $value === FALSE;
						case 'truthy'  :	return (bool) $value;
						case 'falsey'  :	return ! ( (bool) $value );
						case 'null'    :	return $value === NULL;
						case 'notnull' :	return $value !== NULL;
						default        :	return FALSE;
					}
				},
			)),
			
			/* Number Comparison */
			array( 'rules_number_comparison', array(
				'title' => 'Compare Numbers',
				'description' => 'Check the value of a number against another.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'>' 	=> 'Number 1 is greater than Number 2',
							'<' 	=> 'Number 1 is less than Number 2',
							'=='	=> 'Number 1 is equal to Number 2',
							'!='	=> 'Number 1 is not equal to Number 2',
							'>='	=> 'Number 1 is greater than or equal to Number 2',
							'<='	=> 'Number 1 is less than or equal to Number 2'
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : '>',
						));						
					},
				),
				'arguments'	=> array(
					'number1' => array(
						'label' => 'Number 1',
						'argtypes' => array(
							'int' 	=> array( 'description' => 'a value to use as number 1' ),
							'float' => array( 'description' => 'a value to use as number 1' ),
						),				
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'rules_comparison_number1', 'text', array(
									'label' => __( 'Number 1', 'mwp-rules' ),
									'data' => isset( $values['rules_comparison_number1'] ) ? $values['rules_comparison_number1'] : '',
								));
							},
							'saveValues' => function( &$values, $condition ) {
								settype( $values['rules_comparison_number1'], 'float' ); 
							},
							'getArg' => function( $values ) {
								return $values['rules_comparison_number1'];
							},
						),
					),
					'number2' => array(
						'label' => 'Number 2',
						'default' => 'manual',
						'argtypes' => array(
							'int' 	=> array( 'description' => 'a value to use as number 2' ),
							'float' => array( 'description' => 'a value to use as number 2' ),
						),				
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'rules_comparison_number2', 'text', array(
									'label' => __( 'Number 2', 'mwp-rules' ),
									'data' => isset( $values['rules_comparison_number2'] ) ? $values['rules_comparison_number2'] : '',
								));
							},
							'saveValues' => function( &$values, $condition ) {
								settype( $values['rules_comparison_number2'], 'float' ); 
							},
							'getArg' => function( $values ) {
								return $values['rules_comparison_number2'];
							},
						),
					),
				),				
				'callback' => function( $number1, $number2, $values ) {
					switch( $values[ 'rules_comparison_type' ] ) {
						case '<':  return $number1 < $number2;
						case '>':  return $number1 > $number2;
						case '==': return $number1 == $number2;
						case '!=': return $number1 != $number2;
						case '>=': return $number1 >= $number2;
						case '<=': return $number1 <= $number2;
						default: return FALSE;
					}
				},
			)),
			
			/* String Comparision */
			array( 'rules_string_comparison', array(
				'title' => 'Compare Strings',
				'description' => 'Check the contents of a string.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'equals'     => 'String 1 is equal to String 2',
							'contains'   => 'String 1 contains String 2',
							'startswith' => 'String 1 starts with String 2',
							'endswith'   => 'String 1 ends with String 2',
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : 'equals',
						));						
					},
				),
				'arguments'	=> array(
					'string1' => array(
						'label' => 'String 1',
						'argtypes' => array(
							'string' => array( 'description' => 'the value to use as string 1' ),
						),				
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'rules_comparison_string1', 'text', array(
									'label' => __( 'String 1', 'mwp-rules' ),
									'data' => isset( $values['rules_comparison_string1'] ) ? $values['rules_comparison_string1'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_comparison_string1' ];
							},
						),
					),
					'string2' => array(
						'label' => 'String 2',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'the value to use as string 2' ),
						),				
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'rules_comparison_string2', 'text', array(
									'label' => __( 'String 2', 'mwp-rules' ),
									'data' => isset( $values['rules_comparison_string2'] ) ? $values['rules_comparison_string2'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_comparison_string2' ];
							},
						),
					),
				),
				'callback' => function( $string1, $string2, $values ) {
					switch( $values['rules_comparison_type'] ) {
						case 'contains':   return mb_strpos( $string1, $string2 ) !== FALSE;
						case 'startswith': return mb_substr( $string1, 0, mb_strlen( $string2 ) ) == $string2;
						case 'endswith':   return mb_substr( $string1, mb_strlen( $string2 ) * -1 ) == $string2;
						case 'equals':     return $string1 == $string2;
						default:           return FALSE;
					}
				},
			)),
			
			/* Array Attributes */
			array( 'rules_array_comparison', array(
				'title' => 'Inspect An Array',
				'description' => 'Check the attributes of an array for specific conditions.',				
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'containskey'	=> 'Array contains a specific key',
							'containsvalue' => 'Array contains a specific value',
							'keyhasvalue'   => 'Array key has a specific value',
							'lengthgreater'	=> 'Array size is greater than',
							'lengthless' 	=> 'Array size is less than',
							'lengthequal'	=> 'Array size is equal to',
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : 'containsvalue',
							'toggles' => array(
								'keyhasvalue' => array( 'show' => array( '#rules_array_key' ) ),
							),
						));

						$form->addField( 'rules_array_key', 'text', array(
							'row_attr' => array( 'id' => 'rules_array_key' ),
							'label' => __( 'Array Key', 'mwp-rules' ),
							'attr' => array( 'placeholder' => __( 'Enter the name of an array key', 'mwp-rules' ) ),
							'data' => isset( $values['rules_array_key'] ) ? $values['rules_array_key'] : '',
						));
					},
				),
				'arguments' => array(
					'array' => array(
						'label' => 'Array to Check',
						'argtypes' => array(
							'array' => array( 'description' => 'an array to compare' ),
						),
						'required' => true,
					),
					'value' => array(
						'label' => 'Value to Check',
						'default' => 'manual',
						'argtypes' => array(
							'mixed' => array( 'description' => 'the key or value to check' ),
						),	
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'compare_value', 'text', array(
									'label' => __( 'Value', 'mwp-rules' ),
									'data' => isset( $values['compare_value'] ) ? $values['compare_value'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values[ 'compare_value' ];
							},
						),
					),
				),		
				'callback' => function( $array, $value, $values ) {
					if ( ! is_array( $array ) ) {
						return FALSE;
					}
					
					switch ( $values['rules_comparison_type'] ) {
						case 'lengthgreater': return count( $array ) > (int) $value;
						case 'lengthless':    return count( $array ) < (int) $value;
						case 'lengthequal':   return count( $array ) == (int) $value;
						case 'containskey':   return in_array( $value, array_keys( $array ), true );
						case 'containsvalue': return in_array( $value, $array, true );
						case 'keyhasvalue':
							if ( $key = $values['rules_array_key'] ) {
								return array_key_exists( $key, $array ) && $array[$key] === $value;
							}
							break;
					}
					
					return false;
				},
			)),
			
			/* Object Comparision */
			array( 'rules_object_comparison', array(
				'title' => 'Inspect An Object',
				'description' => 'Inspect an object to compare its class or equality with another object.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'isa'        => 'Object is the same class or a subclass of value',
							'isclass' 	 => 'Object is the same class as value',
							'issubclass' => 'Object is a subclass of value',
							'equal'      => 'Object and value are the same object',
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : 'isa',
						));
					},
				),
				'arguments' => array(
					'object' => array(
						'argtypes' => array(
							'object' => array( 'description' => 'the object to compare' ),
						),				
						'required'	=> true,
					),
					'value' => array(
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'A classname to compare' ),
							'object' => array( 'description' => 'An object to compare' ),
						),				
						'required'	=> true,
						'configuration' => array(
							'form' => function( $form, $values, $condition ) {
								$form->addField( 'compare_value', 'text', array(
									'label' => __( 'Class Name', 'mwp-rules' ),
									'data' => isset( $values['compare_value'] ) ? $values['compare_value'] : '',
									'attr' => array( 'placeholder' => 'Enter a fully qualified object class name' ),
								));
							},
							'getArg' => function( $values ) {
								return $values[ 'compare_value' ];
							},
						),
					),
				),
				'callback' => function( $object, $value, $values ) {
					if ( ! is_object( $object ) ) {
						return FALSE;
					}
					
					switch ( $values['rules_comparison_type'] ) {
						case 'isa':        return is_a( $object, is_object( $value ) ? get_class( $value ) : $value );
						case 'isclass':	   return get_class( $object ) == ltrim( is_object( $value ) ? get_class( $value ) : $value, '\\' );
						case 'issubclass': return is_subclass_of( $object, is_object( $value ) ? get_class( $value ) : $value );
						case 'equal':      return $object === $value;
						default:           return false;
					}
				},
			)),
			
			/* Time Comparision */
			array( 'rules_time_comparison', array(
				'title' => 'Compare Dates',
				'description' => 'Compare a date/time with another date/time.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$date_compare_options = array (
							'<' => 'Date 1 is before Date 2',
							'>' => 'Date 1 is after Date 2',
							'=' => 'Date 1 and Date 2 are on the same day',
							'?' => 'Date 1 and Date 2 are within a certain amount of time of each other',
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $date_compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : '<',
							'toggles' => array(
								'?' => array( 'show' => array( '#time_compare_minutes', '#time_compare_hours', '#time_compare_days', '#time_compare_months', '#time_compare_years' ) ),
							),
						));
						
						$form->addField( 'compare_minutes', 'integer', array( 'row_attr' => array( 'id' => 'time_compare_minutes' ), 'label' => 'Minutes', 'data' => isset( $values['compare_minutes'] ) ? $values['compare_minutes'] : 0 ) );
						$form->addField( 'compare_hours', 'integer', array( 'row_attr' => array( 'id' => 'time_compare_hours' ), 'label' => 'Hours', 'data' => isset( $values['compare_hours'] ) ? $values['compare_hours'] : 0 ) );
						$form->addField( 'compare_days', 'integer', array( 'row_attr' => array( 'id' => 'time_compare_days' ), 'label' => 'Days', 'data' => isset( $values['compare_days'] ) ? $values['compare_days'] : 0 ) );
						$form->addField( 'compare_months', 'integer', array( 'row_attr' => array( 'id' => 'time_compare_months' ), 'label' => 'Months', 'data' => isset( $values['compare_months'] ) ? $values['compare_months'] : 0 ) );
						$form->addField( 'compare_years', 'integer', array( 'row_attr' => array( 'id' => 'time_compare_years' ), 'label' => 'Years', 'data' => isset( $values['compare_years'] ) ? $values['compare_years'] : 0 ) );						
					},
				),
				'arguments'	=> array(
					'date1' => array(
						'label' => 'Date 1',
						'argtypes' => array( 
							'object' => array( 'description' => 'An instance of a DateTime object', 'classes' => array( 'DateTime' ) ) 
						),
						'configuration' => $plugin->configPreset( 'datetime', 'compare_date1', array( 'label' => 'Date 1' ) ),
						'required'	=> true,
					),
					'date2' => array(
						'label' => 'Date 2',
						'default'	=> 'manual',
						'argtypes' => array( 
							'object' => array( 'description' => 'An instance of a DateTime object', 'classes' => array( 'DateTime' ) ) 
						),
						'configuration' => $plugin->configPreset( 'datetime', 'compare_date2', array( 'label' => 'Date 2' ) ),
						'required'	=> true,
					),
				),
				'callback' 	=> function( $date1, $date2, $values ) {
					if ( ! ( ( $date1 instanceof \DateTime ) and ( $date2 instanceof \DateTime ) ) ) {
						return FALSE;
					}
					
					switch ( $values['rules_comparison_type'] ) {
						case '?':
							$value = 0
								+ ( intval( $values[ 'compare_minutes' ] ) * 60 )
								+ ( intval( $values[ 'compare_hours' ]  ) * ( 60 * 60 ) )
								+ ( intval( $values[ 'compare_days' ]   ) * ( 60 * 60 * 24 ) )
								+ ( intval( $values[ 'compare_months' ] ) * ( 60 * 60 * 24 * 30 ) )
								+ ( intval( $values[ 'compare_years' ]  ) * ( 60 * 60 * 24 * 365 ) );
								
							return abs( $date1->getTimestamp() - $date2->getTimestamp() ) < $value;
							
						case '>':
							return $date1->getTimestamp() > $date2->getTimestamp();
							
						case '<':
							return $date1->getTimestamp() < $date2->getTimestamp();
							
						case '=':
							return (
								$date1->format( 'Y' ) == $date2->format( 'Y' ) and
								$date1->format( 'm' ) == $date2->format( 'm' ) and
								$date1->format( 'd' ) == $date2->format( 'd' )
							);
					}
				},
			)),
			
			/* Data Type Comparision */
			array( 'rules_data_type_comparison', array(
				'title' => 'Check Data Type',
				'description' => 'Check if a value has a certain data type.',
				'configuration' => array(
					'form' => function( $form, $values, $condition ) {
						$compare_options = array(
							'boolean'	=> 'Value is a Boolean (TRUE/FALSE)',
							'string' 	=> 'Value is a String',
							'integer'	=> 'Value is a Integer',
							'double'	=> 'Value is a Float (Decimal)',
							'array'		=> 'Value is an Array',
							'object'	=> 'Value is an Object',
							'NULL'		=> 'Value is NULL',
						);
						
						$form->addField( 'rules_comparison_type', 'choice', array( 
							'label' => __( 'Comparison Type', 'mwp-rules' ),
							'choices' => array_flip( $compare_options ),
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['rules_comparison_type'] ) ? $values['rules_comparison_type'] : 'boolean',
						));						
					},
				),
				'arguments'	=> array(
					'value' => array(
						'label' => 'Value to Check',
						'argtypes' => array(
							'mixed' => array( 'description' => 'the value to check' ),
						),
					),
				),
				'callback' 	=> function( $value, $values ) {
					$type = gettype( $value );		
					return $type === $values['rules_comparison_type'];
				},
			)),
			
			/* Check For Scheduled Action */
			array( 'rules_check_scheduled_action', array(
				'title' => 'Check For A Scheduled Action',
				'description' => 'Check to see if an action with a particular key has been scheduled.',
				'arguments' => array(
					'action_key' => array(
						'label' => 'Action key to check',
						'default' => 'manual',
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_action_key', 'text', array(
									'label' => 'Action Key',
									'data' => isset( $values['rules_action_key'] ) ? $values['rules_action_key'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_action_key'];
							},
						),
					),
				),
				'callback' => function( $action_key ) {
					if ( $action_key ) {
						$count = \MWP\Rules\ScheduledAction::countWhere( array( 'schedule_unique_key=%s', $action_key ) );
						return $count > 0;
					}
					
					return false;
				},
			)),
			
			/* Execute Custom PHP Code */
			array( 'rules_execute_php', array(
				'title' => 'Execute Custom PHP Code',
				'description' => 'Run a custom block of php code.',
				'configuration' => array(
					'form' => function( $form, $saved_values, $operation ) use ( $plugin ) {
						$form->addField( 'rules_custom_phpcode', 'textarea', array(
							'row_prefix' => '<hr>',
							'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $saved_values['rules_custom_phpcode'] ) ? $saved_values['rules_custom_phpcode'] : "// <?php\n\nreturn;",
							'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 'operation' => $operation, 'event' => $operation->event() ) ),
						));
					}
				),
				'callback' => function( $saved_values, $event_args, $operation ) {
					$evaluate = function( $phpcode ) use ( $event_args, $operation ) {
						extract( $event_args );
						return @eval( $phpcode );
					};
					
					return $evaluate( $saved_values[ 'rules_custom_phpcode' ] );
				},
			)),
			
		));
		
	}	
}
