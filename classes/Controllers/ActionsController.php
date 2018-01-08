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
 * Actions Class
 */
class ActionsController extends ActiveRecordController
{
	/**
	 * @var	MWP\Rules\Rule
	 */
	protected $rule;
	
	/**
	 * Set the associated rule
	 */
	public function setRule( $rule )
	{
		$this->rule = $rule;
	}
	
	/**
	 * Get the associated rule
	 */
	public function getRule()
	{
		return $this->rule;
	}
	
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
				'sort_by' => 'action_weight',
				'sort_order' => 'ASC',
				'bulk_actions' => array(),
				'columns' => array(
					'details' => __( 'Details', 'mwp-rules' ),
				),
				'handlers' => array(
					'details' => function( $row ) use ( $plugin ) {
						$action = Action::load( $row['action_id'] );
						return $plugin->getTemplateContent( 'rules/actions/table_row', array( 'action' => $action ) );
					}
				),
			),
		));
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
		
		/* Auto set the rule */
		if ( isset( $_REQUEST['rule_id'] ) ) {
			try {
				$rule = \MWP\Rules\Rule::load( $_REQUEST['rule_id'] );
				$this->setRule( $rule );
			} catch( \OutOfRangeException $e ) { }
		}
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
		$table->tableTemplate = 'rules/actions/table';
		$table->rowTemplate = 'rules/actions/table_row';
		
		return $table;
	}
	
	/**
	 * Index Page
	 * 
	 * @return	string
	 */
	public function do_index()
	{
		$rule_id = isset( $_REQUEST['rule_id'] ) ? $_REQUEST['rule_id'] : NULL;
		wp_redirect( \MWP\Rules\Plugin::instance()->getRulesController()->getUrl( array( 'id' => $rule_id, 'do' => 'edit', '_tab' => 'rule_actions' ) ) );
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
		
		if ( isset( $_REQUEST['rule_id'] ) ) {
			try {
				$rule = \MWP\Rules\Rule::load( $_REQUEST['rule_id'] );
				$record = new $class;
				$record->rule_id = $rule->id;
				$record->else = isset( $_REQUEST['action_else'] ) ? (int) $_REQUEST['action_else'] : 0;
			}
			catch( \OutOfRangeException $e ) { 
				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The specified rule could not be found.', 'mwp-rules' ) ) );
				return;
			}
		}
		
		parent::do_new( $record );
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
				'title' => __( 'Add Action', 'mwp-rules' ),
				'params' => array( 'do' => 'new' ),
				'attr' => array( 'class' => 'btn btn-primary' ),
			)
		);
	}
	
	/**
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		return parent::getUrl( array_merge( array( 'rule_id' => $this->getRule()->id ), $args ) );
	}

}
