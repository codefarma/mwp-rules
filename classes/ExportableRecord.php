<?php
/**
 * Plugin Class File
 *
 * Created:   April 11, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    1.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * ExportableRecord Class
 */
abstract class _ExportableRecord extends ActiveRecord
{
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$data = $this->_data;
		unset( $data[ static::_getPrefix() . static::_getKey() ] );
		unset( $data[ static::_getPrefix() . 'imported' ] );
		
		return array(
			'data' => $data,
		);
	}
	
	/**
	 * Perform a bulk action on records
	 *
	 * @param	string			$action					The action to perform
	 * @param	array			$records				The records to perform the bulk action on
	 */
	public static function processBulkAction( $action, array $records )
	{
		if ( empty( $records ) ) {
			return;
		}
		
		$parts = explode( '\\', get_class( $records[0] ) );
		$entity = strtolower( array_pop( $parts ) );
		
		if ( $entity == 'hook' ) {
			$entity = $records[0]->isCustom() ? 'action' : 'event';
		}
		
		switch( $action ) {
			case 'export':
				$package = Plugin::instance()->createPackage( $records );
				$package_title = count( $records ) == 1 ? sanitize_title( $records[0]->title ) : sanitize_title( current_time( 'mysql' ) );
				$package_suffix = count( $records ) > 1 ? $entity . 's' : $entity;
				header('Content-disposition: attachment; filename=' . $package_title . '-' . $package_suffix . '.json');
				header('Content-type: application/json');
				echo json_encode( $package, JSON_PRETTY_PRINT );
				exit;
				
			default:
				parent::processBulkAction( $action, $records );
				break;
		}
	}	
}
