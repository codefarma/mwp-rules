<?php
/**
 * Plugin Class File
 *
 * Created:   January 10, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;
use MWP\Framework\Framework;
use MWP\Rules\ECA\Token;

/**
 * GenericOperation Class
 */
abstract class _GenericOperation extends ActiveRecord
{
	/**
	 * @var string
	 */
	public static $optype;
	
	/**
	 * Build Operation Form ( Condition / Action )
	 *
	 * @param	MWP\Framework\Helpers\Form	$form		The form to build
	 * @param	MWP\Rules\(Condition/Action)	$operation	The condition or action node
	 * @return	void
	 */
	public static function buildConfigForm( $form, $operation )
	{
		$rulesPlugin = \MWP\Rules\Plugin::instance();
		$definition = $operation->definition();
		$optype = $operation::$optype;
		$opkey = $operation->getFormKey();
		$request = Framework::instance()->getRequest();
		$operation_label = __( $optype == 'condition' ? 'Condition to apply' : 'Action to take', 'mwp-rules' );
		
		/**
		 * Operation title
		 */
		$form->addField( 'title', 'text', array(
			'label' => __( ucwords( $optype ) . ' description', 'mwp-rules' ),
			'description' => __( "Summarize the intended purpose of this {$optype}.", 'mwp-rules' ),
			'data' => $operation->title,
			'attr' => array( 'placeholder' => __( "Describe what this {$optype} is for", 'mwp-rules' ) ),
			'required' => true,
		));
		
		/* Step 1: Configure the operation type for new operations */
		if ( ! $operation->id ) 
		{
			$operation_choices = array();
			$operation_definitions = $optype == 'condition' ? $rulesPlugin->getConditions() : $rulesPlugin->getActions();
			
			foreach( $operation_definitions as $definition ) {
				$group = isset( $definition->group ) ? $definition->group : 'Misc';
				$operation_choices[ $group ][ $definition->title ] = $definition->key;
			}
			
			$form->addField( 'key', 'choice', array(
				'label' => $operation_label,
				'choices' => $operation_choices,
				'data' => $operation->key,
				'required' => true,
			),
			NULL, 'title', 'before' );
		
			$form->addField( 'submit', 'submit', array( 
				'label' => __( 'Continue', 'mwp-rules' ), 
				'attr' => array( 'class' => 'btn btn-primary' ),
				'row_attr' => array( 'class' => 'text-center' ),
			));
			
			return $form;			
		}
		else
		{
			$operation_name = $definition ? $definition->title : 'Missing (' . $opkey . ')';
			
			/* Add the operation description */
			$form->addField( 'key', 'choice', array(
				'label' => $operation_label,
				'choices' => array( $operation_name => $operation->key ),
				'data' => $operation->key,
				'required' => true,
			),
			NULL, 'title', 'before' );
		
		}
		
		/* Make sure we have a definition to work with */
		if ( $definition ) 
		{
			/* Add operation level form fields */
			if ( isset( $definition->configuration['form'] ) and is_callable( $definition->configuration['form'] ) ) {
				call_user_func( $definition->configuration['form'], $form, $operation->data, $operation );
			}
			
			/**
			 * Add argument level configurations if this operation takes arguments
			 */
			if ( isset( $definition->arguments ) and is_array( $definition->arguments ) )
			{
				foreach ( $definition->arguments as $arg_name => $arg )
				{
					$arg_sources = array();
					$argNameKey = $opkey . '_' . $arg_name;
					$default_source = isset( $arg['default'] ) ? $arg['default'] : null;
					
					/* Check if manual configuration is available for this argument */
					$has_manual_config = ( 
						( isset ( $arg[ 'configuration' ][ 'form' ] ) 	and is_callable( $arg[ 'configuration' ][ 'form' ] ) ) and 
						( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) )
					);
					
					/* Look for event data that can be used to supply the value for this argument */
					$usable_event_data = array();
					if ( $event = $operation->event() ) {
						$usable_event_data = $event->getArgumentTokens( $arg, NULL, 2 );
						foreach( $usable_event_data as $token => &$title ) {
							$title = $token . ' - ' . $title;
						}
						$usable_event_data = array_flip( $usable_event_data );
					}
					
					if ( ! empty( $usable_event_data ) ) {
						$arg_sources[ 'Event / Global Data' ] = 'event';
					}
					
					if ( $has_manual_config ) {
						$arg_sources[ 'Manual Configuration' ] = 'manual';
					}
					
					if ( isset( $arg['argtypes'] ) ) {
						$arg_sources[ 'Custom PHP Code' ] = 'phpcode';
					}
					
					$form->addHtml( '__' . $arg_name . '_form_wrapper_start', '<div id="' . $argNameKey . '_form_wrapper">' );
					$form->addHeading( '__' . $arg_name . '_heading', isset( $arg['label'] ) ? $arg['label'] : $arg_name );
					
					$form->addField( $argNameKey . '_source', 'choice', array(
						'label' => __( 'Source', 'mwp-rules' ),
						'choices' => $arg_sources,
						'data' => isset( $operation->data[ $argNameKey . '_source' ] ) ? $operation->data[ $argNameKey . '_source' ] : $default_source,
						'required' => true,
						'toggles' => array(
							'event' => array( 'show' => '#' . $argNameKey . '_eventArg' ),
							'manual' => array( 'show' => '#' . $argNameKey . '_manualConfig' ),
							'phpcode' => array( 'show' => '#' . $argNameKey . '_phpcode' ),
						),
					));
					
					/**
					 * MANUAL CONFIGURATION
					 *
					 * Does the argument support a manual configuration?
					 */
					if ( $has_manual_config )
					{				
						/**
						 * Add manual configuration form fields from definition
						 *
						 * Note: Callbacks should return an array with the ID's of their
						 * added form fields so we know what to toggle.
						 */
						$form->addHtml( '__manual_config_start_' . $arg_name, '<div id="' . $argNameKey . '_manualConfig">' );
						$_fields = call_user_func_array( $arg[ 'configuration' ][ 'form' ], array( $form, $operation->data, $operation ) );
						$form->addHtml( '__manual_config_end_' . $arg_name, '</div>' );
					}
					
					/**
					 * EVENT ARGUMENTS 
					 *
					 * Are there any arguments to use?
					 */
					if ( ! empty( $usable_event_data ) ) 
					{
						$form->addField( $argNameKey . '_eventArg', 'choice', array(
							'row_attr' => array( 'id' => $argNameKey . '_eventArg' ),
							'label' => __( 'Data To Use', 'mwp-rules' ),
							'choices' => $usable_event_data,
							'required' => true,
							'data' => isset( $operation->data[ $argNameKey . '_eventArg' ] ) ? $operation->data[ $argNameKey . '_eventArg' ] : NULL,
						));
					}
					
					/**
					 * PHP CODE
					 *
					 * Requires return argtype(s) to be specified
					 */
					if ( isset( $arg[ 'argtypes' ] ) ) {
						/**
						 * Compile argtype info
						 */
						$_arg_list 	= array();
						
						if ( is_array( $arg[ 'argtypes' ] ) ) {
							foreach( $arg[ 'argtypes' ] as $_type => $_type_def ) {
								if ( is_array( $_type_def ) ) {
									if ( isset ( $_type_def[ 'description' ] ) ) {
										$_arg_list[] = "<code>{$_type}</code>" . ( isset( $_type_def[ 'classes' ] ) ? ' (' . implode( ',', (array) $_type_def[ 'classes' ] ) . ')' : '' ) . ": {$_type_def[ 'description' ]}";
									}
									else {
										$_arg_list[] = "<code>{$_type}</code>" . ( isset( $_type_def[ 'classes' ] ) ? ' (' . implode( ',', (array) $_type_def[ 'classes' ] ) . ')' : '' );
									}
								}
								else {
									$_arg_list[] = "<code>{$_type_def}</code>";
								}
							}
						}
						
						$form->addField( $argNameKey . '_phpcode', 'textarea', array(
							'row_attr' => array(  'id' => $argNameKey . '_phpcode', 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'Custom PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $operation->data[ $argNameKey . '_phpcode' ] ) ? $operation->data[ $argNameKey . '_phpcode' ] : "// <?php \n\nreturn;",
							'description' => $rulesPlugin->getTemplateContent( 'snippets/phpcode_description', array( 'operation' => $operation, 'return_args' => $_arg_list, 'event' => $operation->event() ) ),
							'required' => false,
						));
					}
					
					$form->addHtml( '__' . $arg_name . '_form_wrapper_end', '</div>' );
				}
			}
			
		}
		
		/* Save button */
		$form->addField( 'submit', 'submit', array( 
			'label' => __( 'Save ' . ucwords( $optype ), 'mwp-rules' ), 
			'attr' => array( 'class' => 'btn btn-primary' ),
			'row_attr' => array( 'class' => 'text-center' ),
		));
	}
	
	/**
	 * Process the values from an operation configuration form submission
	 * 
	 * @param	array							$values				The submitted form values
	 * @return	void
	 */
	public function processConfigForm( $values )
	{
		foreach( $values as $key => $value ) {
			if ( substr( $key, 0, 2 ) == '__' ) {
				unset( $values[$key] );
			}
		}
		
		if ( $definition = $this->definition() ) {
			if ( isset( $definition->configuration['saveValues'] ) and is_callable( $definition->configuration['saveValues'] ) ) {
				call_user_func( $definition->configuration['saveValues'], $values, $this );
			}
			foreach( $definition->arguments as $arg ) {
				if ( isset( $arg['configuration']['saveValues'] ) and is_callable( $arg['configuration']['saveValues'] ) ) {
					call_user_func( $arg['configuration']['saveValues'], $values, $this );
				}
			}
		}
		
		/* Remove non-custom configuration data */
		unset( 
			$values['key'],
			$values['title'],
			$values['event_details'],
			$values['not'],
			$values['group_compare'],
			$values['enabled'],
			$values['else'],
			$values['schedule_mode'], 
			$values['schedule_minutes'], 
			$values['schedule_hours'], 
			$values['schedule_days'],
			$values['schedule_months'],
			$values['schedule_date'],
			$values['schedule_key'],
			$values['schedule_customcode']
		);
		
		$this->data = $values;
	}
	
	/**
	 * Invoke An Operation
	 *
	 * @param	array								$args			The arguments the operation was invoked with
	 * @return	mixed
	 */
	protected function opInvoke( $args )
	{
		$rulesPlugin = \MWP\Rules\Plugin::instance();
		
		if ( ( $definition = $this->definition() ) !== NULL )
		{
			$arg_map         = array();
			$operation_args  = array();
			$event_arg_index = array();
			$i               = 0;
			$event           = $this->event();
			$opkey           = $this->getFormKey();
			
			/* Name and index all the event arguments */
			if ( isset( $event->arguments ) and count( $event->arguments ) ) {
				foreach ( $event->arguments as $event_arg_name => $event_arg ) {
					$arg_map[ $event_arg_name ] = $args[ $i ];
					$event_arg_index[ $event_arg_name ] = $i++;
				}
			}
			
			try
			{
				if ( isset( $definition->arguments ) and is_array( $definition->arguments ) )
				{
					/* Put together the argument list needed by this operation */
					foreach ( $definition->arguments as $arg_name => $arg )
					{
						$argument_missing 	= FALSE;
						$argNameKey 		= $opkey . '_' . $arg_name;
						$token              = NULL;
						
						/* Check which source the user has configured for the argument data */
						switch ( $this->data[ $argNameKey . '_source' ] )
						{
							/**
							 * Grab argument from event
							 */
							case 'event':
							
								/**
								 * Determine which argument index to use and if the argument
								 * needs class conversion or not
								 */
								$tokenized_key = $this->data[ $argNameKey . '_eventArg' ];								
								$_operation_arg	= NULL;
								
								$token = Token::createFromResources( $tokenized_key, $this->getResources( $arg_map ) );
								$event_arg = $token->getTokenValue();
								$event_arg_def = $token->getArgument();
								$event_arg_type = isset( $event_arg_def['argtype'] ) ? $event_arg_def['argtype'] : NULL;
								
								/**
								 * Check if argument is present in the event
								 */
								if ( isset ( $event_arg ) ) {									
									/**
									 * Argtypes must be defined to use event arguments
									 */
									if ( is_array( $arg[ 'argtypes' ] ) ) {
										/* Simple definitions with no processing callbacks */
										if ( in_array( $event_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) ) {
											$_operation_arg = $event_arg;
										}
										
										/* Complex definitions, check for processing callbacks */
										else if ( isset( $arg[ 'argtypes' ][ $event_arg_type ] ) ) {
											if ( isset ( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ] ) ) {
												$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ $event_arg_type ][ 'converter' ], array( $event_arg, $this->data ) );
											} else {
												$_operation_arg = $event_arg;
											}
										}
										else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) ) {
											if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) ) {
												$_operation_arg = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $event_arg, $this->data ) );
											} else {
												$_operation_arg = $event_arg;
											}
										}
									}
								}
								
								/**
								 * After all that, check if we have an argument to pass
								 */
								if ( isset( $_operation_arg ) ) {
									$operation_args[] = $_operation_arg;
								} else {
									$argument_missing = TRUE;
								}
								
								break;
							
							/**
							 * Grab manual entry argument
							 */
							case 'manual':
							
								/**
								 * Arguments received from manual configuration callbacks are not passed through any processing callbacks
								 * because it is expected that the designer of the operation will return an argument that is
								 * already in a state that can be passed directly to the operation callback.
								 */
								if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) ) {
									$operation_args[] = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $this->data, $arg_map, $this ) );
								}
								else {
									$argument_missing = TRUE;
								}
								break;
							
							/**
							 * Calculate an argument using PHP
							 */
							case 'phpcode':
							
								$evaluate = function( $phpcode ) use ( $arg_map ) {
									extract( $arg_map );								
									return @eval( $phpcode );
								};
								
								$argVal = $evaluate( $this->data[ $argNameKey . '_phpcode' ] );
								
								if ( isset( $argVal ) )
								{
									if ( is_array( $arg[ 'argtypes' ] ) )
									{
										$type_map = array
										( 
											'integer' 	=> 'int',
											'double'	=> 'float',
											'boolean' 	=> 'bool',
											'string' 	=> 'string',
											'array'		=> 'array',
											'object'	=> 'object',
										);
										
										$php_arg_type = $type_map[ gettype( $argVal ) ];
										
										/* Simple definitions with no value processing callbacks */
										if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) ) {
											$operation_args[] = $argVal;
										}
										
										/* Complex definitions, check for value processing callbacks */
										else if ( isset( $arg[ 'argtypes' ][ $php_arg_type ] ) ) {
											if ( isset ( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) ) {
												$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ], array( $argVal, $this->data ) );
											}
											else {
												$operation_args[] = $argVal;
											}
										}
										else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) ) {
											if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) ) {
												$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $argVal, $this->data ) );
											}
											else {
												$operation_args[] = $argVal;
											}
										
										}
										else {
											$argument_missing = TRUE;
										}
									}
									else {
										/**
										 * The argument cannot be processed because argtypes aren't supported
										 */
										$argument_missing = TRUE;
									}
									
								}
								else
								{
									$argument_missing = TRUE;
								}
								break;
								
							default:
							
								$argument_missing = TRUE;
						}
						
						/**
						 * If we haven't obtained a usable argument, use the manual default configuration if applicable
						 */
						if 
						( 
							$argument_missing and 
							$this->data[ $argNameKey . '_source' ] == 'event' and
							$this->data[ $argNameKey . '_eventArg_useDefault' ]
						)	
						{
							/**
							 * Get the default value from manual configuration setting
							 */
							if ( isset ( $arg[ 'configuration' ][ 'getArg' ] ) and is_callable( $arg[ 'configuration' ][ 'getArg' ] ) ) {
								$argVal = call_user_func_array( $arg[ 'configuration' ][ 'getArg' ], array( $this->data, $this ) );
								if ( isset( $argVal ) ) {
									$argument_missing = FALSE;
									$operation_args[] = $argVal;
								}
							}
							
							/**
							 * Get the default value from phpcode
							 */
							else
							{
								/* Only if we haven't already attempted to get the argument from phpcode */
								if ( $this->data[ $argNameKey . '_source' ] !== 'phpcode' )
								{
									$evaluate = function( $phpcode ) use ( $arg_map )
									{
										extract( $arg_map );								
										return @eval( $phpcode );
									};
									
									$argVal = $evaluate( $this->data[ $argNameKey . '_phpcode' ] );
									
									if ( isset( $argVal ) )
									{
										if ( is_array( $arg[ 'argtypes' ] ) )
										{
											$type_map = array
											( 
												'integer' 	=> 'int',
												'double'	=> 'float',
												'boolean' 	=> 'bool',
												'string' 	=> 'string',
												'array'		=> 'array',
												'object'	=> 'object',
											);
											
											$php_arg_type = $type_map[ gettype( $argVal ) ];
											
											/* Simple definitions with no processing callbacks */
											if ( in_array( $php_arg_type, $arg[ 'argtypes' ] ) or in_array( 'mixed', $arg[ 'argtypes' ] ) ) {
												$operation_args[] = $argVal;
												$argument_missing = FALSE;
											}
											
											/* Complex definitions, check for processing callbacks */
											else if ( isset( $arg[ 'argtypes' ][ $php_arg_type ] ) )
											{
												if ( isset ( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ] ) ) {
													$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ $php_arg_type ][ 'converter' ], array( $argVal, $this->data ) );
												}
												else {
													$operation_args[] = $argVal;
												}
												$argument_missing = FALSE;
											}
											else if ( isset( $arg[ 'argtypes' ][ 'mixed' ] ) )
											{
												if ( isset ( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) and is_callable( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ] ) ) {
													$operation_args[] = call_user_func_array( $arg[ 'argtypes' ][ 'mixed' ][ 'converter' ], array( $argVal, $this->data ) );
												}
												else {
													$operation_args[] = $argVal;
												}
												$argument_missing = FALSE;
											}
										}
									}							
								}
							}
						}

						if ( $argument_missing ) {
							if ( $arg[ 'required' ] ) {
								/* Operation cannot be invoked because we're missing a required argument */
								if ( $rule = $this->rule() and $rule->debug ) {
									$log_message = "Missing value for: " . $arg_name;
									if ( isset( $token ) ) {
										$log_message = array( 'message' => $log_message, 'history' => $token->getHistory() );
									}
									$rulesPlugin->rulesLog( $event, $this->rule(), $this, $log_message, 'Operation skipped (missing argument)' );
								}
								return NULL;
							}
							else {
								$operation_args[] = NULL;
							}
						}
					}
				}
				
				/**
				 * Now that we have our argument list, time to execute the operation callback
				 */
				if ( isset( $definition->callback ) and is_callable( $definition->callback ) )
				{
					/**
					 * Perform token replacements on string value arguments
					 */
					foreach ( $operation_args as &$_operation_arg ) {
						if ( in_array( gettype( $_operation_arg ), array( 'string' ) ) ) {
							$_operation_arg = $this->replaceTokens( $_operation_arg, $arg_map );
						}
					}
					
					try
					{
						/**
						 * Check to see if actions have a future scheduling
						 */
						if ( $this instanceof \MWP\Rules\Action and $this->schedule_mode )
						{
							$future_time = 0;
							switch ( $this->schedule_mode )
							{
								/**
								 * Defer to end of rule processing
								 */
								case 1:
									$result = '__suppress__';
									$event->actionStack[] = array
									(
										'action' 	=> $this,
										'args' 	 	=> $operation_args,
										'event_args' 	=> $arg_map,
										'thread' 	=> $event->thread,
										'parent' 	=> $event->parentThread,
									);
									break;
									
								/**
								 * Set amount of time in the future 
								 */
								case 2:
									$future_time = \strtotime
									( 
										'+' . intval( $this->schedule_months ) . ' months ' . 
										'+' . intval( $this->schedule_days ) . ' days ' .
										'+' . intval( $this->schedule_hours ) . ' hours ' .
										'+' . intval( $this->schedule_minutes ) . ' minutes '
									);
									break;
									
								/**
								 * On a specific date/time
								 */
								case 3:
									$future_time = $this->schedule_date;
									break;
									
								/**
								 * On a calculated date
								 */
								case 4:
									$evaluate = function( $phpcode ) use ( $arg_map ) {
										extract( $arg_map );
										return @eval( $phpcode );
									};
									
									$custom_time = $evaluate( $this->schedule_customcode );
									
									if ( is_numeric( $custom_time ) )
									{
										$future_time = intval( $custom_time );
									}
									else if ( is_object( $custom_time ) )
									{
										if ( $custom_time instanceof \DateTime )
										{
											$future_time = $custom_time->getTimestamp();
										}
									}
									else if ( is_string( $custom_time ) )
									{
										$future_time = strtotime( $custom_time );
									}
									break;
									
								/**
								 * At the end of the page load
								 */
								case 5:
								
									if ( ! $rulesPlugin->shuttingDown )
									{
										$result = '__suppress__';
										$rulesPlugin->actionQueue[] = array
										(
											'event'	=> $event,
											'action' => array
											(
												'action' 	=> $this,
												'args' 	 	=> $operation_args,
												'event_args' 	=> $arg_map,
												'thread' 	=> $event->thread,
												'parent' 	=> $event->parentThread,
											),
										);
									}
									else
									{
										$result = 'Action skipped. Page shut down already initiated.';
									}
									break;
									
							}
							
							if ( $future_time > time() )
							{
								$thread = $parentThread = NULL;
								
								if ( $rule = $this->rule() ) {
									$thread        = $rule->event()->thread;
									$parentThread  = $rule->event()->parentThread;
								}
								
								$unique_key = $this->schedule_key ? $this->replaceTokens( $this->schedule_key, $tokens ) : NULL;
								$result = $rulesPlugin->scheduleAction( $this, $future_time, $operation_args, $arg_map, $thread, $parentThread, $unique_key );
							}
							
						}
					
						/**
						 * If our operation was scheduled, then it will have a result already from the scheduler
						 */
						if ( ! isset ( $result ) ) {
							$result = call_user_func_array( $definition->callback, array_merge( $operation_args, array( $this->data, $arg_map, $this ) ) );					
						}
						
						/**
						 * Conditions have a special setting to invert their result with NOT, so let's check that 
						 */
						if ( $this instanceof \MWP\Rules\Condition and $this->not ) {
							$result = ! $result;
						}
						
						if ( $rule = $this->rule() and $rule->debug and $result !== '__suppress__' ) {
							$rulesPlugin->rulesLog( $rule->event(), $rule, $this, $result, 'Evaluated' );
						}
						
						return $result;
					}
					catch ( \Exception $e ) 
					{
						/**
						 * Log exceptions that happen during operation execution
						 */
						$event = $this->rule() ? $this->rule()->event() : NULL;
						$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
						$file = array_pop( $paths );
						$rulesPlugin->rulesLog( $event, $this->rule(), $this, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, 'Operation Callback Exception', 1 );
					}
				}
				else
				{
					if ( $rule = $this->rule() )
					{
						$rulesPlugin->rulesLog( $rule->event(), $rule, $this, FALSE, 'Missing Callback', 1  );
					}
				}
			}
			catch ( \Exception $e )
			{
				/**
				 * Log exceptions that happen during argument preparation
				 */
				$event = $this->rule() ? $this->rule()->event() : NULL;
				$paths = explode( '/', str_replace( '\\', '/', $e->getFile() ) );
				$file = array_pop( $paths );
				$rulesPlugin->rulesLog( $event, $this->rule(), $this, $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file, "Argument Callback Exception ({$arg_name})", 1 );
			}
		}
		else
		{
			/**
			 * Log non-invokable action
			 */
			$event = $this->rule() ? $this->rule()->event() : NULL;
			$rulesPlugin->rulesLog( $event, $this->rule(), $this, FALSE, 'Operation aborted. (Missing Definition)', 1 );		
		}
	}
	
	/**
	 * Replace Tokens
	 * 
	 * @param 	string		$string				The string with possible tokens to replace
	 * @param	array		$arg_map			The argument map of starting values to use with tokens
	 * @return	string							The string with tokens replaced
	 */
	public function replaceTokens( $string, $arg_map )
	{
		$operation = $this;
		
		do {
			$string = preg_replace_callback( "/\{\{(([\w]+(\[([^\{].+)\])?[:]?)+)\}\}/m", function( $matches ) use ( $operation, $arg_map ) {
				$token = Token::createFromResources( $matches[1], $operation->getResources( $arg_map ) );
				
				try {
					return (string) $token;
				}
				catch( \Exception $e ) {
					return $e->getMessage();
				}
			},
			$string, -1, $count );
		}
		while ( $count > 0 );
		
		return $string;
	}
	
	/**
	 * Get the resources for generating tokens
	 *
	 * @param	array		$event_args			Variable arguments from an event
	 * @return	array
	 */
	public function getResources( $event_args=[] )
	{
		return array(
			'feature' => $this->getFeature(),
			'event' => $this->event(),
			'event_args' => $event_args,
		);
	}
	
	/**
	 * Get the associated feature
	 *
	 * @return	Feature|NULL
	 */
	public function getFeature()
	{
		if ( $rule = $this->rule() ) {
			return $rule->getFeature();
		}
		
		return NULL;
	}
	
	/**
	 * Get the attached event
	 *
	 * @return	MWP\Rules\ECA\Event|NULL
	 */
	public function event()
	{
		if ( $rule = $this->rule() ) {
			return $rule->event();
		}
		
		return NULL;
	}
	
	/**
	 * Get the attached event
	 *
	 * @return	Rule|False
	 */
	public function rule()
	{
		if ( isset ( $this->rule ) ) {
			return $this->rule;
		}
		
		try	{
			$this->rule = Rule::load( $this->rule_id );
		}
		catch ( \OutOfRangeException $e ) {
			$this->rule = FALSE;
		}
		
		return $this->rule;
	}
	
	/**
	 * Get a form friendly key
	 *
	 * @return	string
	 */
	public function getFormKey()
	{
		return preg_replace('/[^\da-z]/i', '-', $this->key );
	}

}
