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

<p class="alert alert-warning"><strong>Warning:</strong> PHP is for advanced users only. Your php code needs to return a value to use as the argument. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.</p>

The operation expects you to return one of the following argument types:<br>
<ul>
	<?php foreach ( $return_args as $arg_type ) : ?>
	<li><?php echo $arg_type ?></li>
	<?php endforeach ?>
</ul>

<?php if ( $event ) : ?>
	The following variables are available to your php code:<br>
	<?php echo $event->getDisplayArgInfo() ?>
<?php endif ?>
