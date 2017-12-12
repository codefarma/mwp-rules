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
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * Get the attached event
	 *
	 * @return	MWP\Rules\ECA\Event
	 * @throws	Exception
	 */
	public function event()
	{
		if ( $rule = $this->rule() )
		{
			return $rule->event();
		}
		
		throw new \Exception( 'Action is not assigned to a valid rule.' );
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
