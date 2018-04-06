<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 19, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Hook				$hook				The associated hook
 * @param	ActiveRecordTable			$table				The arguments table
 * @param	ActiveRecordController		$controller			The arguments controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$actions = array_replace_recursive( $controller->getActions(), isset( $actions ) ? $actions : array() );
?>

<div class="arguments-actions">
	<?php echo $controller->getActionsHtml( $actions ); ?>
</div>

<div class="arguments-table" <?php echo $table->getViewModelAttr() ?>>
	<?php echo $table->getDisplay() ?>
</div>