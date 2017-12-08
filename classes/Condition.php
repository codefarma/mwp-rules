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
 * Condition Class
 */
class Condition extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_conditions";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
        'title',
        'weight',
		'parent_id',
		'rule_id',
		'key',
		'data' => array(
			'format' => 'JSON'
		),
        'enabled',
		'group_compare',
		'not',
		'enable_recursion',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'condition_';
	
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
		
		throw new \Exception( 'Condition is not assigned to a valid rule.' );
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
		return \MWP\Rules\Plugin::instance()->getCondition( $this->key );
	}
	
	/**
	 * Get Compare Mode
	 *
	 * @return	string
	 */
	public function compareMode()
	{
		return $this->group_compare ?: 'and';
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Condition
	 *
	 * @return	bool
	 */
	public function invoke()
	{
		$plugin = \MWP\Rules\Plugin::instance();
		
		if ( ! $this->locked or $this->enable_recursion )
		{
			/**
			 * Lock this from being triggered recursively
			 * and creating never ending loops
			 */
			$this->locked = TRUE;
			
			try
			{
				$result = call_user_func_array( array( $plugin, 'opInvoke' ), array( $this, 'conditions', func_get_args() ) );
			}
			catch( \Exception $e )
			{
				$this->locked = FALSE;
				throw $e;
			}
			
			if ( count( $this->children() ) )
			{
				$compareMode = $this->compareMode();
				
				/**
				 * We already have a winner
				 */
				if ( $result and $compareMode == 'or' )
				{
					return TRUE;
				}
				
				/**
				 * We have already failed
				 */
				if ( ! $result and $compareMode == 'and' )
				{
					return FALSE;
				}
				
				/* Only possibilities at this point */
				// result FALSE mode OR
				// result TRUE mode AND
				
				foreach ( $this->children() as $condition )
				{
					if ( $condition->enabled )
					{
						$conditionsCount++;
						$_result = call_user_func_array( array( $condition, 'invoke' ), func_get_args() );
						
						if ( $_result and $compareMode == 'or' ) 
						{
							$result = TRUE;
							break;
						}

						if ( ! $_result and $compareMode == 'and' )
						{
							$result = FALSE;
							break;
						}
					}
					else
					{
						if ( $rule = $this->rule() and $rule->debug )
						{
							$plugin->rulesLog( $rule->event(), $rule, $condition, '--', 'Condition not evaluated (disabled)' );
						}
					}
				}
			}
			
			$this->locked = FALSE;
			
			return $result;
		}
		else
		{
			if ( $rule = $this->rule() and $rule->debug )
			{
				$plugin->rulesLog( $rule->event(), $rule, $this, '--', 'Condition recursion (not evaluated)' );
			}
		}
	}
	
	/**
	 * @var	array
	 */
	protected $childrenCache;
	
	/**
	 * Get the children
	 * 
	 * @return array[Condition]
	 */
	public function children()
	{
		if ( isset( $this->childrenCache ) ) {
			return $this->childrenCache;
		}
		
		$this->childrenCache = static::loadWhere( 'condition_parent_id=%d', $this->id );
		return $this->childrenCache;
	}
	
	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		foreach ( $this->children() as $child )
		{
			$child->delete();
		}
		
		return parent::delete();
	}	
	
}
