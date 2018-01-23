<?php

if ( defined( 'DIR_TESTDATA' ) ) {
	if ( ! class_exists( 'ModernWordpressFramework' ) ) {
		$plugin_dir = dirname( dirname( __FILE__ ) );
		if ( ! file_exists( $plugin_dir . '/modern-framework/plugin.php' ) ) {
			die( 'Error: Modern framework is required to run tests on this plugin.' );
		}
		
		require_once $plugin_dir . '/modern-framework/plugin.php';
	}
}

require_once 'plugin.php';