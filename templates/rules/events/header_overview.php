<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 19, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * @param	Plugin			$this		The plugin instance which is loading this template
 *
 * @param	bool			$event		The event to display a detailed overview for
 * @param	MWP\Rules\Rule	$rule		The rule the event is being applied to
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$tokens = $event->getTokens( NULL, $rule );

?>

<div class="event-overview alert alert-info" data-view-model="mwp-rules">
	<div class="event-title"><i class="glyphicon glyphicon-info-sign" style="vertical-align: -2px;"></i> <span class="subtle">Event: </span> <?php echo $event->title ?></div>
	<div class="event-description">
		<p style="margin: 0 0 5px 0;"><?php echo $event->description ?></p>
		<span class="label label-info"><?php echo $event->type ?></span> <span class="label label-success"><?php echo $event->hook ?></span> 
	</div>
	<div class="event-arguments">
		<p class="subtle">The following data is provided by this event:</p>
		<?php echo $event->getDisplayArgInfo() ?>
	</div>
	
	<div class="tokens-toggle" data-bind="click: function(vm, e) { jQuery(e.target).closest('.event-overview').find('.tokens-list').slideToggle(); }">
		<strong><i class="glyphicon glyphicon-triangle-right"></i> Replacement Tokens</strong> (<?php echo count( $tokens ) ?> tokens)
	</div>
	<div class="tokens-list">
		<div style="margin:3px 0;">
			<i class="glyphicon glyphicon-info-sign"></i> You can type the names of replacement tokens (including the brackets) into text entry fields on this form and they will be replaced by their associated data when the rule is executed.<br>
			<i class="glyphicon glyphicon-arrow-right"></i> Alternative token format: Replace the brackets with tildes ( ~ ) ( Example: ~token:name~ ) for use in places where brackets are problematic (such as urls).
		</div>
		
		<ul>
		<?php foreach( $tokens as $token => $description ) : ?>
			<li><code><?php echo esc_html( $token ) . '</code> - ' . esc_html( $description ); ?></li>
		<?php endforeach; ?>
		</ul>
		
		<?php /*
		<h3>Global Data</h3>
		<ul>
			<?php foreach( Rules\Plugin::instance()->getGlobalArguments() as $global_key => $global_arg ) : ?>
				<?php $derivatives = Rules\Plugin::instance()->getDerivativeTokens( $global_arg ); ?>
				<li data-token="global:<?php echo $global_key ?>" class="<?php if ( count( $derivatives ) ) { echo "has-derivatives"; } ?>"><code>global:<?php echo $global_key ?></code></li>
			<?php endforeach; ?>
		</ul>
		<?php if ( ! empty( $event->arguments ) ) : ?>
			<h3>Event Data</h3>
			<ul>
				<?php foreach( $event->arguments as $arg_key => $arg_def ) : ?>
					<?php $derivatives = Rules\Plugin::instance()->getDerivativeTokens( $arg_def ); ?>
					<li data-token="event:<?php echo $arg_key ?>" class="<?php if ( count( $derivatives ) ) { echo "has-derivatives"; } ?>"><code>event:<?php echo $arg_key ?></code></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( isset( $rule ) && $feature = $rule->getFeature() ) : ?>
			<h3>Feature Settings</h3>
			<ul>
				<?php foreach( $feature->getArguments() as $argument ) : ?>
					<?php $derivatives = Rules\Plugin::instance()->getDerivativeTokens( $argument->getProvidesDefinition() ); ?>
					<li data-token="feature:<?php echo $argument->varname ?>" class="<?php if ( count( $derivatives ) ) { echo "has-derivatives"; } ?>"><code>feature:<?php echo $argument->varname ?></code></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>	
		*/
		?>
	</div>
</div>

