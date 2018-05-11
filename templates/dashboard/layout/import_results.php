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
 * @param	array		$results			The import results
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

?>

<?php echo Rules\Plugin::instance()->getTemplateContent( 'snippets/styles/texture_bg' ) ?>

<div style="max-width: 1180px; margin: 0 auto;">
	<div class="row">
		<div class="col-xs-12 text-right">
			<a href="<?php echo $controller->getUrl() ?>" class="btn btn-primary"><i class="glyphicon glyphicon-arrow-left"></i> Return To Dashboard</a>
		</div>
	</div>

	<hr>

	<?php if ( isset( $results['imports']['hooks'] ) ) : ?>
		<h2>Custom Events: <?php echo count( $results['imports']['hooks'] ) ?></h2>
		<ul>
		<?php foreach( $results['imports']['hooks'] as $hook ) : ?>
			<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $hook['data']['hook_title'] ) ?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if ( isset( $results['imports']['logs'] ) ) : ?>
		<h2>Custom Logs: <?php echo count( $results['imports']['logs'] ) ?></h2>
		<ul>
		<?php foreach( $results['imports']['logs'] as $log ) : ?>
			<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $log['data']['custom_log_title'] ) ?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if ( isset( $results['imports']['apps'] ) ) : ?>
		<h2>Apps: <?php echo count( $results['imports']['apps'] ) ?></h2>
		<ul>
		<?php foreach( $results['imports']['apps'] as $app ) : ?>
			<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $app['data']['app_title'] ) ?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if ( isset( $results['imports']['bundles'] ) ) : ?>
		<h2>Bundles: <?php echo count( $results['imports']['bundles'] ) ?></h2>
		<ul>
		<?php foreach( $results['imports']['bundles'] as $bundle ) : ?>
			<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $bundle['data']['bundle_title'] ) ?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if ( isset( $results['imports']['rules'] ) ) : ?>
		<h2>Rules: <?php echo count( $results['imports']['rules'] ) ?></h2>
		<ul>
		<?php foreach( $results['imports']['rules'] as $rule ) : ?>
			<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $rule['data']['rule_title'] ) ?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>
</div>

