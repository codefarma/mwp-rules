<?php
/**
 * Plugin Class File
 *
 * Created:   December 4, 2017
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
 * Action Class
 */
class _Action extends GenericOperation
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
		'uuid',
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
				'icon' => 'glyphicon glyphicon-wrench',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Configure Action', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'edit',
					'id' => $this->id,
					'_tab' => 'operation_config',
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
	 * Get the controller
	 *
	 * @param	string		$key			The controller key
	 * @return	ActiveRecordController
	 */
	public function _getController( $key='admin' )
	{
		return $this->getPlugin()->getActionsController( $this->getRule(), $key );
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$action = $this;
		
		/* Display details for the app/bundle/parent */
		$form->addHtml( 'rule_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'rule_item' => 'rule_actions',
			'rule' => $this->getRule(), 
			'bundle' => $this->getRule() ? $this->getRule()->getBundle() : null, 
			'app' => $this->getRule() ? $this->getRule()->getApp() : null, 
		]));
		
		if ( $action->title ) {
			$form->addHtml( 'rule_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-flash"></i>',
				'label' => 'Action',
				'title' => $action->title,
			]));
		}
		
		static::buildConfigForm( $form, $action );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Action Enabled?', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $action->enabled ) ? (bool) $action->enabled : true,
			'row_suffix' => '<hr>',
			'required' => false,
		),
		'operation_details', 'key', 'before' );
		
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
		),
		'operation_details', 'title' );
		
		if ( $action->id ) {
			
			$schedule_default = 1;
			
			if ( isset( $action->definition()->updates_filter ) and $action->definition()->updates_filter ) {
				$schedule_default = 0;
				$scheduling_options = array(
					__( 'Immediate - No Delay', 'mwp-rules' ) => 0,
				);
			} 
			else {
				$scheduling_options = array(
					__( 'Immediate - No Delay', 'mwp-rules' ) => 0,
					__( 'Queued until the end of the event (default)', 'mwp-rules' ) => 1,
					__( 'Queued until the end of the page load', 'mwp-rules' ) => 5,
					__( 'Fixed amount of time in the future', 'mwp-rules' ) => 2,
					__( 'A specific date in the future', 'mwp-rules' ) => 3,
					__( 'A calculated date and time', 'mwp-rules' ) => 4,
				);
			}
			
			$form->addTab( 'operation_advanced', array( 
				'title' => __( 'Advanced', 'mwp-rules' ),
			));
		
			$form->addField( 'schedule_mode', 'choice', array(
				'label' => __( 'Action execution is', 'mwp-rules' ),
				'description' => "
				  <ul>
					<li style='margin-bottom:0'>Immediate actions happen in real time and can affect other rules attached to the same event that come afterwards.</li>
					<li style='margin-bottom:0'>Actions queued until the end of the event allow other rules on the event to operate using the same starting conditions.</li>
					<li style='margin-bottom:0'>Actions queued until the end of the page load allow all rules on all other events to happen before the action is taken.</li>
					<li style='margin-bottom:0'>Actions selected to happen at a future time will be queued and executed via cron when the designated time comes.</li>
				  </ul>",
				'choices' => $scheduling_options,
				'data' => $action->schedule_mode !== NULL ? $action->schedule_mode : ( isset( $action->definition()->default_mode ) ? $action->definition()->default_mode : $schedule_default ),
				'required' => true,
				'expanded' => true,
				'toggles' => array(
					2 => array( 'show' => array( '#schedule_key', '#schedule_minutes', '#schedule_hours', '#schedule_days', '#schedule_months' ) ),
					3 => array( 'show' => array( '#schedule_key', '#schedule_date' ) ),
					4 => array( 'show' => array( '#schedule_key', '#schedule_customcode' ) ),
				),
				'row_suffix' => '<hr>',
			));
		
			$form->addField( 'schedule_key', 'text', array( 
				'row_attr' => array( 'id' => 'schedule_key' ),
				'label' => __( 'Unique Scheduling Keyphrase', 'mwp-rules' ),
				'data' => $action->schedule_key,
				'description' => __( 'Optional. Only one action will remain scheduled for any given keyphrase at a time. If an action is rescheduled, any previously scheduled actions with the same keyphrase will be removed. You can use tokens in the keyphrase and they will be replaced.', 'mwp-rules' ),
				'required' => false,
				'field_suffix' => '<hr>',
			));
			
			/* Fixed amount of time in the future */
			$form->addField( 'schedule_minutes', 'integer', array( 'label' => __( 'Minutes', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_minutes' ), 'data' => (int) $action->schedule_minutes ) );
			$form->addField( 'schedule_hours', 'integer', array( 'label' => __( 'Hours', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_hours' ), 'data' => (int) $action->schedule_hours ) );
			$form->addField( 'schedule_days', 'integer', array( 'label' => __( 'Days', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_days' ), 'data' => (int) $action->schedule_days ) );
			$form->addField( 'schedule_months', 'integer', array( 'label' => __( 'Months', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_months' ), 'data' => (int) $action->schedule_months ) );
			
			/* Specific date in the future */
			$form->addField( 'schedule_date', 'datetime', array(
				'row_attr' => array( 'id' => 'schedule_date' ),
				'label' => __( 'Date', 'mwp-rules' ),
				'data' => $action->schedule_date ?: time(),
				'input' => 'timestamp',
				'widget' => 'single_text',
				'view_timezone' => get_option('timezone_string'),
			));
			
			/* Custom calculated date time */
			$form->addField( 'schedule_customcode', 'textarea', array( 
				'row_attr' => array( 'id' => 'schedule_customcode', 'data-view-model' => 'mwp-rules' ),
				'label' => __( 'Scheduled Date', 'mwp-rules' ),
				'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
				'data' => $action->schedule_customcode ?: "// <?php\n\nreturn strtotime('tomorrow 8:00am');",
				'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 
					'operation' => $action, 
					'event' => $action->event(), 
					'return_args' => array( 
						__( '<strong>int</strong> - A unix timestamp', 'mwp-rules' ), 
						__( '<strong>object</strong> Instance of a DateTime object', 'mwp-rules' ),
						__( '<strong>string</strong> - A date/time string', 'mwp-rules' ),
					), 
				)),
			));
			
		}
		
		if ( ! $action->id ) {
			$form->onComplete( function() use ( $action, $plugin ) {
				$controller = $plugin->getActionsController( $action->getRule() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $action->id(), '_tab' => 'operation_config' ) ) );
				exit;
			});
		}
		
		return $form;
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
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	Rule			$rule_id			The parent rule id
	 * @return	array
	 */
	public static function import( $data, $rule_id )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$action = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$action->_setDirectly( $col, $value );
			}
			
			$action->rule_id = $rule_id;
			$action->imported = time();
			$result = $action->save();
			
			if ( ! is_wp_error( $result ) ) {
				$results['imports']['actions'][] = $data;
			} else {
				$results['errors']['actions'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Get the action url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getActionsController( $this->getRule() )->getUrl( array_merge( array( 'id' => $this->id, 'do' => 'edit' ), $params ) );
	}
	
	/**
	 * Save
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		return parent::save();
	}
	
}
