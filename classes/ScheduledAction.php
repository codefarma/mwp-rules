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
		'queued',
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
		return array();
	}
	
	/**
	 * Execute the Scheduled Action
	 *
	 */
	public function execute()
	{
		if ( $this->queued ) {
			return;
		} else {
			$this->queued = time();
			$this->save();
		}
		
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
				$action = \MWP\Rules\Action::load( $this->action_id );
				
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
							$plugin->rulesLog( $event, $action->rule(), $action, $t->getMessage(), 'Error Exception', 1 );
						}
						catch( \Exception $e ) {
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
			catch ( \OutOfRangeException $e ) { }
		}

		$this->delete();
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
}
