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
 * FeatureSet Class
 */
class _Feature extends ExportableRecord
{
	
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_features";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid',
		'title',
		'weight',
		'description',
		'enabled',
		'imported',
		'app_id',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'feature_';

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
	public static $lang_singular = 'Feature';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Features';
	
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
				return __( 'Feature Settings', 'mwp-rules' );
		}
		
		return parent::_getEditTitle( $type );
	}
	
	/**
	 * Check if the feature is active
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
	 * Does this feature have settings?
	 *
	 * @return	bool
	 */
	public function hasSettings()
	{
		return count( $this->getSettableArguments() ) > 0;
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
		
		$feature_actions = array(
			'edit' => '',
			'settings' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-cog',
				'attr' => array( 
					'title' => __( 'Edit Settings', 'mwp-rules' ),
					'class' => 'btn btn-xs btn-default',
				),
				'params' => array(
					'do' => 'settings',
					'id' => $this->id(),
				),
			),
			'export' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-export',
				'attr' => array( 
					'title' => __( 'Export ' . $this->_getSingularName(), 'mwp-rules' ),
					'class' => 'btn btn-xs btn-default',
				),
				'params' => array(
					'do' => 'export',
					'id' => $this->id(),
				),
			),
			'delete' => ''
		);
		
		if ( ! $this->hasSettings() ) {
			unset( $feature_actions['settings'] );
		}
		
		return array_replace_recursive( $feature_actions, $actions );
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
		
		/* Display details for the app/feature */
		$form->addHtml( 'feature_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'app' => $this->getApp(), 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'feature_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-lamp"></i> ',
				'label' => 'Feature',
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'feature_details', array(
			'title' => __( 'Feature Details', 'mwp-rules' ),
		));
		
		if ( $this->id() ) {
			$app_choices = [
				'Unassigned' => 0,
			];
			
			foreach( App::loadWhere('1') as $app ) {
				$app_choices[ $app->title ] = $app->id();
			}
			
			$form->addField( 'app_id', 'choice', array(
				'label' => __( 'Associated App', 'mwp-rules' ),
				'choices' => $app_choices,
				'required' => true,
				'data' => $this->app_id,
			), 'feature_details' );
		}
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'feature_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'feature_details' );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Enabled', 'mwp-rules' ),
			'description' => __( 'Choose whether this feature is enabled or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => $this->enabled !== NULL ? (bool) $this->enabled : true,
		), 'feature_details' );
		
		if ( $this->id() ) {
			
			$form->addTab( 'arguments', array(
				'title' => __( 'Feature Settings', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'actions' => array_replace_recursive( $argumentsController->getActions(), array( 
					'new' => array( 
						'title' => __( 'Add Parameter', 'mwp-rules' ),
					), 
				)),
				'feature' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
			
			$form->addTab( 'feature_rules', array(
				'title' => __( 'Feature Rules', 'mwp-rules' ),
			));
			
			$rulesController = $plugin->getRulesController( $this );
			$rulesTable = $rulesController->createDisplayTable();
			$rulesTable->bulkActions = array();
			$rulesTable->prepare_items( array( 'rule_parent_id=0 AND rule_feature_id=%d', $this->id() ) );
			
			$form->addHtml( 'rules_table', $this->getPlugin()->getTemplateContent( 'rules/subrules/table_wrapper', array( 
				'rule' => null, 
				'table' => $rulesTable, 
				'controller' => $rulesController,
			)),
			'feature_rules' );
			
			/* Redirect to the features tab of the containing app after saving */
			$feature = $this;
			$form->onComplete( function() use ( $feature, $plugin ) {
				if ( $app = $feature->getApp() ) {
					$controller = $plugin->getAppsController();
					wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_features' ) ) );
					exit;
				}
			});
			
		} else {
			/* Redirect to the rules tab of newly created features */
			$feature = $this;
			$form->onComplete( function() use ( $feature, $plugin ) {
				$controller = $plugin->getFeaturesController( $feature->getApp() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $feature->id(), '_tab' => 'feature_rules' ) ) );
				exit;
			});			
		}
		
		$submit_text = $this->id() ? 'Save Feature' : 'Create Feature';
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
		$_values = $values['feature_details'];
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Build the feature settings form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildSettingsForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'settings', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		
		/* Display details for the app/feature */
		$form->addHtml( 'feature_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'app' => $this->getApp(), 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'feature_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-lamp"></i> ',
				'label' => 'Feature',
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
	 * Process the feature settings form
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
	 * Get the rules associated with the feature
	 *
	 * @return	array[Rule]
	 */
	public function getRules()
	{
		return Rule::loadWhere( array( 'rule_parent_id=0 AND rule_feature_id=%d', $this->id() ) );
	}
	
	/**
	 * Get a count of all the rules associated with this feature
	 *
	 * @return	int
	 */
	public function getRuleCount()
	{
		return Rule::countWhere( array( 'rule_feature_id=%d', $this->id() ) );
	}
	
	protected $argmap;
	
	/**
	 * Get an argument by varname
	 *
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
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getFeaturesController( $this->getApp() )->getUrl( array( 'id' => $this->id(), 'do' => 'edit' ) + $params );
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
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	int				$app_id				The app id the feature belongs to
	 * @return	array
	 */
	public static function import( $data, $app_id=0 )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$feature = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$feature->_setDirectly( $col, $value );
			}
			
			$feature->app_id = $app_id;
			$feature->imported = time();
			$result = $feature->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['features'][] = $data;
				
				$imported_argument_uuids = [];
				$imported_rule_uuids = [];
				
				/* Import feature arguments */
				if ( isset( $data['arguments'] ) and ! empty( $data['arguments'] ) ) {
					foreach( $data['arguments'] as $argument ) {
						$imported_argument_uuids[] = $argument['data']['argument_uuid'];
						$results = array_merge_recursive( $results, Argument::import( $argument, $feature ) );
					}
				}
				
				/* Import feature rules */
				if ( isset( $data['rules'] ) and ! empty( $data['rules'] ) ) {
					foreach( $data['rules'] as $rule ) {
						$imported_rule_uuids[] = $rule['data']['rule_uuid'];
						$results = array_merge_recursive( $results, Rule::import( $rule, 0, $feature->id() ) );
					}
				}
				
				/* Cull previously imported arguments which are no longer part of this imported feature */
				foreach( Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d AND argument_imported > 0 AND argument_uuid NOT IN (\'' . implode("','", $imported_argument_uuids) . '\')', Argument::getParentType( $feature ), $feature->id() ) ) as $argument ) {
					$argument->delete();
				}
				
				/* Cull previously imported subrules which are no longer part of this imported feature */
				foreach( Rule::loadWhere( array( 'rule_parent_id=0 AND rule_feature_id=%d AND rule_imported > 0 AND rule_uuid NOT IN (\'' . implode("','", $imported_rule_uuids) . '\')', $feature->id() ) ) as $rule ) {
					$rule->delete();
				}
				
			} else {
				$results['errors']['features'][] = $result;
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
