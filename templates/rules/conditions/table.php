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
 * @param	ActiveRecordTable			$table		The table
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<ol data-bind="nestableRecords: { 
	class: <?php echo esc_attr( json_encode( $table->activeRecordClass ) ) ?>,
	options: { 
		handle: 'div', 
		items: 'li', 
		toleranceElement: '> div'
	} 
}">
	<?php if ( $table->has_items() ) : ?>
		<?php $table->display_rows() ?>
	<?php else: ?>
		<li>No conditions.</li>
	<?php endif ?>
</ol>