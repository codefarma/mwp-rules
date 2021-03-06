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
				<!-- <th><i class="glyphicon glyphicon-gift"></i> Package</th> -->
				<th><i class="glyphicon glyphicon-cog"></i> Settings</th>
				<th class="text-right">Status</th>
			</tr>
		  </thead>
		  <tbody>
		  <?php foreach( $bundles as $bundle ) : ?>
			<tr>
				<td><a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'edit','id'=>$bundle->id()]) ?>"><?php echo esc_html( $bundle->title ) ?></a></td>
				<td><a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'edit','id'=>$bundle->id(),'_tab'=>'bundle_rules']) ?>"><?php echo $bundle->getRuleCount() ?></a></td>
				<!-- <td>
					<i class="glyphicon glyphicon-download"></i> 
					<a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'export','id'=>$bundle->id()]) ?>">Download</a>
				</td> -->
				<td>
					<?php if ( $bundle->hasSettings() ) { ?>
					<i class="glyphicon glyphicon-triangle-right"></i> 
					<a href="<?php echo $plugin->getBundlesController()->getUrl(['do'=>'settings','id'=>$bundle->id(),'from'=>'dashboard']) ?>" style="margin-right: 10px">Configure</a>  
					<?php } else { ?>
					None
					<?php } ?>
				</td>
				<td class="text-right">
					<span class="label label-<?php echo $bundle->enabled ? 'success' : 'danger' ?> rules-pointer" data-rules-enabled-toggle="bundle" data-rules-id="<?php echo $bundle->id() ?>">
						<?php echo $bundle->enabled ? 'ENABLED' : 'DISABLED' ?>
					</span> 
				</td>
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