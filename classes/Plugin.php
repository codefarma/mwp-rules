<?php
/**
 * Plugin Class File
 *
 * @vendor: Code Farma
 * @package: MWP Rules
 * @author: Kevin Carwile
 * @link: http://www.codefarma.com
 * @since: December 4, 2017
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

const ACTION_STANDARD = 0;
const ACTION_ELSE = 1;

use MWP\Framework\Framework;
use MWP\Framework\Task;
use MWP\Rules\ECA\Loader;
use MWP\Rules\ECA\Token;

use MWP\Rules\Log as RuleLog;

/**
 * Plugin Class
 */
class _Plugin extends \MWP\Framework\Plugin
{
	/**
	 * Instance Cache - Required
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string		Plugin Name
	 */
	public $name = 'MWP Rules';
	
	/**
	 * @var	array
	 */
	protected $events = array();
		
	/**
	 * @var	array
	 */
	protected $conditions = array();
	
	/**
	 * @var	array
	 */
	protected $actions = array();
	
	/**
	 * @var	bool
	 */
	public $shuttingDown = FALSE;
	
	/**
	 * @var	array
	 */
	public $actionQueue = array();
	
	/**
	 * @MWP\WordPress\Script( deps={"mwp"} )
	 */
	public $mainController = 'assets/js/main.js';
	
	/**
	 * @MWP\WordPress\Script( deps={"jquery-ui-sortable"} )
	 */
	public $nestedSortable = 'assets/js/jquery.mjs.nestedSortable.js';
	
	/**
	 * @MWP\WordPress\Script( deps={"jquery"} )
	 */
	public $selectizeJS = 'assets/js/selectize/js/selectize.js';
	
	/**
	 * @MWP\WordPress\Script( handle="codemirror" )
	 */
	public $codeMirror = 'assets/js/codemirror/codemirror.js';
	
	/**
	 * @MWP\WordPress\Script( handle="codemirror-xml" )
	 */
	public $codeMirrorXML = 'assets/js/codemirror/mode/xml/xml.js';

	/**
	 * @MWP\WordPress\Script( handle="codemirror-css" )
	 */
	public $codeMirrorCSS = 'assets/js/codemirror/mode/css/css.js';

	/**
	 * @MWP\WordPress\Script( handle="codemirror-javascript" )
	 */
	public $codeMirrorJS = 'assets/js/codemirror/mode/javascript/javascript.js';

	/**
	 * @MWP\WordPress\Script( handle="codemirror-clike" )
	 */
	public $codeMirrorCLIKE = 'assets/js/codemirror/mode/clike/clike.js';

	/**
	 * @MWP\WordPress\Script( handle="codemirror-htmlmixed", deps={"codemirror-xml","codemirror-javascript","codemirror-css"} )
	 */
	public $codeMirrorHTML = 'assets/js/codemirror/mode/htmlmixed/htmlmixed.js';
	
	/**
	 * @MWP\WordPress\Script( handle="codemirror-php", deps={"codemirror","codemirror-htmlmixed","codemirror-clike"} )
	 */
	public $codeMirrorPHP = 'assets/js/codemirror/mode/php/php.js';
	
	/**
	 * @MWP\WordPress\Stylesheet
	 */
	public $selectizeCSS = 'assets/js/selectize/css/selectize.bootstrap3.css';
	
	/**
	 * @MWP\WordPress\Script( handle="jstree", deps={"jquery"} )
	 */
	public $jsTreeJS = 'assets/js/jstree/jstree.min.js';
	
	/**
	 * @MWP\WordPress\Stylesheet
	 */
	public $jsTreeCSS = 'assets/js/jstree/themes/default/style.min.css';
	
	/**
	 * @MWP\WordPress\Stylesheet
	 */
	public $adminStyle = 'assets/css/admin_style.css';
	
	/**
	 * @MWP\WordPress\Stylesheet
	 */
	public $codeMirrorStyle = 'assets/css/codemirror.css';
	
	/**
	 * Enqueue scripts and stylesheets
	 * 
	 * @return	void
	 */
	public function enqueueScripts()
	{
		$plugin = $this;
		
		add_action( 'admin_enqueue_scripts', function() use ( $plugin ) {
			$plugin->useScript( $plugin->mainController, array(
				'templates' => [
					'token_browser' => $this->getTemplateContent( 'dialogs/token-browser' ),
				],
				'types' => [
					'string' => [
						'icon' => $this->fileUrl('assets/img/jstree/string.png'),
					],
					'float' => [
						'icon' => $this->fileUrl('assets/img/jstree/number.png'),
					],
					'int' => [
						'icon' => $this->fileUrl('assets/img/jstree/number.png'),
					],
					'bool' => [
						'icon' => $this->fileUrl('assets/img/jstree/boolean.png'),
					],
					'array' => [
						'icon' => $this->fileUrl('assets/img/jstree/array.png'),
					],
					'object' => [
						'icon' => $this->fileUrl('assets/img/jstree/object.png'),
					],
					'mixed' => [
						'icon' => $this->fileUrl('assets/img/jstree/mixed.png'),
					],
				],
			));
			$plugin->useScript( $plugin->nestedSortable );
			$plugin->useScript( $plugin->codeMirror );
			$plugin->useStyle( $plugin->codeMirrorStyle );
			$plugin->useScript( $plugin->codeMirrorPHP );
			$plugin->useStyle( $plugin->adminStyle );
			$plugin->useScript( $plugin->selectizeJS );
			$plugin->useStyle( $plugin->selectizeCSS );	
			$plugin->useScript( $plugin->jsTreeJS );
			$plugin->useStyle( $plugin->jsTreeCSS );
		});
	}
	
	/**
	 * Give plugins a common hook to register ECA's
	 *
	 * @MWP\WordPress\Action( for="mwp_framework_init", priority=99 )
	 *
	 * @return	void
	 */
	public function whenPluginsLoaded()
	{
		/* Get cached information about the plugins/expansions providing ECA's */
		$stored_providers = $this->getECAProviders();
		
		/* Include ECA expansion packs */
		$expansion_dir = $this->getPath() . '/expansions';
		if ( is_dir( $expansion_dir ) ) {
			foreach( glob( $expansion_dir . '/*', GLOB_ONLYDIR ) as $expansion_path ) {
				if ( file_exists( $expansion_path . '/init.php' ) ) {
					include_once $expansion_path . '/init.php';
				}
			}
		}
		
		/* Allow plugins to register their own ECA's */
		do_action( 'rules_register_ecas' );
		
		/* Load custom defined hooks... */
		$custom_hooks = $this->getCustomHooks();
		
		/* Register custom events */
		if ( isset( $custom_hooks['events'] ) ) {
			foreach( $custom_hooks['events'] as $type => $events ) {
				foreach( $events as $hook => $info ) {
					if ( isset( $info['definition'] ) ) {
						$definition = $info['definition'];
						$this->describeEvent( $type, $hook, $definition );
					}
				}
			}
		}
		
		/* Register custom actions */
		if ( isset( $custom_hooks['actions'] ) ) {
			foreach( $custom_hooks['actions'] as $hook => $info ) {
				if ( isset( $info['definition'] ) ) {
					$definition = $info['definition'];
					$definition['title'] = $definition['title'];
					if ( ! isset( $definition['callback'] ) ) {
						$definition['callback'] = function() use ( $hook ) {
							call_user_func_array( 'do_action', array_merge( array( $hook ), func_get_args() ) );
						};
					}
					$this->defineAction( $hook, $definition );
				}
			}
		}
		
		/* If our providers cache has been updated, save it */
		if ( $stored_providers !== $this->providers ) {
			$this->setECAProviders( $this->providers );
		}
		
		/* Connect all enabled first level rules to their hooks */
		$_suppress = Rule::getDb()->suppress_errors;
		Rule::getDb()->suppress_errors = true;
		foreach( Rule::loadWhere( array( 'rule_enabled=1 AND rule_parent_id=0' ), 'rule_priority ASC, rule_weight ASC' ) as $rule ) {
			if ( $rule->isActive() ) {
				$rule->deploy();
			}
		}
		Rule::getDb()->suppress_errors = $_suppress;
	}
	
	/**
	 * Add the rules dashboard link to the admin bar
	 *
	 * @MWP\WordPress\Action( for="admin_bar_menu", priority=21 )
	 *
	 * @param	WP_Admin_Bar		$wp_admin_bar			The admin bar instance
	 * @return	array
	 */
	public function addRulesToNetworkAdminBar( $wp_admin_bar )
	{
		if ( ! is_user_logged_in() || ! is_multisite() ) {
			return;
		}

		if ( current_user_can( 'manage_network' ) ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'network-admin',
				'id'     => 'network-admin-rules',
				'title'  => __( 'Rules Engine', 'mwp-rules' ),
				'href'   => $this->getDashboardController()->getUrl(),
			) );			
		}
	}
	
	/**
	 * Get custom hooks cache
	 *
	 * @return array
	 */
	public function getCustomHooks()
	{
		$custom_hooks = $this->getCache( 'custom_hooks', TRUE );
		
		if ( ! is_array( $custom_hooks ) ) 
		{
			$custom_hooks = array( 'events' => array(), 'actions' => array() );
			$dbHelper = \MWP\Framework\DbHelper::instance();
			
			if ( $dbHelper->tableExists( Hook::_getTable() ) and $dbHelper->tableExists( CustomLog::_getTable() ) ) {
				foreach( Hook::loadWhere('1') as $hook ) {
					switch( $hook->type ) {
						case 'custom':
							$custom_hooks['actions'][$hook->hook] = array(
								'definition' => $hook->getActionDefinition(),
							);
							
							// Intentionally move on and add custom action as an event also...
							
						case 'action':
							$custom_hooks['events']['action'][$hook->hook] = array(
								'definition' => $hook->getEventDefinition(),
							);
							break;
						case 'filter':
							$custom_hooks['events']['filter'][$hook->hook] = array(
								'definition' => $hook->getEventDefinition(),
							);
							break;
					}
				}
				
				foreach( CustomLog::loadWhere('1') as $log ) {
					$custom_hooks['events']['action'][ $log->getHookPrefix() . '_create' ] = array(
						'definition' => $log->getEventDefinition(),
					);
					
					$custom_hooks['actions'][ $log->getHookPrefix() . '_create' ] = array(
						'definition' => $log->getActionDefinition(),
					);
				}
				
				$this->setCache( 'custom_hooks', $custom_hooks, TRUE );
			}
		}
		
		return $custom_hooks;		
	}
	
	/**
	 * @var array
	 */
	protected $sites;
	
	/**
	 * Get an array of all multisite sites
	 *
	 * @return	array
	 */
	public function getSites()
	{
		if ( ! isset( $this->sites ) ) {
			$this->sites = array();
			if ( is_multisite() ) {
				foreach( get_sites() as $site ) {
					$this->sites[ $site->id ] = $site;
				}
			}
		}
		
		return $this->sites;
	}
	
	/**
	 * Add select bundles to the WP Settings menu
	 * 
	 * @MWP\WordPress\Action( for="admin_menu" )
	 * 
	 * @return void
	 */
	public function addSettingsMenus()
	{
		$output = '';
		$callback = function() use ( &$output ) { echo $output; };
		
		foreach( Bundle::loadWhere( array( 'bundle_add_menu=1 AND bundle_enabled=1 AND bundle_app_id=0' ) ) as $bundle ) {
			if ( $bundle->hasSettings() ) {
				$menu_name = $bundle->data['menu_title'] ?: $bundle->title;
				$page_hook = add_options_page( $bundle->title, $menu_name, 'manage_options', 'rules-bundle-settings-' . $bundle->id(), $callback );
				add_action( 'load-' . $page_hook, function() use ( $bundle, &$output ) {
					$form = $bundle->getForm( 'settings' );
					if ( $form->isValidSubmission() ) {
						$values = $form->getValues();
						$bundle->processForm( $values, 'settings' );
						add_action( 'admin_notices', function() {
							echo '<div class="notice notice-success">
								 <p>' . __( 'Settings have been saved.', 'mwp-rules' ) . '</p>
							</div>';
						});
						
						/* Fetch new form with updated values */
						$form = $bundle->getForm( 'settings' );
					}
					
					$output .= '<div style="max-width: 1100px; margin: 50px auto;">' . $form->render() . '</div>';
				});
			}
		}
		
	}
	
	/**
	 * Run scheduled actions
	 *
	 * @MWP\WordPress\Action( for="rules_action_runner" )
	 *
	 * @param	Task		$task				The running task
	 * @return	void
	 */
	public function runScheduledActions( $task )
	{
		$_next_action = ScheduledAction::getNextAction();
		
		if ( ! $_next_action ) {
			$task->log( 'No more scheduled actions to run.' );
			return $task->complete();
		}
		
		if ( $_next_action->time > time() ) {
			$task->log( 'Rescheduling for next action in the queue.' );
			$task->next_start = $_next_action->time;
			return;
		}
		
		$task->log( 'Executing scheduled action: ' . $_next_action->id() );
		$_next_action->execute();
	}
	
	/**
	 * Run log maintenance
	 *
	 * @MWP\WordPress\Action( for="rules_log_maintenance" )
	 *
	 * @param	Task		$task				The running task
	 * @return	void
	 */
	public function runLogMaintenance( $task )
	{
		try {
			$log = CustomLog::load( $task->getData( 'log_id' ) );
		}
		catch( \OutOfRangeException $e ) {
			$task->log( 'Custom log no longer exists.' );
			return $task->complete();
		}
		
		$recordClass = $log->getRecordClass();
		
		if ( $log->getMaxAge() > 0 ) {
			$where = array( 'entry_timestamp<=%d', time() - ( $log->getMaxAge() * 24 * 60 * 60 ) );
			if ( $entry = reset( $recordClass::loadWhere( $where, 'entry_timestamp ASC', 1 ) ) ) {
				$entry->delete();
				$task->log( 'Deleted entry #' . $entry->id() );
				$remaining = $recordClass::countWhere( $where );
				$task->setStatus( $remaining . ' expired entries left to delete' );
				return;
			}
		}
		
		if ( $log->getMaxLogs() > 0 ) {
			$total_logs = $recordClass::countWhere('1');
			if ( $total_logs > $log->getMaxLogs() ) {
				if ( $entry = reset( $recordClass::loadWhere('1', 'entry_timestamp ASC', 1 ) ) ) {
					$entry->delete();
					$task->log( 'Deleted entry #' . $entry->id() );
					$remaining = $total_logs - 1 - $log->getMaxLogs();
					$task->setStatus( $remaining . ' overflow entries left to delete' );
					return;
				}
			}
		}
		
		return $task->complete();
	}
	
	/**
	 * Clear hook cache
	 *
	 * @return	void
	 */
	public function clearCustomHooksCache()
	{
		$this->clearCache( 'custom_hooks', TRUE );
	}
	
	/**
	 * ECA Provider Details
	 *
	 * @var array
	 */
	protected $providers;
	
	/**
	 * Get ECA provider details
	 *
	 * @return	array
	 */
	public function getECAProviders()
	{
		if ( isset( $this->providers ) ) {
			return $this->providers;
		}
		
		$this->providers = $this->getCache( 'providers' ) ?: array();
		
		return $this->providers;
	}
	
	/**
	 * Set ECA provider details
	 *
	 * @return	void
	 */
	public function setECAProviders( $providers )
	{
		$this->providers = $providers;
		$this->setCache( 'providers', $providers, FALSE, Framework::instance()->isDev() ? MINUTE_IN_SECONDS : MONTH_IN_SECONDS );
	}
	
	/**
	 * @var	array
	 */
	protected $plugin_list;
	
	/**
	 * Get a list of all active plugins 
	 * 
	 * @return	array
	 */
	public function getPluginList()
	{
		if ( isset( $this->plugin_list ) ) {
			return $this->plugin_list;
		}
		
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$plugin_list = array_keys( get_plugins() );
		$this->plugin_list = array_combine( array_map( function($p) { return explode('/',$p)[0]; }, $plugin_list ), $plugin_list );
		
		return $this->plugin_list;
	}
	
	/**
	 * Get the plugin file for a given plugin slug
	 * 
	 * @param	string			$slug				The plugin directory slug
	 * @return	string|NULL
	 */
	public function getPluginFile( $slug )
	{
		$plugin_list = $this->getPluginList();
		
		if ( isset( $plugin_list[ $slug ] ) ) {
			return $plugin_list[ $slug ];
		}
		
		return null;
	}
	
	/**
	 * Get all available rule packages bundled with plugins
	 *
	 * @return	array
	 */
	public function getPluginRulesPackages()
	{
		$rules_packages = array();
		$plugin = $this;
		
		foreach( $this->getPluginList() as $slug => $plugin_file ) {
			if ( is_dir( WP_PLUGIN_DIR . '/' . $slug . '/rules' ) ) {
				$packages = array_filter( 
					array_map( function( $file ) use ( $plugin, $slug, $plugin_file ) { 
						try {
							return Package::loadFromFile( $file );
						}
						catch( \Exception $e ) { }
					}, glob( WP_PLUGIN_DIR . '/' . $slug . '/rules/*.json' 
				)));
				
				if ( ! empty( $packages ) ) {
					$rules_packages[] = array(
						'source' => [ 
							'type' => 'plugin',
							'data' => $this->getPluginData( WP_PLUGIN_DIR . '/' . $plugin_file ),
						],
						'packages' => $packages,
					);
				}
			}
		}
		
		return $rules_packages;
	}
	
	/**
	 * @var	array
	 */
	protected $themes = array();
	
	/**
	 * Get a theme by its slug
	 * 
	 * @param	string			$theme_slug				The theme directory slug
	 * @return	WP_Theme|bool
	 */
	public function getTheme( $theme_slug )
	{
		if ( isset( $this->themes[ $theme_slug ] ) ) {
			return $this->themes[ $theme_slug ];
		}
		
		$theme = wp_get_theme( $theme_slug );
		if ( $theme->exists() ) {
			$this->themes[ $theme_slug ] = $theme;
		} else {
			$this->themes[ $theme_slug ] = false;
		}
		
		return $this->themes[ $theme_slug ];
	}
	
	/**
	 * @var array
	 */
	protected $resource_dirs;
	
	/**
	 * Get directories of resources that can contain ECA's
	 * 
	 * @return	array
	 */
	public function getResourceDirs()
	{
		if ( isset( $this->resource_dirs ) ) {
			return $this->resource_dirs;
		}
		
		$this->resource_dirs = array(
			'plugin_dir' => wp_normalize_path( WP_PLUGIN_DIR ),
			'theme_dir' => wp_normalize_path( get_theme_root() ),
			'expansion_dir' => wp_normalize_path( $this->getPath() . '/expansions' ),
		);
		
		return $this->resource_dirs;
	}
	
	/**
	 * @var array
	 */
	protected $plugin_data = array();
	
	/**
	 * Get plugin data
	 *
	 * @param	string		$plugin_file		The plugin file
	 * @return	array
	 */
	public function getPluginData( $plugin_file )
	{
		if ( isset( $this->plugin_data[ $plugin_file ] ) ) {
			return $this->plugin_data[ $plugin_file ];
		}
		
		$this->plugin_data[ $plugin_file ] = get_plugin_data( $plugin_file, false );
		
		return $this->plugin_data[ $plugin_file ];
	}
	
	/**
	 * @var array
	 */
	protected $file_data = array();
	
	/**
	 * Get file data
	 *
	 * @param	string		$file				The full file path
	 * @return	array
	 */
	public function getFileData( $file, $default_headers )
	{
		if ( isset( $this->file_data[ $file ] ) ) {
			return $this->file_data[ $file ];
		}
		
		$this->file_data[ $file ] = get_file_data( $file, $default_headers );
		
		return $this->file_data[ $file ];
	}
	
	/**
	 * Get the details of the code registering an ECA
	 *
	 * @param	int			$stack_depth			The depth to start slicing off the backtrace to get to the function caller
	 * @return	array
	 */
	public function getCallerDetails( $stack_depth=0 )
	{
		$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		
		do {
			$caller = array_slice( $debug_backtrace, $stack_depth, 1 )[0];
		} 
		while( 
			substr( $caller['file'], -13 ) === "eval()'d code" && 
			$stack_depth = $stack_depth + substr_count( $caller['file'], "eval()'d code" ) + substr_count( $caller['file'], "rules.core.functions.php" )
		);
		
		$file = wp_normalize_path( $caller['file'] );	
		list( $plugin_dir, $theme_dir, $expansion_dir ) = array_values( $this->getResourceDirs() );
		
		/* Is the resource in the expansions path */
		if ( substr( $file, 0, strlen( $expansion_dir ) ) == $expansion_dir ) 
		{
			$relative_file = trim( substr( $file, strlen( $expansion_dir ) ), '/' );
			$expansion_slug = substr( $relative_file, 0, strpos( $relative_file, '/' ) );
			
			if ( is_file( $expansion_dir . '/' . $expansion_slug . '/init.php' ) ) {
				$expansion_data = $this->getFileData( $expansion_dir . '/' . $expansion_slug . '/init.php', [ 'Name' => 'Name', 'Url' => 'Url', 'Version' => 'Version' ] );
				
				return array(
					'provider' => [
						'type'     => 'expansion',
						'slug'     => $expansion_slug,
						'title'    => $expansion_data['Name'] ?: ucwords( str_replace( '_', ' ', $expansion_slug ) ),
						'url'      => $expansion_data['Url'] ?: null,
						'version'  => $expansion_data['Version'] ?: null,
					],
				);
			}
		}
		
		/* Is the resource in the plugin path */
		else if ( substr( $file, 0, strlen( $plugin_dir ) ) == $plugin_dir ) 
		{
			$relative_file = trim( substr( $file, strlen( $plugin_dir ) ), '/' );
			$plugin_slug = substr( $relative_file, 0, strpos( $relative_file, '/' ) );

			if ( $plugin_file = $this->getPluginFile( $plugin_slug ) ) {
				if ( is_file( $plugin_dir . '/' . $plugin_file ) ) {
					$plugin_data = $this->getPluginData( $plugin_dir . '/' . $plugin_file, false, false );
					
					return array(
						'provider' => [
							'type'     => 'plugin',
							'slug'     => $plugin_slug,
							'title'    => $plugin_data['Name'],
							'url'      => $plugin_data['PluginURI'] ?: null,
							'version'  => $plugin_data['Version'] ?: null,
						],
					);
				}
			}
		}
		
		/* Is the resource in the theme path */
		else if ( substr( $file, 0, strlen( $theme_dir ) ) == $theme_dir ) 
		{
			$relative_file = trim( substr( $file, strlen( $theme_dir ) ), '/' );
			$theme_slug = substr( $relative_file, 0, strpos( $relative_file, '/' ) );

			if ( $theme = $this->getTheme( $theme_slug ) ) {					
				return array(
					'provider' => [
						'type'     => 'theme',
						'slug'     => $theme_slug,
						'parent'   => $theme->get( 'Template' ),
						'title'    => $theme->get( 'Name' ),
						'url'      => $theme->get( 'ThemeURI' ),
						'version'  => $theme->get( 'Version' ),
					],
				);
			}
		}
		
		return array();
	}
	
	/**
	 * Describe an event that rules can be created for
	 *
	 * @param	string					$type				The event type (action, filter)
	 * @param	string					$hook_name			The event hook name
	 * @param	array|object|closure	$definition			The event definition
	 * @param	int						$stack_depth		(internal) Used to track how many calls back to trace the original caller
	 * @return	void
	 */
	public function describeEvent( $type, $hook_name, $definition=array(), $stack_depth=1 )
	{
		if ( ! isset( $this->providers['events'][ $type ][ $hook_name ] ) ) {
			$provider = $this->getCallerDetails( $stack_depth );
			$provider_hash = md5( json_encode( $provider ) );
			$this->providers['events'][ $type ][ $hook_name ] = $provider_hash;
			$this->providers['refs'][ $provider_hash ] = $provider;
		}
		
		$provider_info = @$this->providers['refs'][ $this->providers['events'][ $type ][ $hook_name ] ] ?: array();
		
		$this->events[ $type ][ $hook_name ] = new Loader( 'MWP\Rules\ECA\Event', $definition, array_merge( $provider_info, array( 
			'type' => $type,
			'hook' => $hook_name,
		)));
	}
	
	/**
	 * Register a condition that can be added to rules
	 *
	 * @param	string			$condition_key		The condition key
	 * @param	mixed			$definition			The condition definition
	 * @param	int				$stack_depth		(internal) Used to track how many calls back to trace the original caller
	 * @return	void
	 */
	public function registerCondition( $condition_key, $definition, $stack_depth=1 )
	{
		if ( ! isset( $this->providers['conditions'][ $condition_key ] ) ) {
			$provider = $this->getCallerDetails( $stack_depth );
			$provider_hash = md5( json_encode( $provider ) );
			$this->providers['conditions'][ $condition_key ] = $provider_hash;
			$this->providers['refs'][ $provider_hash ] = $provider;
		}
		
		$provider_info = @$this->providers['refs'][ $this->providers['conditions'][ $condition_key ] ] ?: array();
		
		$this->conditions[ $condition_key ] = new Loader( 'MWP\Rules\ECA\Condition', $definition, array_merge( $provider_info, array(
			'key' => $condition_key,
		)));
	}
	
	/**
	 * Define an action that can be added to rules
	 *
	 * @param	string			$action_key			The action key
	 * @param	mixed			$definition			The action definition
	 * @param	int				$stack_depth		(internal) Used to track how many calls back to trace the original caller
	 * @return	void
	 */
	public function defineAction( $action_key, $definition, $stack_depth=1 )
	{
		if ( ! isset( $this->providers['actions'][ $action_key ] ) ) {
			$provider = $this->getCallerDetails( $stack_depth );
			$provider_hash = md5( json_encode( $provider ) );
			$this->providers['actions'][ $action_key ] = $provider_hash;
			$this->providers['refs'][ $provider_hash ] = $provider;
		}
		
		$provider_info = @$this->providers['refs'][ $this->providers['actions'][ $action_key ] ] ?: array();
		
		$this->actions[ $action_key ] = new Loader( 'MWP\Rules\ECA\Action', $definition, array_merge( $provider_info, array(
			'key' => $action_key,
		)));
	}
	
	/**
	 * Get all events
	 * 
	 * @param	string		$type			The events type
	 * @return	array
	 */
	public function getEvents( $type )
	{
		if ( isset( $this->events[ $type ] ) ) {
			return array_map( function( $eca ) { return $eca->instance(); }, $this->events[ $type ] );
		}
		
		return array();
	}
	
	/**
	 * Get a specific event
	 * 
	 * @param	string		$type			The events type
	 * @param	string		$hook_name		The event name
	 * @return	object|NULL
	 */
	public function getEvent( $type, $hook_name )
	{
		if ( isset( $this->events[ $type ][ $hook_name ] ) ) {
			return $this->events[ $type ][ $hook_name ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * Get all conditions
	 * 
	 * @return	array
	 */
	public function getConditions()
	{
		return array_map( function( $eca ) { return $eca->instance(); }, $this->conditions );
	}
	
	/**
	 * Get a specific condition
	 * 
	 * @param	string		$condition_key			The condition key
	 * @return	object|NULL
	 */
	public function getCondition( $condition_key )
	{
		if ( isset( $this->conditions[ $condition_key ] ) ) {
			return $this->conditions[ $condition_key ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * Get all actions
	 * 
	 * @return	array
	 */
	public function getActions()
	{
		return array_map( function( $eca ) { return $eca->instance(); }, $this->actions );
	}
	
	/**
	 * Get a specific action
	 * 
	 * @param	string		$action_key			The action key
	 * @return	object|NULL
	 */
	public function getAction( $action_key )
	{
		if ( isset( $this->actions[ $action_key ] ) ) {
			return $this->actions[ $action_key ]->instance();
		}
		
		return NULL;
	}
	
	/**
	 * Get the rules controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getDashboardController()
	{
		return Controllers\DashboardController::get('dashboard');
	}
	
	/**
	 * Get the rules controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getRulesController( $container=null, $key='admin' )
	{
		$controller = Rule::getController( $key );
		$controller->setBundle( NULL );
		$controller->setHook( NULL );
		
		if ( $container instanceof Bundle ) {
			$controller->setBundle( $container );
		}
		else if ( $container instanceof Hook ) {
			$controller->setHook( $container );
		}
		
		return $controller;
	}
	
	/**
	 * Get the custom logs controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getCustomLogsController( $key='admin' )
	{
		return CustomLog::getController( $key );
	}
	
	/**
	 * Get the hooks controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getHooksController( $key='admin' )
	{
		return Hook::getController( $key );		
	}
	
	/**
	 * Get the apps controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getAppsController( $key='admin' )
	{
		return App::getController( $key );		
	}
	
	/**
	 * Get the bundles controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getBundlesController( $app=null, $key='admin' )
	{
		$controller = Bundle::getController( $key );
		$controller->setApp( $app );
		
		return $controller;
	}
	
	/**
	 * Get the conditions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getConditionsController( $rule=null, $key='admin' )
	{
		$controller = Condition::getController( $key );
		$controller->setRule( $rule );	
		
		return $controller;
	}
	
	/**
	 * Get the actions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getActionsController( $rule=null, $key='admin' )
	{
		$controller = Action::getController( $key );
		$controller->setRule( $rule );
		
		return $controller;
	}
	
	/**
	 * Get the arguments controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getArgumentsController( $parent=null, $key='admin' )
	{
		$controller = Argument::getController( $key );
		$controller->setParent( $parent );
		
		return $controller;
	}
	
	/**
	 * Get the logs controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getLogsController( $key='admin' )
	{
		return RuleLog::getController( $key );
	}
	
	/**
	 * Get the actions controller
	 * 
	 * @return	ActiveRecordController
	 */
	public function getScheduleController( $key='admin' )
	{
		return ScheduledAction::getController( $key );
	}
	
	/**
	 * Global Arguments
	 */
	public $globalArguments;
	
	/**
	 * Get Global Arguments
	 *
	 * @param	arg_name	Optional name of an argument definition to return
	 * @return 	array		Keyed array of global arguments
	 */
	public function getGlobalArguments( $arg_name=NULL )
	{
		if ( isset ( $this->globalArguments ) ) {
			return isset( $arg_name ) ? ( isset( $this->globalArguments[ $arg_name ] ) ? $this->globalArguments[ $arg_name ] : NULL ) : $this->globalArguments;
		}
		
		$this->globalArguments = apply_filters( 'rules_global_arguments', array() );
		
		return $this->getGlobalArguments( $arg_name );
	}
	
	/**
	 * Class map
	 */
	public $classMap;
	
	/**
	 * Get Class Conversion Mappings
	 * 
	 * @param 	string|NULL		$class		A specific class to return conversions for, NULL for all
	 * @return	array|NULL					Class conversion definitions
	 */
	public function getClassMappings( $class=NULL )
	{
		if ( isset( $this->classMap ) ) {
			return isset( $class ) ? ( isset( $this->classMap[ $class ] ) ? $this->classMap[ $class ] : NULL ) : $this->classMap;
		}
		
		$this->classMap = apply_filters( 'rules_class_map', array() );
		
		return $this->getClassMappings( $class );
	}
	
	/**
	 * Add to the rules class map
	 * 
	 * @MWP\WordPress\Action( for="rules_class_map" )
	 * 
	 * @param	array		$map			The class map
	 * @return	array
	 */
	public function addRecordClassMappings( $map )
	{
		CustomLog::addToRulesMap( $map );
		
		foreach( CustomLog::loadWhere('1') as $custom_log ) {
			$custom_log->getRecordController();
			$customLogClass = $custom_log->getRecordClass();
			$customLogClass::addToRulesMap( $map );
		}
		
		return $map;
	}

	/**
	 * Get a list of arguments, and add in any sub mappings for array types with defined keys
	 * 
	 * @param	array		$arguments					The list of arguments to expand
	 * @return	array
	 */
	public function getExpandedArguments( $arguments )
	{
		$expanded_arguments = array();
		
		foreach( $arguments as $key => $argument ) {
			$expanded_arguments[ $key ] = $argument;
			if ( isset( $argument['argtype'] ) and $argument['argtype'] == 'array' ) {
				$default_argument = isset( $argument['keys']['default'] ) && is_array( $argument['keys']['default'] ) ? $argument['keys']['default'] : array();
				if ( isset( $argument['keys']['mappings'] ) and is_array( $argument['keys']['mappings'] ) ) {
					foreach( $argument['keys']['mappings'] as $mapped_key => $mapped_argument ) {
						$expanded_arguments[ $key . '[' . $mapped_key . ']' ] = array_merge( $default_argument, (array) $mapped_argument );
					}
				}
			}
		}
		
		return $expanded_arguments;
	}

	/**
	 * Get possible derivative arguments using the class map
	 *
	 * Based on the arguments provided, returns a map of subsequent arguments that can be derived
	 *
	 * @param	array	$source_argument		The starting argument
	 * @param	array	$target_argument		The definition which derivative arguments must match (or leave empty to return all derivatives)
	 * @param	int		$max_levels				The number of levels of recursion to dive
	 * @param	bool	$include_arbitrary		Include an arbitrary keys representation token in the results
	 * @param	string	$token_prefix			Prefix to apply to the tokenized keys (for internal use)
	 * @param	int		$level					The current level of recursion (for internal use)
	 * @return	array							Class converter methods
	 */
	public function getDerivativeTokens( $source_argument, $target_argument=NULL, $max_levels=1, $include_arbitrary=TRUE, $token_prefix='', $level=1 )
	{
		/* Depth limit */
		if ( $level > $max_levels ) {
			return array();
		}
		
		$derivative_arguments = array();
		$mappings             = array();
		$source_class         = NULL;

		if ( $token_prefix ) {
			$token_prefix .= ':';
		}
		
		if ( isset( $source_argument['class'] ) ) {
			list( $source_class, $source_key ) = $this->parseIdentifier( $source_argument['class'] );
		}
		
		/* If the source argument doesn't point to any specific class... it can't map to anything */
		if ( ! $source_class ) {
			return array();
		}
		
		if ( $source_argument['argtype'] !== 'object' ) {			
			/* If the source argument can't be used to load an instance... it can't map to anything */
			$source_class_map = $this->getClassMappings( $source_class );
			if ( ! $source_class_map or ! isset( $source_class_map['loader'] ) or ! is_callable( $source_class_map['loader'] ) ) {
				return array();
			}
		}
		
		/**
		 * Compile a list of all the classes in our class map that are compliant 
		 * with the argument, meaning that the argument can be used to load it, 
		 * or a subclass of it
		 */
		foreach ( $this->getClassMappings() as $classname => $class ) {
			if ( $this->isClassCompliant( $source_argument['class'], $classname ) ) {
				$augmented_class = in_array( $source_argument['argtype'], array( 'object', 'array' ) ) ? array() : array(
					'mappings' => array(
						'*' => array(
							'argtype' => 'object',
							'class' => $classname,
							'label' => ( isset( $class['label'] ) ? $class['label'] : $classname ) . ' Object',
							'getter' => function( $object ) { return $object; },
						),
					),
				);
				
				$mappings[ $classname ] = array_replace_recursive( $augmented_class, $class );
			}
		}
		
		/**
		 * Now for every class that has conversions available, we look at each of the 
		 * conversion options and see if they are compatible with our target argument. 
		 */
		foreach ( $mappings as $classname => $class ) {
			if ( isset( $class['mappings'] ) ) {
				foreach ( $class['mappings'] as $argument_key => $converted_argument ) {
					$original_converted_argtype = $converted_argument['argtype'];
					
					/* Source arrays are always going to produce another array */
					if ( $source_argument['argtype'] == 'array' ) {
						$converted_argument['subtype'] = $converted_argument['argtype'] != 'array' ? 
							$converted_argument['argtype'] : ( 
								isset( $converted_argument['class'] ) ? 
								'object' : ( $converted_argument['subtype'] ?? 'mixed' ) 
							);

						$converted_argument['argtype'] = 'array';
					}
					
					if ( $this->isArgumentCompliant( $converted_argument, $target_argument ) ) {
						$derivative_arguments[ $token_prefix . $argument_key ] = $converted_argument;
					}
					
					/* For arrays that have key mappings, let's look at those too to see what we have */
					if ( $converted_argument['argtype'] == 'array' ) {
						
						$default_array_argument = array( 
							'label' => isset( $converted_argument['label'] ) ? $converted_argument['label'] : '',
							'argtype' => $original_converted_argtype != 'array' ? 
								$original_converted_argtype : ( 
									isset( $converted_argument['class'] ) ? 'object' : ( 
										$converted_argument['subtype'] ?? 'mixed' 
									) 
								), 
						);
						$arbitrary_key_indicator = ( isset( $converted_argument['keys']['associative'] ) and $converted_argument['keys']['associative'] ) ? 'a-z' : '0-9';
						
						// Default for arrays with a class specification
						if ( isset( $converted_argument['class'] ) ) {
							$default_array_argument = array_merge( $default_array_argument, array( 'argtype' => 'object', 'class' => $converted_argument['class'] ) );
						}
						
						// Default override for arbitrary keys
						if ( isset( $converted_argument['keys']['default'] ) ) {
							$default_array_argument = array_merge( $default_array_argument, $converted_argument['keys']['default'] );
						}

						// Add tokens for arbitrary array keys
						if ( $include_arbitrary and ( ! isset( $converted_argument['keys']['fixed'] ) or ! $converted_argument['keys']['fixed'] ) ) {
							if ( $source_argument['argtype'] == 'array' and $original_converted_argtype == 'array' ) {
								$default_array_argument['argtype'] = 'array';
							}
							if ( $this->isArgumentCompliant( $default_array_argument, $target_argument ) ) {
								$derivative_arguments[ $token_prefix . $argument_key . '[' . $arbitrary_key_indicator . ']' ] = $default_array_argument;
							}
							
							/* Go deep on arbitrary keys */
							$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $default_array_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key . '[' . $arbitrary_key_indicator . ']', $level + 1 ) );
						}
						
						// Add tokens for specific array keys
						if ( isset( $converted_argument['keys']['mappings'] ) ) {
							foreach( $converted_argument['keys']['mappings'] as $converted_array_key => $converted_array_argument ) {
								if ( ! empty( $default_array_argument ) ) {
									$converted_array_argument = array_merge( $default_array_argument, $converted_array_argument );
								}
								if ( $source_argument['argtype'] == 'array' and $original_converted_argtype == 'array' ) {
									$converted_array_argument['argtype'] = 'array';
								}
								
								if ( $this->isArgumentCompliant( $converted_array_argument, $target_argument ) ) {
									$derivative_arguments[ $token_prefix . $argument_key . '[' . $converted_array_key . ']' ] = $converted_array_argument;
								}
								
								/* Go deep on specific keys */
								$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $converted_array_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key . '[' . $converted_array_key . ']', $level + 1 ) );
							}
						}
					}
					
					/* Go deep on token */
					if ( $argument_key !== '*' ) {
						$derivative_arguments = array_merge( $derivative_arguments, $this->getDerivativeTokens( $converted_argument, $target_argument, $max_levels, $include_arbitrary, $token_prefix . $argument_key, $level + 1 ) );
					}
				}
			}
		}
		
		return $derivative_arguments;
	}
	
	/**
	 * Get the identifier and optional key for an class specification
	 *
	 * @param	string			$identifier			The identifier in the form of identifier[key]
	 * @return	array
	 */
	public function parseIdentifier( $identifier )
	{
		if ( strstr( $identifier, '[' ) !== FALSE ) {
			$components = explode( '[', $identifier );
			return array( $components[0], str_replace( ']', '', $components[1] ) );
		}
		
		return array( $identifier, NULL );
	}
	
	/**
	 * Check For Argument Compliance
	 *
	 * @param	array	$source_argument		The argument definition to map
	 * @param	array	$target_argument		The argument which is needed (or leave empty to return all derivatives)
	 * @return	bool
	 */
	public function isArgumentCompliant( $source_argument, $target_argument=NULL )
	{
		if ( ! isset( $source_argument['argtype'] ) ) {
			return false;
		}
		
		if ( ! isset( $target_argument ) ) {
			return true;
		}
		
		$target_types = array();

		if ( isset( $target_argument['argtypes'] ) ) {
			foreach( (array) $target_argument['argtypes'] as $k => $v ) {
				if ( is_array( $v ) ) {
					$target_types[ $k ] = $v;
				} else {
					$target_types[ $v ] = array();
				}
			}
		}
		
		if ( in_array( 'mixed', array_keys( $target_types ) ) or in_array( $source_argument['argtype'], array_keys( $target_types ) ) ) {
			$is_compliant = true;
			$target_type = ! empty( $target_types ) ? ( in_array( $source_argument['argtype'], array_keys( $target_types ) ) ? $target_types[ $source_argument['argtype'] ] : $target_types['mixed'] ) : array();
			if ( isset( $target_type['classes'] ) and ! empty( $target_type['classes'] ) ) {
				$is_compliant = false;
				if ( isset( $source_argument['class'] ) ) {
					foreach( (array) $target_type['classes'] as $target_class ) {
						if ( $this->isClassCompliant( $source_argument['class'], $target_class ) ) {
							$is_compliant = true;
							break;
						}
					}
				}
			}
			
			return $is_compliant;
		}
		
		return false;
	}

	/**
	 * Check For Class Compliance (Can argument with type $class be used as input to arguments that need $classes)
	 *
	 * @param	string 		        $class      Class to check compliance
	 * @param	string|array	    $classes    A classname or array of classnames to validate against
	 * @return	bool				Will return TRUE if $class is the same as or is a subclass of any $classes
	 */
	public function isClassCompliant( $class, $classes )
	{
		list( $class, $class_key ) = $this->parseIdentifier( $class );
		$compliant = FALSE;
		
		foreach ( (array) $classes as $_class ) {
			list( $_class, $_class_key ) = $this->parseIdentifier( $_class );
			
			if ( ltrim( $_class, '\\' ) === ltrim( $class, '\\' ) ) {
				$compliant = TRUE;
			}
			else if ( is_subclass_of( $class, $_class ) ) {
				$compliant = TRUE;
			}
			
			if ( $compliant and ( $_class_key and $class_key ) ) {
				if ( $class_key != $_class_key ) {
					$compliant = FALSE;
				}
			}
			
			if ( $compliant ) {
				break;
			}
		}
		
		return $compliant;
	}
	
	/**
	 * @var	array
	 */
	protected $config_preset_options;
	
	/**
	 * Provide available rules config preset options
	 *
	 * @return	array
	 */
	public function getRulesConfigPresetOptions()
	{
		if ( isset( $this->config_preset_options ) ) {
			return $this->config_preset_options;
		}
		
		$plugin = $this;
		
		$this->config_preset_options = apply_filters( 'rules_config_preset_options', array(
			'text' => array(
				'label' => 'Text Field',
				'argtypes' => ['string'],
				'config' => array(
					'form' => function( $name, $form, $values, $argument ) {
						$form->addField( $name . '_placeholder', 'text', array(
							'label' => __( 'Placeholder', 'mwp-rules' ),
							'data' => isset( $values[ $name . '_placeholder' ] ) ? $values[ $name . '_placeholder' ] : '',
						));
					},
					'getConfig' => function( $name, $values, $argument ) {
						return array(
							'attr' => array( 'placeholder' => isset( $values[ $name . '_placeholder' ] ) ? $values[ $name . '_placeholder' ] : '' ),
						);
					},
				),
			),
			'checkbox' => array(
				'label' => 'Checkbox',
				'argtypes' => ['bool'],
			),
			'choice' => array(
				'label' => 'Choice Field',
				'config' => array(
					'form' => function( $name, $form, $values, $argument ) use ( $plugin ) {
						$form->addField( $name . '_multiple', 'checkbox', array(
							'label' => __( 'Allow Multiple Selections', 'mwp-rules' ),
							'value' => 1,
							'data' => isset( $values[ $name . '_multiple' ] ) ? $values[ $name . '_multiple' ] : false,
						));
						$form->addField( $name . '_expanded', 'checkbox', array(
							'label' => __( 'Show In Expanded Form', 'mwp-rules' ),
							'description' => __( 'Expanded form will show a list of radio/checkboxes to select from. Otherwise, a standard select field is shown.', 'mwp-rules' ),
							'value' => 1,
							'data' => isset( $values[ $name . '_expanded' ] ) ? $values[ $name . '_expanded' ] : false,
						));
						$form->addField( $name . '_options_source', 'choice', array(
							'label' => __( 'Options Source', 'mwp-rules' ),
							'data' => isset( $values[ $name . '_options_source' ] ) ? $values[ $name . '_options_source' ] : 'manual',
							'required' => true,
							'choices' => array(
								'Pre-Defined'   => 'manual',
								'PHP Code'      => 'phpcode',
								'User Roles'    => 'roles',
								'Post Types'    => 'post_types',
								'Post Statuses' => 'post_statuses',
							),
							'toggles' => array(
								'manual' => array( 'show' => array( '#' . $name . '_options_manual' ) ),
								'phpcode' => array( 'show' => array( '#' . $name . '_options_phpcode' ) ),
							),
						));
						$key_array_preset = $plugin->configPreset( 'key_array', $name . '_options_manual', array(
							'row_attr' => array( 'id' => $name . '_options_manual' ),
							'label' => __( 'Choice Values', 'mwp-rules' ),
						));
						call_user_func( $key_array_preset['form'], $form, $values, $argument );
						$form->addField( $name . '_options_phpcode', 'textarea', array(
							'row_attr' => array(  'id' => $name . '_options_phpcode', 'data-view-model' => 'mwp-rules' ),
							'label' => __( 'PHP Code', 'mwp-rules' ),
							'attr' => array( 'data-bind' => 'codemirror: { lineNumbers: true, mode: \'application/x-httpd-php\' }' ),
							'data' => isset( $values[ $name . '_options_phpcode' ] ) ? $values[ $name . '_options_phpcode' ] : "// <?php \n\nreturn array();",
							'description' => $plugin->getTemplateContent( 'snippets/phpcode_description', array( 'return_args' => [ "<code>array</code>: An array of select options, the keys being the option text." ] ) ),
							'required' => false,
						));
					},
					'getConfig' => function( $name, $values, $argument ) use ( $plugin ) {
						$options_source = isset( $values[ $name . '_options_source' ] ) ? $values[ $name . '_options_source' ] : NULL;
						$choices = array();
						
						switch( $options_source ) {
							case 'manual':
								$preset = $plugin->configPreset( 'key_array', $name . '_options_manual', [] );
								if ( isset( $preset['getArg'] ) and is_callable( $preset['getArg'] ) ) {
									$choices = array_flip( call_user_func( $preset['getArg'], $values, [], $argument ) );
								}
								break;
							case 'phpcode':
								$evaluate = rules_evaluation_closure();
								if ( isset( $values[ $name . '_options_phpcode' ] ) ) {
									$choices = $evaluate( $values[ $name . '_options_phpcode' ] );
								}
								break;
							case 'roles':
								$roles = wp_roles();
								foreach( $roles->roles as $slug => $role ) {
									$choices[ $role['name'] ] = $slug;
								}							
								break;
								
							case 'post_types':
								foreach( get_post_types( [], 'objects' ) as $post_type ) {
									$choices[ $post_type->label ] = $post_type->name;
								}								
								break;
								
							case 'post_statuses': 
								foreach( get_post_stati( [], 'objects' ) as $name => $status ) {
									$choices[ $status->label ] = $status->name;
								}
								break;
							
						}
						
						return array( 
							'choices' => $choices,
							'multiple' => isset( $values[ $name . '_multiple' ] ) ? (bool) $values[ $name . '_multiple' ] : false,
							'expanded' => isset( $values[ $name . '_expanded' ] ) ? (bool) $values[ $name . '_expanded' ] : false,
						);
					},
				),
			),
			'integer' => array( 
				'label' => 'Integer Input',
				'argtypes' => ['int'],
			),
			'textarea' => array(
				'label' => 'Text Area',
				'argtypes' => ['string'],
				'config' => array(
					'form' => function( $name, $form, $values, $argument ) {
						$form->addField( $name . '_placeholder', 'textarea', array(
							'label' => __( 'Placeholder', 'mwp-rules' ),
							'data' => isset( $values[ $name . '_placeholder' ] ) ? $values[ $name . '_placeholder' ] : '',
						));
					},
					'getConfig' => function( $name, $values, $argument ) {
						return array(
							'attr' => array( 'placeholder' => isset( $values[ $name . '_placeholder' ] ) ? $values[ $name . '_placeholder' ] : '' ),
						);
					},
				),
			),
			'datetime' => array(
				'label' => 'Date and Time Input',
				'argtypes' => ['object'],
				'classes' => ['DateTime'],
			),
			'date' => array(
				'label' => 'Date Input',
				'argtypes' => ['object'],
				'classes' => ['DateTime'],
			),
			'time' => array(
				'label' => 'Time Input',
				'argtypes' => ['object'],
				'classes' => ['DateTime'],
			),
			'user' => array(
				'label' => 'User Select',
				'argtypes' => ['object'],
				'classes' => ['WP_User'],
			),
			'users' => array(
				'label' => 'Multiple Users Select',
				'argtypes' => ['array'],
				'classes' => ['WP_User'],
			),
			'post' => array(
				'label' => 'Single Post',
				'argtypes' => ['object'],
				'classes' => ['WP_Post'],
			),
			'posts' => array(
				'label' => 'Multiple Posts',
				'argtypes' => ['array'],
				'classes' => ['WP_Posts'],
			),
			'comment' => array(
				'label' => 'Single Comment',
				'argtypes' => ['object'],
				'classes' => ['WP_Comment'],
			),
			'comments' => array(
				'label' => 'Multiple Comments',
				'argtypes' => ['array'],
				'classes' => ['WP_Comment'],
			),
			'taxonomy' => array(
				'label' => 'Single Taxonomy',
				'argtypes' => ['object'],
				'classes' => ['WP_Taxonomy'],
			),
			'taxonomies' => array(
				'label' => 'Multiple Taxonomies',
				'argtypes' => ['array'],
				'classes' => ['WP_Taxonomy'],
			),
			'term' => array(
				'label' => 'Single Taxonomy Term',
				'argtypes' => ['object'],
				'classes' => ['WP_Term'],
			),
			'terms' => array(
				'label' => 'Multiple Taxonomy Terms',
				'argtypes' => ['array'],
				'classes' => ['WP_Term'],
			),
			'array' => array(
				'label' => 'Array',
				'argtypes' => ['array'],
			),
			'key_array' => array(
				'label' => 'Array with keys',
				'argtypes' => ['array'],
			),
			'meta_values' => array(
				'label' => 'Meta Key/Value Pairs',
				'argtypes' => ['array'],
			),
		));
		
		return $this->config_preset_options;
	}
	
	/**
	 * Configuration Form Presets
	 *
	 * @param	string	$key			The key for the configuration preset to retrieve
	 * @param	string	$field_name		The name of the field
	 * @param	array	$options		Additional config options
	 * @return	array					The argument preset definition
	 */
	public function configPreset( $key, $field_name, $options=array() )
	{
		$config = array();
		
		switch ( $key ) {
			
			/* Choice Entry Field */
			case 'choice':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'choice', array_replace_recursive( array(
							'label' => __( 'Choice', 'mwp-rules' ),
							'choices' => [],
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL,
						),
						$options ));
					},
					'getArg' => function( $values ) use ( $field_name ) {
						return isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL;
					}
				);
				break;
				
			/* Integer Entry Field */
			case 'integer':
			
				$config = array(
					'form' => function( $form, $values ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'integer', array_replace_recursive( array(
							'label' => __( 'Number', 'mwp-rules' ),
							'description' => __( 'Enter an integer value.', 'mwp-rules' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL,
						),
						$options ));
					},
					'getArg' => function( $values ) use ( $field_name ) {
						return isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL;
					}
				);
				break;
			
			/* Simple Text Field */
			case 'text':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Text', 'mwp-rules' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						return isset( $values[ $field_name ] ) ? $operation->replaceTokens( $values[$field_name], $arg_map ) : NULL;
					}
				);
				break;
			
			/* Checkbox Field */
			case 'checkbox':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'checkbox', array_replace_recursive( array(
							'label' => __( 'Enable', 'mwp-rules' ),
							'value' => 1,
							'data' => isset( $values[ $field_name ] ) ? (bool) $values[ $field_name ] : false,
						),
						$options ));
					},
					'getArg' => function( $values ) use ( $field_name ) {
						return isset( $values[ $field_name ] ) ? (bool) $values[ $field_name ] : NULL;
					}
				);
				break;
				
			/* Simple Textarea Field */
			case 'textarea':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Text', 'mwp-rules' ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						return isset( $values[ $field_name ] ) ? $operation->replaceTokens( $values[$field_name], $arg_map ) : NULL;
					}
				);
				break;
				
			/* Date/Time */
			case 'datetime':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'datetime', array_replace_recursive( array(
							'label' => __( 'Date/Time', 'mwp-rules' ),
							'view_timezone' => get_option( 'timezone_string' ) ?: 'UTC',
							'input' => 'timestamp',
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL,
						), 
						$options ));
					},
					'saveValues' => function( &$values, $operation ) use ( $field_name ) {	
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \DateTime ) {
							$values[ $field_name ] = $values[ $field_name ]->getTimestamp();
						}
					},
					'getArg' => function( $values ) use ( $field_name ) {
						if ( isset( $values[ $field_name ] ) ) {
							$date = new \DateTime();
							$date->setTimestamp( (int) $values[ $field_name ] );
							return $date;
						}
					},
				);
				break;
				
			/* Date */
			case 'date':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'date', array_replace_recursive( array(
							'label' => __( 'Date', 'mwp-rules' ),
							'view_timezone' => get_option( 'timezone_string' ) ?: 'UTC',
							'input' => 'timestamp',
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL,
						), 
						$options ));
					},
					'saveValues' => function( &$values, $operation ) use ( $field_name ) {	
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \DateTime ) {
							$values[ $field_name ] = $values[ $field_name ]->getTimestamp();
						}
					},
					'getArg' => function( $values ) use ( $field_name ) {
						if ( isset( $values[ $field_name ] ) ) {
							$date = new \DateTime();
							$date->setTimestamp( (int) $values[ $field_name ] );
							return $date;
						}
					},
				);
				break;

			/* Time */
			case 'time':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$form->addField( $field_name, 'time', array_replace_recursive( array(
							'label' => __( 'Time', 'mwp-rules' ),
							'view_timezone' => get_option( 'timezone_string' ) ?: 'UTC',
							'input' => 'timestamp',
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : NULL,
						), 
						$options ));
					},
					'saveValues' => function( &$values, $operation ) use ( $field_name ) {	
						if ( isset( $values[ $field_name ] ) and $values[ $field_name ] instanceof \DateTime ) {
							$values[ $field_name ] = $values[ $field_name ]->getTimestamp();
						}
					},
					'getArg' => function( $values ) use ( $field_name ) {
						if ( isset( $values[ $field_name ] ) ) {
							$date = new \DateTime();
							$date->setTimestamp( (int) $values[ $field_name ] );
							return $date;
						}
					},
				);
				break;

			/* Individual User */
			case 'user':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Select a user by field value (id, slug, email, or login). i.e. "id: 1" or "login: administrator"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'User', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$user_string = $operation->replaceTokens( $values[$field_name], $arg_map );
						$pieces = explode( ':', $user_string );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id', 'slug', 'email', 'login' ) ) ) {
							return get_user_by( $field, $attribute ) ?: NULL;
						}
					},
				);
				break;
				
			/* Multiple Users */
			case 'users':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter each user selection on a new line identified by field value (id, slug, email, or login). i.e. "id: 1" or "login: administrator"', 'mwp-rules' ) . "<hr><strong>" . __( 'Example', 'mwp-rules' ) . ":</strong><pre>login: administrator&#10;id: 102</pre></div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Users', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$user_strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						$users = array();
						foreach( $user_strings as $user_string ) {
							$pieces = explode( ':', $user_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id', 'slug', 'email', 'login' ) ) ) {
								$users[] = get_user_by( $field, $attribute );
							}
						}
						
						return array_filter( $users );
					},
				);
				break;
				
			/* Individual Post */
			case 'post':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Select a post by field value (id). i.e. "id: 1"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Post', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id' ) ) ) {
							return get_post( $attribute );
						}
					},
				);
				break;
			
			/* Multiple Posts */
			case 'posts':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter each post selection on a new line identified by field value (id). i.e. "id: 1"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Posts', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$post_strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						$posts = array();
						foreach( $post_strings as $post_string ) {
							$pieces = explode( ':', $post_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id' ) ) ) {
								$posts[] = get_post( $attribute );
							}
						}
						
						return array_filter( $posts );
					},
				);
				break;
				
			/* Individual Comment */
			case 'comment':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Select a comment by field value (id). i.e. "id: 1"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Comment', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id' ) ) ) {
							return get_comment( $attribute );
						}
					},
				);
				break;
			
			/* Multiple Comments */
			case 'comments':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter each comment selection on a new line identified by field value (id). i.e. "id: 1"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Comments', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$comment_strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						$comments = array();
						foreach( $comment_strings as $comment_string ) {
							$pieces = explode( ':', $comment_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id' ) ) ) {
								$comments[] = get_comment( $attribute );
							}
						}
						
						return array_filter( $comments );
					},
				);
				break;
				
			/* Indexed Array */
			case 'array':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter values one per line.', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Values', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'Value1&#10;Value2', 'rows' => 6 ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						if ( ! isset( $values[ $field_name ] ) ) {
							return array();
						}

						return array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
					}
				);
				break;
				
			/* Keyed Array */
			case 'key_array':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter keyed values one per line, in the format of "key: value".', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Key/Value Pairs', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'key1: Value 1&#10;key2: Value 2', 'rows' => 6 ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$key_array = array();
						$strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						foreach( $strings as $string ) {
							if ( strpos( $string, ':' ) !== false ) {
								$pieces = explode( ':', $string );
								$key = trim( array_shift( $pieces ) );
								$value = trim( implode( ':', $pieces ) );
								if ( $key or strval( $key ) === '0' ) {
									$key_array[ $key ] = $value;
								}
							}
						}
						
						return $key_array;
					}
				);
				break;
				
			/* Meta Data */
			case 'meta_values':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter meta values one per line, in the format of "meta_key: meta_value".', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Meta Values', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'meta_key: meta_value', 'rows' => 6 ),
							'data' => isset( $values[ $field_name ] ) ? $values[ $field_name ] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$meta_values = array();
						$meta_strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						foreach( $meta_strings as $meta_string ) {
							if ( strpos( $meta_string, ':' ) !== false ) {
								$pieces = explode( ':', $meta_string );
								$key = trim( array_shift( $pieces ) );
								$value = trim( implode( ':', $pieces ) );
								if ( $key ) {
									$meta_values[ $key ] = $value;
								}
							}
						}
						
						return $meta_values;
					}
				);
				break;
				
			/* Individual Taxonomy */
			case 'taxonomy':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Select a taxonomy by field value (name). i.e. "name: category"', 'mwp-rules' ) . "</div>";
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Taxonomy', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'name: taxonomy_name' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'name' ) ) ) {
							return get_taxonomy( $attribute ) ?: null;
						}
					},
				);
				break;
			
			/* Multiple Taxonomies */
			case 'taxonomies':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter each taxonomy selection on a new line identified by field value (name).', 'mwp-rules' ) . "<hr><strong>" . __( 'Example', 'mwp-rules' ) . ":</strong><pre>name: category&#10;name: tags</pre>" . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Taxonomy Terms', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "name: taxonomy_one&#10;name: taxonomy_two" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						$taxonomies = array();
						foreach( $strings as $string ) {
							$pieces = explode( ':', $string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'name' ) ) ) {
								$taxonomies[] = get_taxonomy( $attribute );
							}
						}
						
						return array_filter( $taxonomies );
					},
				);
				break;

			/* Individual Term */
			case 'term':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Select a term by field value (id, slug, or name). When identifying a term by it\'s slug or name, you must also specify the taxonomy to get it from.', 'mwp-rules' ) . "<hr><strong>" . __( 'Example', 'mwp-rules' ) . ":</strong><pre>id: 1&#10;slug: taxonomy_name/term-slug&#10;name: taxonomy_name/Term Name</pre>" . "</div>";
						$form->addField( $field_name, 'text', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Taxonomy Term', 'mwp-rules' ),
							'attr' => array( 'placeholder' => 'id: 1' ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$pieces = explode( ':', $operation->replaceTokens( $values[$field_name], $arg_map ) );
						$field = trim( array_shift( $pieces ) );
						$attribute = trim( implode( ':', $pieces ) );
						if ( in_array( $field, array( 'id', 'slug', 'name' ) ) ) {
							if ( $field == 'id' ) {
								return get_term( (int) $attribute ) ?: null;
							}
							$more_pieces = explode( '/', $attribute );
							$taxonomy = array_shift( $more_pieces );
							$attribute = implode( '/', $more_pieces );
							return get_term_by( $field, $attribute, $taxonomy ) ?: null;
						}
					},
				);
				break;
			
			/* Multiple Terms */
			case 'terms':
			
				$config = array(
					'form' => function( $form, $values, $operation ) use ( $field_name, $options ) {
						$options['description'] = ( isset( $options['description'] ) ? $options['description'] : '' ) . "<div class='alert alert-info desc-info'>" . __( 'Enter each term selection on a new line identified by field value (id, slug, or name). When identifying a term by it\'s slug or name, you must also specify the taxonomy to get it from.', 'mwp-rules' ) . "<hr><strong>" . __( 'Example:', 'mwp-rules' ) . ":</strong><pre>id: 1&#10;slug: taxonomy_name/term-slug&#10;name: taxonomy_name/Term Name</pre>" . "</div>";
						$form->addField( $field_name, 'textarea', array_replace_recursive( array(
							'field_prefix' => is_callable( array( $operation, 'getTokenSelector' ) ) ? $operation->getTokenSelector() : null,
							'label' => __( 'Taxonomy Terms', 'mwp-rules' ),
							'attr' => array( 'placeholder' => "id: 1&#10;id: 2" ),
							'data' => isset( $values[$field_name] ) ? $values[$field_name] : '',
						),
						$options ));
					},
					'getArg' => function( $values, $arg_map, $operation ) use ( $field_name ) {
						$term_strings = array_map( 'trim', explode( "\n", $operation->replaceTokens( $values[ $field_name ], $arg_map ) ) );
						$terms = array();
						foreach( $term_strings as $term_string ) {
							$pieces = explode( ':', $term_string );
							$field = trim( array_shift( $pieces ) );
							$attribute = trim( implode( ':', $pieces ) );
							if ( in_array( $field, array( 'id', 'slug', 'name' ) ) ) {
								if ( $field == 'id' ) {
									$terms[] = get_term( (int) $attribute );
									continue;
								}
								$more_pieces = explode( '/', $attribute );
								$taxonomy = array_shift( $more_pieces );
								$attribute = implode( '/', $more_pieces );
								$terms[] = get_term_by( $field, $attribute, $taxonomy );
							}
						}
						
						return array_filter( $terms );
					},
				);
				break;
			
		}
		
		/**
		 * Allow custom presets to be used
		 *
		 * @param    array     $config        The existing configuration preset (if any)
		 * @param    string    $key           The key of the preset requested
		 * @param    string    $field_name    The name to use when creating form fields
		 * @param    array     $options       Customized options to use when creating the configuration
		 * @return   array
		 */
		return apply_filters( 'rules_config_preset', $config, $key, $field_name, $options );
	}

	/**
	 * Schedule An Action
	 *
	 * @param 	Action|Hook			$action			The action to schedule
	 * @param	int					$time			The timestamp of when the action is scheduled
	 * @param	array				$args			The arguments to send to the action
	 * @param	array				$event_args		The arguments from the event
	 * @param	string				$thread			The event thread to tie the action back to (for debugging)
	 * @param	string				$parentThread	The events parent thread to tie the action back to (for debugging)
	 * @param	string|NULL			$unique_key		A unique key to identify the action for later updating/removal
	 * @param	string|NULL			$iteration		Identifier for which rule loop execution an action is executed in
	 * @return	mixed								A message to log to the database if debugging is on
	 */
	public function scheduleAction( $action, $time, $args=[], $event_args=[], $thread=NULL, $parentThread=NULL, $unique_key=NULL, $iteration=NULL )
	{
		/**
		 * Delete any existing action with the same unique key
		 */
		if ( isset( $unique_key ) and trim( $unique_key ) != '' ) {
			foreach( ScheduledAction::loadWhere( array( 'schedule_unique_key=%s', trim( $unique_key ) ) ) as $existing ) {
				$existing->delete();
			}
		}
		
		$type_id = $action instanceof Action ? 'action_id' : 'custom_id';
		
		$scheduled_action                = new ScheduledAction;		
		$scheduled_action->time          = $time;
		$scheduled_action->$type_id      = $action->id();
		$scheduled_action->thread        = $thread;
		$scheduled_action->parent_thread = $parentThread;
		$scheduled_action->created       = time();
		$scheduled_action->unique_key    = trim( $unique_key );
		$scheduled_action->iteration     = $iteration;
		
		$db_args = array();
		foreach ( $args as $key => $arg ) {
			$db_args[ $key ] = $this->storeArg( $arg );
		}
		
		$db_event_args = array();
		foreach ( $event_args as $key => $arg ) {
			$db_event_args[ $key ] = $this->storeArg( $arg );
		}
		
		$scheduled_action->data = array(
			'args' => $db_args,
			'event_args' => $db_event_args,
		);
		
		$scheduled_action->save();
		
		return $scheduled_action;
	}
	
	/**
	 * Update the action runner task
	 *
	 * @return	Task|NULL
	 */
	public function updateActionRunner()
	{
		if ( $_next_action = ScheduledAction::getNextAction() ) {
			$tasks = Task::loadWhere( array( 'task_action=%s AND task_completed=0 AND task_fails<3 AND task_blog_id=%d', 'rules_action_runner', get_current_blog_id() ) );
			
			if ( $task = array_shift( $tasks ) ) {
				if ( ! $task->running ) {
					$task->next_start = $_next_action->time;
					$task->save();
				}
			} else {
				$task = Task::queueTask( array( 'action' => 'rules_action_runner', 'next_start' => $_next_action->time ) );
			}
			
			return $task;
		}
	}
	
	/**
	 * Create a token evaluator closure
	 *
	 * @param	GenericOperation		$operation			The operation for the token context
	 * @param	array					$event_args			The event arguments for the token context
	 * @return	closure
	 */
	public function createTokenEvaluator( $operation, $event_args )
	{
		$token_evaluator = function( $tokenized_key ) use ( $operation, $event_args ) {
			$token = Token::createFromResources( $tokenized_key, $operation->getResources( $event_args ) );
			try {
				return $token->getTokenValue();
			} catch( \ErrorException $e ) { }
			
			return NULL;
		};
		
		return $token_evaluator;
	}

	/**
	 * Prepare an argument for database storage
	 *
	 * Known objects are stored in a way that they can be easily reconstructed
	 * into original form. All other objects will be cast into stdClass when restored.
	 *
	 * @param 	mixed		$arg		The argument to store
	 * @return	mixed					An argument which can be json encoded
	 */
	public function storeArg( $arg )
	{
		/* Walk through arrays recursively to store arguments */
		if ( is_array( $arg ) ) {
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg ) {
				$arg_array[ $k ] = $this->storeArg( $_arg );
			}
			
			return $arg_array;
		}
		
		if ( ! is_object( $arg ) ) {
			return $arg;
		}
		
		if ( $object_store = apply_filters( 'rules_store_object', NULL, $arg ) ) {
			return $object_store;
		}
		
		$data = NULL;
		$arg_class = get_class( $arg );		
		$mapped = $this->getClassMappings( $arg_class );
		
		if ( $mapped ) {
			if ( isset( $mapped['reference'] ) and is_callable( $mapped['reference'] ) ) {
				$data = call_user_func( $mapped['reference'], $arg );
			}
		}
		
		$object_store = ( $data !== NULL ) ? array( '_obj_class' => $arg_class, 'data' => $data ) : array( '_obj_class' => $arg_class, 'data' => $data, 'saved_data' => (array) $arg );
		
		return $object_store;
	}

	/**
	 * Restore an argument from database storage
	 *
	 * @param 	object		$arg		The argument to restore
	 * @return	mixed					The restored argument
	 */
	public function restoreArg( $arg )
	{
		if ( ! is_array( $arg ) ) {
			return $arg;
		}
		
		/* If the array is not a stored object reference, walk through elements recursively to restore values */
		if ( ! isset ( $arg['_obj_class'] ) ) {
			$arg_array = array();
			
			foreach ( $arg as $k => $_arg ) {
				$arg_array[ $k ] = $this->restoreArg( $_arg );
			}

			return $arg_array;
		}
		
		if ( $object = apply_filters( 'rules_restore_object', NULL, $arg ) ) {
			return $object;
		}
		
		$typeMap = array(
			'object' => 'object',
			'integer' => 'int',
			'double' => 'float',
			'boolean' => 'bool',
			'string' => 'string',
			'array' => 'array',
			'NULL' => '',
		);
		
		$mapped = $this->getClassMappings( $arg['_obj_class'] );
		if ( $mapped ) {
			if ( isset( $mapped['loader'] ) and is_callable( $mapped['loader'] ) ) {
				if ( isset( $arg['data'] ) ) {
					if ( is_array( $arg['data'] ) ) {
						list( $val, $key ) = $arg['data'];
					} else {
						$val = $arg['data'];
						$key = NULL;
					}
					
					return call_user_func( $mapped['loader'], $val, $typeMap[gettype($val)], $key );
				}
			}
		}
		
	}
	
	/**
	 * Get the custom logs used in an export
	 *
	 * @param	array			$data			The export data
	 * @return	array
	 */
	public function getLogsFromExportData( $data, $logs=[] )
	{
		/* Look for rules that use log events */
		if ( isset( $data['rules'] ) and ! empty( $data['rules'] ) ) {
			foreach( $data['rules'] as $rule ) {
				$_type = $rule['data']['rule_event_type'];
				$_hook = $rule['data']['rule_event_hook'];
				
				if ( $_type == 'action' and substr( $_hook, 0, strlen( 'rules_log_' ) ) === 'rules_log_' ) {
					if ( ! isset( $hooks[ $_type . ':' . $_hook ] ) ) {
						$pieces = explode( '_', $_hook );
						if ( isset( $pieces[2] ) and $log_uuid = $pieces[2] ) {
							if ( ! isset( $logs[ $log_uuid ] ) ) {
								if ( $_logs = CustomLog::loadWhere( array( 'custom_log_uuid=%s', $log_uuid ) ) ) {
									$logs[ $log_uuid ] = array_shift( $_logs );
								}
							}
						}
					}
				}
				
				if ( isset( $rule['actions'] ) and ! empty( $rule['actions'] ) ) {
					$logs = $this->getLogsFromExportData( $rule, $logs );
				}
			}
		}
		
		/* Look for actions that reference logs */
		if ( isset( $data['actions'] ) and ! empty( $data['actions'] ) ) {
			foreach( $data['actions'] as $action ) {				
				if ( substr( $action['data']['action_key'], 0, strlen( 'rules_log_' ) ) === 'rules_log_' ) {
					$pieces = explode( '_', $action['data']['action_key'] );
					if ( isset( $pieces[2] ) and $log_uuid = $pieces[2] ) {
						if ( ! isset( $logs[ $log_uuid ] ) ) {
							if ( $_logs = CustomLog::loadWhere( array( 'custom_log_uuid=%s', $log_uuid ) ) ) {
								$logs[ $log_uuid ] = array_shift( $_logs );
							}
						}
					}
				}
			}
		}
		
		foreach( array( 'apps', 'bundles', 'hooks' ) as $container_type ) {
			if ( isset( $data[ $container_type ] ) and ! empty( $data[ $container_type ] ) ) {
				foreach( $data[ $container_type ] as $container ) {
					$logs = $this->getLogsFromExportData( $container, $logs );
				}
			}
		}
		
		return $logs;
	}
	
	/**
	 * Get the custom hooks used in an export
	 *
	 * @param	array		$data			The export data
	 * @return	array
	 */
	public function getHooksFromExportData( $data, $hooks=[] )
	{
		/* Look for rules that use custom hooks */
		if ( isset( $data['rules'] ) and ! empty( $data['rules'] ) ) {
			foreach( $data['rules'] as $rule ) {
				$_type = $rule['data']['rule_event_type'];
				$_hook = $rule['data']['rule_event_hook'];
				
				/* Only load custom hooks that we haven't fetched already */
				if ( ! isset( $hooks[ $_type . ':' . $_hook ] ) ) {
					$where = ( $_type == 'action' ? array( 'hook_type IN (%s, %s) AND hook_hook=%s', 'custom', 'action', $_hook ) : array( 'hook_type=%s AND hook_hook=%s', 'filter', $_hook ) );
					if ( $_hooks = Hook::loadWhere( $where ) ) {
						$hooks[ $_type . ':' . $_hook ] = array_shift( $_hooks );
					}
				}
				
				if ( isset( $rule['actions'] ) and ! empty( $rule['actions'] ) ) {
					$hooks = $this->getHooksFromExportData( $rule, $hooks );
				}
			}
		}
		
		/* Look for actions that reference custom hooks */
		if ( isset( $data['actions'] ) and ! empty( $data['actions'] ) ) {
			foreach( $data['actions'] as $action ) {				
				if ( substr( $action['data']['action_key'], 0, strlen( 'rules/action/' ) ) === 'rules/action/' ) {
					if ( ! isset( $hooks[ 'action:' . $action['data']['action_key'] ] ) ) {
						if ( $_hooks = Hook::loadWhere( array( 'hook_hook=%s', $action['data']['action_key'] ) ) ) {
							$hooks[ 'action:' . $action['data']['action_key'] ] = array_shift( $_hooks );
						}
					}
				}
			}
		}		
		
		foreach( array( 'apps', 'bundles' ) as $container_type ) {
			if ( isset( $data[ $container_type ] ) and ! empty( $data[ $container_type ] ) ) {
				foreach( $data[ $container_type ] as $container ) {
					$hooks = $this->getHooksFromExportData( $container, $hooks );
				}
			}
		}
		
		return $hooks;
	}
	
	/**
	 * Create a package of rule configurations
	 *
	 * @param	array|object		$models			An array of models, or a single model to export
	 * @return	array
	 */
	public function createPackage( $models )
	{
		if ( ! is_array( $models ) ) { 
			$models = array( $models );
		}
		
		$package = array(
			'rules_version' => $this->getVersion(),
			'hooks' => [],
			'logs' => [],
		);
		
		foreach( $models as $model ) {
			if ( $model instanceof ExportableRecord ) {
				if ( $model instanceof App ) {
					$package['apps'][] = $model->getExportData();
				}
				if ( $model instanceof Bundle ) {
					$package['bundles'][] = $model->getExportData();
				}
				if ( $model instanceof Rule ) {
					$package['rules'][] = $model->getExportData();
				}
				if ( $model instanceof Hook ) {
					$package['hooks'][] = $model->getExportData();
				}
				if ( $model instanceof CustomLog ) {
					$package['logs'][] = $model->getExportData();
				}
			}
		}
		
		$package['hooks'] = array_merge( $package['hooks'], array_map( function( $hook ) { return $hook->getExportData(); }, array_values( $this->getHooksFromExportData( $package ) ) ) );
		$package['logs'] = array_merge( $package['logs'], array_map( function( $log ) { return $log->getExportData(); }, array_values( $this->getLogsFromExportData( $package ) ) ) );
		
		if ( empty( $package['hooks'] ) ) {
			unset( $package['hooks'] );
		}
		
		if ( empty( $package['logs'] ) ) {
			unset( $package['logs'] );
		}
		
		return $package;
	}
	
	/**
	 * Import a package
	 *
	 * @param	array			$package_data			The package data to import
	 * @throws  \ErrorException
	 * @return	array
	 */
	public function importPackage( $package_data )
	{
		$package = new Package( $package_data );		
		return $package->importAll();
	}
	
	/**
	 * Recursion Protection
	 */
	public $logLocked = FALSE;
	
	/**
	 * Create a Rules Log
	 *
	 * @param	\IPS\rules\Event		$event		The event associated with the log
	 * @param	\IPS\rules\Rule|NULL	$rule		The rule associated with the log
	 * @param	\IPS\rules\Action		$operation	The condition or action associated with the log
	 * @param	mixed					$result		The value returned by the operation or log event
	 * @param	string					$message	The reason for the log
	 * @param	int						$error		The error code, or zero indicating a debug log
	 * @return 	void
	 */
	public function rulesLog( $event, $rule, $operation, $result, $message='', $error=0 )
	{
		if ( ! $this->logLocked )
		{
			$this->logLocked = TRUE;
			
			$log 				= new RuleLog;
			$log->thread 		= is_object( $event ) 		? $event->thread			: NULL;
			$log->parent		= is_object( $event )		? $event->parentThread		: NULL;
			$log->event_type    = is_object( $event )       ? $event->type              : NULL;
			$log->event_hook	= is_object( $event ) 		? $event->hook				: NULL;
			$log->rule_id		= is_object( $rule )		? $rule->id					: 0;
			$log->rule_parent 	= is_object ( $rule ) 		? $rule->parent_id			: 0; 
			$log->op_id			= is_object( $operation ) 	? $operation->id			: 0;
			$log->type 			= is_object( $operation ) 	? get_class( $operation )	: NULL;
			$log->result 		= json_encode( $result );
			$log->message 		= $message;
			$log->error			= $error;
			$log->iteration     = is_object( $rule )        ? $rule->activeIteration[ $event->thread ] : NULL;
			$log->time 			= time();
			
			$log->save();
			
			$this->logLocked = FALSE;

			// Link sub rule logs to this parent to be able to better track logs created in a sub-rule loop context
			if ( $rule ) {
				foreach( RuleLog::loadWhere([ 'thread=%s AND op_id=0 AND rule_parent=%d AND parent_log is NULL', $event->thread, $rule->id() ]) as $sublog ) {
					$sublog->parent_log = $log->id();
					$sublog->save();
				}
			}
		}
	}
	
	/**
	 * Shutdown Rules: Execute queued actions
	 *
	 * @return	void
	 */ 
	public function shutDown()
	{
		if ( ! $this->shuttingDown )
		{
			/* No more actions should be queued from this point forward */
			$this->shuttingDown = TRUE;
			
			/**
			 * Run end of page queued actions
			 */
			while( $queued = array_shift( $this->actionQueue ) )
			{
				$event = $queued[ 'event' ];
				$action = array( $queued[ 'action' ] );
				
				$event->executeDeferred( $action );
			}
		}
	}
	
	/**
	 * Uninstall routine
	 *
	 * @return	void
	 */
	public function uninstall()
	{
		// Remove tables created for custom logs
		foreach( CustomLog::loadWhere('1') as $log ) {
			$log->delete();
		}
		
		// Normal uninstall of tables, settings, etc
		parent::uninstall();
	}
}

register_shutdown_function( function() { 
	\MWP\Rules\Plugin::instance()->shutDown(); 
});
