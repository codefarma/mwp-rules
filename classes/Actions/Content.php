<?php
/**
 * Plugin Class File
 *
 * Created:   December 5, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Content Class
 */
class Content
{
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
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
	public function setPlugin( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \MWP\Rules\Plugin::instance() );
	}
	
	/**
	 * Register ECA's
	 * 
	 * @Wordpress\Action( for="rules_register_ecas" )
	 * 
	 * @return	void
	 */
	public function registerECAs()
	{
		$plugin = $this->getPlugin();
		
		rules_define_actions( array(
		
			/* Create A Post */
			array( 'rules_create_post', array(
				'title' => 'Create A Post',
				'description' => 'Create a new post',
				'configuration' => array(
					'form' => function( $form, $values, $operation ) use ( $plugin ) {
						$open_closed_choices = array(
							__( 'Site Default', 'mwp-rules' ) => 'default',
							__( 'Open', 'mwp-rules' ) => 'open',
							__( 'Closed', 'mwp-rules' ) => 'closed',
						);
							
						$form->addField( 'post_comment_status', 'choice', array(
							'label' => __( 'Post Comment Status', 'mwp-rules' ),
							'choices' => $open_closed_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['post_comment_status'] ) ? $values['post_comment_status'] : 'default',
						));
						
						$form->addField( 'post_ping_status', 'choice', array(
							'label' => __( 'Post Ping Status', 'mwp-rules' ),
							'choices' => $open_closed_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['post_ping_status'] ) ? $values['post_ping_status'] : 'default',
						));
						
						$form->addField( 'post_run_php', 'checkbox', array(
							'label' => 'Process Post Using PHP',
							'description' => 'For advanced usage, you can process the post using php code after it has been created.',
							'value' => 1,
							'data' => isset( $values['post_run_php'] ) ? (bool) $values['post_run_php'] : false,
							'toggles' => array(
								1 => array( 'show' => array( '#post_run_phpcode' ) ),
							),
						));
						
						$form->addField( 'post_run_phpcode', 'textarea', array(
							'row_attr' => array(  'id' => 'post_run_phpcode', 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'Custom PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $values[ 'post_run_phpcode' ] ) ? $values[ 'post_run_phpcode' ] : "// <?php \n\nreturn;",
							'description' => $plugin->getTemplateContent( 'rules/phpcode_description', array( 'operation' => $operation, 'return_args' => NULL, 'event' => $operation->event() ) ),
							'required' => false,
						));
					},
				),
				'arguments' => array(
					'type' => array(
						'label' => 'Post Type',
						'default' => 'manual',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post type', 'classes' => array( 'WP_Post_Type' ) ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$post_type_choices = array();
								foreach( get_post_types( array(), 'objects' ) as $post_type ) {	$post_type_choices[ $post_type->label ] = $post_type->name;	}
								$form->addField( 'rules_post_type', 'choice', array(
									'label' => __( 'Post Type', 'mwp-rules' ),
									'choices' => $post_type_choices,
									'required' => true,
									'data' => isset( $values['rules_post_type'] ) ? $values['rules_post_type'] : 'post',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_post_type'];
							},
						),
					),
					'author' => array(
						'label' => 'Author',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The author of the post', 'classes' => array( 'WP_User' ) ),
						),
					),
					'title' => array(
						'label' => 'Post Title',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The title of the post' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_post_title', 'text', array(
									'label' => __( 'Title', 'mwp-rules' ),
									'data' => isset( $values['rules_post_title'] ) ? $values['rules_post_title'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_post_title'];
							}
						),
					),
					'content' => array(
						'label' => 'Post Content',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The content of the post' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_post_content', 'text', array(
									'label' => __( 'Post Content', 'mwp-rules' ),
									'data' => isset( $values['rules_post_content'] ) ? $values['rules_post_content'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_post_content'];
							}
						),
					),
					'excerpt' => array(
						'label' => 'Post Excerpt',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'An excerpt of the post content' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_post_excerpt', 'text', array(
									'label' => __( 'Post Excerpt', 'mwp-rules' ),
									'data' => isset( $values['rules_post_excerpt'] ) ? $values['rules_post_excerpt'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_post_excerpt'];
							}
						),
					),
					'status' => array(
						'label' => 'Status',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The status of the post' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$post_statuses = array();
								foreach( get_post_stati( array(), 'objects' ) as $status ) { 
									$post_statuses[ $status->label . ' - [' . $status->name . '] (' . ( $status->_builtin ? 'core' : $status->label_count['domain'] ) . ')' ] = $status->name;	
								}
								$form->addField( 'rules_post_status', 'choice', array(
									'label' => __( 'Post Status', 'mwp-rules' ),
									'choices' => $post_statuses,
									'required' => true,
									'expanded' => true,
									'data' => isset( $values['rules_post_status'] ) ? $values['rules_post_status'] : 'draft',
								));								
							},
							'getArg' => function( $values ) {
								return $values['rules_post_status'];
							}
						),
					),
					'date' => array(
						'label' => 'Post Date',
						'argtypes' => array(
							'object' => array( 'description' => 'The date for the post', 'classes' => array( 'DateTime' ) ),
						),
						'configuration' => $plugin->configPreset( 'datetime', 'rules_post_date', array( 'label' => 'Post Date' ) ),
					),
					'terms' => array(
						'label' => 'Taxonomy Terms',
						'default' => 'phpcode',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of terms to assign to the post', 'classes' => array( 'WP_Term' ) ),
							'object' => array( 'description' => 'An individual term to assign to the post', 'classes' => array( 'WP_term' ) ),
						),
					),
					'meta' => array(
						'label' => 'Meta Values',
						'default' => 'phpcode',
						'argtypes' => array(
							'array' => array( 'description' => 'An associative array of meta values to add to the post' ),
						),
					),
				),
				'callback' => function( $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values ) {
					
				}
			)),
			
			/* Delete A Post */
			
			/* Change Post Status */			
			
			/* Change Post Author */
			
			/* Change Post Title */
			
			/* Change Post Content */
			
			/* Change Post Excerpt */
			
			/* Change Post Type */
			
			/* Change Post Password */
			
			/* Change Post Terms */
			
			/* Create A Comment */
			
			/* Delete A Comment */
			
			/* Change Comment Text */
			
			/* Change Comment Status */
			
			/* Change Comment Author */
			
			
		
		));
		
	}	
}
