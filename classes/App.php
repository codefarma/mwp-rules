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
 * App Class
 */
class _App extends ExportableRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_apps";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid',
		'title',
		'description',
		'weight',
		'enabled',
		'creator',
		'imported',
		'version',
		'data' => array( 'format' => 'JSON' ),
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'app_';

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
	public static $lang_singular = 'App';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Apps';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Check if the app is active
	 *
	 * @return	bool
	 */
	public function isActive()
	{
		if ( ! $this->enabled ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get the 'edit record' page title
	 * 
	 * @return	string
	 */
	public function _getEditTitle( $type=NULL )
	{
		switch( $type ) {
			case 'settings':
				return __( 'App Settings', 'mwp-rules' );
		}
		
		return parent::_getEditTitle( $type );
	}
	
	/**
	 * @var	array
	 */
	protected $_bundles;
	
	/**
	 * Get the bundles of this app
	 *
	 * @return	array[Bundle]
	 */
	public function getBundles()
	{
		if ( isset( $this->_bundles ) ) {
			return $this->_bundles;
		}
		
		$this->_bundles = Bundle::loadWhere( array( 'bundle_app_id=%d', $this->id() ) );
		
		return $this->_bundles;
	}
	
	/**
	 * Check if the app has settings
	 *
	 * @return	bool
	 */
	public function hasSettings()
	{
		$has_settings = false;
		
		/* If any bundle has settings, then the app has settings */
		foreach( $this->getBundles() as $bundle ) {
			if ( $bundle->hasSettings() ) {
				$has_settings = true;
			}
		}
		
		return $has_settings;
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
		
		$app_actions = array(
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
			unset( $app_actions['settings'] );
		}
		
		return array_replace_recursive( $app_actions, $actions );
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
		
		if ( $this->title ) {
			$form->addHtml( 'app_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-tent"></i> ',
				'label' => 'App',
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'app_details', array(
			'title' => __( 'App Details', 'mwp-rules' ),
		));
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'app_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'app_details' );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Enabled', 'mwp-rules' ),
			'description' => __( 'Choose whether this app is enabled or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => $this->enabled !== NULL ? (bool) $this->enabled : true,
		), 'app_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'app_bundles', array(
				'title' => __( 'App Bundles', 'mwp-rules' ),
			));
			
			$bundlesController = $plugin->getBundlesController( $this );
			$bundlesTable = $bundlesController->createDisplayTable();
			$bundlesTable->bulkActions = array();
			$bundlesTable->prepare_items();
			
			$form->addHtml( 'bundles_table', $this->getPlugin()->getTemplateContent( 'rules/bundles/table_wrapper', array( 
				'app' => $this, 
				'table' => $bundlesTable, 
				'controller' => $bundlesController,
			)),
			'app_bundles' );
			
		} else {
			$app = $this;
			$form->onComplete( function() use ( $app, $plugin ) {
				$controller = $plugin->getAppsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_bundles' ) ) );
				exit;
			});			
		}
		
		$submit_text = $this->id() ? 'Save App' : 'Create App';
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
		$_values = $values['app_details'];
		
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
		
		if ( $this->title ) {
			$form->addHtml( 'app_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-tent"></i> ',
				'label' => 'App',
				'title' => $this->title,
			]));
		}
		
		foreach( $this->getBundles() as $bundle ) {
			if ( $arguments = $bundle->getSettableArguments() ) {
				$form->addField( 'bundle_' . $bundle->id() . '_settings', 'fieldgroup' );
				$form->setCurrentContainer( 'bundle_' . $bundle->id() . '_settings' );
				$form->addHeading( 'bundle_' . $bundle->id() . '_heading', $bundle->title );
				foreach( $arguments as $argument ) {
					$argument->addFormWidget( $form, $argument->getSavedValues() );
				}
				$form->endLastContainer();
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
		foreach( $this->getBundles() as $bundle ) {
			if ( $arguments = $bundle->getSettableArguments() ) {
				$bundle_values = $values[ 'bundle_' . $bundle->id() . '_settings' ];
				foreach( $arguments as $argument ) {
					$formValues = $argument->getWidgetFormValues( $bundle_values );
					$argument->updateValues( $formValues );
					$argument->save();
				}
			}
		}
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getAppsController()->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit' ), $params ) );
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		$export['bundles'] = array_map( function( $bundle ) { return $bundle->getExportData(); }, $this->getBundles() );
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
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$app = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$app->_setDirectly( $col, $value );
			}
			
			$app->imported = time();
			$result = $app->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['apps'][] = $data;
				
				$imported_bundle_uuids = [];
				
				/* Import bundles */
				if ( isset( $data['bundles'] ) and ! empty( $data['bundles'] ) ) {
					foreach( $data['bundles'] as $bundle ) {
						$imported_bundle_uuids[] = $bundle['data']['bundle_uuid'];
						$results = array_merge_recursive( $results, Bundle::import( $bundle, $app->id() ) );
					}
				}
				
				/* Cull previously imported bundles which are no longer part of this imported app */
				foreach( Bundle::loadWhere( array( 'bundle_app_id=%d AND bundle_imported > 0 AND bundle_uuid NOT IN (\'' . implode("','", $imported_bundle_uuids) . '\')', $app->id() ) ) as $bundle ) {
					$bundle->delete();
				}
				
			} else {
				$results['errors']['apps'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Delete
	 *
	 * @return	bool
	 */
	public function delete()
	{
		foreach( $this->getBundles() as $bundle ) {
			$bundle->delete();
		}
		
		return parent::delete();
	}
	
	/**
	 * Save
	 *
	 * @return	bool
	 */
	public function save()
	{
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		return parent::save();
	}
	
	/**
	 * Perform a bulk action on records
	 *
	 * @param	string			$action					The action to perform
	 * @param	array			$records				The records to perform the bulk action on
	 */
	public static function processBulkAction( $action, array $records )
	{
		switch( $action ) {
			case 'export':
				$package = Plugin::instance()->createPackage( $records );
				$package_title = sanitize_title( current_time( 'mysql' ) );
				header('Content-disposition: attachment; filename=' . $package_title . '.package.rules.json');
				header('Content-type: application/json');
				echo json_encode( $package, JSON_PRETTY_PRINT );
				exit;
				
			default:
				parent::processBulkAction( $action, $records );
				break;
		}
		foreach( $records as $record ) {
			if ( is_callable( array( $record, $action ) ) ) {
				call_user_func( array( $record, $action ) );
			}
		}
	}	
}
