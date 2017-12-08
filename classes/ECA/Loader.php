<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\ECA;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Loader Class
 */
class Loader
{
	/**
	 * @var	string 
	 */
	protected $class;
	
	/**
	 * @var	mixed 
	 */
	protected $instance;
	
	/**
	 * Constructor
	 *
	 * @param	string			$class				The class of the eca being kept
	 * @param	mixed			$instance			The eca instance or configuration 
	 * @return	void
	 */
	public function __construct( $class, $instance )
	{
		$this->class = $class;
		$this->instance = $instance;
	}
	
	/**
	 * Get the instance
	 */
	public function instance()
	{
		if ( is_callable( $this->instance ) ) {
			$this->instance = call_user_func( $this->instance );
		}
		
		if ( ! is_object( $this->instance ) ) 
		{
			$class = $this->class;
			$this->instance = new $class( $this->instance );
		}
		
		return $this->instance;
	}
}
