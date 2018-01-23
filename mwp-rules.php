<?php

/* Load framework for tests */
if ( defined( 'DIR_TESTDATA' ) ) {
	$plugin_dir = dirname( dirname( __FILE__ ) );
	if ( ! file_exists( $plugin_dir . '/modern-framework/plugin.php' ) ) {
		die( 'Error: Modern framework must be present in ' . $plugin_dir . '/modern-framework to run tests on this plugin.' );
	}
	
	require_once $plugin_dir . '/modern-framework/plugin.php';
}

require_once 'plugin.php';