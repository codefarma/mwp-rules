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

/**
 * Schedule Controller Class
 */
class _ScheduleController extends ActiveRecordController
{
	/**
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_merge_recursive( parent::getDefaultConfig(), array(
			'tableConfig' => array( 
				'constructor' => array(
					'singular' => 'action',
					'plural' => 'actions',
				),
				'sort_by' => 'schedule_time',
				'sort_order' => 'DESC',
				'columns' => array(
					'type' => __( 'Type', 'mwp-rules' ),
					'action' => __( 'Action Description', 'mwp-rules' ),
					'schedule_time' => __( 'Scheduled Date/Time', 'mwp-rules' ),
					'schedule_unique_key' => __( 'Keyphrase', 'mwp-rules' ),
					'schedule_queued' => __( 'Status', 'mwp-rules' ),
					'schedule_created' => __( 'Creation Date', 'mwp-rules' ),
					'schedule_data' => __( 'Data', 'mwp-rules' ),
				),
				'handlers' => array(
					'type' => function( $row ) {
						if ( $row['schedule_action_id'] ) {
							return '<div class="mwp-bootstrap"><span class="label label-default">RULE ACTION</span></div>';
						} else if ( $row['schedule_custom_id'] ) {
							return '<div class="mwp-bootstrap"><span class="label label-default">CUSTOM ACTION</span></div>';
						}
					},
					'action' => function( $row ) {
						if ( $row['schedule_action_id'] ) {
							try {
								$action = \MWP\Rules\Action::load( $row['schedule_action_id'] );
								return '<a href="' . $action->url() . '">' . esc_html( $action->title ) . '</a>';
							}
							catch( \OutOfRangeException $e ) { }
						}
					},
					'schedule_time' => function( $row ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $row['schedule_time'] ), 'F j, Y H:i:s' );
					},
					'schedule_created' => function( $row ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $row['schedule_created'] ), 'F j, Y H:i:s' );
					},
					'schedule_queued' => function( $row ) {
						if ( $row['schedule_queued'] > 0 ) {
							return __( 'Running since', 'mwp-rules' ) . ' ' . get_date_from_gmt( date( 'Y-m-d H:i:s', $row['schedule_queued'] ), 'F j, Y H:i:s' );
						} else {
							return __( 'Queued', 'mwp-rules' );
						}
					}
				)
			),
		));
	}

	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record
	 * @return	void
	 */
	public function do_new( $record=NULL )
	{
		$this->do_index( $record );
	}

	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array();
	}
}
