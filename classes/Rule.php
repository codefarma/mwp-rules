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

use MWP\Framework\Framework;
use MWP\Framework\Pattern\ActiveRecord;
use MWP\Rules\Condition;
use MWP\Rules\Action;
use MWP\Rules\ECA\Token;

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
		'uuid'             => [ 'type' => 'varchar', 'length' => 25 ],
		'title'            => [ 'type' => 'varchar', 'length' => 255, 'allow_null' => false ],
		'weight'           => [ 'type' => 'int', 'length' => 11, 'default' => '0', 'allow_null' => false ],
		'enabled'          => [ 'type' => 'tinyint', 'length' => 1, 'default' => '1', 'allow_null' => false ],
		'parent_id'        => [ 'type' => 'bigint', 'length' => 20, 'default' => 0, 'allow_null' => false ],
		'event_type'       => [ 'type' => 'varchar', 'length' => 15, 'allow_null' => false ],
		'event_hook'       => [ 'type' => 'varchar', 'length' => 255, 'allow_null' => false ],
		'documentation'    => [ 'type' => 'text', 'default' => '' ],
		'data'             => [ 'type' => 'text', 'format' => 'JSON' ],
		'event_provider'   => [ 'type' => 'text', 'format' => 'JSON' ],
		'priority'         => [ 'type' => 'int', 'length' => 11, 'default' => 10, 'allow_null' => false ],
		'base_compare'     => [ 'type' => 'enum', 'values' => ['and','or'], 'default' => 'and', 'allow_null' => false ],
		'debug'            => [ 'type' => 'tinyint', 'length' => 1, 'default' => 0, 'allow_null' => false ],
		'bundle_id'        => [ 'type' => 'bigint', 'length' => 20 ],
		'enable_recursion' => [ 'type' => 'tinyint', 'length' => 1, 'default' => 0, 'allow_null' => false ],
		'recursion_limit'  => [ 'type' => 'int', 'length' => 11, 'default' => 1 ],
		'imported'         => [ 'type' => 'int', 'length' => 11 ],
 		'custom_internal'  => [ 'type' => 'tinyint', 'length' => 1, 'default' => 0, 'allow_null' => false ],
		'sites'            => [ 'type' => 'varchar', 'length' => 2048, 'default' => '', 'allow_null' => false ],
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
		$request = Framework::instance()->getRequest();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$rule = $this;
		$rule_data = $this->data;
		
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

		$form->addField( 'sub_mode', 'choice', array(
			'label' => 'Sub-Rule Context',
			'choices' => array(
				'Normal - Same context as parent rule.' => 'normal',
				'Looped - Create a loop context for sub-rules.' => 'loop',
			),
			'required' => true,
			'expanded' => true,
			'description' => __('If you create a loop context, all sub-rules will be run for each element of an array you choose.', 'mwp-rules'),
			'data' => isset( $rule_data['sub_mode'] ) ? $rule_data['sub_mode'] : 'normal',
			'toggles' => array(
				'loop' => array(
					'show' => [ '#sub_mode_context_settings' ],
				)
			),
		), 
		'rule_subrules' );

		/**
		 * Add sub-rule context configuration
		 */
		$form->addHtml( 'sub_mode_context_start', '<div id="sub_mode_context_settings">' );
		$form->addHeading( 'sub_mode_context_heading', __( 'Loop Context', 'mwp-rules' ) . '  <small><code>array</code></small>', 'rule_subrules' );

		/* Look for event data that can be used to supply the value for this argument */
		$usable_event_data = array();
		$usable_event_data_objects = array();
		$usable_event_data_optgroups = array();
		$arg = array( 'label' => 'Loop Data Source', 'argtypes' => [ 'array' ] );

		if ( $event = $this->event() ) {
			$usable_event_data = $event->getArgumentTokens( $arg, NULL, 2, TRUE, $this );
			foreach( $usable_event_data as $token => &$title ) {
				$parts = explode(':', $token);
				
				if ( ! isset( $usable_event_data_objects[ $parts[0] ] ) ) {
					$usable_event_data_optgroups[ $parts[0] ] = array(
						'label' => ucwords( __( $parts[0] . ' Arguments', 'mwp-rules' ) ),
						'value' => $parts[0],
					);
				}
				
				$usable_event_data_objects[] = array( 'value' => $token, 'text' => $token, 'optgroup' => $parts[0] );
				$title = $token; // . ' - ' . $title;
			}
			$usable_event_data = array_flip( $usable_event_data );
			$usable_event_data_optgroups = array_values( $usable_event_data_optgroups );
			
			$current_event_arg = isset( $rule_data[ 'sub_mode_context_eventArg' ] ) ? $rule_data[ 'sub_mode_context_eventArg' ] : NULL;
			if ( isset( $current_event_arg ) and ! in_array( $current_event_arg, array_values( $usable_event_data ) ) ) {
				$usable_event_data_objects[] = array( 'value' => $current_event_arg, 'text' => $current_event_arg );
			}
		}

		$form->addField( 'sub_mode_context_varname', 'text', array(
			'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
			'label' => __( 'Machine Name', 'mwp-rules' ),
			'description' => __( 'Enter an identifier to be used for the loop value. It may only contain alphanumeric characters or underscores. It must also start with a letter.', 'mwp-rules' ),
			'attr' => array( 
				'placeholder' => 'var_name', 
				'data-fixed' => "false",
				'data-bind' => "init: function() { 
					var varname = jQuery(this);
					varname.on('keypress', function (event) {
						switch (event.keyCode) {
							case 8:  // Backspace
								break;
							case 9:  // Tab
							case 13: // Enter
							case 37: // Left
							case 38: // Up
							case 39: // Right
							case 40: // Down
								return;
							default:
								var regex = new RegExp(\"[a-zA-Z0-9_]\");
								var key = event.key;
								if (!regex.test(key)) {
									event.preventDefault();
									return false;
								}
								break;
						}
					});
				}
			"),
			'data' => isset( $rule_data[ 'sub_mode_context_varname' ] ) ? $rule_data[ 'sub_mode_context_varname' ] : '',
			'constraints' => array( function( $data, $context ) {
				$form_values = $context->getRoot()->getData();
				if ( $form_values['rule_subrules']['sub_mode'] == 'loop' ) {
					if ( ! preg_match( "/^([A-Za-z])+([A-Za-z0-9_]+)?$/", $data ) ) {
						$context->addViolation( __('The machine name must be only alphanumerics or underscores, and must start with a letter.','mwp-rules') ); 
					}
				}
			}),
		),
		'rule_subrules' );

		$arg_sources[ 'Event / Global Data' ] = 'event';
		$arg_sources[ 'Custom PHP Code' ] = 'phpcode';
		
		$form->addField( 'sub_mode_context_source', 'choice', array(
			'label' => __( 'Source', 'mwp-rules' ),
			'choices' => $arg_sources,
			'data' => isset( $rule_data[ 'sub_mode_context_source' ] ) ? $rule_data[ 'sub_mode_context_source' ] : 'event',
			'required' => ! empty( $arg_sources ),
			'toggles' => array(
				'event' => array( 'show' => '#' . 'sub_mode_context_eventArg' ),
				'phpcode' => array( 'show' => '#' . 'sub_mode_context_phpcode' ),
			),
		));
		
		/**
		 * EVENT ARGUMENTS 
		 *
		 */
		$form->addField( 'sub_mode_context_eventArg', 'text', array(
			'field_prefix' => $this->getDataSelector( $arg ),
			'row_attr' => array( 'id' => 'sub_mode_context_eventArg', 'data-view-model' => 'mwp-rules' ),
			'attr' => array( 'data-role' => 'token-select', 'data-opkey' => 'rule', 'data-opid' => $this->id(), 'data-bind' => 'jquery: { 
				selectize: {
					plugins: [\'restore_on_backspace\'],
					optgroups: ' . json_encode( $usable_event_data_optgroups ) . ',
					options: ' . json_encode( $usable_event_data_objects ) . ',
					persist: true,
					maxItems: 1,
					highlight: false,
					hideSelected: false,
					create: true
				}
			}'),
			'label' => __( 'Data To Use', 'mwp-rules' ),
			'required' => ! empty( $usable_event_data ),
			'data' => ( isset( $rule_data[ 'sub_mode_context_eventArg' ] ) and $rule_data[ 'sub_mode_context_eventArg' ] ) ? $rule_data[ 'sub_mode_context_eventArg' ] : reset( $usable_event_data ),
		));

		/**
		 * PHP CODE
		 *
		 * Requires return argtype(s) to be specified
		 */
		if ( isset( $arg[ 'argtypes' ] ) ) {
			/**
			 * Compile argtype info
			 */
			$_arg_list 	= array();
			
			if ( is_array( $arg[ 'argtypes' ] ) ) {
				foreach( $arg[ 'argtypes' ] as $_type => $_type_def ) {
					if ( is_array( $_type_def ) ) {
						if ( isset ( $_type_def[ 'description' ] ) ) {
							$_arg_list[] = "<code>{$_type}</code>" . ( isset( $_type_def[ 'classes' ] ) ? ' (' . implode( ',', (array) $_type_def[ 'classes' ] ) . ')' : '' ) . ": {$_type_def[ 'description' ]}";
						}
						else {
							$_arg_list[] = "<code>{$_type}</code>" . ( isset( $_type_def[ 'classes' ] ) ? ' (' . implode( ',', (array) $_type_def[ 'classes' ] ) . ')' : '' );
						}
					}
					else {
						$_arg_list[] = "<code>{$_type_def}</code>";
					}
				}
			}
			
			$form->addField( 'sub_mode_context_phpcode', 'textarea', array(
				'row_attr' => array(  'id' => 'sub_mode_context_phpcode', 'data-view-model' => 'mwp-rules' ),
				'label' => __( 'Custom PHP Code', 'mwp-rules' ),
				'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
				'data' => isset( $rule_data[ 'sub_mode_context_phpcode' ] ) ? $rule_data[ 'sub_mode_context_phpcode' ] : "// <?php \n\nreturn;",
				'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 'return_args' => $_arg_list, 'event' => $this->event(), 'rule' => $this ) ),
				'required' => false,
			));
		}
		
		/**
		 * End sub-rule context config
		 */
		$form->addHtml( 'sub_mode_context_end', '</div>' );

		$rulesController = $plugin->getRulesController( $this->getContainer() );
		$rulesTable = $rulesController->createDisplayTable();
		$rulesTable->bulkActions = array();
		unset( $rulesTable->columns['rule_event_hook'] );
		
		/* Read table inputs */
		if ( $request->get('_tab') == 'rule_subrules' ) {
			$rulesTable->read_inputs();
		}
		
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
			
			/* Read table inputs */
			if ( $request->get('_tab') == 'rule_debug_console' ) {
				$logsTable->read_inputs();
			}
			
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

				if ( $rule->custom_internal ) {
					$hook = $rule->getContainer();
					$controller = $plugin->getHooksController('actions');
					wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $hook->id(), '_tab' => 'hook_rules' ) ) );
					exit;
				}
			});
		}
		
		return $form;
	}

	/**
	 * Get the data selector button for this rule
	 *
	 * @param	string|null		$arg				The argument this will be used to select an input to it
	 * @param	array			$config				Custom config options for the dialog
	 * @param	array			$params				Custom params for the browser
	 * @return	string
	 */
	public function getDataSelector( $arg=null, $config=[], $params=[] )
	{
		if ( $event = $this->getEvent() ) {
			$params['event_type'] = $event->type;
			$params['event_hook'] = $event->hook;
		}
		
		if ( $bundle = $this->getBundle() ) {
			$params['bundle_id'] = $bundle->id();
		}
		
		$params = array_merge( array( 'target' => array( 'argtypes' => [ 'string', 'float', 'int', 'bool' ] ) ), $params );
		$default_title = __( 'Insert Data Replacement Token', 'mwp-rules' );
		$default_done_label = __( 'Insert', 'mwp-rules' );
		$wrap_tokens = true;
		$default_callback = "function( node, tokens, tree, dialog ) { 
			jQuery(this)
				.closest('.form-group.row')
				.find('input[type=text],textarea').eq(0)
				.insertAtCaret( '{{' + tokens.join(':') + '}}' )
		}";
		
		if ( $arg ) {
			$default_title = __( 'Choose the data to use for: ', 'mwp-rules' ) . ( isset( $arg['label'] ) ? $arg['label'] : '' );
			$default_done_label = __( 'Select', 'mwp-rules' );
			$wrap_tokens = false;
			$default_callback = "function( node, tokens, tree, dialog ) {
				var input = jQuery(this)
					.closest('.form-group.row')
					.find('input.selectized').eq(0);
				
				if (input.length) {
					var selectize = input[0].selectize;
					var tokenized_key = tokens.join(':');
					selectize.addOption( { value: tokenized_key } );
					selectize.setValue( tokenized_key );
				}
			}";
			$params['target']['argtypes'] = $arg['argtypes'];
		}
		
		$config = array_merge( array( 
			'title' => $default_title, 
			'callback' => $default_callback,
			'done_label' => $default_done_label,
			'wrap_tokens' => $wrap_tokens,
			'wrap_html' => true,
		), $config );
		
		$html = Token::getBrowserLauncherHTML( $config, $params );
		
		if ( $config['wrap_html'] ) {
			$html = Plugin::instance()->getTemplateContent( 'snippets/token-browser-launcher-wrapper', [ 'html' => $html ] );
		}
		
		return $html;
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

		// Update ad-hoc rule data
		$rule_data = $this->data ?: array();
		if ( isset( $values['rule_subrules'] ) ) {
			$rule_data['sub_mode'] = $values['rule_subrules']['sub_mode'];
			$rule_data['sub_mode_context_varname'] = $values['rule_subrules']['sub_mode_context_varname'];
			$rule_data['sub_mode_context_source'] = $values['rule_subrules']['sub_mode_context_source'];
			$rule_data['sub_mode_context_eventArg'] = $values['rule_subrules']['sub_mode_context_eventArg'];
			$rule_data['sub_mode_context_phpcode'] = $values['rule_subrules']['sub_mode_context_phpcode'];

			if ( $rule_data['sub_mode_context_source'] == 'event' ) {
				$resources = [
					'event' => $this->getEvent(),
					'bundle' => $this->getBundle(),
					'rule' => $this,
				];

				$tokenPath = $rule_data['sub_mode_context_eventArg'];
				$tokenData = Token::parseDataFromResources($tokenPath, $resources);
				$reflectionData = Token::getReflection($tokenData['argument'], $tokenData['parsed']['token_path']);

				$rule_data['sub_mode_context_argument'] = $reflectionData['final_argument'];
			}
		}
		$this->data = $rule_data;
		
		if ( isset( $_values['sites'] ) and is_array( $_values['sites'] ) ) {
			$_values['sites'] = implode( ',', $_values['sites'] );
		}
		
		if ( isset( $_values['sites_select'] ) and $_values['sites_select'] == 'all' ) {
			$_values['sites'] = '';
		}
		
		if ( isset( $values['rule_conditions']['base_compare'] ) ) {
			$_values['base_compare'] = $values['rule_conditions']['base_compare'];
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
								// Check if rule has a loop context, get the loop array, and iterate
								if ( $this->isLoopy() ) {
									$loop_values = $this->getLoopValues( $args );
									if ( ! empty( $loop_values ) ) {
										foreach( $loop_values as $value ) {
											$loop_args = array_merge( $args, array($value) );
											$result = call_user_func_array( array( $_rule, 'invoke' ), $loop_args );
											
											if ( $this->event_type == 'filter' ) {
												$args[0] = $result;
												$this->filtered_values[ $this->event()->thread ] = $args[0];
											}
										}
									}
									else {
										if ( $this->debug ) {
											$plugin->rulesLog( $this->event(), $_rule, NULL, '--', 'Rule not evaluated (empty loop context)' );
										}
									}
								}
								// Execute the rule in normal mode
								else {
									$result = call_user_func_array( array( $_rule, 'invoke' ), $args );
									
									if ( $this->event_type == 'filter' ) {
										$args[0] = $result;
										$this->filtered_values[ $this->event()->thread ] = $args[0];
									}
								}
							}
							else
							{
								if ( $this->debug ) {
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
								if ( $this->debug ) {
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
				catch( \Throwable $t )
				{
					$this->locked = FALSE;
					$this->recursionCount--;
					throw $t;
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
				if ( $this->debug ) {
					$plugin->rulesLog( $this->event(), $this, NULL, '--', 'Rule recursion protection (not evaluated)' );
				}
			}
		}
		else
		{
			if ( $this->debug ) {
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
	 * Check whether sub-rules should have a loop context
	 *
	 * @return	bool
	 */
	public function isLoopy() {
		$data = $this->data;
		return isset( $data['sub_mode'] ) && $data['sub_mode'] == 'loop';
	}

	/**
	 * Get the loop values to iterate for sub-rules
	 *
	 * @param	array 		$args			The args provided to the rule
	 * @return	array
	 */
	public function getLoopValues( $args ) {
		
		if ( $this->isLoopy() ) {
			$i = 0;
			$event_arguments = $this->getEvent()->getArguments( $this );
			$arg_map = [];
			
			/* Name and index all the event arguments */
			if ( ! empty( $event_arguments ) ) {
				foreach ( $event_arguments as $event_arg_name => $event_arg ) {
					$arg_map[ $event_arg_name ] = $args[ $i++ ];
				}
			}

			$self = $this;
			$token_value_getter = function( $tokenized_key ) use ( $arg_map, $self ) {
				$token = Token::createFromResources( $tokenized_key, [
					'event' => $self->getEvent(),
					'bundle' => $self->getBundle(),
					'event_args' => $arg_map,
					'rule' => $self,
				]);

				try {
					return $token->getTokenValue();
				} catch( \ErrorException $e ) { }
				
				return NULL;
			};

			$data = $this->data;
			switch( $data['sub_mode_context_source'] ) {
				case 'event':
					return $token_value_getter( $data['sub_mode_context_eventArg'] );

				case 'phpcode':
					$phpcode = $data['sub_mode_context_phpcode'];
					$evaluate = rules_evaluation_closure( array_merge( array( 'token_value' => $token_value_getter ), $arg_map ) );
					return $evaluate( $phpcode );
			}
		}

		return array();
	}


	/**
	 * Get the var_name to use for sub-rule loops
	 *
	 * @return string|NULL
	 */
	public function getLoopContext() {
		if ( $this->isLoopy() ) {
			$data = $this->data;

			$argument = array(
				'argtype' => 'mixed',
			);

			if ( $data['sub_mode_context_argument'] ) {
				$argument = $data['sub_mode_context_argument'];
				$argument['argtype'] = $argument['subtype'] ?? 'mixed';
				unset( $argument['subtype'] );
			}

			return array(
				'var_name' => $data['sub_mode_context_varname'],
				'argument' => $argument,
			);
		}

		return NULL;
	}

	/**
	 * Get the upward chain of loop contexts for this rule
	 *
	 * @return array
	 */
	public function getUpwardLoopContext() {
		$contextChain = [];
		$parent = $this;
		while( $parent = $parent->parent() ) {
			if ( $context = $parent->getLoopContext() ) {
				array_unshift( $contextChain, $context );
			}
		}

		return $contextChain;
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
	 * Get the attached event
	 *
	 * @return	MWP\Rules\ECA\Event|NULL
	 */
	public function event()
	{
		return \MWP\Rules\Plugin::instance()->getEvent( $this->event_type, $this->event_hook );
	}
	
	/**
	 * Get the attached event
	 *
	 * @return	MWP\Rules\ECA\Event|NULL
	 */
	public function getEvent()
	{
		return $this->event();
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
