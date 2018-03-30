<?php
/**
 * Plugin Class File
 *
 * Created:   January 14, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\WP;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Site Class
 */
class _Site
{
	/**
	 * @var int
	 */
	protected static $instance;
	
	/**
	 * Get instance
	 *
	 * @return	self
	 */
	public function instance()
	{
		if ( isset( static::$instance ) ) {
			return static::$instance;
		}
		
		static::$instance = new static;
		return static::$instance;
	}
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	protected function __construct() { }
	
	/**
	 * Get blog info
	 *
	 * @return	string
	 */
	public function __get( $key )
	{
		return get_bloginfo( $key );
	}
}
