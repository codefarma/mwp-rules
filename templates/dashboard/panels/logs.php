<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 12, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    1.0.0
 *
 * @param	string		$controller			The dashboard controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$recent_limit = 25;

$plugin = Rules\Plugin::instance();
$logs = Rules\CustomLog::loadWhere('1');

$logsController = $plugin->getLogsController();
$systemLogTable = $logsController->createDisplayTable();

?>

<div class="automation-logs panel panel-info">
  <div class="panel-heading">
	<a href="<?php echo $plugin->getCustomLogsController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Logs</a>
	<h3 class="panel-title">
		Automation Logs <small style="opacity: 0.7; font-size:0.75em; margin-left:20px;">Logs can provide insight into the performance of your automations.</small>
	</h3>
  </div>
  <div class="panel-body">
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#system" aria-controls="system" role="tab" data-toggle="tab">System</a></li>
		<?php foreach( $logs as $log ) : ?>
		<li role="presentation"><a href="#log_<?php echo $log->id() ?>" aria-controls="log_<?php echo $log->id() ?>" role="tab" data-toggle="tab"><?php echo esc_html( $log->title ) ?></a></li>
		<?php endforeach; ?>
    </ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="system">
			<div class="table-container">
			<?php 
				$table = $plugin->getLogsController()->createDisplayTable([
					'displayTopNavigation' => false,
					'displayBottomNavigation' => false,
					'perPage' => $recent_limit,
					'bulkActions' => [],
					'sortBy' => 'id',
					'sortOrder' => 'DESC',
				]);
				$table->prepare_items( array( 'error>0 OR ( op_id=0 AND rule_parent=0 )' ) );
				echo $table->getDisplay();
			?>
			</div>
			<div class="table-controller-actions">
				<p class="text-info">
					<?php $total = $table->_pagination_args['total_items']; ?>
					<a href="<?php echo $plugin->getLogsController()->getUrl() ?>" class="btn btn-<?php echo $total >= $recent_limit ? "primary" : "default" ?> btn-sm pull-right"><?php _e( 'View All', 'mwp-rules' ) ?></a>
					<?php if ( $total ) : ?>
						<?php _e( 'Showing the most recent', 'mwp-rules' ) ?> 
						<strong><?php echo $recent_limit <= $total ? $recent_limit : $total; ?></strong> 
						<?php _e( 'logs of', 'mwp-rules' ) ?> 
						<strong><?php echo $total ?></strong> 
						<?php _e( 'total', 'mwp-rules' ) ?>.
					<?php endif; ?>
				</p>
			</div>
		</div>
		<?php foreach( $logs as $log ) : ?>
		<div role="tabpanel" class="tab-pane" id="log_<?php echo $log->id() ?>">
			<div class="table-container">
			<?php 
				$table = $log->getRecordController()->createDisplayTable([
					'displayTopNavigation' => false,
					'displayBottomNavigation' => false,
					'perPage' => $recent_limit,
					'bulkActions' => [],
					'sortBy' => 'entry_id',
					'sortOrder' => 'DESC',
				]);
				$table->prepare_items(array('1'));
				echo $table->getDisplay();
			?>
			</div>
			<div class="table-controller-actions">
				<p class="text-info">
					<?php $total = $table->_pagination_args['total_items']; ?>
					<a href="<?php echo $log->getRecordController()->getUrl() ?>" class="btn btn-<?php echo $total >= $recent_limit ? "primary" : "default" ?> btn-sm pull-right"><?php _e( 'View All', 'mwp-rules' ) ?></a>
					<?php if ( $total ) : ?>
						<?php _e( 'Showing the most recent', 'mwp-rules' ) ?> 
						<strong><?php echo $recent_limit <= $total ? $recent_limit : $total; ?></strong> 
						<?php _e( 'logs of', 'mwp-rules' ) ?> 
						<strong><?php echo $total ?></strong> 
						<?php _e( 'total', 'mwp-rules' ) ?>.
					<?php endif; ?>
				</p>
			</div>
		</div>
		<?php endforeach; ?>
	</div>	
  </div>
</div>
