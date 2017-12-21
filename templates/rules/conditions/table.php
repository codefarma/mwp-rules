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
 * @param	ActiveRecordTable			$table		The conditions table
 * @param	ActiveRecordController		$controller	The conditions controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<?php echo $controller->getActionsHtml() ?>
<?php echo $table->getDisplay() ?>