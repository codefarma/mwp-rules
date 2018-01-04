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
 * @param	bool						$show_buttons		Show the actions buttons or not
 * @param	MWP\Rules\Rules				$rule				The associated rule
 * @param	ActiveRecordTable			$table				The table
 * @param	ActiveRecordController		$controller			The controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<ol>
	<?php if ( $table->has_items() ) : ?>
		<?php $table->display_rows() ?>
	<?php else: ?>
		<li>No sub-rules.</li>
	<?php endif ?>
</ol>