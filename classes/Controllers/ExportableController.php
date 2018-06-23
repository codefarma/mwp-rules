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

use MWP\Rules;

/**
 * ArgumentsController Class
 */
abstract class _ExportableController extends BaseController
{
	/**
	 * Download an export package
	 *
	 * @return	void
	 */
	public function do_export( $record=NULL )
	{
		$class = $this->recordClass;
		$controller = $this;
		
		if ( ! $record ) {
			try {
				$record = $class::load( isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0 );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->error( __( 'The record could not be loaded.', 'mwp-framework' ) . ' Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ) );
				return;
			}
		}
		
		$class::processBulkAction( 'export', array( $record ) );
	}
}
