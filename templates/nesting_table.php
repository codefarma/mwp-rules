<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 28, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'nesting_table', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	Wordpress\Helpers\ActiveRecordTable		$table		The table to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

list( $columns, $hidden, $sortable, $primary ) = $table->get_column_info();

?>

<div class="table">
	<?php if ( $table->has_items() ) : ?>
		<?php foreach ( $table->items as $item ) : ?>
			<div class="row">
				<?php foreach( $columns as $name => $title ) : ?>
					<div class="col">
						<?php echo $table->column_default( $item, $name ) ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach ?>
	<?php else: ?>
		No Items
	<?php endif ?>
</div>

