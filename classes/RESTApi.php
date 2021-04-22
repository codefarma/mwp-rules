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
     * Register API endpoints for custom actions.
     *
     * @MWP\WordPress\Action( for="rest_api_init" )
     */
    public function registerCustomActionEndpoints()
    {
        $custom_hooks = $this->getPlugin()->getCustomHooks();

        if ( isset( $custom_hooks['actions'] ) ) {
            foreach( $custom_hooks['actions'] as $hook => $info ) {
                if ( isset( $info['definition'] ) ) {
                    $definition = $info['definition'];

                    if ( isset( $definition['hook_data'] ) ) {
                        if ($definition['hook_data']['hook_enable_api'] == "1") {
                            $route = '/'.$hook.'/';
                            if ( $arguments = $definition['arguments'] ) {
                                foreach ( $arguments as $arg => $details ) {
                                    var_dump($details);
                                    $route .= '(?P<'.$arg.'>\d+)'; // @todo: do not hard-code the argument type; get from $details
                                }
                            }

                            register_rest_route('mwp-rules/v1', $route, array(
                                'methods' => 'GET', // @todo: get from configuration
                                'callback' => function ( \WP_REST_Request $request ) use ( $hook ) {
                                    call_user_func_array(
                                        'do_action',
                                        array_merge(array($hook), array_values($request->get_params()))
                                    );

                                    $response = new \WP_REST_Response();
                                    $response->set_data('Success');
                                    $response->set_status(200);

                                    return $response; // @todo: determine return result programmatically
                                },
//                                 @todo: validate args, add permission check(https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/)
//                                'args' => array()
//                                'permission_callback' => function ( \WP_REST_Request $request ) {
//                                    return current_user_can( 'edit_posts' ); // @todo: get from configuration
//                                },
                            ));

                        }
                    }
                }
            }
        }
    }

}
