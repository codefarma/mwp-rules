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
 * System Class
 */
class System
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
			
			/* Execute Custom PHP Code */
			array( 'rules_execute_php', array(
				'title' => 'Execute custom PHP code',
				'description' => 'Run a custom block of php code.',
				'configuration' => array(
					'form' => function( $form, $saved_values, $operation ) use ( $plugin ) {
						$form->addField( 'rules_custom_phpcode', 'textarea', array(
							'row_prefix' => '<hr>',
							'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $saved_values['rules_custom_phpcode'] ) ? $saved_values['rules_custom_phpcode'] : "// <?php\n\nreturn;",
							'description' => $plugin->getTemplateContent( 'rules/phpcode_description', array( 'operation' => $operation, 'event' => $operation->event() ) ),
						));
					}
				),
				'callback' => function( $saved_values, $event_args, $operation ) {
					$evaluate = function( $phpcode ) use ( $event_args, $operation ) {
						extract( $event_args );
						return @eval( $phpcode );
					};
					
					return $evaluate( $saved_values[ 'rules_custom_phpcode' ] );
				},
			)),
			
			/* Modify the value being filtered */
			array( 'rules_modify_filtered_value', array(
				'title' => 'Modify the filtered value',
				'description' => 'Change the value being filtered in a hook.',
				'updates_filter' => true,
				'arguments' => array(
					'new_value' => array(
						'required' => true,
						'default' => 'manual',
						'argtypes' => array(
							'mixed' => array( 'description' => 'The new value' ),
						),
						'configuration' => array(
							'form' => function( $form, $saved_values, $operation ) {
								
							}
						),
					),
				),
				'callback' => function( $new_value, $saved_values, $event_args, $operation ) {
					$operation->rule()->setReturnValue( $new_value );
					return $new_value;
				}
			)),
			
			/* Send an email */
			array( 'rules_send_email', array( 
				'title' => 'Send an email',
				'description' => 'Send an email to a user or users',
				'configuration' => array(
					'form' => function( $form, $values ) {
						$form->addField( 'rules_email_from_source', 'choice', array( 
							'label' => 'Email "From" Source',
							'choices' => array(
								'Site Default' => 'default',
								'Custom Value' => 'custom',
							),
							'data' => isset( $values['rules_email_from_source'] ) ? $values['rules_email_from_source'] : 'default',
							'required' => true,
							'expanded' => true,
							'toggles' => array(
								'custom' => array( 'show' => array( '#rules_email_from' ) ),
							),
						));
						
						$form->addField( 'rules_email_from', 'email', array(
							'row_attr' => array( 'id' => 'rules_email_from' ),
							'label' => __( 'From Address', 'mwp-rules' ),
							'description' => __( 'Enter an email address.', 'mwp-rules' ),
							'data' => isset( $values['rules_email_from'] ) ? $values['rules_email_from'] : '',
						));
						$form->addField( 'rules_email_from_name', 'text', array(
							'row_attr' => array( 'id' => 'rules_email_from' ),
							'label' => __( 'From Name', 'mwp-rules' ),
							'description' => __( 'Enter the name of who this email is from.', 'mwp-rules' ),
							'data' => isset( $values['rules_email_from_name'] ) ? $values['rules_email_from_name'] : '',
						));
					},
				),
				'arguments' => array(
					'to' => array(
						'label' => 'Email Recipients',
						'argtypes' => array(
							'array' => array( 'description' => 'an array of email addresses to send email to' ),
							'string' => array( 'description' => 'an individual email address, or comma delimited list of addresses to send mail to' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_email_to', 'textarea', array(
									'label' => __( 'Email Recipients', 'mwp-rules' ),
									'description' => __( 'Enter a comma delimited list of email addresses.', 'mwp-rules' ),
									'data' => isset( $values['rules_email_to'] ) ? $values['rules_email_to'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_email_to'];
							},
						),
					),
					'subject' => array(
						'label' => 'Email Subject Line',
						'default' => 'manual',
						'argtypes' => array( 'string' => array( 'description' => 'The email subject line to send' ) ),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_email_subject', 'text', array( 
									'label' => __( 'Email Subject', 'mwp-rules' ),
									'data' => isset( $values['rules_email_subject'] ) ? $values['rules_email_subject'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_email_subject'];
							},
						),
					),
					'message' => array(
						'label' => 'Email Message',
						'default' => 'manual',
						'argtypes' => array(
							'string' => array( 'description' => 'The email message to send' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_email_message', 'textarea', array(
									'label' => __( 'Message', 'mwp-rules' ),
									'description' => __( 'Enter the email message to send.', 'mwp-rules' ),
									'data' => isset( $values['rules_email_message'] ) ? $values['rules_email_message'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_email_message'];
							},
						),						
					),
					'headers' => array(
						'label' => 'Email Headers',
						'default' => 'manual',
						'argtypes' => array(
							'array' => array( 'description' => 'An array of email headers to send' ),
							'string' => array( 'description' => 'A list of headers to send each on a new line in the format "Header-Name: header-value"' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_email_headers', 'textarea', array(
									'label' => __( 'Headers', 'mwp-rules' ),
									'description' => __( 'Enter one email header per line in the format "Header-Name: header-value".', 'mwp-rules' ),
									'data' => isset( $values['rules_email_headers'] ) ? $values['rules_email_headers'] : '',
								));
							},
							'getArg' => function( $values ) {
								return explode( "\n", str_replace( "\r\n", "\n", $values['rules_email_headers'] ) );
							},
						),						
					),
				),
				'callback' => function( $to, $subject, $message, $headers, $values ) {
					$custom_from = $values['rules_email_from_source'] == 'custom' and $values['rules_email_from'];
					if ( $custom_from ) {
						$change_mail_from = function() use ( $values ) { return $values['rules_email_from']; };
						$change_mail_from_name = function() use ( $values ) { return $values['rules_email_from_name']; };
						add_filter( 'wp_mail_from', $return_mail_from );
						add_filter( 'wp_mail_from_name', $return_mail_from_name );
					}
					wp_mail( $to, $subject, $message, $headers );
					if ( $custom_from ) {
						remove_filter( 'wp_mail_from', $return_mail_from );
						remove_filter( 'wp_mail_from_name', $return_mail_from_name );
					}
					
					return array( 'to' => $to, 'subject' => $subject );
				}
			)),
			
			/* Redirect the page */
			array( 'rules_redirect', array(
				'title' => 'Redirect the page',
				'description' => 'Issue an HTTP redirect to another page',
				'arguments' => array(
					'status' => array(
						'label' => 'HTTP Status',
						'default' => 'manual',
						'argtypes' => array(
							'int' => array( 'description' => 'The HTTP status code to redirect with' ),
						),
						'configuration' => array( 
							'form' => function( $form, $values ) {
								$form->addField( 'rules_redirect_status', 'choice', array(
									'label' => 'Status Codes',
									'choices' => array(
										'Permanently Redirect (301)' => 301,
										'Temporary Redirect (302)' => 302,
									),
									'data' => isset( $values['rules_redirect_status'] ) ? $values['rules_redirect_status'] : 302,
									'expanded' => true,
									'required' => true,
								));
							},
							'getArg' => function( $values ) {
								return isset( $values['rules_redirect_status'] ) ? $values['rules_redirect_status'] : 302;
							},
						),
					),
					'url' => array(
						'label' => 'Redirect URL',
						'default' => 'manual',
						'argtypes' => array( 
							'object' => array( 'description' => 'The url to redirect to', 'class' => 'MWP\Rules\WP\Url' ),
						),
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_redirect_url', 'url', array(
									'label' => __( 'Redirect URL', 'mwp-rules' ),
									'description' => __( 'Enter the url to redirect the page to.', 'mwp-rules' ),
									'data' => isset( $values['rules_redirect_url'] ) ? $values['rules_redirect_url'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_redirect_url'];
							},
						),
					),
				),
				'callback' => function( $status, $url ) {
					wp_redirect( (string) $url, $status );
					exit;
				},
			)),
			
			/* Unschedule an action */
			array( 'rules_unschedule_action', array(
				'title' => 'Unschedule an action',
				'description' => 'Check for and remove a scheduled action by key.',
				'arguments' => array(
					'action_key' => array(
						'label' => 'Action key to unschedule',
						'default' => 'manual',
						'configuration' => array(
							'form' => function( $form, $values ) {
								$form->addField( 'rules_action_key', 'text', array(
									'label' => 'Action Key',
									'data' => isset( $values['rules_action_key'] ) ? $values['rules_action_key'] : '',
								));
							},
							'getArg' => function( $values ) {
								return $values['rules_action_key'];
							},
						),
					),
				),
				'callback' => function( $action_key ) {
					if ( $action_key ) {
						$count = \MWP\Rules\ScheduledAction::countWhere( array( 'schedule_key=%s', $action_key ) );
						\MWP\Rules\ScheduledAction::deleteWhere( array( 'schedule_key=%s', $action_key ) );
						return 'Deleted ' . $count . ' scheduled actions.';
					}
					
					return 'no action key specified';
				},
			)),
			
		));
		
	}
}
