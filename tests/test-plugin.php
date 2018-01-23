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
 * Example plugin tests
 */
class MWPRulesPluginTest extends WP_UnitTestCase 
{
	/**
	 * Test that the plugin is a modern wordpress plugin
	 */
	public function test_plugin_class() 
	{
		$plugin = \MWP\Rules\Plugin::instance();
		
		// Check that the plugin is a subclass of Modern\Wordpress\Plugin 
		$this->assertTrue( $plugin instanceof \Modern\Wordpress\Plugin );
	}
}
