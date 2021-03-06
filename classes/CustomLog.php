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
    protected static $table = "rules_custom_logs";

    /**
     * @var    array        Table columns
     */
    protected static $columns = array(
        'id',
		'uuid',
        'title',
        'weight',
		'description',
		'enabled',
		'data' => array( 'format' => 'JSON' ),
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
    protected static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    protected static $prefix = 'custom_log_';
	
	/**
	 * @var	string
	 */
	protected static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	protected static $sequence_col = 'weight';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Custom Log';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Custom Logs';
	
	/**
	 * @var array
	 */
	protected $_arguments;
	
	/**
	 * Get the log arguments
	 *
	 * @param	bool		$reload				Reload the arguments
	 * @return	array
	 */
	public function getArguments( $reload=FALSE )
	{
		if ( ! $reload and isset( $this->_arguments ) ) {
			return $this->_arguments;
		}
		
		$this->_arguments = Argument::loadWhere( array( 'argument_parent_type=%s AND argument_parent_id=%d', Argument::getParentType( $this ), $this->id() ), 'argument_weight ASC' );
		
		return $this->_arguments;
	}
	
	/**
	 * Get a specific argument
	 *
	 * @param	string			$varname			The argument varname
	 * @return	Argument|NULL
	 */
	public function getArgument( $varname ) 
	{
		foreach( $this->getArguments() as $argument ) {
			if ( $argument->varname === $varname ) {
				return $argument;
			}
		}
		
		return NULL;
	}
	
	/**
	 * Get the controller
	 *
	 * @param	string		$key			The controller key
	 * @return	ActiveRecordController
	 */
	public function _getController( $key='admin' )
	{
		return $this->getPlugin()->getCustomLogsController( $key );
	}
	
	/**
	 * Get the max logs 
	 *
	 * @return	int
	 */
	public function getMaxLogs()
	{
		return $this->max_logs > 0 ? $this->max_logs : 0;
	}
	
	/**
	 * Get the max log age
	 *
	 * @return	int
	 */
	public function getMaxAge()
	{
		return $this->max_age > 0 ? $this->max_age : 0;
	}
	
	/**
	 * Get the event definition
	 *
	 * @return	array
	 */
	public function getEventDefinition()
	{
		$definition = array(
			'title' => 'Entry Logged For: ' . $this->title,
			'description' => 'A log entry has been logged to the log: ' . $this->title,
			'group' => 'Custom Log',
			'arguments' => array(
				'log' => array(
					'argtype' => 'object',
					'class' => 'MWP\Rules\CustomLog',
					'label' => 'Custom Log',
					'description' => 'The custom log being logged to',
				),
				'entry' => array(
					'argtype' => 'object',
					'class' => $this->getRecordClass(),
					'label' => 'Log Entry',
					'description' => 'The log entry which was just logged',
				),
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
			'view' => array(
				'title' => '',
				'title' => __( 'View Log Entries', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-list-alt',
				'url' => $this->getRecordController()->getUrl(),
			),
			'edit' => array(
				'title' => $this->_getEditTitle(),
				'icon' => 'glyphicon glyphicon-pencil',
				'params' => array(
					'do' => 'edit',
					'id' => $this->id(),
				),
			),
			'manage_fields' => array(
				'title' => __( 'Manage Custom Fields', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-expand',
				'params' => array(
					'do' => 'edit',
					'_tab' => 'arguments',
					'id' => $this->id(),
				),
			),
			'export' => array(
				'title' => __( 'Download ' . $this->_getSingularName(), 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-export',
				'params' => array(
					'do' => 'export',
					'id' => $this->id(),
				),
			),
			'empty' => array(
				'separator' => true,
				'title' => __( 'Flush Log Entries', 'mwp-rules' ),
				'icon' => 'glyphicon glyphicon-erase',
				'attr' => array(
					'class' => 'text-warning',
				),
				'params' => array(
					'do' => 'flush',
					'id' => $this->id(),
				),
			),
			'delete' => array(
				'title' => $this->_getDeleteTitle(),
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'class' => 'text-danger',
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
		
		$form->addTab( 'log_display', array(
			'title' => __( 'Display Options', 'mwp-rules' ),
		));
		
		$message_lang = isset( $this->data['message_lang'] ) ? $this->data['message_lang'] : 'Message';
		$form->addField( 'message_lang', 'text', array(
			'label' => __( 'Message Column Name', 'mwp-rules' ),
			'description' => __( 'Customize the name of the message column as shown on the log table.', 'mwp-rules' ),
			'attr' => [ 'placeholder' => 'Message' ],
			'data' => $message_lang,
			'required' => true,
		));
		
		$column_choices = array(
			'Date/Time' => 'entry_timestamp',
			$message_lang => 'entry_message',
		);
		
		foreach( $this->getArguments() as $argument ) {
			$column_choices[ $argument->title ] = $argument->getColumnName();
		}
		
		$form->addField( 'field_visibility', 'choice', array(
			'label' => __( 'Displayed Table Columns', 'mwp-rules' ),
			'choices' => $column_choices,
			'data' => isset( $this->data['field_visibility'] ) ? $this->data['field_visibility'] : array( 'entry_timestamp', 'entry_message' ),
			'description' => __( 'Choose the fields that should display as columns when viewing the log entries table.', 'mwp-rules' ),
			'multiple' => true,
			'expanded' => true,
			'required' => true,
		));
		
		$form->addField( 'default_per_page', 'number', array(
			'label' => __( 'Entries Per Page', 'mwp-rules' ),
			'description' => __( 'Number of entries to show per page in the log table.', 'mwp-rules' ),
			'data' => isset( $this->data['default_per_page'] ) ? intval( $this->data['default_per_page'] ) : 50,
			'required' => true,
		));
		
		$form->addField( 'default_sortby', 'choice', array(
			'label' => __( 'Default Sort Column', 'mwp-rules' ),
			'choices' => $column_choices,
			'description' => __( 'Choose the column which entries should be sorted by default.', 'mwp-rules' ),
			'required' => true,
			'data' => isset( $this->data['default_sortby'] ) ? $this->data['default_sortby'] : 'entry_timestamp',
		));
		
		$form->addField( 'default_sortorder', 'choice', array(
			'label' => __( 'Default Sort Order', 'mwp-rules' ),
			'choices' => [ 'ASC' => 'ASC', 'DESC' => 'DESC' ],
			'description' => __( 'Choose the default sort order for entries.', 'mwp-rules' ),
			'expanded' => true,
			'required' => true,
			'data' => isset( $this->data['default_sortorder'] ) ? $this->data['default_sortorder'] : 'DESC',
		));
		
		$form->addField( 'sortable_columns', 'choice', array(
			'label' => __( 'Sortable Columns', 'mwp-rules' ),
			'choices' => $column_choices,
			'data' => isset( $this->data['sortable_columns'] ) ? $this->data['sortable_columns'] : array( 'entry_timestamp' ),
			'description' => __( 'Choose the columns that the user can sort log entries by.', 'mwp-rules' ),
			'multiple' => true,
			'expanded' => true,
			'required' => false,
		));
		
		$form->addField( 'searchable_columns', 'choice', array(
			'label' => __( 'Searchable Columns', 'mwp-rules' ),
			'choices' => $column_choices,
			'data' => isset( $this->data['searchable_columns'] ) ? $this->data['searchable_columns'] : array( 'entry_message' ),
			'description' => __( 'Choose the columns that the user can search log entries by.', 'mwp-rules' ),
			'multiple' => true,
			'expanded' => true,
			'required' => false,
		));
		
		$form->addTab( 'log_maintenance', array(
			'title' => __( 'Retention Options', 'mwp-rules' ),
		));
		
		$form->addField( 'max_logs', 'integer', array(
			'label' => __( 'Max Total Entries', 'mwp-rules' ),
			'attr' => [ 'min' => '0', 'step' => 1 ],
			'description' => __( 'Enter the maximum amount of entries to retain for this log. Once this threshold is reached, older logs will be deleted to make room for newer logs. Set to 0 to disable.', 'mwp-rules' ),
			'data' => $this->max_logs ?: 0,
		));
		
		$form->addField( 'max_age', 'integer', array(
			'label' => __( 'Max Entry Age', 'mwp-rules' ),
			'attr' => [ 'min' => '0', 'step' => 1 ],
			'description' => __( 'Enter the maximum amount of days to retain entries for this log. Once logs have reached the threshold age, they will be deleted. Set to 0 to disable.', 'mwp-rules' ),
			'data' => $this->max_age ?: 0,
		));
		
		if ( $this->id() ) {
			$form->addTab( 'arguments', array(
				'title' => __( 'Custom Fields', 'mwp-rules' ),
			));
			
			$argumentsController = $plugin->getArgumentsController( $this );
			$argumentsTable = $argumentsController->createDisplayTable();
			unset( $argumentsTable->columns['default_value'] );
			$argumentsTable->bulkActions = array();
			$argumentsTable->prepare_items();
			
			
			$form->addHtml( 'arguments_table', $this->getPlugin()->getTemplateContent( 'rules/arguments/table_wrapper', array( 
				'log' => $this, 
				'table' => $argumentsTable, 
				'controller' => $argumentsController,
			)));
			
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
		$_values = array_merge( $values['log_details'], $values['log_maintenance'] );
		
		$this->data = $values['log_display'];
		
		parent::processEditForm( $_values );
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildFlushForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'flush', array( 'attr' => array( 'class' => 'container', 'style' => 'max-width: 600px; margin: 75px auto;' ) ) );
		
		$form->addHtml( 'flush_notice', $plugin->getTemplateContent( 'views/management/records/notice_flush', [ 'record' => $this ] ) );
		
		$form->addField( 'cancel', 'submit', array( 
			'label' => __( 'Cancel', 'mwp-framework' ), 
			'attr' => array( 'class' => 'btn btn-warning' ),
			'row_attr' => array( 'class' => 'col-xs-6 text-right' ),
		));
		
		$form->addField( 'confirm', 'submit', array( 
			'label' => __( 'Confirm Flush', 'mwp-framework' ), 
			'attr' => array( 'class' => 'btn btn-danger' ),
			'row_attr' => array( 'class' => 'col-xs-6 text-left' ),
		));
		
		return $form;
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
			
			foreach( $this->getArguments( true ) as $argument ) {
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
	 * Create controllers for each custom log
	 *
	 * @return
	 */
	public static function createRecordControllers()
	{
		$_suppress = static::getDb()->suppress_errors;
		static::getDb()->suppress_errors = true;
		foreach( static::loadWhere('1') as $log ) {
			$log->getRecordController();
		}
		static::getDb()->suppress_errors = $_suppress;
	}
	
	/**
	 * Get the custom language used for the log message
	 *
	 * @return	string
	 */
	public function getMessageLang()
	{
		return ( isset( $this->data['message_lang'] ) and $this->data['message_lang'] ) ? $this->data['message_lang'] : 'Message';	
	}
	
	/**
	 * Get log entry record class
	 *
	 * @return	string
	 */
	public function getRecordClass()
	{
		$class = 'MWP\Rules\CustomLogEntry' . $this->id();
		
		if ( ! class_exists( $class ) ) {
			eval( "
				namespace MWP\Rules;
				class CustomLogEntry{$this->id()} extends CustomLogEntry {
					protected static \$multitons = array();
					protected \$log_id = {$this->id()};
					protected static \$table = \"rules_custom_log_{$this->id()}\";
					public static \$columns = array(
						'id' => [ 'title' => 'ID' ],
						'timestamp' => [ 'title' => 'Date/Time' ],
						'message' => [ 'title' => 'Message' ],
					);
				}
			");
			
			$class::$columns['message']['title'] = $this->getMessageLang();
			$class::setControllerClass( Controllers\CustomLogEntriesController::class );
		}
		
		return $class;
	}
	
	/**
	 * Get the active record controller
	 *
	 * @return	ActiveRecordController
	 */
	public function getRecordController()
	{
		$class = $this->getRecordClass();
		$controller = $class::getController( 'admin' );
		$log = $this;
		
		if ( ! $controller ) {
			
			$sortable_columns = isset( $this->data['sortable_columns'] ) ? (array) $this->data['sortable_columns'] : array( 'entry_timestamp' );
			$searchable_columns = isset( $this->data['searchable_columns'] ) ? (array) $this->data['searchable_columns'] : array( 'entry_message' );
			
			$controller_config = array(
				'adminPage' => [ 
					'for' => is_multisite() ? 'network' : 'site',
					'type' => 'submenu',
					'title' => $this->title . ': ' . $class::$lang_plural,
				],
				'tableConfig' => array(
					'perPage' => isset( $this->data['default_per_page'] ) ? $this->data['default_per_page'] : 50,
					'sortBy' => isset( $this->data['default_sortby'] ) ? $this->data['default_sortby'] : 'entry_timestamp',
					'sortOrder' => isset( $this->data['default_sortorder'] ) ? $this->data['default_sortorder'] : 'DESC',
					'columns' => array(
						'entry_timestamp' => __( 'Date/Time', 'mwp-rules' ),
						'entry_message' => __( $this->getMessageLang(), 'mwp-rules' ),
					),
					'sortable' => array_combine( $sortable_columns, $sortable_columns ),
					'searchable' => array_combine( $searchable_columns, array_map( function( $column ) { return [ 'type' => 'contains', 'combine_words' => 'AND' ]; }, $searchable_columns ) ),
					'handlers' => array(
						'entry_timestamp' => function( $row ) {
							return get_date_from_gmt( date( 'Y-m-d H:i:s', $row['entry_timestamp'] ), 'F j, Y H:i:s' );
						},
					),
				),
			);
			
			$display_columns = ( isset( $this->data['field_visibility'] ) and ! empty( $this->data['field_visibility'] ) ) ? $this->data['field_visibility'] : array( 'entry_timestamp', 'entry_message' );
			
			foreach( $this->getArguments() as $argument ) 
			{				
				$column_name = $argument->getColumnName();
				
				$class::$columns[ 'col_' . $argument->id() ] = array(
					'title' => $argument->title,
					'format' => in_array( $argument->type, array( 'mixed', 'array', 'object' ) ) ? 'JSON' : NULL,
				);
				
				if ( in_array( $column_name, $display_columns ) ) {
					$controller_config['tableConfig']['columns'][ $class::$prefix . 'col_' . $argument->id() ] = $argument->title;
					$controller_config['tableConfig']['handlers'][ $column_name ] = function( $row ) use ( $argument, $column_name, $log ) {
						if ( isset( $argument->data['advanced_options']['argument_handle_display'] ) and $argument->data['advanced_options']['argument_handle_display'] ) {
							$args = array(
								'column_value' => $row[ $column_name ],
								'column_name' => $column_name,
								'row' => $row,
								'log' => $log,
								'argument' => $argument,
							);
							$evaluate = rules_evaluation_closure( $args );
							return $evaluate( $argument->data['advanced_options']['argument_display_phpcode'] );
						}
						
						return $argument->getDisplayValue( $row[ $column_name ] );
					};
				}
			}
			
			if ( ! in_array( 'entry_message', $display_columns ) ) {
				unset( $controller_config['tableConfig']['columns']['entry_message'] );
			}
			
			if ( ! in_array( 'entry_timestamp', $display_columns ) and count( $controller_config['tableConfig']['columns'] ) > 1 ) {
				unset( $controller_config['tableConfig']['columns']['entry_timestamp'] );
			}
			
			$controller = $class::createController( 'admin', apply_filters( 'rules_custom_log_controller_config', $controller_config, $this ) );			
		}
		
		return $controller;
	}
	
	/**
	 * Check if maintenance on the log is needed and schedule it
	 *
	 * @return	void
	 */
	public function checkAndScheduleMaintenance()
	{
		/* Check if maintenance is already queued */
		if ( Framework\Task::countTasks( 'rules_log_maintenance', 'custom_log_' . $this->id() ) ) {
			return;
		}
		
		$recordClass = $this->getRecordClass();
		
		if ( $this->getMaxAge() > 0 ) {
			if ( $recordClass::countWhere( array( 'entry_timestamp<=%d', time() - ( $this->getMaxAge() * 24 * 60 * 60 ) ) ) ) {
				Framework\Task::queueTask([ 'action' => 'rules_log_maintenance', 'tag' => 'custom_log_' . $this->id() ], [ 'log_id' => $this->id() ]);
				return;
			}
		}
		
		if ( $this->getMaxLogs() > 0 ) {
			if ( $recordClass::countWhere('1') > $this->getMaxLogs() ) {
				Framework\Task::queueTask([ 'action' => 'rules_log_maintenance', 'tag' => 'custom_log_' . $this->id() ], [ 'log_id' => $this->id() ]);
				return;
			}
		}
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
		$this->checkAndScheduleMaintenance();
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
		foreach( $this->getArguments() as $argument ) {
			$argument->delete();
		}
		
		$result = parent::delete();
		
		Plugin::instance()->clearCustomHooksCache();
		$dbHelper = Framework\DbHelper::instance();
		$dbHelper->dropTable( $this->getTableName() );
		
		return $result;
	}
	
	/**
	 * Magic method used to act as a rules ECA callback
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
						if ( $parts[2] == 'create' ) {
							$fields = $arguments;
							$recordClass = $log->getRecordClass();
							
							$entry = new $recordClass;
							$entry->timestamp = time();
							$entry->message = array_shift( $fields );
							
							foreach( $log->getArguments() as $argument ) {
								$column = 'col_' . $argument->id();
								$entry->$column = array_shift( $fields );
							}
							
							$result = $entry->save();
							
							if ( is_wp_error( $result ) ) {
								return array( 'success' => false, 'message' => $result->get_error_message(), 'entry' => $entry->dataArray() );
							}
							
							/* Trigger rules event */
							call_user_func_array( 'do_action', array_merge( array( $log->getHookPrefix() . '_' . $parts[2] ), array_merge( array( $log, $entry ), $arguments ) ) );
							
							return array( 'success' => true, 'message' => 'Log entry created.', 'entry' => $entry->dataArray() );
						}
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
	
	/**
	 * Flush the logs table
	 *
	 * @return	void
	 */
	public function flushLogs()
	{
		$recordClass = $this->getRecordClass();
		$this->getPlugin()->getDb()->query( "TRUNCATE TABLE " . $recordClass::_getTable(true) );
	}
	
}
