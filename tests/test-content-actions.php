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
class MWPRulesActionsTest extends WP_UnitTestCase 
{
	public $plugin;
	
	public $test_operation;
	
	public function __construct()
	{
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
	 * Create, Update, Delete Posts
	 */
	public function test_crud_posts()
	{
		/* Create A Post */
		$create_action = $this->plugin->getAction( 'rules_create_post' );
		$this->assertTrue( $create_action instanceof \MWP\Rules\ECA\Action );
		
		$created_term = wp_insert_term( 'Test Post CRUD', 'category', array( 'slug' => 'test-crud-term' ) );
		
		$values = array(
			'post_comment_status' => 'open',
			'post_ping_status' => 'closed',
			'rules_post_type' => 'post',
			'post_author' => 'id: 1',
			'rules_post_title' => 'Created Test Post',
			'rules_post_content' => 'Post content.',
			'rules_post_excerpt' => 'Post excerpt.',
			'rules_post_status' => 'publish',
			'rules_post_date' => 0,
			'post_tax_terms' => 'slug: category/test-crud-term',
			'post_meta_values' => "hero: Batman\nvillain: Joker",
		);
		
		$arg_map = array();
		
		$type = call_user_func( $create_action->arguments['type']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $type instanceof \WP_Post_Type );
		
		$author = call_user_func( $create_action->arguments['author']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $author instanceof \WP_User );
		
		$title = call_user_func( $create_action->arguments['title']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $title, 'Created Test Post' );
		
		$content = call_user_func( $create_action->arguments['content']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $content, 'Post content.' );
		
		$excerpt = call_user_func( $create_action->arguments['excerpt']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $excerpt, 'Post excerpt.' );
		
		$status = call_user_func( $create_action->arguments['status']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $status, 'publish' );
		
		$date = call_user_func( $create_action->arguments['date']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $date instanceof \DateTime );
		
		$terms = call_user_func( $create_action->arguments['terms']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $terms ) );
		$this->assertTrue( $terms[0] instanceof \WP_Term );
		
		$meta = call_user_func( $create_action->arguments['meta']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( $meta['hero'], "Batman" );
		$this->assertEquals( $meta['villain'], "Joker" );
		
		$result = call_user_func( $create_action->callback, $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $result['post_id'] );
		$this->assertEquals( $post->post_title, $title );
		$this->assertEquals( $post->post_author, $author->ID );
		$this->assertEquals( $post->post_content, $content );
		$this->assertEquals( $post->post_excerpt, $excerpt );
		$this->assertEquals( $post->post_status, $status );
		$this->assertEquals( $post->post_date, '1970-01-01 00:00:00' );
		$this->assertEquals( $post->post_date_gmt, '1970-01-01 00:00:00' );
		$this->assertEquals( $post->post_type, $type->name );
		$this->assertEquals( $post->comment_status, 'open' );
		$this->assertEquals( $post->ping_status, 'closed' );
		$this->assertEquals( get_post_meta( $post->ID, 'hero', true ), "Batman" );
		$this->assertEquals( get_post_meta( $post->ID, 'villain', true ), "Joker" );		
		$this->assertTrue( has_term( $created_term['term_id'], $terms[0]->taxonomy, $post->ID ) );
		
		/* Update A Post */
		$update_action = $this->plugin->getAction( 'rules_update_post' );
		$this->assertTrue( $update_action instanceof \MWP\Rules\ECA\Action );
		
		$values = array(
			'rules_post_update_attributes' => array( 'type', 'author', 'date', 'status', 'title', 'content', 'excerpt', 'meta', 'comment_status', 'ping_status', 'terms' ),
			'rules_post' => 'id: ' . $post->ID,
			'post_comment_status' => 'closed',
			'post_ping_status' => 'open',
			'rules_post_type' => 'page',
			'post_author' => 'id: 1',
			'rules_post_title' => 'Updated Test Post',
			'rules_post_content' => 'New post content.',
			'rules_post_excerpt' => 'New post excerpt.',
			'rules_post_status' => 'draft',
			'rules_post_date' => 1,
			'post_tax_terms' => 'id: 1',
			'post_tax_terms_method' => 'set',
			'post_meta_values' => "hero: Joker\nvillain: Batman",
		);
		
		$post = call_user_func( $update_action->arguments['post']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $post instanceof \WP_Post );
		
		$type = call_user_func( $update_action->arguments['type']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $type instanceof \WP_Post_Type );
		
		$author = call_user_func( $update_action->arguments['author']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $author instanceof \WP_User );
		
		$title = call_user_func( $update_action->arguments['title']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $title, 'Updated Test Post' );
		
		$content = call_user_func( $update_action->arguments['content']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $content, 'New post content.' );
		
		$excerpt = call_user_func( $update_action->arguments['excerpt']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $excerpt, 'New post excerpt.' );
		
		$status = call_user_func( $update_action->arguments['status']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $status, 'draft' );
		
		$date = call_user_func( $update_action->arguments['date']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $date instanceof \DateTime );
		
		$terms = call_user_func( $update_action->arguments['terms']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $terms ) );
		$this->assertTrue( $terms[0] instanceof \WP_Term );
		
		$meta = call_user_func( $update_action->arguments['meta']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( $meta['villain'], "Batman" );
		$this->assertEquals( $meta['hero'], "Joker" );
		
		$result = call_user_func( $update_action->callback, $post, $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertEquals( $post->post_title, $title );
		$this->assertEquals( $post->post_author, $author->ID );
		$this->assertEquals( $post->post_content, $content );
		$this->assertEquals( $post->post_excerpt, $excerpt );
		$this->assertEquals( $post->post_status, 'draft' );
		$this->assertEquals( $post->post_date, '1970-01-01 00:00:01' );
		$this->assertEquals( $post->post_date_gmt, '1970-01-01 00:00:01' );
		$this->assertEquals( $post->post_type, 'page' );
		$this->assertEquals( $post->comment_status, 'closed' );
		$this->assertEquals( $post->ping_status, 'open' );
		$this->assertEquals( get_post_meta( $post->ID, 'villain', true ), "Batman" );
		$this->assertEquals( get_post_meta( $post->ID, 'hero', true ), "Joker" );		
		$this->assertTrue( has_term( 1, 'category', $post->ID ) );
		$this->assertTrue( ! has_term( $created_term['term_id'], 'category', $post->ID ) );

		/* Trash / Delete A Post */
		$delete_action = $this->plugin->getAction( 'rules_delete_post' );

		$values = array( 
			'rules_post' => 'id: ' . $post->ID,
			'rules_post_trash' => 'trash',
		);
		
		$post = call_user_func( $delete_action->arguments['post']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $post instanceof \WP_Post );
		
		$result = call_user_func( $delete_action->callback, $post, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertTrue( $post->post_status == 'trash' );
		
		$values['rules_post_trash'] = 'delete';
		$result = call_user_func( $delete_action->callback, $post, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertNull( $post );
	}
	
	/**
	 * Create, Update, Delete Comments
	 */
	public function test_crud_comments()
	{
		/* Create A Post */
		$create_action = $this->plugin->getAction( 'rules_create_comment' );
		$this->assertTrue( $create_action instanceof \MWP\Rules\ECA\Action );
		
		$post_id = wp_insert_post( array( 'post_title' => 'Batman meets Catwoman at Nightclub', 'post_author' => 1 ) );
		
		$values = array(
			'rules_comment_post' => 'id:' . $post_id,
			'rules_comment_parent' => '',
			'comment_author_type' => 'existing',
			'comment_author' => 'id:1',
			'comment_author_name' => 'Cat Woman',
			'comment_author_email' => 'cat@woman.com',
			'comment_author_url' => 'http://ladylegs.marvel.com',
			'rules_comment_content' => "I Don't Know About You Miss Kitty But I'm Feeling...So Much Yummier.",
			'rules_comment_date' => 0,
			'comment_meta_values' => "hero: Batman\nvillain: Joker",
			'comment_approved' => 1,
		);
		
		$arg_map = array();
		
		$post = call_user_func( $create_action->arguments['post']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $post instanceof \WP_Post );
		
		$parent = call_user_func( $create_action->arguments['parent']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertNull( $parent );
		
		$author = call_user_func( $create_action->arguments['author']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $author instanceof \WP_User );
		
		$content = call_user_func( $create_action->arguments['content']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $content, $values['rules_comment_content'] );
		
		$date = call_user_func( $create_action->arguments['date']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $date instanceof \DateTime );
		
		$meta = call_user_func( $create_action->arguments['meta']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( $meta['hero'], "Batman" );
		$this->assertEquals( $meta['villain'], "Joker" );
		
		$result = call_user_func( $create_action->callback, $post, $parent, $author, $content, $date, $meta, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$comment = get_comment( $result['comment_id'] );
		$this->assertTrue( $comment instanceof \WP_Comment );
		$this->assertTrue( $comment->comment_parent == 0 );
		$this->assertTrue( $comment->comment_approved == 1 );
		$this->assertTrue( $comment->comment_post_ID == $post->ID );
		$this->assertTrue( $comment->user_id == $author->ID );
		$this->assertEquals( $comment->comment_content, $content );
		$this->assertEquals( $comment->comment_date, '1970-01-01 00:00:00' );
		$this->assertEquals( $comment->comment_date_gmt, '1970-01-01 00:00:00' );
		$this->assertEquals( get_comment_meta( $comment->comment_ID, 'hero', true ), "Batman" );
		$this->assertEquals( get_comment_meta( $comment->comment_ID, 'villain', true ), "Joker" );		

		/* Create a Sub Comment */
		$values['comment_author_type'] = 'anonymous';
		$values['comment_approved'] = 0;
		$values['rules_comment_parent'] = 'id:' . $comment->comment_ID;
		
		$author = call_user_func( $create_action->arguments['author']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $author ) );
		
		$parent = call_user_func( $create_action->arguments['parent']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $parent instanceof \WP_Comment );
		
		$result = call_user_func( $create_action->callback, $post, $parent, $author, $content, $date, $meta, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$subcomment = get_comment( $result['comment_id'] );
		
		$this->assertTrue( $subcomment instanceof \WP_Comment );
		$this->assertTrue( $subcomment->comment_parent == $comment->comment_ID );
		$this->assertTrue( $subcomment->comment_approved == 0 );
		$this->assertTrue( $subcomment->user_id == 0 );
		$this->assertEquals( $subcomment->comment_author, $values['comment_author_name'] );
		$this->assertEquals( $subcomment->comment_author_email, $values['comment_author_email'] );
		$this->assertEquals( $subcomment->comment_author_url, $values['comment_author_url'] );
		
		return;
		
		/* Update A Comment */
		$update_action = $this->plugin->getAction( 'rules_update_post' );
		$this->assertTrue( $update_action instanceof \MWP\Rules\ECA\Action );
		
		$values = array(
			'rules_post_update_attributes' => array( 'type', 'author', 'date', 'status', 'title', 'content', 'excerpt', 'meta', 'comment_status', 'ping_status', 'terms' ),
			'rules_post' => 'id: ' . $post->ID,
			'post_comment_status' => 'closed',
			'post_ping_status' => 'open',
			'rules_post_type' => 'page',
			'post_author' => 'id: 1',
			'rules_post_title' => 'Updated Test Post',
			'rules_post_content' => 'New post content.',
			'rules_post_excerpt' => 'New post excerpt.',
			'rules_post_status' => 'draft',
			'rules_post_date' => 1,
			'post_tax_terms' => 'id: 1',
			'post_tax_terms_method' => 'set',
			'post_meta_values' => "hero: Joker\nvillain: Batman",
		);
		
		$post = call_user_func( $update_action->arguments['post']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $post instanceof \WP_Post );
		
		$type = call_user_func( $update_action->arguments['type']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $type instanceof \WP_Post_Type );
		
		$author = call_user_func( $update_action->arguments['author']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $author instanceof \WP_User );
		
		$title = call_user_func( $update_action->arguments['title']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $title, 'Updated Test Post' );
		
		$content = call_user_func( $update_action->arguments['content']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $content, 'New post content.' );
		
		$excerpt = call_user_func( $update_action->arguments['excerpt']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $excerpt, 'New post excerpt.' );
		
		$status = call_user_func( $update_action->arguments['status']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertEquals( $status, 'draft' );
		
		$date = call_user_func( $update_action->arguments['date']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $date instanceof \DateTime );
		
		$terms = call_user_func( $update_action->arguments['terms']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $terms ) );
		$this->assertTrue( $terms[0] instanceof \WP_Term );
		
		$meta = call_user_func( $update_action->arguments['meta']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $meta ) );
		$this->assertEquals( $meta['villain'], "Batman" );
		$this->assertEquals( $meta['hero'], "Joker" );
		
		$result = call_user_func( $update_action->callback, $post, $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertEquals( $post->post_title, $title );
		$this->assertEquals( $post->post_author, $author->ID );
		$this->assertEquals( $post->post_content, $content );
		$this->assertEquals( $post->post_excerpt, $excerpt );
		$this->assertEquals( $post->post_status, 'draft' );
		$this->assertEquals( $post->post_date, '1970-01-01 00:00:01' );
		$this->assertEquals( $post->post_date_gmt, '1970-01-01 00:00:01' );
		$this->assertEquals( $post->post_type, 'page' );
		$this->assertEquals( $post->comment_status, 'closed' );
		$this->assertEquals( $post->ping_status, 'open' );
		$this->assertEquals( get_post_meta( $post->ID, 'villain', true ), "Batman" );
		$this->assertEquals( get_post_meta( $post->ID, 'hero', true ), "Joker" );		
		$this->assertTrue( has_term( 1, 'category', $post->ID ) );
		$this->assertTrue( ! has_term( $created_term['term_id'], 'category', $post->ID ) );

		/* Trash / Delete A Post */
		$delete_action = $this->plugin->getAction( 'rules_delete_post' );

		$values = array( 
			'rules_post' => 'id: ' . $post->ID,
			'rules_post_trash' => 'trash',
		);
		
		$post = call_user_func( $delete_action->arguments['post']['configuration']['getArg'], $values, $arg_map, $this->test_operation );
		$this->assertTrue( $post instanceof \WP_Post );
		
		$result = call_user_func( $delete_action->callback, $post, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertTrue( $post->post_status == 'trash' );
		
		$values['rules_post_trash'] = 'delete';
		$result = call_user_func( $delete_action->callback, $post, $values, $arg_map, $this->test_operation );
		$this->assertTrue( is_array( $result ) and $result['success'] );
		
		$post = get_post( $post->ID );
		$this->assertNull( $post );
	}
	
}
