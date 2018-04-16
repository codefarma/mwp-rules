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
		unset( $data[ static::$prefix . static::$key ] );
		unset( $data[ static::$prefix . 'imported' ] );
		
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
	}	
}
