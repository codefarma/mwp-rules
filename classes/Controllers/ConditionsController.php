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
 * Conditions Class
 */
class ConditionsController extends ActiveRecordController
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
				'sort_by' => 'condition_weight',
				'sort_order' => 'ASC',
				'bulk_actions' => array(),
				'columns' => array(
					'details' => __( 'Conditions', 'mwp-rules' ),
				),
				'handlers' => array(
					'details' => function( $row ) use ( $plugin ) {
						$condition = Condition::load( $row['condition_id'] );
						return $plugin->getTemplateContent( 'rules/conditions/table_row', array( 'condition' => $condition ) );
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
	 * @return	MWP\Framework\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable( $override_options=array() )
	{
		$table = parent::createDisplayTable( $override_options );
		$table->tableTemplate = 'rules/conditions/table';
		$table->rowTemplate = 'rules/conditions/table_row';
		
		return $table;
	}
	
	/**
	 * Initialize
	 */
	public function init()
	{
		$rule_id = isset( $_REQUEST['rule_id'] ) ? $_REQUEST['rule_id'] : NULL;
		$action = isset( $_REQUEST['do'] ) ? $_REQUEST['do'] : NULL;
		if ( ! $action or $action == 'index' ) {
			wp_redirect( \MWP\Rules\Plugin::instance()->getRulesController()->getUrl( array( 'id' => $rule_id, 'do' => 'edit', '_tab' => 'rule_conditions' ) ) );
			exit;
		}
	}
	
	/**
	 * Index Page
	 * 
	 * @return	string
	 */
	public function do_index()
	{

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
				
				if ( isset( $_REQUEST['parent_id'] ) ) {
					$record->parent_id = $_REQUEST['parent_id'];
				}
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
				'title' => __( 'Add Base Condition', 'mwp-rules' ),
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
