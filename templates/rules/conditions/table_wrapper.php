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
 * @param	MWP\Rules\Rules				$rule				The associated rule
 * @param	ActiveRecordTable			$table				The conditions table
 * @param	ActiveRecordController		$controller			The conditions controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="operation-actions">
	<?php echo $controller->getActionsHtml() ?>
</div>

<div class="operation-table" <?php echo $table->getViewModelAttr() ?>>
	<?php echo $table->getDisplay() ?>
</div>