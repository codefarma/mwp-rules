<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 19, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin			$this		The plugin instance which is loading this template
 *
 * @param	bool			$event		The event to display a detailed overview for
 * @param	MWP\Rules\Rule	$rule		The rule the event is being applied to
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$tokens = $event->getTokens();

?>

<?php if ( $rule ) : ?>
	<div class="rule-overview alert alert-success">
		<div class="rule-title"><i class="glyphicon glyphicon-king"></i> <span class="subtle">Rule:</span> <a href="<?php echo $rule->url() ?>"><?php echo $rule->title ?></a></div>
	</div>
<?php endif; ?>

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
	</div>
</div>

