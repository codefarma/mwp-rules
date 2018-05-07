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
$bundles = Rules\Bundle::loadWhere('bundle_app_id=0');

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<a href="<?php echo $plugin->getBundlesController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Bundles</a> 
	<a href="https://www.codefarma.com/products/automation-bundles" target="_blank" class="btn btn-success btn-xs pull-right" style="margin-right: 5px">Browse Bundles</a>
	<h3 class="panel-title">
		Automation Bundles <small style="opacity: 0.7; font-size:0.75em; margin-left:20px;">Bundles are rules that are grouped into a unit.</small>
	</h3>
  </div>
  <div class="panel-body">
	<?php if ( count( $bundles ) ) : ?>
		<table class="table">
		  <thead>
			<tr>
				<th>Bundle Name</th>
				<th>Total Rules</th>
				<th><i class="glyphicon glyphicon-gift"></i> Package</th>
				<th><i class="glyphicon glyphicon-cog"></i> Settings</th>
				<th class="text-right">Status</th>
			</tr>
		  </thead>
		  <tbody>
		  <?php foreach( $bundles as $bundle ) : ?>
			<tr>
				<td><a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'edit','id'=>$bundle->id()]) ?>"><?php echo esc_html( $bundle->title ) ?></a></td>
				<td><?php echo $bundle->getRuleCount() ?></td>
				<td>
					<i class="glyphicon glyphicon-download"></i> 
					<a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'export','id'=>$bundle->id()]) ?>">Download</a>
				</td>
				<td>
					<?php if ( $bundle->hasSettings() ) { ?>
					<i class="glyphicon glyphicon-triangle-right"></i> 
					<a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'settings','id'=>$bundle->id(),'from'=>'dashboard']) ?>" style="margin-right: 10px">Configure</a>  
					<?php } else { ?>
					None
					<?php } ?>
				</td>
				<td class="text-right"><?php echo $bundle->enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></td>
			</tr>
			</tr>
		  <?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		No bundles yet. 
	<?php endif; ?>
  </div>
</div>