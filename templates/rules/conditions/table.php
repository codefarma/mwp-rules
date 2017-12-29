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
 * @param	ActiveRecordTable			$table		The table
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="operation-rows conditions-rows <?php echo implode( ' ', $table->get_table_classes() ); ?>">
	<?php if ( $table->has_items() ) : ?>
		<?php $table->display_rows() ?>
	<?php else: ?>
		No conditions.
	<?php endif ?>
</div>