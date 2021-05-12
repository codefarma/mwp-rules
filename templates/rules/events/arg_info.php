<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 27, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'rules/events/arg_info', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin					$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\ECA\Event		$event		The event
 * @param	MWP\Rules\Rule			$rule 		The rule which the event context is for
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$strtolower = is_callable( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
$event_arguments = $event ? $event->getArguments( $rule ) : array();
$arguments_by_context = [];

foreach( $event_arguments as $_name => $_arg ) {
	$context = $_arg['context'] ?? '';
	$arguments_by_context[ $context ][ $_name ] = $_arg;
}
?>

<div class="event-argument-info">
	<?php if ( ! empty( $arguments_by_context ) ) : ?>
		<?php foreach ( $arguments_by_context as $context => $arguments ) : ?>
			<?php if ( $context ) : ?>
				<hr style="margin-bottom:0px" />
				<div style="margin:8px 0"><?php echo ucwords($context) ?> Context:</div>
			<?php endif ?>
			<ul>
			<?php foreach ( $arguments as $_event_arg_name => $_event_arg ) : ?>
				<li>
					<code>$<?php echo $_event_arg_name ?></code> (<?php echo $_event_arg['argtype'] ?><?php if ( isset( $_event_arg['class'] ) ) { echo "[" . $_event_arg['class'] . "]"; } ?>) - <?php echo ( isset( $_event_arg['description'] ) ? ucfirst( $strtolower( $_event_arg['description'] ) ) : '' ) . ( ( isset( $_event_arg[ 'nullable' ] ) and $_event_arg[ 'nullable' ] ) ? " ( may be NULL )" : "" ) ?>
					<?php if ( $_event_arg['argtype'] == 'array' and isset( $_event_arg['keys']['mappings'] ) and ! empty( $_event_arg['keys']['mappings'] ) ) : ?>
						<ul class="array-keys">
						<?php foreach( $_event_arg['keys']['mappings'] as $key_name => $key ) : ?>
							<li>[<?php echo $key_name ?>] (<?php echo $key['argtype'] ?><?php if ( isset( $key['class'] ) ) { echo "[" . $key['class'] . "]"; } ?>) - <?php echo isset( $key['description'] ) ? $key['description'] : '' ?></li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach ?>
			</ul>
		<?php endforeach ?>
	<?php else: ?>
		No Event Data Associated
	<?php endif ?>
</div>

