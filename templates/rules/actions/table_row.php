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
 * @param	ActiveRecordTable		$table			The table
 * @param	array					$item			The row item
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$action = \MWP\Rules\Action::load( $item['action_id'] );
$definition = $action->definition();
$controller = $table->getController();

?>

<div class="operation-row action-row">
	<div class="pull-right operation-row-actions">
		<?php foreach ( $action->getControllerActions() as $_action ) : ?>
			<a <?php if ( isset( $_action['attr'] ) ) { foreach( $_action['attr'] as $k => $v ) { if ( is_array( $v ) ) { $v = json_encode( $v ); } printf( '%s="%s" ', $k, esc_attr( $v ) ); } }	?> 
				href="<?php echo $controller->getUrl( isset( $_action['params'] ) ? $_action['params'] : array() ) ?>
				">
				<?php if ( isset( $_action['icon'] ) ) : ?>
					<i class="<?php echo $_action['icon'] ?>"></i>
				<?php endif ?>
				<?php echo $_action['title'] ?>
			</a>
		<?php endforeach ?>
	</div>
	
	<strong style="font-size:1.2em"><?php echo $definition ? $definition->title : 'Unregistered action' ?></strong> 
	<?php if ( ! $action->enabled ) : ?>
		<span class="label label-danger">Disabled</span>
	<?php endif ?>
	<p>
		<?php echo $action->title ?> 
		<?php if ( in_array( $action->schedule_mode, array( 2, 3, 4 ) ) ) : ?>
			<span title="This action will be executed at a scheduled time" class="label label-warning"><i class="glyphicon glyphicon-time"></i> Scheduled</span>
		<?php endif ?>
	</p>
</div>