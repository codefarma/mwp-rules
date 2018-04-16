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
					'export' => __( 'Export Apps', 'mwp-rules' ),
				),
				'columns' => array(
					'app_title'        => __( 'App Title', 'mwp-rules' ),
					'app_description'  => __( 'App Description', 'mwp-rules' ),
					'app_enabled'      => __( 'App Enabled', 'mwp-rules' ),
				),
				'handlers' => array(
					'app_enabled' => function( $row ) {
						return (bool) $row['app_enabled'] ? 'Yes' : 'No';
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
		
		echo $this->wrap( $app->_getEditTitle( 'settings' ), $output, 'settings' );
	}
	
}
