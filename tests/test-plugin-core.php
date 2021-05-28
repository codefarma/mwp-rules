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

use \MWP\Rules\ECA\Token;

/**
 * Tests
 */
class MWPRulesPluginTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public function __construct()
	{
		parent::__construct();

		error_reporting( E_ALL & ~E_NOTICE );
		
		$this->plugin = \MWP\Rules\Plugin::instance();
		$this->plugin->describeEvent( 'action', 'unit_test_action' );
	}
	
	/**
	 * Test that the plugin is a modern wordpress plugin
	 */
	public function test_plugin_class() 
	{
		// Check that the plugin is a subclass of MWP\Framework\Plugin 
		$this->assertTrue( $this->plugin instanceof \MWP\Framework\Plugin );
	}
	
	public function test_token_values()
	{
		$user_id = wp_insert_user( array(
			'user_login' => 'unit_testing',
			'user_url' => 'http://test.domain.com',
			'user_pass' => 'password',
			'user_nicename' => 'unit-testing',
			'user_email' => 'test@test.domain.com',
			'display_name' => 'Unit Testing',
			'nickname' => 'Testy Two Lips',
			'first_name' => 'Unit',
			'last_name' => 'Testy',
			'description' => 'Hi, my name is...',
			'user_registered' => date('Y-m-d H:i:s'),
			'role' => 'editor',
		));
		
		$user = get_user_by( 'id', $user_id );
		
		$post_id = wp_insert_post( array(
			'post_title' => 'I am mighty test. Heed my words.',
			'post_content' => 'Live long and prosper',
			'post_password' => 'password',
			'post_excerpt' => 'Be well',
			'post_author' => $user_id,
			'post_status' => 'publish',
			'meta_input' => array(
				'test' => 'pass',
			),
		));
		
		$child_post_id = wp_insert_post( array(
			'post_type' => 'revision',
			'post_parent' => $post_id,
			'post_author' => $user_id,
			'post_status' => 'draft',
		));
		
		$comment_id = wp_insert_comment( array(
			'user_id' => $user_id,
			'comment_author' => 'Charcoal Teapot',
			'comment_author_email' => 'charcoal@tea.pot',
			'comment_author_IP' => '0.0.0.0',
			'comment_author_url' => 'http://url.net',
			'comment_approved' => 1,
			'comment_content' => 'Very nice!',
			'comment_post_ID' => $post_id,
			'comment_meta' => array(
				'test_key' => 'pass_check',
			),
		));
		
		/* User Mappings */
		
		$token = Token::create( $user_id, '*', array( 'argtype' => 'int', 'class' => 'WP_User' ) );		
		$this->assertTrue( $token->getTokenValue() instanceof \WP_User );
		$this->assertTrue( $token->getTokenValue()->ID == $user_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );

		$token = Token::create( $user, 'id' );
		$this->assertTrue( $token->getTokenValue() == $user_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'int' );
		
		$token = Token::create( $user, 'login' );
		$this->assertEquals( $token->getTokenValue(), 'unit_testing' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'first_name' );
		$this->assertEquals( $token->getTokenValue(), 'Unit' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_name' );
		$this->assertEquals( $token->getTokenValue(), 'Testy' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'nicename' );
		$this->assertEquals( $token->getTokenValue(), 'unit-testing' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'email' );
		$this->assertEquals( $token->getTokenValue(), 'test@test.domain.com' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'website_url' );
		$this->assertEquals( $token->getTokenValue(), 'http://test.domain.com' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $user, 'website_url:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \MWP\Rules\WP\Url );
		$this->assertEquals( (string) $token->getTokenValue(), 'http://test.domain.com' );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'posts_url:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \MWP\Rules\WP\Url );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'registered' );
		$this->assertTrue( $token->getTokenValue() instanceof \DateTime );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
	
		$token = Token::create( $user, 'capabilities' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'roles' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( in_array( 'editor', $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'meta' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		/* Post Mappings */
		
		$token = Token::create( $user, 'last_post' );
		$last_post = $token->getTokenValue();
		$this->assertTrue( $last_post instanceof \WP_Post );
		$this->assertTrue( $last_post->ID == $post_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
	
		$token = Token::create( $user, 'last_post:id' );
		$this->assertTrue( $token->getTokenValue() == $last_post->ID );
		$this->assertEquals( $token->getArgument()['argtype'], 'int' );
		
		$token = Token::create( $user, 'last_post:author' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_User );
		$this->assertTrue( $token->getTokenValue()->ID == $user->ID );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:slug' );
		$this->assertTrue( $token->getTokenValue() != '' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $user, 'last_post:url:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \MWP\Rules\WP\Url );
		$this->assertEquals( (string) $token->getTokenValue(), get_permalink( $last_post ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:type' );
		$this->assertEquals( $token->getTokenValue(), 'post' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $user, 'last_post:title' );
		$this->assertEquals( $token->getTokenValue(), 'I am mighty test. Heed my words.' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:created' );
		$this->assertTrue( $token->getTokenValue() instanceof \DateTime );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:modified' );
		$this->assertTrue( $token->getTokenValue() instanceof \DateTime );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:content' );
		$this->assertTrue( strstr( $token->getTokenValue(), 'Live long and prosper' ) !== FALSE );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:excerpt' );
		$this->assertTrue( strstr( $token->getTokenValue(), 'Be well' ) !== FALSE );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:status' );
		$this->assertEquals( $token->getTokenValue(), 'publish' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:comment_status' );
		$this->assertEquals( $token->getTokenValue(), 'open' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:ping_status' );
		$this->assertEquals( $token->getTokenValue(), 'open' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:password' );
		$this->assertEquals( $token->getTokenValue(), 'password' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $child_post_id, 'parent', array( 'class' => 'WP_Post' ) );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Post );
		$this->assertEquals( $token->getTokenValue()->ID, $post_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:meta' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getTokenValue()['test'], 'pass' );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'last_post:meta[test]' );
		$this->assertTrue( is_string( $token->getTokenValue() ) );
		$this->assertEquals( $token->getTokenValue(), 'pass' );
		$this->assertEquals( $token->getArgument()['argtype'], 'mixed' );

		$token = Token::create( $user, 'last_post:comments' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( count( $token->getTokenValue() ) == 1 );
		$this->assertTrue( $token->getTokenValue()[0] instanceof \WP_Comment );
		$this->assertTrue( $token->getTokenValue()[0]->comment_ID == $comment_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'last_post:comments[0]' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Comment );
		$this->assertTrue( $token->getTokenValue()->comment_ID == $comment_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:comments:post' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );

		$token = Token::create( $user, 'last_post:comments[0]:post' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Post );
		$this->assertEquals( $token->getTokenValue()->ID, $post_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:taxonomies' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( $token->getTokenValue()[0] instanceof \WP_Taxonomy );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'last_post:terms' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( $token->getTokenValue()[0] instanceof \WP_Term );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		/* Post Type Mappings */
		
		$token = Token::create( $user, 'last_post:type:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Post_Type );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:type:name' );
		$this->assertEquals( $token->getTokenValue(), 'post' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:type:label' );
		$this->assertTrue( $token->getTokenValue() != '' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:type:labels' );
		$this->assertTrue( is_array( $token->getTokenValue() ) and count( $token->getTokenValue() ) > 0 );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $user, 'last_post:type:labels[name]' );
		$this->assertEquals( $token->getTokenValue(), 'Posts' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:type:description' );
		$token->getTokenValue();
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:type:public' );
		$this->assertTrue( is_bool( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'bool' );
		
		$token = Token::create( $user, 'last_post:type:hierarchical' );
		$this->assertTrue( is_bool( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'bool' );
		
		$token = Token::create( $user, 'last_post:type:menu_icon' );
		$token->getTokenValue();
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:type:capabilities' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		/* Comment Mappings */
		
		$token = Token::create( $user, 'last_post:comments[0]:id' );
		$this->assertTrue( $token->getTokenValue() == $comment_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'int' );
		
		$token = Token::create( $user, 'last_post:comments[0]:post' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Post );
		$this->assertTrue( $token->getTokenValue()->ID == $post_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );

		$token = Token::create( $user, 'last_post:comments[0]:type' );
		$this->assertEquals( $token->getTokenValue(), 'comment' );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $user, 'last_post:comments[0]:author' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_User );
		$this->assertTrue( $token->getTokenValue()->ID == $user->ID );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:comments[0]:created' );
		$this->assertTrue( $token->getTokenValue() instanceof \DateTime );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $user, 'last_post:comments[0]:content' );
		$this->assertTrue( strstr( $token->getTokenValue(), 'Very nice!' ) !== FALSE );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$child_comment_id = wp_insert_comment( array(
			'user_id' => 0,
			'comment_parent' => $comment_id,
			'comment_author' => 'Charcoal Teapot',
			'comment_author_email' => 'charcoal@tea.pot',
			'comment_author_IP' => '0.0.0.0',
			'comment_author_url' => 'http://url.net',
			'comment_approved' => 1,
			'comment_content' => 'Nice again!',
			'comment_post_ID' => $post_id,
		));

		$comment = get_comment( $comment_id );
		$child_comment = get_comment( $child_comment_id );
		
		$token = Token::create( $child_comment, 'url:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \MWP\Rules\WP\Url );		
		$this->assertEquals( (string) $token->getTokenValue(), get_comment_link( $child_comment ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $child_comment, 'parent' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Comment );
		$this->assertEquals( $token->getTokenValue()->comment_ID, $comment_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $comment, 'children' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( $token->getTokenValue()[0] instanceof \WP_Comment );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $comment, 'children[0]' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Comment );
		$this->assertTrue( $token->getTokenValue()->comment_ID == $child_comment_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $comment, 'meta' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getTokenValue()['test_key'], 'pass_check' );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
		$token = Token::create( $comment, 'meta[test_key]' );
		$this->assertTrue( is_string( $token->getTokenValue() ) );
		$this->assertEquals( $token->getTokenValue(), 'pass_check' );
		$this->assertEquals( $token->getArgument()['argtype'], 'mixed' );
		
		/* Taxonomy Mappings */
		
		$token = Token::create( $user, 'last_post:taxonomies[category]' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Taxonomy );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$taxonomy = $token->getTokenValue();
		
		$token = Token::create( $taxonomy, 'name' );
		$this->assertEquals( $token->getTokenValue(), $taxonomy->name );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $taxonomy, 'label' );
		$this->assertEquals( $token->getTokenValue(), $taxonomy->label );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $taxonomy, 'labels' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );

		$token = Token::create( $taxonomy, 'description' );
		$this->assertEquals( $token->getTokenValue(), $taxonomy->description );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );

		$token = Token::create( $taxonomy, 'public' );
		$this->assertTrue( is_bool( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'bool' );

		$token = Token::create( $taxonomy, 'capabilities' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );

		$token = Token::create( $taxonomy, 'terms' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertTrue( $token->getTokenValue()[0] instanceof \WP_Term );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );

		/* Term Mappings */
		
		$term = $token->getTokenValue()[0];
		
		$inserted = wp_insert_term( 'Test Category', $term->taxonomy, array(
			'parent' => $term->term_id,
		));
		
		$new_term = get_term( $inserted['term_id'] );
		
		$token = Token::create( $term, 'id' );
		$this->assertEquals( $token->getTokenValue(), $term->term_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'int' );
		
		$token = Token::create( $term, 'name' );
		$this->assertEquals( $token->getTokenValue(), $term->name );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $term, 'description' );
		$this->assertTrue( is_string( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $term, 'slug' );
		$this->assertEquals( $token->getTokenValue(), $term->slug );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $term, 'url:*' );
		$this->assertTrue( $token->getTokenValue() instanceof \MWP\Rules\WP\Url );		
		$this->assertEquals( (string) $token->getTokenValue(), get_term_link( $term ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $term, 'taxonomy' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Taxonomy );
		$this->assertEquals( $token->getTokenValue()->name, $taxonomy->name );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $new_term, 'parent' );
		$this->assertTrue( $token->getTokenValue() instanceof \WP_Term );
		$this->assertEquals( $token->getTokenValue()->term_id, $term->term_id );
		$this->assertEquals( $token->getArgument()['argtype'], 'object' );
		
		$token = Token::create( $term, 'count' );
		$this->assertTrue( is_numeric( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'int' );
		
		$token = Token::create( $term, 'filter' );
		$this->assertEquals( $token->getTokenValue(), $term->filter );
		$this->assertEquals( $token->getArgument()['argtype'], 'string' );
		
		$token = Token::create( $term, 'meta' );
		$this->assertTrue( is_array( $token->getTokenValue() ) );
		$this->assertEquals( $token->getArgument()['argtype'], 'array' );
		
	}
	
	public function test_derivatives()
	{
		$user_int = array( 'argtype' => 'int', 'class' => 'WP_User' );
		$user_object = array( 'argtype' => 'object', 'class' => 'WP_User' );
		
		$reflection = Token::getReflection( $user_int, '*' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'object' );
		
		$reflection = Token::getReflection( $user_object, '' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'object' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments[0-9]' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'object' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments[0-9]:meta' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments[0-9]:meta[a-z]' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'mixed' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments:meta[a-z]' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );

		$reflection = Token::getReflection( $user_object, 'last_post:comments:post' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );
		$this->assertEquals( $reflection['final_argument']['class'], 'WP_Post' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments:post:author' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );
		$this->assertEquals( $reflection['final_argument']['class'], 'WP_User' );
		
		$reflection = Token::getReflection( $user_object, 'last_post:comments:post:author:meta[a-z]' );
		$this->assertEquals( $reflection['final_argument']['argtype'], 'array' );
		
	}
	
	public function test_argument_compliance()
	{
		$core = $this->plugin;
		
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'int' ), null ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'int' ), array( 'argtypes' => array( 'int' ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'int' ), array( 'argtypes' => array( 'int' => array() ) ) ) );
		$this->assertFalse( $core->isArgumentCompliant( array( 'argtype' => 'int' ), array( 'argtypes' => array( 'object' ) ) ) );
		$this->assertFalse( $core->isArgumentCompliant( array( 'argtype' => 'int', 'class' => 'WP_User' ), array( 'argtypes' => array( 'object' => array( 'classes' => array( 'WP_User' ) ) ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'object', 'class' => 'WP_User' ), array( 'argtypes' => array( 'object' => array( 'classes' => array( 'WP_User' ) ) ) ) ) );
		$this->assertFalse( $core->isArgumentCompliant( array( 'argtype' => 'object', 'class' => 'WP_User' ), array( 'argtypes' => array( 'object' => array( 'classes' => array( 'WP_Post' ) ) ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'object', 'class' => 'WP_User' ), array( 'argtypes' => array( 'object' => array( 'classes' => array( 'WP_Post', 'WP_User' ) ) ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'object', 'class' => 'MWP\Rules\ECA\Action' ), array( 'argtypes' => array( 'object' => array( 'classes' => array( 'MWP\Rules\ECA\BaseDefinition' ) ) ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'int' ), array( 'argtypes' => array( 'mixed' ) ) ) );
		$this->assertFalse( $core->isArgumentCompliant( array( 'argtype' => 'mixed' ), array( 'argtypes' => array( 'int' ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'mixed', 'class' => 'WP_Taxonomy' ), array( 'argtypes' => array( 'mixed' ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'mixed', 'class' => 'WP_Taxonomy' ), array( 'argtypes' => array( 'mixed' => array( 'classes' => 'WP_Taxonomy' ) ) ) ) );
		$this->assertTrue( $core->isArgumentCompliant( array( 'argtype' => 'int', 'class' => 'WP_Taxonomy' ), array( 'argtypes' => array( 'mixed' => array( 'classes' => 'WP_Taxonomy' ) ) ) ) );
	}
	
	public function test_argument_storage()
	{
		$core = $this->plugin;
		
		$obj = new stdClass;
		$obj->name = 'Test Object';
		
		$this->assertEquals( $core->restoreArg( $core->storeArg( null ) ), null );
		$this->assertEquals( $core->restoreArg( $core->storeArg( true ) ), true );
		$this->assertEquals( $core->restoreArg( $core->storeArg( false ) ), false );
		$this->assertEquals( $core->restoreArg( $core->storeArg( '' ) ), '' );
		$this->assertEquals( $core->restoreArg( $core->storeArg( "{test.success}" ) ), "{test.success}" );
		$this->assertEquals( $core->restoreArg( $core->storeArg( 0 ) ), 0 );
		$this->assertEquals( $core->restoreArg( $core->storeArg( 20.2 ) ), 20.2 );
		$this->assertEquals( $core->restoreArg( $core->storeArg( array( 10, 11, 'help' ) ) ), array( 10, 11, 'help' ) );
		$this->assertEquals( $core->restoreArg( $core->storeArg( array( 'did' => 'done' ) ) ), array( 'did' => 'done' ) );
		$this->assertTrue( $core->restoreArg( $core->storeArg( $obj ) )->name == 'Test Object' );
	}
}
