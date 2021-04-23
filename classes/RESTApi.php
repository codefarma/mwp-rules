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
     * @return bool
     */
	private function validate ( $type, $value )
    {
        switch ( $type ) {
            case 'int':
            case 'float':
                return is_numeric($value);

            case 'string':
                return is_string($value);

            case 'object':
                return is_object(json_decode($value));

            case 'bool':
            case 'array':
            case 'mixed':
            default:
                return is_scalar($value);
        }
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
        switch ( $type ) {
            case 'int':
                $value = intval($value);
                break;

            case 'float':
                $value = floatval($value);
                break;

            case 'object':
                $value = json_decode($value);
                break;

            case 'bool':
                if ( strtolower($value) === "false" ) {
                    $value = false;
                }

                $value = boolval($value);
                break;

            case 'array':
                $value = explode(",", $value);
                break;

            case 'string':
            case 'mixed':
            default:
                break;
        }

        return $value;
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
            if ( isset( $definition['arguments'] ) && is_array( $definition['arguments'] ) ) {
                foreach ( $definition['arguments'] as $arg => $details ) {
                    $rest_args[$arg] = $this->getArgConfig($details);
                }
            }

            $route = '/'.$hook.'/';
            register_rest_route('mwp-rules/v1', $route, array(
                'methods' => 'GET', // @todo: get from configuration
                'callback' => function ( \WP_REST_Request $request ) use ( $hook ) {
                    call_user_func_array(
                        'do_action',
                        array_merge(array($hook), array_values($request->get_params()))
                    );

                    // @todo: determine return result programmatically
                    $response = new \WP_REST_Response();
                    $response->set_data(array( 'result' => 'success' ));
                    $response->set_status(201);

                    return $response;
                },
                'args' => $rest_args,
//                'permission_callback' => function ( \WP_REST_Request $request ) {
//                    return current_user_can( 'edit_others_posts' ); // @todo: get from configuration
//                },
            ));
        }
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

        return array(
            'required' => isset($details['required']) && $details['required'],
            'validate_callback' => function( $param ) use ( $argTypes ) {
                return $this->validateParam($param, $argTypes);
            },
            'sanitize_callback' => function( $param ) use ( $argTypes ) {
                return $this->sanitizeValue($param, $argTypes);
            }
        );
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
            if ( $this->validate($type, $value) ) {
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
            if ( $this->validate($type, $value) ) {
                return $this->sanitize($type, $value, $info);
            }
        }

        return $value;
    }


}
