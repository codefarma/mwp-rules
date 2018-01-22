<?php
/**
 * Plugin Class File
 *
 * Created:   January 14, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\WP;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Url Class
 */
class Url
{
	/**
	 * @var	string
	 */
	public $url;
	
	/**
	 * @var	array
	 */
	public $components;
	 
	/**
	 * Constructor
	 *
	 * @param	string			$url			The url
	 * @return	void
	 */
	public function __construct( $url=NULL ) { 
		if ( ! isset( $url ) ) {
			$url = add_query_arg();
		}
		
		$this->url = $url;		
		$this->parse();
	}
	
	/**
	 * Parse the url
	 *
	 * @return	void
	 */
	public function parse()
	{
		$this->components = parse_url( $this->url );
	}
	
	/**
	 * Get a component
	 *
	 * @return	string|NULL
	 */
	public function getComponent( $component )
	{
		$value = null;
		
		if ( isset( $this->components[ $component ] ) ) {
			$value = $this->components[ $component ];
			
			if ( $component == 'query' ) {
				$query = array();
				foreach( explode( '&', $value ) as $pair ) {
					$parts = explode( '=', $pair );
					$query[ $parts[0] ] = $parts[1];
				}
				$value = $query;
			}
		}
		
		return $value;
	}
	
	/**
	 * Adjust the query args
	 *
	 * @param	array		$query			Query args to adjust
	 */
	public function addQueryArgs( $query )
	{
		$this->url = add_query_arg( $query, $this->url );
		$this->parse();
	}
	
	/**
	 * Convert to string
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return $this->url;
	}
	
}
