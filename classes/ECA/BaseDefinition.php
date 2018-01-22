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
namespace MWP\Rules\ECA;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * BaseDefinition Class
 */
class BaseDefinition
{
	/**
	 * Constructor
	 *
	 * @param	array		$attributes			Attributes of the eca
	 * @return	void
	 */
	public function __construct( $attributes )
	{
		foreach( $attributes as $key => $value ) {
			$this->set( $key, $value );
		}
	}
	
	/**
	 * Set an object property
	 * 
	 * @param	string		$name			Property name
	 * @param	mixed		$value			Property value
	 * @return	void
	 */
	public function set( $name, $value )
	{
		$this->$name = $value;
	}
	
	/**
	 * Get an object property
	 * 
	 * @param	string		$name			Property name
	 * @param	mixed		$value			Property value
	 * @return	void
	 */
	public function get( $name )
	{
		return $this->$name;
	}
	
	/**
	 * Check if this definition has a callback
	 */
	public function hasCallback()
	{
		return isset( $this->callback ) and is_callable( $this->callback );
	}
}
