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
$apps = Rules\App::loadWhere('1');

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<a href="#" class="btn btn-success btn-xs pull-right">Get Apps</a>
	<!-- <a style="margin-right:5px;" href="<?php echo $plugin->getAppsController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Apps</a> -->
	<h3 class="panel-title">
		Automation Apps <small style="opacity: 0.7; font-size:0.75em; margin-left:20px;">Apps are packaged automation bundles.</small>
	</h3>
  </div>
  <div class="panel-body">
	<?php if ( count( $apps ) ) : ?>
		<table class="table">
		  <thead>
			<tr>
				<th>App Name</th>
				<th>Author</th>
				<th>Version</th>
				<th><i class="glyphicon glyphicon-gift"></i> Package</th>
				<th><i class="glyphicon glyphicon-cog"></i> Settings</th>
				<th class="text-right">Status</th>
			</tr>
		  </thead>
		  <tbody>
		  <?php foreach( $apps as $app ) : ?>
			<tr>
				<td><?php echo esc_html( $app->title ) ?></td>
				<td><?php echo $app->creator ?></td>
				<td><?php echo $app->version ?></td>
				<td>
					<a href="<?php echo $plugin->getAppsController()->getUrl(['do'=>'export','id'=>$app->id()]) ?>"><i class="glyphicon glyphicon-download"></i> Download</a>
				</td>
				<td>
					<?php if ( $app->hasSettings() ) { ?>
					<a href="<?php echo $plugin->getAppsController()->getUrl(['do'=>'settings','id'=>$app->id(),'from'=>'dashboard']) ?>" type="button"><i class="glyphicon glyphicon-triangle-right"></i> Configure</button>
					<?php } else { ?>
					None
					<?php } ?>
				</td>
				<td class="text-right">
					<span class="label label-<?php echo $app->enabled ? 'success' : 'danger' ?> rules-pointer" data-rules-enabled-toggle="app" data-rules-id="<?php echo $app->id() ?>">
						<?php echo $app->enabled ? 'ENABLED' : 'DISABLED' ?>
					</span> 
				</td>
			</tr>
			</tr>
		  <?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		No apps yet. 
	<?php endif; ?>
  </div>
</div>
