<?php
/**
 * Plugin Class File
 *
 * Created:   December 12, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Helpers\ActiveRecordController;
use MWP\Rules;

/**
 * Rules Class
 */
class _CustomLogsController extends ExportableController
{
	
	/**
	 * @var	MWP\Rules\Bundle
	 */
	protected $bundle;
	
	/**
	 * Set the associated rule
	 */
	public function setBundle( $bundle )
	{
		$this->bundle = $bundle;
	}
	
	/**
	 * Get the associated rule
	 */
	public function getBundle()
	{
		return $this->bundle;
	}
	
	/**
	 * Get the associated app id
	 *
	 * @return	int
	 */
	public function getBundleId()
	{
		if ( $bundle = $this->getBundle() ) {
			return $bundle->id();
		}
		
		return 0;
	}
	
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array
		(
			'tableConfig' => array(
				'default_where' => array( 'custom_log_bundle_id=%d', $this->getBundleId() ),
				'bulkActions' => array(
					'delete' => __( 'Delete Logs', 'mwp-rules' ),
					'export' => __( 'Export Logs', 'mwp-rules' ),
				),
				'columns' => array(
					'custom_log_title' => __( 'Title', 'mwp-rules' ),
				),
			),
		));
	}
	
	/**
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		if ( $this->getBundleId() ) {
			$args = array_merge( array( 'bundle_id' => $this->getBundleId(), $args ) );
		}
		
		return parent::getUrl( $args );
	}

	/**
	 * Constructor
	 *
	 * @param	string		$recordClass			The active record class
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $recordClass, $options=array() )
	{
		parent::__construct( $recordClass, $options );
		
		/* Auto set the bundle */
		if ( isset( $_REQUEST['bundle_id'] ) ) {
			try {
				$bundle = Rules\Bundle::load( $_REQUEST['bundle_id'] );
				$this->setBundle( $bundle );
			} catch( \OutOfRangeException $e ) { }
		}
	}
	
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New Log', 'mwp-rules' ),
				'params' => array( 'do' => 'new' ),
				'attr' => array( 'class' => 'btn btn-primary' ),
			)
		);
	}
	
	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_new( $record=NULL )
	{
		$class = $this->recordClass;
		$record = new $class;
		$record->bundle_id = $this->getBundleId();
		
		parent::do_new( $record );
	}

}
