<?php
/**
 * Plugin Class File
 *
 * Created:   December 12, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Helpers\ActiveRecordController;

/**
 * Rules Class
 */
class Rules extends ActiveRecordController
{
	/**
	 * @var	string
	 */
	public static $recordClass = 'MWP\Rules\Rule';
	
	/**
	 * Constructor
	 *
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $options=array() )
	{
		parent::__construct( $options );
		$this->setPlugin( \MWP\Rules\Plugin::instance() );
	}

}
