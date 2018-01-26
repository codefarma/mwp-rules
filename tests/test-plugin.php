<?php
/**
 * Testing Class
 *
 * To set up testing for your wordpress plugin:
 *
 * @see: http://wp-cli.org/docs/plugin-unit-tests/
 *
 * @package MWP Rules
 */
if ( ! class_exists( 'WP_UnitTestCase' ) )
{
	die( 'Access denied.' );
}

/**
 * Tests
 */
class MWPRulesPluginTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public function __construct()
	{
		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_action' );
	}
	
	/**
	 * Test that the plugin is a modern wordpress plugin
	 */
	public function test_plugin_class() 
	{
		// Check that the plugin is a subclass of Modern\Wordpress\Plugin 
		$this->assertTrue( $this->plugin instanceof \Modern\Wordpress\Plugin );
	}
}
