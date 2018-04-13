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
		'created_time',
		'imported_time',
		'version',
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
			'data' => (bool) $this->enabled,
		), 'app_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'app_features', array(
				'title' => __( 'App Features', 'mwp-rules' ),
			));
			
			$featuresController = $plugin->getFeaturesController( $this );
			$featuresTable = $featuresController->createDisplayTable();
			$featuresTable->bulkActions = array();
			$featuresTable->prepare_items();
			
			$form->addHtml( 'features_table', $this->getPlugin()->getTemplateContent( 'rules/features/table_wrapper', array( 
				'app' => $this, 
				'table' => $featuresTable, 
				'controller' => $featuresController,
			)),
			'app_features' );
			
		} else {
			$app = $this;
			$form->onComplete( function() use ( $app, $plugin ) {
				$controller = $plugin->getAppsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_features' ) ) );
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
	 * Get the features of this app
	 *
	 * @return	array[Feature]
	 */
	public function getFeatures()
	{
		return Feature::loadWhere( array( 'feature_app_id=%d', $this->id() ) );
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
		$data = $this->_data;
		unset( $data[ static::$prefix . static::$key ] );
		
		return array(
			'data' => $data,
			'features' => array_map( function( $feature ) { return $feature->getExportData(); }, $this->getFeatures() ),
		);
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
			
			$result = $app->save();
			
			if ( ! is_wp_error( $result ) ) {
				$results['imports']['apps'][] = $data['data'];
				if ( isset( $data['features'] ) and ! empty( $data['features'] ) ) {
					foreach( $data['features'] as $feature ) {
						$results = array_merge_recursive( $results, Feature::import( $feature, $app->id() ) );
					}
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
		foreach( $this->getFeatures() as $feature ) {
			$feature->delete();
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
