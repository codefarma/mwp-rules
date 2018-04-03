<?php
/**
 * Plugin Name: MWP Rules
 * Plugin URI: https://www.codefarma.com/rules
 * Description: An automation rules engine for WordPress
 * Author: Kevin Carwile
 * Author URI: https://www.codefarma.com
 * Version: 0.9.2
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( class_exists( 'MWPRulesPlugin' ) ) {
	return;
}

use MWP\Framework\Framework;
use MWP\Rules\Log as RuleLog;
use MWP\WordPress\AdminPage;

/* Autoloaders */
include_once 'includes/plugin-bootstrap.php';

/**
 * This plugin uses the MWP Application Framework to init.
 *
 * @return void
 */
add_action( 'mwp_framework_init', function() 
{
	/* Prepare Settings */
	Plugin::instance()->addSettings( Settings::instance() );
	
	/* Attach callbacks to WordPress */
	Framework::instance()
	
		->attach( Plugin::instance() )
		->attach( Settings::instance() )
		->attach( AjaxHandlers::instance() )
		
		->attach( new Events\System )
		->attach( new Events\Content )
		->attach( new Events\Users )
		
		->attach( new Conditions\System )
		->attach( new Conditions\Content )
		
		->attach( new Actions\System )
		->attach( new Actions\Content )
		;
	
	/* Load config */	
	$config = array(
		'controllers' => Plugin::instance()->getData( 'controllers', 'config' ),
	);
	
	/* Assign customized controller classes */
	Rule            ::setControllerClass( Controllers\RulesController::class );
	Action          ::setControllerClass( Controllers\ActionsController::class );
	Condition       ::setControllerClass( Controllers\ConditionsController::class );
	RuleLog         ::setControllerClass( Controllers\LogsController::class );
	ScheduledAction ::setControllerClass( Controllers\ScheduleController::class );
	Argument        ::setControllerClass( Controllers\ArgumentsController::class );
	
	/* Create controllers and admin pages */
	Rule            ::createController('admin', $config['controllers']['rules_rules']);
	App             ::createController('admin', $config['controllers']['rules_apps']);
	Feature         ::createController('admin', $config['controllers']['rules_features']);
	Condition       ::createController('admin', $config['controllers']['rules_conditions']);
	Action          ::createController('admin', $config['controllers']['rules_actions']);
	Hook            ::createController('admin', $config['controllers']['rules_hooks']);
	Argument        ::createController('admin', $config['controllers']['rules_arguments']);
	RuleLog         ::createController('admin', $config['controllers']['rules_logs']);
	ScheduledAction ::createController('admin', $config['controllers']['rules_scheduled_actions']);
	
	/* Global functions */
	include_once( 'includes/rules.core.functions.php' );
	
	/* Backwards compatibility with older WordPress versions */
	include_once( 'includes/rules.compat.functions.php' );
	
	/* Core class map */
	include_once( 'includes/rules.core.maps.php' );
	
});

