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

<strong style="font-size:1.2em"><?php echo $action->title ?></strong>
<p>(<?php echo $definition ? $definition->title : 'Unregistered action' ?>)</p>