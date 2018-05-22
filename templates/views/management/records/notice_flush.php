<?php
/**
 * Plugin HTML Template
 *
 * Created:  May 15, 2018
 *
 * @package  MWP Application Framework
 * @author   Kevin Carwile
 * @since    1.0.1
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	ActiveRecord		$record			The log being flushed
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$recordClass = $record->getRecordClass();

?>
<div class="panel panel-warning">
  <div class="panel-heading">
    <h3 class="panel-title"><?php _e( 'Alert', 'mwp-framework' ) ?></h3>
  </div>
  <div class="panel-body">
	<p class="text-center"><?php _e( 'You are about to flush this log. <br><br> This will delete all log entries. Are you sure you want to do this?', 'mwp-framework' ) ?></p>
	<h3 class="text-center text-danger"><?php _e( 'Records to Flush' ) ?>: <?php echo $recordClass::countWhere('1') ?></h3>
  </div>
</div>
