<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 4, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Rule		$rule		The rule to display an overview for
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

?>

<?php if ( isset( $app ) or isset( $feature ) ) : ?>
	<div class="alert alert-warning overview">

	<?php if ( isset( $app ) ) { ?>
		<div class="app-title">
			<span class="subtle"><i class="glyphicon glyphicon-tent"></i> App:</span> <a href="<?php echo $app->url( isset( $feature ) ? [ '_tab' => 'app_features' ] : [] ) ?>"><?php echo esc_html( $app->title ) ?></a>
		</div>
	<?php } ?>

	<?php if ( isset( $feature ) ) { ?>
		<div class="feature-title">
			<span class="subtle"><i class="glyphicon glyphicon-lamp"></i> Feature:</span> <a href="<?php echo $feature->url( isset( $rule ) ? [ '_tab' => 'feature_rules' ] : [] ) ?>"><?php echo esc_html( $feature->title ) ?></a>
		</div>
	<?php } ?>

	</div>
<?php endif; ?>

<?php if ( isset( $rule ) ) {
	
	$event = $rule->event();
	if ( $event ) {
		echo $event->getDisplayDetails();
	}
	
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


