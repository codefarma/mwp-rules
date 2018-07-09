<?php
/**
 * BaseController Class
 *
 * Created:   June 21, 2018
 *
 * @package:  Automation Rules
 * @author:   Code Farma
 * @since:    1.1.0
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Helpers\ActiveRecordController;

/**
 * BaseController
 */
abstract class _BaseController extends ActiveRecordController
{

	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		$this->getPlugin()->enqueueScripts();
		parent::init();
	}
	
}
