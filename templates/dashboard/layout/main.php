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
$bundles = Rules\Bundle::loadWhere('bundle_app_id=0');
$rules = Rules\Rule::loadWhere('rule_bundle_id=0 AND rule_parent_id=0');

?>

<style>
#wpcontent {
	background-image: url(<?php echo Rules\Plugin::instance()->fileUrl('assets/img/gray-texture-bg.jpg') ?>); 
	background-size: cover;
}
</style>

<div class="row">
	<div class="col-xs-12 text-right">
		<a href="<?php echo $plugin->getRulesController()->getUrl(['do'=>'new']) ?>" class="btn btn-primary pull-left"><i class="glyphicon glyphicon-arrow-right"></i> Start A New Rule</a>
		<a href="<?php echo $controller->getUrl([ 'do' => 'import_package' ]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-import"></i> Import a Package</a>
	</div>
</div>

<hr>

<div class="row">
	<div class="col-md-8">
		<?php echo $plugin->getTemplateContent( 'dashboard/panels/rules', [ 'controller' => $controller ] ) ?>
		<?php echo $plugin->getTemplateContent( 'dashboard/panels/bundles', [ 'controller' => $controller ] ) ?>
		<?php // echo $plugin->getTemplateContent( 'dashboard/panels/apps', [ 'controller' => $controller ] ) ?>
		<?php echo $plugin->getTemplateContent( 'dashboard/panels/logs', [ 'controller' => $controller ] ) ?>
	</div>
	<div class="col-md-4">
		<?php echo $plugin->getTemplateContent( 'dashboard/panels/expansions', [ 'controller' => $controller ] ) ?>
	</div>
</div>

