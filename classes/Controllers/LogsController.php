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

/**
 * Rules Class
 */
class _LogsController extends BaseController
{
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
			'tableConfig' => array
			( 
				'default_where' => array( 'error>0 OR ( op_id=0 AND rule_parent=0 )' ),
				'sort_by' => 'time',
				'sort_order' => 'DESC',
				'bulkActions' => array(),
				'columns' => array(
					'log_source' => __( 'Log Source', 'mwp-rules' ),
					'event_type' => __( 'Event', 'mwp-rules' ),
					'event_hook' => __( 'Hook', 'mwp-rules' ),
					'message' => __( 'Status', 'mwp-rules' ),
					'result' => __( 'Result', 'mwp-rules' ),
					'time' => __( 'Date/Time', 'mwp-rules' ),
				),
				'handlers' => array(
					'log_source' => function( $row ) {
						$output = '<div class="mwp-bootstrap">';
						
						if ( $row['error'] ) {
							$output .= '<span class="label label-danger">ERROR</span>&nbsp;';
						} else {
							$output .= '<span class="label label-warning">DEBUG</span>&nbsp;';
						}
						
						if ( $row['rule_id'] ) {
							try {
								$rule = \MWP\Rules\Rule::load( $row['rule_id'] );
								$output .= '<a href="' . $rule->url() . '">' . esc_html( $rule->title ) . '</a>';
							}
							catch( \OutOfRangeException $e ) { }
						}
						
						return $output;
					},
					'op_id' => function( $row ) {
						if ( $row['op_id'] and $class = $row['type'] and class_exists( $class ) ) {
							try {
								$op = $class::load( $row['op_id'] );
								return '<a href="' . $op->url() . '">' . esc_html( $op->title ) . '</a>';
							} catch( \OutOfRangeException $e ) {
								return 'Deleted operation.';
							}
						}
					},
					'event_type' => function( $row ) use ( $plugin ) {
						if ( $event = $plugin->getEvent( $row['event_type'], $row['event_hook'] ) ) {
							return $event->title;
						}					
					},
					'event_hook' => function( $row ) use ( $plugin ) {
						$output = '<div class="mwp-bootstrap">';
						switch( $row['event_type'] ) {
							case 'filter': $output .= '<code>apply_filters(\'' . $row['event_hook'] . '\')</code>'; break;
							case 'action': $output .= '<code>do_action(\'' . $row['event_hook'] . '\')</code>'; break;
						}
						$output .= '</div>';
						return $output;
					},
					'rule_id' => function( $row ) use ( $plugin ) 
					{
						if ( $row['rule_id'] ) {
							try {
								$rule = \MWP\Rules\Rule::load( $row['rule_id'] );
								return '<a href="' . $rule->url() . '">' . esc_html( $rule->title ) . '</a>';
							}
							catch( \OutOfRangeException $e ) { }
						}					
					},
					'result' => function( $row ) {
						return '<pre style="margin:0">' . esc_html( json_encode( json_decode( $row['result'], true ), JSON_PRETTY_PRINT ) ) . '</pre>';
					},
					'time' => function( $row ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $row['time'] ), 'F j, Y H:i:s' );
					},
				),
			),
		));
	}

	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		return array( 
			'flush_system_logs' => array(
				'title' => __( 'Flush All Logs', 'mwp-rules' ),
				'params' => array( 'do' => 'flush_system' ),
			)
		);
	}
	
	/**
	 * Get the active record display table
	 *
	 * @param	array			$override_options			Default override options
	 * @return	MWP\Framework\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable( $override_options=array() )
	{
		$table = parent::createDisplayTable( $override_options );
		$table->removeTableClass( 'fixed' );

		return $table;
	}
	
	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_new( $record=NULL )
	{
		return $this->do_index();
	}
	
	/**
	 * View an active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_view( $record=NULL )
	{
		$class = $this->recordClass;
		
		if ( ! $record )
		{
			try
			{
				$record = $class::load( $_REQUEST[ 'id' ] );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be loaded. Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ), 'mwp-framework' ) ) );
				return;
			}
		}
		
		$output = $this->getPlugin()->getTemplateContent( 'rules/logs/view_wrapper', array( 'title' => $record->_getViewTitle(), 'plugin' => $this->getPlugin(), 'controller' => $this, 'log' => $record ) );
		
		echo $this->wrap( $record->_getViewTitle(), $output, [ 'classes' => 'view' ] );
	}
	
	/**
	 * Flush all the system logs
	 *
	 * @return	void
	 */
	public function do_flush_system()
	{
		$class = $this->recordClass;
		$class::deleteWhere('1');
		wp_redirect( $this->getUrl() );
	}

}
