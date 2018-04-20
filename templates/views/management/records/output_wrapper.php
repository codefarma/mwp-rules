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

?>

<div class="wrap management records <?php echo $classes ?>">
	<h1 class="mwp-bootstrap"><a href="<?php echo Rules\Plugin::instance()->getDashboardController()->getUrl() ?>" class="btn btn-sm btn-default ull-right" style="margin-right: 15px"><i class="glyphicon glyphicon-triangle-left"></i> Back to Dashboard</a><span><?php echo $title ?></span></h1>
	<?php echo $output ?>
</div>
