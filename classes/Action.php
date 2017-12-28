<?php
/**
 * Plugin Class File
 *
 * Created:   December 4, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\ActiveRecord;

/**
 * Action Class
 */
class Action extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_actions";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
        'title',
        'weight',
		'rule_id',
		'key',
		'data' => array(
			'format' => 'JSON'
		),
		'description',
        'enabled',
		'schedule_mode',
		'schedule_minutes',
		'schedule_hours',
		'schedule_days',
		'schedule_months',
		'schedule_date',
		'schedule_customcode',
		'schedule_key',
		'else',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'action_';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Action';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Actions';
	
	/**
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * Build an editing form
	 *
	 * @param	ActiveRecord					$action					The action to edit
	 * @return	Modern\Wordpress\Helpers\Form
	 */
	public static function getForm( $action=NULL )
	{
		$plugin = \MWP\Rules\Plugin::instance();
		$action = $action ?: new static;
		$form = $plugin->createForm( 'mwp_rules_action_form', array(), array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ), 'symfony' );
		
		/* Display details for the event */
		if ( $event = $action->event() ) {
			$form->addHtml( 'event_details', $event->getDisplayDetails( $action->rule() ) );
		}
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Action Enabled?', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $action->enabled ) ? (bool) $action->enabled : true,
			'row_suffix' => '<hr>',
		));
		
		/* Else action config */
		$form->addField( 'else', 'choice', array(
			'label' => __( 'Action Mode', 'mwp-rules' ),
			'choices' => array( 
				__( 'Standard - Perform when conditions on the rule ARE MET.', 'mwp-rules' ) => 0,
				__( 'Else - Perform when conditions on the rule ARE NOT MET.', 'mwp-rules' ) => 1,
			),
			'data' => $action->else,
			'required' => true,
			'expanded' => true,
		));
		
		$plugin->buildOpConfigForm( $form, $action, 'action' );
		
		$scheduling_options = array(
			__( 'Immediately', 'mwp-rules' )                        => 0,
			__( 'At the end of the event (default)', 'mwp-rules' )  => 1,
			__( 'At the end of the page load', 'mwp-rules' )        => 5,
			__( 'Fixed amount of time in the future', 'mwp-rules' ) => 2,
			__( 'A specific date in the future', 'mwp-rules' )      => 3,
			__( 'A calculated date and time', 'mwp-rules' )         => 4,
		);
		
		$form->addField( 'schedule_mode', 'choice', array(
			'label' => __( 'Action should be executed', 'mwp-rules' ),
			'description' => "
			  <ul>
				<li>Immediate actions are taken before other rules on the same event are evaluated.</li>
				<li>Actions executed at the end of the event allow actions to queue while other rules on the same event are tested.</li>
				<li>Actions selected to execute at the end of the page load will queue until all events on the page have finished.</li>
				<li>Actions selected to happen at a future time will be queued and executed via cron.</li>
			  </ul>",
			'choices' => $scheduling_options,
			'data' => $action->schedule_mode !== NULL ? $action->schedule_mode : 1,
			'required' => true,
			'expanded' => true,
			'toggles' => array(
				2 => array( 'show' => array( '#schedule_key', '#schedule_minutes', '#schedule_hours', '#schedule_days', '#schedule_months' ) ),
				3 => array( 'show' => array( '#schedule_key', '#schedule_date' ) ),
				4 => array( 'show' => array( '#schedule_key', '#schedule_customcode' ) ),
			),
		),
		NULL, 'title' );
		
		/* Fixed amount of time in the future */
		$form->addField( 'schedule_minutes', 'integer', array( 'label' => __( 'Minutes', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_minutes' ), 'data' => (int) $action->schedule_minutes ), NULL, 'schedule_mode' );
		$form->addField( 'schedule_hours', 'integer', array( 'label' => __( 'Hours', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_hours' ), 'data' => (int) $action->schedule_hours ), NULL, 'schedule_minutes' );
		$form->addField( 'schedule_days', 'integer', array( 'label' => __( 'Days', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_days' ), 'data' => (int) $action->schedule_days ), NULL, 'schedule_hours' );
		$form->addField( 'schedule_months', 'integer', array( 'label' => __( 'Months', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_months' ), 'data' => (int) $action->schedule_months ), NULL, 'schedule_days' );
		
		/* Specific date in the future */
		$form->addField( 'schedule_date', 'datetime', array(
			'row_attr' => array( 'id' => 'schedule_date' ),
			'label' => __( 'Date', 'mwp-rules' ),
			'data' => $action->schedule_date ?: time(),
			'input' => 'timestamp',
			'widget' => 'single_text',
			'view_timezone' => get_option('timezone_string'),
		),
		NULL, 'schedule_months' );
		
		/* Custom calculated date time */
		$form->addField( 'schedule_customcode', 'codemirror', array( 
			'row_attr' => array( 'id' => 'schedule_customcode' ),
			'label' => __( 'Scheduled Date', 'mwp-rules' ),
			'data' => $action->schedule_customcode ?: "// <?php\n\nreturn;",
		),
		NULL, 'schedule_date' );
		
		$form->addField( 'schedule_key', 'text', array( 
			'row_attr' => array( 'id' => 'schedule_key' ),
			'label' => __( 'Unique Scheduling Keyphrase', 'mwp-rules' ),
			'data' => $action->schedule_key,
			'description' => __( 'Optional. Only one action will remain scheduled for any given keyphrase at a time. If an action is rescheduled, any previously scheduled actions with the same keyphrase will be removed.', 'mwp-rules' ),
		),
		NULL, 'schedule_customcode' );
		
		return $form;
	}
	
	/**
	 * Process submitted form values 
	 *
	 * @param	array			$values				Submitted form values
	 * @return	void
	 */
	public function processForm( $values )
	{
		\MWP\Rules\Plugin::instance()->processOpConfigForm( $values, $this, 'action' );
		parent::processForm( $values );
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
	 * Get the condition definition
	 * 
	 * @return	array|NULL
	 */
	public function definition()
	{
		return \MWP\Rules\Plugin::instance()->getAction( $this->key );
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Action
	 *
	 * @return	mixed
	 */
	public function invoke()
	{
		$plugin = \MWP\Rules\Plugin::instance();
		
		if ( ! $this->locked or $this->rule()->enable_recursion )
		{
			/**
			 * Lock this action from being triggered recursively by itself
			 * and creating never ending loops
			 */
			$this->locked = TRUE;
			
			try
			{
				call_user_func_array( array( $plugin, 'opInvoke' ), array( $this, 'actions', func_get_args() ) );
			}
			catch( \Exception $e )
			{
				$this->locked = FALSE;
				throw $e;
			}
			
			$this->locked = FALSE;
		}
		else
		{
			if ( $rule = $this->rule() and $rule->debug )
			{
				$plugin->rulesLog( $rule->event(), $rule, $this, '--', 'Action recursion protection (not evaluated)' );
			}
		}
	}
		
}
