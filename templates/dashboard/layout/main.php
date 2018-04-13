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
$features = Rules\Feature::loadWhere('feature_app_id=0');
$rules = Rules\Rule::loadWhere('rule_feature_id=0 AND rule_parent_id=0');

?>

<div class="row">
	<div class="col-xs-12 text-right">
		<a href="<?php echo $plugin->getRulesController()->getUrl(['do'=>'new']) ?>" class="btn btn-success pull-left"><i class="glyphicon glyphicon-grain"></i> Start A Rule</a>
		<a href="<?php echo $controller->getUrl([ 'do' => 'import_package' ]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-import"></i> Install a Package</a>
	</div>
</div>

<hr>

<div class="row">
	<div class="col-md-8">
		<div class="panel panel-info">
		  <div class="panel-heading">
			<a href="#" class="btn btn-success btn-xs pull-right">Find Apps</a>
			<a style="margin-right:5px;" href="<?php echo $plugin->getAppsController()->getUrl() ?>" class="btn btn-primary btn-xs pull-right">Manage Apps</a> 
			<h3 class="panel-title">
				Rule Apps
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
							<a href="<?php echo $plugin->getAppsController()->getUrl(['do'=>'settings','id'=>$app->id()]) ?>" type="button"><i class="glyphicon glyphicon-triangle-right"></i> Configure</button>
							<?php } else { ?>
							None
							<?php } ?>
						</td>
						<td class="text-right"><?php echo $app->enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></td>
					</tr>
					</tr>
				  <?php endforeach; ?>
				  </tbody>
				</table>
			<?php else: ?>
				No apps installed.
			<?php endif; ?>
		  </div>
		</div>
		<div class="panel panel-info">
		  <div class="panel-heading">
			<a href="<?php echo $plugin->getFeaturesController()->getUrl() ?>" class="btn btn-primary btn-xs pull-right">Manage Features</a>
			<h3 class="panel-title">
				Feature Sets
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
							<a href="<?php echo $plugin->getFeaturesController()->getUrl(['do'=>'settings','id'=>$feature->id()]) ?>" style="margin-right: 10px"><i class="glyphicon glyphicon-triangle-right"></i> Configure</a>  
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
				No features defined.
			<?php endif; ?>
		  </div>
		</div>
		<div class="panel panel-info">
		  <div class="panel-heading">
			<a href="<?php echo $plugin->getRulesController()->getUrl() ?>" class="btn btn-primary btn-xs pull-right">Manage Rules</a>
			<h3 class="panel-title">
				Individual Rules
			</h3>
		  </div>
		  <div class="panel-body">
			<?php if ( count( $rules ) ) : ?>
				<table class="table">
				  <thead>
					<tr>
						<th>Rule Summary</th>
						<th class="text-right">Status</th>
					</tr>
				  </thead>
				  <tbody>
				  <?php foreach( $rules as $rule ) : ?>
					<tr>
						<td>When 
							<?php if ( $rule->event() ) : ?>
								<strong class="text-info"><?php echo esc_html( $rule->event()->title ) ?></strong>,
							<?php else: ?>
								<strong class="text-danger">An Unknown Event Occurs</strong>,
							<?php endif; ?> Then <strong class="text-success"><?php echo esc_html( $rule->title ) ?></strong></td>
						<td class="text-right"><?php echo $rule->enabled ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></td>
					</tr>
					</tr>
				  <?php endforeach; ?>
				  </tbody>
				</table>
			<?php else: ?>
				No rules created.
			<?php endif; ?>
		  </div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-info">
		  <div class="panel-heading">
			<a href="#" class="btn btn-default btn-xs pull-right">Find Expansions</a> 
			<h3 class="panel-title">Installed Expansions</h3>
		  </div>
		  <div class="panel-body">
			No expansions currently installed.
		  </div>
		</div>		
	</div>
</div>
