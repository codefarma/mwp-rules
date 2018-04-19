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
$rules = Rules\Rule::loadWhere('rule_feature_id=0 AND rule_parent_id=0');

?>

<div class="panel panel-info">
  <div class="panel-heading">
	<a href="<?php echo $plugin->getRulesController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Rules</a>
	<h3 class="panel-title">
		Your Automations
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
				<td>
					<a class="nounderline" href="<?php echo $rule->url() ?>"><strong class="text-info"><?php echo esc_html( $rule->title ) ?></strong></a>  
					<i class="glyphicon glyphicon-triangle-right"></i> When 
					<?php if ( $rule->event() ) : ?>
						<strong><?php echo esc_html( $rule->event()->title ) ?></strong>,
					<?php else: ?>
						<strong class="text-danger">An Unknown Event Occurs</strong>,
					<?php endif; ?> 
					<?php if ( count( $conditions = array_filter( $rule->getConditions(), function( $c ) { return $c->enabled; } ) ) ) { ?>
						Under 
							<a class="nounderline" href="<?php echo $rule->url(['_tab'=>'rule_conditions']) ?>">
								<strong class="text-warning">Certain Conditions</strong>,
							</a>
					<?php } ?>
					Then 
					<a class="nounderline" href="<?php echo $rule->url(['_tab'=>'rule_actions']) ?>">
						<strong class="text-success">
							<?php 
								if ( count( $actions = array_filter( $rule->getActions(), function( $a ) { return (bool) $a->enabled; } ) ) > 0 ) {
									$_action = array_shift( $actions );
									$title = esc_html( $_action->title );
								} else {
									$title = __( 'Do Nothing' );
								}
								echo esc_html( $title ) 
							?>
						</strong>
					</a> 
					<?php if ( count( $actions ) > 1 ) { ?> and more... <?php } ?>
				</td>
				<td class="text-right"><?php echo $rule->enabled ? '<span class="text-success">Enabled</span>' . ( $rule->debug ? ' <a href="' . $rule->url(['_tab'=>'rule_debug_console']) . '" class="nounderline"><span class="text-warning">(debug)</span></a>' : '' ) : '<span class="text-danger">Disabled</span>'; ?></td>
			</tr>
			</tr>
		  <?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		You haven't created any automations yet. Begin by <a href="<?php echo $plugin->getRulesController()->getUrl(['do'=>'new']) ?>">starting a new automation rule</a>.
	<?php endif; ?>
  </div>
</div>
