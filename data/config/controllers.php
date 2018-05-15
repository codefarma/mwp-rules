<?php

if ( ! defined('ABSPATH') ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$plugin = Rules\Plugin::instance();

return [	

	/* Rules Dashboard */
	'rules_dashboard' => [
		'adminPage' => [
			'title' => __( 'Dashboard', 'mwp-rules' ),
			'type' => 'menu', 
			'slug' => 'mwp-rules', 
			'menu' => __( 'Rules Engine', 'mwp-rules' ),
			'menu_submenu' => __( 'Dashboard', 'mwp-rules' ),
			'icon' => $plugin->fileUrl( 'assets/img/gavel.png' ), 
			'position' => 76,
		],
	],
	
	'rules_apps' => [
		'adminPage' => [
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'App Manager', 'mwp-rules' ),
		],
	],
	
	/* Rulesets */
	'rules_bundles' => [
		'adminPage' => [
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'Bundle Manager', 'mwp-rules' ),
		],
	],

	/* Rules */
	'rules_rules' => [
		'adminPage' => [
			'title' => __( 'Rules', 'mwp-rules' ),
			'type' => 'submenu', 
			'parent' => 'mwp-rules', 
			'menu' => __( 'Rule Manager', 'mwp-rules' ), 
		],
	],
	
	/* Rules Conditions */
	'rules_conditions' => [
		'adminPage' => [ 
			'type' => 'submenu', 
		],
	],

	/* Rules Actions */
	'rules_actions' => [
		'adminPage' => [ 
			'type' => 'submenu', 
		],
	],
	
	/* Rules Hooks */
	'rules_hooks' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'menu' => __( 'Custom Events', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
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
					$hook = Rules\Hook::load( $row['hook_id'] );
					$args = array_map( function( $arg ) { return '$' . $arg->varname; }, $hook->getArguments() );
					return ! empty( $args ) ? '<span class="mwp-bootstrap"><code>' . implode( ', ', $args ) . '</code></span>' : 'No arguments.';
				}
			],
		],
	],
	
	/* Rules Logs */
	'rules_logs' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			//'menu' => __( 'Log Viewer', 'mwp-rules' ), 
			//'parent' => 'mwp-rules',
		],
	],
	
	/* Rules Scheduled Actions */
	'rules_scheduled_actions' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'menu' => __( 'Scheduled Actions', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
	],
	
	/* Arguments Controller */
	'rules_arguments' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'parent_slug' => 'mwp-rules' 
		],
	],
	
	/* Rules Custom Logs */
	'rules_custom_logs' => [
		'adminPage' => [
			'title' => __( 'Custom Logs', 'mwp-rules' ),
			'type' => 'submenu', 
			'parent' => 'mwp-rules', 
			'menu' => __( 'Custom Logs', 'mwp-rules' ), 
		],
	],
	
];