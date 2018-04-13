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
		'tableConfig' => array(
			'bulkActions' => array(
				'delete' => __( 'Delete Apps', 'mwp-rules' ),
				'export' => __( 'Export Apps', 'mwp-rules' ),
			),
			'columns' => array(
				'app_title'        => __( 'App Title', 'mwp-rules' ),
				'app_description'  => __( 'App Description', 'mwp-rules' ),
				'app_enabled'      => __( 'App Enabled', 'mwp-rules' ),
			),
			'handlers' => array(
				'app_enabled' => function( $row ) {
					return (bool) $row['app_enabled'] ? 'Yes' : 'No';
				},
			),
		),
	],
	
	/* Rulesets */
	'rules_features' => [
		'adminPage' => [
			'type' => 'submenu',
			'parent' => 'mwp-rules',
			'menu' => __( 'Feature Manager', 'mwp-rules' ),
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
			'menu' => __( 'Custom Actions', 'mwp-rules' ), 
			'parent' => 'mwp-rules',
		],
		'tableConfig' => [
			'bulkActions' => array(
				'delete' => __( 'Delete Hooks', 'mwp-rules' ),
				'export' => __( 'Export Hooks', 'mwp-rules' ),
			),
			'columns' => [
				'hook_type' => __( 'Type', 'mwp-rules' ),
				'hook_hook' => __( 'Hook', 'mwp-rules' ),
				'hook_title' => __( 'Title', 'mwp-rules' ),
				'hook_description' => __( 'Description', 'mwp-rules' ),
			],
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
				'argument_widget' => __( 'Widget', 'mwp-rules' ),
				'default_value' => __( 'Default Value', 'mwp-rules' ),
			],
			'handlers' => [
				'argument_varname' => function( $row ) {
					return '<code>' . '$' . $row['argument_varname'] . '</code>';
				},
				'argument_required' => function( $row ) {
					return $row['argument_required'] ? 'Yes' : 'No';
				},
				'argument_widget' => function( $row ) {
					$argument = Rules\Argument::load( $row['argument_id'] );
					return '<a href="' . $argument->url([ '_tab' => 'widget_config' ]) . '">' . $argument->widget . '</a>';
				},
				'default_value' => function( $row ) {
					$argument = Rules\Argument::load( $row['argument_id'] );
					$default_values = $argument->getSavedValues( 'default' );
					
					if ( ! $argument->usesDefault() ) {
						return '--';
					}
					
					if ( ! is_array( $default_values ) or count( $default_values ) == 1 ) {
						$default_values = (array) $default_values;
						$value = array_shift( $default_values );
						if ( ! is_array( $value ) or is_object( $value ) ) {
							return '<a href="' . $argument->url([ 'do' => 'set_default' ]) . '">' . ( $value ? esc_html( (string) $value ) : '--' ) . '</a>';
						}
					}
					
					return '<a href="' . $argument->url([ 'do' => 'set_default' ]) . '">Complex Data</a>';
				}
			],
		],
		
	],
	
];