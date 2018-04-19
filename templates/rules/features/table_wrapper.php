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
 * @param	MWP\Rules\App				$app				The associated app
 * @param	ActiveRecordTable			$table				The bundles table
 * @param	ActiveRecordController		$controller			The bundles controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="bundles-actions">
	<?php echo $controller->getActionsHtml(); ?>
</div>

<div class="bundles-table" <?php echo $table->getViewModelAttr() ?>>
	<?php echo $table->getDisplay() ?>
</div>