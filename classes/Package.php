<?php
/**
 * Package Class
 *
 * Created:   September 26, 2018
 *
 * @package:  Automation Rules
 * @author:   Code Farma
 * @since:    1.1.4
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Package
 */
class _Package
{
	/**
	 * @var	string
	 */
	protected $id;
	
	/**
	 * @var array
	 */
	protected $package_data;
	
	/**
	 * @var	array
	 */
	protected $package_details;
	 
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
	 * Constructor
	 * 
	 * @param	array		$package_data			The package data 
	 * @param	array		$package_details			Details about the package source
	 * @return	void
	 */
	public function __construct( $package_data, $package_details=[] )
	{
		if ( ! is_array( $package_data ) or ! isset( $package_data['rules_version'] ) ) {
			throw new \InvalidArgumentException( 'Invalid rules package data' );
		}
		
		$this->package_data = $package_data;
		$this->package_details = $package_details;
	}
	
	/**
	 * Load a package from a file
	 *
	 * @param	string		$file					The path to the file to load
	 * @param	array		$package_details			Details about the package source
	 * @return	Package
	 * @throws	InvalidArgumentException
	 */
	public function loadFromFile( $file, $package_details=[] )
	{
		if ( ! is_file ( $file ) ) {
			throw new \InvalidArgumentException( 'Invalid file.' );
		}
		
		$package_data = json_decode( file_get_contents( $file ), TRUE );
		
		if ( ! isset( $package_details['title'] ) ) {
			$package_details['title'] = ucwords( str_replace( '-', ' ', basename( $file, '.json' ) ) );
		}
		
		return new Package( $package_data, $package_details );
	}
	
	/**
	 * Get raw package data
	 *
	 * @return	array
	 */
	public function getPackageData()
	{
		return $this->package_data;
	}
	
	/**
	 * Get the package title
	 *
	 * @return	string
	 */
	public function getTitle()
	{
		return isset( $this->package_details['title'] ) ? $this->package_details['title'] : 'Untitled';
	}
	
	/**
	 * Get the package title
	 *
	 * @return	string
	 */
	public function getId()
	{
		if ( isset( $this->id ) ) {
			return $this->id;
		}
		
		$this->id = md5( $this->package_data );
		
		return $this->id;
	}
	
	/**
	 * Import all of the packaged data
	 * 
	 * @return	array
	 */
	public function importAll()
	{
		$results = [];
		$package_data = $this->getPackageData();
		
		if ( isset( $package_data['hooks'] ) ) {
			foreach( $package_data['hooks'] as $hook ) {
				$results = array_merge_recursive( $results, Hook::import( $hook ) );
			}
		}
		
		if ( isset( $package_data['logs'] ) ) {
			foreach( $package_data['logs'] as $log ) {
				$results = array_merge_recursive( $results, CustomLog::import( $log ) );
			}
		}
		
		if ( isset( $package_data['rules'] ) ) {
			foreach( $package_data['rules'] as $rule ) {
				$results = array_merge_recursive( $results, Rule::import( $rule ) );
			}
		}
		
		if ( isset( $package_data['bundles'] ) ) {
			foreach( $package_data['bundles'] as $bundle ) {
				$results = array_merge_recursive( $results, Bundle::import( $bundle ) );
			}
		}
		
		if ( isset( $package_data['apps'] ) ) {
			foreach( $package_data['apps'] as $app ) {
				$results = array_merge_recursive( $results, App::import( $app ) );
			}
		}
		
		return $results;
	}
	
}
