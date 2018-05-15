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

$action = \MWP\Rules\Action::load( $item['action_id'] );
$definition = $action->definition();
$controller = $table->getController();

?>

<li class="operation-row action-row" id="<?php echo $action->id ?>">
	<div class="operation-details row-handle row">
		<div class="col-sm-8">
			<strong class="text-info" style="font-size:1.2em"><?php echo $action->title ?> </strong> 
			<span style="margin-left: 15px" class="label label-<?php echo $action->enabled ? 'success' : 'danger' ?> rules-pointer" data-rules-enabled-toggle="action" data-rules-id="<?php echo $action->id() ?>">
				<?php echo $action->enabled ? 'ENABLED' : 'DISABLED' ?>
			</span>
			<p>
				<i class="glyphicon glyphicon-triangle-right"></i> Using: <span class="text-success"><?php echo $definition ? $definition->title : 'Unregistered action' ?></span> 
				<?php if ( $action->schedule_mode == 0 ) : ?>
					<span title="This action will be executed immediately during the event" class="label label-default"><i class="glyphicon glyphicon-time"></i> Immediately</span>
				<?php endif ?>
				<?php if ( in_array( $action->schedule_mode, array( 2, 3, 4 ) ) ) : ?>
					<span title="This action will be executed at a scheduled time" class="label label-warning"><i class="glyphicon glyphicon-time"></i> Scheduled</span>
				<?php endif ?>
			</p>
		</div>
		<div class="col-sm-4">
			<div class="pull-right draggable-handle" style="margin:2px 0 0 5px;">
				<i class="glyphicon glyphicon-menu-hamburger" style="padding: 5px;"></i>
			</div>
			<div class="pull-right operation-row-actions">
				<?php echo $this->getTemplateContent( 'views/management/records/row_actions', array( 
					'controller' => $controller,
					'record' => $action,
					'table' => $table,
					'actions' => $action->getControllerActions(),
					'default_row_actions' => '',
				)); ?>
			</div>
		</div>
	</div>
</li>