<?php
/**
 * Settings Class File
 *
 * @vendor: Miller Media
 * @package: MWP Rules
 * @author: Kevin Carwile
 * @link: http://millermedia.io
 * @since: December 4, 2017
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Plugin Settings
 *
 * @MWP\WordPress\Options
 * @MWP\WordPress\Options\Section( title="General Settings" )
 * @MWP\WordPress\Options\Field( name="setting1", type="text", title="Setting 1" )
 * @MWP\WordPress\Options\Field( name="setting2", type="select", title="Setting 2", options={ "opt1":"Option1", "opt2": "Option2" } )
 * @MWP\WordPress\Options\Field( name="setting3", type="select", title="Setting 3", options="optionsCallback" )
 */
class Settings extends \MWP\Framework\Plugin\Settings
{
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string	Settings Access Key ( default: main )
	 */
	public $key = 'main';
	
	/**
	 * Example Options Generator
	 * @see: class annotation for setting3
	 *
	 * @param		mixed			$currentValue				Current settings value
	 * @return		array
	 */ 
	public function optionsCallback( $currentValue )
	{
		return array
		(
			'opt3' => 'Option 3',
			'opt4' => 'Option 4',
		);
	}
		
}