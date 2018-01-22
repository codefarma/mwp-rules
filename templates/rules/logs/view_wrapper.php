<?php
/**
 * Plugin HTML Template
 *
 * Created:  January 8, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 * @param	string												$title			The provided title
 * @param	Modern\Wordpress\Plugin								$plugin			The plugin associated with the active records/view
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The associated controller displaying this view
 * @param	MWP\Rules\Log										$log			The log record
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="wrap">

	<h1><?php echo $title ?></h1>
	
	<div>
		<?php echo $log->getView() ?>
	</div>
</div>