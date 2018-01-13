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
class Action extends GenericOperation
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
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * @var string
	 */
	public static $optype = 'action';
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		return array(
			'edit' => array(
				'icon' => 'glyphicon glyphicon-cog',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Configure Action', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'edit',
					'id' => $this->id,
				),
			),
			'delete' => array(
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Delete Action', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'delete',
					'id' => $this->id,
				),
			)
		);
	}
	
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
			'required' => false,
		));
		
		/* Else action config */
		$form->addField( 'else', 'choice', array(
			'label' => __( 'Action Mode', 'mwp-rules' ),
			'choices' => array( 
				__( 'Standard - Perform when conditions on the rule ARE MET.', 'mwp-rules' ) => 0,
				__( 'Else - Perform when conditions on the rule ARE NOT MET.', 'mwp-rules' ) => 1,
			),
			'data' => (int) $action->else,
			'required' => true,
			'expanded' => true,
		));
		
		static::buildConfigForm( $form, $action );
		
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
			'description' => $plugin->getTemplateContent( 'rules/phpcode_description', array( 
				'operation' => $action, 
				'event' => $action->event(), 
				'return_args' => array( 
					__( '<strong>int</strong> - A unix timestamp', 'mwp-rules' ), 
					__( '<strong>object</strong> Instance of a DateTime object', 'mwp-rules' ),
					__( '<strong>string</strong> - A date/time string', 'mwp-rules' ),
				), 
			)),
		),
		NULL, 'schedule_date' );
		
		$form->addField( 'schedule_key', 'text', array( 
			'row_attr' => array( 'id' => 'schedule_key' ),
			'label' => __( 'Unique Scheduling Keyphrase', 'mwp-rules' ),
			'data' => $action->schedule_key,
			'description' => __( 'Optional. Only one action will remain scheduled for any given keyphrase at a time. If an action is rescheduled, any previously scheduled actions with the same keyphrase will be removed.', 'mwp-rules' ),
			'required' => false,
		),
		NULL, 'schedule_customcode' );
		
		if ( ! $action->id ) {
			$form->onComplete( function() use ( $action, $plugin ) {
				$controller = $plugin->getActionsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $action->id ) ) );
				exit;
			});
		}
		
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
		$this->processConfigForm( $values );
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
				$this->opInvoke( func_get_args() );
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
	
	/**
	 * Get the action url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getActionsController()->getUrl( array_merge( array( 'id' => $this->id, 'do' => 'edit', 'rule_id' => $this->rule_id ), $params ) );
	}
	
}
