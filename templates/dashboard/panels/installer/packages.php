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
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$plugin = Rules\Plugin::instance();

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<h3 class="panel-title">Packaged Automations</h3>
  </div>
  <div class="panel-body">
	<?php foreach( $plugin->getPluginRulesPackages() as $rules_package ) { ?>
		<table class="table">
			<thead>
				<tr>
					<th>Plugin</th>
					<th>Package</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach( $rules_package['packages'] as $package ) { ?>
				<tr>
					<td><?php echo $rules_package['source']['data']['Name'] ?></td>
					<td><?php echo $package->getTitle() ?></td>
					<td><span class="label label-warning">Not Installed</span></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
  </div>
</div>

