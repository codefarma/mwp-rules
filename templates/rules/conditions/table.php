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

<ol data-bind="nestableRecords: { 
	class: '<?php echo $table->activeRecordClass ?>',
	options: { 
		handle: 'div', 
		items: 'li', 
		toleranceElement: '> div', 
		relocate: conditionRelocated 
	} 
}">
	<?php if ( $table->has_items() ) : ?>
		<?php $table->display_rows() ?>
	<?php else: ?>
		<li>No conditions.</li>
	<?php endif ?>
</ol>