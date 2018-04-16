<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.9.2
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * CustomAction Class
 */
class _Hook extends ExportableRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_hooks";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid',
		'title',
		'weight',
		'description',
		'enable_api',
		'api_methods',
		'type',
		'hook',
		'imported',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'hook_';

    /**
     * @var bool        Separate table per site?
     */
    public static $site_specific = FALSE;

    /**
     * @var string      The class of the managing plugin
     */
    public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Hook';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Hooks';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';

	/**
	 * @var	string
	 */
	public static $lang_create = 'Add';

	/**
	 * @var	string
	 */
	public static $lang_edit = 'Edit';
	
	/**
	 * @var	string
	 */
	public static $lang_delete = 'Delete';
	
	/**
	 * Get the 'edit record' page title
	 * 
	 * @return	string
	 */
	public function _getEditTitle( $type=NULL )
	{
		$singular = $this->type == 'custom' ? 'Custom Action' : ucfirst( $this->type );
		return __( static::$lang_edit . ' ' . $singular );
	}
	
	/**
	 * Get the 'view record' page title
	 * 
	 * @return	string
	 */
	public function _getViewTitle()
	{
		$singular = $this->type == 'custom' ? 'Custom Action' : ucfirst( $this->type );
		return __( static::$lang_view . ' ' . $singular );
	}
	
	/**
	 * Get the 'delete record' page title
	 * 
	 * @return	string
	 */
	public function _getDeleteTitle()
	{
		$singular = $this->type == 'custom' ? 'Custom Action' : ucfirst( $this->type );
		return __( static::$lang_delete . ' ' . $singular );
	}
	
	/**
	 * Get the hook arguments
	 *
	 * @return	array
	 */
	public function getArguments()
	{
		return Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', Argument::getParentType( $this ), $this->id() ), 'argument_weight ASC' );
	}
	
	/**
	 * Get the event definition
	 *
	 * @return	array
	 */
	public function getEventDefinition()
	{
		$definition = array(
			'title' => $this->title,
			'description' => $this->description,
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getProvidesDefinition();
		}
		
		$definition['hook_data'] = $this->_data;
		
		return $definition;
	}
	
	/**
	 * Get the action definition
	 *
	 * @return	array
	 */
	public function getActionDefinition()
	{
		$definition = array(
			'title' => $this->title,
			'description' => $this->description,
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getReceivesDefinition();
		}
		
		$definition['hook_data'] = $this->_data;
		
		return $definition;
	}
	
	/**
	 * Get the title of the hook type for display
	 *
	 * @return	string
	 */
	public function getTypeTitle()
	{
		return $this->type == 'custom' ? __( 'Custom Action', 'mwp-rules' ) : ucfirst( $this->type );
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$has_hook_config = false;
		
		if ( $this->title ) {
			$form->addHtml( 'hook_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-flash"></i>',
				'label' => $this->getTypeTitle(),
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'hook_details', array(
			'title' => __( 'Hook Details', 'mwp-rules' ),
		));
		
		if ( ! $this->type ) {
			$form->addField( 'specification', 'choice', array(
				'label' => __( 'Hook Type', 'mwp-rules' ),
				'description' => "<div class='alert alert-info'><ol>" . 
					"<li>" . __( 'Choose "Unique Custom Action" if you want to create a new custom action that you can trigger using rules.', 'mwp-rules' ) . "</li>" .
					"<li>" . __( 'Choose "Existing Hook Event" to add an existing hook event triggered by WordPress core or a 3rd party plugin.', 'mwp-rules' ) . "</li>" .
				"</ol></div>",
				'data' => 'automatic',
				'choices' => array(
					'Unique Custom Action' => 'new',
					'Existing Hook Event' => 'existing',
				),
				'toggles' => array(
					'new' => array( 'hide' => array( '#hook_hook', '#hook_type' ) ),
				),
				'required' => true,
			), 'hook_details' );
		}
		
		if ( $this->type != 'custom' ) {
			$has_hook_config = true;
			$form->addField( 'hook', 'text', array(
				'row_attr' => array( 'id' => 'hook_hook' ),
				'label' => __( 'Hook' ),
				'description' => __( 'Enter the name of the hook', 'mwp-rules' ),
				'attr' => array( 'placeholder' => 'hook_name' ),
				'data' => $this->hook,
				'required' => $this->id() > 0,
			), 'hook_details' );
			
			$form->addField( 'type', 'choice', array(
				'row_attr' => array( 'id' => 'hook_type' ),
				'label' => __( 'Type', 'mwp-rules' ),
				'choices' => array(
					'Action' => 'action',
					'Filter' => 'filter',
				),
				'data' => $this->type ?: 'action',
				'description' => __( 'Choose whether the hook is an action or a filter.', 'mwp-rules' ),
				'expanded' => true,
				'required' => true,
			), 'hook_details' );
		}
		
		$form->addField( 'title', 'text', array(
			'row_prefix' => $has_hook_config ? '<hr>' : '',
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'hook_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'hook_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'arguments', array(
				'title' => __( 'Arguments', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'hook' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
		} else {
			$hook = $this;
			$form->onComplete( function() use ( $hook, $plugin ) {
				$controller = $plugin->getHooksController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $hook->id(), '_tab' => 'arguments' ) ) );
				exit;
			});			
		}
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save', 'mwp-rules' ),
		), '');
		
		return $form;
	}
	
	/**
	 * Process submitted form values 
	 *
	 * @param	array			$values				Submitted form values
	 * @return	void
	 */
	protected function processEditForm( $values )
	{
		$_values = $values['hook_details'];
		
		if ( isset( $_values['specification'] ) ) {
			if ( $_values['specification'] == 'new' ) {
				$_values['hook'] = uniqid( 'rules/action/' );
				$_values['type'] = 'custom';
			}
		}
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getHooksController()->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit' ), $params ) );
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		$export['arguments'] = array_map( function( $argument ) { return $argument->getExportData(); }, $this->getArguments() );
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @return	array
	 */
	public static function import( $data )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$hook = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$hook->_setDirectly( $col, $value );
			}
			
			$hook->imported = time();
			$result = $hook->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['hooks'][] = $data;
				
				$imported_argument_uuids = [];

				/* Import hook arguments */
				if ( isset( $data['arguments'] ) and ! empty( $data['arguments'] ) ) {
					foreach( $data['arguments'] as $argument ) {
						$imported_argument_uuids[] = $argument['data']['argument_uuid'];
						$results = array_merge_recursive( $results, Argument::import( $argument, $hook ) );
					}
				}
				
				/* Cull previously imported arguments which are no longer part of this imported hook */
				foreach( Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d AND argument_imported > 0 AND argument_uuid NOT IN (\'' . implode("','", $imported_argument_uuids) . '\')', Argument::getParentType( $hook ), $hook->id() ) ) as $argument ) {
					$argument->delete();
				}
				
			} else {
				$results['errors']['features'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Save
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		Plugin::instance()->clearCustomHooksCache();
		return parent::save();
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		Argument::deleteWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', 'hook', $this->id() ) );
		Plugin::instance()->clearCustomHooksCache();
		return parent::delete();
	}
}
