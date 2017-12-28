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
	 * @var	string
	 */
	public static $recordClass = 'MWP\Rules\Action';
	
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
	 * Constructor
	 *
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $options=array() )
	{
		parent::__construct( $options );
		$this->setPlugin( \MWP\Rules\Plugin::instance() );
		
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
	 * @return	Modern\Wordpress\Helpers\ActiveRecordTable
	 */
	public function createDisplayTable()
	{
		$table = parent::createDisplayTable();
		//$table->setTemplate( 'nesting_table' );	
		
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
		$class = static::$recordClass;
		
		if ( isset( $_REQUEST['rule_id'] ) ) {
			try {
				$rule = \MWP\Rules\Rule::load( $_REQUEST['rule_id'] );
				$record = new $class;
				$record->rule_id = $rule->id;
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
	public function getActionButtons()
	{
		return array( 
			'new' => array(
				'title' => __( 'Add Action', 'mwp-rules' ),
				'href' => $this->getUrl( array( 'do' => 'new' ) ),
				'class' => 'btn btn-primary',
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
