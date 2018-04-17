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
	 * Get a decorated user select field for comment authors
	 *
	 * @return	array
	 */
	public function commentAuthorConfig()
	{
		$plugin = $this->plugin;
		
		/* Get the user selection config preset */
		$config = $plugin->configPreset( 'user', 'comment_author', array( 'label' => 'Comment Author', 'row_attr' => array( 'id' => 'comment_author' ) ) );
		
		/* Reference base implementations */
		$baseFormBuilder = $config['form'];
		$baseArgGetter = $config['getArg'];
		
		/* Decorate the base form builder */
		$config['form'] = function( $form, $values, $operation ) use ( $baseFormBuilder ) {
			$form->addField( 'comment_author_type', 'choice', array(
				'label' => __( 'Author Type', 'mwp-user' ),
				'choices' => array(
					__( 'Existing User', 'mwp-rules' ) => 'existing',
					__( 'Anonymous User', 'mwp-rules' ) => 'anonymous',
				),
				'expanded' => true,
				'required' => true,
				'toggles' => array(
					'existing' => array( 'show' => array( '#comment_author' ) ),
					'anonymous' => array( 'show' => array( '#comment_author_name', '#comment_author_email', '#comment_author_url' ) ),
				),
				'data' => isset( $values['comment_author_type'] ) ? $values['comment_author_type'] : 'existing',
			));
			
			call_user_func( $baseFormBuilder, $form, $values, $operation );
			
			$form->addField( 'comment_author_name', 'text', array(
				'row_attr' => array( 'id' => 'comment_author_name' ),
				'label' => __( 'Author Name', 'mwp-rules' ),
				'description' => __( 'Optional. Enter the name of the comment author.', 'mwp-rules' ),
				'data' => isset( $values['comment_author_name'] ) ? $values['comment_author_name'] : '',
			));
			
			$form->addField( 'comment_author_email', 'text', array(
				'row_attr' => array( 'id' => 'comment_author_email' ),
				'label' => __( 'Author Email', 'mwp-rules' ),
				'description' => __( 'Optional. Enter the email of the comment author.', 'mwp-rules' ),
				'data' => isset( $values['comment_author_email'] ) ? $values['comment_author_email'] : '',
			));
			
			$form->addField( 'comment_author_url', 'text', array(
				'row_attr' => array( 'id' => 'comment_author_url' ),
				'label' => __( 'Author Url', 'mwp-rules' ),
				'description' => __( 'Optional. Enter the url of the comment author.', 'mwp-rules' ),
				'data' => isset( $values['comment_author_url'] ) ? $values['comment_author_url'] : '',
			));								
		};
		
		/* Decorate the base argument getter */
		$config['getArg'] = function( $values, $arg_map, $operation ) use ( $baseArgGetter ) {
			$event = $operation->event();
			if ( $values['comment_author_type'] == 'existing' ) {
				return call_user_func( $baseArgGetter, $values, $arg_map, $operation );
			} else {
				$name = $operation->replaceTokens( $values['comment_author_name'], $arg_map );
				$email = $operation->replaceTokens( $values['comment_author_email'], $arg_map );
				$url = $operation->replaceTokens( $values['comment_author_url'], $arg_map );
				
				return array( 
					'name' => $name, 
					'email' => $email, 
					'url' => $url,
				);
			}								
		};
		
		return $config;		
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
		$plugin = $this->getPlugin();
		
		rules_define_actions( array(
		
			/* Register A Post Type */
			array( 'rules_register_post_type', array( 
				'title' => 'Register A Post Type',
				'description' => 'Register a post type to use on the site.',
				'configuration' => array(
				
				),
				'arguments' => array(
					'type' => array(
						'label' => 'Post Type',
						'default' => 'manual',
						'required' => true,
						'argtypes' => array(
							'string' => array( 'description' => 'The name of the post type. (max. 20 characters, cannot contain capital letters or spaces)' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'rules_post_type', array( 
							'label' => 'Slug', 
							'attr' => array( 'placeholder' => __( 'post_type', 'mwp-rules' ) ),
							'description' => 'max. 20 characters, cannot contain capital letters or spaces',
							'validators' => [ function( $data, $context ) {
								$slug = str_replace( '_', '', $data );
								if ( preg_match( '/[A-Z \W]/', $slug ) ) {
									$context->addViolation( 'Slug contains invalid characters.' );
								}
								if ( strlen( $data ) > 20 ) {
									$context->addViolation( 'Slug cannot be more than 20 characters.' );
								}
								if ( ! $data ) {
									$context->addViolation( 'Slus is a required field.' );
								}
							}],
						)),
					),
					'args' => array(
						'label' => 'Arguments',
						'default' => 'phpcode',
						'required' => false,
						'argtypes' => array(
							'array' => array( 'description' => 'An array of arguments to use when registering the post type.' ),
						),
						'configuration' => $plugin->configPreset( 'key_array', 'post_type_arguments', array(
							'label' => 'Post Type Settings',
						)),
					),
				),
				'callback' => function( $type, $args ) {
					$args = $args ?: array();
					$post_type = register_post_type( $type, $args );
					
					if ( is_wp_error( $post_type ) ) {
						return array( 'success' => false, 'message' => $post_type->get_error_message(), 'type' => $type, 'args' => $args );
					}
					
					return array( 'success' => true, 'message' => 'Post type registered.', 'type' => $type, 'args' => $args );
				},
			)),
			
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
							'description' => "<p class='alert alert-info'>" . 
								__( 'The created post is available via the <code>$created_post</code> variable in your php code. It is a WP_Post object.<br><strong>Note:</strong> You may optionally return a value from this PHP code to be used as the log message for this action during debugging.', 'mwp-rules' ) . 
								"</p>" .
								$plugin->getTemplateContent( 'snippets/phpcode_description', array( 'operation' => $operation, 'return_args' => NULL, 'event' => $operation->event() ) ),
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
							'object' => array( 'description' => 'The post type object', 'classes' => array( 'WP_Post_Type' ) ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$post_type_choices = array();
								foreach( get_post_types( array(), 'objects' ) as $post_type ) {	
									$post_type_choices[ $post_type->label ] = $post_type->name;	
								}
								$form->addField( 'rules_post_type', 'choice', array(
									'label' => __( 'Post Type', 'mwp-rules' ),
									'choices' => $post_type_choices,
									'required' => true,
									'data' => isset( $values['rules_post_type'] ) ? $values['rules_post_type'] : 'post',
								));
							},
							'getArg' => function( $values ) {
								$post_types = get_post_types( array( 'name' => $values['rules_post_type'] ), 'objects' );
								$post_type = array_shift( $post_types );
								if ( $post_type instanceof \WP_Post_Type ) {
									return $post_type;
								}
							},
						),
					),
					'author' => array(
						'label' => 'Author',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The author of the post', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'post_author', array( 'label' => 'Post Author' ) ),
					),
					'title' => array(
						'label' => 'Post Title',
						'default' => 'manual',
						'required' => true,
						'argtypes' => array(
							'string' => array( 'description' => 'The title of the post' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'rules_post_title', array( 'label' => 'Post Title', 'attr' => array( 'placeholder' => __( 'Enter title', 'mwp-rules' ) ) ) ),
					),
					'content' => array(
						'label' => 'Post Content',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The content of the post' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_post_content', array( 'label' => 'Post Content', 'attr' => array( 'style' => 'min-height: 250px' ) ) ),
					),
					'excerpt' => array(
						'label' => 'Post Excerpt',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'An excerpt of the post content' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_post_excerpt', array( 'label' => 'Post Excerpt' ) ),
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
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of terms to assign to the post', 'classes' => array( 'WP_Term' ) ),
							'object' => array( 'description' => 'An individual term to assign to the post', 'classes' => array( 'WP_Term' ) ),
						),
						'configuration' => $plugin->configPreset( 'terms', 'post_tax_terms' ),
					),
					'meta' => array(
						'label' => 'Meta Values',
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An associative array of meta values to add to the post' ),
						),
						'configuration' => $plugin->configPreset( 'meta_values', 'post_meta_values' ),
					),
				),
				'callback' => function( $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values, $arg_map ) {
					
					if ( ! $type instanceof \WP_Post_Type ) {
						return array( 'error' => 'Type is not an instance of WP_Post_Type. Aborted.', 'type' => $type );
					}
					
					if ( ! $author instanceof \WP_User ) {
						return array( 'error' => 'Author is not an instance of WP_User. Aborted.', 'author' => $author );
					}
					
					if ( ! $date instanceof \DateTime ) {
						return array( 'error' => 'Date is not an instance of DateTime. Aborted.', 'date' => $date );
					}
					
					if ( empty( $title ) ) {
						return array( 'error' => 'Title is empty. Aborted.' );
					}
					
					$tax_terms = array();
					$terms = is_object( $terms ) ? array( $terms ) : $terms;
					if ( is_array( $terms ) ) {
						foreach( $terms as $term ) {
							if ( $term instanceof \WP_Term ) {
								$tax_terms[ $term->taxonomy ][] = $term->term_id;
							}
						}
					}
					
					$post_args = array(
						'post_author' => $author->ID,
						'post_title' => $title,
						'post_content' => $content,
						'post_excerpt' => $excerpt,
						'post_status' => $status,
						'post_type' => $type->name,
						'post_date' => date( 'Y-m-d H:i:s', $date->getTimestamp() ),
						'post_date_gmt' => date( 'Y-m-d H:i:s', $date->getTimestamp() ),
					);
					
					if ( is_array( $meta ) ) {
						$post_args['meta_input'] = $meta;
					}
					
					if ( in_array( $values['post_comment_status'], array( 'open', 'closed' ) ) ) {
						$post_args['comment_status'] = $values['post_comment_status'];
					}
					
					if ( in_array( $values['post_ping_status'], array( 'open', 'closed' ) ) ) {
						$post_args['ping_status'] = $values['post_ping_status'];
					}
					
					$result = wp_insert_post( $post_args, true );
					
					if ( is_wp_error( $result ) ) {
						return array( 'success' => false, 'message' => 'Post creation failed.', 'reason' => implode( ',', $result->get_error_messages() ), 'args' => $post_args );
					}

					/**
					 * Adding taxonomy terms after the fact because there is a permission check performed
					 * for the current logged in user in the wp_insert_post() api. So if this rule action is
					 * executed on a page view by a user which does not happen to have the correct permission, 
					 * then the terms will not actually be added if provided in the $post_args.
					 */
					if ( ! empty( $tax_terms ) ) {
						foreach( $tax_terms as $taxonomy => $terms ) {
							wp_set_post_terms( $result, $terms, $taxonomy );
						}
					}
					
					if ( $values['post_run_php'] ) {
						$created_post = get_post( $result );
						$evaluate = function( $phpcode ) use ( $arg_map, $created_post ) {
							extract( $arg_map );
							return @eval( $phpcode );
						};
						
						$custom_result = $evaluate( $values['post_run_phpcode'] );
						if ( $custom_result ) {
							return $custom_result;
						}
					}
					
					return array( 'success' => true, 'message' => 'Post created successfully.', 'post_id' => $result, 'args' => $post_args );
				}
			)),
			
			/* Update A Post */
			array( 'rules_update_post', array(
				'title' => 'Update A Post',
				'description' => 'Update the attributes of an existing post.',
				'configuration' => array(
					'form' => function( $form, $values, $operation ) use ( $plugin ) {
						
						$form->addField( 'rules_post_update_attributes', 'choice', array(
							'label' => __( 'Attributes to update', 'mwp-rules' ),
							'choices' => array(
								__( 'Comment Status', 'mwp-rules' ) => 'comment_status',
								__( 'Ping Status', 'mwp-rules' ) => 'ping_status',
								__( 'Post Type', 'mwp-rules' ) => 'type',
								__( 'Author', 'mwp-rules' ) => 'author',
								__( 'Title', 'mwp-rules' ) => 'title',
								__( 'Post Content', 'mwp-rules' ) => 'content',
								__( 'Post Excerpt', 'mwp-rules' ) => 'excerpt',
								__( 'Post Status', 'mwp-rules' ) => 'status',
								__( 'Post Date', 'mwp-rules' ) => 'date',
								__( 'Taxonomy Terms', 'mwp-rules' ) => 'terms',
								__( 'Meta Data', 'mwp-rules' ) => 'meta',
							),
							'expanded' => true,
							'multiple' => true,
							'data' => isset( $values['rules_post_update_attributes'] ) ? $values['rules_post_update_attributes'] : array(),
							'description' => __( 'Choose the attributes that you want to update with this action.', 'mwp-rules' ),
							'toggles' => array(
								'comment_status' => array( 'show' => array( '#rules_update_post_comment_status' ) ),
								'ping_status' => array( 'show' => array( '#rules_update_post_ping_status' ) ),
								'type' => array( 'show' => array( '#rules_update_post_type_form_wrapper' ) ),
								'author' => array( 'show' => array( '#rules_update_post_author_form_wrapper' ) ),
								'title' => array( 'show' => array( '#rules_update_post_title_form_wrapper' ) ),
								'content' => array( 'show' => array( '#rules_update_post_content_form_wrapper' ) ),
								'excerpt' => array( 'show' => array( '#rules_update_post_excerpt_form_wrapper' ) ),
								'status' => array( 'show' => array( '#rules_update_post_status_form_wrapper' ) ),
								'date' => array( 'show' => array( '#rules_update_post_date_form_wrapper' ) ),
								'terms' => array( 'show' => array( '#rules_update_post_terms_form_wrapper' ) ),
								'meta' => array( 'show' => array( '#rules_update_post_meta_form_wrapper' ) ),
							),
						));
						
						$open_closed_choices = array(
							__( 'Open', 'mwp-rules' ) => 'open',
							__( 'Closed', 'mwp-rules' ) => 'closed',
						);
						
						$form->addField( 'post_comment_status', 'choice', array(
							'row_attr' => array( 'id' => 'rules_update_post_comment_status' ),
							'label' => __( 'Post Comment Status', 'mwp-rules' ),
							'choices' => $open_closed_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['post_comment_status'] ) ? $values['post_comment_status'] : 'open',
						));
						
						$form->addField( 'post_ping_status', 'choice', array(
							'row_attr' => array( 'id' => 'rules_update_post_ping_status' ),
							'label' => __( 'Post Ping Status', 'mwp-rules' ),
							'choices' => $open_closed_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['post_ping_status'] ) ? $values['post_ping_status'] : 'open',
						));
						
					},
				),
				'arguments' => array(
					'post' => array(
						'label' => 'Post To Update',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post to update', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'rules_post', array( 'label' => 'Post To Update' ) ),
					),
					'type' => array(
						'label' => 'Post Type',
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The post type object', 'classes' => array( 'WP_Post_Type' ) ),
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
								$post_types = get_post_types( array( 'name' => $values['rules_post_type'] ), 'objects' );
								$post_type = array_shift( $post_types );
								if ( $post_type instanceof \WP_Post_Type ) {
									return $post_type;
								}
							},
						),
					),
					'author' => array(
						'label' => 'Author',
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The author of the post', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $plugin->configPreset( 'user', 'post_author', array( 'label' => 'Post Author' ) ),
					),
					'title' => array(
						'label' => 'Post Title',
						'default' => 'manual',
						'required' => true,
						'argtypes' => array(
							'string' => array( 'description' => 'The title of the post' ),
						),
						'configuration' => $plugin->configPreset( 'text', 'rules_post_title', array( 'label' => 'Post Title', 'attr' => array( 'placeholder' => __( 'Enter title', 'mwp-rules' ) ) ) ),
					),
					'content' => array(
						'label' => 'Post Content',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The content of the post' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_post_content', array( 'label' => 'Post Content', 'attr' => array( 'style' => 'min-height: 250px' ) ) ),
					),
					'excerpt' => array(
						'label' => 'Post Excerpt',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'An excerpt of the post content' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_post_excerpt', array( 'label' => 'Post Excerpt' ) ),
					),
					'status' => array(
						'label' => 'Status',
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
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of terms to assign to the post', 'classes' => array( 'WP_Term' ) ),
							'object' => array( 'description' => 'An individual term to assign to the post', 'classes' => array( 'WP_Term' ) ),
						),
						'configuration' => call_user_func( function() use ( $plugin ) {
							/* Get the taxonomy terms config preset */
							$config = $plugin->configPreset( 'terms', 'post_tax_terms' );
							
							/* Reference base implementations */
							$baseFormBuilder = $config['form'];
							
							/* Decorate the base implementation */
							$config['form'] = function( $form, $values, $operation ) use ( $baseFormBuilder ) {
								$form->addField( 'post_tax_terms_method', 'choice', array(
									'label' => __( 'Update Strategy', 'mwp-rules' ),
									'description' => __( 'Choose how you want the terms to be updated for this post.', 'mwp-rules' ),
									'choices' => array(
										__( 'Add Terms', 'mwp-rules' ) => 'add',
										__( 'Remove Terms', 'mwp-rules' ) => 'remove',
										__( 'Set As Only Terms', 'mwp-rules' ) => 'set',
									),
									'data' => isset( $values['post_tax_terms_method'] ) ? $values['post_tax_terms_method'] : 'add',
									'required' => true,
									'expanded' => true,
								));
								
								call_user_func( $baseFormBuilder, $form, $values, $operation );
							};
							
							return $config;
						}),
					),
					'meta' => array(
						'label' => 'Meta Values',
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An associative array of meta values to update for the post' ),
						),
						'configuration' => $plugin->configPreset( 'meta_values', 'post_meta_values' ),
					),
				),
				'callback' => function( $post, $type, $author, $title, $content, $excerpt, $status, $date, $terms, $meta, $values, $arg_map ) {
					
					if ( ! $post instanceof \WP_Post ) {
						return array( 'error' => 'Post is not an instance of WP_Post. Aborted.', 'post' => $post );
					}
					
					$update_attributes = (array) $values['rules_post_update_attributes'];
					$post_args = array( 'ID' => $post->ID );
					
					if ( in_array( 'type', $update_attributes ) ) {
						if ( ! $type instanceof \WP_Post_Type ) {
							return array( 'error' => 'Type is not an instance of WP_Post_Type. Aborted.', 'type' => $type );
						}
						$post_args['post_type'] = $type->name;
					}
					
					if ( in_array( 'status', $update_attributes ) ) {
						$post_args['post_status'] = $status;
					}
					
					if ( in_array( 'author', $update_attributes ) ) {
						if ( ! $author instanceof \WP_User ) {
							return array( 'error' => 'Author is not an instance of WP_User. Aborted.', 'author' => $author );
						}
						$post_args['post_author'] = $author->ID;
					}
					
					if ( in_array( 'date', $update_attributes ) ) {
						if ( ! $date instanceof \DateTime ) {
							return array( 'error' => 'Date is not an instance of DateTime. Aborted.', 'date' => $date );
						}
						$post_args['post_date_gmt'] = $post_args['post_date'] = date( 'Y-m-d H:i:s', $date->getTimestamp() );
					}
					
					if ( in_array( 'title', $update_attributes ) ) {
						if ( empty( $title ) ) {
							return array( 'error' => 'Title is empty. Aborted.' );
						}
						$post_args['post_title'] = $title;
					}
					
					if ( in_array( 'content', $update_attributes ) ) {
						$post_args['post_content'] = $content;
					}
					
					if ( in_array( 'excerpt', $update_attributes ) ) {
						$post_args['post_excerpt'] = $excerpt;
					}
					
					if ( in_array( 'meta', $update_attributes ) and is_array( $meta ) ) {
						$post_args['meta_input'] = $meta;
					}
					
					if ( in_array( 'comment_status', $update_attributes ) ) {
						$post_args['comment_status'] = $values['post_comment_status'];
					}
					
					if ( in_array( 'ping_status', $update_attributes ) ) {
						$post_args['ping_status'] = $values['post_ping_status'];
					}
					
					$result = wp_update_post( $post_args, true );
					
					if ( is_wp_error( $result ) ) {
						return array( 'success' => false, 'message' => 'Post update failed.', 'reason' => implode( ',', $result->get_error_messages() ), 'args' => $post_args );
					}
					
					/**
					 * Adding taxonomy terms after the fact because there is a permission check performed
					 * for the current logged in user in the wp_insert_post() api. So if this rule action is
					 * executed on a page view by a user which does not happen to have the correct permission, 
					 * then the terms will not actually be added if provided in the $post_args.
					 */
					if ( in_array( 'terms', $update_attributes ) ) 
					{
						$tax_terms = array();
						$terms = is_object( $terms ) ? array( $terms ) : $terms;
						if ( is_array( $terms ) ) {
							foreach( $terms as $term ) {
								if ( $term instanceof \WP_Term ) {
									$tax_terms[ $term->taxonomy ][] = $term->term_id;
								}
							}
						}
						
						if ( ! empty( $tax_terms ) ) {
							foreach( $tax_terms as $taxonomy => $terms ) {
								switch( $values['post_tax_terms_method'] ) {
									case 'set':    wp_set_post_terms( $post->ID, $terms, $taxonomy ); break;
									case 'add':    wp_set_post_terms( $post->ID, $terms, $taxonomy, true ); break;
									case 'remove': wp_remove_object_terms( $post->ID, $terms, $taxonomy ); break;
								}
							}
						}
					}
					
					return array( 'success' => true, 'message' => 'Post updated successfully.', 'args' => $post_args );
				}
			)),
			
			/* Delete A Post */
			array( 'rules_delete_post', array(
				'title' => 'Trash/Delete A Post',
				'description' => 'Delete or trash a post.',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$form->addField( 'rules_post_trash', 'choice', array(
							'label' => __( 'Delete Method', 'mwp-rules' ),
							'choices' => array(
								__( 'Move to trash only', 'mwp-rules' ) => 'trash',
								__( 'Delete permanently', 'mwp-rules' ) => 'delete',
							),
							'data' => isset( $values['rules_post_trash'] ) ? $values['rules_post_trash'] : 'trash',
							'expanded' => true,
							'required' => true,
						));
					},
				),
				'arguments' => array(
					'post' => array(
						'label' => 'Post To Delete',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post to delete', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'rules_post', array( 'label' => 'Post To Delete' ) ),
					),
				),
				'callback' => function( $post, $values ) {
					$force_delete = ( isset( $values['rules_post_trash'] ) and $values['rules_post_trash'] == 'delete' ) ? true : false;
					$result = wp_delete_post( $post->ID, $force_delete );
					
					if ( $result ) {
						return array( 'success' => true, 'message' => ( $force_delete ? 'Post permanently deleted.' : 'Post moved to trash.' ), 'post' => $result, 'forced' => $forced_delete );
					}
					
					return array( 'success' => false, 'message' => 'Delete failed.', 'post' => $post, 'forced' => $forced_delete );
				},
			)),
			
			/* Create A Comment */
			array( 'rules_create_comment', array(
				'title' => 'Create A Comment',
				'description' => 'Create a new comment on a post',
				'configuration' => array(
					'form' => function( $form, $values, $operation ) use ( $plugin ) {
						$status_choices = array(
							__( 'Approved', 'mwp-rules' ) => 1,
							__( 'Unapproved', 'mwp-rules' ) => 0,
						);
						
						$form->addField( 'comment_approved', 'choice', array(
							'label' => __( 'Comment Status', 'mwp-rules' ),
							'choices' => $status_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['comment_approved'] ) ? $values['comment_approved'] : '1',
						));
						
						$form->addField( 'post_run_php', 'checkbox', array(
							'label' => 'Process Comment Using PHP',
							'description' => 'For advanced usage, you choose to process the comment using php code after it has been created.',
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
							'description' => "<p class='alert alert-info'>" . 
								__( 'The created comment is available via the <code>$created_comment</code> variable in your php code. It is a WP_Comment object.<br><strong>Note:</strong> You may optionally return a value from this PHP code to be used as the log message for this action during debugging.', 'mwp-rules' ) . 
								"</p>" .
								$plugin->getTemplateContent( 'snippets/phpcode_description', array( 'operation' => $operation, 'return_args' => NULL, 'event' => $operation->event() ) ),
							'required' => false,
						));
					},
				),
				'arguments' => array(
					'post' => array(
						'label' => 'Post To Assign Comment To',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The post to assign the comment to', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'rules_comment_post', array( 'label' => 'Parent Post' ) ),
					),
					'parent' => array(
						'label' => 'Parent Comment',
						'argtypes' => array(
							'object' => array( 'description' => 'The parent comment', 'classes' => array( 'WP_Comment' ) ),
						),
						'configuration' => $plugin->configPreset( 'comment', 'rules_comment_parent', array( 'label' => 'Parent Comment' ) ),
					),
					'author' => array(
						'label' => 'Author',
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The author of the comment', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $this->commentAuthorConfig(),
					),
					'content' => array(
						'label' => 'Comment Content',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The content of the comment' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_comment_content', array( 'label' => 'Comment Content', 'attr' => array( 'style' => 'min-height: 250px' ) ) ),
					),
					'date' => array(
						'label' => 'Comment Date',
						'argtypes' => array(
							'object' => array( 'description' => 'The date for the comment', 'classes' => array( 'DateTime' ) ),
						),
						'configuration' => $plugin->configPreset( 'datetime', 'rules_comment_date', array( 'label' => 'Comment Date' ) ),
					),
					'meta' => array(
						'label' => 'Meta Values',
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An associative array of meta values to add to the comment' ),
						),
						'configuration' => $plugin->configPreset( 'meta_values', 'comment_meta_values' ),
					),
				),
				'callback' => function( $post, $parent, $author, $content, $date, $meta, $values, $arg_map ) {
					
					if ( ! $post instanceof \WP_Post ) {
						return array( 'error' => 'Post is not an instance of WP_Post. Aborted.', 'post' => $post );
					}
					
					if ( $parent && ! $parent instanceof \WP_Comment ) {
						return array( 'error' => "Parent comment is not an instance of WP_Comment. Aborted.", 'parent' => $parent );
					}
					
					if ( $values['comment_author_type'] == 'existing' and ! $author instanceof \WP_User ) {
						return array( 'error' => 'Author is not an instance of WP_User. Aborted.', 'author' => $author );
					}
					
					if ( ! $date instanceof \DateTime ) {
						return array( 'error' => 'Date is not an instance of DateTime. Aborted.', 'date' => $date );
					}
					
					if ( empty( $content ) ) {
						return array( 'error' => 'Comment content is empty. Aborted.' );
					}
					
					$comment_args = array(
						'comment_post_ID' => $post->ID,
						'comment_agent' => 'rules',
						'comment_approved' => $values['comment_approved'],
						'comment_author' => is_array( $author ) && isset( $author['name'] ) ? $author['name'] : '',
						'comment_author_email' => is_array( $author ) && isset( $author['email'] ) ? $author['email'] : '',
						'comment_author_url' => is_array( $author ) && isset( $author['url'] ) ? $author['url'] : '',
						'comment_author_IP' => '0.0.0.0',
						'comment_content' => $content,
						'comment_parent' => $parent ? $parent->comment_ID : '',
						'comment_date' => date( 'Y-m-d H:i:s', $date->getTimestamp() ),
						'comment_date_gmt' => date( 'Y-m-d H:i:s', $date->getTimestamp() ),
						'user_id' => $author instanceof \WP_User ? $author->ID : 0,
					);
					
					if ( is_array( $meta ) ) {
						$comment_args['comment_meta'] = $meta;
					}
					
					$result = wp_insert_comment( $comment_args, true );
					
					if ( ! $result ) {
						return array( 'success' => false, 'message' => 'Comment creation failed.', 'args' => $comment_args );
					}
					
					if ( $values['post_run_php'] ) {
						$created_comment = get_comment( $result );
						$evaluate = function( $phpcode ) use ( $arg_map, $created_comment ) {
							extract( $arg_map );
							return @eval( $phpcode );
						};
						
						$custom_result = $evaluate( $values['post_run_phpcode'] );
						if ( $custom_result ) {
							return $custom_result;
						}
					}
					
					return array( 'success' => true, 'message' => 'Comment created successfully.', 'comment_id' => $result, 'args' => $comment_args );
				}
			)),
			
			/* Update A Comment */
			array( 'rules_update_comment', array(
				'title' => 'Update A Comment',
				'description' => 'Update the attributes of an existing comment.',
				'configuration' => array(
					'form' => function( $form, $values, $operation ) use ( $plugin ) {
						
						$form->addField( 'rules_update_attributes', 'choice', array(
							'label' => __( 'Attributes to update', 'mwp-rules' ),
							'choices' => array(
								__( 'Comment Approved', 'mwp-rules' ) => 'comment_approved',
								__( 'Assigned Post', 'mwp-rules' ) => 'post',
								__( 'Parent Comment', 'mwp-rules' ) => 'parent',
								__( 'Author', 'mwp-rules' ) => 'author',
								__( 'Comment Content', 'mwp-rules' ) => 'content',
								__( 'Comment Date', 'mwp-rules' ) => 'date',
								__( 'Meta Data', 'mwp-rules' ) => 'meta',
							),
							'expanded' => true,
							'multiple' => true,
							'data' => isset( $values['rules_update_attributes'] ) ? $values['rules_update_attributes'] : array(),
							'description' => __( 'Choose the attributes that you want to update with this action.', 'mwp-rules' ),
							'toggles' => array(
								'comment_approved' => array( 'show' => array( '#update_comment_approved' ) ),
								'post' => array( 'show' => array( '#rules_update_comment_post_form_wrapper' ) ),
								'parent' => array( 'show' => array( '#rules_update_comment_parent_form_wrapper' ) ),
								'author' => array( 'show' => array( '#rules_update_comment_author_form_wrapper' ) ),
								'content' => array( 'show' => array( '#rules_update_comment_content_form_wrapper' ) ),
								'date' => array( 'show' => array( '#rules_update_comment_date_form_wrapper' ) ),
								'meta' => array( 'show' => array( '#rules_update_comment_meta_form_wrapper' ) ),
							),
						));
						
						$status_choices = array(
							__( 'Approved', 'mwp-rules' ) => 1,
							__( 'Unapproved', 'mwp-rules' ) => 0,
						);
						
						$form->addField( 'comment_approved', 'choice', array(
							'row_attr' => array( 'id' => 'update_comment_approved' ),
							'label' => __( 'Comment Status', 'mwp-rules' ),
							'choices' => $status_choices,
							'expanded' => true,
							'required' => true,
							'data' => isset( $values['comment_approved'] ) ? $values['comment_approved'] : '1',
						));
					},
				),
				'arguments' => array(
					'comment' => array(
						'label' => 'Comment To Update',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The comment to update', 'classes' => array( 'WP_Comment' ) ),
						),
						'configuration' => $plugin->configPreset( 'comment', 'rules_comment', array( 'label' => 'Comment' ) ),
					),
					'post' => array(
						'label' => 'Post Comment Is Assigned To',
						'argtypes' => array(
							'object' => array( 'description' => 'The post to assign the comment to', 'classes' => array( 'WP_Post' ) ),
						),
						'configuration' => $plugin->configPreset( 'post', 'rules_comment_post', array( 'label' => 'Parent Post' ) ),
					),
					'parent' => array(
						'label' => 'Parent Comment',
						'argtypes' => array(
							'object' => array( 'description' => 'The parent comment', 'classes' => array( 'WP_Comment' ) ),
						),
						'configuration' => $plugin->configPreset( 'comment', 'rules_comment_parent', array( 'label' => 'Parent Comment' ) ),
					),
					'author' => array(
						'label' => 'Author',
						'default' => 'manual',
						'argtypes' => array(
							'object' => array( 'description' => 'The author of the comment', 'classes' => array( 'WP_User' ) ),
						),
						'configuration' => $this->commentAuthorConfig(),
					),
					'content' => array(
						'label' => 'Comment Content',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The content of the comment' ),
						),
						'configuration' => $plugin->configPreset( 'textarea', 'rules_comment_content', array( 'label' => 'Comment Content', 'attr' => array( 'style' => 'min-height: 250px' ) ) ),
					),
					'date' => array(
						'label' => 'Comment Date',
						'argtypes' => array(
							'object' => array( 'description' => 'The date for the comment', 'classes' => array( 'DateTime' ) ),
						),
						'configuration' => $plugin->configPreset( 'datetime', 'rules_comment_date', array( 'label' => 'Comment Date' ) ),
					),
					'meta' => array(
						'label' => 'Meta Values',
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An associative array of meta values to add to the comment' ),
						),
						'configuration' => $plugin->configPreset( 'meta_values', 'comment_meta_values' ),
					),
				),
				'callback' => function( $comment, $post, $parent, $author, $content, $date, $meta, $values, $arg_map ) {
					
					if ( ! $comment instanceof \WP_Comment ) {
						return array( 'error' => 'Comment is not an instance of WP_Comment', 'comment' => $comment );
					}
					
					$update_attributes = (array) $values['rules_update_attributes'];
					$comment_args = array( 'comment_ID' => $comment->comment_ID );
					
					if ( in_array( 'comment_approved', $update_attributes ) ) {
						$comment_args['comment_approved'] = $values['comment_approved'];
					}
					
					if ( in_array( 'post', $update_attributes ) ) {
						if ( ! $post instanceof \WP_Post ) {
							return array( 'error' => 'Post is not an instance of WP_Post. Aborted.', 'post' => $post );
						}
						$comment_args['comment_post_ID'] = $post->ID;
					}
					
					if ( in_array( 'parent', $update_attributes ) ) {
						if ( $parent && ! $parent instanceof \WP_Comment ) {
							return array( 'error' => "Parent comment is not an instance of WP_Comment. Aborted.", 'parent' => $parent );
						}
						$comment_args['comment_parent'] = $parent ? $parent->comment_ID : 0;
					}
					
					if ( in_array( 'date', $update_attributes ) ) {
						if ( ! $date instanceof \DateTime ) {
							return array( 'error' => 'Date is not an instance of DateTime. Aborted.', 'date' => $date );
						}
						$comment_args['comment_date'] = date( 'Y-m-d H:i:s', $date->getTimestamp() );
						$comment_args['comment_date_gmt'] = date( 'Y-m-d H:i:s', $date->getTimestamp() );
					}
					
					if ( in_array( 'author', $update_attributes ) ) {
						if ( $values['comment_author_type'] == 'existing' and ! $author instanceof \WP_User ) {
							return array( 'error' => 'Author is not an instance of WP_User. Aborted.', 'author' => $author );
						}
						if ( is_array( $author ) ) {
							$comment_args['comment_author'] = isset( $author['name'] ) ? $author['name'] : '';
							$comment_args['comment_author_email'] = isset( $author['email'] ) ? $author['email'] : '';
							$comment_args['comment_author_url'] = isset( $author['url'] ) ? $author['url'] : '';
							$comment_args['user_id'] = 0;
						} else {
							$comment_args['comment_author'] = '';
							$comment_args['comment_author_email'] =  '';
							$comment_args['comment_author_url'] =  '';
							$comment_args['user_id'] = $author->ID;
						}
					}
					
					if ( in_array( 'content', $update_attributes ) ) {
						if ( empty( $content ) ) {
							return array( 'error' => 'Comment content is empty. Aborted.' );
						}
						$comment_args['comment_content'] = $content;
					}
					
					$result = wp_update_comment( $comment_args, true );
					
					if ( ! $result ) {
						return array( 'success' => false, 'message' => 'Comment update failed.', 'args' => $comment_args );
					}
					
					if ( in_array( 'meta', $update_attributes ) and is_array( $meta ) ) {
						foreach( $meta as $key => $value ) {
							update_comment_meta( $comment->comment_ID, $key, $value );
						}
					}
					
					return array( 'success' => true, 'message' => 'Comment updated successfully.', 'args' => $comment_args );

				}
			)),
			
			/* Delete A Comment */
			array( 'rules_delete_comment', array(
				'title' => 'Trash/Delete A Comment',
				'description' => 'Delete or trash a comment.',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$form->addField( 'rules_comment_trash', 'choice', array(
							'label' => __( 'Delete Method', 'mwp-rules' ),
							'choices' => array(
								__( 'Move to trash only', 'mwp-rules' ) => 'trash',
								__( 'Delete permanently', 'mwp-rules' ) => 'delete',
							),
							'data' => isset( $values['rules_comment_trash'] ) ? $values['rules_comment_trash'] : 'trash',
							'expanded' => true,
							'required' => true,
						));
					},
				),
				'arguments' => array(
					'comment' => array(
						'label' => 'Comment To Delete',
						'required' => true,
						'argtypes' => array(
							'object' => array( 'description' => 'The comment to delete', 'classes' => array( 'WP_Comment' ) ),
						),
						'configuration' => $plugin->configPreset( 'comment', 'rules_comment', array( 'label' => 'Comment To Delete' ) ),
					),
				),
				'callback' => function( $comment, $values ) {
					
					if ( ! $comment instanceof \WP_Comment ) {
						return array( 'error' => 'Comment is not an instance of WP_Comment', 'comment' => $comment );
					}
					
					$force_delete = ( isset( $values['rules_comment_trash'] ) and $values['rules_comment_trash'] == 'delete' ) ? true : false;
					$result = wp_delete_comment( $comment->comment_ID, $force_delete );
					
					if ( $result ) {
						return array( 'success' => true, 'message' => ( $force_delete ? 'Comment permanently deleted.' : 'Comment moved to trash.' ), 'comment' => $comment, 'forced' => $forced_delete );
					}
					
					return array( 'success' => false, 'message' => 'Delete failed.', 'comment' => $comment, 'forced' => $forced_delete );
				},
			)),			
		));
		
	}	
}
