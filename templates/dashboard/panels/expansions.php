<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 12, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    1.0.0
 *
 * @param	string		$controller			The dashboard controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$plugin = Rules\Plugin::instance();

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<a href="https://www.codefarma.com/products/rules-expansions/" target="_blank" class="btn btn-success btn-xs pull-right">Get Expansions</a> 
	<h3 class="panel-title">Expansion Packs</h3>
  </div>
  <div class="panel-body">
	No expansions currently installed.
  </div>
</div>

