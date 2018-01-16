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
		'current_site' => array(
			'argtype' => 'object',
			'class' => 'MWP\Rules\WP\Site',
			'label' => 'Current Site',
			'getter' => function() {
				return \MWP\Rules\WP\Site::instance();
			}
		),
		'current_user' => array(
			'argtype' => 'object',
			'class' => 'WP_User',
			'label' => 'Current Logged In User',
			'getter' => function() {
				return wp_get_current_user();
			}
		),
		'current_time' => array(
			'argtype' => 'object',
			'class' => 'DateTime',
			'label' => 'Current Time',
			'getter' => function() {
				return new \DateTime;
			}
		),
		'current_post' => array(
			'argtype' => 'object',
			'class' => 'WP_Post',
			'label' => 'Current Post',
			'nullable' => true,
			'getter' => function() {
				return get_post();
			}
		),
		'current_url' => array(
			'argtype' => 'object',
			'class' => 'MWP\Rules\WP\Url',
			'label' => 'Current URL',
			'getter' => function() {
				return new \MWP\Rules\WP\Url( add_query_arg() );
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
		'MWP\Rules\WP\Site' => array(
			'label' => 'Site',
			'mappings' => array(
				
			),
		),
		'MWP\Rules\WP\Url' => array(
			'label' => 'Url',
			'loader' => function( $val ) {
				return new \MWP\Rules\WP\Url( $val );
			},
			'mappings' => array(
				'link' => array(
					'argtype' => 'string',
					'label' => 'Full Url',
					'getter' => function( $url ) {
						return $url->url;
					}
				),
				'scheme' => array(
					'argtype' => 'string',
					'label' => 'Scheme',
					'getter' => function( $url ) {
						return $url->getComponent( 'scheme' );
					}
				),
				'host' => array(
					'argtype' => 'string',
					'label' => 'Host Domain',
					'getter' => function( $url ) {
						return $url->getComponent( 'host' );
					}
				),
				'port' => array(
					'argtype' => 'string',
					'label' => 'Port',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'port' );
					}
				),
				'user' => array(
					'argtype' => 'string',
					'label' => 'User',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'user' );
					}
				),
				'pass' => array(
					'argtype' => 'string',
					'label' => 'Scheme',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'scheme' );
					}
				),
				'path' => array(
					'argtype' => 'string',
					'label' => 'Path',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'path' );
					}
				),
				'query' => array(
					'argtype' => 'array',
					'label' => 'Query Args',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'query' );
					}
				),
				'fragment' => array(
					'argtype' => 'string',
					'label' => 'Fragment',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'fragment' );
					}
				)
			),
		),
		'DateTime' => array(
			'label' => 'Date/Time',
			'loader' => function( $val, $type, $key ) {
				if ( $type == 'int' ) {
					$date = new \DateTime;
					$date->setTimestamp( (int) $val );
					return $date;
				}
				
				return new \DateTime( $val );
			},
			'mappings' => array( 
				'timestamp' => array(
					'argtype' => 'int',
					'label' => 'Unix Timestamp',
					'getter' => function( $date ) {
						return $date->getTimestamp();
					}
				),
				'gmt_datetime' => array(
					'argtype' => 'string',
					'label' => 'GMT Date/Time',
					'getter' => function( $date ) {
						return date( 'F j, Y H:i:s', $date->getTimestamp() );
					}
				),
				'gmt_date' => array(
					'argtype' => 'string',
					'label' => 'GMT Date',
					'getter' => function( $date ) {
						return date( 'F j, Y', $date->getTimestamp() );
					}
				),
				'gmt_time' => array(
					'argtype' => 'string',
					'label' => 'GMT Time',
					'getter' => function( $date ) {
						return date( 'H:i:s', $date->getTimestamp() );
					}
				),
				'gmt_day' => array(
					'argtype' => 'int',
					'label' => 'GMT Day',
					'getter' => function( $date ) {
						return intval( date( 'd', $date->getTimestamp() ) );
					}
				),
				'gmt_month' => array(
					'argtype' => 'int',
					'label' => 'GMT Month',
					'getter' => function( $date ) {
						return intval( date( 'm', $date->getTimestamp() ) );
					}
				),
				'gmt_year' => array(
					'argtype' => 'int',
					'label' => 'GMT Year',
					'getter' => function( $date ) {
						return intval( date( 'Y', $date->getTimestamp() ) );
					}
				),
				'gmt_hour' => array(
					'argtype' => 'int',
					'label' => 'GMT Hour',
					'getter' => function( $date ) {
						return intval( date( 'H', $date->getTimestamp() ) );
					}				
				),
				'gmt_minute' => array(
					'argtype' => 'int',
					'label' => 'GMT Minute',
					'getter' => function( $date ) {
						return intval( date( 'i', $date->getTimestamp() ) );
					}				
				),
				'gmt_second' => array(
					'argtype' => 'int',
					'label' => 'GMT Second',
					'getter' => function( $date ) {
						return intval( date( 's', $date->getTimestamp() ) );
					}				
				),
				'local_datetime' => array(
					'argtype' => 'string',
					'label' => 'Local Date/Time',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'F j, Y H:i:s' );
					}
				),
				'local_date' => array(
					'argtype' => 'string',
					'label' => 'Local Date',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'F j, Y' );
					}
				),
				'local_time' => array(
					'argtype' => 'string',
					'label' => 'Local Time',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'H:i:s' );
					}
				),
				'local_day' => array(
					'argtype' => 'int',
					'label' => 'Local Day',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'd' );
					}
				),
				'local_month' => array(
					'argtype' => 'int',
					'label' => 'Local Month',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'm' );
					}
				),
				'local_year' => array(
					'argtype' => 'int',
					'label' => 'Local Year',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'Y' );
					}
				),
				'local_hour' => array(
					'argtype' => 'int',
					'label' => 'Local Hour',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'H' );
					}				
				),
				'local_minute' => array(
					'argtype' => 'int',
					'label' => 'Local Minute',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 'i' );
					}				
				),
				'local_second' => array(
					'argtype' => 'int',
					'label' => 'Local Second',
					'getter' => function( $date ) {
						return get_date_from_gmt( date( 'Y-m-d H:i:s', $date->getTimestamp() ), 's' );
					}				
				),
			),
		),
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
				'website' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Website',
					'nullable' => true,
					'getter' => function( $user ) {
						return $user->user_url ?: null;
					}
				),
				'url' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Author Posts Url',
					'getter' => function( $user ) {
						return get_author_posts_url( $user->ID );
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
				'meta' => array(
					'argtype' => 'array',
					'label' => 'Meta Data',
					'getter' => function( $user ) {
						return get_user_meta( $user->ID );
					},
					'key_getter' => function( $user, $meta_key ) {
						return get_user_meta( $user->ID, $meta_key, true );
					},
					'mappings' => array(
						'last_update' => array(
							'argtype' => 'int',
							'class' => 'DateTime',
							'label' => 'Last Update',
						),
					),
				),
				'last_post' => array(
					'argtype' => 'object',
					'class' => 'WP_Post',
					'label' => 'Latest Post',
					'nullable' => true,
					'getter' => function( $user ) {
						$posts = get_posts( array(
							'numberposts' => 1, 
							'orderby' => 'post_date',
							'order' => 'DESC',
							'author' => $user->ID,
						));
						if ( $posts ) {
							return $posts[0];
						}
					}
				)
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
				'url' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Url',
					'getter' => function( $post ) {
						return get_permalink( $post );
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
						return apply_filters( 'the_excerpt', $post->post_excerpt );
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
				'meta' => array(
					'argtype' => 'array',
					'label' => 'Meta Data',
					'getter' => function( $post ) {
						return get_post_meta( $post->ID );
					},
					'key_getter' => function( $post, $meta_key ) {
						return get_post_meta( $post->ID, $meta_key, true );
					},
				),
				'taxonomies' => array(
					'argtype' => 'array',
					'label' => 'Taxonomies',
					'class' => 'WP_Taxonomy',
					'getter' => function( $post ) {
						return array_map( function( $name ) { return get_taxonomy( $name ); }, get_post_taxonomies( $post ) );
					},
					'key_getter' => function( $post, $taxonomy_name ) {
						foreach( get_post_taxonomies( $post ) as $taxonomy ) {
							if ( $taxonomy == $taxonomy_name ) {
								return get_taxonomy( $taxonomy_name );
							}
						}
					},
				),
				'terms' => array(
					'argtype' => 'array',
					'class' => 'WP_Term',
					'label' => 'Taxonomy Terms',
					'getter' => function( $post ) {
						return wp_get_post_terms( $post->ID, get_post_taxonomies( $post ) );
					}
				),
			),
		),
		'WP_Comment' => array(
			'label' => 'Comment',
			'loader' => function( $comment_id ) {
				return get_comment( $comment_id );
			},
			'mappings' => array(
				'id' => array(
					'argtype' => 'int',
					'label' => 'Comment ID',
					'getter' => function( $comment ) {
						return $comment->post_ID;
					}
				),
				'post' => array(
					'argtype' => 'object',
					'class' => 'WP_Post',
					'label' => 'Parent Post',
					'getter' => function( $comment ) {
						return get_post( $comment->comment_post_ID );
					}
				),
				'author' => array(
					'argtype' => 'object',
					'label' => 'Author',
					'class' => 'WP_User',
					'getter' => function( $comment ) {
						return get_user_by( 'id', $comment->user_id );
					}
				),
				'url' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Url',
					'getter' => function( $comment ) {
						return get_comment_link( $comment );
					}
				),
				'created' => array(
					'argtype' => 'object',
					'class' => 'DateTime',
					'label' => 'Created Date',
					'getter' => function( $comment ) {
						return new \DateTime( $comment->comment_date_gmt );
					}
				),
				'content' => array(
					'argtype' => 'string',
					'label' => 'Comment Content',
					'getter' => function( $comment ) {
						return $comment->comment_content;
					}
				),
				'parent' => array(
					'argtype' => 'object',
					'class' => 'WP_Comment',
					'label' => 'Parent Comment',
					'nullable' => true,
					'getter' => function( $comment ) {
						return get_comment( $comment->comment_parent ) ?: null;
					}
				),
				'children' => array(
					'argtype' => 'array',
					'class' => 'WP_Comment',
					'label' => 'Children Comments',
					'getter' => function( $comment ) {
						return $comment->get_children();
					}
				),
				'meta' => array(
					'argtype' => 'array',
					'label' => 'Meta Data',
					'getter' => function( $comment ) {
						return get_comment_meta( $comment->comment_ID );
					},
					'key_getter' => function( $comment, $meta_key ) {
						return get_comment_meta( $comment->comment_ID, $meta_key, true );
					},
				),
			),
		),
		'WP_Taxonomy' => array(
			'label' => 'Taxonomy',
			'loader' => function( $val, $type, $key ) {
				return get_taxonomy( $val );
			},
			'mappings' => array(
				'name' => array(
					'argtype' => 'string',
					'label' => 'Name',
					'getter' => function( $taxonomy ) {
						return $taxonomy->name;
					}
				),
				'label' => array(
					'argtype' => 'string',
					'label' => 'Label',
					'getter' => function( $taxonomy ) {
						return $taxonomy->label;
					}
				),
				'labels' => array(
					'argtype' => 'array',
					'label' => 'Labels',
					'getter' => function( $taxonomy ) {
						return (array) $taxonomy->labels;
					}
				),
				'description' => array(
					'argtype' => 'string',
					'label' => 'Description',
					'getter' => function( $taxonomy ) {
						return $taxonomy->description;
					}				
				),
				'public' => array(
					'argtype' => 'bool',
					'label' => 'Public Use',
					'getter' => function( $taxonomy ) {
						return $taxonomy->public;
					}				
				),
				'capabilities' => array(
					'argtype' => 'array',
					'label' => 'Capabilities',
					'getter' => function( $taxonomy ) {
						return $taxonomy->cap;
					}
				),
				'terms' => array(
					'argtype' => 'array',
					'class' => 'WP_Term',
					'label' => 'Terms',
					'getter' => function( $taxonomy ) {
						return get_terms( $taxonomy->name );
					},
					'key_getter' => function( $taxonomy, $term_key ) {
						return get_term_by( 'slug', $term_key, $taxonomy->name ) ?: null;
					},
				),
			),
		),
		'WP_Term' => array(
			'label' => 'Taxonomy Term',
			'loader' => function( $val, $type, $key ) {
				return get_term( $val );
			},
			'mappings' => array(
				'id' => array(
					'argtype' => 'int',
					'label' => 'Term ID',
					'getter' => function( $term ) {
						return $term->term_id;
					}
				),
				'name' => array(
					'argtype' => 'string',
					'label' => 'Name',
					'getter' => function( $term ) {
						return $term->name;
					}
				),
				'description' => array(
					'argtype' => 'string',
					'label' => 'Description',
					'getter' => function( $term ) {
						return $term->description;
					}
				),
				'slug' => array(
					'argtype' => 'string',
					'label' => 'Term ID',
					'getter' => function( $term ) {
						return $term->slug;
					}
				),
				'url' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Url',
					'getter' => function( $term ) {
						return get_term_link( $term );
					}
				),
				'group' => array(
					'argtype' => 'string',
					'label' => 'Group',
					'getter' => function( $term ) {
						return $term->term_group;
					}
				),
				'taxonomy' => array(
					'argtype' => 'object',
					'class' => 'WP_Taxonomy',
					'label' => 'Taxonomy',
					'getter' => function( $term ) {
						return get_taxonomy( $term->taxonomy );
					}
				),
				'parent' => array(
					'argtype' => 'object',
					'class' => 'WP_Term',
					'label' => 'Parent Term',
					'nullable' => true,
					'getter' => function( $term ) {
						return get_term( $term->parent ) ?: null;
					}
				),
				'count' => array(
					'argtype' => 'int',
					'label' => 'Object Count',
					'getter' => function( $term ) {
						return $term->count;
					}
				),
				'filter' => array(
					'argtype' => 'string',
					'label' => 'Filter',
					'getter' => function( $term ) {
						return $term->filter;
					}
				),
				'meta' => array(
					'argtype' => 'array',
					'label' => 'Meta Data',
					'getter' => function( $term ) {
						return get_term_meta( $term->term_id );
					},
					'key_getter' => function( $term, $meta_key ) {
						return get_term_meta( $term->term_id, $meta_key, true );
					},
				),
			),
		),
		
	));
});