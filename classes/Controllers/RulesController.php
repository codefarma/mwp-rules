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
class _RulesController extends ExportableController
{
	
	/**
	 * @var	MWP\Rules\Bundle
	 */
	protected $bundle;
	
	/**
	 * @var	MWP\Rules\Hook
	 */
	protected $hook;
	
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
	 * Set the associated rule
	 */
	public function setHook( $hook )
	{
		$this->hook = $hook;
	}
	
	/**
	 * Get the associated rule
	 */
	public function getHook()
	{
		return $this->hook;
	}
	
	/**
	 * Get the parent
	 */
	public function getParent()
	{
		return $this->getBundle() ?: $this->getHook();
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
		
		/* Auto set the hook */
		if ( isset( $_REQUEST['hook_id'] ) ) {
			try {
				$hook = Rules\Hook::load( $_REQUEST['hook_id'] );
				$this->setHook( $hook );
			} catch( \OutOfRangeException $e ) { }
		}
		
	}
	
	/**
	 * Initialize
	 */
	public function init()
	{
		$rule_id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : NULL;
		$action = isset( $_REQUEST['do'] ) ? $_REQUEST['do'] : NULL;
		if ( ! $rule_id and ( ! $action or $action == 'index' ) ) {
			if ( $bundle = $this->getBundle() ) {
				wp_redirect( Rules\Plugin::instance()->getBundlesController( $bundle->getApp() )->getUrl( array( 'id' => $bundle->id(), 'do' => 'edit', '_tab' => 'bundle_rules' ) ) );
				exit;
			}
			if ( $hook = $this->getHook() ) {
				wp_redirect( Rules\Plugin::instance()->getHooksController( $hook->getControllerKey() )->getUrl( array( 'id' => $hook->id(), 'do' => 'edit', '_tab' => 'hook_rules' ) ) );
				exit;				
			}
		}
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
				'tableTemplate' => 'rules/table',
				'default_where' => array( 'rule_parent_id=0 AND rule_bundle_id=%d AND rule_custom_internal=0', $this->getBundleId() ),
				'columns' => array(
					'rule_title'      => __( 'Rule Summary', 'mwp-rules' ),
					'rule_event_hook' => __( 'Evaluated When', 'mwp-rules' ),
					'subrules'        => __( 'Subrules', 'mwp-rules' ),
					'rule_enabled'    => __( 'Status', 'mwp-rules' ),
					'_row_actions'    => '',
					'drag_handle'     => '',
				),
				'bulkActions' => array(
					'enable' => __( 'Enable Rules', 'mwp-rules' ),
					'disable' => __( 'Disable Rules', 'mwp-rules' ),
					'export' => __( 'Download Rules', 'mwp-rules' ),
					'enableDebugRecursive' => __( 'Enable Debug Mode', 'mwp-rules' ),
					'disableDebugRecursive' => __( 'Disable Debug Mode', 'mwp-rules' ),
					'delete' => __( 'Delete Rules', 'mwp-rules' ),
				),
				'handlers' => array(
					'drag_handle' => function( $row ) {
						return '<div class="draggable-handle mwp-bootstrap"><i class="glyphicon glyphicon-menu-hamburger"></i></div>';
					},
					'rule_title' => function( $record )
					{
						$rule = Rules\Rule::load( $record['rule_id'] );
						$event = $rule->event();
						
						$condition_count = Rules\Condition::countWhere( array( 'condition_rule_id=%d', $rule->id ) );
						$action_count = count( $rule->actions() );
						
						$controller = Rules\Plugin::instance()->getRulesController( $rule->getBundle() );
						$conditionsUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_conditions' ) );
						$actionsUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_actions' ) );
						
						return '<div class="mwp-bootstrap">' . 
							'<span style="font-size: 1.2em; display: inline-block;">' . $record['rule_title'] . '<hr style="margin:7px 0 5px;"></span>' . 
							'' .
							'<ul style="margin:0;">' . 
								"<li style='margin-bottom:0'><i class='glyphicon glyphicon-triangle-right' style='font-size:0.8em; opacity: 0.6;'></i> {$condition_count} <a href='{$conditionsUrl}'>conditions</a></li>" . 
								"<li style='margin-bottom:0'><i class='glyphicon glyphicon-triangle-right' style='font-size:0.8em; opacity: 0.6;'></i> {$action_count} <a href='{$actionsUrl}'>actions</a></li>" . 
							'</ul>' .
						'</div>';
					},
					'rule_enabled' => function( $record ) 
					{
						$rule = Rules\Rule::load( $record['rule_id'] );
						$event = $rule->event();
						
						$status = '<div class="mwp-bootstrap" style="margin-bottom:10px; min-width: 120px;">';
						$status .= $event ? ( $record['rule_enabled'] ? 
							'<span data-rules-enabled-toggle="rule" data-rules-id="' . $rule->id() . '" class="label label-success rules-pointer">ENABLED</span>' : 
							'<span data-rules-enabled-toggle="rule" data-rules-id="' . $rule->id() . '" class="label label-danger rules-pointer">DISABLED</span>' ) : 
							'<span class="label label-warning">INOPERABLE</span>';
							
						if ( ! $rule->parent() and $rule->enabled ) {
							$status .= ' <span title="' . __( 'Priority', 'mwp-rules' ) . '" class="label label-primary">' . $rule->priority . '</span>';
						}
						
						if ( $rule->enable_recursion ) {
							$status .= ' <span title="' . __( 'Recursions Allowed', 'mwp-rules' ) . '" class="label label-default"><i class="glyphicon glyphicon-repeat"></i> ' . $rule->recursion_limit . '</span>';
						}
						
						if ( $record['rule_debug'] ) {
							$status .= '<div style="margin-top: 4px"><a href="' . $rule->url( array( '_tab' => 'rule_debug_console' ) ) . '" class="nounderline"><span class="label label-warning"><i class="glyphicon glyphicon-wrench"></i> DEBUG MODE ON</span></a></div>';
						}
						
						if ( is_multisite() ) {
							$status .= Rules\Plugin::instance()->getTemplateContent( 'snippets/site-list', [ 'sites' => $rule->getSites() ] );
						}
						
						return $status;
					},
					'rule_event_hook' => function( $record ) use ( $plugin ) 
					{
						$event = $plugin->getEvent( $record['rule_event_type'], $record['rule_event_hook'] );
						
						$event_title = $event ? '<strong>' . $event->title . '</strong>' : '<strong class="text-danger">Missing Event Definition</strong>';
						
						return '<div class="mwp-bootstrap">' . 
							$event_title . '<br>' . 
							( $event ? $event->description . '<br>' : '' ) .
							'<span class="text-info">via:</span> <code>' . ( $record['rule_event_type'] == 'filter' ? 'filter: ' : 'action: ' ) . $record['rule_event_hook'] . '</code> ' . 
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
						
						$rule = Rules\Rule::load( $record['rule_id' ] );					
						$subrule_count = $recursiveRuleCount( $rule );
						$controller = Rules\Plugin::instance()->getRulesController( $rule->getBundle() );
						$subrulesUrl = $controller->getUrl( array( 'do' => 'edit', 'id' => $rule->id, '_tab' => 'rule_subrules' ) );
						
						$output = '<div class="mwp-bootstrap" style="min-width: 150px; margin-right: 25px;">';
						
						if ( $subrule_count ) {
							$output .= "<a class='btn btn-xs btn-primary' href='{$subrulesUrl}'><i class='glyphicon glyphicon-link' style='font-size:0.85em; margin-right:5px;'></i> {$subrule_count} sub-rules</a>";
						} else {
							$output .= "No subrules.";
						}
						
						$output .= "<hr style='margin:8px 0'><i class='glyphicon glyphicon-plus' style='font-size:0.7em; vertical-align: 2px;'></i> <a href=\"" . $controller->getUrl( array( 'do' => 'new', 'parent_id' => $rule->id ) ) . "\">Add sub-rule</a>";
						$output .= "</div>";
						
						return $output;
					}
				),
			),
		));
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
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		if ( $this->getBundleId() ) {
			$args = array_merge( array( 'bundle_id' => $this->getBundleId(), $args ) );
		}
		else if ( $this->getHook() ) {
			$args = array_merge( array( 'hook_id' => $this->getHook()->id() ), $args );
		}
		
		return parent::getUrl( $args );
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
		
		if ( $hook = $this->getHook() ) {
			$record->custom_internal = 1;
			$record->event_type = 'action';
			$record->event_hook = $hook->hook;
		}
		
		if ( isset( $_REQUEST['parent_id'] ) ) {
			try {
				$parent = $class::load( $_REQUEST['parent_id'] );
				$record->parent_id       = $parent->id();
				$record->bundle_id       = $parent->bundle_id;
				$record->custom_internal = $parent->custom_internal;
				$record->event_type      = $parent->event_type;
				$record->event_hook      = $parent->event_hook;
			} 
			catch( \OutOfRangeException $e ) {}
		}
		
		parent::do_new( $record );
	}

}
