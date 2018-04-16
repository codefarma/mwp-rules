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

$plugin = Rules\Plugin::instance();
$features = Rules\Feature::loadWhere('feature_app_id=0');

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<a href="<?php echo $plugin->getFeaturesController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Features</a>
	<h3 class="panel-title">
		Your Features
	</h3>
  </div>
  <div class="panel-body">
	<?php if ( count( $features ) ) : ?>
		<table class="table">
		  <thead>
			<tr>
				<th>Feature Name</th>
				<th>Total Rules</th>
				<th><i class="glyphicon glyphicon-gift"></i> Package</th>
				<th><i class="glyphicon glyphicon-cog"></i> Settings</th>
				<th class="text-right">Status</th>
			</tr>
		  </thead>
		  <tbody>
		  <?php foreach( $features as $feature ) : ?>
			<tr>
				<td><?php echo esc_html( $feature->title ) ?></td>
				<td><?php echo $feature->getRuleCount() ?></td>
				<td>
					<a href="<?php echo $plugin->getFeaturesController()->getUrl(['do'=>'export','id'=>$feature->id()]) ?>"><i class="glyphicon glyphicon-download"></i> Download</a>
				</td>
				<td>
					<?php if ( $feature->hasSettings() ) { ?>
					<a href="<?php echo $plugin->getFeaturesController()->getUrl(['do'=>'settings','id'=>$feature->id(),'from'=>'dashboard']) ?>" style="margin-right: 10px"><i class="glyphicon glyphicon-triangle-right"></i> Configure</a>  
					<?php } else { ?>
					None
					<?php } ?>
				</td>
				<td class="text-right"><?php echo $feature->enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></td>
			</tr>
			</tr>
		  <?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		No features yet.
	<?php endif; ?>
  </div>
</div>