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
class MWPRulesSystemActionsTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public $test_operation;
	
	public function __construct()
	{
		parent::__construct();
		
		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_actions' );
		
		$rule = new \MWP\Rules\Rule;
		$rule->event_type = 'action';
		$rule->event_hook = 'unit_test_actions';
		
		$operation = new \MWP\Rules\Action;
		$operation->rule = $rule;
		
		$this->test_operation = $operation;
	}
	
	/**
	 * Update Meta Data
	 */
	public function test_update_meta_data()
	{
		$action = $this->plugin->getAction( 'rules_update_metadata' );
		$this->assertTrue( $action instanceof \MWP\Rules\ECA\Action );
		
		$values = array(
			'rules_meta_key' => 'test_success',
			'rules_meta_value' => '100',
			'rules_meta_array_unique' => '',
		);
		
		$arg_map = array();
		
		$meta_key = call_user_func( $action->arguments['meta_key']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $meta_key, 'test_success' );
		
		$meta_value = call_user_func( $action->arguments['meta_value']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $meta_value, '100' );
		
		$user_id = 1;
		$term_id = 1;
		$post_id = wp_insert_post( array( 'post_title' => 'Test Update Meta Data' ) );
		$comment_id = wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_content' => 'Hello.' ) );
		
		$this->assertTrue( $comment_id > 0 );
		$this->assertTrue( $post_id > 0 );
		
		$user = get_user_by( 'id', $user_id );
		$post = get_post( $post_id );
		$comment = get_comment( $comment_id );
		$term = get_term( $term_id );
		
		$objects = array(
			'user' => $user,
			'post' => $post,
			'comment' => $comment,
			'term' => $term,
		);
		
		$object_ids = array(
			'user' => 1,
			'post' => $post_id,
			'comment' => $comment_id,
			'term' => 1,
		);
		
		$values['rules_meta_update_method'] = 'append_string';
		$result = call_user_func( $action->callback, $post, '_test_string', 'unit', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_string', true ), 'unit' );
		
		$values['rules_meta_update_method'] = 'prepend_string';
		$result = call_user_func( $action->callback, $post, '_test_string', 'test', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_string', true ), 'testunit' );
		
		$values['rules_meta_update_method'] = 'append_string';
		$result = call_user_func( $action->callback, $post, '_test_string', 'test', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_string', true ), 'testunittest' );
		
		$values['rules_meta_update_method'] = 'remove_string';
		$result = call_user_func( $action->callback, $post, '_test_string', 'test', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_string', true ), 'unit' );
		
		$values['rules_meta_update_method'] = 'append_array';
		$result = call_user_func( $action->callback, $post, '_test_string', 'test', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_string', true ), array( 'unit', 'test' ) );
		
		$values['rules_meta_update_method'] = 'append_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'unit test', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'unit test' ) );
		
		$values['rules_meta_update_method'] = 'prepend_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'successful', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'successful', 'unit test' ) );
		
		$values['rules_meta_update_method'] = 'append_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'failure', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'successful', 'unit test', 'failure' ) );
		
		$values['rules_meta_update_method'] = 'remove_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'failure', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'successful', 'unit test' ) );
		
		$values['rules_meta_update_method'] = 'append_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'successful', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'successful', 'unit test', 'successful' ) );
		
		$values['rules_meta_array_unique'] = 1;
		$values['rules_meta_update_method'] = 'append_array';
		$result = call_user_func( $action->callback, $post, '_test_array', 'successful', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_array', true ), array( 'successful', 'unit test' ) );
		
		$values['rules_meta_update_method'] = 'add';
		$result = call_user_func( $action->callback, $post, '_test_math', '7', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_math', true ), '7' );
		
		$values['rules_meta_update_method'] = 'add';
		$result = call_user_func( $action->callback, $post, '_test_math', '3', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_math', true ), '10' );
		
		$values['rules_meta_update_method'] = 'subtract';
		$result = call_user_func( $action->callback, $post, '_test_math', '5', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_math', true ), '5' );
		
		$values['rules_meta_update_method'] = 'multiply';
		$result = call_user_func( $action->callback, $post, '_test_math', '3', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_math', true ), '15' );
		
		$values['rules_meta_update_method'] = 'divide';
		$result = call_user_func( $action->callback, $post, '_test_math', '5', $values, $arg_map, $this->test_operation );
		$this->assertEquals( get_post_meta( $post->ID, '_test_math', true ), '3' );
		
		$values['rules_meta_update_method'] = 'explicit';
		$result = call_user_func( $action->callback, $objects, '_test_array', 'done.', $values, $arg_map, $this->test_operation );
		$this->assertEquals( 'done.', get_post_meta( $post_id, '_test_array', true ) );
		$this->assertEquals( 'done.', get_user_meta( $user_id, '_test_array', true ) );
		$this->assertEquals( 'done.', get_comment_meta( $comment_id, '_test_array', true ) );
		$this->assertEquals( 'done.', get_term_meta( $term_id, '_test_array', true ) );
	}
	
	/**
	 * Execute PHP
	 */
	public function test_system_execute_php()
	{
		$action = $this->plugin->getAction( 'rules_execute_php' );
		$this->assertTrue( $action instanceof \MWP\Rules\ECA\Action );
		$this->assertEquals( call_user_func( $action->callback, array( 'rules_custom_phpcode' => 'return "test success";' ), array(), $this->test_operation ), "test success" );
	}
	
}
