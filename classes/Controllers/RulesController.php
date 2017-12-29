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
class RulesController extends ActiveRecordController
{
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActionButtons()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New Rule', 'mwp-rules' ),
				'href' => $this->getUrl( array( 'do' => 'new' ) ),
				'class' => 'btn btn-primary',
			)
		);
	}

}
