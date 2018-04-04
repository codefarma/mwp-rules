<?php
/**
 * Plugin Class File
 *
 * Created:   April 3, 2018
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
 * App Class
 */
class _App extends ActiveRecord
{
    /**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_apps";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'title',
		'description',
		'weight',
		'enabled',
		'creator',
		'created_time',
		'imported_time',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'app_';

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
	public static $lang_singular = 'App';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Apps';
	
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
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit' );
		
		if ( $this->title ) {
			$form->addHtml( 'app_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-tent"></i> ',
				'label' => 'App',
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'app_details', array(
			'title' => __( 'App Details', 'mwp-rules' ),
		));
		
		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'app_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'app_details' );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Enabled', 'mwp-rules' ),
			'description' => __( 'Choose whether this app is enabled or not.', 'mwp-rules' ),
			'value' => 1,
			'data' => (bool) $this->enabled,
		), 'app_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'app_features', array(
				'title' => __( 'App Features', 'mwp-rules' ),
			));
			
			$featuresController = $plugin->getFeaturesController( $this );
			$featuresTable = $featuresController->createDisplayTable();
			$featuresTable->bulkActions = array();
			$featuresTable->prepare_items();
			
			$form->addHtml( 'features_table', $this->getPlugin()->getTemplateContent( 'rules/features/table_wrapper', array( 
				'app' => $this, 
				'table' => $featuresTable, 
				'controller' => $featuresController,
			)),
			'app_features' );
			
		} else {
			$app = $this;
			$form->onComplete( function() use ( $app, $plugin ) {
				$controller = $plugin->getAppsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $app->id(), '_tab' => 'app_features' ) ) );
				exit;
			});			
		}
		
		$submit_text = $this->id() ? 'Save App' : 'Create App';
		$form->addField( 'save', 'submit', [ 'label' => __( $submit_text, 'mwp-rules' ), 'row_prefix' => '<hr>', 'row_attr' => [ 'class' => 'text-center' ] ] );
		
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
		$_values = $values['app_details'];
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Get the features of this app
	 *
	 * @return	array[Feature]
	 */
	public function getFeatures()
	{
		return Feature::loadWhere( array( 'feature_app_id=%d', $this->id() ) );
	}
	
	/**
	 * Get the app url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getAppsController()->getUrl( array( 'id' => $this->id(), 'do' => 'edit' ) + $params );
	}
	
	
}
