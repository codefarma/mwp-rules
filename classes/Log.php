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
 * Log Class
 */
class _Log extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_logs";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
        'event_type',
        'event_hook',
		'result',
		'message',
		'time',
		'thread',
		'rule_id',
		'op_id',
		'type',
		'parent',
		'rule_parent',
		'error',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = '';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'Inspect';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Log';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Logs';
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		return array(
			'view' => array(
				'title' => __( static::$lang_view . ' ' . static::$lang_singular, 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-zoom-in',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
				),
				'params' => array(
					'do' => 'view',
					'id' => $this->id,
				),
			),
		);
	}
	
	/**
	 * Get log view html
	 *
	 * @return	string
	 */
	public function getView()
	{
		$logsController = $this->getPlugin()->getLogsController();
		
		$logConditionsTable = $logsController->createDisplayTable( array( 
			'constructor' => array(
				'singular' => 'condition',
				'plural' => 'conditions',
			),
			'columns' => array( 
				'op_id' => __( 'Condition Title', 'mwp-rules' ),
				'message' => __( 'Status', 'mwp-rules' ),
				'result' => __( 'Result', 'mwp-rules' ),
			),
			'actionsColumn' => '_none_',
		));
		
		$logConditionsTable->displayBottomNavigation = false;
		$logConditionsTable->prepare_items( array( 'thread=%s AND type=%s AND rule_id=%d', $this->thread, 'MWP\Rules\Condition', $this->rule_id ) );
		
		$logActionsTable = $logsController->createDisplayTable( array( 
			'constructor' => array(
				'singular' => 'action',
				'plural' => 'actions',
			),
			'sort_by' => 'time',
			'sort_order' => 'ASC',
			'columns' => array(
				'op_id' => __( 'Action Title', 'mwp-rules' ),
				'message' => __( 'Status', 'mwp-rules' ),
				'result' => __( 'Result', 'mwp-rules' ),
				'time' => __( 'Date/Time', 'mwp-rules' ),
			),
			'actionsColumn' => '_none_',
		));
		
		$logActionsTable->displayBottomNavigation = false;
		$logActionsTable->prepare_items( array( 'thread=%s AND type=%s AND rule_id=%d', $this->thread, 'MWP\Rules\Action', $this->rule_id ) );
		
		$logSubrulesTable = $logsController->createDisplayTable( array( 
			'constructor' => array(
				'singular' => 'rule',
				'plural' => 'rules',
			),
			'columns' => array( 
				'rule_id' => __( 'Rule', 'mwp-rules' ),
				'message' => __( 'Status', 'mwp-rules' ),
				'result' => __( 'Result', 'mwp-rules' ),
			),
		));
		
		$logSubrulesTable->displayBottomNavigation = false;
		$logSubrulesTable->prepare_items( array( 'thread=%s AND op_id=0 AND rule_parent=%d', $this->thread, $this->rule_id ) );
		
		return $this->getPlugin()->getTemplateContent( 'rules/logs/view', array( 
			'log' => $this,
			'conditions' => $logConditionsTable,
			'actions' => $logActionsTable,
			'subrules' => $logSubrulesTable,
		));
	}
	
	/**
	 * Get the log rule
	 *
	 * @return	MWP\Rules\Rule|NULL
	 */
	public function rule()
	{
		if ( $this->rule_id ) {
			try {
				return \MWP\Rules\Rule::load( $this->rule_id );
			}
			catch( \OutOfRangeException $e ) { }
		}
	}

	/**
	 * Get the log event
	 *
	 * @return	MWP\Rules\ECA\Event|NULL
	 */
	public function event()
	{
		if ( $rule = $this->rule() ) {
			return $rule->event();
		}
	}
	
}
