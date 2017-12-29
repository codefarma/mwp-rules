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

use Modern\Wordpress\Pattern\ActiveRecord;
use MWP\Rules\Condition;
use MWP\Rules\Action;

/**
 * Rule Class
 */
class Rule extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_rules";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
        'title',
        'weight',
        'enabled',
		'parent_id',
		'event_type',
		'event_hook',
		'args',
		'priority',
		'base_compare',
		'debug',
		'ruleset_id',
		'enable_recursion',
		'recursion_limit',
		'imported_time',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'rule_';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Rule';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Rules';
	
	/**
	 * Build an editing form
	 *
	 * @param	ActiveRecord		$rule					The rule to edit
	 * @return	Modern\Wordpress\Helpers\Form
	 */
	public static function getForm( $rule=NULL )
	{
		$plugin = \MWP\Rules\Plugin::instance();
		$rule = $rule ?: new Rule;
		$form = $plugin->createForm( 'mwp_rules_rule_form', array(), array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ), 'symfony' );
		
		/* Display details for the event */
		if ( $event = $rule->event() ) {
			$form->addHtml( 'event_details', $event->getDisplayDetails() );
		}
		
		$form->addTab( 'rule_settings', array( 
			'title' => __( 'Settings', 'mwp-rules' ) 
		));
		
		/**
		 * Rule title
		 */
		$form->addField( 'title', 'text', array(
			'label' => __( 'Rule Description', 'mwp-rules' ),
			'description' => __( 'Summarize what you intend to do with this rule.', 'mwp-rules' ),
			'data' => $rule->title,
			'attr' => array( 'placeholder' => __( 'Describe what this rule is for', 'mwp-rules' ) ),
			'required' => true,
		), 
		'rule_settings' );
		
		/* Step 1: Configure the event for new rules */
		if ( ! $rule->id and ! $rule->parent_id ) 
		{
			$event_choices = array();
			
			foreach( array( 'action', 'filter' ) as $type ) {
				foreach( $plugin->getEvents( $type ) as $event ) {
					$event_choices[ ucwords( $type ) ][ $event->title ] = $event->type . '/' . $event->hook;
				}
			}
			
			$form->addField( 'event', 'choice', array(
				'label' => __( 'Rule Triggered When:', 'mwp-rules' ),
				'choices' => $event_choices,
				'data' => $rule->event_type . '/' . $rule->event_hook,
				'required' => true,
			),
			'rule_settings', 'title', 'before' );
		
			$form->addField( 'submit', 'submit', array( 
				'label' => __( 'Continue', 'mwp-rules' ), 
				'attr' => array( 'class' => 'btn btn-primary' ),
				'row_attr' => array( 'class' => 'text-center' ),
			));
			
			return $form;
		}
		else
		{
			$form->addField( 'enabled', 'checkbox', array( 
				'row_prefix' => '<hr>',
				'label' => __( 'Rule Enabled', 'mwp-rules' ),
				'value' => 1,
				'description' => __( 'Enable the operation of this rule' ),
				'data' => isset( $rule->enabled ) ? (bool) $rule->enabled : true,
			),
			'rule_settings' );
			
			$form->addField( 'debug', 'checkbox', array( 
				'label' => __( 'Rule Debug', 'mwp-rules' ),
				'value' => 1,
				'description' => __( 'Enable debug logs for this rule' ),
				'data' => (bool) $rule->debug,
			),
			'rule_settings' );
		}
		
		/**
		 * Conditions tab
		 */
		$form->addTab( 'rule_conditions', array(
			'title' => __( 'Conditions', 'mwp-rules' ),
		));
		
		/* Base compare mode */
		$form->addField( 'base_compare', 'choice', array(
			'label' => __( 'Base Conditions Comparison', 'mwp-rules' ),
			'choices' => array( 'AND' => 'and', 'OR' => 'or' ),
			'required' => true,
			'data' => $rule->base_compare ?: 'and',
			'expanded' => true,
			'description' => "<p>You can choose how you want the base conditions for this rule to be evaluated.</p>
				<ol>
					<li>If you choose AND, all base conditions must be valid for actions to be executed.</li>
					<li>If you choose OR, actions will be executed if any base condition is valid.</li>
				</ol>",
		), 
		'rule_conditions');
		
		$conditionsController = $plugin->getConditionsController( $rule );
		$conditionsTable = $conditionsController->createDisplayTable();
		
		/* Linked conditions table */
		$conditionsTable->prepare_items( array( 'condition_rule_id=%d AND condition_parent_id=0', $rule->id ) );
		$form->addHtml( 'conditions_table', $plugin->getTemplateContent( 'rules/conditions/table_wrapper', array( 
			'rule' => $rule, 
			'table' => $conditionsTable, 
			'controller' => $conditionsController 
		)), 
		'rule_conditions' );
		
		/**
		 * Actions tab
		 */
		$form->addTab( 'rule_actions', array(
			'title' => __( 'Actions', 'mwp-rules' ),
		));
		
		$actionsController = $plugin->getActionsController( $rule );
		$actionsTable = $actionsController->createDisplayTable();
		
		/* Linked actions table (normal actions)*/
		$actionsTable->prepare_items( array( 'action_rule_id=%d AND action_else=0', $rule->id ) );
		$form->addHtml( 'actions_table', $plugin->getTemplateContent( 'rules/actions/table_wrapper', array( 
			'show_buttons' => true,
			'rule' => $rule, 
			'table' => $actionsTable, 
			'controller' => $actionsController 
		)), 
		'rule_actions' );
		
		/* Linked actions table (else actions)*/
		$actionsTable->prepare_items( array( 'action_rule_id=%d AND action_else=1', $rule->id ) );
		$form->addHeading( 'else_actions_heading', __( 'Else Actions', 'mwp-rules' ), 'rule_actions' );
		$form->addHtml( 'actions_else_table', $plugin->getTemplateContent( 'rules/actions/table_wrapper', array(
			'show_buttons' => false,
			'rule' => $rule, 
			'table' => $actionsTable, 
			'controller' => $actionsController 
		)), 
		'rule_actions' );
		
		/**
		 * Debug tab
		 */
		if ( $rule->debug ) {
			$form->addTab( 'rule_debug_console', array(
				'title' => __( 'Debug Console', 'mwp-rules' ),
			));
		}
		
		$form->addField( 'submit', 'submit', array( 
			'label' => __( 'Save Rule', 'mwp-rules' ), 
			'attr' => array( 'class' => 'btn btn-primary' ),
			'row_attr' => array( 'class' => 'text-center' ),
		));
		
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
		if ( isset( $values['event'] ) ) {
			$event_parts = explode( '/', $values['event'] );
			$type = array_shift( $event_parts );
			$hook = implode( '/', $event_parts );
			
			$values['event_type'] = $type;
			$values['event_hook'] = $hook;
		}
		
		parent::processForm( $values );
	}
	
	/**
	 * Deploy to wordpress via hooks
	 *
	 * @return	bool
	 */
	public function deploy()
	{
		if ( $event = $this->event() ) {
			return $event->deployRule( $this );
		}
		
		return false;
	}

	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * @var	int
	 */
	public $recursionCount = 0;
	
	/**
	 * @var	array
	 */
	public $filtered_values = array();
	
	/**
	 * Invoke Rule
	 */
	public function invoke()
	{
		$plugin = \MWP\Rules\Plugin::instance();
		$args = func_get_args();

		if ( $this->event_type == 'filter' ) {
			$this->filtered_values[ $this->event()->thread ] = $args[0];
		}
		
		if ( $this->enabled )
		{
			if ( ( ! $this->locked or $this->enable_recursion ) and ! $this->event()->locked and $this->recursionCount < $this->recursion_limit )
			{
				try
				{
					$this->recursionCount++;
					$this->locked = TRUE;
				
					$compareMode     = $this->compareMode();
					$conditions		 = $this->conditions();
					$conditionsCount = 0;
					
					/**
					 * For 'or' operations, starting condition is FALSE
					 * For 'and' operations, starting condition is TRUE
					 */
					$conditionsValid = $compareMode != 'or';
					
					foreach ( $conditions as $condition )
					{
						if ( $condition->enabled )
						{
							$conditionsCount++;
							$result = call_user_func_array( array( $condition, 'invoke' ), $args );
							
							if ( $result and $compareMode == 'or' ) 
							{
								$conditionsValid = TRUE;
								break;
							}

							if ( ! $result and $compareMode == 'and' )
							{
								$conditionsValid = FALSE;
								break;
							}
						}
						else
						{
							if ( $this->debug )
							{
								$plugin->rulesLog( $this->event(), $this, $condition, '--', 'Condition not evaluated (disabled)' );
							}
						}
					}
					
					if ( $conditionsValid or $conditionsCount === 0 )
					{
						foreach ( $this->actions( ACTION_STANDARD ) as $action )
						{
							if ( $action->enabled )
							{
								call_user_func_array( array( $action, 'invoke' ), $args );
								if ( $this->event_type == 'filter' ) {
									$args[0] = $this->filtered_values[ $this->event()->thread ];
								}
							}
							else
							{
								if ( $this->debug )
								{
									$plugin->rulesLog( $this->event(), $this, $action, '--', 'Action not taken (disabled)' );
								}
							}
						}
						
						foreach ( $this->children() as $_rule )
						{
							if ( $_rule->enabled )
							{
								$result = call_user_func_array( array( $_rule, 'invoke' ), $args );
								
								if ( $this->event_type == 'filter' ) {
									$args[0] = $result;
									$this->filtered_values[ $this->event()->thread ] = $args[0];
								}
							}
							else
							{
								if ( $this->debug )
								{
									$plugin->rulesLog( $this->event(), $_rule, NULL, '--', 'Rule not evaluated (disabled)' );
								}
							}
						}
						
						$this->locked = FALSE;
						$this->recursionCount--;
						
						if ( $this->debug or ( $parent = $this->parent() and $parent->debug ) ) {
							$plugin->rulesLog( $this->event(), $this, NULL, 'conditions met', 'Rule evaluated' );
						}

					}
					else
					{
						/* Else Actions */
						foreach ( $this->actions( ACTION_ELSE ) as $action )
						{
							if ( $action->enabled )
							{
								call_user_func_array( array( $action, 'invoke' ), $args );
								if ( $this->event_type == 'filter' ) {
									$args[0] = $this->filtered_values[ $this->event()->thread ];
								}
							}
							else
							{
								if ( $this->debug )
								{
									$plugin->rulesLog( $this->event(), $this, $action, '--', 'Action not taken (disabled)' );
								}
							}
						}					
					
						$this->locked = FALSE;
						$this->recursionCount--;
					
						if ( $this->debug or ( $parent = $this->parent() and $parent->debug ) ) {
							$plugin->rulesLog( $this->event(), $this, NULL, 'conditions not met', 'Rule evaluated' );
						}
					}
				}
				catch( \Exception $e )
				{
					$this->locked = FALSE;
					$this->recursionCount--;
					throw $e;
				}
			}
			else
			{
				if ( $this->debug )
				{
					$plugin->rulesLog( $this->event(), $this, NULL, '--', 'Rule recursion protection (not evaluated)' );
				}
			}
		}
		else
		{
			if ( $this->debug )
			{
				$plugin->rulesLog( $this->event(), $this, NULL, '--', 'Rule not evaluated (disabled)' );
			}
		}
		
		if ( $this->event_type == 'filter' ) {
			$filtered_value = $this->filtered_values[ $this->event()->thread ];
			unset( $this->filtered_values[ $this->event()->thread ] );
			return $filtered_value;
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
		
		$this->childrenCache = static::loadWhere( array( 'rule_parent_id=%d', $this->id ), 'rule_weight ASC' );
		return $this->childrenCache;
	}
	
	/**
	 * Get the parent rule if it exists
	 *
	 * @return	Rule|NULL
	 */
	public function parent()
	{
		try {
			return static::load( $this->parent_id );
		}
		catch( \OutOfRangeException $e ) { }
		
		return NULL;
	}
	
	/**
	 * Get the event for this rule
	 */
	public function event()
	{
		return \MWP\Rules\Plugin::instance()->getEvent( $this->event_type, $this->event_hook );
	}
	
	/**
	 * Ruleset Cache
	 */
	public $ruleset = NULL;
	
	/**
	 * Get the event for this rule
	 */
	public function ruleset()
	{
		if ( isset( $this->ruleset ) )
		{
			return $this->ruleset;
		}
		
		if ( $this->ruleset_id )
		{
			try
			{
				return $this->ruleset = Ruleset::load( $this->ruleset_id );
			}
			catch( \OutOfRangeException $e ) {}
		}
		
		return $this->ruleset = FALSE;
	}
	
	/**
	 * @brief	Cache for conditions
	 */
	protected $conditionCache = NULL;
	
	/**
	 * Retrieve enabled conditions assigned to this rule
	 */
	public function conditions()
	{
		if ( isset( $this->conditionCache ) )
		{
			return $this->conditionCache;
		}
		
		$this->conditionCache = Condition::loadWhere( array( 'condition_parent_id=0 AND condition_rule_id=%d', $this->id ), 'condition_weight ASC' );
		
		return $this->conditionCache;
	}
	
	/**
	 * @brief	Cache for actions
	 */
	protected $actionCache = array();
	
	/**
	 * Retrieve actions assigned to this rule
	 *
	 * @param	int|NULL	$mode		Mode of actions to return
	 */
	public function actions( $mode=NULL )
	{
		$cache_key = md5( json_encode( $mode ) );
		
		if ( isset( $this->actionCache[ $cache_key ] ) ) {
			return $this->actionCache[ $cache_key ];
		}
		
		$where = array( 'action_rule_id=%d', $this->id );
		
		if ( $mode !== NULL ) {
			$where = array( 'action_rule_id=%d AND action_else=%s', $this->id, $mode );
		}
		
		return $this->actionCache[ $cache_key ] = Action::loadWhere( $where, 'action_weight ASC' );
	}
	
	/**
	 * Get Compare Mode
	 */
	public function compareMode()
	{
		return $this->base_compare ?: 'and';
	}
	
	/**
	 * Get the rule url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return \MWP\Rules\Plugin::instance()->getRulesController()->getUrl( array( 'id' => $this->id, 'do' => 'edit' ) + $params );
	}

}
