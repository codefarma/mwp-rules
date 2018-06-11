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
 * Rules Class
 */
class _CustomLogsController extends ExportableController
{
	
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array
		(
			'tableConfig' => array(
				'bulkActions' => array(
					'delete' => __( 'Delete Custom Logs', 'mwp-rules' ),
					'export' => __( 'Export Custom Logs', 'mwp-rules' ),
				),
				'columns' => array(
					'custom_log_title' => __( 'Log Title', 'mwp-rules' ),
					'fields' => __( 'Custom Fields', 'mwp-rules' ),
					'records' => __( 'Total Records', 'mwp-rules' ),
					'_row_actions'   => '',
					'drag_handle'    => '',
				),
				'handlers' => [
					'drag_handle' => function( $row ) {
						return '<div class="draggable-handle mwp-bootstrap"><i class="glyphicon glyphicon-menu-hamburger"></i></div>';
					},
					'fields' => function( $row ) {
						$log = Rules\CustomLog::load( $row['custom_log_id'] );
						$fields = array_map( function( $arg ) { return '<code>' . esc_html( $arg->title ) . '</code>'; }, $log->getArguments() );
						return ! empty( $fields ) ? implode( ', ', $fields ) : 'None.';
					},
					'records' => function ( $row ) {
						$log = Rules\CustomLog::load( $row['custom_log_id'] );
						$recordClass = $log->getRecordClass();
						return $recordClass::countWhere('1');
					}
				],
			),
		));
	}
	
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New Custom Log', 'mwp-rules' ),
				'params' => array( 'do' => 'new' ),
			)
		);
	}
	
	/**
	 * Delete an active record
	 * 
	 * @param	ActiveRecord			$record				The active record, or NULL to load by request param
	 * @return	void
	 */
	public function do_flush( $record=NULL )
	{
		$controller = $this;
		$class = $this->recordClass;
		
		if ( ! $record ) {
			try	{
				$record = $class::load( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
			}
			catch( \OutOfRangeException $e ) { 
 				echo $this->error( __( 'The custom log could not be loaded.', 'mwp-rules' ) . ' Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ) );
				return;
			}
		}
		
		$form = $record->getForm( 'flush' );
		
		if ( $form->isValidSubmission() )
		{
			if ( $form->getForm()->getClickedButton()->getName() === 'confirm' ) {
				$record->flushLogs();
			}
			
			$form->processComplete( function() use ( $controller ) {
				wp_redirect( $controller->getUrl() );
				exit;
			});
		}
	
		$output = $this->getPlugin()->getTemplateContent( 'views/management/records/delete', array( 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
		
		echo $this->wrap( __( 'Flush All Log Entries' ) . ': ' . $record->title, $output, [ 'classes' => 'flush', 'record' => $record ] );
	}
	
	
}
