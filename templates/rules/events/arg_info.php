<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 27, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="event-argument-info">
	<?php if ( $event and isset( $event->arguments ) and is_array( $event->arguments ) and count( $event->arguments ) ) : ?>
		<ul>
		<?php foreach ( $event->arguments as $_event_arg_name => $_event_arg ) : ?>
			<li>
				<strong>$<?php echo $_event_arg_name ?></strong> (<?php echo $_event_arg['argtype'] ?>) - <?php echo ( isset( $_event_arg['description'] ) ? ucfirst( mb_strtolower( $_event_arg['description'] ) ) : '' ) . ( ( isset( $_event_arg[ 'nullable' ] ) and $_event_arg[ 'nullable' ] ) ? " ( may be NULL )" : "" ) ?>
				<?php if ( $_event_arg['argtype'] == 'array' and isset( $_event_arg['keys']['mappings'] ) and ! empty( $_event_arg['keys']['mappings'] ) ) : ?>
					<ul class="array-keys">
					<?php foreach( $_event_arg['keys']['mappings'] as $key_name => $key ) : ?>
						<li>[<?php echo $key_name ?>] (<?php echo $key['argtype'] ?>) - <?php echo isset( $key['description'] ) ? $key['description'] : '' ?></li>
					<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach ?>
		</ul>
	<?php else: ?>
		No Data Associated
	<?php endif ?>
</div>

