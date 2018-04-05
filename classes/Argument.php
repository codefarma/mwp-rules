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
 * Argument Class
 */
class _Argument extends ActiveRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_arguments";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'title',
		'type',
		'class',
		'required',
		'weight',
		'description',
		'varname',
		'parent_id',
		'parent_type',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'argument_';

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
	public static $lang_singular = 'Argument';
	
	/**
	 * @var	string
	 */
	public static $lang_create = 'Add';

	/**
	 * @var	string
	 */
	public static $lang_plural = 'Arguments';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Get map of named parent classes
	 *
	 * @return	array
	 */
	public static function parentClassMap()
	{
		return array(
			'hook' => Hook::class,
			'feature' => Feature::class,
		);		
	}
	
	/**
	 * Get the parent class
	 *
	 * @param   string        $type          The parent type to get the class of
	 * @return	string|NULL
	 */
	public static function getParentClass( $type )
	{
		$parent_class_map = static::parentClassMap();
		
		if ( isset( $parent_class_map[ $type ] ) ) {
			return $parent_class_map[ $type ];
		}
		
		return NULL;
	}
	
	/**
	 * Get the type for a parent
	 *
	 * @param   ActiveRecord        $record          The parent record to translate into a type identifier
	 * @return	string|NULL
	 */
	public static function getParentType( ActiveRecord $record )
	{
		foreach( static::parentClassMap() as $type => $class ) {
			if ( $record instanceof $class ) {
				return $type;
			}
		}
		
		return NULL;
	}	
	
	/**
	 * Get the parent record
	 *
	 * @return	ActiveRecord|NULL
	 */
	public function getParent()
	{
		if ( $class = static::getParentClass( $this->parent_type ) and $this->parent_id ) {
			try {
				$parent = $class::load( $this->parent_id );
				return $parent;
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$plugin = $this->getPlugin();
		
		/* Display details for the app/feature/parent */
		$form->addHtml( 'argument_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'argument' => $this, 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'hook_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-"></i>',
				'label' => 'Argument',
				'title' => $this->title,
			]));
		}
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		));
		
		$form->addField( 'type', 'choice', array(
			'label' => __( 'Argument Type', 'mwp-rules' ),
			'choices' => array(
				'String' => 'string',
				'Integer' => 'int',
				'Decimal' => 'float',
				'Boolean (True/False)' => 'bool',
				'Array' => 'array',
				'Object' => 'object',
				'Mixed Type' => 'mixed',
				'Null' => 'null',
			),
			'data' => $this->type,
			'required' => true,
		));
		
		$form->addField( 'varname', 'text', array(
			'label' => __( 'Machine Name', 'mwp-rules' ),
			'description' => __( 'Enter an identifier to be used for this argument. It may only contain alphanumeric characters or underscores. It must also start with a letter.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'var_name' ),
			'data' => $this->varname,
			'required' => true,
		));
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		));
		
		$form->addField( 'class', 'text', array(
			'label' => __( 'Object Class', 'mwp-rules' ),
			'description' => __( 'If this argument is an object, or is a scalar value that maps to an object, enter the class name of that object here.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'WP_User' ),
			'data' => $this->class,
			'required' => false,
		));
		
		$form->addField( 'required', 'checkbox', array(
			'row_prefix' => '<hr>',
			'label' => __( 'Required', 'mwp-rules' ),
			'description' => __( 'Choose whether this argument is required or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => (bool) $this->required,
		));
		
		$argument = $this;
		$form->onComplete( function() use ( $argument ) {
			if ( $parent = $argument->getParent() ) {
				wp_redirect( $parent->url( array( 'do' => 'edit', 'id' => $parent->id(), '_tab' => 'arguments' ) ) );
				exit;
			}
		});
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save Argument', 'mwp-rules' ),
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
		parent::processEditForm( $values );
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
		Plugin::instance()->clearCustomHooksCache();
		parent::delete();
	}

}
