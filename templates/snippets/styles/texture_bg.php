<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 27, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

?>

<style>
html {
	background-image: url(<?php echo Rules\Plugin::instance()->fileUrl('assets/img/gray-texture-bg.jpg') ?>); 
	background-size: cover;
	background-attachment: fixed;
}
body {
	background-color: transparent;
}
@media screen and (max-width:782px) {
	.wrap {
		padding-left: 10px !important;
	}
}
</style>