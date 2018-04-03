<?php

if ( ! defined('ABSPATH') ) {
	die( 'Access denied.' );
}

$plugin = MWP\Rules\Plugin::instance();

return [	

	/* Rules */
	'rules_rules' => [
		'adminPage' => [
			'title' => __( 'Rules', 'mwp-rules' ),
			'type' => 'menu', 
			'slug' => 'mwp-rules', 
			'menu' => __( 'Rules', 'mwp-rules' ), 
			'icon' => $plugin->fileUrl( 'assets/img/gavel.png' ), 
			'position' => 76,
		],
	],
	
	/* Rules Conditions */
	'rules_conditions' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'parent_slug' => 'mwp-rules',
		],
	],

	/* Rules Actions */
	'rules_actions' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'parent_slug' => 'mwp-rules',
		],
	],
	
	'rules_apps' => [
		'adminPage' => [
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'Apps', 'mwp-rules' ),
		],
	],
	
	/* Rulesets */
	'rules_features' => [
		'adminPage' => [
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'Features', 'mwp-rules' ),
		],
	],

	/* Rules Logs */
	'rules_logs' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'menu' => __( 'Rules Logs', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
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
	
	/* Rules Hooks */
	'rules_hooks' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'menu' => __( 'Custom Hooks', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
		'tableConfig' => [
			'columns' => [
				'hook_type' => __( 'Type', 'mwp-rules' ),
				'hook_hook' => __( 'Hook', 'mwp-rules' ),
				'hook_title' => __( 'Title', 'mwp-rules' ),
				'hook_description' => __( 'Description', 'mwp-rules' ),
			],
		],
	],
	
	/* Arguments Controller */
	'rules_arguments' => [
		'adminPage' => [ 
			'type' => 'submenu', 
			'parent_slug' => 'mwp-rules' 
		],
		'tableConfig' => [
			'columns' => [
				'argument_title' => __( 'Title', 'mwp-rules' ),
				'argument_varname' => __( 'Variable Name', 'mwp-rules' ),
				'argument_type' => __( 'Type', 'mwp-rules' ),
				'argument_required' => __( 'Required', 'mwp-rules' ),
			],
			'handlers' => [
				'argument_varname' => function( $row ) {
					return '<code>' . '$' . $row['argument_varname'] . '</code>';
				},
				'argument_required' => function( $row ) {
					return $row['argument_required'] ? 'Yes' : 'No';
				},
			],
		],
		
	],
	
];