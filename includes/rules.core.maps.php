<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules\WP;

/**
 * Core Global Arguments
 */
add_filter( 'rules_global_arguments', function( $globals ) 
{
	return array_replace_recursive( $globals, array(
		'current_site' => array(
			'argtype' => 'object',
			'class' => 'MWP\Rules\WP\Site',
			'label' => 'Current Site',
			'getter' => function() {
				return WP\Site::instance();
			}
		),
		'current_user' => array(
			'argtype' => 'object',
			'class' => 'WP_User',
			'label' => 'Current Site User',
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
				return new WP\Url( add_query_arg( [] ) );
			}
		),
	));
});

/**
 * Core Class Map
 */
add_filter( 'rules_class_map', function( $map ) 
{
	return array_replace_recursive( $map, array(
		'stdClass' => array(
			'label' => 'Plain Object',
			'loader' => function( $val ) {
				if ( is_array( $val ) ) {
					return (object) $val;
				}
			},
			'reference' => function( $object ) {
				return [ (array) $object ];
			},
		),
		'MWP\Rules\WP\Site' => array(
			'label' => 'Site',
			'mappings' => array(
				'name' => array(
					'argtype' => 'string',
					'label' => 'Name',
					'getter' => function( $site ) {
						return $site->name;
					}
				),
				'description' => array(
					'argtype' => 'string',
					'label' => 'Description',
					'getter' => function( $site ) {
						return $site->description;
					}
				),
				'wpurl' => array(
					'argtype' => 'object',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'WordPress Url',
					'getter' => function( $site ) {
						return new WP\Url( $site->wpurl );
					}
				),
				'url' => array(
					'argtype' => 'object',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Site Url',
					'getter' => function( $site ) {
						return new WP\Url( $site->url );
					}
				),
				'admin_email' => array(
					'argtype' => 'string',
					'label' => 'Admin Email',
					'getter' => function( $site ) {
						return $site->admin_email;
					}
				),
				'charset' => array(
					'argtype' => 'string',
					'label' => 'Character Encoding',
					'getter' => function( $site ) {
						return $site->charset;
					}
				),
				'text_direction' => array(
					'argtype' => 'string',
					'label' => 'Text Direction',
					'getter' => function( $site ) {
						return $site->text_direction;
					}
				),
				'language' => array(
					'argtype' => 'string',
					'label' => 'Language Code',
					'getter' => function( $site ) {
						return $site->language;
					}
				),
				'stylesheet_url' => array(
					'argtype' => 'string',
					'label' => 'Active Theme URL',
					'getter' => function( $site ) {
						return $site->stylesheet_url;
					}
				),
				'stylesheet_directory' => array(
					'argtype' => 'string',
					'label' => 'Active Theme Path',
					'getter' => function( $site ) {
						return $site->stylesheet_directory;
					}
				),
				'template_url' => array(
					'argtype' => 'string',
					'label' => 'Base Theme URL',
					'getter' => function( $site ) {
						return $site->template_url;
					}
				),
				'template_directory' => array(
					'argtype' => 'string',
					'label' => 'Base Theme Path',
					'getter' => function( $site ) {
						return $site->template_directory;
					}
				),
			),
		),
		'MWP\Rules\WP\Url' => array(
			'label' => 'Url',
			'loader' => function( $val ) {
				return new \MWP\Rules\WP\Url( $val );
			},
			'reference' => function( $url ) {
				return $url->url;
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
					},
					'keys' => array(
						'associative' => true,
						'default' => array(
							'argtype' => 'string',
						),
					),
				),
				'fragment' => array(
					'argtype' => 'string',
					'label' => 'Fragment',
					'nullable' => true,
					'getter' => function( $url ) {
						return $url->getComponent( 'fragment' );
					}
				),
			),
		),
		'DateTime' => array(
			'label' => 'Date/Time',
			'loader' => function( $val, $type, $key ) {
				if ( isset( $val ) ) {
					if ( $type == 'int' ) {
						$date = new \DateTime;
						$date->setTimestamp( (int) $val );
						return $date;
					}
					
					return new \DateTime( $val );
				}
			},
			'reference' => function( $date ) {
				return $date->getTimestamp();
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
			'reference' => function( $user ) {
				return (int) $user->ID;
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
				'display_name' => array(
					'argtype' => 'string',
					'label' => 'Display Name',
					'getter' => function( $user ) {
						return $user->display_name;
					}
				),
				'nicename' => array(
					'argtype' => 'string',
					'label' => 'User Name Slug',
					'getter' => function( $user ) {
						return $user->user_nicename;
					},
				),
				'email' => array(
					'argtype' => 'string',
					'label' => 'Email',
					'getter' => function( $user ) {
						return $user->user_email;
					},
				),
				'website_url' => array(
					'argtype' => 'string',
					'class' => 'MWP\Rules\WP\Url',
					'label' => 'Website',
					'nullable' => true,
					'getter' => function( $user ) {
						return $user->user_url ?: null;
					}
				),
				'posts_url' => array(
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
					},
					'keys' => array(
						'associative' => true,
						'default' => array( 'argtype' => 'bool', 'label' => 'Capability' ),
					),
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
						$meta = array();
						foreach( array_keys( get_user_meta( $user->ID ) ) as $meta_key ) {
							$meta[ $meta_key ] = get_user_meta( $user->ID, $meta_key, true );
						}
						return $meta;
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $user, $meta_key ) {
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
				),
				'last_post' => array(
					'argtype' => 'object',
					'class' => 'WP_Post',
					'label' => 'Latest Post',
					'nullable' => true,
					'getter' => function( $user ) {
						$posts = get_posts( array(
							'numberposts' => 1, 
							'orderby' => 'date',
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
			'reference' => function( $post ) {
				return $post->ID;
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
					'label' => 'Post Url',
					'getter' => function( $post ) {
						return get_permalink( $post );
					}
				),
				'type' => array(
					'label' => 'Post Type',
					'argtype' => 'string',
					'class' => 'WP_Post_Type',
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
					'label' => 'Comment Status',
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
						$meta = array();
						foreach( array_keys( get_post_meta( $post->ID ) ) as $meta_key ) {
							$meta[ $meta_key ] = get_post_meta( $post->ID, $meta_key, true );
						}
						return $meta;
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $post, $meta_key ) {
							return get_post_meta( $post->ID, $meta_key, true );
						},
					),
				),
				'comments' => array(
					'argtype' => 'array',
					'class' => 'WP_Comment',
					'label' => 'Comments',
					'getter' => function( $post ) {
						return get_comments( array( 'post_id' => $post->ID ) );
					},
				),
				'taxonomies' => array(
					'argtype' => 'array',
					'label' => 'Taxonomies',
					'class' => 'WP_Taxonomy',
					'getter' => function( $post ) {
						return array_map( function( $name ) { return get_taxonomy( $name ); }, get_post_taxonomies( $post ) );
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $post, $taxonomy_name ) {
							foreach( get_post_taxonomies( $post ) as $taxonomy ) {
								if ( $taxonomy == $taxonomy_name ) {
									return get_taxonomy( $taxonomy_name );
								}
							}
						},
					),
				),
				'terms' => array(
					'argtype' => 'array',
					'class' => 'WP_Term',
					'label' => 'Taxonomy Terms',
					'getter' => function( $post ) {
						return wp_get_post_terms( $post->ID, get_post_taxonomies( $post ) );
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $post, $term_key ) {
							$terms = array();
							foreach( get_post_taxonomies( $post ) as $taxonomy_name ) {
								if ( $_term = get_term_by( 'slug', $term_key, $taxonomy_name ) ) {
									$terms[] = $_term;
								}
							}
							return $terms;
						},
						'default' => array(
							'argtype' => 'array',
							'class' => 'WP_Term',
							'label' => 'Terms',
						),
					),
				),
			),
		),
		'WP_Post_Type' => array(
			'label' => 'Post Type',
			'loader' => function( $name ) {
				$post_types = get_post_types( array( 'name' => $name ), 'objects' );
				if ( ! empty( $post_types ) ) {
					return $post_types[$name];
				}
				return null;
			},
			'reference' => function( $post_type ) {
				return $post_type->name;
			},
			'mappings' => array(
				'name' => array(
					'label' => 'Name',
					'argtype' => 'string',
					'getter' => function( $post_type ) {
						return $post_type->name;
					}
				),
				'label' => array(
					'label' => 'Label',
					'argtype' => 'string',
					'getter' => function( $post_type ) {
						return $post_type->label;
					}
				),
				'labels' => array(
					'argtype' => 'array',
					'label' => 'Labels',
					'getter' => function( $post_type ) {
						return (array) $post_type->labels;
					},
					'keys' => array(
						'associative' => true,
						'default' => array( 'label' => 'Label', 'argtype' => 'string' ),
					),
				),
				'description' => array( 
					'label' => 'Description',
					'argtype' => 'string',
					'getter' => function( $post_type ) {
						return $post_type->description;
					}
				),
				'public' => array(
					'label' => 'Is Public',
					'argtype' => 'bool',
					'getter' => function( $post_type ) {
						return (bool) $post_type->public;
					}
				),
				'hierarchical' => array(
					'label' => 'Is Hierarchical',
					'argtype' => 'bool',
					'getter' => function( $post_type ) {
						return (bool) $post_type->hierarchical;
					}
				),
				'menu_icon' => array(
					'label' => 'Menu Icon',
					'argtype' => 'string',
					'getter' => function( $post_type ) {
						return $post_type->menu_icon;
					}
				),
				'capabilities' => array(
					'label' => 'Mapped Capabilities',
					'argtype' => 'array',
					'getter' => function( $post_type ) {
						return (array) $post_type->cap;
					}
				),
			),
		),
		'WP_Comment' => array(
			'label' => 'Comment',
			'loader' => function( $comment_id ) {
				return get_comment( $comment_id );
			},
			'reference' => function( $comment ) {
				return $comment->comment_ID;
			},
			'mappings' => array(
				'id' => array(
					'argtype' => 'int',
					'label' => 'Comment ID',
					'getter' => function( $comment ) {
						return $comment->comment_ID;
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
				'type' => array(
					'argtype' => 'string',
					'label' => 'Comment Type',
					'getter' => function( $comment ) {
						return $comment->comment_type ?: 'comment';
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
						return apply_filters( 'get_comment_text', $comment->comment_content, $comment, array() );
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
						return array_values( $comment->get_children() );
					}
				),
				'meta' => array(
					'argtype' => 'array',
					'label' => 'Meta Data',
					'getter' => function( $comment ) {
						$meta = array();
						foreach( array_keys( get_comment_meta( $comment->comment_ID ) ) as $meta_key ) {
							$meta[ $meta_key ] = get_comment_meta( $comment->comment_ID, $meta_key, true );
						}
						return $meta;
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $comment, $meta_key ) {
							return get_comment_meta( $comment->comment_ID, $meta_key, true );
						},
					),
				),
			),
		),
		'WP_Taxonomy' => array(
			'label' => 'Taxonomy',
			'loader' => function( $val, $type, $key ) {
				return get_taxonomy( $val );
			},
			'reference' => function( $taxonomy ) {
				return $taxonomy->name;
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
					},
					'keys' => array(
						'associative' => true,
						'default' => array( 'label' => 'Label', 'argtype' => 'string' ),
					),
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
						return (array) $taxonomy->cap;
					},
					'keys' => array(
						'associative' => true,
					),
				),
				'terms' => array(
					'argtype' => 'array',
					'class' => 'WP_Term',
					'label' => 'Terms',
					'getter' => function( $taxonomy ) {
						return get_terms( $taxonomy->name );
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $taxonomy, $term_key ) {
							return get_term_by( 'slug', $term_key, $taxonomy->name ) ?: null;
						},
					),
				),
			),
		),
		'WP_Term' => array(
			'label' => 'Taxonomy Term',
			'loader' => function( $val, $type, $key ) {
				$term = get_term( $val );
				if ( ! is_wp_error( $term ) ) {
					return $term;
				}
			},
			'reference' => function( $term ) {
				return $term->term_id;
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
						$term = get_term( $term->parent );
						if ( ! is_wp_error( $term ) ) {
							return $term;
						}
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
						$meta = array();
						foreach( array_keys( get_term_meta( $term->term_id ) ) as $meta_key ) {
							$meta[ $meta_key ] = get_term_meta( $term->term_id, $meta_key, true );
						}
						return $meta;
					},
					'keys' => array(
						'associative' => true,
						'getter' => function( $term, $meta_key ) {
							return get_term_meta( $term->term_id, $meta_key, true );
						},
					),
				),
			),
		),
		
	));
});