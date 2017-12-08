<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\ActiveRecord;

/**
 * ScheduledAction Class
 */
class ScheduledAction extends ActiveRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_scheduled_actions";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
        'time',
		'data' => array(
			'format' => 'JSON'
		),
		'unique_key',
        'action_id',
		'queued',
		'thread',
		'parent_thread',
		'created',
		'custom_id',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'schedule_';
	
}
