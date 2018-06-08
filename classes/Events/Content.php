<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Events;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Content Class
 */
class _Content
{
	/**
	 * @var 	\MWP\Framework\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\MWP\Framework\Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\MWP\Framework\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \MWP\Framework\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Rules\Plugin::instance() );
	}
	
	/**
	 * Register ECA's
	 * 
	 * @MWP\WordPress\Action( for="rules_register_ecas" )
	 * 
	 * @return	void
	 */
	public function registerECAs()
	{
		rules_register_events( array(
			
			/* Post Title Filter */
			array( 'filter', 'the_title', array(
				'title' => 'Post Title Is Filtered',
				'description' => 'The post title is filtered before it is output to the page.',
				'group' => 'Output',
				'arguments' => array(
					'title' => array( 'argtype' => 'string', 'label' => 'Post Title', 'description' => 'The post title' ),
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post whose title is being filtered' ),
				),
			)),
			
			/* Post Content Filter */
			array( 'filter', 'the_content', array(
				'title' => 'Post Content Is Filtered',
				'description' => 'The post content is filtered before it is output to the page.',
				'group' => 'Output',
				'arguments' => array(
					'content' => array( 'argtype' => 'string', 'label' => 'Post Content', 'description' => 'The post content' ),
				),
			)),
			
			/* Post Excerpt Filter */
			array( 'filter', 'the_excerpt', array(
				'title' => 'Post Excerpt Is Filtered',
				'description' => 'The post excerpt is filtered before it is output to the page.',
				'group' => 'Output',
				'arguments' => array(
					'excerpt' => array( 'argtype' => 'string', 'label' => 'Post Excerpt', 'description' => 'The post excerpt' ),
				),
			)),
			
			/* Post Meta Filter */
			array( 'filter', 'the_meta_key', array(
				'title' => 'Post Meta Is Filtered',
				'description' => 'Post meta keys are filtered before they are output to the page as list items.',
				'group' => 'Output',
				'arguments' => array(
					'html' => array( 'argtype' => 'string', 'label' => 'Meta Key HTML', 'description' => 'The html that displays the meta key list item' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key being displayed' ),
					'meta_value' => array( 'argtype' => 'string', 'label' => 'Meta Value', 'description' => 'The stringified meta key value' ),
				),
			)),
			
			/* Post Attachment Filter */
			array( 'filter', 'prepend_attachment', array(
				'title' => 'Post Attachment Is Filtered',
				'description' => 'The post attachment markup is filtered before it is prepended to the post content.',
				'group' => 'Output',
				'arguments' => array(
					'markup' => array( 'argtype' => 'string', 'label' => 'Markup', 'description' => 'The post attachment html markup' ),
				),
			)),
			
			/* Post Created Or Updated */
			array( 'action', 'save_post', array(
				'title' => 'Post Is Created Or Updated',
				'description' => 'This event occurs for posts and revisions when they are created, updated, or auto-saved.',
				'group' => 'Post',
				'arguments' => array(
					'post_id' => array( 'argtype' => 'int', 'label' => 'Post ID', 'description' => 'The ID of the post which was created or updated' ),
					'post' => array( 'argtype' => 'object', 'class' => 'WP_Post', 'label' => 'Post', 'description' => 'The post object' ),
					'update' => array( 'argtype' => 'bool', 'label' => 'Is Update', 'description' => 'Boolean flag indicating if this is an update to an existing post' ),
				),
			)),
			
			/* Post Trashed */
			array( 'action', 'trashed_post', array( 
				'title' => 'Post Is Trashed',
				'description' => 'This event occurs after a post has been moved to the trash.',
				'group' => 'Post',
				'arguments' => array(
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post that was trashed' ),
				),
			)),
			
			/* Post Untrashed */
			array( 'action', 'untrashed_post', array( 
				'title' => 'Post Is Un-Trashed',
				'description' => 'This event occurs after a post has been restored from the trash.',
				'group' => 'Post',
				'arguments' => array(
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post that was untrashed' ),
				),
			)),
			
			/* Post Deleted */
			array( 'action', 'before_delete_post', array(
				'title' => 'Post Is Being Deleted Permanently',
				'description' => 'This event occurs just before a post and all of its associated data is deleted.',
				'group' => 'Post',
				'arguments' => array(
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post which is being deleted' ),
				),
			)),
			
			/* Post Meta Added */
			array( 'action', 'added_user_meta', array(
				'title' => 'Post Meta Has Been Added',
				'description' => 'This event occurs when post meta data is added for the first time.',
				'group' => 'Post',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was added' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Post Meta Updated */
			array( 'action', 'updated_user_meta', array(
				'title' => 'Post Meta Has Been Updated',
				'description' => 'This event occurs after post meta data has been successfully updated.',
				'group' => 'Post',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was updated' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Post Meta Deleted */
			array( 'action', 'deleted_user_meta', array(
				'title' => 'Post Meta Has Been Deleted',
				'description' => 'This event occurs after post meta data has been deleted.',
				'group' => 'Post',
				'arguments' => array(
					'meta_ids' => array( 'argtype' => 'array', 'label' => "Meta IDs", 'description' => 'The IDs of the meta data rows that were deleted' ),
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was deleted' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was matched' ),
				),
			)),
			
			/* Post Terms Updated */
			array( 'action', 'set_object_terms', array(
				'title' => 'Post Taxonomy Terms Have Been Updated',
				'description' => 'This event occurs when a post has had new taxonomy terms assigned.',
				'group' => 'Post',
				'arguments' => array(
					'post_id' => array( 'argtype' => 'int', 'class' => 'WP_Post', 'label' => 'Post ID', 'description' => 'The ID of the post which was assigned the taxonomy terms' ),
					'terms' => array( 'argtype' => 'array', 'class' => 'WP_Term', 'label' => 'Term IDs', 'description' => 'The IDs of the terms which were set on the post' ),
					'tt_ids' => array( 'argtype' => 'array', 'label' => 'Term/Taxonomy IDs', 'description' => 'The IDs of the relationships that link the post/taxonomy/term' ),
					'taxonomy' => array( 'argtype' => 'string', 'class' => 'WP_Taxonomy', 'label' => 'Taxonomy Name', 'description' => 'The name of the taxonomy the terms belong to' ),
					'append' => array( 'argtype' => 'bool', 'label' => 'Appended Flag', 'description' => 'Boolean value indicating if the terms were appended to the existing terms for the post' ),
				),
			)),
			
			/* Comment Created */
			array( 'action', 'wp_insert_comment', array( 
				'title' => 'Comment Is Posted',
				'description' => 'This event occurs when a new comment is posted.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'label' => 'Comment ID', 'description' => 'The ID of the new comment' ),
					'comment' => array( 'argtype' => 'object', 'class' => 'WP_Comment', 'label' => 'Comment', 'description' => 'The new comment' ),
				),
			)),
			
			/* Comment Edited */
			array( 'action', 'edit_comment', array( 
				'title' => 'Comment Is Edited',
				'description' => 'This event occurs when a comment is edited.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the edited comment' ),
					'data' => array( 'argtype' => 'array', 'label' => 'Comment Data', 'description' => 'The updated comment data' ),
				),
			)),
			
			/* Comment Status Changed */
			array( 'action', 'transition_comment_status', array( 
				'title' => 'Comment Status Has Been Changed',
				'description' => 'This event occurs when the status of a comment has transitioned.',
				'group' => 'Comment',
				'arguments' => array(
					'new_status' => array( 'argtype' => 'string', 'label' => 'New Status', 'description' => 'The new status of the comment' ),
					'old_status' => array( 'argtype' => 'string', 'label' => 'Old Status', 'description' => 'The old status of the comment' ),
					'comment' => array( 'argtype' => 'object', 'class' => 'WP_Comment', 'label' => 'Comment', 'description' => 'The comment for which the status changed occurred' ),
				),
			)),
			
			/* Comment Spam */
			array( 'action', 'spammed_comment', array( 
				'title' => 'Comment Is Marked As Spam',
				'description' => 'This event occurs after a comment has been marked as spam.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the spam comment' ),
				),
			)),
			
			/* Comment Not Spam */
			array( 'action', 'unspammed_comment', array( 
				'title' => 'Comment Is Un-Marked As Spam',
				'description' => 'This event occurs after a comment has been un-marked as spam.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the not spam comment' ),
				),
			)),
			
			/* Comment Filtered */
			array( 'filter', 'comment_text', array( 
				'title' => 'Comment Text Is Filtered',
				'description' => 'Comments are filtered before they are output to the page.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_text' => array( 'argtype' => 'string', 'label' => 'Comment Text', 'description' => 'The content of the comment' ),
					'comment' => array( 'argtype' => 'object', 'class' => 'WP_Comment', 'label' => 'Comment', 'description' => 'The comment', 'nullable' => true ),
					'args' => array( 'argtype' => 'array', 'label' => 'Arguments', 'description' => 'Arguments used when fetching the comment text' ),
				),
			)),
			
			/* Comment Trashed */
			array( 'action', 'trashed_comment', array( 
				'title' => 'Comment Is Trashed',
				'description' => 'This event occurs after a comment has been moved to the trash.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment which was trashed' ),
				),
			)),
			
			/* Comment Untrashed */
			array( 'action', 'untrashed_comment', array( 
				'title' => 'Comment Is Un-Trashed',
				'description' => 'This event occurs after a comment has been restored from the trash.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment which was untrashed' ),
				),
			)),
			
			/* Comment Deleted */
			array( 'action', 'delete_comment', array(
				'title' => 'Comment Is Being Deleted Permanently',
				'description' => 'This event occurs just before a comment and all of its associated data is deleted.',
				'group' => 'Comment',
				'arguments' => array(
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment which is being deleted' ),
				),
			)),
			
			/* Comment Meta Added */
			array( 'action', 'added_comment_meta', array(
				'title' => 'Comment Meta Has Been Added',
				'description' => 'This event occurs when comment meta data is added for the first time.',
				'group' => 'Comment',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was added' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Comment Meta Updated */
			array( 'action', 'updated_comment_meta', array(
				'title' => 'Comment Meta Has Been Updated',
				'description' => 'This event occurs after comment meta data has been successfully updated.',
				'group' => 'Comment',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was updated' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Comment Meta Deleted */
			array( 'action', 'deleted_comment_meta', array(
				'title' => 'Comment Meta Has Been Deleted',
				'description' => 'This event occurs after comment meta data has been deleted.',
				'group' => 'Comment',
				'arguments' => array(
					'meta_ids' => array( 'argtype' => 'array', 'label' => "Meta IDs", 'description' => 'The IDs of the meta data rows that were deleted' ),
					'comment_id' => array( 'argtype' => 'int', 'class' => 'WP_Comment', 'label' => 'Comment ID', 'description' => 'The ID of the comment that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was deleted' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was matched' ),
				),
			)),
			
			/* Taxonomy Term Created */
			array( 'action', 'created_term', array(
				'title' => 'Taxonomy Term Is Added',
				'description' => 'This event fires after a taxonomy term is added to a taxonomy.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term which has been added' ),
					'term_taxonomy_id' => array( 'argtype' => 'int', 'label' => 'Term Taxonomy ID', 'description' => 'The ID of the term to taxonomy relationship database record' ),
					'taxonomy_name' => array( 'argtype' => 'string', 'class' => 'WP_Taxonomy', 'label' => 'Taxonomy Name', 'description' => 'The name of the taxonomy to which the term belongs' ),
				),
			)),
			
			/* Taxonomy Term Edited */
			array( 'action', 'edited_term', array(
				'title' => 'Taxonomy Term Is Edited',
				'description' => 'This event fires after a taxonomy term has been edited.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term which has been edited' ),
					'term_taxonomy_id' => array( 'argtype' => 'int', 'label' => 'Term Taxonomy ID', 'description' => 'The ID of the term to taxonomy relationship database record' ),
					'taxonomy_name' => array( 'argtype' => 'string', 'class' => 'WP_Taxonomy', 'label' => 'Taxonomy Name', 'description' => 'The name of the taxonomy to which the term belongs' ),
				),
			)),
			
			/* Taxonomy Term Deleted */
			array( 'action', 'pre_delete_term', array(
				'title' => 'Taxonomy Term Is Being Deleted',
				'description' => 'This event fires just before a taxonomy term and all of its associated data is deleted.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term which is being deleted' ),
					'taxonomy_name' => array( 'argtype' => 'string', 'class' => 'WP_Taxonomy', 'label' => 'Taxonomy Name', 'description' => 'The name of the taxonomy to which the term belongs' ),
				),
			)),
			
			/* Term Meta Added */
			array( 'action', 'added_term_meta', array(
				'title' => 'Taxonomy Term Meta Has Been Added',
				'description' => 'This event occurs when taxonomy term meta data is added for the first time.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was added' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Term Meta Updated */
			array( 'action', 'updated_term_meta', array(
				'title' => 'Taxonomy Term Meta Has Been Updated',
				'description' => 'This event occurs after taxonomy term meta data has been successfully updated.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'meta_id' => array( 'argtype' => 'int', 'label' => 'Meta ID', 'description' => 'The ID of the meta data row' ),
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was updated' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was saved' ),
				),
			)),
			
			/* Term Meta Deleted */
			array( 'action', 'deleted_term_meta', array(
				'title' => 'Taxonomy Term Meta Has Been Deleted',
				'description' => 'This event occurs after taxonomy term meta data has been deleted.',
				'group' => 'Taxonomy',
				'arguments' => array(
					'meta_ids' => array( 'argtype' => 'array', 'label' => "Meta IDs", 'description' => 'The IDs of the meta data rows that were deleted' ),
					'term_id' => array( 'argtype' => 'int', 'class' => 'WP_Term', 'label' => 'Term ID', 'description' => 'The ID of the taxonomy term that the meta data belongs to' ),
					'meta_key' => array( 'argtype' => 'string', 'label' => 'Meta Key', 'description' => 'The meta key that was deleted' ),
					'meta_value' => array( 'argtype' => 'mixed', 'label' => 'Meta Value', 'description' => 'The meta data value which was matched' ),
				),
			)),
			
		));
	}
}
