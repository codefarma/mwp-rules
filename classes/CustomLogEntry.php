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
	
}
