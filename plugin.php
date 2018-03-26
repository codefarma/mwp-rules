<?php
/**
 * Plugin Name: MWP Rules
 * Plugin URI: https://www.codefarma.com/rules
 * Description: An automation rules engine for WordPress
 * Author: Kevin Carwile
 * Author URI: https://www.codefarma.com
 * Version: 0.9.2
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( class_exists( 'MWPRulesPlugin' ) ) {
	return;
}

use MWP\Framework\Framework;
use MWP\Rules\Plugin;
use MWP\Rules\Settings;
use MWP\Rules\AjaxHandlers;
use MWP\Rules\Action;
use MWP\Rules\Condition;
use MWP\Rules\Rule;
use MWP\Rules\Log as RuleLog;
use MWP\Rules\ScheduledAction;
use MWP\Rules\CustomAction;

use MWP\Rules\Events\System as SystemEvents;
use MWP\Rules\Events\Content as ContentEvents;
use MWP\Rules\Events\Users as UserEvents;

use MWP\Rules\Conditions\System as SystemConditions;
use MWP\Rules\Conditions\Content as ContentConditions;

use MWP\Rules\Actions\System as SystemActions;
use MWP\Rules\Actions\Content as ContentActions;

use MWP\Rules\Controllers\RulesController;
use MWP\Rules\Controllers\ConditionsController;
use MWP\Rules\Controllers\ActionsController;
use MWP\Rules\Controllers\LogsController;
use MWP\Rules\Controllers\ScheduleController;

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
		
		->attach( new SystemEvents )
		->attach( new ContentEvents )
		->attach( new UserEvents )
		
		->attach( new SystemConditions )
		->attach( new ContentConditions )
		
		->attach( new SystemActions )
		->attach( new ContentActions )
		;
	
	/* Assign our specialized controller classes */
	Rule            ::setControllerClass( RulesController::class );
	Action          ::setControllerClass( ActionsController::class );
	Condition       ::setControllerClass( ConditionsController::class );
	RuleLog         ::setControllerClass( LogsController::class );
	ScheduledAction ::setControllerClass( ScheduleController::class );
	
	/* Register our admin pages */
	Rule            ::createController('admin') ->registerAdminPage([
		'title' => __( 'Rules', 'mwp-rules' ),
		'type' => 'menu', 
		'slug' => 'mwp-rules', 
		'menu' => __( 'Rules', 'mwp-rules' ), 
		'icon' => Plugin::instance()->fileUrl( 'assets/img/gavel.png' ), 
		'position' => 76,
	]);
	CustomAction    ::createController('admin') ->registerAdminPage([ 'type' => 'submenu', 'menu' => __( 'Custom Actions', 'mwp-rules' ), 'parent' => 'mwp-rules' ]);
	RuleLog         ::createController('admin') ->registerAdminPage([ 'type' => 'submenu', 'menu' => __( 'Rules Logs', 'mwp-rules' ), 'parent' => 'mwp-rules' ]);
	ScheduledAction ::createController('admin') ->registerAdminPage([ 'type' => 'submenu', 'menu' => __( 'Scheduled Actions', 'mwp-rules' ), 'parent' => 'mwp-rules' ]);
	Condition       ::createController('admin') ->registerAdminPage([ 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ]);
	Action          ::createController('admin') ->registerAdminPage([ 'type' => 'submenu', 'parent_slug' => 'mwp-rules' ]);
	
	/* Global functions */
	include_once( 'includes/rules.core.functions.php' );
	
	/* Backwards compatibility with older WordPress versions */
	include_once( 'includes/rules.compat.functions.php' );
	
	/* Core class map */
	include_once( 'includes/rules.core.maps.php' );
	
});

