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
