<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord

/**
 * Argument Class
 */
class _Argument extends ActiveRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    protected static $table = "rules_arguments";

    /**
     * @var    array        Table columns
     */
    protected static $columns = array(
        'id',
		'name',
		'type',
		'class',
		'required',
		'weight',
		'custom_class',
		'description',
		'varname',
		'parent_id',
		'parent_type',
    );

    /**
     * @var    string        Table primary key
     */
    protected static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    protected static $prefix = 'argument_';

    /**
     * @var bool        Separate table per site?
     */
    protected static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    protected static $plugin_class = 'MWP\Rules\Plugin';

}
