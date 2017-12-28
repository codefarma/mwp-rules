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

$definition = $condition->definition();
$subconditions = $condition->children();
?>

<strong style="font-size:1.2em"><?php echo $definition ? $definition->title : 'Unregistered condition' ?></strong> 
<?php if ( count( $subconditions ) ) : ?>
	<span class="label label-info"><?php if ( $condition->compareMode() == 'and' ) : ?>AND ALL SUBCONDITIONS<?php else: ?>OR ANY SUBCONDITION<?php endif ?></span>
<?php endif ?>
<?php if ( ! $condition->enabled ) : ?>
	<span class="label label-danger">Disabled</span>
<?php endif ?>
<p>
	<i class="glyphicon glyphicon-filter"></i> 
	<?php echo $condition->title ?> 
	<?php if ( $condition->not ) : ?>
		<span title="This condition evaluates TRUE if the condition is NOT MET" class="label label-warning">NOT</span>
	<?php endif ?>
</p>