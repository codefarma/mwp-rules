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
	 * Build an editing form
	 *
	 * @param	ActiveRecord		$rule					The rule to edit
	 * @return	Modern\Wordpress\Helpers\Form
	 */
	public static function getForm( $rule )
	{
		$plugin = \MWP\Rules\Plugin::instance();
		$rule = $rule ?: new Rule;
		$form = $plugin->createForm( 'mwp_rules_rule_form', array(), array( 'attr' => array( 'class' => 'form-horizontal' ) ), 'symfony' );
		
		$event_choices = array();
		
		$form->addTab( 'rule_settings', array( 
			'title' => __( 'Settings', 'mwp-rules' ) 
		));
		
		/**
		 * Rule title
		 */
		$form->addField( 'title', 'text', array(
			'label' => __( 'Rule Title', 'mwp-rules' ),
			'description' => __( 'Summarize the intended purpose of this rule.' ),
			'data' => $rule->title,
			'attr' => array( 'placeholder' => __( 'Describe what this rule is for', 'mwp-rules' ) ),
			'required' => true,
		), 
		'rule_settings' );
		
		/* Step 1: Configure the event for new rules */
		if ( ! $rule->id and ! $rule->parent_id ) 
		{
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
			'rule_settings' );
		
			$form->addField( 'submit', 'submit', array( 
				'label' => __( 'Continue', 'mwp-rules' ), 
				'attr' => array( 'class' => 'btn btn-primary' ) 
			));
			
			return $form;
		}
		else
		{
			/* Display details for the already configured event */
			$event = $plugin->getEvent( $rule->event_type, $rule->event_hook );
			if ( $event ) {
				// @TODO: add event description html
			}
			
			$form->addField( 'debug', 'checkbox', array( 
				'label' => __( 'Debug Mode', 'mwp-rules' ),
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
		$conditionsTable = $conditionsController->getDisplayTable();
		
		/* Linked conditions table */
		$conditionsTable->prepare_items( array( 'condition_rule_id=%d AND condition_parent_id=0', $rule->id ) );
		$form->addHtml( $plugin->getTemplateContent( 'rules/conditions/table', array( 
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
		$actionsTable = $actionsController->getDisplayTable();
		
		/* Linked actions table (normal actions)*/
		$actionsTable->prepare_items( array( 'action_rule_id=%d AND action_else=0', $rule->id ) );
		$form->addHtml( $plugin->getTemplateContent( 'rules/actions/table', array( 
			'show_buttons' => true,
			'rule' => $rule, 
			'table' => $actionsTable, 
			'controller' => $actionsController 
		)), 
		'rule_actions' );
		
		/* Linked actions table (else actions)*/
		$actionsTable->prepare_items( array( 'action_rule_id=%d AND action_else=1', $rule->id ) );
		$form->addHeading( __( 'Else Actions', 'mwp-rules' ), 'rule_actions' );
		$form->addHtml( $plugin->getTemplateContent( 'rules/actions/table', array(
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
			'attr' => array( 'class' => 'btn btn-primary' ) 
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
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{	
		$events 	= array();
		$event_missing 	= FALSE;
		
		$form->addTab( 'rules_settings' );
		
		/**
		 * New Child Rules Inherit Event From Parent
		 */
		if 
		( 
			! $this->id and 
			(
				\IPS\Request::i()->parent and
				! \IPS\Request::i()->subnode
			)
		)
		{
			$parent = \IPS\rules\Rule::load( \IPS\Request::i()->parent );
			$this->event_app 	= $parent->event_app;
			$this->event_class 	= $parent->event_class;
			$this->event_key	= $parent->event_key;
			$form->actionButtons 	= array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( 'rules_next', 'submit', null, 'ipsButton ipsButton_primary', array( 'accesskey' => 's' ) ) );
		}
		
		/**
		 * Root rules can be moved between rule sets
		 */
		else if	( ! $this->parent() )
		{
			if ( \IPS\rules\Rule\Ruleset::roots( NULL ) )
			{
				$ruleset_id = $this->ruleset_id ?: 0;
				if 
				( 
					! $this->id and
					\IPS\Request::i()->subnode == 1 and
					\IPS\Request::i()->parent
				)
				{
					$ruleset_id = \IPS\Request::i()->parent;
				}
				
				$form->add( new \IPS\Helpers\Form\Node( 'rule_ruleset_id', $ruleset_id, TRUE, array( 'class' => '\IPS\rules\Rule\Ruleset', 'zeroVal' => 'rule_no_ruleset', 'subnodes' => FALSE ) ) );
			}
		}
		
		if ( $this->event_key and $this->event()->placeholder )
		{
			$form->addHtml( \IPS\Theme::i()->getTemplate( 'components' )->missingEvent( $this ) );
			$event_missing = TRUE;
		}

		/**
		 * If the event hasn't been configured for this rule, build an option list
		 * for all available events for the user to select.
		 */
		if ( ! $this->event_key )
		{
			$form->actionButtons 	= array( \IPS\Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( 'rules_next', 'submit', null, 'ipsButton ipsButton_primary', array( 'accesskey' => 's' ) ) );
			foreach ( \IPS\rules\Application::rulesDefinitions() as $definition_key => $definition )
			{
				foreach ( $definition[ 'events' ] as $event_key => $event_data )
				{
					$group = ( isset( $event_data[ 'group' ] ) and $event_data[ 'group' ] ) ? $event_data[ 'group' ] : $definition[ 'group' ];
					$events[ $group ][ $definition_key . '_' . $event_key ] = $definition[ 'app' ] . '_' . $definition[ 'class' ] . '_event_' . $event_key;
				}
			}
			$form->add( new \IPS\Helpers\Form\Select( 'rule_event_selection', $this->id ? md5( $this->event_app . $this->event_class ) . '_' . $this->event_key : NULL, TRUE, array( 'options' => $events, 'noDefault' => TRUE ), NULL, "<div class='chosen-collapse' data-controller='rules.admin.ui.chosen'>", "</div>", 'rule_event_selection' ) );
		}
		
		/* Rule Title */
		$form->add( new \IPS\Helpers\Form\Text( 'rule_title', $this->title, TRUE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'rule_title_placeholder' ) ) ) );
		
		/**
		 * Conditions & Actions
		 *
		 * Only allow configuration if the rule has been saved (it needs an ID),
		 * and if the event it is assigned to has a valid definition.
		 */
		if ( $this->id and ! $event_missing )
		{			
			$form->add( new \IPS\Helpers\Form\YesNo( 'rule_debug', $this->debug, FALSE ) );
			
			if ( isset( \IPS\Request::i()->tab ) )
			{
				$form->activeTab = 'rules_' . \IPS\Request::i()->tab;
			}
		
			$form->addTab( 'rules_conditions' );
			$form->addHeader( 'rule_conditions' );
			
			$compare_options = array(
				'and' 	=> 'AND',
				'or'	=> 'OR',
			);
			
			$form->add( new \IPS\Helpers\Form\Radio( 'rule_base_compare', $this->base_compare ?: 'and', FALSE, array( 'options' => $compare_options ), NULL, NULL, NULL, 'rule_base_compare' ) );
			
			/* Just a little nudging */
			$form->addHtml( "
				<style>
					#rule_base_compare br { display:none; }
					#elRadio_rule_base_compare_rule_base_compare { width: 100px; display:inline-block; }
				</style>
			" );
			
			/**
			 * Rule Conditions
			 */
			$conditionClass		= '\IPS\rules\Condition';
			$conditionController 	= new \IPS\rules\modules\admin\rules\conditions( NULL, $this );
			$conditions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=conditions&rule={$this->id}" ),
							$conditionClass::$nodeTitle, 
							array( $conditionController, '_getRoots' ), 
							array( $conditionController, '_getRow' ), 
							array( $conditionController, '_getRowParentId' ), 
							array( $conditionController, '_getChildren' ), 
							array( $conditionController, '_getRootButtons' )
						);
			
			/* Replace form constructs with div's */
			$conditionsTreeHtml = (string) $conditions;
			$conditionsTreeHtml = str_replace( '<form ', '<div ', $conditionsTreeHtml );
			$conditionsTreeHtml = str_replace( '</form>', '</div>', $conditionsTreeHtml );
			$form->addHtml( $conditionsTreeHtml );
			
			/**
			 * Rule Actions
			 */
			$form->addTab( 'rules_actions' );
			$form->addHeader( 'rule_actions' );
			
			$actionClass		= '\IPS\rules\Action';
			$actionController 	= new \IPS\rules\modules\admin\rules\actions( NULL, $this, \IPS\rules\ACTION_STANDARD );
			$actions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&rule={$this->id}" ),
							$actionClass::$nodeTitle, 
							array( $actionController, '_getRoots' ), 
							array( $actionController, '_getRow' ), 
							array( $actionController, '_getRowParentId' ), 
							array( $actionController, '_getChildren' ), 
							array( $actionController, '_getRootButtons' )
						);
			
			/* Replace form constructs with div's */
			$actionsTreeHtml = (string) $actions;
			$actionsTreeHtml = str_replace( '<form ', '<div ', $actionsTreeHtml );
			$actionsTreeHtml = str_replace( '</form>', '</div>', $actionsTreeHtml );
			$form->addHtml( $actionsTreeHtml );
			
			/* Else Actions */
			$form->addHeader( 'rules_actions_else' );
			$form->addHtml( '<p class="ipsPad">' . \IPS\Member::loggedIn()->language()->addToStack( 'rules_actions_else_description' ) . '</p>' );
			
			$elseActionController 	= new \IPS\rules\modules\admin\rules\actions( NULL, $this, \IPS\rules\ACTION_ELSE );
			$elseActions 		= new \IPS\Helpers\Tree\Tree( 
							\IPS\Http\Url::internal( "app=rules&module=rules&controller=actions&rule={$this->id}" ),
							$actionClass::$nodeTitle, 
							array( $elseActionController, '_getRoots' ), 
							array( $elseActionController, '_getRow' ), 
							array( $elseActionController, '_getRowParentId' ), 
							array( $elseActionController, '_getChildren' ), 
							array( $elseActionController, '_getRootButtons' )
						);
			
			/* Replace form constructs with div's */
			$elseActionsTreeHtml = (string) $elseActions;
			$elseActionsTreeHtml = str_replace( '<form ', '<div ', $elseActionsTreeHtml );
			$elseActionsTreeHtml = str_replace( '</form>', '</div>', $elseActionsTreeHtml );
			$form->addHtml( $elseActionsTreeHtml );			
			
			/**
			 * Show debugging console for this rule if debugging is enabled
			 */
			if ( $this->debug )
			{
				$form->addTab( 'rules_debug_console' );
				
				$self 		= $this;
				$controllerUrl 	= \IPS\Http\Url::internal( "app=rules&module=rules&controller=rulesets&do=viewlog" );
				$table 		= new \IPS\Helpers\Table\Db( 'rules_logs', \IPS\Http\Url::internal( "app=rules&module=rules&controller=rules&do=form&id=". $this->id ), array( 'rule_id=? AND op_id=0', $this->id ) );
				$table->include = array( 'time', 'message', 'result' );
				$table->parsers = array(
					'time'	=> function( $val )
					{
						return (string) \IPS\DateTime::ts( $val );
					},
					'result' => function( $val )
					{
						return $val;
					},
				);			
				$table->sortBy = 'time';
				$table->rowButtons = function( $row ) use ( $self, $controllerUrl )
				{	
					$buttons = array();
					
					$buttons[ 'view' ] = array(
						'icon'		=> 'search',
						'title'		=> 'View Details',
						'id'		=> "{$row['id']}-view",
						'link'		=> $controllerUrl->setQueryString( array( 'logid' => $row[ 'id' ] ) ),
						'data'		=> array( 'ipsDialog' => '' ),
					);
					
					return $buttons;
				};
		
				$form->addHtml( (string) $table );
			}			
			
		}
		
		parent::form( $form );
	}
	
	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		if ( isset( $values[ 'rule_event_selection' ] ) )
		{
			list( $definition_key, $event_key ) = explode( '_', $values[ 'rule_event_selection' ], 2 );
			
			if ( $definition = \IPS\rules\Application::rulesDefinitions( $definition_key ) )
			{
				$values[ 'rule_event_app' ]	= $definition[ 'app' ];
				$values[ 'rule_event_class' ]	= $definition[ 'class' ];
				$values[ 'rule_event_key' ] 	= $event_key;
			}
			
			unset( $values[ 'rule_event_selection' ] );
		}
		
		if ( isset ( $values[ 'rule_ruleset_id' ] ) and is_object( $values[ 'rule_ruleset_id' ] ) )
		{
			$values[ 'rule_ruleset_id' ] = $values[ 'rule_ruleset_id' ]->id;
		}
		
		parent::saveForm( $values );
		
		/**
		 * Save Footprint
		 */
		$this->init();
		if ( $this->event()->data !== NULL )
		{
			$this->event_footprint = md5( json_encode( $this->event()->data[ 'arguments' ] ) );
			$this->save();
		}
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

}
