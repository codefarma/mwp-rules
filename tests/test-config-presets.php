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
		parent::__construct();
		
		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_presets' );
		
		$rule = new \MWP\Rules\Rule;
		$rule->event_type = 'action';
		$rule->event_hook = 'unit_test_presets';
		
		$action = new \MWP\Rules\Action;
		$action->rule = $rule;
		
		$this->test_operation = $action;
	}

	/**
	 * Text
	 */
	public function test_config_preset_text()
	{
		$config = $this->plugin->configPreset( 'text', 'text_string' );
		$this->assertEquals( call_user_func( $config['getArg'], array( 'text_string' => "test was successful." ), array(), $this->test_operation ), "test was successful." );
	}
	
	/**
	 * Textarea
	 */
	public function test_config_preset_textarea()
	{
		$config = $this->plugin->configPreset( 'textarea', 'text_string' );
		$this->assertEquals( call_user_func( $config['getArg'], array( 'text_string' => "test was successful." ), array(), $this->test_operation ), "test was successful." );
	}
	
	/**
	 * Time
	 */
	public function test_config_preset_datetime()
	{
		$timestamp = time() - 10000;
		$config = $this->plugin->configPreset( 'datetime', 'timestamp' );
		$date = call_user_func( $config['getArg'], array( 'timestamp' => $timestamp ), array(), $this->test_operation );
		$this->assertTrue( $date instanceof \DateTime );
		$this->assertEquals( $date->getTimestamp(), $timestamp );
	}
	
	/**
	 * Users
	 */
	public function test_config_preset_users()
	{
		/* Test multiple users */
		$config = $this->plugin->configPreset( 'users', 'users_string' );
		$users = call_user_func( $config['getArg'], array( 'users_string' => "id: 1\nid:1\nid: 100\n" ), array(), $this->test_operation );
		
		$this->assertTrue( count( $users ) == 2 );
		$this->assertTrue( $users[0] instanceof \WP_User );
		$this->assertTrue( $users[1] instanceof \WP_User );
		$this->assertTrue( $users[0]->ID == $users[1]->ID );
		
		/* Test individual user getter */
		$config = $this->plugin->configPreset( 'user', 'user_string' );
		$user = call_user_func( $config['getArg'], array( 'user_string' => "id: 1" ), array(), $this->test_operation );
		$this->assertTrue( $user instanceof \WP_User );
		$this->assertTrue( $user->ID == $users[0]->ID );
	}
	
	/**
	 * Posts
	 */
	public function test_config_preset_posts()
	{
		$post1_id = wp_insert_post( array( 'post_title' => 'Test1' ) );
		$post2_id = wp_insert_post( array( 'post_title' => 'Test2' ) );
		
		/* Test multiple posts */
		$config = $this->plugin->configPreset( 'posts', 'selection_string' );
		$selections = call_user_func( $config['getArg'], array( 'selection_string' => "id: {$post1_id}\nid:{$post2_id}\nid: 100\n" ), array(), $this->test_operation );
		
		$this->assertTrue( count( $selections ) == 2 );
		$this->assertTrue( $selections[0] instanceof \WP_Post );
		$this->assertTrue( $selections[1] instanceof \WP_Post );
		$this->assertTrue( $selections[0]->ID == $post1_id );
		$this->assertTrue( $selections[1]->ID == $post2_id );
		
		/* Test individual post getter */
		$config = $this->plugin->configPreset( 'post', 'selection_string' );
		$selection = call_user_func( $config['getArg'], array( 'selection_string' => "id: {$post1_id}" ), array(), $this->test_operation );
		$this->assertTrue( $selection instanceof \WP_Post );
		$this->assertTrue( $selection->ID == $post1_id );
	}
	
	/**
	 * Comments
	 */
	public function test_config_preset_comments()
	{
		$post_id = wp_insert_post( array( 'post_title' => 'Test For Comments' ) );
		$comment1_id = wp_insert_comment( array( 'comment_content' => 'Test1', 'comment_post_ID' => $post_id ) );
		$comment2_id = wp_insert_comment( array( 'comment_content' => 'Test2', 'comment_post_ID' => $post_id ) );
		
		/* Test multiple comments */
		$config = $this->plugin->configPreset( 'comments', 'selection_string' );
		$selections = call_user_func( $config['getArg'], array( 'selection_string' => "id: {$comment1_id}\nid:{$comment2_id}\nid: 100\n" ), array(), $this->test_operation );
		
		$this->assertTrue( count( $selections ) == 2 );
		$this->assertTrue( $selections[0] instanceof \WP_Comment );
		$this->assertTrue( $selections[1] instanceof \WP_Comment );
		$this->assertTrue( $selections[0]->comment_ID == $comment1_id );
		$this->assertTrue( $selections[1]->comment_ID == $comment2_id );
		
		/* Test individual comment getter */
		$config = $this->plugin->configPreset( 'comment', 'selection_string' );
		$selection = call_user_func( $config['getArg'], array( 'selection_string' => "id: {$comment1_id}" ), array(), $this->test_operation );
		$this->assertTrue( $selection instanceof \WP_Comment );
		$this->assertTrue( $selection->comment_ID == $comment1_id );
	}
	
	/**
	 * Taxonomy Term / Terms
	 */
	public function test_config_preset_terms()
	{
		wp_insert_term( 'Unit Test Term', 'category', array( 'slug' => 'unit-test-term' ) );
		
		/* Test multiple terms getter */
		$config = $this->plugin->configPreset( 'terms', 'terms_string' );
		$terms = call_user_func( $config['getArg'], array( 'terms_string' => "slug: category/unit-test-term\nname: category/Unit Test Term\nid: 1\nslug: category/missing" ), array(), $this->test_operation );
		
		$this->assertTrue( count( $terms ) == 3 );
		$this->assertTrue( $terms[0] instanceof \WP_Term );
		$this->assertTrue( $terms[1] instanceof \WP_Term );
		$this->assertTrue( $terms[2] instanceof \WP_Term );
		$this->assertTrue( $terms[0]->term_id == $terms[1]->term_id );
		
		/* Test individual term getter */
		$config = $this->plugin->configPreset( 'term', 'term_string' );
		$term = call_user_func( $config['getArg'], array( 'term_string' => "slug: category/unit-test-term" ), array(), $this->test_operation );
		$this->assertTrue( $term instanceof \WP_Term );
		$this->assertTrue( $term->term_id == $terms[0]->term_id );
		
		$term = call_user_func( $config['getArg'], array( 'term_string' => "name: category/Unit Test Term" ), array(), $this->test_operation );
		$this->assertTrue( $term instanceof \WP_Term );
		$this->assertTrue( $term->term_id == $terms[1]->term_id );
	}
	
	/**
	 * Meta Values
	 */
	public function test_config_preset_meta()
	{
		$config = $this->plugin->configPreset( 'meta_values', 'meta_values' );
		$meta = call_user_func( $config['getArg'], array( 'meta_values' => " one: Value 1 \nTwo:Value2\nval-three: 3\n" ), array(), $this->test_operation );
		$this->assertEquals( $meta, array( 'one' => 'Value 1', 'Two' => 'Value2', 'val-three' => '3' ) );
	}
	
}
