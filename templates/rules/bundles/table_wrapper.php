<?php
/**
 * Plugin HTML Template
 *
 * Created:  May 19, 2021
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\App				$app				The associated app
 * @param	ActiveRecordTable			$table				The table
 * @param	ActiveRecordController		$controller			The controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="subrule-actions">
	<?php echo $controller->getActionsHtml( isset( $actions ) ? $actions : NULL ) ?>
</div>

<div class="subrule-table" <?php echo $table->getViewModelAttr() ?>>
	<?php echo $table->getDisplay() ?>
</div>