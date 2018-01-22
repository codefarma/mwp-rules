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

$condition = \MWP\Rules\Condition::load( $item['condition_id'] );
$definition = $condition->definition();
$subconditions = $condition->children();
$controller = $table->getController();

?>

<li class="operation-row condition-row" id="condition_<?php echo $condition->id ?>">
	<div class="operation-details row-handle">
		<div class="pull-right operation-row-actions">
			<?php foreach ( $condition->getControllerActions() as $action ) : ?>
				<a <?php if ( isset( $action['attr'] ) ) { foreach( $action['attr'] as $k => $v ) { if ( is_array( $v ) ) { $v = json_encode( $v ); } printf( '%s="%s" ', $k, esc_attr( $v ) ); } }	?> 
					href="<?php echo $controller->getUrl( isset( $action['params'] ) ? $action['params'] : array() ) ?>
					">
					<?php if ( isset( $action['icon'] ) ) : ?>
						<i class="<?php echo $action['icon'] ?>"></i>
					<?php endif ?>
					<?php echo $action['title'] ?>
				</a>
			<?php endforeach ?>
		</div>
		<strong style="font-size:1.2em"><?php echo $definition ? $definition->title : 'Unregistered condition' ?></strong> 
		<?php if ( count( $subconditions ) ) : ?>
			<span class="label label-info"><?php if ( $condition->compareMode() == 'and' ) : ?>AND ALL SUBCONDITIONS<?php else: ?>OR ANY SUBCONDITION<?php endif ?></span>
		<?php endif ?>
		<?php if ( ! $condition->enabled ) : ?>
			<span class="label label-danger">Disabled</span>
		<?php endif ?>
		<p>
			<?php echo $condition->title ?> 
			<?php if ( $condition->not ) : ?>
				<span title="This condition evaluates TRUE if the condition is NOT MET" class="label label-warning">NOT</span>
			<?php endif ?>
		</p>
	</div>
	<?php if ( $subconditions ) : ?>
		<ol>
		<?php
			$sub_table = $controller->createDisplayTable();
			$sub_table->prepare_items( array( 'condition_rule_id=%d AND condition_parent_id=%d', $condition->rule_id, $condition->id ) );
			echo $sub_table->display_rows();
		?>
		</ol>
	<?php endif ?>
</li>
