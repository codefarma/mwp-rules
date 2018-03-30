<?php

namespace MWP\Rules\Extensions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

class MWPFrameworkHelpersFormSymfonyForm extends _MWPFrameworkHelpersFormSymfonyForm
{
	/**
	 * Prepare a field to be added to the form
	 *
	 * @param	string			$name				The field name
	 * @param	string			$type				The field type (registered shorthand or a class name)
	 * @param	array			$options			The field options
	 * @return	array 								The prepared field associative array
	 */
	public static function prepareField( $name, $type, $options )
	{	
		$field = parent::prepareField( $name, $type, $options );
		
		/* Special types */
		if ( $field['type'] == 'codemirror' ) {
			$field['type'] = 'textarea';
			$field['options']['row_attr'] = ( isset( $field['options']['row_attr'] ) ? $field['options']['row_attr'] : array() ) + array( 'data-view-model' => 'mwp-rules' );
			$field['options']['attr'] = ( isset( $field['options']['attr'] ) ? $field['options']['attr'] : array() ) + array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' );
		}

		return $field;
	}
}