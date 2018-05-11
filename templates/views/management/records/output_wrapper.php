<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 13, 2017
 *
 * @package  MWP Application Framework
 * @author   Kevin Carwile
 * @since    1.4.0
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Framework\Plugin								$plugin			The plugin that created the controller
 * @param	MWP\Framework\Helpers\ActiveRecordController		$controller		The active record controller
 * @param	MWP\Framework\Helpers\ActiveRecordTable			$table			The active record display table
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

$breadcrumbs = [];

if ( isset( $_REQUEST['do'] ) ) {
	if ( isset( $record ) and $record->title ) {
		array_unshift( $breadcrumbs, array( 
			'title' => $record->title,
			'class' => 'text-success',
		));
	}
	array_unshift( $breadcrumbs, array(
		'url' => $controller->getUrl(),
		'title' => $controller->adminPage->title,
	));
}

$_controller = $controller;

while ( is_callable( array( $_controller, 'getParent' ) ) and $_parent = $_controller->getParent() ) {
	array_unshift( $breadcrumbs, array( 
		'url' => $_parent->url(),
		'title' => $_parent->title,
		'class' => 'text-success',
		'sep' => '<i class="glyphicon glyphicon-arrow-right" style="font-size:0.8em"></i>',
	));
	$_controller = $_parent->_getController();
	array_unshift( $breadcrumbs, array(
		'url' => $_controller->getUrl(),
		'title' => $_controller->adminPage->title,
	));
}

array_unshift( $breadcrumbs, array(
	'url' => Rules\Plugin::instance()->getDashboardController()->getUrl(),
	'title' => __( 'Automation Rules', 'mwp-rules' ),
));

?>

<?php echo Rules\Plugin::instance()->getTemplateContent( 'snippets/styles/texture_bg' ) ?>
<?php echo Rules\Plugin::instance()->getTemplateContent( 'snippets/screen_header', [ 'title' => $title ] ) ?>

<div class="wrap management records rules <?php echo $classes ?>">
	<h1 style="display:none;"></h1>
	<div class="mwp-bootstrap" style="margin: 10px 12px">
		<i class="glyphicon glyphicon-triangle-left" style="font-size: 0.8em; opacity: 0.75; margin-right: 4px;"></i> 
		<?php foreach( $breadcrumbs as $i => $crumb ) : ?>
			<?php if ( isset( $crumb['url'] ) ) { ?><a href="<?php echo $crumb['url'] ?>"><?php } ?><span class="<?php echo isset( $crumb['class'] ) ? $crumb['class'] : '' ?>"><?php echo esc_html( $crumb['title'] ) ?></span><?php if ( isset( $crumb['url'] ) ) { ?></a><?php } ?>
			<?php if ( $i < count( $breadcrumbs ) - 1 ) : ?>
				<span style="margin:0 7px; opacity: 0.6;"><?php echo isset( $crumb['sep'] ) ? $crumb['sep'] : '\\' ?></span>
			<?php endif ?>
		<?php endforeach ?>
	</div>
	<?php echo $output ?>
</div>
