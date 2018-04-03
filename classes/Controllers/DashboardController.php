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

use MWP\Framework\Pattern\Singleton;

/**
 * DashboardController Class
 *
 */
class _DashboardController extends Singleton
{
	
	/**
	 * @var	Instance Cache
	 */
	protected static $_instance;
	
	/**
	 * Dashboard Index
	 * 
	 * @return	void
	 */
	public function do_index()
	{
		
	}

}
