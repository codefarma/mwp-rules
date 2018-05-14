<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;
use MWP\Framework;

/**
 * Log Class
 */
abstract class _CustomLogEntry extends ActiveRecord
{
    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'entry_';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Log Entry';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Log Entries';
	
	/**
	 * Get the log
	 *
	 * @return	CustomLog
	 */
	public function getLog()
	{
		return CustomLog::load( $this->log_id );
	}
	
	/**
	 * Property getter
	 *
	 * @param	string		$property		The property to get
	 * @return	mixed
	 */
	public function __get( $property )
	{
		$value = parent::__get( $property );
		return Plugin::instance()->restoreArg( $value );
	}
	
	/**
	 * Property setter
	 *
	 * @param	string		$property		The property to set
	 * @param	mixed		$value			The value to set
	 * @return	void
	 * @throws	InvalidArgumentException
	 */
	public function __set( $property, $value )
	{
		$value = Plugin::instance()->storeArg( $value );
		return parent::__set( $property, $value );
	}
	
	/**
	 * Save entry
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		$is_new = ! $this->id();		
		$result = parent::save();
		
		if ( $is_new ) {
			$this->getLog()->checkAndScheduleMaintenance();
		}
		
		return $result;
	}
	
}
