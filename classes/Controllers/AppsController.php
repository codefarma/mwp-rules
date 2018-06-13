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
class _AppsController extends ExportableController
{
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
					'delete' => __( 'Delete Apps', 'mwp-rules' ),
					'export' => __( 'Download Apps', 'mwp-rules' ),
				),
				'columns' => array(
					'app_title'        => __( 'Title', 'mwp-rules' ),
					'app_description'  => __( 'Description', 'mwp-rules' ),
					'app_enabled'      => __( 'Status', 'mwp-rules' ),
				),
				'handlers' => array(
					'app_enabled' => function( $row ) {
						$output = '<div class="mwp-bootstrap">' . ( 
							$row['app_enabled'] ? 
							'<span data-rules-enabled-toggle="app" data-rules-id="' . $row['app_id'] . '" class="label label-success rules-pointer">ENABLED</span>' : 
							'<span data-rules-enabled-toggle="app" data-rules-id="' . $row['app_id'] . '" class="label label-danger rules-pointer">DISABLED</span>' ) .
						'</div>';
						
						if ( is_multisite() ) {
							$app = Rules\App::load( $row['app_id'] );
							$output .= Rules\Plugin::instance()->getTemplateContent( 'snippets/site-list', [ 'sites' => $app->getSites() ] );
						}
						
						return $output;						
					},
				),
			),
		));
	}
	
	/**
	 * Customize the app settings
	 *
	 * @param	MWP\Rules\App|NULL		$app			The app to customize settings for, or NULL to load by request param
	 * @return	void
	 */
	public function do_settings( $app=NULL )
	{
		$class = $this->recordClass;
		$controller = $this;
		
		if ( ! $app ) {
			try {
				$app = $class::load( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->error( __( 'The record could not be loaded.', 'mwp-framework' ) . ' Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ) );
				return;
			}
		}
		
		$form = $app->getForm( 'settings' );
		$save_error = NULL;
		
		if ( $form->isValidSubmission() ) 
		{
			$app->processForm( $form->getValues(), 'settings' );
			if ( isset( $_REQUEST['from'] ) and $_REQUEST['from'] == 'dashboard' ) {
				$controller = $this->getPlugin()->getDashboardController();
			}

			$form->processComplete( function() use ( $controller ) {
				wp_redirect( $controller->getUrl() );
				exit;
			});	
		}

		$output = $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'title' => $app->_getEditTitle( 'settings' ), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $app, 'error' => $save_error ) );
		
		echo $this->wrap( $app->_getEditTitle( 'settings' ), $output, [ 'classes' => 'settings' ] );
	}
	
}
