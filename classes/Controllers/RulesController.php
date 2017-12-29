<?php
/**
 * Plugin Class File
 *
 * Created:   December 12, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Helpers\ActiveRecordController;

/**
 * Rules Class
 */
class RulesController extends ActiveRecordController
{
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New Rule', 'mwp-rules' ),
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
		
		if ( isset( $_REQUEST['parent_id'] ) ) {
			try {
				$rule = $class::load( $_REQUEST['parent_id'] );
				$record->parent_id = $rule->id;
				$record->event_type = $rule->event_type;
				$record->event_hook = $rule->event_hook;
			} 
			catch( \OutOfRangeException $e ) {}
		}
		
		parent::do_new( $record );
	}

}
