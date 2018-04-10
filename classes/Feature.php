<?php
/**
 * Plugin Class File
 *
 * Created:   April 3, 2018
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
 * FeatureSet Class
 */
class _Feature extends ActiveRecord
{
	
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_features";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'title',
		'weight',
		'description',
		'enabled',
		'creator',
		'created_time',
		'imported_time',
		'app_id',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'feature_';

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
	public static $lang_singular = 'Feature';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Features';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Get the linked app
	 *
	 * @return	MWP\Rules\App|NULL
	 */
	public function getApp()
	{
		if ( $this->app_id ) {
			try {
				return App::load( $this->app_id );
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
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
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		
		/* Display details for the app/feature */
		$form->addHtml( 'feature_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'app' => $this->getApp(), 
		]));
		
		if ( $this->title ) {
			$form->addHtml( 'feature_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-lamp"></i> ',
				'label' => 'Feature',
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'feature_details', array(
			'title' => __( 'Feature Details', 'mwp-rules' ),
		));
		
		if ( $this->id() ) {
			$app_choices = [
				'Unassigned' => 0,
			];
			
			foreach( App::loadWhere('1') as $app ) {
				$app_choices[ $app->title ] = $app->id();
			}
			
			$form->addField( 'app_id', 'choice', array(
				'label' => __( 'Associated App', 'mwp-rules' ),
				'choices' => $app_choices,
				'required' => true,
				'data' => $this->app_id,
			), 'feature_details' );
		}
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'feature_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'feature_details' );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Enabled', 'mwp-rules' ),
			'description' => __( 'Choose whether this feature is enabled or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => $this->enabled !== NULL ? (bool) $this->enabled : true,
		), 'feature_details' );
		
		if ( $this->id() ) {
			
			$form->addTab( 'arguments', array(
				'title' => __( 'Feature Settings', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'actions' => array_replace_recursive( $argumentsController->getActions(), array( 
					'new' => array( 
						'title' => __( 'Add Parameter', 'mwp-rules' ),
					), 
				)),
				'feature' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
			
			$form->addTab( 'feature_rules', array(
				'title' => __( 'Feature Rules', 'mwp-rules' ),
			));
			
			$rulesController = $plugin->getRulesController( $this );
			$rulesTable = $rulesController->createDisplayTable();
			$rulesTable->bulkActions = array();
			$rulesTable->prepare_items( array( 'rule_parent_id=0 AND rule_feature_id=%d', $this->id() ) );
			
			$form->addHtml( 'rules_table', $this->getPlugin()->getTemplateContent( 'rules/subrules/table_wrapper', array( 
				'rule' => null, 
				'table' => $rulesTable, 
				'controller' => $rulesController,
			)),
			'feature_rules' );
			
			/* Redirect to the features tab of the containing app after saving */
			$feature = $this;
			$form->onComplete( function() use ( $feature, $plugin ) {
				if ( $app = $feature->getApp() ) {
					$controller = $plugin->getAppsController();
					wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_features' ) ) );
					exit;
				}
			});
			
		} else {
			/* Redirect to the rules tab of newly created features */
			$feature = $this;
			$form->onComplete( function() use ( $feature, $plugin ) {
				$controller = $plugin->getFeaturesController( $feature->getApp() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $feature->id(), '_tab' => 'feature_rules' ) ) );
				exit;
			});			
		}
		
		$submit_text = $this->id() ? 'Save Feature' : 'Create Feature';
		$form->addField( 'save', 'submit', [ 'label' => __( $submit_text, 'mwp-rules' ), 'row_prefix' => '<hr>', 'row_attr' => [ 'class' => 'text-center' ] ], '' );
		
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
		$_values = $values['feature_details'];
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Get the rules associated with the feature
	 *
	 * @return	array[Rule]
	 */
	public function getRules()
	{
		return Rule::loadWhere( array( 'rule_parent_id=0 AND rule_feature_id=%d', $this->id() ) );
	}
	
	/**
	 * Get a count of all the rules associated with this feature
	 *
	 * @return	int
	 */
	public function getRuleCount()
	{
		return Rule::countWhere( array( 'rule_feature_id=%d', $this->id() ) );
	}
	
	protected $argmap;
	
	/**
	 * Get an argument by varname
	 *
	 * @return	Argument|NULL
	 */
	public function getArgument( $varname )
	{
		$varname = strtolower( $varname );
		
		if ( ! isset( $this->argmap ) ) {
			foreach( $this->getArguments() as $argument ) {
				$this->argmap[ strtolower( $argument->varname ) ] = $argument;
			}
		}
		
		if ( isset( $this->argmap[ $varname ] ) ) {
			return $this->argmap[ $varname ];
		}
		
		return NULL;
	}
	
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getFeaturesController( $this->getApp() )->getUrl( array( 'id' => $this->id(), 'do' => 'edit' ) + $params );
	}
	
}
