<?php
/**
 * Plugin Name: MWP Rules
 * Plugin URI: 
 * Description: An automation rules engine for WordPress
 * Author: Kevin Carwile
 * Author URI: http://www.codefarma.com
 * Version: 0.9.2
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( class_exists( 'MWPRulesPlugin' ) ) {
	return;
}

/* Autoloaders */
include_once 'includes/plugin-bootstrap.php';

/**
 * This plugin uses the MWP Application Framework to init.
 *
 * @return void
 */
add_action( 'mwp_framework_init', function() 
{
	/**
	 * Get the framework instance
	 */
	$framework = MWP\Framework\Framework::instance();

	/* Plugin Core */
	$plugin	= MWP\Rules\Plugin::instance();
	
	/* Plugin Settings */
	$settings = MWP\Rules\Settings::instance();
	$plugin->addSettings( $settings );
	
	/* Attach callbacks to WordPress */
	$framework
		->attach( $plugin )
		->attach( $settings )
		->attach( MWP\Rules\AjaxHandlers::instance() )
		
		->attach( new MWP\Rules\Events\System )
		->attach( new MWP\Rules\Events\Content )
		->attach( new MWP\Rules\Events\Users )
		
		->attach( new MWP\Rules\Conditions\Content )
		->attach( new MWP\Rules\Conditions\System )
		
		->attach( new MWP\Rules\Actions\Content )
		->attach( new MWP\Rules\Actions\System )
		;
		
	$plugin->getRulesController()->registerAdminPage( array( 
		'title' => __( 'Rules', 'mwp-rules' ),
		'type' => 'menu', 
		'slug' => 'mwp-rules', 
		'menu' => __( 'Rules', 'mwp-rules' ), 
		'icon' => $plugin->fileUrl( 'assets/img/gavel.png' ), 
		'position' => 76,
	));
	
	$plugin->getLogsController()       ->registerAdminPage( array( 'type' => 'submenu', 'menu' => __( 'Rules Logs', 'mwp-rules' ), 'parent' => 'mwp-rules' ) );
	$plugin->getScheduleController()   ->registerAdminPage( array( 'type' => 'submenu', 'menu' => __( 'Scheduled Actions', 'mwp-rules' ), 'parent' => 'mwp-rules' ) );
	$plugin->getConditionsController() ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ) );
	$plugin->getActionsController()    ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ) );
	
	/* Backwards Compat */
	include_once( 'includes/backwards.functions.php' );
	
	/* Core class map */
	include_once( 'includes/rules.core.maps.php' );
	
	/* Global convenience functions */
	include_once( 'includes/rules.global.functions.php' );
	
});