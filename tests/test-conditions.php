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
class MWPRulesConditionsTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public $test_operation;
	
	public function __construct()
	{
		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_conditions' );
		
		$rule = new \MWP\Rules\Rule;
		$rule->event_type = 'action';
		$rule->event_hook = 'unit_test_conditions';
		
		$operation = new \MWP\Rules\Action;
		$operation->rule = $rule;
		
		$this->test_operation = $operation;
	}
	
	/**
	 * Truth
	 */
	public function test_system_truth_conditions()
	{
		$condition = $this->plugin->getCondition( 'rules_truth' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$this->assertTrue( call_user_func( $condition->callback, true, array( 'compare_type' => 'true' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, false, array( 'compare_type' => 'true' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 1, array( 'compare_type' => 'true' ) ) );

		$this->assertTrue( call_user_func( $condition->callback, false, array( 'compare_type' => 'false' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, true, array( 'compare_type' => 'false' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 0, array( 'compare_type' => 'false' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, true, array( 'compare_type' => 'truthy' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, false, array( 'compare_type' => 'truthy' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 1, array( 'compare_type' => 'truthy' ) ) );

		$this->assertTrue( call_user_func( $condition->callback, false, array( 'compare_type' => 'falsey' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, true, array( 'compare_type' => 'falsey' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 0, array( 'compare_type' => 'falsey' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, null, array( 'compare_type' => 'null' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, '', array( 'compare_type' => 'null' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, '', array( 'compare_type' => 'notnull' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, null, array( 'compare_type' => 'notnull' ) ) );			
	}

	/**
	 * Number Comparison
	 */
	public function test_system_number_comparison()
	{
		$condition = $this->plugin->getCondition( 'rules_number_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$this->assertTrue( call_user_func( $condition->callback, -1, 15, array( 'rules_comparison_type' => '<' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 0.9, 1.6, array( 'rules_comparison_type' => '<' ) ) );
		
		$this->assertFalse( call_user_func( $condition->callback, -1, 15, array( 'rules_comparison_type' => '>' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 0.9, 1.6, array( 'rules_comparison_type' => '>' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, 1, 1.0, array( 'rules_comparison_type' => '==' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 1, '1', array( 'rules_comparison_type' => '==' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 2, 3, array( 'rules_comparison_type' => '==' ) ) );
		
		$this->assertFalse( call_user_func( $condition->callback, 1, 1.0, array( 'rules_comparison_type' => '!=' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 1, '1', array( 'rules_comparison_type' => '!=' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 2, 3, array( 'rules_comparison_type' => '!=' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, 1, 2, array( 'rules_comparison_type' => '<=' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 1, '1', array( 'rules_comparison_type' => '<=' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 2, 1, array( 'rules_comparison_type' => '<=' ) ) );
		
		$this->assertFalse( call_user_func( $condition->callback, 1, 2, array( 'rules_comparison_type' => '>=' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 1, '1', array( 'rules_comparison_type' => '>=' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 2, 1, array( 'rules_comparison_type' => '>=' ) ) );
	}
		
}
