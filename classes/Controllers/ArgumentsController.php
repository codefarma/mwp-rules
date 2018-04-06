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
use MWP\Framework\Pattern\ActiveRecord;
use MWP\Rules;

/**
 * ArgumentsController Class
 */
class _ArgumentsController extends ActiveRecordController
{
	/**
	 * @var  ing
	 */
	protected $parent_id;
	
	/**
	 * @var  string
	 */
	protected $parent_type;
	
	/**
	 * Set the parent id
	 * 
	 * @param   string        $parent_type             The parent type
	 * @return  void
	 */
	public function setParentId( $parent_id )
	{
		$this->parent_id = $parent_id;
	}
	
	/**
	 * Set the parent type
	 * 
	 * @param   string        $parent_type             The parent type
	 * @return  void
	 */
	public function setParentType( $parent_type )
	{
		$this->parent_type = $parent_type;
	}
	
	/**
	 * Get the parent id
	 * 
	 * @return	string
	 */
	public function getParentId()
	{
		return $this->parent_id;
	}
	/**
	 * Get the parent type
	 * 
	 * @return	string
	 */
	public function getParentType()
	{
		return $this->parent_type;
	}
	
	/**
	 * Set the parent
	 *
	 * @param   ActiveRecord       $parent          The parent to set
	 * @return  void
	 */
	public function setParent( ActiveRecord $parent )
	{
		if ( $type = Rules\Argument::getParentType( $parent ) ) {
			$this->setParentType( $type );
			$this->setParentId( $parent->id() );
		}
	}
	
	/**
	 * Get the parent record
	 *
	 * @return	ActiveRecord|NULL
	 */
	public function getParent()
	{
		if ( $class = Rules\Argument::getParentClass( $this->getParentType() ) and $parent_id = $this->getParentId() ) {
			try {
				$parent = $class::load( $parent_id );
				return $parent;
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
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
		
		/* Auto set the parent type */
		if ( isset( $_REQUEST['parent_type'] ) ) {
			$this->setParentType( $_REQUEST['parent_type'] );
		}
		
		/* Auto set the parent id */
		if ( isset( $_REQUEST['parent_id'] ) ) {
			$this->setParentId( $_REQUEST['parent_id'] );
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
		$table->hardFilters[] = array( 'argument_parent_type=%s AND argument_parent_id=%d', $this->getParentType(), $this->getParentId() );
		
		return $table;
	}
	
	/**
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		return parent::getUrl( array_merge( array( 'parent_type' => $this->getParentType(), 'parent_id' => $this->getParentId() ), $args ) );
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
		
		if ( $parent = $this->getParent() ) {
			$record = $record ?: new $class;
			$record->parent_type = $this->getParentType();
			$record->parent_id = $this->getParentId();
		} else {
			echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The argument requires a parent type and id to be assigned to.', 'mwp-rules' ) ) );
		}
		
		if ( $this->getParent() instanceof Rules\Feature ) {
			Rules\Argument::$lang_singular = 'Setting';
			Rules\Argument::$lang_plural = 'Settings';
		}
		
		parent::do_new( $record );
	}
	
	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_set_default( $record=NULL )
	{
		$controller = $this;
		$class = $this->recordClass;
		
		if ( ! $record ) {
			try
			{
				$record = $class::load( $_REQUEST['id'] );
			}
			catch( \OutOfRangeException $e ) { 
 				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be loaded. Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ), 'mwp-framework' ) ) );
				return;
			}
		}
		
		$form = $record->getForm( 'SetDefault' );
		$save_error = NULL;
		
		if ( $form->isValidSubmission() ) 
		{
			$record->processForm( $form->getValues(), 'SetDefault' );			
			$result = $record->save();
			
			if ( ! is_wp_error( $result ) ) {
				$form->processComplete( function() use ( $controller ) {
					wp_redirect( $controller->getUrl() );
					exit;
				});	
			} else {
				$save_error = $result;
			}
		}

		$output = $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record, 'error' => $save_error ) );
		
		echo $this->wrap( __( 'Set Default Value', 'mwp-rules' ), $output, 'edit' );
	}	
	
	/**
	 * Index Page
	 * 
	 * @return	string
	 */
	public function do_index()
	{
		if ( $parent = $this->getParent() ) {
			wp_redirect( Rules\Hook::getController('admin')->getUrl( array( 'id' => $parent->id(), 'do' => 'edit', '_tab' => 'hook_arguments' ) ) );
		}
	}
	

}
