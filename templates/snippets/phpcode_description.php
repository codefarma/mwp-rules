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
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<p class="alert alert-warning"><strong>Warning:</strong> PHP is for advanced users only. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.</p>

<?php if ( isset( $return_args ) and ! empty( $return_args ) ) : ?>
	<p class="text-success">
		Your php code needs to return one of the following argument types:
	</p>
	<ul>
		<?php foreach ( $return_args as $arg_description ) : ?>
		<li><?php echo $arg_description ?></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ( isset( $variables ) and ! empty( $variables ) ) : ?>
	<hr>
	<p class="text-success">
		The following variables are available to your php code:
	</p>
	<ul>
		<?php foreach ( $variables as $var_description ) : ?>
		<li><?php echo $var_description ?></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ( isset( $event ) and ! empty( $event ) ) : ?>
	<hr>
	<p class="text-success">
		The following variables are available to your php code:
	</p>
	<?php echo $event->getDisplayArgInfo() ?>
<?php endif ?>
