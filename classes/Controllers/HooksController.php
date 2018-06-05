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
use MWP\Rules\Hook;

/**
 * Events Controller Class
 */
class _HooksController extends ActiveRecordController
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
					'export' => __( 'Export Events', 'mwp-rules' ),
				),
				'columns' => [
					'hook_hook' => __( 'Hook', 'mwp-rules' ),
					'hook_title' => __( 'Event', 'mwp-rules' ),
					'hook_description' => __( 'Description', 'mwp-rules' ),
					'arguments' => __( 'Arguments', 'mwp-rules' ),
					//'hook_type' => __( 'Type', 'mwp-rules' ),
				],
				'handlers' => [
					'hook_hook' => function( $row ) {
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
						return ! empty( $args ) ? '<span class="mwp-bootstrap"><code>' . implode( ', ', $args ) . '</code></span>' : 'No arguments.';
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
