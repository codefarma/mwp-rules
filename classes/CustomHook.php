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

use MWP\Framework\Pattern\ActiveRecord;

/**
 * CustomHook Class
 */
class _CustomHook extends ActiveRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_custom_actions";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'title',
		'weight',
		'description',
		'key',
		'enable_api',
		'api_methods',
		'type',
		'hook',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'custom_action_';

    /**
     * @var bool        Separate table per site?
     */
    public static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * Build an editing form
	 *
	 * @param   ActiveRecord|NULL           $record     The record to edit, or NULL if creating
	 * @return  MWP\Framework\Helpers\Form
	 */
	public static function getForm( $record=NULL )
	{
		$form = parent::getForm( $record );

		return $form;
	}
	
}
