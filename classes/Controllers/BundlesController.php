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
class _BundlesController extends ExportableController
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
	 * Get the parent
	 */
	public function getParent()
	{
		return $this->getApp();
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
					'delete' => __( 'Delete Bundles', 'mwp-rules' ),
					'export' => __( 'Export Bundles', 'mwp-rules' ),
				),
				'columns' => array(
					'bundle_title'        => __( 'Bundle Title', 'mwp-rules' ),
					'bundle_description'  => __( 'Bundle Description', 'mwp-rules' ),
					'bundle_enabled'      => __( 'Bundle Enabled', 'mwp-rules' ),
					'overview'             => __( 'Bundle Overview', 'mwp-rules' ),
				),
				'handlers' => array(
					'bundle_enabled' => function( $row ) {
						return (bool) $row['bundle_enabled'] ? 'Yes' : 'No';
					},
					'overview' => function( $row ) {
						$bundle = Rules\Bundle::load( $row['bundle_id'] );
						return __( 'Rule Count: ', 'mwp-rules' ) . $bundle->getRuleCount();
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
		$table->hardFilters[] = array( 'bundle_app_id=%d', $this->getAppId() );
		
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
			wp_redirect( Rules\Plugin::instance()->getAppsController()->getUrl( array( 'id' => $app->id(), 'do' => 'edit', '_tab' => 'app_bundles' ) ) );
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
	 * Customize the bundle settings
	 *
	 * @param	MWP\Rules\Bundle|NULL		$bundle			The bundle to customize settings for, or NULL to load by request param
	 * @return	void
	 */
	public function do_settings( $bundle=NULL )
	{
		$class = $this->recordClass;
		$controller = $this;
		
		if ( ! $bundle ) {
			try {
				$bundle = $class::load( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->error( __( 'The record could not be loaded.', 'mwp-framework' ) . ' Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ) );
				return;
			}
		}
		
		$form = $bundle->getForm( 'settings' );
		$save_error = NULL;
		
		if ( $form->isValidSubmission() ) 
		{
			$bundle->processForm( $form->getValues(), 'settings' );			
			if ( isset( $_REQUEST['from'] ) and $_REQUEST['from'] == 'dashboard' ) {
				$controller = $this->getPlugin()->getDashboardController();
			}
			
			$form->processComplete( function() use ( $controller ) {
				wp_redirect( $controller->getUrl() );
				exit;
			});	
		}

		$output = $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'title' => $bundle->_getEditTitle( 'settings' ), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $bundle, 'error' => $save_error ) );
		
		echo $this->wrap( $bundle->_getEditTitle( 'settings' ), $output, [ 'classes' => 'settings' ] );
	}
	
}
