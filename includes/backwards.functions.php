<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Add WP_Taxonomy to WP before 4.7.0
 */
if ( ! class_exists( 'WP_Taxonomy' ) ) {
	class WP_Taxonomy {}
	add_action( 'registered_taxonomy', function( $taxonomy ) {
		global $wp_taxonomies;
		$registered_taxonomy = $wp_taxonomies[ $taxonomy ];
		$upgraded_taxonomy = new \WP_Taxonomy;
		foreach( (array) $registered_taxonomy as $prop => $val ) {
			$upgraded_taxonomy->$prop = $val;
		}
		$wp_taxonomies[ $taxonomy ] = $upgraded_taxonomy;
	}, 
	99 );
}