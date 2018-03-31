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
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$form = static::createForm( 'edit' );
		
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
		
		$form->addField( 'class', 'text', array(
			'label' => __( 'Object Class', 'mwp-rules' ),
			'description' => __( '(optional) If this argument is an object, or is a value that represents an object, enter the class name of the object that it represents.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'WP_User' ),
			'data' => $this->class,
			'required' => false,
		));
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		));
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		));
		
		$form->addField( 'varname', 'text', array(
			'label' => __( 'Variable Name', 'mwp-rules' ),
			'description' => __( 'Enter a name to be used as the php code variable for this argument. Only alphanumerics and underscore are allowed. It must also start with a letter.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'var_name' ),
			'data' => $this->varname,
			'required' => true,
		));
		
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

}
