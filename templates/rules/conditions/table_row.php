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
			<?php echo $this->getTemplateContent( 'views/management/records/row_actions', array( 
				'controller' => $controller,
				'record' => $condition,
				'table' => $table,
				'actions' => $condition->getControllerActions(),
				'default_row_actions' => '',
			)); ?>
		</div>
		<?php if ( $condition->not ) : ?>
			<span style="vertical-align: 2px" title="This condition evaluates TRUE if the condition is NOT MET" class="label label-warning">NOT</span> 
		<?php endif ?>
		<strong class="text-info" style="font-size:1.2em"><?php echo $condition->title ?> </strong> 
		<?php if ( count( $subconditions ) ) : ?>
			<span class="label label-info"><?php if ( $condition->compareMode() == 'and' ) : ?>AND ALL SUBCONDITIONS<?php else: ?>OR ANY SUBCONDITION<?php endif ?></span>
		<?php endif ?>
		<span style="margin-left: 15px; vertical-align: 2px;" class="label label-<?php echo $condition->enabled ? 'success' : 'danger' ?> rules-pointer" data-rules-enabled-toggle="condition" data-rules-id="<?php echo $condition->id() ?>">
			<?php echo $condition->enabled ? 'ENABLED' : 'DISABLED' ?>
		</span>
		<p>
			<i class="glyphicon glyphicon-triangle-right"></i> Using: <span class="text-success"><?php echo $definition ? $definition->title : 'Unregistered condition' ?></span> 
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
