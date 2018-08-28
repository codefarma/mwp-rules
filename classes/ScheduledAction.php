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
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * ScheduledAction Class
 */
class _ScheduledAction extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    protected static $table = "rules_scheduled_actions";

	/**
	 * @var	string
	 */
	protected static $plugin_class = 'MWP\Rules\Plugin';
	
    /**
     * @var    array        Table columns
     */
    protected static $columns = array(
        'id',
        'time',
		'data' => array(
			'format' => 'JSON'
		),
		'unique_key',
        'action_id',
		'running',
		'thread',
		'parent_thread',
		'created',
		'custom_id',
    );

    /**
     * @var    string        Table primary key
     */
    protected static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    protected static $prefix = 'schedule_';
	
	/**
	 * @var		bool
	 */
	protected static $site_specific = TRUE;
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Scheduled Action';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Scheduled Actions';
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		$actions = parent::getControllerActions();
		
		return $actions;
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'schedule', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$data = $this->data;
		
		$form->addField( 'schedule_type', 'choice', array(
			'label' => __( 'Scheduled Time', 'mwp-rules' ),
			'description' => __( 'When do you want this action to run?', 'mwp-rules' ),
			'choices' => array( 'Specified Date/Time' => 'datetime', 'Right Now' => 'immediate' ),
			'data' => 'datetime',
			'toggles' => array( 'immediate' => array( 'hide' => array( '#schedule_time' ) ) ),
			'expanded' => true,
			'required' => true,			
		));
		
		$form->addField( 'time', 'datetime', array(
			'row_attr' => array( 'id' => 'schedule_time' ),
			'label' => __( 'Date/Time', 'mwp-rules' ),
			'input' => 'timestamp',
			'view_timezone' => get_option('timezone_string') ?: 'UTC',
			'data' => $this->time,
		));
		
		$form->addField( 'recurrance_type', 'choice', array(
			'label' => __( 'Recurrance', 'mwp-rules' ),
			'choices' => array(
				'One Time Only' => 'once',
				'Repeating Interval' => 'repeating',
			),
			'toggles' => array(
				'repeating' => array( 'show' => array( '#schedule_minutes', '#schedule_hours', '#schedule_days', '#schedule_months' ) ),
			),
			'data' => isset( $data['recurrance'] ) ? $data['recurrance'] : 'once',
			'required' => true,
			'expanded' => true,
		));
		
		$form->addField( 'recurrance_minutes', 'integer', array( 'label' => __( 'Minutes', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_minutes' ), 'data' => isset( $data['minutes'] ) ? $data['minutes'] : 0 ) );
		$form->addField( 'recurrance_hours', 'integer', array( 'label' => __( 'Hours', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_hours' ), 'data' => isset( $data['hours'] ) ? $data['hours'] : 0 ) );
		$form->addField( 'recurrance_days', 'integer', array( 'label' => __( 'Days', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_days' ), 'data' => isset( $data['days'] ) ? $data['days'] : 0 ) );
		$form->addField( 'recurrance_months', 'integer', array( 'label' => __( 'Months', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_months' ), 'data' => isset( $data['months'] ) ? $data['months'] : 0 ) );
		
		$form->addField( 'submit', 'submit', array(
			'label' => __( 'Save', 'mwp-rules' ),
		));
		
		return $form;
	}
	
	/**
	 * Process submitted form values 
	 *
	 * @param	array			$values				Submitted form values
	 * @return	void
	 */
	protected function processEditForm( $values )
	{
		if ( $values['schedule_type'] == 'immediate' ) {
			$values['time'] = time();
		}
		
		$data = $this->data ?: array();
		
		if ( isset( $values['recurrance_type'] ) ) {
			$data['recurrance'] = $values['recurrance_type'];
		}
		
		if ( isset( $values['recurrance_minutes'] ) ) {
			$data['minutes'] = $values['recurrance_minutes'];
		}
		
		if ( isset( $values['recurrance_minutes'] ) ) {
			$data['hours'] = $values['recurrance_hours'];
		}
		
		if ( isset( $values['recurrance_minutes'] ) ) {
			$data['days'] = $values['recurrance_days'];
		}
		
		if ( isset( $values['recurrance_minutes'] ) ) {
			$data['months'] = $values['recurrance_months'];
		}
		$this->data = $data;
		
		parent::processEditForm( $values );
	}
	
	/**
	 * Execute the Scheduled Action
	 *
	 */
	public function execute( $task=NULL )
	{
		if ( $this->running ) {
			return;
		} else {
			$this->running = time();
			$this->save();
		}
		
		$deleteWhenDone = true;
		$action_data = $this->data;
		$plugin = $this->getPlugin();
		
		$args = array();
		$event_args = array();

		/**
		 * Standard Scheduled Action
		 */
		if ( $this->action_id )
		{
			foreach ( (array) $action_data[ 'args' ] as $arg ) {
				$args[] = $plugin->restoreArg( $arg );
			}

			foreach ( (array) $action_data[ 'event_args' ] as $key => $arg ) {
				$event_args[ $key ] = $plugin->restoreArg( $arg );
			}

			try
			{
				$action = Action::load( $this->action_id );
				
				if ( $event = $action->event() ) 
				{
					/**
					 * Set the event threads to match the originals. Adjust the root thread so that further triggers of
					 * this event due to this action also result in the deferred actionStack queue being executed.
					 */
					$event->thread = $this->thread;
					$event->parentThread = $this->parent_thread;					
					$event->rootThread = $this->thread;
					
					$definition = $action->definition();
					
					if ( isset( $definition->callback ) and is_callable( $definition->callback ) )
					{
						try {
							$result = call_user_func_array( $definition->callback, array_merge( $args, array( $action->data, $event_args, $action ) ) );

							if ( $rule = $action->rule() and $rule->debug ) {
								$plugin->rulesLog( $event, $rule, $action, $result, 'Evaluated' );
							}
						}
						catch( \Throwable $t ) {
							if ( isset( $task ) ) {
								$task->log( 'Error Exception: ' . $t->getMessage() . '. Check the rules system log for more details.' );
							}
							$plugin->rulesLog( $event, $action->rule(), $action, $t->getMessage(), 'Error Exception', 1 );
						}
						catch( \Exception $e ) {
							if ( isset( $task ) ) {
								$task->log( 'Error Exception: ' . $t->getMessage() . '. Check the rules system log for more details.' );
							}
							$plugin->rulesLog( $event, $action->rule(), $action, $e->getMessage(), 'Error Exception', 1 );
						}
					}
					else
					{
						if ( $rule = $action->rule() ) {
							$plugin->rulesLog( $rule->event(), $rule, $action, FALSE, 'Missing Callback', 1  );
						}
					}
				}
			}
			catch ( \OutOfRangeException $e ) { 
				if ( isset( $task ) ) {
					$task->log( 'Exception: ' . $e->getMessage() );
				}
			}
		}

		/**
		 * Custom Scheduled Actions
		 */
		else if ( $this->custom_id )
		{
			try
			{
				$hook = Hook::load( $this->custom_id );
				$arguments = $hook->getArguments();
				$deleteWhenDone = isset( $action_data['recurrance'] ) ? $action_data['recurrance'] !== 'repeating' : true;
				
				/* If the custom scheduled action was manually scheduled, it will have config_data used to obtain the arguments */
				if ( isset( $action_data['config_data'] ) ) {
					$args = array_combine( array_column( $arguments, 'varname' ), array_map( function( $a ) use ( $action_data ) {
						if ( isset( $action_data['config_data'][ $a->varname . '_argconfig_source' ] ) and $action_data['config_data'][ $a->varname . '_argconfig_source' ] == 'phpcode' ) {
							if ( isset( $action_data['config_data'][ $a->varname . '_phpcode' ] ) ) {
								$evaluate = rules_evaluation_closure( [ 'scheduled_action' => $this ] );
								return $evaluate( $action_data['config_data'][ $a->varname . '_phpcode' ] );
							}
						}
						else {
							return isset( $action_data['config_data']['arguments'][ $a->varname ] ) ? $a->getArg( $action_data['config_data']['arguments'][ $a->varname ] ) : NULL;
						}
					}, $arguments ) );					
				}
				
				/* Otherwise, load the arguments from standard storage */
				else {
					$args = array_map( function( $a ) use ( $action_data, $plugin ) { 
						if ( isset( $action_data['args'][ $a->varname ] ) ) {
							return $plugin->restoreArg( $action_data['args'][ $a->varname ] );
						}
					}, $arguments );
				}
				
				/**
				 * Process as bulk action 
				 */
				if ( isset( $action_data['bulk_option'] ) and $bulk_arg = $action_data['bulk_option'] ) 
				{
					/* Init bulk data if needed */
					if ( ! isset( $action_data['bulk_data'] ) ) {
						$action_data['bulk_data'] = array();
						$action_data['bulk_count'] = count( $action_data['bulk_data'] );
					}
					
					/* Run next bulk item */
					if ( ! empty( $action_data['bulk_data'] ) ) {
						$deleteWhenDone = false;
						$args[ $bulk_arg ] = array_shift( $action_data['bulk_data'] );					
						call_user_func_array( 'do_action', array_merge( [ $hook->hook ], array_values( $args ) ) );
						$this->data = $action_data;
						$this->save();
					}
					
					/* Reschedule if processing complete */
					else {
						unset( $action_data['bulk_data'] );
						$this->data = $action_data;
						$this->reschedule();
					}
				}
				
				/**
				 * Process as regular action 
				 */
				else {
					call_user_func_array( 'do_action', array_merge( [ $hook->hook ], array_values( $args ) ) );
					$this->reschedule();
				}
			}
			catch( \OutOfRangeException $e ) { 
				if ( isset( $task ) ) {
					$task->log( 'Custom action has been deleted. It could not be loaded.' );
				}
			}
		}

		if ( $deleteWhenDone ) {
			$this->delete();
		}
		else {
			$this->running = 0;
			$this->save();
		}	
	}
	
	/**
	 * Reschedule for the next run
	 *
	 * @return	void
	 */
	public function reschedule()
	{
		$next_run = $this->time;
		$action_data = $this->data;
		
		while ( $next_run <= time() ) {
			$interval = 
					( (int) $action_data['minutes'] * 60 ) + 
					( (int) $action_data['hours'] * 60 * 60 ) +
					( (int) $action_data['days'] * 60 * 60 * 24 ) +
					( (int) $action_data['months'] * 60 * 60 * 24 * 30 );
					
			/* If zero interval, add 5 minutes to current time and break */
			if ( $interval <= 0 ) {
				$next_run = time() + ( 60 * 5 );
				break;
			}
			
			$next_run += $interval;
		}
		
		$this->time = $next_run;
		$this->save();
	}
	
	/**
	 * Get the next scheduled action that needs to be ran
	 *
	 * @return	ScheduledAction|NULL
	 */
	public static function getNextAction()
	{
		return static::loadWhere( array( 'schedule_running=0' ), 'schedule_time ASC', 1 )[0];
	}

	/**
 	 * Save record
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		$result = parent::save();
		
		/* Make sure the rules action runner task is up to date */
		$this->getPlugin()->updateActionRunner();
		
		return $result;
	}
}
