<?php
/**
 * Plugin Class File
 *
 * Created:   April 3, 2018
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

/**
 * BundleSet Class
 */
class _Bundle extends ExportableRecord
{
	
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_bundles";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid' => [ 'type' => 'varchar', 'length' => 25 ],
		'title' => [ 'type' => 'varchar', 'length' => 1028, 'allow_null' => false ],
		'weight' => [ 'type' => 'int', 'length' => 11, 'default' => '0', 'allow_null' => false ],
		'description' => [ 'type' => 'text', 'default' => '' ],
		'enabled' => [ 'type' => 'tinyint', 'length' => 1, 'default' => '1', 'allow_null' => false ],
		'imported' => [ 'type' => 'int', 'length' => 11, 'default' => '0', 'allow_null' => false ],
		'app_id' => [ 'type' => 'bigint', 'length' => 20, 'default' => '0', 'allow_null' => false ],
		'data' => [ 'type' => 'text', 'format' => 'JSON' ],
		'add_menu' => [ 'type' => 'tinyint', 'length' => 1, 'default' => '0', 'allow_null' => false ],
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'bundle_';

    /**
     * @var bool        Separate table per site?
     */
    public static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Bundle';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Bundles';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Get the 'edit record' page title
	 * 
	 * @return	string
	 */
	public function _getEditTitle( $type=NULL )
	{
		switch( $type ) {
			case 'settings':
				return __( 'Bundle Settings', 'mwp-rules' );
		}
		
		return parent::_getEditTitle( $type );
	}
	
	/**
	 * Check if the bundle is active
	 *
	 * @return	bool
	 */
	public function isActive()
	{
		if ( ! $this->enabled ) {
			return false;
		}
		
		if ( $app = $this->getApp() ) {
			return $app->isActive();
		}
		
		return true;
	}
	
	/**
	 * Get the linked app
	 *
	 * @return	MWP\Rules\App|NULL
	 */
	public function getApp()
	{
		if ( $this->app_id ) {
			try {
				return App::load( $this->app_id );
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
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
	 * @var array
	 */
	protected $argmap;
	
	/**
	 * Get an argument by varname
	 *
	 * @param	string			$varname			The argument varname
	 * @return	Argument|NULL
	 */
	public function getArgument( $varname )
	{
		$varname = strtolower( $varname );
		
		if ( ! isset( $this->argmap ) ) {
			foreach( $this->getArguments() as $argument ) {
				$this->argmap[ strtolower( $argument->varname ) ] = $argument;
			}
		}
		
		if ( isset( $this->argmap[ $varname ] ) ) {
			return $this->argmap[ $varname ];
		}
		
		return NULL;
	}
	
	/**
	 * Get customizable arguments
	 *
	 * @return	array
	 */
	public function getSettableArguments()
	{
		$arguments = array();
		foreach( $this->getArguments() as $argument ) {
			if ( $argument->isSettable() ) {
				$arguments[] = $argument;
			}
		}
		
		return $arguments;
	}
	
	/**
	 * Does this bundle have settings?
	 *
	 * @return	bool
	 */
	public function hasSettings()
	{
		return count( $this->getSettableArguments() ) > 0;
	}
	
	/**
	 * Get the controller
	 *
	 * @param	string		$key			The controller key
	 * @return	ActiveRecordController
	 */
	public function _getController( $key='admin' )
	{
		return $this->getPlugin()->getBundlesController( $this->getApp(), $key );
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
		
		$bundle_actions = array(
			'settings' => array(
				'title' => __( 'Adjust Settings', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-cog',
				'params' => array(
					'do' => 'settings',
					'id' => $this->id(),
				),
			),
			'edit' => '',
			'manage_rules' => array(
				'title' => __( 'Manage Rules', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-briefcase',
				'params' => array(
					'do' => 'edit',
					'_tab' => 'bundle_rules',
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
		
		if ( ! $this->hasSettings() ) {
			unset( $bundle_actions['settings'] );
		}
		
		return array_replace_recursive( $bundle_actions, $actions );
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
		
		/* Display details for the app/bundle */
		$form->addHtml( 'bundle_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'app' => $this->getApp(), 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'bundle_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-lamp"></i> ',
				'label' => 'Bundle',
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'bundle_details', array(
			'title' => __( 'Bundle Details', 'mwp-rules' ),
		));
		
		if ( $this->id() ) {
			$app_choices = [
				'Unassigned' => 0,
			];
			
			foreach( App::loadWhere('1') as $app ) {
				$app_choices[ $app->title ] = $app->id();
			}
			
			if ( count( $app_choices ) > 1 ) {
				$form->addField( 'app_id', 'choice', array(
					'label' => __( 'Associated App', 'mwp-rules' ),
					'choices' => $app_choices,
					'required' => true,
					'data' => $this->app_id,
				), 'bundle_details' );
			}
		}
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'bundle_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'bundle_details' );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Enabled', 'mwp-rules' ),
			'description' => __( 'Choose whether this bundle is enabled or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => $this->enabled !== NULL ? (bool) $this->enabled : true,
		), 'bundle_details' );
		
		if ( $this->id() ) {
			
			$form->addTab( 'arguments', array(
				'title' => __( 'Variables', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'actions' => array_replace_recursive( $argumentsController->getActions(), array( 
					'new' => array( 
						'title' => __( 'Add Variable', 'mwp-rules' ),
					), 
				)),
				'bundle' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
			
			$form->addTab( 'bundle_rules', array(
				'title' => __( 'Rules', 'mwp-rules' ),
			));
			
			$rulesController = $plugin->getRulesController( $this );
			$rulesTable = $rulesController->createDisplayTable();
			$rulesTable->bulkActions = array();
			$rulesTable->prepare_items( array( 'rule_parent_id=0 AND rule_bundle_id=%d', $this->id() ) );
			
			$form->addHtml( 'rules_table', $this->getPlugin()->getTemplateContent( 'rules/subrules/table_wrapper', array( 
				'rule' => null, 
				'table' => $rulesTable, 
				'controller' => $rulesController,
			)),
			'bundle_rules' );
			
			if ( ! $bundle->app_id ) {
				$form->addTab( 'bundle_advanced', array(
					'title' => __( 'Advanced', 'mwp-rules' ),
				));
				
				$form->addField( 'add_menu', 'checkbox', array(
					'label' => __( 'Add Settings Menu', 'mwp-rules' ),
					'description' => __( 'Add the settings for this bundle to the core WordPress Settings menu.', 'mwp-rules' ),
					'value' => 1,
					'data' => $this->add_menu !== NULL ? (bool) $this->add_menu : false,
					'toggles' => array(
						1 => array( 'show' => array( '#menu_title' ) ),
					),
				));
				
				$form->addField( 'menu_title', 'text', array(
					'row_attr' => array( 'id' => 'menu_title' ),
					'label' => __( 'Custom Menu Title', 'mwp-rules' ),
					'description' => __( 'Customize the name of the settings menu link', 'mwp-rules' ),
					'attr' => array( 'placeholder' => $this->title ),
					'required' => false,
					'data' => $this->data['menu_title'],
				));
			}
			
			/* Redirect to the bundles tab of the containing app after saving */
			$bundle = $this;
			$form->onComplete( function() use ( $bundle, $plugin ) {
				if ( $app = $bundle->getApp() ) {
					$controller = $plugin->getAppsController();
					wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_bundles' ) ) );
					exit;
				}
			});
			
		} else {
			/* Redirect to the rules tab of newly created bundles */
			$bundle = $this;
			$form->onComplete( function() use ( $bundle, $plugin ) {
				$controller = $plugin->getBundlesController( $bundle->getApp() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $bundle->id(), '_tab' => 'arguments' ) ) );
				exit;
			});			
		}
		
		$submit_text = $this->id() ? 'Save Bundle' : 'Create Bundle';
		$form->addField( 'save', 'submit', [ 'label' => __( $submit_text, 'mwp-rules' ), 'row_prefix' => '<hr>', 'row_attr' => [ 'class' => 'text-center' ] ], '' );
		
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
		$_values = $values['bundle_details'];
		
		if ( isset( $values['bundle_advanced'] ) ) {
			if ( isset( $values['bundle_advanced']['add_menu'] ) ) {
				$_values['add_menu'] = $values['bundle_advanced']['add_menu'];
			}
			
			$data = $this->data;
			$data['menu_title'] = $values['bundle_advanced']['menu_title'];
			$this->data = $data;
		}
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Build the bundle settings form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildSettingsForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'settings', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		
		/* Display details for the app/bundle */
		$form->addHtml( 'bundle_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'app' => $this->getApp(), 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'bundle_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-lamp"></i> ',
				'label' => 'Bundle',
				'title' => $this->title,
			]));
		}
		
		foreach( $this->getArguments() as $argument ) {
			if ( $argument->isSettable() ) {
				$argument->addFormWidget( $form, $argument->getSavedValues() );
			}
		}
		
		$form->addField( 'submit', 'submit', array(
			'label' => __( 'Save Settings', 'mwp-rules' ),
		));
		
		return $form;
	}
	
	/**
	 * Process the bundle settings form
	 *
	 * @param	array			$values				Value from the form submission
	 * @return	void
	 */
	public function processSettingsForm( $values )
	{
		foreach( $this->getArguments() as $argument ) {
			if ( $argument->isSettable() ) {
				$formValues = $argument->getWidgetFormValues( $values );
				$argument->updateValues( $formValues );
				$argument->save();
			}
		}
	}
	
	/**
	 * Get the rules associated with the bundle
	 *
	 * @return	array[Rule]
	 */
	public function getRules()
	{
		return Rule::loadWhere( array( 'rule_parent_id=0 AND rule_bundle_id=%d', $this->id() ) );
	}
	
	/**
	 * Get a count of all the rules associated with this bundle
	 *
	 * @param	bool		$enabled_only				Only count rules that are enabled
	 * @return	int
	 */
	public function getRuleCount( $enabled_only=FALSE )
	{
		if ( $enabled_only ) {
			return Rule::countWhere( array( 'rule_bundle_id=%d AND rule_enabled=1', $this->id() ) );
		}
		
		return Rule::countWhere( array( 'rule_bundle_id=%d', $this->id() ) );
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getBundlesController( $this->getApp() )->getUrl( array( 'id' => $this->id(), 'do' => 'edit' ) + $params );
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
		$export['rules'] = array_map( function( $rule ) { return $rule->getExportData(); }, $this->getRules() );
		
		unset( $export['app_id'] );
		
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	int				$app_id				The app id the bundle belongs to
	 * @return	array
	 */
	public static function import( $data, $app_id=0 )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$bundle = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$bundle->_setDirectly( $col, $value );
			}
			
			$bundle->app_id = $app_id;
			$bundle->imported = time();
			$result = $bundle->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['bundles'][] = $data;
				
				$imported_argument_uuids = [];
				$imported_rule_uuids = [];
				
				/* Import bundle arguments */
				if ( isset( $data['arguments'] ) and ! empty( $data['arguments'] ) ) {
					foreach( $data['arguments'] as $argument ) {
						$imported_argument_uuids[] = $argument['data']['argument_uuid'];
						$results = array_merge_recursive( $results, Argument::import( $argument, $bundle ) );
					}
				}
				
				/* Import bundle rules */
				if ( isset( $data['rules'] ) and ! empty( $data['rules'] ) ) {
					foreach( $data['rules'] as $rule ) {
						$imported_rule_uuids[] = $rule['data']['rule_uuid'];
						$results = array_merge_recursive( $results, Rule::import( $rule, 0, $bundle->id() ) );
					}
				}
				
				/* Cull previously imported arguments which are no longer part of this imported bundle */
				foreach( Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d AND argument_imported > 0 AND argument_uuid NOT IN (\'' . implode("','", $imported_argument_uuids) . '\')', Argument::getParentType( $bundle ), $bundle->id() ) ) as $argument ) {
					$argument->delete();
				}
				
				/* Cull previously imported subrules which are no longer part of this imported bundle */
				foreach( Rule::loadWhere( array( 'rule_parent_id=0 AND rule_bundle_id=%d AND rule_imported > 0 AND rule_uuid NOT IN (\'' . implode("','", $imported_rule_uuids) . '\')', $bundle->id() ) ) as $rule ) {
					$rule->delete();
				}
				
			} else {
				$results['errors']['bundles'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		foreach( $this->getRules() as $rule ) {
			$rule->delete();
		}
		
		foreach( $this->getArguments() as $argument ) {
			$argument->delete();
		}
		
		return parent::delete();
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
