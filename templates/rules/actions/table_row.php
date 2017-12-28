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
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Condition		$condition		The condition to show details for
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$definition = $action->definition();

?>


<strong style="font-size:1.2em"><?php echo $definition ? $definition->title : 'Unregistered condition' ?></strong> 
<?php if ( ! $action->enabled ) : ?>
	<span class="label label-danger">Disabled</span>
<?php endif ?>
<p>
	<i class="glyphicon glyphicon-flash"></i> 
	<?php echo $action->title ?> 
	<?php if ( in_array( $action->schedule_mode, array( 2, 3, 4 ) ) ) : ?>
		<span title="This action will be executed at a scheduled time" class="label label-warning"><i class="glyphicon glyphicon-time"></i> Scheduled</span>
	<?php endif ?>
</p>