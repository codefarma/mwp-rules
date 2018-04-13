<?php
/**
 * Plugin Class File
 *
 * Created:   December 12, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Helpers\ActiveRecordController;
use MWP\Rules;

/**
 * ArgumentsController Class
 */
class _FeaturesController extends ExportableController
{
	/**
	 * @var	MWP\Rules\App
	 */
	protected $app;
	
	/**
	 * Set the associated rule
	 */
	public function setApp( $app )
	{
		$this->app = $app;
	}
	
	/**
	 * Get the associated rule
	 */
	public function getApp()
	{
		return $this->app;
	}
	
	/**
	 * Get the associated app id
	 *
	 * @return	int
	 */
	public function getAppId()
	{
		if ( $app = $this->getApp() ) {
			return $app->id();
		}
		
		return 0;
	}
	
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array(
			'tableConfig' => array(
				'bulkActions' => array(
					'delete' => __( 'Delete Features', 'mwp-rules' ),
					'export' => __( 'Export Features', 'mwp-rules' ),
				),
				'columns' => array(
					'feature_title'        => __( 'Feature Title', 'mwp-rules' ),
					'feature_description'  => __( 'Feature Description', 'mwp-rules' ),
					'feature_enabled'      => __( 'Feature Enabled', 'mwp-rules' ),
					'overview'             => __( 'Feature Overview', 'mwp-rules' ),
				),
				'handlers' => array(
					'feature_enabled' => function( $row ) {
						return (bool) $row['feature_enabled'] ? 'Yes' : 'No';
					},
					'overview' => function( $row ) {
						$feature = Rules\Feature::load( $row['feature_id'] );
						return __( 'Rule Count: ', 'mwp-rules' ) . $feature->getRuleCount();
					},
				),
			),
		));
	}
	
	/**
	 * Constructor
	 *
	 * @param	string		$recordClass			The active record class
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $recordClass, $options=array() )
	{
		parent::__construct( $recordClass, $options );
		
		/* Auto set the app */
		if ( isset( $_REQUEST['app_id'] ) ) {
			try {
				$app = Rules\App::load( $_REQUEST['app_id'] );
				$this->setApp( $app );
			} catch( \OutOfRangeException $e ) { }
		}
	}
	
	/**
	 * Get the active record display table
	 *
	 * @param	array			$override_options			Default override options
	 * @return	MWP\Framework\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable( $override_options=array() )
	{
		$table = parent::createDisplayTable( $override_options );
		$table->hardFilters[] = array( 'feature_app_id=%d', $this->getAppId() );
		
		return $table;
	}
	
	/**
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		return parent::getUrl( array_merge( array( 'app_id' => $this->getAppId() ), $args ) );
	}

	/**
	 * Index Page
	 * 
	 * @return	string
	 */
	public function do_index()
	{
		if ( $app = $this->getApp() ) {
			wp_redirect( Rules\Plugin::instance()->getAppsController()->getUrl( array( 'id' => $app->id(), 'do' => 'edit', '_tab' => 'app_features' ) ) );
		}
		
		parent::do_index();
	}
	
	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_new( $record=NULL )
	{
		$class = $this->recordClass;
		$record = $record ?: new $class;
		$record->app_id = $this->getAppId();
		
		parent::do_new( $record );
	}
	
	/**
	 * Customize the feature settings
	 *
	 * @param	MWP\Rules\Feature|NULL		$feature			The feature to customize settings for, or NULL to load by request param
	 * @return	void
	 */
	public function do_settings( $feature=NULL )
	{
		$class = $this->recordClass;
		$controller = $this;
		
		if ( ! $feature ) {
			try {
				$feature = $class::load( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->error( __( 'The record could not be loaded.', 'mwp-framework' ) . ' Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ) );
				return;
			}
		}
		
		$form = $feature->getForm( 'settings' );
		$save_error = NULL;
		
		if ( $form->isValidSubmission() ) 
		{
			$feature->processForm( $form->getValues(), 'settings' );			
			$result = $feature->save();
			
			if ( ! is_wp_error( $result ) ) {
				$form->processComplete( function() use ( $controller ) {
					wp_redirect( $controller->getUrl() );
					exit;
				});	
			} else {
				$save_error = $result;
			}
		}

		$output = $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'title' => $feature->_getEditTitle( 'settings' ), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $feature, 'error' => $save_error ) );
		
		echo $this->wrap( $feature->_getEditTitle( 'settings' ), $output, 'settings' );
	}
	
}
