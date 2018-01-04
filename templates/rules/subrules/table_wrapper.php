<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 19, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Rules				$rule				The associated rule
 * @param	ActiveRecordTable			$table				The table
 * @param	ActiveRecordController		$controller			The controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$actions = $controller->getActions();
$actions['new']['params']['parent_id'] = $rule->id;
$actions['new']['title'] = __( 'Add New Subrule', 'mwp-rules' );

?>

<div class="subrule-actions">
	<?php echo $controller->getActionsHtml( $actions ) ?>
</div>

<div class="subrule-table" <?php echo $table->getViewModelAttr() ?>>
	<?php echo $table->getDisplay() ?>
</div>