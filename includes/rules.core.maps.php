<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Core Global Arguments
 */
add_filter( 'rules_global_arguments', function( $globals ) 
{
	return array_merge_recursive( $globals, array(
		'current_user_id' => array(
			'label' => __( 'Current logged in user ID', 'mwp-rules' ),
			'argtype' => 'int',
			'class' => 'WP_User',
			'getter' => function() {
				return get_current_user_id();
			}
		),
	));
});

/**
 * Core Class Map
 */
add_filter( 'rules_class_map', function( $map ) 
{
	return array_merge_recursive( $map, array(
		'WP_User' => array(
			'label' => 'User',
			'loader' => function( $val, $type, $key ) {
				switch( $type ) {
					case 'int': return get_user_by('id', $val);
					case 'string': return get_user_by($key, $val);
				}
			},
			'mappings' => array(
				'id' => array(
					'argtype' => 'int',
					'label' => 'User ID',
					'getter' => function( $user ) {
						return $user->ID;
					},
				),
				'login' => array(
					'argtype' => 'string',
					'label' => 'Login Name',
					'getter' => function( $user ) {
						return $user->user_login;
					},
				),
				'first_name' => array(
					'argtype' => 'string',
					'label' => 'First Name',
					'getter' => function( $user ) {
						return $user->first_name;
					},
				),
				'last_name' => array(
					'argtype' => 'string',
					'label' => 'Last Name',
					'getter' => function( $user ) {
						return $user->last_name;
					},
				),
				'nicename' => array(
					'argtype' => 'string',
					'label' => 'Nice Name',
					'getter' => function( $user ) {
						return $user->nicename;
					},
				),
				'email' => array(
					'argtype' => 'string',
					'label' => 'Email',
					'getter' => function( $user ) {
						return $user->user_email;
					},
				),
				'url' => array(
					'argtype' => 'string',
					'label' => 'Url',
					'nullable' => true,
					'getter' => function( $user ) {
						return $user->user_url ?: NULL;
					}
				),
				'registered' => array(
					'argtype' => 'object',
					'label' => 'Registration Date',
					'class' => 'DateTime',
					'getter' => function( $user ) {
						try {
							return new \DateTime( $user->user_registered );
						} catch( \Exception $e ) { 
							return new \DateTime();
						}
					}
				),
				'capabilities' => array(
					'argtype' => 'array',
					'label' => 'Capabilities',
					'getter' => function( $user ) {
						return $user->allcaps;
					}
				),
				'roles' => array(
					'argtype' => 'array',
					'label' => 'Roles',
					'getter' => function( $user ) {
						return $user->roles;
					}
				),
			),
		),
		'WP_Post' => array(
			'label' => 'Post',
			'loader' => function( $val ) {
				return get_post( $val );
			},
			'mappings' => array(
				'id' => array(
					'argtype' => 'int',
					'label' => 'Post ID',
					'getter' => function( $post ) {
						return $post->ID;
					}
				),
				'author' => array(
					'argtype' => 'object',
					'label' => 'Author',
					'class' => 'WP_User',
					'getter' => function( $post ) {
						return get_user_by( 'id', $post->post_author );
					}
				),
				'slug' => array(
					'argtype' => 'string',
					'label' => 'Post Slug',
					'getter' => function( $post ) {
						return $post->post_name;
					}
				),
				'type' => array(
					'argtype' => 'string',
					'label' => 'Post Type',
					'getter' => function( $post ) {
						return $post->post_type;
					}
				),
				'title' => array(
					'argtype' => 'string',
					'label' => 'Post Title',
					'getter' => function( $post ) {
						return $post->post_title;
					}
				),
				'created' => array(
					'argtype' => 'object',
					'class' => 'DateTime',
					'label' => 'Created Date',
					'getter' => function( $post ) {
						return new \DateTime( $post->post_date_gmt );
					}
				),
				'modified' => array(
					'argtype' => 'object',
					'class' => 'DateTime',
					'label' => 'Modified Date',
					'getter' => function( $post ) {
						return new \DateTime( $post->post_modified_gmt );
					}
				),
				'content' => array(
					'argtype' => 'string',
					'label' => 'Post Content',
					'getter' => function( $post ) {
						return apply_filters( 'the_content', $post->post_content );
					}
				),
				'excerpt' => array(
					'argtype' => 'string',
					'label' => 'Post Excerpt',
					'getter' => function( $post ) {
						return $post->post_excerpt;
					}
				),
				'status' => array(
					'argtype' => 'string',
					'label' => 'Post Status',
					'getter' => function( $post ) {
						return $post->post_status;
					}
				),
				'comment_status' => array(
					'argtype' => 'string',
					'label' => 'Comments Status',
					'getter' => function( $post ) {
						return $post->comment_status;
					}
				),
				'ping_status' => array(
					'argtype' => 'string',
					'label' => 'Ping Status',
					'getter' => function( $post ) {
						return $post->ping_status;
					}
				),
				'password' => array(
					'argtype' => 'string',
					'label' => 'Post Password',
					'getter' => function( $post ) {
						return $post->post_password;
					}
				),
				'parent' => array(
					'argtype' => 'object',
					'label' => 'Parent Post',
					'class' => 'WP_Post',
					'nullable' => true,
					'getter' => function( $post ) {
						return get_post( $post->post_parent ) ?: null;
					}
				),
			),
		),
	));
});