<?php
/**
 * RESTApi Class [Singleton]
 *
 * Created:   April 22, 2021
 *
 * @package:  Automation Rules
 * @author:   Code Farma
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\Singleton;

/**
 * RESTApi
 */
class _RESTApi extends Singleton
{
	/**
	 * @var self
	 */
	protected static $_instance;
	
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
		if ( isset( $this->plugin ) ) {
			return $this->plugin;
		}
		
		$this->setPlugin( \MWP\Rules\Plugin::instance() );
		
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
	 * Validate a value against the given type.
	 *
	 * @param $type
	 * @param $value
	 * @param $info
	 * @return bool
	 */
	private function validate ( $type, $value, $info )
	{
		$validated = false;

		switch ( $type ) {
			case 'int':
			case 'float':
				$validated = is_numeric($value);
				break;

			case 'string':
				$validated = is_string($value);
				break;

			case 'object':
				$validated = is_object($value);
				if ( !$validated ) {
					if ( !isset($info['classes']) || !$info['classes'] ) {
						if ( is_string($value) ) {
							$validated = is_object(json_decode($value));
						}

						if ( is_array($value) ) {
							$validated = is_object((object) $value);
						}
					}

					if ( is_array($info['classes']) ) {
						$validated = is_scalar($value);
					}
				}

				break;

			case 'array':
				$validated = is_array($value) || is_string($value);
				break;

			case 'bool':
				$validated = is_bool($value) || is_scalar($value);
				break;

			case 'mixed':
			default:
				$validated = is_scalar($value);
				break;
		}

		return apply_filters( 'mwp_rules_validated_value', $validated, $type, $value, $info );
	}

	/**
	 * Sanitize a value according to the given type.
	 *
	 * @param $type
	 * @param $value
	 * @param $info
	 * @return bool
	 */
	private function sanitize ( $type, $value, $info )
	{
		$sanitized = $value;

		switch ( $type ) {
			case 'int':
				$sanitized = intval($value);
				break;

			case 'float':
				$sanitized = floatval($value);
				break;

			case 'object':
				if ( !is_object($value) ) {
					if ( !isset($info['classes']) || !$info['classes'] ) {
						if ( is_string($value) ) {
							$sanitized = json_decode($value);
						}

						if ( is_array($value) ) {
							$sanitized = (object) $value;
						}
					}

					if ( is_array($info['classes']) ) {
						foreach ( $info['classes'] as $class ) {
							if ( $classMapping = $this->getPlugin()->getClassMappings($class) ) {
								$sanitized = $classMapping['loader']($value);
								break;
							}
						}
					}
				}

				break;

			case 'bool':
				if ( !is_bool($value) ) {
					$_value = $value;

					if ( is_string($_value) ) {
						if ( strtolower($_value) === "false" ) {
							$_value = false;
						}
					}

					$sanitized = boolval($_value);
				}

				break;

			case 'array':
				if ( !is_array($value) ) {
					if ( is_string($value) ) {
						$sanitized = explode(",", $value);
					}
				}

				break;

			case 'string':
			case 'mixed':
			default:
				break;
		}

		return apply_filters( 'mwp_rules_sanitized_value', $sanitized, $type, $value, $info );
	}

	/**
	 * Register API endpoints for custom actions.
	 *
	 * @MWP\WordPress\Action( for="rest_api_init" )
	 */
	public function registerCustomActionEndpoints()
	{
		$custom_hooks = $this->getPlugin()->getCustomHooks();

		if ( !isset( $custom_hooks['actions'] ) || !is_array( $custom_hooks['actions'] ) ) {
			return;
		}

		foreach( $custom_hooks['actions'] as $hook => $info ) {
			if ( !isset( $info['definition'] ) || !is_array( $info['definition'] ) ) {
				continue;
			}

			$definition = $info['definition'];
			if ( !isset( $definition['hook_data'] ) || !is_array( $definition['hook_data'] ) ) {
				continue;
			}

			$hook_data = $definition['hook_data'];
			if ( !isset( $hook_data['hook_enable_api'] ) || $hook_data['hook_enable_api'] != "1" ) {
				continue;
			}

			$rest_args = array();
			$arguments = isset( $definition['arguments'] ) ? $definition['arguments'] : null;
			if ( is_array( $arguments ) ) {
				foreach ( $arguments as $arg => $details ) {
					$rest_args[$arg] = $this->getArgConfig($details);
				}
			}

			$api_roles = explode(",", $hook_data['hook_api_roles']);
			$route = '/'.$hook.'/';

			register_rest_route('mwp-rules/v1', $route, array(
				'methods' => $this->getRESTMethods($hook_data['hook_api_methods']),
				'callback' => function ( \WP_REST_Request $request ) use ( $hook, $arguments ) {
					try {
						call_user_func_array(
							'do_action',
							array_merge(
								array($hook),
								$this->getRESTArguments($request, $arguments)
							)
						);
					} catch ( \Exception $e ) {
						return new \WP_Error(
							'mwp_rest_error',
							'An unexpected error has occurred.',
							array(
								'hook' => $hook,
								'request_params' => $request->get_params()
							)
						);
					}


					// @TODO: determine return result programmatically - in the future
					$response = new \WP_REST_Response();
					$response->set_data(array( 'result' => 'success' ));
					$response->set_status(201);

					return $response;

				},
				'args' => $rest_args,
				'permission_callback' => function ( \WP_REST_Request $request ) use ( $api_roles ) {
					return $this->checkRESTPermissions($api_roles);
				},
			));
		}
	}

	/**
	 * Get an array of REST methods.
	 *
	 * @param string|null $methods  A comma-separated list of REST operations (e.g. 'get,post,delete')
	 * @return string
	 */
	public function getRESTMethods ( $methods=null )
	{
		if ( !$methods ) {
			return \WP_REST_Server::READABLE;
		}

		return apply_filters( 'mwp_rules_rest_methods', implode(",", array_map( function ( $m ) {
			$RESTMethods = array(
				'get'       => \WP_REST_Server::READABLE,
				'post'      => \WP_REST_Server::EDITABLE,
				'delete'    => \WP_REST_Server::DELETABLE
			);

			return $RESTMethods[$m];
		}, explode(",", $methods) ) ) );
	}

	/**
	 * Get an array of argument values for a given request
	 * and list of expected arguments.
	 *
	 * @param $request      \WP_REST_Request
	 * @param $arguments    array
	 * @return array
	 */
	public function getRESTArguments ( $request, $arguments )
	{
		if ( !is_array($arguments) ) {
			return array();
		}

		$argMap = array();
		foreach( $arguments as $k => $v ) {
			$argMap[$k] = null;
		}

		$args = array_values( array_merge( $argMap, $request->get_params() ) );

		return apply_filters( 'mwp_rules_rest_arguments', $args, $request, $arguments, $argMap );
	}

	/**
	 * Check the current user's assigned roles against those configured on the custom action
	 * to determine whether to allow access to the REST endpoint.
	 *
	 * @param $roles array  List of slugs for allowed user roles (e.g. administrator, editor, etc)
	 * @return bool         Whether the current user is permitted to access the REST endpoint
	 */
	public function checkRESTPermissions( $roles )
	{
		$user = wp_get_current_user();
		if ( empty( $user ) ) {
			return apply_filters( 'mwp_rules_rest_permissions', false, $user, $roles );
		}

		foreach ( $user->roles as $role ) {
			if ( in_array( $role, $roles ) ) {
				return apply_filters( 'mwp_rules_rest_permissions', true, $user, $roles );
			}
		}

		return apply_filters( 'mwp_rules_rest_permissions', false, $user, $roles );
	}

	/**
	 * Get argument configuration settings formatted for a WP REST endpoint.
	 *
	 * @param $details array    The argument details as configured in the custom action
	 * @return array
	 */
	public function getArgConfig ( $details )
	{
		$argTypes = isset($details['argtypes']) ? $details['argtypes'] : array();

		return apply_filters( 'mwp_rules_arg_config', array(
			'required' => isset($details['required']) && $details['required'],
			'validate_callback' => function( $param ) use ( $argTypes ) {
				return $this->validateParam($param, $argTypes);
			},
			'sanitize_callback' => function( $param ) use ( $argTypes ) {
				return $this->sanitizeValue($param, $argTypes);
			}
		), $details );
	}

	/**
	 * Validate a value given a set of types.
	 *
	 * @param $value string|int     The value to be validated
	 * @param $argTypes array       The types to be validated against
	 * @return bool
	 */
	public function validateParam( $value, $argTypes )
	{
		foreach ( $argTypes as $type => $info ) {
			if ( $this->validate($type, $value, $info) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitize a value given a set of types.
	 *
	 * @param $value string|int     The value to be sanitized
	 * @param $argTypes array       The types to be sanitized against
	 * @return mixed
	 */
	public function sanitizeValue( $value, $argTypes )
	{
		foreach ( $argTypes as $type => $info ) {
			if ( $this->validate($type, $value, $info) ) {
				return $this->sanitize($type, $value, $info);
			}
		}

		return $value;
	}


}