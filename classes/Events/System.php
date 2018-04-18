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
 * System Class
 */
class _System
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
		rules_describe_events( array(
			
			/* Pre setup theme */
			array( 'action', 'setup_theme', array(
				'title' => 'Theme Is Being Setup (Before)',
				'description' => 'Fires before the theme is loaded.',
				'group' => 'Initialization',
			)),
			
			/* Post setup theme */
			array( 'action', 'after_setup_theme', array(
				'title' => 'Theme Is Being Setup (After)',
				'description' => 'Fires after the theme is loaded.',
				'group' => 'Initialization',
			)),
			
			/* Init */
			array( 'action', 'init', array(
				'title' => 'WordPress Is Being Initialized',
				'description' => 'The init hook is fired just after all plugins have been loaded.',
				'group' => 'Initialization',
			)),
			
			/* Wordpress Loaded */
			array( 'action', 'wp_loaded', array( 
				'title' => 'WordPress Is Loaded',
				'description' => 'This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.',
				'group' => 'Initialization',
			)),
			
			/* Template Redirect */
			array( 'action', 'template_redirect', array(
				'title' => 'Page Template Is Being Loaded',
				'description' => 'This event occurs just before the template for the current page is loaded.',
				'group' => 'Initialization',
			)),
			
			/* Wordpress Shutdown */
			array( 'action', 'shutdown', array( 
				'title' => 'WordPress Is Shutting Down',
				'description' => 'This event occurs just before PHP shuts down execution.',
				'group' => 'Shutdown',
			)),
			
			/* Document Title */
			array( 'filter', 'document_title_parts', array(
				'title' => 'Document Title Is Being Filtered',
				'description' => 'The document title is the page title which appears in the browser when a page is viewed.',
				'group' => 'Output',
				'arguments' => array(
					'parts' => array( 
						'argtype' => 'array',
						'label' => 'Document Title Parts', 
						'description' => 'The individual parts of the document title.' ,
						'keys' => array(
							'mappings' => array(
								'title'   => array( 'argtype' => 'string', 'label' => 'Title', 'description' => 'Title of the viewed page.' ),
								'page'    => array( 'argtype' => 'string', 'label' => 'Page Number', 'description' => 'Page number if the page is paginated.' ),
								'tagline' => array( 'argtype' => 'string', 'label' => 'Tagline', 'description' => 'Site description when on home page.' ),
								'site'    => array( 'argtype' => 'string', 'label' => 'Site Title', 'description' => 'Title of the site when not on home page.' ),
							),
						),
					),
				),
			)),
			
			/* WP Mail */
			array( 'filter', 'wp_mail', array(
				'title' => 'Email Is Being Sent',
				'description' => 'The wp_mail filter hook allows you to filter the arguments that are passed to the wp_mail() function.',
				'group' => 'Email',
				'arguments' => array(
					'mail' => array(
						'argtype' => 'array',
						'label' => 'WP Mail Arguments',
						'description' => 'A compacted array of wp_mail() arguments.',
						'keys' => array(
							'mappings' => array(
								'to' => array( 
									'argtype' => 'array',
									'label' => 'To Email',
									'description' => 'Array or comma-separated list of email addresses to send message.',
									'converter' => function( $to ) { return is_string( $to ) ? explode(',', $to) : $to; },
								),
								'subject'     => array( 'argtype' => 'string', 'label' => 'Subject', 'description' => 'Email subject.' ),
								'message'     => array( 'argtype' => 'string', 'label' => 'Message', 'description' => 'Email message content.' ),
								'headers'     => array( 'argtype' => 'array',  'label' => 'Headers', 'description' => 'Additional headers.', 'converter' => function( $val ) { return (array) $val; } ),
								'attachments' => array( 'argtype' => 'array', 'label' => 'Attachments', 'description' => 'Files to attach.', 'converter' => function( $val ) { return (array) $val; } ),
							),
						),
					),
				),
			))
			
		));
	}
}
