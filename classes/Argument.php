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
		
		$form->addField( 'class', 'text', array(
			'label' => __( 'Object Class', 'mwp-rules' ),
			'description' => __( 'If this argument is an object, or is a scalar value that can be used to load an object, enter the class name of that object here.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'WP_User' ),
			'data' => $this->class,
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
		
		$form->addTab( 'widget_config', array(
			'title' => __( 'Input Widget', 'mwp-rules' ),
		));
		
		$widget_choices = [	'None' => '' ];
		$widget_toggles = [ '' => array( 'hide' => array( '#widget_config_all', '[id$="advanced_options_tab"]' ) ) ];
		$config_preset_options = apply_filters( 'rules_config_preset_options', array() );
		
		foreach( $config_preset_options as $key => $preset ) {
			$widget_choices[$preset['label']] = $key;
			$widget_toggles[ $key ]['show'][] = '#widget_' . $key . '_config';
		}
		
		$form->addField( 'widget', 'choice', array(
			'label' => __( 'Widget Type', 'mwp-rules' ),
			'choices' => $widget_choices,
			'toggles' => $widget_toggles,
			'data' => $this->widget,
			'expanded' => false,
			'description' => __( 'An input widget will allow the user to manually configure the value of the argument provided to rule configurations.', 'mwp-rules' ),
			'required' => true,
		));
		
		$data = $this->data;
		
		$form->addHtml( 'widget_config_start', "<div style=\"min-height: 200px\"><div id=\"widget_config_all\">" );
		
			foreach( $config_preset_options as $key => $preset ) {
				$form->addField( 'widget_' . $key . '_config', 'fieldgroup', [ 'row_attr' => array( 'id' => 'widget_' . $key . '_config' ) ] );
				$form->setCurrentContainer( 'widget_' . $key . '_config' );
				if ( isset( $preset['config']['form'] ) and is_callable( $preset['config']['form'] ) ) {
					call_user_func( $preset['config']['form'], 'widget_' . $key . '_config', $form, isset( $data['widget_config'][ $key ] ) ? $data['widget_config'][ $key ] : [], $this );
				} else {
					$form->addHtml( 'widget_' . $key . '_no_config', '<div class="col-lg-2 col-md-3 col-sm-4"></div><div class="col-lg-6 col-md-7 col-sm-8 alert alert-info">' . __( 'This widget does not have any special configuration options.', 'mwp-rules' ) . "</div>" );
				}
				$form->endLastContainer();
			}
			
		$form->addHtml( 'widget_config_end', "</div></div>" );
		
		$form->addTab( 'advanced_options', array(
			'title' => __( 'Advanced Config', 'mwp-rules' ),
		));
		
		$form->addField( 'widget_use_advanced', 'checkbox', array(
			'row_attr' => array( 'id' => 'widget_advanced_options' ),
			'label' => __( 'Custom Options', 'mwp-rules' ),
			'description' => __( 'For advanced users, you can provide additional options to the widget using custom PHP code.', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $data['advanced_options']['widget_use_advanced'] ) ? (bool) $data['advanced_options']['widget_use_advanced'] : false,
			'toggles' => array( 1 => array( 'show' => array( '#widget_options_phpcode' ) ) ),
		));
		
		$form->addField( 'widget_options_phpcode', 'textarea', array(
			'row_attr' => array(  'id' => 'widget_options_phpcode', 'data-view-model' => 'mwp-rules' ),
			'label' => __( 'Custom PHP Code', 'mwp-rules' ),
			'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
			'data' => isset( $data['advanced_options'][ 'widget_options_phpcode' ] ) ? $data['advanced_options'][ 'widget_options_phpcode' ] : "// <?php \n\nreturn array();\n",
			'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 
				'return_args' => array( '<code>array</code>: An associative array of options to provide to the widget' ),
				'variables' => array( 
					'<code>$options</code> (array) - The default configured options for the widget',
					'<code>$argument</code> (object) (MWP\Rules\Argument) - The argument which is using the widget',
				),
			)),
			'required' => false,
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
		$widget_type = $values['widget_config']['widget'];
		
		$_values = $values['argument_details'];
		$_values['widget'] = $widget_type;
		$_values['varname'] = strtolower( $_values['varname'] );
		$_values['data'] = array(
			'advanced_options' => $values['advanced_options'],
			'widget_config' => array(
				$widget_type => $values['widget_config']['widget_' . $widget_type . '_config'],
			),
		);
		
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
