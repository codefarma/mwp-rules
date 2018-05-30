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
			'loader' => function( $val ) use ( $object_class ) {
				try {
					return $object_class::load( $val );
				} catch( \OutOfRangeException $e ) { 
					return NULL;
				}
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
			
			$column_type = isset( $config['type'] ) ? $config['type'] : 'varchar';
			$type = isset( $type_map[ $column_type ] ) ? $type_map[ $column_type ] : 'string';
			$label = ucwords( str_replace( '_', ' ', $prop ) );
			$class = NULL;
			$nullable = isset( $config['allow_null'] ) && $config['allow_null'] == false ? false : true;
			
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
			);
			
			$object_map['mappings'][$prop] = $mapping;
		}
		
		$map = array_replace_recursive( $map, array( $object_class => $object_map ), $augmentations );
		
		return $map;
	}

}