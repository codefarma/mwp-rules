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
 * @param	ActiveRecordTable		$table			The table
 * @param	array					$item			The row item
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$argument = Rules\Argument::load( $item['argument_id'] );
$controller = $table->getController();
?>

<li class="operation-row action-row" id="<?php echo $argument->id ?>">
	<div class="operation-details row-handle">
		<div class="pull-right operation-row-actions">
			<?php foreach ( $argument->getControllerActions() as $_action ) : ?>
				<a <?php if ( isset( $_action['attr'] ) ) { foreach( $_action['attr'] as $k => $v ) { if ( is_array( $v ) ) { $v = json_encode( $v ); } printf( '%s="%s" ', $k, esc_attr( $v ) ); } }	?> 
					href="<?php echo $controller->getUrl( isset( $_action['params'] ) ? $_action['params'] : array() ) ?>
					">
					<?php if ( isset( $_action['icon'] ) ) : ?>
						<i class="<?php echo $_action['icon'] ?>"></i>
					<?php endif ?>
					<?php echo isset( $_action['title'] ) ? $_action['title'] : '' ?>
				</a>
			<?php endforeach ?>
		</div>
		
		<strong style="font-size:1.2em"><?php echo $argument->title ?></strong> 
		<p>
			<code>$<?php echo $argument->varname ?></code> (<?php echo $argument->type ?>)
		</p>
	</div>
</li>