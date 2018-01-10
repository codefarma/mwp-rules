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
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_merge_recursive( parent::getDefaultConfig(), array
		(
			'tableConfig' => array
			( 
				'where' => array( 'rule_parent_id=0' ),
				'columns' => array(
					'rule_title'      => __( 'Rule Summary', 'mwp-rules' ),
					'rule_event_hook' => __( 'Evaluated When', 'mwp-rules' ),
					'subrules'        => __( 'Subrules', 'mwp-rules' ),
					'rule_enabled'    => __( 'Settings', 'mwp-rules' ),
				),
				'searchable' => array(
					'rule_title' => array( 'type' => 'contains', 'combine_words' => 'and' ),
				),
				'bulk_actions' => array(
					'enable' => __( 'Enable Rules', 'mwp-rules' ),
					'disable' => __( 'Disable Rules', 'mwp-rules' ),
					'enableDebugRecursive' => __( 'Enable Debug Mode', 'mwp-rules' ),
					'disableDebugRecursive' => __( 'Disable Debug Mode', 'mwp-rules' ),
					'delete' => __( 'Delete Rules', 'mwp-rules' ),
				),
				'handlers' => array(
					'rule_title' => function( $record )
					{
						$rule = \MWP\Rules\Rule::load( $record['rule_id'] );
						$event = $rule->event();
						
						$condition_count = \MWP\Rules\Condition::countWhere( array( 'condition_rule_id=%d', $rule->id ) );
						$action_count = count( $rule->actions() );
						
						$controller = \MWP\Rules\Plugin::instance()->getRulesController();
						$conditionsUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_conditions' ) );
						$actionsUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_actions' ) );
						
						return '<span style="min-height: 30px; font-size: 1.2em;">' . $record['rule_title'] . '</span><br>' . 
							'<ul style="list-style-type:disc; margin:2px 0 0 20px;">' . 
								"<li style='margin-bottom:0'><a href='{$conditionsUrl}'>{$condition_count} conditions</a></li>" . 
								"<li style='margin-bottom:0'><a href='{$actionsUrl}'>{$action_count} actions</a></li>" . 
							'</ul>';
					},
					'rule_enabled' => function( $record ) 
					{
						$rule = \MWP\Rules\Rule::load( $record['rule_id'] );
						$event = $rule->event();
						
						$status = '<div class="mwp-bootstrap" style="margin-bottom:10px">';
						$status .= $event ? ( $record['rule_enabled'] ? 
							'<span class="label label-success">ENABLED</span>' : 
							'<span class="label label-danger">DISABLED</span>' ) : 
							'<span class="label label-warning">INOPERABLE</span>';
							
						if ( $record['rule_debug'] ) {
							$status .= ' <a href="' . $rule->url( array( '_tab' => 'rule_debug_console' ) ) . '"><span class="label label-info"><i class="glyphicon glyphicon-wrench"></i> DEBUG MODE ON</span></a>';
						}
						
						if ( ! $rule->parent() and $rule->enabled ) {
							$status .= ' <span title="' . __( 'Priority', 'mwp-rules' ) . '" class="label label-warning">' . $rule->priority . '</span>';
						}
						
						if ( $rule->enable_recursion ) {
							$status .= ' <span title="' . __( 'Recursions Allowed', 'mwp-rules' ) . '" class="label label-default"><i class="glyphicon glyphicon-repeat"></i> ' . $rule->recursion_limit . '</span>';
						}
						
						$status .= '</div>';
						
						return $status;
					},
					'rule_event_hook' => function( $record ) use ( $plugin ) 
					{
						$event = $plugin->getEvent( $record['rule_event_type'], $record['rule_event_hook'] );
						
						$event_title = $event ? '<strong>' . $event->title . '</strong>' : '<strong class="text-danger">Missing Event Definition</strong>';
						
						return '<div class="mwp-bootstrap">' . 
							$event_title . '<br>' . 
							( $event ? $event->description . '<br>' : '' ) .
							'<code>' . ( $record['rule_event_type'] == 'filter' ? 'add_filter(\'' : 'add_action(\'' ) . $record['rule_event_hook'] . '\')</code> ' . 
						'</div>';
						
					},
					'subrules' => function( $record )
					{
						$recursiveRuleCount = function( $_rule ) use ( &$recursiveRuleCount ) {
							$total = 0;
							foreach( $_rule->children() as $_subrule ) {
								$total += $recursiveRuleCount( $_subrule ) + 1;
							}
							return $total;
						};
						
						$rule = \MWP\Rules\Rule::load( $record['rule_id' ] );					
						$subrule_count = $recursiveRuleCount( $rule );
						$controller = \MWP\Rules\Plugin::instance()->getRulesController();
						$subrulesUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_subrules' ) );
						
						$output = '<div class="mwp-bootstrap">';
						
						if ( $subrule_count ) {
							$output .= "<span style='font-size:15px; font-weight:bold;'><i class='glyphicon glyphicon-link'></i> <a href='{$subrulesUrl}'>{$subrule_count} sub-rules.</a></span>";
						} else {
							$output .= "No subrules.";
						}
						
						$output .= "<div style='margin-top:10px'><a class='btn btn-sm btn-default' href=\"" . $controller->getUrl( array( 'do' => 'new', 'parent_id' => $rule->id ) ) . "\"><i class='glyphicon glyphicon-plus'></i> New sub-rule</a></div>";
						$output .= "</div>";
						
						return $output;
					}
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
			'new' => array(
				'title' => __( 'Create New Rule', 'mwp-rules' ),
				'params' => array( 'do' => 'new' ),
				'attr' => array( 'class' => 'btn btn-primary' ),
			)
		);
	}
	
	/**
	 * Get the active record display table
	 *
	 * @param	array			$override_options			Default override options
	 * @return	Modern\Wordpress\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable( $override_options=array() )
	{
		$table = parent::createDisplayTable( $override_options );
		$table->tableTemplate = 'rules/table';
		$table->actionsColumn = 'rule_enabled';
		
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
