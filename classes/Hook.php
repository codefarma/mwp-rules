<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2018
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * CustomAction Class
 */
class _Hook extends ActiveRecord
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
		'title',
		'weight',
		'description',
		'key',
		'enable_api',
		'api_methods',
		'type',
		'hook',
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
	 * Get the hook arguments
	 *
	 * @return	array
	 */
	public function getArguments()
	{
		return Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', 'hook', $this->id() ), 'argument_weight ASC' );
	}
	
	/**
	 * Get the event definition
	 *
	 * @return	array
	 */
	public function getDefinition()
	{
		$definition = array(
			'title' => $this->title,
			'description' => $this->description,
		);
		
		foreach( $this->getArguments() as $argument ) {
			$arg_def = array(
				'argtype' => $argument->type,
				'class' => $argument->class,
				'label' => $argument->title,
				'description' => $argument->description,
			);
			
			$definition['arguments'][ $argument->varname ] = $arg_def;
		}
		
		return $definition;
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit' );
		
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
			'row_prefix' => '<hr>',
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
			$form->addTab( 'hook_arguments', array(
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
			'hook_arguments' );
		}
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save', 'mwp-rules' ),
		));
		
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
	 * Save
	 *
	 * @return	void
	 */
	public function save()
	{
		Plugin::instance()->clearCustomHooksCache();
		parent::save();
	}
	
	/**
	 * Delete
	 *
	 * @return	void
	 */
	public function delete()
	{
		Argument::deleteWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', 'hook', $this->id() ) );
		Plugin::instance()->clearCustomHooksCache();
		parent::delete();
	}
}
