<?php
/**
 * Plugin Class File
 *
 * Created:   April 3, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * FeatureSet Class
 */
class _Feature extends ActiveRecord
{
	
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_features";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'title',
		'weight',
		'description',
		'enabled',
		'creator',
		'created_time',
		'imported_time',
		'app_id',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'feature_';

    /**
     * @var bool        Separate table per site?
     */
    public static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Feature';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Features';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
		

}
