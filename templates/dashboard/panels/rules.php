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
$rules = Rules\Rule::loadWhere('rule_bundle_id=0 AND rule_parent_id=0 AND rule_custom_internal=0');

?>

<div class="panel panel-info" data-view-model="mwp-rules">
  <div class="panel-heading">
	<a href="<?php echo $plugin->getRulesController()->getUrl() ?>" class="btn btn-default btn-xs pull-right">Manage Rules</a>
	<a href="https://www.codefarma.com/products/automations" target="_blank" class="btn btn-primary btn-xs pull-right" style="margin-right: 5px">Find Automations</a>
	<h3 class="panel-title">
		Your Automations <small style="opacity: 0.7; font-size:0.75em; margin-left:20px;"><a target="_blank" href="https://www.codefarma.com/docs/rules-user"><i class="glyphicon glyphicon-question-sign"></i> User Guide</a></small>
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
					<?php if ( count( $rule->children() ) > 0 ) { ?> <strong>+ <?php echo count( $rule->children() ) ?> <a href="<?php echo $rule->url(['_tab'=>'rule_subrules']) ?>">Sub-rules</a></strong><?php } ?>
				</td>
				<td class="text-right">
					<?php echo ( $rule->debug ? ' <a href="' . $rule->url(['_tab'=>'rule_debug_console']) . '" class="nounderline"><span class="text-warning">(debug)</span></a>' : '' ) ?> 
					<span class="label label-<?php echo $rule->enabled ? 'success' : 'danger' ?> rules-pointer" data-rules-enabled-toggle="rule" data-rules-id="<?php echo $rule->id() ?>">
						<?php echo $rule->enabled ? 'ENABLED' : 'DISABLED' ?>
					</span> 
				</td>
			</tr>
			</tr>
		  <?php endforeach; ?>
		  </tbody>
		</table>
	<?php else: ?>
		No rules yet. <span style="margin-left: 15px">Begin by <a href="<?php echo $plugin->getRulesController()->getUrl(['do'=>'new']) ?>">starting a new rule</a>.</span>
	<?php endif; ?>
  </div>
</div>
