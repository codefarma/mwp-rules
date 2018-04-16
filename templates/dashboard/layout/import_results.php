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

<?php if ( isset( $results['imports']['apps'] ) ) : ?>
	<h2>Apps: <?php echo count( $results['imports']['apps'] ) ?></h2>
	<ul>
	<?php foreach( $results['imports']['apps'] as $app ) : ?>
		<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $app['data']['app_title'] ) ?></li>
	<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ( isset( $results['imports']['features'] ) ) : ?>
	<h2>Features: <?php echo count( $results['imports']['features'] ) ?></h2>
	<ul>
	<?php foreach( $results['imports']['features'] as $feature ) : ?>
		<li><i class="glyphicon glyphicon-triangle-right"></i> <?php echo esc_html( $feature['data']['feature_title'] ) ?></li>
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


