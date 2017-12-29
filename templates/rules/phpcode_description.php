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
 * @param	string		$title		The provided title
 * @param	string		$content	The provided content
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<p class="alert alert-warning"><strong>Warning:</strong> PHP is for advanced users only. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.</p>

<?php if ( isset( $return_args ) and ! empty( $return_args ) ) : ?>
	<p class="text-success">
		Your php code needs to return a value to use as an argument. The operation expects you to return one of the following argument types:
	</p>
	<ul>
		<?php foreach ( $return_args as $arg_type ) : ?>
		<li><?php echo $arg_type ?></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ( $event ) : ?>
	<p class="text-success">
		The following variables are available to your php code:
	</p>
	<?php echo $event->getDisplayArgInfo() ?>
<?php endif ?>
