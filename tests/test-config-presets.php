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
class MWPRulesConfigPresetsTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public function __construct()
	{
		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_presets' );
	}
	
	/**
	 * Taxonomy Term / Terms
	 */
	public function test_config_preset_terms()
	{
		wp_insert_term( 'Unit Test Term', 'category', array( 'slug' => 'unit-test-term' ) );
		
		$rule = new \MWP\Rules\Rule;
		$rule->event_type = 'action';
		$rule->event_hook = 'unit_test_presets';
		
		$action = new \MWP\Rules\Action;
		$action->rule = $rule;
		
		/* Test multiple terms getter */
		$config = $this->plugin->configPreset( 'terms', 'terms_string' );
		$terms = call_user_func( $config['getArg'], array( 'terms_string' => "slug: category/unit-test-term\nname: category/Unit Test Term\nid: 1\nslug: category/missing" ), array(), $action );
		
		$this->assertTrue( count( $terms ) == 3 );
		$this->assertTrue( $terms[0] instanceof \WP_Term );
		$this->assertTrue( $terms[1] instanceof \WP_Term );
		$this->assertTrue( $terms[2] instanceof \WP_Term );
		$this->assertTrue( $terms[0]->term_id == $terms[1]->term_id );
		
		/* Test individual term getter */
		$config = $this->plugin->configPreset( 'term', 'term_string' );
		$term = call_user_func( $config['getArg'], array( 'term_string' => "slug: category/unit-test-term" ), array(), $action );
		$this->assertTrue( $term instanceof \WP_Term );
		$this->assertTrue( $term->term_id == $terms[0]->term_id );
		
		$term = call_user_func( $config['getArg'], array( 'term_string' => "name: category/Unit Test Term" ), array(), $action );
		$this->assertTrue( $term instanceof \WP_Term );
		$this->assertTrue( $term->term_id == $terms[1]->term_id );
	}
}
