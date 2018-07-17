<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.9.2
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;
use MWP\Framework\Framework;

/**
 * CustomAction Class
 */
class _Hook extends ExportableRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    protected static $table = "rules_hooks";

    /**
     * @var    array        Table columns
     */
    protected static $columns = array(
        'id',
		'uuid',
		'title',
		'weight',
		'description',
		'enable_api',
		'api_methods',
		'type',
		'hook',
		'category',
		'imported',
    );

    /**
     * @var    string        Table primary key
     */
    protected static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    protected static $prefix = 'hook_';

    /**
     * @var bool        Separate table per site?
     */
    protected static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    protected static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Event';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Events';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';

	/**
	 * @var	string
	 */
	public static $lang_create = 'Add';

	/**
	 * @var	string
	 */
	public static $lang_edit = 'Edit';
	
	/**
	 * @var	string
	 */
	public static $lang_delete = 'Delete';
	
	/**
	 * Get the 'edit record' page title
	 * 
	 * @return	string
	 */
	public function _getEditTitle( $type=NULL )
	{
		switch( $type ) {
			case 'schedule':
				return __( 'Schedule ' . $this->_getSingularName() );
		}
		
		return __( static::$lang_edit . ' ' . $this->_getSingularName() );
	}
	
	/**
	 * Get the 'view record' page title
	 * 
	 * @return	string
	 */
	public function _getViewTitle()
	{
		return __( static::$lang_view . ' ' . $this->_getSingularName() );
	}
	
	/**
	 * Get the 'delete record' page title
	 * 
	 * @return	string
	 */
	public function _getDeleteTitle()
	{
		return __( static::$lang_delete . ' ' . $this->_getSingularName() );
	}
	
	/**
	 * Get the singular name
	 * 
	 * @return	string
	 */
	public function _getSingularName()
	{
		return __( $this->type == 'custom' ? 'Action' : static::$lang_singular );
	}
	
	/**
	 * Get the plural name
	 * 
	 * @return	string
	 */
	public function _getPluralName()
	{
		return __( $this->type == 'custom' ? 'Actions' : static::$lang_plural );
	}
	
	/**
	 * @var	array
	 */
	protected $_arguments;
	
	/**
	 * Get the hook arguments
	 *
	 * @return	array
	 */
	public function getArguments()
	{
		if ( isset( $this->_arguments ) ) {
			return $this->_arguments;
		}
		
		$this->_arguments = Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', Argument::getParentType( $this ), $this->id() ), 'argument_weight ASC' );
		
		return $this->_arguments;
	}
	
	/**
	 * Get a specific argument
	 *
	 * @param	string			$varname			The argument varname
	 * @return	Argument|NULL
	 */
	public function getArgument( $varname ) 
	{
		foreach( $this->getArguments() as $argument ) {
			if ( $argument->varname === $varname ) {
				return $argument;
			}
		}
		
		return NULL;
	}
	
	/**
	 * Get the controller
	 *
	 * @return	ActiveRecordController
	 */
	public function _getController()
	{
		return $this->getPlugin()->getHooksController( $this->getControllerKey() );
	}
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		$data = $this->data;
		$actions = parent::getControllerActions();
		
		unset( $actions['view'] );
		
		$hook_actions = array(
			'edit' => '',
			'schedule' => array(
				'title' => __( 'Schedule Execution', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-time',
				'params' => array(
					'do' => 'edit',
					'edit' => 'schedule',
					'id' => $this->id(),
				),
				'separator' => 'bottom',
			),
			'manage_arguments' => array(
				'title' => __( 'Manage Arguments', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-th-list',
				'params' => array(
					'do' => 'edit',
					'_tab' => 'arguments',
					'id' => $this->id(),
				),
			),
			'rules' => array(
				'title' => __( 'Manage Rules', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-th-list',
				'params' => array(
					'do' => 'edit',
					'_tab' => 'hook_rules',
					'id' => $this->id(),
				),
			),
			'export' => array(
				'title' => __( 'Download ' . $this->_getSingularName(), 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-cloud-download',
				'params' => array(
					'do' => 'export',
					'id' => $this->id(),
				),
			),
			'delete' => ''
		);
		
		if ( ! $this->isCustom() ) {
			unset( $hook_actions['schedule'] );
			unset( $hook_actions['rules'] );
		}
		
		return array_replace_recursive( $hook_actions, $actions );
	}

	/**
	 * Get the event definition
	 *
	 * @return	array
	 */
	public function getEventDefinition()
	{
		$definition = array(
			'title' => $this->title . ( $this->isCustom() ? ' (Custom Action)' : ' (Custom Event)' ),
			'description' => $this->description,
			'group' => $this->category ?: 'Uncategorized',
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getProvidesDefinition();
		}
		
		$definition['hook_data'] = $this->_data;
		
		return $definition;
	}
	
	/**
	 * Get the action definition
	 *
	 * @return	array
	 */
	public function getActionDefinition()
	{
		$definition = array(
			'title' => $this->title . ' (Custom Action)',
			'description' => $this->description,
			'group' => $this->category ?: 'Custom',
			'callback' => array( static::class, 'callback_' . $this->id() ),
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getReceivesDefinition();
		}
		
		$definition['hook_data'] = $this->_data;
		
		return $definition;
	}
	
	/**
	 * Get the title of the hook type for display
	 *
	 * @return	string
	 */
	public function getTypeTitle()
	{
		return $this->isCustom() ? __( 'Custom Action', 'mwp-rules' ) : ucfirst( $this->type );
	}
	
	/**
	 * Check if this is a custom event
	 *
	 * @return	bool
	 */
	public function isCustom()
	{
		return $this->type == 'custom';
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
		$has_hook_config = false;
		
		if ( $this->title ) {
			$form->addHtml( 'hook_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-flash"></i>',
				'label' => $this->getTypeTitle(),
				'title' => $this->title,
			]));
		}
		
		if ( ! $this->type ) {
			$request = Framework::instance()->getRequest();
			if ( $request->get( 'type' ) == 'custom' ) {
				$this->type = 'custom';
				$form->addField( 'specification', 'hidden', array( 'data' => 'new' ) );
			}
		}
		
		$form->addTab( 'hook_details', array(
			'title' => __( ( $this->isCustom() ? 'Action' : 'Event' ) . ' Details', 'mwp-rules' ),
		));
		
		if ( $this->type != 'custom' ) {
			$has_hook_config = true;
			$form->addField( 'hook', 'text', array(
				'row_attr' => array( 'id' => 'hook_hook' ),
				'label' => __( 'Hook' ),
				'description' => __( 'Enter the name of the hook', 'mwp-rules' ),
				'attr' => array( 'placeholder' => 'hook_name' ),
				'data' => $this->hook,
				'required' => $this->id() > 0,
			), 'hook_details' );
			
			$form->addField( 'type', 'choice', array(
				'row_attr' => array( 'id' => 'hook_type' ),
				'label' => __( 'Type', 'mwp-rules' ),
				'choices' => array(
					'Action' => 'action',
					'Filter' => 'filter',
				),
				'data' => $this->type ?: 'action',
				'description' => __( 'Choose whether the hook is an action or a filter.', 'mwp-rules' ),
				'expanded' => true,
				'required' => true,
			), 'hook_details' );
		}
		
		$form->addField( 'title', 'text', array(
			'row_prefix' => $has_hook_config ? '<hr>' : '',
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'hook_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'hook_details' );
		
		$form->addField( 'category', 'text', array(
			'label' => __( 'Category', 'mwp-rules' ),
			'description' => __( 'A category name used to categorize this event in the event selection widget displayed when starting new rules.', 'mwp-rules' ),
			'data' => $this->category,
			'required' => false,
		), 'hook_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'arguments', array(
				'title' => __( 'Arguments', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'hook' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
			
			if ( $this->isCustom() ) {
				$form->addTab( 'hook_rules', array(
					'title' => __( 'Rules', 'mwp-rules' ),
				));
				
				$rulesController = $plugin->getRulesController();
				$rulesController->setHook( $this );
				$rulesTable = $rulesController->createDisplayTable([ 
					'perPage' => 1000,
					'hardFilters' => array( array( "rule_custom_internal=%d AND rule_event_type=%s AND rule_event_hook=%s", 1, 'action', $this->hook ) ),
					'bulkActions' => [],
				]);
				$rulesTable->prepare_items();
				
				$form->addHtml( 'hook_rules_table', $this->getPlugin()->getTemplateContent( 'rules/subrules/table_wrapper', array( 
					'rule' => null,
					'table' => $rulesTable,
					'controller' => $rulesController,
				)));
			}
			
		} else {
			$hook = $this;
			$form->onComplete( function() use ( $hook, $plugin ) {
				$controller = $plugin->getHooksController( $hook->getControllerKey() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $hook->id(), '_tab' => 'arguments' ) ) );
				exit;
			});			
		}
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save', 'mwp-rules' ),
		), '');
		
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
		$_values = $values['hook_details'];
		
		if ( ! $this->id() and $this->type == 'custom' ) {
			$this->hook = uniqid( 'rules/action/' );
		}
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Schedule an action
	 * 
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildScheduleForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'schedule', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		
		/* Only allow scheduling of custom actions */
		if ( $this->type != 'custom' ) {
			$form->addHtml( '<div class="alert alert-danger">Scheduling is not available for this hook.</div>' );
			return $form;
		}
		
		$form->addTab( 'scheduling', array(
			'title' => __( 'Scheduling', 'mwp-rules' ),
		));
		
		$form->addField( 'schedule_time', 'datetime', array(
			'label' => __( 'Scheduled Date/Time', 'mwp-rules' ),
			'input' => 'timestamp',
			'data' => time(),
		));
		
		$form->addField( 'recurrance', 'choice', array(
			'label' => __( 'Recurrance', 'mwp-rules' ),
			'choices' => array(
				'One Time Only' => 'none',
				'Repeating Interval' => 'repeating',
			),
			'toggles' => array(
				'repeating' => array( 'show' => array( '#schedule_minutes', '#schedule_hours', '#schedule_days', '#schedule_months' ) ),
			),
			'data' => 'none',
			'required' => true,
			'expanded' => true,
		));
		
		$form->addField( 'schedule_minutes', 'integer', array( 'label' => __( 'Minutes', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_minutes' ), 'data' => 0 ) );
		$form->addField( 'schedule_hours', 'integer', array( 'label' => __( 'Hours', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_hours' ), 'data' => 0 ) );
		$form->addField( 'schedule_days', 'integer', array( 'label' => __( 'Days', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_days' ), 'data' => 0 ) );
		$form->addField( 'schedule_months', 'integer', array( 'label' => __( 'Months', 'mwp-rules' ), 'row_attr' => array( 'id' => 'schedule_months' ), 'data' => 0 ) );
		
		$form->addTab( 'data', array(
			'title' => __( 'Action Data', 'mwp-rules' ),
		));
		
		foreach( $this->getArguments() as $argument ) {
			$argument->addFormWidget( $form, [] );
		}
		
		return $form;
	}
	
	/**
	 * Process the scheduling form
	 * 
	 * @param	array		$values				The form values
	 * @return	void
	 */
	public function processScheduleForm( $values )
	{
		
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getHooksController( $this->getControllerKey() )->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit' ), $params ) );
	}
	
	/**
	 * Get the key of the controller for this hook
	 *
	 * @return	string
	 */
	public function getControllerKey()
	{
		return $this->isCustom() ? 'actions' : 'events';
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		$export['arguments'] = array_map( function( $argument ) { return $argument->getExportData(); }, $this->getArguments() );
		
		if ( $this->isCustom() ) {
			$export['rules'] = array_map( function( $rule ) { return $rule->getExportData(); }, Rule::loadWhere(['rule_parent_id=0 AND rule_custom_internal=1 AND rule_event_type=%s AND rule_event_hook=%s', 'action', $this->hook ]) );
		}
		
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @return	array
	 */
	public static function import( $data )
	{
		$uuid_col = static::_getPrefix() . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$hook = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Remove duplicates of the same event */
			static::deleteWhere( array( 'hook_hook=%s AND hook_type=%s AND hook_uuid<>%s', $data['data']['hook_hook'], $data['data']['hook_type'], $data['data']['hook_uuid'] ) );
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$hook->_setDirectly( $col, $value );
			}
			
			$hook->imported = time();
			$result = $hook->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['hooks'][] = $data;
				
				$imported_argument_uuids = [];
				$imported_rule_uuids = [];

				/* Import hook arguments */
				if ( isset( $data['arguments'] ) and ! empty( $data['arguments'] ) ) {
					foreach( $data['arguments'] as $argument ) {
						$imported_argument_uuids[] = $argument['data']['argument_uuid'];
						$results = array_merge_recursive( $results, Argument::import( $argument, $hook ) );
					}
				}
				
				/* Import bundle rules */
				if ( isset( $data['rules'] ) and ! empty( $data['rules'] ) ) {
					foreach( $data['rules'] as $rule ) {
						$imported_rule_uuids[] = $rule['data']['rule_uuid'];
						$results = array_merge_recursive( $results, Rule::import( $rule ) );
					}
				}
				
				/* Cull previously imported arguments which are no longer part of this imported hook */
				foreach( Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d AND argument_imported > 0 AND argument_uuid NOT IN (\'' . implode("','", $imported_argument_uuids) . '\')', Argument::getParentType( $hook ), $hook->id() ) ) as $argument ) {
					$argument->delete();
				}
				
				/* Cull previously imported rules which are no longer part of this imported hook */
				foreach( Rule::loadWhere( array( 'rule_parent_id=0 AND rule_custom_internal=1 AND rule_imported > 0 AND rule_event_type=%d AND rule_event_hook=%d AND rule_uuid NOT IN (\'' . implode("','", $imported_rule_uuids) . '\')', 'action', $hook->hook ) ) as $rule ) {
					$rule->delete();
				}
				
			} else {
				$results['errors']['hooks'][] = $result;
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
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		Plugin::instance()->clearCustomHooksCache();
		return parent::save();
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		foreach( $this->getArguments() as $argument ) {
			$argument->delete();
		}
		
		if ( $this->isCustom() ) {
			foreach( Rule::loadWhere( array( 'rule_custom_internal=1 AND rule_event_type=%s AND rule_event_hook=%s', $this->event_type, $this->event_hook ) ) as $rule ) {
				$rule->delete();
			}
		}
		
		Plugin::instance()->clearCustomHooksCache();
		return parent::delete();
	}
	
	/**
	 * Magic method used to act as a rules ECA callback
	 * 
	 * @return	mixed
	 */
	public static function __callStatic( $name, $arguments )
	{
		$parts = explode( '_', $name );
		if ( $parts[0] == 'callback' ) {
			if ( count( $parts ) == 2 ) {
				try {
					if ( $hook = static::load( $parts[1] ) ) {
						call_user_func_array( 'do_action', array_merge( array( $hook->hook ), $arguments ) );
					}
				}
				catch( \OutOfRangeException $e ) { }
			}
		}
	}	
}
