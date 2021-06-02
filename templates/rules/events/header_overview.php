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

$rule_id = isset( $rule ) ? ( $rule->id() ?: 'undefined' ) : 'undefined';

?>

<div class="event-overview alert alert-info" data-view-model="mwp-rules">
	<div class="event-title"><i class="glyphicon glyphicon-info-sign" style="vertical-align: -2px;"></i> <span class="subtle">Event: </span> <?php echo $event->title ?></div>
	<div class="event-description">
		<p style="margin: 0 0 5px 0;"><?php echo $event->description ?></p>
		<span class="label label-info"><?php echo $event->type ?></span> <span class="label label-success"><?php echo $event->hook ?></span> 
	</div>
	<div class="event-arguments">
		<p class="subtle">The following data is provided by this event:</p>
		<?php echo $event->getDisplayArgInfo( isset( $rule ) ? $rule : NULL ) ?>
	</div>
	
	<div class="tokens-toggle" data-bind="
		click: function() { 
			mwp.controller.get('mwp-rules').openTokenBrowser({
				title: 'Browsing All Accessible Data',
				cancel_label: 'Close',
				callback: function( node, tokens, tree, dialog ) {
					dialog.find('[role=token-path]').select();
					return false;
				}
			},
			{ 
				event_type: '<?php echo $event->type ?>', 
				event_hook: '<?php echo esc_attr( $event->hook ) ?>', 
				rule_id: <?php echo $rule_id ?>
			}); 
		}">
		<strong><i class="glyphicon glyphicon-modal-window"></i> Browse All Data</strong>
	</div>

</div>

