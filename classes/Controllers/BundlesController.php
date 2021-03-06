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
					'export' => __( 'Download Bundles', 'mwp-rules' ),
				),
				'columns' => array(
					'bundle_title'   => __( 'Bundle', 'mwp-rules' ),
					'bundle_creator' => __( 'Creator', 'mwp-rules' ),
					'overview'       => __( 'Overview', 'mwp-rules' ),
					'bundle_enabled' => __( 'Status', 'mwp-rules' ),
					'_row_actions'   => '',
					'drag_handle'    => '',
				),
				'sortable' => array(
					'bundle_creator' => 'bundle_creator',
				),
				'handlers' => array(
					'drag_handle' => function( $row ) {
						return '<div class="draggable-handle mwp-bootstrap"><i class="glyphicon glyphicon-menu-hamburger"></i></div>';
					},
					'bundle_title' => function( $row ) {
						return '<div>' . 
							'<div class="mwp-bootstrap" style="margin:0 0 4px 0; font-size: 1.3em;"><i class="glyphicon glyphicon-briefcase" style="margin-right: 5px; font-size: 0.7em;"></i> ' . esc_html( $row['bundle_title'] ) . '</div>' .
							'<div>' . esc_html( $row['bundle_description'] ) . '</div>' .
						'</div>';
					},
					'overview' => function( $row ) {
						$bundle = Rules\Bundle::load( $row['bundle_id'] );
						$active_count = $bundle->getRuleCount( $enabled_only=TRUE );
						$total_count = $bundle->getRuleCount();
						
						return '<div class="mwp-bootstrap">' . 
							'<i class="glyphicon glyphicon-triangle-right" style="font-size:0.7em"></i> <a href="' . $bundle->url(['_tab'=>'bundle_rules']) . '">' . $total_count . ' total rules</a> ' . 
							( $active_count < $total_count ? "({$active_count} enabled)" : "" ) .
						'</div>';
					},
					'bundle_enabled' => function( $row ) {
						$output = '<div class="mwp-bootstrap">' . ( 
							$row['bundle_enabled'] ? 
							'<span data-rules-enabled-toggle="bundle" data-rules-id="' . $row['bundle_id'] . '" class="label label-success rules-pointer">ENABLED</span>' : 
							'<span data-rules-enabled-toggle="bundle" data-rules-id="' . $row['bundle_id'] . '" class="label label-danger rules-pointer">DISABLED</span>' ) .
						'</div>';
						
						if ( is_multisite() ) {
							$bundle = Rules\Bundle::load( $row['bundle_id'] );
							$output .= Rules\Plugin::instance()->getTemplateContent( 'snippets/site-list', [ 'sites' => $bundle->getSites() ] );
						}
						
						return $output;
					},
				),
			),
		));
	}
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	protected function constructed()
	{
		parent::constructed();
		
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
		$table->removeTableClass( 'fixed' );
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
