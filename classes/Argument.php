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
		'widget',
		'data' => array( 'format' => 'JSON' ),
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
		
		$form->addTab( 'argument_details', array(
			'title' => __( 'Argument Details', 'mwp-rules' ),
		));
		
		$form->addTab( 'advanced_settings', array(
			'title' => __( 'Advanced Settings', 'mwp-rules' ),
		));
		
		$form->addTab( 'manual_widget', array(
			'title' => __( 'Input Widget', 'mwp-rules' ),
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
		),
		'argument_details' );
		
		$form->addField( 'title', 'text', array(
			'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
			'attr' => array( 'data-role' => 'arg-title' ),
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		),
		'argument_details' );
		
		$form->addField( 'varname', 'text', array(
			'row_attr' => array( 'data-view-model' => 'mwp-rules' ),
			'label' => __( 'Machine Name', 'mwp-rules' ),
			'description' => __( 'Enter an identifier to be used for this argument. It may only contain alphanumeric characters or underscores. It must also start with a letter.', 'mwp-rules' ),
			'attr' => array( 
				'placeholder' => 'var_name', 
				'data-fixed' => isset( $this->varname ) ? "true" : "false",
				'pattern' => "^([A-Za-z])+([A-Za-z0-9_]+)?$",
				'data-bind' => "init: function() { 
					var varname = jQuery(this);
					var title = varname.closest('form').find('[data-role=\"arg-title\"]');
					title.on('keyup change blur', function() {
						if ( ! varname.val() ) {
							varname.data('fixed',false);
						}
						if ( ! varname.data('fixed') ) {
							varname.val( title.val().replace(/^[0-9]+/,'').replace(/ /g,'_').replace(/[^A-Za-z0-9_]/g,'').toLowerCase() );
						}
					});
					varname.on('keypress', function (event) {
						switch (event.keyCode) {
							case 8:  // Backspace
								break;
							case 9:  // Tab
							case 13: // Enter
							case 37: // Left
							case 38: // Up
							case 39: // Right
							case 40: // Down
								return;
							default:
								var regex = new RegExp(\"[a-zA-Z0-9_]\");
								var key = event.key;
								if (!regex.test(key)) {
									event.preventDefault();
									return false;
								}
								break;
						}
						varname.data('fixed', true);
					});
				}
			"),
			'data' => $this->varname,
			'constraints' => array( function( $data, $context ) {
				if ( ! preg_match( "/^([A-Za-z])+([A-Za-z0-9_]+)?$/", $data ) ) {
					$context->addViolation( __('The machine name must be only alphanumerics or underscores, and must start with a letter.','mwp-rules') ); 
				}
			}),
			'required' => true,
		),
		'argument_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		),
		'argument_details' );
		
		$form->addField( 'required', 'checkbox', array(
			'row_prefix' => '<hr>',
			'label' => __( 'Required', 'mwp-rules' ),
			'description' => __( 'Choose if this argument is required to have a value.', 'mwp-rules' ),
			'value' => 1,
			'data' => (bool) $this->required,
		),
		'argument_details' );
		
		$form->addField( 'class', 'text', array(
			'label' => __( 'Object Class', 'mwp-rules' ),
			'description' => __( 'If this argument is an object, or is a scalar value that can be used to load an object, enter the class name of that object here.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'WP_User' ),
			'data' => $this->class,
			'required' => false,
		),
		'advanced_settings' );
		
		$widget_choices = [
			'None' => '',
		];
		
		foreach( apply_filters( 'rules_config_preset_options', array() ) as $key => $preset ) {
			$widget_choices[$preset['label']] = $key;
		}
		
		$form->addField( 'widget', 'choice', array(
			'label' => __( 'Widget Type', 'mwp-rules' ),
			'choices' => $widget_choices,
			'data' => $this->widget,
			'expanded' => false,
			'required' => true,
		),
		'manual_widget' );
		
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
		$_values = array_merge( $values['argument_details'], $values['advanced_settings'], $values['manual_widget'] );
		$_values['varname'] = strtolower( $_values['varname'] );
		
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
		Plugin::instance()->clearCustomHooksCache();
		parent::delete();
	}

}
