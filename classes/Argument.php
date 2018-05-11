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
use MWP\Framework\DbHelper;

/**
 * Argument Class
 */
class _Argument extends ExportableRecord
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
		'uuid',
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
		'imported',
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
	
	public static $lang_singular_bundle = 'Variable';
	public static $lang_plural_bundle = 'Variables';
	public static $lang_singular_log = 'Field';
	public static $lang_plural_log = 'Fields';
	
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
			'bundle' => Bundle::class,
			'log' => CustomLog::class,
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
	 * Get the singular name of this type of argument`
	 *
	 * @return	string
	 */
	public function getSingularName()
	{
		$parent = $this->getParent();
		
		if ( $parent instanceof Bundle ) {
			return static::$lang_singular_bundle;
		}
		
		if ( $parent instanceof CustomLog ) {
			return static::$lang_singular_log;
		}
		
		return static::$lang_singular;
	}
	
	/**
	 * Get the singular name of this type of argument`
	 *
	 * @return	string
	 */
	public function getPluralName()
	{
		$parent = $this->getParent();
		
		if ( $parent instanceof Bundle ) {
			return static::$lang_plural_bundle;
		}
		
		if ( $parent instanceof CustomLog ) {
			return static::$lang_plural_log;
		}
		
		return static::$lang_plural;
	}
	
	/**
	 * Get the 'create record' page title
	 * 
	 * @return	string
	 */
	public static function _getCreateTitle()
	{
		return __( static::$lang_create . ' ' . static::$lang_singular );
	}
	
	/**
	 * Get the 'view record' page title
	 * 
	 * @return	string
	 */
	public function _getViewTitle()
	{
		return __( static::$lang_view . ' ' . $this->getSingularName() );
	}
	
	/**
	 * Get the 'edit record' page title
	 * 
	 * @return	string
	 */
	public function _getEditTitle( $type=NULL )
	{
		return __( static::$lang_edit . ' ' . $this->getSingularName() );
	}
	
	/**
	 * Get the 'delete record' page title
	 * 
	 * @return	string
	 */
	public function _getDeleteTitle()
	{
		return __( static::$lang_delete . ' ' . $this->getSingularName() );
	}

	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		$data = $this->data;
		$actions = parent::getControllerActions();
		
		unset( $actions['view'] );
		
		$argument_actions = array(
			'edit' => '',
			'set_default' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-edit',
				'attr' => array( 
					'title' => __( 'Set Default Value', 'mwp-rules' ),
					'class' => 'btn btn-xs btn-default',
				),
				'params' => array(
					'do' => 'set_default',
					'id' => $this->id(),
				),
			),
			'delete' => ''
		);
		
		if ( ! $this->widget or ! $this->usesDefault() ) {
			unset( $argument_actions['set_default'] );
		}
		
		return array_replace_recursive( $argument_actions, $actions );
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
		
		/* Display details for the app/bundle/parent */
		$form->addHtml( 'argument_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'argument' => $this, 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'hook_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-"></i>',
				'label' => $this->getSingularName(),
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'argument_details', array(
			'title' => __( $this->getSingularName() . ' Details', 'mwp-rules' ),
		));
		
		$form->addField( 'type', 'choice', array(
			'label' => __( $this->getSingularName() . ' Data Type', 'mwp-rules' ),
			'choices' => array(
				'String' => 'string',
				'Integer' => 'int',
				'Decimal' => 'float',
				'Boolean (True/False)' => 'bool',
				'Array' => 'array',
				'Object' => 'object',
				'Mixed Type' => 'mixed',
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
			'description' => __( 'Enter an identifier to be used for this ' . strtolower( $this->getSingularName() ) . '. It may only contain alphanumeric characters or underscores. It must also start with a letter.', 'mwp-rules' ),
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
			'description' => __( 'If this data is an object, or is a scalar value that can be used to load an object, enter the class name of that object here.', 'mwp-rules' ),
			'attr' => array( 'placeholder' => 'WP_User' ),
			'data' => $this->class,
			'required' => false,
		),
		'argument_details' );
		
		$form->addField( 'required', 'checkbox', array(
			'row_prefix' => '<hr>',
			'label' => __( 'Required', 'mwp-rules' ),
			'description' => __( 'Choose if this ' . strtolower( $this->getSingularName() ) . ' is required to have a value.', 'mwp-rules' ),
			'value' => 1,
			'data' => $this->required !== NULL ? (bool) $this->required : true,
		),
		'argument_details' );
		
		$form->addTab( 'widget_config', array(
			'title' => __( 'Input Widget', 'mwp-rules' ),
		));
		
		$widget_choices = [	'None' => '' ];
		$widget_toggles = [ '' => array( 'hide' => array( '#widget_config_all', '[id$="advanced_options_tab"]' ) ) ];
		$config_preset_options = $this->getPlugin()->getRulesConfigPresetOptions();
		
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
			'description' => __( 'An input widget is what is used to allow users to manually configure the value of this ' . strtolower( $this->_getSingularName() ) . '.', 'mwp-rules' ),
			'required' => true,
			'constraints' => function() {},
		));
		
		$data = $this->data;
		
		$form->addHtml( 'widget_config_start', "<div style=\"min-height: 200px\"><div id=\"widget_config_all\"><hr>" );
		
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
		
		$default_value_description = $this->getParent() instanceof Bundle ? 
			__( 'If enabled, you can set a default value to be used for this ' . strtolower( $this->_getSingularName() ) . ' in the absense of a user customized value.', 'mwp-rules' ) : 
			__( 'If enabled, you can customize the default value displayed in the widget when manually configuring it in rule operations.', 'mwp-rules' );
		
		$form->addField( 'widget_use_default', 'checkbox', array(
			'label' => __( 'Use Default Value', 'mwp-rules' ),
			'description' => $default_value_description,
			'value' => 1,
			'data' => isset( $data['advanced_options']['widget_use_default'] ) ? (bool) $data['advanced_options']['widget_use_default'] : true,
		));
		
		if ( $this->getParent() instanceof Bundle ) {		
			$form->addField( 'widget_allow_custom_value', 'checkbox', array(
				'label' => __( 'User Customizable', 'mwp-rules' ),
				'description' => __( 'Allow users to edit the value of this setting.', 'mwp-rules' ),
				'value' => 1,
				'data' => isset( $data['advanced_options']['widget_allow_custom_value'] ) ? (bool) $data['advanced_options']['widget_allow_custom_value'] : true,
			));
		}
		
		$form->addField( 'widget_use_advanced', 'checkbox', array(
			'row_prefix' => '<hr>',
			'label' => __( 'Custom Widget Config', 'mwp-rules' ),
			'description' => __( 'You can provide additional options to configure the input widget using custom PHP code.', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $data['advanced_options']['widget_use_advanced'] ) ? (bool) $data['advanced_options']['widget_use_advanced'] : false,
			'toggles' => array( 1 => array( 'show' => array( '#widget_options_phpcode' ) ) ),
		));
		
		$form->addField( 'widget_options_phpcode', 'textarea', array(
			'row_attr' => array(  'id' => 'widget_options_phpcode', 'data-view-model' => 'mwp-rules' ),
			'label' => __( 'PHP Code', 'mwp-rules' ),
			'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
			'data' => isset( $data['advanced_options'][ 'widget_options_phpcode' ] ) ? $data['advanced_options'][ 'widget_options_phpcode' ] : "// <?php \n\nreturn array();\n",
			'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 
				'return_args' => array( '<code>array</code>: An associative array of configuration options to pass to the widget' ),
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
			'label' => __( 'Save ' . $this->getSingularName(), 'mwp-rules' ),
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
		$data = $this->data;
		$widget_type = $values['widget_config']['widget'];
		$widget_config = $values['widget_config']['widget_' . $widget_type . '_config'];
		$config_preset_options = $this->getPlugin()->getRulesConfigPresetOptions();
		
		if ( isset( $config_preset_options[ $widget_type ]['config']['saveValues'] ) and is_callable( $config_preset_options[ $widget_type ]['config']['saveValues'] ) ) {
			$saveValues = $config_preset_options[ $widget_type ]['config']['saveValues'];
			$saveValues( 'widget_' . $widget_type . '_config', $widget_config, $this );
		}
		
		$data['advanced_options'] = $values['advanced_options'];
		$data['widget_config'] = array(
			$widget_type => $widget_config,
		);
		
		$_values = $values['argument_details'];
		$_values['widget'] = $widget_type;
		$_values['varname'] = strtolower( $_values['varname'] );
		$_values['data'] = $data;
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Build the form to set a default value for this argument
	 * 
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildSetDefaultForm()
	{
		$form = static::createForm( 'set_default', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$plugin = $this->getPlugin();
		$data = $this->data;
		
		/* Display details for the app/bundle/parent */
		$form->addHtml( 'argument_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'argument' => $this, 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'hook_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-"></i>',
				'label' => $this->getSingularName(),
				'title' => $this->title,
			]));
		}
		
		$this->addFormWidget( $form, $this->getSavedValues( 'default' ) ?: array() );
		
		$argument = $this;
		$form->onComplete( function() use ( $argument ) {
			if ( $parent = $argument->getParent() ) {
				wp_redirect( $parent->url( array( 'do' => 'edit', 'id' => $parent->id(), '_tab' => 'arguments' ) ) );
				exit;
			}
		});
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save Default Value', 'mwp-rules' ),
		), '');

		return $form;
	}
	
	/**
	 * Process submitted form values 
	 *
	 * @param	array			$values				Submitted form values
	 * @return	void
	 */
	public function processSetDefaultForm( $values )
	{
		$data = $this->data;
		$widget_values = $this->getWidgetFormValues( $values );
		$this->updateValues( $widget_values, 'default' );
	}
	
	/**
	 * Build the form to set a default value for this argument
	 * 
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildDeleteForm()
	{
		$form = parent::buildDeleteForm();
		$argument = $this;
		
		$form->onComplete( function() use ( $argument ) {
			if ( $parent = $argument->getParent() ) {
				wp_redirect( $parent->url(['_tab' => 'arguments']) );
				exit;
			}
		});
		
		return $form;
	}
	
	/**
	 * Add a configuration widget for this argument to a form
	 * 
	 * @param	MWP\Framework\Helpers\Form			$form				The form to add to
	 * @param	array								$values				Existing form values for widget
	 * @return	void
	 */
	public function addFormWidget( $form, $values )
	{
		$preset = $this->getPreset();
		
		/**
		 * Build the input form
		 */
		if ( isset( $preset['form'] ) and is_callable( $preset['form'] ) ) {
			$form->addField( $this->varname . '_widget', 'fieldgroup' );
			$form->setCurrentContainer( $this->varname . '_widget' );
			call_user_func( $preset['form'], $form, $values, $this );
			$form->endLastContainer();
		}
	}
	
	/**
	 * Get the values to save created by the widget
	 *
	 * @param	array			$values				Values from the submitted form
	 * @return	array								Processed values to save
	 */
	public function getWidgetFormValues( $values )
	{
		$preset = $this->getPreset();
		$widget_values = isset( $values[ $this->varname . '_widget' ] ) ? $values[ $this->varname . '_widget' ] : [];
		
		if ( isset( $preset['saveValues'] ) and is_callable( $preset['saveValues'] ) ) {
			$saveValues = $preset['saveValues'];
			$saveValues( $widget_values, $this );
		}
		
		return $widget_values;
	}
	
	/**
	 * @var	array
	 */
	protected $widget_config;
	
	/**
	 * Get the options used to configure the input widget
	 *
	 * @return	array
	 */
	public function getWidgetConfig()
	{
		if ( isset( $this->widget_config ) ) {
			return $this->widget_config;
		}
		
		$data = $this->data;
		$widget_type = $this->widget;
		$config_preset_options = $this->getPlugin()->getRulesConfigPresetOptions();		
		$widget_config = isset( $data['widget_config'][ $widget_type ] ) ? $data['widget_config'][ $widget_type ] : [];
		$config_options = [
			'label' => $this->title,
			'description' => $this->description,
			'required' => $this->getParent() instanceof Bundle && $this->required,
		];
		
		/* Use the getOptions callback to get configured options for this widget */
		if ( isset( $config_preset_options[ $widget_type ]['config']['getConfig'] ) and is_callable( $config_preset_options[ $widget_type ]['config']['getConfig'] ) ) {
			$getConfig = $config_preset_options[ $widget_type ]['config']['getConfig'];
			$_options = $getConfig( 'widget_' . $widget_type . '_config', $widget_config, $this );
			if ( is_array( $_options ) ) {
				$config_options = array_replace_recursive( $config_options, $_options );
			}
		}
		
		/* Use the advanced php code bundle to provide additional options for this widget */
		if ( isset( $data['advanced_options']['widget_use_advanced'] ) and $data['advanced_options']['widget_use_advanced'] ) {
			if ( isset( $data['advanced_options']['widget_options_phpcode'] ) and $phpcode = $data['advanced_options']['widget_options_phpcode'] ) {
				$variables = array(
					'options' => $config_options,
					'argument' => $this,
				); 
				
				$evaluate = rules_evaluation_closure( $variables );
				$_options = $evaluate( $phpcode );
				if ( is_array( $_options ) ) {
					$config_options = array_replace_recursive( $config_options, $_options );
				}
			}
		}

		$this->widget_config = $config_options;
		return $this->widget_config;
	}
	
	/**
	 * @var	array
	 */
	protected $definition;
	
	/**
	 * Get the argument definition
	 *
	 * @return	array
	 */
	public function getProvidesDefinition()
	{		
		return array(
			'argtype' => $this->type,
			'label' => $this->title,
			'description' => $this->description,
			'class' => $this->class,
			'nullable' => ! $this->required,
		);
	}
	
	/**
	 * Get the argument definition
	 *
	 * @return	array
	 */
	public function getReceivesDefinition()
	{
		if ( isset( $this->definition ) ) {
			return $this->definition;
		}
		
		$argument = $this;
		
		$this->definition = array(
			'label' => $this->title,
			'argtypes' => array( 
				$this->type => array(
					'description' => $this->description,
					'classes' => $this->class ? array( $this->class ) : NULL,
				),
			),
			'required' => (bool) $this->required,
			'configuration' => array(
				'form' => array( static::class, 'preset_' . $this->id() . '_form' ),
				'saveValues' => array( static::class, 'preset_' . $this->id() . '_saveValues' ),
				'getArg' => array( static::class, 'preset_' . $this->id() . '_getArg' ),
			),
		);
		
		return $this->definition;
	}
	
	/**
	 * Get the argument table column definition
	 *
	 * @return	array
	 */
	public function getColumnDefinition()
	{
		$definition = array(
			'allow_null' => true,
			'auto_increment' => false,
			'binary' => false,
			'decimals' => null,
			'default' => null,
			'length' => 1028,
			'name' => $this->getColumnName(),
			'type' => 'VARCHAR',
			'unsigned' => false,
			'values' => [],
			'zerofill' => false,
		);
		
		if ( $this->required ) {
			$definition['allow_null'] = false;
		}
		
		switch( $this->type ) {
			case 'string':
				$definition['collation'] = 'utf8mb4_unicode_ci';
				break;
			
			case 'int':
				$definition['type'] = 'BIGINT';
				$definition['length'] = 20;
				break;
				
			case 'float':
				$definition['type'] = 'FLOAT';
				$definition['length'] = 25;
				$definition['decimals'] = 10;
				break;
				
			case 'bool':
				$definition['type'] = 'INT';
				$definition['length'] = 1;
				break;
			
			case 'mixed':
			case 'object':
			case 'array':
				$definition['type'] = 'MEDIUMTEXT';
				$definition['length'] = 0;
				$definition['collation'] = 'utf8mb4_unicode_ci';
				break;
		}
		
		return $definition;
	}
	
	/**
	 * Get the name of the data column for this argument
	 *
	 * @return	string
	 */
	public function getColumnName()
	{
		return 'entry_col_' . $this->id();
	}
	
	/**
	 * @var	array
	 */
	protected $preset;
	
	/**
	 * Get the preset handlers
	 *
	 * @return	array
	 */
	public function getPreset()
	{
		if ( isset( $this->preset ) ) {
			return $this->preset;
		}
		
		$this->preset = $this->getPlugin()->configPreset( $this->widget, $this->varname . '_value', $this->getWidgetConfig() );
		return $this->preset;
	}
	
	/**
	 * Get the key used to store custom setting values for this argument
	 *
	 * @return	string
	 */
	public function getValueKey()
	{
		return 'setting';
	}
	
	/**
	 * Get the argument value
	 *
	 * @param	string			$key				The key of the value to get
	 * @return	mixed
	 */
	public function getValue( $key=NULL )
	{
		$data = $this->data;
		$preset = $this->getPreset();
		$saved_values = $this->getSavedValues( $key );
		
		if ( $saved_values === NULL and $key !== 'default' and isset( $data['advanced_options']['widget_use_default'] ) and $data['advanced_options']['widget_use_default'] ) {
			$saved_values = $this->getSavedValues( 'default' );
		}
		
		if ( isset( $preset['getArg'] ) and is_callable( $preset['getArg'] ) ) {
			$getArg = $preset['getArg'];
			return $getArg( $saved_values, $this );
		}
		
		return NULL;
	}
	
	/**
	 * Get saved widget values by key
	 *
	 * @param	string			$key			Key to retrieve values from
	 * @return	array|NULL
	 */
	public function getSavedValues( $key=NULL )
	{
		$data = $this->data;
		
		/* In absence of key, return customized values with a fallback to the default values */
		if ( $key === NULL ) {
			if ( $value_key = $this->getValueKey() ) {
				return $this->getSavedValues( $value_key ) ?: $this->getSavedValues( 'default' );
			}
		}
		
		if ( $key === 'default' and ! $this->usesDefault() ) {
			return NULL;
		}
		
		if ( isset( $data['values'][ $key ][ $this->varname ] ) ) {
			return $data['values'][ $key ][ $this->varname ];
		}
		
		return NULL;
	}
	
	/**
	 * Check if the argument uses a default value
	 * 
	 * @return	bool
	 */
	public function usesDefault()
	{
		$data = $this->data;
		return ( isset( $data['advanced_options']['widget_use_default'] ) and $data['advanced_options']['widget_use_default'] );
	}
	
	/**
	 * Check if the argument can have its value customized
	 * 
	 * @return	bool
	 */
	public function isSettable()
	{
		$data = $this->data;
		return ( isset( $data['advanced_options']['widget_allow_custom_value'] ) and $data['advanced_options']['widget_allow_custom_value'] );
	}
	
	/**
	 * Update saved widget values
	 *
	 * @param	array			$values			The values to update
	 * @param	string|NULL		$key			Key to update values for
	 * @return	void
	 */
	public function updateValues( $values, $key=NULL )
	{
		$data = $this->data;
		$key = $key ?: $this->getValueKey();
		
		$data['values'][ $key ] = array(
			$this->varname => $values,
		);
		$this->data = $data;
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		
		$argument_data = $this->data;
		if ( isset( $argument_data['values'] ) and is_array( $argument_data['values'] ) ) {
			$argument_data['values'] = array(
				'default' => isset( $argument_data['values']['default'] ) ? $argument_data['values']['default'] : array(),
			);
		}
		
		$export['data']['argument_data'] = json_encode( $argument_data );
		
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	ActiveRecord	$parent				The parent object
	 * @return	array
	 */
	public static function import( $data, ActiveRecord $parent )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$argument = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				
				/* Merge custom data */
				if ( $col === 'data' ) {
					$argument_data = $argument->data ?: array();
					$new_data = json_decode( $value, true ) ?: array();
					$value = json_encode( array_replace_recursive( $argument_data, $new_data ) );
				}
				
				$argument->_setDirectly( $col, $value );
			}
			
			$argument->parent_type = static::getParentType( $parent );
			$argument->parent_id = $parent->id();
			$argument->imported = time();
			$result = $argument->save();
			
			if ( ! is_wp_error( $result ) ) {
				$results['imports']['arguments'][] = $data;
			} else {
				$results['errors']['arguments'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getArgumentsController( $this->getParent() )->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit' ), $params ) );
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
		$result = parent::save();
		
		if ( $this->parent_type == 'log' ) {
			if ( $log = $this->getParent() ) {
				$log->save();
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		Plugin::instance()->clearCustomHooksCache();
		$result = parent::delete();
		
		if ( $this->parent_type == 'log' ) {
			if ( $log = $this->getParent() ) {
				$dbHelper = DbHelper::instance();
				$dbHelper->dropColumn( $log->getTableName(), $this->getColumnName() );
			}
		}
		
		return $result;
	}
	
	/**
	 * Magic method used to act as a rules ECA callback
	 * 
	 * @see getRecievesDefinition()
	 * @return	mixed
	 */
	public static function __callStatic( $name, $arguments )
	{
		$parts = explode( '_', $name );
		if ( $parts[0] == 'preset' ) {
			if ( count( $parts ) == 3 ) {
				try {
					if ( $argument = static::load( $parts[1] ) ) {
						if ( $preset = $argument->getPreset() ) {
							if ( isset( $preset[ $parts[2] ] ) and is_callable( $preset[ $parts[2] ] ) ) {
								return call_user_func_array( $preset[ $parts[2] ], $arguments );
							}
						}
					}
				}
				catch( \OutOfRangeException $e ) { }
			}
		}
	}


}
