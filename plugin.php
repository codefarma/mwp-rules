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
			
			/* Connect annotated resources to wordpress core */
			$framework = \Modern\Wordpress\Framework::instance()
				->attach( $plugin )
				->attach( $settings )
				
				->attach( new \MWP\Rules\Events\Content )
				->attach( new \MWP\Rules\Events\System )
				
				->attach( new \MWP\Rules\Conditions\Content )
				->attach( new \MWP\Rules\Conditions\System )
				
				->attach( new \MWP\Rules\Actions\Content )
				->attach( new \MWP\Rules\Actions\System )
				;
				
			$rulesController = new \MWP\Rules\Controllers\Rules( array
			(
				'where' => array( 'rule_parent_id=0' ),
				'columns' => array(
					'rule_title'      => __( 'Rule Title', 'mwp-rules' ),
					'rule_event_hook' => __( 'Event', 'mwp-rules' ),
				),
				'searchable' => array(
					'rule_title' => array( 'type' => 'contains', 'combine_words' => 'and' ),
				),
				'handlers' => array(
					'rule_event_hook' => function( $record ) use ( $plugin ) {
						$event = $plugin->getEvent( $record['rule_event_type'], $record['rule_event_hook'] );
						if ( ! $event ) {
							return 'Undescribed ' . $record['rule_event_type'] . ': ' . $record['rule_event_hook'];
						}
						
						return $event->title . '<br>' . $event->description;
					},
				),
				'adminPage' => array(
					'type' => 'management',
				),
				'formImplementation' => 'piklist',
			));
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

