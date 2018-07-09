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

$systemLogTable = $plugin->getLogsController()->createDisplayTable([
	'displayTopNavigation' => false,
	'displayBottomNavigation' => false,
	'perPage' => $recent_limit,
	'bulkActions' => [],
	'sortable' => [],
	'searchable' => [],
	'sortBy' => 'id',
	'sortOrder' => 'DESC',
	'hardFilters' => [ array( 'error>0 OR ( op_id=0 AND rule_parent=0 )' ) ],
]);
$systemLogTable->prepare_items();

$custom_logs = array_map( function( $log ) use ( $recent_limit ) {
	$table = $log->getRecordController()->createDisplayTable([
		'displayTopNavigation' => false,
		'displayBottomNavigation' => false,
		'perPage' => $recent_limit,
		'bulkActions' => [],
		'sortable' => [],
		'searchable' => [],
		'sortBy' => 'entry_id',
		'sortOrder' => 'DESC',
	]);
	$table->prepare_items();
	
	return array(
		'record' => $log,
		'table' => $table,
	);
}, $logs );

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
		<?php foreach( array_values( $custom_logs ) as $i => $log ) : ?>
		<li role="presentation" class="<?php echo $i == 0 ? 'active' : '' ?>">
			<a href="#log_<?php echo $log['record']->id() ?>" aria-controls="log_<?php echo $log['record']->id() ?>" role="tab" data-toggle="tab">
				<?php echo esc_html( $log['record']->title ) ?> (<?php echo $log['table']->_pagination_args['total_items'] ?>)
			</a>
		</li>
		<?php endforeach; ?>
		<li role="presentation" class="<?php echo count( $custom_logs ) == 0 ? 'active' : '' ?>">
			<a href="#system" aria-controls="system" role="tab" data-toggle="tab">
				System (<?php echo $systemLogTable->_pagination_args['total_items']; ?>)
			</a>
		</li>
    </ul>
	<div class="tab-content">
		<?php foreach( array_values( $custom_logs ) as $i => $log ) : ?>
		<div role="tabpanel" class="tab-pane <?php echo $i == 0 ? 'active' : '' ?>" id="log_<?php echo $log['record']->id() ?>">
			<div class="table-container">
			<?php 
				echo $log['table']->getDisplay();
			?>
			</div>
			<div class="table-controller-actions">
				<p class="text-info">
					<?php $total = $log['table']->_pagination_args['total_items']; ?>
					<a href="<?php echo $log['record']->getRecordController()->getUrl() ?>" class="btn btn-<?php echo $total >= $recent_limit ? "primary" : "default" ?> btn-sm pull-right"><?php _e( 'View More', 'mwp-rules' ) ?></a>
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
		<div role="tabpanel" class="tab-pane <?php echo count( $logs ) == 0 ? 'active' : '' ?>" id="system">
			<div class="table-container">
			<?php 
				echo $systemLogTable->getDisplay();
			?>
			</div>
			<div class="table-controller-actions">
				<p class="text-info">
					<?php $total = $systemLogTable->_pagination_args['total_items']; ?>
					<a href="<?php echo $plugin->getLogsController()->getUrl() ?>" class="btn btn-<?php echo $total >= $recent_limit ? "primary" : "default" ?> btn-sm pull-right"><?php _e( 'View More', 'mwp-rules' ) ?></a>
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
	</div>	
  </div>
</div>
