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
	 * Default controller configuration
	 *
	 * @return	array
	 */
	public function getDefaultConfig()
	{
		$plugin = $this->getPlugin();
		
		return array_replace_recursive( parent::getDefaultConfig(), array(
			'tableConfig' => [
				'columns' => [
					'argument_title' => __( 'Title', 'mwp-rules' ),
					'argument_varname' => __( 'Machine Name', 'mwp-rules' ),
					'argument_type' => __( 'Type', 'mwp-rules' ),
					'argument_widget' => __( 'Input Widget', 'mwp-rules' ),
					'default_value' => __( 'Default Value', 'mwp-rules' ),
					'argument_required' => __( 'Required', 'mwp-rules' ),
					'_row_actions'   => '',
					'drag_handle'    => '',
				],
				'handlers' => [
					'drag_handle' => function( $row ) {
						return '<div class="draggable-handle mwp-bootstrap"><i class="glyphicon glyphicon-menu-hamburger"></i></div>';
					},
					'argument_varname' => function( $row ) {
						return '<code>' . $row['argument_varname'] . '</code>';
					},
					'argument_required' => function( $row ) {
						return $row['argument_required'] ? 'Yes' : 'No';
					},
					'argument_widget' => function( $row ) {
						$argument = Rules\Argument::load( $row['argument_id'] );
						return '<a href="' . $argument->url([ '_tab' => 'widget_config' ]) . '">' . ( $argument->widget ?: 'None' ) . '</a>';
					},
					'default_value' => function( $row ) {
						$argument = Rules\Argument::load( $row['argument_id'] );
						$default_values = $argument->getSavedValues( 'default' );
						
						if ( ! $argument->usesDefault() ) {
							return '--';
						}
						
						if ( ! is_array( $default_values ) or count( $default_values ) == 1 ) {
							$default_values = (array) $default_values;
							$value = array_shift( $default_values );
							if ( ! is_array( $value ) or is_object( $value ) ) {
								return '<a href="' . $argument->url([ 'do' => 'set_default' ]) . '">' . ( $value ? esc_html( (string) $value ) : '--' ) . '</a>';
							}
						}
						
						return '<a href="' . $argument->url([ 'do' => 'set_default' ]) . '">Complex Data</a>';
					}
				],
			],
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
	 * Initialize
	 */
	public function init()
	{
		$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : NULL;
		$action = isset( $_REQUEST['do'] ) ? $_REQUEST['do'] : NULL;
		if ( ! $id and ( ! $action or $action == 'index' ) and $parent = $this->getParent() ) {
			wp_redirect( $parent->_getController()->getUrl( array( 'id' => $parent->id(), 'do' => 'edit', '_tab' => 'arguments' ) ) );
			exit;
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
		$override_options['constructor']['singular'] = $this->getSingularName();
		$override_options['constructor']['plural'] = $this->getPluralName();
		
		$table = parent::createDisplayTable( $override_options );
		$table->hardFilters[] = array( 'argument_parent_type=%s AND argument_parent_id=%d', $this->getParentType(), $this->getParentId() );
		$table->removeTableClass( 'fixed' );
		
		if ( ! $this->getParent() instanceof Rules\Bundle ) {
			unset( $table->columns['default_value'] );
		}
		
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
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActions()
	{
		$recordClass = $this->recordClass;
		
		return array( 
			'new' => array(
				'title' => __( $recordClass::$lang_create . ' ' . $this->getSingularName() ),
				'params' => array( 'do' => 'new' ),
				'attr' => array( 'class' => 'btn btn-primary' ),
			)
		);
	}
	
	/**
	 * Get the singular name of argument
	 *
	 * @return string
	 */
	public function getSingularName()
	{
		$class = $this->recordClass;
		
		if ( $this->getParent() instanceof Rules\Bundle ) {
			return $class::$lang_singular_bundle;
		}
		
		if ( $this->getParent() instanceof Rules\CustomLog ) {
			return $class::$lang_singular_log;
		}
		
		return $class::$lang_singular;
	}
	
	/**
	 * Get the plural name of arguments
	 *
	 * @return string
	 */
	public function getPluralName()
	{
		$class = $this->recordClass;
		
		if ( $this->getParent() instanceof Rules\Bundle ) {
			return $class::$lang_plural_bundle;
		}
		
		if ( $this->getParent() instanceof Rules\CustomLog ) {
			return $class::$lang_plural_log;
		}
		
		return $class::$lang_plural;
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
		
		if ( $this->getParent() instanceof Rules\Bundle ) {
			Rules\Argument::$lang_singular = Rules\Argument::$lang_singular_bundle;
			Rules\Argument::$lang_plural = Rules\Argument::$lang_plural_bundle;
		}
		
		if ( $this->getParent() instanceof Rules\CustomLog ) {
			Rules\Argument::$lang_singular = Rules\Argument::$lang_singular_log;
			Rules\Argument::$lang_plural = Rules\Argument::$lang_plural_log;
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
		
		echo $this->wrap( __( 'Set Default Value', 'mwp-rules' ), $output, [ 'classes' => 'edit' ] );
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
