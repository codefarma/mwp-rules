<?php

if ( ! defined('ABSPATH') ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$plugin = Rules\Plugin::instance();
$for = is_multisite() ? 'network' : 'site';

return [	

	/* Rules Dashboard */
	'rules_dashboard' => [
		'adminPage' => [
			'for' => $for,
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
			'for' => $for,
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'App Manager', 'mwp-rules' ),
		],
	],
	
	/* Rulesets */
	'rules_bundles' => [
		'adminPage' => [
			'for' => $for,
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'Bundle Manager', 'mwp-rules' ),
		],
	],

	/* Rules */
	'rules_rules' => [
		'adminPage' => [
			'for' => $for,
			'title' => __( 'Rules', 'mwp-rules' ),
			'type' => 'submenu', 
			'parent' => 'mwp-rules', 
			'menu' => __( 'Rule Manager', 'mwp-rules' ), 
		],
	],
	
	/* Rules Conditions */
	'rules_conditions' => [
		'adminPage' => [ 
			'for' => $for,
			'type' => 'submenu', 
		],
	],

	/* Rules Actions */
	'rules_actions' => [
		'adminPage' => [ 
			'for' => $for,
			'type' => 'submenu', 
		],
	],
	
	/* Rules Events */
	'rules_events' => [
		'adminPage' => [ 
			'for' => $for,
			'title' => __( 'Custom Events', 'mwp-rules' ), 
			'type' => 'submenu',
			'slug' => 'mwp-rules-events',
			'menu' => __( 'Custom Events', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
		'tableConfig' => [
			'default_where' => array( "hook_type IN ( 'action', 'filter' )" ),
			'columns' => [
				'hook_hook' => __( 'Hook', 'mwp-rules' ),
				'hook_title' => __( 'Event', 'mwp-rules' ),
				'hook_description' => __( 'Description', 'mwp-rules' ),
				'arguments' => __( 'Arguments', 'mwp-rules' ),
			],
		],
	],
	
	/* Rules Custom Actions */
	'rules_custom_actions' => [
		'adminPage' => [ 
			'for' => $for,
			'type' => 'submenu',
			'title' => __( 'Custom Actions', 'mwp-rules' ),
			'slug' => 'mwp-rules-custom-actions',
			'menu' => __( 'Custom Actions', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
		'getActions' => function( $actions ) {
			$actions['new']['title'] = __( 'Create Custom Action', 'mwp-rules' );
			$actions['new']['params']['type'] = 'custom';
			
			return $actions;
		},
		'tableConfig' => [
			'constructor' => [ 'singular' => 'action', 'plural' => 'actions' ],
			'default_where' => array( "hook_type IN ( 'custom' )" ),
			'bulkActions' => array(
				'delete' => __( 'Delete Actions', 'mwp-rules' ),
				'export' => __( 'Download Actions', 'mwp-rules' ),
			),
			'columns' => [
				'hook_title' => __( 'Action Name', 'mwp-rules' ),
				'hook_description' => __( 'Description', 'mwp-rules' ),
				'arguments' => __( 'Arguments', 'mwp-rules' ),
				'rules' => __( 'Internal Rules', 'mwp-rules' ),
			],
			'handlers' => [
				'rules' => function( $row ) {
					$hook = Rules\Hook::load( $row['hook_id'] );
					return '<a href="' . $hook->url(['_tab'=>'hook_rules']) . '">' . Rules\Rule::countWhere(['rule_custom_internal=1 AND rule_event_type=%s AND rule_event_hook=%s', 'action', $row['hook_hook'] ]) . '</a>';
				}
			],
		],
	],
	
	/* Rules Logs */
	'rules_logs' => [
		'adminPage' => [ 
			'for' => $for,
			'type' => 'submenu',
			'menu' => __( 'System Log', 'mwp-rules' ),
			'parent' => 'mwp-rules',
		],
	],
	
	/* Rules Scheduled Actions */
	'rules_scheduled_actions' => [
		'adminPage' => [ 
			'for' => 'site',
			'type' => 'management', 
			'menu' => __( 'Rules Action Queue', 'mwp-rules' ), 
			//'parent' => 'mwp-rules',
		],
	],
	
	/* Arguments Controller */
	'rules_arguments' => [
		'adminPage' => [ 
			'for' => $for,
			'type' => 'submenu', 
			'parent_slug' => 'mwp-rules' 
		],
	],
	
	/* Rules Custom Logs */
	'rules_custom_logs' => [
		'adminPage' => [
			'for' => $for,
			'title' => __( 'Custom Logs', 'mwp-rules' ),
			'type' => 'submenu', 
			'parent' => 'mwp-rules', 
			'menu' => __( 'Custom Logs', 'mwp-rules' ), 
		],
	],
	
];