<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 4, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.9.2
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Rule		$rule		(optional) The rule to use in the overview
 * @param   MWP\Rules\Argument  $argument   (optional) An argument to use in the overview
 * @param   MWP\Rules\Feature   $feature    (optional) The feature to use in the overview
 * @param   MWP\Rules\App       $app        (optional) The app to use in the overview
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

if ( isset( $argument ) ) {
	if ( $arg_parent = $argument->getParent() ) {
		if ( $arg_parent instanceof Rules\Feature ) {
			$feature = $arg_parent;
			$app = $feature->getApp();
		}
		else if ( $arg_parent instanceof Rules\Hook ) {
			$hook = $arg_parent;
		}
	}
}

?>

<?php if ( isset( $app ) or isset( $feature ) or isset( $hook ) or ( isset( $rule_item ) and $rule_item and $rule ) ) : ?>
	<div class="alert alert-warning overview">

	<?php if ( isset( $app ) ) { ?>
		<div class="app-title">
			<span class="subtle"><i class="glyphicon glyphicon-tent"></i> App:</span> <a href="<?php echo $app->url( isset( $feature ) ? [ '_tab' => 'app_features' ] : [] ) ?>"><?php echo esc_html( $app->title ) ?></a>
		</div>
	<?php } ?>

	<?php if ( isset( $feature ) ) { 
		$tab = isset( $rule ) ? 'feature_rules' : ( isset( $argument ) ? 'arguments' : null );
	?>
		<div class="feature-title">
			<span class="subtle"><i class="glyphicon glyphicon-lamp"></i> Feature:</span> <a href="<?php echo $feature->url( isset( $tab ) ? [ '_tab' => $tab ] : [] ) ?>"><?php echo esc_html( $feature->title ) ?></a>
		</div>
	<?php } ?>

	<?php if ( isset( $hook ) ) { ?>
		<div class="feature-title">
			<span class="subtle"><i class="glyphicon glyphicon-flash"></i> <?php echo $hook->getTypeTitle() ?>:</span> <a href="<?php echo $hook->url([ '_tab' => 'arguments' ]) ?>"><?php echo esc_html( $hook->title ) ?></a> 
			<code><?php echo esc_html( $hook->hook ) ?></code>
		</div>
	<?php } ?>
	
	<?php if ( isset( $rule_item ) and $rule_item and $rule ) { ?>
		<div class="rule-title">
			<span class="subtle"><i class="glyphicon glyphicon-triangle-right"></i> Rule:</span> <a href="<?php echo $rule->url([ '_tab' => $rule_item ]) ?>"><?php echo esc_html( $rule->title ) ?></a> 
		</div>	
	<?php } ?>

	</div>
<?php endif; ?>

<?php if ( isset( $rule ) ) {
	
	$event = $rule->event();
	if ( $event ) {
		echo $event->getDisplayDetails( $rule );
	}
	
?>

<?php 
	
	$_rule = $rule;
	$rule_parents = [];

	while ( $_rule = $_rule->parent() ) {
		array_unshift( $rule_parents, $_rule );
	}

	?>
	<?php if ( count( $rule_parents ) ) : ?>
	<div class="alert alert-success">
		<div class="rule-parents-title">
			<span>Rule Group:</span>
		</div>
		<?php foreach( $rule_parents as $i => $_rule ) : ?>
		<ul class="rule-group parent_<?php echo $i ?>">
			<li>
				<?php if ( $_rule->id() ) : ?>
					<a href="<?php echo $_rule->url([ '_tab' => 'rule_subrules' ]) ?>">
				<?php endif; ?>
				<?php echo esc_html( $_rule->title ) ?>
				<?php if ( $_rule->id() ) : ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php foreach( $rule_parents as $_rule ) : ?>
			</li>
		</ul>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
<?php } ?>


