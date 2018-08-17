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
	
	/**
	 * String Comparison
	 */
	public function test_system_string_comparison()
	{
		$condition = $this->plugin->getCondition( 'rules_string_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$this->assertTrue( call_user_func( $condition->callback, 'string', 'string', [], 0, array( 'rules_comparison_type' => 'equals' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, '1', 1, [], 0, array( 'rules_comparison_type' => 'equals' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string', 'STRING', [], 0, array( 'rules_comparison_type' => 'equals' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', 'string', [], 0, array( 'rules_comparison_type' => 'startswith' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', 'cheese', [], 0, array( 'rules_comparison_type' => 'startswith' ) ) );

		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', 'string', [], 0, array( 'rules_comparison_type' => 'endswith' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', 'cheese', [], 0, array( 'rules_comparison_type' => 'endswith' ) ) );

		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', 'string', [], 0, array( 'rules_comparison_type' => 'contains' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', 'cheese', [], 0, array( 'rules_comparison_type' => 'contains' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', 'cheddar', [], 0, array( 'rules_comparison_type' => 'contains' ) ) );
		
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', '', ['string','cheddar'], 0, array( 'rules_comparison_type' => 'contains_any' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', '', ['cheddar'], 0, array( 'rules_comparison_type' => 'contains_any' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', '', ['string','cheddar'], 0, array( 'rules_comparison_type' => 'contains_all' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', '', ['string','cheese'], 0, array( 'rules_comparison_type' => 'contains_all' ) ) );
		
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', '', ['string','cheddar'], 1, array( 'rules_comparison_type' => 'contains_more' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', '', ['string','cheddar'], 1, array( 'rules_comparison_type' => 'contains_exact' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 'string cheese', '', ['string','cheese'], 1, array( 'rules_comparison_type' => 'contains_exact' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 'string cheese', '', ['string','cheese'], 1, array( 'rules_comparison_type' => 'contains_more' ) ) );
	}
	
	/**
	 * Array Attributes
	 */
	public function test_system_array_attributes()
	{
		$condition = $this->plugin->getCondition( 'rules_array_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$array = array(
			'one' => 1,
			'two' => 2,
			'twenty-two' => 22,			
		);
	
		$this->assertTrue( call_user_func( $condition->callback, $array, 'one', array( 'rules_comparison_type' => 'containskey' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $array, 'zero', array( 'rules_comparison_type' => 'containskey' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $array, 1, array( 'rules_comparison_type' => 'containsvalue' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $array, 0, array( 'rules_comparison_type' => 'containsvalue' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $array, 22, array( 'rules_comparison_type' => 'keyhasvalue', 'rules_array_key' => 'twenty-two' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $array, 2, array( 'rules_comparison_type' => 'keyhasvalue', 'rules_array_key' => 'twenty-two' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $array, 2, array( 'rules_comparison_type' => 'lengthgreater' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $array, 4, array( 'rules_comparison_type' => 'lengthgreater' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $array, 4, array( 'rules_comparison_type' => 'lengthless' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $array, 2, array( 'rules_comparison_type' => 'lengthless' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $array, 3, array( 'rules_comparison_type' => 'lengthequal' ) ) );
	}

	/**
	 * Object Comparison
	 */
	public function test_system_objects_compare()
	{
		$condition = $this->plugin->getCondition( 'rules_object_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );

		$this->assertTrue( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\BaseDefinition', array( 'rules_comparison_type' => 'isa' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\Condition', array( 'rules_comparison_type' => 'isa' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\Action', array( 'rules_comparison_type' => 'isa' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\BaseDefinition', array( 'rules_comparison_type' => 'isclass' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\Condition', array( 'rules_comparison_type' => 'isclass' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $condition, new \MWP\Rules\ECA\Condition([]), array( 'rules_comparison_type' => 'isclass' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\BaseDefinition', array( 'rules_comparison_type' => 'issubclass' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $condition, 'MWP\Rules\ECA\Condition', array( 'rules_comparison_type' => 'issubclass' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $condition, new \MWP\Rules\ECA\Condition([]), array( 'rules_comparison_type' => 'issubclass' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $condition, $condition, array( 'rules_comparison_type' => 'equal' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $condition, new \MWP\Rules\ECA\Condition([]), array( 'rules_comparison_type' => 'equal' ) ) );
	}
	
	/**
	 * Time Comparison
	 */
	public function test_system_dates_compare()
	{
		$condition = $this->plugin->getCondition( 'rules_time_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$date = new \DateTime();
		$date2 = clone $date;
		$date3 = clone $date;
		$date4 = clone $date;
	
		$this->assertTrue( call_user_func( $condition->callback, $date, $date2->add(new \DateInterval('P1D')), array( 'rules_comparison_type' => '<' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $date, $date2, array( 'rules_comparison_type' => '>' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $date, $date2->sub( new \DateInterval('P1D') ), array( 'rules_comparison_type' => '=' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $date, $date2->sub( new \DateInterval('P1D') ), array( 'rules_comparison_type' => '=' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, $date, $date3->add( new \DateInterval('P1Y1M1DT1H') ), array( 'rules_comparison_type' => '?', 'compare_minutes' => 1, 'compare_hours' => 1, 'compare_days' => 6, 'compare_months' => 1, 'compare_years' => 1 ) ) );
		$this->assertFalse( call_user_func( $condition->callback, $date, $date4->add( new \DateInterval('P1Y1M2DT1H2M') ), array( 'rules_comparison_type' => '?', 'compare_minutes' => 1, 'compare_hours' => 1, 'compare_days' => 0, 'compare_months' => 1, 'compare_years' => 1 ) ) );
		
	}
	
	/**
	 * Data Type
	 */
	public function test_system_data_type_compare()
	{
		$condition = $this->plugin->getCondition( 'rules_data_type_comparison' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$this->assertTrue( call_user_func( $condition->callback, true, array( 'rules_comparison_type' => 'boolean' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, false, array( 'rules_comparison_type' => 'boolean' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, '', array( 'rules_comparison_type' => 'string' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 0, array( 'rules_comparison_type' => 'integer' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, 1.5, array( 'rules_comparison_type' => 'double' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, 1, array( 'rules_comparison_type' => 'double' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, array(), array( 'rules_comparison_type' => 'array' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, new stdClass, array( 'rules_comparison_type' => 'array' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, new stdClass, array( 'rules_comparison_type' => 'object' ) ) );
		$this->assertTrue( call_user_func( $condition->callback, null, array( 'rules_comparison_type' => 'NULL' ) ) );
		$this->assertFalse( call_user_func( $condition->callback, '', array( 'rules_comparison_type' => 'NULL' ) ) );	
	}
	
	/**
	 * Scheduled Action
	 */
	public function test_system_scheduled_action()
	{
		$condition = $this->plugin->getCondition( 'rules_check_scheduled_action' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		
		$scheduled_action = new \MWP\Rules\ScheduledAction;
		$scheduled_action->unique_key = 'unit_test';
		$scheduled_action->save();
		
		$this->assertTrue( call_user_func( $condition->callback, 'unit_test', array() ) );
		$this->assertFalse( call_user_func( $condition->callback, 'test_unit', array() ) );
	}
	
	/**
	 * Execute PHP
	 */
	public function test_system_execute_php()
	{
		$condition = $this->plugin->getCondition( 'rules_execute_php' );
		$this->assertTrue( $condition instanceof \MWP\Rules\ECA\Condition );
		$this->assertEquals( call_user_func( $condition->callback, array( 'rules_custom_phpcode' => 'return "test success";' ), array(), $this->test_operation ), "test success" );
	}
	
}
