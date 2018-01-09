<?php
/**
 * Plugin Name: MWP Rules
 * Plugin URI: 
 * Description: A rules engine for wordpress
 * Author: Kevin Carwile
 * Author URI: http://millermedia.io
 * Depends: lib-modern-framework
 * Version: 0.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( ! class_exists( 'MWPRulesPlugin' ) )
{
	class MWPRulesPlugin
	{
		public static function init()
		{
			/* Plugin Core */
			$plugin	= \MWP\Rules\Plugin::instance();
			$plugin->setPath( rtrim( plugin_dir_path( __FILE__ ), '/' ) );
			
			/* Plugin Settings */
			$settings = \MWP\Rules\Settings::instance();
			$plugin->addSettings( $settings );
			
			$ajaxHandlers = \MWP\Rules\AjaxHandlers::instance();
			
			/* Connect annotated resources to wordpress core */
			$framework = \Modern\Wordpress\Framework::instance()
				->attach( $plugin )
				->attach( $settings )
				->attach( $ajaxHandlers )
				
				->attach( new \MWP\Rules\Events\Content )
				->attach( new \MWP\Rules\Events\System )
				
				->attach( new \MWP\Rules\Conditions\Content )
				->attach( new \MWP\Rules\Conditions\System )
				
				->attach( new \MWP\Rules\Actions\Content )
				->attach( new \MWP\Rules\Actions\System )
				;
				
			$plugin->getRulesController()      ->registerAdminPage( array( 'type' => 'management' ) );
			$plugin->getLogsController()       ->registerAdminPage( array( 'type' => 'management', 'menu' => __( 'Rules Logs', 'mwp-rules' ) ) );
			$plugin->getConditionsController() ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules-rule' ) );
			$plugin->getActionsController()    ->registerAdminPage( array( 'type' => 'submenu', 'parent_slug' => 'mwp-rules-rule' ) );
		}
		
		public static function status() {
			if ( ! class_exists( 'ModernWordpressFramework' ) ) {
				echo '<td colspan="3" class="plugin-update colspanchange">
						<div class="update-message notice inline notice-error notice-alt">
							<p><strong style="color:red">INOPERABLE.</strong> Please activate <a href="' . admin_url( 'plugins.php?page=tgmpa-install-plugins' ) . '"><strong>Modern Framework for Wordpress</strong></a> to enable the operation of this plugin.</p>
						</div>
					  </td>';
			}
		}
	}
	
	/* Autoload Classes */
	require_once 'vendor/autoload.php';
	
	/* Bundled Framework */
	if ( file_exists( __DIR__ . '/framework/plugin.php' ) ) {
		include_once 'framework/plugin.php';
	}

	/* Register plugin dependencies */
	include_once 'includes/plugin-dependency-config.php';
	
	/* Register plugin status notice */
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( 'MWPRulesPlugin', 'status' ) );
	
	/**
	 * Global Functions 
	 */
	
	function rules_event( $type, $hook, $definition ) {
		\MWP\Rules\Plugin::instance()->describeEvent( $type, $hook, $definition );
	}
	
	function rules_events( $events ) {
		foreach( $events as $event ) {
			call_user_func_array( 'rules_event', $event );
		}
	}
	
	function rules_condition( $key, $definition ) {
		\MWP\Rules\Plugin::instance()->registerCondition( $key, $definition );
	}
	
	function rules_conditions( $conditions ) {
		foreach( $conditions as $condition ) {
			call_user_func_array( 'rules_condition', $condition );
		}
	}
	
	function rules_action( $key, $definition ) {
		\MWP\Rules\Plugin::instance()->defineAction( $key, $definition );
	}
	
	function rules_actions( $actions ) {
		foreach( $actions as $action ) {
			call_user_func_array( 'rules_action', $action );
		}
	}
	
	/**
	 * DO NOT REMOVE
	 *
	 * This plugin depends on the modern wordpress framework.
	 * This block ensures that it is loaded before we init.
	 */
	if ( class_exists( 'ModernWordpressFramework' ) ) {
		MWPRulesPlugin::init();
	}
	else {
		add_action( 'modern_wordpress_init', array( 'MWPRulesPlugin', 'init' ) );
	}	
}

