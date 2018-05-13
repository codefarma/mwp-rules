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

use MWP\Framework\Helpers\ActiveRecordController;
use MWP\Rules;

/**
 * Rules Class
 */
class _CustomLogsController extends ExportableController
{
	
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array
		(
			'tableConfig' => array(
				'bulkActions' => array(
					'delete' => __( 'Delete Custom Logs', 'mwp-rules' ),
					'export' => __( 'Export Custom Logs', 'mwp-rules' ),
				),
				'columns' => array(
					'custom_log_title' => __( 'Title', 'mwp-rules' ),
				),
			),
		));
	}
	
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New Custom Log', 'mwp-rules' ),
				'params' => array( 'do' => 'new' ),
				'attr' => array( 'class' => 'btn btn-primary' ),
			)
		);
	}
	
}
