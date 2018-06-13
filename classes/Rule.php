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
use MWP\Rules\Condition;
use MWP\Rules\Action;

/**
 * Rule Class
 */
class _Rule extends ExportableRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    protected static $table = "rules_rules";

    /**
     * @var    array        Table columns
     */
    protected static $columns = array(
        'id',
		'uuid',
        'title',
        'weight',
        'enabled',
		'parent_id',
		'event_type',
		'event_hook',
		'data' => [ 'format' => 'JSON' ],
		'event_provider' => [ 'format' => 'JSON' ],
		'priority',
		'base_compare',
		'debug',
		'bundle_id',
		'enable_recursion',
		'recursion_limit',
		'imported',
 		'custom_internal',
		'sites',
    );

    /**
     * @var    string        Table primary key
     */
    protected static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    protected static $prefix = 'rule_';
	
	/**
	 * @var	string
	 */
	protected static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	protected static $sequence_col = 'weight';
	
	/**
	 * @var	string
	 */
	protected static $parent_col = 'parent_id';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Rule';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Rules';
	
	/**
	 * @var array
	 */
	protected $_sites;
	
	/**
	 * Get the sites this bundle applies to
	 *
	 * @return	array|NULL
	 */
	public function getSites()
	{
		if ( ! $this->sites ) {
			return NULL;
		}
		
		if ( ! isset( $this->_sites ) ) {
			$this->_sites = array();
			$all_sites = $this->getPlugin()->getSites();
			foreach( explode( ',', $this->sites ) as $site_id ) {
				if ( isset( $all_sites[ $site_id ] ) ) {
					$this->_sites[ $site_id ] = $all_sites[ $site_id ];
				}
			}
		}
		
		return $this->_sites;
	}
	
	/**
	 * Get the associated bundle
	 *
	 * @return	MWP\Rules\Bundle|NULL
	 */
	public function getBundle()
	{
		$rule = $this;
		while( $rule->parent() ) {
			$rule = $rule->parent();
		}
		
		if ( $rule->bundle_id ) {
			try {
				return Bundle::load( $rule->bundle_id );
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
	}
	
	/**
	 * @var Hook
	 */
	protected $hook;
	
	/**
	 * Get the containing model Bundle/Hook (if applicable)
	 *
	 * @return	Bundle|Hook|NULL
	 */
	public function getContainer()
	{
		if ( $bundle = $this->getBundle() ) {
			return $bundle;
		}
		
		if ( ! isset( $this->hook ) ) {
			$rule = $this;
			while( $rule->parent() ) { $rule = $rule->parent(); }
		
			if ( $rule->custom_internal ) {
				$hooks = Hook::loadWhere([ 'hook_type=%s AND hook_hook=%s', 'custom', $rule->event_hook ]);
			}
			
			$this->hook = isset( $hooks ) && ! empty( $hooks ) ? array_shift( $hooks ) : false;
		}
		
		if ( $this->hook ) {
			return $this->hook;
		}
		
		return NULL;
	}
	
	/**
	 * Get the associated app
	 *
	 * @return	MWP\Rules\App|NULL
	 */
	public function getApp()
	{
		if ( $bundle = $this->getBundle() ) {
			return $bundle->getApp();
		}
		
		return NULL;
	}
	
	/**
	 * Get the controller
	 *
	 * @param	string		$key			The controller key
	 * @return	ActiveRecordController
	 */
	public function _getController( $key='admin' )
	{
		return $this->getPlugin()->getRulesController( $this->getContainer(), $key );
	}
	
	/**
	 * Check if the rule is active
	 *
	 * @return	bool
	 */
	public function isActive()
	{
		if ( ! $this->enabled ) {
			return false;
		}
		
		if ( $this->sites and ! in_array( get_current_blog_id(), explode( ',', $this->sites ) ) ) {
			return false;
		}
		
		if ( $parent = $this->parent() ) {
			return $parent->isActive();
		}
		
		if ( $bundle = $this->getBundle() ) {
			return $bundle->isActive();
		}
		
		return true;
	}
	
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
				'title' => __( 'Configure Rule', 'mwp-rules' ),
				'params' => array(
					'do' => 'edit',
					'id' => $this->id,
				),
			),
			'add' => array(
				'icon' => 'glyphicon glyphicon-link',
				'title' => __( 'Create New Subrule', 'mwp-rules' ),
				'params' => array(
					'do' => 'new',
					'parent_id' => $this->id,
				)
			),
			'export' => array(
				'title' => __( 'Download ' . $this->_getSingularName(), 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-cloud-download',
				'params' => array(
					'do' => 'export',
					'id' => $this->id(),
				),
			),
			'delete' => array(
				'separator' => true,
				'title' => __( 'Delete Rule', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'class' => 'text-danger',
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
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$rule = $this;
		
		/* Display details for the app/bundle/parent */
		$form->addHtml( 'rule_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'rule' => $this, 
			'bundle' => $this->getBundle(), 
			'app' => $this->getApp(), 
		]));
		
		if ( $rule->title ) {
			$form->addHtml( 'rule_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<img style="background-color: #333; padding:3px; border-radius: 3px" src="' . $plugin->fileUrl('assets/img/gavel.png') . '">',
				'label' => 'Rule',
				'title' => $rule->title,
			]));
		}
		
		$form->addTab( 'rule_settings', array( 
			'title' => __( 'Settings', 'mwp-rules' ) 
		));
		
		if ( $this->id() and ! $this->parent() ) {
			
			$bundle_choices = [
				'Unassigned' => 0,
			];
			
			foreach( App::loadWhere('1') as $app ) {
				$app_bundles = [];
				foreach( $app->getBundles() as $bundle ) {
					$app_bundles[ $bundle->title ] = $bundle->id();
				}
				$bundle_choices[ $app->title ] = $app_bundles;
			}
			
			foreach( Bundle::loadWhere( 'bundle_app_id=0' ) as $bundle ) {
				$bundle_choices[ 'Independent Bundles' ][ $bundle->title ] = $bundle->id();
			}
			
			$form->addField( 'bundle_id', 'choice', array(
				'label' => __( 'Associated Bundle', 'mwp-rules' ),
				'choices' => $bundle_choices,
				'required' => true,
				'data' => $this->bundle_id,
			), 
			'rule_settings' );
		}
		
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
		
		/* Rule priority */
		if ( $this->id() and ! $rule->parent() ) {
			$form->addField( 'priority', 'integer', array( 
				'label' => __( 'Rule Priority', 'mwp-rules' ),
				'description' => __( 'The priority at which this base rule will be evaluated during an event.', 'mwp-rules' ),
				'data' => isset( $rule->priority ) ? (int) $rule->priority : 10,
				'required' => true,
			),
			'rule_settings' );
		}
		
		/* Step 1: Configure the event for new rules */
		if ( ! $rule->id() ) 
		{
			if ( ! $rule->parent_id and ! $rule->custom_internal ) {
				$event_choices = array();
				
				foreach( array( 'action', 'filter' ) as $type ) {
					foreach( $plugin->getEvents( $type ) as $event ) {
						$group = ( $event->group ?: ( isset( $event->provider['title'] ) ? __( $event->provider['title'] ) : __( 'Unclassified', 'mwp-rules' ) ) ) . ' ' . ( $type == 'action' ? 'Event' : ucwords( $type ) ) . 's';
						$event_choices[ $group ][ $event->title ] = $event->type . '/' . $event->hook;
					}
				}
				
				$form->addField( 'event', 'choice', array(
					'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
					'label' => __( 'Rule Triggered When:', 'mwp-rules' ),
					'attr' => array( 'placeholder' => 'Select an event', 'data-bind' => 'jquery: { selectize: {} }' ),
					'choices' => $event_choices,
					'data' => $rule->event_type . '/' . $rule->event_hook,
					'required' => false,
					'constraints' => [ function( $data, $context ) {
						if ( ! $data ) {
							$context->addViolation( __( 'You must select an event for the rule.', 'mwp-rules' ) );
						}
					}],
				),
				'rule_settings', 'title', 'before' );
			}			
		}
		else
		{
			$form->addField( 'enabled', 'checkbox', array( 
				'row_prefix' => '<hr>',
				'label' => __( 'Rule Enabled', 'mwp-rules' ),
				'value' => 1,
				'description' => __( 'Enable the operation of this rule' ),
				'data' => isset( $rule->enabled ) ? (bool) $rule->enabled : true,
				'required' => false,
			),
			'rule_settings' );
			
			$form->addField( 'debug', 'checkbox', array( 
				'label' => __( 'Rule Debug', 'mwp-rules' ),
				'value' => 1,
				'description' => __( 'Enable debug logs for this rule' ),
				'data' => (bool) $rule->debug,
				'required' => false,
			),
			'rule_settings' );
			
			$form->addField( 'enable_recursion', 'checkbox', array(
				'label' => __( 'Enable Recursion', 'mwp-rules' ),
				'value' => 1,
				'description' => __( 'Allow this rule to be recursively triggered by its own conditions and actions. By default, recursive rule processing is protected to prevent infinite loops.', 'mwp-rules' ),
				'data' => (bool) $rule->enable_recursion,
				'toggles' => array(
					1 => array( 'show' => array( '#rule_recursion_limit' ) ),
				),
			),
			'rule_settings' );
			
			$form->addField( 'recursion_limit', 'integer', array(
				'row_attr' => array( 'id' => 'rule_recursion_limit' ),
				'attr' => array( 'min' => 1 ),
				'label' => __( 'Recursion Limit', 'mwp-rules' ),
				'description' => __( 'Enter the number of times this rule should will be able to recurse on itself.', 'mwp-rules' ),
				'data' => (int) $rule->recursion_limit ?: 1,
				'required' => true,
			),
			'rule_settings' );
		}
		
		if ( is_multisite() ) {
			$form->addField( 'sites_select', 'choice', array(
				'row_prefix' => '<h2>Network Configuration</h2><hr>',
				'label' => __( 'Site Selection', 'mwp-rules' ),
				'description' => __( 'Choose which sites this rule will apply to. (Also requires the Automation Rules plugin to be enabled on the site.)', 'mwp-rules' ),
				'choices' => array(
					__( 'All Sites', 'mwp-rules' ) => 'all',
					__( 'Specific Sites', 'mwp-rules' ) => 'specific',
				),
				'data' => $this->sites ? 'specific' : 'all',
				'multiple' => false,
				'expanded' => true,
				'required' => true,
				'toggles' => array(
					'specific' => array( 'show' => array( '#sites' ) ),
				),
			),
			'rule_settings' );
			
			$site_options = array();
			foreach( get_sites() as $site ) {
				$site_options[ $site->blogname ] = $site->id;
			}
			
			$form->addField( 'sites', 'choice', array( 
				'row_attr' => array( 'id' => 'sites' ),
				'label' => __( 'Choose Sites', 'mwp-rules' ),
				'choices' => $site_options,
				'data' => explode( ',', $this->sites ),
				'multiple' => true,
				'expanded' => true,
			),
			'rule_settings' );
		}
		
		/* We must have an existing rule to configure the rest... */
		if ( ! $rule->id() ) {
			$form->addField( 'submit', 'submit', array( 
				'label' => __( 'Continue', 'mwp-rules' ), 
				'attr' => array( 'class' => 'btn btn-primary' ),
				'row_attr' => array( 'class' => 'text-center' ),
			));
			
			$form->onComplete( function() use ( $rule, $plugin ) {
				$controller = $plugin->getRulesController( $rule->getContainer() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_conditions' ) ) );
				exit;
			});
			
			return $form;
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
			'choices' => array( 'ALL' => 'and', 'ANY' => 'or' ),
			'required' => true,
			'data' => $rule->base_compare ?: 'and',
			'expanded' => false,
			'description' => "<p>Choose how you want the root conditions for this rule to be evaluated.</p>
				<ol class='text-info'>
					<li>If you choose ALL, all root conditions must be valid for actions to be executed.</li>
					<li>If you choose ANY, actions will be executed if any root condition is valid.</li>
				</ol>",
		), 
		'rule_conditions');
		
		$conditionsController = $plugin->getConditionsController( $rule );
		$conditionsTable = $conditionsController->createDisplayTable();
		$conditionsTable->viewModel = 'mwp-rules';
		$conditionsTable->dataBinds = array( 'nestedSortable' => "{ handle: 'div', toleranceElement: '> div', items: 'li', relocate: conditionRelocated }" );
		
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
		$actionsTable->viewModel = 'mwp-rules';
		$actionsTable->dataBinds = array( 'nestedSortable' => "{ handle: 'div', toleranceElement: '> div', items: 'li', relocate: actionRelocated }" );
		
		/* Linked actions table (normal actions)*/
		$actionsTable->prepare_items( array( 'action_rule_id=%d AND action_else=0', $rule->id ) );
		$form->addHtml( 'actions_controller_buttons', '<div style="margin-bottom: 20px">' . $actionsController->getActionsHtml() . '</div>', 'rule_actions' );
		$form->addHeading( 'standard_actions_heading', __( 'Standard Actions', 'mwp-rules' ), 'rule_actions' );
		$form->addHtml( 'actions_table', $plugin->getTemplateContent( 'rules/actions/table_wrapper', array( 
			'show_buttons' => false,
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
		
		$form->addTab( 'rule_subrules', array( 
			'title' => __( 'Sub-rules', 'mwp-rules' ),
		));
		
		$rulesController = $plugin->getRulesController( $this->getContainer() );
		$rulesTable = $rulesController->createDisplayTable();
		$rulesTable->bulkActions = array();
		unset( $rulesTable->columns['rule_event_hook'] );
		$rulesTable->prepare_items( array( 'rule_parent_id=%d', $rule->id ) );
		
		$form->addHtml( 'subrules_table', $plugin->getTemplateContent( 'rules/subrules/table_wrapper', array(
			'actions' => array_replace_recursive( $rulesController->getActions(), array( 
				'new' => array( 
					'title' => __( 'Add New Subrule', 'mwp-rules' ),
					'params' => array( 
						'parent_id' => $rule->id() 
					),
				), 
			)),
			'rule' => $rule, 
			'table' => $rulesTable, 
			'controller' => $rulesController 
		)),
		'rule_subrules' );
		
		/**
		 * Debug tab
		 */
		if ( $rule->debug ) {
			$form->addTab( 'rule_debug_console', array(
				'title' => __( 'Debug Console', 'mwp-rules' ),
			));
			
			$logsController = $plugin->getLogsController();
			$logsTable = $logsController->createDisplayTable();
			unset( $logsTable->columns['id'], $logsTable->columns['event_hook'], $logsTable->columns['rule_id'] );
			$logsTable->prepare_items( array( 'op_id=0 AND rule_id=%d', $rule->id ) );
			
			$form->addHtml( 'rule_debug_logs', $logsTable->getDisplay(), 'rule_debug_console' );			
		}
		
		$submit_text = $this->id() ? 'Save Rule' : 'Create Rule';
		$form->addField( 'submit', 'submit', array( 
			'label' => __( $submit_text, 'mwp-rules' ), 
			'attr' => array( 'class' => 'btn btn-primary' ),
			'row_attr' => array( 'class' => 'text-center' ),
		), '');
		
		/* If the rule is a sub-rule, redirect to the parent rules tab after saving */
		if ( $parent = $rule->parent() ) {
			$form->onComplete( function() use ( $parent, $plugin ) {
				$controller = $plugin->getRulesController( $parent->getContainer() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $parent->id(), '_tab' => 'rule_subrules' ) ) );
				exit;
			});
		}
		else {
			$form->onComplete( function() use ( $rule, $plugin ) {
				if ( $bundle = $rule->getBundle() ) {
					$controller = $plugin->getBundlesController( $bundle->getApp() );
					wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $bundle->id(), '_tab' => 'bundle_rules' ) ) );
					exit;
				}
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
	protected function processEditForm( $values )
	{
		$_values = $values['rule_settings'];
		
		if ( isset( $_values['sites'] ) and is_array( $_values['sites'] ) ) {
			$_values['sites'] = implode( ',', $_values['sites'] );
		}
		
		if ( isset( $_values['sites_select'] ) and $_values['sites_select'] == 'all' ) {
			$_values['sites'] = '';
		}
		
		if ( isset( $_values['event'] ) ) {
			$event_parts = explode( '/', $_values['event'] );
			$type = array_shift( $event_parts );
			$hook = implode( '/', $event_parts );
			
			$_values['event_type'] = $type;
			$_values['event_hook'] = $hook;
		}
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Set the return value used for filters
	 *
	 * @param	mixed		$value			The updated value
	 * @return	void
	 */
	public function setReturnValue( $value )
	{
		$this->filtered_values[ $this->event()->thread ] = $value;
	}
	
	/**
	 * Get the return value used for filters
	 *
	 * @return	mixed
	 */
	public function getReturnValue()
	{
		return isset( $this->filtered_values[ $this->event()->thread ] ) ? $this->filtered_values[ $this->event()->thread ] : null;
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
	 * Enable rule
	 *
	 * @return	void
	 */
	public function enable()
	{
		$this->enabled = 1;
		$this->save();
	}
	
	/**
	 * Disable rule
	 *
	 * @return	void
	 */
	public function disable()
	{
		$this->enabled = 0;
		$this->save();
	}
	
	/**
	 * Enable rule debug
	 *
	 * @return	void
	 */
	public function enableDebug()
	{
		$this->debug = 1;
		$this->save();
	}
	
	/**
	 * Disable rule debug
	 *
	 * @return	void
	 */
	public function disableDebug()
	{
		$this->debug = 0;
		$this->save();
	}
	
	/**
	 * Enable debug mode recursively
	 *
	 * @return	void
	 */
	public function enableDebugRecursive()
	{
		$this->debug = 1;
		$this->save();
		foreach( $this->children() as $child ) {
			$child->enableDebugRecursive();
		}
	}
	
	/**
	 * Disable debug mode recursively
	 *
	 * @return	void
	 */
	public function disableDebugRecursive()
	{
		$this->debug = 0;
		$this->save();
		foreach( $this->children() as $child ) {
			$child->disableDebugRecursive();
		}
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
	
	public function getChildren()
	{
		return $this->children();
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
	 *
	 * @return	array
	 */
	public function conditions()
	{
		if ( isset( $this->conditionCache ) ) {
			return $this->conditionCache;
		}
		
		$this->conditionCache = Condition::loadWhere( array( 'condition_parent_id=0 AND condition_rule_id=%d', $this->id ), 'condition_weight ASC' );
		
		return $this->conditionCache;
	}
	
	public function getConditions()
	{
		return $this->conditions();
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
	
	public function getActions( $mode=NULL )
	{
		return $this->actions( $mode );
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
		return $this->getPlugin()->getRulesController( $this->getContainer() )->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit', $params ) ) );
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		
		$export['children'] = array_map( function( $subrule ) { return $subrule->getExportData(); }, $this->getChildren() );
		$export['conditions'] = array_map( function( $condition ) { return $condition->getExportData(); }, $this->getConditions() );
		$export['actions'] = array_map( function( $action ) { return $action->getExportData(); }, $this->getActions() );
		
		/* Add current provider if available */
		if ( $event = $this->event() ) {
			if ( $event->provider ) {
				$export['data']['rule_event_provider'] = json_encode( $event->provider );
			}
		}
		
		unset( $export['data']['rule_parent_id'] );
		unset( $export['data']['rule_bundle_id'] );
		unset( $export['data']['rule_sites'] );
		
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	int				$parent_id			The parent rule id
	 * @param	int				$bundle_id			The parent bundle id
	 * @return	array
	 */
	public static function import( $data, $parent_id=0, $bundle_id=0 )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$rule = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$rule->_setDirectly( $col, $value );
			}
			
			$rule->parent_id = $parent_id;
			$rule->bundle_id = $bundle_id;
			$rule->imported = time();
			$result = $rule->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['rules'][] = $data;
				
				$imported_condition_uuids = [];
				$imported_action_uuids = [];
				$imported_rule_uuids = [];
				
				/* Import conditions, keeping track of imported uuids */
				if ( isset( $data['conditions'] ) and ! empty( $data['conditions'] ) ) {
					foreach( $data['conditions'] as $condition ) {
						$_results = Condition::import( $condition, $rule->id() );
						if ( isset( $_results['imports']['conditions'] ) ) {
							$imported_condition_uuids = array_merge( $imported_condition_uuids, array_map( function( $c ) { return $c['data']['condition_uuid']; }, $_results['imports']['conditions'] ) );
						}
						$results = array_merge_recursive( $results, $_results );
					}
				}
				
				/* Import actions, keeping track of imported uuids */
				if ( isset( $data['actions'] ) and ! empty( $data['actions'] ) ) {
					foreach( $data['actions'] as $action ) {
						$imported_action_uuids[] = $action['data']['action_uuid'];
						$results = array_merge_recursive( $results, Action::import( $action, $rule->id() ) );
					}
				}
				
				/* Import subrules, keeping track of imported uuids */
				if ( isset( $data['children'] ) and ! empty( $data['children'] ) ) {
					foreach( $data['children'] as $subrule ) {
						$imported_rule_uuids[] = $subrule['data']['rule_uuid'];
						$results = array_merge_recursive( $results, Rule::import( $subrule, $rule->id(), $bundle_id ) );
					}
				}
				
				/* Cull previously imported conditions which are no longer part of this imported rule */
				foreach( Condition::loadWhere( array( 'condition_rule_id=%d AND condition_imported > 0 AND condition_uuid NOT IN (\'' . implode("','", $imported_condition_uuids) . '\')', $rule->id() ) ) as $condition ) {
					$condition->delete();
				}
				
				/* Cull previously imported actions which are no longer part of this imported rule */
				foreach( Action::loadWhere( array( 'action_rule_id=%d AND action_imported > 0 AND action_uuid NOT IN (\'' . implode("','", $imported_action_uuids) . '\')', $rule->id() ) ) as $action ) {
					$action->delete();
				}
				
				/* Cull previously imported subrules which are no longer part of this imported rule */
				foreach( Rule::loadWhere( array( 'rule_parent_id=%d AND rule_imported > 0 AND rule_uuid NOT IN (\'' . implode("','", $imported_rule_uuids) . '\')', $rule->id() ) ) as $subrule ) {
					$subrule->delete();
				}
				
			} else {
				$results['errors']['rules'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Save
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		$changed = $this->_getChanged();
		
		/* Update all subrules when this rule is moved to a new bundle */
		if ( array_key_exists( 'rule_bundle_id', $changed ) ) {
			foreach( $this->children() as $subrule ) {
				$subrule->bundle_id = $this->bundle_id;
				$subrule->save();
			}
		}
		
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		return parent::save();
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		foreach( $this->getChildren() as $subrule ) {
			$subrule->delete();
		}
		
		foreach( $this->getActions() as $action ) {
			$action->delete();
		}
		
		foreach( $this->getConditions() as $condition ) {
			$condition->delete();
		}
		
		return parent::delete();
	}

}
