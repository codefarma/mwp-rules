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
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Condition';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Conditions';
	
	/**
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		return array(
			'add' => array(
				'icon' => 'glyphicon glyphicon-plus-sign',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Add New Subcondition', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'new',
					'parent_id' => $this->id,
				)
			),
			'edit' => array(
				'icon' => 'glyphicon glyphicon-cog',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Configure Condition', 'mwp-rules' ),
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
					'title' => __( 'Delete Condition', 'mwp-rules' ),
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
	 * @param	ActiveRecord					$condition					The condition to edit
	 * @return	Modern\Wordpress\Helpers\Form
	 */
	public static function getForm( $condition=NULL )
	{
		$plugin = \MWP\Rules\Plugin::instance();
		$condition = $condition ?: new static;
		$form = $plugin->createForm( 'mwp_rules_condition_form', array(), array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ), 'symfony' );
		
		/* Display details for the event */
		if ( $event = $condition->event() ) {
			$form->addHtml( 'event_details', $event->getDisplayDetails( $condition->rule() ) );
		}
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Condition Enabled?', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $condition->enabled ) ? (bool) $condition->enabled : true,
			'row_suffix' => '<hr>',
		));
		
		$plugin->buildOpConfigForm( $form, $condition, 'condition' );
		
		/** Condition specific form fields **/
		
		$form->addField( 'not', 'checkbox', array(
			'label' => __( 'NOT', 'mwp-rules' ),
			'value' => 1,
			'description' => __( 'Using NOT will reverse the condition result so that the result is TRUE if the condition is NOT MET. ', 'mwp-rules' ),
			'data' => (bool) $condition->not,
		),
		NULL, 'title' );
		
		if ( $condition->children() ) {
			$form->addField( 'group_compare', 'choice', array(
				'label' => __( 'Group Compare Mode', 'mwp-rules' ),
				'choices' => array(
					'AND' => 'and',
					'OR' => 'or',
				),
				'data' => $condition->compare_mode ?: 'and',
				'required' => true,
				'expanded' => true,
				'description' => "
					Since this condition has subconditions, you can choose how you want those subconditions to affect the state of this condition.<br>
					<ul>
						<li>If you choose AND, this condition and all subconditions must be true for this condition to be valid.</li>
						<li>If you choose OR, this condition will pass if it is valid, or if any subcondition is valid.</li>
					</ul>",
				'row_suffix' => '<hr>',
			),
			NULL, 'enabled' );
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
		\MWP\Rules\Plugin::instance()->processOpConfigForm( $values, $this, 'condition' );
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
		
		if ( ! $this->locked or $this->rule()->enable_recursion )
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
				$plugin->rulesLog( $rule->event(), $rule, $this, '--', 'Condition recursion protection (not evaluated)' );
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
		if ( ! $this->id ) {
			return array();
		}

		if ( isset( $this->childrenCache ) ) {
			return $this->childrenCache;
		}
		
		$this->childrenCache = static::loadWhere( array( 'condition_parent_id=%d', $this->id ), 'condition_weight ASC' );
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
