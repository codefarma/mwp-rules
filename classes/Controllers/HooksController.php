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

use MWP\Rules\Hook;

/**
 * Events Controller Class
 */
class _HooksController extends ExportableController
{
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array(
			'tableConfig' => [
				'bulkActions' => array(
					'delete' => __( 'Delete Events', 'mwp-rules' ),
					'export' => __( 'Download Events', 'mwp-rules' ),
				),
				'handlers' => [
					'hook_hook' => function( $row ) {
						$output = '';
						switch( $row['hook_type'] ) {
							case 'action':
								$output .= '<code class="mwp-bootstrap"><span class="text-success">' . $row['hook_type'] . ':</span></code>';
								break;
							case 'filter':
								$output .= '<code class="mwp-bootstrap"><span class="text-warning">' . $row['hook_type'] . ':</span></code>';
								break;
							case 'custom':
								$output .= '<code class="mwp-bootstrap"><span class="text-primary">action:</span></code>';
								break;
							default:
								$output .= '<code class="mwp-bootstrap"><span>' . $row['hook_type'] . ':</span></code>';
						}
						
						return $output . '<code>' . $row['hook_hook'] . '</code>';
					},
					'arguments' => function( $row ) {
						$hook = Hook::load( $row['hook_id'] );
						$args = array_map( function( $arg ) { return '$' . $arg->varname; }, $hook->getArguments() );
						return ! empty( $args ) ? '<span class="mwp-bootstrap"><code>' . implode( '</code>, <code>', $args ) . '</code></span>' : 'No arguments.';
					}
				],
			],
		));
	}

	/**
	 * Get the active record display table
	 *
	 * @param	array			$override_options			Default override options
	 * @return	MWP\Framework\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable( $override_options=array() )
	{
		$table = parent::createDisplayTable( $override_options );
		$table->removeTableClass( 'fixed' );

		return $table;
	}
	
	
}
