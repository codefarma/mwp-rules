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