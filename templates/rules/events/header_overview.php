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

?>

<?php if ( $rule ) : ?>
	<div class="rule-overview alert alert-success">
		<div class="rule-title"><i class="glyphicon glyphicon-king"></i> <span class="subtle">Rule:</span> <a href="<?php echo $rule->url() ?>"><?php echo $rule->title ?></a></div>
	</div>
<?php endif; ?>

<div class="event-overview alert alert-info">
	<div class="event-title"><i class="glyphicon glyphicon-info-sign" style="vertical-align: -2px;"></i> <span class="subtle">Event: </span> <?php echo $event->title ?></div>
	<div class="event-description">
		<p style="margin: 0 0 5px 0;"><?php echo $event->description ?></p>
		<span class="label label-info"><?php echo $event->type ?></span> <span class="label label-success"><?php echo $event->hook ?></span> 
	</div>
	<div class="event-arguments">
		<p class="subtle">The following data is provided by this event:</p>
		<?php echo $event->getDisplayArgInfo() ?>
	</div>
</div>

