<?php

namespace MWP\Rules\Extensions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

class MWPFrameworkPatternActiveRecord extends _MWPFrameworkPatternActiveRecord
{
	/**
	 * Auto generate the class map needed to describe this object type to rules
	 * 
	 * @param	array			$map						The rules class map (passed by reference)
	 * @param	array			$augmentations				Additional properties to add to the map
	 * @return	array
	 */
	public static function addToRulesMap( &$map, $augmentations=[] )
	{
		$object_class = static::class;
		
		$object_map = array(
			'label' => static::$lang_singular,
			'loader' => function( $val, $type, $key ) use ( $object_class ) {
				if ( ! isset( $key ) ) {
					try { return $object_class::load( $val ); } 
					catch( \OutOfRangeException $e ) { return null; }
				}
				
				$records = $object_class::loadWhere( array( $key . '=%s', $val ) );
				return array_shift( $records );
			},
			'mappings' => [],
		);
		
		$type_map = array(
			'varchar'    => 'string',
			'char'       => 'string',
			'text'       => 'string',
			'tinytext'   => 'string',
			'mediumtext' => 'string',
			'longtext'   => 'string',
			'tinyint'    => 'bool',
			'boolean'    => 'bool',
			'bit'        => 'bool',
			'smallint'   => 'int',
			'mediumint'  => 'int',
			'int'        => 'int',
			'bigint'     => 'int',
			'decimal'    => 'float',
			'float'      => 'float',
			'double'     => 'float',
			'year'       => 'string',
			'time'       => 'string',
			'date'       => 'string',
			'datetime'   => 'string',
			'timestamp'  => 'string',
			'enum'       => 'string',
			'set'        => 'string',
		);

		foreach( static::_getColumns() as $prop => $config ) 
		{
			if ( ! is_array( $config ) ) {
				$prop = $config;
				$config = [];
			}
			
			if ( $prop == static::_getKey() ) {
				$config = array(
					'type' => 'bigint',
					'length' => 20,
					'allow_null' => false,
					'auto_increment' => true,
				);
			}
			
			$column_type = isset( $config['type'] ) ? $config['type'] : 'varchar';
			$type = isset( $config['argtype'] ) ? $config['argtype'] : ( isset( $type_map[ $column_type ] ) ? $type_map[ $column_type ] : 'string' );
			$label = isset( $config['label'] ) ? $config['label'] : ucwords( str_replace( '_', ' ', $prop ) );
			$class = isset( $config['class'] ) ? $config['class'] : NULL;
			$nullable = isset( $config['allow_null'] ) && $config['allow_null'] == false ? false : true;
			$keys = isset( $config['keys'] ) ? $config['keys'] : null;
			
			if ( in_array( $column_type, array( 'timestamp', 'datetime' ) ) ) {
				$type = 'object';
				$class = 'DateTime';
				$getter = function( $record ) use ( $prop ) {
					if ( $record->$prop ) {
						try { return new \DateTime( $record->$prop ); } catch( \Exception $e ) { return NULL; }
					}
					
					return NULL;
				};
			}
			else {
				$getter = function( $record ) use ( $prop ) {
					return $record->$prop;
				};
			}
			
			if ( $type == 'string' && isset( $config['format'] ) ) {
				switch( $config['format'] ) {
					case 'JSON':
						$type = 'mixed';
						break;
					case 'ActiveRecord':
						$type = 'object';
						$class = $config['class'];
						break;
				}
			}
			
			if ( isset( $config['title'] ) ) {
				$label = $config['title'];
			}
			
			$mapping = array(
				'argtype' => $type,
				'label' => $label,
				'class' => $class,
				'nullable' => $nullable,
				'getter' => $getter,
				'keys' => $keys,
			);
			
			$object_map['mappings'][$prop] = $mapping;
		}
		
		$map = array_replace_recursive( $map, array( $object_class => array_replace_recursive( $object_map, $augmentations ) ) );
		
		return $map;
	}
	
	/**
	 * Register ECAs to Rules
	 *
	 * @return	void
	 */
	public static function registerRulesECAs()
	{
		$ecas = static::getRulesECAs();
		
		if ( isset( $ecas['events'] ) ) {
			rules_register_events( $ecas['events'] );
		}
		
		if ( isset( $ecas['conditions'] ) ) {
			rules_register_conditions( $ecas['conditions'] );
		}
		
		if ( isset( $ecas['actions'] ) ) {
			rules_register_actions( $ecas['actions'] );
		}
	}
	
	/**
	 * Get ECA's for rules
	 * 
	 * @return	array
	 */
	public static function getRulesECAs()
	{
		$rulesPlugin = \MWP\Rules\Plugin::instance();
		$recordClass = get_called_class();
		$class_slug = str_replace( '\\', '_', strtolower( $recordClass ) );
		$pluginClass = $recordClass::_getPluginClass();
		$plugin = $pluginClass::instance();
		$plugin_meta = $plugin->getData( 'plugin-meta' );
		$group = isset( $plugin_meta['name'] ) ? $plugin_meta['name'] : $plugin->pluginSlug();
		$pieces = explode( '\\', $recordClass );
		$classvar = strtolower( array_pop( $pieces ) );
		
		$type_map = array(
			'varchar'    => 'string',
			'char'       => 'string',
			'text'       => 'string',
			'tinytext'   => 'string',
			'mediumtext' => 'string',
			'longtext'   => 'string',
			'tinyint'    => 'bool',
			'boolean'    => 'bool',
			'bit'        => 'bool',
			'smallint'   => 'int',
			'mediumint'  => 'int',
			'int'        => 'int',
			'bigint'     => 'int',
			'decimal'    => 'float',
			'float'      => 'float',
			'double'     => 'float',
			'year'       => 'string',
			'time'       => 'string',
			'date'       => 'string',
			'datetime'   => 'string',
			'timestamp'  => 'string',
			'enum'       => 'string',
			'set'        => 'string',
		);
		
		$widget_map = array(
			'varchar'    => 'text',
			'char'       => 'text',
			'text'       => 'textarea',
			'tinytext'   => 'text',
			'mediumtext' => 'textarea',
			'longtext'   => 'textarea',
			'tinyint'    => 'checkbox',
			'boolean'    => 'checkbox',
			'bit'        => 'checkbox',
			'smallint'   => 'text',
			'mediumint'  => 'text',
			'int'        => 'text',
			'bigint'     => 'text',
			'decimal'    => 'text',
			'float'      => 'text',
			'double'     => 'text',
			'year'       => 'text',
			'time'       => 'text',
			'date'       => 'text',
			'datetime'   => 'text',
			'timestamp'  => 'text',
			'enum'       => 'text',
			'set'        => 'text',
		);
		
		$ecas = array();
		
		$ecas['events']['created_updated'] = array( 'action', 'created_updated_' . $class_slug, array(
			'title' => $recordClass::$lang_singular . ' ' . __( 'has been created or updated' ),
			'description' => __( 'This event occurs after a' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'has either been created or updated.' ),
			'group' => $group,
			'arguments' => array(
				$classvar => array(
					'argtype' => 'object',
					'label' => $recordClass::$lang_singular,
					'class' => $recordClass,
					'description' => __( 'The' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'that was created or updated' ),
				),
				'is_new' => array(
					'argtype' => 'bool',
					'label' => __( 'Is New' ),
					'description' => __( 'Flag indicating if the record has been created new.' ),
				),
				'changed' => array(
					'argtype' => 'array',
					'label' => __( 'Changed' ),
					'description' => __( 'The properties which were changed' ),
				),
			),
		));
		
		$ecas['events']['created'] = array( 'action', 'created_' . $class_slug, array(
			'title' => $recordClass::$lang_singular . ' ' . __( 'has been created' ),
			'description' => __( 'This event occurs after a' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'has been created.' ),
			'group' => $group,
			'arguments' => array(
				$classvar => array(
					'argtype' => 'object',
					'label' => $recordClass::$lang_singular,
					'class' => $recordClass,
					'description' => __( 'The' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'that was created' ),
				),
				'changed' => array(
					'argtype' => 'array',
					'label' => __( 'Changed' ),
					'description' => __( 'The properties which were changed' ),
				),
			),
		));
		
		$ecas['events']['updated'] = array( 'action', 'updated_' . $class_slug, array(
			'title' => $recordClass::$lang_singular . ' ' . __( 'has been updated' ),
			'description' => __( 'This event occurs after a' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'has been updated.' ),
			'group' => $group,
			'arguments' => array(
				$classvar => array(
					'argtype' => 'object',
					'label' => $recordClass::$lang_singular,
					'class' => $recordClass,
					'description' => __( 'The' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'that was updated' ),
				),
				'changed' => array(
					'argtype' => 'array',
					'label' => __( 'Changed' ),
					'description' => __( 'The properties which were changed' ),
				),
			),
		));
		
		$ecas['events']['deleted'] = array( 'action', 'deleted_' . $class_slug, array(
			'title' => $recordClass::$lang_singular . ' ' . __( 'has been deleted' ),
			'description' => __( 'This event occurs after a' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'has been deleted.' ),
			'group' => $group,
			'arguments' => array(
				$classvar => array(
					'argtype' => 'object',
					'label' => $recordClass::$lang_singular,
					'class' => $recordClass,
					'description' => __( 'The' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'that was deleted' ),
				),
			),
		));
		
		$selection_arguments = array(
			$classvar => array(
				'label' => $recordClass::$lang_singular,
				'required' => true,
				'argtypes' => array(
					'object' => array( 'description' => __( 'The' ) . ' ' . $recordClass::$lang_singular . ' ' . __( 'object.' ), 'classes' => [ $recordClass ] ),
				),
			),
		);
		
		/* Lazy loadable arguments (with caching)... */
		$arguments = null;
		$get_arguments = function() use ( $recordClass, &$arguments, $type_map, $widget_map, $rulesPlugin ) 
		{
			if ( isset( $arguments ) ) {
				return $arguments;
			}
			
			$arguments = array();
			
			foreach( $recordClass::_getColumns() as $prop => $config ) 
			{
				if ( ! is_array( $config ) ) {
					$prop = $config;
					$config = [];
				}
				
				if ( $prop == $recordClass::_getKey() ) {
					continue;
				}
				
				$column_type = isset( $config['type'] ) ? $config['type'] : 'varchar';
				$argtype = isset( $config['argtype'] ) ? $config['argtype'] : ( isset( $type_map[ $column_type ] ) ? $type_map[ $column_type ] : 'string' );
				$label = isset( $config['label'] ) ? $config['label'] : ucwords( str_replace( '_', ' ', $prop ) );
				$class = isset( $config['class'] ) ? $config['class'] : NULL;
				$nullable = isset( $config['allow_null'] ) && $config['allow_null'] == false ? false : true;
				$keys = isset( $config['keys'] ) ? $config['keys'] : null;
				$widgettype = isset( $config['widget']['type'] ) ? $config['widget']['type'] : $widget_map[ $column_type ];
				$widgetoptions = array();
				
				$arguments[ $prop ] = array(
					'label' => $label,
					'required' => ( ( isset( $config['allow_null'] ) && $config['allow_null'] ) || isset( $config['default'] ) ) ? false : true,
					'argtypes' => array(
						$argtype => array( 'description' => __( 'Value to use for' ) . ' ' . $label, 'classes' => $class ? [ $class ] : NULL )
					),
					'configuration' => $rulesPlugin->configPreset( $widgettype, 'field_' . $prop, $widgetoptions ),
				);
			}
			
			return $arguments;
		};
		
		/* Lazy loadable 'Create Record' action */
		$ecas['actions']['create'] = array( 'create_' . $class_slug, function() use ( $recordClass, $get_arguments, $group ) 
		{	
			$arguments = call_user_func( $get_arguments );
			
			return array(
				'title' => __( 'Create a' ) . ' ' . $recordClass::$lang_singular,
				'description' => __( 'Create a new' ) . ' ' . $recordClass::$lang_singular,
				'group' => $group,
				'configuration' => array(),
				'arguments' => $arguments,
				'callback' => function() use ( $arguments, $recordClass ) {
					$args = func_get_args();
					$record = new $recordClass;
					foreach( $arguments as $prop => $arg ) {
						$value = array_shift( $args );
						if ( $value !== NULL ) {
							$record->$prop = $value;
						}
					}
					$result = $record->save();
					if ( ! is_wp_error( $result ) ) {
						return array( 'success' => true, 'message' => 'Record created.', 'data' => $record->dataArray() );
					}
					
					return array( 'success' => false, 'message' => 'Error creating record', 'errors' => $result->get_error_messages(), 'data' => $record->dataArray() );
				},
			);
		});
		
		/* Lazy loadable 'Update Record' action */
		$ecas['actions']['update'] = array( 'update_' . $class_slug, function() use ( $recordClass, $get_arguments, $class_slug, $group, $selection_arguments ) 
		{
			$arguments = call_user_func( $get_arguments );
			
			$form_slug = str_replace( '_', '-', $class_slug );

			return array(
				'title' => __( 'Update a' ) . ' ' . $recordClass::$lang_singular,
				'description' => __( 'Update an existing' ) . ' ' . $recordClass::$lang_singular,
				'group' => $group,
				'configuration' => array(
					'form' => function( $form, $values, $operation ) use ( $arguments, $form_slug ) {
						$choices = array_combine( array_column( $arguments, 'label' ), array_keys( $arguments ) );
						$toggles = array_combine( array_keys( $arguments ), array_map( function( $p ) use ( $form_slug ) { return array( 'show' => array( '#update-' . $form_slug . '_' . $p . '_form_wrapper' ) ); }, array_keys( $arguments ) ) );
						$form->addField( 'update_choices', 'choice', array(
							'label' => __( 'Choose fields to update', 'mwp-rules' ),
							'choices' => $choices,
							'multiple' => true,
							'expanded' => true,
							'required' => false,
							'toggles' => $toggles,
							'data' => isset( $values['update_choices'] ) ? $values['update_choices'] : array(),
						));
					},
				),
				'arguments' => array_merge( $selection_arguments, $arguments ),
				'callback' => function() use ( $arguments ) {
					$args = func_get_args();
					$arg_map = array();
					
					if ( $record = array_shift( $args ) ) {
						foreach( $arguments as $prop => $arg ) {
							$arg_map[ $prop ] = array_shift( $args );
						}
						
						$form_values = array_shift( $args );
						$update_choices = isset( $form_values['update_choices'] ) ? (array) $form_values['update_choices'] : array();
						
						foreach( $arg_map as $prop => $value ) {
							if ( in_array( $prop, $update_choices ) ) {
								$record->$prop = $value;
							}
						}
					
						$result = $record->save();
						if ( ! is_wp_error( $result ) ) {
							return array( 'success' => true, 'message' => 'Record updated.', 'data' => $record->dataArray() );
						}
						
						return array( 'success' => false, 'message' => 'Error updating record', 'errors' => $result->get_error_messages(), 'data' => $record->dataArray() );
					}
					
					return array( 'success' => false, 'message' => 'No record provided', 'record' => $record );
				},
			);
		});
		
		/* Lazy loadable 'Delete Record' action */
		$ecas['actions']['delete'] = array( 'delete_' . $class_slug, function() use ( $recordClass, $group, $selection_arguments ) 
		{
			return array(
				'title' => __( 'Delete a' ) . ' ' . $recordClass::$lang_singular,
				'description' => __( 'Delete an existing' ) . ' ' . $recordClass::$lang_singular,
				'group' => $group,
				'configuration' => array(),
				'arguments' => $selection_arguments,
				'callback' => function() {
					$args = func_get_args();
					
					if ( $record = array_shift( $args ) ) {
						$result = $record->delete();
						if ( ! is_wp_error( $result ) ) {
							return array( 'success' => true, 'message' => 'Record deleted.', 'data' => $record->dataArray() );
						}
						
						return array( 'success' => false, 'message' => 'Error deleting record', 'errors' => $result->get_error_messages(), 'data' => $record->dataArray() );
					}
					
					return array( 'success' => false, 'message' => 'No record provided', 'record' => $record );
				},
			);
		});

		return $ecas;
	}
	
	/**
	 * Save record
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		$recordClass = get_called_class();
		$class_slug = str_replace( '\\', '_', strtolower( $recordClass ) );
		$is_new = (bool) $this->id();
		$changed = $this->_getChanged();
		
		$result = parent::save();
		
		if ( ! is_wp_error( $result ) ) {
			$specific_type = $is_new ? 'created_' : 'updated_';
			do_action( 'created_updated_' . $class_slug, $this, $is_new, $changed );
			do_action( $specific_type . $class_slug, $this, $changed );
		}
		
		return $result;
	}
	
	/**
	 * Delete record
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		$recordClass = get_called_class();
		$class_slug = str_replace( '\\', '_', strtolower( $recordClass ) );
		
		$result = parent::delete();
		
		if ( ! is_wp_error( $result ) ) {
			do_action( 'deleted_' . $class_slug, $this );
		}
		
		return $result;
	}

}