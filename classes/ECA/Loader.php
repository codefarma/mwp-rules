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
	 * @var array
	 */
	public $extras = array();
	
	/**
	 * Constructor
	 *
	 * @param	string			$class				The class of the eca being kept
	 * @param	mixed			$instance			The eca instance or configuration 
	 * @param	array			$extras				Extra data to pass along to loaded instance
	 * @return	void
	 */
	public function __construct( $class, $instance, $extras=array() )
	{
		$this->class = $class;
		$this->instance = $instance;
		$this->extras = $extras;
	}
	
	/**
	 * Get the instance
	 */
	public function instance()
	{
		if ( is_callable( $this->instance ) ) {
			$this->instance = call_user_func( $this->instance );
		}
		
		if ( is_array( $this->instance ) ) 
		{
			$class = $this->class;
			$this->instance = new $class( array_merge( $this->extras, $this->instance ) );
		}
		
		return $this->instance;
	}
}
