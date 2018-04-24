<?php
/**
 * Plugin Class File
 *
 * Created:   December 6, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework;

/**
 * Log Class
 */
class _CustomLog extends ExportableRecord
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_custom_logs";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid',
        'title',
        'weight',
		'bundle_id',
		'description',
		'enabled',
		'key',
		'class',
		'max_logs',
		'entity_max',
		'max_age',
		'limit',
		'display_empty',
		'sortby',
		'sortdir',
		'display_time',
		'lang_time',
		'lang_message',
		'imported',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'custom_log_';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Log';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Logs';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * Get the associated bundle
	 *
	 * @return	MWP\Rules\Bundle|NULL
	 */
	public function getBundle()
	{
		if ( $this->bundle_id ) {
			try {
				return Bundle::load( $this->bundle_id );
			} catch( \OutOfRangeException $e ) { }
		}
		
		return NULL;
	}
	
	/**
	 * Get the log arguments
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
			'title' => 'Entry Logged: ' . $this->title,
			'description' => 'A log entry has been logged to: ' . $this->title,
			'group' => 'Custom Log',
			'arguments' => array(
				'message' => array(
					'argtype' => 'string',
					'label' => 'Log Message',
					'description' => 'The logged message',
				),
			),
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getProvidesDefinition();
		}
		
		$definition['log_data'] = $this->_data;
		
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
			'title' => 'Create Log Entry: ' . $this->title,
			'description' => 'Create a new log entry for: ' . $this->title,
			'group' => 'Custom Log',
			'arguments' => array(
				'message' => array(
					'label' => 'Log Message',
					'default' => 'manual',
					'argtypes' => array( 'string' => array( 'description' => 'A message to log' ) ),
					'configuration' => array(
						'form' => array( static::class, 'preset_message_form' ),
						'getArg' => array( static::class, 'preset_message_getArg' ),
					),
				),
			),
			'callback' => array( static::class, 'callback_' . $this->id() . '_create' ),
		);
		
		foreach( $this->getArguments() as $argument ) {
			$definition['arguments'][ $argument->varname ] = $argument->getReceivesDefinition();
		}
		
		$definition['log_data'] = $this->_data;
		
		return $definition;
	}
	
	/**
	 * Get the hook prefix for this log
	 *
	 * @return	string
	 */
	public function getHookPrefix()
	{
		return 'rules_log_' . $this->uuid;
	}
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		return array(
			'edit' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-pencil',
				'attr' => array( 
					'title' => $this->_getEditTitle(),
					'class' => 'btn btn-sm btn-default',
				),
				'params' => array(
					'do' => 'edit',
					'id' => $this->id(),
				),
			),
			'view' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-eye-open',
				'attr' => array( 
					'title' => $this->_getViewTitle(),
					'class' => 'btn btn-sm btn-default',
				),
				'params' => array(
					'do' => 'view',
					'id' => $this->id(),
				),
			),
			'export' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-export',
				'attr' => array( 
					'title' => __( 'Export ' . $this->_getSingularName(), 'mwp-rules' ),
					'class' => 'btn btn-sm btn-default',
				),
				'params' => array(
					'do' => 'export',
					'id' => $this->id(),
				),
			),
			'delete' => array(
				'title' => '',
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'title' => $this->_getDeleteTitle(),
					'class' => 'btn btn-sm btn-default',
				),
				'params' => array(
					'do' => 'delete',
					'id' => $this->id(),
				),
			),
		);
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
		$log = $this;
		
		if ( $this->title ) {
			$form->addHtml( 'log_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-flash"></i>',
				'label' => __( 'Custom Log', 'mwp-rules' ),
				'title' => $this->title,
			]));
		}
		
		$form->addTab( 'log_details', array(
			'title' => __( 'Log Details', 'mwp-rules' ),
		));
		
		if ( $this->id() ) {
			
			$bundle_choices = [
				'Unassigned' => 0,
			];
			
			foreach( App::loadWhere('1') as $app ) {
				$app_bundles = [];
				foreach( $app->getBundles() as $bundle ) {
					$app_bundles[ $bundle->title ] = $bundle->id();
				}
				$bundle_choices[ $app->title ] = $app_bundles;
			}
			
			foreach( Bundle::loadWhere( 'bundle_app_id=0' ) as $bundle ) {
				$bundle_choices[ 'Independent Bundles' ][ $bundle->title ] = $bundle->id();
			}
			
			$form->addField( 'bundle_id', 'choice', array(
				'label' => __( 'Associated Bundle', 'mwp-rules' ),
				'choices' => $bundle_choices,
				'required' => true,
				'data' => $this->bundle_id,
			), 
			'log_details' );
		}

		$form->addField( 'title', 'text', array(
			'label' => __( 'Title', 'mwp-rules' ),
			'data' => $this->title,
			'required' => true,
		), 'log_details' );
		
		$form->addField( 'description', 'text', array(
			'label' => __( 'Description', 'mwp-rules' ),
			'data' => $this->description,
			'required' => false,
		), 'log_details' );
		
		if ( $this->id() ) {
			$form->addTab( 'arguments', array(
				'title' => __( 'Custom Fields', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'log' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)),
			'arguments' );
		} else {
			$form->onComplete( function() use ( $log, $plugin ) {
				$controller = $plugin->getCustomLogsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $log->id(), '_tab' => 'arguments' ) ) );
				exit;
			});			
		}
		
		$form->addField( 'save', 'submit', array(
			'label' => __( 'Save', 'mwp-rules' ),
		), '');
		
		$form->onComplete( function() use ( $log, $plugin ) {
			if ( $bundle = $log->getBundle() ) {
				$controller = $plugin->getBundlesController( $bundle->getApp() );
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $bundle->id(), '_tab' => 'bundle_logs' ) ) );
				exit;
			}
		});
			
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
		$_values = $values['log_details'];
		
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
		return $this->getPlugin()->getCustomLogsController()->getUrl( array_replace_recursive( array( 'id' => $this->id(), 'do' => 'edit' ), $params ) );
	}
	
	/**
	 * Update the database schema for this log
	 *
	 * @return	array|NULL
	 */
	public function updateSchema()
	{
		if ( $schema = $this->getTableSchema() ) {
			$dbHelper = Framework\DbHelper::instance();
			$tableSQL = $dbHelper->buildTableSQL( $schema, FALSE );
			return dbDelta( $tableSQL, true );
		}
		
		return NULL;
	}
	
	/**
	 * Get the table structure
	 *
	 * @return	array|NULL
	 */
	public function getTableSchema()
	{
		if ( $this->id() ) {
			$table = array(
				'name' => $this->getTableName(),
				'columns' => array(
					'entry_id' => array(
						'allow_null' => false,
						'auto_increment' => true,
						'binary' => false,
						'decimals' => null,
						'default' => null,
						'length' => 20,
						'name' => 'entry_id',
						'type' => 'BIGINT',
						'unsigned' => true,
						'values' => [],
						'zerofill' => false,
					),
					'entry_timestamp' => array(
						'allow_null' => false,
						'auto_increment' => false,
						'binary' => false,
						'decimals' => null,
						'default' => null,
						'length' => 11,
						'name' => 'entry_timestamp',
						'type' => 'INT',
						'unsigned' => true,
						'values' => [],
						'zerofill' => false,
					),
					'entry_message' => array(
						'allow_null' => false,
						'auto_increment' => false,
						'binary' => false,
						'decimals' => null,
						'default' => '',
						'length' => 255,
						'name' => 'entry_message',
						'type' => 'VARCHAR',
						'unsigned' => false,
						'values' => [],
						'zerofill' => false,
					),
				),
				'indexes' => array(
					'PRIMARY' => array(
						'type' => 'primary',
						'name' => 'PRIMARY',
						'length' => array( NULL ),
						'columns' => array(
							'entry_id'
						),
					),
				),
			);
			
			foreach( $this->getArguments() as $argument ) {
				$table['columns'][ $argument->getColumnName() ] = $argument->getColumnDefinition();
			}
			
			return $table;
		}
	}
	
	/**
	 * Get the name of the log table
	 *
	 * @return	string
	 */
	public function getTableName()
	{
		return 'rules_custom_log_' . $this->id();
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
			$log = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$log->_setDirectly( $col, $value );
			}
			
			$log->imported = time();
			$result = $log->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['logs'][] = $data;
				
				$imported_argument_uuids = [];

				/* Import log arguments */
				if ( isset( $data['arguments'] ) and ! empty( $data['arguments'] ) ) {
					foreach( $data['arguments'] as $argument ) {
						$imported_argument_uuids[] = $argument['data']['argument_uuid'];
						$results = array_merge_recursive( $results, Argument::import( $argument, $log ) );
					}
				}
				
				/* Cull previously imported arguments which are no longer part of this imported log */
				foreach( Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d AND argument_imported > 0 AND argument_uuid NOT IN (\'' . implode("','", $imported_argument_uuids) . '\')', Argument::getParentType( $log ), $log->id() ) ) as $argument ) {
					$argument->delete();
				}
				
			} else {
				$results['errors']['logs'][] = $result;
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
		
		$result = parent::save();
		$this->updateSchema();
		Plugin::instance()->clearCustomHooksCache();
		
		return $result;
	}
	
	/**
	 * Delete
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		$result = parent::delete();
		
		Plugin::instance()->clearCustomHooksCache();
		$dbHelper = Framework\DbHelper::instance();
		$dbHelper->dropTable( $this->getTableName() );
		
		return $result;
	}
	
	/**
	 * Magic callback used for serializing for caching purposes
	 * 
	 * @return	mixed
	 */
	public static function __callStatic( $name, $arguments )
	{
		$plugin = Plugin::instance();
		$parts = explode( '_', $name );
		
		/* Execute the callback of an action */
		if ( $parts[0] == 'callback' ) {
			if ( count( $parts ) == 3 ) {
				try {
					if ( $log = static::load( $parts[1] ) ) {
						/* @TODO: Create log entry */
						
						/* Trigger rules event */
						call_user_func_array( 'do_action', array_merge( array( $log->getHookPrefix() . '_' . $parts[2] ), $arguments ) );
					}
				}
				catch( \OutOfRangeException $e ) { }
			}
		}
		
		/* Execute the callback for a preset */
		if ( $parts[0] == 'preset' ) {
			switch( $parts[1] ) {
				case 'message':
					$preset = $plugin->configPreset( 'text', 'message', [ 'label' => __( 'Log Message', 'mwp-rules' ) ] );
					if ( isset( $preset[ $parts[2] ] ) and is_callable( $preset[ $parts[2] ] ) ) {
						return call_user_func_array( $preset[ $parts[2] ], $arguments );
					}
					break;
			}
		}
	}
	
}
